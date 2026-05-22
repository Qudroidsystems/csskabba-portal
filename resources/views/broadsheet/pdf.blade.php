<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Broadsheet</title>
<style>
/* ── PDF-safe reset ─────────────────────────────────────────────────── */
* { margin:0; padding:0; box-sizing:border-box; }

/*
  Key fix: we use a single, very wide page that matches the actual
  table width so DomPDF never clips content.
  The @page width is set large; DomPDF will render the full width.
  Landscape orientation is declared here.
*/
@page {
    margin: 8mm 10mm;
    size: auto landscape;
}

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.5pt;
    color: #1a1a2e;
    background: #fff;
    line-height: 1.25;
    width: 100%;
}

/* ── Animations (for reference - PDFs don't animate but styles apply) ── */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes countUp {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes progressFill {
    from { width: 0; }
}

.animate-fade-up { animation: fadeInUp 0.5s ease both; }
.animate-count { animation: countUp 0.6s ease both; }

/* ── School header ───────────────────────────────────────────────────── */
.school-header {
    border-bottom: 2.5px solid #1e3a5f;
    padding-bottom: 8px;
    margin-bottom: 8px;
}
.header-inner { display: table; width: 100%; }
.header-logo-cell {
    display: table-cell;
    width: 75px;
    vertical-align: middle;
    text-align: center;
}
.header-logo-cell img {
    width: 65px; height: 65px;
    object-fit: contain;
    border-radius: 50%;
    border: 2px solid #1e3a5f;
}
.header-text-cell {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    padding: 0 8px;
}
.school-name {
    font-size: 13pt;
    font-weight: bold;
    color: #1e3a5f;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.school-address { font-size: 7.5pt; color: #555; margin-top: 2px; }
.school-contact { font-size: 7pt;   color: #666; margin-top: 1px; }
.school-motto   { font-size: 7.5pt; color: #1e3a5f; font-style: italic; margin-top: 2px; font-weight: 600; }

/* ── Title strip ─────────────────────────────────────────────────────── */
.doc-title-strip {
    background: #1e3a5f;
    color: white;
    text-align: center;
    padding: 5px 8px;
    font-size: 10pt;
    font-weight: bold;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 7px;
}

/* ── Stat Cards ──────────────────────────────────────────────────────── */
.stat-cards {
    display: table;
    width: 100%;
    margin-bottom: 10px;
    border-collapse: collapse;
}
.stat-card {
    display: table-cell;
    background: #f0f4fa;
    border: 1px solid #c5d3e8;
    border-radius: 8px;
    padding: 6px 8px;
    text-align: center;
    width: 25%;
}
.stat-value {
    font-size: 14pt;
    font-weight: bold;
    color: #1e3a5f;
}
.stat-label {
    font-size: 6pt;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ── Meta info ───────────────────────────────────────────────────────── */
.meta-grid { display: table; width: 100%; margin-bottom: 7px; border: 1px solid #c5d3e8; background: #f0f4fa; }
.meta-cell {
    display: table-cell;
    padding: 4px 8px;
    border-right: 1px solid #c5d3e8;
    vertical-align: middle;
}
.meta-cell:last-child { border-right: none; }
.meta-label { font-size: 6pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; display: block; }
.meta-value { font-size: 8.5pt; font-weight: bold; color: #1e3a5f; }

/* ── Grade key ───────────────────────────────────────────────────────── */
.grade-key { display: table; width: 100%; margin-bottom: 7px; border: 1px solid #e2e8f0; padding: 3px 5px; background: #fafafa; }
.grade-key-title { font-size: 6.5pt; font-weight: bold; color: #1e3a5f; display: table-cell; width: 75px; vertical-align: middle; }
.grade-key-items { display: table-cell; vertical-align: middle; }
.grade-item { display: inline-block; margin-right: 7px; font-size: 6pt; }
.grade-badge { display: inline-block; padding: 1px 4px; border-radius: 2px; font-weight: bold; color: white; font-size: 6.5pt; }

/* ── Position Badge ──────────────────────────────────────────────────── */
.pos-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 9pt;
    font-weight: 800;
    border: 1.5px solid;
}
.pos-1 { background: linear-gradient(135deg, #fef9c3, #fde68a); border-color: #f59e0b; color: #92400e; }
.pos-2 { background: linear-gradient(135deg, #f1f5f9, #e2e8f0); border-color: #94a3b8; color: #475569; }
.pos-3 { background: linear-gradient(135deg, #ffedd5, #fed7aa); border-color: #f97316; color: #9a3412; }
.pos-other { background: #f0f4fa; border-color: #c5d3e8; color: #6b7280; }

/* ── Progress Bar ────────────────────────────────────────────────────── */
.progress-bar-wrap {
    background: #e2e8f0;
    border-radius: 3px;
    height: 3px;
    overflow: hidden;
    margin-top: 2px;
}
.progress-bar {
    height: 100%;
    border-radius: 3px;
    background: #22c55e;
}
.progress-bar.red { background: #dc2626; }
.progress-bar.amber { background: #f59e0b; }
.progress-bar.green { background: #22c55e; }

/* ── Performance Strip ───────────────────────────────────────────────── */
.performance-strip {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 6px;
    padding: 5px 8px;
    color: white;
}
.performance-strip table { width: 100%; }
.performance-strip td { padding: 1px 2px; border: none; color: white; }
.performance-label { font-size: 5.5pt; opacity: 0.8; }
.performance-value { font-size: 6.5pt; font-weight: bold; }

/* ── Score colors ────────────────────────────────────────────────────── */
.score-red { color: #dc2626 !important; font-weight: bold; }
.score-amber { color: #d97706 !important; font-weight: bold; }
.score-green { color: #16a34a !important; font-weight: bold; }

/* ── MAIN TABLE ──────────────────────────────────────────────────────── */
.broadsheet-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #1e3a5f;
    font-size: 6.5pt;
    page-break-inside: auto;
    table-layout: auto;
}

/* Subject header row */
.broadsheet-table thead tr.subject-header th {
    background: #1e3a5f;
    color: white;
    text-align: center;
    padding: 4px 2px;
    border: 0.5px solid #2563eb55;
    font-size: 6.5pt;
    font-weight: bold;
    white-space: nowrap;
}
.broadsheet-table thead tr.subject-header th.student-col {
    background: #0f2040;
    text-align: left;
    padding-left: 4px;
}
.broadsheet-table thead tr.subject-header th.subj-name-hdr {
    background: #163562;
    border-left: 1.5px solid #2563eb;
    font-size: 6pt;
    white-space: normal;
    word-break: break-word;
    min-width: 55px;
    max-width: 90px;
}

/* Assessment sub-header row */
.broadsheet-table thead tr.assessment-header th {
    background: #1a3d6a;
    color: #a8d4ef;
    text-align: center;
    padding: 3px 2px;
    border: 0.5px solid #2563eb33;
    font-size: 6pt;
    white-space: nowrap;
}
.broadsheet-table thead tr.assessment-header th.sub-boundary {
    border-left: 1.5px solid #2563eb;
}

/* Data rows */
.broadsheet-table tbody tr { page-break-inside: avoid; }
.broadsheet-table tbody tr:nth-child(odd)  { background: #ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background: #f0f4fa; }

.broadsheet-table tbody td {
    padding: 2px 2px;
    border: 0.5px solid #c5d3e8;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}
.broadsheet-table tbody td.student-info-cell {
    text-align: left;
    padding-left: 4px;
    font-weight: 600;
    min-width: 100px;
    white-space: nowrap;
}
.broadsheet-table tbody td.sn-cell {
    color: #6b7280;
    font-size: 6pt;
    width: 18px;
    text-align: center;
}
.broadsheet-table tbody td.adm-cell {
    font-size: 6pt;
    color: #4a6e8a;
    min-width: 50px;
}
.broadsheet-table tbody td.sub-boundary {
    border-left: 1.5px solid #2563eb66;
}
.broadsheet-table tbody td.performance-cell {
    padding: 4px;
    min-width: 140px;
}

/* Grade colour coding */
.score-a1 { background: #dcfce7 !important; color: #166534; font-weight: bold; }
.score-b2 { background: #dbeafe !important; color: #1e40af; }
.score-b3 { background: #e0eeff !important; color: #1e40af; }
.score-c4 { background: #fef9c3 !important; color: #854d0e; }
.score-c5 { background: #fef3c7 !important; color: #92400e; }
.score-c6 { background: #fde68a !important; color: #78350f; }
.score-d7 { background: #ffedd5 !important; color: #9a3412; }
.score-e8 { background: #fed7aa !important; color: #9a3412; }
.score-f9 { background: #fee2e2 !important; color: #991b1b; font-weight: bold; }
.score-empty { color: #9ca3af; }

/* Stats footer rows */
.stats-row td {
    background: #1e3a5f !important;
    color: white;
    font-weight: bold;
    padding: 3px 2px;
    text-align: center;
    border: 0.5px solid #163785;
    white-space: nowrap;
}
.stats-row td.stats-label {
    text-align: left;
    padding-left: 4px;
    font-size: 6pt;
    font-weight: bold;
}
.stats-hi td { background: #0a2240 !important; }
.stats-lo td { background: #111c2a !important; }

/* GPA columns */
.gpa-cell { background: #eff6ff !important; color: #1e3a8a; font-weight: bold; border-left: 1.5px solid #3b82f6 !important; }

/* ── Summary table ───────────────────────────────────────────────────── */
.summary-section { margin-top: 10px; }
.sum-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 6.5pt;
    border: 1px solid #c5d3e8;
    page-break-inside: avoid;
}
.sum-table thead tr th {
    background: #1e3a5f;
    color: white;
    padding: 3px 5px;
    text-align: left;
    border: 0.5px solid #2563eb55;
}
.sum-table tbody tr:nth-child(even) { background: #f0f4fa; }
.sum-table tbody td {
    padding: 3px 5px;
    border: 0.5px solid #c5d3e8;
}
.pass-good { background: #dcfce7; color: #166534; font-weight: bold; padding: 1px 5px; border-radius: 3px; }
.pass-bad  { background: #fee2e2; color: #991b1b; font-weight: bold; padding: 1px 5px; border-radius: 3px; }

/* ── Signatures ─────────────────────────────────────────────────────── */
.signature-row { margin-top: 18px; display: table; width: 100%; }
.sig-cell { display: table-cell; width: 25%; text-align: center; padding: 0 8px; vertical-align: bottom; }
.sig-line { border-top: 1px solid #333; padding-top: 3px; font-size: 7pt; color: #374151; margin-top: 22px; }

/* ── Footer ──────────────────────────────────────────────────────────── */
.page-footer {
    margin-top: 8px;
    border-top: 1px solid #e2e8f0;
    padding-top: 3px;
    display: table;
    width: 100%;
    font-size: 6pt;
    color: #9ca3af;
}
.footer-left  { display: table-cell; text-align: left; }
.footer-right { display: table-cell; text-align: right; }

/* Print optimization */
@media print {
    .progress-bar {
        background-color: #22c55e !important;
    }
    .progress-bar.red { background-color: #dc2626 !important; }
    .progress-bar.amber { background-color: #f59e0b !important; }
    .progress-bar.green { background-color: #22c55e !important; }
}
</style>
</head>
<body>

@php
    // Calculate positions based on cumulative total
    $positionMap = [];
    $sortedByCum = collect($studentRows)->sortByDesc('total_cum')->values();
    $prevPct = null;
    $prevPos = 0;
    $counter = 0;
    foreach($sortedByCum as $stu) {
        $counter++;
        if($prevPct !== null && $stu['total_cum'] == $prevPct) {
            $positionMap[$stu['id']] = $prevPos;
        } else {
            $positionMap[$stu['id']] = $counter;
            $prevPos = $counter;
        }
        $prevPct = $stu['total_cum'];
    }

    // Calculate class average percentage
    $totalObtainable = count($subjects) * 100;
    $totalPct = 0;
    foreach($studentRows as $stu) {
        $totalPct += $totalObtainable > 0 ? ($stu['total_cum'] / $totalObtainable) * 100 : 0;
    }
    $avgClassPercentage = count($studentRows) > 0 ? round($totalPct / count($studentRows), 1) : 0;

    // Find top performer
    $topGPA = 0;
    $topName = '—';
    foreach($studentRows as $stu) {
        if($stu['gpa'] > $topGPA) {
            $topGPA = $stu['gpa'];
            $topName = $stu['lastname'] . ' ' . $stu['firstname'];
        }
    }
@endphp

{{-- ── School header ── --}}
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
        <div class="header-logo-cell"></div>
    </div>
</div>

<div class="doc-title-strip">CLASS ACADEMIC BROADSHEET</div>

{{-- ── Stat Cards ── --}}
<table class="stat-cards">
    <tr>
        <td class="stat-card">
            <div class="stat-value">{{ count($studentRows) }}</div>
            <div class="stat-label">Total Students</div>
        </td>
        <td class="stat-card">
            <div class="stat-value">{{ count($subjects) }}</div>
            <div class="stat-label">Subjects</div>
        </td>
        <td class="stat-card">
            <div class="stat-value">{{ $avgClassPercentage }}%</div>
            <div class="stat-label">Avg % (Cum)</div>
        </td>
        <td class="stat-card">
            <div class="stat-value">{{ $topName }}</div>
            <div class="stat-label">Top Performer</div>
        </td>
    </tr>
</table>

{{-- ── Meta row ── --}}
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
        <span class="meta-value">{{ count($studentRows) }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">No. of Subjects</span>
        <span class="meta-value">{{ count($subjects) }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">Generated</span>
        <span class="meta-value" style="font-size:7pt;">{{ $generatedAt }}</span>
    </div>
</div>

{{-- ── Grade key ── --}}
<div class="grade-key">
    <div class="grade-key-title">GRADING SCALE:</div>
    <div class="grade-key-items">
        @php
        $gradeKey = [
            'A1' => ['75-100', '#16a34a'], 'B2' => ['70-74', '#1d4ed8'], 'B3' => ['65-69', '#2563eb'],
            'C4' => ['60-64', '#d97706'],  'C5' => ['55-59', '#b45309'], 'C6' => ['50-54', '#92400e'],
            'D7' => ['45-49', '#ea580c'],  'E8' => ['40-44', '#c2410c'], 'F9' => ['0-39',  '#dc2626'],
        ];
        @endphp
        @foreach($gradeKey as $grade => $info)
            <span class="grade-item">
                <span class="grade-badge" style="background:{{ $info[1] }};">{{ $grade }}</span>
                {{ $info[0] }}
            </span>
        @endforeach
        &nbsp;&nbsp;
        <span style="font-size:6pt;color:#555;">
            <strong>BF</strong>=Brought Forward &nbsp;
            <strong>CUM</strong>=Cumulative &nbsp;
            <strong>POS</strong>=Position &nbsp;
            <strong>AVG</strong>=Class Average
        </span>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- BROADSHEET TABLE                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@php
    $selected  = $selectedColumns ?? [];
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
    $showNumSub   = in_array('num_subjects', $selected);
    $showTotalGP  = in_array('total_grade_points', $selected);

    $activeAssessments = $assessments->filter(fn($a) =>
        empty($selected) || in_array('assessment_' . $a->id, $selected)
    );

    $gradeColors = [
        'A1'=>'score-a1','B2'=>'score-b2','B3'=>'score-b3',
        'C4'=>'score-c4','C5'=>'score-c5','C6'=>'score-c6',
        'D7'=>'score-d7','E8'=>'score-e8','F9'=>'score-f9','-'=>'score-empty',
    ];

    /* Per-subject colspan */
    $subColspan = $activeAssessments->count();
    if ($showTotal)    $subColspan++;
    if ($showBF)       $subColspan++;
    if ($showCum)      $subColspan++;
    if ($showGrade)    $subColspan++;
    if ($showPosition) $subColspan++;
    if ($showAvg)      $subColspan++;
    if ($showRemark)   $subColspan++;

    $frozenCols = 2 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0); // SN + Pos + Name + AdmNo + Gender

    /* GPA colspan */
    $gpaColspan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);
@endphp

<table class="broadsheet-table">
<thead>

  {{-- Row 1: Subject names --}}
  <tr class="subject-header">
    <th class="student-col" rowspan="2" style="width:18px;">#</th>
    <th class="student-col" rowspan="2" style="width:28px;">Pos</th>
    @if($showAdmNo)
      <th class="student-col" rowspan="2" style="min-width:50px;">Adm. No</th>
    @endif
    <th class="student-col" rowspan="2" style="min-width:100px;text-align:left;padding-left:4px;">Student Name</th>
    @if($showGender)
      <th class="student-col" rowspan="2" style="width:22px;">Sex</th>
    @endif

    @foreach($subjects as $subId => $subInfo)
      <th class="subj-name-hdr" colspan="{{ max(1,$subColspan) }}">
        {{ $subInfo['subject_name'] }}
        @if(!empty($subInfo['subject_code']))
          <br><span style="font-size:5.5pt;opacity:.75;">({{ $subInfo['subject_code'] }})</span>
        @endif
      </th>
    @endforeach

    <th class="subj-name-hdr" style="min-width:140px;">Performance Summary</th>

    @if($gpaColspan > 0)
      <th colspan="{{ $gpaColspan }}" style="background:#0a1e38;border-left:2px solid #3b82f6;font-size:6pt;">
        GPA METRICS
      </th>
    @endif
  </tr>

  {{-- Row 2: Assessment sub-headers --}}
  <tr class="assessment-header">
    @foreach($subjects as $subId => $subInfo)
      @foreach($activeAssessments as $aIdx => $a)
        <th class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}" style="min-width:22px;">
          {{ $a->name }}<br>
          <span style="font-size:5pt;opacity:.75;">/{{ $a->max_score }}</span>
        </th>
      @endforeach
      @if($showTotal)    <th style="min-width:24px;">Total</th>  @endif
      @if($showBF)       <th style="min-width:20px;">BF</th>     @endif
      @if($showCum)      <th style="min-width:24px;">Cum</th>    @endif
      @if($showGrade)    <th style="min-width:20px;">Grd</th>    @endif
      @if($showPosition) <th style="min-width:20px;">Pos</th>    @endif
      @if($showAvg)      <th style="min-width:22px;">Avg</th>    @endif
      @if($showRemark)   <th style="min-width:30px;">Rmk</th>    @endif
    @endforeach
    <th style="min-width:140px;background:#163562;">Performance Metrics</th>
    @if($showGPA)      <th style="background:#0a1e38;color:#93c5fd;min-width:26px;border-left:2px solid #3b82f6;">GPA</th>      @endif
    @if($showCGPA)     <th style="background:#0a1e38;color:#86efac;min-width:26px;">CGPA</th>    @endif
    @if($showGPAGrade) <th style="background:#0a1e38;color:#fcd34d;min-width:22px;">GGrd</th>   @endif
    @if($showNumSub)   <th style="background:#0a1e38;color:#a8d4ef;min-width:22px;">NS</th>     @endif
    @if($showTotalGP)  <th style="background:#0a1e38;color:#a8d4ef;min-width:24px;">TGP</th>   @endif
  </tr>

</thead>
<tbody>

  @foreach($studentRows as $idx => $stu)
    @php
        $totalObtainable = count($subjects) * 100;
        $termPercentage = $totalObtainable > 0 ? round(($stu['total_cum'] / $totalObtainable) * 100, 1) : 0;
        $termColorClass = $termPercentage < 40 ? 'red' : ($termPercentage < 70 ? 'amber' : 'green');
        $pos = $positionMap[$stu['id']] ?? 0;
        $posClass = $pos === 1 ? 'pos-1' : ($pos === 2 ? 'pos-2' : ($pos === 3 ? 'pos-3' : 'pos-other'));
        $posIcon = $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : $pos));
        $imgSrc = $stu['picture'] ? asset('storage/student_avatars/' . basename($stu['picture'])) : '';
    @endphp
    <tr>
      <td class="sn-cell">{{ $idx + 1 }}</td>
      <td style="text-align:center;">
        <div class="pos-badge {{ $posClass }}">{{ $posIcon }}</div>
      </td>
      @if($showAdmNo)
        <td class="adm-cell">{{ $stu['admissionno'] }}</td>
      @endif
      <td class="student-info-cell">
        <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
      </td>
      @if($showGender)
        <td style="font-size:6pt;">{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
      @endif

      @foreach($subjects as $subId => $subInfo)
        @php $sd = $stu['subjects'][$subId] ?? []; $g = $sd['grade'] ?? '-'; $gc = $gradeColors[$g] ?? ''; @endphp
        @foreach($activeAssessments as $aIdx => $a)
          @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
          <td class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}">
            {{ $as > 0 ? number_format($as,1) : '—' }}
          </td>
        @endforeach
        @if($showTotal)    <td class="{{ $gc }}">{{ ($sd['total']??0)>0 ? number_format($sd['total'],1) : '—' }}</td> @endif
        @if($showBF)        <td>{{ ($sd['bf']??0)>0 ? number_format($sd['bf'],1) : '—' }}</td> @endif
        @if($showCum)      <td class="{{ $gc }}" style="font-weight:bold;">{{ ($sd['cum']??0)>0 ? number_format($sd['cum'],1) : '—' }}</td> @endif
        @if($showGrade)    <td class="{{ $gc }}" style="font-weight:bold;">{{ $g }}</td> @endif
        @if($showPosition) <td style="font-size:6pt;">{{ $sd['position'] ?? '—' }}</td> @endif
        @if($showAvg)      <td style="font-size:5.5pt;color:#6b7280;">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td> @endif
        @if($showRemark)   <td style="font-size:5.5pt;white-space:nowrap;">{{ $sd['remark'] ?? '—' }}</td> @endif
      @endforeach

      {{-- Performance Summary Cell --}}
      <td class="performance-cell">
        <div class="performance-strip">
            <table>
                <tr>
                    <td class="performance-label">Obtained (Cum):</td>
                    <td class="performance-value" style="text-align:right;">{{ number_format($stu['total_cum'], 1) }}</td>
                </tr>
                <tr>
                    <td class="performance-label">Obtainable:</td>
                    <td class="performance-value" style="text-align:right;">{{ $totalObtainable }}</td>
                </tr>
                <tr>
                    <td class="performance-label">% Obtained:</td>
                    <td class="performance-value {{ $termPercentage < 50 ? 'score-red' : ($termPercentage < 70 ? 'score-amber' : 'score-green') }}" style="text-align:right;">
                        {{ $termPercentage }}%
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:2px;">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar {{ $termColorClass }}" style="width: {{ $termPercentage }}%;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="performance-label">GPA:</td>
                    <td class="performance-value" style="text-align:right;">{{ number_format($stu['gpa'], 2) }}</td>
                </tr>
                <tr>
                    <td class="performance-label">CGPA:</td>
                    <td class="performance-value" style="text-align:right;">{{ number_format($stu['cgpa'], 2) }}</td>
                </tr>
            </table>
        </div>
      </td>

      @if($showGPA)      <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>      @endif
      @if($showCGPA)     <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td> @endif
      @if($showGPAGrade) @php $ggc = $gradeColors[$stu['gpa_grade']??'-']??''; @endphp
                         <td class="gpa-cell {{ $ggc }}" style="font-weight:bold;">{{ $stu['gpa_grade']??'—' }}</td> @endif
      @if($showNumSub)   <td>{{ $stu['num_subjects']??'—' }}</td> @endif
      @if($showTotalGP)  <td>{{ number_format($stu['total_grade_points'],1) }}</td> @endif
    </tr>
  @endforeach

  {{-- Stats rows --}}
  @php
  $statRows = [
    ['CLASS AVG', 'stats-avg', 'avg'],
    ['HIGHEST',   'stats-hi',  'highest'],
    ['LOWEST',    'stats-lo',  'lowest'],
  ];
  $styles = ['stats-avg'=>'background:#1e3a5f','stats-hi'=>'background:#0a2240','stats-lo'=>'background:#111c2a'];
  @endphp
  @foreach($statRows as [$label, $cssClass, $key])
    <tr class="stats-row {{ $cssClass }}" style="{{ $styles[$cssClass] ?? '' }}">
      <td class="stats-label" colspan="{{ $frozenCols }}">{{ $label }}</td>
      @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? []; @endphp
        @foreach($activeAssessments as $a) <td>—</td> @endforeach
        @if($showTotal)    <td>{{ $st[$key] ?? '—' }}</td> @endif
        @if($showBF)       <td>—</td> @endif
        @if($showCum)      <td>—</td> @endif
        @if($showGrade)    <td>—</td> @endif
        @if($showPosition) <td>—</td> @endif
        @if($showAvg)      <td>{{ $key==='avg' ? ($st['avg']??'—') : '—' }}</td> @endif
        @if($showRemark)   <td>—</td> @endif
      @endforeach
      <td class="stats-label">—</td>
      @if($showGPA)      <td>—</td> @endif
      @if($showCGPA)     <td>—</td> @endif
      @if($showGPAGrade) <td>—</td> @endif
      @if($showNumSub)   <td>—</td> @endif
      @if($showTotalGP)  <td>—</td> @endif
    </tr>
  @endforeach

</tbody>
</table>

{{-- ── Pass/Fail Summary ── --}}
<div class="summary-section">
  <table class="sum-table">
    <thead>
      <tr>
        <th style="min-width:120px;">Subject</th>
        <th style="text-align:center;min-width:35px;">Avg</th>
        <th style="text-align:center;min-width:40px;">Highest</th>
        <th style="text-align:center;min-width:35px;">Lowest</th>
        <th style="text-align:center;min-width:35px;">Passed</th>
        <th style="text-align:center;min-width:35px;">Failed</th>
        <th style="text-align:center;min-width:50px;">Pass Rate</th>
      </tr>
    </thead>
    <tbody>
      @foreach($subjects as $subId => $subInfo)
        @php
          $st = $subjectStats[$subId] ?? [];
          $p  = $st['passed'] ?? 0;
          $f  = $st['failed'] ?? 0;
          $t  = $p + $f;
          $pr = $t > 0 ? round($p/$t*100) : 0;
        @endphp
        <tr style="{{ $loop->iteration % 2 === 0 ? 'background:#f0f4fa;' : '' }}">
          <td style="font-weight:600;">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
              <span style="color:#6b7280;font-size:5.5pt;">({{ $subInfo['subject_code'] }})</span>
            @endif
          </td>
          <td style="text-align:center;font-weight:bold;">{{ $st['avg'] ?? '—' }}</td>
          <td style="text-align:center;color:#16a34a;font-weight:bold;">{{ $st['highest'] ?? '—' }}</td>
          <td style="text-align:center;color:#dc2626;font-weight:bold;">{{ $st['lowest'] ?? '—' }}</td>
          <td style="text-align:center;color:#16a34a;">{{ $p }}</td>
          <td style="text-align:center;color:#dc2626;">{{ $f }}</td>
          <td style="text-align:center;">
            <span class="{{ $pr >= 50 ? 'pass-good' : 'pass-bad' }}">{{ $pr }}%</span>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- ── Signatures ── --}}
<div class="signature-row">
    <div class="sig-cell"><div class="sig-line">Class Teacher</div></div>
    <div class="sig-cell"><div class="sig-line">Head of Department</div></div>
    <div class="sig-cell"><div class="sig-line">Vice Principal</div></div>
    <div class="sig-cell"><div class="sig-line">Principal</div></div>
</div>

{{-- ── Footer ── --}}
<div class="page-footer">
    <div class="footer-left">{{ $schoolInfo->school_name ?? '' }} — Confidential Academic Record</div>
    <div class="footer-right">Generated: {{ $generatedAt }}</div>
</div>

</body>
</html>
