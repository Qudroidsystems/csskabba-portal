<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Broadsheet</title>
<style>
/*
 ═══════════════════════════════════════════════════════════════
  BROADSHEET PDF — DomPDF rendering rules
  KEY: page size is set dynamically by controller to exactly
  match table width. We never use max-width or overflow:hidden.
  table-layout:auto + white-space:nowrap = table drives width.
 ═══════════════════════════════════════════════════════════════
*/

/* ── Reset ─────────────────────────────────────────────────── */
* { margin: 0; padding: 0; box-sizing: border-box; }

/* ── Page ───────────────────────────────────────────────────── */
@page {
    margin: 7mm 9mm;
    /* size is determined by DomPDF setPaper() in controller */
}

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.5pt;
    color: #111827;
    background: #ffffff;
    line-height: 1.3;
    /* NEVER constrain width — let table dictate it */
    width: 100%;
    page-break-inside: auto;
}

/* ── School Header ──────────────────────────────────────────── */
.header-wrap {
    border-bottom: 2.5pt solid #0f2744;
    margin-bottom: 6pt;
    padding-bottom: 6pt;
}
.header-table {
    width: 100%;
    border-collapse: collapse;
}
.header-logo-cell {
    width: 72pt;
    text-align: center;
    vertical-align: middle;
    padding-right: 6pt;
}
.header-logo-cell img {
    width: 62pt;
    height: 62pt;
    object-fit: contain;
    border: 2pt solid #0f2744;
    border-radius: 50%;
}
.header-text-cell {
    text-align: center;
    vertical-align: middle;
}
.sch-name {
    font-size: 14pt;
    font-weight: bold;
    color: #0f2744;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
}
.sch-address { font-size: 7.5pt; color: #374151; margin-top: 2pt; }
.sch-contact { font-size: 7pt;   color: #4b5563; margin-top: 1pt; }
.sch-motto   { font-size: 7.5pt; color: #1d4ed8; font-style: italic; margin-top: 2pt; font-weight: bold; }

/* ── Title Strip ─────────────────────────────────────────────── */
.title-strip {
    background: #0f2744;
    color: #ffffff;
    text-align: center;
    padding: 4.5pt 8pt;
    font-size: 10pt;
    font-weight: bold;
    letter-spacing: 1pt;
    text-transform: uppercase;
    margin-bottom: 6pt;
}

/* ── Meta Row ────────────────────────────────────────────────── */
.meta-table {
    width: 100%;
    border-collapse: collapse;
    border: 1pt solid #c5d3e8;
    background: #eff6ff;
    margin-bottom: 6pt;
}
.meta-td {
    padding: 3.5pt 8pt;
    border-right: 1pt solid #c5d3e8;
    vertical-align: middle;
}
.meta-td:last-child { border-right: none; }
.meta-lbl { font-size: 6pt; color: #4b5563; text-transform: uppercase; letter-spacing: 0.3pt; display: block; }
.meta-val { font-size: 8.5pt; font-weight: bold; color: #0f2744; }

/* ── Grade Key ───────────────────────────────────────────────── */
.grade-key-table {
    width: 100%;
    border-collapse: collapse;
    border: 1pt solid #e2e8f0;
    background: #fafafa;
    margin-bottom: 6pt;
    padding: 3pt 5pt;
}
.grade-key-title-td {
    width: 65pt;
    vertical-align: middle;
    font-size: 6.5pt;
    font-weight: bold;
    color: #0f2744;
    padding: 3pt 5pt;
}
.grade-key-items-td {
    vertical-align: middle;
    padding: 3pt 5pt;
}
.gk { display: inline-block; margin-right: 6pt; font-size: 6pt; }
.gk-badge {
    display: inline-block;
    padding: 1pt 3.5pt;
    border-radius: 2pt;
    font-weight: bold;
    color: #ffffff;
    font-size: 6.5pt;
}
.gk-legend { font-size: 6pt; color: #4b5563; }

/* ═══════════════════════════════════════════════════════════════
   THE BROADSHEET TABLE
   CRITICAL rules for DomPDF full-width rendering:
   - table-layout: auto  (never fixed — let content define width)
   - white-space: nowrap (prevents line-wrapping that confuses width calc)
   - width: max-content  (ensures table is as wide as it needs to be)
   - No max-width, no overflow anywhere
 ═══════════════════════════════════════════════════════════════ */
.bs-table {
    border-collapse: collapse;
    border: 1.5pt solid #0f2744;
    font-size: 6.5pt;
    table-layout: auto;
    width: max-content;
    /* DomPDF renders this at full computed width — no clipping */
    page-break-inside: auto;
}

/* Subject header row */
.bs-table thead tr.th-subj th {
    background: #0f2744;
    color: #ffffff;
    font-weight: bold;
    font-size: 6.5pt;
    text-align: center;
    padding: 3.5pt 2pt;
    border: 0.5pt solid rgba(255,255,255,.15);
    white-space: nowrap;
    vertical-align: middle;
}
.bs-table thead tr.th-subj th.th-frozen {
    background: #07192f;
    text-align: left;
    padding-left: 4pt;
}
.bs-table thead tr.th-subj th.th-subject {
    background: #163562;
    border-left: 2pt solid #3b82f6;
    font-size: 6pt;
    white-space: normal;
    word-break: break-word;
    min-width: 50pt;
    max-width: 90pt;
}

/* Assessment sub-header row */
.bs-table thead tr.th-asm th {
    background: #1a3d6a;
    color: rgba(255,255,255,.82);
    font-size: 5.5pt;
    font-weight: bold;
    text-align: center;
    padding: 2.5pt 2pt;
    border: 0.5pt solid rgba(255,255,255,.12);
    white-space: nowrap;
    vertical-align: middle;
}
.bs-table thead tr.th-asm th.th-boundary {
    border-left: 2pt solid #3b82f6;
}

/* Data rows */
.bs-table tbody tr { page-break-inside: avoid; }
.bs-table tbody tr:nth-child(odd)  { background: #ffffff; }
.bs-table tbody tr:nth-child(even) { background: #eff6ff; }

.bs-table tbody td {
    padding: 2pt 2.5pt;
    border: 0.5pt solid #c5d3e8;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    font-size: 6.5pt;
    color: #111827;
}
.bs-table tbody td.td-sn {
    width: 18pt;
    font-size: 6pt;
    color: #6b7280;
    text-align: center;
    font-weight: 700;
}
.bs-table tbody td.td-adm {
    min-width: 48pt;
    font-size: 6pt;
    color: #374151;
}
.bs-table tbody td.td-name {
    min-width: 100pt;
    text-align: left;
    padding-left: 4pt;
    font-weight: 700;
    font-size: 6.5pt;
    white-space: nowrap;
}
.bs-table tbody td.td-boundary {
    border-left: 2pt solid #93c5fd;
}

/* ── Grade colour cells ─────────────────────────────────────── */
.sc-A1 { background: #dcfce7 !important; color: #15803d; font-weight: bold; }
.sc-B2 { background: #dbeafe !important; color: #1e40af; font-weight: bold; }
.sc-B3 { background: #e0eeff !important; color: #1d4ed8; font-weight: bold; }
.sc-C4 { background: #fef9c3 !important; color: #854d0e; }
.sc-C5 { background: #fef3c7 !important; color: #92400e; }
.sc-C6 { background: #fde68a !important; color: #78350f; }
.sc-D7 { background: #ffedd5 !important; color: #9a3412; }
.sc-E8 { background: #fed7aa !important; color: #9a3412; font-weight: bold; }
.sc-F9 { background: #fee2e2 !important; color: #991b1b; font-weight: bold; }
.sc-empty { color: #9ca3af; }

/* GPA cells */
.gpa-cell {
    background: #eff6ff !important;
    color: #4338ca;
    font-weight: bold;
    border-left: 2pt solid #6366f1 !important;
}

/* ── Stats rows ─────────────────────────────────────────────── */
.tr-stats td {
    background: #0f2744 !important;
    color: #ffffff;
    font-weight: bold;
    padding: 2.5pt 2pt;
    text-align: center;
    border: 0.5pt solid #163785;
    white-space: nowrap;
    font-size: 6pt;
}
.tr-stats td.td-lbl {
    text-align: left;
    padding-left: 4pt;
    font-size: 5.5pt;
    background: #07192f !important;
}
.tr-high td { background: #0a2240 !important; }
.tr-low  td { background: #111c2a !important; }
.tr-pass td { background: #052e16 !important; }
.tr-fail td { background: #450a0a !important; }

/* ── Summary Section ────────────────────────────────────────── */
.summary-wrap { margin-top: 9pt; page-break-inside: avoid; }
.sum-title {
    background: #0f2744;
    color: #ffffff;
    font-size: 7.5pt;
    font-weight: bold;
    padding: 4pt 8pt;
    margin-bottom: 0;
}
.sum-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 6.5pt;
    border: 1pt solid #c5d3e8;
}
.sum-table thead th {
    background: #1e3a5f;
    color: #ffffff;
    padding: 3pt 6pt;
    text-align: left;
    font-weight: bold;
    border: 0.5pt solid #2563eb44;
}
.sum-table tbody tr:nth-child(even) { background: #eff6ff; }
.sum-table tbody td {
    padding: 2.5pt 6pt;
    border: 0.5pt solid #e2e8f0;
    vertical-align: middle;
}
.pass-tag {
    display: inline-block;
    padding: 1pt 4pt;
    border-radius: 2pt;
    font-weight: bold;
    font-size: 6pt;
}
.pass-good { background: #dcfce7; color: #166534; }
.pass-bad  { background: #fee2e2; color: #991b1b; }

/* ── Signatures ─────────────────────────────────────────────── */
.sig-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16pt;
}
.sig-td {
    width: 25%;
    text-align: center;
    padding: 0 8pt;
    vertical-align: bottom;
}
.sig-line {
    border-top: 1pt solid #374151;
    padding-top: 3pt;
    font-size: 7pt;
    color: #374151;
    margin-top: 24pt;
}

/* ── Footer ─────────────────────────────────────────────────── */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 7pt;
    border-top: 1pt solid #e2e8f0;
    padding-top: 3pt;
}
.footer-left  { font-size: 6pt; color: #9ca3af; text-align: left; }
.footer-right { font-size: 6pt; color: #9ca3af; text-align: right; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- SCHOOL HEADER                                          --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="header-wrap">
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if(!empty($school_logo_base64))
                    <img src="{{ $school_logo_base64 }}" alt="Logo">
                @endif
            </td>
            <td class="header-text-cell">
                <div class="sch-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
                @if(!empty($schoolInfo->school_address))
                    <div class="sch-address">{{ $schoolInfo->school_address }}</div>
                @endif
                @if(!empty($schoolInfo->school_phone) || !empty($schoolInfo->school_email))
                    <div class="sch-contact">
                        @if(!empty($schoolInfo->school_phone)) Tel: {{ $schoolInfo->school_phone }} @endif
                        @if(!empty($schoolInfo->school_email)) &nbsp;| Email: {{ $schoolInfo->school_email }} @endif
                    </div>
                @endif
                @if(!empty($schoolInfo->school_motto))
                    <div class="sch-motto">"{{ $schoolInfo->school_motto }}"</div>
                @endif
            </td>
            <td class="header-logo-cell"></td>{{-- balance --}}
        </tr>
    </table>
</div>

<div class="title-strip">CLASS ACADEMIC BROADSHEET</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- META ROW                                               --}}
{{-- ══════════════════════════════════════════════════════ --}}
<table class="meta-table">
    <tr>
        <td class="meta-td">
            <span class="meta-lbl">Class</span>
            <span class="meta-val">{{ ($schoolclass->schoolclass??'-') . ' ' . ($schoolclass->arm_name??'') }}</span>
        </td>
        <td class="meta-td">
            <span class="meta-lbl">Academic Session</span>
            <span class="meta-val">{{ $schoolsession->session??'-' }}</span>
        </td>
        <td class="meta-td">
            <span class="meta-lbl">Term</span>
            <span class="meta-val">{{ $schoolterm->term??'-' }}</span>
        </td>
        <td class="meta-td">
            <span class="meta-lbl">No. of Students</span>
            <span class="meta-val">{{ $totalStudents }}</span>
        </td>
        <td class="meta-td">
            <span class="meta-lbl">No. of Subjects</span>
            <span class="meta-val">{{ count($subjects) }}</span>
        </td>
        <td class="meta-td">
            <span class="meta-lbl">Generated</span>
            <span class="meta-val" style="font-size:7pt;">{{ $generatedAt }}</span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- GRADE KEY                                              --}}
{{-- ══════════════════════════════════════════════════════ --}}
<table class="grade-key-table">
    <tr>
        <td class="grade-key-title-td">GRADING SCALE:</td>
        <td class="grade-key-items-td">
            @php
            $gkey = [
                'A1'=>['75–100','#15803d'],'B2'=>['70–74','#1d4ed8'],'B3'=>['65–69','#2563eb'],
                'C4'=>['60–64','#d97706'], 'C5'=>['55–59','#b45309'],'C6'=>['50–54','#92400e'],
                'D7'=>['45–49','#ea580c'], 'E8'=>['40–44','#dc2626'],'F9'=>['0–39','#991b1b'],
            ];
            @endphp
            @foreach($gkey as $g=>$info)
                <span class="gk">
                    <span class="gk-badge" style="background:{{ $info[1] }};">{{ $g }}</span>
                    {{ $info[0] }}
                </span>
            @endforeach
            &nbsp;&nbsp;
            <span class="gk-legend">
                <strong>BF</strong>=Brought Fwd &nbsp;
                <strong>CUM</strong>=Cumulative &nbsp;
                <strong>POS</strong>=Position &nbsp;
                <strong>AVG</strong>=Class Average
            </span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- MAIN BROADSHEET TABLE                                  --}}
{{-- ══════════════════════════════════════════════════════ --}}
@php
    $sel      = $selectedColumns ?? [];
    $showAdm  = empty($sel) || in_array('admission_no', $sel);
    $showGend = in_array('gender', $sel);
    $showTot  = empty($sel) || in_array('total', $sel);
    $showBF   = in_array('bf', $sel) || empty($sel);
    $showCum  = empty($sel) || in_array('cum', $sel);
    $showGrd  = empty($sel) || in_array('grade', $sel);
    $showPos  = empty($sel) || in_array('position', $sel);
    $showAvg  = in_array('class_average', $sel);
    $showRmk  = in_array('remark', $sel);
    $showGPA  = in_array('gpa', $sel);
    $showCGPA = in_array('cgpa', $sel);
    $showGPAG = in_array('gpa_grade', $sel);
    $showNS   = in_array('num_subjects', $sel);
    $showTGP  = in_array('total_grade_points', $sel);

    $activeAsm = $assessments->filter(fn($a) =>
        empty($sel) || in_array('assessment_'.$a->id, $sel)
    );

    // Compute per-subject colspan
    $subSpan = $activeAsm->count()
        + ($showTot?1:0) + ($showBF?1:0) + ($showCum?1:0) + ($showGrd?1:0)
        + ($showPos?1:0) + ($showAvg?1:0) + ($showRmk?1:0);
    $subSpan = max(1, $subSpan);

    // Frozen cols count (for stats row colspan)
    $frozenCols = 1 + ($showAdm?1:0) + 1 + ($showGend?1:0); // sn + adm + name + gender

    // GPA colspan
    $gpaSpan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAG?1:0)+($showNS?1:0)+($showTGP?1:0);

    $gradeColors = [
        'A1'=>'sc-A1','B2'=>'sc-B2','B3'=>'sc-B3',
        'C4'=>'sc-C4','C5'=>'sc-C5','C6'=>'sc-C6',
        'D7'=>'sc-D7','E8'=>'sc-E8','F9'=>'sc-F9','-'=>'sc-empty',
    ];

    $subjColors = [
        '#163562','#065f46','#4c1d95','#7f1d1d','#0c4a6e',
        '#78350f','#831843','#1e3a5f','#7c2d12','#1e1b4b',
        '#064e3b','#701a75','#431407','#1e3a8a','#052e16',
    ];
@endphp

<table class="bs-table">

{{-- ─── THEAD ─────────────────────────────────────────── --}}
<thead>

{{-- Row 1: Subject names --}}
<tr class="th-subj">
    <th class="th-frozen" rowspan="2" style="min-width:18pt;text-align:center;">#</th>
    @if($showAdm)
        <th class="th-frozen" rowspan="2" style="min-width:48pt;">Adm. No</th>
    @endif
    <th class="th-frozen" rowspan="2" style="min-width:100pt;text-align:left;padding-left:4pt;">Student Name</th>
    @if($showGend)
        <th class="th-frozen" rowspan="2" style="min-width:20pt;">Sex</th>
    @endif

    @foreach($subjects as $subIdx => $subInfo)
        @php $sc = $subjColors[array_search($subIdx, array_keys($subjects)) % count($subjColors)]; @endphp
        <th class="th-subject" colspan="{{ $subSpan }}" style="background:{{ $sc }};">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
                <br><span style="font-size:5.5pt;opacity:.75;">({{ $subInfo['subject_code'] }})</span>
            @endif
        </th>
    @endforeach

    @if($gpaSpan > 0)
        <th colspan="{{ $gpaSpan }}"
            style="background:#1e1b4b;border-left:2pt solid #6366f1;font-size:6pt;">
            GPA METRICS
        </th>
    @endif
</tr>

{{-- Row 2: Assessment sub-headers --}}
<tr class="th-asm">
    @foreach($subjects as $subIdx => $subInfo)
        @foreach($activeAsm as $aIdx => $a)
            <th class="{{ $aIdx===0?'th-boundary':'' }}" style="min-width:22pt;">
                {{ $a->name }}<br>
                <span style="font-size:5pt;opacity:.7;">/{{ $a->max_score }}</span>
            </th>
        @endforeach
        @if($showTot) <th style="min-width:22pt;">Total</th> @endif
        @if($showBF)  <th style="min-width:18pt;">BF</th>   @endif
        @if($showCum) <th style="min-width:22pt;">Cum</th>  @endif
        @if($showGrd) <th style="min-width:18pt;">Grd</th>  @endif
        @if($showPos) <th style="min-width:18pt;">Pos</th>  @endif
        @if($showAvg) <th style="min-width:22pt;">Avg</th>  @endif
        @if($showRmk) <th style="min-width:30pt;">Rmk</th>  @endif
    @endforeach
    @if($showGPA)  <th style="min-width:24pt;background:#312e81;border-left:2pt solid #6366f1;color:#c7d2fe;">GPA</th>  @endif
    @if($showCGPA) <th style="min-width:24pt;background:#312e81;color:#a5f3fc;">CGPA</th> @endif
    @if($showGPAG) <th style="min-width:20pt;background:#312e81;color:#fde68a;">GGrd</th> @endif
    @if($showNS)   <th style="min-width:18pt;background:#312e81;color:#d9f99d;">NS</th>   @endif
    @if($showTGP)  <th style="min-width:22pt;background:#312e81;color:#d9f99d;">TGP</th>  @endif
</tr>

</thead>

{{-- ─── TBODY ─────────────────────────────────────────── --}}
<tbody>
@foreach($studentRows as $idx => $stu)
    @php
        $subScores = $stu['subjects'] ?? [];
    @endphp
    <tr>
        <td class="td-sn">{{ $idx + 1 }}</td>
        @if($showAdm)
            <td class="td-adm">{{ $stu['admissionno'] }}</td>
        @endif
        <td class="td-name">
            <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
        </td>
        @if($showGend)
            <td style="font-size:6pt;">{{ strtoupper(substr($stu['gender']??'',0,1)) }}</td>
        @endif

        @foreach($subjects as $subId => $subInfo)
            @php
                $sd = $subScores[$subId] ?? [];
                $g  = $sd['grade'] ?? '-';
                $gc = $gradeColors[$g] ?? '';
            @endphp
            @foreach($activeAsm as $aIdx => $a)
                @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
                <td class="{{ $aIdx===0?'td-boundary':'' }}">
                    {{ $as > 0 ? number_format($as,1) : '—' }}
                </td>
            @endforeach
            @if($showTot)
                <td class="{{ $gc }}">{{ ($sd['total']??0)>0 ? number_format($sd['total'],1) : '—' }}</td>
            @endif
            @if($showBF)
                <td>{{ ($sd['bf']??0)>0 ? number_format($sd['bf'],1) : '—' }}</td>
            @endif
            @if($showCum)
                <td class="{{ $gc }}" style="font-weight:bold;">
                    {{ ($sd['cum']??0)>0 ? number_format($sd['cum'],1) : '—' }}
                </td>
            @endif
            @if($showGrd)
                <td class="{{ $gc }}" style="font-weight:bold;">{{ $g }}</td>
            @endif
            @if($showPos)
                <td style="font-size:6pt;">{{ $sd['position']??'—' }}</td>
            @endif
            @if($showAvg)
                <td style="font-size:5.5pt;color:#6b7280;">{{ $subjectStats[$subId]['avg']??'—' }}</td>
            @endif
            @if($showRmk)
                <td style="font-size:5.5pt;white-space:nowrap;">{{ $sd['remark']??'—' }}</td>
            @endif
        @endforeach

        @if($showGPA)
            <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>
        @endif
        @if($showCGPA)
            <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">
                {{ number_format($stu['cgpa'],2) }}
            </td>
        @endif
        @if($showGPAG)
            @php $ggc = $gradeColors[$stu['gpa_grade']??'-']??''; @endphp
            <td class="{{ $ggc }}" style="font-weight:bold;">{{ $stu['gpa_grade']??'—' }}</td>
        @endif
        @if($showNS)
            <td>{{ $stu['num_subjects']??'—' }}</td>
        @endif
        @if($showTGP)
            <td>{{ number_format($stu['total_grade_points'],1) }}</td>
        @endif
    </tr>
@endforeach

{{-- ─── Stats Rows ──────────────────────────────────────── --}}
@php
$statDefs = [
    ['tr-stats tr-avg',  'CLASS AVG', 'avg'],
    ['tr-stats tr-high', 'HIGHEST',   'highest'],
    ['tr-stats tr-low',  'LOWEST',    'lowest'],
    ['tr-stats tr-pass', 'PASS',      'passed'],
    ['tr-stats tr-fail', 'FAIL',      'failed'],
];
@endphp
@foreach($statDefs as [$cls,$lbl,$key])
<tr class="{{ $cls }}">
    <td class="td-lbl" colspan="{{ $frozenCols }}">{{ $lbl }}</td>
    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? []; @endphp
        @foreach($activeAsm as $a) <td>—</td> @endforeach
        @if($showTot) <td>{{ $st[$key]??'—' }}</td>                            @endif
        @if($showBF)  <td>—</td>                                                @endif
        @if($showCum) <td>—</td>                                                @endif
        @if($showGrd) <td>—</td>                                                @endif
        @if($showPos) <td>—</td>                                                @endif
        @if($showAvg) <td>{{ $key==='avg'?($st['avg']??'—'):'—' }}</td>        @endif
        @if($showRmk) <td>—</td>                                                @endif
    @endforeach
    @if($showGPA)
        @php
            $gpaStat = '—';
            if($key==='avg')     $gpaStat = count($studentRows)>0?round(collect($studentRows)->avg('gpa'),2):'—';
            if($key==='highest') $gpaStat = count($studentRows)>0?collect($studentRows)->max('gpa'):'—';
            if($key==='lowest')  $gpaStat = count($studentRows)>0?collect($studentRows)->min('gpa'):'—';
        @endphp
        <td>{{ $gpaStat }}</td>
    @endif
    @if($showCGPA) <td>—</td> @endif
    @if($showGPAG) <td>—</td> @endif
    @if($showNS)   <td>—</td> @endif
    @if($showTGP)  <td>—</td> @endif
</tr>
@endforeach

</tbody>
</table>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- PASS / FAIL SUMMARY                                    --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="summary-wrap">
    <div class="sum-title">SUBJECT PASS / FAIL SUMMARY</div>
    <table class="sum-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th style="text-align:center;">Students</th>
                <th style="text-align:center;">Avg Score</th>
                <th style="text-align:center;">Highest</th>
                <th style="text-align:center;">Lowest</th>
                <th style="text-align:center;">Passed</th>
                <th style="text-align:center;">Failed</th>
                <th style="text-align:center;">Pass Rate</th>
            </tr>
        </thead>
        <tbody>
        @foreach($subjects as $subId => $subInfo)
            @php
                $st  = $subjectStats[$subId] ?? ['avg'=>0,'highest'=>0,'lowest'=>0,'passed'=>0,'failed'=>0];
                $tot = $st['passed'] + $st['failed'];
                $pr  = $tot > 0 ? round($st['passed']/$tot*100) : 0;
            @endphp
            <tr>
                <td style="color:#9ca3af;font-size:5.5pt;">{{ $loop->iteration }}</td>
                <td style="font-weight:700;text-align:left;">
                    {{ $subInfo['subject_name'] }}
                    @if(!empty($subInfo['subject_code']))
                        <span style="color:#6b7280;font-size:5.5pt;">({{ $subInfo['subject_code'] }})</span>
                    @endif
                </td>
                <td style="text-align:center;">{{ $tot }}</td>
                <td style="text-align:center;font-weight:bold;">{{ $st['avg'] }}</td>
                <td style="text-align:center;color:#16a34a;font-weight:bold;">{{ $st['highest'] }}</td>
                <td style="text-align:center;color:#d97706;font-weight:bold;">{{ $st['lowest'] }}</td>
                <td style="text-align:center;color:#16a34a;font-weight:bold;">{{ $st['passed'] }}</td>
                <td style="text-align:center;color:#991b1b;font-weight:bold;">{{ $st['failed'] }}</td>
                <td style="text-align:center;">
                    <span class="pass-tag {{ $pr>=50?'pass-good':'pass-bad' }}">{{ $pr }}%</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- SIGNATURES                                             --}}
{{-- ══════════════════════════════════════════════════════ --}}
<table class="sig-table">
    <tr>
        @foreach(['Class Teacher','Head of Department','Vice Principal','Principal'] as $role)
        <td class="sig-td">
            <div class="sig-line">{{ $role }}</div>
        </td>
        @endforeach
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- FOOTER                                                 --}}
{{-- ══════════════════════════════════════════════════════ --}}
<table class="footer-table">
    <tr>
        <td class="footer-left">{{ $schoolInfo->school_name ?? '' }} — Confidential Academic Record</td>
        <td class="footer-right">Generated: {{ $generatedAt }}</td>
    </tr>
</table>

</body>
</html>
