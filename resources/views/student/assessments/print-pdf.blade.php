<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Terminal Report - {{ $allStudentData[0]['schoolInfo']->school_name ?? 'School' }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        @page { size:A4; margin:6mm 5mm; }

        body {
            font-family:'Times New Roman', Times, serif;
            font-size:9.5px;
            line-height:1.3;
            color:#000;
            background:#fff;
            padding:0;
        }

        /* WATERMARK — fixed, behind everything */
        .watermark-text {
            position:fixed;
            top:50%; left:50%;
            transform:translate(-50%,-50%) rotate(-25deg);
            font-size:60px; font-weight:900;
            color:rgba(220,38,38,0.06);
            font-family:'Arial Black',sans-serif;
            letter-spacing:5px; white-space:nowrap;
            pointer-events:none; z-index:0;
            text-transform:uppercase; width:100%; text-align:center;
        }

        /* ONE REPORT CARD PER STUDENT */
        .student-section {
            width:100%;
            background:#ffffff;
            border:3px double #000;
            position:relative;
            text-align:left;
            overflow:hidden;
            z-index:1;
            /* No fixed height — content drives height */
        }

        /* page-break only between multiple students */
        .student-section + .student-section {
            page-break-before:always;
            margin-top:0;
        }

        /* SCHOOL NAME HEADER */
        .school-name-header {
            width:100%;
            background:#111827;
            color:white;
            padding:8px 10px 5px;
            text-align:center;
            border-bottom:1px solid #1e40af;
        }
        .school-name-header .school-full-name {
            font-family:'Arial Black',sans-serif;
            font-size:18px; font-weight:900;
            letter-spacing:1.5px; text-transform:uppercase; line-height:1.05;
        }
        .school-name-header .motto {
            font-size:9px; font-weight:700; letter-spacing:2px; opacity:.95; margin-top:2px;
        }

        /* HEADER TABLE */
        .header-table { width:100%; border-collapse:collapse; }
        .header-table td { padding:6px 8px; vertical-align:middle; }

        .school-logo, .photo-frame {
            width:68px; height:80px;
            border:2px solid #47b492; border-radius:5px;
            background:white; padding:3px; overflow:hidden;
            display:block;
        }
        .school-logo img, .photo-frame img {
            width:100%; height:100%; object-fit:contain; display:block;
        }

        .middle-info { font-size:9px; line-height:1.7; }
        .middle-info strong { color:#1e40af; font-weight:700; }

        .header-divider  { height:2px; background:#1e40af; }
        .header-divider2 { height:1px; background:#64748b; margin:1px 0; }

        /* REPORT TITLE */
        .report-title {
            background:#111827; color:white;
            padding:5px 8px; font-size:10.5px; font-weight:700;
            text-align:center;
        }

        /* STUDENT INFO BAR */
        .student-info-bar {
            border:2px solid #2aa886; border-radius:5px;
            padding:5px 10px; margin:6px 8px; font-size:8.5px;
            background:#f0f7ff;
        }
        .info-table { width:100%; border-collapse:collapse; }
        .info-table td { padding:2px 5px; }
        .info-bar-label { color:#1e40af; font-weight:700; font-size:7.8px; white-space:nowrap; }
        .info-bar-value { font-weight:900; font-size:8.5px; padding-left:3px; }

        /* RESULT TABLE */
        .result-table { margin:6px 8px 0; }
        .result-table table {
            width:100%; border:2px solid #000; border-collapse:collapse; font-size:7.5px;
        }
        .result-table thead th {
            background:#0d1a3d; color:white; font-weight:800;
            border:1px solid #000; padding:3px 1px;
            font-size:6.5px; text-align:center; line-height:1.2;
        }
        .result-table tbody td {
            border:1px solid #000; padding:2px 1px;
            text-align:center; font-size:7.3px;
            background:white; font-weight:600;
            height:15px; line-height:15px;
        }
        .result-table tbody td.subject-name {
            text-align:left; font-weight:700; padding-left:5px; font-size:7.5px;
        }

        .highlight-red { color:#dc2626; font-weight:900; }

        /* Column widths — tuned for 16-subject table fitting A4 */
        .col-sn          { width:18px; }
        .col-admissionno { width:62px; }
        .col-name        { width:110px; }
        .col-assessment  { width:32px; }
        .col-total       { width:34px; }
        .col-bf          { width:26px; }
        .col-cum         { width:30px; }
        .col-grade       { width:28px; }
        .col-position    { width:28px; }
        .col-avg         { width:28px; }

        /* TOTALS SUMMARY */
        .totals-summary {
            background:#0d1a3d; color:#fff;
            font-weight:900; font-size:7.5px;
            padding:4px 10px; border:2px solid #000; border-top:none;
            text-align:center; margin:0 8px 6px;
        }

        /* REMARKS */
        .remarks-table {
            width:calc(100% - 16px); border:2px solid #000;
            border-collapse:collapse; margin:0 8px 4px;
        }
        .remarks-table td {
            border:1px solid #000; padding:5px 7px;
            background:white; vertical-align:top; font-size:8px;
        }
        .remarks-table .h6 {
            font-weight:700; margin-bottom:3px; font-size:8.5px;
            border-bottom:1px solid #ccc; display:inline-block;
        }

        /* FOOTER */
        .footer-section {
            background:#f1f5f9; padding:7px 12px 5px;
            border-top:1px solid #cbd5e1; text-align:center; margin:0 8px 6px;
        }
        .text-dot-space2 {
            border-bottom:1px dotted #333; display:inline-block;
            min-width:100px; font-weight:bold; margin:0 3px;
        }
        .powered-by { font-size:7.5px; margin-top:3px; color:#64748b; }

        /* STAMP — inline block, no absolute positioning */
        .stamp-inline {
            display:inline-block; opacity:0.12; vertical-align:middle;
            width:60px; height:60px;
        }

        /* Grade colours */
        .grade-A { color:#16a34a; font-weight:900; }
        .grade-B { color:#2563eb; font-weight:900; }
        .grade-C { color:#ca8a04; font-weight:900; }
        .grade-D { color:#ea580c; font-weight:900; }
        .grade-F { color:#dc2626; font-weight:900; }

        /* Position medal colours */
        .pos-1 { background:gold;    color:#000; font-weight:900; }
        .pos-2 { background:silver;  color:#000; font-weight:900; }
        .pos-3 { background:#cd7f32; color:#fff; font-weight:900; }

        @media print {
            body { background:white; }
            .student-section { box-shadow:none; }
        }
    </style>
</head>
<body>
    <div class="watermark-text">STUDENT COPY - NOT FOR OFFICIAL USE</div>

    @php
        /**
         * Format number with ordinal suffix (st, nd, rd, th)
         * Returns '-' for null / zero / non-numeric
         */
        function formatOrdinal($number) {
            if ($number === null || $number === '' || !is_numeric($number) || (int)$number <= 0) {
                return '-';
            }
            $n  = (int) $number;
            $ld = $n % 10;
            $lt = $n % 100;
            if ($lt >= 11 && $lt <= 13) return $n . 'th';
            return $n . match($ld) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
        }

        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns  = [
            'sn', 'admission_no', 'name',
            'total', 'bf', 'cum', 'grade',
            'position', 'position_total', 'arm_position', 'arm_position_cum',
            'class_average',
        ];
        $columnsToShow = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;

        // Colspan fallback counter
        $baseVisibleCount = 0;
        if (in_array('sn',           $columnsToShow)) $baseVisibleCount++;
        if (in_array('admission_no', $columnsToShow)) $baseVisibleCount++;
        if (in_array('name',         $columnsToShow)) $baseVisibleCount++;
    @endphp

    @foreach ($allStudentData as $index => $studentData)
        @php
            $schoolInfo  = $studentData['schoolInfo'] ?? null;
            $student     = ($studentData['students'] ?? collect())->first();
            $assessments = $studentData['assessments'] ?? collect();
            $totals      = $studentData['totals_summary'] ?? [];
            $gpaData     = $studentData['gpa_data'] ?? [];
            $scores      = $studentData['scores'] ?? collect();
            $profile     = ($studentData['studentpp'] ?? collect())->first();

            // Total visible column count for colspan
            $assessmentColumnsCount = 0;
            foreach ($assessments as $assessment) {
                if (in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow)) {
                    $assessmentColumnsCount++;
                }
            }
            $totalColCount = $baseVisibleCount + $assessmentColumnsCount;
            foreach (['total','bf','cum','grade','position','position_total','arm_position','arm_position_cum','class_average'] as $c) {
                if (in_array($c, $columnsToShow)) $totalColCount++;
            }

            $fullName     = strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? '');
            $admNo        = $student->admissionNo ?? '—';
            $classVal     = ($studentData['schoolclass']->schoolclass ?? '—') . ' ' . ($studentData['schoolclass']->arms->arm ?? '');
            $schoolOpened = $schoolInfo->date_school_opened
                ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y') : '—';
            $numInClass   = $studentData['numberOfStudents'] ?? '—';

            $phones = is_array($schoolInfo->school_phones ?? null)
                ? $schoolInfo->school_phones
                : (json_decode($schoolInfo->school_phones ?? '[]', true) ?? []);
            $formattedPhones = !empty($phones)
                ? implode(', ', $phones)
                : ($schoolInfo->school_phone ?? '—');
        @endphp

        <div class="student-section">

            {{-- SCHOOL NAME HEADER --}}
            <div class="school-name-header">
                <div class="school-full-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
                <div class="motto">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</div>
            </div>

            {{-- HEADER: Logo + Info + Photo --}}
            <table class="header-table">
                <tr>
                    <td style="width:80px; text-align:center;">
                        <div class="school-logo">
                            @php
                                $logoSrc = !empty($studentData['school_logo_base64'])
                                    ? $studentData['school_logo_base64']
                                    : 'data:image/svg+xml;base64,' . base64_encode(
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="68" height="80" viewBox="0 0 100 100">
                                         <rect width="100" height="100" fill="#f8f9fa" stroke="#47b492" stroke-width="2"/>
                                         <circle cx="50" cy="40" r="15" fill="#47b492" opacity="0.6"/>
                                         <rect x="35" y="60" width="30" height="20" fill="#47b492" opacity="0.6" rx="3"/>
                                         </svg>'
                                    );
                            @endphp
                            <img src="{{ $logoSrc }}" alt="Logo">
                        </div>
                    </td>
                    <td class="middle-info">
                        <strong>Address:</strong> {{ $schoolInfo->school_address ?? '—' }}<br>
                        <strong>Phone:</strong>   {{ $formattedPhones }}<br>
                        <strong>Email:</strong>   {{ $schoolInfo->school_email ?? '—' }}<br>
                        <strong>Website:</strong> {{ $schoolInfo->school_website ?? '—' }}
                    </td>
                    @if(in_array('picture', $columnsToShow))
                    <td style="width:84px; text-align:right; padding-right:10px;">
                        <div class="photo-frame" style="margin-left:auto;">
                            @if(!empty($studentData['student_image_base64']))
                                <img src="{{ $studentData['student_image_base64'] }}" alt="Photo">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='68' height='80' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='38' r='20' fill='%2394a3b8'/%3E%3Crect x='35' y='64' width='30' height='25' fill='%2394a3b8' rx='4'/%3E%3C/svg%3E" alt="No Photo">
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
            </table>

            <div class="header-divider"></div>
            <div class="header-divider2"></div>

            {{-- REPORT TITLE --}}
            <div class="report-title">
                {{ strtoupper($metadata['term'] ?? 'TERM') }} {{ strtoupper($metadata['session'] ?? 'SESSION') }} ACADEMIC SESSION TERMINAL PROGRESS REPORT
            </div>

            {{-- STUDENT INFO BAR --}}
            <div class="student-info-bar">
                <table class="info-table">
                    <tr>
                        <td><span class="info-bar-label">NAME:</span><span class="info-bar-value">{{ $fullName }}</span></td>
                        <td><span class="info-bar-label">SESSION:</span><span class="info-bar-value">{{ $metadata['session'] ?? '—' }}</span></td>
                        <td><span class="info-bar-label">TERM:</span><span class="info-bar-value">{{ $metadata['term'] ?? '—' }}</span></td>
                        <td><span class="info-bar-label">CLASS:</span><span class="info-bar-value">{{ $classVal }}</span></td>
                        <td><span class="info-bar-label">ADM NO:</span><span class="info-bar-value">{{ $admNo }}</span></td>
                    </tr>
                    <tr>
                        <td><span class="info-bar-label">SCHOOL OPENED:</span><span class="info-bar-value">{{ $schoolOpened }}</span></td>
                        <td><span class="info-bar-label">NO. IN CLASS:</span><span class="info-bar-value">{{ $numInClass }}</span></td>
                        @if(in_array('gender', $columnsToShow))
                        <td><span class="info-bar-label">SEX:</span><span class="info-bar-value">{{ $student->gender ?? '—' }}</span></td>
                        @endif
                        <td><span class="info-bar-label">REPORT DATE:</span><span class="info-bar-value">{{ date('jS M, Y') }}</span></td>
                        <td>
                            <span class="info-bar-label">GPA:</span><span class="info-bar-value">{{ $gpaData['gpa'] ?? '-' }}</span>
                            &nbsp;
                            <span class="info-bar-label">CGPA:</span><span class="info-bar-value">{{ $gpaData['cgpa'] ?? '-' }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- RESULT TABLE --}}
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
                                        <span style="font-size:5px;">({{ $assessment->max_score }})</span>
                                    </th>
                                @endif
                            @endforeach

                            @if(in_array('total',    $columnsToShow)) <th class="col-total">Total</th> @endif
                            @if(in_array('bf',       $columnsToShow)) <th class="col-bf">BF</th> @endif
                            @if(in_array('cum',      $columnsToShow)) <th class="col-cum">Cum</th> @endif
                            @if(in_array('grade',    $columnsToShow)) <th class="col-grade">Grade</th> @endif

                            {{-- ── Four position columns ── --}}
                            @if(in_array('position', $columnsToShow))
                                <th class="col-position" title="All arms — ranked by cumulative average">
                                    Class Pos<br><span style="font-size:4.5px;">(Cum)</span>
                                </th>
                            @endif
                            @if(in_array('position_total', $columnsToShow))
                                <th class="col-position" title="All arms — ranked by term total">
                                    Class Pos<br><span style="font-size:4.5px;">(Total)</span>
                                </th>
                            @endif
                            @if(in_array('arm_position', $columnsToShow))
                                <th class="col-position" title="This arm only — ranked by term total">
                                    Arm Pos<br><span style="font-size:4.5px;">(Total)</span>
                                </th>
                            @endif
                            @if(in_array('arm_position_cum', $columnsToShow))
                                <th class="col-position" title="This arm only — ranked by cumulative average">
                                    Arm Pos<br><span style="font-size:4.5px;">(Cum)</span>
                                </th>
                            @endif

                            @if(in_array('class_average', $columnsToShow)) <th class="col-avg">Av</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scores as $si => $score)
                        @php
                            $gradeRaw   = $score->grade ?? '-';
                            $gradeUpper = strtoupper($gradeRaw);
                            $gradeClass = match(true) {
                                str_starts_with($gradeUpper,'A') => 'grade-A',
                                str_starts_with($gradeUpper,'B') => 'grade-B',
                                str_starts_with($gradeUpper,'C') => 'grade-C',
                                str_starts_with($gradeUpper,'D') => 'grade-D',
                                default                          => 'grade-F',
                            };

                            /*
                             * POSITION RESOLUTION
                             * ───────────────────
                             * The controller stores raw integers in DB.
                             * getStudentResultData() adds _formatted versions via formatOrdinal().
                             * We prefer the formatted string if present; fall back to raw value.
                             *
                             * IMPORTANT: if cum == 0 (BF=0 and it's Term 1 — no previous term),
                             * the controller skips CUM-based positions.
                             * In that case we fall back to the TOTAL-based position so the cell
                             * is never blank when a total position actually exists.
                             */

                            // Raw integers from DB
                            $rawPos       = $score->position         ?? null;   // class pos by cum
                            $rawPosTotal  = $score->position_total   ?? null;   // class pos by total
                            $rawArmPos    = $score->arm_position     ?? null;   // arm pos by total
                            $rawArmPosCum = $score->arm_position_cum ?? null;   // arm pos by cum

                            // BF=0 means Term 1 / no carry-forward — use total-based position as
                            // the displayed "Class Pos (Cum)" when the cum-based one is null.
                            $bf = (float)($score->bf ?? 0);
                            if ($bf == 0 && $rawPos === null && $rawPosTotal !== null) {
                                $rawPos = $rawPosTotal;
                            }
                            if ($bf == 0 && $rawArmPosCum === null && $rawArmPos !== null) {
                                $rawArmPosCum = $rawArmPos;
                            }

                            // Formatted strings (set by getStudentResultData)
                            $dispPos       = ($score->position_formatted         ?? null) ?: formatOrdinal($rawPos);
                            $dispPosTotal  = ($score->position_total_formatted   ?? null) ?: formatOrdinal($rawPosTotal);
                            $dispArmPos    = ($score->arm_position_formatted     ?? null) ?: formatOrdinal($rawArmPos);
                            $dispArmPosCum = ($score->arm_position_cum_formatted ?? null) ?: formatOrdinal($rawArmPosCum);

                            // Medal CSS class
                            $medalPos       = match((int)($rawPos       ?? 0)) { 1=>'pos-1', 2=>'pos-2', 3=>'pos-3', default=>'' };
                            $medalPosTotal  = match((int)($rawPosTotal  ?? 0)) { 1=>'pos-1', 2=>'pos-2', 3=>'pos-3', default=>'' };
                            $medalArmPos    = match((int)($rawArmPos    ?? 0)) { 1=>'pos-1', 2=>'pos-2', 3=>'pos-3', default=>'' };
                            $medalArmPosCum = match((int)($rawArmPosCum ?? 0)) { 1=>'pos-1', 2=>'pos-2', 3=>'pos-3', default=>'' };
                        @endphp
                        <tr>
                            @if(in_array('sn',           $columnsToShow)) <td>{{ $si + 1 }}</td> @endif
                            @if(in_array('admission_no', $columnsToShow)) <td>{{ $student->admissionNo ?? '-' }}</td> @endif
                            @if(in_array('name',         $columnsToShow)) <td class="subject-name">{{ $score->subject_name ?? '—' }}</td> @endif

                            @foreach ($assessments as $assessment)
                                @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    @php
                                        $aScore = 0;
                                        if (isset($score->assessment_scores)) {
                                            $found  = $score->assessment_scores->firstWhere('assessment_id', $assessment->id);
                                            $aScore = $found ? $found->score : 0;
                                        }
                                    @endphp
                                    <td @if($aScore < ($assessment->max_score * 0.5) && is_numeric($aScore)) class="highlight-red" @endif>
                                        {{ $aScore ? number_format($aScore, 0) : '-' }}
                                    </td>
                                @endif
                            @endforeach

                            @if(in_array('total', $columnsToShow))
                                <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>
                                    {{ isset($score->total) ? number_format($score->total, 1) : '-' }}
                                </td>
                            @endif
                            @if(in_array('bf', $columnsToShow))
                                <td>{{ isset($score->bf)  ? number_format($score->bf,  1) : '-' }}</td>
                            @endif
                            @if(in_array('cum', $columnsToShow))
                                <td>{{ isset($score->cum) ? number_format($score->cum, 1) : '-' }}</td>
                            @endif
                            @if(in_array('grade', $columnsToShow))
                                <td class="{{ $gradeClass }}">{{ $gradeRaw }}</td>
                            @endif

                            @if(in_array('position',         $columnsToShow)) <td class="{{ $medalPos       }}">{{ $dispPos       }}</td> @endif
                            @if(in_array('position_total',   $columnsToShow)) <td class="{{ $medalPosTotal  }}">{{ $dispPosTotal  }}</td> @endif
                            @if(in_array('arm_position',     $columnsToShow)) <td class="{{ $medalArmPos    }}">{{ $dispArmPos    }}</td> @endif
                            @if(in_array('arm_position_cum', $columnsToShow)) <td class="{{ $medalArmPosCum }}">{{ $dispArmPosCum }}</td> @endif

                            @if(in_array('class_average', $columnsToShow))
                                <td>{{ isset($score->class_average) ? number_format($score->class_average, 1) : '-' }}</td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $totalColCount }}" style="text-align:center;padding:8px;">No scores available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TOTALS SUMMARY --}}
            <div class="totals-summary">
                TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }}
                &nbsp;|&nbsp; TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }}
                &nbsp;|&nbsp; PERCENTAGE: {{ number_format($totals['percentage'] ?? 0, 1) }}%
                &nbsp;|&nbsp; GPA: {{ $gpaData['gpa'] ?? 0 }}
                &nbsp;|&nbsp; CGPA: {{ $gpaData['cgpa'] ?? 0 }}
            </div>

            {{-- REMARKS --}}
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Remark</div>
                            <div>{{ $profile->classteachercomment ?? 'Performed satisfactorily. Keep improving.' }}</div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark</div>
                            <div>{{ $profile->principalscomment ?? 'Approved. Continue with good work.' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- FOOTER --}}
            <div class="footer-section">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        {{-- Stamp — inline, no absolute positioning --}}
                        <td style="width:70px; text-align:center; vertical-align:middle; padding:4px;">
                            @if(!empty($studentData['school_stamp_base64']))
                                <img src="{{ $studentData['school_stamp_base64'] }}" alt="Stamp"
                                     style="width:60px;height:60px;opacity:0.35;display:block;margin:0 auto;">
                            @else
                                <svg class="stamp-inline" viewBox="0 0 100 100" fill="none">
                                    <circle cx="50" cy="50" r="44" stroke="#8B0000" stroke-width="2.5" stroke-dasharray="5 4"/>
                                    <text x="50" y="38" text-anchor="middle" fill="#8B0000" font-size="9" font-weight="bold">STUDENT</text>
                                    <text x="50" y="52" text-anchor="middle" fill="#8B0000" font-size="9">COPY</text>
                                    <text x="50" y="66" text-anchor="middle" fill="#8B0000" font-size="8">NOT VALID</text>
                                </svg>
                            @endif
                        </td>
                        <td style="text-align:center; vertical-align:middle; font-size:8px; padding:4px 8px;">
                            <div style="margin-bottom:4px;">
                                <strong>Issued:</strong>
                                <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span>
                                <strong style="margin-left:16px;">Received by:</strong>
                                <span class="text-dot-space2">.......................................</span>
                            </div>
                            <div>
                                <strong>Next Term Begins:</strong>
                                @php
                                    $ntb = $schoolInfo->date_next_term_begins ?? null;
                                    echo $ntb
                                        ? \Carbon\Carbon::parse($ntb)->format('jS F, Y')
                                        : '........................';
                                @endphp
                            </div>
                            <div class="powered-by">Powered by School Qudroid Systems | Viteschool</div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>{{-- end .student-section --}}
    @endforeach
</body>
</html>
Done

