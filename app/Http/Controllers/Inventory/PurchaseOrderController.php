<?php

namespace App\Http\Controllers\Inventory;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockLocation;
use App\Models\Inventory\Supplier;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function __construct(private StockService $stock)
    {
        $this->middleware('permission:View stock')->only(['index', 'show']);
        $this->middleware('permission:Manage purchase orders')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->input('status'), PurchaseOrder::STATUSES) ? $request->input('status') : null;
        $orders = PurchaseOrder::with(['supplier', 'location', 'createdBy'])->withCount('lines')
            ->when($status, fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->when($request->integer('supplier'), fn ($q, $v) => $q->where('supplier_id', $v))
            ->orderByRaw("case status when 'draft' then 0 when 'ordered' then 1 when 'partial' then 2 else 3 end")->latest('id')
            ->paginate(25)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $locations = StockLocation::active()->ordered()->get();
        $counts = PurchaseOrder::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $pagetitle = 'Purchase Orders';

        return view('inventory.po.index', compact('pagetitle', 'orders', 'suppliers', 'locations', 'status', 'counts'));
    }

    public function create(Request $request)
    {
        return $this->form(null, $request->integer('supplier') ?: null);
    }

    public function edit(PurchaseOrder $po)
    {
        if (! $po->isEditable()) {
            return redirect()->route('inventory.po.show', $po)->with('error', 'Only a draft order can be changed.');
        }

        return $this->form($po->load('lines.item'));
    }

    private function form(?PurchaseOrder $po, ?int $supplierId = null)
    {
        $items = StockItem::active()->with('category')->orderBy('name')->get();
        $locations = StockLocation::active()->ordered()->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $pagetitle = $po ? "Edit {$po->number}" : 'New Purchase Order';

        return view('inventory.po.form', compact('pagetitle', 'po', 'items', 'locations', 'suppliers', 'supplierId'));
    }

    public function store(Request $request)
    {
        return $this->save($request, null);
    }

    public function update(Request $request, PurchaseOrder $po)
    {
        return $this->save($request, $po);
    }

    private function save(Request $request, ?PurchaseOrder $po)
    {
        $data = $request->validate([
            'supplier_id'       => 'required|exists:suppliers,id',
            'location_id'       => 'required|exists:stock_locations,id',
            'expected_on'       => 'nullable|date',
            'notes'             => 'nullable|string|max:500',
            'lines'             => 'required|array|min:1',
            'lines.*.item_id'   => 'nullable|exists:stock_items,id',
            'lines.*.quantity'  => 'nullable|numeric|min:0|max:99999999',
            'lines.*.unit_cost' => 'nullable|numeric|min:0|max:99999999',
            'send'              => 'nullable|boolean',
        ]);
        try {
            $po = $this->stock->savePurchaseOrder($po, $data, $data['lines']);
            if ($request->boolean('send')) {
                $this->stock->markOrdered($po);
            }
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.po.show', $po)->with('success', $request->boolean('send') ? "{$po->number} marked as ordered." : "{$po->number} saved as a draft.");
    }

    public function show(Request $request, PurchaseOrder $po)
    {
        $po->load(['supplier', 'location', 'lines.item', 'receipts.receivedBy', 'createdBy', 'orderedBy']);
        $pagetitle = $po->number;

        return view($request->boolean('print') ? 'inventory.po.print' : 'inventory.po.show', compact('pagetitle', 'po'));
    }

    public function order(PurchaseOrder $po)
    {
        return $this->attempt(fn () => $this->stock->markOrdered($po), "{$po->number} marked as ordered.");
    }

    public function cancel(Request $request, PurchaseOrder $po)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);

        return $this->attempt(fn () => $this->stock->cancelPurchaseOrder($po, $data['reason']), "{$po->number} cancelled.");
    }

    public function reorder(Request $request)
    {
        $data = $request->validate(['location_id' => 'required|exists:stock_locations,id']);
        $made = $this->stock->draftReorders((int) $data['location_id']);

        return redirect()->route('inventory.po.index', ['status' => 'draft'])->with($made ? 'success' : 'error', $made
            ? count($made) . ' draft order(s) created — check quantities and prices, then mark them ordered.'
            : 'Nothing to reorder: no low items have a preferred supplier set.');
    }

    private function attempt(callable $fn, string $ok)
    {
        try {
            $fn();
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $ok);
    }
}
