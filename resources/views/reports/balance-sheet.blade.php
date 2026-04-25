{{-- resources/views/reports/balance-sheet.blade.php --}}
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
.table-summary {
    margin-bottom: 0;
}
.table-summary th, .table-summary td {
    padding: 10px 15px;
    border-bottom: 1px solid #e2e8f0;
}
.total-row {
    background-color: #f8fafc;
    font-weight: bold;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Balance Sheet</h4>
            @php
                $dateObj = is_string($asAtDate) ? \Carbon\Carbon::parse($asAtDate) : $asAtDate;
            @endphp
            <p class="text-muted">As at {{ $dateObj->format('d F, Y') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-bank-line me-2"></i>Assets</h5>
                </div>
                <div class="report-body">
                    <table class="table table-sm table-summary">
                        <thead>
                            <tr><th>Account</th><th class="text-end">Amount (₦)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                            <tr>
                                <td>{{ $asset['account_name'] ?? $asset->account_name }}</td>
                                <td class="text-end">{{ number_format($asset['balance'] ?? 0, 2) }}</td>
                            </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No asset accounts found</td></tr>
                            @endforelse
                            <tr class="total-row">
                                <th>Total Assets</th>
                                <th class="text-end">₦{{ number_format($totalAssets ?? 0, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-bank-line me-2"></i>Liabilities & Equity</h5>
                </div>
                <div class="report-body">
                    <h6 class="fw-bold mb-2">Liabilities</h6>
                    <table class="table table-sm table-summary">
                        <tbody>
                            @forelse($liabilities as $liability)
                            <tr>
                                <td>{{ $liability['account_name'] ?? $liability->account_name }}</td>
                                <td class="text-end">{{ number_format($liability['balance'] ?? 0, 2) }}</td>
                            </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No liability accounts found</td></tr>
                            @endforelse
                            <tr class="total-row">
                                <th>Total Liabilities</th>
                                <th class="text-end">₦{{ number_format($totalLiabilities ?? 0, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>

                    <h6 class="fw-bold mb-2 mt-3">Equity</h6>
                    <table class="table table-sm table-summary">
                        <tbody>
                            @forelse($equity as $eq)
                            <tr>
                                <td>{{ $eq['account_name'] ?? $eq->account_name }}</td>
                                <td class="text-end">{{ number_format($eq['balance'] ?? 0, 2) }}</td>
                            </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No equity accounts found</td></tr>
                            @endforelse
                            <tr class="total-row">
                                <th>Total Equity</th>
                                <th class="text-end">₦{{ number_format($totalEquity ?? 0, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>
@endsection
