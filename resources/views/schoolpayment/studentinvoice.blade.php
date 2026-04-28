{{-- resources/views/schoolpayment/studentinvoice.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --tb-primary: #009ef7;
    --tb-secondary: #3b82f6;
    --tb-success: #50cd89;
    --tb-light: #f5f8fa;
    --tb-success-subtle: rgba(80,205,137,0.1);
}

.card {
    border: none; border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,.1);
    overflow: hidden; max-width: 100%;
}
.invoice-effect-top { z-index: 0; }
.card-body {
    z-index: 1; position: relative; padding: 1.5rem;
    font-size: 14px; font-weight: 700; color: #000;
}
.card-logo { height: 28px; }
.fs-md  { font-size: 1.25rem !important; font-weight: 800; color: #000; }
.fs-xxs { font-size: 0.75rem !important; font-weight: 700; color: #000; }

.table-borderless th,
.table-borderless td {
    border: none; padding: 0.75rem 1rem;
    vertical-align: middle; font-size: 14px;
    font-weight: 700; color: #000;
}
.table-nowrap th,
.table-nowrap td { white-space: nowrap; }
.table-light { background-color: var(--tb-light); }
.border-top-dashed { border-top: 1px dashed #dee2e6 !important; }

.alert-danger {
    background-color: rgba(241,65,108,.1);
    border-color: #f1416c; color: #f1416c;
    padding: .75rem; font-weight: 700;
}

.invoice-signature img { height: 30px; }
.hstack { display:flex; flex-direction:row; align-items:center; gap:.5rem; }
.d-print-none { display:flex !important; }
.table-responsive { overflow-x:auto; -webkit-overflow-scrolling:touch; }
table { width:100%; table-layout:auto; }

.student-avatar { width:50px; height:50px; object-fit:cover; border-radius:50%; }

.address-wrap {
    overflow-wrap: break-word; word-break: break-word;
    hyphens: auto; max-width: 200px;
    display: inline-block; font-weight: 700; color: #000;
}
.text-muted { color:#000 !important; font-weight:700; }

@media print {
    html, body {
        background:#fff; margin:0; padding:0;
        width:210mm; height:297mm;
        font-size:14px; line-height:1.4;
        font-weight:700; color:#000;
    }
    .main-content,.page-content,.container-fluid { padding:0!important; margin:0!important; width:100%!important; }
    .card { box-shadow:none; max-width:100%; width:100%; border-radius:0; margin:0; padding:0; page-break-inside:avoid; }
    .card-body { padding:.5cm!important; font-size:13px; font-weight:700; color:#000; }
    .d-print-none,.alert { display:none!important; }
    .invoice-effect-top { display:none!important; }
    h6 { font-size:12px!important; margin-bottom:3px!important; line-height:1.3; font-weight:800; color:#000; }
    h5.fs-md { font-size:13px!important; margin-bottom:3px!important; line-height:1.3; font-weight:800; color:#000; }
    p { margin-bottom:4px!important; font-size:11px!important; line-height:1.3; font-weight:700; color:#000; }
    .text-uppercase { font-size:10px!important; letter-spacing:.6px; font-weight:700; color:#000; }
    .table-responsive { overflow:visible!important; margin-top:12px!important; }
    table { table-layout:fixed; font-size:11px!important; margin-bottom:10px!important; font-weight:700; color:#000; }
    .table-borderless th,.table-borderless td { padding:4px 5px!important; font-weight:700; color:#000; border:none!important; }
    thead th { font-size:10px!important; font-weight:800; background-color:#f8f9fa!important; padding:5px 4px!important; color:#000; }
    tbody td { font-size:10px!important; line-height:1.2; font-weight:700; color:#000; }
    .table th:nth-child(1),.table td:nth-child(1){width:6%}
    .table th:nth-child(2),.table td:nth-child(2){width:25%}
    .table th:nth-child(3),.table td:nth-child(3){width:12%}
    .table th:nth-child(4),.table td:nth-child(4){width:12%}
    .table th:nth-child(5),.table td:nth-child(5){width:12%}
    .table th:nth-child(6),.table td:nth-child(6){width:12%}
    .table th:nth-child(7),.table td:nth-child(7){width:13%}
    .table th:nth-child(8),.table td:nth-child(8){width:8%}
    .badge { font-size:9px!important; padding:3px 5px!important; font-weight:700; }
    #products-list-total table { width:280px!important; font-size:10px!important; }
    #products-list-total td,#products-list-total th { padding:3px 5px!important; font-size:10px!important; font-weight:700; color:#000; }
    .student-avatar { width:25px!important; height:25px!important; margin-bottom:3px!important; }
    .address-wrap { max-width:120px!important; font-size:10px!important; line-height:1.2; font-weight:700; color:#000; }
    .invoice-signature { margin-top:10px!important; }
    .invoice-signature img { height:18px!important; }
    .invoice-signature h6 { font-size:10px!important; margin-top:6px!important; font-weight:800; color:#000; }
    .mb-4.pb-2 { margin-bottom:10px!important; padding-bottom:6px!important; font-size:11px!important; font-weight:700; color:#000; }
    @page { size:A4; margin:0.3cm 0.5cm 0.3cm 0.5cm; }
    .row { display:flex!important; flex-wrap:nowrap!important; margin-right:0!important; margin-left:0!important; }
    .col-lg,.col-6,.col-sm-6,.col-md-6 { position:relative!important; width:auto!important; padding-right:5px!important; padding-left:5px!important; flex-shrink:0!important; }
    .row.g-3 { margin-bottom:8px!important; gap:5px!important; display:flex!important; flex-wrap:nowrap!important; align-items:flex-start!important; justify-content:space-between!important; }
    .row.g-3>.col-lg:nth-child(1),.row.g-3>.col-6:nth-child(1){flex:0 0 18%!important}
    .row.g-3>.col-lg:nth-child(2),.row.g-3>.col-6:nth-child(2){flex:0 0 18%!important}
    .row.g-3>.col-lg:nth-child(3),.row.g-3>.col-6:nth-child(3){flex:0 0 18%!important}
    .row.g-3>.col-lg:nth-child(4),.row.g-3>.col-6:nth-child(4){flex:0 0 22%!important}
    .row.g-3>.col-lg:nth-child(5),.row.g-3>.col-6:nth-child(5){flex:0 0 24%!important}
    .table,.border-top-dashed,.invoice-signature { page-break-inside:avoid; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('status') || session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') ?: session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xxl-9 col-lg-10 col-md-12">

            <div class="hstack gap-2 justify-content-end d-print-none mb-4">
                {{-- Back uses $termid / $sessionid (lowercase) as passed by controller --}}
                <a href="{{ route('schoolpayment.termsessionpayments', [
                        'studentId' => $studentId,
                        'termid'    => $termid,
                        'sessionid' => $sessionid,
                    ]) }}"
                   class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <button type="button" id="print-button" class="btn btn-success">
                    <i class="ri-printer-line me-1"></i>Print
                </button>
            </div>

            <div class="card overflow-hidden" id="invoice">

                {{-- Top decorative SVG --}}
                <div class="invoice-effect-top position-absolute start-0">
                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 764 182" width="764" height="182">
                        <g>
                            <path style="fill:var(--tb-light)"   d="m-6.6 177.4c17.5.1 35.1 0 52.8-.4 286.8-6.6 537.6-77.8 700.3-184.6h-753.1z"/>
                            <path style="fill:var(--tb-secondary)" d="m-6.6 132.8c43.5 2.1 87.9 2.7 132.9 1.7 246.9-5.6 467.1-59.2 627.4-142.1h-760.3z"/>
                            <path style="fill:var(--tb-primary);opacity:.5" d="m-6.6 87.2c73.2 7.4 149.3 10.6 227.3 8.8 206.2-4.7 393.8-42.8 543.5-103.6h-770.8z"/>
                        </g>
                    </svg>
                </div>

                <div class="card-body z-1 position-relative">

                    {{-- ── Header ── --}}
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            @if($schoolInfo && $schoolInfo->logo_url)
                                <img src="{{ $schoolInfo->logo_url }}" class="card-logo"
                                     alt="{{ $schoolInfo->school_name ?? 'School' }}" height="28">
                            @else
                                <h5 class="fw-bold" style="color:var(--tb-secondary)">
                                    {{ $schoolInfo->school_name ?? 'School Name' }}
                                </h5>
                            @endif
                        </div>
                        <div class="flex-shrink-0 mt-sm-0 mt-3 text-end">
                            <h6><span class="text-muted fw-normal">Invoice No:</span> {{ $invoiceNumber }}</h6>
                            <h6><span class="text-muted fw-normal">Email:</span> {{ $schoolInfo->school_email ?? 'info@school.edu' }}</h6>
                            <h6>
                                <span class="text-muted fw-normal">Website:</span>
                                @if($schoolInfo->school_website ?? null)
                                    <a href="{{ $schoolInfo->school_website }}" target="_blank">{{ $schoolInfo->school_website }}</a>
                                @else N/A @endif
                            </h6>
                            <h6>
                                <span class="text-muted fw-normal">Address:</span>
                                <span class="address-wrap">{!! Str::replace(',', ',<br>', $schoolInfo->school_address ?? 'School Address') !!}</span>
                            </h6>
                            <h6 class="mb-0">
                                <span class="text-muted fw-normal">Contact:</span>
                                {{ $schoolInfo->school_phone ?? 'N/A' }}
                            </h6>
                        </div>
                    </div>

                    {{-- ── Invoice meta row ── --}}
                    <div class="mt-5 pt-4">
                        <div class="row g-3">
                            <div class="col-lg col-6">
                                <p class="text-muted mb-2 text-uppercase fw-bold" style="font-size:10px">Invoice No</p>
                                <h5 class="fs-md mb-0">#{{ $invoiceNumber }}</h5>
                            </div>
                            <div class="col-lg col-6">
                                <p class="text-muted mb-2 text-uppercase fw-bold" style="font-size:10px">Date</p>
                                <h5 class="fs-md mb-0">{{ \Carbon\Carbon::now()->format('d F, Y') }}</h5>
                            </div>
                            <div class="col-lg col-6">
                                <p class="text-muted mb-2 text-uppercase fw-bold" style="font-size:10px">Due Date</p>
                                <h5 class="fs-md mb-0">{{ \Carbon\Carbon::now()->addDays(7)->format('d F, Y') }}</h5>
                            </div>
                            <div class="col-lg col-6">
                                <p class="text-muted mb-2 text-uppercase fw-bold" style="font-size:10px">Payment Status</p>
                                <span class="badge {{ $totalOutstanding == 0 ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $totalOutstanding == 0 ? 'Paid' : 'Outstanding' }}
                                </span>
                            </div>
                            <div class="col-lg col-6">
                                <p class="text-muted mb-2 text-uppercase fw-bold" style="font-size:10px">Total Amount</p>
                                <h5 class="fs-md mb-0">₦{{ number_format($totalBillAmount, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    {{-- ── Student + Billing address ── --}}
                    <div class="mt-4 pt-2">
                        <div class="row g-3">
                            @if($studentdata->isNotEmpty())
                                @foreach($studentdata as $s)
                                    <div class="col-6">
                                        <p class="text-muted text-uppercase fw-bold mb-2" style="font-size:10px">Student Details</p>
                                        {{-- Student picture --}}
                                        @if($s->avatar)
                                            <img src="{{ Storage::url('images/studentavatar/' . $s->avatar) }}"
                                                 alt="{{ $s->firstname }} {{ $s->lastname }}"
                                                 class="rounded-circle mb-2 student-avatar"
                                                 onerror="this.style.display='none'">
                                        @endif
                                        <h6 class="fs-md">{{ $s->firstname }} {{ $s->lastname }}</h6>
                                        <p class="text-muted mb-1">ID: {{ $s->admissionNo }}</p>
                                        <p class="text-muted mb-1">Class: {{ $s->schoolclass }} {{ $s->arm }}</p>
                                        <p class="text-muted mb-0">Term: {{ $schoolterm }} | Session: {{ $schoolsession }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted text-uppercase fw-bold mb-2" style="font-size:10px">Billing Address</p>
                                        <h6 class="fs-md">{{ $s->firstname }} {{ $s->lastname }}</h6>
                                        <p class="text-muted mb-1 address-wrap">
                                            {!! Str::replace(',', ',<br>', $s->homeadd ?? $s->homeaddress ?? 'N/A') !!}
                                        </p>
                                        <p class="text-muted mb-0">Phone: {{ $s->phone ?? 'N/A' }}</p>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-muted text-center">No student data available.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Bills table ── --}}
                    <div class="table-responsive mt-4">
                        <table class="table table-borderless text-center table-nowrap align-middle mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th style="width:50px">#</th>
                                    <th>Bill Details</th>
                                    <th>Bill Amount</th>
                                    <th>Previous Paid</th>
                                    <th>Paid Today</th>
                                    <th>Total Paid</th>
                                    <th>Payment Method</th>
                                    <th class="text-end">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($studentpaymentbill->isEmpty())
                                    <tr><td colspan="8" class="text-center text-muted">No bills available.</td></tr>
                                @else
                                    @php $counter = 1; @endphp
                                    @foreach($studentpaymentbill as $sp)
                                        <tr>
                                            <th>{{ $counter++ }}</th>
                                            <td class="text-start">
                                                <span class="fw-medium">{{ $sp->title }}</span>
                                                @if($sp->description)
                                                    <p class="text-muted mb-0" style="font-size:11px">{{ $sp->description }}</p>
                                                @endif
                                                {{-- Show savings on invoice --}}
                                                @if(isset($sp->total_savings) && $sp->total_savings > 0)
                                                    <small class="text-success d-block">Savings: ₦{{ number_format($sp->total_savings, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>₦ {{ number_format($sp->amount, 2) }}</td>
                                            <td>₦ {{ number_format($sp->previousPaid, 2) }}</td>
                                            <td>₦ {{ number_format($sp->todayPaid, 2) }}</td>
                                            <td>₦ {{ number_format($sp->amountPaid, 2) }}</td>
                                            <td>
                                                @php $method = $sp->paymentMethod ?? 'N/A'; @endphp
                                                @if($method === 'Bank Transfer')
                                                    <span class="badge bg-primary-subtle text-primary">{{ $method }}</span>
                                                @elseif(in_array($method, ['School POS','Cash']))
                                                    <span class="badge bg-success-subtle text-success">{{ $method }}</span>
                                                @elseif($method === 'N/A')
                                                    <span class="badge bg-secondary-subtle text-secondary">{{ $method }}</span>
                                                @else
                                                    <span class="badge bg-info-subtle text-info">{{ $method }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">₦ {{ number_format($sp->balance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Totals ── --}}
                    <div class="border-top border-top-dashed mt-2" id="products-list-total">
                        <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto" style="width:300px">
                            <tbody>
                                <tr>
                                    <td>Total Bill Amount</td>
                                    <td class="text-end">₦ {{ number_format($totalBillAmount, 2) }}</td>
                                </tr>
                                @if(isset($totalSavings) && $totalSavings > 0)
                                <tr>
                                    <td class="text-success">Total Savings</td>
                                    <td class="text-end text-success">-₦ {{ number_format($totalSavings, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Total Previous Paid</td>
                                    <td class="text-end">₦ {{ number_format($totalPreviousPaid, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Paid Today</td>
                                    <td class="text-end">₦ {{ number_format($totalTodayPaid, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Amount Paid</td>
                                    <td class="text-end">₦ {{ number_format($totalPaid, 2) }}</td>
                                </tr>
                                <tr class="border-top border-top-dashed fw-bold">
                                    <th>Total Outstanding</th>
                                    <td class="text-end">₦ {{ number_format($totalOutstanding, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Payment details ── --}}
                    @if($studentpaymentbill->isNotEmpty())
                        @php $lastPayment = $studentpaymentbill->first(); @endphp
                        <div class="mt-3">
                            <h6 class="text-muted text-uppercase fw-semibold mb-3">Latest Payment Details:</h6>
                            <p class="text-muted mb-1">Payment Method: <span class="fw-medium">{{ $lastPayment->paymentMethod }}</span></p>
                            <p class="text-muted mb-1">Received By: <span class="fw-medium">{{ $lastPayment->receivedBy ?? $lastPayment->recievedBy ?? 'School Administration' }}</span></p>
                            <p class="text-muted mb-0">
                                Total Bill Amount: <span class="fw-medium">₦{{ number_format($totalBillAmount, 2) }}</span>
                            </p>
                        </div>
                    @endif

                    {{-- ── Footer ── --}}
                    <div class="mt-4">
                        <p class="mb-4 pb-2">
                            <b>Thank you for your continued partnership with {{ $schoolInfo->school_name ?? 'our school' }}!</b>
                            We appreciate your commitment to your child's education.
                        </p>
                        <div class="invoice-signature text-center">
                            <img src="{{ asset('assets/images/invoice-signature.svg') }}" alt="Authorized Sign" height="30">
                            <h6 class="mb-0 mt-3">Authorized Sign</h6>
                        </div>
                    </div>

                </div>{{-- /card-body --}}

                {{-- Bottom decorative SVG --}}
                <div class="invoice-effect-top position-absolute end-0" style="transform:rotate(180deg);bottom:-40px">
                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 764 182" width="764" height="182">
                        <g>
                            <path style="fill:var(--tb-light)"   d="m-6.6 177.4c17.5.1 35.1 0 52.8-.4 286.8-6.6 537.6-77.8 700.3-184.6h-753.1z"/>
                            <path style="fill:var(--tb-secondary)" d="m-6.6 132.8c43.5 2.1 87.9 2.7 132.9 1.7 246.9-5.6 467.1-59.2 627.4-142.1h-760.3z"/>
                            <path style="fill:var(--tb-primary);opacity:.5" d="m-6.3 87.51c73.2 7.41 149.6 45.1 227.6 43.4 206.1 4.6 393.7-42.8 543.4-103.6h-770.45z"/>
                        </g>
                    </svg>
                </div>

            </div>{{-- /card --}}
        </div>
    </div>

</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const originalTitle   = document.title;
    const studentName     = @json($studentdata->isNotEmpty() ? $studentdata->first()->firstname . ' ' . $studentdata->first()->lastname : 'Student');
    const invoiceNumber   = @json($invoiceNumber ?? 'INV-000');
    const cleanName       = studentName.trim().replace(/\s+/g,'_');
    const cleanInvoice    = invoiceNumber.replace(/[^a-zA-Z0-9\-]/g,'');
    const customFilename  = cleanName + '_' + cleanInvoice;

    function handlePrint() {
        document.title = customFilename;
        setTimeout(() => {
            window.print();
            setTimeout(() => { document.title = originalTitle; }, 1000);
        }, 100);
    }

    document.getElementById('print-button')?.addEventListener('click', handlePrint);

    window.addEventListener('beforeprint', () => { document.title = customFilename; });
    window.addEventListener('afterprint',  () => { setTimeout(() => { document.title = originalTitle; }, 500); });
});
</script>
@endsection
