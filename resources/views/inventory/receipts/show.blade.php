@extends('layouts.master')

@section('content')
@include('reservations.partials.styles')
@php use App\Models\Inventory\StockItem; $r = $receipt; @endphp
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <x-cb.hero :title="$r->number" icon="ri-truck-line" :subtitle="($r->supplier?->name ?? 'Supplier') . ' → ' . $r->location->name . ' · ' . $r->received_on->format('j M Y')" :back="route('inventory.receipts.index')" back-label="Goods received">
        <x-slot:actions>
            <a class="cb-hero-btn" href="{{ route('inventory.receipts.show', [$r, 'print' => 1]) }}" target="_blank" rel="noopener"><i class="ri-printer-line"></i> Print GRN</a>
        </x-slot:actions>
    </x-cb.hero>
    @include('rooms.partials.flash')

    <div class="row g-3">
        <div class="col-xl-8">
            <x-cb.card title="Items" icon="ri-archive-line" :flush="true">
                <table class="cb-table stack">
                    <thead><tr><th>Item</th><th class="num">Quantity</th><th class="num">Unit cost</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        @foreach ($r->lines as $l)
                            <tr>
                                <td data-label="Item"><a href="{{ route('inventory.items.show', $l->item) }}" class="fw-semibold text-reset">{{ $l->item->name }}</a></td>
                                <td data-label="Quantity" class="num">{{ StockItem::qty($l->quantity) }} {{ $l->item->unit }}</td>
                                <td data-label="Unit cost" class="num">@money($l->unit_cost)</td>
                                <td data-label="Total" class="num fw-semibold">@money((float) $l->quantity * (float) $l->unit_cost)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="num fw-bold">@money($r->total)</td></tr></tfoot>
                </table>
            </x-cb.card>
        </div>
        <div class="col-xl-4">
            <x-cb.card title="Details" icon="ri-information-line">
                <dl class="rm-meta mb-0" style="grid-template-columns: 120px 1fr">
                    <dt>Supplier</dt><dd>{{ $r->supplier?->name ?? '—' }}</dd>
                    <dt>Invoice no.</dt><dd>{{ $r->supplier_invoice ?: '—' }}</dd>
                    <dt>Into</dt><dd>{{ $r->location->name }}</dd>
                    @if ($r->purchaseOrder)<dt>Order</dt><dd><a href="{{ route('inventory.po.show', $r->purchaseOrder) }}">{{ $r->purchaseOrder->number }}</a></dd>@endif
                    <dt>Received</dt><dd>{{ $r->received_on->format('j M Y') }}<div class="small text-muted fw-normal">by {{ $r->receivedBy?->name }}, booked {{ $r->created_at->format('j M g:i A') }}</div></dd>
                    @if ($r->notes)<dt>Notes</dt><dd>{{ $r->notes }}</dd>@endif
                </dl>
            </x-cb.card>
        </div>
    </div>
</div>
</div>
</div>
@endsection
