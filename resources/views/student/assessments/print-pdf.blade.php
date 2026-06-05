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
            background: #e2e8f0;
            padding: 8mm 4mm;
            text-align: center;
        }

        /* WATERMARK - Clear and prominent */
        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 72px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.12);
            font-family: 'Arial Black', sans-serif;
            letter-spacing: 6px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
            border: 3px double rgba(220, 38, 38, 0.2);
            padding: 20px 40px;
            border-radius: 20px;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1);
        }

        /* SCHOOL NAME HEADER */
        .school-name-header {
            width: 100%;
            background: #111827;
            color: white;
            padding: 8px 12px 6px 12px;
            border: 3px double #000000;
            border-bottom: 2px solid #fbbf24;
            text-align: center;
        }

        .school-name-header .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .school-name-header .motto {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: 0.92;
            margin-top: 2px;
        }

        /* STUDENT SECTION CARD - with proper margins and strong border */
        .student-section {
            width: 100%;
            max-width: 200mm;
            margin: 0 auto 15px auto;
            page-break-after: always;
            page-break-inside: avoid;
            break-after: page;
            background: #ffffff;
            border: 3px solid #1a1a2e;
            border-radius: 4px;
            position: relative;
            text-align: left;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        .student-section:last-child {
            page-break-after: auto;
        }

        /* Padding inside card to create margins from PDF sides */
        .card-inner {
            padding: 8px 12px 12px 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-table td {
            padding: 4px 6px;
            vertical-align: middle;
        }

        /* School logo */
        .school-logo {
            width: 68px;
            height: 76px;
            border: 2px solid #1e3a5f;
            border-radius: 8px;
            background: white;
            padding: 4px;
            overflow: hidden;
            display: block;
            text-align: center;
            margin: 0 auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Photo frame */
        .photo-frame {
            width: 68px;
            height: 76px;
            border: 2px solid #1e3a5f;
            border-radius: 8px;
            background: #f1f5f9;
            padding: 0;
            overflow: hidden;
            display: block;
            margin-left: auto;
            margin-right: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .contact-info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .contact-info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .contact-label {
            font-weight: 900;
            color: #1e40af;
            white-space: nowrap;
            width: 55px;
        }

        .header-divider { height: 2px; background: #1e40af; width: 100%; margin: 4px 0 2px; }
        .header-divider2 { height: 1px; background: #94a3b8; width: 100%; margin: 1px 0 3px; }

        .report-title {
            background: #0f172a;
            color: white;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5px;
            border-radius: 4px;
            margin: 4px 0;
        }

        .student-info-bar {
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
            border: 2px solid #2aa886;
            border-radius: 8px;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 9.5px;
        }

        .info-table { width: 100%; }
        .info-table td { padding: 3px 6px; text-align: center; border: none; }
        .info-bar-label { color: #0f3b5c; font-weight: 900; font-size: 8.8px; background: #e0f2fe; padding: 2px 6px; border-radius: 20px; display: inline-block; }
        .info-bar-value { font-weight: 900; font-size: 9.5px; margin-left: 4px; }

        /* RESULT TABLE - strong borders, no overflow */
        .result-table-wrapper {
            margin: 8px 0;
            overflow-x: auto;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.2px;
            background: white;
        }
        .result-table th {
            background: #0b2b44;
            color: white;
            font-weight: 800;
            border: 1.5px solid #000000;
            padding: 5px 2px;
            text-align: center;
            font-size: 7.5px;
        }
        .result-table td {
            border: 1.5px solid #000000;
            padding: 4px 2px;
            text-align: center;
            font-size: 8px;
            background: white;
            font-weight: 600;
        }
        .result-table td.subject-name {
            text-align: left;
            font-weight: 800;
            padding-left: 8px;
            background: #fefce8;
        }

        .highlight-red { color: #dc2626; font-weight: 900; }

        /* FIXED COLUMN PROPORTIONS */
        .col-sn { width: 28px; }
        .col-admissionno { width: 65px; }
        .col-name { width: 130px; }
        .col-assessment { width: 38px; }
        .col-total { width: 38px; }
        .col-bf { width: 32px; }
        .col-cum { width: 38px; }
        .col-grade { width: 36px; }
        .col-position { width: 44px; }
        .col-class-average { width: 38px; }

        .totals-summary {
            background: #0b2b44;
            color: white;
            font-weight: 900;
            font-size: 9px;
            padding: 6px 12px;
            border: 2px solid #000;
            text-align: center;
            margin: 8px 0;
            border-radius: 30px;
        }

        /* Position styling with strong visibility */
        .position-1 { background-color: #FFD966 !important; color: #000; font-weight: 900; }
        .position-2 { background-color: #D1D5DB !important; color: #000; font-weight: 900; }
        .position-3 { background-color: #E6B17E !important; color: #000; font-weight: 900; }

        /* Attendance Box */
        .attendance-box {
            border: 2px solid #0f766e;
            border-radius: 10px;
            margin: 8px 0;
            overflow: hidden;
            background: #f0fdfa;
        }
        .attendance-box-header {
            background: #0f766e;
            color: white;
            font-weight: 900;
            padding: 5px 12px;
            font-size: 9px;
            text-transform: uppercase;
        }
        .attendance-grid {
            display: flex;
            flex-wrap: wrap;
        }
        .att-cell {
            flex: 1;
            padding: 6px 4px;
            text-align: center;
            border-right: 1px solid #ccf0e8;
            min-width: 70px;
        }
        .att-cell:last-child { border-right: none; }
        .att-label {
            font-size: 7.5px;
            font-weight: 800;
            color: #0f766e;
            text-transform: uppercase;
            display: block;
        }
        .att-value {
            font-size: 10px;
            font-weight: 900;
            display: block;
        }
        .att-warn { color: #dc2626; }
        .att-ok { color: #16a34a; }
        .att-pct-bar-wrap {
            background: #e2e8f0;
            border-radius: 20px;
            height: 6px;
            margin: 6px 8px 8px;
        }
        .att-pct-bar {
            height: 100%;
            border-radius: 20px;
            background: #0f766e;
        }
        .att-pct-bar.warning { background: #f97316; }

        /* Remarks Table with strong borders */
        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            border: 2px solid #000;
        }
        .remarks-table td {
            border: 1.5px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 9px;
            background: #fff;
        }
        .remarks-table .h6 {
            font-weight: 900;
            font-size: 9.5px;
            border-bottom: 2px solid #334155;
            display: inline-block;
            margin-bottom: 5px;
        }

        /* Bottom Strip */
        .bottom-strip {
            margin-top: 8px;
            border-top: 2px solid #94a3b8;
            background: #f8fafc;
            padding: 6px 0;
        }
        .strip-table {
            width: 100%;
            border-collapse: collapse;
        }
        .strip-table td {
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        .qr-cell img { width: 55px; height: 55px; display: block; margin: 0 auto; }
        .qr-label { font-size: 6.5px; font-weight: 700; margin-top: 2px; }
        .stamp-cell img { width: 75px; height: 75px; transform: rotate(-6deg); display: block; margin: 0 auto; }
        .sign-line {
            border-bottom: 1.5px dotted #1e293b;
            min-width: 100px;
            display: inline-block;
            margin: 0 5px;
        }
        .powered-by { font-size: 7px; color: #475569; margin-top: 4px; }

        /* Grade colors */
        .grade-A { color: #15803d; font-weight: 900; }
        .grade-B { color: #1d4ed8; font-weight: 900; }
        .grade-C { color: #b45309; font-weight: 900; }
        .grade-D { color: #e11d48; font-weight: 900; }
        .grade-F { color: #b91c1c; font-weight: 900; }

        /* Promotion badge */
        .promo-badge-pdf {
            margin: 6px 0;
            padding: 6px 12px;
            border-radius: 10px;
            border: 2px solid #000;
            font-weight: 800;
            text-align: center;
        }
        .promo-pdf-promoted { background: #dcfce7; border-color: #15803d; color: #14532d; }
        .promo-pdf-repeated { background: #fee2e2; border-color: #b91c1c; color: #7f1d1d; }
        .promo-pdf-awaiting { background: #fef9c3; border-color: #ca8a04; color: #854d0e; }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .student-section {
                box-shadow: none;
                page-break-after: always;
                break-after: page;
                margin: 0;
                border: 3px solid #000;
            }
            .watermark-text {
                color: rgba(220, 38, 38, 0.12);
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- CLEAR "NOT FOR OFFICIAL USE" WATERMARK -->
    <div class="watermark-text">NOT FOR OFFICIAL USE</div>

    @php
        function formatOrdinal($number) {
            if (!is_numeric($number) || $number <= 0) return '-';
            $last = $number % 10;
            $lastTwo = $number % 100;
            if ($lastTwo >= 11 && $lastTwo <= 13) return $number . 'th';
            return $number . match($last) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
        }

        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns = ['sn', 'name', 'total', 'cum', 'grade', 'position', 'class_average', 'attendance_percentage'];
        $columnsToShow = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;
        $showAnyAttendance = collect(['attendance_days_present','attendance_days_absent','attendance_percentage'])->contains(fn($c) => in_array($c, $columnsToShow));
    @endphp

    @foreach ($allStudentData as $studentData)
        @php
            $schoolInfo = $studentData['schoolInfo'] ?? null;
            $student = ($studentData['students'] ?? collect())->first();
            $assessments = $studentData['assessments'] ?? collect();
            $totals = $studentData['totals_summary'] ?? [];
            $attendance = $studentData['attendance_summary'] ?? [];

            $profile = null;
            if (isset($studentData['studentpp']) && $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()) {
                $profile = $studentData['studentpp']->first();
            }

            $admNo = $student->admissionNo ?? 'N/A';
            $fullName = trim(strtoupper($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
            $armName = $studentData['schoolclass']->arms->arm ?? '';
            $classVal = trim(($studentData['schoolclass']->schoolclass ?? '') . ' ' . $armName);
            $session = $metadata['session'] ?? '2025/2026';
            $term = $metadata['term'] ?? 'SECOND TERM';
            $numInClass = $studentData['numberOfStudents'] ?? '—';

            $qrData = "Student: {$fullName}\nAdm: {$admNo}\nClass: {$classVal}\nTerm: {$term}\nSession: {$session}";
            $qrCodeBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(240)->errorCorrection('H')->generate($qrData));

            $stampSrc = !empty($studentData['school_stamp_base64']) ? $studentData['school_stamp_base64'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ccircle cx="50" cy="50" r="42" fill="%23f8fafc" stroke="%233b82f6" stroke-width="3"/%3E%3Ctext x="50" y="55" text-anchor="middle" fill="%231e293b" font-size="11" font-weight="bold"%3ESTAMP%3C/text%3E%3C/svg%3E';
            $attPct = isset($attendance['attendance_percentage']) ? round($attendance['attendance_percentage'],1) : 0;
            $attWarn = $attPct < 75 && $attPct > 0;
            $attFound = $attendance['found'] ?? false;
        @endphp

        <div class="student-section">
            <div class="card-inner">

                {{-- SCHOOL HEADER --}}
                <div class="school-name-header">
                    <div class="school-full-name">{{ $schoolInfo->school_name ?? 'PREMIER ACADEMY' }}</div>
                    <div class="motto">{{ $schoolInfo->school_motto ?? 'INTEGRITY & EXCELLENCE' }}</div>
                </div>

                {{-- LOGO + CONTACT + PHOTO --}}
                <table class="header-table">
                    <tr>
                        <td width="18%" style="text-align:center;">
                            <div class="school-logo">
                                @php $logoSrc = $studentData['school_logo_base64'] ?? 'data:image/svg+xml;base64,' . base64_encode('<svg width="70" height="80" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f8f9fa" stroke="#1e3a5f" stroke-width="2"/><circle cx="50" cy="40" r="16" fill="#1e3a5f" opacity="0.7"/><text x="50" y="70" text-anchor="middle" font-size="12" fill="#1e3a5f">SCHOOL</text></svg>'); @endphp
                                <img src="{{ $logoSrc }}" alt="Logo">
                            </div>
                        </td>
                        <td style="vertical-align:top;">
                            <table class="contact-info-table">
                                <tr><td class="contact-label">Address:</td><td>{{ $schoolInfo->school_address ?? '—' }}</td></tr>
                                <tr><td class="contact-label">Phone:</td><td>{{ $schoolInfo->formatted_phones ?? '—' }}</td></tr>
                                <tr><td class="contact-label">Email:</td><td>{{ $schoolInfo->school_email ?? '—' }}</td></tr>
                                <tr><td class="contact-label">Website:</td><td>{{ $schoolInfo->school_website ?? '—' }}</td></tr>
                            </table>
                        </td>
                        <td width="20%" style="text-align:right;">
                            <div class="photo-frame">
                                @if(!empty($studentData['student_image_base64']))
                                    <img src="{{ $studentData['student_image_base64'] }}" alt="Student">
                                @else
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='30' y='65' width='40' height='25' fill='%2394a3b8'/%3E%3C/svg%3E" alt="Photo">
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="header-divider"></div>
                <div class="header-divider2"></div>

                <div class="report-title">
                    {{ strtoupper($term) }} {{ strtoupper($session) }} ACADEMIC SESSION – TERMINAL PROGRESS REPORT
                </div>

                {{-- STUDENT INFO PANEL --}}
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
                            <td><span class="info-bar-label">SCHOOL OPENS:</span> <span class="info-bar-value">{{ $schoolInfo->date_school_opened ? \Carbon\Carbon::parse($schoolInfo->date_school_opened)->format('jS M, Y') : '—' }}</span></td>
                            <td><span class="info-bar-label">CLASS SIZE:</span> <span class="info-bar-value">{{ $numInClass }}</span></td>
                            <td><span class="info-bar-label">GENDER:</span> <span class="info-bar-value">{{ $student->gender ?? '—' }}</span></td>
                        </tr>
                    </table>
                </div>

                {{-- SUBJECT TABLE --}}
                <div class="result-table-wrapper">
                    <table class="result-table">
                        <thead>
                            <tr>
                                @if(in_array('sn', $columnsToShow)) <th class="col-sn">#</th> @endif
                                @if(in_array('name', $columnsToShow)) <th class="col-name">SUBJECT</th> @endif
                                @foreach($assessments as $ass)
                                    @if(in_array($ass->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                        <th class="col-assessment">{{ $ass->name }}<br><span style="font-size:6px;">({{ $ass->max_score }})</span></th>
                                    @endif
                                @endforeach
                                @if(in_array('total', $columnsToShow)) <th class="col-total">TOTAL</th> @endif
                                @if(in_array('cum', $columnsToShow)) <th class="col-cum">CUM</th> @endif
                                @if(in_array('grade', $columnsToShow)) <th class="col-grade">GRADE</th> @endif
                                @if(in_array('position', $columnsToShow)) <th class="col-position">CLASS POS<br>(CUM)</th> @endif
                                @if(in_array('class_average', $columnsToShow)) <th class="col-class-average">AVG</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentData['scores'] as $idx => $score)
                            @php
                                $posCum = $score->position ?? null;
                                $posClass = ($posCum == 1) ? 'position-1' : (($posCum == 2) ? 'position-2' : (($posCum == 3) ? 'position-3' : ''));
                            @endphp
                            <tr>
                                @if(in_array('sn', $columnsToShow)) <td>{{ $idx+1 }}</td> @endif
                                @if(in_array('name', $columnsToShow)) <td class="subject-name">{{ $score->subject_name ?? '—' }}</td> @endif
                                @foreach($assessments as $ass)
                                    @if(in_array($ass->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                        @php $as = $score->assessment_scores->firstWhere('assessment_id', $ass->id)->score ?? 0; @endphp
                                        <td @if($as < ($ass->max_score*0.5)) class="highlight-red" @endif>{{ $as ? number_format($as,0) : '-' }}</td>
                                    @endif
                                @endforeach
                                @if(in_array('total', $columnsToShow)) <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>{{ number_format($score->total ?? 0,1) }}</td> @endif
                                @if(in_array('cum', $columnsToShow)) <td>{{ number_format($score->cum ?? 0,1) }}</td> @endif
                                @if(in_array('grade', $columnsToShow)) <td class="grade-{{ substr($score->grade ?? 'F',0,1) }}">{{ $score->grade ?? '-' }}</td> @endif
                                @if(in_array('position', $columnsToShow)) <td class="{{ $posClass }}">{{ formatOrdinal($posCum) }}</td> @endif
                                @if(in_array('class_average', $columnsToShow)) <td>{{ number_format($score->class_average ?? 0,1) }}</td> @endif
                            </tr>
                            @empty
                            <tr><td colspan="20" style="padding:12px; text-align:center;">No subject scores recorded</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TOTALS --}}
                <div class="totals-summary">
                    🎯 TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }} &nbsp;|&nbsp;
                    TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }} &nbsp;|&nbsp;
                    PERCENTAGE: {{ $totals['percentage'] ?? 0 }}%
                </div>

                {{-- PROMOTION STATUS --}}
                @php $isPromoTerm = ($term == 'THIRD TERM'); $promoStatus = ($totals['percentage'] ?? 0) >= 50 ? 'promoted' : 'repeated'; @endphp
                @if($isPromoTerm)
                    <div class="promo-badge-pdf {{ $promoStatus == 'promoted' ? 'promo-pdf-promoted' : 'promo-pdf-repeated' }}">
                        <span class="promo-pdf-label">{{ $promoStatus == 'promoted' ? '✅ PROMOTED TO NEXT CLASS' : '⚠️ NOT PROMOTED — REPEAT CLASS' }}</span>
                    </div>
                @else
                    <div class="promo-badge-pdf promo-pdf-awaiting">
                        <span class="promo-pdf-label">⏳ AWAITING FINAL TERM</span>
                    </div>
                @endif

                {{-- ATTENDANCE --}}
                @if($showAnyAttendance && $attFound)
                <div class="attendance-box">
                    <div class="attendance-box-header">📅 TERMINAL ATTENDANCE</div>
                    <div class="attendance-grid">
                        @if(in_array('attendance_days_present', $columnsToShow))<div class="att-cell"><span class="att-label">PRESENT</span><span class="att-value att-ok">{{ $attendance['days_present'] ?? 0 }}</span></div>@endif
                        @if(in_array('attendance_days_absent', $columnsToShow))<div class="att-cell"><span class="att-label">ABSENT</span><span class="att-value {{ ($attendance['days_absent'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">{{ $attendance['days_absent'] ?? 0 }}</span></div>@endif
                        @if(in_array('attendance_percentage', $columnsToShow))<div class="att-cell"><span class="att-label">ATTENDANCE %</span><span class="att-value {{ $attWarn ? 'att-warn' : 'att-ok' }}">{{ $attPct }}%</span></div>@endif
                    </div>
                    <div class="att-pct-bar-wrap"><div class="att-pct-bar {{ $attWarn ? 'warning' : '' }}" style="width:{{ min($attPct,100) }}%;"></div></div>
                </div>
                @endif

                {{-- REMARKS --}}
                <table class="remarks-table">
                    <tr>
                        <td width="50%"><div class="h6">📝 CLASS TEACHER'S REMARK</div><div>{{ $profile ? ($profile->classteachercomment ?? '—') : '—' }}</div></td>
                        <td width="50%"><div class="h6">🏫 PRINCIPAL'S REMARK</div><div>{{ $profile ? ($profile->principalscomment ?? '—') : '—' }}</div></td>
                    </tr>
                </table>

                {{-- BOTTOM STRIP (QR, SIGNATURE, STAMP) --}}
                <div class="bottom-strip">
                    <table class="strip-table">
                        <tr>
                            <td class="qr-cell" width="22%"><img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR"><div class="qr-label">Verify with portal</div></td>
                            <td width="56%" style="text-align:center;">
                                <div><strong>ISSUED:</strong> <span class="sign-line">{{ now()->format('jS F, Y') }}</span></div>
                                <div style="margin:6px 0;"><strong>PARENT/GUARDIAN SIGN:</strong> <span class="sign-line"> _________________ </span></div>
                                <div><strong>NEXT TERM BEGINS:</strong> <span class="sign-line">{{ $schoolInfo->date_next_term_begins ? \Carbon\Carbon::parse($schoolInfo->date_next_term_begins)->format('jS F, Y') : 'To be announced' }}</span></div>
                                <div class="powered-by">🔹 Powered by Qudroid Systems 🔹</div>
                            </td>
                            <td class="stamp-cell" width="22%"><img src="{{ $stampSrc }}" alt="Stamp"></td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    @endforeach
</body>
</html>
