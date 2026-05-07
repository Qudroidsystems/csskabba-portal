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
    .account-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .account-row.total {
        border-top: 2px solid #e2e8f0;
        border-bottom: none;
        font-weight: bold;
        margin-top: 10px;
        padding-top: 15px;
    }
    .section-title {
        font-weight: 700;
        color: #1e3a5f;
        margin: 15px 0 10px;
        padding-bottom: 5px;
        border-bottom: 2px solid #e2e8f0;
    }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold">Balance Sheet</h4>
                    <p class="text-muted">As at {{ \Carbon\Carbon::parse($asAtDate)->format('d F, Y') }}</p>
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
                    <h5 class="mb-0"><i class="ri-bank-line me-2"></i>Assets</h5>
                </div>
                <div class="p-3">
                    <div class="section-title">Current Assets</div>
                    @php $currentAssets = array_filter($assets, fn($a) => str_contains(strtolower($a['account_name'] ?? ''), 'current') || in_array(strtolower($a['account_name'] ?? ''), ['cash in hand', 'bank account', 'accounts receivable', 'prepaid expenses'])); @endphp
                    @forelse($currentAssets as $asset)
                        <div class="account-row">
                            <span>{{ $asset['account_name'] ?? 'N/A' }}</span>
                            <span>₦{{ number_format($asset['balance'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="account-row text-muted">No current assets recorded</div>
                    @endforelse

                    <div class="section-title mt-3">Fixed Assets</div>
                    @php $fixedAssets = array_filter($assets, fn($a) => str_contains(strtolower($a['account_name'] ?? ''), 'fixed') || str_contains(strtolower($a['account_name'] ?? ''), 'asset')); @endphp
                    @forelse($fixedAssets as $asset)
                        <div class="account-row">
                            <span>{{ $asset['account_name'] ?? 'N/A' }}</span>
                            <span>₦{{ number_format($asset['balance'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="account-row text-muted">No fixed assets recorded</div>
                    @endforelse

                    <div class="account-row total">
                        <span class="fw-bold">Total Assets</span>
                        <span class="fw-bold">₦{{ number_format($totalAssets, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-hand-coin-line me-2"></i>Liabilities & Equity</h5>
                </div>
                <div class="p-3">
                    <div class="section-title">Liabilities</div>
                    @forelse($liabilities as $liability)
                        <div class="account-row">
                            <span>{{ $liability['account_name'] ?? 'N/A' }}</span>
                            <span>₦{{ number_format($liability['balance'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="account-row text-muted">No liabilities recorded</div>
                    @endforelse

                    <div class="account-row total">
                        <span class="fw-bold">Total Liabilities</span>
                        <span class="fw-bold">₦{{ number_format($totalLiabilities, 2) }}</span>
                    </div>

                    <div class="section-title mt-3">Equity</div>
                    @forelse($equity as $eq)
                        <div class="account-row">
                            <span>{{ $eq['account_name'] ?? 'N/A' }}</span>
                            <span>₦{{ number_format($eq['balance'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="account-row text-muted">No equity recorded</div>
                    @endforelse

                    <div class="account-row total">
                        <span class="fw-bold">Total Equity</span>
                        <span class="fw-bold">₦{{ number_format($totalEquity, 2) }}</span>
                    </div>

                    <div class="account-row total mt-3">
                        <span class="fw-bold">Total Liabilities & Equity</span>
                        <span class="fw-bold">₦{{ number_format($totalLiabilities + $totalEquity, 2) }}</span>
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
