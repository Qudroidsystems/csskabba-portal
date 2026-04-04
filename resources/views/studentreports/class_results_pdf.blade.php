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
            width: 210mm;
            max-height: 297mm;
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

        /* School logo container */
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

        /* Student info bar */
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

        /* Main result table container - now with two side-by-side tables */
        .results-wrapper {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }
        .academic-table {
            flex: 2;
        }
        .psychomotor-table {
            flex: 1;
        }
        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
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
        .psychomotor-table .psycho-label {
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

        /* Psychomotor totals row */
        .psycho-totals-row td {
            background: #0d1a3d !important;
            color: #ffffff !important;
            font-weight: 900;
            font-size: 9px;
            text-align: center;
            padding: 4px;
        }

        /* Remarks Table */
        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 4px;
            margin-top: 8px;
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

        /* Grade Key Section */
        .grade-key-section {
            margin-top: 8px;
            border: 1px solid #000;
            padding: 6px;
            background: #f9f9f9;
            font-size: 9px;
        }
        .grade-key-title {
            font-weight: 900;
            text-align: center;
            margin-bottom: 4px;
        }
        .grade-key-items {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
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
        .font-bold {
            font-weight: 900;
        }
        .text-primary {
            color: #02175e;
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

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .student-section {
                width: 210mm;
                max-height: 297mm;
                margin: 0 auto;
                padding: 12px;
                page-break-after: always;
                box-shadow: none;
            }
            .watermark-text {
                position: fixed;
                font-size: 70px;
                color: rgba(0, 0, 0, 0.1);
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
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

    // Define psychomotor skills (based on sample PDF)
    $psychomotorSkills = [
        'Handwriting' => 5,
        'Sports' => 5,
        'Musical Skills' => 5,
        'Participation in Class' => 5,
        'Punctuality' => 5,
        'Concern for Others' => 5,
        'Relationship with Students' => 5,
        'Relationship with Staff' => 5,
        'Courtesy' => 5,
        'Neatness' => 5,
        'Honesty' => 5,
        'Team Spirit' => 5,
        'Leadership Skills' => 5,
        'Listening Skills' => 5,
        'Organizational Ability' => 5,
        'Self Control' => 5,
        'Perseverance' => 5,
        'Initiative' => 5,
    ];
    $psychomotorObtainable = count($psychomotorSkills) * 5;
    $psychomotorObtained = 0;
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

        // For demo, generate random psychomotor scores (in real implementation, fetch from database)
        $psychomotorScores = [];
        foreach ($psychomotorSkills as $skill => $maxScore) {
            // Simulate score between 1-5 (in production, get from student's psychomotor records)
            $score = rand(1, 5);
            $psychomotorScores[$skill] = $score;
            $psychomotorObtained += $score;
        }
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
                                    $hasLogo = false;
                                    $logoSrc = '';
                                    if (!empty($studentData['school_logo_base64'])) {
                                        $logoSrc = $studentData['school_logo_base64'];
                                        $hasLogo = true;
                                    }
                                    if (!$hasLogo) {
                                        $logoSrc = 'data:image/svg+xml;base64,' . base64_encode('
                                            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                                                <rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/>
                                                <circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/>
                                                <rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/>
                                                <text x="50" y="95" text-anchor="middle" fill="#1e40af" font-family="Arial" font-size="8" font-weight="bold">CLARET</text>
                                            </svg>
                                        ');
                                    }
                                @endphp
                                <img class="header-img" src="{{ $logoSrc }}" alt="School Logo">
                            </div>
                        </td>
                        <td width="50%" style="padding-left: 10px; vertical-align: middle;">
                            <div style="font-family: 'Arial Black', 'Helvetica Bold', sans-serif; font-weight: 900; color: #000; line-height: 1.2; text-align: left;">
                                <div style="font-size: 20px; letter-spacing: 1px; margin-bottom: 4px; color: #1e293b;">
                                    CLARET SECONDARY SCHOOL KABBA
                                </div>
                                <div style="font-size: 9px;">
                                    <strong style="color: #1e40af;">Motto:</strong>
                                    <span>{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</span>
                                </div>
                                <div style="font-size: 9px;">
                                    <strong style="color: #1e40af;">Address:</strong>
                                    <span>{{ $schoolInfo->school_address ?? 'Iludun Quarters, Olle Road, Kabba Kogi State' }}</span>
                                </div>
                                <div style="font-size: 9px;">
                                    <strong style="color: #1e40af;">Phone:</strong>
                                    <span>{{ $schoolInfo->school_phone ?? '08136663185' }}</span>
                                </div>
                            </div>
                        </td>
                        <td width="25%">
                            @if(in_array('picture', $columnsToShow))
                            <div class="photo-frame">
                                @if(!empty($studentData['student_image_base64']))
                                    <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                                @else
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3Ctext x='50' y='95' text-anchor='middle' fill='%23475569' font-size='8'%3EPHOTO%3C/text%3E%3C/svg%3E" alt="Default">
                                @endif
                            </div>
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="header-divider"></div>
                <div class="report-title">
                    FIRST TERM REPORT SHEET
                </div>
            </div>

            <!-- STUDENT INFO -->
            @if ($studentData['students'] && $studentData['students']->isNotEmpty())
                @php
                    $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()
                                ? $studentData['studentpp']->first()
                                : null;
                    $fullName = strtoupper($student->lastname ?? '') . ', ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
                    $admNo = $student->admissionNo ?? '—';
                    $sessionVal = $studentData['schoolsession']->session ?? '—';
                    $termVal = $studentData['schoolterm']->term ?? 'First';
                    $classVal = ($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
                    $gender = $student->gender ?? 'MALE';
                @endphp
                <div class="student-info-bar">
                    <div class="info-line">
                        <span class="info-bar-item"><span class="info-bar-label">Name of Student:</span><span class="info-bar-value">{{ $fullName }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">Adm.No:</span><span class="info-bar-value">{{ $admNo }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">House:</span><span class="info-bar-value">—</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">Sex:</span><span class="info-bar-value">{{ $gender }}</span></span>
                    </div>
                    <div class="info-line">
                        <span class="info-bar-item"><span class="info-bar-label">Session:</span><span class="info-bar-value">{{ $sessionVal }}</span></span>
                        <span class="separator">|</span>
                        <span class="info-bar-item"><span class="info-bar-label">Class:</span><span class="info-bar-value">{{ $classVal }}</span></span>
                    </div>
                </div>
            @endif

            <!-- RESULTS: Academic + Psychomotor side by side -->
            <div class="results-wrapper">
                <!-- Academic Scores Table -->
                <div class="academic-table result-table">
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2">S/N</th>
                                <th rowspan="2" colspan="7">SUBJECTS</th>
                                <th colspan="3">SCORES</th>
                                <th rowspan="2">Grade</th>
                                <th rowspan="2">Pos</th>
                            </tr>
                            <tr>
                                <th>CA1</th>
                                <th>CA2</th>
                                <th>Exam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['scores'] as $scoreIndex => $score)
                            <tr>
                                <td>{{ $scoreIndex + 1 }}</td>
                                <td colspan="7" class="subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td>
                                <td>{{ $score->ca1 ?? '-' }}</td>
                                <td>{{ $score->ca2 ?? '-' }}</td>
                                <td>{{ $score->exam ?? '-' }}</td>
                                <td>{{ $score->grade ?? '-' }}</td>
                                <td>{{ $score->position ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="12">No scores available</td></tr>
                            @endforelse
                            <!-- Totals row for academic -->
                            <tr class="totals-row">
                                <td colspan="10" style="text-align: right; padding-right: 10px;">TOTAL</td>
                                <td>{{ number_format($totals['obtained'] ?? 0, 0) }}</td>
                                <td></td>
                            </tr>
                            <tr class="totals-summary-row">
                                <td colspan="12">Obtained: {{ number_format($totals['obtained'] ?? 0, 0) }} | Obtainable: {{ $totals['obtainable'] ?? 0 }} | % Obtained: {{ $totals['percentage'] ?? 0 }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Psychomotor/Affective Skills Table -->
                <div class="psychomotor-table result-table">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">PSYCHOMOTOR/AFFECTIVE</th>
                            </tr>
                            <tr>
                                <th>Skills</th>
                                <th>Score (0-5)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($psychomotorSkills as $skill => $maxScore)
                            <tr>
                                <td class="psycho-label">{{ $skill }}</td>
                                <td>{{ $psychomotorScores[$skill] ?? '-' }}</td>
                            </tr>
                            @endforeach
                            <tr class="psycho-totals-row">
                                <td style="text-align: right; font-weight: 900;">TOTAL</td>
                                <td>{{ $psychomotorObtained }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="background: #f0f0f0; font-size: 9px;">Obtainable: {{ $psychomotorObtainable }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Class Teacher's Comment + Grade Key + Principal's Comment in a combined layout -->
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Comment:</div>
                            <div style="font-size: 11px; min-height: 50px;">
                                {{ $profile ? ($profile->classteachercomment ?? 'Good performance, keep it up!') : 'Good performance, keep it up!' }}
                            </div>
                            <div style="margin-top: 10px;"><strong>Signature:</strong> _________________________</div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Comment:</div>
                            <div style="font-size: 11px; min-height: 50px;">
                                {{ $profile ? ($profile->principalscomment ?? 'Well done. Continue working hard.') : 'Well done. Continue working hard.' }}
                            </div>
                            <div style="margin-top: 10px;"><strong>Signature:</strong> _________________________</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Grade Key Section -->
            <div class="grade-key-section">
                <div class="grade-key-title">GRADE KEY</div>
                <div class="grade-key-items">
                    <span>50 - 59: PASS</span>
                    <span>60 - 69: LOWER CREDIT</span>
                    <span>70 - 79: UPPER CREDIT</span>
                    <span>80 - 100: DISTINCTION</span>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer-section">
                <table class="footer-layout-table">
                    <tr>
                        <td><span class="font-bold">Issued: </span>{{ now()->format('jS F, Y') }}</td>
                        <td><span class="font-bold">Collected by:</span> .......................................</td>
                    </tr>
                    <tr>
                        <td><span class="font-bold text-primary">Next Term Begins:</span> ........................</td>
                        <td><span class="font-bold">House Master's Comment:</span> ........................</td>
                    </tr>
                </table>
                <div class="powered-by">Powered by Qudroid Systems</div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
