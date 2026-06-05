<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Terminal Report</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        @page{
            size: A5 portrait;
            margin: 5mm;
        }
        body{
            font-family:'Times New Roman', Times, serif;
            font-size:8.5px;
            line-height:1.3;
            color:#000;
        }
        .watermark-text{
            position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-30deg);
            font-size:48px; font-weight:900; color:rgba(220,38,38,0.07);
            pointer-events:none; z-index:0; text-transform:uppercase;
        }
        .student-section{
            border:2px double #000; position:relative; overflow:hidden; z-index:1;
            min-height: 190mm; background:white;
        }
        table{ width:100%; border-collapse:collapse; }
        th, td{ border:1px solid #000; padding:3px 2px; text-align:center; font-size:7.8px; }
        th{ background:#0d1a3d; color:white; font-weight:800; font-size:7px; }
        .subject-name{ text-align:left; font-weight:700; padding-left:5px; }
        .highlight-red{ color:#dc2626; font-weight:900; }
        .grade-A { color:#16a34a; font-weight:900; }
        .grade-B { color:#2563eb; font-weight:900; }
        .grade-C { color:#ca8a04; font-weight:900; }
        .grade-D { color:#ea580c; font-weight:900; }
        .grade-F { color:#dc2626; font-weight:900; }
        .pos-1 { background:gold; color:#000; font-weight:900; }
        .pos-2 { background:silver; color:#000; font-weight:900; }
        .pos-3 { background:#cd7f32; color:#fff; font-weight:900; }

        /* Additional styles for terminal UI */
        .school-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 8px 12px;
            text-align: center;
            border-bottom: 3px solid #fbbf24;
        }
        .school-name {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .info-grid {
            background: #fefce8;
            border: 1px solid #d1d5db;
            margin: 8px 10px;
            padding: 5px 8px;
            border-radius: 6px;
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
        .totals-banner {
            background: #0b2b44;
            color: white;
            font-weight: 800;
            text-align: center;
            padding: 6px;
            margin: 6px 10px;
            border-radius: 30px;
            font-size: 9px;
        }
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
        .sign-line {
            border-bottom: 1px dotted #334155;
            min-width: 100px;
            display: inline-block;
            margin: 0 5px;
        }
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
        }
    </style>
</head>
<body>
<div class="watermark-text">TERMINAL REPORT • STUDENT COPY</div>

@php
    function ordinalPosition($num) {
        if (!is_numeric($num) || $num <= 0) return '-';
        $last = $num % 10;
        $lastTwo = $num % 100;
        if ($lastTwo >= 11 && $lastTwo <= 13) return $num . 'th';
        return $num . (in_array($last, [1,2,3]) ? ($last == 1 ? 'st' : ($last == 2 ? 'nd' : 'rd')) : 'th');
    }

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

    // FIXED: Safe access to studentpp - check if exists and is not empty
    $profileRemark = null;
    if (isset($studentData['studentpp']) && $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()) {
        $profileRemark = $studentData['studentpp']->first();
    }

    $gpaData       = $studentData['gpa_data'] ?? [];

    // Build student display name
    $fullName = trim(strtoupper(($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? '')));
    if (empty($fullName)) $fullName = $student->name ?? 'STUDENT';

    $className = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arm_name ?? ''));
    $session   = $metadata['session'] ?? '2025/2026';
    $term      = $metadata['term'] ?? 'SECOND TERM';
    $numInClass= $studentData['numberOfStudents'] ?? '—';

    // Promotional logic
    $promoStatus = $studentData['promotion_result']['status'] ?? 'awaiting';
    $isPromoTerm = $studentData['promotion_result']['is_promotional_term'] ?? true;
    $avgPercent  = $totals['percentage'] ?? 0;

    // Attendance calculations
    $attPct = isset($attendance['attendance_percentage']) ? round($attendance['attendance_percentage'], 1) : 0;
    $attWarn = $attPct < 75 && $attPct > 0;
    $attFound = $attendance['found'] ?? false;

    // QR Data
    $qrPayload = "Student: {$fullName}\nAdm: " . ($student->admissionNo ?? 'N/A') . "\nClass: {$className}\nTerm: {$term} {$session}";
    $qrBase64 = isset($studentData['qr_code_base64']) ? $studentData['qr_code_base64'] : '';

    // Stamp and logo
    $stampSrc = !empty($studentData['school_stamp_base64']) ? $studentData['school_stamp_base64'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ccircle cx="50" cy="50" r="45" fill="%23f1f5f9" stroke="%233b82f6" stroke-width="2"/%3E%3Ctext x="50" y="55" text-anchor="middle" fill="%231e293b" font-size="12"%3ESTAMP%3C/text%3E%3C/svg%3E';
    $logoSrc = $studentData['school_logo_base64'] ?? 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"%3E%3Crect width="80" height="80" fill="%23fbbf24" /%3E%3Ctext x="40" y="45" text-anchor="middle" fill="%231e293b" font-size="18"%3ESCH%3C/text%3E%3C/svg%3E';
@endphp

<div class="student-section">
    {{-- SCHOOL BANNER --}}
    <div class="school-banner">
        <div class="school-name">{{ $schoolInfo->school_name ?? 'EXCELLENCE HIGH SCHOOL' }}</div>
        <div style="font-size:7px; letter-spacing:1px;">{{ $schoolInfo->school_motto ?? '...DISCIPLINE, KNOWLEDGE & INTEGRITY...' }}</div>
    </div>

    {{-- HEADER with LOGO --}}
    <table style="width: 100%; margin: 4px 0; padding: 0 8px;">
        <tr>
            <td width="15%" style="text-align:center; padding: 4px;">
                <div style="border: 2px solid #2563eb; border-radius: 8px; background: white; width: 55px; margin: 0 auto; padding: 3px;">
                    <img src="{{ $logoSrc }}" style="max-width:45px; height:auto;" alt="logo">
                </div>
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div style="font-weight:800; font-size:12px;">TERMINAL PROGRESS REPORT</div>
                <div style="font-size:8px;">{{ $term }} | ACADEMIC SESSION {{ $session }}</div>
            </td>
            <td width="15%" style="text-align:center; padding: 4px;">
                <div style="border: 2px solid #2563eb; border-radius: 8px; background: #f1f5f9; width: 55px; margin: 0 auto; overflow:hidden;">
                    @if(!empty($studentData['student_image_base64']))
                        <img src="{{ $studentData['student_image_base64'] }}" style="width:100%; height:auto; display:block;">
                    @else
                        <div style="height:55px; background:#cbd5e1;"></div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- STUDENT INFO PANEL --}}
    <div class="info-grid">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td width="33%"><span class="info-label">NAME</span> {{ $fullName }}</td>
                <td width="33%"><span class="info-label">ADM NO</span> {{ $student->admissionNo ?? '—' }}</td>
                <td width="34%"><span class="info-label">CLASS</span> {{ $className }}</td>
            </tr>
            <tr>
                <td><span class="info-label">SESSION</span> {{ $session }}</td>
                <td><span class="info-label">TERM</span> {{ $term }}</td>
                <td><span class="info-label">CLASS SIZE</span> {{ $numInClass }}</td>
            </tr>
            <tr>
                <td><span class="info-label">GPA</span> {{ $gpaData['gpa'] ?? '—' }}</td>
                <td><span class="info-label">CGPA</span> {{ $gpaData['cgpa'] ?? '—' }}</td>
                <td><span class="info-label">NEXT TERM</span> {{ $schoolInfo->date_next_term_begins ?? 'To be announced' }}</td>
            </tr>
        </table>
    </div>

    {{-- SUBJECT PERFORMANCE TABLE --}}
    <div style="padding: 0 8px; margin: 6px 0;">
        <table style="width:100%; border-collapse:collapse; border:2px solid #000;">
            <thead>
                <tr>
                    @if(in_array('sn', $columnsToShow)) <th style="border:1px solid #000; padding:3px;">#</th> @endif
                    @if(in_array('name', $columnsToShow)) <th style="border:1px solid #000; padding:3px;">SUBJECT</th> @endif
                    @foreach($assessments as $ass)
                        @if(in_array($ass->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                            <th style="border:1px solid #000; padding:3px;">{{ $ass->name }}<br><span style="font-weight:normal;">({{ $ass->max_score }})</span></th>
                        @endif
                    @endforeach
                    @if(in_array('total', $columnsToShow)) <th style="border:1px solid #000;">TOTAL</th> @endif
                    @if(in_array('cum', $columnsToShow)) <th style="border:1px solid #000;">CUM</th> @endif
                    @if(in_array('grade', $columnsToShow)) <th style="border:1px solid #000;">GRADE</th> @endif
                    @if(in_array('position', $columnsToShow)) <th style="border:1px solid #000;">CLASS POS</th> @endif
                    @if(in_array('class_average', $columnsToShow)) <th style="border:1px solid #000;">AVG</th> @endif
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
                    @if(in_array('sn', $columnsToShow)) <td style="border:1px solid #000;">{{ $idx + 1 }}</td> @endif
                    @if(in_array('name', $columnsToShow)) <td class="subject-name" style="border:1px solid #000;">{{ $score->subject_name }}</td> @endif

                    @foreach($assessments as $ass)
                        @if(in_array($ass->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                            @php $aScore = $score->assessment_scores->firstWhere('assessment_id', $ass->id)->score ?? 0; @endphp
                            <td style="border:1px solid #000;" @if($aScore < ($ass->max_score * 0.5) && $aScore > 0) class="highlight-red" @endif>
                                {{ $aScore ? number_format($aScore, 0) : '-' }}
                            </td>
                        @endif
                    @endforeach

                    @if(in_array('total', $columnsToShow))
                        <td style="border:1px solid #000;" @if(($score->total ?? 0) < 50) class="highlight-red" @endif>{{ number_format($score->total ?? 0, 1) }}</td>
                    @endif
                    @if(in_array('cum', $columnsToShow)) <td style="border:1px solid #000;">{{ number_format($score->cum ?? 0, 1) }}</td> @endif
                    @if(in_array('grade', $columnsToShow)) <td style="border:1px solid #000;" class="{{ $gradeCss }}">{{ $score->grade ?? '-' }}</td> @endif
                    @if(in_array('position', $columnsToShow)) <td style="border:1px solid #000;"><span class="{{ $posClass }}" style="padding:2px 6px; border-radius:12px;">{{ $posVal }}</span></td> @endif
                    @if(in_array('class_average', $columnsToShow)) <td style="border:1px solid #000;">{{ number_format($score->class_average ?? 0, 1) }}</td> @endif
                </tr>
                @empty
                <tr><td colspan="12" style="text-align:center; padding:10px;">No subject scores recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TOTALS SUMMARY BANNER --}}
    <div class="totals-banner">
        📊 TOTAL: {{ number_format($totals['obtained'] ?? 0, 1) }} / {{ $totals['obtainable'] ?? 0 }}
        &nbsp;&nbsp;|&nbsp;&nbsp; PERCENTAGE: {{ number_format($totals['percentage'] ?? 0, 1) }}%
    </div>

    {{-- PROMOTION STATUS --}}
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
    </div>

    {{-- ATTENDANCE --}}
    @if(isset($attendance) && !empty($attendance))
    <div class="attendance-box">
        <div class="att-head">📅 TERMINAL ATTENDANCE RECORD</div>
        @if(!$attFound)
            <div style="padding:6px; text-align:center;background:#fef9c3;">Attendance data not finalized</div>
        @else
        <div class="att-grid">
            <div class="att-item"><div class="att-label">SCHOOL DAYS</div><div class="att-value">{{ $attendance['total_school_days'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">PRESENT</div><div class="att-value" style="color:#16a34a;">{{ $attendance['days_present'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">ABSENT</div><div class="att-value" style="color:#dc2626;">{{ $attendance['days_absent'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label">ATTENDANCE %</div><div class="att-value {{ $attWarn ? 'highlight-red' : '' }}">{{ $attPct }}%</div></div>
        </div>
        <div class="progress-bar-container">
            <div class="progress-fill {{ $attWarn ? 'warning' : '' }}" style="width: {{ min($attPct, 100) }}%;"></div>
        </div>
        @endif
    </div>
    @endif

    {{-- REMARKS (FIXED: safe null checks) --}}
    <table class="remarks-table">
        <tr>
            <td width="50%">
                <span class="remark-title">🗣️ CLASS TEACHER'S REMARK</span><br>
                {{ $profileRemark ? ($profileRemark->classteachercomment ?? 'No remark provided') : 'No remark provided' }}
             </td>
            <td width="50%">
                <span class="remark-title">🏫 PRINCIPAL'S REMARK</span><br>
                {{ $profileRemark ? ($profileRemark->principalscomment ?? 'No remark provided') : 'No remark provided' }}
             </td>
        </tr>
    </table>

    {{-- BOTTOM STRIP --}}
    <div class="bottom-strip">
        <table class="strip-table">
            <tr>
                <td width="22%">
                    @if($qrBase64)
                        <img src="data:image/png;base64,{{ $qrBase64 }}" style="width:50px;">
                    @endif
                    <div style="font-size:6px;">verify result</div>
                </td>
                <td width="56%" style="text-align:center;">
                    <div><strong>ISSUED:</strong> <span class="sign-line">{{ now()->format('d/m/Y') }}</span></div>
                    <div style="margin-top:6px;"><strong>PARENT SIGN:</strong> <span class="sign-line"> _________________ </span></div>
                    <div style="margin-top:4px;"><small>Powered by School Management System</small></div>
                </td>
                <td width="22%">
                    <img src="{{ $stampSrc }}" alt="stamp" style="max-height: 55px;">
                    <div style="font-size:6px;">AUTHORIZED STAMP</div>
                </td>
            </tr>
        </table>
    </div>
</div>
@endforeach
</body>
</html>
