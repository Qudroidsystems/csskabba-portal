@extends('layouts.master')

@section('content')
<style>
.details-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
}
.details-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: white;
    padding: 20px;
}
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}
.info-label {
    font-weight: 600;
    color: #64748b;
}
.info-value {
    font-weight: 500;
    color: #1e293b;
}
.payment-table th {
    background: #f8fafc;
    padding: 12px;
    font-weight: 600;
}
.payment-table td {
    padding: 10px 12px;
    vertical-align: middle;
}
.bill-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold">Student Payment Details</h4>
                    <p class="text-muted">View detailed payment information for {{ $studentName ?? ($student->firstname ?? '') . ' ' . ($student->lastname ?? '') }}</p>
                </div>
                <div>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Information -->
    <div class="details-card">
        <div class="details-header">
            <h5 class="mb-0"><i class="ri-user-line me-2"></i>Student Information</h5>
        </div>
        <div class="p-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">Student Name:</span>
                        <span class="info-value">{{ $studentName ?? ($student->firstname ?? '') . ' ' . ($student->lastname ?? '') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Admission No:</span>
                        <span class="info-value">{{ $student->admissionNo ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">{{ $student->student_status ?? 'Active' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender:</span>
                        <span class="info-value">{{ $student->gender ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="details-card">
        <div class="details-header">
            <h5 class="mb-0"><i class="ri-wallet-line me-2"></i>Payment Summary</h5>
        </div>
        <div class="p-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-row">
                        <span class="info-label">Total Billed:</span>
                        <span class="info-value text-primary">₦{{ number_format($paymentBook->adjusted_amount ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-row">
                        <span class="info-label">Total Paid:</span>
                        <span class="info-value text-success">₦{{ number_format($paymentBook->amount_paid ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-row">
                        <span class="info-label">Outstanding:</span>
                        <span class="info-value text-danger">₦{{ number_format($paymentBook->amount_owed ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            @if(($paymentBook->scholarship_deduction ?? 0) > 0 || ($paymentBook->discount_deduction ?? 0) > 0)
            <div class="mt-3 p-3 bg-warning-subtle rounded">
                <strong><i class="ri-gift-line me-1"></i> Savings Applied:</strong>
                @if(($paymentBook->scholarship_deduction ?? 0) > 0)
                    <span class="badge bg-warning ms-2">Scholarship: ₦{{ number_format($paymentBook->scholarship_deduction, 2) }}</span>
                @endif
                @if(($paymentBook->discount_deduction ?? 0) > 0)
                    <span class="badge bg-info ms-2">Discount: ₦{{ number_format($paymentBook->discount_deduction, 2) }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Bills List -->
    <div class="details-card">
        <div class="details-header">
            <h5 class="mb-0"><i class="ri-receipt-line me-2"></i>Fee Bills</h5>
        </div>
        <div class="p-4">
            @forelse($bills as $bill)
            <div class="bill-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">{{ $bill->title }}</h6>
                        @if($bill->description)
                            <p class="text-muted small mb-0">{{ $bill->description }}</p>
                        @endif
                    </div>
                    <span class="badge bg-primary">₦{{ number_format($bill->bill_amount, 2) }}</span>
                </div>
            </div>
            @empty
            <p class="text-center text-muted py-4">No bills assigned for this term/session.</p>
            @endforelse
        </div>
    </div>

    <!-- Payment History -->
    <div class="details-card">
        <div class="details-header">
            <h5 class="mb-0"><i class="ri-history-line me-2"></i>Payment History</h5>
        </div>
        <div class="table-responsive">
            <table class="table payment-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount Paid (₦)</th>
                        <th>Balance (₦)</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentRecords as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record->payment_date)->format('d M, Y') }}</td>
                        <td class="text-success">₦{{ number_format($record->amount_paid, 2) }}</td>
                        <td>₦{{ number_format($record->balance, 2) }}</td>
                        <td>{{ $record->payment_method ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $record->status == 'Completed' ? 'success' : 'warning' }}">
                                {{ $record->status ?? 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $record->received_by ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No payment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>
@endsection
