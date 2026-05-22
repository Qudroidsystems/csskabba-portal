<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Broadsheet</title>
<style>
/* ── PDF-safe reset ─────────────────────────────────────────────────── */
* { margin:0; padding:0; box-sizing:border-box; }

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
.school-name    { font-size: 13pt; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.4px; }
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
.stat-cards { display: table; width: 100%; margin-bottom: 8px; border-collapse: separate; border-spacing: 3px; }
.stat-card {
    display: table-cell;
    background: linear-gradient(135deg, #f0f4fa, #e8f0fe);
    border: 1px solid #c5d3e8;
    border-radius: 6px;
    padding: 5px 8px;
    text-align: center;
    width: 25%;
}
.stat-card-accent {
    display: block;
    height: 2px;
    border-radius: 2px;
    margin-bottom: 4px;
}
.stat-value {
    font-size: 13pt;
    font-weight: bold;
    color: #1e3a5f;
    display: block;
}
.stat-label {
    font-size: 5.5pt;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ── Meta info ───────────────────────────────────────────────────────── */
.meta-grid { display: table; width: 100%; margin-bottom: 7px; border: 1px solid #c5d3e8; background: #f0f4fa; border-radius: 4px; overflow: hidden; }
.meta-cell {
    display: table-cell;
    padding: 5px 8px;
    border-right: 1px solid #c5d3e8;
    vertical-align: middle;
}
.meta-cell:last-child { border-right: none; }
.meta-label { font-size: 6pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; display: block; margin-bottom: 1px; }
.meta-value { font-size: 8.5pt; font-weight: bold; color: #1e3a5f; }

/* ── Grade key ───────────────────────────────────────────────────────── */
.grade-key-wrap { display: table; width: 100%; margin-bottom: 7px; border: 1px solid #e2e8f0; padding: 4px 6px; background: #fafafa; border-radius: 4px; }
.grade-key-title { font-size: 6.5pt; font-weight: bold; color: #1e3a5f; display: table-cell; width: 75px; vertical-align: middle; }
.grade-key-items { display: table-cell; vertical-align: middle; }
.grade-item { display: inline-block; margin-right: 7px; font-size: 6pt; }
.grade-badge-key { display: inline-block; padding: 1px 5px; border-radius: 3px; font-weight: bold; color: white; font-size: 6.5pt; }

/* ── Position Badge ──────────────────────────────────────────────────── */
.pos-badge {
    display: inline-block;
    width: 22px; height: 22px;
    border-radius: 50%;
    font-size: 7pt; font-weight: 800;
    border: 1.5px solid;
    text-align: center; line-height: 19px;
    vertical-align: middle;
}
.pos-1     { background: #fef9c3; border-color: #f59e0b; color: #92400e; }
.pos-2     { background: #f1f5f9; border-color: #94a3b8; color: #475569; }
.pos-3     { background: #ffedd5; border-color: #f97316; color: #9a3412; }
.pos-other { background: #f0f4fa; border-color: #c5d3e8; color: #6b7280; font-size: 6.5pt; }

/* ── Progress Bar ────────────────────────────────────────────────────── */
.pct-bar-outer {
    background: #374151;
    border-radius: 3px;
    height: 4px;
    width: 100%;
    overflow: hidden;
    margin-top: 2px;
    margin-bottom: 2px;
}
.pct-bar-inner {
    height: 4px;
    border-radius: 3px;
}
.bar-red   { background: #f43f5e; }
.bar-amber { background: #f59e0b; }
.bar-green { background: #22c55e; }

/* ── Performance Summary Cell ────────────────────────────────────────── */
/*
  This cell replaces the old "Performance Summary" column.
  It shows ALL six metrics in a compact dark strip — matching
  the popup design in the web view.
*/
.perf-strip {
    background: #0f2342;
    border-radius: 5px;
    padding: 4px 5px;
    color: white;
}
.perf-strip-title {
    font-size: 5.5pt;
    font-weight: bold;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
    border-bottom: 0.5px solid rgba(255,255,255,0.2);
    padding-bottom: 2px;
}
/* 3-column metrics grid (DomPDF-safe: use table) */
.perf-metrics { width: 100%; border-collapse: collapse; }
.perf-metrics td {
    padding: 1px 2px;
    border: none;
    white-space: nowrap;
    vertical-align: middle;
}
.pm-lbl {
    font-size: 5pt;
    opacity: 0.75;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #a8d4ef;
    display: block;
}
.pm-val {
    font-size: 7pt;
    font-weight: bold;
    color: #ffffff;
    display: block;
}
.pm-val-red   { color: #fca5a5 !important; }
.pm-val-amber { color: #fcd34d !important; }
.pm-val-green { color: #86efac !important; }
/* Position pill inside perf strip */
.pos-pill {
    display: inline-block;
    background: rgba(245,158,11,0.25);
    border: 1px solid rgba(245,158,11,0.5);
    border-radius: 10px;
    padding: 0px 5px;
    font-size: 6pt;
    font-weight: bold;
    color: #fcd34d;
    margin-top: 3px;
}

/* ── Score colors ────────────────────────────────────────────────────── */
.score-red   { color: #dc2626 !important; font-weight: bold; }
.score-amber { color: #d97706 !important; font-weight: bold; }
.score-green { color: #16a34a !important; font-weight: bold; }

/* ── Grade colour coding ─────────────────────────────────────────────── */
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

/* ── MAIN TABLE ──────────────────────────────────────────────────────── */
.broadsheet-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #1e3a5f;
    font-size: 6.5pt;
    page-break-inside: auto;
    table-layout: auto;
}
.broadsheet-table thead tr.subject-header th {
    background: #1e3a5f;
    color: white;
    text-align: center;
    padding: 4px 2px;
    border: 0.5px solid rgba(37,99,235,.35);
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
.broadsheet-table thead tr.assessment-header th {
    background: #1a3d6a;
    color: #a8d4ef;
    text-align: center;
    padding: 3px 2px;
    border: 0.5px solid rgba(37,99,235,.2);
    font-size: 6pt;
    white-space: nowrap;
}
.broadsheet-table thead tr.assessment-header th.sub-boundary { border-left: 1.5px solid #2563eb; }

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
.broadsheet-table tbody td.sn-cell   { color: #6b7280; font-size: 6pt; width: 18px; text-align: center; }
.broadsheet-table tbody td.adm-cell  { font-size: 6pt; color: #4a6e8a; min-width: 50px; }
.broadsheet-table tbody td.sub-boundary { border-left: 1.5px solid rgba(37,99,235,.4); }
.broadsheet-table tbody td.perf-col  { padding: 4px 4px; min-width: 155px; vertical-align: top; }

/* GPA columns */
.gpa-cell { background: #eff6ff !important; color: #1e3a8a; font-weight: bold; border-left: 1.5px solid #3b82f6 !important; }

/* ── Stats footer rows ───────────────────────────────────────────────── */
.stats-row td {
    background: #1e3a5f !important;
    color: white;
    font-weight: bold;
    padding: 3px 2px;
    text-align: center;
    border: 0.5px solid #163785;
    white-space: nowrap;
}
.stats-row td.stats-label { text-align: left; padding-left: 4px; font-size: 6pt; font-weight: bold; }
.stats-hi td { background: #0a2240 !important; }
.stats-lo td { background: #111c2a !important; }

/* ── Subject summary table ───────────────────────────────────────────── */
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
    border: 0.5px solid rgba(37,99,235,.35);
}
.sum-table tbody tr:nth-child(even) { background: #f0f4fa; }
.sum-table tbody td { padding: 3px 5px; border: 0.5px solid #c5d3e8; }
.pass-good { background: #dcfce7; color: #166534; font-weight: bold; padding: 1px 5px; border-radius: 3px; }
.pass-bad  { background: #fee2e2; color: #991b1b; font-weight: bold; padding: 1px 5px; border-radius: 3px; }

/* ── Signatures ─────────────────────────────────────────────────────── */
.signature-row { margin-top: 18px; display: table; width: 100%; }
.sig-cell { display: table-cell; width: 25%; text-align: center; padding: 0 8px; vertical-align: bottom; }
.sig-line { border-top: 1px solid #333; padding-top: 3px; font-size: 7pt; color: #374151; margin-top: 26px; }

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
</style>
</head>
<body>

@php
    /* ── Position map (by cumulative total) ── */
    $positionMap = [];
    $sortedByCum = collect($studentRows)->sortByDesc('total_cum')->values();
    $prevPct = null; $prevPos = 0; $counter = 0;
    foreach ($sortedByCum as $stu) {
        $counter++;
        if ($prevPct !== null && $stu['total_cum'] == $prevPct) {
            $positionMap[$stu['id']] = $prevPos;
        } else {
            $positionMap[$stu['id']] = $counter;
            $prevPos = $counter;
        }
        $prevPct = $stu['total_cum'];
    }
    $totalClassStudents = count($studentRows);

    /* ── Class-level stats ── */
    $subjectCount    = count($subjects);
    $totalObtainable = $subjectCount * 100;
    $cumPctSum       = 0;
    $topCum          = -1;
    $topName         = '—';
    foreach ($studentRows as $stu) {
        $cp = $totalObtainable > 0 ? ($stu['total_cum'] / $totalObtainable) * 100 : 0;
        $cumPctSum += $cp;
        if ($stu['total_cum'] > $topCum) {
            $topCum  = $stu['total_cum'];
            $topName = trim(($stu['lastname'] ?? '') . ' ' . ($stu['firstname'] ?? ''));
        }
    }
    $avgClassPct = $totalClassStudents > 0 ? round($cumPctSum / $totalClassStudents, 1) : 0;

    /* ── Grade colour map ── */
    $gradeColors = [
        'A1'=>'score-a1','B2'=>'score-b2','B3'=>'score-b3',
        'C4'=>'score-c4','C5'=>'score-c5','C6'=>'score-c6',
        'D7'=>'score-d7','E8'=>'score-e8','F9'=>'score-f9','-'=>'score-empty',
    ];

    /* ── Column visibility ── */
    $selected     = $selectedColumns ?? [];
    $showAdmNo    = empty($selected) || in_array('admission_no',      $selected);
    $showGender   = in_array('gender',                                 $selected);
    $showTotal    = empty($selected) || in_array('total',             $selected);
    $showBF       = in_array('bf',                                     $selected);
    $showCum      = empty($selected) || in_array('cum',               $selected);
    $showGrade    = empty($selected) || in_array('grade',             $selected);
    $showPosition = empty($selected) || in_array('position',          $selected);
    $showAvg      = in_array('class_average',                          $selected);
    $showRemark   = in_array('remark',                                 $selected);
    $showGPA      = in_array('gpa',                                    $selected);
    $showCGPA     = in_array('cgpa',                                   $selected);
    $showGPAGrade = in_array('gpa_grade',                              $selected);
    $showNumSub   = in_array('num_subjects',                           $selected);
    $showTotalGP  = in_array('total_grade_points',                     $selected);

    $activeAssessments = $assessments->filter(fn($a) =>
        empty($selected) || in_array('assessment_' . $a->id, $selected)
    );

    /* Colspan per subject block */
    $subColspan = $activeAssessments->count();
    if ($showTotal)    $subColspan++;
    if ($showBF)       $subColspan++;
    if ($showCum)      $subColspan++;
    if ($showGrade)    $subColspan++;
    if ($showPosition) $subColspan++;
    if ($showAvg)      $subColspan++;
    if ($showRemark)   $subColspan++;
    $subColspan = max(1, $subColspan);

    $frozenCols = 2 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0);
    $gpaColspan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);
@endphp

{{-- ══════════════════════════════════════════
     SCHOOL HEADER
══════════════════════════════════════════ --}}
<div class="school-header">
    <div class="header-inner">
        <div class="header-logo-cell">
            @if(!empty($school_logo_base64))
                <img src="{{ $school_logo_base64 }}" alt="School Logo">
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
                    @if(!empty($schoolInfo->school_email)) &nbsp;|&nbsp; Email: {{ $schoolInfo->school_email }} @endif
                </div>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <div class="school-motto">"{{ $schoolInfo->school_motto }}"</div>
            @endif
        </div>
        <div class="header-logo-cell">{{-- right spacer for symmetry --}}</div>
    </div>
</div>

<div class="doc-title-strip">
    CLASS ACADEMIC BROADSHEET
    @if(!empty($is_combined))
        &nbsp;—&nbsp;<span style="font-size:8pt;font-weight:400;opacity:.8;">Combined Arms</span>
    @endif
</div>

{{-- ══════════════════════════════════════════
     STAT CARDS
══════════════════════════════════════════ --}}
<table class="stat-cards">
    <tr>
        <td class="stat-card">
            <span class="stat-card-accent" style="background:linear-gradient(90deg,#0f2342,#0d9488);"></span>
            <span class="stat-value">{{ $totalClassStudents }}</span>
            <span class="stat-label">Total Students</span>
        </td>
        <td class="stat-card">
            <span class="stat-card-accent" style="background:linear-gradient(90deg,#0ea5e9,#38bdf8);"></span>
            <span class="stat-value">{{ $subjectCount }}</span>
            <span class="stat-label">Subjects</span>
        </td>
        <td class="stat-card">
            <span class="stat-card-accent" style="background:linear-gradient(90deg,#22c55e,#4ade80);"></span>
            <span class="stat-value" style="{{ $avgClassPct < 50 ? 'color:#dc2626;' : ($avgClassPct < 70 ? 'color:#d97706;' : 'color:#16a34a;') }}">{{ $avgClassPct }}%</span>
            <span class="stat-label">Avg % (Cumulative)</span>
        </td>
        <td class="stat-card">
            <span class="stat-card-accent" style="background:linear-gradient(90deg,#f59e0b,#fcd34d);"></span>
            <span class="stat-value" style="font-size:9pt;">{{ $topName }}</span>
            <span class="stat-label">Top Performer (Cum)</span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════
     META ROW
══════════════════════════════════════════ --}}
<div class="meta-grid">
    <div class="meta-cell">
        <span class="meta-label">Class</span>
        <span class="meta-value">{{ ($schoolclass->schoolclass ?? '-') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
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
        <span class="meta-value">{{ $totalClassStudents }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">No. of Subjects</span>
        <span class="meta-value">{{ $subjectCount }}</span>
    </div>
    <div class="meta-cell">
        <span class="meta-label">Generated</span>
        <span class="meta-value" style="font-size:7pt;">{{ $generatedAt }}</span>
    </div>
</div>

{{-- ══════════════════════════════════════════
     GRADE KEY
══════════════════════════════════════════ --}}
<div class="grade-key-wrap">
    <div class="grade-key-title">GRADING SCALE:</div>
    <div class="grade-key-items">
        @php
        $gradeKeyList = [
            'A1'=>['75–100','#16a34a'],'B2'=>['70–74','#1d4ed8'],'B3'=>['65–69','#2563eb'],
            'C4'=>['60–64','#d97706'], 'C5'=>['55–59','#b45309'],'C6'=>['50–54','#92400e'],
            'D7'=>['45–49','#ea580c'], 'E8'=>['40–44','#c2410c'],'F9'=>['0–39', '#dc2626'],
        ];
        @endphp
        @foreach($gradeKeyList as $grade => $info)
            <span class="grade-item">
                <span class="grade-badge-key" style="background:{{ $info[1] }};">{{ $grade }}</span>
                {{ $info[0] }}
            </span>
        @endforeach
        &nbsp;&nbsp;
        <span style="font-size:6pt;color:#555;">
            <strong>BF</strong>=Brought Forward &nbsp;
            <strong>CUM</strong>=Cumulative &nbsp;
            <strong>POS</strong>=Position in Subject &nbsp;
            <strong>AVG</strong>=Class Average
        </span>
    </div>
</div>

{{-- ══════════════════════════════════════════
     MAIN BROADSHEET TABLE
══════════════════════════════════════════ --}}
<table class="broadsheet-table">
<thead>

  {{-- Row 1 — Subject name headers ── --}}
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
        <th class="subj-name-hdr" colspan="{{ $subColspan }}">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
                <br><span style="font-size:5.5pt;opacity:.75;">({{ $subInfo['subject_code'] }})</span>
            @endif
        </th>
    @endforeach

    {{-- Performance Summary column ── --}}
    <th class="subj-name-hdr" style="min-width:155px;background:#0a2240;border-left:2px solid #0d9488;">
        Performance Summary
    </th>

    @if($gpaColspan > 0)
        <th colspan="{{ $gpaColspan }}" style="background:#0a1e38;border-left:2px solid #3b82f6;font-size:6pt;">
            GPA METRICS
        </th>
    @endif
  </tr>

  {{-- Row 2 — Assessment / score sub-headers ── --}}
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

    <th style="min-width:155px;background:#0a2240;border-left:2px solid #0d9488;">
        Metrics
    </th>

    @if($showGPA)      <th style="background:#0a1e38;color:#93c5fd;min-width:26px;border-left:2px solid #3b82f6;">GPA</th>   @endif
    @if($showCGPA)     <th style="background:#0a1e38;color:#86efac;min-width:26px;">CGPA</th>    @endif
    @if($showGPAGrade) <th style="background:#0a1e38;color:#fcd34d;min-width:22px;">GGrd</th>   @endif
    @if($showNumSub)   <th style="background:#0a1e38;color:#a8d4ef;min-width:22px;">NS</th>     @endif
    @if($showTotalGP)  <th style="background:#0a1e38;color:#a8d4ef;min-width:24px;">TGP</th>   @endif
  </tr>

</thead>
<tbody>

  @foreach($studentRows as $idx => $stu)
  @php
      /* ── Per-student analytics ── */
      $sid  = $stu['id'];
      $pos  = $positionMap[$sid] ?? 0;
      $suffix = $pos === 1 ? 'st' : ($pos === 2 ? 'nd' : ($pos === 3 ? 'rd' : 'th'));

      /* Term total = sum of 'total' field across all subjects */
      $termObtained = 0;
      foreach ($stu['subjects'] as $sd) { $termObtained += ($sd['total'] ?? 0); }
      $termObtained = round($termObtained, 1);

      $cumObtained = round($stu['total_cum'], 1);

      $termPct = $totalObtainable > 0 ? round(($termObtained / $totalObtainable) * 100, 1) : 0;
      $cumPct  = $totalObtainable > 0 ? round(($cumObtained  / $totalObtainable) * 100, 1) : 0;

      $termBarClass = $termPct < 40 ? 'bar-red' : ($termPct < 70 ? 'bar-amber' : 'bar-green');
      $cumBarClass  = $cumPct  < 40 ? 'bar-red' : ($cumPct  < 70 ? 'bar-amber' : 'bar-green');

      $termValClass = $termPct < 50 ? 'pm-val-red' : ($termPct < 70 ? 'pm-val-amber' : 'pm-val-green');
      $cumValClass  = $cumPct  < 50 ? 'pm-val-red' : ($cumPct  < 70 ? 'pm-val-amber' : 'pm-val-green');

      $posClass  = $pos === 1 ? 'pos-1' : ($pos === 2 ? 'pos-2' : ($pos === 3 ? 'pos-3' : 'pos-other'));
      $posIcon   = $pos === 1 ? '1st'   : ($pos === 2 ? '2nd'   : ($pos === 3 ? '3rd'   : $pos));
      $posIconEmoji = $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : ''));
  @endphp
  <tr>
    <td class="sn-cell">{{ $idx + 1 }}</td>
    <td style="text-align:center;">
        <span class="pos-badge {{ $posClass }}">{{ $posIconEmoji ?: $pos }}</span>
    </td>
    @if($showAdmNo)
        <td class="adm-cell">{{ $stu['admissionno'] }}</td>
    @endif
    <td class="student-info-cell">
        <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
        @if(!empty($stu['arm']))
            <span style="font-size:5.5pt;color:#6b7280;"> — Arm {{ $stu['arm'] }}</span>
        @endif
    </td>
    @if($showGender)
        <td style="font-size:6pt;">{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
    @endif

    {{-- ── Score cells ── --}}
    @foreach($subjects as $subId => $subInfo)
        @php $sd = $stu['subjects'][$subId] ?? []; $g = $sd['grade'] ?? '-'; $gc = $gradeColors[$g] ?? ''; @endphp
        @foreach($activeAssessments as $aIdx => $a)
            @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
            <td class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}">
                {{ $as > 0 ? number_format($as,1) : '—' }}
            </td>
        @endforeach
        @if($showTotal)    <td class="{{ $gc }}">{{ ($sd['total']??0)>0 ? number_format($sd['total'],1) : '—' }}</td>           @endif
        @if($showBF)       <td>{{ ($sd['bf']??0)>0 ? number_format($sd['bf'],1) : '—' }}</td>                                   @endif
        @if($showCum)      <td class="{{ $gc }}" style="font-weight:bold;">{{ ($sd['cum']??0)>0 ? number_format($sd['cum'],1) : '—' }}</td> @endif
        @if($showGrade)    <td class="{{ $gc }}" style="font-weight:bold;">{{ $g }}</td>                                          @endif
        @if($showPosition) <td style="font-size:6pt;">{{ $sd['position'] ?? '—' }}</td>                                          @endif
        @if($showAvg)      <td style="font-size:5.5pt;color:#6b7280;">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td>              @endif
        @if($showRemark)   <td style="font-size:5.5pt;white-space:nowrap;">{{ $sd['remark'] ?? '—' }}</td>                        @endif
    @endforeach

    {{-- ══════════════════════════════════════════
         PERFORMANCE SUMMARY CELL
         Shows all 6 metrics + progress bars,
         matching the web-view popup exactly.
    ══════════════════════════════════════════ --}}
    <td class="perf-col">
        <div class="perf-strip">
            <div class="perf-strip-title">&#9679; Performance Snapshot</div>

            {{-- 3-column grid: row 1 ── --}}
            <table class="perf-metrics">
                <tr>
                    <td style="width:33%;">
                        <span class="pm-lbl">Obtained (Term)</span>
                        <span class="pm-val">{{ number_format($termObtained, 1) }}</span>
                    </td>
                    <td style="width:34%;">
                        <span class="pm-lbl">Obtained (Cum)</span>
                        <span class="pm-val">{{ number_format($cumObtained, 1) }}</span>
                    </td>
                    <td style="width:33%;">
                        <span class="pm-lbl">Obtainable</span>
                        <span class="pm-val">{{ $totalObtainable }}</span>
                    </td>
                </tr>
            </table>

            {{-- Term % with progress bar ── --}}
            <table class="perf-metrics" style="margin-top:3px;">
                <tr>
                    <td style="width:50%;">
                        <span class="pm-lbl">% Obtained (Term)</span>
                        <span class="pm-val {{ $termValClass }}">{{ $termPct }}%</span>
                        <div class="pct-bar-outer">
                            <div class="pct-bar-inner {{ $termBarClass }}" style="width:{{ $termPct }}%;"></div>
                        </div>
                    </td>
                    <td style="width:50%;">
                        <span class="pm-lbl">% Obtained (Cum)</span>
                        <span class="pm-val {{ $cumValClass }}">{{ $cumPct }}%</span>
                        <div class="pct-bar-outer">
                            <div class="pct-bar-inner {{ $cumBarClass }}" style="width:{{ $cumPct }}%;"></div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Position pill ── --}}
            <div style="margin-top:3px;text-align:center;">
                <span class="pos-pill">
                    {{ $pos }}{{ $suffix }} / {{ $totalClassStudents }}
                    @if($pos <= 3) &nbsp;{{ $posIconEmoji }} @endif
                </span>
            </div>
        </div>
    </td>

    {{-- GPA columns ── --}}
    @if($showGPA)      <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>      @endif
    @if($showCGPA)     <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td> @endif
    @if($showGPAGrade) @php $ggc = $gradeColors[$stu['gpa_grade']??'-'] ?? ''; @endphp
                       <td class="gpa-cell {{ $ggc }}" style="font-weight:bold;">{{ $stu['gpa_grade'] ?? '—' }}</td> @endif
    @if($showNumSub)   <td>{{ $stu['num_subjects'] ?? '—' }}</td>                         @endif
    @if($showTotalGP)  <td>{{ number_format($stu['total_grade_points'],1) }}</td>         @endif
  </tr>
  @endforeach

  {{-- Stats footer rows ── --}}
  @php
    $statDefs = [
        ['CLASS AVG', '',        'avg'],
        ['HIGHEST',   'stats-hi','highest'],
        ['LOWEST',    'stats-lo','lowest'],
    ];
  @endphp
  @foreach($statDefs as [$label, $cssClass, $key])
    <tr class="stats-row {{ $cssClass }}">
      <td class="stats-label" colspan="{{ $frozenCols }}">{{ $label }}</td>
      @foreach($subjects as $subId => $subInfo)
          @php $st = $subjectStats[$subId] ?? []; @endphp
          @foreach($activeAssessments as $a) <td>—</td> @endforeach
          @if($showTotal)    <td>{{ $st[$key] ?? '—' }}</td>                                 @endif
          @if($showBF)       <td>—</td>                                                       @endif
          @if($showCum)      <td>—</td>                                                       @endif
          @if($showGrade)    <td>—</td>                                                       @endif
          @if($showPosition) <td>—</td>                                                       @endif
          @if($showAvg)      <td>{{ $key === 'avg' ? ($st['avg'] ?? '—') : '—' }}</td>        @endif
          @if($showRemark)   <td>—</td>                                                       @endif
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

{{-- ══════════════════════════════════════════
     SUBJECT PERFORMANCE SUMMARY
══════════════════════════════════════════ --}}
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
                    $pr = $t > 0 ? round($p / $t * 100) : 0;
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

{{-- ══════════════════════════════════════════
     SIGNATURES
══════════════════════════════════════════ --}}
<div class="signature-row">
    <div class="sig-cell"><div class="sig-line">Class Teacher</div></div>
    <div class="sig-cell"><div class="sig-line">Head of Department</div></div>
    <div class="sig-cell"><div class="sig-line">Vice Principal</div></div>
    <div class="sig-cell"><div class="sig-line">Principal</div></div>
</div>

{{-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ --}}
<div class="page-footer">
    <div class="footer-left">{{ $schoolInfo->school_name ?? '' }} — Confidential Academic Record</div>
    <div class="footer-right">Generated: {{ $generatedAt }}</div>
</div>

</body>
</html>
