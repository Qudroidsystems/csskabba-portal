<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Terminal Progress Report</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
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
            width: 100%;
            background: #111827;
            color: white;
            padding: 7px 10px 5px 10px;
            border: 3px double #000000;
            border-bottom: 1px solid #1e40af;
            text-align: center;
        }

        .school-name-header .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .school-name-header .motto {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: 0.95;
            margin-top: 2px;
        }

        /* One card per printed page */
        .student-section {
            width: 100%;
            max-width: 190mm;
            page-break-after: always;
            page-break-inside: avoid;
            break-after: page;
            break-inside: avoid;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto 10px auto;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            overflow-x: visible;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            padding: 3px 7px;
        }

        .header-table td {
            vertical-align: middle;
        }

        /* School logo */
        .school-logo {
            width: 60px;
            height: 70px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: white;
            padding: 3px;
            overflow: hidden;
            display: block;
            text-align: center;
            margin: 0 auto;
        }

        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Photo frame */
        .photo-frame {
            width: 60px;
            height: 70px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: #e2e8f0;
            padding: 0;
            overflow: hidden;
            display: block;
            margin-left: auto;
            margin-right: 0;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .header-divider { height: 2px; background: #1e40af; width: 100%; }
        .header-divider2 { height: 1px; background: #64748b; width: 100%; margin: 1px 0; }

        .report-title {
            background: #111827;
            color: white;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .student-info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 6px;
            padding: 5px 8px;
            margin: 5px 8px;
            font-size: 9px;
            text-align: center;
        }

        .info-table { width: 100%; margin: 0 auto; }
        .info-table td { padding: 2px 4px; text-align: center; }
        .info-bar-label { color: #1e40af; font-weight: 900; font-size: 8.5px; white-space: nowrap; }
        .info-bar-value { font-weight: 900; font-size: 9px; padding-left: 3px; }

        /* RESULT TABLE - FIXED TO PREVENT OVERFLOW */
        .result-table {
            padding: 0 6px;
            margin: 5px 0;
            overflow-x: auto;
            overflow-y: visible;
        }

        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            font-size: 7.5px;
            margin: 0;
            table-layout: fixed;
        }

        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 3px 1px;
            font-size: 6.8px;
            text-align: center;
            line-height: 1.2;
            word-break: break-word;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 2px 1px;
            text-align: center;
            font-size: 7.5px;
            background: white;
            font-weight: 800;
            word-break: break-word;
        }

        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 800;
            font-size: 7.5px;
            padding-left: 4px;
        }

        .highlight-red { color: #dc2626; font-weight: 900; }

        /* FIXED COLUMN WIDTHS */
        .col-sn { width: 22px; }
        .col-admissionno { width: 55px; }
        .col-name { width: 100px; }
        .col-assessment { width: 32px; }
        .col-total { width: 32px; }
        .col-bf { width: 28px; }
        .col-cum { width: 32px; }
        .col-grade { width: 30px; }
        .col-position { width: 38px; }
        .col-class-average { width: 32px; }

        .totals-summary {
            width: calc(100% - 16px);
            background: #0d1a3d;
            color: #ffffff;
            font-weight: 900;
            font-size: 8.5px;
            padding: 4px 8px;
            border: 2px solid #000000;
            border-top: none;
            text-align: center;
            margin: 0 8px 5px 8px;
        }

        /* Position styling */
        .position-1 { background-color: #FFD700; color: #000000; font-weight: 900; }
        .position-2 { background-color: #C0C0C0; color: #000000; font-weight: 900; }
        .position-3 { background-color: #CD7F32; color: #000000; font-weight: 900; }
        td.position-1, td.position-2, td.position-3 { color: #000000 !important; }

        /* Attendance Box */
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
            font-size: 8px;
            font-weight: 900;
            padding: 4px 10px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .attendance-grid {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }

        .att-cell {
            flex: 1;
            padding: 4px 4px;
            text-align: center;
            border-right: 1px solid #d1fae5;
            vertical-align: middle;
            background: #f0fdf9;
            min-width: 55px;
        }

        .att-cell:last-child { border-right: none; }

        .att-label {
            font-size: 7px;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 1px;
        }

        .att-value {
            font-size: 9px;
            font-weight: 900;
            color: #111827;
            display: block;
        }

        .att-value.att-warn { color: #dc2626; }
        .att-value.att-ok { color: #16a34a; }

        .att-pct-bar-wrap {
            width: calc(100% - 16px);
            margin: 0 8px 2px 8px;
            background: #e2e8f0;
            border-radius: 20px;
            height: 4px;
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

        /* Remarks Table */
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
            font-size: 8.5px;
        }

        .remarks-table .h6 {
            font-weight: 700;
            margin-bottom: 3px;
            font-size: 9px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
        }

        /* Bottom Strip */
        .bottom-strip { width: 100%; border-top: 1px solid #cbd5e1; background: #f1f5f9; margin-top: 4px; }
        .bottom-strip table { width: 100%; border-collapse: collapse; }
        .bottom-strip td { padding: 5px 6px; vertical-align: middle; }
        .bottom-strip .cell-qr { width: 70px; text-align: center; vertical-align: middle; }
        .bottom-strip .cell-footer { text-align: center; font-size: 8px; vertical-align: middle; }
        .bottom-strip .cell-stamp { width: 90px; text-align: center; vertical-align: middle; }
        .bottom-strip .cell-qr img { width: 50px; height: 50px; display: block; margin: 0 auto 2px; }
        .qr-label { font-size: 6.5px; color: #333; font-weight: 600; text-align: center; }
        .bottom-strip .cell-stamp img { width: 70px; height: 70px; transform: rotate(-8deg); display: block; margin: 0 auto; }

        .text-dot-space2 {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 80px;
            font-weight: bold;
            margin: 0 4px;
        }

        .powered-by { font-size: 7px; margin-top: 3px; color: #64748b; }

        /* Grade colours */
        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }

        /* Promotion badge */
        .promo-badge-pdf {
            width: calc(100% - 16px);
            margin: 3px 8px 5px 8px;
            padding: 5px 10px;
            border-radius: 6px;
            border: 2px solid #000;
            font-size: 9px;
            font-weight: 700;
        }
        .promo-badge-pdf .promo-pdf-label {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .4px;
            display: block;
        }
        .promo-badge-pdf .promo-pdf-sub {
            font-size: 8px;
            display: block;
            margin-top: 2px;
            font-weight: 600;
            opacity: .9;
        }
        .promo-pdf-promoted { background: #f0fdf4; border-color: #16a34a; color: #14532d; }
        .promo-pdf-repeated { background: #fef2f2; border-color: #dc2626; color: #7f1d1d; }
        .promo-pdf-awaiting { background: #f8fafc; border-color: #94a3b8; color: #475569; }

        /* PRINT STYLES */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .student-section {
                box-shadow: none;
                page-break-inside: avoid;
                page-break-after: always;
                break-after: page;
                margin: 0;
                width: 100%;
            }
            .watermark-text {
                color: rgba(0, 0, 0, 0.03);
            }
            .result-table {
                overflow: visible;
            }
        }

        /* BUTTON STYLES FOR BROWSER */
        .print-controls {
            position: sticky;
            top: 10px;
            z-index: 2000;
            background: #1e293b;
            padding: 10px 20px;
            border-radius: 40px;
            display: inline-flex;
            gap: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .print-controls button {
            background: #fbbf24;
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 30px;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }
        .print-controls button:hover {
            background: #f59e0b;
            transform: scale(1.02);
        }
        .print-controls .download-btn {
            background: #10b981;
            color: white;
        }
        .print-controls .download-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <div class="watermark-text">STUDENT COPY</div>

    <!-- Browser controls - display first, then user can download/print -->
    <div class="print-controls">
        <button onclick="window.print();">🖨️ Print / Save as PDF</button>
        <button class="download-btn" onclick="downloadPDF();">📥 Download PDF</button>
    </div>

    @php
        function formatOrdinal($number) {
            if (!is_numeric($number) || $number <= 0) { return '-'; }
            $lastDigit = $number % 10;
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
        $defaultColumns = [
            'sn', 'admission_no', 'name',
            'total', 'bf', 'cum', 'grade',
            'position', 'position_total', 'arm_position', 'arm_position_cum',
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
            $schoolInfo = $studentData['schoolInfo'] ?? null;
            $student = $studentData['students'] && $studentData['students']->isNotEmpty()
                ? $studentData['students']->first() : null;
            $assessments = $studentData['assessments'] ?? collect();
            $totals = $studentData['totals_summary'] ?? [];
            $attendance = $studentData['attendance_summary'] ?? [];

            // SAFE ACCESS: Check if studentpp exists and is not empty (from Studentpersonalityprofile)
            $profile = null;
            if (isset($studentData['studentpp']) && $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()) {
                $profile = $studentData['studentpp']->first();
            }

            $admNo = $student->admissionNo ?? 'N/A';
            $fullName = trim(strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));

            // SAFE ACCESS: Check if arms relationship exists
            $armName = '';
            if (isset($studentData['schoolclass']) && isset($studentData['schoolclass']->arms)) {
                $armName = $studentData['schoolclass']->arms->arm ?? '';
            }
            $classVal = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . $armName);

            $session = $metadata['session'] ?? '2025/2026';
            $term = $metadata['term'] ?? 'SECOND TERM';

            $qrData = "Name: {$fullName}\nAdm No: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}\nSchool: " . ($schoolInfo->school_name ?? 'School');

            $qrCodeBase64 = base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(200)
                    ->errorCorrection('H')
                    ->generate($qrData)
            );

            $stampSrc = !empty($studentData['school_stamp_base64'])
                ? $studentData['school_stamp_base64']
                : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ccircle cx="50" cy="50" r="45" fill="%23f1f5f9" stroke="%233b82f6" stroke-width="2"/%3E%3Ctext x="50" y="55" text-anchor="middle" fill="%231e293b" font-size="12"%3ESTAMP%3C/text%3E%3C/svg%3E';

            $attPct = isset($attendance['attendance_percentage']) ? round($attendance['attendance_percentage'], 1) : 0;
            $attWarn = $attPct < 75 && $attPct > 0;
            $attFound = $attendance['found'] ?? false;
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
                    <td width="15%" style="text-align:center; padding: 4px 4px;">
                        <div class="school-logo">
                            @php
                                $logoSrc = $studentData['school_logo_base64'] ??
                                    'data:image/svg+xml;base64,' . base64_encode(
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="60" height="70" viewBox="0 0 100 100">
                                        <rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/>
                                        <circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/>
                                        <rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/>
                                        </svg>'
                                    );
                            @endphp
                            <img src="{{ $logoSrc }}" alt="School Logo">
                        </div>
                    </td>
                    <td style="vertical-align:top; padding: 4px 6px;">
                        <table style="border:none; border-collapse:collapse; width:100%; font-size:8.5px;">
                            <tr><td style="font-weight:900; color:#1e40af;">Address:</td><td>{{ $schoolInfo->school_address ?? '—' }}</td></tr>
                            <tr><td style="font-weight:900; color:#1e40af;">Phone:</td><td>{{ $schoolInfo->formatted_phones ?? '—' }}</td></tr>
                            <tr><td style="font-weight:900; color:#1e40af;">Email:</td><td>{{ $schoolInfo->school_email ?? '—' }}</td></tr>
                            <tr><td style="font-weight:900; color:#1e40af;">Website:</td><td>{{ $schoolInfo->school_website ?? '—' }}</td></tr>
                        </table>
                    </td>
                    <td width="15%" style="text-align:right; padding: 4px 4px;">
                        @if(in_array('picture', $columnsToShow))
                        <div class="photo-frame">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='70' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3C/svg%3E" alt="Default Photo">
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
                    $fullNameDisplay = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
                    $admNoDisplay = $student->admissionNo ?? '—';
                    $classValDisplay = $classVal;
                    $schoolOpened = $schoolInfo->date_school_opened
                        ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y') : '—';
                    $numInClass = $studentData['numberOfStudents'] ?? '—';
                @endphp

                <div class="student-info-bar">
                    <table class="info-table">
                        <tr><td><span class="info-bar-label">NAME:</span> <span class="info-bar-value">{{ $fullNameDisplay }}</span></td>
                            <td><span class="info-bar-label">SESSION:</span> <span class="info-bar-value">{{ $session }}</span></td>
                            <td><span class="info-bar-label">TERM:</span> <span class="info-bar-value">{{ $term }}</span></td>
                            <td><span class="info-bar-label">CLASS:</span> <span class="info-bar-value">{{ $classValDisplay }}</span></td>
                        </tr>
                        <tr><td><span class="info-bar-label">ADM NO:</span> <span class="info-bar-value">{{ $admNoDisplay }}</span></td>
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
                            @if(in_array('sn', $columnsToShow)) <th class="col-sn">S/N</th> @endif
                            @if(in_array('admission_no', $columnsToShow)) <th class="col-admissionno">Adm No</th> @endif
                            @if(in_array('name', $columnsToShow)) <th class="col-name">Subject</th> @endif

                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    <th class="col-assessment">{{ $assessment->name }}<br><span style="font-size:5.5px;">({{ $assessment->max_score }})</span></th>
                                @endif
                            @endforeach

                            @if(in_array('total', $columnsToShow)) <th class="col-total">Total</th> @endif
                            @if(in_array('bf', $columnsToShow)) <th class="col-bf">BF</th> @endif
                            @if(in_array('cum', $columnsToShow)) <th class="col-cum">Cum</th> @endif
                            @if(in_array('grade', $columnsToShow)) <th class="col-grade">Grade</th> @endif
                            @if(in_array('position', $columnsToShow)) <th class="col-position">Class Pos<br>(Cum)</th> @endif
                            @if(in_array('position_total', $columnsToShow)) <th class="col-position">Class Pos<br>(Total)</th> @endif
                            @if(in_array('arm_position', $columnsToShow)) <th class="col-position">Arm Pos<br>(Total)</th> @endif
                            @if(in_array('arm_position_cum', $columnsToShow)) <th class="col-position">Arm Pos<br>(Cum)</th> @endif
                            @if(in_array('class_average', $columnsToShow)) <th class="col-class-average">Avg</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($studentData['scores'] as $scoreIndex => $score)
                        @php
                            $posCum = isset($score->position) && $score->position > 0 ? $score->position : null;
                            $posTotal = isset($score->position_total) && $score->position_total > 0 ? $score->position_total : null;
                            $armPos = isset($score->arm_position) && $score->arm_position > 0 ? $score->arm_position : null;
                            $armPosCum = isset($score->arm_position_cum) && $score->arm_position_cum > 0 ? $score->arm_position_cum : null;

                            $posCumClass = ($posCum == 1) ? 'position-1' : (($posCum == 2) ? 'position-2' : (($posCum == 3) ? 'position-3' : ''));
                            $posTotalClass = ($posTotal == 1) ? 'position-1' : (($posTotal == 2) ? 'position-2' : (($posTotal == 3) ? 'position-3' : ''));
                            $armPosClass = ($armPos == 1) ? 'position-1' : (($armPos == 2) ? 'position-2' : (($armPos == 3) ? 'position-3' : ''));
                            $armPosCumClass = ($armPosCum == 1) ? 'position-1' : (($armPosCum == 2) ? 'position-2' : (($armPosCum == 3) ? 'position-3' : ''));

                            $posCumFormatted = formatOrdinal($posCum);
                            $posTotalFormatted = formatOrdinal($posTotal);
                            $armPosFormatted = formatOrdinal($armPos);
                            $armPosCumFormatted = formatOrdinal($armPosCum);
                        @endphp
                        <tr>
                            @if(in_array('sn', $columnsToShow)) <td>{{ $scoreIndex + 1 }}</td> @endif
                            @if(in_array('admission_no', $columnsToShow)) <td>{{ $student->admissionNo ?? '-' }}</td> @endif
                            @if(in_array('name', $columnsToShow)) <td class="subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td> @endif

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

                            @if(in_array('total', $columnsToShow)) <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>{{ $score->total ? number_format($score->total, 1) : '-' }}</td> @endif
                            @if(in_array('bf', $columnsToShow)) <td>{{ $score->bf ? number_format($score->bf, 1) : '-' }}</td> @endif
                            @if(in_array('cum', $columnsToShow)) <td>{{ $score->cum ? number_format($score->cum, 1) : '-' }}</td> @endif

                            @if(in_array('grade', $columnsToShow))
                                @php
                                    $gradeRaw = $score->grade ?? '-';
                                    $gradeUpper = strtoupper($gradeRaw);
                                    $gradeClass = match(true) {
                                        str_starts_with($gradeUpper, 'A') => 'grade-A',
                                        str_starts_with($gradeUpper, 'B') => 'grade-B',
                                        str_starts_with($gradeUpper, 'C') => 'grade-C',
                                        str_starts_with($gradeUpper, 'D') => 'grade-D',
                                        default => 'grade-F',
                                    };
                                @endphp
                                <td class="{{ $gradeClass }}">{{ $gradeRaw }}</td>
                            @endif

                            @if(in_array('position', $columnsToShow)) <td class="{{ $posCumClass }}">{{ $posCumFormatted }}</td> @endif
                            @if(in_array('position_total', $columnsToShow)) <td class="{{ $posTotalClass }}">{{ $posTotalFormatted }}</td> @endif
                            @if(in_array('arm_position', $columnsToShow)) <td class="{{ $armPosClass }}">{{ $armPosFormatted }}</td> @endif
                            @if(in_array('arm_position_cum', $columnsToShow)) <td class="{{ $armPosCumClass }}">{{ $armPosCumFormatted }}</td> @endif
                            @if(in_array('class_average', $columnsToShow)) <td>{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td> @endif
                        </tr>
                        @empty
                        <tr><td colspan="20" style="text-align:center; padding:8px;">No scores available.</td></tr>
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
            @php
                $pr = $studentData['promotion_result'] ?? [];
                $promoStatus = $pr['status'] ?? 'awaiting';
                $isPromoTerm = $pr['is_promotional_term'] ?? false;
            @endphp

            @if (!$isPromoTerm)
                <div class="promo-badge-pdf promo-pdf-awaiting">
                    <span class="promo-pdf-label">⏳ Awaiting Final Term</span>
                    <span class="promo-pdf-sub">Promotion will be assessed at the end of the academic year.</span>
                </div>
            @elseif ($promoStatus === 'promoted')
                <div class="promo-badge-pdf promo-pdf-promoted">
                    <span class="promo-pdf-label">🎓 PROMOTED</span>
                </div>
            @else
                <div class="promo-badge-pdf promo-pdf-repeated">
                    <span class="promo-pdf-label">⚠️ NOT PROMOTED</span>
                </div>
            @endif

            {{-- ATTENDANCE BOX --}}
            @if($showAnyAttendance && $attFound)
            <div class="attendance-box">
                <div class="attendance-box-header">📅 Attendance Record — {{ $term }}</div>
                <div class="attendance-grid">
                    @if(in_array('attendance_total_days', $columnsToShow))
                    <div class="att-cell"><span class="att-label">School Days</span><span class="att-value">{{ $attendance['total_school_days'] ?? 0 }}</span></div>
                    @endif
                    @if(in_array('attendance_days_present', $columnsToShow))
                    <div class="att-cell"><span class="att-label">Present</span><span class="att-value att-ok">{{ $attendance['days_present'] ?? 0 }}</span></div>
                    @endif
                    @if(in_array('attendance_days_absent', $columnsToShow))
                    <div class="att-cell"><span class="att-label">Absent</span><span class="att-value {{ ($attendance['days_absent'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">{{ $attendance['days_absent'] ?? 0 }}</span></div>
                    @endif
                    @if(in_array('attendance_percentage', $columnsToShow))
                    <div class="att-cell"><span class="att-label">Attendance %</span><span class="att-value {{ $attWarn ? 'att-warn' : 'att-ok' }}">{{ $attPct }}%</span></div>
                    @endif
                </div>
                @if(in_array('attendance_percentage', $columnsToShow))
                <div class="att-pct-bar-wrap"><div class="att-pct-bar {{ $attWarn ? 'att-pct-warn' : '' }}" style="width:{{ min($attPct, 100) }}%;"></div></div>
                @endif
            </div>
            @endif

            {{-- REMARKS --}}
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%"><div class="h6">Class Teacher's Remark</div><div>{{ $profile ? ($profile->classteachercomment ?? 'NO COMMENT') : 'NO COMMENT' }}</div></td>
                        <td width="50%"><div class="h6">Principal's Remark</div><div>{{ $profile ? ($profile->principalscomment ?? 'NO COMMENT') : 'NO COMMENT' }}</div></td>
                    </tr>
                </tbody>
            </table>

            {{-- BOTTOM STRIP --}}
            <div class="bottom-strip">
                <table class="strip-table"><tr>
                    <td class="cell-qr"><img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR"><div class="qr-label">Scan to Verify</div></td>
                    <td class="cell-footer"><div><strong>Issued:</strong> <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span></div>
                    <div><strong>Parent/Guardian Sign:</strong> <span class="text-dot-space2">.......................</span></div>
                    <div><strong>Next Term Begins:</strong> <span class="text-dot-space2">{{ $schoolInfo->date_next_term_begins ? \Carbon\Carbon::parse($schoolInfo->date_next_term_begins)->format('jS F, Y') : '.......................' }}</span></div>
                    <div class="powered-by">Powered by Qudroid Systems</div></td>
                    <td class="cell-stamp"><img src="{{ $stampSrc }}" alt="Stamp"></td>
                </tr></table>
            </div>
        </div>
    @endforeach

    <script>
        function downloadPDF() {
            window.print();
        }
    </script>
</body>
</html>
