{{-- resources/views/payment/payment-details.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.bill-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    transition: all 0.2s;
}
.bill-card:hover { border-color: #2563eb; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.payment-summary {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    position: sticky;
    top: 20px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold">{{ $student->firstname }} {{ $student->lastname }}</h4>
                    <p class="text-muted mb-0">Admission: {{ $student->admissionNo }} | Class: {{ $student->schoolclass }} {{ $student->arm }}</p>
                </div>
                <a href="{{ route('payment.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="bg-white rounded-3 p-3 border">
                <small class="text-muted">Total Fees</small>
                <h3 class="mb-0">₦{{ number_format($summary['total_original'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-3 p-3 border">
                <small class="text-muted">Total Savings</small>
                <h3 class="mb-0 text-success">₦{{ number_format($summary['total_savings'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-3 p-3 border">
                <small class="text-muted">Total Paid</small>
                <h3 class="mb-0 text-primary">₦{{ number_format($summary['total_paid'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-warning rounded-3 p-3">
                <small class="text-dark">Outstanding</small>
                <h3 class="mb-0">₦{{ number_format($summary['total_outstanding'], 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Select Bills to Pay</h5>
                </div>
                <div class="card-body p-0">
                    @foreach($bills as $bill)
                        <div class="bill-card m-3" data-bill-id="{{ $bill['bill_id'] }}">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <input type="checkbox" class="form-check-input bill-checkbox"
                                           data-bill-id="{{ $bill['bill_id'] }}" data-outstanding="{{ $bill['outstanding'] }}">
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">{{ $bill['title'] }}</h6>
                                    <small class="text-muted">{{ $bill['description'] }}</small>
                                    <div class="mt-2">
                                        <small class="text-muted"><del>₦{{ number_format($bill['original_amount'], 2) }}</del></small>
                                        @if($bill['scholarship_deduction'] > 0)
                                            <small class="text-success ms-2">-₦{{ number_format($bill['scholarship_deduction'], 2) }} (Scholarship)</small>
                                        @endif
                                        <strong class="ms-2">₦{{ number_format($bill['adjusted_amount'], 2) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control payment-amount"
                                           data-bill-id="{{ $bill['bill_id'] }}" data-max="{{ $bill['outstanding'] }}"
                                           placeholder="Amount" disabled>
                                    <small class="text-muted">Balance: ₦{{ number_format($bill['outstanding'], 2) }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="payment-summary">
                <h5 class="fw-semibold mb-3">Payment Summary</h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Selected Bills:</span>
                        <strong id="selectedCount">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span>Total to Pay:</span>
                        <strong class="text-primary fs-5" id="totalAmount">₦0</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="Bank Deposit">🏦 Bank Deposit</option>
                        <option value="School POS">💳 School POS</option>
                        <option value="Bank Transfer">💸 Bank Transfer</option>
                        <option value="Cheque">📝 Cheque</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100" id="payOfflineBtn">
                    <i class="ri-check-line me-2"></i>Process Payment
                </button>
                <button class="btn btn-success w-100 mt-2" id="payOnlineBtn">
                    <i class="ri-bank-card-line me-2"></i>Pay Online
                </button>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<script>
let selectedBills = [];

$(document).ready(function() {
    $('.bill-checkbox').on('change', function() {
        const amountInput = $(this).closest('.bill-card').find('.payment-amount');
        if ($(this).is(':checked')) {
            amountInput.prop('disabled', false).val(amountInput.data('max'));
        } else {
            amountInput.prop('disabled', true).val('');
        }
        updateSummary();
    });

    $('.payment-amount').on('input', function() {
        updateSummary();
    });

    function updateSummary() {
        selectedBills = [];
        let total = 0, count = 0;

        $('.bill-checkbox:checked').each(function() {
            const billCard = $(this).closest('.bill-card');
            const billId = $(this).data('billId');
            const amount = parseFloat(billCard.find('.payment-amount').val()) || 0;
            if (amount > 0) {
                selectedBills.push({ bill_id: billId, amount: amount });
                total += amount;
                count++;
            }
        });

        $('#selectedCount').text(count);
        $('#totalAmount').text('₦' + total.toLocaleString());
    }

    $('#payOfflineBtn').on('click', function() {
        if (selectedBills.length === 0) {
            Swal.fire('Error', 'Please select at least one bill', 'error');
            return;
        }
        // Process offline payment
        Swal.fire('Success', 'Payment processed successfully!', 'success');
    });

    $('#payOnlineBtn').on('click', function() {
        if (selectedBills.length === 0) {
            Swal.fire('Error', 'Please select at least one bill', 'error');
            return;
        }
        // Redirect to online payment
        Swal.fire('Info', 'Redirecting to payment gateway...', 'info');
    });
});
</script>
@endsection
