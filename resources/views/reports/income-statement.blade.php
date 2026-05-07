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
    .income-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .income-row.total {
        border-top: 2px solid #e2e8f0;
        border-bottom: none;
        font-weight: bold;
        margin-top: 10px;
        padding-top: 15px;
    }
    .profit-row {
        background: #f0fdf4;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
    .loss-row {
        background: #fef2f2;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold">Income Statement</h4>
                    <p class="text-muted">{{ \Carbon\Carbon::parse($startDate)->format('d F, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F, Y') }}</p>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="ri-printer-line"></i> Print
                    </button>
                    <button onclick="exportToPDF()" class="btn btn-primary">
                        <i class="ri-file-pdf-line"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-money-dollar-circle-line me-2"></i>Income</h5>
                </div>
                <div class="p-3">
                    @forelse($income as $inc)
                        <div class="income-row">
                            <span>{{ $inc['account_name'] ?? 'N/A' }}</span>
                            <span class="text-success">₦{{ number_format($inc['amount'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="income-row text-muted">No income recorded</div>
                    @endforelse

                    <div class="income-row total">
                        <span class="fw-bold">Total Income</span>
                        <span class="fw-bold text-success">₦{{ number_format($totalIncome, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-expenses-line me-2"></i>Expenses</h5>
                </div>
                <div class="p-3">
                    @forelse($expenses as $exp)
                        <div class="income-row">
                            <span>{{ $exp['account_name'] ?? 'N/A' }}</span>
                            <span class="text-danger">₦{{ number_format($exp['amount'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="income-row text-muted">No expenses recorded</div>
                    @endforelse

                    <div class="income-row total">
                        <span class="fw-bold">Total Expenses</span>
                        <span class="fw-bold text-danger">₦{{ number_format($totalExpenses, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            @if($netProfit >= 0)
                <div class="profit-row">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 text-success">Net Profit</h5>
                            <p class="mb-0 text-muted small">Income exceeds expenses</p>
                        </div>
                        <div>
                            <span class="h3 text-success fw-bold">₦{{ number_format($netProfit, 2) }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="loss-row">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 text-danger">Net Loss</h5>
                            <p class="mb-0 text-muted small">Expenses exceed income</p>
                        </div>
                        <div>
                            <span class="h3 text-danger fw-bold">₦{{ number_format(abs($netProfit), 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
</div>
</div>

<script>
function exportToPDF() {
    let url = new URL(window.location.href);
    url.searchParams.set('format', 'pdf');
    window.open(url.toString(), '_blank');
}
</script>
@endsection
