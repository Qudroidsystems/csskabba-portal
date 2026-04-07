<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Broadsheet</title>
<style>
/* ── Reset & base ────────────────────────────────────────────────────── */
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 7.5pt;
    color: #1a1a2e;
    background: #fff;
    line-height: 1.3;
}

/* ── School header ───────────────────────────────────────────────────── */
.school-header {
    border-bottom: 3px solid #1e3a5f;
    padding-bottom: 10px;
    margin-bottom: 10px;
    position: relative;
}
.header-inner {
    display: table;
    width: 100%;
}
.header-logo-cell {
    display: table-cell;
    width: 80px;
    vertical-align: middle;
    text-align: center;
}
.header-logo-cell img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 50%;
    border: 2px solid #1e3a5f;
}
.header-text-cell {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    padding: 0 10px;
}
.school-name {
    font-size: 15pt;
    font-weight: bold;
    color: #1e3a5f;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.school-address {
    font-size: 8pt;
    color: #555;
    margin-top: 2px;
}
.school-contact {
    font-size: 7.5pt;
    color: #666;
    margin-top: 1px;
}
.school-motto {
    font-size: 8pt;
    color: #1e3a5f;
    font-style: italic;
    margin-top: 3px;
    font-weight: 600;
}

/* ── Document title strip ────────────────────────────────────────────── */
.doc-title-strip {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: white;
    text-align: center;
    padding: 6px 10px;
    font-size: 11pt;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    border-radius: 4px;
    margin-bottom: 8px;
}

/* ── Meta info grid ──────────────────────────────────────────────────── */
.meta-grid {
    display: table;
    width: 100%;
    margin-bottom: 8px;
    border: 1px solid #c5d3e8;
    border-radius: 4px;
    background: #f0f4fa;
}
.meta-cell {
    display: table-cell;
    padding: 5px 10px;
    border-right: 1px solid #c5d3e8;
    vertical-align: middle;
}
.meta-cell:last-child { border-right: none; }
.meta-label {
    font-size: 6.5pt;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
}
.meta-value {
    font-size: 9pt;
    font-weight: bold;
    color: #1e3a5f;
}

/* ── Grade key strip ─────────────────────────────────────────────────── */
.grade-key {
    display: table;
    width: 100%;
    margin-bottom: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 4px 6px;
    background: #fafafa;
}
.grade-key-title {
    font-size: 7pt;
    font-weight: bold;
    color: #1e3a5f;
    display: table-cell;
    width: 80px;
    vertical-align: middle;
}
.grade-key-items {
    display: table-cell;
    vertical-align: middle;
}
.grade-item {
    display: inline-block;
    margin-right: 8px;
    font-size: 6.5pt;
}
.grade-badge {
    display: inline-block;
    padding: 1px 5px;
    border-radius: 3px;
    font-weight: bold;
    color: white;
    font-size: 7pt;
}

/* ── Main broadsheet table ───────────────────────────────────────────── */
.broadsheet-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #1e3a5f;
    font-size: 7pt;
    page-break-inside: auto;
}

/* Header rows */
.broadsheet-table thead tr.subject-header th {
    background: #1e3a5f;
    color: white;
    text-align: center;
    padding: 4px 3px;
    border: 1px solid #2563eb;
    font-size: 7pt;
    white-space: nowrap;
}
.broadsheet-table thead tr.subject-header th.student-col {
    background: #0f2040;
    text-align: left;
    padding-left: 5px;
}
.broadsheet-table thead tr.assessment-header th {
    background: #1a56db;
    color: white;
    text-align: center;
    padding: 3px 2px;
    border: 1px solid #2563eb;
    font-size: 6.5pt;
}
.broadsheet-table thead tr.assessment-header th.student-sub {
    background: #163785;
}

/* Data rows */
.broadsheet-table tbody tr { page-break-inside: avoid; }
.broadsheet-table tbody tr:nth-child(odd)  { background: #ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background: #f0f4fa; }
.broadsheet-table tbody tr:hover { background: #e8f0fe; }

.broadsheet-table tbody td {
    padding: 3px 2px;
    border: 0.5px solid #c5d3e8;
    text-align: center;
    vertical-align: middle;
}
.broadsheet-table tbody td.student-info-cell {
    text-align: left;
    padding-left: 5px;
    font-weight: 500;
    white-space: nowrap;
    min-width: 110px;
}
.broadsheet-table tbody td.sn-cell {
    color: #6b7280;
    font-size: 6.5pt;
    width: 22px;
}

/* Score cell colours */
.score-a1 { background: #dcfce7 !important; color: #166534; font-weight: bold; }
.score-b2, .score-b3 { background: #dbeafe !important; color: #1e40af; }
.score-c4, .score-c5, .score-c6 { background: #fef3c7 !important; color: #92400e; }
.score-d7, .score-e8 { background: #fed7aa !important; color: #9a3412; }
.score-f9 { background: #fee2e2 !important; color: #991b1b; }
.score-empty { color: #9ca3af; }

/* Grade badge inline */
.grade-pill {
    display: inline-block;
    padding: 1px 4px;
    border-radius: 3px;
    font-weight: bold;
    font-size: 6.5pt;
}

/* Stats footer row */
.stats-row td {
    background: #1e3a5f !important;
    color: white;
    font-weight: bold;
    padding: 4px 2px;
    text-align: center;
    border: 0.5px solid #163785;
}
.stats-row td.stats-label {
    text-align: left;
    padding-left: 5px;
}

/* ── Summary section ─────────────────────────────────────────────────── */
.summary-section {
    margin-top: 12px;
    display: table;
    width: 100%;
}
.summary-box {
    display: table-cell;
    width: 33%;
    padding: 6px 8px;
    border: 1px solid #c5d3e8;
    border-radius: 4px;
    vertical-align: top;
    margin: 0 3px;
}
.summary-title {
    font-size: 8pt;
    font-weight: bold;
    color: #1e3a5f;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 3px;
    margin-bottom: 4px;
}

/* ── Signature row ───────────────────────────────────────────────────── */
.signature-row {
    margin-top: 20px;
    display: table;
    width: 100%;
}
.sig-cell {
    display: table-cell;
    width: 25%;
    text-align: center;
    padding: 0 10px;
    vertical-align: bottom;
}
.sig-line {
    border-top: 1px solid #333;
    padding-top: 4px;
    font-size: 7.5pt;
    color: #374151;
    margin-top: 25px;
}

/* ── Footer ──────────────────────────────────────────────────────────── */
.page-footer {
    margin-top: 10px;
    border-top: 1px solid #e2e8f0;
    padding-top: 4px;
    display: table;
    width: 100%;
    font-size: 6.5pt;
    color: #9ca3af;
}
.footer-left  { display: table-cell; text-align: left; }
.footer-right { display: table-cell; text-align: right; }

/* ── Page break ──────────────────────────────────────────────────────── */
.page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- SCHOOL HEADER                                                           --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="school-header">
    <div class="header-inner">
        <div class="header-logo-cell">
            @if(!empty($school_logo_base64))
                <img src="{{ $school_logo_base64 }}" alt="Logo">
            @endif
        </div>
        <div class="header-text-cell">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
            @if(!empty($schoolInfo->school_address))
                <div class="school-address">{{ $schoolInfo->school_address }}</div>
            @endif
            @if(!empty($schoolInfo->school_phone) || !empty($schoolInfo->school_email))
                <div class="school-contact">
                    @if(!empty($schoolInfo->school_phone)) Tel: {{ $schoolInfo->school_phone }} @endif
                    @if(!empty($schoolInfo->school_email)) &nbsp;| Email: {{ $schoolInfo->school_email }} @endif
                </div>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <div class="school-motto">"{{ $schoolInfo->school_motto }}"</div>
            @endif
        </div>
        <div class="header-logo-cell">
            {{-- Right side — could be class badge or empty --}}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- DOCUMENT TITLE                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="doc-title-strip">CLASS ACADEMIC BROADSHEET</div>

{{-- ── Meta info row ── --}}
<div class="meta-grid">
    <div class="meta-cell">
        <span class="meta-label">Class</span>
        <span class="meta-value">{{ $schoolclass->schoolclass ?? '-' }} {{ $schoolclass->arm_name ?? '' }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">Academic Session</span>
        <span class="meta-value">{{ $schoolsession->session ?? '-' }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">Term</span>
        <span class="meta-value">{{ $schoolterm->term ?? '-' }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">No. of Students</span>
        <span class="meta-value">{{ $totalStudents }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">No. of Subjects</span>
        <span class="meta-value">{{ count($subjects) }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">Generated</span>
        <span class="meta-value" style="font-size:7.5pt;">{{ $generatedAt }}</span>
    </div>
</div>

{{-- ── WAEC Grade Key ── --}}
<div class="grade-key">
    <div class="grade-key-title">GRADING SCALE:</div>
    <div class="grade-key-items">
        @php
        $gradeKey = [
            'A1' => ['75-100', '#16a34a'],
            'B2' => ['70-74',  '#1d4ed8'],
            'B3' => ['65-69',  '#2563eb'],
            'C4' => ['60-64',  '#d97706'],
            'C5' => ['55-59',  '#b45309'],
            'C6' => ['50-54',  '#92400e'],
            'D7' => ['45-49',  '#ea580c'],
            'E8' => ['40-44',  '#c2410c'],
            'F9' => ['0-39',   '#dc2626'],
        ];
        @endphp
        @foreach($gradeKey as $grade => $info)
            <span class="grade-item">
                <span class="grade-badge" style="background:{{ $info[1] }};">{{ $grade }}</span>
                {{ $info[0] }}
            </span>
        @endforeach
        &nbsp;&nbsp;
        <span style="font-size:6.5pt;color:#555;">
            <strong>BF</strong>=Brought Forward &nbsp;
            <strong>CUM</strong>=Cumulative Score &nbsp;
            <strong>POS</strong>=Position in Class &nbsp;
            <strong>AVG</strong>=Class Average
        </span>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- BROADSHEET TABLE                                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@php
    $selected = $selectedColumns ?? [];
    $showAdmNo    = empty($selected) || in_array('admission_no', $selected);
    $showGender   = in_array('gender', $selected);
    $showTotal    = empty($selected) || in_array('total', $selected);
    $showBF       = in_array('bf', $selected);
    $showCum      = empty($selected) || in_array('cum', $selected);
    $showGrade    = empty($selected) || in_array('grade', $selected);
    $showPosition = empty($selected) || in_array('position', $selected);
    $showAvg      = in_array('class_average', $selected);
    $showRemark   = in_array('remark', $selected);
    $showGPA      = in_array('gpa', $selected);
    $showCGPA     = in_array('cgpa', $selected);
    $showGPAGrade = in_array('gpa_grade', $selected);

    // Which assessment columns to show
    $activeAssessments = $assessments->filter(function($a) use ($selected) {
        return empty($selected) || in_array('assessment_' . $a->id, $selected);
    });

    // Grade colour mapping
    $gradeColors = [
        'A1' => 'score-a1', 'B2' => 'score-b2', 'B3' => 'score-b3',
        'C4' => 'score-c4', 'C5' => 'score-c4', 'C6' => 'score-c4',
        'D7' => 'score-d7', 'E8' => 'score-d7',
        'F9' => 'score-f9', '-'  => 'score-empty',
    ];
@endphp

<table class="broadsheet-table">
    <thead>

        {{-- ── Row 1: Subject name headers ── --}}
        <tr class="subject-header">
            <th class="student-col" rowspan="2" style="width:22px;">#</th>
            @if($showAdmNo)
                <th class="student-col" rowspan="2" style="min-width:55px;">Adm. No</th>
            @endif
            <th class="student-col" rowspan="2" style="min-width:110px;text-align:left;padding-left:5px;">Student Name</th>
            @if($showGender)
                <th class="student-col" rowspan="2" style="width:35px;">Gender</th>
            @endif

            @foreach($subjects as $subId => $subInfo)
                @php
                    $asmCount = $activeAssessments->count();
                    $colspan  = $asmCount;
                    if ($showTotal)    $colspan++;
                    if ($showBF)       $colspan++;
                    if ($showCum)      $colspan++;
                    if ($showGrade)    $colspan++;
                    if ($showPosition) $colspan++;
                    if ($showAvg)      $colspan++;
                    if ($showRemark)   $colspan++;
                @endphp
                <th colspan="{{ max(1, $colspan) }}" style="border-left:2px solid #2563eb;font-size:7pt;">
                    {{ $subInfo['subject_name'] }}
                    @if(!empty($subInfo['subject_code']))
                        <br><span style="font-size:6pt;opacity:.8;">({{ $subInfo['subject_code'] }})</span>
                    @endif
                </th>
            @endforeach

            @if($showGPA)
                <th rowspan="2" style="width:30px;background:#0f2040;">GPA</th>
            @endif
            @if($showCGPA)
                <th rowspan="2" style="width:30px;background:#0f2040;">CGPA</th>
            @endif
            @if($showGPAGrade)
                <th rowspan="2" style="width:30px;background:#0f2040;">GPA Grd</th>
            @endif
        </tr>

        {{-- ── Row 2: Assessment sub-headers ── --}}
        <tr class="assessment-header">
            @foreach($subjects as $subId => $subInfo)
                @foreach($activeAssessments as $a)
                    <th style="border-left:1px solid #2563eb;font-size:6.5pt;">
                        {{ $a->name }}<br>
                        <span style="font-size:5.5pt;opacity:.8;">({{ $a->max_score }})</span>
                    </th>
                @endforeach
                @if($showTotal)    <th>Total</th> @endif
                @if($showBF)       <th>BF</th>    @endif
                @if($showCum)      <th>Cum</th>   @endif
                @if($showGrade)    <th>Grd</th>   @endif
                @if($showPosition) <th>Pos</th>   @endif
                @if($showAvg)      <th>Avg</th>   @endif
                @if($showRemark)   <th>Rmk</th>   @endif
            @endforeach
        </tr>

    </thead>
    <tbody>

        @foreach($studentRows as $idx => $stu)
            <tr>
                {{-- SN --}}
                <td class="sn-cell">{{ $idx + 1 }}</td>

                {{-- Admission No --}}
                @if($showAdmNo)
                    <td style="font-size:6.5pt;white-space:nowrap;">{{ $stu['admissionno'] }}</td>
                @endif

                {{-- Name --}}
                <td class="student-info-cell">
                    <strong>{{ strtoupper($stu['lastname']) }}</strong>,
                    {{ $stu['firstname'] }}
                </td>

                {{-- Gender --}}
                @if($showGender)
                    <td style="font-size:6.5pt;">{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
                @endif

                {{-- Subject scores --}}
                @foreach($subjects as $subId => $subInfo)
                    @php $subData = $stu['subjects'][$subId] ?? []; @endphp

                    {{-- Assessment scores --}}
                    @foreach($activeAssessments as $a)
                        @php $aScore = $subData['assessments'][$a->id] ?? 0; @endphp
                        <td style="border-left:1px solid #2563eb;">
                            {{ $aScore > 0 ? number_format($aScore, 1) : '-' }}
                        </td>
                    @endforeach

                    {{-- Total --}}
                    @if($showTotal)
                        @php
                            $total = $subData['total'] ?? 0;
                            $grade = $subData['grade'] ?? '-';
                            $cls   = $gradeColors[$grade] ?? '';
                        @endphp
                        <td class="{{ $cls }}">{{ $total > 0 ? number_format($total, 1) : '-' }}</td>
                    @endif

                    {{-- BF --}}
                    @if($showBF)
                        @php $bf = $subData['bf'] ?? 0; @endphp
                        <td>{{ $bf > 0 ? number_format($bf, 1) : '-' }}</td>
                    @endif

                    {{-- Cum --}}
                    @if($showCum)
                        @php
                            $cum    = $subData['cum'] ?? 0;
                            $cumCls = $gradeColors[$subData['grade'] ?? '-'] ?? '';
                        @endphp
                        <td class="{{ $cumCls }}" style="font-weight:600;">
                            {{ $cum > 0 ? number_format($cum, 1) : '-' }}
                        </td>
                    @endif

                    {{-- Grade --}}
                    @if($showGrade)
                        @php
                            $g    = $subData['grade'] ?? '-';
                            $gCls = $gradeColors[$g] ?? '';
                        @endphp
                        <td class="{{ $gCls }}" style="font-weight:bold;">{{ $g }}</td>
                    @endif

                    {{-- Position --}}
                    @if($showPosition)
                        <td style="font-size:6.5pt;">{{ $subData['position'] ?? '-' }}</td>
                    @endif

                    {{-- Class Average --}}
                    @if($showAvg)
                        @php $stats = $subjectStats[$subId] ?? []; @endphp
                        <td style="color:#6b7280;font-size:6pt;">{{ $stats['avg'] ?? '-' }}</td>
                    @endif

                    {{-- Remark --}}
                    @if($showRemark)
                        <td style="font-size:6pt;white-space:nowrap;">{{ $subData['remark'] ?? '-' }}</td>
                    @endif
                @endforeach

                {{-- GPA / CGPA --}}
                @if($showGPA)
                    <td style="font-weight:bold;background:#eff6ff;color:#1e40af;">{{ number_format($stu['gpa'], 2) }}</td>
                @endif
                @if($showCGPA)
                    <td style="background:#f0fdf4;color:#166534;">{{ number_format($stu['cgpa'], 2) }}</td>
                @endif
                @if($showGPAGrade)
                    @php $ggCls = $gradeColors[$stu['gpa_grade'] ?? '-'] ?? ''; @endphp
                    <td class="{{ $ggCls }}" style="font-weight:bold;">{{ $stu['gpa_grade'] ?? '-' }}</td>
                @endif
            </tr>
        @endforeach

        {{-- ── Stats footer row ── --}}
        <tr class="stats-row">
            <td class="stats-label" colspan="{{ 1 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0) }}">
                CLASS STATS
            </td>

            @foreach($subjects as $subId => $subInfo)
                @php $stats = $subjectStats[$subId] ?? []; @endphp

                @foreach($activeAssessments as $a)
                    <td>—</td>
                @endforeach

                @if($showTotal)
                    <td title="Average Total">{{ $stats['avg'] ?? '-' }}</td>
                @endif
                @if($showBF)    <td>—</td>  @endif
                @if($showCum)   <td>—</td>  @endif
                @if($showGrade) <td>—</td>  @endif
                @if($showPosition) <td>—</td> @endif
                @if($showAvg)
                    <td>{{ $stats['avg'] ?? '-' }}</td>
                @endif
                @if($showRemark) <td>—</td> @endif
            @endforeach

            @if($showGPA)      <td>—</td> @endif
            @if($showCGPA)     <td>—</td> @endif
            @if($showGPAGrade) <td>—</td> @endif
        </tr>

        {{-- ── Highest score row ── --}}
        <tr class="stats-row" style="background:#163785 !important;">
            <td class="stats-label" colspan="{{ 1 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0) }}">
                HIGHEST
            </td>
            @foreach($subjects as $subId => $subInfo)
                @php $stats = $subjectStats[$subId] ?? []; @endphp
                @foreach($activeAssessments as $a) <td>—</td> @endforeach
                @if($showTotal)    <td>{{ $stats['highest'] ?? '-' }}</td> @endif
                @if($showBF)       <td>—</td> @endif
                @if($showCum)      <td>—</td> @endif
                @if($showGrade)    <td>—</td> @endif
                @if($showPosition) <td>—</td> @endif
                @if($showAvg)      <td>—</td> @endif
                @if($showRemark)   <td>—</td> @endif
            @endforeach
            @if($showGPA)      <td>—</td> @endif
            @if($showCGPA)     <td>—</td> @endif
            @if($showGPAGrade) <td>—</td> @endif
        </tr>

        {{-- ── Lowest score row ── --}}
        <tr class="stats-row" style="background:#1e3a5f !important;">
            <td class="stats-label" colspan="{{ 1 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0) }}">
                LOWEST
            </td>
            @foreach($subjects as $subId => $subInfo)
                @php $stats = $subjectStats[$subId] ?? []; @endphp
                @foreach($activeAssessments as $a) <td>—</td> @endforeach
                @if($showTotal)    <td>{{ $stats['lowest'] ?? '-' }}</td> @endif
                @if($showBF)       <td>—</td> @endif
                @if($showCum)      <td>—</td> @endif
                @if($showGrade)    <td>—</td> @endif
                @if($showPosition) <td>—</td> @endif
                @if($showAvg)      <td>—</td> @endif
                @if($showRemark)   <td>—</td> @endif
            @endforeach
            @if($showGPA)      <td>—</td> @endif
            @if($showCGPA)     <td>—</td> @endif
            @if($showGPAGrade) <td>—</td> @endif
        </tr>

    </tbody>
</table>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- SUBJECT PASS/FAIL SUMMARY                                              --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="summary-section" style="margin-top:14px;">
    <table style="width:100%;border-collapse:collapse;font-size:7pt;border:1px solid #c5d3e8;">
        <thead>
            <tr style="background:#1e3a5f;color:white;">
                <th style="padding:4px 6px;text-align:left;border:1px solid #2563eb;min-width:130px;">Subject</th>
                <th style="padding:4px;border:1px solid #2563eb;width:50px;">Avg</th>
                <th style="padding:4px;border:1px solid #2563eb;width:50px;">Highest</th>
                <th style="padding:4px;border:1px solid #2563eb;width:50px;">Lowest</th>
                <th style="padding:4px;border:1px solid #2563eb;width:45px;">Passed</th>
                <th style="padding:4px;border:1px solid #2563eb;width:45px;">Failed</th>
                <th style="padding:4px;border:1px solid #2563eb;width:60px;">Pass Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $subId => $subInfo)
                @php
                    $stats    = $subjectStats[$subId] ?? [];
                    $passed   = $stats['passed'] ?? 0;
                    $failed   = $stats['failed'] ?? 0;
                    $total    = $passed + $failed;
                    $passRate = $total > 0 ? round($passed / $total * 100) : 0;
                    $rowEven  = $loop->iteration % 2 === 0;
                @endphp
                <tr style="{{ $rowEven ? 'background:#f0f4fa;' : '' }}">
                    <td style="padding:3px 6px;border:0.5px solid #c5d3e8;font-weight:500;">
                        {{ $subInfo['subject_name'] }}
                        @if(!empty($subInfo['subject_code']))
                            <span style="color:#6b7280;font-size:6pt;">({{ $subInfo['subject_code'] }})</span>
                        @endif
                    </td>
                    <td style="padding:3px;border:0.5px solid #c5d3e8;text-align:center;font-weight:bold;">{{ $stats['avg'] ?? '-' }}</td>
                    <td style="padding:3px;border:0.5px solid #c5d3e8;text-align:center;color:#16a34a;font-weight:bold;">{{ $stats['highest'] ?? '-' }}</td>
                    <td style="padding:3px;border:0.5px solid #c5d3e8;text-align:center;color:#dc2626;font-weight:bold;">{{ $stats['lowest'] ?? '-' }}</td>
                    <td style="padding:3px;border:0.5px solid #c5d3e8;text-align:center;color:#16a34a;">{{ $passed }}</td>
                    <td style="padding:3px;border:0.5px solid #c5d3e8;text-align:center;color:#dc2626;">{{ $failed }}</td>
                    <td style="padding:3px;border:0.5px solid #c5d3e8;text-align:center;">
                        <span style="background:{{ $passRate >= 50 ? '#dcfce7' : '#fee2e2' }};color:{{ $passRate >= 50 ? '#166534' : '#991b1b' }};padding:1px 5px;border-radius:3px;font-weight:bold;">
                            {{ $passRate }}%
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- SIGNATURE ROW                                                           --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="signature-row">
    <div class="sig-cell"><div class="sig-line">Class Teacher</div></div>
    <div class="sig-cell"><div class="sig-line">Head of Department</div></div>
    <div class="sig-cell"><div class="sig-line">Vice Principal</div></div>
    <div class="sig-cell"><div class="sig-line">Principal</div></div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- FOOTER                                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="page-footer">
    <div class="footer-left">
        {{ $schoolInfo->school_name ?? '' }} &mdash; Confidential Academic Record
    </div>
    <div class="footer-right">
        Generated: {{ $generatedAt }} &nbsp;|&nbsp; Page <span class="pagenum"></span>
    </div>
</div>

</body>
</html>
