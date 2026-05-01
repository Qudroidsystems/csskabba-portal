<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Terminal Report - Claret Secondary School Kabba</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5px;
            line-height: 1.3;
            color: #000;
            background: #f5f5f5;
            padding: 8mm 0;
            text-align: center;
        }

        /* SCHOOL NAME HEADER */
        .school-name-header {
            width: 100%;
            background: #111827;
            color: white;
            padding: 9px 10px 5px 10px;
            border: 3px double #000000;
            border-bottom: 1px solid #1e40af;
        }

        .school-name-header .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 19.5px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .school-name-header .motto {
            font-size: 9.8px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: 0.95;
            margin-top: 3px;
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

        /* MAIN REPORT CARD */
        .student-section {
            width: 190mm;
            page-break-after: always;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto 15px auto;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            padding: 8px 10px 6px 10px;
        }

        .school-logo, .photo-frame {
            width: 74px;
            height: 88px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: white;
            padding: 3px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .school-logo img, .photo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .middle-info {
            font-size: 10.4px;           /* Bigger as requested */
            line-height: 1.75;
            padding: 0 15px;
            vertical-align: middle;
        }

        .middle-info strong {
            color: #1e40af;
            font-weight: 700;
        }

        .header-divider { height: 2px; background: #1e40af; width: 100%; }
        .header-divider2 { height: 1px; background: #64748b; width: 100%; margin: 2px 0; }

        .report-title {
            background: #111827;
            color: white;
            padding: 6px 8px;
            font-size: 11.5px;
            font-weight: 700;
            text-align: center;
        }

        /* STUDENT INFO BAR */
        .student-info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 6px;
            padding: 7px 12px;
            margin: 8px 10px;
            font-size: 8.8px;
        }

        .info-table td { padding: 2px 6px; }

        .info-bar-label {
            color: #1e40af;
            font-weight: 700;
            font-size: 8.2px;
            white-space: nowrap;
        }

        .info-bar-value {
            font-weight: 900;
            padding-left: 4px;
        }

        /* ACADEMIC RESULT TABLE - FULL WIDTH */
        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            font-size: 7.8px;
            margin: 8px 10px;
        }

        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 4px 2px;
            font-size: 6.9px;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 3px 2px;
            text-align: center;
            font-size: 7.6px;
            background: white;
            font-weight: 600;
            height: 17px;
            line-height: 17px;
        }

        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 700;
            padding-left: 6px;
        }

        .highlight-red { color: #dc2626; font-weight: 900; }

        /* Column Widths */
        .col-sn { width: 28px; }
        .col-admissionno { width: 78px; }
        .col-name { width: 165px; }
        .col-assessment { width: 42px; }
        .col-total { width: 48px; }
        .col-bf { width: 38px; }
        .col-cum { width: 44px; }
        .col-grade { width: 38px; }
        .col-position { width: 38px; }
        .col-class-average { width: 42px; }

        .totals-summary {
            width: 98%;
            background: #0d1a3d;
            color: #ffffff;
            font-weight: 900;
            font-size: 7.9px;
            padding: 6px 10px;
            border: 2px solid #000000;
            border-top: none;
            text-align: center;
            margin: 8px auto;
        }

        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin: 8px 10px 4px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            background: white;
            vertical-align: top;
            font-size: 8.5px;
        }

        .remarks-table .h6 {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 9px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
        }

        /* FOOTER */
        .footer-section {
            background: #f1f5f9;
            padding: 9px 12px 6px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            margin: 0 10px 8px;
            font-size: 8.6px;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            line-height: 1.4;
        }

        .text-dot-space2 {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 120px;
            font-weight: bold;
            margin: 0 4px;
        }

        .powered-by {
            font-size: 8px;
            margin-top: 4px;
            color: #64748b;
        }

        /* APPROVED STAMP */
        .approved-stamp {
            position: absolute;
            bottom: 95px;
            right: 48px;
            width: 135px;
            height: 135px;
            opacity: 0.85;
            z-index: 10;
            pointer-events: none;
            transform: rotate(-8deg);
        }

        /* QR CODE */
        .qr-code-container {
            position: absolute;
            bottom: 45px;
            right: 35px;
            text-align: center;
            z-index: 11;
            background: rgba(255,255,255,0.9);
            padding: 5px 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .qr-code-container img {
            width: 82px;
            height: 82px;
        }

        .qr-label {
            font-size: 6.8px;
            color: #333;
            margin-top: 2px;
            font-weight: 600;
        }

        /* GRADE STYLES */
        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }

        .position-1 { background: gold; color: black; font-weight: 900; }
        .position-2 { background: silver; color: black; font-weight: 900; }
        .position-3 { background: #cd7f32; color: white; font-weight: 900; }

        @media print {
            body { background: white; padding: 0; }
            .student-section { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="watermark-text">ORIGINAL COPY</div>

    @php
        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns = ['sn', 'admission_no', 'name', 'total', 'bf', 'cum', 'grade', 'position', 'class_average'];
        $columnsToShow = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;
        $baseVisibleCount = 0;
        if (in_array('sn', $columnsToShow)) $baseVisibleCount++;
        if (in_array('admission_no', $columnsToShow)) $baseVisibleCount++;
        if (in_array('name', $columnsToShow)) $baseVisibleCount++;
    @endphp

    @foreach ($allStudentData as $index => $studentData)
        @php
            $schoolInfo = $studentData['schoolInfo'] ?? null;
            $student = $studentData['students'] && $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
            $assessments = $studentData['assessments'] ?? collect();
            $totals = $studentData['totals_summary'] ?? [];

            // QR Code Data
            $admNo = $student->admissionNo ?? 'N/A';
            $fullName = trim(strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
            $classVal = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? ''));
            $session = $metadata['session'] ?? '2025/2026';
            $term = $metadata['term'] ?? 'SECOND TERM';

            $qrData = "Name: {$fullName}\nAdm No: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}\nSchool: Claret Secondary School Kabba";

            $qrCodeBase64 = base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(280)
                    ->errorCorrection('H')
                    ->generate($qrData)
            );
        @endphp

        <div class="student-section">

            <!-- APPROVED STAMP -->
            <img src="{{ asset('stamp.jpeg') }}" alt="Approved Stamp" class="approved-stamp">

            <!-- QR CODE -->
            <div class="qr-code-container">
                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
                <div class="qr-label">Scan for Verification</div>
            </div>

            <!-- SCHOOL NAME HEADER -->
            <div class="school-name-header">
                <div class="school-full-name">CLARET SECONDARY SCHOOL KABBA</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
            </div>

            <!-- HEADER: Logo + Info + Photo -->
            <table class="header-table">
                <tr>
                    <td width="20%" style="text-align:center;">
                        <div class="school-logo">
                            @php
                                $logoSrc = $studentData['school_logo_base64'] ??
                                    'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="72" height="85" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/><circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/><rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/><text x="50" y="95" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">CLARET</text></svg>');
                            @endphp
                            <img src="{{ $logoSrc }}" alt="School Logo">
                        </div>
                    </td>

                    <td width="55%" class="middle-info">
                        <strong>Address:</strong> {{ $schoolInfo->school_address ?? 'No. 1, Claret Avenue, Kabba, Kogi State' }}<br>
                        <strong>Phone:</strong> {{ $schoolInfo->school_phone ?? '08136663185' }}<br>
                        <strong>Email:</strong> {{ $schoolInfo->school_email ?? '—' }}<br>
                        <strong>Website:</strong> {{ $schoolInfo->school_website ?? '—' }}
                    </td>

                    <td width="25%" style="text-align:right; padding-right: 18px; vertical-align: top;">
                        @if(in_array('picture', $columnsToShow))
                        <div class="photo-frame">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='85' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3Ctext x='50' y='95' text-anchor='middle' fill='%23475569' font-size='8'%3EPHOTO%3C/text%3E%3C/svg%3E" alt="Default Photo">
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="header-divider"></div>
            <div class="header-divider2"></div>

            <!-- REPORT TITLE -->
            <div class="report-title">
                {{ strtoupper($term) }} {{ strtoupper($session) }} ACADEMIC SESSION TERMINAL PROGRESS REPORT
            </div>

            <!-- STUDENT INFO BAR -->
            @if ($studentData['students'] && $studentData['students']->isNotEmpty())
                @php
                    $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
                    $fullNameDisplay = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
                    $admNoDisplay = $student->admissionNo ?? '—';
                    $classValDisplay = ($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
                    $schoolOpened = $schoolInfo->date_school_opened ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y') : '—';
                    $numInClass = $studentData['numberOfStudents'] ?? '—';
                @endphp

                <div class="student-info-bar">
                    <table class="info-table">
                        <tr>
                            <td><span class="info-bar-label">NAME:</span><span class="info-bar-value">{{ $fullNameDisplay }}</span></td>
                            <td><span class="info-bar-label">SESSION:</span><span class="info-bar-value">{{ $session }}</span></td>
                            <td><span class="info-bar-label">TERM:</span><span class="info-bar-value">{{ $term }}</span></td>
                            <td><span class="info-bar-label">CLASS:</span><span class="info-bar-value">{{ $classValDisplay }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-bar-label">ADM NO:</span><span class="info-bar-value">{{ $admNoDisplay }}</span></td>
                            <td><span class="info-bar-label">SCHOOL OPENED:</span><span class="info-bar-value">{{ $schoolOpened }}</span></td>
                            <td><span class="info-bar-label">NO. IN CLASS:</span><span class="info-bar-value">{{ $numInClass }}</span></td>
                            @if(in_array('gender', $columnsToShow))
                                <td><span class="info-bar-label">SEX:</span><span class="info-bar-value">{{ $student->gender ?? '—' }}</span></td>
                            @endif
                        </tr>
                    </table>
                </div>
            @endif

            <!-- ACADEMIC RESULT TABLE -->
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
                            @if(in_array('position', $columnsToShow)) <th class="col-position">Pos</th> @endif
                            @if(in_array('class_average', $columnsToShow)) <th class="col-class-average">Av</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($studentData['scores'] as $scoreIndex => $score)
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

                            @if(in_array('total', $columnsToShow))
                                <td @if($score->total < 50) class="highlight-red" @endif>
                                    {{ $score->total ? number_format($score->total, 1) : '-' }}
                                </td>
                            @endif
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
                                        default => 'grade-F'
                                    };
                                @endphp
                                <td class="{{ $gradeClass }}">{{ $gradeRaw }}</td>
                            @endif

                            @if(in_array('position', $columnsToShow))
                                @php
                                    $posVal = $score->position ?? '-';
                                    $posClass = is_numeric($posVal) ? match((int)$posVal) {
                                        1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => ''
                                    } : '';
                                @endphp
                                <td class="{{ $posClass }}">{{ $posVal }}</td>
                            @endif

                            @if(in_array('class_average', $columnsToShow))
                                <td>{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="20" style="text-align:center; padding:10px;">No scores available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TOTALS SUMMARY -->
            <div class="totals-summary">
                TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }} &nbsp;&nbsp;|&nbsp;&nbsp;
                TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }} &nbsp;&nbsp;|&nbsp;&nbsp;
                % OBTAINED: {{ $totals['percentage'] ?? 0 }}%
            </div>

            <!-- REMARKS -->
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

            <!-- FOOTER -->
            <div class="footer-section">
                <div class="footer-content">
                    <div>
                        <strong>Issued:</strong>
                        <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span>
                        <strong style="margin-left:25px;">Collected by:</strong>
                        <span class="text-dot-space2">.......................................</span>
                    </div>
                    <div>
                        <strong>Next Term Begins:</strong>
                        <span class="text-dot-space2">
                            @php
                                $nextTerm = $schoolInfo->date_next_term_begins ?? null;
                                echo $nextTerm ? \Carbon\Carbon::parse($nextTerm)->format('jS F, Y') : '........................';
                            @endphp
                        </span>
                    </div>
                </div>
                <div class="powered-by">Powered by Qudroid Systems</div>
            </div>
        </div>
    @endforeach
</body>
</html>
