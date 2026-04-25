{{-- resources/views/reports/trial-balance.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.report-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
.report-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: white;
    padding: 15px 20px;
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
            <h4 class="fw-bold">Trial Balance</h4>
            @php
                $asAtDateObj = is_string($asAtDate) ? \Carbon\Carbon::parse($asAtDate) : $asAtDate;
            @endphp
            <p class="text-muted">As at {{ $asAtDateObj->format('d F, Y') }}</p>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-list-check me-2"></i>Account Balances</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Account Type</th>
                        <th class="text-end">Debit (₦)</th>
                        <th class="text-end">Credit (₦)</th>
                        <th class="text-end">Balance (₦)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trialBalance as $account)
                        <tr>
                            <td>{{ $account['account_code'] }}</td>
                            <td>{{ $account['account_name'] }}</td>
                            <td>{{ ucfirst($account['account_type']) }}</td>
                            <td class="text-end">{{ number_format($account['debit'], 2) }}</td>
                            <td class="text-end">{{ number_format($account['credit'], 2) }}</td>
                            <td class="text-end {{ $account['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ₦{{ number_format($account['balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No trial balance data available</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="fw-bold">Totals</td>
                        <td class="text-end fw-bold">₦{{ number_format($totalDebit, 2) }}</td>
                        <td class="text-end fw-bold">₦{{ number_format($totalCredit, 2) }}</td>
                        <td class="text-end"></td>
                    </tr>
                    @if($totalDebit == $totalCredit)
                    <tr class="bg-success-subtle">
                        <td colspan="6" class="text-center text-success py-2">
                            <i class="ri-checkbox-circle-line me-2"></i>Trial balance is balanced!
                        </td>
                    </tr>
                    @else
                    <tr class="bg-danger-subtle">
                        <td colspan="6" class="text-center text-danger py-2">
                            <i class="ri-error-warning-line me-2"></i>Trial balance is NOT balanced! Difference: ₦{{ number_format(abs($totalDebit - $totalCredit), 2) }}
                        </td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>

</div>
</div>
</div>
@endsection
