<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] }}</title>
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
            background: #f5f5f5;
            padding: 2mm 0;
            text-align: center;
        }

        /* WATERMARK */
        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 65px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.04);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 5px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
        }

        /* SCHOOL NAME HEADER */
        .school-name-header {
            width: 96.5%;
            background: #111827;
            color: white;
            padding: 7px 10px 5px 10px;
            border: 3px double #000000;
            border-bottom: 1px solid #1e40af;
            text-align: center;
        }

        .school-name-header .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .school-name-header .motto {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: 0.95;
            margin-top: 2px;
        }

        /* One card per printed page */
        .student-section {
            width: 190mm;
            page-break-after: always;
            page-break-inside: avoid;
            break-after: page;
            break-inside: avoid;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            padding: 3px 7px 3px 7px;
        }

        .school-logo {
            width: 68px;
            height: 76px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: white;
            padding: 3px;
            overflow: hidden;
            display: block;
            text-align: center;
        }

        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .photo-frame {
            width: 68px;
            height: 76px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: #e2e8f0;
            padding: 0;
            overflow: hidden;
            display: block;
            margin-left: auto;
            margin-right: 4px;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .header-divider  { height: 2px; background: #1e40af; width: 100%; }
        .header-divider2 { height: 1px; background: #64748b; width: 100%; margin: 1px 0; }

        .report-title {
            background: #111827;
            color: white;
            padding: 5px 8px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .student-info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 6px;
            padding: 5px 10px;
            margin: 5px 8px;
            font-size: 10px;
            text-align: center;
        }

        .info-table { width: 100%; margin: 0 auto; }
        .info-table td { padding: 2px 6px; text-align: center; }
        .info-bar-label { color: #1e40af; font-weight: 900; font-size: 9.5px; white-space: nowrap; }
        .info-bar-value { font-weight: 900; font-size: 10.5px; padding-left: 3px; }

        .result-table { padding: 0 8px; margin: 5px 0; }

        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            font-size: 9px;
            margin: 0;
        }

        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 3px 2px;
            font-size: 8.2px;
            text-align: center;
            line-height: 1.2;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 2px 2px;
            text-align: center;
            font-size: 9px;
            background: white;
            font-weight: 800;
            height: 15px;
            line-height: 15px;
        }

        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 800;
            font-size: 9px;
            padding-left: 6px;
        }

        .highlight-red { color: #dc2626; font-weight: 900; }

        .col-sn            { width: 20px; }
        .col-admissionno   { width: 70px; }
        .col-name          { width: 148px; }
        .col-assessment    { width: 36px; }
        .col-total         { width: 36px; }
        .col-bf            { width: 30px; }
        .col-cum           { width: 34px; }
        .col-grade         { width: 32px; }
        .col-position      { width: 32px; }
        .col-class-average { width: 34px; }

        .totals-summary {
            width: calc(100% - 16px);
            background: #0d1a3d;
            color: #ffffff;
            font-weight: 900;
            font-size: 9px;
            padding: 4px 8px;
            border: 2px solid #000000;
            border-top: none;
            text-align: center;
            margin: 0 8px 5px 8px;
        }

        .position-cell { font-weight: 900; text-align: center; padding: 2px 4px; }
        .position-1 { background-color: #FFD700; color: #000000; font-weight: 900; }
        .position-2 { background-color: #C0C0C0; color: #000000; font-weight: 900; }
        .position-3 { background-color: #CD7F32; color: #000000; font-weight: 900; }
        td.position-1, td.position-2, td.position-3 { color: #000000 !important; }

        .attendance-box {
            width: calc(100% - 16px);
            margin: 0 8px 5px 8px;
            border: 2px solid #0d9488;
            border-radius: 5px;
            overflow: hidden;
        }

        .attendance-box-header {
            background: #0d9488;
            color: #ffffff;
            font-size: 9px;
            font-weight: 900;
            padding: 4px 10px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .attendance-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .att-cell {
            display: table-cell;
            padding: 4px 6px;
            text-align: center;
            border-right: 1px solid #d1fae5;
            vertical-align: middle;
            background: #f0fdf9;
        }

        .att-cell:last-child { border-right: none; }

        .att-label {
            font-size: 8px;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 1px;
        }

        .att-value {
            font-size: 11px;
            font-weight: 900;
            color: #111827;
            display: block;
        }

        .att-value.att-warn { color: #dc2626; }
        .att-value.att-ok   { color: #16a34a; }

        .att-pct-bar-wrap {
            width: calc(100% - 16px);
            margin: 0 8px 2px 8px;
            background: #e2e8f0;
            border-radius: 20px;
            height: 5px;
            overflow: hidden;
        }

        .att-pct-bar {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #0d9488, #22c55e);
        }

        .att-pct-bar.att-pct-warn {
            background: linear-gradient(90deg, #f59e0b, #dc2626);
        }

        .remarks-table {
            width: calc(100% - 16px);
            border: 2px solid #000000;
            border-collapse: collapse;
            margin: 5px 8px 3px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            background: white;
            vertical-align: top;
            font-size: 9.5px;
        }

        .remarks-table .h6 {
            font-weight: 700;
            margin-bottom: 3px;
            font-size: 10px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
        }

        .bottom-strip {
            width: 100%;
            border-top: 1px solid #cbd5e1;
            background: #f1f5f9;
            margin-top: 4px;
        }

        .bottom-strip table {
            width: 100%;
            border-collapse: collapse;
        }

        .bottom-strip td {
            padding: 5px 8px;
            vertical-align: middle;
        }

        .bottom-strip .cell-qr {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }

        .bottom-strip .cell-footer {
            text-align: center;
            font-size: 9.5px;
            vertical-align: middle;
        }

        .bottom-strip .cell-stamp {
            width: 110px;
            text-align: center;
            vertical-align: middle;
        }

        .bottom-strip .cell-qr img {
            width: 65px;
            height: 65px;
            display: block;
            margin: 0 auto 2px;
        }

        .qr-label {
            font-size: 7.5px;
            color: #333;
            font-weight: 600;
            text-align: center;
        }

        .bottom-strip .cell-stamp img {
            width: 95px;
            height: 95px;
            transform: rotate(-8deg);
            display: block;
            margin: 0 auto;
        }

        .text-dot-space2 {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 110px;
            font-weight: bold;
            margin: 0 4px;
        }

        .powered-by { font-size: 9px; margin-top: 3px; color: #64748b; }

        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }

        /* PROMOTION BADGE */
        .promo-card {
            width: calc(100% - 16px);
            margin: 6px 8px 8px 8px;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            clear: both;
        }

        .promo-title {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .promo-rule {
            font-size: 8.5px;
            font-weight: 600;
            margin-bottom: 3px;
            opacity: 0.85;
        }

        .promo-message {
            font-size: 8px;
            font-weight: 500;
            margin-top: 2px;
            line-height: 1.3;
        }

        .promo-average {
            font-size: 8px;
            font-weight: 600;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 1px dashed rgba(0,0,0,0.1);
        }

        .promo-promoted {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-left: 3px solid #16a34a;
            border-right: 3px solid #16a34a;
            color: #14532d;
        }

        .promo-trial {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border-left: 3px solid #ca8a04;
            border-right: 3px solid #ca8a04;
            color: #854d0e;
        }

        .promo-principal {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 3px solid #3b82f6;
            border-right: 3px solid #3b82f6;
            color: #1e3a8a;
        }

        .promo-repeated {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 3px solid #dc2626;
            border-right: 3px solid #dc2626;
            color: #7f1d1d;
        }

        .promo-awaiting {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-left: 3px solid #94a3b8;
            border-right: 3px solid #94a3b8;
            color: #475569;
        }

        @media print {
            body { background: white; padding: 0; }
            .student-section {
                box-shadow: none;
                page-break-inside: avoid;
                page-break-after: always;
                break-after: page;
            }
        }
    </style>
</head>
<body>
    <div class="watermark-text">ORIGINAL COPY</div>

    @php
        function formatOrdinal($number) {
            if (!is_numeric($number) || $number <= 0) { return '-'; }
            $lastDigit     = $number % 10;
            $lastTwoDigits = $number % 100;
            if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) { return $number . 'th'; }
            switch ($lastDigit) {
                case 1: return $number . 'st';
                case 2: return $number . 'nd';
                case 3: return $number . 'rd';
                default: return $number . 'th';
            }
        }

        $selectedColumns = $metadata['selected_columns'] ?? [];
        // "total"   -> grade the printed subject grade off the term's raw total (default, current behaviour)
        // "cum_ave" -> grade it off the cumulative average instead (Cum ÷ term number)
        $gradeBasis = $metadata['grade_basis'] ?? 'total';
        if (!in_array($gradeBasis, ['total', 'cum_ave'], true)) {
            $gradeBasis = 'total';
        }
        // "cum" = raw cumulative sum (BF + this term's total).
        // "cum_ave" = that sum divided by the term number — the figure that reflects
        // actual academic performance. Both are included by default.
        $defaultColumns  = [
            'sn', 'admission_no', 'name',
            'total', 'bf', 'cum', 'cum_ave', 'grade',
            'arm_position', 'arm_position_cum', 'position_total', 'position',
            'class_average',
            'attendance_days_present', 'attendance_days_absent',
            'attendance_total_days', 'attendance_percentage',
        ];
        $columnsToShow = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;

        $showAnyAttendance = collect([
            'attendance_days_present', 'attendance_days_absent', 'attendance_days_late',
            'attendance_sick_leave', 'attendance_excused',
            'attendance_total_days', 'attendance_percentage',
        ])->contains(fn($col) => in_array($col, $columnsToShow));
    @endphp

    @foreach ($allStudentData as $index => $studentData)
        @php
            $schoolInfo  = $studentData['schoolInfo'] ?? null;
            $student     = $studentData['students'] && $studentData['students']->isNotEmpty()
                ? $studentData['students']->first() : null;
            $assessments = $studentData['assessments'] ?? collect();
            $totals      = $studentData['totals_summary'] ?? [];
            $attendance  = $studentData['attendance_summary'] ?? [];
            // Used only when $gradeBasis === 'cum_ave' — all subjects for a
            // student share the same class, so one category lookup covers them.
            $gradeCategory = ($studentData['schoolclass']->classcategories ?? collect())->first();

            $admNo    = $student->admissionNo ?? 'N/A';
            $fullName = trim(strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
            $classVal = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? ''));
            $session  = $metadata['session'] ?? '2025/2026';
            $term     = $metadata['term']    ?? 'SECOND TERM';

            $qrData = "Name: {$fullName}\nAdm No: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}\nSchool: " . ($schoolInfo->school_name ?? 'School');

            $qrCodeBase64 = base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(280)
                    ->errorCorrection('H')
                    ->generate($qrData)
            );

            $stampSrc = !empty($studentData['school_stamp_base64'])
                ? $studentData['school_stamp_base64']
                : asset('stamp.jpeg');

            $attPct   = isset($attendance['attendance_percentage']) ? round($attendance['attendance_percentage'], 1) : 0;
            $attWarn  = $attPct < 75;
            $attFound = $attendance['found'] ?? false;

            // Get school term to check if promotional
            $schoolTerm = $studentData['schoolterm'] ?? null;
            $isTermPromotional = $schoolTerm && $schoolTerm->is_promotional;

            // Promotion data
            $pr           = $studentData['promotion_result'] ?? [];
            $promoStatus  = $pr['status']              ?? 'awaiting';
            $promoFailed  = $pr['failed_compulsory']   ?? [];
            $reqAvg       = $pr['required_average']    ?? null;
            $actAvg       = $pr['actual_average']      ?? null;
            $promoTotal   = $pr['compulsory_count']    ?? 0;
            $promoPassed  = $pr['passed_compulsory']   ?? 0;
            $statusLabel  = $pr['status_label']        ?? 'Awaiting Decision';
            $appliedRule  = $pr['applied_rule']['name'] ?? null;

            // Clean rule display
            $ruleDisplay = '';
            if ($appliedRule) {
                $ruleDisplay = preg_replace('/^Rule\s+\d+\s*[-:.]?\s*/i', '', $appliedRule);
                $ruleDisplay = trim($ruleDisplay);
                if (empty($ruleDisplay)) {
                    $ruleDisplay = preg_replace('/^Rule\s+\d+\s*/i', '', $appliedRule);
                    $ruleDisplay = trim($ruleDisplay);
                }
                if (empty($ruleDisplay) || $ruleDisplay === 'null') {
                    $ruleDisplay = '';
                }
            }
        @endphp

        <div class="student-section">
            {{-- SCHOOL NAME HEADER --}}
            <div class="school-name-header">
                <div class="school-full-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
            </div>

            {{-- HEADER: Logo + Contact + Photo --}}
            <table class="header-table">
                <tr>
                    <td width="18%" style="text-align:center; padding: 4px 6px; vertical-align:middle;">
                        <div class="school-logo">
                            @php
                                $logoSrc = $studentData['school_logo_base64'] ??
                                    'data:image/svg+xml;base64,' . base64_encode(
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="72" height="85" viewBox="0 0 100 100">
                                        <rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/>
                                        <circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/>
                                        <rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/>
                                        </svg>'
                                    );
                            @endphp
                            <img src="{{ $logoSrc }}" alt="School Logo">
                        </div>
                    </td>
                    <td style="vertical-align:top; padding: 4px 7px;">
                        <table style="border:none; border-collapse:collapse; width:100%; font-size:10px;">
                            <tr>
                                <td style="font-weight:900; color:#1e40af; white-space:nowrap; vertical-align:top; padding:0 4px 0 0;">Address:</td>
                                <td style="vertical-align:top; padding:0;">{{ $schoolInfo->school_address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:900; color:#1e40af; white-space:nowrap; padding:0 4px 0 0;">Phone:</td>
                                <td style="vertical-align:top; padding:0;">{{ $schoolInfo->formatted_phones ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:900; color:#1e40af; white-space:nowrap; padding:0 4px 0 0;">Email:</td>
                                <td style="vertical-align:top; padding:0;">{{ $schoolInfo->school_email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:900; color:#1e40af; white-space:nowrap; padding:0 4px 0 0;">Website:</td>
                                <td style="vertical-align:top; padding:0;">{{ $schoolInfo->school_website ?? '—' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="20%" style="text-align:right; padding: 4px 6px 4px 0; vertical-align:middle;">
                        @if(in_array('picture', $columnsToShow))
                        <div class="photo-frame">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='85' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3C/svg%3E" alt="Default Photo">
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="header-divider"></div>
            <div class="header-divider2"></div>

            {{-- REPORT TITLE --}}
            <div class="report-title">
                {{ strtoupper($term) }} {{ strtoupper($session) }} ACADEMIC SESSION TERMINAL PROGRESS REPORT
            </div>

            {{-- STUDENT INFO BAR --}}
            @if ($studentData['students'] && $studentData['students']->isNotEmpty())
                @php
                    $profile         = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()
                        ? $studentData['studentpp']->first() : null;
                    $fullNameDisplay = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
                    $admNoDisplay    = $student->admissionNo ?? '—';
                    $classValDisplay = ($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
                    $schoolOpened    = $schoolInfo->date_school_opened
                        ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y') : '—';
                    $numInClass = $studentData['numberOfStudents'] ?? '—';
                @endphp

                <div class="student-info-bar">
                    <table class="info-table">
                        <tr>
                            <td><span class="info-bar-label">NAME:</span> <span class="info-bar-value">{{ $fullNameDisplay }}</span></td>
                            <td><span class="info-bar-label">SESSION:</span> <span class="info-bar-value">{{ $session }}</span></td>
                            <td><span class="info-bar-label">TERM:</span> <span class="info-bar-value">{{ $term }}</span></td>
                            <td><span class="info-bar-label">CLASS:</span> <span class="info-bar-value">{{ $classValDisplay }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-bar-label">ADM NO:</span> <span class="info-bar-value">{{ $admNoDisplay }}</span></td>
                            <td><span class="info-bar-label">SCHOOL OPENED:</span> <span class="info-bar-value">{{ $schoolOpened }}</span></td>
                            <td><span class="info-bar-label">NO. IN CLASS:</span> <span class="info-bar-value">{{ $numInClass }}</span></td>
                            @if(in_array('gender', $columnsToShow))
                                <td><span class="info-bar-label">SEX:</span> <span class="info-bar-value">{{ $student->gender ?? '—' }}</span></td>
                            @endif
                        </tr>
                    </table>
                </div>
            @endif

            {{-- RESULT TABLE --}}
            <div class="result-table">
                <table>
                    <thead>
                        <tr>
                            @if(in_array('sn', $columnsToShow))
                                <th class="col-sn">S/N</th>
                            @endif
                            @if(in_array('admission_no', $columnsToShow))
                                <th class="col-admissionno">Adm No</th>
                            @endif
                            @if(in_array('name', $columnsToShow))
                                <th class="col-name">Subject</th>
                            @endif

                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    <th class="col-assessment">
                                        {{ $assessment->name }}<br>
                                        <span style="font-size:6.5px;">({{ $assessment->max_score }})</span>
                                    </th>
                                @endif
                            @endforeach

                            @if(in_array('total', $columnsToShow))
                                <th class="col-total">Total<br><span style="font-size:6.5px;">(100)</span></th>
                            @endif
                            @if(in_array('bf', $columnsToShow))
                                <th class="col-bf">BF</th>
                            @endif
                            @if(in_array('cum', $columnsToShow))
                                <th class="col-cum">Cum</th>
                            @endif
                            @if(in_array('cum_ave', $columnsToShow))
                                <th class="col-cum">Cum<br><span style="font-size:6.5px;">Ave</span></th>
                            @endif
                            @if(in_array('grade', $columnsToShow))
                                <th class="col-grade">Grade<br><span style="font-size:6.5px;">({{ $gradeBasis === 'cum_ave' ? 'Cum Ave' : 'Total' }})</span></th>
                            @endif
                            @if(in_array('arm_position', $columnsToShow))
                                <th class="col-position">Arm Pos<br>(Total)</th>
                            @endif
                            @if(in_array('arm_position_cum', $columnsToShow))
                                <th class="col-position">Arm Pos<br>(Cum)</th>
                            @endif
                            @if(in_array('position_total', $columnsToShow))
                                <th class="col-position">Class Pos<br>(Total)</th>
                            @endif
                            @if(in_array('position', $columnsToShow))
                                <th class="col-position">Class Pos<br>(Cum)</th>
                            @endif
                            @if(in_array('class_average', $columnsToShow))
                                <th class="col-class-average">Subject<br><span style="font-size:6.5px;">Ave</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($studentData['scores'] as $scoreIndex => $score)
                        @php
                            $posCum    = isset($score->position) && $score->position > 0 ? $score->position : null;
                            $posTotal  = isset($score->position_total) && $score->position_total > 0 ? $score->position_total : null;
                            $armPos    = isset($score->arm_position) && $score->arm_position > 0 ? $score->arm_position : null;
                            $armPosCum = isset($score->arm_position_cum) && $score->arm_position_cum > 0 ? $score->arm_position_cum : null;

                            if (!$posCum && property_exists($score, 'subject_position_class')) {
                                $posCum = $score->subject_position_class > 0 ? $score->subject_position_class : null;
                            }
                            if (!$posTotal && property_exists($score, 'subject_position_class_total')) {
                                $posTotal = $score->subject_position_class_total > 0 ? $score->subject_position_class_total : null;
                            }

                            $posCumClass    = ($posCum == 1) ? 'position-1' : (($posCum == 2) ? 'position-2' : (($posCum == 3) ? 'position-3' : ''));
                            $posTotalClass  = ($posTotal == 1) ? 'position-1' : (($posTotal == 2) ? 'position-2' : (($posTotal == 3) ? 'position-3' : ''));
                            $armPosClass    = ($armPos == 1) ? 'position-1' : (($armPos == 2) ? 'position-2' : (($armPos == 3) ? 'position-3' : ''));
                            $armPosCumClass = ($armPosCum == 1) ? 'position-1' : (($armPosCum == 2) ? 'position-2' : (($armPosCum == 3) ? 'position-3' : ''));

                            $posCumFormatted    = formatOrdinal($posCum);
                            $posTotalFormatted  = formatOrdinal($posTotal);
                            $armPosFormatted    = formatOrdinal($armPos);
                            $armPosCumFormatted = formatOrdinal($armPosCum);
                        @endphp
                        <tr>
                            @if(in_array('sn', $columnsToShow))
                                <td>{{ $scoreIndex + 1 }}</td>
                            @endif
                            @if(in_array('admission_no', $columnsToShow))
                                <td>{{ $student->admissionNo ?? '-' }}</td>
                            @endif
                            @if(in_array('name', $columnsToShow))
                                <td class="subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td>
                            @endif

                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    @php
                                        $assessmentScore = 0;
                                        if (isset($score->assessment_scores)) {
                                            $found = $score->assessment_scores->firstWhere('assessment_id', $assessment->id);
                                            $assessmentScore = $found ? $found->score : 0;
                                        }
                                        $isLow = $assessmentScore < ($assessment->max_score * 0.5);
                                    @endphp
                                    <td @if($isLow && is_numeric($assessmentScore)) class="highlight-red" @endif>
                                        {{ $assessmentScore ? number_format($assessmentScore, 0) : '-' }}
                                    </td>
                                @endif
                            @endforeach

                            @if(in_array('total', $columnsToShow))
                                <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>
                                    {{ $score->total ? number_format($score->total, 1) : '-' }}
                                </td>
                            @endif
                            @if(in_array('bf', $columnsToShow))
                                <td>{{ $score->bf ? number_format($score->bf, 1) : '-' }}</td>
                            @endif
                            @if(in_array('cum', $columnsToShow))
                                {{-- Raw cumulative sum (BF + this term's total) --}}
                                <td>{{ $score->cum ? number_format($score->cum, 1) : '-' }}</td>
                            @endif
                            @if(in_array('cum_ave', $columnsToShow))
                                {{-- Cumulative sum divided by the term number --}}
                                <td>{{ $score->cum_ave ? number_format($score->cum_ave, 1) : '-' }}</td>
                            @endif

                            @if(in_array('grade', $columnsToShow))
                                @php
                                    // Grade basis toggle: 'total' uses the stored term-total
                                    // grade as before; 'cum_ave' recalculates the grade from
                                    // the cumulative average instead, using the same class
                                    // category rules (senior A1–F9 / junior A–F) as everywhere else.
                                    if ($gradeBasis === 'cum_ave' && $gradeCategory && $score->cum_ave !== null) {
                                        $gradeRaw = $gradeCategory->calculateGrade($score->cum_ave);
                                    } else {
                                        $gradeRaw = $score->grade ?? '-';
                                    }
                                    $gradeUpper = strtoupper($gradeRaw);
                                    $gradeClass = match(true) {
                                        str_starts_with($gradeUpper, 'A') => 'grade-A',
                                        str_starts_with($gradeUpper, 'B') => 'grade-B',
                                        str_starts_with($gradeUpper, 'C') => 'grade-C',
                                        str_starts_with($gradeUpper, 'D') => 'grade-D',
                                        default                           => 'grade-F',
                                    };
                                @endphp
                                <td class="{{ $gradeClass }}">{{ $gradeRaw }}</td>
                            @endif

                            @if(in_array('arm_position', $columnsToShow))
                                <td class="{{ $armPosClass }}">{{ $armPosFormatted }}</td>
                            @endif
                            @if(in_array('arm_position_cum', $columnsToShow))
                                <td class="{{ $armPosCumClass }}">{{ $armPosCumFormatted }}</td>
                            @endif
                            @if(in_array('position_total', $columnsToShow))
                                <td class="{{ $posTotalClass }}">{{ $posTotalFormatted }}</td>
                            @endif
                            @if(in_array('position', $columnsToShow))
                                <td class="{{ $posCumClass }}">{{ $posCumFormatted }}</td>
                            @endif
                            @if(in_array('class_average', $columnsToShow))
                                <td>{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="30" style="text-align:center; padding:8px;">No scores available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TOTALS SUMMARY --}}
            <div class="totals-summary">
                TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                % OBTAINED: {{ $totals['percentage'] ?? 0 }}%
            </div>

            {{-- PROMOTION BADGE --}}
            @if($isTermPromotional)
                @if($promoStatus === 'promoted')
                    <div class="promo-card promo-promoted">
                        <div class="promo-title">{{ $statusLabel }}</div>
                        @if($ruleDisplay && $ruleDisplay !== 'null' && $ruleDisplay !== '')
                            <div class="promo-rule">{{ $ruleDisplay }}</div>
                        @endif
                        <div class="promo-message">
                            @if($promoTotal > 0)
                                Passed {{ $promoPassed }}/{{ $promoTotal }} compulsory subject(s)
                            @else
                                Met all promotion requirements
                            @endif
                        </div>
                        @if($reqAvg !== null && $actAvg !== null)
                            <div class="promo-average">
                                Average: {{ number_format($actAvg, 1) }}%
                                (Required: {{ number_format($reqAvg, 1) }}%) ✓
                            </div>
                        @endif
                    </div>
                @elseif($promoStatus === 'trial')
                    <div class="promo-card promo-trial">
                        <div class="promo-title">{{ $statusLabel }}</div>
                        @if($ruleDisplay && $ruleDisplay !== 'null' && $ruleDisplay !== '')
                            <div class="promo-rule">{{ $ruleDisplay }}</div>
                        @endif
                        <div class="promo-message">Promoted conditionally - needs improvement</div>
                        @if($reqAvg !== null && $actAvg !== null)
                            <div class="promo-average">
                                Average: {{ number_format($actAvg, 1) }}%
                                (Required: {{ number_format($reqAvg, 1) }}%)
                            </div>
                        @endif
                    </div>
                @elseif($promoStatus === 'see_principal')
                    <div class="promo-card promo-principal">
                        <div class="promo-title">{{ $statusLabel }}</div>
                        @if($ruleDisplay && $ruleDisplay !== 'null' && $ruleDisplay !== '')
                            <div class="promo-rule">{{ $ruleDisplay }}</div>
                        @endif
                        <div class="promo-message">Parents must see the Principal for discussion</div>
                        @if($reqAvg !== null && $actAvg !== null)
                            <div class="promo-average">
                                Average: {{ number_format($actAvg, 1) }}%
                                (Required: {{ number_format($reqAvg, 1) }}%)
                            </div>
                        @endif
                    </div>
                @elseif($promoStatus === 'repeated' || $promoStatus === 'repeat')
                    <div class="promo-card promo-repeated">
                        <div class="promo-title">{{ $statusLabel }}</div>
                        @if(!empty($promoFailed))
                            <div class="promo-message">
                                Failed: {{ collect($promoFailed)->pluck('subject')->filter()->implode(', ') }}
                            </div>
                        @endif
                        @if($reqAvg !== null && $actAvg !== null)
                            <div class="promo-average">
                                Average: {{ number_format($actAvg, 1) }}%
                                (Required: {{ number_format($reqAvg, 1) }}%)
                            </div>
                        @endif
                    </div>
                @else
                    <div class="promo-card promo-awaiting">
                        <div class="promo-title">AWAITING DECISION</div>
                        <div class="promo-message">Promotion decision pending further review</div>
                    </div>
                @endif
            @else
                <div class="promo-card promo-awaiting">
                    <div class="promo-title">NON-PROMOTIONAL TERM</div>
                    <div class="promo-message">This term is not a promotional term. Promotion is only assessed at the end of the academic year (Third Term).</div>
                </div>
            @endif

            {{-- ATTENDANCE BOX --}}
            @if($showAnyAttendance)
            <div class="attendance-box">
                <div class="attendance-box-header">📅 Attendance Record — {{ $term }}</div>
                @if(!$attFound)
                    <div style="padding:6px 10px;font-size:9px;color:#6b7280;text-align:center;background:#f9fafb;">
                        No attendance record available for this term.
                    </div>
                @else
                    <div class="attendance-grid">
                        @if(in_array('attendance_total_days', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">School Days</span>
                            <span class="att-value">{{ $attendance['total_school_days'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_days_present', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Present</span>
                            <span class="att-value att-ok">{{ $attendance['days_present'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_days_absent', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Absent</span>
                            <span class="att-value {{ ($attendance['days_absent'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">
                                {{ $attendance['days_absent'] ?? 0 }}
                            </span>
                        </div>
                        @endif
                        @if(in_array('attendance_days_late', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Late</span>
                            <span class="att-value {{ ($attendance['days_late'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">
                                {{ $attendance['days_late'] ?? 0 }}
                            </span>
                        </div>
                        @endif
                        @if(in_array('attendance_sick_leave', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Sick</span>
                            <span class="att-value">{{ $attendance['days_sick_leave'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_excused', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Excused</span>
                            <span class="att-value">{{ $attendance['days_excused'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_percentage', $columnsToShow))
                        <div class="att-cell" style="min-width:70px;">
                            <span class="att-label">Attendance %</span>
                            <span class="att-value {{ $attWarn ? 'att-warn' : 'att-ok' }}">{{ $attPct }}%</span>
                        </div>
                        @endif
                    </div>
                    @if(in_array('attendance_percentage', $columnsToShow))
                    <div style="padding:3px 8px 5px;">
                        <div class="att-pct-bar-wrap">
                            <div class="att-pct-bar {{ $attWarn ? 'att-pct-warn' : '' }}" style="width:{{ min($attPct, 100) }}%;"></div>
                        </div>
                        <div style="font-size:8px;color:#6b7280;margin-top:2px;text-align:right;">
                            {{ $attWarn ? 'Below 75% — requires attention' : 'Satisfactory attendance' }}
                        </div>
                    </div>
                    @endif
                @endif
            </div>
            @endif

            {{-- REMARKS --}}
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Remark</div>
                            <div>{{ $profile ? ($profile->classteachercomment ?? 'NO COMMENT') : 'NO COMMENT' }}</div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark</div>
                            <div>{{ $profile ? ($profile->principalscomment ?? 'NO COMMENT') : 'NO COMMENT' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- BOTTOM STRIP --}}
            <div class="bottom-strip">
                <table>
                    <tr>
                        <td class="cell-qr">
                            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
                            <div class="qr-label">Scan for Verification</div>
                        </td>
                        <td class="cell-footer">
                            <div><strong>Issued:</strong> <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span></div>
                            <div style="margin-top:3px;"><strong>Collected by:</strong> <span class="text-dot-space2">.......................................</span></div>
                            <div style="margin-top:3px;"><strong>Next Term Begins:</strong> <span class="text-dot-space2">
                                @php
                                    $nextTerm = $schoolInfo->date_next_term_begins ?? null;
                                    echo $nextTerm ? \Carbon\Carbon::parse($nextTerm)->format('jS F, Y') : '........................';
                                @endphp
                            </span></div>
                            <div class="powered-by">Powered by Qudroid Systems</div>
                        </td>
                        <td class="cell-stamp">
                            <img src="{{ $stampSrc }}" alt="School Stamp">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>
</html>
