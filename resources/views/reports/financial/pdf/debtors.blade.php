{{-- resources/views/reports/financial/pdf/debtors.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debtors List</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
            color: #1e293b;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 15px;
        }
        .header .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1e3a5f;
        }
        .header .school-address {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }
        .header .report-title {
            font-size: 16px;
            font-weight: 600;
            color: #2563eb;
            margin-top: 6px;
        }
        .header .report-meta {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .filters {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .filters .filter-item {
            display: inline-block;
            margin-right: 20px;
        }
        .filters .filter-item strong {
            color: #1e3a5f;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background: #1e3a5f;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        table .text-end {
            text-align: right;
        }
        table .text-center {
            text-align: center;
        }
        
        .totals {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #1e3a5f;
        }
        .totals table {
            width: auto;
            float: right;
        }
        .totals table td {
            padding: 4px 12px;
            font-weight: 600;
            border: none;
        }
        .totals table .label {
            color: #6b7280;
            font-weight: 400;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
        }
        .badge-scholarship { background: #fef3c7; color: #d97706; }
        .badge-discount { background: #ede9fe; color: #6d28d9; }
        .badge-both { background: #fef3c7; color: #d97706; }
        
        .avatar-placeholder {
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            text-align: center;
            line-height: 24px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .amount-high { color: #dc2626; font-weight: 700; }
        .amount-medium { color: #d97706; font-weight: 600; }
        .amount-low { color: #16a34a; font-weight: 500; }
        
        @page {
            margin: 15mm;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        @if($schoolInfo && $schoolInfo->logo_base64)
            <img src="{{ $schoolInfo->logo_base64 }}" alt="School Logo" style="max-height:60px;margin-bottom:8px;">
        @endif
        <div class="school-name">{{ $schoolInfo->school_name ?? 'School Name' }}</div>
        <div class="school-address">{{ $schoolInfo->school_address ?? '' }}</div>
        <div class="school-address">{{ $schoolInfo->formatted_phones ?? '' }}</div>
        <div class="report-title">DEBTORS LIST</div>
        <div class="report-meta">
            Generated: {{ now()->format('d F, Y H:i:s') }}
            @if($filters['term'] || $filters['session'] || $filters['class'])
                | Filters Applied
            @endif
        </div>
    </div>

    <!-- Filters -->
    @if($filters['term'] || $filters['session'] || $filters['class'] || $filters['search'])
    <div class="filters">
        @if($filters['class'])
            <span class="filter-item"><strong>Class:</strong> {{ $filters['class'] }}</span>
        @endif
        @if($filters['term'])
            <span class="filter-item"><strong>Term:</strong> {{ $filters['term'] }}</span>
        @endif
        @if($filters['session'])
            <span class="filter-item"><strong>Session:</strong> {{ $filters['session'] }}</span>
        @endif
        @if($filters['search'])
            <span class="filter-item"><strong>Search:</strong> {{ $filters['search'] }}</span>
        @endif
    </div>
    @endif

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th width="30">#</th>
                <th>Student</th>
                <th>Admission</th>
                <th>Class</th>
                <th>Benefits</th>
                <th class="text-end">Billed (₦)</th>
                <th class="text-end">Paid (₦)</th>
                <th class="text-end">Outstanding (₦)</th>
                <th class="text-end">Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataset as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @if($row['avatar_base64'])
                            <img src="{{ $row['avatar_base64'] }}" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                        @else
                            <span class="avatar-placeholder">{{ substr($row['student_name'] ?? 'ST', 0, 2) }}</span>
                        @endif
                        <span>{{ $row['student_name'] }}</span>
                    </div>
                </td>
                <td>{{ $row['admission_no'] }}</td>
                <td>{{ $row['class_name'] }}</td>
                <td>
                    @if($row['has_scholarship'] && $row['has_discount'])
                        <span class="badge badge-both">Both</span>
                    @elseif($row['has_scholarship'])
                        <span class="badge badge-scholarship">Scholarship</span>
                    @elseif($row['has_discount'])
                        <span class="badge badge-discount">Discount</span>
                    @else
                        <span style="color:#9ca3af;">—</span>
                    @endif
                </td>
                <td class="text-end">{{ number_format($row['original_amount'], 2) }}</td>
                <td class="text-end">{{ number_format($row['amount_paid'], 2) }}</td>
                <td class="text-end">
                    <span class="amount-{{ $row['outstanding'] >= 100000 ? 'high' : ($row['outstanding'] >= 50000 ? 'medium' : 'low') }}">
                        {{ number_format($row['outstanding'], 2) }}
                    </span>
                </td>
                <td class="text-end">{{ $row['collection_rate'] }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding:20px;color:#6b7280;">
                    No debtors found matching the criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td class="label">Total Debtors:</td>
                <td>{{ $totals['debtors'] }}</td>
            </tr>
            <tr>
                <td class="label">Total Billed:</td>
                <td>₦{{ number_format($totals['original'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Paid:</td>
                <td>₦{{ number_format($totals['paid'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Outstanding:</td>
                <td>₦{{ number_format($totals['outstanding'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Savings:</td>
                <td>₦{{ number_format($totals['savings'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-top:2px solid #1e3a5f;">
                <td class="label">Collection Rate:</td>
                <td>
                    @php
                        $rate = $totals['original'] > 0 ? round(($totals['paid'] / $totals['original']) * 100, 1) : 0;
                    @endphp
                    {{ $rate }}%
                </td>
            </tr>
        </table>
        <div style="clear:both;"></div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>{{ $schoolInfo->school_name ?? '' }} &bull; {{ $schoolInfo->school_address ?? '' }}</div>
        <div>Generated on {{ now()->format('d F, Y H:i:s') }} &bull; Page {PAGE_NUM} of {PAGE_COUNT}</div>
        @if($schoolInfo && $schoolInfo->stamp_base64)
            <div style="margin-top:8px;">
                <img src="{{ $schoolInfo->stamp_base64 }}" style="max-height:40px;">
            </div>
        @endif
    </div>

</body>
</html>