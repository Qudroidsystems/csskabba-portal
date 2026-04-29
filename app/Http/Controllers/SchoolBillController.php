<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SchoolBillModel;
use Yajra\DataTables\DataTables;

class SchoolBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-bills|Create school-bills|Update school-bills|Delete school-bills', ['only' => ['index']]);
        $this->middleware('permission:Create school-bills', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update school-bills', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete school-bills', ['only' => ['destroy', 'bulkDestroy']]);
    }

    /**
     * Display listing with DataTables AJAX support.
     */
    public function index(Request $request)
    {
        // ── Stats endpoint ────────────────────────────────────────────
        if ($request->has('stats')) {
            $bills = SchoolBillModel::leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
                ->whereIn('student_status.id', [1, 2])
                ->select('school_bill.bill_amount', 'school_bill.statusId')
                ->get();

            return response()->json([
                'stats' => [
                    'total'        => $bills->count(),
                    'old'          => $bills->where('statusId', 1)->count(),
                    'new'          => $bills->where('statusId', 2)->count(),
                    'total_amount' => $bills->sum('bill_amount'),
                ]
            ]);
        }

        // ── DataTables AJAX ───────────────────────────────────────────
        if ($request->ajax()) {
            $schoolbills = SchoolBillModel::leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
                ->whereIn('student_status.id', [1, 2])
                ->select([
                    'school_bill.id',
                    'school_bill.title',
                    'school_bill.description',
                    'school_bill.bill_amount',
                    'student_status.id as statusId',
                    'school_bill.updated_at',
                ]);

            return DataTables::of($schoolbills)
                ->addIndexColumn()
                ->addColumn('status_name', function ($row) {
                    if ($row->statusId == 1) {
                        return '<span class="bill-badge bill-badge-old"><i class="ri-user-line me-1"></i>Old Student</span>';
                    }
                    if ($row->statusId == 2) {
                        return '<span class="bill-badge bill-badge-new"><i class="ri-user-add-line me-1"></i>New Student</span>';
                    }
                    return '<span class="bill-badge bill-badge-unknown">Unknown</span>';
                })
                ->addColumn('formatted_amount', function ($row) {
                    return '₦&nbsp;' . number_format($row->bill_amount, 2);
                })
                ->addColumn('formatted_date', function ($row) {
                    return $row->updated_at
                        ? '<span class="text-muted small">'
                            . $row->updated_at->format('d M Y')
                            . '<br><span style="font-size:10px">'
                            . $row->updated_at->format('H:i')
                            . '</span></span>'
                        : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';
                    if (auth()->user()->can('Update school-bills')) {
                        $buttons .= '<button class="btn btn-primary edit-bill" title="Edit"
                            data-id="'          . $row->id             . '"
                            data-title="'       . e($row->title)       . '"
                            data-amount="'      . $row->bill_amount    . '"
                            data-description="' . e($row->description) . '"
                            data-status="'      . $row->statusId       . '">
                            <i class="ri-pencil-line"></i>
                        </button>';
                    }
                    if (auth()->user()->can('Delete school-bills')) {
                        $buttons .= '<button class="btn btn-danger delete-bill" title="Delete"
                            data-id="'    . $row->id       . '"
                            data-title="' . e($row->title) . '">
                            <i class="ri-delete-bin-line"></i>
                        </button>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status_name', 'formatted_amount', 'formatted_date', 'action'])
                ->make(true);
        }

        $pagetitle = 'School Bill Management';
        return view('schoolbill.index', compact('pagetitle'));
    }

    /**
     * Store a newly created bill.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|min:1|unique:school_bill,title',
            'bill_amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'statusId'    => 'required|in:1,2',
        ], [
            'title.required'       => 'Please enter a bill title.',
            'title.unique'         => 'This bill title already exists.',
            'bill_amount.required' => 'Please enter a bill amount.',
            'bill_amount.numeric'  => 'Bill amount must be a number.',
            'bill_amount.min'      => 'Bill amount must be at least ₦1.',
            'statusId.required'    => 'Please select a student status.',
            'statusId.in'          => 'Invalid student status selected.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $amount = floatval(str_replace(['₦', ','], '', $request->bill_amount));

        $bill = SchoolBillModel::create([
            'title'       => $request->title,
            'bill_amount' => $amount,
            'description' => $request->description,
            'statusId'    => $request->statusId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Bill created successfully.',
            'data'    => $bill,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bill = SchoolBillModel::find($id);
        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $bill]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $bill = SchoolBillModel::find($id);
        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $bill]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $bill = SchoolBillModel::find($id);
        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'required|min:1|unique:school_bill,title,' . $id,
            'bill_amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'statusId'    => 'required|in:1,2',
        ], [
            'title.required'       => 'Please enter a bill title.',
            'title.unique'         => 'This bill title already exists.',
            'bill_amount.required' => 'Please enter a bill amount.',
            'bill_amount.numeric'  => 'Bill amount must be a number.',
            'bill_amount.min'      => 'Bill amount must be at least ₦1.',
            'statusId.required'    => 'Please select a student status.',
            'statusId.in'          => 'Invalid student status selected.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $amount = floatval(str_replace(['₦', ','], '', $request->bill_amount));

        $bill->update([
            'title'       => $request->title,
            'bill_amount' => $amount,
            'description' => $request->description,
            'statusId'    => $request->statusId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Bill updated successfully.',
            'data'    => $bill,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bill = SchoolBillModel::find($id);
        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }

        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully.',
        ]);
    }

    /**
     * Bulk delete bills.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No bills selected.',
            ], 400);
        }

        $deleted = SchoolBillModel::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' bill(s) deleted successfully.',
        ]);
    }
}
