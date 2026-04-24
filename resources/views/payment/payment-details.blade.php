{{-- resources/views/payment/payment-details.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent: #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-danger: #dc2626;
    --pay-border: #e2e8f0;
    --pay-radius: 12px;
}

.bill-card {
    background: white;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 20px;
    transition: all 0.2s;
    cursor: pointer;
}
.bill-card:hover {
    border-color: var(--pay-accent);
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
}
.bill-card.selected {
    border-color: var(--pay-accent);
    background: #eff6ff;
}
.progress-circle {
    width: 60px;
    height: 60px;
}
.payment-summary {
    background: white;
    border-radius: var(--pay-radius);
    border: 1px solid var(--pay-border);
    padding: 20px;
    position: sticky;
    top: 20px;
}
.payment-modal .modal-content {
    border-radius: 16px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold" style="color: var(--pay-primary);">
                        <i class="ri-user-line me-2"></i>{{ $student->firstname }} {{ $student->lastname }}
                    </h4>
                    <p class="text-muted mb-0">Admission: {{ $student->admissionNo }} | Class: {{ $student->schoolclass }} {{ $student->arm }}</p>
                </div>
                <div>
                    <a href="{{ route('payment.index') }}" class="btn btn-light me-2">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.location.reload()">
                        <i class="ri-refresh-line me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="bg-white rounded-3 p-3 border">
                <small class="text-muted d-block">Total Fees</small>
                <h3 class="mb-0">₦{{ number_format($summary['total_original'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-3 p-3 border">
                <small class="text-muted d-block">Total Savings</small>
                <h3 class="mb-0 text-success">₦{{ number_format($summary['total_savings'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-3 p-3 border">
                <small class="text-muted d-block">Total Paid</small>
                <h3 class="mb-0 text-primary">₦{{ number_format($summary['total_paid'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-warning text-dark rounded-3 p-3">
                <small class="text-dark-50 d-block">Outstanding Balance</small>
                <h3 class="mb-0">₦{{ number_format($summary['total_outstanding'], 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Bills List --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">School Bills</h5>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllBills">
                            <label class="form-check-label" for="selectAllBills">Select All</label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="billsContainer">
                        @foreach($bills as $bill)
                            <div class="bill-card m-3" data-bill-id="{{ $bill['bill_id'] }}" data-outstanding="{{ $bill['outstanding'] }}" data-adjusted="{{ $bill['adjusted_amount'] }}">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input bill-checkbox"
                                                   data-bill-id="{{ $bill['bill_id'] }}"
                                                   data-title="{{ $bill['title'] }}"
                                                   data-outstanding="{{ $bill['outstanding'] }}"
                                                   data-adjusted="{{ $bill['adjusted_amount'] }}">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $bill['title'] }}</h6>
                                                <small class="text-muted">{{ $bill['description'] }}</small>
                                                <div class="mt-2">
                                                    <small class="text-muted"><del>₦{{ number_format($bill['original_amount'], 2) }}</del></small>
                                                    @if($bill['scholarship_deduction'] > 0)
                                                        <small class="text-success ms-2">-₦{{ number_format($bill['scholarship_deduction'], 2) }} (Scholarship)</small>
                                                    @endif
                                                    @if($bill['discount_deduction'] > 0)
                                                        <small class="text-success">-₦{{ number_format($bill['discount_deduction'], 2) }} (Discount)</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-primary">₦{{ number_format($bill['adjusted_amount'], 2) }}</div>
                                                <small class="text-muted">Paid: ₦{{ number_format($bill['paid_amount'], 2) }}</small>
                                                <div class="progress mt-1" style="height: 4px; width: 100px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $bill['completion_percentage'] }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mt-md-0">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₦</span>
                                            <input type="number" class="form-control payment-amount"
                                                   data-bill-id="{{ $bill['bill_id'] }}"
                                                   data-max="{{ $bill['outstanding'] }}"
                                                   placeholder="Amount" disabled>
                                        </div>
                                        <small class="text-muted">Outstanding: ₦{{ number_format($bill['outstanding'], 2) }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Payment History Table --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="ri-history-line me-2"></i>Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Bill</th>
                                    <th>Amount Paid</th>
                                    <th>Method</th>
                                    <th>Received By</th>
                                    <th>Status</th>
                                    <th>Invoice</th>
                                </tr>
                            </thead>
                            <tbody id="paymentHistoryBody">
                                @forelse($paymentHistory as $payment)
                                    @foreach($payment->paymentRecords as $record)
                                        <tr>
                                            <td>{{ $record->created_at->format('d M, Y') }}</td>
                                            <td>{{ $payment->schoolBill->title }}</td>
                                            <td>₦{{ number_format($record->amount_paid, 2) }}</td>
                                            <td>{{ $payment->payment_method }}</td>
                                            <td>{{ $payment->generatedBy->name ?? 'System' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $record->complete_payment ? 'success' : 'warning' }}">
                                                    {{ $record->complete_payment ? 'Completed' : 'Partial' }}
                                                </span>
                                             </td>
                                            <td>
                                                @if($record->invoiceNo)
                                                    <a href="{{ route('payment.invoice', ['studentId' => $student->id, 'schoolclassid' => $classId, 'termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="ri-file-pdf-line"></i>
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary generate-invoice" data-payment-id="{{ $payment->id }}" disabled>
                                                        <i class="ri-file-copy-line"></i>
                                                    </button>
                                                @endif
                                             </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-3 text-muted">No payment history found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="col-lg-5">
            <div class="payment-summary">
                <h5 class="fw-semibold mb-3">Payment Summary</h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Selected Bills:</span>
                        <strong id="selectedCount">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Amount to Pay:</span>
                        <strong class="text-primary fs-5" id="totalAmount">₦0</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email for Receipt</label>
                    <input type="email" id="paymentEmail" class="form-control" value="{{ $student->email ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Method</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="Bank Deposit">🏦 Bank Deposit / Teller</option>
                        <option value="School POS">💳 School POS / Cash</option>
                        <option value="Bank Transfer">💸 Bank Transfer</option>
                        <option value="Cheque">📝 Cheque</option>
                    </select>
                </div>

                <div class="mb-3" id="referenceDiv" style="display: none;">
                    <label class="form-label fw-semibold">Reference Number</label>
                    <input type="text" id="referenceNo" class="form-control" placeholder="Teller/Cheque/Transaction Number">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes (Optional)</label>
                    <textarea id="paymentNotes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-lg" id="processPaymentBtn">
                        <i class="ri-check-line me-2"></i>Process Payment
                    </button>
                    <button class="btn btn-success" id="payOnlineBtn">
                        <i class="ri-bank-card-line me-2"></i>Pay Online (Card/Transfer)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

{{-- Payment Processing Modal --}}
<div class="modal fade payment-modal" id="paymentProcessingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5 class="mb-2">Processing Payment</h5>
                <p class="text-muted mb-0">Please wait while we process your payment...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const studentId = {{ $student->id }};
const classId = {{ $classId }};
const termId = {{ $termId }};
const sessionId = {{ $sessionId }};

let selectedBills = [];

document.addEventListener('DOMContentLoaded', function() {
    const billCheckboxes = document.querySelectorAll('.bill-checkbox');
    const paymentAmounts = document.querySelectorAll('.payment-amount');
    const selectAll = document.getElementById('selectAllBills');
    const selectedCountSpan = document.getElementById('selectedCount');
    const totalAmountSpan = document.getElementById('totalAmount');
    const processBtn = document.getElementById('processPaymentBtn');
    const payOnlineBtn = document.getElementById('payOnlineBtn');
    const paymentMethod = document.getElementById('paymentMethod');
    const referenceDiv = document.getElementById('referenceDiv');

    // Show/hide reference field based on payment method
    paymentMethod.addEventListener('change', function() {
        referenceDiv.style.display = (this.value === 'Bank Transfer' || this.value === 'Cheque') ? 'block' : 'none';
    });

    // Select All functionality
    selectAll.addEventListener('change', function() {
        billCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            const amountInput = document.querySelector(`.payment-amount[data-bill-id="${checkbox.dataset.billId}"]`);
            if (this.checked) {
                amountInput.disabled = false;
                amountInput.value = amountInput.dataset.max;
            } else {
                amountInput.disabled = true;
                amountInput.value = '';
            }
        });
        updateSummary();
    });

    billCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const amountInput = document.querySelector(`.payment-amount[data-bill-id="${this.dataset.billId}"]`);
            if (this.checked) {
                amountInput.disabled = false;
                amountInput.value = amountInput.dataset.max;
            } else {
                amountInput.disabled = true;
                amountInput.value = '';
            }
            updateSummary();
        });
    });

    paymentAmounts.forEach(input => {
        input.addEventListener('input', function() {
            let value = parseFloat(this.value) || 0;
            const maxValue = parseFloat(this.dataset.max);
            if (value > maxValue) {
                this.value = maxValue;
                value = maxValue;
            }
            if (value < 0) this.value = 0;
            updateSummary();
        });
    });

    function updateSummary() {
        selectedBills = [];
        let total = 0;
        let count = 0;

        billCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const amountInput = document.querySelector(`.payment-amount[data-bill-id="${checkbox.dataset.billId}"]`);
                const amount = parseFloat(amountInput.value) || 0;
                const billId = checkbox.dataset.billId;
                const title = checkbox.dataset.title;

                if (amount > 0) {
                    selectedBills.push({ bill_id: billId, title: title, amount: amount });
                    total += amount;
                    count++;
                }
            }
        });

        selectedCountSpan.textContent = count;
        totalAmountSpan.textContent = '₦' + total.toLocaleString();
    }

    // Process Offline Payment
    processBtn.addEventListener('click', async function() {
        if (selectedBills.length === 0) {
            Swal.fire('Error', 'Please select at least one bill to pay', 'error');
            return;
        }

        const email = document.getElementById('paymentEmail').value;
        if (!email) {
            Swal.fire('Error', 'Please provide email for receipt', 'error');
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('paymentProcessingModal'));
        modal.show();

        try {
            const response = await fetch('{{ route("payment.offline.process") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({
                    student_id: studentId,
                    class_id: classId,
                    term_id: termId,
                    session_id: sessionId,
                    payment_items: selectedBills,
                    payment_method: paymentMethod.value,
                    reference_no: document.getElementById('referenceNo').value,
                    notes: document.getElementById('paymentNotes').value,
                    email: email
                })
            });

            const data = await response.json();

            modal.hide();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Successful!',
                    html: `Amount Paid: <strong>₦${data.total_paid.toLocaleString()}</strong><br>Savings Applied: <strong>₦${data.total_savings.toLocaleString()}</strong>`,
                    confirmButtonText: 'View Receipt'
                }).then(() => {
                    window.location.href = data.redirect_url;
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            modal.hide();
            Swal.fire('Error', 'Payment processing failed. Please try again.', 'error');
        }
    });

    // Online Payment
    payOnlineBtn.addEventListener('click', async function() {
        if (selectedBills.length === 0) {
            Swal.fire('Error', 'Please select at least one bill to pay', 'error');
            return;
        }

        const email = document.getElementById('paymentEmail').value;
        if (!email) {
            Swal.fire('Error', 'Please provide email for receipt', 'error');
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('paymentProcessingModal'));
        modal.show();

        try {
            const response = await fetch('{{ route("payment.flexible.initialize") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({
                    student_id: studentId,
                    class_id: classId,
                    term_id: termId,
                    session_id: sessionId,
                    payment_items: selectedBills,
                    email: email
                })
            });

            const data = await response.json();
            modal.hide();

            if (data.success) {
                window.location.href = data.authorization_url;
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            modal.hide();
            Swal.fire('Error', 'Payment initialization failed', 'error');
        }
    });

    // Refresh payment history periodically
    setInterval(refreshPaymentHistory, 30000);

    async function refreshPaymentHistory() {
        try {
            const response = await fetch(`{{ route("payment.history") }}?studentId=${studentId}&termid=${termId}&sessionid=${sessionId}`);
            const data = await response.json();
            if (data.success && data.data.length > 0) {
                updatePaymentHistoryTable(data.data);
            }
        } catch (error) {
            console.error('Failed to refresh payment history:', error);
        }
    }

    function updatePaymentHistoryTable(payments) {
        const tbody = document.getElementById('paymentHistoryBody');
        if (!tbody) return;

        tbody.innerHTML = payments.map(payment => `
            <tr>
                <td>${new Date(payment.payment_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
                <td>${payment.title}</td>
                <td>₦${parseFloat(payment.amount_paid).toLocaleString()}</td>
                <td>${payment.payment_method}</td>
                <td>${payment.received_by}</td>
                <td><span class="badge bg-${payment.status === 'Completed' ? 'success' : 'warning'}">${payment.status}</span></td>
                <td>-</td>
            </tr>
        `).join('');
    }
});
</script>
@endsection
