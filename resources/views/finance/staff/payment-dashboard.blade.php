{{-- resources/views/finance/staff/payment-dashboard.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">My Payment Dashboard</h4>
            <p class="text-muted">Welcome back, {{ $staff->user->name ?? 'Staff' }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <h3 class="text-primary">₦{{ number_format($payments->sum('amount'), 2) }}</h3>
                <small class="text-muted">Total Payments Received</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <h3 class="text-success">{{ $payments->count() }}</h3>
                <small class="text-muted">Total Transactions</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <h3 class="text-warning">{{ $loanSummary['total_deduction'] ?? 0 }}</h3>
                <small class="text-muted">Active Loan Deductions</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Recent Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Reference</th><th>Amount</th><th>Date</th><th>Type</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_reference }}</td>
                            <td>₦{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>{{ ucfirst($payment->payment_type) }}</td>
                            <td><span class="badge bg-success">{{ ucfirst($payment->payment_status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>
@endsection
