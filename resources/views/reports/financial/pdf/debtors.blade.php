{{-- resources/views/reports/financial/pdf/debtors.blade.php --}}
@php
    $generatedAt = now()->format('d M Y, h:i A');
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Student Debtors Report</title>
<style>
    @page { margin: 20px 24px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color:#1e293b; }

    .header-table { width:100%; border-bottom: 2.5px solid #1e3a5f; padding-bottom:10px; margin-bottom:14px; }
    .school-logo { width:56px; height:56px; object-fit:contain; }
    .school-name { font-size:17px; font-weight:700; color:#1e3a5f; margin:0; }
    .school-meta { font-size:9px; color:#64748b; margin:2px 0 0; }
    .report-title { font-size:15px; font-weight:700; color:#dc2626; text-align:right; margin:0; }
    .report-meta { font-size:9px; color:#64748b; text-align:right; margin-top:3px; }

    .filters-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:12px; font-size:9.5px; color:#334155; }
    .filters-box b { color:#1e3a5f; }

    .stats-table { width:100%; margin-bottom:14px; border-collapse:separate; border-spacing:6px 0; }
    .stats-table td { width:25%; text-align:center; padding:0; }
    .stat-box { border:1px solid #e2e8f0; border-radius:8px; padding:8px 4px; }
    .stat-box .v { font-size:14px; font-weight:700; color:#1e3a5f; display:block; }
    .stat-box .l { font-size:7.5px; color:#64748b; text-transform:uppercase; letter-spacing:.3px; }
    .stat-box.red .v { color:#dc2626; }
    .stat-box.green .v { color:#16a34a; }
    .stat-box.orange .v { color:#d97706; }

    table.debtors { width:100%; border-collapse:collapse; }
    table.debtors thead th {
        background:#1e3a5f; color:#fff; font-size:8.5px; text-transform:uppercase;
        padding:7px 5px; text-align:left; letter-spacing:.2px;
    }
    table.debtors tbody td { padding:7px 5px; border-bottom:1px solid #e2e8f0; font-size:9px; vertical-align:top; }
    table.debtors tbody tr:nth-child(odd) { background:#fafbfc; }

    .avatar-cell img { width:28px; height:28px; border-radius:50%; object-fit:cover; border:1px solid #e2e8f0; }
    .avatar-fallback {
        width:28px; height:28px; border-radius:50%; background:#dc2626; color:#fff;
        text-align:center; font-size:9px; font-weight:700; line-height:28px;
    }

    .student-name { font-weight:700; color:#1e3a5f; }
    .student-adm  { color:#94a3b8; font-size:8px; }

    .badge { display:inline-block; padding:2px 6px; border-radius:8px; font-size:7.5px; font-weight:700; margin:0 2px 2px 0; }
    .badge.sch  { background:#fde68a; color:#92400e; }
    .badge.disc { background:#dbeafe; color:#1e40af; }
    .badge.none { color:#cbd5e1; }

    .bill-list { font-size:8px; color:#64748b; margin-top:2px; }

    .text-end { text-align:right; }
    .amt-out  { color:#dc2626; font-weight:700; }
    .amt-paid { color:#16a34a; }
    .amt-save { color:#d97706; }

    tfoot td { border-top:2px solid #1e3a5f; font-weight:700; padding:9px 5px; font-size:9.5px; }

    .footer-note { margin-top:16px; font-size:8px; color:#94a3b8; text-align:center; }
</style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width:60px; vertical-align:middle;">
                @if(!empty($schoolInfo?->logo_base64))
                    <img src="{{ $schoolInfo->logo_base64 }}" class="school-logo">
                @endif
            </td>
            <td style="vertical-align:middle;">
                <p class="school-name">{{ $schoolInfo->school_name ?? 'School Name' }}</p>
                <p class="school-meta">{{ $schoolInfo->school_address ?? '' }}</p>
                <p class="school-meta">
                    {{ $schoolInfo->formatted_phones ?? '' }}
                    @if(!empty($schoolInfo?->school_email)) &nbsp;·&nbsp; {{ $schoolInfo->school_email }} @endif
                </p>
            </td>
            <td style="vertical-align:middle; text-align:right;">
                <p class="report-title">STUDENT DEBTORS REPORT</p>
                <p class="report-meta">Generated: {{ $generatedAt }}</p>
            </td>
        </tr>
    </table>

    @if($filters['class'] || $filters['term'] || $filters['session'] || $filters['search'])
    <div class="filters-box">
        <b>Filters applied:</b>
        @if($filters['class'])   Class: <b>{{ $filters['class'] }}</b> &nbsp;|&nbsp; @endif
        @if($filters['term'])    Term: <b>{{ $filters['term'] }}</b> &nbsp;|&nbsp; @endif
        @if($filters['session']) Session: <b>{{ $filters['session'] }}</b> &nbsp;|&nbsp; @endif
        @if($filters['search'])  Search: <b>"{{ $filters['search'] }}"</b> @endif
    </div>
    @endif

    <table class="stats-table">
        <tr>
            <td><div class="stat-box"><span class="v">{{ $totals['debtors'] }}</span><span class="l">Total Debtors</span></div></td>
            <td><div class="stat-box red"><span class="v">₦{{ number_format($totals['outstanding'],2) }}</span><span class="l">Outstanding</span></div></td>
            <td><div class="stat-box green"><span class="v">₦{{ number_format($totals['paid'],2) }}</span><span class="l">Collected</span></div></td>
            <td><div class="stat-box orange"><span class="v">₦{{ number_format($totals['savings'],2) }}</span><span class="l">Total Savings</span></div></td>
        </tr>
    </table>

    <table class="debtors">
        <thead>
            <tr>
                <th style="width:18px;">#</th>
                <th style="width:34px;">Photo</th>
                <th>Student</th>
                <th>Class</th>
                <th>Term / Session</th>
                <th>Scholarship / Discount</th>
                <th class="text-end">Original (₦)</th>
                <th class="text-end">Paid (₦)</th>
                <th class="text-end">Outstanding (₦)</th>
                <th class="text-end">Savings (₦)</th>
                <th class="text-end">Rate</th>
            </tr>
        </thead>
        <tbody>
        @forelse($dataset as $i => $row)
            @php
                $nameParts = preg_split('/\s+/', trim($row['student_name']));
                $rowInitials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? '', 0, 1)) ?: 'ST';
                $billTitles = collect($row['bills'])->pluck('title')->filter()->implode(', ');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="avatar-cell">
                    @if(!empty($row['avatar_base64']))
                        <img src="{{ $row['avatar_base64'] }}">
                    @else
                        <div class="avatar-fallback">{{ $rowInitials }}</div>
                    @endif
                </td>
                <td>
                    <div class="student-name">{{ $row['student_name'] }}</div>
                    <div class="student-adm">Adm: {{ $row['admission_no'] }}</div>
                    @if($billTitles)
                        <div class="bill-list">{{ $row['bill_count'] }} bill{{ $row['bill_count'] == 1 ? '' : 's' }}: {{ $billTitles }}</div>
                    @endif
                </td>
                <td>{{ $row['class_name'] }}</td>
                <td>{{ $row['term_name'] }}<br><span style="color:#94a3b8;">{{ $row['session_name'] }}</span></td>
                <td>
                    @if($row['scholarship'])
                        <span class="badge sch">{{ $row['scholarship']['title'] }}</span><br>
                    @endif
                    @forelse($row['discounts'] as $d)
                        <span class="badge disc">{{ $d['title'] }}</span>
                    @empty
                        @if(!$row['scholarship'])
                            <span class="badge none">—</span>
                        @endif
                    @endforelse
                </td>
                <td class="text-end">{{ number_format($row['original_amount'],2) }}</td>
                <td class="text-end amt-paid">{{ number_format($row['amount_paid'],2) }}</td>
                <td class="text-end amt-out">{{ number_format($row['outstanding'],2) }}</td>
                <td class="text-end amt-save">{{ $row['savings'] > 0 ? number_format($row['savings'],2) : '—' }}</td>
                <td class="text-end">{{ $row['collection_rate'] }}%</td>
            </tr>
        @empty
            <tr><td colspan="11" style="text-align:center; padding:24px; color:#94a3b8;">No debtors found for the selected filters.</td></tr>
        @endforelse
        </tbody>
        @if($dataset->count())
        <tfoot>
            <tr>
                <td colspan="6" class="text-end">TOTALS</td>
                <td class="text-end">{{ number_format($totals['original'],2) }}</td>
                <td class="text-end">{{ number_format($totals['paid'],2) }}</td>
                <td class="text-end">{{ number_format($totals['outstanding'],2) }}</td>
                <td class="text-end">{{ number_format($totals['savings'],2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <p class="footer-note">This report was generated automatically by the school management system and reflects fee records as at the generation date/time above.</p>

</body>
</html>