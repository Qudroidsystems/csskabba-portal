<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Payment Analysis</title>
    <style>
        @page {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        body {
            font-family: DejaVuSans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
        }

        /* Header styling */
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 5px;
            vertical-align: middle;
        }
        .school-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #1d25c3;
        }
        .school-motto {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        .school-contact {
            font-size: 7px;
            color: #555;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            background: #1e3a5f;
            color: white;
            padding: 5px;
        }
        .class-info {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px;
            background: #f0f0f0;
        }

        /* Main table styling */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-top: 10px;
        }
        .main-table, .main-table th, .main-table td {
            border: 1px solid #ddd;
        }
        .main-table th {
            background-color: #1e3a5f;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
            font-size: 7px;
        }
        .main-table td {
            padding: 3px 2px;
            vertical-align: middle;
        }
        .student-row:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Status badges */
        .status-paid { background-color: #dcfce7; color: #166534; font-weight: bold; padding: 2px 4px; border-radius: 3px; display: inline-block; }
        .status-partial { background-color: #fef9c3; color: #854d0e; padding: 2px 4px; border-radius: 3px; display: inline-block; }
        .status-unpaid { background-color: #fee2e2; color: #991b1b; font-weight: bold; padding: 2px 4px; border-radius: 3px; display: inline-block; }

        /* Benefit badges */
        .badge-scholarship { background: #fef3c7; color: #d97706; font-size: 6px; padding: 1px 3px; border-radius: 3px; display: inline-block; margin: 1px; }
        .badge-discount { background: #ede9fe; color: #6d28d9; font-size: 6px; padding: 1px 3px; border-radius: 3px; display: inline-block; margin: 1px; }

        /* Summary sections */
        .summary-section {
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .summary-title {
            font-size: 10px;
            font-weight: bold;
            background: #1e3a5f;
            color: white;
            padding: 4px 8px;
            margin-bottom: 5px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 10px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 4px;
        }
        .summary-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .cell-amount {
            text-align: right;
        }
        .text-bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        /* Student avatar */
        .student-avatar {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 7px;
            color: #666;
            margin-top: 15px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
        }

        /* Progress bar */
        .progress-bar-container {
            width: 60px;
            background-color: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto;
        }
        .progress-bar-fill {
            height: 4px;
            border-radius: 10px;
        }
        .progress-high { background: linear-gradient(90deg, #16a34a, #22c55e); }
        .progress-medium { background: linear-gradient(90deg, #d97706, #f59e0b); }
        .progress-low { background: linear-gradient(90deg, #dc2626, #ef4444); }
    </style>
</head>
<body>

{{-- Header --}}
<table class="header-table">
    <tr>
        <td width="15%" align="center">
            @if($schoolInfo && $schoolInfo->getLogoUrlAttribute())
                <img src="{{ $schoolInfo->getLogoUrlAttribute() }}" class="school-logo" alt="School Logo">
            @endif
        </td>
        <td width="70%" align="center">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'TRINITY COMPREHENSIVE INTERNATIONAL SCHOOL ONDO' }}</div>
            <div class="school-motto">"{{ $schoolInfo->school_motto ?? 'Knowledge and Excellence' }}"</div>
            <div class="school-contact">
                @if($schoolInfo)
                    {{ $schoolInfo->school_address ?? '' }} |
                    Tel: {{ $schoolInfo->getPrimaryPhoneAttribute() ?? '' }} |
                    Email: {{ $schoolInfo->school_email ?? '' }}
                @endif
            </div>
        </td>
        <td width="15%" align="center">
            @if($schoolInfo && $schoolInfo->getStampUrlAttribute())
                <img src="{{ $schoolInfo->getStampUrlAttribute() }}" style="width: 50px; height: 50px;" alt="School Stamp">
            @endif
        </td>
    </tr>
</table>

<div class="report-title">STUDENT PAYMENT ANALYSIS REPORT</div>
<div class="class-info">
    Class: {{ $schoolClass->schoolclass ?? '' }} {{ $schoolClass->arm ?? '' }} |
    Term: {{ $schoolTerm ?? '' }} |
    Session: {{ $schoolSession ?? '' }}
</div>

{{-- Summary Cards --}}
<table class="summary-table" style="width: 100%; margin-bottom: 10px;">
    <tr>
        <td style="background: #e3f2fd; text-align: center;"><strong>Total Students</strong><br><span style="font-size: 12px; font-weight: bold;">{{ $students->count() }}</span></td>
        <td style="background: #e8f5e9; text-align: center;"><strong>Total Billed</strong><br><span style="font-size: 12px; font-weight: bold; color: green;">₦{{ number_format($totalPaidSum + $totalBalanceSum, 2) }}</span></td>
        <td style="background: #fff3e0; text-align: center;"><strong>Total Paid</strong><br><span style="font-size: 12px; font-weight: bold; color: #d97706;">₦{{ number_format($totalPaidSum, 2) }}</span></td>
        <td style="background: #ffebee; text-align: center;"><strong>Outstanding</strong><br><span style="font-size: 12px; font-weight: bold; color: red;">₦{{ number_format($totalBalanceSum, 2) }}</span></td>
        <td style="background: #e0f2f1; text-align: center;"><strong>Collection Rate</strong><br><span style="font-size: 12px; font-weight: bold;">{{ $collectionRate }}%</span></td>
    </tr>
</table>

{{-- Scholarship & Discount Summary --}}
<div class="summary-section">
    <div class="summary-title">SCHOLARSHIP & DISCOUNT SUMMARY</div>
    <table class="summary-table">
        <thead><tr><th>Type</th><th>Number of Beneficiaries</th><th>Remarks</th></tr></thead>
        <tbody>
            <tr><td class="text-bold" style="color:#d97706;">Scholarships</td><td class="text-center">{{ $scholarshipAssignments->count() }}</td><td>{{ $scholarshipAssignments->count() }} student(s) have scholarship benefits</td></tr>
            <tr><td class="text-bold" style="color:#6d28d9;">Discounts</td><td class="text-center">{{ $discountAssignments->count() }}</td><td>{{ $discountAssignments->count() }} student(s) have discount benefits</td></tr>
        </tbody>
    </table>
</div>

{{-- Main Student Table --}}
<div class="summary-section">
    <div class="summary-title">STUDENT PAYMENT DETAILS</div>
    <table class="main-table">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Gender</th>
                <th>Benefits</th>
                @foreach($studentBillInfo as $bill)
                    <th class="text-center">{{ $bill->title }}<br>(₦{{ number_format($bill->amount, 2) }})</th>
                @endforeach
                <th>Total Paid<br>(₦)</th>
                <th>Outstanding<br>(₦)</th>
                <th>Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($students as $std)
                @php
                    $hasScholarship = $scholarshipAssignments->contains('student_id', $std->stid);
                    $hasDiscount = $discountAssignments->contains('student_id', $std->stid);
                    $status = $studentTotals[$std->stid]['status'];
                    $statusClass = 'status-' . $status;
                    $totalBilledForStudent = $studentBillInfo->sum('amount');
                    $totalPaid = $studentTotals[$std->stid]['totalPaid'];
                    $totalBalance = $studentTotals[$std->stid]['totalBalance'];
                    $completion = $totalBilledForStudent > 0 ? round(($totalPaid / $totalBilledForStudent) * 100, 1) : 0;
                    $progressClass = $completion >= 70 ? 'progress-high' : ($completion >= 40 ? 'progress-medium' : 'progress-low');
                @endphp
                <td>
                    <td class="text-center">{{ $counter++ }}</td>
                    <td class="text-center">{{ $std->admissionno }}</td>
                    <td style="text-align: left;">{{ $std->lastname }} {{ $std->firstname }} {{ $std->othername }}</td>
                    <td class="text-center">{{ $std->gender }}</td>
                    <td class="text-center">
                        @if($hasScholarship)<span class="badge-scholarship">Scholarship</span> @endif
                        @if($hasDiscount)<span class="badge-discount">Discount</span> @endif
                        @if(!$hasScholarship && !$hasDiscount)<span>—</span>@endif
                    </td>
                    @foreach($studentBillInfo as $bill)
                        @php
                            $payment = $studentPayments
                                ->where('stid', $std->stid)
                                ->where('schoolbillid', $bill->schoolbillid)
                                ->first();
                            $paid = $payment ? $payment->totalAmountPaid : 0;
                        @endphp
                        <td class="cell-amount">{{ $paid > 0 ? '₦' . number_format($paid, 2) : '—' }}</td>
                    @endforeach
                    <td class="cell-amount text-bold">₦{{ number_format($totalPaid, 2) }}</td>
                    <td class="cell-amount text-bold">₦{{ number_format($totalBalance, 2) }}</td>
                    <td class="text-center"><span class="{{ $statusClass }}">{{ ucfirst($status) }}</span></td>
                    <td class="text-center">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill {{ $progressClass }}" style="width: {{ $completion }}%"></div>
                        </div>
                        <small>{{ $completion }}%</small>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background: #f0f0f0;">
            <tr class="text-bold">
                <td colspan="5" class="text-right">TOTALS</td>
                @foreach($studentBillInfo as $bill)
                    @php $billTotalPaid = $studentPayments->where('schoolbillid', $bill->schoolbillid)->sum('totalAmountPaid'); @endphp
                    <td class="cell-amount">₦{{ number_format($billTotalPaid, 2) }}</td>
                @endforeach
                <td class="cell-amount">₦{{ number_format($totalPaidSum, 2) }}</td>
                <td class="cell-amount">₦{{ number_format($totalBalanceSum, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Payment Collection Summary --}}
<div class="summary-section">
    <div class="summary-title">PAYMENT COLLECTION SUMMARY BY BILL</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Bill Title</th>
                <th class="cell-amount">Amount per Student (₦)</th>
                <th class="cell-amount">Total Expected (₦)</th>
                <th class="cell-amount">Total Collected (₦)</th>
                <th class="cell-amount">Outstanding (₦)</th>
                <th class="cell-amount">Collection %</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalExpected = 0;
                $grandTotalCollected = 0;
                $grandTotalOutstanding = 0;
            @endphp
            @foreach($studentBillInfo as $bill)
                @php
                    $billTotalPaid = $studentPayments->where('schoolbillid', $bill->schoolbillid)->sum('totalAmountPaid');
                    $totalExpected = $students->count() * $bill->amount;
                    $billTotalOwed = $totalExpected - $billTotalPaid;
                    $collectionPercentage = $totalExpected > 0 ? ($billTotalPaid / $totalExpected) * 100 : 0;

                    $grandTotalExpected += $totalExpected;
                    $grandTotalCollected += $billTotalPaid;
                    $grandTotalOutstanding += $billTotalOwed;
                @endphp
                <tr>
                    <td>{{ $bill->title }}</td>
                    <td class="cell-amount">{{ number_format($bill->amount, 2) }}</td>
                    <td class="cell-amount">{{ number_format($totalExpected, 2) }}</td>
                    <td class="cell-amount">{{ number_format($billTotalPaid, 2) }}</td>
                    <td class="cell-amount">{{ number_format($billTotalOwed, 2) }}</td>
                    <td class="cell-amount">{{ number_format($collectionPercentage, 1) }}%</td>
                </tr>
            @endforeach
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td>GRAND TOTAL</td>
                <td class="cell-amount">{{ number_format($studentBillInfo->sum('amount'), 2) }}</td>
                <td class="cell-amount">₦{{ number_format($grandTotalExpected, 2) }}</td>
                <td class="cell-amount">₦{{ number_format($grandTotalCollected, 2) }}</td>
                <td class="cell-amount">₦{{ number_format($grandTotalOutstanding, 2) }}</td>
                <td class="cell-amount">{{ $grandTotalExpected > 0 ? number_format(($grandTotalCollected / $grandTotalExpected) * 100, 1) : 0 }}%</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Payment Status Overview --}}
<div class="summary-section">
    <div class="summary-title">PAYMENT STATUS OVERVIEW</div>
    <table class="summary-table" style="width: 50%;">
        <thead><tr><th>Status</th><th class="text-center">Count</th><th class="text-center">Percentage</th></tr></thead>
        <tbody>
            @php
                $statusCounts = ['paid' => 0, 'partial' => 0, 'unpaid' => 0];
                foreach ($studentTotals as $total) { $statusCounts[$total['status']]++; }
                $totalStdCount = count($studentTotals);
            @endphp
            <tr><td class="status-paid">Fully Paid</td><td class="text-center">{{ $statusCounts['paid'] }}</td><td class="text-center">{{ $totalStdCount > 0 ? number_format(($statusCounts['paid'] / $totalStdCount) * 100, 1) : 0 }}%</td></tr>
            <tr><td class="status-partial">Partially Paid</td><td class="text-center">{{ $statusCounts['partial'] }}</td><td class="text-center">{{ $totalStdCount > 0 ? number_format(($statusCounts['partial'] / $totalStdCount) * 100, 1) : 0 }}%</td></tr>
            <tr><td class="status-unpaid">Not Paid</td><td class="text-center">{{ $statusCounts['unpaid'] }}</td><td class="text-center">{{ $totalStdCount > 0 ? number_format(($statusCounts['unpaid'] / $totalStdCount) * 100, 1) : 0 }}%</td></tr>
            <tr style="background: #f0f0f0;"><td class="text-bold">TOTAL</td><td class="text-center text-bold">{{ $totalStdCount }}</td><td class="text-center text-bold">100%</td></tr>
        </tbody>
    </table>
</div>

{{-- Footer --}}
<div class="footer">
    Generated on {{ $generatedAt }} | Page {PAGENO} of {nbpg}
</div>

</body>
</html>
