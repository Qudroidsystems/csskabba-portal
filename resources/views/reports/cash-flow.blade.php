{{-- resources/views/reports/cash-flow.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.report-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
}
.report-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: white;
    padding: 15px 20px;
}
.report-body {
    padding: 20px;
}
.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e3a5f;
    margin: 15px 0 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid #e2e8f0;
}
.positive-flow {
    color: #16a34a;
}
.negative-flow {
    color: #dc2626;
}
.total-row {
    background-color: #f8fafc;
    font-weight: bold;
    border-top: 2px solid #e2e8f0;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Cash Flow Statement</h4>
            @php
                $startDateObj = is_string($startDate) ? \Carbon\Carbon::parse($startDate) : $startDate;
                $endDateObj = is_string($endDate) ? \Carbon\Carbon::parse($endDate) : $endDate;
            @endphp
            <p class="text-muted">For the period {{ $startDateObj->format('d F, Y') }} - {{ $endDateObj->format('d F, Y') }}</p>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-bar-chart-line me-2"></i>Cash Flow Analysis</h5>
        </div>
        <div class="report-body">
            {{-- Operating Activities --}}
            <h6 class="section-title"><i class="ri-building-line me-2"></i>Operating Activities</h6>
            <table class="table table-sm">
                <tbody>
                    @foreach($cashFlow['operating_activities'] ?? [] as $activity)
                    <tr>
                        <td>{{ $activity['description'] }}</td>
                        <td class="text-end {{ $activity['amount'] >= 0 ? 'positive-flow' : 'negative-flow' }}">
                            {{ $activity['amount'] >= 0 ? '+' : '' }}₦{{ number_format(abs($activity['amount']), 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>Net Cash from Operating Activities</strong></td>
                        <td class="text-end">
                            @php
                                $netOperating = array_sum(array_column($cashFlow['operating_activities'] ?? [], 'amount'));
                            @endphp
                            <strong class="{{ $netOperating >= 0 ? 'positive-flow' : 'negative-flow' }}">
                                {{ $netOperating >= 0 ? '+' : '' }}₦{{ number_format(abs($netOperating), 2) }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Investing Activities --}}
            <h6 class="section-title mt-4"><i class="ri-invest-line me-2"></i>Investing Activities</h6>
            <table class="table table-sm">
                <tbody>
                    @foreach($cashFlow['investing_activities'] ?? [] as $activity)
                    <tr>
                        <td>{{ $activity['description'] }}</td>
                        <td class="text-end {{ $activity['amount'] >= 0 ? 'positive-flow' : 'negative-flow' }}">
                            {{ $activity['amount'] >= 0 ? '+' : '' }}₦{{ number_format(abs($activity['amount']), 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>Net Cash from Investing Activities</strong></td>
                        <td class="text-end">
                            @php
                                $netInvesting = array_sum(array_column($cashFlow['investing_activities'] ?? [], 'amount'));
                            @endphp
                            <strong class="{{ $netInvesting >= 0 ? 'positive-flow' : 'negative-flow' }}">
                                {{ $netInvesting >= 0 ? '+' : '' }}₦{{ number_format(abs($netInvesting), 2) }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Financing Activities --}}
            <h6 class="section-title mt-4"><i class="ri-hand-coin-line me-2"></i>Financing Activities</h6>
            <table class="table table-sm">
                <tbody>
                    @foreach($cashFlow['financing_activities'] ?? [] as $activity)
                    <tr>
                        <td>{{ $activity['description'] }}</td>
                        <td class="text-end {{ $activity['amount'] >= 0 ? 'positive-flow' : 'negative-flow' }}">
                            {{ $activity['amount'] >= 0 ? '+' : '' }}₦{{ number_format(abs($activity['amount']), 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>Net Cash from Financing Activities</strong></td>
                        <td class="text-end">
                            @php
                                $netFinancing = array_sum(array_column($cashFlow['financing_activities'] ?? [], 'amount'));
                            @endphp
                            <strong class="{{ $netFinancing >= 0 ? 'positive-flow' : 'negative-flow' }}">
                                {{ $netFinancing >= 0 ? '+' : '' }}₦{{ number_format(abs($netFinancing), 2) }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Total --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="border rounded-3 p-3 {{ $netCashFlow >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="fs-5">Net Cash Flow</strong>
                            <strong class="fs-3 {{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $netCashFlow >= 0 ? '+' : '' }}₦{{ number_format(abs($netCashFlow), 2) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>
@endsection
