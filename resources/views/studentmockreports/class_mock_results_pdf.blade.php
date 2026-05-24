<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Mock Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            background: #f5f5f5;
            padding: 3mm 0;
            text-align: center;
        }

        /* ── Watermark ── */
        .watermark-text {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 60px;
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

        /* ── Outer wrapper ── */
        .student-section {
            width: 190mm;
            page-break-after: always;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto 12px;
            padding: 0;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .student-section:last-child { page-break-after: avoid; }

        /* ── School name header ── */
        .school-name-header {
            width: 100%;
            background: #111827;
            color: white;
            padding: 6px 10px 4px;
            text-align: center;
            border-bottom: 1px solid #1e40af;
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
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: .92;
            margin-top: 2px;
        }

        /* ── Header table: logo | contact | photo ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            padding: 5px 8px 4px;
        }
        .school-logo {
            width: 63px; height: 70px;
            border: 2px solid #47b492;
            border-radius: 5px;
            background: white;
            padding: 3px;
            overflow: hidden;
            display: block;
            text-align: center;
        }
        .school-logo img { max-width:100%; max-height:100%; object-fit:contain; }

        .photo-frame {
            width: 58px; height: 70px;
            border: 2px solid #47b492;
            border-radius: 5px;
            background: white;
            padding: 2px;
            overflow: hidden;
            display: block;
            text-align: center;
            margin-left: auto; margin-right: 0;
        }
        .photo-frame img { max-width:100%; max-height:100%; object-fit:cover; }

        .contact-table { border:none; border-collapse:collapse; width:100%; font-size:8.8px; }
        .contact-table td { padding: 1.8px 4px 1.8px 0; vertical-align:top; }
        .contact-key { font-weight:900; color:#1e40af; white-space:nowrap; }

        /* ── Dividers ── */
        .header-divider  { width:100%; height:2px; background:#1e40af; }
        .header-divider2 { width:100%; height:1px; background:#64748b; margin:1px 0; }

        /* ── Report title + mock badge ── */
        .report-title {
            background: #111827;
            color: white;
            padding: 5px 8px;
            font-size: 10.5px;
            font-weight: 700;
            text-align: center;
            letter-spacing: .3px;
        }
        .mock-badge {
            background: #b45309;
            color: white;
            font-size: 7.5px;
            font-weight: 700;
            padding: 2px 8px;
            letter-spacing: .8px;
            text-align: center;
        }

        /* ── Student info bar — single clean table, no nested rows ── */
        .info-bar {
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 5px;
            margin: 5px 9px;
            overflow: hidden;
        }
        .info-bar-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-bar-table td {
            padding: 4px 8px;
            font-size: 8.5px;
            vertical-align: middle;
            border-right: 1px solid #d1fae5;
        }
        .info-bar-table td:last-child { border-right: none; }
        .info-bar-table tr:first-child td {
            border-bottom: 1px solid #d1fae5;
        }
        .ibl { color:#1e40af; font-weight:900; font-size:7.8px; display:block; }
        .ibv { font-weight:900; font-size:9px; }

        /* ── Result table ── */
        .result-table { padding: 0 9px; margin: 5px 0; }
        .result-table table {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
            font-size: 7.5px;
        }
        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000;
            padding: 3px 1px;
            font-size: 6.5px;
            text-align: center;
        }
        /* Real data rows */
        .result-table tbody td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            font-size: 7.5px;
            background: white;
            font-weight: 800;
            height: 14px;
            line-height: 14px;
        }
        /* Empty filler rows — thinner so they don't bloat height */
        .result-table tbody tr.filler td {
            height: 10px;
            line-height: 10px;
            padding: 0;
            border: 1px solid #d1d5db; /* lighter border for fillers */
            background: #fafafa;
        }
        .result-table tbody td.subject-name {
            text-align: left;
            padding-left: 5px;
        }

        /* Grade / position colours */
        .highlight-red { color:#dc2626; font-weight:900; }
        .grade-A { color:#16a34a; font-weight:900; }
        .grade-B { color:#2563eb; font-weight:900; }
        .grade-C { color:#ca8a04; font-weight:900; }
        .grade-D { color:#ea580c; font-weight:900; }
        .grade-F { color:#dc2626; font-weight:900; }
        .position-1 { background:gold;    color:black; font-weight:900; border-radius:2px; }
        .position-2 { background:silver;  color:black; font-weight:900; }
        .position-3 { background:#cd7f32; color:white; font-weight:900; }

        /* ── Totals bar ── */
        .totals-summary {
            width: calc(100% - 18px);
            background: #0d1a3d;
            color: #ffffff;
            font-weight: 900;
            font-size: 7.5px;
            padding: 4px 9px;
            border: 2px solid #000;
            border-top: none;
            text-align: center;
            margin: 0 9px 5px;
        }

        /* ── Remarks ── */
        .remarks-table {
            width: calc(100% - 18px);
            border: 2px solid #000;
            border-collapse: collapse;
            margin: 0 9px 4px;
        }
        .remarks-table td {
            border: 1px solid #000;
            padding: 4px 7px;
            background: white;
            vertical-align: top;
            font-size: 8px;
            line-height: 1.35;
        }
        .remarks-table .remark-label {
            font-weight: 700;
            font-size: 8.5px;
            border-bottom: 1px solid #ccc;
            display: block;
            margin-bottom: 3px;
            padding-bottom: 1px;
        }

        /* ── Bottom strip ── */
        .bottom-strip {
            width: 100%;
            border-top: 1px solid #cbd5e1;
            background: #f1f5f9;
            margin-top: 4px;
        }
        .bottom-strip table { width:100%; border-collapse:collapse; }
        .bottom-strip td    { padding:5px 8px; vertical-align:middle; }
        .cell-qr     { width:76px; text-align:center; vertical-align:middle; }
        .cell-footer { text-align:center; font-size:7.8px; vertical-align:middle; }
        .cell-stamp  { width:106px; text-align:center; vertical-align:middle; }
        .cell-qr img { width:64px; height:64px; display:block; margin:0 auto 2px; }
        .qr-label    { font-size:6px; color:#333; font-weight:600; text-align:center; }
        /* stamp — rotated, no overflow clipping */
        .cell-stamp img {
            width: 90px; height: 90px;
            transform: rotate(-8deg);
            display: block; margin: 0 auto;
        }
        .text-dot-space2 {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 100px;
            font-weight: bold;
            margin: 0 3px;
        }
        .powered-by { font-size:7px; margin-top:3px; color:#64748b; }

        @media print {
            body { background:white; padding:0; }
            .student-section { width:190mm; margin:0 auto; box-shadow:none; }
        }
    </style>
</head>
<body>
    <div class="watermark-text">MOCK EXAMINATION</div>

    @php
        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns  = ['sn','name','exam','total','grade','position','class_average'];
        $columnsToShow   = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;

        // Column count for colspan on empty rows
        $colCount = 0;
        if (in_array('sn',            $columnsToShow)) $colCount++;
        if (in_array('name',          $columnsToShow)) $colCount++;
        if (in_array('exam',          $columnsToShow)) $colCount++;
        if (in_array('total',         $columnsToShow)) $colCount++;
        if (in_array('grade',         $columnsToShow)) $colCount++;
        if (in_array('position',      $columnsToShow)) $colCount++;
        if (in_array('class_average', $columnsToShow)) $colCount++;
        if (in_array('cmin',          $columnsToShow)) $colCount++;
        if (in_array('cmax',          $columnsToShow)) $colCount++;
    @endphp

    @foreach ($allStudentData as $studentData)
        @php
            $schoolInfo = $studentData['schoolInfo'] ?? null;
            $student    = $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
            $mockScores = $studentData['mockScores'] ?? collect();
            $totals     = $studentData['totals_summary'] ?? [];
            $profile    = $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;

            $fullName = strtoupper($student->lastname ?? '') . ', ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
            $admNo    = $student->admissionNo ?? '—';
            $classVal = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . ($studentData['schoolclass']->arms->arm ?? ''));
            $session  = $metadata['session'] ?? '2025/2026';
            $term     = $metadata['term']    ?? 'SECOND TERM';

            // ── 16 min rows, rendered as thin filler rows ──
            $minRows   = 16;
            $extraRows = max(0, $minRows - $mockScores->count());

            $qrData = "Name: {$fullName}\nAdm No: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}\nSchool: " . ($schoolInfo->school_name ?? 'School');
            $qrCodeBase64 = base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(256)->errorCorrection('H')->generate($qrData)
            );

            $stampSrc = !empty($studentData['school_stamp_base64'])
                ? $studentData['school_stamp_base64']
                : asset('stamp.jpeg');

            $logoSrc = !empty($studentData['school_logo_base64'])
                ? $studentData['school_logo_base64']
                : 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="63" height="70" viewBox="0 0 100 100">
                     <rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/>
                     <text x="50" y="55" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">LOGO</text>
                     </svg>'
                );
        @endphp

        <div class="student-section">

            {{-- ── SCHOOL NAME HEADER ── --}}
            <div class="school-name-header">
                <div class="school-full-name">{{ $schoolInfo->school_name ?? 'CLARET SECONDARY SCHOOL KABBA' }}</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
            </div>

            {{-- ── HEADER: Logo | Contact | Photo ── --}}
            <table class="header-table">
                <tr>
                    <td width="14%" style="vertical-align:middle; text-align:center; padding:5px 6px 4px 8px;">
                        <div class="school-logo">
                            <img src="{{ $logoSrc }}" alt="School Logo">
                        </div>
                    </td>
                    <td style="vertical-align:top; padding:5px 6px 4px;">
                        <table class="contact-table">
                            <tr>
                                <td class="contact-key">Address:</td>
                                <td>{{ $schoolInfo->school_address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="contact-key">Phone:</td>
                                <td>{{ $schoolInfo->formatted_phones ?? ($schoolInfo->school_phone ?? '—') }}</td>
                            </tr>
                            <tr>
                                <td class="contact-key">Email:</td>
                                <td>{{ $schoolInfo->school_email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="contact-key">Website:</td>
                                <td>{{ $schoolInfo->school_website ?? '—' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="16%" style="vertical-align:middle; text-align:right; padding:5px 8px 4px 4px;">
                        @if(in_array('picture', $columnsToShow))
                        <div class="photo-frame">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Student Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='58' height='70' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='38' r='20' fill='%2394a3b8'/%3E%3Crect x='30' y='64' width='40' height='28' fill='%2394a3b8' rx='4'/%3E%3C/svg%3E" alt="Photo">
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

            {{-- ── STUDENT INFO BAR ──
                 Two rows: top = Name | Session | Term | Class
                           bottom = Adm No | No. in Class | (Gender) | (DOB)
            ── --}}
            <div class="info-bar">
                <table class="info-bar-table">
                    <tr>
                        <td style="width:40%;">
                            <span class="ibl">NAME</span>
                            <span class="ibv">{{ $fullName }}</span>
                        </td>
                        <td style="width:20%;">
                            <span class="ibl">SESSION</span>
                            <span class="ibv">{{ $session }}</span>
                        </td>
                        <td style="width:18%;">
                            <span class="ibl">TERM</span>
                            <span class="ibv">{{ $term }}</span>
                        </td>
                        <td style="width:22%;">
                            <span class="ibl">CLASS</span>
                            <span class="ibv">{{ $classVal }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="ibl">ADMISSION NO</span>
                            <span class="ibv">{{ $admNo }}</span>
                        </td>
                        <td>
                            <span class="ibl">NO. IN CLASS</span>
                            <span class="ibv">{{ $studentData['numberOfStudents'] ?? '—' }}</span>
                        </td>
                        @if(in_array('gender', $columnsToShow))
                        <td>
                            <span class="ibl">SEX</span>
                            <span class="ibv">{{ $student->gender ?? '—' }}</span>
                        </td>
                        @else
                        <td></td>
                        @endif
                        @if(in_array('dob', $columnsToShow))
                        <td>
                            <span class="ibl">DATE OF BIRTH</span>
                            <span class="ibv">{{ $student->dateofbirth ?? '—' }}</span>
                        </td>
                        @else
                        <td></td>
                        @endif
                    </tr>
                </table>
            </div>

            {{-- ── RESULTS TABLE ── --}}
            <div class="result-table">
                <table>
                    <thead>
                        <tr>
                            @if(in_array('sn',            $columnsToShow)) <th style="width:24px;">S/N</th> @endif
                            @if(in_array('name',          $columnsToShow)) <th style="text-align:left;padding-left:5px;">Subject</th> @endif
                            @if(in_array('exam',          $columnsToShow)) <th style="width:44px;">Exam Score</th> @endif
                            @if(in_array('total',         $columnsToShow)) <th style="width:44px;">Total</th> @endif
                            @if(in_array('grade',         $columnsToShow)) <th style="width:34px;">Grade</th> @endif
                            @if(in_array('position',      $columnsToShow)) <th style="width:34px;">Pos</th> @endif
                            @if(in_array('class_average', $columnsToShow)) <th style="width:38px;">Avg</th> @endif
                            @if(in_array('cmin',          $columnsToShow)) <th style="width:34px;">Min</th> @endif
                            @if(in_array('cmax',          $columnsToShow)) <th style="width:34px;">Max</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ── Real data rows ── --}}
                        @forelse ($mockScores as $i => $score)
                        <tr>
                            @if(in_array('sn',   $columnsToShow)) <td>{{ $i + 1 }}</td> @endif
                            @if(in_array('name', $columnsToShow)) <td class="subject-name">{{ $score->subject_name ?? 'N/A' }}</td> @endif

                            @if(in_array('exam', $columnsToShow))
                                <td @if(($score->exam ?? 0) < 50 && ($score->exam ?? 0) > 0) class="highlight-red" @endif>
                                    {{ $score->exam ? number_format($score->exam, 1) : '-' }}
                                </td>
                            @endif

                            @if(in_array('total', $columnsToShow))
                                <td @if(($score->total ?? 0) < 50 && ($score->total ?? 0) > 0) class="highlight-red" @endif>
                                    {{ $score->total ? number_format($score->total, 1) : '-' }}
                                </td>
                            @endif

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
                                    $posNum = is_numeric(preg_replace('/\D/','', $pos)) ? (int)preg_replace('/\D/','', $pos) : 0;
                                    $posC   = match($posNum) { 1=>'position-1', 2=>'position-2', 3=>'position-3', default=>'' };
                                @endphp
                                <td class="{{ $posC }}">{{ $pos }}</td>
                            @endif

                            @if(in_array('class_average', $columnsToShow)) <td>{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td> @endif
                            @if(in_array('cmin',          $columnsToShow)) <td>{{ $score->cmin ? number_format($score->cmin, 1) : '-' }}</td> @endif
                            @if(in_array('cmax',          $columnsToShow)) <td>{{ $score->cmax ? number_format($score->cmax, 1) : '-' }}</td> @endif
                        </tr>
                        @empty
                        <tr><td colspan="{{ $colCount }}" style="text-align:center;padding:6px;">No mock scores available.</td></tr>
                        @endforelse

                        {{-- ── Thin filler rows (16 min, currently 10px height) ── --}}
                        @for($i = 0; $i < $extraRows; $i++)
                        <tr class="filler">
                            <td colspan="{{ $colCount }}">&nbsp;</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- ── TOTALS BAR ── --}}
            <div class="totals-summary">
                TOTAL OBTAINED:&nbsp;{{ number_format($totals['obtained'] ?? 0, 1) }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                TOTAL OBTAINABLE:&nbsp;{{ $totals['obtainable'] ?? 0 }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                % OBTAINED:&nbsp;{{ $totals['percentage'] ?? 0 }}%
            </div>

            {{-- ── REMARKS ── --}}
            <table class="remarks-table">
                <tr>
                    <td width="40%">
                        <span class="remark-label">Class Teacher's Remark</span>
                        {{ $profile ? ($profile->classteachercomment ?? 'No comment recorded.') : 'No comment recorded.' }}
                    </td>
                    <td width="60%">
                        <span class="remark-label">Principal's Remark</span>
                        {{ $profile ? ($profile->principalscomment ?? 'No comment recorded.') : 'No comment recorded.' }}
                    </td>
                </tr>
            </table>

            {{-- ── BOTTOM STRIP: QR | Footer fields | Stamp ── --}}
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
                                        echo $ntb ? \Carbon\Carbon::parse($ntb)->format('jS F, Y') : '.....................';
                                    @endphp
                                </span>
                            </div>
                            <div class="powered-by">Powered by Qudroid Systems</div>
                        </td>

                        <td class="cell-stamp">
                            <img src="{{ $stampSrc }}" alt="School Stamp">
                        </td>
                    </tr>
                </table>
            </div>

        </div>{{-- /student-section --}}
    @endforeach
</body>
</html>
