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
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.loading-overlay.active {
    display: flex;
}
.loading-spinner {
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

/* Hero */
.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 24px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.pay-hero::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pay-hero h1 { font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 4px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

/* Student card */
.student-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 20px 24px;
    margin-bottom: 20px;
    box-shadow: var(--pay-shadow);
}
.student-avatar-lg {
    width: 72px; height: 72px;
    border-radius: 50%; object-fit: cover;
    border: 3px solid var(--pay-border);
}
.avatar-placeholder-lg {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #93c5fd);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; color: var(--pay-accent);
    border: 3px solid var(--pay-border);
    flex-shrink: 0;
}
.info-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--pay-bg);
    border: 1px solid var(--pay-border);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px; font-weight: 600;
}
.info-chip i { opacity: .7; }

/* Benefit banners */
.benefit-banner {
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex; align-items: flex-start; gap: 12px;
    font-size: 13px;
}
.benefit-banner.schol { background: #fef9c3; border: 1px solid #fde68a; color: #92400e; }
.benefit-banner.disc  { background: #ede9fe; border: 1px solid #ddd6fe; color: #6d28d9; }
.benefit-banner .icon { font-size: 20px; flex-shrink: 0; margin-top: 1px; }

/* Bill cards */
.bill-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: 12px;
    padding: 18px 20px;
    height: 100%;
    position: relative;
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.bill-card:hover { transform: translateY(-2px); box-shadow: var(--pay-shadow); }
.bill-card .stripe { position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.bill-card.paid    .stripe { background: linear-gradient(90deg, #16a34a, #15803d); }
.bill-card.partial .stripe { background: linear-gradient(90deg, #2563eb, #1d4ed8); }
.bill-card.unpaid  .stripe { background: linear-gradient(90deg, #d97706, #b45309); }
.bill-card.savings .stripe { background: linear-gradient(90deg, #7c3aed, #6d28d9); }
.bill-card.selected { border: 2px solid #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.2); }

.bill-amount-main { font-size: 22px; font-weight: 700; color: var(--pay-primary); }
.bill-mini-label  { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
.bill-mini-value  { font-size: 13px; font-weight: 700; }

.schol-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fef9c3; border: 1px solid #fde68a;
    color: #92400e; border-radius: 20px;
    padding: 2px 9px; font-size: 11px; font-weight: 600;
}
.disc-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #ede9fe; border: 1px solid #ddd6fe;
    color: #6d28d9; border-radius: 20px;
    padding: 2px 9px; font-size: 11px; font-weight: 600;
}

/* Progress bar */
.progress { height: 6px; border-radius: 10px; background: #e2e8f0; overflow: hidden; }
.progress-bar-paid   { background: linear-gradient(90deg, #16a34a, #15803d); border-radius: 10px; height: 6px; }
.progress-bar-partial{ background: linear-gradient(90deg, #2563eb, #1d4ed8); border-radius: 10px; height: 6px; }

/* Tabs */
.nav-tabs .nav-link {
    color: var(--pay-muted);
    font-size: 13px; font-weight: 600;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 10px 16px;
}
.nav-tabs .nav-link.active {
    color: var(--pay-accent);
    border-bottom-color: var(--pay-accent);
    background: transparent;
}

/* Record tables */
.rec-table th {
    background: var(--pay-bg);
    color: var(--pay-primary);
    padding: 10px 14px;
    font-size: 12px; font-weight: 700;
    white-space: nowrap;
    border-bottom: 2px solid var(--pay-border);
}
.rec-table td {
    padding: 10px 14px;
    vertical-align: middle;
    font-size: 13px;
    border-bottom: 1px solid var(--pay-border);
}
.rec-table tr:hover td { background: #f0f9ff; }

/* Empty state */
.empty-state {
    text-align: center;
    padding: 52px 24px;
    color: var(--pay-muted);
}
.empty-state i { font-size: 3rem; opacity: .25; display: block; margin-bottom: 14px; }
.empty-state p { margin: 0; font-size: 14px; }
.empty-state .sub { font-size: 12px; margin-top: 6px; }

/* Payment Modal */
#paymentModal .modal-content,
#bulkPaymentModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px;
    position: relative;
    overflow: hidden;
}
.modal-hero-bar::before {
    content: '';
    position: absolute; top: -25px; right: -25px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.modal-hero-bar h5 { color: #fff; font-weight: 700; margin: 0; font-size: 15px; position: relative; }
.modal-hero-bar .btn-close { position: absolute; top: 16px; right: 20px; filter: invert(1); }

.savings-breakdown {
    background: linear-gradient(135deg, #f3e8ff, #ede9fe);
    border: 1px solid #ddd6fe;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 14px;
}
.savings-breakdown .title { font-size: 12px; font-weight: 700; color: #7c3aed; margin-bottom: 8px; }
.savings-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
.savings-row:last-child { margin-bottom: 0; border-top: 1px solid #ddd6fe; padding-top: 6px; font-weight: 700; }

.form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-control, .form-select {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 14px;
    transition: border .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--pay-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.form-control[readonly] { background: var(--pay-bg); cursor: default; }

/* Bulk payment summary */
.bulk-summary {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}
.bulk-summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #dcfce7;
}
.bulk-summary-item:last-child {
    border-bottom: none;
    font-weight: 700;
    color: var(--pay-primary);
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Flash messages --}}
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

    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Processing payment...</p>
        </div>
    </div>

    {{-- Hero --}}
    <div class="pay-hero">
        <h1><i class="ri-wallet-line me-2"></i>Payment Details</h1>
        <p>Manage school fee payments for the selected student, term, and session.</p>
    </div>

    {{-- Main Content Container - Will be populated via AJAX --}}
    <div id="paymentContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading payment data...</p>
        </div>
    </div>

</div>
</div>
</div>

{{-- Bulk Payment Modal --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-wallet-line me-2"></i>Bulk Payment</h5>
            </div>
            <div class="modal-body p-4">
                <div id="selectedBillsSummary"></div>

                <div class="bulk-summary" id="bulkTotals">
                    <div class="bulk-summary-item">
                        <span>Total Payable Amount:</span>
                        <span class="fw-bold" id="bulkTotalPayable">₦0</span>
                    </div>
                    <div class="bulk-summary-item">
                        <span>Total Savings Applied:</span>
                        <span class="fw-bold text-success" id="bulkTotalSavings">₦0</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Payment Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text fw-semibold">₦</span>
                        <input type="text" id="bulk_payment_amount" class="form-control" placeholder="Enter total amount to pay">
                    </div>
                    <div class="form-text text-muted">Amount will be distributed across selected bills starting from earliest due.</div>
                    <div class="invalid-feedback" id="bulkAmountError"></div>
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

                <div id="paymentDistribution" class="mt-3" style="display: none;">
                    <label class="form-label">Payment Distribution</label>
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

{{-- Individual Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-wallet-line me-2"></i>Make Payment</h5>
            </div>
            <form id="paymentForm">
                @csrf
                <input type="hidden" id="student_id" name="student_id">
                <input type="hidden" id="class_id" name="class_id">
                <input type="hidden" id="term_id" name="term_id">
                <input type="hidden" id="session_id" name="session_id">
                <input type="hidden" id="school_bill_id" name="school_bill_id">
                <input type="hidden" id="actual_amount" name="actual_amount">
                <input type="hidden" id="adjusted_amount" name="adjusted_amount">
                <input type="hidden" id="balance2" name="balance2">
                <input type="hidden" id="last_amount_paid" name="last_amount_paid">
                <input type="hidden" id="payment_amount2" name="payment_amount2">
                <input type="hidden" id="scholarship_deduction" name="scholarship_deduction">
                <input type="hidden" id="discount_deduction" name="discount_deduction">

                <div class="modal-body p-4">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="fw-semibold" id="modal-bill-title" style="color:var(--pay-primary);font-size:15px">—</div>
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

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Bill Amount</label>
                            <input type="text" id="amount_d" class="form-control" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Amount Paid</label>
                            <input type="text" id="amount_paid_d" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outstanding Balance</label>
                        <input type="text" id="balance_d" class="form-control" readonly style="background:#fff3cd;font-weight:700;">
                        <div class="form-text small text-muted">Payment cannot exceed this balance.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Enter Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">₦</span>
                            <input type="text" id="payment_amount" name="payment_amount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="invalid-feedback d-block" id="amountError" style="display:none"></div>
                    </div>

                    <div class="mb-3">
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

{{-- Confirm Delete Modal --}}
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
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('studentId');
    const termid = urlParams.get('termid');
    const sessionid = urlParams.get('sessionid');

    let selectedBillsForBulk = new Set();
    let billsDataGlobal = [];
    let currentDeleteUrl = '';

    function fmt(n) {
        return Number(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 0 });
    }

    function showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.add('active');
        } else {
            overlay.classList.remove('active');
        }
    }

    function loadPaymentData() {
        if (!studentId || !termid || !sessionid) {
            document.getElementById('paymentContent').innerHTML = `
                <div class="alert alert-warning">
                    <i class="ri-alert-line"></i>
                    Invalid parameters. Please go back and select a student, term, and session.
                    <a href="{{ route('schoolpayment.index') }}" class="alert-link ms-2">← Back</a>
                </div>
            `;
            return;
        }

        showLoading(true);

        const url = '{{ route("schoolpayment.getPaymentDetailsAjax") }}' + `?studentId=${studentId}&termid=${termid}&sessionid=${sessionid}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                renderPaymentContent(result.data);
            } else {
                document.getElementById('paymentContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line"></i>
                        ${result.message || 'Failed to load payment data'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('paymentContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i>
                    An error occurred while loading payment data.
                </div>
            `;
        })
        .finally(() => showLoading(false));
    }

    function renderPaymentContent(data) {
        const student = data.student;
        const bills = data.bills;
        const paymentRecords = data.payment_records;
        const paymentHistory = data.payment_history;
        const scholarship = data.scholarship;
        const discounts = data.discounts;
        const totals = data.totals;

        billsDataGlobal = bills;

        const avatarHtml = student.avatar
            ? `<img src="/storage/images/studentavatar/${student.avatar}" alt="${student.name}" class="student-avatar-lg" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<div class=\"avatar-placeholder-lg\">${student.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2)}</div>';">`
            : `<div class="avatar-placeholder-lg">${student.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2)}</div>`;

        const billsHtml = bills.map(bill => {
            const cardClass = bill.is_paid ? 'paid' : (bill.is_partial ? 'partial' : (bill.total_savings > 0 ? 'savings' : 'unpaid'));
            return `
            <div class="col-xl-4 col-lg-6">
                <div class="bill-card ${cardClass}" data-bill-id="${bill.id}" data-bill-title="${bill.title.replace(/'/g, "\\'")}">
                    <div class="stripe"></div>
                    <div class="d-flex align-items-start justify-content-between mb-2 mt-1">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size:14px;color:var(--pay-primary)">${escapeHtml(bill.title)}</div>
                            ${bill.description ? `<div class="text-muted" style="font-size:11px">${escapeHtml(bill.description)}</div>` : ''}
                        </div>
                        <div class="ms-2 flex-shrink-0">
                            <input type="checkbox" class="bill-select-checkbox" data-bill-id="${bill.id}" ${bill.is_paid ? 'disabled' : ''} style="transform: scale(1.2);">
                        </div>
                    </div>
                    ${bill.total_savings > 0 ? `
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        ${bill.scholarship_deduction > 0 ? `
                        <span class="schol-pill">
                            <i class="ri-award-line"></i>
                            -₦${fmt(bill.scholarship_deduction)} Scholarship
                        </span>` : ''}
                        ${bill.discount_deduction > 0 ? `
                        <span class="disc-pill">
                            <i class="ri-price-tag-3-line"></i>
                            -₦${fmt(bill.discount_deduction)} Discount
                        </span>` : ''}
                    </div>
                    ` : ''}
                    <div class="text-center mb-2">
                        ${bill.total_savings > 0 ? `
                        <div class="text-muted text-decoration-line-through" style="font-size:12px">₦${fmt(bill.original_amount)}</div>
                        ` : ''}
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
                    ${!bill.is_paid ? `
                        ${bill.has_pending_invoice ? `
                        <button class="btn btn-secondary btn-sm w-100" disabled title="Generate invoice first">
                            <i class="ri-lock-line me-1"></i>Invoice Pending
                        </button>
                        ` : `
                        <button class="btn btn-primary btn-sm w-100 make-payment-btn" data-bill='${JSON.stringify(bill)}'>
                            <i class="ri-wallet-line me-1"></i>Make Payment
                        </button>
                        `}
                    ` : `
                        <button class="btn btn-success btn-sm w-100" disabled>
                            <i class="ri-checkbox-circle-line me-1"></i>Fully Paid
                        </button>
                    `}
                </div>
            </div>`;
        }).join('');

        const paymentRecordsHtml = paymentRecords.length > 0 ? `
            <div class="table-responsive">
                <table class="table rec-table w-100 mb-0">
                    <thead>
                        <tr><th>#</th><th>Bill</th><th>Bill Amount</th><th>Amount Paid</th><th>Balance</th><th>Method</th><th>Received By</th><th>Date</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        ${paymentRecords.map((sp, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td><div class="fw-semibold">${escapeHtml(sp.title)}</div>${sp.description ? `<div class="text-muted small">${escapeHtml(sp.description)}</div>` : ''}</td>
                            <td>₦${fmt(sp.billAmount)}</td>
                            <td class="text-success fw-semibold">₦${fmt(sp.totalAmountPaid)}</td>
                            <td class="${sp.balance > 0 ? 'text-danger' : 'text-success'} fw-semibold">₦${fmt(sp.balance)}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">${sp.paymentMethod || '—'}</span></td>
                            <td class="text-muted small">${sp.receivedBy || '—'}</td>
                            <td class="text-muted small">${sp.receivedDate ? new Date(sp.receivedDate).toLocaleDateString() : 'N/A'}</td>
                            <td><span class="badge ${sp.paymentStatus === 'Completed' ? 'bg-success' : 'bg-warning text-dark'}">${sp.paymentStatus || 'Pending'}</span></td>
                            <td>${sp.recordId ? `<button class="btn btn-sm btn-danger delete-payment" data-record-id="${sp.recordId}"><i class="ri-delete-bin-line"></i></button>` : '<span class="text-muted small">—</span>'}</td>
                        </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<div class="empty-state"><i class="ri-receipt-line"></i><p>No pending payment records.</p></div>';

        const historyHtml = paymentHistory.length > 0 ? `
            <div class="table-responsive">
                <table class="table rec-table w-100 mb-0">
                    <thead><tr><th>#</th><th>Bill</th><th>Bill Amount</th><th>Paid</th><th>Balance</th><th>Method</th><th>Received By</th><th>Date</th><th>Status</th><th>Invoice</th></tr></thead>
                    <tbody>
                        ${paymentHistory.map((ph, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td><div class="fw-semibold">${escapeHtml(ph.title)}</div>${ph.description ? `<div class="text-muted small">${escapeHtml(ph.description)}</div>` : ''}</td>
                            <td>₦${fmt(ph.billAmount)}</td>
                            <td class="text-success fw-semibold">₦${fmt(ph.totalAmountPaid)}</td>
                            <td class="${ph.balance > 0 ? 'text-danger' : 'text-success'} fw-semibold">₦${fmt(ph.balance)}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">${ph.paymentMethod || '—'}</span></td>
                            <td class="text-muted small">${ph.receivedBy || '—'}</td>
                            <td class="text-muted small">${ph.receivedDate ? new Date(ph.receivedDate).toLocaleDateString() : 'N/A'}</td>
                            <td><span class="badge ${(ph.paymentStatus === 'Completed' || ph.completePayment) ? 'bg-success' : 'bg-warning text-dark'}">${(ph.paymentStatus === 'Completed' || ph.completePayment) ? 'Completed' : 'Partial'}</span></td>
                            <td><a href="{{ url('schoolpayment/invoice') }}/${studentId}/${ph.classId || ''}/${ph.termId || termid}/${ph.sessionId || sessionid}" class="btn btn-sm btn-outline-primary" title="View Invoice"><i class="ri-file-download-line"></i></a></td>
                        </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<div class="empty-state"><i class="ri-history-line"></i><p>No payment history found.</p></div>';

        const totalOutstanding = totals.outstanding;
        const totalSavings = totals.savings;

        const contentHtml = `
            <div class="student-card">
                <div class="d-flex align-items-start gap-4 flex-wrap">
                    <div>${avatarHtml}</div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="mb-0 fw-bold" style="color:var(--pay-primary)">${escapeHtml(student.name)}</h5>
                            ${student.student_status === 'Active' ? '<span class="badge bg-success-subtle text-success px-2 py-1" style="font-size:11px">Active</span>' : `<span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size:11px">${student.student_status || 'Unknown'}</span>`}
                            ${student.statusId == 1 ? '<span class="badge bg-info-subtle text-info px-2 py-1" style="font-size:11px">Returning Student</span>' : '<span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size:11px">New Student</span>'}
                        </div>
                        <div class="text-muted small font-monospace mb-3">${student.admissionNo}</div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="info-chip"><i class="ri-building-line text-success"></i>${student.schoolclass} ${student.arm}</div>
                            <div class="info-chip"><i class="ri-calendar-line text-primary"></i>${data.term}</div>
                            <div class="info-chip"><i class="ri-time-line text-warning"></i>${data.session}</div>
                            <div class="info-chip"><i class="ri-money-dollar-circle-line text-danger"></i>Total: ₦${fmt(totals.adjusted)}</div>
                            <div class="info-chip"><i class="ri-check-line text-success"></i>Paid: ₦${fmt(totals.paid)}</div>
                            ${totalOutstanding > 0 ?
                                `<div class="info-chip" style="background:#fef2f2;border-color:#fecaca;color:#dc2626"><i class="ri-alert-line"></i>Outstanding: ₦${fmt(totalOutstanding)}</div>` :
                                `<div class="info-chip" style="background:#f0fdf4;border-color:#bbf7d0;color:#16a34a"><i class="ri-checkbox-circle-line"></i>Fully Paid</div>`
                            }
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-start">
                        <a href="{{ route('schoolpayment.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line me-1"></i>Back</a>
                        ${paymentRecords.length > 0 ?
                            `<a href="{{ url('schoolpayment/invoice') }}/${studentId}/${student.schoolclassId || ''}/${termid}/${sessionid}" class="btn btn-primary btn-sm"><i class="ri-file-download-line me-1"></i>Generate Invoice</a>` :
                            `<button class="btn btn-primary btn-sm" disabled title="Make a payment first"><i class="ri-file-download-line me-1"></i>Generate Invoice</button>`
                        }
                        <a href="{{ url('schoolpayment/statement') }}/${studentId}/${student.schoolclassId || ''}/${termid}/${sessionid}" class="btn btn-outline-primary btn-sm"><i class="ri-file-list-line me-1"></i>Statement</a>
                        <button class="btn btn-success btn-sm" id="bulkPaymentBtn" ${bills.filter(b => !b.is_paid && !b.has_pending_invoice).length === 0 ? 'disabled' : ''}>
                            <i class="ri-wallet-3-line me-1"></i>Bulk Payment
                            <span class="badge bg-white text-success ms-1" id="selectedCount">0</span>
                        </button>
                    </div>
                </div>
            </div>
            ${scholarship ? `
            <div class="benefit-banner schol">
                <i class="ri-award-line icon"></i>
                <div>
                    <div class="fw-semibold mb-1">Scholarship Active: ${escapeHtml(scholarship.title)}</div>
                    <div class="small">${scholarship.value_type === 'percentage' ? `${scholarship.value}% deduction on applicable fees.` : `₦${fmt(scholarship.value)} fixed deduction per bill.`} ${scholarship.effective_to ? `Valid until ${new Date(scholarship.effective_to).toLocaleDateString()}.` : ''}<strong class="ms-2">Total Savings: ₦${fmt(totalSavings)}</strong></div>
                </div>
            </div>
            ` : ''}
            ${discounts.length > 0 ? `
            <div class="benefit-banner disc">
                <i class="ri-price-tag-3-line icon"></i>
                <div>
                    <div class="fw-semibold mb-1">Discount(s) Active</div>
                    <div class="small">${discounts.map(d => `<span class="me-3"><strong>${escapeHtml(d.title)}:</strong> ${d.value_type === 'percentage' ? `${d.value}% off` : `₦${fmt(d.value)} off`}</span>`).join('')}</div>
                </div>
            </div>
            ` : ''}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pt-3 pb-0">
                    <ul class="nav nav-tabs border-0" id="payTabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-bills"><i class="ri-bill-line me-1"></i>School Bills <span class="badge bg-primary-subtle text-primary ms-1">${bills.length}</span></a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-records"><i class="ri-receipt-line me-1"></i>Payment Records ${paymentRecords.length ? `<span class="badge bg-success-subtle text-success ms-1">${paymentRecords.length}</span>` : ''}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-history"><i class="ri-history-line me-1"></i>History ${paymentHistory.length ? `<span class="badge bg-info-subtle text-info ms-1">${paymentHistory.length}</span>` : ''}</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-bills">
                            ${bills.length > 0 ? `
                            <div class="row g-3 mt-1">
                                ${billsHtml}
                            </div>
                            ${totals.savings > 0 ? `
                            <div class="mt-4 p-3 rounded-3" style="background:linear-gradient(135deg,#f3e8ff,#ede9fe);border:1px solid #ddd6fe">
                                <div class="d-flex align-items-center gap-2"><i class="ri-gift-line" style="font-size:18px;color:#7c3aed"></i><div><span class="fw-semibold" style="color:#7c3aed">Total Savings Applied:</span><span class="fw-bold ms-1" style="color:#7c3aed">₦${fmt(totals.savings)}</span><span class="text-muted small ms-2">(Original: ₦${fmt(totals.original)} → Payable: ₦${fmt(totals.adjusted)})</span></div></div>
                            </div>
                            ` : ''}
                            ` : '<div class="empty-state"><i class="ri-inbox-line"></i><p>No bills assigned.</p></div>'}
                        </div>
                        <div class="tab-pane fade" id="tab-records">${paymentRecordsHtml}</div>
                        <div class="tab-pane fade" id="tab-history">${historyHtml}</div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('paymentContent').innerHTML = contentHtml;

        // Attach event listeners
        attachBillSelectionEvents(bills);
        attachPaymentButtons();
        attachDeleteHandlers();

        document.getElementById('bulkPaymentBtn').addEventListener('click', () => openBulkPaymentModal(bills));
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    function attachBillSelectionEvents(bills) {
        const checkboxes = document.querySelectorAll('.bill-select-checkbox');
        const selectedCountSpan = document.getElementById('selectedCount');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const billId = parseInt(e.target.dataset.billId);
                if (e.target.checked) {
                    selectedBillsForBulk.add(billId);
                } else {
                    selectedBillsForBulk.delete(billId);
                }
                selectedCountSpan.textContent = selectedBillsForBulk.size;

                document.querySelectorAll('.bill-card').forEach(card => {
                    const cardBillId = parseInt(card.dataset.billId);
                    if (selectedBillsForBulk.has(cardBillId)) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            });
        });
    }

    function attachPaymentButtons() {
        document.querySelectorAll('.make-payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const billData = JSON.parse(this.dataset.bill);
                openPaymentModal(billData);
            });
        });
    }

    function attachDeleteHandlers() {
        document.querySelectorAll('.delete-payment').forEach(btn => {
            btn.removeEventListener('click', handleDeleteClick);
            btn.addEventListener('click', handleDeleteClick);
        });
    }

    function handleDeleteClick(e) {
        const recordId = this.dataset.recordId;
        if (recordId) {
            currentDeleteUrl = '/schoolpayment/delete/' + recordId;
            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        }
    }

    function openPaymentModal(bill) {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));

        document.getElementById('modal-bill-title').textContent = bill.title;
        document.getElementById('student_id').value = studentId;
        document.getElementById('class_id').value = bill.class_id || '';
        document.getElementById('term_id').value = termid;
        document.getElementById('session_id').value = sessionid;
        document.getElementById('school_bill_id').value = bill.id;
        document.getElementById('actual_amount').value = bill.original_amount;
        document.getElementById('adjusted_amount').value = bill.adjusted_amount;
        document.getElementById('balance2').value = bill.balance;
        document.getElementById('last_amount_paid').value = bill.amount_paid;
        document.getElementById('scholarship_deduction').value = bill.scholarship_deduction || 0;
        document.getElementById('discount_deduction').value = bill.discount_deduction || 0;
        document.getElementById('amount_d').value = '₦' + fmt(bill.adjusted_amount);
        document.getElementById('amount_paid_d').value = '₦' + fmt(bill.amount_paid);
        document.getElementById('balance_d').value = '₦' + fmt(bill.balance);
        document.getElementById('payment_amount').value = '';
        document.getElementById('payment_amount2').value = '';
        document.getElementById('payment_method2').value = '';

        const savBox = document.getElementById('savingsBreakdown');
        if (bill.total_savings > 0) {
            savBox.classList.remove('d-none');
            const scholRow = document.getElementById('scholRow');
            const discRow = document.getElementById('discRow');
            if (bill.scholarship_deduction > 0) {
                scholRow.classList.remove('d-none');
                document.getElementById('scholLabel').textContent = bill.scholarship_label || 'Scholarship';
                document.getElementById('scholAmt').textContent = '-₦' + fmt(bill.scholarship_deduction);
            } else {
                scholRow.classList.add('d-none');
            }
            if (bill.discount_deduction > 0) {
                discRow.classList.remove('d-none');
                document.getElementById('discLabel').textContent = bill.discount_labels || 'Discount';
                document.getElementById('discAmt').textContent = '-₦' + fmt(bill.discount_deduction);
            } else {
                discRow.classList.add('d-none');
            }
            document.getElementById('totalSavingsAmt').textContent = '-₦' + fmt(bill.total_savings);
        } else {
            savBox.classList.add('d-none');
        }

        modal.show();
    }

    function openBulkPaymentModal(bills) {
        const selectedBills = bills.filter(b => selectedBillsForBulk.has(b.id));

        if (selectedBills.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Bills Selected', text: 'Please select at least one bill for bulk payment.', confirmButtonColor: '#2563eb' });
            return;
        }

        const totalPayable = selectedBills.reduce((sum, b) => sum + b.balance, 0);
        const totalSavings = selectedBills.reduce((sum, b) => sum + (b.total_savings || 0), 0);

        const summaryHtml = `
            <div class="mb-3">
                <label class="form-label">Selected Bills (${selectedBills.length})</label>
                <div class="list-group">
                    ${selectedBills.map(b => `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>${escapeHtml(b.title)}</div>
                            <div class="text-end">
                                <small class="text-muted d-block">Balance: ₦${fmt(b.balance)}</small>
                                ${b.total_savings > 0 ? `<small class="text-success">Saved: ₦${fmt(b.total_savings)}</small>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        document.getElementById('selectedBillsSummary').innerHTML = summaryHtml;
        document.getElementById('bulkTotalPayable').textContent = '₦' + fmt(totalPayable);
        document.getElementById('bulkTotalSavings').textContent = '₦' + fmt(totalSavings);
        document.getElementById('bulk_payment_amount').value = '';
        document.getElementById('bulk_payment_method').value = '';
        document.getElementById('paymentDistribution').style.display = 'none';

        const modal = new bootstrap.Modal(document.getElementById('bulkPaymentModal'));
        modal.show();

        const amountInput = document.getElementById('bulk_payment_amount');
        const distributionDiv = document.getElementById('distributionList');

        amountInput.oninput = () => {
            let amount = parseFloat(amountInput.value.replace(/[^0-9.]/g, '')) || 0;
            let remaining = amount;
            let distribution = [];

            for (let bill of selectedBills) {
                if (remaining <= 0) break;
                if (bill.balance <= 0) continue;

                const payAmount = Math.min(remaining, bill.balance);
                if (payAmount > 0) {
                    distribution.push({ title: bill.title, amount: payAmount, newBalance: bill.balance - payAmount });
                    remaining -= payAmount;
                }
            }

            if (distribution.length > 0) {
                distributionDiv.innerHTML = distribution.map(d => `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>${escapeHtml(d.title)}</span>
                        <div class="text-end">
                            <span class="fw-semibold text-success">₦${fmt(d.amount)}</span>
                            <small class="text-muted d-block">New Balance: ₦${fmt(d.newBalance)}</small>
                        </div>
                    </div>
                `).join('');
                document.getElementById('paymentDistribution').style.display = 'block';
            } else {
                document.getElementById('paymentDistribution').style.display = 'none';
            }
        };

        document.getElementById('submitBulkPayment').onclick = () => submitBulkPayment(selectedBills);
    }

    function submitBulkPayment(selectedBills) {
        const paymentAmount = parseFloat(document.getElementById('bulk_payment_amount').value.replace(/[^0-9.]/g, '')) || 0;
        const paymentMethod = document.getElementById('bulk_payment_method').value;
        const totalPayable = selectedBills.reduce((sum, b) => sum + b.balance, 0);

        if (paymentAmount <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Please enter a valid payment amount.', confirmButtonColor: '#2563eb' });
            return;
        }

        if (paymentAmount > totalPayable) {
            Swal.fire({ icon: 'warning', title: 'Amount Exceeds Balance', text: `Total outstanding balance is ₦${fmt(totalPayable)}.`, confirmButtonColor: '#2563eb' });
            return;
        }

        if (!paymentMethod) {
            Swal.fire({ icon: 'warning', title: 'Missing Method', text: 'Please select a payment method.', confirmButtonColor: '#2563eb' });
            return;
        }

        showLoading(true);

        const billPayments = selectedBills.map(bill => ({
            school_bill_id: bill.id,
            title: bill.title,
            adjusted_amount: bill.adjusted_amount,
            balance: bill.balance,
            scholarship_deduction: bill.scholarship_deduction || 0,
            discount_deduction: bill.discount_deduction || 0,
        }));

        fetch('{{ route("schoolpayment.bulk-store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                student_id: studentId,
                class_id: 1,
                term_id: termid,
                session_id: sessionid,
                payment_amount: paymentAmount,
                payment_method: paymentMethod,
                bill_payments: billPayments
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: result.message, timer: 2000, showConfirmButton: false })
                    .then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('bulkPaymentModal'))?.hide();
                        selectedBillsForBulk.clear();
                        document.getElementById('selectedCount').textContent = '0';
                        loadPaymentData();
                    });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while processing the payment.' });
        })
        .finally(() => showLoading(false));
    }

    // Payment form submission
    document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const amount = parseFloat(document.getElementById('payment_amount').value.replace(/[^0-9.]/g, '')) || 0;
        const balance = parseFloat(document.getElementById('balance2').value) || 0;
        const method = document.getElementById('payment_method2').value;

        if (amount <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Enter a valid amount.', confirmButtonColor: '#2563eb' });
            return;
        }
        if (amount > balance) {
            Swal.fire({ icon: 'warning', title: 'Amount Exceeds Balance', text: `Balance is ₦${fmt(balance)}`, confirmButtonColor: '#2563eb' });
            return;
        }
        if (!method) {
            Swal.fire({ icon: 'warning', title: 'Missing Method', text: 'Select a payment method.', confirmButtonColor: '#2563eb' });
            return;
        }

        document.getElementById('payment_amount2').value = amount.toFixed(2);

        const btn = document.getElementById('paySubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

        fetch('{{ route("schoolpayment.store") }}', {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
                Swal.fire({ icon: 'success', title: 'Payment Recorded!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => loadPaymentData());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(error => Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred.' }))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-wallet-line me-1"></i>Record Payment';
        });
    });

    // Delete confirmation handler
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function () {
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
        if (modal) modal.hide();

        const self = this;
        self.disabled = true;
        self.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

        fetch(currentDeleteUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function () {
                    loadPaymentData();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(function () {
            Swal.fire('Error', 'Failed to delete payment. Please try again.', 'error');
        })
        .finally(function () {
            self.disabled = false;
            self.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Delete';
            currentDeleteUrl = '';
        });
    });

    // Load initial data
    loadPaymentData();
});
</script>
@endsection
