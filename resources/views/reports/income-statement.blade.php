{{-- resources/views/reports/income-statement.blade.php --}}
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
.profit-row {
    background-color: #dcfce7;
    font-weight: bold;
}
.loss-row {
    background-color: #fee2e2;
    font-weight: bold;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Income Statement</h4>
            @php
                $startDateObj = is_string($startDate) ? \Carbon\Carbon::parse($startDate) : $startDate;
                $endDateObj = is_string($endDate) ? \Carbon\Carbon::parse($endDate) : $endDate;
            @endphp
            <p class="text-muted">For the period {{ $startDateObj->format('d F, Y') }} - {{ $endDateObj->format('d F, Y') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header bg-success">
                    <h5 class="mb-0"><i class="ri-income-line me-2"></i>Income (Revenue)</h5>
                </div>
                <div class="report-body">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Account</th><th class="text-end">Amount (₦)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($income as $inc)
                            <tr>
                                <td>{{ $inc['account_name'] ?? $inc->account_name }}</td>
                                <td class="text-end">{{ number_format($inc['amount'] ?? 0, 2) }}</td>
                            </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No income accounts found</td></tr>
                            @endforelse
                            <tr class="total-row">
                                <th>Total Income</th>
                                <th class="text-end">₦{{ number_format($totalIncome ?? 0, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header bg-danger">
                    <h5 class="mb-0"><i class="ri-expenses-line me-2"></i>Expenses</h5>
                </div>
                <div class="report-body">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Account</th><th class="text-end">Amount (₦)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $exp)
                            <tr>
                                <td>{{ $exp['account_name'] ?? $exp->account_name }}</td>
                                <td class="text-end">{{ number_format($exp['amount'] ?? 0, 2) }}</td>
                            </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No expense accounts found</td></tr>
                            @endforelse
                            <tr class="total-row">
                                <th>Total Expenses</th>
                                <th class="text-end">₦{{ number_format($totalExpenses ?? 0, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="report-card">
                <div class="report-header bg-primary">
                    <h5 class="mb-0"><i class="ri-bar-chart-line me-2"></i>Summary</h5>
                </div>
                <div class="report-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 text-center">
                                <small class="text-muted">Total Income</small>
                                <h4 class="text-success mb-0">₦{{ number_format($totalIncome ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 text-center">
                                <small class="text-muted">Total Expenses</small>
                                <h4 class="text-danger mb-0">₦{{ number_format($totalExpenses ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 text-center {{ ($netProfit ?? 0) >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                <small class="text-muted">Net Profit / (Loss)</small>
                                <h4 class="{{ ($netProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    ₦{{ number_format($netProfit ?? 0, 2) }}
                                </h4>
                            </div>
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
