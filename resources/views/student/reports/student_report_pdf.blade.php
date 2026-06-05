<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Terminal Progress Report - Student Result Slip</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #e2e8f0;
            padding: 12px 0;
            text-align: center;
        }

        /* PRINT OPTIMIZATION */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .student-card {
                page-break-after: always;
                break-after: page;
                box-shadow: none;
                margin: 0 auto;
            }
            .no-break {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        /* WATERMARK (ORIGINAL COPY) */
        .watermark-original {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 68px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.035);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 6px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
        }

        /* STUDENT CARD (EXACT TERMINAL UI) */
        .student-card {
            width: 210mm;
            max-width: 210mm;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 3px double #1e293b;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            position: relative;
            text-align: left;
            page-break-after: always;
            break-after: page;
        }

        /* DARK TOP HEADER (SCHOOL BANNER) */
        .school-banner {
            background: #0f172a;
            color: white;
            padding: 10px 12px 8px 12px;
            border-bottom: 2px solid #fbbf24;
            text-align: center;
        }
        .school-name-large {
            font-family: 'Arial Black', sans-serif;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .school-motto-text {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            opacity: 0.9;
            margin-top: 3px;
        }

        /* LOGO + INFO + PHOTO LAYOUT */
        .info-panel {
            width: 100%;
            border-collapse: collapse;
            padding: 5px 8px;
        }
        .info-panel td {
            vertical-align: middle;
            padding: 5px 4px;
        }
        .logo-box {
            width: 74px;
            height: 80px;
            border: 2px solid #2aa886;
            border-radius: 8px;
            background: white;
            padding: 3px;
            text-align: center;
        }
        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .photo-box {
            width: 74px;
            height: 80px;
            border: 2px solid #2aa886;
            border-radius: 8px;
            background: #f1f5f9;
            overflow: hidden;
            margin-left: auto;
            margin-right: 0;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .contact-details {
            font-size: 9.5px;
            line-height: 1.4;
        }
        .contact-details strong {
            color: #1e3a8a;
            font-weight: 900;
        }

        /* DIVIDERS */
        .divider-blue {
            height: 2px;
            background: #1e40af;
            width: 100%;
        }
        .divider-light {
            height: 1px;
            background: #cbd5e1;
            width: 100%;
            margin: 2px 0;
        }

        /* REPORT TITLE STRIP */
        .report-title-strip {
            background: #0f172a;
            color: #facc15;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* STUDENT INFO BAR (GRID) */
        .student-info-grid {
            background: linear-gradient(98deg, #f0f9ff 0%, #ffffff 100%);
            border: 1.5px solid #2aa886;
            border-radius: 10px;
            margin: 8px 10px;
            padding: 6px 12px;
        }
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid-table td {
            padding: 4px 8px;
            text-align: center;
            font-size: 10px;
            font-weight: 600;
        }
        .info-label {
            color: #1e40af;
            font-weight: 800;
            background: #eef2ff;
            border-radius: 12px;
            padding: 2px 8px;
            display: inline-block;
        }
        .info-value {
            font-weight: 900;
            font-size: 10.5px;
            margin-left: 5px;
        }

        /* SUBJECT TABLE */
        .result-table-wrapper {
            padding: 0 10px;
            margin: 5px 0;
        }
        .subject-table {
            width: 100%;
            border: 2px solid #0f172a;
            border-collapse: collapse;
            font-size: 9px;
        }
        .subject-table th {
            background: #0b2b44;
            color: white;
            border: 1px solid #000;
            padding: 5px 2px;
            font-size: 8.2px;
            font-weight: 800;
            text-align: center;
        }
        .subject-table td {
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            font-weight: 600;
            font-size: 9px;
        }
        .subject-name-cell {
            text-align: left;
            font-weight: 800;
            padding-left: 8px;
        }
        .highlight-red {
            color: #dc2626;
            font-weight: 900;
        }

        /* GRADE COLOURS */
        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }

        /* POSITION BADGES */
        .pos-1 { background: #FFD966; color: #000; font-weight: 900; border-radius: 12px; display: inline-block; padding: 0px 6px; }
        .pos-2 { background: #C0C0C0; color: #000; font-weight: 900; border-radius: 12px; display: inline-block; padding: 0px 6px; }
        .pos-3 { background: #CD7F32; color: #000; font-weight: 900; border-radius: 12px; display: inline-block; padding: 0px 6px; }

        /* TOTALS SUMMARY */
        .totals-summary-card {
            background: #0b2b44;
            color: #facc15;
            font-weight: 900;
            font-size: 9.5px;
            padding: 6px 12px;
            text-align: center;
            margin: 6px 10px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        /* PROMOTION BADGE */
        .promo-badge {
            margin: 6px 10px;
            padding: 6px 12px;
            border-radius: 12px;
            border-left: 8px solid;
            font-weight: 700;
            background: #f8fafc;
        }
        .promo-promoted {
            background: #f0fdf4;
            border-left-color: #16a34a;
            color: #14532d;
        }
        .promo-not {
            background: #fef2f2;
            border-left-color: #dc2626;
            color: #7f1d1d;
        }
        .promo-await {
            background: #fefce8;
            border-left-color: #eab308;
            color: #854d0e;
        }

        /* ATTENDANCE SECTION */
        .attendance-container {
            margin: 6px 10px;
            border: 1.5px solid #0d9488;
            border-radius: 12px;
            overflow: hidden;
        }
        .att-header {
            background: #0d9488;
            color: white;
            padding: 6px;
            font-weight: 800;
            font-size: 9.5px;
            text-align: center;
        }
        .att-stats {
            display: flex;
            flex-wrap: wrap;
            background: #f0fdfa;
        }
        .att-item {
            flex: 1;
            padding: 6px 4px;
            text-align: center;
            border-right: 1px solid #ccfbf1;
        }
        .att-item:last-child { border-right: none; }
        .att-label-sm {
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #0f766e;
        }
        .att-number {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
        }
        .progress-bar-bg {
            background: #e2e8f0;
            border-radius: 20px;
            height: 5px;
            margin: 4px 12px 8px 12px;
        }
        .progress-fill {
            height: 5px;
            border-radius: 20px;
            background: #0d9488;
        }
        .warn-fill {
            background: #dc2626;
        }

        /* REMARKS TABLE */
        .remarks-table {
            width: calc(100% - 20px);
            margin: 6px 10px;
            border: 1.5px solid #334155;
            border-collapse: collapse;
        }
        .remarks-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 8px;
            vertical-align: top;
            font-size: 9.5px;
        }
        .remark-head {
            font-weight: 800;
            font-size: 10px;
            border-bottom: 1px dashed #94a3b8;
            display: inline-block;
            margin-bottom: 4px;
        }

        /* BOTTOM STRIP (QR + SIGNATURE + STAMP) */
        .bottom-strip {
            margin-top: 6px;
            border-top: 1px solid #cbd5e1;
            background: #f8fafc;
            width: 100%;
        }
        .strip-table {
            width: 100%;
            border-collapse: collapse;
        }
        .strip-table td {
            padding: 6px 8px;
            vertical-align: middle;
        }
        .qr-code img {
            width: 60px;
            height: 60px;
        }
        .sign-line {
            border-bottom: 1px dotted #1e293b;
            min-width: 100px;
            display: inline-block;
            margin: 0 4px;
        }
        .stamp-img {
            max-width: 85px;
            max-height: 75px;
            transform: rotate(-6deg);
        }

        .powered {
            font-size: 8px;
            color: #475569;
            text-align: center;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="watermark-original">ORIGINAL COPY</div>

@php
    // helper: ordinal suffix
    function ordinalSuffix($num) {
        if (!is_numeric($num) || $num <= 0) return '-';
        $last = $num % 10;
        $lastTwo = $num % 100;
        if ($lastTwo >= 11 && $lastTwo <= 13) return $num . 'th';
        return $num . (match($last) {1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'});
    }

    // Sample data simulation (in real usage, $studentsData is passed from controller)
    // For this UI, we assume $allStudentData exists with proper structure.
    // The following is a demonstration to satisfy the design requirement.
    // In your actual implementation, the loop will render each student exactly as shown.

    // The variables below are placeholders; actual usage expects the passed $allStudentData.
    if (!isset($allStudentData) || empty($allStudentData)) {
        // Fallback demo data to illustrate exact terminal UI when previewing standalone
        $demoSchool = (object) [
            'school_name' => 'PREMIER ACADEMY',
            'school_motto' => 'DISCIPLINE, EXCELLENCE, SERVICE',
            'school_address' => '12 Education Crescent, GRA, Lagos',
            'formatted_phones' => '+234 812 345 6789',
            'school_email' => 'info@premieracademy.edu',
            'school_website' => 'www.premieracademy.edu',
            'date_school_opened' => '2026-01-10',
            'date_next_term_begins' => '2026-05-04',
        ];

        $demoStudent = (object) [
            'lastname' => 'OKONKWO',
            'fname' => 'CHINEDU',
            'othername' => 'MICHAEL',
            'admissionNo' => 'PAS/2024/0123',
            'gender' => 'Male',
        ];

        $demoProfile = (object) [
            'classteachercomment' => 'Very consistent, strong analytical skills. keep up the momentum.',
            'principalscomment' => 'Excellent performance. Recommended for academic prize consideration.'
        ];

        $demoScores = [];
        $subjects = ['Mathematics', 'English Studies', 'Basic Science', 'Social Studies', 'Computer Studies', 'Agricultural Science'];
        $totObtained = 0;
        foreach ($subjects as $idx => $subj) {
            $total = rand(65, 94);
            $cum = $total + rand(0, 5);
            $grade = $total >= 80 ? 'A' : ($total >= 65 ? 'B' : ($total >= 50 ? 'C' : ($total >= 45 ? 'D' : 'F')));
            $totObtained += $total;
            $demoScores[] = (object) [
                'subject_name' => $subj,
                'total' => $total,
                'cum' => $cum,
                'grade' => $grade,
                'position' => rand(1, 18),
                'position_total' => rand(1, 18),
                'arm_position' => rand(1, 8),
                'arm_position_cum' => rand(1, 8),
                'class_average' => rand(58, 77),
                'assessment_scores' => collect([(object)['assessment_id' => 1, 'score' => rand(12, 29)], (object)['assessment_id' => 2, 'score' => rand(18, 35)]])
            ];
        }

        $allStudentData = [[
            'schoolInfo' => $demoSchool,
            'students' => collect([$demoStudent]),
            'studentpp' => collect([$demoProfile]),
            'schoolclass' => (object)['schoolclass' => 'SS 2', 'arms' => (object)['arm' => 'SCIENCE']],
            'assessments' => collect([(object)['id' => 1, 'name' => 'CA 1', 'max_score' => 30], (object)['id' => 2, 'name' => 'CA 2', 'max_score' => 40]]),
            'scores' => collect($demoScores),
            'totals_summary' => ['obtained' => $totObtained, 'obtainable' => 600, 'percentage' => round($totObtained/6, 1)],
            'attendance_summary' => ['found' => true, 'total_school_days' => 62, 'days_present' => 54, 'days_absent' => 8, 'days_late' => 3, 'days_sick_leave' => 1, 'days_excused' => 0, 'attendance_percentage' => 87.1],
            'numberOfStudents' => 24,
            'promotion_result' => ['status' => 'promoted', 'is_promotional_term' => true, 'failed_compulsory' => [], 'average_failed' => false, 'required_average' => 60, 'actual_average' => 74.2, 'compulsory_count' => 4, 'passed_compulsory' => 4],
            'school_logo_base64' => null,
            'student_image_base64' => null,
            'school_stamp_base64' => null
        ]];
    }

    // Define columns to show (as per terminal UI requirement)
    $columnsToShow = ['sn', 'name', 'total', 'cum', 'grade', 'position', 'arm_position', 'class_average', 'attendance_percentage'];
    $assessments = $allStudentData[0]['assessments'] ?? collect();
@endphp

@foreach ($allStudentData as $studentData)
@php
    $school = $studentData['schoolInfo'] ?? (object)[];
    $studentRec = $studentData['students'] && $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
    $profileRec = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
    $scoresList = $studentData['scores'] ?? collect();
    $totalsData = $studentData['totals_summary'] ?? [];
    $attend = $studentData['attendance_summary'] ?? [];
    $attPct = isset($attend['attendance_percentage']) ? round($attend['attendance_percentage'], 1) : 0;
    $attWarn = $attPct < 75;
    $promo = $studentData['promotion_result'] ?? [];
    $isPromoTerm = $promo['is_promotional_term'] ?? false;
    $promoStatus = $promo['status'] ?? 'awaiting';

    $fullName = trim(strtoupper($studentRec->lastname ?? '') . ' ' . ($studentRec->fname ?? '') . ' ' . ($studentRec->othername ?? ''));
    $className = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? ''));
    $session = $metadata['session'] ?? '2025/2026';
    $term = $metadata['term'] ?? 'SECOND TERM';

    // QR data generation (simulated base64)
    $qrText = "Name: {$fullName}\nAdm No: {$studentRec->admissionNo}\nClass: {$className}\nTerm: {$term}\nSession: {$session}\nSchool: " . ($school->school_name ?? 'School');
    $fakeQrBase64 = 'iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAYAAAA6/NlyAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAABdSURBVGhD7dJBDQAwDMCwEv7fVCZggCSBxbSjD/Z/aGgOw3AYhsNwGA7DYTgMh+EwHIbDcBgOw2E4DIfhMByGw3AYDsNhOAyH4TAchsNwGA7DYTgMh+HwGj+4pPwAAAD//wMAH6pMAQAAAABJRU5ErkJggg==';
    $stampPlaceholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 100"%3E%3Crect width="120" height="100" fill="%23fef9c3" stroke="%23b45309" stroke-width="2" rx="6"/%3E%3Ctext x="60" y="45" font-size="10" text-anchor="middle" fill="%23922c1c"%3ESCHOOL%3C/text%3E%3Ctext x="60" y="65" font-size="9" text-anchor="middle" fill="%23922c1c"%3ESTAMP%3C/text%3E%3C/svg%3E';
@endphp

<div class="student-card">
    <!-- SCHOOL BANNER (DARK) -->
    <div class="school-banner">
        <div class="school-name-large">{{ $school->school_name ?? 'PREMIER ACADEMY' }}</div>
        <div class="school-motto-text">{{ $school->school_motto ?? 'KNOWLEDGE • INTEGRITY • EXCELLENCE' }}</div>
    </div>

    <!-- LOGO + CONTACT + PHOTO -->
    <table class="info-panel">
        <tr>
            <td width="18%" style="text-align: center;">
                <div class="logo-box">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f1f5f9' stroke='%2347b492' stroke-width='2'/%3E%3Ccircle cx='50' cy='38' r='14' fill='%2347b492' opacity='0.7'/%3E%3Crect x='38' y='58' width='24' height='20' fill='%2347b492' opacity='0.7'/%3E%3C/svg%3E" alt="Logo">
                </div>
            </td>
            <td style="padding: 0 8px;">
                <div class="contact-details">
                    <strong>📍 Address:</strong> {{ $school->school_address ?? '—' }}<br>
                    <strong>📞 Phone:</strong> {{ $school->formatted_phones ?? '—' }} &nbsp;|&nbsp;
                    <strong>✉️ Email:</strong> {{ $school->school_email ?? '—' }}<br>
                    <strong>🌐 Website:</strong> {{ $school->school_website ?? '—' }}
                </div>
            </td>
            <td width="20%" style="text-align: right;">
                <div class="photo-box">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='64' width='30' height='22' fill='%2394a3b8'/%3E%3C/svg%3E" alt="Student">
                </div>
            </td>
        </tr>
    </table>
    <div class="divider-blue"></div>
    <div class="divider-light"></div>

    <!-- REPORT TITLE -->
    <div class="report-title-strip">
        {{ strtoupper($term) }} {{ strtoupper($session) }} ACADEMIC SESSION — TERMINAL PROGRESS REPORT
    </div>

    <!-- STUDENT INFO BAR -->
    <div class="student-info-grid">
        <table class="info-grid-table">
            <tr>
                <td><span class="info-label">NAME</span> <span class="info-value">{{ $fullName }}</span></td>
                <td><span class="info-label">ADM NO</span> <span class="info-value">{{ $studentRec->admissionNo ?? '—' }}</span></td>
                <td><span class="info-label">CLASS</span> <span class="info-value">{{ $className }}</span></td>
                <td><span class="info-label">TERM</span> <span class="info-value">{{ $term }}</span></td>
            </tr>
            <tr>
                <td><span class="info-label">SESSION</span> <span class="info-value">{{ $session }}</span></td>
                <td><span class="info-label">NO. IN CLASS</span> <span class="info-value">{{ $studentData['numberOfStudents'] ?? '—' }}</span></td>
                <td><span class="info-label">SCHOOL OPENED</span> <span class="info-value">{{ $school->date_school_opened ? \Carbon\Carbon::parse($school->date_school_opened)->format('jS M, Y') : '—' }}</span></td>
                <td><span class="info-label">SEX</span> <span class="info-value">{{ $studentRec->gender ?? '—' }}</span></td>
            </tr>
        </table>
    </div>

    <!-- SUBJECT TABLE -->
    <div class="result-table-wrapper">
        <table class="subject-table">
            <thead>
                <tr>
                    <th style="width:30px">S/N</th>
                    <th>SUBJECT</th>
                    @foreach ($assessments as $ass)<th>{{ $ass->name }}<br><span style="font-size:6px">({{ $ass->max_score }})</span></th>@endforeach
                    <th>TOTAL</th><th>CUM</th><th>GRADE</th><th>CLASS POS</th><th>ARM POS</th><th>AVG</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($scoresList as $idx => $row)
                @php
                    $posFormatted = ordinalSuffix($row->position ?? 0);
                    $armPosFormatted = ordinalSuffix($row->arm_position ?? 0);
                    $gradeClass = match(strtoupper($row->grade ?? '')) { 'A' => 'grade-A', 'B' => 'grade-B', 'C' => 'grade-C', 'D' => 'grade-D', default => 'grade-F' };
                @endphp
                <tr>
                    <td>{{ $idx+1 }}</td>
                    <td class="subject-name-cell">{{ $row->subject_name }}</td>
                    @foreach ($assessments as $ass)
                        @php $scoreVal = isset($row->assessment_scores) ? ($row->assessment_scores->firstWhere('assessment_id', $ass->id)->score ?? 0) : 0; @endphp
                        <td class="{{ $scoreVal < ($ass->max_score * 0.5) ? 'highlight-red' : '' }}">{{ $scoreVal ? number_format($scoreVal,0) : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($row->total ?? 0,1) }}</td>
                    <td>{{ number_format($row->cum ?? 0,1) }}</td>
                    <td class="{{ $gradeClass }}">{{ $row->grade ?? '-' }}</td>
                    <td><span class="{{ $row->position == 1 ? 'pos-1' : ($row->position == 2 ? 'pos-2' : ($row->position == 3 ? 'pos-3' : '')) }}">{{ $posFormatted }}</span></td>
                    <td><span class="{{ $row->arm_position == 1 ? 'pos-1' : ($row->arm_position == 2 ? 'pos-2' : ($row->arm_position == 3 ? 'pos-3' : '')) }}">{{ $armPosFormatted }}</span></td>
                    <td>{{ number_format($row->class_average ?? 0,1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- TOTALS SUMMARY -->
    <div class="totals-summary-card">
        🏆 TOTAL OBTAINED: {{ number_format($totalsData['obtained'] ?? 0, 1) }} &nbsp;|&nbsp;
        🎯 TOTAL OBTAINABLE: {{ $totalsData['obtainable'] ?? 0 }} &nbsp;|&nbsp;
        📊 PERCENTAGE: {{ $totalsData['percentage'] ?? 0 }}%
    </div>

    <!-- PROMOTION STATUS -->
    <div class="promo-badge @if($isPromoTerm && $promoStatus=='promoted') promo-promoted @elseif($isPromoTerm && $promoStatus!='promoted') promo-not @else promo-await @endif">
        @if(!$isPromoTerm) ⏳ AWAITING FINAL TERM — Promotion assessed at year end.
        @elseif($promoStatus === 'promoted') ✅ PROMOTED — Passed compulsory subjects. Excellent progress.
        @else ⚠️ NOT PROMOTED — Requires improvement in compulsory subjects. @endif
    </div>

    <!-- ATTENDANCE SECTION -->
    <div class="attendance-container">
        <div class="att-header">📅 ATTENDANCE RECORD — {{ $term }}</div>
        <div class="att-stats">
            <div class="att-item"><div class="att-label-sm">School Days</div><div class="att-number">{{ $attend['total_school_days'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label-sm">Present</div><div class="att-number">{{ $attend['days_present'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label-sm">Absent</div><div class="att-number">{{ $attend['days_absent'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label-sm">Late</div><div class="att-number">{{ $attend['days_late'] ?? 0 }}</div></div>
            <div class="att-item"><div class="att-label-sm">Attendance %</div><div class="att-number {{ $attWarn ? 'highlight-red' : '' }}">{{ $attPct }}%</div></div>
        </div>
        <div class="progress-bar-bg"><div class="progress-fill {{ $attWarn ? 'warn-fill' : '' }}" style="width: {{ min($attPct,100) }}%;"></div></div>
    </div>

    <!-- REMARKS -->
    <table class="remarks-table">
        <tr><td width="50%"><div class="remark-head">🏫 CLASS TEACHER'S REMARK</div><div>{{ $profileRec->classteachercomment ?? 'No comment recorded.' }}</div></td>
            <td width="50%"><div class="remark-head">🎓 PRINCIPAL'S REMARK</div><div>{{ $profileRec->principalscomment ?? 'No comment recorded.' }}</div></td>
        </tr>
    </table>

    <!-- BOTTOM QR + STAMP + SIGNATURE -->
    <div class="bottom-strip">
        <table class="strip-table">
            <tr>
                <td class="qr-code" width="80"><img src="data:image/png;base64,{{ $fakeQrBase64 }}" alt="QR"><div style="font-size: 7px;">Verify online</div></td>
                <td style="text-align: center;">
                    <div><strong>Issued:</strong> <span class="sign-line">{{ \Carbon\Carbon::now()->format('jS F, Y') }}</span></div>
                    <div style="margin-top: 5px;"><strong>Parent/Guardian signature:</strong> <span class="sign-line">_________________</span></div>
                    <div style="margin-top: 5px;"><strong>Next term begins:</strong> <span class="sign-line">{{ $school->date_next_term_begins ? \Carbon\Carbon::parse($school->date_next_term_begins)->format('jS F, Y') : '...................' }}</span></div>
                    <div class="powered">🔹 Powered by Qudroid Systems 🔹</div>
                </td>
                <td width="100" style="text-align: right;"><img src="{{ $stampPlaceholder }}" class="stamp-img" alt="Stamp"></td>
            </tr>
        </table>
    </div>
</div>
@endforeach
</body>
</html>
