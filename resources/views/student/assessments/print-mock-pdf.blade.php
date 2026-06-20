<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mock Examination Report | Student Copy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #e2e8f0;
            padding: 8mm 5mm;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 68px;
            font-weight: 900;
            color: rgba(201, 168, 76, 0.09);
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

        .student-section {
            max-width: 210mm;
            width: 100%;
            margin: 0 auto;
            background: white;
            border: 3px solid #0f172a;
            border-radius: 6px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            /* Prevent overflow to next page */
            page-break-inside: avoid;
            page-break-after: avoid;
            break-inside: avoid;
            max-height: 297mm; /* A4 height */
            overflow: hidden;
        }

        .card-inner {
            padding: 10px 14px 10px 14px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow: hidden;
            height: 100%;
        }

        /* SCHOOL HEADER - reduced padding */
        .school-name-header {
            background: #111827;
            color: white;
            padding: 6px 14px 5px 14px;
            border: 2px solid #c9a84c;
            border-left: none;
            border-right: none;
            text-align: center;
            margin-bottom: 2px;
            flex-shrink: 0;
        }
        .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .motto {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 2px;
            opacity: 0.95;
        }

        /* HEADER TABLE - reduced sizes */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 2px;
            flex-shrink: 0;
        }
        .school-logo {
            width: 60px;
            height: 68px;
            border: 2px solid #2c7a4d;
            border-radius: 8px;
            background: white;
            padding: 3px;
            text-align: center;
            margin: 0 auto;
        }
        .school-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .photo-frame {
            width: 60px;
            height: 68px;
            border: 2px solid #c9a84c;
            border-radius: 8px;
            overflow: hidden;
            margin-left: auto;
            background: #f1f5f9;
        }
        .photo-frame img { width: 100%; height: 100%; object-fit: cover; }
        .contact-info { width: 100%; font-size: 8.5px; border-collapse: collapse; }
        .contact-info td { padding: 2px 3px; }
        .contact-label { font-weight: 900; color: #1e40af; width: 52px; }

        .divider-dark { height: 1.5px; background: #1e40af; margin: 3px 0 1px; flex-shrink: 0; }
        .divider-light { height: 1px; background: #94a3b8; margin: 1px 0 2px; flex-shrink: 0; }

        .report-title {
            background: linear-gradient(135deg, #0f1c35, #1a2f55);
            color: white;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
            padding: 5px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            border: 2px solid #c9a84c;
            flex-shrink: 0;
        }

        /* STUDENT INFO - reduced */
        .student-info-bar {
            background: linear-gradient(135deg, #fffbea, #ffffff);
            border: 1.8px solid #c9a84c;
            border-radius: 8px;
            padding: 4px 10px;
            margin: 2px 0;
            flex-shrink: 0;
        }
        .info-table { width: 100%; }
        .info-table td {
            padding: 2px 4px;
            text-align: center;
            font-size: 9px;
        }
        .info-badge {
            background: #fef3c7;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 900;
            color: #92400e;
            display: inline-block;
            font-size: 8px;
        }
        .info-value {
            font-weight: 900;
            margin-left: 4px;
            font-size: 9.5px;
        }

        /* STATS TABLE - more compact */
        .stats-strip-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 3px 0;
            flex-shrink: 0;
        }

        .stats-strip-table td {
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
            padding: 3px 2px;
        }

        .stats-strip-table .stat-cell {
            background: #f8fafc;
        }

        .stats-strip-table .gold-cell {
            background: #fef9e3;
        }

        .stats-strip-table .high-cell {
            background: #d1fae5;
        }

        .stats-strip-table .low-cell {
            background: #fee2e2;
        }

        .stat-label {
            font-size: 7px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .stat-value {
            font-size: 12px;
            font-weight: 900;
            color: #0f1c35;
            margin-top: 1px;
        }

        .gold-val {
            color: #92400e;
        }

        .high-val {
            color: #065f46;
        }

        .low-val {
            color: #991b1b;
        }

        /* RESULT TABLE - scrollable container */
        .result-wrapper {
            margin: 3px 0;
            border: 2px solid #0f1c35;
            border-radius: 4px;
            overflow: hidden;
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .result-table-scroll {
            overflow-y: auto;
            flex: 1;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .result-table th {
            background: #0b2b44;
            color: white;
            border: 1.2px solid #000;
            padding: 3px 2px;
            font-size: 8px;
            text-align: center;
            font-weight: 800;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .result-table td {
            border: 1.2px solid #000;
            padding: 3px 2px;
            text-align: center;
            font-size: 8.5px;
            background: white;
            font-weight: 600;
        }
        .subject-name-cell {
            text-align: left;
            font-weight: 800;
            padding-left: 6px;
            background: #fef9e3;
        }
        .highlight-red { color: #dc2626; font-weight: 900; }

        .grade-A { color: #15803d; font-weight: 900; font-size: 9px; }
        .grade-B { color: #1d4ed8; font-weight: 900; font-size: 9px; }
        .grade-C { color: #b45309; font-weight: 900; font-size: 9px; }
        .grade-D { color: #e11d48; font-weight: 900; font-size: 9px; }
        .grade-F { color: #b91c1c; font-weight: 900; font-size: 9px; }

        .pos-1 { background-color: #FFD966 !important; color: #000; font-weight: 900; }
        .pos-2 { background-color: #D1D5DB !important; color: #000; font-weight: 900; }
        .pos-3 { background-color: #E6B17E !important; color: #000; font-weight: 900; }

        .score-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 2px;
            overflow: hidden;
        }
        .score-fill {
            height: 100%;
            border-radius: 2px;
        }

        .totals-summary {
            background: #0b2b44;
            color: white;
            font-weight: 900;
            font-size: 9.5px;
            text-align: center;
            padding: 4px;
            border-radius: 30px;
            margin: 3px 0;
            flex-shrink: 0;
        }

        .perf-badge {
            margin: 2px 0;
            padding: 4px 10px;
            border-radius: 30px;
            font-weight: 800;
            text-align: center;
            font-size: 9px;
            border: 1.8px solid;
            flex-shrink: 0;
        }
        .perf-excellent { background: #dcfce7; border-color: #15803d; color: #14532d; }
        .perf-good { background: #dbeafe; border-color: #1d4ed8; color: #1e3a5f; }
        .perf-average { background: #fef3c7; border-color: #ca8a04; color: #854d0e; }
        .perf-poor { background: #fee2e2; border-color: #b91c1c; color: #7f1d1d; }

        .grade-dist-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 8.5px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: center;
            flex-shrink: 0;
        }

        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            border: 2px solid #000;
            flex-shrink: 0;
        }
        .remarks-table td {
            border: 1.2px solid #000;
            padding: 4px 7px;
            vertical-align: top;
            font-size: 8.5px;
            background: white;
        }
        .remark-title {
            font-weight: 900;
            font-size: 8.5px;
            border-bottom: 1.5px solid #94a3b8;
            display: inline-block;
            margin-bottom: 3px;
        }

        .bottom-strip {
            margin-top: 3px;
            border-top: 1.8px solid #cbd5e1;
            background: #f8fafc;
            padding: 4px 0 2px;
            flex-shrink: 0;
        }
        .strip-table { width: 100%; }
        .strip-table td {
            padding: 3px 3px;
            vertical-align: middle;
            text-align: center;
        }
        .qr-img {
            width: 45px;
            height: 45px;
            display: block;
            margin: 0 auto;
        }
        .qr-label {
            font-size: 6.5px;
            font-weight: 700;
            margin-top: 1px;
        }
        .stamp-img {
            width: 65px;
            transform: rotate(-6deg);
        }
        .sign-line {
            border-bottom: 1.5px dotted #1e293b;
            min-width: 80px;
            display: inline-block;
            margin: 0 4px;
        }
        .powered {
            font-size: 6.5px;
            color: #475569;
            margin-top: 2px;
        }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .student-section {
                box-shadow: none;
                margin: 0;
                page-break-after: avoid;
                page-break-inside: avoid;
                max-height: 100vh;
                border: 3px solid #0f172a;
            }
            .watermark-text { color: rgba(201,168,76,0.07); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .result-table-scroll { overflow-y: visible; }
            .result-table th { position: static; }
        }

        @media screen and (max-width: 768px) {
            .student-section { max-height: none; overflow: visible; }
            .result-table-scroll { overflow-y: visible; }
            .result-table th { position: static; }
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

        $admNo = $student->admissionNo ?? 'N/A';
        $fullName = trim(strtoupper($student->lastname ?? '').' '.($student->firstname ?? '').' '.($student->othername ?? ''));
        $armName = $schoolclass->arms->arm ?? '';
        $className = trim(($schoolclass->schoolclass ?? '').' '.$armName);
        $qrData = "Student: {$fullName}\nAdm: {$admNo}\nClass: {$className}\nTerm: {$term}\nSession: {$session}\nType: Mock Exam";
        $qrBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(220)->errorCorrection('H')->generate($qrData));

        $pct = $mockSummary['percentage'] ?? 0;
        $perfBadgeClass = $pct >= 70 ? 'perf-excellent' : ($pct >= 55 ? 'perf-good' : ($pct >= 40 ? 'perf-average' : 'perf-poor'));
        $perfText = $pct >= 70
            ? '🌟 EXCELLENT PERFORMANCE'
            : ($pct >= 55
                ? '👍 GOOD PERFORMANCE'
                : ($pct >= 40
                    ? '⚠️ AVERAGE PERFORMANCE — NEEDS IMPROVEMENT'
                    : '🚨 POOR PERFORMANCE — URGENT ATTENTION REQUIRED'));

        $avgScore = $mockRows->count() > 0
            ? round(($mockSummary['obtained'] ?? 0) / $mockRows->count(), 1)
            : 0;

        $highest = $mockRows->max('total') ?? 0;
        $lowest = $mockRows->min('total') ?? 0;

        $gradeCount = $mockRows->groupBy(fn($r) => strtoupper(substr($r->grade ?? 'F', 0, 1)))->map->count();

        $gradeColors = [
            'A' => ['bg' => '#dcfce7', 'color' => '#15803d'],
            'B' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
            'C' => ['bg' => '#fef3c7', 'color' => '#92400e'],
            'D' => ['bg' => '#ffe4cc', 'color' => '#9a3412'],
            'F' => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
        ];
    @endphp

    <div class="student-section">
        <div class="card-inner">
            {{-- SCHOOL HEADER --}}
            <div class="school-name-header">
                <div class="school-full-name">{{ $schoolInfo->school_name ?? 'PREMIER ACADEMY' }}</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE & INTEGRITY' }}</div>
            </div>

            {{-- LOGO + CONTACT + PHOTO --}}
            <table class="header-table">
                <tr>
                    <td width="18%" style="text-align:center">
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
                    <td width="20%" style="text-align:right">
                        <div class="photo-frame">
                            <img src="{{ $pictureBase64 }}" alt="student photo">
                        </div>
                    </td>
                </tr>
            </table>

            <div class="divider-dark"></div>
            <div class="divider-light"></div>

            {{-- REPORT TITLE --}}
            <div class="report-title">
                MOCK EXAMINATION RESULT REPORT — {{ strtoupper($term) }} {{ strtoupper($session) }}
            </div>

            {{-- STUDENT INFO --}}
            <div class="student-info-bar">
                <table class="info-table">
                    <tr>
                        <td><span class="info-badge">NAME</span> <span class="info-value">{{ $fullName }}</span></td>
                        <td><span class="info-badge">ADM NO</span> <span class="info-value">{{ $admNo }}</span></td>
                        <td><span class="info-badge">CLASS</span> <span class="info-value">{{ $className }}</span></td>
                    </tr>
                    <tr>
                        <td><span class="info-badge">SESSION</span> <span class="info-value">{{ $session }}</span></td>
                        <td><span class="info-badge">TERM</span> <span class="info-value">{{ $term }}</span></td>
                        <td><span class="info-badge">SUBJECTS</span> <span class="info-value">{{ $mockRows->count() }}</span></td>
                    </tr>
                </table>
            </div>

            @if($mockRows->isEmpty())
                <div style="text-align:center; padding:40px 20px; color:#64748b;">
                    <div style="font-size:32px; margin-bottom:8px;">📋</div>
                    No mock examination results available.
                </div>
            @else
            {{-- STATS STRIP --}}
            <table class="stats-strip-table">
                <tr>
                    <td class="stat-cell">
                        <div class="stat-label">SUBJECTS</div>
                        <div class="stat-value">{{ $mockRows->count() }}</div>
                    </td>
                    <td class="stat-cell gold-cell">
                        <div class="stat-label">TOTAL OBTAINED</div>
                        <div class="stat-value gold-val">
                            {{ number_format($mockSummary['obtained'] ?? 0, 1) }}
                        </div>
                    </td>
                    <td class="stat-cell">
                        <div class="stat-label">TOTAL OBTAINABLE</div>
                        <div class="stat-value">
                            {{ $mockSummary['obtainable'] ?? 0 }}
                        </div>
                    </td>
                    <td class="stat-cell gold-cell">
                        <div class="stat-label">OVERALL %</div>
                        <div class="stat-value gold-val">
                            {{ $mockSummary['percentage'] ?? 0 }}%
                        </div>
                    </td>
                    <td class="stat-cell">
                        <div class="stat-label">AVERAGE</div>
                        <div class="stat-value">
                            {{ $avgScore }}
                        </div>
                    </td>
                    <td class="stat-cell high-cell">
                        <div class="stat-label">HIGHEST</div>
                        <div class="stat-value high-val">
                            {{ number_format($highest, 1) }}
                        </div>
                    </td>
                    <td class="stat-cell low-cell">
                        <div class="stat-label">LOWEST</div>
                        <div class="stat-value low-val">
                            {{ number_format($lowest, 1) }}
                        </div>
                    </td>
                </tr>
            </table>

            {{-- RESULTS TABLE with scroll container --}}
            <div class="result-wrapper">
                <div class="result-table-scroll">
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th style="width:24px;">#</th>
                                <th style="width:120px; text-align:left; padding-left:6px;">SUBJECT</th>
                                <th style="width:40px;">EXAM</th>
                                <th style="width:48px;">TOTAL</th>
                                <th style="width:34px;">GRADE</th>
                                <th style="width:50px;">REMARK</th>
                                <th style="width:38px;">POSITION</th>
                                <th style="width:40px;">CLASS AVG</th>
                                <th style="width:50px;">MIN / MAX</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mockRows as $mi => $mock)
                            @php
                                $mTotal = (float)($mock->total ?? 0);
                                $mExam = (float)($mock->exam ?? 0);
                                $gRaw = $mock->grade ?? '-';
                                $gLetter = ($gRaw !== '-') ? strtoupper(substr($gRaw, 0, 1)) : 'F';
                                $gradeStyle = match($gLetter) { 'A'=>'grade-A','B'=>'grade-B','C'=>'grade-C','D'=>'grade-D', default=>'grade-F' };
                                $mPos = $mock->position ?? null;
                                $posClass = ($mPos == 1) ? 'pos-1' : (($mPos == 2) ? 'pos-2' : (($mPos == 3) ? 'pos-3' : ''));
                                $barColor = $mTotal >= 70 ? '#15803d' : ($mTotal >= 55 ? '#1d4ed8' : ($mTotal >= 40 ? '#d97706' : '#dc2626'));
                                $isLow = $mTotal < 40;
                            @endphp
                            <tr>
                                <td>{{ $mi + 1 }}</td>
                                <td class="subject-name-cell">
                                    {{ $mock->subject_name ?? '—' }}
                                    @if(!empty($mock->subject_code))<br><small style="color:#64748b;font-size:7px;">{{ $mock->subject_code }}</small>@endif
                                </td>
                                <td @if($isLow) class="highlight-red" @endif>{{ number_format($mExam, 1) }}</td>
                                <td @if($isLow) class="highlight-red" @endif>
                                    <div style="font-weight:700;">{{ number_format($mTotal, 1) }}</div>
                                    <div class="score-bar"><div class="score-fill" style="width:{{ min($mTotal,100) }}%; background:{{ $barColor }};"></div></div>
                                </td>
                                <td class="{{ $gradeStyle }}">{{ $gRaw }}</td>
                                <td style="font-size:7.8px; color:#6b7280;">{{ $mock->remark ?? '—' }}</td>
                                <td class="{{ $posClass }}">{{ mockOrdinal($mPos) }}</td>
                                <td>{{ number_format($mock->class_average ?? 0, 1) }}</td>
                                <td>
                                    <span style="color:#dc2626;">{{ number_format($mock->cmin ?? 0,1) }}</span> /
                                    <span style="color:#15803d;">{{ number_format($mock->cmax ?? 0,1) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TOTALS --}}
            <div class="totals-summary">
                🎯 TOTAL OBTAINED: {{ number_format($mockSummary['obtained'] ?? 0, 1) }}
                &nbsp;|&nbsp; TOTAL OBTAINABLE: {{ $mockSummary['obtainable'] ?? 0 }}
                &nbsp;|&nbsp; PERCENTAGE: {{ $mockSummary['percentage'] ?? 0 }}%
            </div>

            {{-- PERFORMANCE BADGE --}}
            <div class="perf-badge {{ $perfBadgeClass }}"><strong>{{ $perfText }}</strong></div>

            {{-- GRADE DISTRIBUTION --}}
            <div class="grade-dist-box">
                <strong>GRADE DISTRIBUTION:</strong>
                @foreach(['A','B','C','D','F'] as $gl)
                    @if(($gradeCount[$gl] ?? 0) > 0)
                        <span style="background:{{ $gradeColors[$gl]['bg'] }}; color:{{ $gradeColors[$gl]['color'] }}; padding:1px 9px; border-radius:20px; font-weight:800; font-size:8px;">
                            {{ $gl }}: {{ $gradeCount[$gl] }}
                        </span>
                    @endif
                @endforeach
            </div>

            {{-- REMARKS --}}
            <table class="remarks-table">
                <tr>
                    <td width="50%">
                        <span class="remark-title">📖 CLASS TEACHER'S REMARK</span><br>
                        Based on mock performance — keep striving for excellence.
                    </td>
                    <td width="50%">
                        <span class="remark-title">🏛️ PRINCIPAL'S REMARK</span><br>
                        Use this result to identify areas for improvement before the terminal examination.
                    </td>
                </tr>
            </table>
            @endif

            {{-- FOOTER --}}
            <div class="bottom-strip">
                <table class="strip-table">
                    <tr>
                        <td width="20%">
                            <img class="qr-img" src="data:image/png;base64,{{ $qrBase64 }}" alt="QR">
                            <div class="qr-label">Verify with portal</div>
                        </td>
                        <td width="58%" style="text-align:center">
                            <div style="font-size:8.5px;"><strong>Issued:</strong> <span class="sign-line">{{ now()->format('jS F, Y') }}</span></div>
                            <div style="margin:3px 0;font-size:8.5px;"><strong>Parent/Guardian Signature:</strong> <span class="sign-line"> ________________________ </span></div>
                            <div class="powered">🔹 Powered by Qudroid Systems 🔹</div>
                        </td>
                        <td width="22%">
                            <img class="stamp-img" src="{{ $stampBase64 }}" alt="stamp">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
