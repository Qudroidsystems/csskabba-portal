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
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #f5f5f5;
            padding: 15mm 0;
            text-align: center;
            position: relative;
        }

        /* WATERMARK - "ORIGINAL COPY" */
        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.06);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 8px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
        }

        .student-section {
            width: 190mm;
            max-height: 287mm;
            page-break-after: always;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto 20px auto;
            padding: 12px;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1;
            background-color: #fff;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        /* School logo container - larger and properly aligned */
        .school-logo {
            width: 100px;
            height: 110px;
            border: 2px solid #47b492;
            border-radius: 8px;
            background: white;
            padding: 4px;
            overflow: hidden;
            text-align: center;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        /* Student photo frame - fixed to keep image inside border */
        .photo-frame {
            border: 2px solid #47b492;
            border-radius: 8px;
            background: white;
            padding: 4px;
            width: 100px;
            height: 110px;
            margin: 0 auto;
            text-align: center;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-frame img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .header-img {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background: #1e40af;
            margin: 4px 0;
        }

        .header-divider2 {
            width: 100%;
            height: 1px;
            background: #64748b;
            margin: 2px 0;
        }

        .report-title {
            background: #111827;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            margin: 8px 0;
            letter-spacing: 0.5px;
        }

        .header {
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        /* Student info bar - clean with | separators */
        .student-info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
        }
        .info-line {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 12px 20px;
            margin-bottom: 6px;
        }
        .info-line:last-child {
            margin-bottom: 0;
        }
        .info-bar-item {
            white-space: nowrap;
        }
        .info-bar-label {
            color: #1e40af;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 700;
        }
        .info-bar-value {
            color: #000;
            margin-left: 4px;
            font-size: 11px;
            font-weight: 900;
        }
        .separator {
            color: #94a3b8;
            font-weight: 400;
            margin: 0 4px;
        }

        .result-table {
            margin-bottom: 8px;
        }

        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 6px 3px;
            text-align: center;
            font-size: 8px;
        }

        .result-table thead th.assessment-header {
            width: 25px;
            font-size: 7px;
        }

        .result-table tbody tr {
            font-weight: 800;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 4px 3px;
            text-align: center;
            font-size: 10px;
            background: white;
            font-weight: 900;
        }

        .result-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 600;
        }

        .highlight-red {
            color: #dc2626;
            font-weight: 900;
        }

        .totals-row td {
            background: #0d1a3d !important;
            color: #ffffff !important;
            border: 1px solid #000000 !important;
            font-weight: 900 !important;
            font-size: 9px !important;
            text-align: center;
            padding: 4px 3px;
        }

        .totals-fraction {
            display: inline-block;
            text-align: center;
            font-size: 8px;
            font-weight: 900;
            line-height: 1.1;
        }

        .totals-fraction .t-num {
            display: block;
            border-bottom: 1.5px solid #ffffff;
            padding: 0 4px 1px 4px;
        }

        .totals-fraction .t-den {
            display: block;
            padding: 1px 4px 0 4px;
        }

        /* White background, black text for totals summary row */
        .totals-summary-row td {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            font-weight: 800 !important;
            font-size: 10px !important;
            text-align: center;
            padding: 6px 4px;
            letter-spacing: 0.3px;
        }

        /* Remarks Table - two columns: Class Teacher & Principal */
        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 10px;
            background: white;
            vertical-align: top;
            width: 50%;
        }

        .remarks-table .h6 {
            color: #050505;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
            padding-right: 8px;
        }

        .footer-section {
            background: #f1f5f9;
            padding: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 6px;
        }

        .footer-layout-table {
            width: 100%;
        }

        .footer-layout-table td {
            padding: 3px;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 900;
        }

        .text-primary {
            color: #02175e;
        }

        .student-section-inner {
            width: 100%;
        }

        .powered-by {
            font-size: 12px;
            color: #000000;
            font-weight: 700;
            margin-top: 6px;
        }

        .text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-width: 120px;
            font-weight: bold;
        }

        .col-sn { width: 30px; }
        .col-admissionno { width: 80px; }
        .col-name { width: 150px; }
        .col-assessment { width: 40px; }
        .col-total { width: 50px; }
        .col-bf { width: 40px; }
        .col-cum { width: 50px; }
        .col-grade { width: 40px; }
        .col-position { width: 60px; }
        .col-class-average { width: 60px; }
        .col-num-subjects { width: 60px; }
        .col-total-grade-points { width: 60px; }
        .col-gpa { width: 50px; }
        .col-calculated-gpa { width: 60px; }
        .col-gpa-grade { width: 60px; }
        .col-cgpa { width: 50px; }
        .col-compulsory { width: 60px; }
        .col-vetted { width: 80px; }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .student-section {
                width: 190mm;
                max-height: 287mm;
                margin: 0 auto;
                padding: 12px;
                page-break-after: always;
                box-shadow: none;
            }
            .watermark-text {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-25deg);
                font-size: 70px;
                color: rgba(0, 0, 0, 0.1);
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
<!-- Beautiful Watermark "ORIGINAL COPY" -->
<div class="watermark-text">ORIGINAL COPY</div>

@php
    $selectedColumns = $metadata['selected_columns'] ?? [];
    $defaultColumns  = ['sn', 'admission_no', 'name', 'total', 'bf', 'cum', 'grade', 'position', 'class_average', 'gpa', 'cgpa', 'vetted_status'];
    $columnsToShow   = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;

    $baseVisibleCount = 0;
    if (in_array('sn', $columnsToShow))           $baseVisibleCount++;
    if (in_array('admission_no', $columnsToShow)) $baseVisibleCount++;
    if (in_array('name', $columnsToShow))          $baseVisibleCount++;

    $assessmentColumnsCount = 0;
@endphp

@foreach ($allStudentData as $index => $studentData)
    @php
        $schoolInfo  = $studentData['schoolInfo'] ?? null;
        $student     = $studentData['students'] && $studentData['students']->isNotEmpty()
                        ? $studentData['students']->first()
                        : null;
        $assessments = $studentData['assessments'] ?? collect();
        $gpaData     = $studentData['gpa_data'] ?? [];
        $totals      = $studentData['totals_summary'] ?? [];

        $assessmentColumnsCount = 0;
        foreach ($assessments as $assessment) {
            if (in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow)) {
                $assessmentColumnsCount++;
            }
        }

        $currentVisibleColumnCount = $baseVisibleCount + $assessmentColumnsCount;
        $otherScoreCols = ['total', 'bf', 'cum', 'grade', 'position', 'class_average',
                           'num_subjects', 'total_grade_points', 'gpa', 'calculated_gpa',
                           'gpa_grade', 'cgpa', 'compulsory_flag', 'vetted_status'];
        foreach ($otherScoreCols as $col) {
            if (in_array($col, $columnsToShow)) $currentVisibleColumnCount++;
        }

        $totalLabelColspan = $baseVisibleCount + $assessmentColumnsCount;
        $gpaSummaryLabelColspan = $baseVisibleCount + $assessmentColumnsCount;
        if (in_array('total', $columnsToShow))         $gpaSummaryLabelColspan++;
        if (in_array('bf', $columnsToShow))            $gpaSummaryLabelColspan++;
        if (in_array('cum', $columnsToShow))           $gpaSummaryLabelColspan++;
        if (in_array('grade', $columnsToShow))         $gpaSummaryLabelColspan++;
        if (in_array('position', $columnsToShow))      $gpaSummaryLabelColspan++;
        if (in_array('class_average', $columnsToShow)) $gpaSummaryLabelColspan++;
    @endphp

    <div class="student-section">
        <div class="student-section-inner">
            <!-- HEADER -->
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td width="25%">
                            <div class="school-logo">
                                @php
                                    $hasLogo  = false;
                                    $logoSrc  = '';
                                    if (!empty($studentData['school_logo_base64'])) {
                                        $logoSrc = $studentData['school_logo_base64'];
                                        $hasLogo = true;
                                    }
                                    if (!$hasLogo) {
                                        $schoolName = $schoolInfo->school_name ?? 'Claret Secondary School Kabba';
                                        $logoSrc    = 'data:image/svg+xml;base64,' . base64_encode('
                                            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                                                <rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/>
                                                <circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/>
                                                <rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/>
                                                <text x="50" y="95" text-anchor="middle" fill="#1e40af" font-family="Arial" font-size="8" font-weight="bold">
                                                    CLARET
                                                </text>
                                            </svg>
                                        ');
                                    }
                                @endphp
                                <img class="header-img" src="{{ $logoSrc }}" alt="School Logo">
                            </div>
                        </td>
                        <td width="50%" style="padding-left: 10px; vertical-align: middle;">
                            <div style="font-family: 'Arial Black', 'Helvetica Bold', sans-serif; font-weight: 900; color: #000; line-height: 1.2; text-align: left;">
                                <div style="font-size: 22px; letter-spacing: 1px; margin-bottom: 6px; color: #1e293b; white-space: nowrap;">
                                    CLARET SECONDARY SCHOOL KABBA
                                </div>
                                <div style="font-size: 10px;">
                                    <strong style="color: #1e40af;">Motto:</strong>
                                    <span style="margin-left: 6px;">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</span>
                                </div>
                                <div style="font-size: 10px;">
                                    <strong style="color: #1e40af;">Address:</strong>
                                    <span style="margin-left: 6px;">{{ $schoolInfo->school_address ?? 'No. 1, Claret Avenue, Iludun Quarters, Olle Road, Kabba, Kogi State, Nigeria' }}</span>
                                </div>
                                <div style="font-size: 10px;">
                                    <strong style="color: #1e40af;">Phone:</strong>
                                    <span style="margin-left: 6px;">{{ $schoolInfo->school_phone ?? '08136663185' }}</span>
                                </div>
                            </div>
                        </td>
                        <td width="25%">
                            @if(in_array('picture', $columnsToShow))
                            <div class="photo-frame">
                                @if(!empty($studentData['student_image_base64']))
                                    <img src="{{ $studentData['student_image_base64'] }}" alt="{{ $student->fname ?? 'Student' }}'s picture">
                                @else
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3Ctext x='50' y='95' text-anchor='middle' fill='%23475569' font-size='8'%3EPHOTO%3C/text%3E%3C/svg%3E" alt="Default Photo">
                                @endif
                            </div>
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="header-divider"></div>
                <div class="header-divider2"></div>
                <div class="report-title">
                    {{ strtoupper($metadata['term']) }} {{ strtoupper($metadata['session']) }} ACADEMIC SESSION TERMINAL PROGRESS REPORT
                </div>
            </div>

            <!-- STUDENT INFO BAR -->
            @if ($studentData['students'] && $studentData['students']->isNotEmpty())
                @php
                    $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()
                                ? $studentData['studentpp']->first()
                                : null;
                    $fullName = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
                    $admNo = $student->admissionNo ?? '—';
                    $sessionVal = $studentData['schoolsession']->session ?? '—';
                    $termVal = $studentData['schoolterm']->term ?? '—';
                    $classVal = ($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
                    $schoolOpened = $schoolInfo->date_school_opened ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y') : '—';
                    $numInClass = $studentData['numberOfStudents'] ?? '—';
                @endphp
                <div class="student-info-bar">
                    <div class="info-line">
                        <span class="info-bar-item"><span class="info-bar-label">NAME:</span><span class="info-bar-value">{{ $fullName }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">SESSION:</span><span class="info-bar-value">{{ $sessionVal }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">TERM:</span><span class="info-bar-value">{{ $termVal }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">CLASS:</span><span class="info-bar-value">{{ $classVal }}</span></span>
                    </div>
                    <div class="info-line">
                        <span class="info-bar-item"><span class="info-bar-label">ADM NO:</span><span class="info-bar-value">{{ $admNo }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">SCHOOL OPENED:</span><span class="info-bar-value">{{ $schoolOpened }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">NO. IN CLASS:</span><span class="info-bar-value">{{ $numInClass }}</span></span>
                        @if(in_array('gender', $columnsToShow))
                            <span class="separator">|</span>
                            <span class="info-bar-item"><span class="info-bar-label">SEX:</span><span class="info-bar-value">{{ $student->gender ?? '—' }}</span></span>
                        @endif
                        @if(in_array('dob', $columnsToShow))
                            <span class="separator">|</span>
                            <span class="info-bar-item"><span class="info-bar-label">DOB:</span><span class="info-bar-value">{{ $student->dateofbirth ? \Carbon\Carbon::parse($student->dateofbirth)->format('jS M, Y') : '—' }}</span></span>
                        @endif
                    </div>
                </div>
            @else
                <div class="student-info-bar">
                    <div class="info-line"><span class="info-bar-item">No student data available.</span></div>
                </div>
            @endif

            <!-- RESULTS TABLE -->
            <div class="result-table">
                <table>
                    <thead>
                        <tr>
                            @if(in_array('sn', $columnsToShow)) <th class="col-sn">S/N</th> @endif
                            @if(in_array('admission_no', $columnsToShow)) <th class="col-admissionno">Adm No</th> @endif
                            @if(in_array('name', $columnsToShow)) <th class="col-name">Subject</th> @endif
                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                <th class="col-assessment assessment-header">{{ $assessment->name }}<br><small>({{ $assessment->max_score }})</small></th>
                                @endif
                            @endforeach
                            @if(in_array('total', $columnsToShow)) <th class="col-total">Total</th> @endif
                            @if(in_array('bf', $columnsToShow)) <th class="col-bf">BF</th> @endif
                            @if(in_array('cum', $columnsToShow)) <th class="col-cum">Cum</th> @endif
                            @if(in_array('grade', $columnsToShow)) <th class="col-grade">Grade</th> @endif
                            @if(in_array('position', $columnsToShow)) <th class="col-position">Pos</th> @endif
                            @if(in_array('class_average', $columnsToShow)) <th class="col-class-average">Avg</th> @endif
                            @if(in_array('num_subjects', $columnsToShow)) <th class="col-num-subjects">Subj</th> @endif
                            @if(in_array('total_grade_points', $columnsToShow)) <th class="col-total-grade-points">TGP</th> @endif
                            @if(in_array('gpa', $columnsToShow)) <th class="col-gpa">GPA</th> @endif
                            @if(in_array('calculated_gpa', $columnsToShow)) <th class="col-calculated-gpa">Calc</th> @endif
                            @if(in_array('gpa_grade', $columnsToShow)) <th class="col-gpa-grade">Grd</th> @endif
                            @if(in_array('cgpa', $columnsToShow)) <th class="col-cgpa">CGPA</th> @endif
                            @if(in_array('compulsory_flag', $columnsToShow)) <th class="col-compulsory">Comp</th> @endif
                            @if(in_array('vetted_status', $columnsToShow)) <th class="col-vetted">Vetted</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($studentData['scores'] as $scoreIndex => $score)
                        <tr>
                            @if(in_array('sn', $columnsToShow)) <td class="col-sn">{{ $scoreIndex + 1 }}</td> @endif
                            @if(in_array('admission_no', $columnsToShow)) <td class="col-admissionno">{{ $student->admissionNo ?? '-' }}</td> @endif
                            @if(in_array('name', $columnsToShow)) <td class="col-name subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td> @endif
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
                                <td class="col-assessment @if ($isLow && is_numeric($assessmentScore)) highlight-red @endif">{{ $assessmentScore ? number_format($assessmentScore, 0) : '-' }}</td>
                                @endif
                            @endforeach
                            @if(in_array('total', $columnsToShow)) <td class="col-total @if ($score->total < 50) highlight-red @endif">{{ $score->total ? number_format($score->total, 1) : '-' }}</td> @endif
                            @if(in_array('bf', $columnsToShow)) <td class="col-bf">{{ $score->bf ? number_format($score->bf, 1) : '-' }}</td> @endif
                            @if(in_array('cum', $columnsToShow)) <td class="col-cum">{{ $score->cum ? number_format($score->cum, 1) : '-' }}</td> @endif
                            @if(in_array('grade', $columnsToShow)) <td class="col-grade @if (in_array($score->grade ?? '', ['F','F9','E','E8'])) highlight-red @endif">{{ $score->grade ?? '-' }}</td> @endif
                            @if(in_array('position', $columnsToShow)) <td class="col-position">{{ $score->position ?? '-' }}</td> @endif
                            @if(in_array('class_average', $columnsToShow)) <td class="col-class-average">{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td> @endif
                            @if(in_array('num_subjects', $columnsToShow)) <td class="col-num-subjects">{{ $gpaData['num_subjects'] ?? '-' }}</td> @endif
                            @if(in_array('total_grade_points', $columnsToShow)) <td class="col-total-grade-points">{{ $gpaData['total_grade_points'] ? number_format($gpaData['total_grade_points'], 1) : '-' }}</td> @endif
                            @if(in_array('gpa', $columnsToShow)) <td class="col-gpa">{{ $gpaData['gpa'] ? number_format($gpaData['gpa'], 2) : '-' }}</td> @endif
                            @if(in_array('calculated_gpa', $columnsToShow)) <td class="col-calculated-gpa">{{ $gpaData['calculated_gpa'] ? number_format($gpaData['calculated_gpa'], 2) : '-' }}</td> @endif
                            @if(in_array('gpa_grade', $columnsToShow)) <td class="col-gpa-grade">{{ $gpaData['gpa_grade'] ?? '-' }}</td> @endif
                            @if(in_array('cgpa', $columnsToShow)) <td class="col-cgpa">{{ $gpaData['cgpa'] ? number_format($gpaData['cgpa'], 2) : '-' }}</td> @endif
                            @if(in_array('compulsory_flag', $columnsToShow)) <td class="col-compulsory">{{ ($score->is_compulsory ?? false) ? '✓' : '-' }}</td> @endif
                            @if(in_array('vetted_status', $columnsToShow)) <td class="col-vetted">@php $vs = $score->vettedstatus ?? '2'; echo $vs === '1' ? '✓' : ($vs === '0' ? '✗' : '...'); @endphp</td> @endif
                        </tr>
                        @empty
                            <tr><td colspan="{{ $currentVisibleColumnCount }}" style="text-align: center;">No scores available.</td></tr>
                        @endforelse

                        <!-- Totals Row -->
                        <tr class="totals-row">
                            <td colspan="{{ $totalLabelColspan }}" style="text-align: right; padding-right: 10px;">TOTAL</td>
                            @if(in_array('total', $columnsToShow))
                            <td class="col-total"><div class="totals-fraction"><span class="t-num">{{ number_format($totals['obtained'], 1) }}</span><span class="t-den">{{ $totals['obtainable'] }}</span></div></td>
                            @endif
                            @if(in_array('bf', $columnsToShow)) <td class="col-bf"></td> @endif
                            @if(in_array('cum', $columnsToShow)) <td class="col-cum"></td> @endif
                            @if(in_array('grade', $columnsToShow)) <td class="col-grade"></td> @endif
                            @if(in_array('position', $columnsToShow)) <td class="col-position"></td> @endif
                            @if(in_array('class_average', $columnsToShow)) <td class="col-class-average">{{ $totals['percentage'] }}%</td> @endif
                            @foreach(['num_subjects','total_grade_points','gpa','calculated_gpa','gpa_grade','cgpa','compulsory_flag','vetted_status'] as $trailingCol)
                                @if(in_array($trailingCol, $columnsToShow)) <td></td> @endif
                            @endforeach
                        </tr>
                        <!-- Totals Summary Row: White background, black text -->
                        <tr class="totals-summary-row">
                            <td colspan="{{ $currentVisibleColumnCount }}">TOTAL OBTAINED: {{ number_format($totals['obtained'], 1) }}  |  TOTAL OBTAINABLE: {{ $totals['obtainable'] }}  |  % OBTAINED: {{ $totals['percentage'] }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- REMARKS: Two columns - Class Teacher's Remark (left) and Principal's Remark (right) -->
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Remark</div>
                            <div style="font-size: 12px; font-weight: 500; min-height: 40px;">
                                {{ $profile ? ($profile->classteachercomment ?? 'NO INFO') : 'NO INFO' }}
                            </div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark</div>
                            <div style="font-size: 12px; font-weight: 500; min-height: 40px;">
                                {{ $profile ? ($profile->principalscomment ?? 'NO INFO') : 'NO INFO' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- FOOTER -->
            <div class="footer-section">
                <table class="footer-layout-table">
                    <tr><td><span class="font-bold">Issued: </span><span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span><span class="font-bold">Collected by:</span><span>.......................................</span></td></tr>
                    <tr><td><span class="font-bold text-primary">Next Term Begins:</span><span class="text-dot-space2">@php $nextTermBegins = $schoolInfo->date_next_term_begins ?? null; $formattedNextTermBegins = $nextTermBegins ? \Carbon\Carbon::parse($nextTermBegins)->format('jS F, Y') : '........................'; @endphp {{ $formattedNextTermBegins }}</span></td></tr>
                </table>
                <div class="powered-by">Powered by Qudroid Systems</div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
