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
            color: rgba(0, 0, 0, 0.04);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 5px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
        }

        .student-section {
            width: 190mm;
            page-break-after: always;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto 15px auto;
            padding: 8px 8px;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        .school-logo {
            width: 72px;
            height: 85px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: white;
            padding: 2px;
            overflow: hidden;
            text-align: center;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .photo-frame {
            border: 2px solid #47b492;
            border-radius: 6px;
            background: white;
            padding: 2px;
            width: 72px;
            height: 85px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .photo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background: #1e40af;
            margin: 3px 0;
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
            padding: 4px 6px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            margin: 5px 0;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0 2px;
        }

        .student-info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 6px;
            padding: 5px 10px;
            margin-bottom: 8px;
            font-size: 8.5px;
        }
        .info-line {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 6px 12px;
            margin-bottom: 3px;
        }
        .info-bar-label {
            color: #1e40af;
            font-size: 8px;
            font-weight: 700;
        }
        .info-bar-value {
            font-weight: 900;
            margin-left: 2px;
        }
        .separator {
            color: #94a3b8;
            margin: 0 2px;
        }

        /* =====================================================
           CORE FIX: USE A TABLE FOR THE DUAL-COLUMN LAYOUT
           This works in ALL PDF renderers (wkhtmltopdf, dompdf,
           puppeteer, prince, etc.) unlike CSS flexbox/grid.
           ===================================================== */
        .dual-layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            /* prevent the whole block splitting across pages */
            page-break-inside: avoid;
        }
        /* Left cell: academic results — takes all remaining width */
        .dual-layout-table td.academic-cell {
            vertical-align: top;
            padding: 0;
            padding-right: 6px;
            /* width is auto — takes what psychomotor doesn't */
        }
        /* Right cell: psychomotor — fixed 148px, never squishes */
        .dual-layout-table td.psycho-cell {
            vertical-align: top;
            padding: 0;
            width: 148px;          /* hard pixel width */
            min-width: 148px;
        }
        /* ===================================================== */

        /* PSYCHOMOTOR PANEL */
        .psychomotor-container {
            width: 148px;
            background: #fef9e6;
            border: 2px solid #c0a86a;
            border-radius: 8px;
            padding: 0 4px 4px 4px;
        }
        .psychomotor-title {
            background: #2c3e4e;
            color: white;
            text-align: center;
            font-weight: 800;
            font-size: 8.5px;
            padding: 3px 2px;
            margin: 0 -4px 5px -4px;
            border-radius: 6px 6px 0 0;
            letter-spacing: 0.3px;
        }
        .psycho-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2px;
            table-layout: fixed;
        }
        .psycho-table th,
        .psycho-table td {
            border: 1px solid #b78d4a;
            padding: 2px 2px;
            word-break: break-word;
        }
        .psycho-table th {
            background: #e9d6b0;
            font-weight: 800;
            font-size: 7px;
            padding: 3px 1px;
        }
        .psycho-table td:first-child {
            width: 72%;
            font-weight: 600;
            background: #fff7e8;
            padding-left: 3px;
        }
        .psycho-table td:last-child {
            width: 28%;
            text-align: center;
            font-weight: bold;
        }
        .psycho-totals-row td {
            background: #e9d6b0;
            font-weight: 900;
            font-size: 7.5px;
        }
        .psycho-obtainable-row td {
            background: #faf0dd;
            font-size: 6.8px;
            text-align: center;
        }
        .psycho-note {
            font-size: 6px;
            text-align: center;
            margin-top: 4px;
            color: #4a5b6e;
            line-height: 1.2;
        }

        /* ACADEMIC TABLE */
        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            font-size: 7.8px;
            table-layout: fixed;
        }
        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 3px 1px;
            font-size: 6.8px;
            overflow: hidden;
        }
        .result-table tbody td {
            border: 1px solid #000000;
            padding: 2px 1px;
            text-align: center;
            font-size: 7.5px;
            background: white;
            font-weight: 600;
            overflow: hidden;
        }
        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 700;
            font-size: 7.5px;
            padding-left: 3px;
            word-break: break-word;
            white-space: normal;
        }
        .highlight-red {
            color: #dc2626;
            font-weight: 900;
        }
        .totals-row td {
            background: #0d1a3d !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 7.5px !important;
            padding: 2px 1px;
        }
        .totals-fraction {
            display: inline-block;
            text-align: center;
            font-size: 6.5px;
            line-height: 1;
        }
        .totals-fraction .t-num {
            display: block;
            border-bottom: 1px solid white;
            padding: 0 2px 1px;
        }
        .totals-summary-row td {
            background: #ffffff !important;
            color: #000000 !important;
            font-weight: 800 !important;
            font-size: 7.5px !important;
            padding: 3px 2px;
            white-space: normal;
        }

        /* Column widths for academic table */
        .col-sn           { width: 18px; }
        .col-admissionno  { width: 60px; }
        .col-name         { width: 100px; }
        .col-assessment   { width: 26px; }
        .col-total        { width: 32px; }
        .col-bf           { width: 26px; }
        .col-cum          { width: 32px; }
        .col-grade        { width: 26px; }
        .col-position     { width: 28px; }
        .col-class-average{ width: 30px; }

        /* Remarks table */
        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .remarks-table td {
            border: 1px solid #000000;
            padding: 5px 6px;
            background: white;
            vertical-align: top;
            width: 50%;
            font-size: 8.5px;
        }
        .remarks-table .h6 {
            font-weight: 700;
            margin-bottom: 3px;
            font-size: 9px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
        }

        /* Footer */
        .footer-section {
            background: #f1f5f9;
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 5px;
            font-size: 7.5px;
        }
        .footer-layout-table td {
            padding: 2px;
            font-size: 7.5px;
        }
        .text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-width: 90px;
            font-weight: bold;
        }
        .powered-by {
            font-size: 8px;
            margin-top: 2px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .student-section {
                width: 190mm;
                margin: 0 auto;
                padding: 6px;
                page-break-after: always;
                box-shadow: none;
            }
            .watermark-text {
                position: fixed;
                font-size: 55px;
                color: rgba(0, 0, 0, 0.07);
                print-color-adjust: exact;
            }
            .dual-layout-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="watermark-text">ORIGINAL COPY</div>

@php
    $selectedColumns = $metadata['selected_columns'] ?? [];
    $defaultColumns  = ['sn', 'admission_no', 'name', 'total', 'bf', 'cum', 'grade', 'position', 'class_average'];
    $columnsToShow   = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;

    $baseVisibleCount = 0;
    if (in_array('sn', $columnsToShow))           $baseVisibleCount++;
    if (in_array('admission_no', $columnsToShow)) $baseVisibleCount++;
    if (in_array('name', $columnsToShow))          $baseVisibleCount++;
@endphp

@foreach ($allStudentData as $index => $studentData)
    @php
        $schoolInfo  = $studentData['schoolInfo'] ?? null;
        $student     = $studentData['students'] && $studentData['students']->isNotEmpty()
                        ? $studentData['students']->first()
                        : null;
        $assessments = $studentData['assessments'] ?? collect();
        $totals      = $studentData['totals_summary'] ?? [];

        $psychomotorSkills = [
            'Handwriting', 'Sports', 'Musical Skills', 'Participation', 'Punctuality',
            'Concern for Others', 'Relationship(Students)', 'Relationship(Staff)',
            'Courtesy', 'Neatness', 'Honesty', 'Team Spirit', 'Leadership',
            'Listening Skills', 'Organizational Ability', 'Self Control', 'Perseverance', 'Initiative'
        ];
        $displaySkills = [
            'Handwriting', 'Sports', 'Musical Skills', 'Participation', 'Punctuality',
            'Concern for Others', 'Relate(Students)', 'Relate(Staff)',
            'Courtesy', 'Neatness', 'Honesty', 'Team Spirit', 'Leadership',
            'Listening', 'Organizational', 'Self Control', 'Perseverance', 'Initiative'
        ];
        $psychomotorObtainable = count($psychomotorSkills) * 5;

        if (isset($studentData['psychomotor_scores']) && is_array($studentData['psychomotor_scores'])) {
            $psychomotorScores   = $studentData['psychomotor_scores'];
            $psychomotorObtained = array_sum($psychomotorScores);
        } else {
            $sampleScores = [
                'Handwriting'=>4,'Sports'=>3,'Musical Skills'=>3,'Participation'=>3,
                'Punctuality'=>4,'Concern for Others'=>5,'Relationship(Students)'=>4,
                'Relationship(Staff)'=>4,'Courtesy'=>5,'Neatness'=>3,'Honesty'=>5,
                'Team Spirit'=>4,'Leadership'=>5,'Listening Skills'=>3,
                'Organizational Ability'=>4,'Self Control'=>4,'Perseverance'=>5,'Initiative'=>4
            ];
            $psychomotorScores = [];
            foreach ($psychomotorSkills as $skill) {
                $psychomotorScores[$skill] = $sampleScores[$skill] ?? rand(3,5);
            }
            $psychomotorObtained = array_sum($psychomotorScores);
        }

        $assessmentColumnsCount = 0;
        foreach ($assessments as $assessment) {
            if (in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow)) {
                $assessmentColumnsCount++;
            }
        }
        $currentVisibleColumnCount = $baseVisibleCount + $assessmentColumnsCount;
        $otherScoreCols = ['total','bf','cum','grade','position','class_average'];
        foreach ($otherScoreCols as $col) {
            if (in_array($col, $columnsToShow)) $currentVisibleColumnCount++;
        }
        $totalLabelColspan = $baseVisibleCount + $assessmentColumnsCount;
        if (in_array('total',         $columnsToShow)) $totalLabelColspan++;
        if (in_array('bf',            $columnsToShow)) $totalLabelColspan++;
        if (in_array('cum',           $columnsToShow)) $totalLabelColspan++;
        if (in_array('grade',         $columnsToShow)) $totalLabelColspan++;
        if (in_array('position',      $columnsToShow)) $totalLabelColspan++;
        if (in_array('class_average', $columnsToShow)) $totalLabelColspan++;
    @endphp

    <div class="student-section">

        <!-- ── HEADER ── -->
        <table class="header-table" style="width:100%">
            <tr>
                <td width="20%">
                    <div class="school-logo">
                        @php
                            if (!empty($studentData['school_logo_base64'])) {
                                $logoSrc = $studentData['school_logo_base64'];
                            } else {
                                $logoSrc = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="72" height="85" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/><circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/><rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/><text x="50" y="95" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">CLARET</text></svg>');
                            }
                        @endphp
                        <img src="{{ $logoSrc }}" alt="School Logo">
                    </div>
                </td>
                <td width="60%" style="padding-left:6px;">
                    <div style="font-family:'Arial Black',sans-serif;font-weight:900;line-height:1.2;">
                        <div style="font-size:16px;letter-spacing:0.3px;">CLARET SECONDARY SCHOOL KABBA</div>
                        <div style="font-size:7.5px;"><strong>Motto:</strong> {{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
                        <div style="font-size:7.5px;"><strong>Address:</strong> {{ $schoolInfo->school_address ?? 'No. 1, Claret Avenue, Kabba, Kogi State' }}</div>
                        <div style="font-size:7.5px;"><strong>Phone:</strong> {{ $schoolInfo->school_phone ?? '08136663185' }}</div>
                    </div>
                </td>
                <td width="20%">
                    @if(in_array('picture', $columnsToShow))
                    <div class="photo-frame">
                        @if(!empty($studentData['student_image_base64']))
                            <img src="{{ $studentData['student_image_base64'] }}" alt="Student">
                        @else
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='85' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3Ctext x='50' y='95' text-anchor='middle' fill='%23475569' font-size='8'%3EPHOTO%3C/text%3E%3C/svg%3E" alt="Default">
                        @endif
                    </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="header-divider"></div>
        <div class="header-divider2"></div>
        <div class="report-title">
            {{ strtoupper($metadata['term'] ?? 'SECOND TERM') }} {{ strtoupper($metadata['session'] ?? '2025/2026') }} ACADEMIC SESSION TERMINAL PROGRESS REPORT
        </div>

        <!-- ── STUDENT INFO BAR ── -->
        @if ($studentData['students'] && $studentData['students']->isNotEmpty())
            @php
                $profile   = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
                $fullName  = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
                $admNo     = $student->admissionNo ?? '—';
                $classVal  = ($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
                $schoolOpened = $schoolInfo->date_school_opened
                    ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y')
                    : '—';
                $numInClass = $studentData['numberOfStudents'] ?? '—';
            @endphp
            <div class="student-info-bar">
                <div class="info-line">
                    <span><span class="info-bar-label">NAME:</span><span class="info-bar-value">{{ $fullName }}</span></span>
                    <span class="separator">|</span>
                    <span><span class="info-bar-label">SESSION:</span><span class="info-bar-value">{{ $metadata['session'] ?? '—' }}</span></span>
                    <span class="separator">|</span>
                    <span><span class="info-bar-label">TERM:</span><span class="info-bar-value">{{ $metadata['term'] ?? '—' }}</span></span>
                    <span class="separator">|</span>
                    <span><span class="info-bar-label">CLASS:</span><span class="info-bar-value">{{ $classVal }}</span></span>
                </div>
                <div class="info-line">
                    <span><span class="info-bar-label">ADM NO:</span><span class="info-bar-value">{{ $admNo }}</span></span>
                    <span class="separator">|</span>
                    <span><span class="info-bar-label">SCHOOL OPENED:</span><span class="info-bar-value">{{ $schoolOpened }}</span></span>
                    <span class="separator">|</span>
                    <span><span class="info-bar-label">NO. IN CLASS:</span><span class="info-bar-value">{{ $numInClass }}</span></span>
                    @if(in_array('gender', $columnsToShow))
                        <span class="separator">|</span>
                        <span><span class="info-bar-label">SEX:</span><span class="info-bar-value">{{ $student->gender ?? '—' }}</span></span>
                    @endif
                </div>
            </div>
        @else
            <div class="student-info-bar"><div class="info-line">No student data available.</div></div>
        @endif

        <!-- ══════════════════════════════════════════════════════
             DUAL-COLUMN LAYOUT: TABLE-BASED (PDF-safe)
             Left  cell → academic results   (auto width)
             Right cell → psychomotor panel  (148px fixed)
             ══════════════════════════════════════════════════════ -->
        <table class="dual-layout-table">
            <tr>

                <!-- LEFT: ACADEMIC RESULTS -->
                <td class="academic-cell">
                    <div class="result-table">
                        <table>
                            <thead>
                                <tr>
                                    @if(in_array('sn',           $columnsToShow)) <th class="col-sn">S/N</th> @endif
                                    @if(in_array('admission_no', $columnsToShow)) <th class="col-admissionno">Adm No</th> @endif
                                    @if(in_array('name',         $columnsToShow)) <th class="col-name">Subject</th> @endif
                                    @foreach ($assessments as $assessment)
                                        @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                            <th class="col-assessment">
                                                {{ $assessment->name }}<br>
                                                <span style="font-size:5.5px;">({{ $assessment->max_score }})</span>
                                            </th>
                                        @endif
                                    @endforeach
                                    @if(in_array('total',         $columnsToShow)) <th class="col-total">Total</th> @endif
                                    @if(in_array('bf',            $columnsToShow)) <th class="col-bf">BF</th> @endif
                                    @if(in_array('cum',           $columnsToShow)) <th class="col-cum">Cum</th> @endif
                                    @if(in_array('grade',         $columnsToShow)) <th class="col-grade">Grade</th> @endif
                                    @if(in_array('position',      $columnsToShow)) <th class="col-position">Pos</th> @endif
                                    @if(in_array('class_average', $columnsToShow)) <th class="col-class-average">Avg</th> @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($studentData['scores'] as $scoreIndex => $score)
                                <tr>
                                    @if(in_array('sn',           $columnsToShow)) <td>{{ $scoreIndex + 1 }}</td> @endif
                                    @if(in_array('admission_no', $columnsToShow)) <td>{{ $student->admissionNo ?? '-' }}</td> @endif
                                    @if(in_array('name',         $columnsToShow)) <td class="subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td> @endif
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
                                    @if(in_array('total',    $columnsToShow)) <td @if($score->total < 50) class="highlight-red" @endif>{{ $score->total ? number_format($score->total, 1) : '-' }}</td> @endif
                                    @if(in_array('bf',       $columnsToShow)) <td>{{ $score->bf  ? number_format($score->bf,  1) : '-' }}</td> @endif
                                    @if(in_array('cum',      $columnsToShow)) <td>{{ $score->cum ? number_format($score->cum, 1) : '-' }}</td> @endif
                                    @if(in_array('grade',    $columnsToShow)) <td @if(in_array($score->grade ?? '', ['F','F9','E','E8'])) class="highlight-red" @endif>{{ $score->grade ?? '-' }}</td> @endif
                                    @if(in_array('position', $columnsToShow)) <td>{{ $score->position ?? '-' }}</td> @endif
                                    @if(in_array('class_average', $columnsToShow)) <td>{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td> @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $currentVisibleColumnCount }}" style="text-align:center;">No scores available.</td>
                                </tr>
                                @endforelse

                                <!-- Totals row -->
                                <tr class="totals-row">
                                    <td colspan="{{ $totalLabelColspan }}" style="text-align:right;padding-right:5px;">TOTAL</td>
                                    @if(in_array('total', $columnsToShow))
                                        <td>
                                            <div class="totals-fraction">
                                                <span class="t-num">{{ number_format($totals['obtained'], 1) }}</span>
                                                <span class="t-den">{{ $totals['obtainable'] }}</span>
                                            </div>
                                        </td>
                                    @endif
                                    @if(in_array('bf',            $columnsToShow)) <td></td> @endif
                                    @if(in_array('cum',           $columnsToShow)) <td></td> @endif
                                    @if(in_array('grade',         $columnsToShow)) <td></td> @endif
                                    @if(in_array('position',      $columnsToShow)) <td></td> @endif
                                    @if(in_array('class_average', $columnsToShow)) <td>{{ $totals['percentage'] }}%</td> @endif
                                </tr>

                                <!-- Summary row -->
                                <tr class="totals-summary-row">
                                    <td colspan="{{ $currentVisibleColumnCount }}">
                                        TOTAL OBTAINED: {{ number_format($totals['obtained'], 1) }}&nbsp;&nbsp;|&nbsp;&nbsp;TOTAL OBTAINABLE: {{ $totals['obtainable'] }}&nbsp;&nbsp;|&nbsp;&nbsp;% OBTAINED: {{ $totals['percentage'] }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>

                <!-- RIGHT: PSYCHOMOTOR PANEL -->
                <td class="psycho-cell">
                    <div class="psychomotor-container">
                        <div class="psychomotor-title">PSYCHOMOTOR &amp; AFFECTIVE</div>
                        <table class="psycho-table">
                            <thead>
                                <tr><th>Skill</th><th>Score</th></tr>
                            </thead>
                            <tbody>
                                @foreach($displaySkills as $idx => $shortSkill)
                                <tr>
                                    <td>{{ $shortSkill }}</td>
                                    <td>{{ $psychomotorScores[$psychomotorSkills[$idx]] ?? '-' }}</td>
                                </tr>
                                @endforeach
                                <tr class="psycho-totals-row">
                                    <td style="text-align:right;font-weight:900;">TOTAL OBTAINED</td>
                                    <td style="text-align:center;font-weight:900;">{{ $psychomotorObtained }}</td>
                                </tr>
                                <tr class="psycho-obtainable-row">
                                    <td colspan="2" style="text-align:center;">Max: {{ $psychomotorObtainable }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="psycho-note">5=Excellent ... 1=Needs Improvement</div>
                    </div>
                </td>

            </tr>
        </table>
        <!-- ══ end dual-column layout ══ -->

        <!-- ── REMARKS ── -->
        <table class="remarks-table">
            <tbody>
                <tr>
                    <td width="50%">
                        <div class="h6">Class Teacher's Remark</div>
                        <div style="font-size:8.5px;">{{ $profile ? ($profile->classteachercomment ?? 'NO INFO') : 'NO INFO' }}</div>
                    </td>
                    <td width="50%">
                        <div class="h6">Principal's Remark</div>
                        <div style="font-size:8.5px;">{{ $profile ? ($profile->principalscomment ?? 'NO INFO') : 'NO INFO' }}</div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ── FOOTER ── -->
        <div class="footer-section">
            <table class="footer-layout-table" style="width:100%">
                <tr>
                    <td>
                        <span style="font-weight:bold;">Issued: </span>
                        <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span>
                        <span style="font-weight:bold;"> Collected by:</span>
                        <span>.......................................</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="font-weight:bold;">Next Term Begins:</span>
                        @php
                            $nextTermBegins = $schoolInfo->date_next_term_begins ?? null;
                            $formattedNextTermBegins = $nextTermBegins
                                ? \Carbon\Carbon::parse($nextTermBegins)->format('jS F, Y')
                                : '........................';
                        @endphp
                        <span class="text-dot-space2">{{ $formattedNextTermBegins }}</span>
                    </td>
                </tr>
            </table>
            <div class="powered-by">Powered by Qudroid Systems</div>
        </div>

    </div>{{-- end .student-section --}}
@endforeach

</body>
</html>
