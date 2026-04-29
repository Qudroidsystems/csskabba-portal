@extends('layouts.master')

@section('content')
<style>
    :root {
        --tb-primary: #0d6efd;
        --tb-secondary: #1e3a5f;
        --tb-success: #198754;
        --tb-danger: #dc3545;
        --tb-warning: #ffc107;
        --tb-info: #0dcaf0;
        --tb-light: #f8f9fa;
        --tb-dark: #212529;
        --tb-border: #dee2e6;
        --tb-success-subtle: rgba(25, 135, 84, 0.1);
        --tb-primary-subtle: rgba(13, 110, 253, 0.1);
    }

    /* Professional Invoice Styles */
    .invoice-wrapper {
        background: #f0f2f5;
        padding: 30px 0;
        min-height: 100vh;
    }

    .invoice-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        background: #fff;
        transition: all 0.3s ease;
    }

    .invoice-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d6efd 100%);
        padding: 30px 40px;
        position: relative;
        overflow: hidden;
    }

    .invoice-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .invoice-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
    }

    .invoice-logo img {
        height: 50px;
        filter: brightness(0) invert(1);
    }

    .invoice-title {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 1px;
    }

    .invoice-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        margin-top: 5px;
    }

    .invoice-body {
        padding: 40px;
    }

    /* Student Profile Section */
    .student-profile-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .student-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background: #fff;
    }

    .avatar-placeholder-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        color: #fff;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .student-info-card {
        background: #fff;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .student-info-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .student-info-value {
        font-size: 14px;
        font-weight: 600;
        color: #1e3a5f;
        margin-bottom: 0;
    }

    /* Meta Info Grid */
    .meta-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .meta-card {
        background: #fff;
        border-radius: 12px;
        padding: 15px 20px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .meta-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.1);
    }

    .meta-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .meta-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e3a5f;
        margin-bottom: 0;
    }

    .meta-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .meta-badge-success {
        background: #d1e7dd;
        color: #0f5132;
    }

    .meta-badge-warning {
        background: #fff3cd;
        color: #856404;
    }

    /* Invoice Table */
    .invoice-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 30px;
    }

    .invoice-table thead th {
        background: #1e3a5f;
        color: #fff;
        padding: 15px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .invoice-table thead th:first-child {
        border-radius: 10px 0 0 10px;
    }

    .invoice-table thead th:last-child {
        border-radius: 0 10px 10px 0;
    }

    .invoice-table tbody tr {
        transition: background 0.2s ease;
    }

    .invoice-table tbody tr:hover {
        background: #f8f9fa;
    }

    .invoice-table tbody td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        font-size: 13px;
        vertical-align: middle;
    }

    /* Payment Method Badges */
    .payment-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .payment-badge-transfer {
        background: #e7f1ff;
        color: #084298;
    }

    .payment-badge-pos {
        background: #d1e7dd;
        color: #0f5132;
    }

    .payment-badge-deposit {
        background: #fff3cd;
        color: #856404;
    }

    .payment-badge-cheque {
        background: #e2e3e5;
        color: #41464b;
    }

    /* Totals Panel */
    .totals-panel {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 25px;
        margin-top: 20px;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .totals-row:last-child {
        border-bottom: none;
    }

    .totals-label {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
    }

    .totals-value {
        font-size: 16px;
        font-weight: 700;
        color: #1e3a5f;
    }

    .totals-grand {
        background: linear-gradient(135deg, #1e3a5f, #0d6efd);
        color: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        margin-top: 15px;
    }

    .totals-grand .totals-label,
    .totals-grand .totals-value {
        color: #fff;
    }

    /* Footer Section */
    .invoice-footer {
        background: #f8f9fa;
        padding: 25px 40px;
        text-align: center;
        border-top: 1px solid #e9ecef;
    }

    .invoice-signature {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px dashed #dee2e6;
    }

    .signature-line {
        display: inline-block;
        width: 200px;
        border-bottom: 2px solid #1e3a5f;
        margin-top: 30px;
    }

    /* Action Buttons */
    .action-buttons {
        position: sticky;
        top: 20px;
        z-index: 100;
        margin-bottom: 20px;
    }

    /* School Header */
    .school-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }

    .school-info {
        text-align: right;
    }

    .school-info h6 {
        margin-bottom: 5px;
        font-size: 12px;
    }

    .school-info .text-muted {
        color: #6c757d !important;
    }

    .address-wrap {
        max-width: 250px;
        display: inline-block;
        word-break: break-word;
    }

    /* Print Styles */
    @media print {
        .action-buttons,
        .d-print-none {
            display: none !important;
        }

        .invoice-wrapper {
            background: #fff;
            padding: 0;
        }

        .invoice-card {
            box-shadow: none;
            border-radius: 0;
        }

        .invoice-header {
            background: #1e3a5f;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .meta-card,
        .student-profile-section,
        .totals-panel,
        .invoice-footer {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-table thead th {
            background: #1e3a5f;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .totals-grand {
            background: #1e3a5f;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .invoice-header {
            padding: 20px;
        }

        .invoice-body {
            padding: 20px;
        }

        .meta-info-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .student-profile-section .row {
            flex-direction: column;
            text-align: center;
        }

        .school-header {
            flex-direction: column;
        }

        .school-info {
            text-align: left;
            margin-top: 15px;
        }

        .address-wrap {
            max-width: 100%;
        }
    }
</style>

<div class="invoice-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-xl-11">

                {{-- Action Buttons --}}
                <div class="action-buttons d-flex gap-2 justify-content-end mb-3">
                    <a href="{{ route('schoolpayment.termsessionpayments', [
                        'studentId' => $studentId,
                        'termid' => $termId,
                        'sessionid' => $sessionId,
                    ]) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="button" id="print-button" class="btn btn-primary btn-sm">
                        <i class="fas fa-print me-1"></i> Print Invoice
                    </button>
                    <button type="button" id="download-button" class="btn btn-success btn-sm">
                        <i class="fas fa-download me-1"></i> Download PDF
                    </button>
                </div>

                {{-- Invoice Card --}}
                <div class="invoice-card" id="invoice">

                    {{-- Header --}}
                    <div class="invoice-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="invoice-logo">
                                    @if($schoolInfo && $schoolInfo->logo_url)
                                        <img src="{{ $schoolInfo->logo_url }}" alt="School Logo">
                                    @else
                                        <h2 class="invoice-title">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</h2>
                                    @endif
                                </div>
                                <p class="invoice-subtitle mt-2">Official Fee Invoice</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h1 class="invoice-title">INVOICE</h1>
                                <p class="invoice-subtitle">#{{ $invoiceNumber }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="invoice-body">

                        {{-- Student Profile Section --}}
                        @php
                            $student = $studentdata->first() ?? null;
                            $avatarUrl = null;
                            if($student && $student->avatar && $student->avatar != 'unnamed.jpg' && $student->avatar != '') {
                                $avatarUrl = asset('storage/images/student_avatars/' . $student->avatar);
                            }
                            $initials = '';
                            if($student) {
                                $firstName = $student->firstname ?? '';
                                $lastName = $student->lastname ?? '';
                                $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
                                if(empty($initials)) $initials = 'ST';
                            }
                        @endphp

                        <div class="student-profile-section">
                            <div class="row align-items-center">
                                <div class="col-auto text-center">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}"
                                             alt="{{ $student->firstname ?? '' }} {{ $student->lastname ?? '' }}"
                                             class="student-avatar-large"
                                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="avatar-placeholder-large" style="display: none;">{{ $initials }}</div>
                                    @else
                                        <div class="avatar-placeholder-large">{{ $initials }}</div>
                                    @endif
                                </div>
                                <div class="col">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="student-info-card">
                                                <div class="student-info-label">Student Name</div>
                                                <div class="student-info-value">{{ $student->lastname ?? '' }} {{ $student->firstname ?? '' }}</div>
                                            </div>
                                            <div class="student-info-card">
                                                <div class="student-info-label">Admission Number</div>
                                                <div class="student-info-value">{{ $student->admissionNo ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="student-info-card">
                                                <div class="student-info-label">Class & Arm</div>
                                                <div class="student-info-value">{{ $student->schoolclass ?? '' }} {{ $student->arm ?? '' }}</div>
                                            </div>
                                            <div class="student-info-card">
                                                <div class="student-info-label">Academic Session</div>
                                                <div class="student-info-value">{{ $schoolterm ?? 'N/A' }} Term, {{ $schoolsession ?? 'N/A' }} Session</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- School Information Row --}}
                        <div class="school-header">
                            <div>
                                <h6><span class="text-muted fw-normal">Invoice No:</span> {{ $invoiceNumber }}</h6>
                                <h6><span class="text-muted fw-normal">Date Issued:</span> {{ \Carbon\Carbon::now()->format('d F, Y') }}</h6>
                                <h6><span class="text-muted fw-normal">Due Date:</span> {{ \Carbon\Carbon::now()->addDays(7)->format('d F, Y') }}</h6>
                            </div>
                            <div class="school-info">
                                <h6><span class="text-muted fw-normal">Email:</span> {{ $schoolInfo->school_email ?? 'info@school.edu' }}</h6>
                                <h6><span class="text-muted fw-normal">Phone:</span> {{ $schoolInfo->school_phone ?? 'N/A' }}</h6>
                                <h6><span class="text-muted fw-normal">Address:</span> <span class="address-wrap">{{ $schoolInfo->school_address ?? 'School Address' }}</span></h6>
                            </div>
                        </div>

                        {{-- Meta Information Grid --}}
                        <div class="meta-info-grid">
                            <div class="meta-card">
                                <div class="meta-label">Invoice Date</div>
                                <div class="meta-value">{{ \Carbon\Carbon::now()->format('d F, Y') }}</div>
                            </div>
                            <div class="meta-card">
                                <div class="meta-label">Due Date</div>
                                <div class="meta-value">{{ \Carbon\Carbon::now()->addDays(7)->format('d F, Y') }}</div>
                            </div>
                            <div class="meta-card">
                                <div class="meta-label">Payment Status</div>
                                <div class="meta-value">
                                    <span class="meta-badge {{ $totalOutstanding == 0 ? 'meta-badge-success' : 'meta-badge-warning' }}">
                                        {{ $totalOutstanding == 0 ? 'FULLY PAID' : 'PARTIALLY PAID' }}
                                    </span>
                                </div>
                            </div>
                            <div class="meta-card">
                                <div class="meta-label">Total Bill Amount</div>
                                <div class="meta-value">₦{{ number_format($totalBillAmount, 2) }}</div>
                            </div>
                        </div>

                        {{-- Invoice Table --}}
                        <div class="table-responsive">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="30%">Bill Description</th>
                                        <th width="10%" class="text-end">Amount (₦)</th>
                                        <th width="10%" class="text-end">Previous Paid (₦)</th>
                                        <th width="10%" class="text-end">Today's Payment (₦)</th>
                                        <th width="10%" class="text-end">Total Paid (₦)</th>
                                        <th width="15%">Payment Method</th>
                                        <th width="10%" class="text-end">Balance (₦)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $counter = 1; @endphp
                                    @forelse($studentpaymentbill as $sp)
                                    <tr>
                                        <td class="text-center">{{ $counter++ }}</td>
                                        <td>
                                            <strong>{{ $sp->title }}</strong>
                                            @if($sp->description)
                                                <br><small class="text-muted">{{ $sp->description }}</small>
                                            @endif
                                            @if(isset($sp->total_savings) && $sp->total_savings > 0)
                                                <br><small class="text-success">
                                                    <i class="fas fa-gift me-1"></i>Savings: ₦{{ number_format($sp->total_savings, 2) }}
                                                </small>
                                            @endif
                                         </td>
                                        <td class="text-end">₦{{ number_format($sp->amount, 2) }}</td>
                                        <td class="text-end">₦{{ number_format($sp->previousPaid, 2) }}</td>
                                        <td class="text-end text-success">₦{{ number_format($sp->todayPaid, 2) }}</td>
                                        <td class="text-end">₦{{ number_format($sp->amountPaid, 2) }}</td>
                                        <td class="text-center">
                                            @php $method = $sp->paymentMethod ?? 'N/A'; @endphp
                                            <span class="payment-badge
                                                @if($method === 'Bank Transfer') payment-badge-transfer
                                                @elseif(in_array($method, ['School POS','Cash'])) payment-badge-pos
                                                @elseif($method === 'Bank Deposit') payment-badge-deposit
                                                @elseif($method === 'Cheque') payment-badge-cheque
                                                @else payment-badge-deposit
                                                @endif">
                                                <i class="fas
                                                    @if($method === 'Bank Transfer') fa-university
                                                    @elseif(in_array($method, ['School POS','Cash'])) fa-credit-card
                                                    @elseif($method === 'Bank Deposit') fa-money-bill
                                                    @elseif($method === 'Cheque') fa-file-invoice
                                                    @else fa-receipt
                                                    @endif me-1"></i>
                                                {{ $method }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold {{ $sp->balance > 0 ? 'text-danger' : 'text-success' }}">
                                            ₦{{ number_format($sp->balance, 2) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">No payment records found for this student.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Totals Section --}}
                        <div class="totals-panel">
                            <div class="totals-row">
                                <span class="totals-label">Subtotal (Bill Amount)</span>
                                <span class="totals-value">₦{{ number_format($totalBillAmount, 2) }}</span>
                            </div>
                            @if(isset($totalSavings) && $totalSavings > 0)
                            <div class="totals-row">
                                <span class="totals-label text-success">Total Savings Applied</span>
                                <span class="totals-value text-success">-₦{{ number_format($totalSavings, 2) }}</span>
                            </div>
                            @endif
                            <div class="totals-row">
                                <span class="totals-label">Total Previous Payments</span>
                                <span class="totals-value">₦{{ number_format($totalPreviousPaid, 2) }}</span>
                            </div>
                            <div class="totals-row">
                                <span class="totals-label text-success">Today's Payment</span>
                                <span class="totals-value text-success">₦{{ number_format($totalTodayPaid, 2) }}</span>
                            </div>
                            <div class="totals-row">
                                <span class="totals-label">Total Amount Paid</span>
                                <span class="totals-value">₦{{ number_format($totalPaid, 2) }}</span>
                            </div>
                            <div class="totals-grand">
                                <div class="totals-row" style="border-bottom: none;">
                                    <span class="totals-label fw-bold">OUTSTANDING BALANCE</span>
                                    <span class="totals-value fw-bold">₦{{ number_format($totalOutstanding, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Details --}}
                        @if($studentpaymentbill->isNotEmpty())
                        @php $lastPayment = $studentpaymentbill->first(); @endphp
                        <div class="mt-4">
                            <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fas fa-info-circle me-1"></i> Latest Payment Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="student-info-card">
                                        <div class="student-info-label">Payment Method</div>
                                        <div class="student-info-value">{{ $lastPayment->paymentMethod ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="student-info-card">
                                        <div class="student-info-label">Received By</div>
                                        <div class="student-info-value">{{ $lastPayment->receivedBy ?? $lastPayment->recievedBy ?? 'School Administration' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="student-info-card">
                                        <div class="student-info-label">Payment Date</div>
                                        <div class="student-info-value">{{ $lastPayment->paymentDate ? \Carbon\Carbon::parse($lastPayment->paymentDate)->format('d F, Y') : date('d F, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>{{-- /.invoice-body --}}

                    {{-- Footer --}}
                    <div class="invoice-footer">
                        <div class="row">
                            <div class="col-md-6 text-md-start">
                                <p class="mb-2">
                                    <i class="fas fa-gavel me-1"></i>
                                    This is a computer-generated invoice and requires no signature.
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $schoolInfo->school_email ?? 'info@school.edu' }} |
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $schoolInfo->school_phone ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="invoice-signature">
                                    <div class="signature-line"></div>
                                    <p class="mt-2 mb-0 fw-semibold">Authorized Signatory</p>
                                    <p class="small text-muted mt-1">
                                        {{ $schoolInfo->school_name ?? 'School Name' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 text-center">
                            <p class="small text-muted mb-0">
                                <i class="fas fa-heart text-danger me-1"></i>
                                Thank you for your partnership in education
                            </p>
                        </div>
                    </div>

                </div>{{-- /.invoice-card --}}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const originalTitle = document.title;
    const studentName = @json($studentdata->isNotEmpty() ? $studentdata->first()->firstname . '_' . $studentdata->first()->lastname : 'Student');
    const invoiceNumber = @json($invoiceNumber ?? 'INV-000');
    const cleanName = studentName.replace(/\s+/g, '_');
    const cleanInvoice = invoiceNumber.replace(/[^a-zA-Z0-9\-]/g, '');
    const customFilename = cleanName + '_' + cleanInvoice;

    function handlePrint() {
        document.title = customFilename;
        setTimeout(() => {
            window.print();
            setTimeout(() => { document.title = originalTitle; }, 1000);
        }, 100);
    }

    document.getElementById('print-button')?.addEventListener('click', handlePrint);

    document.getElementById('download-button')?.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Downloading...';
        window.location.assign('{{ url()->current() }}?download_pdf=1');
        setTimeout(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-download me-1"></i> Download PDF';
        }, 2000);
    });

    window.addEventListener('beforeprint', () => { document.title = customFilename; });
    window.addEventListener('afterprint', () => { setTimeout(() => { document.title = originalTitle; }, 500); });
});
</script>
@endsection
