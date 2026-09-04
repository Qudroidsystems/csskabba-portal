{{-- resources/views/schoolpayment/studentpayment.blade.php --}}
@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent:  #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-danger:  #dc2626;
    --pay-purple:  #7c3aed;
    --pay-muted:   #6b7280;
    --pay-border:  #e2e8f0;
    --pay-bg:      #f8fafc;
    --pay-radius:  12px;
    --pay-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* Loading overlay */
.loading-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.loading-overlay.active { display: flex; }
.loading-spinner {
    background: white; padding: 24px 32px; border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18); text-align: center;
}
.loading-spinner .spinner-border { width: 2.5rem; height: 2.5rem; }
.loading-spinner p { margin: 10px 0 0; font-size: 14px; font-weight: 600; color: var(--pay-primary); }

/* Hero */
.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius); padding: 24px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.pay-hero::before {
    content: ''; position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px; background: rgba(255,255,255,.06); border-radius: 50%;
}
.pay-hero h1 { font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 4px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

/* Student card */
.student-card {
    background: #fff; border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius); padding: 20px 24px;
    margin-bottom: 20px; box-shadow: var(--pay-shadow);
}
.student-avatar-lg {
    width: 72px; height: 72px; border-radius: 50%;
    object-fit: cover; border: 3px solid var(--pay-border); background: #f0f0f0;
}
.avatar-placeholder-lg {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #93c5fd);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; color: var(--pay-accent);
    border: 3px solid var(--pay-border); flex-shrink: 0;
}
.info-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--pay-bg); border: 1px solid var(--pay-border);
    border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 600;
}
.info-chip i { opacity: .7; }

/* Benefit banners */
.benefit-banner {
    border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;
    display: flex; align-items: flex-start; gap: 12px; font-size: 13px;
}
.benefit-banner.schol { background: #fef9c3; border: 1px solid #fde68a; color: #92400e; }
.benefit-banner.disc  { background: #ede9fe; border: 1px solid #ddd6fe; color: #6d28d9; }
.benefit-banner .icon { font-size: 20px; flex-shrink: 0; margin-top: 1px; }

/* Fallback warning banner */
.fallback-banner {
    background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412;
    border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;
    display: flex; align-items: flex-start; gap: 10px; font-size: 13px;
}
.fallback-banner i { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

/* Bill cards */
.bill-card {
    background: #fff; border: 1px solid var(--pay-border);
    border-radius: 12px; padding: 18px 20px; height: 100%;
    position: relative; overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.bill-card:hover { transform: translateY(-2px); box-shadow: var(--pay-shadow); }
.bill-card .stripe { position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.bill-card.paid    .stripe { background: linear-gradient(90deg, #16a34a, #15803d); }
.bill-card.partial .stripe { background: linear-gradient(90deg, #2563eb, #1d4ed8); }
.bill-card.unpaid  .stripe { background: linear-gradient(90deg, #d97706, #b45309); }
.bill-card.savings .stripe { background: linear-gradient(90deg, #7c3aed, #6d28d9); }
.bill-card.selected {
    border: 2px solid #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.15);
}

.bill-amount-main { font-size: 22px; font-weight: 700; color: var(--pay-primary); }
.bill-mini-label  { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
.bill-mini-value  { font-size: 13px; font-weight: 700; }

.schol-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fef9c3; border: 1px solid #fde68a;
    color: #92400e; border-radius: 20px; padding: 2px 9px;
    font-size: 11px; font-weight: 600;
}
.disc-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #ede9fe; border: 1px solid #ddd6fe;
    color: #6d28d9; border-radius: 20px; padding: 2px 9px;
    font-size: 11px; font-weight: 600;
}
.select-hint {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; color: var(--pay-muted);
    background: #f8fafc; border: 1px dashed var(--pay-border);
    border-radius: 6px; padding: 3px 8px; margin-top: 4px;
}

/* Progress bar */
.progress { height: 6px; border-radius: 10px; background: #e2e8f0; overflow: hidden; }
.progress-bar-paid    { background: linear-gradient(90deg, #16a34a, #15803d); border-radius: 10px; height: 6px; }
.progress-bar-partial { background: linear-gradient(90deg, #2563eb, #1d4ed8); border-radius: 10px; height: 6px; }

/* Tabs */
.nav-tabs .nav-link {
    color: var(--pay-muted); font-size: 13px; font-weight: 600;
    border: none; border-bottom: 2px solid transparent; padding: 10px 16px;
}
.nav-tabs .nav-link.active {
    color: var(--pay-accent); border-bottom-color: var(--pay-accent); background: transparent;
}

/* Record tables */
.rec-table th {
    background: var(--pay-bg); color: var(--pay-primary);
    padding: 10px 14px; font-size: 12px; font-weight: 700;
    white-space: nowrap; border-bottom: 2px solid var(--pay-border);
}
.rec-table td {
    padding: 10px 14px; vertical-align: middle;
    font-size: 13px; border-bottom: 1px solid var(--pay-border);
}
.rec-table tr:hover td { background: #f0f9ff; }

/* Empty state */
.empty-state { text-align: center; padding: 52px 24px; color: var(--pay-muted); }
.empty-state i { font-size: 3rem; opacity: .25; display: block; margin-bottom: 14px; }
.empty-state p { margin: 0; font-size: 14px; }

/* ============================================
   PAYMENT MODAL - ENHANCED CURRENCY INPUT
   ============================================ */
#paymentModal .modal-content,
#bulkPaymentModal .modal-content {
    border: none; border-radius: 16px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px; position: relative; overflow: hidden;
}
.modal-hero-bar::before {
    content: ''; position: absolute; top: -25px; right: -25px;
    width: 100px; height: 100px; background: rgba(255,255,255,.07); border-radius: 50%;
}
.modal-hero-bar h5 { color: #fff; font-weight: 700; margin: 0; font-size: 15px; position: relative; }
.modal-hero-bar .btn-close { position: absolute; top: 16px; right: 20px; filter: invert(1); }

.savings-breakdown {
    background: linear-gradient(135deg, #f3e8ff, #ede9fe);
    border: 1px solid #ddd6fe; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 14px;
}
.savings-breakdown .title { font-size: 12px; font-weight: 700; color: #7c3aed; margin-bottom: 8px; }
.savings-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
.savings-row:last-child { margin-bottom: 0; border-top: 1px solid #ddd6fe; padding-top: 6px; font-weight: 700; }

.form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-control, .form-select {
    border: 1.5px solid var(--pay-border); border-radius: 8px;
    font-size: 13px; padding: 9px 14px; transition: border .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--pay-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.form-control[readonly] { background: var(--pay-bg); cursor: default; }

/* ============================================
   CURRENCY INPUT - LIKE OPAY APP
   ============================================ */
.currency-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #fff;
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    transition: all 0.15s ease;
    overflow: hidden;
}

.currency-input-wrapper:focus-within {
    border-color: var(--pay-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.currency-input-wrapper .currency-symbol {
    position: absolute;
    left: 12px;
    font-size: 18px;
    font-weight: 700;
    color: var(--pay-primary);
    pointer-events: none;
    z-index: 1;
    font-family: 'DejaVu Sans', sans-serif;
}

.currency-input-wrapper .currency-amount {
    width: 100%;
    border: none;
    padding: 12px 14px 12px 36px;
    font-size: 24px;
    font-weight: 700;
    color: var(--pay-primary);
    background: transparent;
    outline: none;
    font-family: 'DejaVu Sans', sans-serif;
    letter-spacing: 0.5px;
}

.currency-input-wrapper .currency-amount::placeholder {
    color: #d1d5db;
    font-weight: 400;
    font-size: 18px;
    letter-spacing: normal;
}

.currency-input-wrapper .currency-amount:focus {
    box-shadow: none;
}

/* Amount display in modal */
.amount-display-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid var(--pay-border);
    font-size: 13px;
}

.amount-display-row:last-child {
    border-bottom: none;
}

.amount-display-row .label {
    color: var(--pay-muted);
}

.amount-display-row .value {
    font-weight: 700;
    color: var(--pay-primary);
}

.amount-display-row .value.text-success {
    color: var(--pay-success);
}

.amount-display-row .value.text-danger {
    color: var(--pay-danger);
}

/* Amount quick buttons */
.quick-amount-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}

.quick-amount-btn {
    padding: 4px 14px;
    border-radius: 20px;
    border: 1.5px solid var(--pay-border);
    background: #fff;
    font-size: 12px;
    font-weight: 600;
    color: var(--pay-muted);
    cursor: pointer;
    transition: all 0.15s ease;
}

.quick-amount-btn:hover {
    border-color: var(--pay-accent);
    color: var(--pay-accent);
    background: #eff6ff;
}

.quick-amount-btn:active {
    transform: scale(0.95);
}

.quick-amount-btn.full-amount {
    border-color: var(--pay-success);
    color: var(--pay-success);
}

.quick-amount-btn.full-amount:hover {
    background: #f0fdf4;
}

/* Bulk payment */
.bulk-summary {
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 12px; padding: 16px; margin-bottom: 20px;
}
.bulk-summary-item {
    display: flex; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid #dcfce7;
}
.bulk-summary-item:last-child {
    border-bottom: none; font-weight: 700; color: var(--pay-primary);
}

/* Responsive adjustments for currency input */
@media (max-width: 576px) {
    .currency-input-wrapper .currency-amount {
        font-size: 20px;
        padding: 10px 12px 10px 34px;
    }
    
    .currency-input-wrapper .currency-symbol {
        font-size: 16px;
        left: 10px;
    }
    
    .quick-amount-btn {
        font-size: 11px;
        padding: 3px 10px;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Global loading overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
            <p>Processing…</p>
        </div>
    </div>

    <div class="pay-hero">
        <h1><i class="ri-wallet-line me-2"></i>Payment Details</h1>
        <p>Manage school fee payments for the selected student, term, and session.</p>
    </div>

    <div id="paymentContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
            <p class="mt-3 text-muted">Loading payment data…</p>
        </div>
    </div>

</div>
</div>
</div>

{{-- BULK PAYMENT MODAL --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:620px">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-wallet-3-line me-2"></i>Bulk Payment</h5>
            </div>
            <div class="modal-body p-4">
                <div id="selectedBillsSummary"></div>
                <div class="bulk-summary">
                    <div class="bulk-summary-item">
                        <span>Total Payable (selected bills):</span>
                        <span class="fw-bold" id="bulkTotalPayable">₦0</span>
                    </div>
                    <div class="bulk-summary-item">
                        <span>Total Savings Applied:</span>
                        <span class="fw-bold text-success" id="bulkTotalSavings">₦0</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Payment Amount <span class="text-danger">*</span></label>
                    <div class="currency-input-wrapper">
                        <span class="currency-symbol">₦</span>
                        <input type="text" id="bulk_payment_amount" class="currency-amount"
                               placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="form-text text-muted">Amount is distributed across selected bills in order.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select id="bulk_payment_method" class="form-select">
                        <option value="">— Select Method —</option>
                        <option value="Bank Deposit">Bank Deposit / Teller</option>
                        <option value="School POS">School POS / Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>
                <div id="paymentDistribution" style="display:none">
                    <label class="form-label">Distribution Preview</label>
                    <div id="distributionList"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitBulkPayment">
                    <i class="ri-wallet-line me-1"></i>Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

{{-- INDIVIDUAL PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-wallet-line me-2"></i>Make Payment</h5>
            </div>
            <form id="paymentForm">
                @csrf
                <input type="hidden" id="student_id"            name="student_id">
                <input type="hidden" id="class_id"              name="class_id">
                <input type="hidden" id="term_id"               name="term_id">
                <input type="hidden" id="session_id"            name="session_id">
                <input type="hidden" id="school_bill_id"        name="school_bill_id">
                <input type="hidden" id="actual_amount"         name="actual_amount">
                <input type="hidden" id="adjusted_amount"       name="adjusted_amount">
                <input type="hidden" id="balance2"              name="balance2">
                <input type="hidden" id="last_amount_paid"      name="last_amount_paid">
                <input type="hidden" id="payment_amount2"       name="payment_amount2">
                <input type="hidden" id="scholarship_deduction" name="scholarship_deduction">
                <input type="hidden" id="discount_deduction"    name="discount_deduction">

                <div class="modal-body p-4">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="fw-semibold" id="modal-bill-title"
                             style="color:var(--pay-primary);font-size:15px">—</div>
                    </div>

                    <div class="savings-breakdown d-none" id="savingsBreakdown">
                        <div class="title"><i class="ri-gift-line me-1"></i>Savings Applied</div>
                        <div class="savings-row d-none" id="scholRow">
                            <span id="scholLabel">Scholarship</span>
                            <span class="fw-semibold" style="color:#92400e" id="scholAmt">-₦0</span>
                        </div>
                        <div class="savings-row d-none" id="discRow">
                            <span id="discLabel">Discount</span>
                            <span class="fw-semibold" style="color:#6d28d9" id="discAmt">-₦0</span>
                        </div>
                        <div class="savings-row">
                            <span>Total Savings</span>
                            <span style="color:#7c3aed;font-weight:700" id="totalSavingsAmt">-₦0</span>
                        </div>
                    </div>

                    {{-- Amount Display --}}
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="amount-display-row">
                            <span class="label">Bill Amount</span>
                            <span class="value" id="amount_d">₦0</span>
                        </div>
                        <div class="amount-display-row">
                            <span class="label">Amount Paid</span>
                            <span class="value text-success" id="amount_paid_d">₦0</span>
                        </div>
                        <div class="amount-display-row">
                            <span class="label">Outstanding Balance</span>
                            <span class="value text-danger" id="balance_d">₦0</span>
                        </div>
                    </div>

                    {{-- Currency Input - Like OPay --}}
                    <div class="mb-2">
                        <label class="form-label">Enter Payment Amount <span class="text-danger">*</span></label>
                        <div class="currency-input-wrapper">
                            <span class="currency-symbol">₦</span>
                            <input type="text" id="payment_amount" name="payment_amount"
                                   class="currency-amount" placeholder="0.00"
                                   autocomplete="off" inputmode="decimal">
                        </div>
                        <div class="invalid-feedback d-block" id="amountError"></div>
                    </div>

                    {{-- Quick Amount Buttons --}}
                    <div class="quick-amount-buttons" id="quickAmountButtons">
                        <button type="button" class="quick-amount-btn" data-percent="25">25%</button>
                        <button type="button" class="quick-amount-btn" data-percent="50">50%</button>
                        <button type="button" class="quick-amount-btn" data-percent="75">75%</button>
                        <button type="button" class="quick-amount-btn full-amount" data-percent="100">100%</button>
                        <button type="button" class="quick-amount-btn" data-custom="clear">Clear</button>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select id="payment_method2" name="payment_method2" class="form-select" required>
                            <option value="">— Select Method —</option>
                            <option value="Bank Deposit">Bank Deposit / Teller</option>
                            <option value="School POS">School POS / Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="paySubmitBtn">
                        <i class="ri-wallet-line me-1"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to delete this payment record?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── URL params (primary) or Blade-injected fallback ───────────────────
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('studentId') || '{{ $studentId ?? "" }}';
    const termid    = urlParams.get('termid')    || '{{ $termid ?? "" }}';
    const sessionid = urlParams.get('sessionid') || '{{ $sessionid ?? "" }}';

    // State
    let selectedBillsMap = {};
    let billsDataGlobal  = [];
    let currentDeleteUrl = '';

    // ── Currency Formatting Helpers ──────────────────────────────────────

    /**
     * Format a number as currency with commas (like OPay)
     * Examples:
     *   1234 -> "1,234"
     *   1234.56 -> "1,234.56"
     *   1000000 -> "1,000,000"
     */
    function formatCurrency(value) {
        if (value === null || value === undefined || value === '') return '';
        
        // Remove any non-numeric characters except decimal point
        let num = String(value).replace(/[^0-9.]/g, '');
        
        // Parse as float
        let floatNum = parseFloat(num);
        if (isNaN(floatNum)) return '';
        
        // Format with commas
        let parts = num.split('.');
        let wholePart = parts[0];
        let decimalPart = parts.length > 1 ? '.' + parts[1] : '';
        
        // Add commas to whole part
        wholePart = wholePart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        return wholePart + decimalPart;
    }

    /**
     * Parse formatted currency string back to a number
     * Example: "1,234.56" -> 1234.56
     */
    function parseCurrency(value) {
        if (!value) return 0;
        return parseFloat(String(value).replace(/[^0-9.]/g, '')) || 0;
    }

    /**
     * Format and display currency in an input field
     * This is the main handler for the currency input
     */
    function formatCurrencyInput(input) {
        if (!input) return;
        
        // Get cursor position before formatting
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const oldValue = input.value;
        
        // Parse the numeric value
        let rawValue = String(input.value).replace(/[^0-9.]/g, '');
        
        // Handle decimal points - only allow one
        let parts = rawValue.split('.');
        if (parts.length > 2) {
            rawValue = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Format with commas
        let formatted = formatCurrency(rawValue);
        
        // Update input value
        input.value = formatted;
        
        // Restore cursor position (adjust for added commas)
        let newCursorPos = start;
        let diff = (formatted.length - oldValue.length);
        newCursorPos = Math.max(0, Math.min(formatted.length, newCursorPos + diff));
        
        // If the user is typing, try to maintain position
        if (document.activeElement === input) {
            input.setSelectionRange(newCursorPos, newCursorPos);
        }
    }

    /**
     * Handle currency input with proper cursor management
     */
    function handleCurrencyInput(e) {
        const input = e.target;
        
        // Save cursor position
        const start = input.selectionStart;
        const rawValue = input.value;
        const numericValue = parseCurrency(rawValue);
        
        // Check if user pressed a special key
        const isSpecialKey = e.key === 'Backspace' || e.key === 'Delete' || 
                            e.key === 'ArrowLeft' || e.key === 'ArrowRight' ||
                            e.key === 'Home' || e.key === 'End' ||
                            e.key === 'Tab' || e.ctrlKey || e.metaKey;
        
        // Allow: numbers, decimal point, backspace, delete, arrow keys
        if (e.key && !isSpecialKey && !/[0-9.]/.test(e.key)) {
            e.preventDefault();
            return;
        }
        
        // Prevent multiple decimal points
        if (e.key === '.' && rawValue.includes('.')) {
            e.preventDefault();
            return;
        }
        
        // Let the browser handle the input, then format
        setTimeout(() => {
            formatCurrencyInput(input);
            
            // Trigger change event for validation
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }, 0);
    }

    /**
     * Set currency input value programmatically
     */
    function setCurrencyValue(input, value) {
        if (!input) return;
        const formatted = formatCurrency(value);
        input.value = formatted;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /**
     * Get numeric value from currency input
     */
    function getCurrencyValue(input) {
        return parseCurrency(input.value);
    }

    // ── Quick Amount Buttons ─────────────────────────────────────────────

    function setupQuickAmountButtons() {
        const buttons = document.querySelectorAll('.quick-amount-btn');
        const input = document.getElementById('payment_amount');
        const balanceEl = document.getElementById('balance_d');
        
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                const percent = parseInt(this.dataset.percent);
                const isClear = this.dataset.custom === 'clear';
                
                if (isClear) {
                    setCurrencyValue(input, '');
                    return;
                }
                
                // Get the balance from the display
                const balanceText = balanceEl.textContent.replace(/[^0-9.]/g, '');
                const balance = parseFloat(balanceText) || 0;
                
                if (balance <= 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Balance',
                        text: 'This bill has no outstanding balance.',
                        confirmButtonColor: '#2563eb',
                    });
                    return;
                }
                
                const amount = (balance * percent / 100);
                const roundedAmount = Math.round(amount * 100) / 100; // Round to 2 decimal places
                setCurrencyValue(input, roundedAmount);
                
                // Trigger validation
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    // ── Load data ─────────────────────────────────────────────────────────

    function loadPaymentData() {
        if (!studentId || !termid || !sessionid) {
            document.getElementById('paymentContent').innerHTML = `
                <div class="alert alert-warning">
                    <i class="ri-alert-line me-2"></i>Invalid parameters.
                    <a href="{{ route('schoolpayment.index') }}" class="alert-link ms-2">← Back</a>
                </div>`;
            return;
        }

        document.getElementById('paymentContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
                <p class="mt-3 text-muted">Loading payment data…</p>
            </div>`;

        const url = '{{ route("schoolpayment.getPaymentDetailsAjax") }}'
                  + '?studentId=' + studentId
                  + '&termid='    + termid
                  + '&sessionid=' + sessionid;

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(result => {
            if (result.success) {
                renderPaymentContent(result.data, result.used_fallback === true);
            } else {
                document.getElementById('paymentContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line me-2"></i>${escapeHtml(result.message || 'Failed to load data')}
                    </div>`;
            }
        })
        .catch(err => {
            console.error('Load error:', err);
            document.getElementById('paymentContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>An error occurred. Please refresh and try again.
                </div>`;
        });
    }

    // ── Render ────────────────────────────────────────────────────────────

    function renderPaymentContent(data, usedFallback) {
        const student        = data.student;
        const bills          = data.bills;
        const paymentRecords = data.payment_records;
        const paymentHistory = data.payment_history;
        const scholarship    = data.scholarship;
        const discounts      = data.discounts;
        const totals         = data.totals;

        billsDataGlobal = bills;

        // Keep only still-valid selections across reloads
        const newMap = {};
        Object.keys(selectedBillsMap).forEach(id => {
            const refreshed = bills.find(b => String(b.id) === id);
            if (refreshed && !refreshed.is_paid && !refreshed.has_pending_invoice) {
                newMap[id] = refreshed;
            }
        });
        selectedBillsMap = newMap;

        // ── Avatar ────────────────────────────────────────────────────────
        const initials = (student.name || '??')
            .split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        let avatarHtml;
        const avatarUrl = getAvatarUrl(student.avatar);
        if (avatarUrl) {
            avatarHtml = `
                <img src="${escapeHtml(avatarUrl)}"
                     alt="${escapeHtml(student.name)}"
                     class="student-avatar-lg"
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                <div class="avatar-placeholder-lg" style="display:none">${escapeHtml(initials)}</div>`;
        } else {
            avatarHtml = `<div class="avatar-placeholder-lg">${escapeHtml(initials)}</div>`;
        }

        // ── Bill cards ────────────────────────────────────────────────────
        const billsHtml = bills.map(bill => {
            const billKey    = String(bill.id);
            const isSelected = !!selectedBillsMap[billKey];
            const cardClass  = bill.is_paid     ? 'paid'
                             : bill.is_partial   ? 'partial'
                             : bill.total_savings > 0 ? 'savings'
                             : 'unpaid';

            return `
            <div class="col-xl-4 col-lg-6">
                <div class="bill-card ${cardClass}${isSelected ? ' selected' : ''}" data-bill-id="${bill.id}">
                    <div class="stripe"></div>
                    <div class="d-flex align-items-start justify-content-between mb-2 mt-1">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size:14px;color:var(--pay-primary)">
                                ${escapeHtml(bill.title)}
                            </div>
                            ${bill.description ? `<div class="text-muted" style="font-size:11px">${escapeHtml(bill.description)}</div>` : ''}
                        </div>
                        <div class="ms-2 flex-shrink-0">
                            <input type="checkbox" class="bill-select-checkbox"
                                   data-bill-id="${bill.id}"
                                   ${bill.is_paid || bill.has_pending_invoice ? 'disabled' : ''}
                                   ${isSelected ? 'checked' : ''}
                                   style="transform:scale(1.3);cursor:pointer;">
                        </div>
                    </div>
                    ${bill.total_savings > 0 ? `
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        ${bill.scholarship_deduction > 0 ? `<span class="schol-pill"><i class="ri-award-line"></i> -₦${fmt(bill.scholarship_deduction)} Scholarship</span>` : ''}
                        ${bill.discount_deduction    > 0 ? `<span class="disc-pill"><i class="ri-price-tag-3-line"></i> -₦${fmt(bill.discount_deduction)} Discount</span>` : ''}
                    </div>` : ''}
                    <div class="text-center mb-2">
                        ${bill.total_savings > 0
                            ? `<div class="text-muted text-decoration-line-through" style="font-size:12px">₦${fmt(bill.original_amount)}</div>`
                            : ''}
                        <div class="bill-amount-main">₦${fmt(bill.adjusted_amount)}</div>
                        <div style="font-size:11px;color:var(--pay-muted)">${bill.total_savings > 0 ? 'After savings' : 'Payable amount'}</div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6 text-center">
                            <div class="bill-mini-label">Paid</div>
                            <div class="bill-mini-value text-success">₦${fmt(bill.amount_paid)}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="bill-mini-label">Balance</div>
                            <div class="bill-mini-value ${bill.balance > 0 ? 'text-danger' : 'text-success'}">₦${fmt(bill.balance)}</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size:10px;color:var(--pay-muted)">
                            <span>Progress</span>
                            <span class="fw-semibold ${bill.is_paid ? 'text-success' : 'text-primary'}">${Math.round(bill.progress)}%</span>
                        </div>
                        <div class="progress">
                            <div class="${bill.is_paid ? 'progress-bar-paid' : 'progress-bar-partial'}" style="width:${bill.progress}%"></div>
                        </div>
                    </div>
                    ${!bill.is_paid
                        ? (bill.has_pending_invoice
                            ? `<button class="btn btn-secondary btn-sm w-100" disabled>
                                   <i class="ri-lock-line me-1"></i>Invoice Pending
                               </button>`
                            : `<button class="btn btn-primary btn-sm w-100 make-payment-btn"
                                       data-bill-id="${bill.id}">
                                   <i class="ri-wallet-line me-1"></i>Make Payment
                               </button>
                               <div class="select-hint mt-2"><i class="ri-checkbox-line"></i>Tick to include in bulk payment</div>`)
                        : `<button class="btn btn-success btn-sm w-100" disabled>
                               <i class="ri-checkbox-circle-line me-1"></i>Fully Paid
                           </button>`
                    }
                </div>
            </div>`;
        }).join('');

        // ── Payment Records table ─────────────────────────────────────────
        const paymentRecordsHtml = paymentRecords.length > 0
            ? `<div class="table-responsive">
                <table class="table rec-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Bill</th><th>Bill Amt</th><th>Paid</th>
                            <th>Balance</th><th>Method</th><th>Received By</th>
                            <th>Date</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    ${paymentRecords.map((sp, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td><div class="fw-semibold">${escapeHtml(sp.title)}</div>${sp.description ? `<div class="text-muted small">${escapeHtml(sp.description)}</div>` : ''}</td>
                            <td>₦${fmt(sp.billAmount)}</td>
                            <td class="text-success fw-semibold">₦${fmt(sp.totalAmountPaid)}</td>
                            <td class="${sp.balance > 0 ? 'text-danger' : 'text-success'} fw-semibold">₦${fmt(sp.balance)}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">${escapeHtml(sp.paymentMethod || '—')}</span></td>
                            <td class="text-muted small">${escapeHtml(sp.receivedBy || '—')}</td>
                            <td class="text-muted small">${sp.receivedDate ? new Date(sp.receivedDate).toLocaleDateString('en-GB') : 'N/A'}</td>
                            <td><span class="badge ${sp.paymentStatus === 'Completed' ? 'bg-success' : 'bg-warning text-dark'}">${escapeHtml(sp.paymentStatus || 'Pending')}</span></td>
                            <td>${sp.recordId
                                ? `<button class="btn btn-sm btn-danger delete-payment" data-record-id="${sp.recordId}">
                                       <i class="ri-delete-bin-line"></i>
                                   </button>`
                                : '<span class="text-muted small">—</span>'}
                            </td>
                        </tr>`).join('')}
                    </tbody>
                </table>
               </div>`
            : '<div class="empty-state"><i class="ri-receipt-line"></i><p>No pending payment records.</p></div>';

        // ── Payment History table ─────────────────────────────────────────
        const historyHtml = paymentHistory.length > 0
            ? `<div class="table-responsive">
                <table class="table rec-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Bill</th><th>Bill Amt</th><th>Paid</th>
                            <th>Balance</th><th>Method</th><th>Received By</th>
                            <th>Date</th><th>Status</th><th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                    ${paymentHistory.map((ph, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td><div class="fw-semibold">${escapeHtml(ph.title)}</div>${ph.description ? `<div class="text-muted small">${escapeHtml(ph.description)}</div>` : ''}</td>
                            <td>₦${fmt(ph.billAmount)}</td>
                            <td class="text-success fw-semibold">₦${fmt(ph.totalAmountPaid)}</td>
                            <td class="${ph.balance > 0 ? 'text-danger' : 'text-success'} fw-semibold">₦${fmt(ph.balance)}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">${escapeHtml(ph.paymentMethod || '—')}</span></td>
                            <td class="text-muted small">${escapeHtml(ph.receivedBy || '—')}</td>
                            <td class="text-muted small">${ph.receivedDate ? new Date(ph.receivedDate).toLocaleDateString('en-GB') : 'N/A'}</td>
                            <td><span class="badge ${(ph.paymentStatus === 'Completed' || ph.completePayment) ? 'bg-success' : 'bg-warning text-dark'}">${(ph.paymentStatus === 'Completed' || ph.completePayment) ? 'Completed' : 'Partial'}</span></td>
                            <td>
                                <a href="{{ url('schoolpayment/invoice') }}/${studentId}/${ph.classId || student.schoolclassId || ''}/${ph.termId || termid}/${ph.sessionId || sessionid}"
                                   class="btn btn-sm btn-outline-primary" title="View Invoice">
                                    <i class="ri-file-download-line"></i>
                                </a>
                            </td>
                        </tr>`).join('')}
                    </tbody>
                </table>
               </div>`
            : '<div class="empty-state"><i class="ri-history-line"></i><p>No payment history found.</p></div>';

        const totalOutstanding = totals.outstanding;
        const totalPaid = totals.paid;
        const selectedCount    = Object.keys(selectedBillsMap).length;
        const hasBulkableBills = bills.some(b => !b.is_paid && !b.has_pending_invoice);

        // Fallback warning banner
        const fallbackBanner = usedFallback ? `
            <div class="fallback-banner">
                <i class="ri-information-line"></i>
                <div>
                    <strong>Note:</strong> This student was not enrolled in the selected term/session.
                    Data shown is from their current active enrollment.
                </div>
            </div>` : '';

        const contentHtml = `
            ${fallbackBanner}

            <div class="student-card">
                <div class="d-flex align-items-start gap-4 flex-wrap">
                    <div class="flex-shrink-0">${avatarHtml}</div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="mb-0 fw-bold" style="color:var(--pay-primary)">${escapeHtml(student.name)}</h5>
                            <span class="badge ${student.student_status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} px-2 py-1" style="font-size:11px">
                                ${escapeHtml(student.student_status || 'Unknown')}
                            </span>
                            <span class="badge ${student.statusId == 1 ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning'} px-2 py-1" style="font-size:11px">
                                ${student.statusId == 1 ? 'Returning Student' : 'New Student'}
                            </span>
                        </div>
                        <div class="text-muted small font-monospace mb-3">${escapeHtml(student.admissionNo)}</div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="info-chip"><i class="ri-building-line text-success"></i>${escapeHtml(student.schoolclass)} ${escapeHtml(student.arm)}</div>
                            <div class="info-chip"><i class="ri-calendar-line text-primary"></i>${escapeHtml(data.term || '')}</div>
                            <div class="info-chip"><i class="ri-time-line text-warning"></i>${escapeHtml(data.session || '')}</div>
                            <div class="info-chip"><i class="ri-money-dollar-circle-line text-danger"></i>Total: ₦${fmt(totals.adjusted)}</div>
                            <div class="info-chip"><i class="ri-check-line text-success"></i>Paid: ₦${fmt(totals.paid)}</div>
                            ${bills.length === 0
                                ? `<div class="info-chip" style="background:#fef2f2;border-color:#fecaca;color:#dc2626"><i class="ri-alert-line"></i>No Bills Assigned</div>`
                                : totalOutstanding > 0
                                    ? `<div class="info-chip" style="background:#fef2f2;border-color:#fecaca;color:#dc2626"><i class="ri-alert-line"></i>Outstanding: ₦${fmt(totalOutstanding)}</div>`
                                    : totalPaid > 0
                                        ? `<div class="info-chip" style="background:#f0fdf4;border-color:#bbf7d0;color:#16a34a"><i class="ri-checkbox-circle-line"></i>Fully Paid</div>`
                                        : `<div class="info-chip" style="background:#fef2f2;border-color:#fecaca;color:#dc2626"><i class="ri-alert-line"></i>No Payments Made</div>`}
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-start">
                        <a href="{{ route('schoolpayment.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Back
                        </a>
                        ${paymentRecords.length > 0
                            ? `<a href="{{ url('schoolpayment/invoice') }}/${studentId}/${student.schoolclassId || ''}/${termid}/${sessionid}"
                                   class="btn btn-primary btn-sm">
                                   <i class="ri-file-download-line me-1"></i>Generate Invoice
                               </a>`
                            : `<button class="btn btn-primary btn-sm" disabled title="Make a payment first">
                                   <i class="ri-file-download-line me-1"></i>Generate Invoice
                               </button>`}
                        <a href="{{ url('schoolpayment/statement') }}/${studentId}/${student.schoolclassId || ''}/${termid}/${sessionid}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="ri-file-list-line me-1"></i>Statement
                        </a>
                        <button class="btn btn-success btn-sm" id="bulkPaymentBtn"
                                ${!hasBulkableBills ? 'disabled' : ''}>
                            <i class="ri-wallet-3-line me-1"></i>Bulk Payment
                            <span class="badge bg-white text-success ms-1" id="selectedCount">${selectedCount}</span>
                        </button>
                    </div>
                </div>
            </div>

            ${scholarship ? `
            <div class="benefit-banner schol">
                <i class="ri-award-line icon"></i>
                <div>
                    <div class="fw-semibold mb-1">Scholarship Active: ${escapeHtml(scholarship.title)}</div>
                    <div class="small">
                        ${scholarship.value_type === 'percentage'
                            ? `${scholarship.value}% deduction.`
                            : `₦${fmt(scholarship.value)} fixed deduction per bill.`}
                        ${scholarship.effective_to ? ` Valid until ${new Date(scholarship.effective_to).toLocaleDateString('en-GB')}.` : ''}
                        <strong class="ms-2">Total Savings: ₦${fmt(totals.savings)}</strong>
                    </div>
                </div>
            </div>` : ''}

            ${discounts.length > 0 ? `
            <div class="benefit-banner disc">
                <i class="ri-price-tag-3-line icon"></i>
                <div>
                    <div class="fw-semibold mb-1">Discount(s) Active</div>
                    <div class="small">
                        ${discounts.map(d =>
                            `<span class="me-3"><strong>${escapeHtml(d.title)}:</strong>
                             ${d.value_type === 'percentage' ? `${d.value}% off` : `₦${fmt(d.value)} off`}</span>`
                        ).join('')}
                    </div>
                </div>
            </div>` : ''}

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pt-3 pb-0">
                    <ul class="nav nav-tabs border-0" id="payTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-bills">
                                <i class="ri-bill-line me-1"></i>School Bills
                                <span class="badge ${bills.length === 0 ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary'} ms-1">${bills.length}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-records">
                                <i class="ri-receipt-line me-1"></i>Payment Records
                                ${paymentRecords.length ? `<span class="badge bg-success-subtle text-success ms-1">${paymentRecords.length}</span>` : ''}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-history">
                                <i class="ri-history-line me-1"></i>History
                                ${paymentHistory.length ? `<span class="badge bg-info-subtle text-info ms-1">${paymentHistory.length}</span>` : ''}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-bills">
                            ${bills.length > 0
                                ? `<div class="row g-3 mt-1">${billsHtml}</div>
                                   ${bills.length === 0 ? '' : totals.savings > 0 ? `
                                   <div class="mt-4 p-3 rounded-3" style="background:linear-gradient(135deg,#f3e8ff,#ede9fe);border:1px solid #ddd6fe">
                                       <div class="d-flex align-items-center gap-2">
                                           <i class="ri-gift-line" style="font-size:18px;color:#7c3aed"></i>
                                           <div>
                                               <span class="fw-semibold" style="color:#7c3aed">Total Savings Applied: </span>
                                               <span class="fw-bold" style="color:#7c3aed">₦${fmt(totals.savings)}</span>
                                               <span class="text-muted small ms-2">(Original: ₦${fmt(totals.original)} → Payable: ₦${fmt(totals.adjusted)})</span>
                                           </div>
                                       </div>
                                   </div>` : ''}`
                                : `<div class="empty-state">
                                    <i class="ri-inbox-line"></i>
                                    <p>No bills assigned to this student for the selected term/session.</p>
                                    <div class="alert alert-info mt-3 text-start" style="font-size:12px">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Possible reasons:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>No fee structure has been configured for this class (${escapeHtml(student.schoolclass)} ${escapeHtml(student.arm)})</li>
                                            <li>The selected term (${escapeHtml(data.term || '')}) or session (${escapeHtml(data.session || '')}) has no associated bills</li>
                                            <li>The student's enrollment in this class may not have bills assigned</li>
                                        </ul>
                                        <hr class="my-2">
                                        <p class="mb-0 small">Please contact the school administrator to configure fee structures for this class, term, and session.</p>
                                    </div>
                                </div>`}
                        </div>
                        <div class="tab-pane fade" id="tab-records">${paymentRecordsHtml}</div>
                        <div class="tab-pane fade" id="tab-history">${historyHtml}</div>
                    </div>
                </div>
            </div>`;

        document.getElementById('paymentContent').innerHTML = contentHtml;

        attachBillSelectionEvents(bills);
        attachPaymentButtons(bills);
        attachDeleteHandlers();
        
        // Setup currency input handlers
        setupCurrencyInputHandlers();
        setupQuickAmountButtons();

        const bulkBtn = document.getElementById('bulkPaymentBtn');
        if (bulkBtn) bulkBtn.addEventListener('click', () => openBulkPaymentModal());
    }

    // ── Currency Input Handlers ──────────────────────────────────────────

    function setupCurrencyInputHandlers() {
        // Individual payment amount input
        const paymentInput = document.getElementById('payment_amount');
        if (paymentInput) {
            // Handle input events for real-time formatting
            paymentInput.addEventListener('input', handleCurrencyInput);
            
            // Handle focus - select all text for easy typing
            paymentInput.addEventListener('focus', function() {
                this.select();
            });
            
            // Handle blur - ensure proper formatting
            paymentInput.addEventListener('blur', function() {
                formatCurrencyInput(this);
            });
            
            // Handle keydown for special keys
            paymentInput.addEventListener('keydown', function(e) {
                // Allow: backspace, delete, tab, escape, enter, arrow keys, home, end
                if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' ||
                    e.key === 'Escape' || e.key === 'Enter' || e.key === 'Home' ||
                    e.key === 'End' || e.key.startsWith('Arrow')) {
                    return;
                }
                
                // Allow: numbers, decimal point
                if (!/^[0-9.]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }
        
        // Bulk payment amount input
        const bulkInput = document.getElementById('bulk_payment_amount');
        if (bulkInput) {
            bulkInput.addEventListener('input', handleCurrencyInput);
            bulkInput.addEventListener('focus', function() { this.select(); });
            bulkInput.addEventListener('blur', function() { formatCurrencyInput(this); });
        }
    }

    // ── Bill checkbox selection ───────────────────────────────────────────

    function attachBillSelectionEvents(bills) {
        document.querySelectorAll('.bill-select-checkbox').forEach(cb => {
            cb.addEventListener('change', function () {
                const billKey = String(this.dataset.billId);
                const bill    = bills.find(b => String(b.id) === billKey);

                if (this.checked && bill) {
                    selectedBillsMap[billKey] = bill;
                } else {
                    delete selectedBillsMap[billKey];
                }

                const card = document.querySelector(`.bill-card[data-bill-id="${billKey}"]`);
                if (card) card.classList.toggle('selected', !!selectedBillsMap[billKey]);

                const badge = document.getElementById('selectedCount');
                if (badge) badge.textContent = Object.keys(selectedBillsMap).length;
            });
        });
    }

    // ── Individual payment modal ──────────────────────────────────────────

    function attachPaymentButtons(bills) {
        document.querySelectorAll('.make-payment-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const billKey = String(this.dataset.billId);
                const bill = bills.find(b => String(b.id) === billKey);
                if (bill) {
                    openPaymentModal(bill);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Could not find bill data. Please refresh.' });
                }
            });
        });
    }

    function openPaymentModal(bill) {
        document.getElementById('modal-bill-title').textContent        = bill.title;
        document.getElementById('student_id').value                    = studentId;
        document.getElementById('class_id').value                      = bill.class_id || '';
        document.getElementById('term_id').value                       = termid;
        document.getElementById('session_id').value                    = sessionid;
        document.getElementById('school_bill_id').value                = bill.id;
        document.getElementById('actual_amount').value                 = bill.original_amount;
        document.getElementById('adjusted_amount').value               = bill.adjusted_amount;
        document.getElementById('balance2').value                      = bill.balance;
        document.getElementById('last_amount_paid').value              = bill.amount_paid;
        document.getElementById('scholarship_deduction').value         = bill.scholarship_deduction || 0;
        document.getElementById('discount_deduction').value            = bill.discount_deduction || 0;
        
        // Display amounts with proper formatting
        document.getElementById('amount_d').textContent                = '₦' + fmt(bill.adjusted_amount);
        document.getElementById('amount_paid_d').textContent           = '₦' + fmt(bill.amount_paid);
        document.getElementById('balance_d').textContent               = '₦' + fmt(bill.balance);
        
        // Clear currency input
        const paymentInput = document.getElementById('payment_amount');
        setCurrencyValue(paymentInput, '');
        document.getElementById('payment_method2').value               = '';
        document.getElementById('amountError').textContent             = '';

        const savBox = document.getElementById('savingsBreakdown');
        if (bill.total_savings > 0) {
            savBox.classList.remove('d-none');
            const scholRow = document.getElementById('scholRow');
            const discRow  = document.getElementById('discRow');
            if (bill.scholarship_deduction > 0) {
                scholRow.classList.remove('d-none');
                document.getElementById('scholLabel').textContent = bill.scholarship_label || 'Scholarship';
                document.getElementById('scholAmt').textContent   = '-₦' + fmt(bill.scholarship_deduction);
            } else { scholRow.classList.add('d-none'); }
            if (bill.discount_deduction > 0) {
                discRow.classList.remove('d-none');
                document.getElementById('discLabel').textContent = bill.discount_labels || 'Discount';
                document.getElementById('discAmt').textContent   = '-₦' + fmt(bill.discount_deduction);
            } else { discRow.classList.add('d-none'); }
            document.getElementById('totalSavingsAmt').textContent = '-₦' + fmt(bill.total_savings);
        } else {
            savBox.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('paymentModal')).show();
        
        // Focus the currency input after modal opens
        setTimeout(() => {
            paymentInput.focus();
        }, 300);
    }

    // ── Bulk payment modal ────────────────────────────────────────────────

    function openBulkPaymentModal() {
        const selectedBills = Object.values(selectedBillsMap);

        if (selectedBills.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Bills Selected',
                html: `<p>Please tick the checkbox on one or more bills before clicking <strong>Bulk Payment</strong>.</p>`,
                confirmButtonColor: '#2563eb',
            });
            return;
        }

        const totalPayable = selectedBills.reduce((s, b) => s + parseFloat(b.balance   || 0), 0);
        const totalSavings = selectedBills.reduce((s, b) => s + parseFloat(b.total_savings || 0), 0);

        document.getElementById('selectedBillsSummary').innerHTML = `
            <div class="mb-3">
                <label class="form-label">Selected Bills (${selectedBills.length})</label>
                <div class="list-group">
                    ${selectedBills.map(b => `
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div class="fw-semibold">${escapeHtml(b.title)}</div>
                            <div class="text-end">
                                <small class="text-muted d-block">Balance: ₦${fmt(b.balance)}</small>
                                ${b.total_savings > 0 ? `<small class="text-success">Saved: ₦${fmt(b.total_savings)}</small>` : ''}
                            </div>
                        </div>`).join('')}
                </div>
            </div>`;

        document.getElementById('bulkTotalPayable').textContent      = '₦' + fmt(totalPayable);
        document.getElementById('bulkTotalSavings').textContent      = '₦' + fmt(totalSavings);
        
        // Clear bulk currency input
        const bulkInput = document.getElementById('bulk_payment_amount');
        setCurrencyValue(bulkInput, '');
        document.getElementById('bulk_payment_method').value         = '';
        document.getElementById('paymentDistribution').style.display = 'none';

        new bootstrap.Modal(document.getElementById('bulkPaymentModal')).show();
        
        // Focus the currency input after modal opens
        setTimeout(() => {
            bulkInput.focus();
        }, 300);

        // Distribution preview
        bulkInput.oninput = function () {
            const remaining0 = getCurrencyValue(this);
            let remaining = remaining0;
            const dist = [];
            for (const bill of selectedBills) {
                if (remaining <= 0) break;
                const bal = parseFloat(bill.balance || 0);
                if (bal <= 0) continue;
                const pay = Math.min(remaining, bal);
                dist.push({ title: bill.title, amount: pay, newBalance: bal - pay });
                remaining -= pay;
            }
            const distDiv = document.getElementById('paymentDistribution');
            if (dist.length > 0 && remaining0 > 0) {
                document.getElementById('distributionList').innerHTML = dist.map(d => `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="fw-semibold">${escapeHtml(d.title)}</span>
                        <div class="text-end">
                            <span class="fw-semibold text-success">₦${fmt(d.amount)}</span>
                            <small class="text-muted d-block">New Balance: ₦${fmt(d.newBalance)}</small>
                        </div>
                    </div>`).join('');
                distDiv.style.display = 'block';
            } else {
                distDiv.style.display = 'none';
            }
        };

        const submitBtn = document.getElementById('submitBulkPayment');
        submitBtn.onclick = null;
        submitBtn.onclick = () => submitBulkPayment(selectedBills);
    }

    // ── Submit bulk payment ───────────────────────────────────────────────

    function submitBulkPayment(selectedBills) {
        const paymentAmount = getCurrencyValue(document.getElementById('bulk_payment_amount'));
        const paymentMethod = document.getElementById('bulk_payment_method').value;
        const totalPayable  = selectedBills.reduce((s, b) => s + parseFloat(b.balance || 0), 0);

        if (paymentAmount <= 0)
            return Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Please enter a valid payment amount.', confirmButtonColor: '#2563eb' });
        if (paymentAmount > totalPayable + 0.01)
            return Swal.fire({ icon: 'warning', title: 'Exceeds Balance', text: `Total outstanding is ₦${fmt(totalPayable)}.`, confirmButtonColor: '#2563eb' });
        if (!paymentMethod)
            return Swal.fire({ icon: 'warning', title: 'No Method', text: 'Please select a payment method.', confirmButtonColor: '#2563eb' });

        showLoading(true);

        const classId = selectedBills[0]?.class_id || 0;

        fetch('{{ route("schoolpayment.bulk-store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                student_id:     studentId,
                class_id:       classId,
                term_id:        termid,
                session_id:     sessionid,
                payment_amount: paymentAmount,
                payment_method: paymentMethod,
                bill_payments:  selectedBills.map(b => ({
                    school_bill_id:        b.id,
                    title:                 b.title,
                    adjusted_amount:       b.adjusted_amount,
                    balance:               b.balance,
                    scholarship_deduction: b.scholarship_deduction || 0,
                    discount_deduction:    b.discount_deduction    || 0,
                })),
            }),
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: result.message, timer: 2000, showConfirmButton: false })
                    .then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('bulkPaymentModal'))?.hide();
                        selectedBillsMap = {};
                        loadPaymentData();
                    });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while processing the payment.' }))
        .finally(() => showLoading(false));
    }

    // ── Delete handlers ───────────────────────────────────────────────────

    function attachDeleteHandlers() {
        document.querySelectorAll('.delete-payment').forEach(btn => {
            btn.addEventListener('click', function () {
                const recordId = this.dataset.recordId;
                if (recordId) {
                    currentDeleteUrl = '/schoolpayment/delete/' + recordId;
                    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
                }
            });
        });
    }

    // ── Individual payment form ───────────────────────────────────────────

    document.getElementById('paymentForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const amount  = getCurrencyValue(document.getElementById('payment_amount'));
        const balance = parseFloat(document.getElementById('balance2').value) || 0;
        const method  = document.getElementById('payment_method2').value;

        if (amount <= 0)
            return Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Enter a valid amount.', confirmButtonColor: '#2563eb' });
        if (amount > balance + 0.01)
            return Swal.fire({ icon: 'warning', title: 'Exceeds Balance', text: `Balance is ₦${fmt(balance)}`, confirmButtonColor: '#2563eb' });
        if (!method)
            return Swal.fire({ icon: 'warning', title: 'No Method', text: 'Select a payment method.', confirmButtonColor: '#2563eb' });

        document.getElementById('payment_amount2').value = amount.toFixed(2);

        const btn = document.getElementById('paySubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

        fetch('{{ route("schoolpayment.store") }}', {
            method:  'POST',
            body:    new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
                Swal.fire({ icon: 'success', title: 'Recorded!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => loadPaymentData());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Payment failed.' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred.' }))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-wallet-line me-1"></i>Record Payment';
        });
    });

    // ── Delete confirm ────────────────────────────────────────────────────

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'))?.hide();

        const self = this;
        self.disabled = true;
        self.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting…';

        fetch(currentDeleteUrl, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                'Accept':        'application/json',
                'Content-Type':  'application/json',
            },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 1500, showConfirmButton: false })
                    .then(() => loadPaymentData());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Failed to delete. Please try again.', 'error'))
        .finally(() => {
            self.disabled = false;
            self.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Delete';
            currentDeleteUrl = '';
        });
    });

    // ── Helpers ───────────────────────────────────────────────────────────

    function fmt(n) {
        return Number(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 0 });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (m) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[m];
        });
    }

    function showLoading(show) {
        document.getElementById('loadingOverlay').classList.toggle('active', !!show);
    }

    function getAvatarUrl(picture) {
        if (!picture || picture === 'unnamed.jpg' || picture === '') return null;
        return '/storage/images/student_avatars/' + picture.replace(/^\/+/, '');
    }

    // ── Boot ──────────────────────────────────────────────────────────────
    loadPaymentData();
});
</script>
@endsection