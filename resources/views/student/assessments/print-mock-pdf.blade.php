<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mock Examination Report</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #e2e8f0;
            padding: 8mm 5mm;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        /* ── WATERMARK ── */
        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 64px;
            font-weight: 900;
            color: rgba(201, 168, 76, 0.10);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 6px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
            border: 3px double rgba(201, 168, 76, 0.18);
            padding: 12px 32px;
            border-radius: 24px;
        }

        /* ── CARD ── */
        .report-card {
            max-width: 200mm;
            width: 100%;
            margin: 0 auto;
            background: white;
            border: 3px solid #0f172a;
            border-radius: 6px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-inner {
            padding: 12px 14px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        /* ── SCHOOL HEADER ── */
        .school-name-header {
            background: #111827;
            color: white;
            padding: 10px 14px 8px;
            border-top: 2px solid #c9a84c;
            border-bottom: 2px solid #c9a84c;
            text-align: center;
            margin-bottom: 4px;
        }
        .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 21px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .motto {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 3px;
            opacity: .95;
        }

        /* ── LOGO / CONTACT / PHOTO ROW ── */
        .header-table { width:100%; border-collapse:collapse; margin:6px 0 4px; }
        .school-logo {
            width:72px; height:80px;
            border:2px solid #2c7a4d; border-radius:10px;
            background:white; padding:4px;
            text-align:center; margin:0 auto;
        }
        .school-logo img { max-width:100%; max-height:100%; object-fit:contain; }
        .photo-frame {
            width:72px; height:80px;
            border:2px solid #c9a84c; border-radius:10px;
            overflow:hidden; margin-left:auto; background:#f1f5f9;
        }
        .photo-frame img { width:100%; height:100%; object-fit:cover; }
        .contact-info { width:100%; font-size:9.5px; border-collapse:collapse; }
        .contact-info td { padding:3px 4px; }
        .contact-label { font-weight:900; color:#1e40af; width:58px; }

        /* ── DIVIDERS ── */
        .divider-dark  { height:2px; background:#1e40af; margin:5px 0 2px; }
        .divider-light { height:1px; background:#94a3b8; margin:2px 0 4px; }

        /* ── REPORT TITLE BAND ── */
        .report-title {
            background: linear-gradient(135deg, #0f1c35 0%, #1a2f55 100%);
            color: white;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            padding: 9px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            border: 2px solid #c9a84c;
        }
        .report-title .title-badge {
            background: #c9a84c;
            color: #0f1c35;
            font-size: 10px;
            font-weight: 900;
            padding: 2px 12px;
            border-radius: 20px;
            margin-left: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        /* ── SESSION INFO BOX ── */
        .mock-info-box {
            background: linear-gradient(135deg, #0f1c35, #1a2f55);
            border: 2px solid #c9a84c;
            border-radius: 8px;
            padding: 8px 14px;
            color: white;
            font-size: 9px;
            text-align: center;
            margin: 4px 0;
        }
        .mock-info-box strong { color: #c9a84c; }

        /* ── STUDENT INFO BAR ── */
        .student-info-bar {
            background: linear-gradient(135deg, #fffbea, #ffffff);
            border: 1.8px solid #c9a84c;
            border-radius: 10px;
            padding: 8px 12px;
            margin: 4px 0;
        }
        .info-table { width:100%; }
        .info-table td { padding:4px 6px; text-align:center; font-size:10px; }
        .info-badge {
            background: #fef3c7;
            padding: 3px 10px;
            border-radius: 30px;
            font-weight: 900;
            color: #92400e;
            display: inline-block;
        }
        .info-value { font-weight:900; margin-left:5px; font-size:10.5px; }

        /* ── STATS STRIP ── */
        .stats-strip {
            display: flex;
            gap: 0;
            border: 1.5px solid #0f1c35;
            border-radius: 8px;
            overflow: hidden;
            margin: 6px 0;
        }
        .stat-cell {
            flex: 1;
            text-align: center;
            padding: 8px 4px;
            border-right: 1px solid #cbd5e1;
            background: #f8fafc;
        }
        .stat-cell:last-child { border-right: none; }
        .stat-cell.gold-cell { background: #fef9e3; }
        .stat-label { font-size: 7.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        .stat-value { font-size: 14px; font-weight: 900; color: #0f1c35; margin-top: 2px; }
        .stat-value.gold-val { color: #92400e; }

        /* ── RESULT TABLE WRAPPER ── */
        .result-wrapper {
            margin: 8px 0;
            border: 2px solid #0f1c35;
            border-radius: 6px;
            overflow-x: auto;
        }
        .result-table { width:100%; border-collapse:collapse; font-size:8.8px; }

        /* Table heading row 1 — banner */
        .result-table thead tr.banner-row th {
            background: #0f1c35;
            color: white;
            border: 1.2px solid #000;
            padding: 7px 6px;
            font-size: 9px;
            font-weight: 800;
            text-align: center;
            letter-spacing: .3px;
        }

        /* Table heading row 2 — column labels */
        .result-table thead tr.col-label-row th {
            background: #0b2b44;
            color: white;
            border: 1.2px solid #000;
            padding: 6px 4px;
            font-size: 8px;
            text-align: center;
            font-weight: 800;
            white-space: nowrap;
        }
        .result-table thead tr.col-label-row th.left-align { text-align:left; padding-left:8px; }

        /* Body cells */
        .result-table tbody td {
            border: 1.2px solid #000;
            padding: 5px 4px;
            text-align: center;
            font-size: 8.8px;
            background: white;
            font-weight: 600;
            vertical-align: middle;
        }
        .subject-name-cell {
            text-align: left !important;
            font-weight: 800;
            padding-left: 8px !important;
            background: #fef9e3 !important;
        }

        /* Grade colours */
        .grade-A { color:#15803d; font-weight:900; }
        .grade-B { color:#1d4ed8; font-weight:900; }
        .grade-C { color:#b45309; font-weight:900; }
        .grade-D { color:#e11d48; font-weight:900; }
        .grade-F { color:#b91c1c; font-weight:900; }

        /* Position medals */
        .pos-1 { background-color:#FFD966 !important; color:#000; font-weight:900; }
        .pos-2 { background-color:#D1D5DB !important; color:#000; font-weight:900; }
        .pos-3 { background-color:#E6B17E !important; color:#000; font-weight:900; }

        .highlight-red { color:#dc2626; font-weight:900; }

        /* Mini score bar */
        .score-bar  { height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin-top:4px; }
        .score-fill { height:100%; border-radius:3px; }

        /* ── TOTALS STRIP ── */
        .totals-summary {
            background: #0b2b44;
            color: white;
            font-weight: 900;
            font-size: 10px;
            text-align: center;
            padding: 8px;
            border-radius: 40px;
            margin: 5px 0;
        }

        /* ── PERFORMANCE BADGE ── */
        .perf-badge {
            margin: 4px 0;
            padding: 7px 12px;
            border-radius: 40px;
            font-weight: 800;
            text-align: center;
            font-size: 9.5px;
            border: 1.5px solid;
        }
        .perf-excellent { background:#dcfce7; border-color:#15803d; color:#14532d; }
        .perf-good      { background:#dbeafe; border-color:#1d4ed8; color:#1e3a5f; }
        .perf-average   { background:#fef3c7; border-color:#ca8a04; color:#854d0e; }
        .perf-poor      { background:#fee2e2; border-color:#b91c1c; color:#7f1d1d; }

        /* ── GRADE DISTRIBUTION BOX ── */
        .grade-dist-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 8.5px;
            margin: 4px 0;
        }

        /* ── REMARKS TABLE ── */
        .remarks-table { width:100%; border-collapse:collapse; margin:6px 0; border:2px solid #000; }
        .remarks-table td { border:1.2px solid #000; padding:7px 9px; vertical-align:top; font-size:9px; background:white; }
        .remark-title { font-weight:900; font-size:9.5px; border-bottom:1.5px solid #94a3b8; display:inline-block; margin-bottom:5px; }

        /* ── FOOTER ── */
        .bottom-strip {
            margin-top: 6px;
            border-top: 1.8px solid #cbd5e1;
            background: #f8fafc;
            padding: 6px 0 2px;
        }
        .strip-table { width:100%; }
        .strip-table td { padding:5px 4px; vertical-align:middle; text-align:center; }
        .qr-img    { width:55px; height:55px; display:block; margin:0 auto; }
        .qr-label  { font-size:7px; font-weight:700; margin-top:2px; }
        .stamp-img { width:75px; transform:rotate(-6deg); }
        .sign-line { border-bottom:1.5px dotted #1e293b; min-width:100px; display:inline-block; margin:0 6px; }
        .powered   { font-size:7px; color:#475569; margin-top:4px; }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 40px 24px;
            color: #7b85a3;
            font-size: 12px;
        }

        @media print {
            body { background:white; padding:0; margin:0; }
            .report-card { box-shadow:none; margin:0; }
            .watermark-text { color:rgba(201,168,76,0.08); -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>

<div class="watermark-text">MOCK EXAMINATION</div>

@php
    function mockOrdinal($n) {
        if (!is_numeric($n) || $n <= 0) return '-';
        if ($n % 100 >= 11 && $n % 100 <= 13) return $n.'th';
        return $n . match($n % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
    }

    $admNo     = $student->admissionNo ?? 'N/A';
    $fullName  = trim(strtoupper($student->lastname ?? '').' '.($student->firstname ?? '').' '.($student->othername ?? ''));
    $armName   = $schoolclass->arms->arm ?? '';
    $className = trim(($schoolclass->schoolclass ?? '').' '.$armName);

    $qrData   = "Student: {$fullName}\nAdm: {$admNo}\nClass: {$className}\nTerm: {$term}\nSession: {$session}\nType: Mock Exam";
    $qrBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(220)->errorCorrection('H')->generate($qrData));

    $pct = $mockSummary['percentage'] ?? 0;
    $perfBadgeClass = $pct >= 70 ? 'perf-excellent' : ($pct >= 55 ? 'perf-good' : ($pct >= 40 ? 'perf-average' : 'perf-poor'));
    $perfText       = $pct >= 70
        ? '🌟 EXCELLENT PERFORMANCE'
        : ($pct >= 55
            ? '👍 GOOD PERFORMANCE'
            : ($pct >= 40
                ? '⚠️ AVERAGE PERFORMANCE — NEEDS IMPROVEMENT'
                : '🚨 POOR PERFORMANCE — URGENT ATTENTION REQUIRED'));

    $avgScore = $mockRows->count() > 0
        ? round(($mockSummary['obtained'] ?? 0) / $mockRows->count(), 1)
        : 0;

    $gradeCount = $mockRows->groupBy(fn($r) => strtoupper(substr($r->grade ?? 'F', 0, 1)))->map->count();

    $gradeColors = [
        'A' => ['bg' => '#dcfce7', 'color' => '#15803d'],
        'B' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
        'C' => ['bg' => '#fef3c7', 'color' => '#92400e'],
        'D' => ['bg' => '#ffe4cc', 'color' => '#9a3412'],
        'F' => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
    ];
@endphp

<div class="report-card">
    <div class="card-inner">

        {{-- ── SCHOOL HEADER ── --}}
        <div class="school-name-header">
            <div class="school-full-name">{{ $schoolInfo->school_name ?? 'PREMIER ACADEMY' }}</div>
            <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE & INTEGRITY' }}</div>
        </div>

        {{-- ── LOGO / CONTACT / PHOTO ── --}}
        <table class="header-table">
            <tr>
                <td width="18%" style="text-align:center;">
                    <div class="school-logo"><img src="{{ $logoBase64 }}" alt="logo"></div>
                </td>
                <td>
                    <table class="contact-info">
                        <tr><td class="contact-label">Address:</td><td>{{ $schoolInfo->school_address ?? '—' }}</td></tr>
                        <tr><td class="contact-label">Phone:</td><td>{{ $schoolInfo->formatted_phones ?? '—' }}</td></tr>
                        <tr><td class="contact-label">Email:</td><td>{{ $schoolInfo->school_email ?? '—' }}</td></tr>
                        <tr><td class="contact-label">Website:</td><td>{{ $schoolInfo->school_website ?? '—' }}</td></tr>
                    </table>
                </td>
                <td width="20%" style="text-align:right;">
                    <div class="photo-frame">
                        <img src="{{ $pictureBase64 }}" alt="student photo">
                    </div>
                </td>
            </tr>
        </table>

        <div class="divider-dark"></div>
        <div class="divider-light"></div>

        {{-- ── REPORT TITLE ── --}}
        <div class="report-title">
            MOCK EXAMINATION RESULT REPORT
            <span class="title-badge">{{ strtoupper($term) }}</span>
        </div>

        {{-- ── SESSION INFO ── --}}
        <div class="mock-info-box">
            Academic Session: <strong>{{ $session }}</strong>
            &nbsp;|&nbsp; Term: <strong>{{ $term }}</strong>
            &nbsp;|&nbsp; Subjects Taken: <strong>{{ $mockRows->count() }}</strong>
            &nbsp;|&nbsp; Class Size: <strong>{{ $numberOfStudents }}</strong>
        </div>

        {{-- ── STUDENT INFO BAR ── --}}
        <div class="student-info-bar">
            <table class="info-table">
                <tr>
                    <td><span class="info-badge">NAME</span> <span class="info-value">{{ $fullName }}</span></td>
                    <td><span class="info-badge">ADM NO</span> <span class="info-value">{{ $admNo }}</span></td>
                    <td><span class="info-badge">CLASS</span> <span class="info-value">{{ $className }}</span></td>
                    <td><span class="info-badge">SESSION</span> <span class="info-value">{{ $session }}</span></td>
                </tr>
            </table>
        </div>

        @if($mockRows->isEmpty())

            {{-- ── EMPTY STATE ── --}}
            <div class="empty-state">
                <div style="font-size:28px;margin-bottom:10px;">📋</div>
                No mock examination results are available for the selected term and session.
            </div>

        @else

        {{-- ── STATS STRIP ── --}}
        <div class="stats-strip">
            <div class="stat-cell">
                <div class="stat-label">Subjects</div>
                <div class="stat-value">{{ $mockRows->count() }}</div>
            </div>
            <div class="stat-cell gold-cell">
                <div class="stat-label">Total Obtained</div>
                <div class="stat-value gold-val">{{ number_format($mockSummary['obtained'] ?? 0, 1) }}</div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">Total Obtainable</div>
                <div class="stat-value">{{ $mockSummary['obtainable'] ?? 0 }}</div>
            </div>
            <div class="stat-cell gold-cell">
                <div class="stat-label">Overall %</div>
                <div class="stat-value gold-val">{{ $mockSummary['percentage'] ?? 0 }}%</div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">Average</div>
                <div class="stat-value">{{ $avgScore }}</div>
            </div>
            <div class="stat-cell" style="background:#d1fae5;">
                <div class="stat-label">Highest</div>
                <div class="stat-value" style="color:#065f46;">{{ number_format($mockRows->max('total') ?? 0, 1) }}</div>
            </div>
            <div class="stat-cell" style="background:#fee2e2;">
                <div class="stat-label">Lowest</div>
                <div class="stat-value" style="color:#991b1b;">{{ number_format($mockRows->min('total') ?? 0, 1) }}</div>
            </div>
        </div>

        {{-- ── MOCK RESULTS TABLE ── --}}
        <div class="result-wrapper">
            <table class="result-table">
                <thead>
                    <tr class="banner-row">
                        <th colspan="9">
                            🏆&nbsp; MOCK EXAMINATION SCORES — {{ strtoupper($term) }} &nbsp;|&nbsp; {{ strtoupper($session) }}
                        </th>
                    </tr>
                    <tr class="col-label-row">
                        <th style="width:20px;">#</th>
                        <th class="left-align" style="width:145px;">SUBJECT</th>
                        <th style="width:40px;">EXAM<br><span style="font-size:6px;">(Score)</span></th>
                        <th style="width:55px;">TOTAL<br><span style="font-size:6px;">(/100)</span></th>
                        <th style="width:38px;">GRADE</th>
                        <th style="width:55px;">REMARK</th>
                        <th style="width:40px;">POSITION</th>
                        <th style="width:42px;">CLASS AVG</th>
                        <th style="width:52px;">MIN / MAX</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mockRows as $mi => $mock)
                    @php
                        $mTotal   = (float)($mock->total ?? 0);
                        $mExam    = (float)($mock->exam ?? 0);
                        $gRaw     = $mock->grade ?? '-';
                        $gLetter  = ($gRaw !== '-') ? strtoupper(substr($gRaw, 0, 1)) : 'F';
                        $gradeStyle = match($gLetter) { 'A'=>'grade-A','B'=>'grade-B','C'=>'grade-C','D'=>'grade-D', default=>'grade-F' };
                        $mPos     = $mock->position ?? null;
                        $posClass = ($mPos == 1) ? 'pos-1' : (($mPos == 2) ? 'pos-2' : (($mPos == 3) ? 'pos-3' : ''));
                        $barColor = $mTotal >= 70 ? '#15803d' : ($mTotal >= 55 ? '#1d4ed8' : ($mTotal >= 40 ? '#d97706' : '#dc2626'));
                        $isLow    = $mTotal < 40;
                    @endphp
                    <tr>
                        <td>{{ $mi + 1 }}</td>
                        <td class="subject-name-cell">
                            <div style="font-weight:800;color:#0f1c35;">{{ $mock->subject_name ?? '—' }}</div>
                            @if(!empty($mock->subject_code))
                                <div style="font-size:7.5px;color:#94a3b8;font-weight:600;">{{ $mock->subject_code }}</div>
                            @endif
                        </td>
                        <td @if($isLow) class="highlight-red" @endif>
                            {{ number_format($mExam, 1) }}
                        </td>
                        <td @if($isLow) class="highlight-red" @endif>
                            <div style="font-weight:700;">{{ number_format($mTotal, 1) }}</div>
                            <div class="score-bar">
                                <div class="score-fill" style="width:{{ min($mTotal, 100) }}%;background:{{ $barColor }};"></div>
                            </div>
                        </td>
                        <td class="{{ $gradeStyle }}">{{ $gRaw }}</td>
                        <td style="font-size:7.5px;color:#6b7280;">{{ $mock->remark ?? '—' }}</td>
                        <td class="{{ $posClass }}" style="font-weight:700;">{{ mockOrdinal($mPos) }}</td>
                        <td style="color:#64748b;">{{ number_format($mock->class_average ?? 0, 1) }}</td>
                        <td>
                            <span style="color:#dc2626;font-weight:700;">{{ number_format($mock->cmin ?? 0, 1) }}</span>
                            /
                            <span style="color:#15803d;font-weight:700;">{{ number_format($mock->cmax ?? 0, 1) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── TOTALS SUMMARY ── --}}
        <div class="totals-summary">
            🎯 TOTAL OBTAINED: {{ number_format($mockSummary['obtained'] ?? 0, 1) }}
            &nbsp;|&nbsp; TOTAL OBTAINABLE: {{ $mockSummary['obtainable'] ?? 0 }}
            &nbsp;|&nbsp; PERCENTAGE: {{ $mockSummary['percentage'] ?? 0 }}%
            &nbsp;|&nbsp; AVERAGE: {{ $avgScore }} / 100
        </div>

        {{-- ── PERFORMANCE BADGE ── --}}
        <div class="perf-badge {{ $perfBadgeClass }}"><strong>{{ $perfText }}</strong></div>

        {{-- ── GRADE DISTRIBUTION ── --}}
        <div class="grade-dist-box">
            <strong style="font-size:9px;color:#0f1c35;">GRADE DISTRIBUTION:&nbsp;&nbsp;</strong>
            @foreach(['A','B','C','D','F'] as $gl)
                @if(($gradeCount[$gl] ?? 0) > 0)
                    <span style="
                        background:{{ $gradeColors[$gl]['bg'] }};
                        color:{{ $gradeColors[$gl]['color'] }};
                        padding:2px 9px;
                        border-radius:20px;
                        font-weight:800;
                        margin-right:4px;
                        font-size:8.5px;
                    ">{{ $gl }}: {{ $gradeCount[$gl] }}</span>
                @endif
            @endforeach
        </div>

        {{-- ── REMARKS ── --}}
        <table class="remarks-table">
            <tr>
                <td width="50%">
                    <span class="remark-title">📖 CLASS TEACHER'S REMARK</span><br>
                    <span style="font-style:italic;color:#374151;">
                        Based on mock performance — keep striving for excellence.
                    </span>
                </td>
                <td width="50%">
                    <span class="remark-title">🏛️ PRINCIPAL'S REMARK</span><br>
                    <span style="font-style:italic;color:#374151;">
                        Use this result to identify areas for improvement before the terminal examination.
                    </span>
                </td>
            </tr>
        </table>

        @endif {{-- end mockRows check --}}

        {{-- ── FOOTER ── --}}
        <div class="bottom-strip">
            <table class="strip-table">
                <tr>
                    <td width="22%">
                        <img class="qr-img" src="data:image/png;base64,{{ $qrBase64 }}" alt="QR">
                        <div class="qr-label">Verify with portal</div>
                    </td>
                    <td width="56%" style="text-align:center;">
                        <div><strong>Report Type:</strong> Mock Examination &nbsp;|&nbsp; <strong>Term:</strong> {{ $term }}</div>
                        <div style="margin:5px 0;"><strong>Issued:</strong> <span class="sign-line">{{ now()->format('jS F, Y') }}</span></div>
                        <div><strong>Parent / Guardian Signature:</strong> <span class="sign-line"> ________________________ </span></div>
                        <div style="margin-top:6px;">
                            <em style="font-size:7.5px;color:#64748b;">
                                ⚠️ This is a mock examination result — not for official certification purposes.<br>
                                The terminal examination result will be issued at end of term.
                            </em>
                        </div>
                        <div class="powered">🔹 Powered by Qudroid Systems 🔹</div>
                    </td>
                    <td width="22%">
                        <img class="stamp-img" src="{{ $stampBase64 }}" alt="stamp">
                    </td>
                </tr>
            </table>
        </div>

    </div>{{-- .card-inner --}}
</div>{{-- .report-card --}}

</body>
</html>
