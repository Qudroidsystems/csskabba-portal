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
    --pay-border:  #e2e8f0;
    --pay-bg:      #f8fafc;
    --pay-radius:  12px;
    --pay-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ───────────────────────────────── */
.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius); padding: 24px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.pay-hero::before {
    content:''; position:absolute; top:-50px; right:-50px;
    width:200px; height:200px; background:rgba(255,255,255,.06); border-radius:50%;
}
.pay-hero h1 { font-size:20px; font-weight:700; color:#fff; margin:0 0 4px; position:relative; }
.pay-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* ── Student info card ──────────────────── */
.student-card {
    background:#fff; border:1px solid var(--pay-border);
    border-radius:var(--pay-radius); padding:20px 24px; margin-bottom:20px;
    box-shadow:var(--pay-shadow);
}
.student-avatar-lg {
    width:72px; height:72px; border-radius:50%; object-fit:cover;
    border:3px solid var(--pay-border);
}
.avatar-placeholder-lg {
    width:72px; height:72px; border-radius:50%;
    background:linear-gradient(135deg,#dbeafe,#93c5fd);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:22px; font-weight:700; color:var(--pay-accent);
    border:3px solid var(--pay-border); flex-shrink:0;
}
.info-chip {
    display:inline-flex; align-items:center; gap:5px;
    background:var(--pay-bg); border:1px solid var(--pay-border);
    border-radius:8px; padding:6px 12px; font-size:12px; font-weight:600;
}
.info-chip i { opacity:.7; }

/* ── Scholarship / discount banners ─────── */
.benefit-banner {
    border-radius:10px; padding:12px 16px; margin-bottom:16px;
    display:flex; align-items:flex-start; gap:12px; font-size:13px;
}
.benefit-banner.schol {
    background:#fef9c3; border:1px solid #fde68a; color:#92400e;
}
.benefit-banner.disc {
    background:#ede9fe; border:1px solid #ddd6fe; color:#6d28d9;
}
.benefit-banner .icon { font-size:20px; flex-shrink:0; margin-top:1px; }

/* ── Bill cards ─────────────────────────── */
.bill-card {
    background:#fff; border:1px solid var(--pay-border);
    border-radius:12px; padding:18px 20px; height:100%;
    position:relative; overflow:hidden; transition:transform .15s, box-shadow .15s;
}
.bill-card:hover { transform:translateY(-2px); box-shadow:var(--pay-shadow); }
.bill-card .stripe {
    position:absolute; top:0; left:0; right:0; height:3px;
}
.bill-card.paid    .stripe { background:linear-gradient(90deg,#16a34a,#15803d); }
.bill-card.partial .stripe { background:linear-gradient(90deg,#2563eb,#1d4ed8); }
.bill-card.unpaid  .stripe { background:linear-gradient(90deg,#d97706,#b45309); }
.bill-card.savings .stripe { background:linear-gradient(90deg,#7c3aed,#6d28d9); }

.bill-amount-main { font-size:22px; font-weight:700; color:var(--pay-primary); }
.bill-mini-label  { font-size:10px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
.bill-mini-value  { font-size:13px; font-weight:700; }

.savings-pill {
    display:inline-flex; align-items:center; gap:4px;
    background:#f3e8ff; border:1px solid #e9d5ff;
    color:#7c3aed; border-radius:20px; padding:2px 9px; font-size:11px; font-weight:600;
}
.schol-pill {
    display:inline-flex; align-items:center; gap:4px;
    background:#fef9c3; border:1px solid #fde68a;
    color:#92400e; border-radius:20px; padding:2px 9px; font-size:11px; font-weight:600;
}
.disc-pill {
    display:inline-flex; align-items:center; gap:4px;
    background:#ede9fe; border:1px solid #ddd6fe;
    color:#6d28d9; border-radius:20px; padding:2px 9px; font-size:11px; font-weight:600;
}

.progress { height:6px; border-radius:10px; background:#e2e8f0; }
.progress-bar-paid    { background:linear-gradient(90deg,#16a34a,#15803d); border-radius:10px; }
.progress-bar-partial { background:linear-gradient(90deg,#2563eb,#1d4ed8); border-radius:10px; }

/* ── Tab nav ────────────────────────────── */
.nav-tabs .nav-link {
    color:var(--pay-muted); font-size:13px; font-weight:600;
    border:none; border-bottom:2px solid transparent; padding:10px 16px;
}
.nav-tabs .nav-link.active {
    color:var(--pay-accent); border-bottom-color:var(--pay-accent); background:transparent;
}

/* ── Payment Records table ──────────────── */
.rec-table th {
    background:#f8fafc; color:var(--pay-primary);
    padding:10px 14px; font-size:12px; font-weight:700; white-space:nowrap;
    border-bottom:2px solid var(--pay-border);
}
.rec-table td {
    padding:10px 14px; vertical-align:middle; font-size:13px;
    border-bottom:1px solid var(--pay-border);
}
.rec-table tr:hover td { background:#f0f9ff; }

/* ── Empty state ────────────────────────── */
.empty-state {
    text-align:center; padding:48px 24px;
}
.empty-state i { font-size:3rem; opacity:.25; color:var(--pay-muted); display:block; margin-bottom:12px; }
.empty-state p { color:var(--pay-muted); margin:0; font-size:14px; }

/* ── Payment Modal ──────────────────────── */
#paymentModal .modal-content { border:none; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.15); }
.modal-hero-bar {
    background:linear-gradient(135deg,#1e3a5f,#2563eb);
    padding:20px 28px; position:relative; overflow:hidden;
}
.modal-hero-bar::before {
    content:''; position:absolute; top:-25px; right:-25px;
    width:100px; height:100px; background:rgba(255,255,255,.07); border-radius:50%;
}
.modal-hero-bar h5 { color:#fff; font-weight:700; margin:0; font-size:15px; position:relative; }
.modal-hero-bar .btn-close { position:absolute; top:16px; right:20px; filter:invert(1); }

.savings-breakdown {
    background:linear-gradient(135deg,#f3e8ff,#ede9fe);
    border:1px solid #ddd6fe; border-radius:10px; padding:12px 16px; margin-bottom:14px;
}
.savings-breakdown .title { font-size:12px; font-weight:700; color:#7c3aed; margin-bottom:8px; }
.savings-row { display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; }
.savings-row:last-child { margin-bottom:0; border-top:1px solid #ddd6fe; padding-top:6px; font-weight:700; }

.form-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-control, .form-select {
    border:1.5px solid var(--pay-border); border-radius:8px;
    font-size:13px; padding:9px 14px; transition:border .15s;
}
.form-control:focus, .form-select:focus {
    border-color:var(--pay-accent); box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.form-control[readonly] { background:#f8fafc; cursor:default; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Messages --}}
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

    {{-- Hero --}}
    <div class="pay-hero">
        <h1><i class="ri-wallet-line me-2"></i>Payment Details</h1>
        <p>Manage school fee payments for the selected student, term, and session.</p>
    </div>

    {{-- ── Student info ── --}}
    @if($studentdata)
    @php
        $totalBillOriginal = $student_bill_info->sum('amount');
        $totalBillAdjusted = $student_bill_info->sum('adjusted_amount');
        $totalPaidSoFar    = $studentpaymentbillbook
            ->where('student_id', $studentId)
            ->where('term_id', $termid)
            ->where('session_id', $sessionid)
            ->sum('amount_paid');
        $totalOutstanding  = max(0, $totalBillAdjusted - $totalPaidSoFar);
        $totalSavings      = $student_bill_info->sum('total_savings');
    @endphp
    <div class="student-card">
        <div class="d-flex align-items-start gap-4 flex-wrap">
            {{-- Avatar --}}
            <div>
                @if($studentdata->avatar)
                    <img src="{{ Storage::url('images/studentavatar/' . $studentdata->avatar) }}"
                         alt="{{ $studentdata->firstname }}" class="student-avatar-lg">
                @else
                    <div class="avatar-placeholder-lg">
                        {{ strtoupper(substr($studentdata->firstname,0,1) . substr($studentdata->lastname,0,1)) }}
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h5 class="mb-0 fw-bold" style="color:var(--pay-primary)">
                        {{ $studentdata->firstname }} {{ $studentdata->lastname }}
                    </h5>
                    @if(($studentdata->student_status ?? '') === 'Active')
                        <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size:11px">Active</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size:11px">{{ $studentdata->student_status ?? 'Unknown' }}</span>
                    @endif
                    @if(($studentdata->statusId ?? 0) == 1)
                        <span class="badge bg-info-subtle text-info px-2 py-1" style="font-size:11px">Returning Student</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size:11px">New Student</span>
                    @endif
                </div>
                <div class="text-muted small mb-3 font-monospace">{{ $studentdata->admissionNo }}</div>

                <div class="d-flex flex-wrap gap-2">
                    <div class="info-chip">
                        <i class="ri-building-line text-success"></i>
                        {{ $studentdata->schoolclass }} {{ $studentdata->arm }}
                    </div>
                    <div class="info-chip">
                        <i class="ri-calendar-line text-primary"></i>
                        {{ $schoolterm }}
                    </div>
                    <div class="info-chip">
                        <i class="ri-time-line text-warning"></i>
                        {{ $schoolsession }}
                    </div>
                    <div class="info-chip">
                        <i class="ri-money-dollar-circle-line text-danger"></i>
                        Total: ₦{{ number_format($totalBillAdjusted, 0) }}
                    </div>
                    <div class="info-chip">
                        <i class="ri-check-line text-success"></i>
                        Paid: ₦{{ number_format($totalPaidSoFar, 0) }}
                    </div>
                    @if($totalOutstanding > 0)
                    <div class="info-chip" style="background:#fef2f2;border-color:#fecaca;color:#dc2626">
                        <i class="ri-alert-line"></i>
                        Outstanding: ₦{{ number_format($totalOutstanding, 0) }}
                    </div>
                    @else
                    <div class="info-chip" style="background:#f0fdf4;border-color:#bbf7d0;color:#16a34a">
                        <i class="ri-checkbox-circle-line"></i>Fully Paid
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap align-items-start">
                <a href="{{ route('schoolpayment.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </a>
                @if($paymentRecordsCount > 0)
                <a href="{{ route('schoolpayment.invoice', ['studentId'=>$studentId,'schoolclassid'=>$schoolclassId,'termid'=>$termid,'sessionid'=>$sessionid]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="ri-file-download-line me-1"></i>Generate Invoice
                </a>
                @else
                <button class="btn btn-primary btn-sm" disabled title="Make a payment first">
                    <i class="ri-file-download-line me-1"></i>Generate Invoice
                </button>
                @endif
                <a href="{{ route('schoolpayment.statement', ['studentId'=>$studentId,'schoolclassid'=>$schoolclassId,'termid'=>$termid,'sessionid'=>$sessionid]) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="ri-file-list-line me-1"></i>Statement
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="ri-alert-line me-2"></i>
        Student data could not be loaded. The student may not be enrolled in the current session.
        <a href="{{ route('schoolpayment.index') }}" class="alert-link ms-2">← Back to students</a>
    </div>
    @endif

    {{-- Scholarship banner --}}
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
                <strong class="ms-2">
                    Total Savings: ₦{{ number_format($totalSavings ?? 0, 0) }}
                </strong>
            </div>
        </div>
    </div>
    @endif

    {{-- Discount banners --}}
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

    {{-- ── Tabs ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom pt-3 pb-0">
            <ul class="nav nav-tabs border-0" id="payTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-bills">
                        <i class="ri-bill-line me-1"></i>School Bills
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $student_bill_info->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-records">
                        <i class="ri-receipt-line me-1"></i>Payment Records
                        @if($studentpaymentbill->count())
                            <span class="badge bg-success-subtle text-success ms-1">{{ $studentpaymentbill->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-history">
                        <i class="ri-history-line me-1"></i>History
                        @if($paymentHistory->count())
                            <span class="badge bg-info-subtle text-info ms-1">{{ $paymentHistory->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- ══════════════════ BILLS TAB ══════════════════ --}}
                <div class="tab-pane fade show active" id="tab-bills">
                    @if($student_bill_info->isEmpty())
                        <div class="empty-state">
                            <i class="ri-inbox-line"></i>
                            <p>No bills assigned for this class, term, and session.</p>
                            <p class="text-muted small mt-1">
                                Class: <strong>{{ $studentdata->schoolclass ?? '—' }} {{ $studentdata->arm ?? '' }}</strong> ·
                                Term: <strong>{{ $schoolterm }}</strong> ·
                                Session: <strong>{{ $schoolsession }}</strong>
                            </p>
                        </div>
                    @else
                        <div class="row g-3 mt-1">
                            @foreach($student_bill_info as $bill)
                            @php
                                $bookEntry  = $studentpaymentbillbook->where('school_bill_id', $bill->schoolbillid)->first();
                                $amountPaid = $bookEntry ? (float)$bookEntry->amount_paid : 0;
                                $adjustedAmt = (float)$bill->adjusted_amount;
                                $balance    = max(0, $adjustedAmt - $amountPaid);
                                $progress   = $adjustedAmt > 0 ? min(100, ($amountPaid / $adjustedAmt) * 100) : 0;
                                $isPaid     = $balance <= 0 && $amountPaid > 0;
                                $isPartial  = $amountPaid > 0 && $balance > 0;
                                $hasSavings = $bill->total_savings > 0;

                                $pendingPayment = $studentpaymentbill->where('school_bill_id', $bill->schoolbillid)->first();
                                $invoicePending = $pendingPayment && $pendingPayment->delete_status == '1';

                                $cardClass = $isPaid ? 'paid' : ($isPartial ? 'partial' : 'unpaid');
                                if ($hasSavings && !$isPaid) $cardClass = 'savings';
                            @endphp
                            <div class="col-xl-4 col-lg-6">
                                <div class="bill-card {{ $cardClass }}">
                                    <div class="stripe"></div>

                                    {{-- Header --}}
                                    <div class="d-flex align-items-start justify-content-between mb-2 mt-1">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold mb-1" style="font-size:14px;color:var(--pay-primary)">
                                                {{ $bill->title }}
                                            </div>
                                            @if($bill->description)
                                                <div class="text-muted" style="font-size:11px">{{ $bill->description }}</div>
                                            @endif
                                        </div>
                                        <div class="ms-2">
                                            @if($isPaid)
                                                <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size:10px">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Paid
                                                </span>
                                            @elseif($isPartial)
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size:10px">
                                                    <i class="ri-progress-1-line me-1"></i>Partial
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size:10px">
                                                    <i class="ri-time-line me-1"></i>Unpaid
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Savings badges --}}
                                    @if($bill->total_savings > 0)
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        @if($bill->scholarship_deduction > 0)
                                            <span class="schol-pill">
                                                <i class="ri-award-line"></i>
                                                -₦{{ number_format($bill->scholarship_deduction, 0) }} Scholarship
                                            </span>
                                        @endif
                                        @if($bill->discount_deduction > 0)
                                            <span class="disc-pill">
                                                <i class="ri-price-tag-3-line"></i>
                                                -₦{{ number_format($bill->discount_deduction, 0) }} Discount
                                            </span>
                                        @endif
                                    </div>
                                    @endif

                                    {{-- Amounts --}}
                                    <div class="text-center mb-2">
                                        @if($bill->total_savings > 0)
                                            <div class="text-muted text-decoration-line-through" style="font-size:12px">
                                                ₦{{ number_format($bill->original_amount, 0) }}
                                            </div>
                                        @endif
                                        <div class="bill-amount-main">₦{{ number_format($adjustedAmt, 0) }}</div>
                                        <div style="font-size:11px;color:var(--pay-muted)">
                                            {{ $bill->total_savings > 0 ? 'After savings' : 'Payable amount' }}
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6 text-center">
                                            <div class="bill-mini-label">Paid</div>
                                            <div class="bill-mini-value text-success">₦{{ number_format($amountPaid, 0) }}</div>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="bill-mini-label">Balance</div>
                                            <div class="bill-mini-value {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                                ₦{{ number_format($balance, 0) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Progress --}}
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1" style="font-size:10px;color:var(--pay-muted)">
                                            <span>Progress</span>
                                            <span class="fw-semibold {{ $isPaid ? 'text-success' : 'text-primary' }}">
                                                {{ number_format($progress, 0) }}%
                                            </span>
                                        </div>
                                        <div class="progress">
                                            <div class="{{ $isPaid ? 'progress-bar-paid' : 'progress-bar-partial' }}"
                                                 style="width:{{ $progress }}%;height:6px;"
                                                 role="progressbar"></div>
                                        </div>
                                    </div>

                                    {{-- Action button --}}
                                    @if($isPaid)
                                        <button class="btn btn-success btn-sm w-100" disabled>
                                            <i class="ri-checkbox-circle-line me-1"></i>Fully Paid
                                        </button>
                                    @elseif($invoicePending)
                                        <button class="btn btn-secondary btn-sm w-100" disabled
                                                title="Generate invoice first to make another payment">
                                            <i class="ri-lock-line me-1"></i>Invoice Pending
                                        </button>
                                    @else
                                        <button class="btn btn-primary btn-sm w-100 make-payment"
                                                data-student_id="{{ $studentId }}"
                                                data-amount="{{ $adjustedAmt }}"
                                                data-amount_actual="{{ $adjustedAmt }}"
                                                data-original_amount="{{ $bill->original_amount }}"
                                                data-amount_paid="{{ number_format($amountPaid, 0) }}"
                                                data-balance="{{ $balance }}"
                                                data-school_bill_id="{{ $bill->schoolbillid }}"
                                                data-class_id="{{ $schoolclassId }}"
                                                data-term_id="{{ $termid }}"
                                                data-session_id="{{ $sessionid }}"
                                                data-title="{{ $bill->title }}"
                                                data-scholarship_deduction="{{ $bill->scholarship_deduction }}"
                                                data-scholarship_label="{{ $bill->scholarship_label }}"
                                                data-discount_deduction="{{ $bill->discount_deduction }}"
                                                data-discount_labels="{{ implode(', ', $bill->discount_labels ?? []) }}"
                                                data-total_savings="{{ $bill->total_savings }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentModal">
                                            <i class="ri-wallet-line me-1"></i>Make Payment
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Total savings summary --}}
                        @if(isset($totalSavings) && $totalSavings > 0)
                        <div class="mt-4 p-3 rounded-3" style="background:linear-gradient(135deg,#f3e8ff,#ede9fe);border:1px solid #ddd6fe">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-gift-line" style="font-size:18px;color:#7c3aed"></i>
                                <div>
                                    <span class="fw-semibold" style="color:#7c3aed">Total Savings Applied:</span>
                                    <span class="fw-bold ms-1" style="color:#7c3aed">₦{{ number_format($totalSavings, 0) }}</span>
                                    <span class="text-muted small ms-2">
                                        (Original: ₦{{ number_format($totalBillOriginal, 0) }} → Payable: ₦{{ number_format($totalBillAdjusted, 0) }})
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>

                {{-- ══════════════════ RECORDS TAB ══════════════════ --}}
                <div class="tab-pane fade" id="tab-records">
                    @if($studentpaymentbill->isEmpty())
                        <div class="empty-state">
                            <i class="ri-receipt-line"></i>
                            <p>No pending payment records. Make a payment in the Bills tab.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table rec-table w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bill</th>
                                        <th>Bill Amount</th>
                                        <th>Amount Paid</th>
                                        <th>Balance</th>
                                        <th>Method</th>
                                        <th>Received By</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($studentpaymentbill as $i => $sp)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $sp->title }}</div>
                                            <div class="text-muted small">{{ $sp->description }}</div>
                                        </td>
                                        <td>₦{{ number_format($sp->billAmount, 0) }}</td>
                                        <td class="text-success fw-semibold">₦{{ number_format($sp->totalAmountPaid ?? 0, 0) }}</td>
                                        <td class="{{ ($sp->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                            ₦{{ number_format($sp->balance ?? 0, 0) }}
                                        </td>
                                        <td>
                                            <span class="badge
                                                {{ $sp->paymentMethod === 'Bank Transfer' ? 'bg-primary-subtle text-primary' :
                                                   ($sp->paymentMethod === 'School POS'   ? 'bg-success-subtle text-success' :
                                                   'bg-secondary-subtle text-secondary') }}">
                                                {{ $sp->paymentMethod }}
                                            </span>
                                        </td>
                                        <td>{{ $sp->receivedBy }}</td>
                                        <td>
                                            <span class="text-muted small">
                                                {{ $sp->receivedDate ? \Carbon\Carbon::parse($sp->receivedDate)->format('d M Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $sp->paymentStatus === 'Completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $sp->paymentStatus }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-danger delete-payment"
                                                    data-url="{{ route('schoolpayment.deletestudentpayment', ['recordId'=>$sp->recordId]) }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- ══════════════════ HISTORY TAB ══════════════════ --}}
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
                                        <th>#</th>
                                        <th>Bill</th>
                                        <th>Bill Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Method</th>
                                        <th>Received By</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Invoice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentHistory as $i => $ph)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ph->title }}</div>
                                            <div class="text-muted small">{{ $ph->description }}</div>
                                        </td>
                                        <td>₦{{ number_format($ph->billAmount, 0) }}</td>
                                        <td class="text-success fw-semibold">₦{{ number_format($ph->totalAmountPaid ?? 0, 0) }}</td>
                                        <td class="{{ ($ph->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                            ₦{{ number_format($ph->balance ?? 0, 0) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $ph->paymentMethod }}</span>
                                        </td>
                                        <td>{{ $ph->receivedBy }}</td>
                                        <td>
                                            <span class="text-muted small">
                                                {{ $ph->receivedDate ? \Carbon\Carbon::parse($ph->receivedDate)->format('d M Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ ($ph->paymentStatus ?? '') === 'Completed' || ($ph->completePayment ?? 0) == 1 ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ($ph->paymentStatus ?? '') === 'Completed' || ($ph->completePayment ?? 0) == 1 ? 'Completed' : 'Partial' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('schoolpayment.invoice', ['studentId'=>$studentId,'schoolclassid'=>$ph->classId,'termid'=>$ph->termId,'sessionid'=>$ph->sessionId]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-file-download-line"></i>
                                            </a>
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

            </div>{{-- /tab-content --}}
        </div>{{-- /card-body --}}
    </div>{{-- /card --}}

</div>
</div>
</div>

{{-- ══════════════════════════ PAYMENT MODAL ══════════════════════════ --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-wallet-line me-2"></i>Make Payment</h5>
            </div>
            <form id="paymentForm" action="{{ route('schoolpayment.store') }}" method="POST">
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
                    {{-- Bill name --}}
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="fw-semibold" style="color:var(--pay-primary);font-size:15px" id="modal-bill-title">—</div>
                    </div>

                    {{-- Savings breakdown --}}
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
                            <span style="color:#7c3aed" id="totalSavingsAmt">-₦0</span>
                        </div>
                    </div>

                    {{-- Read-only info --}}
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
                        <input type="text" id="balance_d" class="form-control" readonly
                               style="background:#fff3cd;font-weight:700">
                        <div class="form-text small text-muted">Payment cannot exceed this balance.</div>
                    </div>

                    {{-- Payment amount --}}
                    <div class="mb-3">
                        <label class="form-label">Enter Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="text" id="payment_amount" name="payment_amount"
                                   class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="invalid-feedback" id="amountError"></div>
                    </div>

                    {{-- Payment method --}}
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

                    <div class="alert alert-danger d-none" id="formErrors"></div>
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

{{-- ══════════════════════ CONFIRM DELETE MODAL ══════════════════════ --}}
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

    function fmt(n) {
        return Number(n).toLocaleString('en-NG', { minimumFractionDigits: 0 });
    }

    // ── Populate payment modal ──────────────────────────────────────────
    document.querySelectorAll('.make-payment').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const d = {
                student_id:    this.dataset.student_id,
                amount:        parseFloat(this.dataset.amount),
                original:      parseFloat(this.dataset.original_amount),
                amount_paid:   parseFloat((this.dataset.amount_paid || '0').replace(/,/g, '')),
                balance:       parseFloat(this.dataset.balance),
                bill_id:       this.dataset.school_bill_id,
                class_id:      this.dataset.class_id,
                term_id:       this.dataset.term_id,
                session_id:    this.dataset.session_id,
                title:         this.dataset.title,
                schol_ded:     parseFloat(this.dataset.scholarship_deduction || 0),
                schol_label:   this.dataset.scholarship_label || 'Scholarship',
                disc_ded:      parseFloat(this.dataset.discount_deduction || 0),
                disc_labels:   this.dataset.discount_labels || 'Discount',
                total_savings: parseFloat(this.dataset.total_savings || 0),
            };

            document.getElementById('modal-bill-title').textContent       = d.title;
            document.getElementById('student_id').value                   = d.student_id;
            document.getElementById('class_id').value                     = d.class_id;
            document.getElementById('term_id').value                      = d.term_id;
            document.getElementById('session_id').value                   = d.session_id;
            document.getElementById('school_bill_id').value               = d.bill_id;
            document.getElementById('actual_amount').value                = d.original;
            document.getElementById('adjusted_amount').value              = d.amount;
            document.getElementById('balance2').value                     = d.balance;
            document.getElementById('last_amount_paid').value             = d.amount_paid;
            document.getElementById('scholarship_deduction').value        = d.schol_ded;
            document.getElementById('discount_deduction').value           = d.disc_ded;
            document.getElementById('amount_d').value                     = '₦' + fmt(d.amount);
            document.getElementById('amount_paid_d').value                = '₦' + fmt(d.amount_paid);
            document.getElementById('balance_d').value                    = '₦' + fmt(d.balance);
            document.getElementById('payment_amount').value               = '';
            document.getElementById('payment_amount2').value              = '';
            document.getElementById('payment_method2').value              = '';
            document.getElementById('formErrors').classList.add('d-none');
            document.getElementById('payment_amount').classList.remove('is-invalid');
            document.getElementById('amountError').textContent            = '';

            // Savings breakdown
            const savBox = document.getElementById('savingsBreakdown');
            if (d.total_savings > 0) {
                savBox.classList.remove('d-none');
                const scholRow = document.getElementById('scholRow');
                const discRow  = document.getElementById('discRow');
                if (d.schol_ded > 0) {
                    scholRow.classList.remove('d-none');
                    document.getElementById('scholLabel').textContent = d.schol_label;
                    document.getElementById('scholAmt').textContent   = '-₦' + fmt(d.schol_ded);
                } else { scholRow.classList.add('d-none'); }
                if (d.disc_ded > 0) {
                    discRow.classList.remove('d-none');
                    document.getElementById('discLabel').textContent  = d.disc_labels;
                    document.getElementById('discAmt').textContent    = '-₦' + fmt(d.disc_ded);
                } else { discRow.classList.add('d-none'); }
                document.getElementById('totalSavingsAmt').textContent = '-₦' + fmt(d.total_savings);
            } else {
                savBox.classList.add('d-none');
            }
        });
    });

    // ── Validate payment amount input ───────────────────────────────────
    document.getElementById('payment_amount')?.addEventListener('input', function () {
        const val     = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
        const balance = parseFloat(document.getElementById('balance2').value) || 0;
        const err     = document.getElementById('amountError');
        if (val <= 0) {
            this.classList.add('is-invalid');
            err.textContent = 'Enter a valid amount greater than 0.';
        } else if (val > balance) {
            this.classList.add('is-invalid');
            err.textContent = 'Amount cannot exceed outstanding balance of ₦' + fmt(balance) + '.';
        } else {
            this.classList.remove('is-invalid');
            err.textContent = '';
        }
        document.getElementById('payment_amount2').value = val > 0 ? val.toFixed(2) : '';
    });

    // ── Form submit ─────────────────────────────────────────────────────
    document.getElementById('paymentForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const amtInput = document.getElementById('payment_amount');
        const val      = parseFloat(amtInput.value.replace(/[^0-9.]/g, '')) || 0;
        const balance  = parseFloat(document.getElementById('balance2').value) || 0;
        const method   = document.getElementById('payment_method2').value;

        document.getElementById('amountError').textContent = '';
        amtInput.classList.remove('is-invalid');
        document.getElementById('payment_method2').classList.remove('is-invalid');
        document.getElementById('formErrors').classList.add('d-none');

        if (val <= 0) {
            amtInput.classList.add('is-invalid');
            document.getElementById('amountError').textContent = 'Enter a valid amount greater than 0.';
            return;
        }
        if (val > balance) {
            amtInput.classList.add('is-invalid');
            document.getElementById('amountError').textContent = 'Amount cannot exceed outstanding balance of ₦' + fmt(balance) + '.';
            return;
        }
        if (!method) {
            document.getElementById('payment_method2').classList.add('is-invalid');
            Swal.fire({ icon:'warning', title:'Missing Information', text:'Please select a payment method.', confirmButtonText:'OK' });
            return;
        }

        document.getElementById('payment_amount2').value = val.toFixed(2);

        const btn = document.getElementById('paySubmitBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            if (!response.ok) {
                return response.json().then(function (data) {
                    throw { status: response.status, data: data };
                });
            }
            return response.json();
        })
        .then(function (data) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            if (modal) modal.hide();

            if (data.success) {
                Swal.fire({ icon:'success', title:'Success!', text: data.message, timer:2000, showConfirmButton:false })
                    .then(function () {
                        window.location.href = data.redirect_url || window.location.href;
                    });
            } else {
                let msg = data.message || 'Something went wrong.';
                if (data.errors) msg = Object.values(data.errors).flat().join('<br>');
                Swal.fire({ icon:'error', title:'Error', html: msg, confirmButtonText:'OK' });
            }
        })
        .catch(function (error) {
            let msg = 'An unexpected error occurred. Please try again.';
            if (error.data && error.data.message) msg = error.data.message;
            Swal.fire({ icon:'error', title:'Payment Failed', html: msg, confirmButtonText:'OK' });
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });

    // ── Delete payment ──────────────────────────────────────────────────
    let deleteUrl = '';
    document.querySelectorAll('.delete-payment').forEach(function (btn) {
        btn.addEventListener('click', function () {
            deleteUrl = this.dataset.url;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        });
    });

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function () {
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
        if (modal) modal.hide();

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

        const self = this;
        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                Swal.fire({ icon:'success', title:'Deleted!', text: data.message, timer:1500, showConfirmButton:false })
                    .then(function () { location.reload(); });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(function () { Swal.fire('Error', 'Failed to delete payment.', 'error'); })
        .finally(function () {
            self.disabled = false;
            self.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Delete';
            deleteUrl = '';
        });
    });

});
</script>
@endsection
