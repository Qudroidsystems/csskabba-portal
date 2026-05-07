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
    .section-header {
        background: #f8fafc;
        padding: 10px 15px;
        font-weight: 700;
        color: #1e3a5f;
        border-bottom: 2px solid #e2e8f0;
        margin-top: 10px;
    }
    .cash-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .cash-row.total {
        border-top: 2px solid #e2e8f0;
        border-bottom: none;
        font-weight: bold;
        margin-top: 5px;
        padding-top: 12px;
    }
    .positive {
        color: #16a34a;
    }
    .negative {
        color: #dc2626;
    }
    .net-cash {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-radius: 12px;
        padding: 20px;
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
                    <h4 class="fw-bold">Cash Flow Statement</h4>
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

    <div class="report-card">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-bar-chart-line me-2"></i>Cash Flow Analysis</h5>
        </div>
        <div class="p-0">
            <!-- Operating Activities -->
            <div class="section-header">
                <i class="ri-truck-line me-2"></i>Operating Activities
            </div>
            @forelse($cashFlow['operating_activities'] ?? [] as $activity)
                <div class="cash-row">
                    <span>{{ $activity['description'] ?? 'N/A' }}</span>
                    <span class="{{ ($activity['amount'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ ($activity['amount'] ?? 0) >= 0 ? '+' : '-' }}₦{{ number_format(abs($activity['amount'] ?? 0), 2) }}
                    </span>
                </div>
            @empty
                <div class="cash-row text-muted">No operating activities recorded</div>
            @endforelse

            <div class="cash-row total">
                <span>Net Cash from Operating Activities</span>
                <span class="{{ (array_sum(array_column($cashFlow['operating_activities'] ?? [], 'amount')) ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    ₦{{ number_format(array_sum(array_column($cashFlow['operating_activities'] ?? [], 'amount')), 2) }}
                </span>
            </div>

            <!-- Investing Activities -->
            <div class="section-header">
                <i class="ri-buildings-line me-2"></i>Investing Activities
            </div>
            @forelse($cashFlow['investing_activities'] ?? [] as $activity)
                <div class="cash-row">
                    <span>{{ $activity['description'] ?? 'N/A' }}</span>
                    <span class="{{ ($activity['amount'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ ($activity['amount'] ?? 0) >= 0 ? '+' : '-' }}₦{{ number_format(abs($activity['amount'] ?? 0), 2) }}
                    </span>
                </div>
            @empty
                <div class="cash-row text-muted">No investing activities recorded</div>
            @endforelse

            <div class="cash-row total">
                <span>Net Cash from Investing Activities</span>
                <span class="{{ (array_sum(array_column($cashFlow['investing_activities'] ?? [], 'amount')) ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    ₦{{ number_format(array_sum(array_column($cashFlow['investing_activities'] ?? [], 'amount')), 2) }}
                </span>
            </div>

            <!-- Financing Activities -->
            <div class="section-header">
                <i class="ri-bank-card-line me-2"></i>Financing Activities
            </div>
            @forelse($cashFlow['financing_activities'] ?? [] as $activity)
                <div class="cash-row">
                    <span>{{ $activity['description'] ?? 'N/A' }}</span>
                    <span class="{{ ($activity['amount'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ ($activity['amount'] ?? 0) >= 0 ? '+' : '-' }}₦{{ number_format(abs($activity['amount'] ?? 0), 2) }}
                    </span>
                </div>
            @empty
                <div class="cash-row text-muted">No financing activities recorded</div>
            @endforelse

            <div class="cash-row total">
                <span>Net Cash from Financing Activities</span>
                <span class="{{ (array_sum(array_column($cashFlow['financing_activities'] ?? [], 'amount')) ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    ₦{{ number_format(array_sum(array_column($cashFlow['financing_activities'] ?? [], 'amount')), 2) }}
                </span>
            </div>

            <!-- Net Cash Flow -->
            <div class="net-cash">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Net Cash Flow</h5>
                        <p class="mb-0 text-muted small">Change in cash position for the period</p>
                    </div>
                    <div>
                        <span class="h2 fw-bold {{ $netCashFlow >= 0 ? 'positive' : 'negative' }}">
                            {{ $netCashFlow >= 0 ? '+' : '-' }}₦{{ number_format(abs($netCashFlow), 2) }}
                        </span>
                    </div>
                </div>
            </div>
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
