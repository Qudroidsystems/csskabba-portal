@extends('layouts.master')

@section('content')
@include('reservations.partials.styles')
@php use App\Models\Inventory\StockItem; $q = fn ($v) => StockItem::qty($v); @endphp
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <x-cb.hero :title="$po->number" icon="ri-file-list-3-line" :subtitle="$po->supplier->name . ' → ' . $po->location->name" :back="route('inventory.po.index')" back-label="Purchase orders">
        <x-slot:pills><span class="cb-meta-pill"><i class="ri-information-line"></i> {{ $po->status_label }}</span>
            @if ($po->expected_on)<span class="cb-meta-pill"><i class="ri-calendar-line"></i> Expected {{ $po->expected_on->format('D j M') }}</span>@endif</x-slot:pills>
        <x-slot:actions>
            @if ($po->canReceive())
                @can('Receive stock')<a class="cb-hero-btn" href="{{ route('inventory.receipts.create', ['po' => $po->id]) }}" style="background:#16a34a;border-color:#16a34a"><i class="ri-truck-line"></i> Receive delivery</a>@endcan
            @endif
            @can('Manage purchase orders')
                @if ($po->isEditable())
                    <a class="cb-hero-btn" href="{{ route('inventory.po.edit', $po) }}"><i class="ri-pencil-line"></i> Edit</a>
                    <form method="POST" action="{{ route('inventory.po.order', $po) }}">@csrf<button class="cb-hero-btn" type="submit"><i class="ri-send-plane-line"></i> Mark ordered</button></form>
                @endif
                @if (in_array($po->status, ['draft', 'ordered', 'partial'], true))
                    <button type="button" class="cb-hero-btn" data-bs-toggle="modal" data-bs-target="#cancelPo"><i class="ri-close-circle-line"></i> {{ $po->status === 'partial' ? 'Close' : 'Cancel' }}</button>
                @endif
            @endcan
            <a class="cb-hero-btn" href="{{ route('inventory.po.show', [$po, 'print' => 1]) }}" target="_blank" rel="noopener"><i class="ri-printer-line"></i> Print</a>
        </x-slot:actions>
    </x-cb.hero>
    @include('rooms.partials.flash')
    @if ($po->status === 'cancelled')<div class="cb-banner danger"><i class="ri-close-circle-line"></i><div>Cancelled {{ $po->cancelled_at?->format('j M Y') }} — {{ $po->cancel_reason }}</div></div>@endif

    <div class="row g-3">
        <div class="col-xl-8">
            <x-cb.card title="Items" icon="ri-archive-line" :flush="true">
                <table class="cb-table stack">
                    <thead><tr><th>Item</th><th class="num">Ordered</th><th class="num">Received</th><th class="num">Unit cost</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        @foreach ($po->lines as $l)
                            <tr>
                                <td data-label="Item"><a href="{{ route('inventory.items.show', $l->item) }}" class="fw-semibold text-reset">{{ $l->item->name }}</a></td>
                                <td data-label="Ordered" class="num">{{ $q($l->quantity) }} {{ $l->item->unit }}</td>
                                <td data-label="Received" class="num {{ $l->outstanding() > 0 && $po->status !== 'draft' ? 'text-warning-emphasis fw-semibold' : 'text-success' }}">{{ $q($l->received_quantity) }}@if ($l->outstanding() > 0 && $po->status !== 'draft')<div class="small">{{ $q($l->outstanding()) }} to come</div>@endif</td>
                                <td data-label="Unit cost" class="num">@money($l->unit_cost)</td>
                                <td data-label="Total" class="num fw-semibold">@money((float) $l->quantity * (float) $l->unit_cost)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="4" class="text-end fw-bold">Order value</td><td class="num fw-bold">@money($po->total)</td></tr></tfoot>
                </table>
            </x-cb.card>
            @if ($po->receipts->isNotEmpty())
                <x-cb.card title="Deliveries" icon="ri-truck-line" :count="$po->receipts->count()">
                    @foreach ($po->receipts as $r)
                        <div class="d-flex justify-content-between py-2 border-bottom"><span><a href="{{ route('inventory.receipts.show', $r) }}" class="rs-ref">{{ $r->number }}</a> <span class="small text-muted">{{ $r->received_on->format('j M Y') }} · {{ $r->receivedBy?->name }}</span></span><strong>@money($r->total)</strong></div>
                    @endforeach
                </x-cb.card>
            @endif
        </div>
        <div class="col-xl-4">
            <x-cb.card title="Supplier" icon="ri-store-3-line">
                <div class="fw-bold" style="color: var(--cb-heading)">{{ $po->supplier->name }}</div>
                @if ($po->supplier->contact_person)<div class="small">{{ $po->supplier->contact_person }}</div>@endif
                @if ($po->supplier->phone)<div class="small"><a href="tel:{{ $po->supplier->phone }}">{{ $po->supplier->phone }}</a></div>@endif
                @if ($po->supplier->email)<div class="small"><a href="mailto:{{ $po->supplier->email }}">{{ $po->supplier->email }}</a></div>@endif
            </x-cb.card>
            <x-cb.card title="History" icon="ri-history-line">
                <div class="small">Created {{ $po->created_at->format('j M Y, g:i A') }} by {{ $po->createdBy?->name }}</div>
                @if ($po->ordered_at)<div class="small">Ordered {{ $po->ordered_at->format('j M Y, g:i A') }} by {{ $po->orderedBy?->name }}</div>@endif
                @if ($po->notes)<div class="small mt-2 text-muted">{{ $po->notes }}</div>@endif
            </x-cb.card>
        </div>
    </div>
</div>
</div>
</div>

<div class="modal fade" id="cancelPo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content rm-form" method="POST" action="{{ route('inventory.po.cancel', $po) }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title text-danger">{{ $po->status === 'partial' ? 'Close' : 'Cancel' }} {{ $po->number }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><label>Reason <span class="text-danger">*</span></label><input name="reason" class="form-control" maxlength="255" required placeholder="e.g. Supplier out of stock">
                @if ($po->status === 'partial')<div class="small text-muted mt-2">What has already arrived stays in stock; the rest will not be expected.</div>@endif</div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep it</button><button class="btn btn-danger" type="submit">{{ $po->status === 'partial' ? 'Close order' : 'Cancel order' }}</button></div>
        </form>
    </div>
</div>
@endsection
