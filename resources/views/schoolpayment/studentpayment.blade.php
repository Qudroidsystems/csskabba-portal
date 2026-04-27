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

.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 24px 32px;
    margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.pay-hero::before {
    content: ''; position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px; background: rgba(255,255,255,.06); border-radius: 50%;
}
.pay-hero h1 { font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 4px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

/* ── Student card ── */
.student-card {
    background: #fff; border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius); padding: 20px 24px;
    margin-bottom: 20px; box-shadow: var(--pay-shadow);
}
.student-avatar-lg {
    width: 72px; height: 72px; border-radius: 50%; object-fit: cover;
    border: 3px solid var(--pay-border); display: block;
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

/* ── Benefit banners ── */
.benefit-banner {
    border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;
    display: flex; align-items: flex-start; gap: 12px; font-size: 13px;
}
.benefit-banner.schol { background: #fef9c3; border: 1px solid #fde68a; color: #92400e; }
.benefit-banner.disc  { background: #ede9fe; border: 1px solid #ddd6fe; color: #6d28d9; }
.benefit-banner .icon { font-size: 20px; flex-shrink: 0; margin-top: 1px; }

/* ── Pay-All banner ── */
.pay-all-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 100%);
    border-radius: 12px; padding: 16px 24px; margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    flex-wrap: wrap;
}
.pay-all-info { color: #fff; }
.pay-all-info .label { font-size: 12px; opacity: .75; margin-bottom: 2px; }
.pay-all-info .amount { font-size: 22px; font-weight: 700; }
.pay-all-info .sub { font-size: 11px; opacity: .65; margin-top: 2px; }
.pay-all-btn {
    background: #fff; color: var(--pay-primary); border: none;
    border-radius: 10px; padding: 11px 22px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    transition: opacity .15s, transform .1s; white-space: nowrap;
    flex-shrink: 0;
}
.pay-all-btn:hover  { opacity: .92; transform: translateY(-1px); }
.pay-all-btn:active { transform: translateY(0); }
.pay-all-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ── Bill cards ── */
.bill-card {
    background: #fff; border: 1px solid var(--pay-border);
    border-radius: 12px; padding: 18px 20px; height: 100%;
    position: relative; overflow: hidden; transition: transform .15s, box-shadow .15s;
}
.bill-card:hover { transform: translateY(-2px); box-shadow: var(--pay-shadow); }
.bill-card .stripe { position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.bill-card.paid    .stripe { background: linear-gradient(90deg, #16a34a, #15803d); }
.bill-card.partial .stripe { background: linear-gradient(90deg, #2563eb, #1d4ed8); }
.bill-card.unpaid  .stripe { background: linear-gradient(90deg, #d97706, #b45309); }
.bill-card.savings .stripe { background: linear-gradient(90deg, #7c3aed, #6d28d9); }

.bill-amount-main { font-size: 22px; font-weight: 700; color: var(--pay-primary); }
.bill-mini-label  { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
.bill-mini-value  { font-size: 13px; font-weight: 700; }

.schol-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fef9c3; border: 1px solid #fde68a; color: #92400e;
    border-radius: 20px; padding: 2px 9px; font-size: 11px; font-weight: 600;
}
.disc-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #ede9fe; border: 1px solid #ddd6fe; color: #6d28d9;
    border-radius: 20px; padding: 2px 9px; font-size: 11px; font-weight: 600;
}

.progress { height: 6px; border-radius: 10px; background: #e2e8f0; overflow: hidden; }
.progress-bar-paid    { background: linear-gradient(90deg, #16a34a, #15803d); border-radius: 10px; height: 6px; }
.progress-bar-partial { background: linear-gradient(90deg, #2563eb, #1d4ed8); border-radius: 10px; height: 6px; }

/* ── Tabs ── */
.nav-tabs .nav-link {
    color: var(--pay-muted); font-size: 13px; font-weight: 600;
    border: none; border-bottom: 2px solid transparent; padding: 10px 16px;
}
.nav-tabs .nav-link.active {
    color: var(--pay-accent); border-bottom-color: var(--pay-accent); background: transparent;
}

/* ── Record tables ── */
.rec-table th {
    background: var(--pay-bg); color: var(--pay-primary); padding: 10px 14px;
    font-size: 12px; font-weight: 700; white-space: nowrap; border-bottom: 2px solid var(--pay-border);
}
.rec-table td {
    padding: 10px 14px; vertical-align: middle; font-size: 13px; border-bottom: 1px solid var(--pay-border);
}
.rec-table tr:hover td { background: #f0f9ff; }

.empty-state { text-align: center; padding: 52px 24px; color: var(--pay-muted); }
.empty-state i { font-size: 3rem; opacity: .25; display: block; margin-bottom: 14px; }
.empty-state p { margin: 0; font-size: 14px; }
.empty-state .sub { font-size: 12px; margin-top: 6px; }

/* ── Payment Modal ── */
#paymentModal .modal-content, #payAllModal .modal-content {
    border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.15);
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
    border: 1px solid #ddd6fe; border-radius: 10px; padding: 12px 16px; margin-bottom: 14px;
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

/* ── Pay-All bill breakdown table ── */
.payall-table { font-size: 12px; }
.payall-table th { background: var(--pay-bg); font-weight: 700; color: var(--pay-primary); padding: 8px 10px; font-size: 11px; }
.payall-table td { padding: 8px 10px; border-bottom: 1px solid var(--pay-border); vertical-align: middle; }
.payall-table tr:last-child td { border-bottom: none; }

/* ── AJAX loading overlay ── */
#billsLoadingOverlay {
    display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,.75); z-index: 10;
    align-items: center; justify-content: center;
    border-radius: var(--pay-radius);
}
#billsContainer { position: relative; }
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

    <div class="pay-hero">
        <h1><i class="ri-wallet-line me-2"></i>Payment Details</h1>
        <p>Manage school fee payments for the selected student, term, and session.</p>
    </div>

    @if(!$studentdata)
        <div class="alert alert-warning d-flex align-items-center gap-3">
            <i class="ri-alert-line fs-4"></i>
            <div>
                Student not found or not enrolled in the current session.
                <a href="{{ route('schoolpayment.index') }}" class="alert-link ms-2">← Back to students list</a>
            </div>
        </div>
    @else

    @php
        $totalBillOriginal = $student_bill_info->sum('amount');
        $totalBillAdjusted = $student_bill_info->sum('adjusted_amount');
        $totalPaidSoFar    = $studentpaymentbillbook->sum('amount_paid');
        $totalOutstanding  = max(0, $totalBillAdjusted - $totalPaidSoFar);
        $totalSavings      = $student_bill_info->sum('total_savings');

        // Compute student avatar path
        $avatarPath = null;
        if (!empty($studentdata->avatar)) {
            $avatarPath = asset('storage/images/studentavatar/' . $studentdata->avatar);
        }
        $initials = strtoupper(substr($studentdata->firstname ?? '', 0, 1) . substr($studentdata->lastname ?? '', 0, 1));
    @endphp

    {{-- Student info card --}}
    <div class="student-card">
        <div class="d-flex align-items-start gap-4 flex-wrap">

            <div>
                @if($avatarPath)
                    <img src="{{ $avatarPath }}"
                         alt="{{ $studentdata->firstname }}"
                         class="student-avatar-lg"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                    <div class="avatar-placeholder-lg" style="display:none">{{ $initials }}</div>
                @else
                    <div class="avatar-placeholder-lg">{{ $initials }}</div>
                @endif
            </div>

            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h5 class="mb-0 fw-bold" style="color:var(--pay-primary)">
                        {{ $studentdata->firstname }} {{ $studentdata->lastname }}
                    </h5>
                    @if(($studentdata->student_status ?? '') === 'Active')
                        <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size:11px">Active</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size:11px">
                            {{ $studentdata->student_status ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(($studentdata->statusId ?? 0) == 1)
                        <span class="badge bg-info-subtle text-info px-2 py-1" style="font-size:11px">Returning Student</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size:11px">New Student</span>
                    @endif
                </div>
                <div class="text-muted small font-monospace mb-3">{{ $studentdata->admissionNo }}</div>
                <div class="d-flex flex-wrap gap-2">
                    <div class="info-chip"><i class="ri-building-line text-success"></i>{{ $studentdata->schoolclass }} {{ $studentdata->arm }}</div>
                    <div class="info-chip"><i class="ri-calendar-line text-primary"></i>{{ $schoolterm }}</div>
                    <div class="info-chip"><i class="ri-time-line text-warning"></i>{{ $schoolsession }}</div>
                    <div class="info-chip"><i class="ri-money-dollar-circle-line text-danger"></i>Total: ₦{{ number_format($totalBillAdjusted, 0) }}</div>
                    <div class="info-chip"><i class="ri-check-line text-success"></i>Paid: ₦{{ number_format($totalPaidSoFar, 0) }}</div>
                    @if($totalOutstanding > 0)
                        <div class="info-chip" style="background:#fef2f2;border-color:#fecaca;color:#dc2626">
                            <i class="ri-alert-line"></i>Outstanding: ₦{{ number_format($totalOutstanding, 0) }}
                        </div>
                    @else
                        <div class="info-chip" style="background:#f0fdf4;border-color:#bbf7d0;color:#16a34a">
                            <i class="ri-checkbox-circle-line"></i>Fully Paid
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-start">
                <a href="{{ route('schoolpayment.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </a>
                <button id="generateInvoiceBtn"
                        class="btn btn-primary btn-sm {{ $paymentRecordsCount > 0 ? '' : 'disabled' }}"
                        {{ $paymentRecordsCount > 0 ? '' : 'disabled' }}
                        data-invoice-url="{{ route('schoolpayment.invoice', ['studentId'=>$studentId,'schoolclassid'=>$schoolclassId,'termid'=>$termid,'sessionid'=>$sessionid]) }}">
                    <i class="ri-file-download-line me-1"></i>Generate Invoice
                </button>
                <a href="{{ route('schoolpayment.statement', ['studentId'=>$studentId,'schoolclassid'=>$schoolclassId,'termid'=>$termid,'sessionid'=>$sessionid]) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="ri-file-list-line me-1"></i>Statement
                </a>
            </div>

        </div>
    </div>

    @if(isset($scholarshipInfo) && $scholarshipInfo)
    <div class="benefit-banner schol">
        <i class="ri-award-line icon"></i>
        <div>
            <div class="fw-semibold mb-1">Scholarship Active: {{ $scholarshipInfo->scholarship->title ?? 'Scholarship' }}</div>
            <div class="small">
                @if($scholarshipInfo->value_type === 'percentage')
                    {{ $scholarshipInfo->value }}% deduction on applicable fees.
                @else
                    ₦{{ number_format($scholarshipInfo->value, 0) }} fixed deduction per bill.
                @endif
                @if($scholarshipInfo->effective_to)
                    Valid until {{ \Carbon\Carbon::parse($scholarshipInfo->effective_to)->format('d M Y') }}.
                @endif
                <strong class="ms-2">Total Savings: ₦{{ number_format($totalSavings, 0) }}</strong>
            </div>
        </div>
    </div>
    @endif

    @if(isset($discountAssignments) && $discountAssignments->count())
    <div class="benefit-banner disc">
        <i class="ri-price-tag-3-line icon"></i>
        <div>
            <div class="fw-semibold mb-1">Discount(s) Active</div>
            <div class="small">
                @foreach($discountAssignments as $da)
                    @if($da->discount)
                    <span class="me-3">
                        <strong>{{ $da->discount->title }}:</strong>
                        @if($da->value_type === 'percentage')
                            {{ $da->value }}% off applicable bills.
                        @else
                            ₦{{ number_format($da->value, 0) }} off.
                        @endif
                    </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── TABS ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom pt-3 pb-0">
            <ul class="nav nav-tabs border-0" id="payTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-bills">
                        <i class="ri-bill-line me-1"></i>School Bills
                        <span class="badge bg-primary-subtle text-primary ms-1" id="billsBadge">{{ $student_bill_info->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-records">
                        <i class="ri-receipt-line me-1"></i>Payment Records
                        <span class="badge bg-success-subtle text-success ms-1" id="recordsBadge">{{ $studentpaymentbill->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-history">
                        <i class="ri-history-line me-1"></i>History
                        <span class="badge bg-info-subtle text-info ms-1" id="historyBadge">{{ $paymentHistory->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- ════════════ BILLS TAB ════════════ --}}
                <div class="tab-pane fade show active" id="tab-bills">
                    @if($student_bill_info->isEmpty())
                        <div class="empty-state">
                            <i class="ri-inbox-line"></i>
                            <p>No bills assigned for this class, term, and session.</p>
                            <p class="sub text-muted">
                                Class: <strong>{{ $studentdata->schoolclass }} {{ $studentdata->arm }}</strong> ·
                                Term: <strong>{{ $schoolterm }}</strong> · Session: <strong>{{ $schoolsession }}</strong>
                            </p>
                        </div>
                    @else

                        {{-- ── Pay All Bills Banner ── --}}
                        @php
                            $payableBills = $student_bill_info->filter(function($bill) use ($studentpaymentbillbook, $studentpaymentbill) {
                                $bookEntry  = $studentpaymentbillbook->where('school_bill_id', $bill->schoolbillid)->first();
                                $amountPaid = $bookEntry ? (float) $bookEntry->amount_paid : 0;
                                $balance    = max(0, (float)$bill->adjusted_amount - $amountPaid);
                                $pending    = $studentpaymentbill->where('school_bill_id', $bill->schoolbillid)->first();
                                return $balance > 0 && !($pending && $pending->delete_status == '1');
                            });
                            $totalPayableBalance = $payableBills->sum(function($bill) use ($studentpaymentbillbook) {
                                $bookEntry  = $studentpaymentbillbook->where('school_bill_id', $bill->schoolbillid)->first();
                                $amountPaid = $bookEntry ? (float) $bookEntry->amount_paid : 0;
                                return max(0, (float)$bill->adjusted_amount - $amountPaid);
                            });
                        @endphp

                        @if($payableBills->count() > 1 && $totalPayableBalance > 0)
                        <div class="pay-all-banner" id="payAllBanner">
                            <div class="pay-all-info">
                                <div class="label">Total Outstanding Across {{ $payableBills->count() }} Bills</div>
                                <div class="amount">₦{{ number_format($totalPayableBalance, 0) }}</div>
                                <div class="sub">Pay part or all — amounts are distributed across bills proportionally</div>
                            </div>
                            <button class="pay-all-btn" id="openPayAllBtn">
                                <i class="ri-stack-line"></i>
                                Pay All Bills at Once
                            </button>
                        </div>
                        @endif

                        {{-- Bill cards container (AJAX-refreshed) --}}
                        <div id="billsContainer">
                            <div id="billsLoadingOverlay" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.75);z-index:10;align-items:center;justify-content:center;border-radius:var(--pay-radius)">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-2"></div>
                                    <div class="small text-muted">Updating bills...</div>
                                </div>
                            </div>

                            <div class="row g-3 mt-1" id="billCardsRow">
                                @foreach($student_bill_info as $bill)
                                @php
                                    $bookEntry   = $studentpaymentbillbook->where('school_bill_id', $bill->schoolbillid)->first();
                                    $amountPaid  = $bookEntry ? (float) $bookEntry->amount_paid : 0;
                                    $adjustedAmt = (float) $bill->adjusted_amount;
                                    $balance     = max(0, $adjustedAmt - $amountPaid);
                                    $progress    = $adjustedAmt > 0 ? min(100, ($amountPaid / $adjustedAmt) * 100) : 0;
                                    $isPaid      = $balance <= 0 && $amountPaid > 0;
                                    $isPartial   = $amountPaid > 0 && $balance > 0;
                                    $hasSavings  = $bill->total_savings > 0;
                                    $pendingPayment = $studentpaymentbill->where('school_bill_id', $bill->schoolbillid)->first();
                                    $invoicePending = $pendingPayment && $pendingPayment->delete_status == '1';
                                    $cardClass   = $isPaid ? 'paid' : ($isPartial ? 'partial' : 'unpaid');
                                    if ($hasSavings && !$isPaid) $cardClass = 'savings';
                                @endphp
                                <div class="col-xl-4 col-lg-6 bill-card-col" data-bill-id="{{ $bill->schoolbillid }}">
                                    <div class="bill-card {{ $cardClass }}">
                                        <div class="stripe"></div>
                                        <div class="d-flex align-items-start justify-content-between mb-2 mt-1">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold mb-1" style="font-size:14px;color:var(--pay-primary)">{{ $bill->title }}</div>
                                                @if($bill->description)
                                                    <div class="text-muted" style="font-size:11px">{{ $bill->description }}</div>
                                                @endif
                                            </div>
                                            <div class="ms-2 flex-shrink-0">
                                                @if($isPaid)
                                                    <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size:10px"><i class="ri-checkbox-circle-line me-1"></i>Paid</span>
                                                @elseif($isPartial)
                                                    <span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size:10px"><i class="ri-progress-1-line me-1"></i>Partial</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size:10px"><i class="ri-time-line me-1"></i>Unpaid</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($bill->total_savings > 0)
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            @if($bill->scholarship_deduction > 0)
                                                <span class="schol-pill"><i class="ri-award-line"></i>-₦{{ number_format($bill->scholarship_deduction, 0) }} Scholarship</span>
                                            @endif
                                            @if($bill->discount_deduction > 0)
                                                <span class="disc-pill"><i class="ri-price-tag-3-line"></i>-₦{{ number_format($bill->discount_deduction, 0) }} Discount</span>
                                            @endif
                                        </div>
                                        @endif
                                        <div class="text-center mb-2">
                                            @if($bill->total_savings > 0)
                                                <div class="text-muted text-decoration-line-through" style="font-size:12px">₦{{ number_format($bill->original_amount, 0) }}</div>
                                            @endif
                                            <div class="bill-amount-main">₦{{ number_format($adjustedAmt, 0) }}</div>
                                            <div style="font-size:11px;color:var(--pay-muted)">{{ $bill->total_savings > 0 ? 'After savings' : 'Payable amount' }}</div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6 text-center">
                                                <div class="bill-mini-label">Paid</div>
                                                <div class="bill-mini-value text-success">₦{{ number_format($amountPaid, 0) }}</div>
                                            </div>
                                            <div class="col-6 text-center">
                                                <div class="bill-mini-label">Balance</div>
                                                <div class="bill-mini-value {{ $balance > 0 ? 'text-danger' : 'text-success' }}">₦{{ number_format($balance, 0) }}</div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1" style="font-size:10px;color:var(--pay-muted)">
                                                <span>Progress</span>
                                                <span class="fw-semibold {{ $isPaid ? 'text-success' : 'text-primary' }}">{{ number_format($progress, 0) }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="{{ $isPaid ? 'progress-bar-paid' : 'progress-bar-partial' }}" style="width:{{ $progress }}%"></div>
                                            </div>
                                        </div>
                                        @if($isPaid)
                                            <button class="btn btn-success btn-sm w-100" disabled><i class="ri-checkbox-circle-line me-1"></i>Fully Paid</button>
                                        @elseif($invoicePending)
                                            <button class="btn btn-secondary btn-sm w-100" disabled title="Generate invoice first"><i class="ri-lock-line me-1"></i>Invoice Pending</button>
                                        @else
                                            <button class="btn btn-primary btn-sm w-100 make-payment"
                                                    data-student_id="{{ $studentId }}"
                                                    data-amount="{{ $adjustedAmt }}"
                                                    data-original_amount="{{ $bill->original_amount }}"
                                                    data-amount_paid="{{ $amountPaid }}"
                                                    data-balance="{{ $balance }}"
                                                    data-school_bill_id="{{ $bill->schoolbillid }}"
                                                    data-class_id="{{ $schoolclassId }}"
                                                    data-term_id="{{ $termid }}"
                                                    data-session_id="{{ $sessionid }}"
                                                    data-title="{{ addslashes($bill->title) }}"
                                                    data-scholarship_deduction="{{ $bill->scholarship_deduction }}"
                                                    data-scholarship_label="{{ addslashes($bill->scholarship_label ?? '') }}"
                                                    data-discount_deduction="{{ $bill->discount_deduction }}"
                                                    data-discount_labels="{{ addslashes(implode(', ', $bill->discount_labels ?? [])) }}"
                                                    data-total_savings="{{ $bill->total_savings }}">
                                                <i class="ri-wallet-line me-1"></i>Make Payment
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($totalSavings > 0)
                            <div class="mt-4 p-3 rounded-3" style="background:linear-gradient(135deg,#f3e8ff,#ede9fe);border:1px solid #ddd6fe">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ri-gift-line" style="font-size:18px;color:#7c3aed"></i>
                                    <div>
                                        <span class="fw-semibold" style="color:#7c3aed">Total Savings Applied:</span>
                                        <span class="fw-bold ms-1" style="color:#7c3aed">₦{{ number_format($totalSavings, 0) }}</span>
                                        <span class="text-muted small ms-2">(Original: ₦{{ number_format($totalBillOriginal, 0) }} → Payable: ₦{{ number_format($totalBillAdjusted, 0) }})</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                    @endif
                </div>

                {{-- ════════════ RECORDS TAB ════════════ --}}
                <div class="tab-pane fade" id="tab-records">
                    <div id="recordsTableWrap">
                        @if($studentpaymentbill->isEmpty())
                            <div class="empty-state">
                                <i class="ri-receipt-line"></i>
                                <p>No pending payment records. Make a payment in the Bills tab.</p>
                            </div>
                        @else
                            @include('schoolpayment.partials.records_table', ['studentpaymentbill' => $studentpaymentbill, 'studentId' => $studentId, 'schoolclassId' => $schoolclassId, 'termid' => $termid, 'sessionid' => $sessionid])
                        @endif
                    </div>
                </div>

                {{-- ════════════ HISTORY TAB ════════════ --}}
                <div class="tab-pane fade" id="tab-history">
                    @if($paymentHistory->isEmpty())
                        <div class="empty-state">
                            <i class="ri-history-line"></i>
                            <p>No payment history found for this term and session.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table rec-table w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Bill</th><th>Bill Amount</th><th>Paid</th><th>Balance</th>
                                        <th>Method</th><th>Received By</th><th>Date</th><th>Status</th><th>Invoice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentHistory as $i => $ph)
                                    @php $histDone = ($ph->paymentStatus ?? '') === 'Completed' || ($ph->completePayment ?? 0) == 1; @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ph->title }}</div>
                                            @if($ph->description)<div class="text-muted small">{{ $ph->description }}</div>@endif
                                        </td>
                                        <td>₦{{ number_format($ph->billAmount ?? 0, 0) }}</td>
                                        <td class="text-success fw-semibold">₦{{ number_format($ph->totalAmountPaid ?? 0, 0) }}</td>
                                        <td class="{{ ($ph->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }} fw-semibold">₦{{ number_format($ph->balance ?? 0, 0) }}</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $ph->paymentMethod ?? '—' }}</span></td>
                                        <td class="text-muted small">{{ $ph->receivedBy ?? '—' }}</td>
                                        <td class="text-muted small">{{ $ph->receivedDate ? \Carbon\Carbon::parse($ph->receivedDate)->format('d M Y') : 'N/A' }}</td>
                                        <td><span class="badge {{ $histDone ? 'bg-success' : 'bg-warning text-dark' }}">{{ $histDone ? 'Completed' : 'Partial' }}</span></td>
                                        <td>
                                            <a href="{{ route('schoolpayment.invoice', ['studentId'=>$studentId,'schoolclassid'=>$ph->classId ?? $schoolclassId,'termid'=>$ph->termId ?? $termid,'sessionid'=>$ph->sessionId ?? $sessionid]) }}"
                                               class="btn btn-sm btn-outline-primary"><i class="ri-file-download-line"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('schoolpayment.statement', ['studentId'=>$studentId,'schoolclassid'=>$schoolclassId,'termid'=>$termid,'sessionid'=>$sessionid]) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="ri-file-list-3-line me-1"></i>Download Payment Statement
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @endif

</div>
</div>
</div>

{{-- ══════════════════════ SINGLE PAYMENT MODAL ══════════════════════ --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-wallet-line me-2"></i>Make Payment</h5>
            </div>
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
                        <input type="text" id="payment_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="invalid-feedback d-block" id="amountError" style="display:none!important"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select id="payment_method2" class="form-select" required>
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
                <button type="button" class="btn btn-primary" id="paySubmitBtn">
                    <i class="ri-wallet-line me-1"></i>Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════ PAY ALL BILLS MODAL ══════════════════════ --}}
<div class="modal fade" id="payAllModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px">
        <div class="modal-content">
            <div class="modal-hero-bar" style="background:linear-gradient(135deg,#1e3a5f,#1e40af)">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-stack-line me-2"></i>Pay All Bills at Once</h5>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" style="font-size:13px">
                    Enter the total payment amount below. It will be distributed across all outstanding bills
                    <strong>proportionally by balance</strong> — each bill receives the same fraction of its outstanding amount.
                </p>

                <div class="mb-3 p-3 rounded-3" style="background:#f0f9ff;border:1px solid #bae6fd">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-semibold" style="font-size:13px;color:var(--pay-primary)">Total Outstanding Balance</span>
                        <span class="fw-bold text-danger" id="payAllTotalBalance">₦0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="font-size:12px;color:var(--pay-muted)">Across <span id="payAllBillCount">0</span> unpaid bills</span>
                        <span class="text-success fw-semibold" style="font-size:12px" id="payAllSavingsNote"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text fw-semibold">₦</span>
                        <input type="text" id="payAllAmount" class="form-control" placeholder="Enter amount (up to full balance)">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="payAllFillFull"
                                style="border-radius:0 8px 8px 0;font-size:12px;padding:0 14px">Pay Full</button>
                    </div>
                    <div class="invalid-feedback d-block" id="payAllAmountError" style="display:none!important"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select id="payAllMethod" class="form-select">
                        <option value="">— Select Method —</option>
                        <option value="Bank Deposit">Bank Deposit / Teller</option>
                        <option value="School POS">School POS / Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                {{-- Distribution preview --}}
                <div>
                    <label class="form-label mb-2"><i class="ri-pie-chart-line me-1 text-primary"></i>Distribution Preview</label>
                    <div class="table-responsive" style="max-height:240px;overflow-y:auto">
                        <table class="table payall-table mb-0">
                            <thead>
                                <tr>
                                    <th>Bill</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-end">Allocation</th>
                                    <th class="text-end">Remaining</th>
                                </tr>
                            </thead>
                            <tbody id="payAllDistributionRows">
                                <tr><td colspan="4" class="text-center text-muted py-3">Enter an amount to preview distribution</td></tr>
                            </tbody>
                            <tfoot id="payAllDistributionFoot" style="display:none">
                                <tr style="background:#f8fafc;font-weight:700;font-size:12px">
                                    <td>TOTAL</td>
                                    <td class="text-end text-danger" id="payAllFootBalance">₦0</td>
                                    <td class="text-end text-success" id="payAllFootAlloc">₦0</td>
                                    <td class="text-end" id="payAllFootRemain">₦0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="payAllSubmitBtn" disabled>
                    <i class="ri-stack-line me-1"></i>Record All Payments
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════ CONFIRM DELETE MODAL ════════ --}}
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
(function () {
'use strict';

// ── Shared state ─────────────────────────────────────────────────────────
var CSRF    = document.querySelector('meta[name="csrf-token"]').content;
var STORE   = '{{ route("schoolpayment.store") }}';
var RELOAD_URL = window.location.href;

// Payable bills data from PHP (bills that are not fully paid and not invoice-pending)
var payableBills = @json($payableBills->map(function($bill) use ($studentpaymentbillbook) {
    $bookEntry  = $studentpaymentbillbook->where('school_bill_id', $bill->schoolbillid)->first();
    $amountPaid = $bookEntry ? (float) $bookEntry->amount_paid : 0;
    $balance    = max(0, (float)$bill->adjusted_amount - $amountPaid);
    return [
        'id'                    => $bill->schoolbillid,
        'title'                 => $bill->title,
        'adjusted_amount'       => (float)$bill->adjusted_amount,
        'original_amount'       => (float)$bill->original_amount,
        'amount_paid'           => $amountPaid,
        'balance'               => $balance,
        'scholarship_deduction' => (float)$bill->scholarship_deduction,
        'discount_deduction'    => (float)$bill->discount_deduction,
        'total_savings'         => (float)$bill->total_savings,
    ];
})->values());

var STUDENT_ID  = {{ $studentId }};
var CLASS_ID    = {{ $schoolclassId ?? 'null' }};
var TERM_ID     = {{ $termid }};
var SESSION_ID  = {{ $sessionid }};
var INVOICE_URL = '{{ route("schoolpayment.invoice", ["studentId"=>$studentId,"schoolclassid"=>$schoolclassId,"termid"=>$termid,"sessionid"=>$sessionid]) }}';

// ── Helpers ────────────────────────────────────────────────────────────
function fmt(n) {
    return Number(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function postJSON(url, data) {
    var body   = new FormData();
    body.append('_token', CSRF);
    Object.keys(data).forEach(function(k){ body.append(k, data[k]); });
    return fetch(url, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        body: body
    }).then(function(r) {
        if (!r.ok) return r.json().then(function(d){ var e = new Error(d.message||'Server error'); e.data=d; throw e; });
        return r.json();
    });
}

// ── Refresh bill cards & records tab via AJAX (no full page reload) ──
function refreshPaymentData(callback) {
    var overlay = document.getElementById('billsLoadingOverlay');
    if (overlay) { overlay.style.display = 'flex'; }

    fetch(RELOAD_URL, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    })
    .then(function(r){ return r.text(); })
    .then(function(html) {
        var parser   = new DOMParser();
        var newDoc   = parser.parseFromString(html, 'text/html');

        // Refresh bill cards
        var newBillsRow = newDoc.getElementById('billCardsRow');
        var curBillsRow = document.getElementById('billCardsRow');
        if (newBillsRow && curBillsRow) {
            curBillsRow.innerHTML = newBillsRow.innerHTML;
        }

        // Refresh pay-all banner
        var newBanner = newDoc.getElementById('payAllBanner');
        var curBanner = document.getElementById('payAllBanner');
        if (newBanner) {
            if (curBanner) { curBanner.outerHTML = newBanner.outerHTML; }
        } else if (curBanner) {
            curBanner.remove();
        }

        // Refresh records tab
        var newRecords = newDoc.getElementById('recordsTableWrap');
        var curRecords = document.getElementById('recordsTableWrap');
        if (newRecords && curRecords) {
            curRecords.innerHTML = newRecords.innerHTML;
        }

        // Update badges
        var nb = newDoc.getElementById('recordsBadge');
        var cb = document.getElementById('recordsBadge');
        if (nb && cb) cb.textContent = nb.textContent;

        // Update invoice button
        var newInvBtn = newDoc.getElementById('generateInvoiceBtn');
        var curInvBtn = document.getElementById('generateInvoiceBtn');
        if (newInvBtn && curInvBtn) {
            curInvBtn.disabled = newInvBtn.disabled;
            if (newInvBtn.disabled) {
                curInvBtn.classList.add('disabled');
            } else {
                curInvBtn.classList.remove('disabled');
            }
        }

        // Rebind events on fresh DOM
        bindPaymentButtons();
        bindDeleteButtons();
        bindPayAllBanner();

        if (overlay) { overlay.style.display = 'none'; }
        if (callback) callback();
    })
    .catch(function(err) {
        console.error('Refresh failed, falling back to reload', err);
        window.location.reload();
    });
}

// ── Single Payment Modal ──────────────────────────────────────────────
var singlePayData = {};

function bindPaymentButtons() {
    document.querySelectorAll('.make-payment').forEach(function(btn) {
        btn.removeEventListener('click', openSinglePayModal);
        btn.addEventListener('click', openSinglePayModal);
    });
}

function openSinglePayModal() {
    var d = {
        student_id:    this.dataset.student_id,
        amount:        parseFloat(this.dataset.amount         || 0),
        original:      parseFloat(this.dataset.original_amount|| 0),
        amount_paid:   parseFloat(this.dataset.amount_paid    || 0),
        balance:       parseFloat(this.dataset.balance         || 0),
        bill_id:       this.dataset.school_bill_id,
        class_id:      this.dataset.class_id,
        term_id:       this.dataset.term_id,
        session_id:    this.dataset.session_id,
        title:         this.dataset.title,
        schol_ded:     parseFloat(this.dataset.scholarship_deduction || 0),
        schol_label:   this.dataset.scholarship_label  || 'Scholarship',
        disc_ded:      parseFloat(this.dataset.discount_deduction    || 0),
        disc_labels:   this.dataset.discount_labels    || 'Discount',
        total_savings: parseFloat(this.dataset.total_savings         || 0),
    };
    singlePayData = d;

    document.getElementById('modal-bill-title').textContent  = d.title;
    document.getElementById('amount_d').value                = '₦' + fmt(d.amount);
    document.getElementById('amount_paid_d').value           = '₦' + fmt(d.amount_paid);
    document.getElementById('balance_d').value               = '₦' + fmt(d.balance);
    document.getElementById('payment_amount').value          = '';
    document.getElementById('payment_method2').value         = '';
    document.getElementById('payment_amount').classList.remove('is-invalid');
    document.getElementById('payment_method2').classList.remove('is-invalid');
    hideErr('amountError');

    var savBox = document.getElementById('savingsBreakdown');
    if (d.total_savings > 0) {
        savBox.classList.remove('d-none');
        var scholRow = document.getElementById('scholRow');
        var discRow  = document.getElementById('discRow');
        if (d.schol_ded > 0) {
            scholRow.classList.remove('d-none');
            document.getElementById('scholLabel').textContent = d.schol_label;
            document.getElementById('scholAmt').textContent   = '-₦' + fmt(d.schol_ded);
        } else { scholRow.classList.add('d-none'); }
        if (d.disc_ded > 0) {
            discRow.classList.remove('d-none');
            document.getElementById('discLabel').textContent = d.disc_labels;
            document.getElementById('discAmt').textContent   = '-₦' + fmt(d.disc_ded);
        } else { discRow.classList.add('d-none'); }
        document.getElementById('totalSavingsAmt').textContent = '-₦' + fmt(d.total_savings);
    } else {
        savBox.classList.add('d-none');
    }

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function hideErr(id) {
    var el = document.getElementById(id);
    if (el) { el.textContent = ''; el.style.display = 'none'; }
}
function showErr(id, msg) {
    var el = document.getElementById(id);
    if (el) { el.textContent = msg; el.style.display = 'block'; }
}

document.getElementById('payment_amount').addEventListener('input', function() {
    var val     = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
    var balance = singlePayData.balance || 0;
    if (val <= 0)        { this.classList.add('is-invalid'); showErr('amountError', 'Enter a valid amount greater than 0.'); }
    else if (val > balance) { this.classList.add('is-invalid'); showErr('amountError', 'Amount exceeds balance of ₦' + fmt(balance)); }
    else                 { this.classList.remove('is-invalid'); hideErr('amountError'); }
});

document.getElementById('paySubmitBtn').addEventListener('click', function() {
    var amtInput = document.getElementById('payment_amount');
    var val      = parseFloat(amtInput.value.replace(/[^0-9.]/g, '')) || 0;
    var balance  = singlePayData.balance || 0;
    var method   = document.getElementById('payment_method2').value;

    amtInput.classList.remove('is-invalid');
    document.getElementById('payment_method2').classList.remove('is-invalid');
    hideErr('amountError');

    if (val <= 0) {
        amtInput.classList.add('is-invalid');
        showErr('amountError', 'Enter a valid amount greater than 0.');
        return;
    }
    if (val > balance) {
        amtInput.classList.add('is-invalid');
        showErr('amountError', 'Amount exceeds balance of ₦' + fmt(balance));
        return;
    }
    if (!method) {
        document.getElementById('payment_method2').classList.add('is-invalid');
        Swal.fire({ icon:'warning', title:'Missing Method', text:'Please select a payment method.', confirmButtonColor:'#2563eb' });
        return;
    }

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

    postJSON(STORE, {
        student_id:            singlePayData.student_id,
        class_id:              singlePayData.class_id,
        term_id:               singlePayData.term_id,
        session_id:            singlePayData.session_id,
        school_bill_id:        singlePayData.bill_id,
        actual_amount:         singlePayData.original,
        adjusted_amount:       singlePayData.amount,
        balance2:              balance,
        last_amount_paid:      singlePayData.amount_paid,
        payment_amount:        val.toFixed(2),
        payment_amount2:       val.toFixed(2),
        payment_method2:       method,
        scholarship_deduction: singlePayData.schol_ded,
        discount_deduction:    singlePayData.disc_ded,
    })
    .then(function(data) {
        var modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        if (modal) modal.hide();

        if (data.success) {
            Swal.fire({ icon:'success', title:'Payment Recorded!', text: data.message, timer:2000, showConfirmButton:false })
            .then(function(){ refreshPaymentData(); });
        } else {
            var msg = data.message || 'Something went wrong.';
            if (data.errors) msg = Object.values(data.errors).flat().join('<br>');
            Swal.fire({ icon:'error', title:'Error', html: msg });
        }
    })
    .catch(function(err) {
        var msg = 'An unexpected error occurred.';
        if (err.data && err.data.message) msg = err.data.message;
        Swal.fire({ icon:'error', title:'Payment Failed', html: msg });
    })
    .finally(function() {
        btn.disabled  = false;
        btn.innerHTML = '<i class="ri-wallet-line me-1"></i>Record Payment';
    });
});

// ── Pay All Bills Logic ─────────────────────────────────────────────
function bindPayAllBanner() {
    var btn = document.getElementById('openPayAllBtn');
    if (btn) {
        btn.removeEventListener('click', openPayAllModal);
        btn.addEventListener('click', openPayAllModal);
    }
}

function openPayAllModal() {
    var totalBalance = payableBills.reduce(function(s, b){ return s + b.balance; }, 0);
    var totalSavings = payableBills.reduce(function(s, b){ return s + b.total_savings; }, 0);

    document.getElementById('payAllTotalBalance').textContent = '₦' + fmt(totalBalance);
    document.getElementById('payAllBillCount').textContent    = payableBills.length;
    document.getElementById('payAllAmount').value             = '';
    document.getElementById('payAllMethod').value             = '';
    document.getElementById('payAllSubmitBtn').disabled       = true;
    hideErr('payAllAmountError');

    if (totalSavings > 0) {
        document.getElementById('payAllSavingsNote').textContent = '₦' + fmt(totalSavings) + ' savings applied';
    } else {
        document.getElementById('payAllSavingsNote').textContent = '';
    }

    resetDistributionPreview();
    new bootstrap.Modal(document.getElementById('payAllModal')).show();
}

function resetDistributionPreview() {
    document.getElementById('payAllDistributionRows').innerHTML =
        '<tr><td colspan="4" class="text-center text-muted py-3">Enter an amount to preview distribution</td></tr>';
    document.getElementById('payAllDistributionFoot').style.display = 'none';
    document.getElementById('payAllSubmitBtn').disabled = true;
}

function computeDistribution(totalPayment) {
    var totalBalance = payableBills.reduce(function(s, b){ return s + b.balance; }, 0);
    if (totalBalance <= 0) return [];

    return payableBills.map(function(bill) {
        var share  = bill.balance / totalBalance;         // proportional weight
        var alloc  = Math.min(totalPayment * share, bill.balance);
        alloc      = Math.round(alloc * 100) / 100;       // 2dp
        var remain = Math.max(0, bill.balance - alloc);
        return { bill: bill, alloc: alloc, remain: remain };
    });
}

document.getElementById('payAllAmount').addEventListener('input', function() {
    var val          = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
    var totalBalance = payableBills.reduce(function(s, b){ return s + b.balance; }, 0);

    hideErr('payAllAmountError');
    this.classList.remove('is-invalid');
    document.getElementById('payAllSubmitBtn').disabled = true;

    if (val <= 0) {
        resetDistributionPreview();
        return;
    }
    if (val > totalBalance) {
        this.classList.add('is-invalid');
        showErr('payAllAmountError', 'Amount cannot exceed total balance of ₦' + fmt(totalBalance));
        resetDistributionPreview();
        return;
    }

    // Build preview
    var dist    = computeDistribution(val);
    var rows    = '';
    var footBal = 0, footAlloc = 0, footRemain = 0;

    dist.forEach(function(d) {
        footBal   += d.bill.balance;
        footAlloc += d.alloc;
        footRemain += d.remain;
        rows += '<tr>' +
            '<td class="fw-semibold">' + d.bill.title + '</td>' +
            '<td class="text-end text-danger">₦' + fmt(d.bill.balance) + '</td>' +
            '<td class="text-end text-success fw-semibold">₦' + fmt(d.alloc) + '</td>' +
            '<td class="text-end ' + (d.remain > 0 ? 'text-warning' : 'text-success') + '">₦' + fmt(d.remain) + '</td>' +
        '</tr>';
    });

    document.getElementById('payAllDistributionRows').innerHTML = rows;
    document.getElementById('payAllFootBalance').textContent = '₦' + fmt(footBal);
    document.getElementById('payAllFootAlloc').textContent   = '₦' + fmt(footAlloc);
    document.getElementById('payAllFootRemain').textContent  = '₦' + fmt(footRemain);
    document.getElementById('payAllDistributionFoot').style.display = '';
    document.getElementById('payAllSubmitBtn').disabled = false;
});

document.getElementById('payAllFillFull').addEventListener('click', function() {
    var totalBalance = payableBills.reduce(function(s, b){ return s + b.balance; }, 0);
    document.getElementById('payAllAmount').value = totalBalance.toFixed(2);
    document.getElementById('payAllAmount').dispatchEvent(new Event('input'));
});

document.getElementById('payAllSubmitBtn').addEventListener('click', function() {
    var val    = parseFloat(document.getElementById('payAllAmount').value.replace(/[^0-9.]/g, '')) || 0;
    var method = document.getElementById('payAllMethod').value;

    if (!method) {
        Swal.fire({ icon:'warning', title:'Missing Method', text:'Please select a payment method.', confirmButtonColor:'#2563eb' });
        return;
    }
    if (val <= 0) {
        Swal.fire({ icon:'warning', title:'Invalid Amount', text:'Enter a valid payment amount.', confirmButtonColor:'#2563eb' });
        return;
    }

    var dist = computeDistribution(val);

    Swal.fire({
        icon: 'question',
        title: 'Confirm Bulk Payment',
        html: 'Record <strong>₦' + fmt(val) + '</strong> distributed across <strong>' + payableBills.length + ' bills</strong> via <strong>' + method + '</strong>?',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Yes, Record All',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        var btn = document.getElementById('payAllSubmitBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

        // Sequential payments, one per bill
        var promises = dist.map(function(d) {
            if (d.alloc <= 0) return Promise.resolve({ success: true, skipped: true });
            return postJSON(STORE, {
                student_id:            STUDENT_ID,
                class_id:              CLASS_ID,
                term_id:               TERM_ID,
                session_id:            SESSION_ID,
                school_bill_id:        d.bill.id,
                actual_amount:         d.bill.original_amount,
                adjusted_amount:       d.bill.adjusted_amount,
                balance2:              d.bill.balance,
                last_amount_paid:      d.bill.amount_paid,
                payment_amount:        d.alloc.toFixed(2),
                payment_amount2:       d.alloc.toFixed(2),
                payment_method2:       method,
                scholarship_deduction: d.bill.scholarship_deduction,
                discount_deduction:    d.bill.discount_deduction,
            });
        });

        Promise.all(promises)
        .then(function(results) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('payAllModal'));
            if (modal) modal.hide();

            var failed = results.filter(function(r){ return !r.success && !r.skipped; });
            if (failed.length === 0) {
                Swal.fire({
                    icon: 'success', title: 'All Payments Recorded!',
                    text: '₦' + fmt(val) + ' distributed across ' + payableBills.length + ' bills successfully.',
                    timer: 2500, showConfirmButton: false
                }).then(function(){ refreshPaymentData(); });
            } else {
                Swal.fire({
                    icon: 'warning', title: 'Partial Success',
                    text: (results.length - failed.length) + ' of ' + results.length + ' payments recorded. ' + failed.length + ' failed.',
                }).then(function(){ refreshPaymentData(); });
            }
        })
        .catch(function(err) {
            var msg = err.data && err.data.message ? err.data.message : 'One or more payments failed. Please try again.';
            Swal.fire({ icon:'error', title:'Payment Error', html: msg });
        })
        .finally(function() {
            btn.disabled  = false;
            btn.innerHTML = '<i class="ri-stack-line me-1"></i>Record All Payments';
        });
    });
});

// ── Delete payment ───────────────────────────────────────────────────
var deleteUrl = '';

function bindDeleteButtons() {
    document.querySelectorAll('.delete-payment').forEach(function(btn) {
        btn.removeEventListener('click', handleDeleteClick);
        btn.addEventListener('click', handleDeleteClick);
    });
}

function handleDeleteClick() {
    deleteUrl = this.dataset.url;
    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
    if (modal) modal.hide();

    var self = this;
    self.disabled  = true;
    self.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json',
            'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.success) {
            Swal.fire({ icon:'success', title:'Deleted!', text: data.message, timer:1500, showConfirmButton:false })
            .then(function(){ refreshPaymentData(); });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(function() {
        Swal.fire('Error', 'Failed to delete payment. Please try again.', 'error');
    })
    .finally(function() {
        self.disabled  = false;
        self.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Delete';
        deleteUrl      = '';
    });
});

// ── Invoice button ───────────────────────────────────────────────────
var invBtn = document.getElementById('generateInvoiceBtn');
if (invBtn) {
    invBtn.addEventListener('click', function() {
        if (!this.disabled) window.location.href = this.dataset.invoiceUrl;
    });
}

// ── Initial bind ─────────────────────────────────────────────────────
bindPaymentButtons();
bindDeleteButtons();
bindPayAllBanner();

})();
</script>
@endsection
