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
            padding: 10px 0;
        }

        /* MAIN CARD - ENSURES PAGE BREAKS */
        .student-section {
            width: 190mm;
            min-height: 277mm; /* A4 height in mm */
            page-break-after: always;
            page-break-inside: avoid;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto 15px auto;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Pushes footer to bottom */
        }

        /* PAGE BREAK PROTECTION FOR INTERNAL ELEMENTS */
        .student-section > * {
            page-break-inside: avoid;
        }

        /* SCHOOL NAME HEADER - COMPACT */
        .school-name-header {
            width: 100%;
            background: #111827;
            color: white;
            padding: 6px 10px 4px 10px;
            border-bottom: 1px solid #1e40af;
            text-align: center;
        }
        .school-name-header .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .school-name-header .motto {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 2px;
        }

        /* HEADER TABLE (LOGO, INFO, PHOTO) - MORE COMPACT */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            padding: 5px 10px;
        }
        .school-logo, .photo-frame {
            width: 70px;
            height: 80px;
            border: 2px solid #47b492;
            border-radius: 5px;
            background: white;
            padding: 2px;
            display: block;
            text-align: center;
        }
        .school-logo img, .photo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .middle-info {
            font-size: 9.5px;
            font-weight: 700;
            line-height: 1.4;
            padding: 0 10px;
            vertical-align: middle;
        }
        .middle-info strong {
            color: #1e40af;
            font-weight: 900;
        }
        .header-divider { height: 2px; background: #1e40af; width: 100%; }
        .header-divider2 { height: 1px; background: #64748b; width: 100%; margin: 1px 0; }

        /* REPORT TITLE */
        .report-title {
            background: #111827;
            color: white;
            padding: 4px 6px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        /* STUDENT INFO BAR - COMPACT AND CLEAN */
        .student-info-bar {
            background: #f8fafc;
            border: 1px solid #2aa886;
            border-radius: 4px;
            padding: 4px 8px;
            margin: 6px 10px;
            font-size: 9px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 4px;
            text-align: center;
        }
        .info-bar-label {
            color: #1e40af;
            font-weight: 900;
            font-size: 8.5px;
        }
        .info-bar-value {
            font-weight: 900;
            font-size: 9px;
            padding-left: 3px;
        }

        /* RESULT TABLE */
        .result-table {
            padding: 0 10px;
            margin: 4px 0;
        }
        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            font-size: 7.8px;
        }
        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 4px 2px;
            font-size: 6.8px;
        }
        .result-table tbody td {
            border: 1px solid #000000;
            padding: 3px 3px;
            text-align: center;
            font-size: 7.8px;
            background: white;
            font-weight: 700;
        }
        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 800;
            font-size: 7.8px;
            padding-left: 6px;
        }
        .highlight-red { color: #dc2626; font-weight: 900; }

        /* COLUMN WIDTHS */
        .col-sn { width: 25px; }
        .col-admissionno { width: 75px; }
        .col-name { width: 160px; }
        .col-assessment { width: 40px; }
        .col-total { width: 45px; }
        .col-bf { width: 35px; }
        .col-cum { width: 40px; }
        .col-grade { width: 35px; }
        .col-position { width: 35px; }
        .col-class-average { width: 40px; }

        /* TOTALS SUMMARY */
        .totals-summary {
            width: calc(100% - 20px);
            background: #0d1a3d;
            color: #ffffff;
            font-weight: 900;
            font-size: 8px;
            padding: 5px 10px;
            border: 2px solid #000000;
            border-top: none;
            text-align: center;
            margin: 0 10px 6px 10px;
        }

        /* REMARKS TABLE */
        .remarks-table {
            width: calc(100% - 20px);
            border: 2px solid #000000;
            border-collapse: collapse;
            margin: 4px 10px;
        }
        .remarks-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            background: white;
            vertical-align: top;
            font-size: 8.5px;
        }
        .remarks-table .h6 {
            font-weight: 800;
            margin-bottom: 3px;
            font-size: 9px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
        }

        /* BOTTOM STRIP - FLEXIBLE AND STABLE */
        .bottom-strip {
            width: 100%;
            border-top: 1px solid #cbd5e1;
            background: #f1f5f9;
            margin-top: 5px;
            padding: 5px 0;
        }
        .bottom-strip table {
            width: 100%;
            border-collapse: collapse;
        }
        .bottom-strip td {
            padding: 5px 10px;
            vertical-align: middle;
        }
        .cell-qr {
            width: 90px;
            text-align: center;
        }
        .cell-footer {
            text-align: center;
            font-size: 8.5px;
        }
        .cell-stamp {
            width: 110px;
            text-align: center;
        }
        .cell-qr img {
            width: 65px;
            height: 65px;
            display: block;
            margin: 0 auto 2px;
        }
        .qr-label {
            font-size: 6px;
            color: #333;
            font-weight: 600;
        }
        .cell-stamp img {
            width: 90px;
            height: 90px;
            transform: rotate(-8deg);
            display: block;
            margin: 0 auto;
        }
        .text-dot-space2 {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 100px;
            font-weight: bold;
            margin: 0 4px;
        }
        .powered-by {
            font-size: 7.5px;
            margin-top: 3px;
            color: #64748b;
        }

        /* GRADE COLORS */
        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }

        .position-1 { background: gold; color: black; font-weight: 900; }
        .position-2 { background: silver; color: black; font-weight: 900; }
        .position-3 { background: #cd7f32; color: white; font-weight: 900; }

        /* ADDRESS STYLING - FIXED INDENT */
        .address-line {
            display: block;
            margin-left: 55px; /* Indent the second line to align with the text after "Address:" */
            margin-top: -1.2em; /* Pull it back up to the first line */
        }
        .middle-info strong:first-child {
            display: inline-block;
            width: 55px; /* Fixed width for labels to align content */
        }
        /* Adjust other labels to match width */
        .middle-info strong:nth-child(3), .middle-info strong:nth-child(5), .middle-info strong:nth-child(7) {
            display: inline-block;
            width: 55px;
        }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .student-section { box-shadow: none; margin: 0 auto; page-break-after: always; }
        }
    </style>
</head>
<body>

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

            $admNo = $student->admissionNo ?? 'N/A';
            $fullName = trim(strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
            $classVal = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? ''));
            $session = $metadata['session'] ?? '2025/2026';
            $term = $metadata['term'] ?? 'SECOND TERM';

            $qrData = "Name: {$fullName}\nAdm No: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}\nSchool: Claret Secondary School Kabba";

            $qrCodeBase64 = base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(240)
                    ->errorCorrection('H')
                    ->generate($qrData)
            );
        @endphp

        <div class="student-section">

            <!-- COMPACT SCHOOL NAME HEADER -->
            <div class="school-name-header">
                <div class="school-full-name">{{ $schoolInfo->school_name ?? 'CLARET SECONDARY SCHOOL KABBA' }}</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
            </div>

            <!-- COMPACT HEADER TABLE -->
            <table class="header-table">
                <tr>
                    <td width="18%" style="text-align:center;">
                        <div class="school-logo">
                            @php
                                $logoSrc = $studentData['school_logo_base64'] ??
                                    'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="70" height="80" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/><circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/><rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/><text x="50" y="95" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">CLARET</text></svg>');
                            @endphp
                            <img src="{{ $logoSrc }}" alt="School Logo">
                        </div>
                    </td>

                    <td width="60%" class="middle-info">
                        <strong>Address:</strong> No. 1, Claret Avenue, Iludun Quarters, Olle Road,<br>
                        <span style="margin-left: 55px;">Kabba, Kogi State, Nigeria.</span><br>
                        <strong>Phone:</strong> {{ $schoolInfo->school_phone ?? '08039257337' }}<br>
                        <strong>Email:</strong> {{ $schoolInfo->school_email ?? 'claretsecschools@yahoo.com' }}<br>
                        <strong>Website:</strong> {{ $schoolInfo->school_website ?? 'http://csskabba.ng' }}
                    </td>

                    <td width="22%" style="text-align:right; vertical-align: top; padding-top: 2px;">
                        @if(in_array('picture', $columnsToShow))
                        <div class="photo-frame" style="margin-left: auto; margin-right: 0;">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='70' height='80' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3Ctext x='50' y='95' text-anchor='middle' fill='%23475569' font-size='8'%3EPHOTO%3C/text%3E%3C/svg%3E" alt="Default Photo">
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

            <!-- STUDENT INFO BAR - COMPACT -->
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

            <!-- RESULT TABLE -->
            <div class="result-table">
                <table>
                    <thead>
                        <tr>
                            @if(in_array('sn', $columnsToShow)) <th class="col-sn">S/N</th> @endif
                            @if(in_array('admission_no', $columnsToShow)) <th class="col-admissionno">Adm No</th> @endif
                            @if(in_array('name', $columnsToShow)) <th class="col-name">Subject</th> @endif
                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    <th class="col-assessment">{{ $assessment->name }}<br><span style="font-size:5px;">({{ $assessment->max_score }})</span></th>
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

            <!-- TOTALS -->
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

            <!-- STABLE BOTTOM STRIP -->
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
                            <div style="margin-top:3px;">
                                <strong>Next Term Begins:</strong>
                                <span class="text-dot-space2">
                                    @php
                                        $nextTerm = $schoolInfo->date_next_term_begins ?? null;
                                        echo $nextTerm ? \Carbon\Carbon::parse($nextTerm)->format('jS F, Y') : '........................';
                                    @endphp
                                </span>
                            </div>
                            <div class="powered-by">Powered by Qudroid Systems</div>
                         </td>
                        <td class="cell-stamp">
                            <img src="{{ asset('stamp.jpeg') }}" alt="Approved Stamp">
                         </td>
                     </tr>
                 </table>
            </div>
        </div>
    @endforeach
</body>
</html>
