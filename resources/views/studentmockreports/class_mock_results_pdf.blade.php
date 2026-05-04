<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Mock Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5px;
            line-height: 1.3;
            color: #000;
            background: #f5f5f5;
            padding: 4mm 0;
            text-align: center;
        }
        .school-name-header {
            width: 100%;
            background: #111827;
            color: white;
            padding: 9px 10px 5px;
            text-align: center;
            border: 3px double #000000;
            border-bottom: 1px solid #1e40af;
        }
        .school-name-header .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 19.5px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .school-name-header .motto {
            font-size: 9.8px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 3px;
        }
        .watermark-text {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 65px;
            font-weight: 900;
            color: rgba(0,0,0,0.04);
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
            margin: 0 auto 15px;
            padding: 0;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .student-section:last-child { page-break-after: avoid; }
        .header-table { width: 100%; border-collapse: collapse; padding: 8px 10px 6px; }

        .school-logo, .photo-frame {
            width: 74px; height: 88px;
            border: 2px solid #47b492;
            border-radius: 6px;
            background: white;
            padding: 3px;
            overflow: hidden;
            display: block;
            text-align: center;
        }
        .school-logo img, .photo-frame img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .middle-info {
            font-size: 11.2px;
            font-weight: 700;
            line-height: 2.0;
            padding: 4px 15px;
            vertical-align: middle;
        }
        .middle-info strong {
            color: #1e40af;
            font-weight: 900;
        }

        .header-divider { width: 100%; height: 2px; background: #1e40af; margin: 0; }
        .header-divider2 { width: 100%; height: 1px; background: #64748b; margin: 2px 0; }
        .report-title {
            background: #111827;
            color: white;
            padding: 6px 8px;
            font-size: 11.5px;
            font-weight: 700;
            text-align: center;
            margin: 0;
        }
        .mock-badge {
            background: #b45309;
            color: white;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 8px;
            letter-spacing: 1px;
            text-align: center;
        }

        .student-info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 6px;
            padding: 7px 12px;
            margin: 8px 10px;
            font-size: 9.2px;
            text-align: center;
        }
        .info-table { width: 100%; border-collapse: collapse; margin: 0 auto; }
        .info-table td { padding: 3px 8px; text-align: center; }
        .info-bar-label {
            color: #1e40af;
            font-weight: 900;
            font-size: 8.6px;
            white-space: nowrap;
        }
        .info-bar-value {
            font-weight: 900;
            font-size: 9.4px;
            padding-left: 3px;
        }

        .result-table { padding: 0 10px; margin: 8px 0; }
        .result-table table {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
            font-size: 7.8px;
            margin: 0;
        }
        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000;
            padding: 3px 1px;
            font-size: 6.8px;
        }
        .result-table tbody td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            font-size: 8px;
            background: white;
            font-weight: 800;
            height: 16px;
            line-height: 16px;
        }
        .result-table tbody td.subject-name {
            text-align: left;
            font-weight: 800;
            font-size: 8px;
            padding-left: 5px;
        }
        .highlight-red { color: #dc2626; font-weight: 900; }
        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }
        .position-1 { background: gold; color: black; font-weight: 900; border-radius: 2px; }
        .position-2 { background: silver; color: black; font-weight: 900; }
        .position-3 { background: #cd7f32; color: white; font-weight: 900; }

        .totals-summary {
            width: calc(100% - 20px);
            background: #0d1a3d;
            color: #ffffff;
            font-weight: 900;
            font-size: 7.8px;
            padding: 5px 10px;
            border: 2px solid #000;
            border-top: none;
            text-align: center;
            margin: 0 10px 8px 10px;
        }
        .remarks-table {
            width: calc(100% - 20px);
            border: 2px solid #000;
            border-collapse: collapse;
            margin: 8px 10px 4px;
        }
        .remarks-table td {
            border: 1px solid #000;
            padding: 5px 8px;
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

        .bottom-strip {
            width: 100%;
            border-top: 1px solid #cbd5e1;
            background: #f1f5f9;
            margin-top: 6px;
        }
        .bottom-strip table { width: 100%; border-collapse: collapse; }
        .bottom-strip td { padding: 8px 10px; vertical-align: middle; }
        .bottom-strip .cell-qr { width: 90px; text-align: center; vertical-align: middle; }
        .bottom-strip .cell-footer { text-align: center; font-size: 8.6px; vertical-align: middle; }
        .bottom-strip .cell-stamp { width: 120px; text-align: center; vertical-align: middle; }
        .bottom-strip .cell-qr img { width: 72px; height: 72px; display: block; margin: 0 auto 2px; }
        .qr-label { font-size: 6.5px; color: #333; font-weight: 600; text-align: center; }
        .bottom-strip .cell-stamp img { width: 105px; height: 105px; transform: rotate(-8deg); display: block; margin: 0 auto; }
        .text-dot-space2 {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 110px;
            font-weight: bold;
            margin: 0 4px;
        }
        .powered-by { font-size: 8px; margin-top: 4px; color: #64748b; }

        @media print {
            body { background: white; padding: 0; }
            .student-section { width: 190mm; margin: 0 auto; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="watermark-text">MOCK EXAMINATION</div>

    @php
        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns  = ['sn', 'name', 'exam', 'total', 'grade', 'position', 'class_average'];
        $columnsToShow   = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;
    @endphp

    @foreach ($allStudentData as $studentData)
        @php
            $schoolInfo = $studentData['schoolInfo'] ?? null;
            $student    = $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
            $mockScores = $studentData['mockScores'] ?? collect();
            $totals     = $studentData['totals_summary'] ?? [];
            $profile    = $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;

            $fullName = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
            $admNo    = $student->admissionNo ?? '—';
            $classVal = ($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
            $session  = $metadata['session'] ?? '2025/2026';
            $term     = $metadata['term'] ?? 'SECOND TERM';
            $minRows  = 18;
            $extraRows = max(0, $minRows - $mockScores->count());

            $qrData = "Name: {$fullName}\nAdm No: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}\nSchool: Claret Secondary School Kabba";
            $qrCodeBase64 = base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(280)
                    ->errorCorrection('H')
                    ->generate($qrData)
            );

            // ── STAMP: prefer uploaded stamp (base64), fall back to asset ──
            $stampSrc = !empty($studentData['school_stamp_base64'])
                ? $studentData['school_stamp_base64']
                : asset('stamp.jpeg');
        @endphp

        <div class="student-section">

            {{-- SCHOOL NAME HEADER --}}
            <div class="school-name-header">
                <div class="school-full-name">{{ $schoolInfo->school_name ?? 'CLARET SECONDARY SCHOOL KABBA' }}</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
            </div>

            {{-- HEADER: Logo + Info + Photo --}}
            <table class="header-table">
                <tr>
                    <td width="20%" style="text-align:center; vertical-align:middle;">
                        <div class="school-logo">
                            @php
                                $logoSrc = !empty($studentData['school_logo_base64'])
                                    ? $studentData['school_logo_base64']
                                    : 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="72" height="85" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/><text x="50" y="55" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">LOGO</text></svg>');
                            @endphp
                            <img src="{{ $logoSrc }}" alt="School Logo">
                        </div>
                    </td>
                    <td width="58%" class="middle-info">
                        <strong>Address:</strong> {{ $schoolInfo->school_address ?? '—' }}<br>
                        <strong>Phone:</strong> {{ $schoolInfo->school_phone ?? '—' }}<br>
                        <strong>Email:</strong> {{ $schoolInfo->school_email ?? '—' }}<br>
                        <strong>Website:</strong> {{ $schoolInfo->school_website ?? '—' }}
                    </td>
                    <td width="22%" style="text-align:right; padding-right:8px; vertical-align:top; padding-top:6px;">
                        @if(in_array('picture', $columnsToShow))
                        <div class="photo-frame" style="margin-left:auto; margin-right:0;">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='85' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='65' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3C/svg%3E" alt="Default">
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="header-divider"></div>
            <div class="header-divider2"></div>
            <div class="report-title">
                {{ strtoupper($term) }} {{ strtoupper($session) }} MOCK EXAMINATION RESULT
            </div>
            <div class="mock-badge">MOCK EXAMINATION — NOT FOR OFFICIAL PROMOTION USE</div>

            {{-- STUDENT INFO BAR --}}
            <div class="student-info-bar">
                <table class="info-table">
                    <tr>
                        <td><span class="info-bar-label">NAME:</span> <span class="info-bar-value">{{ $fullName }}</span></td>
                        <td><span class="info-bar-label">SESSION:</span> <span class="info-bar-value">{{ $session }}</span></td>
                        <td><span class="info-bar-label">TERM:</span> <span class="info-bar-value">{{ $term }}</span></td>
                        <td><span class="info-bar-label">CLASS:</span> <span class="info-bar-value">{{ $classVal }}</span></td>
                    </tr>
                    <tr>
                        <td><span class="info-bar-label">ADM NO:</span> <span class="info-bar-value">{{ $admNo }}</span></td>
                        <td><span class="info-bar-label">NO. IN CLASS:</span> <span class="info-bar-value">{{ $studentData['numberOfStudents'] ?? '—' }}</span></td>
                        @if(in_array('gender', $columnsToShow))
                            <td><span class="info-bar-label">SEX:</span> <span class="info-bar-value">{{ $student->gender ?? '—' }}</span></td>
                        @endif
                        <td></td>
                    </tr>
                </table>
            </div>

            {{-- RESULTS TABLE --}}
            <div class="result-table">
                <table>
                    <thead>
                        <tr>
                            @if(in_array('sn', $columnsToShow))            <th style="width:28px;">S/N</th> @endif
                            @if(in_array('name', $columnsToShow))           <th style="min-width:120px; text-align:left; padding-left:4px;">Subject</th> @endif
                            @if(in_array('exam', $columnsToShow))           <th style="width:46px;">Exam Score</th> @endif
                            @if(in_array('total', $columnsToShow))          <th style="width:46px;">Total</th> @endif
                            @if(in_array('grade', $columnsToShow))          <th style="width:36px;">Grade</th> @endif
                            @if(in_array('position', $columnsToShow))       <th style="width:36px;">Pos</th> @endif
                            @if(in_array('class_average', $columnsToShow))  <th style="width:39px;">Avg</th> @endif
                            @if(in_array('cmin', $columnsToShow))           <th style="width:36px;">Min</th> @endif
                            @if(in_array('cmax', $columnsToShow))           <th style="width:36px;">Max</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mockScores as $i => $score)
                        <tr>
                            @if(in_array('sn', $columnsToShow))   <td>{{ $i + 1 }}</td> @endif
                            @if(in_array('name', $columnsToShow))  <td class="subject-name">{{ $score->subject_name ?? 'N/A' }}</td> @endif
                            @if(in_array('exam', $columnsToShow))  <td @if(($score->exam ?? 0) < 50) class="highlight-red" @endif>{{ $score->exam ? number_format($score->exam, 1) : '-' }}</td> @endif
                            @if(in_array('total', $columnsToShow)) <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>{{ $score->total ? number_format($score->total, 1) : '-' }}</td> @endif

                            @if(in_array('grade', $columnsToShow))
                                @php
                                    $g  = $score->grade ?? '-';
                                    $gc = match(true) {
                                        str_starts_with(strtoupper($g), 'A') => 'grade-A',
                                        str_starts_with(strtoupper($g), 'B') => 'grade-B',
                                        str_starts_with(strtoupper($g), 'C') => 'grade-C',
                                        str_starts_with(strtoupper($g), 'D') => 'grade-D',
                                        default => 'grade-F'
                                    };
                                @endphp
                                <td class="{{ $gc }}">{{ $g }}</td>
                            @endif

                            @if(in_array('position', $columnsToShow))
                                @php
                                    $pos    = $score->position ?? '-';
                                    $posNum = preg_replace('/\D/', '', $pos);
                                    $posC   = match((int)$posNum) { 1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => '' };
                                @endphp
                                <td class="{{ $posC }}">{{ $pos }}</td>
                            @endif

                            @if(in_array('class_average', $columnsToShow)) <td>{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td> @endif
                            @if(in_array('cmin', $columnsToShow))           <td>{{ $score->cmin ? number_format($score->cmin, 1) : '-' }}</td> @endif
                            @if(in_array('cmax', $columnsToShow))           <td>{{ $score->cmax ? number_format($score->cmax, 1) : '-' }}</td> @endif
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;">No mock scores available.</td></tr>
                        @endforelse

                        @for($i = 0; $i < $extraRows; $i++)
                        <tr>
                            @if(in_array('sn', $columnsToShow))           <td>&nbsp;</td> @endif
                            @if(in_array('name', $columnsToShow))          <td>&nbsp;</td> @endif
                            @if(in_array('exam', $columnsToShow))          <td>&nbsp;</td> @endif
                            @if(in_array('total', $columnsToShow))         <td>&nbsp;</td> @endif
                            @if(in_array('grade', $columnsToShow))         <td>&nbsp;</td> @endif
                            @if(in_array('position', $columnsToShow))      <td>&nbsp;</td> @endif
                            @if(in_array('class_average', $columnsToShow)) <td>&nbsp;</td> @endif
                            @if(in_array('cmin', $columnsToShow))          <td>&nbsp;</td> @endif
                            @if(in_array('cmax', $columnsToShow))          <td>&nbsp;</td> @endif
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- TOTALS --}}
            <div class="totals-summary">
                TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }}&nbsp;&nbsp;|&nbsp;&nbsp;
                TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }}&nbsp;&nbsp;|&nbsp;&nbsp;
                % OBTAINED: {{ $totals['percentage'] ?? 0 }}%
            </div>

            {{-- REMARKS --}}
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Remark</div>
                            <div>{{ $profile ? ($profile->classteachercomment ?? 'NO INFO') : 'NO INFO' }}</div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark</div>
                            <div>{{ $profile ? ($profile->principalscomment ?? 'NO INFO') : 'NO INFO' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- BOTTOM STRIP: QR | Footer text | Stamp (dompdf-safe normal flow) --}}
            <div class="bottom-strip">
                <table>
                    <tr>
                        <td class="cell-qr">
                            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
                            <div class="qr-label">Scan for Verification</div>
                        </td>
                        <td class="cell-footer">
                            <div>
                                <strong>Issued:</strong>
                                <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span>
                            </div>
                            <div style="margin-top:4px;">
                                <strong>Collected by:</strong>
                                <span class="text-dot-space2">.......................................</span>
                            </div>
                            <div style="margin-top:4px;">
                                <strong>Next Term Begins:</strong>
                                <span class="text-dot-space2">
                                    @php
                                        $ntb = $schoolInfo->date_next_term_begins ?? null;
                                        echo $ntb ? \Carbon\Carbon::parse($ntb)->format('jS F, Y') : '........................';
                                    @endphp
                                </span>
                            </div>
                            <div class="powered-by">Powered by Qudroid Systems</div>
                        </td>
                        <!-- RIGHT: School Stamp (uploaded via admin, falls back to stamp.jpeg) -->
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
