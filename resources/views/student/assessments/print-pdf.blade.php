<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Assessment Report - {{ $allStudentData[0]['schoolInfo']->school_name ?? 'School' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 8mm 5mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            padding: 0;
            text-align: center;
            position: relative;
        }

        /* WATERMARK */
        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 65px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.05);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 5px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
        }

        /* MAIN CARD */
        .result-card {
            width: 100%;
            max-width: 190mm;
            background: #ffffff;
            border: 2px solid #000000;
            margin: 0 auto 10px auto;
            padding: 0;
            position: relative;
            text-align: left;
            page-break-after: avoid;
            overflow: hidden;
        }

        /* SCHOOL NAME HEADER */
        .school-header {
            width: 100%;
            background: #1e3a5f;
            color: white;
            padding: 12px 10px 8px 10px;
            text-align: center;
            border-bottom: 3px solid #f59e0b;
        }

        .school-header .school-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .school-header .motto {
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 2px;
            margin-top: 4px;
            color: #fbbf24;
        }

        /* INFO TABLE */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
            border: none;
        }

        .logo-cell {
            width: 100px;
            text-align: center;
            vertical-align: middle;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            border: 2px solid #1e3a5f;
            border-radius: 50%;
            background: white;
            padding: 5px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .school-details {
            font-size: 9.5px;
            line-height: 1.6;
        }

        .school-details strong {
            color: #1e3a5f;
            font-weight: 700;
        }

        .photo-cell {
            width: 100px;
            text-align: center;
            vertical-align: middle;
        }

        .student-photo {
            width: 85px;
            height: 100px;
            border: 2px solid #1e3a5f;
            border-radius: 8px;
            background: #f1f5f9;
            padding: 5px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .student-photo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .divider {
            width: 100%;
            height: 2px;
            background: #1e3a5f;
            margin: 5px 0;
        }

        .divider-light {
            width: 100%;
            height: 1px;
            background: #cbd5e1;
            margin: 3px 0;
        }

        .report-title {
            background: #f59e0b;
            color: #1e3a5f;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
            margin: 5px 0;
        }

        /* STUDENT INFO BAR */
        .student-info-bar {
            background: linear-gradient(to bottom, #eef2ff 0%, #ffffff 100%);
            border: 1px solid #1e3a5f;
            border-radius: 6px;
            padding: 8px 12px;
            margin: 8px 10px;
            font-size: 9px;
        }

        .student-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-info-table td {
            padding: 4px 8px;
        }

        .info-label {
            color: #1e3a5f;
            font-weight: 700;
            font-size: 8.5px;
            white-space: nowrap;
        }

        .info-value {
            font-weight: 600;
            padding-left: 5px;
        }

        /* RESULT TABLE */
        .result-table {
            margin: 10px;
        }

        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        .result-table thead th {
            background: #1e3a5f;
            color: white;
            font-weight: 700;
            border: 1px solid #000000;
            padding: 5px 2px;
            font-size: 7.5px;
            text-align: center;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 4px 2px;
            text-align: center;
            background: white;
            height: 22px;
            vertical-align: middle;
        }

        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 600;
            padding-left: 8px;
        }

        .highlight-red {
            color: #dc2626;
            font-weight: 700;
        }

        /* Column widths */
        .col-sn { width: 25px; }
        .col-admissionno { width: 70px; }
        .col-name { width: 120px; }
        .col-assessment { width: 45px; }
        .col-total { width: 45px; }
        .col-cum { width: 45px; }
        .col-grade { width: 40px; }
        .col-position { width: 45px; }

        /* Grade colors */
        .grade-A1, .grade-A { color: #16a34a; font-weight: 700; }
        .grade-B2, .grade-B3, .grade-B { color: #2563eb; font-weight: 700; }
        .grade-C4, .grade-C5, .grade-C6, .grade-C { color: #ca8a04; font-weight: 700; }
        .grade-D7, .grade-E8, .grade-D, .grade-E { color: #ea580c; font-weight: 700; }
        .grade-F9, .grade-F { color: #dc2626; font-weight: 700; }

        /* Position medal colors */
        .position-1 { background: gold; color: black; font-weight: 900; }
        .position-2 { background: silver; color: black; font-weight: 900; }
        .position-3 { background: #cd7f32; color: white; font-weight: 900; }

        /* TOTALS SUMMARY */
        .totals-summary {
            background: #1e3a5f;
            color: white;
            font-weight: 700;
            font-size: 8.5px;
            padding: 6px 12px;
            text-align: center;
            margin: 10px;
        }

        /* REMARKS */
        .remarks-table {
            width: calc(100% - 20px);
            border: 1px solid #000000;
            border-collapse: collapse;
            margin: 10px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 9px;
        }

        .remarks-table .remark-title {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 9.5px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
        }

        /* FOOTER */
        .footer {
            background: #f1f5f9;
            padding: 8px 12px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            margin: 10px;
            font-size: 8.5px;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .sign-line {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 120px;
            margin: 0 5px;
        }

        .powered-by {
            font-size: 7.5px;
            margin-top: 5px;
            color: #64748b;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .result-card {
                margin: 0 auto;
                box-shadow: none;
                page-break-after: avoid;
            }
            .watermark-text {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="watermark-text">OFFICIAL TRANSCRIPT</div>

    @php
        /**
         * Format number with ordinal suffix (st, nd, rd, th)
         */
        function formatOrdinal($number) {
            if (!is_numeric($number) || $number <= 0) {
                return '-';
            }

            $lastDigit = $number % 10;
            $lastTwoDigits = $number % 100;

            if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
                return $number . 'th';
            }

            switch ($lastDigit) {
                case 1: return $number . 'st';
                case 2: return $number . 'nd';
                case 3: return $number . 'rd';
                default: return $number . 'th';
            }
        }

        $studentData = $allStudentData[0];
        $schoolInfo = $studentData['schoolInfo'] ?? null;
        $student = $studentData['students']->first();
        $scores = $studentData['scores'] ?? collect();
        $assessments = $studentData['assessments'] ?? collect();
        $totals = $studentData['totals_summary'] ?? [];
        $gpaData = $studentData['gpa_data'] ?? [];
        $class = $studentData['schoolclass'] ?? null;

        $fullName = strtoupper($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? '');
        $admNo = $student->admissionNo ?? '—';
        $className = ($class->schoolclass ?? '') . ' ' . ($class->arms->arm ?? '');

        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns = ['sn', 'name', 'total', 'cum', 'grade', 'position', 'position_total', 'arm_position', 'arm_position_cum'];
        $columnsToShow = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;

        // Count visible columns for colspan
        $baseCount = 0;
        if (in_array('sn', $columnsToShow)) $baseCount++;
        if (in_array('name', $columnsToShow)) $baseCount++;
        $assessmentCount = 0;
        foreach ($assessments as $assessment) {
            if (in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow)) $assessmentCount++;
        }
        $scoreColumns = ['total', 'cum', 'grade', 'position', 'position_total', 'arm_position', 'arm_position_cum'];
        $scoreCount = 0;
        foreach ($scoreColumns as $col) {
            if (in_array($col, $columnsToShow)) $scoreCount++;
        }
        $totalColumns = $baseCount + $assessmentCount + $scoreCount;

        // Format phones
        $phones = is_array($schoolInfo->school_phones ?? null)
            ? $schoolInfo->school_phones
            : (json_decode($schoolInfo->school_phones ?? '[]', true) ?? []);
        $formattedPhones = !empty($phones) ? implode(', ', $phones) : '—';
    @endphp

    <div class="result-card">
        <!-- SCHOOL HEADER -->
        <div class="school-header">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'PREMIUM ACADEMY' }}</div>
            <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
        </div>

        <!-- INFO TABLE: Logo + School Details + Photo -->
        <table class="info-table">
            <tr>
                <td class="logo-cell">
                    <div class="school-logo">
                        @if(!empty($studentData['school_logo_base64']))
                            <img src="{{ $studentData['school_logo_base64'] }}" alt="School Logo">
                        @else
                            <img src="data:image/svg+xml;base64,{{ base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#1e3a5f"/><text x="40" y="46" text-anchor="middle" fill="white" font-family="Arial" font-size="14" font-weight="bold">SCH</text></svg>') }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td class="school-details">
                    <strong>Address:</strong> {{ $schoolInfo->school_address ?? '—' }}<br>
                    <strong>Phone:</strong> {{ $formattedPhones }}<br>
                    <strong>Email:</strong> {{ $schoolInfo->school_email ?? '—' }}<br>
                    <strong>Website:</strong> {{ $schoolInfo->school_website ?? '—' }}
                </td>
                <td class="photo-cell">
                    <div class="student-photo">
                        @if(!empty($studentData['student_image_base64']))
                            <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                        @else
                            <img src="data:image/svg+xml;base64,{{ base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="95" viewBox="0 0 80 95"><rect width="80" height="95" fill="#e2e8f0"/><circle cx="40" cy="32" r="18" fill="#94a3b8"/><rect x="20" y="56" width="40" height="28" rx="4" fill="#94a3b8"/><text x="40" y="90" text-anchor="middle" fill="#475569" font-family="Arial" font-size="8">PHOTO</text></svg>') }}" alt="Photo">
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>
        <div class="divider-light"></div>

        <!-- REPORT TITLE -->
        <div class="report-title">
            {{ strtoupper($metadata['term'] ?? 'TERM') }} {{ strtoupper($metadata['session'] ?? 'SESSION') }} ACADEMIC SESSION ASSESSMENT REPORT
        </div>

        <!-- STUDENT INFO BAR -->
        <div class="student-info-bar">
            <table class="student-info-table">
                <tr>
                    <td width="25%"><span class="info-label">NAME:</span> <span class="info-value">{{ $fullName }}</span></td>
                    <td width="20%"><span class="info-label">SESSION:</span> <span class="info-value">{{ $metadata['session'] ?? '—' }}</span></td>
                    <td width="15%"><span class="info-label">TERM:</span> <span class="info-value">{{ $metadata['term'] ?? '—' }}</span></td>
                    <td width="20%"><span class="info-label">CLASS:</span> <span class="info-value">{{ $className }}</span></td>
                    <td width="20%"><span class="info-label">ADM NO:</span> <span class="info-value">{{ $admNo }}</span></td>
                </tr>
                <tr>
                    <td><span class="info-label">NO. IN CLASS:</span> <span class="info-value">{{ $studentData['numberOfStudents'] ?? '—' }}</span></td>
                    <td><span class="info-label">GPA:</span> <span class="info-value">{{ $gpaData['gpa'] ?? '-' }}</span></td>
                    <td><span class="info-label">CGPA:</span> <span class="info-value">{{ $gpaData['cgpa'] ?? '-' }}</span></td>
                    <td><span class="info-label">GPA GRADE:</span> <span class="info-value">{{ $gpaData['gpa_grade'] ?? '-' }}</span></td>
                    <td><span class="info-label">REPORT DATE:</span> <span class="info-value">{{ date('jS M, Y') }}</span></td>
                </tr>
            </table>
        </div>

        <!-- RESULTS TABLE -->
        <div class="result-table">
            <table>
                <thead>
                    <tr>
                        @if(in_array('sn', $columnsToShow))
                            <th class="col-sn">S/N</th>
                        @endif
                        @if(in_array('name', $columnsToShow))
                            <th class="col-name">Subject</th>
                        @endif
                        @foreach ($assessments as $assessment)
                            @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                <th class="col-assessment">
                                    {{ $assessment->name }}<br>
                                    <span style="font-size:6px;">({{ $assessment->max_score }})</span>
                                </th>
                            @endif
                        @endforeach
                        @if(in_array('total', $columnsToShow))
                            <th class="col-total">Total</th>
                        @endif
                        @if(in_array('cum', $columnsToShow))
                            <th class="col-cum">Cum</th>
                        @endif
                        @if(in_array('grade', $columnsToShow))
                            <th class="col-grade">Grade</th>
                        @endif
                        @if(in_array('position', $columnsToShow))
                            <th class="col-position">Class Pos<br><span style="font-size:6px;">(Cum)</span></th>
                        @endif
                        @if(in_array('position_total', $columnsToShow))
                            <th class="col-position">Class Pos<br><span style="font-size:6px;">(Total)</span></th>
                        @endif
                        @if(in_array('arm_position', $columnsToShow))
                            <th class="col-position">Arm Pos<br><span style="font-size:6px;">(Total)</span></th>
                        @endif
                        @if(in_array('arm_position_cum', $columnsToShow))
                            <th class="col-position">Arm Pos<br><span style="font-size:6px;">(Cum)</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scores as $index => $score)
                        @php
                            // Grade color class
                            $gradeRaw = $score->grade ?? '-';
                            $gradeUpper = strtoupper($gradeRaw);
                            $gradeClass = match(true) {
                                str_contains($gradeUpper, 'A') => 'grade-A',
                                str_contains($gradeUpper, 'B') => 'grade-B',
                                str_contains($gradeUpper, 'C') => 'grade-C',
                                str_contains($gradeUpper, 'D') || str_contains($gradeUpper, 'E') => 'grade-D',
                                default => 'grade-F'
                            };

                            // Position medal colors
                            $posVal = $score->position ?? null;
                            $posClass = is_numeric($posVal) ? match((int)$posVal) { 1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => '' } : '';

                            $posTotalVal = $score->position_total ?? null;
                            $posTotalClass = is_numeric($posTotalVal) ? match((int)$posTotalVal) { 1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => '' } : '';

                            $armPosVal = $score->arm_position ?? null;
                            $armPosClass = is_numeric($armPosVal) ? match((int)$armPosVal) { 1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => '' } : '';

                            $armPosCumVal = $score->arm_position_cum ?? null;
                            $armPosCumClass = is_numeric($armPosCumVal) ? match((int)$armPosCumVal) { 1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => '' } : '';
                        @endphp
                        <tr>
                            @if(in_array('sn', $columnsToShow))
                                <td>{{ $index + 1 }}</td>
                            @endif
                            @if(in_array('name', $columnsToShow))
                                <td class="subject-name">{{ $score->subject_name ?? 'N/A' }}</td>
                            @endif
                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    @php
                                        $assScore = 0;
                                        if (isset($score->assessment_scores)) {
                                            $found = $score->assessment_scores->firstWhere('assessment_id', $assessment->id);
                                            $assScore = $found ? $found->score : 0;
                                        }
                                        $isLow = $assScore < ($assessment->max_score * 0.5);
                                    @endphp
                                    <td @if($isLow && is_numeric($assScore)) class="highlight-red" @endif>
                                        {{ $assScore ? number_format($assScore, 0) : '-' }}
                                    </td>
                                @endif
                            @endforeach
                            @if(in_array('total', $columnsToShow))
                                <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>
                                    {{ isset($score->total) ? number_format($score->total, 1) : '-' }}
                                </td>
                            @endif
                            @if(in_array('cum', $columnsToShow))
                                <td>{{ isset($score->cum) ? number_format($score->cum, 1) : '-' }}</td>
                            @endif
                            @if(in_array('grade', $columnsToShow))
                                <td class="{{ $gradeClass }}">{{ $gradeRaw }}</td>
                            @endif
                            @if(in_array('position', $columnsToShow))
                                <td class="{{ $posClass }}">{{ formatOrdinal($posVal) }}</td>
                            @endif
                            @if(in_array('position_total', $columnsToShow))
                                <td class="{{ $posTotalClass }}">{{ formatOrdinal($posTotalVal) }}</td>
                            @endif
                            @if(in_array('arm_position', $columnsToShow))
                                <td class="{{ $armPosClass }}">{{ formatOrdinal($armPosVal) }}</td>
                            @endif
                            @if(in_array('arm_position_cum', $columnsToShow))
                                <td class="{{ $armPosCumClass }}">{{ formatOrdinal($armPosCumVal) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $totalColumns }}" style="text-align:center;">No assessment records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TOTALS SUMMARY -->
        <div class="totals-summary">
            TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }} &nbsp;|&nbsp;
            TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }} &nbsp;|&nbsp;
            PERCENTAGE: {{ number_format($totals['percentage'] ?? 0, 1) }}%
        </div>

        <!-- REMARKS -->
        <table class="remarks-table">
            <tbody>
                <tr>
                    <td width="50%">
                        <div class="remark-title">Class Teacher's Remark</div>
                        <div style="font-size:8.5px; margin-top:5px;">
                            {{ $scores->first()->remark ?? 'Performed satisfactorily. Keep improving.' }}
                        </div>
                    </td>
                    <td width="50%">
                        <div class="remark-title">Principal's Remark</div>
                        <div style="font-size:8.5px; margin-top:5px;">
                            {{ $scores->first()->remark ?? 'Approved. Continue with good work.' }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- FOOTER -->
        <div class="footer">
            <div class="footer-content">
                <div>
                    <strong>Issued:</strong>
                    <span class="sign-line">{{ now()->format('jS F, Y') }}</span>
                    <strong style="margin-left:20px;">Received by:</strong>
                    <span class="sign-line">.......................................</span>
                </div>
                <div>
                    <strong>Next Term Begins:</strong>
                    @php
                        $nextTermBegins = $schoolInfo->date_next_term_begins ?? null;
                        $formattedNextTermBegins = $nextTermBegins ? \Carbon\Carbon::parse($nextTermBegins)->format('jS F, Y') : '........................';
                    @endphp
                    <span class="sign-line">{{ $formattedNextTermBegins }}</span>
                </div>
            </div>
            <div class="powered-by">Powered by School Management System</div>
        </div>
    </div>
</body>
</html>
