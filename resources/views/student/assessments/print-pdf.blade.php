<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Terminal Progress Report | Student Academic Result</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #f0f2f5;
            padding: 8px 0;
            text-align: center;
        }

        /* WATERMARK (STUDENT COPY) */
        .watermark-text {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 72px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.035);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 8px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
        }

        /* CARD STYLES - each student gets a clean page */
        .student-section {
            width: 100%;
            max-width: 200mm;
            margin: 0 auto 12px auto;
            background: #ffffff;
            border: 3px double #1e293b;
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid;
            position: relative;
            text-align: left;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .student-section:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        /* SCHOOL HEADER (DARK BANNER) */
        .school-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 8px 12px;
            text-align: center;
            border-bottom: 3px solid #fbbf24;
        }
        .school-name {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-family: 'Arial Black', sans-serif;
        }
        .motto {
            font-size: 8.5px;
            letter-spacing: 2px;
            font-weight: 500;
            opacity: 0.9;
            margin-top: 3px;
        }

        /* INFO GRID */
        .info-grid {
            background: #fefce8;
            border: 1px solid #d1d5db;
            margin: 8px 10px;
            padding: 5px 8px;
            border-radius: 6px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 6px;
            font-size: 9px;
            border: none;
            text-align: left;
            vertical-align: top;
        }
        .info-label {
            font-weight: 800;
            color: #0f3b5c;
            background: #e6f0fa;
            display: inline-block;
            width: 70px;
            border-radius: 12px;
            padding: 1px 6px;
            font-size: 8px;
        }

        /* RESULT TABLE */
        .result-wrapper {
            padding: 0 8px;
            margin: 6px 0;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            font-size: 8.2px;
        }
        .result-table th {
            background: #0b2b44;
            color: white;
            font-weight: 800;
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            font-size: 7.8px;
        }
        .result-table td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            font-size: 8.2px;
            background: #fff;
            font-weight: 600;
        }
        .subject-name-cell {
            text-align: left;
            font-weight: 800;
            padding-left: 6px;
            background: #fef9e3;
        }
        .highlight-red {
            color: #dc2626;
            font-weight: 900;
        }

        /* GRADE COLORS */
        .grade-A { color: #15803d; font-weight: 900; }
        .grade-B { color: #1d4ed8; font-weight: 900; }
        .grade-C { color: #b45309; font-weight: 900; }
        .grade-D { color: #e11d48; font-weight: 900; }
        .grade-F { color: #b91c1c; font-weight: 900; }

        /* POSITION BADGES */
        .pos-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 20px;
            font-weight: 900;
            font-size: 8px;
        }
        .pos-1 { background: #fbbf24; color: #1e1b2c; font-weight: 900; }
        .pos-2 { background: #cbd5e1; color: #0f172a; font-weight: 900; }
        .pos-3 { background: #cd7f32; color: white; font-weight: 900; }

        /* TOTALS BANNER */
        .totals-banner {
            background: #0b2b44;
            color: white;
            font-weight: 800;
            text-align: center;
            padding: 6px;
            margin: 6px 10px;
            border-radius: 30px;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        /* PROMOTION / STATUS CARD */
        .promo-card {
            margin: 6px 10px;
            border-radius: 12px;
            border-left: 8px solid;
            padding: 6px 10px;
            background: #f8fafc;
            font-size: 9px;
        }
        .promo-promoted { border-left-color: #16a34a; background: #f0fdf4; }
        .promo-repeated { border-left-color: #dc2626; background: #fef2f2; }
        .promo-awaiting { border-left-color: #f59e0b; background: #fffbeb; }

        /* ATTENDANCE SECTION */
        .attendance-box {
            margin: 8px 10px;
            border: 1px solid #0d9488;
            border-radius: 12px;
            overflow: hidden;
        }
        .att-head {
            background: #0d9488;
            color: white;
            font-weight: 800;
            padding: 5px;
            text-align: center;
            font-size: 9px;
        }
        .att-grid {
            display: flex;
            flex-wrap: wrap;
            background: #f0fdfa;
            padding: 6px;
            gap: 8px;
            justify-content: space-between;
        }
        .att-item {
            flex: 1;
            text-align: center;
            border-right: 1px solid #b4dfd6;
        }
        .att-item:last-child { border-right: none; }
        .att-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .att-value {
            font-weight: 900;
            font-size: 11px;
        }
        .progress-bar-container {
            background: #e2e8f0;
            border-radius: 20px;
            height: 6px;
            margin: 4px 8px 8px 8px;
        }
        .progress-fill {
            background: #22c55e;
            height: 6px;
            border-radius: 20px;
            width: 0%;
        }
        .progress-fill.warning {
            background: #f97316;
        }

        /* REMARKS TABLE */
        .remarks-table {
            width: calc(100% - 20px);
            margin: 6px 10px;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .remarks-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 8.5px;
        }
        .remark-title {
            font-weight: 800;
            font-size: 9px;
            border-bottom: 1px dotted #aaa;
            margin-bottom: 4px;
            display: inline-block;
        }

        /* BOTTOM STRIP (SIGNATURE + QR + STAMP) */
        .bottom-strip {
            margin-top: 8px;
            background: #f1f5f9;
            border-top: 2px solid #94a3b8;
            width: 100%;
        }
        .strip-table {
            width: 100%;
            border-collapse: collapse;
        }
        .strip-table td {
            padding: 8px 6px;
            text-align: center;
            border: none;
            vertical-align: middle;
        }
        .qr-code img {
            width: 55px;
            height: 55px;
        }
        .stamp-img img {
            width: 80px;
            opacity: 0.85;
        }
        .sign-line {
            border-bottom: 1px dotted #334155;
            min-width: 100px;
            display: inline-block;
            margin: 0 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .student-section {
                box-shadow: none;
                break-inside: avoid;
                page-break-after: always;
            }
            .watermark-text {
                color: rgba(0, 0, 0, 0.04);
            }
        }
    </style>
</head>
<body>
<div class="watermark-text">TERMINAL REPORT • STUDENT COPY</div>

@php
    // helper function for ordinal positions
    function ordinalPosition($num) {
        if (!is_numeric($num) || $num <= 0) return '-';
        $last = $num % 10;
        $lastTwo = $num % 100;
        if ($lastTwo >= 11 && $lastTwo <= 13) return $num . 'th';
        return $num . (in_array($last, [1,2,3]) ? ($last == 1 ? 'st' : ($last == 2 ? 'nd' : 'rd')) : 'th');
    }

    // default columns to show if nothing selected
    $selectedCols = $metadata['selected_columns'] ?? [];
    $defaultCols = ['sn', 'name', 'total', 'cum', 'grade', 'position', 'class_average'];
    $columnsToShow = !empty($selectedCols) ? $selectedCols : $defaultCols;
@endphp

@foreach ($allStudentData as $studentData)
@php
    $schoolInfo    = $studentData['schoolInfo'] ?? null;
    $student       = ($studentData['students'] ?? collect())->first();
    $scores        = $studentData['scores'] ?? collect();
    $assessments   = $studentData['assessments'] ?? collect();
    $totals        = $studentData['totals_summary'] ?? [];
    $attendance    = $studentData['attendance_summary'] ?? [];
    $profileRemark = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
    $gpaData       = $studentData['gpa_data'] ?? [];

    // Build student display name
    $fullName = trim(strtoupper(($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? '')));
    if (empty($fullName)) $fullName = $student->name ?? 'STUDENT';

    $className = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arm_name ?? ''));
    $session   = $metadata['session'] ?? '2025/2026';
    $term      = $metadata['term'] ?? 'SECOND TERM';
    $numInClass= $studentData['numberOfStudents'] ?? '—';

    // Promotional logic (mock from data if exists)
    $promoStatus = $studentData['promotion_result']['status'] ?? 'awaiting';
    $isPromoTerm = $studentData['promotion_result']['is_promotional_term'] ?? true;
    $avgPercent  = $totals['percentage'] ?? 0;

    // Attendance calculations
    $attPct = isset($attendance['attendance_percentage']) ? round($attendance['attendance_percentage'], 1) : 0;
    $attWarn = $attPct < 75 && $attPct > 0;
    $attFound = $attendance['found'] ?? false;

    // QR Data (simple verification)
    $qrPayload = "Student: {$fullName}\nAdm: " . ($student->admissionNo ?? 'N/A') . "\nClass: {$className}\nTerm: {$term} {$session}";
    $qrBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(180)->errorCorrection('H')->generate($qrPayload));

    // Stamp and logo placeholder
    $stampSrc = !empty($studentData['school_stamp_base64']) ? $studentData['school_stamp_base64'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ccircle cx="50" cy="50" r="45" fill="%23f1f5f9" stroke="%233b82f6" stroke-width="2"/%3E%3Ctext x="50" y="55" text-anchor="middle" fill="%231e293b" font-size="12"%3ESTAMP%3C/text%3E%3C/svg%3E';
    $logoSrc = $studentData['school_logo_base64'] ?? 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"%3E%3Crect width="80" height="80" fill="%23fbbf24" /%3E%3Ctext x="40" y="45" text-anchor="middle" fill="%231e293b" font-size="18"%3ESCH%3C/text%3E%3C/svg%3E';
@endphp

<div class="student-section">
    {{-- SCHOOL BANNER --}}
    <div class="school-banner">
        <div class="school-name">{{ $schoolInfo->school_name ?? 'EXCELLENCE HIGH SCHOOL' }}</div>
        <div class="motto">{{ $schoolInfo->school_motto ?? '...DISCIPLINE, KNOWLEDGE & INTEGRITY...' }}</div>
    </div>

    {{-- HEADER with LOGO and PHOTO placeholder--}}
    <table style="width: 100%; margin: 4px 0; padding: 0 8px;">
        <tr>
            <td width="18%" style="text-align:center; padding: 4px;">
                <div style="border: 2px solid #2563eb; border-radius: 8px; background: white; width: 60px; margin: 0 auto; padding: 3px;">
                    <img src="{{ $logoSrc }}" style="max-width:50px; height:auto;" alt="logo">
                </div>
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div style="font-weight:800; font-size:14px;">TERMINAL PROGRESS REPORT</div>
                <div style="font-size:9px;">{{ $term }} | ACADEMIC SESSION {{ $session }}</div>
            </td>
            <td width="18%" style="text-align:center; padding: 4px;">
                <div style="border: 2px solid #2563eb; border-radius: 8px; background: #f1f5f9; width: 60px; margin: 0 auto; overflow:hidden;">
                    @if(isset($studentData['student_image_base64']))
                        <img src="{{ $studentData['student_image_base64'] }}" style="width:100%; height:auto; display:block;">
                    @else
                        <div style="height:60px; background:#cbd5e1;"></div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- STUDENT INFO PANEL --}}
    <div class="info-grid">
        <table class="info-table">
            <tr>
                <td width="33%"><span class="info-label">NAME</span> {{ $fullName }}</td>
                <td width="33%"><span class="info-label">ADMISSION NO</span> {{ $student->admissionNo ?? '—' }}</td>
                <td width="34%"><span class="info-label">CLASS</span> {{ $className }}</td>
            </tr>
            <tr>
                <td><span class="info-label">SESSION</span> {{ $session }}</td>
                <td><span class="info-label">TERM</span> {{ $term }}</td>
                <td><span class="info-label">CLASS SIZE</span> {{ $numInClass }}</td>
            </tr>
            <tr>
                <td><span class="info-label">GPA (Current)</span> {{ $gpaData['gpa'] ?? '—' }}</td>
                <td><span class="info-label">CGPA</span> {{ $gpaData['cgpa'] ?? '—' }}</td>
                <td><span class="info-label">NEXT TERM OPENS</span> {{ $schoolInfo->date_next_term_begins ?? 'To be announced' }}</td>
            </tr>
        </table>
    </div>

    {{-- SUBJECT PERFORMANCE TABLE --}}
    <div class="result-wrapper">
        <table class="result-table">
            <thead>
                <tr>
                    @if(in_array('sn', $columnsToShow)) <th>#</th> @endif
                    @if(in_array('name', $columnsToShow)) <th>SUBJECT</th> @endif
                    @foreach($assessments as $ass)
                        @if(in_array($ass->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                            <th>{{ $ass->name }}<br><span style="font-weight:normal;">({{ $ass->max_score }})</span></th>
                        @endif
                    @endforeach
                    @if(in_array('total', $columnsToShow)) <th>TOTAL</th> @endif
                    @if(in_array('cum', $columnsToShow)) <th>CUM</th> @endif
                    @if(in_array('grade', $columnsToShow)) <th>GRADE</th> @endif
                    @if(in_array('position', $columnsToShow)) <th>CLASS POS (CUM)</th> @endif
                    @if(in_array('class_average', $columnsToShow)) <th>CLASS AVG</th> @endif
                </tr>
            </thead>
            <tbody>
                @forelse($scores as $idx => $score)
                @php
                    $gradeRaw = $score->grade ?? '-';
                    $gradeUpper = strtoupper($gradeRaw);
                    $gradeCss = match(true) {
                        str_starts_with($gradeUpper, 'A') => 'grade-A',
                        str_starts_with($gradeUpper, 'B') => 'grade-B',
                        str_starts_with($gradeUpper, 'C') => 'grade-C',
                        str_starts_with($gradeUpper, 'D') => 'grade-D',
                        default => 'grade-F',
                    };
                    $posVal = isset($score->position) ? ordinalPosition($score->position) : '-';
                    $posClass = ($score->position == 1) ? 'pos-1' : (($score->position == 2) ? 'pos-2' : (($score->position == 3) ? 'pos-3' : ''));
                @endphp
                <tr>
                    @if(in_array('sn', $columnsToShow)) <td>{{ $idx + 1 }}</td> @endif
                    @if(in_array('name', $columnsToShow)) <td class="subject-name-cell">{{ $score->subject_name }}</td> @endif

                    @foreach($assessments as $ass)
                        @if(in_array($ass->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                            @php $aScore = $score->assessment_scores->firstWhere('assessment_id', $ass->id)->score ?? 0; @endphp
                            <td @if($aScore < ($ass->max_score * 0.5) && $aScore > 0) class="highlight-red" @endif>
                                {{ $aScore ? number_format($aScore, 0) : '-' }}
                            </td>
                        @endif
                    @endforeach

                    @if(in_array('total', $columnsToShow))
                        <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>{{ number_format($score->total ?? 0, 1) }}</td>
                    @endif
                    @if(in_array('cum', $columnsToShow)) <td>{{ number_format($score->cum ?? 0, 1) }}</td> @endif
                    @if(in_array('grade', $columnsToShow)) <td class="{{ $gradeCss }}">{{ $score->grade ?? '-' }}</td> @endif
                    @if(in_array('position', $columnsToShow)) <td><span class="pos-badge {{ $posClass }}">{{ $posVal }}</span></td> @endif
                    @if(in_array('class_average', $columnsToShow)) <td>{{ number_format($score->class_average ?? 0, 1) }}</td> @endif
                </tr>
                @empty
                <tr><td colspan="12" style="text-align:center;">No subject scores recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TOTALS SUMMARY BANNER--}}
    <div class="totals-banner">
        📊 TOTAL MARKS OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }} / {{ $totals['obtainable'] ?? 0 }}
        &nbsp;&nbsp;|&nbsp;&nbsp; PERCENTAGE: {{ number_format($totals['percentage'] ?? 0, 1) }}%
        &nbsp;&nbsp;|&nbsp;&nbsp; AVERAGE GRADE: {{ $totals['average_grade'] ?? '-' }}
    </div>

    {{-- PROMOTION STATUS / BADGE --}}
    @php
        $promoText = '';
        $promoClass = '';
        if (!$isPromoTerm) {
            $promoText = '⏳ AWAITING FINAL TERM ASSESSMENT';
            $promoClass = 'promo-awaiting';
        } elseif ($promoStatus === 'promoted') {
            $promoText = '🎓 PROMOTED TO NEXT CLASS';
            $promoClass = 'promo-promoted';
        } else {
            $promoText = '⚠️ NOT PROMOTED – REPEAT CLASS';
            $promoClass = 'promo-repeated';
        }
    @endphp
    <div class="promo-card {{ $promoClass }}">
        <strong>{{ $promoText }}</strong>
        @if($promoStatus === 'promoted' && $avgPercent > 0)
            <div style="font-size:7.5px;">Excellent performance with {{ number_format($avgPercent,1) }}% overall average.</div>
        @elseif($promoStatus !== 'promoted' && $isPromoTerm && $avgPercent > 0)
            <div style="font-size:7.5px;">Average {{ number_format($avgPercent,1) }}% - below promotion threshold.</div>
        @endif
    </div>

    {{-- ATTENDANCE --}}
    @if(isset($attendance))
    <div class="attendance-box">
        <div class="att-head">📅 TERMINAL ATTENDANCE RECORD</div>
        @if(!$attFound)
            <div style="padding:6px; text-align:center;background:#fef9c3;">Attendance data not finalized</div>
        @else
        <div class="att-grid">
            <div class="att-item"><div class="att-label">SCHOOL DAYS</div><div class="att-value">{{ $attendance['total_school_days'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">PRESENT</div><div class="att-value" style="color:#16a34a;">{{ $attendance['days_present'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">ABSENT</div><div class="att-value" style="color:#dc2626;">{{ $attendance['days_absent'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">LATE COMING</div><div class="att-value">{{ $attendance['days_late'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">ATTENDANCE %</div><div class="att-value {{ $attWarn ? 'highlight-red' : '' }}">{{ $attPct }}%</div></div>
        </div>
        <div class="progress-bar-container">
            <div class="progress-fill {{ $attWarn ? 'warning' : '' }}" style="width: {{ min($attPct, 100) }}%;"></div>
        </div>
        @endif
    </div>
    @endif

    {{-- REMARKS --}}
    <table class="remarks-table">
        <tr>
            <td width="50%">
                <span class="remark-title">🗣️ CLASS TEACHER'S REMARK</span><br>
                {{ $profileRemark ? ($profileRemark->classteachercomment ?? '—') : '—' }}
            </td>
            <td width="50%">
                <span class="remark-title">🏫 PRINCIPAL'S REMARK</span><br>
                {{ $profileRemark ? ($profileRemark->principalscomment ?? '—') : '—' }}
            </td>
        </tr>
    </table>

    {{-- BOTTOM STRIP (QR, SIGNATURES, STAMP) --}}
    <div class="bottom-strip">
        <table class="strip-table">
            <tr>
                <td class="qr-code" width="22%">
                    <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR">
                    <div style="font-size:6px;">verify result</div>
                </td>
                <td width="56%" style="text-align:center;">
                    <div><strong>ISSUED:</strong> <span class="sign-line">{{ now()->format('d/m/Y') }}</span></div>
                    <div style="margin-top:6px;"><strong>PARENT/GUARDIAN SIGN:</strong> <span class="sign-line"> _________________ </span></div>
                    <div style="margin-top:4px;"><small>Powered by EduTerminal Suite</small></div>
                </td>
                <td class="stamp-img" width="22%">
                    <img src="{{ $stampSrc }}" alt="school stamp" style="max-height: 65px;">
                    <div style="font-size:6px;">AUTHORIZED STAMP</div>
                </td>
            </tr>
        </table>
    </div>
</div>
@endforeach
</body>
</html>
