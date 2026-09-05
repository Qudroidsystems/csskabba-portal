<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Analysis Report</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 5px;
        }
        .school-address {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        .school-motto {
            font-size: 9px;
            font-style: italic;
            color: #666;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            background: #1e3a5f;
            color: white;
            padding: 6px;
            margin: 10px 0;
            text-align: center;
        }
        .class-info {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 5px;
            background: #f0f4fa;
        }

        /* Summary Cards */
        .summary-cards {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-cards td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background: #f8fafc;
        }
        .summary-label {
            font-size: 9px;
            color: #666;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a5f;
        }

        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 10px;
        }
        .main-table th {
            background: #1e3a5f;
            color: white;
            padding: 6px 4px;
            border: 0.5px solid #2563eb;
            font-weight: bold;
            text-align: center;
        }
        .main-table td {
            border: 0.5px solid #c5d3e8;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* Status Badges */
        .status-paid {
            background: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-partial {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-unpaid {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
        }

        /* Benefit Badges */
        .benefit-scholarship {
            background: #fef3c7;
            color: #d97706;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
        }
        .benefit-discount {
            background: #ede9fe;
            color: #6d28d9;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
        }

        /* Progress Bar */
        .progress-container {
            width: 60px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto;
        }
        .progress-fill {
            height: 4px;
            border-radius: 10px;
        }
        .progress-high { background: #16a34a; }
        .progress-medium { background: #d97706; }
        .progress-low { background: #dc2626; }
        .progress-text {
            font-size: 7px;
            margin-top: 2px;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>

<!-- Header with School Info -->
<div class="header">
    @if($schoolInfo && $schoolInfo->getLogoUrlAttribute())
        <img src="{{ $schoolInfo->getLogoUrlAttribute() }}" style="width: 50px; height: 50px; object-fit: contain;">
    @endif
    <div class="school-name">{{ $schoolInfo->school_name ?? 'TRINITY COMPREHENSIVE INTERNATIONAL SCHOOL ONDO' }}</div>
    @if($schoolInfo && $schoolInfo->school_address)
        <div class="school-address">{{ $schoolInfo->school_address }}</div>
    @endif
    @if($schoolInfo && $schoolInfo->school_motto)
        <div class="school-motto">"{{ $schoolInfo->school_motto }}"</div>
    @endif
    <div class="report-title">CLASS FINANCIAL ANALYSIS REPORT</div>
    <div class="class-info">
        Class: {{ $schoolClass->schoolclass ?? '' }} {{ $schoolClass->arm ?? '' }} |
        Term: {{ $schoolTerm ?? '' }} |
        Session: {{ $schoolSession ?? '' }}
    </div>
</div>

<!-- Summary Cards -->
<table class="summary-cards">
    <tr>
        <td style="background:#e3f2fd;"><div class="summary-label">Total Students</div><div class="summary-value">{{ $students->count() }}</div></td>
        <td style="background:#e8f5e9;"><div class="summary-label">Total Billed</div><div class="summary-value" style="color:#16a34a;">₦{{ number_format($totalPaidSum + $totalBalanceSum, 2) }}</div></td>
        <td style="background:#fff3e0;"><div class="summary-label">Total Paid</div><div class="summary-value" style="color:#d97706;">₦{{ number_format($totalPaidSum, 2) }}</div></td>
        <td style="background:#ffebee;"><div class="summary-label">Outstanding</div><div class="summary-value" style="color:#dc2626;">₦{{ number_format($totalBalanceSum, 2) }}</div></td>
        <td style="background:#e0f2f1;"><div class="summary-label">Collection Rate</div><div class="summary-value">{{ $collectionRate }}%</div></td>
    </tr>
</table>

<!-- Main Student Table -->
<table class="main-table">
    <thead>
        <tr>
            <th rowspan="2">#</th>
            <th rowspan="2">Adm No</th>
            <th rowspan="2">Student Name</th>
            <th rowspan="2">Gender</th>
            <th rowspan="2">Benefits</th>
            @foreach($studentBillInfo as $bill)
                <th colspan="1">{{ $bill->title }}<br>(₦{{ number_format($bill->amount, 2) }})</th>
            @endforeach
            <th rowspan="2">Total Paid</th>
            <th rowspan="2">Outstanding</th>
            <th rowspan="2">Progress</th>
            <th rowspan="2">Status</th>
        </tr>
    </thead>
    <tbody>
        @php $counter = 1; @endphp
        @foreach($students as $std)
            @php
                $hasScholarship = $scholarshipAssignments->has($std->stid);
                $hasDiscount = $discountAssignments->has($std->stid);
                $status = $studentTotals[$std->stid]['status'];
                $totalPaid = $studentTotals[$std->stid]['totalPaid'];
                $totalBalance = $studentTotals[$std->stid]['totalBalance'];
                $totalBilledForStudent = $studentTotals[$std->stid]['totalBilled'];
                $completion = $totalBilledForStudent > 0 ? round(($totalPaid / $totalBilledForStudent) * 100, 1) : 0;
                $progressClass = $completion >= 70 ? 'progress-high' : ($completion >= 40 ? 'progress-medium' : 'progress-low');
            @endphp
            <tr>
                <td class="text-center">{{ $counter++ }}</td>
                <td class="text-center">{{ $std->admissionno }}</td>
                <td class="text-left">{{ $std->lastname }} {{ $std->firstname }} {{ $std->othername }}</td>
                <td class="text-center">{{ $std->gender }}</td>
                <td class="text-center">
                    @if($hasScholarship)<span class="benefit-scholarship">Scholarship</span> @endif
                    @if($hasDiscount)<span class="benefit-discount">Discount</span> @endif
                    @if(!$hasScholarship && !$hasDiscount)<span>—</span>@endif
                </td>
                @foreach($studentBillInfo as $bill)
                    @php
                        // FIX: $studentPayments never existed on this data shape.
                        // Payments live in $paymentBooks, keyed "{student_id}_{school_bill_id}"
                        // with the amount on ->amount_paid (see controller).
                        $book = $paymentBooks->get($std->stid . '_' . $bill->schoolbillid);
                        $paid = $book ? (float) $book->amount_paid : 0;
                    @endphp
                    <td class="text-end">{{ $paid > 0 ? '₦' . number_format($paid, 2) : '—' }}</td>
                @endforeach
                <td class="text-end">₦{{ number_format($totalPaid, 2) }}</td>
                <td class="text-end">₦{{ number_format($totalBalance, 2) }}</td>
                <td class="text-center">
                    <div class="progress-container">
                        <div class="progress-fill {{ $progressClass }}" style="width: {{ $completion }}%"></div>
                    </div>
                    <div class="progress-text">{{ $completion }}%</div>
                </td>
                <td class="text-center">
                    @if($status == 'paid')
                        <span class="status-paid">Fully Paid</span>
                    @elseif($status == 'partial')
                        <span class="status-partial">Partial</span>
                    @else
                        <span class="status-unpaid">Unpaid</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot style="background: #f0f0f0; font-weight: bold;">
        <tr>
            <td colspan="5" class="text-end">TOTALS</td>
            @foreach($studentBillInfo as $bill)
                @php
                    // FIX: sum from $paymentBooks filtered by this bill's id,
                    // matching the column name used by student_bill_payment_book.
                    $billTotalPaid = $paymentBooks
                        ->where('school_bill_id', $bill->schoolbillid)
                        ->sum('amount_paid');
                @endphp
                <td class="text-end">₦{{ number_format($billTotalPaid, 2) }}</td>
            @endforeach
            <td class="text-end">₦{{ number_format($totalPaidSum, 2) }}</td>
            <td class="text-end">₦{{ number_format($totalBalanceSum, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<!-- Payment Status Summary -->
<table class="summary-cards" style="margin-top: 15px;">
    <thead>
        <tr><th colspan="3" style="background:#1e3a5f; color:white; padding:6px; text-align:center;">PAYMENT STATUS OVERVIEW</th></tr>
        <tr style="background:#f0f0f0;"><th style="padding:4px;">Status</th><th style="padding:4px;">Count</th><th style="padding:4px;">Percentage</th></tr>
    </thead>
    <tbody>
        @php
            $statusCounts = ['paid' => 0, 'partial' => 0, 'unpaid' => 0];
            foreach ($studentTotals as $total) { $statusCounts[$total['status']]++; }
            $totalStdCount = count($studentTotals);
        @endphp
        <tr><td class="status-paid" style="text-align:center;">Fully Paid</td><td class="text-center">{{ $statusCounts['paid'] }}</td><td class="text-center">{{ $totalStdCount > 0 ? number_format(($statusCounts['paid'] / $totalStdCount) * 100, 1) : 0 }}%</td></tr>
        <tr><td class="status-partial" style="text-align:center;">Partially Paid</td><td class="text-center">{{ $statusCounts['partial'] }}</td><td class="text-center">{{ $totalStdCount > 0 ? number_format(($statusCounts['partial'] / $totalStdCount) * 100, 1) : 0 }}%</td></tr>
        <tr><td class="status-unpaid" style="text-align:center;">Unpaid</td><td class="text-center">{{ $statusCounts['unpaid'] }}</td><td class="text-center">{{ $totalStdCount > 0 ? number_format(($statusCounts['unpaid'] / $totalStdCount) * 100, 1) : 0 }}%</td></tr>
    </tbody>
</table>

<!-- Footer -->
<div class="footer">
    Generated on {{ $generatedAt }} | Page {PAGE_NUM} of {PAGE_COUNT}
</div>

</body>
</html>