<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Terminal Progress Report | Student Copy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #e2e8f0;
            padding: 8mm 5mm;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* WATERMARK */
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
            padding: 12px 32px;
            border-radius: 24px;
        }

        .student-section {
            max-width: 200mm;
            width: 100%;
            margin: 0 auto;
            background: white;
            border: 3px solid #0f172a;
            border-radius: 6px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            page-break-after: always;
            break-inside: avoid;
            display: flex;
            flex-direction: column;
        }

        .card-inner {
            padding: 12px 14px 14px 14px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        /* SCHOOL HEADER */
        .school-name-header {
            background: #111827;
            color: white;
            padding: 10px 14px 8px 14px;
            border: 2px solid #fbbf24;
            border-left: none;
            border-right: none;
            text-align: center;
            margin-bottom: 4px;
        }
        .school-full-name {
            font-family: 'Arial Black', sans-serif;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .motto {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 3px;
            opacity: 0.95;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 4px;
        }
        .school-logo {
            width: 72px;
            height: 80px;
            border: 2px solid #2c7a4d;
            border-radius: 10px;
            background: white;
            padding: 4px;
            text-align: center;
            margin: 0 auto;
        }
        .school-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .photo-frame {
            width: 72px;
            height: 80px;
            border: 2px solid #2c7a4d;
            border-radius: 10px;
            overflow: hidden;
            margin-left: auto;
            background: #f1f5f9;
        }
        .photo-frame img { width: 100%; height: 100%; object-fit: cover; }
        .contact-info { width: 100%; font-size: 9.5px; border-collapse: collapse; }
        .contact-info td { padding: 3px 4px; }
        .contact-label { font-weight: 900; color: #1e40af; width: 58px; }

        .divider-dark { height: 2px; background: #1e40af; margin: 5px 0 2px; }
        .divider-light { height: 1px; background: #94a3b8; margin: 2px 0 4px; }

        .report-title {
            background: #0f172a;
            color: white;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            padding: 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .student-info-bar {
            background: linear-gradient(135deg, #eff6ff, #ffffff);
            border: 1.8px solid #2aa886;
            border-radius: 10px;
            padding: 8px 12px;
            margin: 4px 0;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 4px 6px;
            text-align: center;
            font-size: 10px;
        }
        .info-badge {
            background: #e0f2fe;
            padding: 3px 10px;
            border-radius: 30px;
            font-weight: 900;
            color: #0f3b5c;
            display: inline-block;
        }
        .info-value {
            font-weight: 900;
            margin-left: 5px;
            font-size: 10.5px;
        }

        .result-wrapper {
            margin: 8px 0;
            border: 1.5px solid #94a3b8;
            border-radius: 6px;
            overflow-x: auto;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.8px;
        }
        .result-table th {
            background: #0b2b44;
            color: white;
            border: 1.2px solid #000;
            padding: 6px 2px;
            font-size: 8px;
            text-align: center;
            font-weight: 800;
        }
        .result-table td {
            border: 1.2px solid #000;
            padding: 5px 2px;
            text-align: center;
            font-size: 8.8px;
            background: white;
            font-weight: 600;
        }
        .subject-name-cell {
            text-align: left;
            font-weight: 800;
            padding-left: 8px;
            background: #fef9e3;
        }
        .highlight-red { color: #dc2626; font-weight: 900; }

        .grade-A { color: #15803d; font-weight: 900; }
        .grade-B { color: #1d4ed8; font-weight: 900; }
        .grade-C { color: #b45309; font-weight: 900; }
        .grade-D { color: #e11d48; font-weight: 900; }
        .grade-F { color: #b91c1c; font-weight: 900; }

        .pos-1 { background-color: #FFD966 !important; color: #000; font-weight: 900; }
        .pos-2 { background-color: #D1D5DB !important; color: #000; font-weight: 900; }
        .pos-3 { background-color: #E6B17E !important; color: #000; font-weight: 900; }

        .totals-summary {
            background: #0b2b44;
            color: white;
            font-weight: 900;
            font-size: 10px;
            text-align: center;
            padding: 7px;
            border-radius: 40px;
            margin: 5px 0;
        }

        /* ─── PROMOTION BADGE ─── */
        .promo-badge {
            margin: 4px 0;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
            text-align: center;
            font-size: 10px;
            border: 1.5px solid;
            line-height: 1.5;
        }
        .promo-pass {
            background: #dcfce7;
            border-color: #15803d;
            color: #14532d;
        }
        .promo-fail {
            background: #fee2e2;
            border-color: #b91c1c;
            color: #7f1d1d;
        }
        .promo-trial {
            background: #fef9c3;
            border-color: #ca8a04;
            color: #854d0e;
        }
        .promo-principal {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e3a8a;
        }
        .promo-wait {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #475569;
        }
        .promo-badge .promo-rule {
            font-size: 8.5px;
            font-weight: 600;
            opacity: 0.8;
            margin-top: 2px;
        }
        .promo-badge .promo-detail {
            font-size: 8.5px;
            font-weight: 500;
            margin-top: 2px;
            opacity: 0.9;
        }

        .attendance-box {
            border: 1.8px solid #0f766e;
            border-radius: 12px;
            margin: 5px 0;
            background: #f0fdfa;
        }
        .attendance-header {
            background: #0f766e;
            color: white;
            font-weight: 900;
            padding: 6px;
            font-size: 9.5px;
            text-align: center;
        }
        .att-flex {
            display: flex;
            flex-wrap: wrap;
        }
        .att-cell {
            flex: 1;
            text-align: center;
            padding: 6px 2px;
            border-right: 1px solid #cbd5e1;
        }
        .att-cell:last-child { border-right: none; }
        .att-label {
            font-size: 8px;
            font-weight: 800;
            color: #0f766e;
            text-transform: uppercase;
        }
        .att-value {
            font-size: 11px;
            font-weight: 900;
        }
        .att-warn { color: #dc2626; }
        .att-ok { color: #16a34a; }
        .progress-bar {
            background: #e2e8f0;
            border-radius: 20px;
            height: 6px;
            margin: 6px 10px 8px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 20px;
            background: #0f766e;
        }
        .progress-fill.warning { background: #f97316; }

        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            border: 2px solid #000;
        }
        .remarks-table td {
            border: 1.2px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 9px;
            background: white;
        }
        .remark-title {
            font-weight: 900;
            font-size: 9.5px;
            border-bottom: 1.5px solid #94a3b8;
            display: inline-block;
            margin-bottom: 5px;
        }

        .bottom-strip {
            margin-top: 5px;
            border-top: 1.8px solid #cbd5e1;
            background: #f8fafc;
            padding: 6px 0 2px;
        }
        .strip-table {
            width: 100%;
        }
        .strip-table td {
            padding: 5px 4px;
            vertical-align: middle;
            text-align: center;
        }
        .qr-img {
            width: 55px;
            height: 55px;
            display: block;
            margin: 0 auto;
        }
        .qr-label {
            font-size: 7px;
            font-weight: 700;
            margin-top: 2px;
        }
        .stamp-img {
            width: 75px;
            transform: rotate(-6deg);
        }
        .sign-line {
            border-bottom: 1.5px dotted #1e293b;
            min-width: 100px;
            display: inline-block;
            margin: 0 6px;
        }
        .powered {
            font-size: 7px;
            color: #475569;
            margin-top: 4px;
        }

        .col-sn { width: 28px; }
        .col-adm { width: 65px; }
        .col-subj { width: 135px; }
        .col-assess { width: 38px; }
        .col-num { width: 42px; }
        .col-pos { width: 48px; }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .student-section { box-shadow: none; margin: 0; page-break-after: avoid; }
            .watermark-text { color: rgba(220,38,38,0.1); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="watermark-text">STUDENT COPY</div>

    @php
        function ordinal($n) {
            if (!is_numeric($n) || $n <= 0) return '-';
            if ($n % 100 >= 11 && $n % 100 <= 13) return $n.'th';
            return $n.match($n % 10) {1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'};
        }

        $selected = $metadata['selected_columns'] ?? [];
        $defaultCols = ['sn','admission_no','name','total','bf','cum','grade','position','position_total','arm_position','arm_position_cum','class_average','attendance_days_present','attendance_days_absent','attendance_percentage'];
        $showCols = !empty($selected) ? $selected : $defaultCols;
        $showAttendance = collect(['attendance_days_present','attendance_days_absent','attendance_total_days','attendance_percentage'])->contains(fn($c)=>in_array($c,$showCols));
    @endphp

    @foreach ($allStudentData as $studentData)
        @php
            $school = $studentData['schoolInfo'] ?? null;
            $student = ($studentData['students'] ?? collect())->first();
            $assessments = $studentData['assessments'] ?? collect();
            $totals = $studentData['totals_summary'] ?? [];
            $att = $studentData['attendance_summary'] ?? [];

            // ── PROMOTION DATA (from evaluator) ──
            $pr = $studentData['promotion_result'] ?? [];
            $promoStatus = $pr['status'] ?? 'awaiting';
            $statusLabel = $pr['status_label'] ?? 'Awaiting Decision';
            $promoFailed = $pr['failed_compulsory'] ?? [];
            $reqAvg = $pr['required_average'] ?? null;
            $actAvg = $pr['actual_average'] ?? null;
            $promoTotal = $pr['compulsory_count'] ?? 0;
            $promoPassed = $pr['passed_compulsory'] ?? 0;
            $appliedRule = $pr['applied_rule']['name'] ?? null;
            $isPromoTerm = $pr['is_promotional_term'] ?? false;
            // Clean rule display
            $ruleDisplay = '';
            if ($appliedRule) {
                $ruleDisplay = preg_replace('/^Rule\s+\d+\s*[-:.]?\s*/i', '', $appliedRule);
                $ruleDisplay = trim($ruleDisplay);
                if (empty($ruleDisplay) || $ruleDisplay === 'null') {
                    $ruleDisplay = '';
                }
            }

            $profile = null;
            if (isset($studentData['studentpp']) && $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty()) {
                $profile = $studentData['studentpp']->first();
            }

            $admNo = $student->admissionNo ?? 'N/A';
            $fullName = trim(strtoupper($student->lastname ?? '').' '.($student->fname ?? '').' '.($student->othername ?? ''));
            $armName = $studentData['schoolclass']->arms->arm ?? '';
            $className = trim(($studentData['schoolclass']->schoolclass ?? '').' '.$armName);
            $session = $metadata['session'] ?? '2025/2026';
            $term = $metadata['term'] ?? 'SECOND TERM';
            $classSize = $studentData['numberOfStudents'] ?? '—';
            $schoolOpened = $school->date_school_opened ? \Carbon\Carbon::parse($school->date_school_opened)->format('jS M, Y') : '—';

            $qrData = "Student: {$fullName}\nAdm: {$admNo}\nClass: {$className}\nTerm: {$term}\nSession: {$session}";
            $qrBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(220)->errorCorrection('H')->generate($qrData));

            $stampSrc = !empty($studentData['school_stamp_base64']) ? $studentData['school_stamp_base64'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ccircle cx="50" cy="50" r="42" fill="%23f8fafc" stroke="%233b82f6" stroke-width="3"/%3E%3Ctext x="50" y="55" text-anchor="middle" fill="%231e293b" font-size="11"%3ESTAMP%3C/text%3E%3C/svg%3E';
            $logoBase = $studentData['school_logo_base64'] ?? 'data:image/svg+xml;base64,'.base64_encode('<svg width="80" height="90" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f8f9fa" stroke="#2c7a4d" stroke-width="2"/><circle cx="50" cy="40" r="18" fill="#2c7a4d" opacity="0.7"/><text x="50" y="70" text-anchor="middle" font-size="12" fill="#1e3a5f">SCH</text></svg>');

            $attPct = isset($att['attendance_percentage']) ? round($att['attendance_percentage'],1) : 0;
            $attWarn = $attPct < 75 && $attPct > 0;
            $attFound = $att['found'] ?? false;

            // Determine promo badge class
            $promoClass = 'promo-wait';
            if ($isPromoTerm) {
                switch ($promoStatus) {
                    case 'promoted':
                        $promoClass = 'promo-pass';
                        break;
                    case 'trial':
                        $promoClass = 'promo-trial';
                        break;
                    case 'see_principal':
                        $promoClass = 'promo-principal';
                        break;
                    case 'repeated':
                    case 'repeat':
                        $promoClass = 'promo-fail';
                        break;
                    default:
                        $promoClass = 'promo-wait';
                }
            }
        @endphp

        <div class="student-section">
            <div class="card-inner">
                {{-- SCHOOL HEADER --}}
                <div class="school-name-header">
                    <div class="school-full-name">{{ $school->school_name ?? 'PREMIER ACADEMY' }}</div>
                    <div class="motto">{{ $school->school_motto ?? 'KNOWLEDGE & INTEGRITY' }}</div>
                </div>

                {{-- LOGO + CONTACT + PHOTO --}}
                <table class="header-table">
                    <tr>
                        <td width="18%" style="text-align:center"><div class="school-logo"><img src="{{ $logoBase }}" alt="logo"></div></td>
                        <td>
                            <table class="contact-info">
                                <tr><td class="contact-label">Address:</td><td>{{ $school->school_address ?? '—' }}</td></tr>
                                <tr><td class="contact-label">Phone:</td><td>{{ $school->formatted_phones ?? '—' }}</td></tr>
                                <tr><td class="contact-label">Email:</td><td>{{ $school->school_email ?? '—' }}</td></tr>
                                <tr><td class="contact-label">Website:</td><td>{{ $school->school_website ?? '—' }}</td></tr>
                            </table>
                        </td>
                        <td width="20%" style="text-align:right">
                            <div class="photo-frame">
                                @if(!empty($studentData['student_image_base64']))
                                    <img src="{{ $studentData['student_image_base64'] }}" alt="student">
                                @else
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e2e8f0'/%3E%3Ccircle cx='50' cy='40' r='20' fill='%2394a3b8'/%3E%3Crect x='30' y='65' width='40' height='25' fill='%2394a3b8'/%3E%3C/svg%3E" alt="photo">
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="divider-dark"></div>
                <div class="divider-light"></div>

                <div class="report-title">{{ strtoupper($term) }} {{ strtoupper($session) }} – TERMINAL PROGRESS REPORT</div>

                {{-- STUDENT INFO BAR --}}
                <div class="student-info-bar">
                    <table class="info-table">
                        <tr>
                            <td><span class="info-badge">NAME</span> <span class="info-value">{{ $fullName }}</span></td>
                            <td><span class="info-badge">SESSION</span> <span class="info-value">{{ $session }}</span></td>
                            <td><span class="info-badge">TERM</span> <span class="info-value">{{ $term }}</span></td>
                            <td><span class="info-badge">CLASS</span> <span class="info-value">{{ $className }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-badge">ADM NO</span> <span class="info-value">{{ $admNo }}</span></td>
                            <td><span class="info-badge">SCHOOL OPENED</span> <span class="info-value">{{ $schoolOpened }}</span></td>
                            <td><span class="info-badge">CLASS SIZE</span> <span class="info-value">{{ $classSize }}</span></td>
                            @if(in_array('gender', $showCols))
                            <td><span class="info-badge">SEX</span> <span class="info-value">{{ $student->gender ?? '—' }}</span></td>
                            @endif
                        </tr>
                    </table>
                </div>

                {{-- SUBJECT TABLE --}}
                <div class="result-wrapper">
                    <table class="result-table">
                        <thead>
                            <tr>
                                @if(in_array('sn', $showCols)) <th class="col-sn">#</th> @endif
                                @if(in_array('admission_no', $showCols)) <th class="col-adm">Adm No</th> @endif
                                @if(in_array('name', $showCols)) <th class="col-subj">SUBJECT</th> @endif
                                @foreach($assessments as $ass)
                                    @if(in_array($ass->id, $showCols) || in_array('all_assessments', $showCols))
                                        <th class="col-assess">{{ $ass->name }}<br><span style="font-size:6px;">({{ $ass->max_score }})</span></th>
                                    @endif
                                @endforeach
                                @if(in_array('total', $showCols)) <th class="col-num">Total</th> @endif
                                @if(in_array('bf', $showCols)) <th class="col-num">BF</th> @endif
                                @if(in_array('cum', $showCols)) <th class="col-num">Cum</th> @endif
                                @if(in_array('grade', $showCols)) <th class="col-num">Grade</th> @endif
                                @if(in_array('position', $showCols)) <th class="col-pos">Pos(Cum)</th> @endif
                                @if(in_array('position_total', $showCols)) <th class="col-pos">Pos(Tot)</th> @endif
                                @if(in_array('arm_position', $showCols)) <th class="col-pos">Arm Pos</th> @endif
                                @if(in_array('arm_position_cum', $showCols)) <th class="col-pos">Arm Cum</th> @endif
                                @if(in_array('class_average', $showCols)) <th class="col-num">Avg</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentData['scores'] as $idx => $sc)
                            @php
                                $posCum = $sc->position ?? $sc->subject_position_class ?? null;
                                $posTot = $sc->position_total ?? $sc->subject_position_class_total ?? null;
                                $armPos = $sc->arm_position ?? null;
                                $armCum = $sc->arm_position_cum ?? null;
                                $posCumClass = ($posCum == 1) ? 'pos-1' : (($posCum == 2) ? 'pos-2' : (($posCum == 3) ? 'pos-3' : ''));
                                $posTotClass = ($posTot == 1) ? 'pos-1' : (($posTot == 2) ? 'pos-2' : (($posTot == 3) ? 'pos-3' : ''));
                                $armPosClass = ($armPos == 1) ? 'pos-1' : (($armPos == 2) ? 'pos-2' : (($armPos == 3) ? 'pos-3' : ''));
                                $armCumClass = ($armCum == 1) ? 'pos-1' : (($armCum == 2) ? 'pos-2' : (($armCum == 3) ? 'pos-3' : ''));
                                $gRaw = $sc->grade ?? '-';
                                $gLetter = ($gRaw !== '-') ? substr($gRaw,0,1) : 'F';
                                $gradeStyle = match($gLetter) { 'A'=>'grade-A','B'=>'grade-B','C'=>'grade-C','D'=>'grade-D', default=>'grade-F' };
                            @endphp
                            <tr>
                                @if(in_array('sn', $showCols)) <td>{{ $idx+1 }}</td> @endif
                                @if(in_array('admission_no', $showCols)) <td>{{ $admNo }}</td> @endif
                                @if(in_array('name', $showCols)) <td class="subject-name-cell">{{ $sc->subject_name ?? '—' }}</td> @endif

                                @foreach($assessments as $ass)
                                    @if(in_array($ass->id, $showCols) || in_array('all_assessments', $showCols))
                                        @php
                                            $aScore = $sc->assessment_scores->firstWhere('assessment_id', $ass->id)->score ?? null;
                                            $low = is_numeric($aScore) && $aScore < ($ass->max_score * 0.5);
                                        @endphp
                                        {{--
                                            FIX: previously number_format($aScore, 0) rounded the displayed
                                            value to a whole number, and `$aScore ? ... : '-'` treated a
                                            genuine 0 score as falsy and showed '-' instead of 0.0. Both
                                            broke the visual "these rows sum to the Total" check. Matches
                                            the precision fix already applied to the class results PDF.
                                        --}}
                                        <td @if($low) class="highlight-red" @endif>{{ is_numeric($aScore) ? number_format($aScore, 1) : '-' }}</td>
                                    @endif
                                @endforeach

                                @if(in_array('total', $showCols)) <td @if(($sc->total ?? 0) < 50) class="highlight-red" @endif>{{ number_format($sc->total ?? 0,1) }}</td> @endif
                                @if(in_array('bf', $showCols)) <td>{{ number_format($sc->bf ?? 0,1) }}</td> @endif
                                @if(in_array('cum', $showCols)) <td>{{ number_format($sc->cum ?? 0,1) }}</td> @endif
                                @if(in_array('grade', $showCols)) <td class="{{ $gradeStyle }}">{{ $gRaw }}</td> @endif
                                @if(in_array('position', $showCols)) <td class="{{ $posCumClass }}">{{ ordinal($posCum) }}</td> @endif
                                @if(in_array('position_total', $showCols)) <td class="{{ $posTotClass }}">{{ ordinal($posTot) }}</td> @endif
                                @if(in_array('arm_position', $showCols)) <td class="{{ $armPosClass }}">{{ ordinal($armPos) }}</td> @endif
                                @if(in_array('arm_position_cum', $showCols)) <td class="{{ $armCumClass }}">{{ ordinal($armCum) }}</td> @endif
                                @if(in_array('class_average', $showCols)) <td>{{ number_format($sc->class_average ?? 0,1) }}</td> @endif
                            </tr>
                            @empty
                            <tr><td colspan="20" style="text-align:center; padding:16px;">📌 No subject scores recorded</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TOTALS SUMMARY --}}
                <div class="totals-summary">
                    🎯 TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0,1) }} &nbsp;|&nbsp;
                    TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }} &nbsp;|&nbsp;
                    PERCENTAGE: {{ $totals['percentage'] ?? 0 }}%
                </div>

                {{-- ═══════════════════════════════════════════════════════
                     PROMOTION BADGE – FIXED: uses real evaluator data
                ═══════════════════════════════════════════════════════ --}}
                @if($isPromoTerm)
                    @if($promoStatus === 'promoted')
                        <div class="promo-badge promo-pass">
                            <strong>✅ {{ $statusLabel }}</strong>
                            @if($ruleDisplay && $ruleDisplay !== '' && $ruleDisplay !== 'null')
                                <div class="promo-rule">📋 {{ $ruleDisplay }}</div>
                            @endif
                            @if($promoTotal > 0)
                                <div class="promo-detail">
                                    Passed {{ $promoPassed }}/{{ $promoTotal }} compulsory subject(s)
                                </div>
                            @endif
                            @if($reqAvg !== null && $actAvg !== null)
                                <div class="promo-detail">
                                    Average: {{ number_format($actAvg, 1) }}%
                                    (Required: {{ number_format($reqAvg, 1) }}%) ✓
                                </div>
                            @endif
                        </div>
                    @elseif($promoStatus === 'trial')
                        <div class="promo-badge promo-trial">
                            <strong>⏳ {{ $statusLabel }}</strong>
                            @if($ruleDisplay && $ruleDisplay !== '' && $ruleDisplay !== 'null')
                                <div class="promo-rule">📋 {{ $ruleDisplay }}</div>
                            @endif
                            <div class="promo-detail">Promoted conditionally – needs improvement</div>
                            @if($reqAvg !== null && $actAvg !== null)
                                <div class="promo-detail">
                                    Average: {{ number_format($actAvg, 1) }}%
                                    (Required: {{ number_format($reqAvg, 1) }}%)
                                </div>
                            @endif
                        </div>
                    @elseif($promoStatus === 'see_principal')
                        <div class="promo-badge promo-principal">
                            <strong>🏛️ {{ $statusLabel }}</strong>
                            @if($ruleDisplay && $ruleDisplay !== '' && $ruleDisplay !== 'null')
                                <div class="promo-rule">📋 {{ $ruleDisplay }}</div>
                            @endif
                            <div class="promo-detail">Parents must see the Principal for discussion</div>
                            @if($reqAvg !== null && $actAvg !== null)
                                <div class="promo-detail">
                                    Average: {{ number_format($actAvg, 1) }}%
                                    (Required: {{ number_format($reqAvg, 1) }}%)
                                </div>
                            @endif
                        </div>
                    @elseif($promoStatus === 'repeated' || $promoStatus === 'repeat')
                        <div class="promo-badge promo-fail">
                            <strong>⚠️ {{ $statusLabel }}</strong>
                            @if(!empty($promoFailed))
                                <div class="promo-detail">
                                    Failed: {{ collect($promoFailed)->pluck('subject')->filter()->implode(', ') }}
                                </div>
                            @endif
                            @if($ruleDisplay && $ruleDisplay !== '' && $ruleDisplay !== 'null')
                                <div class="promo-rule">📋 {{ $ruleDisplay }}</div>
                            @endif
                            @if($reqAvg !== null && $actAvg !== null)
                                <div class="promo-detail">
                                    Average: {{ number_format($actAvg, 1) }}%
                                    (Required: {{ number_format($reqAvg, 1) }}%)
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="promo-badge promo-wait">
                            <strong>⏳ Awaiting Decision</strong>
                            <div class="promo-detail">Promotion decision pending further review</div>
                            @if($actAvg !== null)
                                <div class="promo-detail">Average: {{ number_format($actAvg, 1) }}%</div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="promo-badge promo-wait">
                        <strong>⏳ Non-Promotional Term</strong>
                        <div class="promo-detail">Promotion is assessed at the end of the academic year (Third Term).</div>
                    </div>
                @endif

                {{-- ATTENDANCE BLOCK --}}
                @if($showAttendance && $attFound)
                <div class="attendance-box">
                    <div class="attendance-header">📅 TERMINAL ATTENDANCE – {{ $term }}</div>
                    <div class="att-flex">
                        @if(in_array('attendance_days_present', $showCols))
                        <div class="att-cell"><span class="att-label">PRESENT</span><span class="att-value att-ok">{{ $att['days_present'] ?? 0 }}</span></div>
                        @endif
                        @if(in_array('attendance_days_absent', $showCols))
                        <div class="att-cell"><span class="att-label">ABSENT</span><span class="att-value {{ ($att['days_absent'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">{{ $att['days_absent'] ?? 0 }}</span></div>
                        @endif
                        @if(in_array('attendance_percentage', $showCols))
                        <div class="att-cell"><span class="att-label">ATTENDANCE %</span><span class="att-value {{ $attWarn ? 'att-warn' : 'att-ok' }}">{{ $attPct }}%</span></div>
                        @endif
                    </div>
                    @if(in_array('attendance_percentage', $showCols))
                    <div class="progress-bar"><div class="progress-fill {{ $attWarn ? 'warning' : '' }}" style="width:{{ min($attPct,100) }}%;"></div></div>
                    @endif
                </div>
                @endif

                {{-- REMARKS --}}
                <table class="remarks-table">
                    <tr>
                        <td width="50%"><span class="remark-title">📖 CLASS TEACHER'S REMARK</span><br>{{ $profile ? ($profile->classteachercomment ?? '—') : '—' }}</td>
                        <td width="50%"><span class="remark-title">🏛️ PRINCIPAL'S REMARK</span><br>{{ $profile ? ($profile->principalscomment ?? '—') : '—' }}</td>
                    </tr>
                </table>

                {{-- FOOTER --}}
                <div class="bottom-strip">
                    <table class="strip-table">
                        <tr>
                            <td width="22%"><img class="qr-img" src="data:image/png;base64,{{ $qrBase64 }}" alt="QR"><div class="qr-label">verify with portal</div></td>
                            <td width="56%" style="text-align:center">
                                <div><strong>Issued:</strong> <span class="sign-line">{{ now()->format('jS F, Y') }}</span></div>
                                <div style="margin:6px 0"><strong>Parent/Guardian sign:</strong> <span class="sign-line"> _________________ </span></div>
                                <div><strong>Next term begins:</strong> <span class="sign-line">{{ $school->date_next_term_begins ? \Carbon\Carbon::parse($school->date_next_term_begins)->format('jS F, Y') : 'to be announced' }}</span></div>
                                <div class="powered">🔹 Powered by Qudroid Systems 🔹</div>
                            </td>
                            <td width="22%"><img class="stamp-img" src="{{ $stampSrc }}" alt="stamp"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>