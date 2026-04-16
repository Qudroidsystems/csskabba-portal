{{--
    broadsheet/pdf.blade.php
    Full-width, non-truncating PDF broadsheet.
    Uses DomPDF. Paper size / orientation passed from controller.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Broadsheet</title>
<style>
/* ══════════════════════════════════════════════════
   DOMPDF RESET & PAGE SETUP
══════════════════════════════════════════════════ */
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 7pt;
    color: #1a1a1a;
    background: #fff;
}

/* ── Page margins ── */
@page {
    margin: 6mm 5mm 6mm 5mm;
}

/* ══════════════════════════════════════════════════
   SCHOOL HEADER
══════════════════════════════════════════════════ */
.school-header {
    width: 100%;
    border-bottom: 3pt solid #0f2744;
    padding-bottom: 6pt;
    margin-bottom: 5pt;
}
.header-inner {
    display: flex; /* won't work in dompdf — use table layout */
    align-items: center;
    gap: 8pt;
}
.header-table {
    width: 100%;
    border-collapse: collapse;
}
.header-table td { vertical-align: middle; border: none; }
.logo-cell  { width: 50pt; text-align: center; }
.meta-cell  { padding-left: 8pt; }
.pills-cell { width: 140pt; text-align: right; }

.school-logo {
    width: 45pt; height: 45pt;
    object-fit: contain;
}
.school-name {
    font-size: 11pt;
    font-weight: bold;
    color: #0f2744;
    text-transform: uppercase;
    letter-spacing: .4pt;
}
.school-addr {
    font-size: 6.5pt;
    color: #6b7280;
    margin-top: 1pt;
}
.school-motto {
    font-size: 6pt;
    color: #1d4ed8;
    font-style: italic;
}

/* Meta pills as small table */
.mp-table { border-collapse: collapse; }
.mp-table td {
    border: 0.5pt solid #e2e8f0;
    background: #f8fafc;
    padding: 3pt 6pt;
    font-size: 6.5pt;
    text-align: center;
    vertical-align: middle;
}
.mp-val { font-weight: bold; font-size: 8pt; color: #0f2744; }
.mp-lbl { font-size: 5.5pt; color: #6b7280; text-transform: uppercase; }

/* ══════════════════════════════════════════════════
   GRADE KEY BAR
══════════════════════════════════════════════════ */
.grade-key {
    width: 100%;
    background: #fef9c3;
    border: 0.5pt solid #fde047;
    padding: 3pt 6pt;
    margin-bottom: 4pt;
    font-size: 6pt;
}
.grade-key strong { color: #0f2744; }
.gk-item {
    display: inline-block;
    background: white;
    border: 0.5pt solid #e5e7eb;
    padding: 1pt 4pt;
    margin: 0 2pt;
    border-radius: 2pt;
}

/* ══════════════════════════════════════════════════
   BROADSHEET TABLE — CRITICAL: no overflow hidden,
   DomPDF renders full width using page width
══════════════════════════════════════════════════ */
.bs-table-wrap {
    width: 100%;
    overflow: visible;  /* DO NOT set overflow:hidden */
}

table.bs-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 6pt;
    table-layout: auto;  /* let columns breathe */
}

/* HEADERS */
table.bs-table thead tr th {
    background: #0f2744;
    color: white;
    font-weight: bold;
    font-size: 5.5pt;
    text-align: center;
    padding: 2pt 2pt;
    border: 0.5pt solid rgba(255,255,255,.2);
    vertical-align: middle;
    white-space: normal;
    word-wrap: break-word;
}
table.bs-table thead tr.subj-hdr th {
    background: #1e3a5f;
    font-size: 5.5pt;
}
table.bs-table thead tr.asm-hdr th {
    background: #263f6a;
    font-size: 5pt;
    color: rgba(255,255,255,.85);
}

/* Fixed left columns — narrowest possible */
th.c-sn, td.c-sn   { width: 14pt; min-width: 14pt; max-width: 14pt; }
th.c-adm,td.c-adm  { width: 44pt; min-width: 44pt; max-width: 44pt; }
th.c-name,td.c-name { width: 90pt; min-width: 90pt; max-width: 90pt; }

/* Score columns — tight */
th.c-score, td.c-score { width: 16pt; min-width: 16pt; max-width: 22pt; }
th.c-asm,   td.c-asm   { width: 14pt; min-width: 14pt; max-width: 18pt; }

/* BODY ROWS */
table.bs-table tbody tr td {
    height: 13pt;
    padding: 1.5pt 2pt;
    border: 0.4pt solid #d1d5db;
    text-align: center;
    vertical-align: middle;
    font-size: 6pt;
    white-space: nowrap;
    overflow: visible;
}
table.bs-table tbody tr td.c-name {
    text-align: left;
    font-weight: 600;
    font-size: 5.5pt;
    white-space: normal;
    word-break: break-word;
}

/* Alternate row */
table.bs-table tbody tr:nth-child(even) td { background: #f8fafc; }

/* Grade colours */
.g-a1 { color: #15803d; font-weight: bold; }
.g-b2, .g-b3 { color: #1d4ed8; font-weight: 600; }
.g-c4, .g-c5, .g-c6 { color: #b45309; }
.g-d7 { color: #c2410c; }
.g-e8, .g-f9 { color: #dc2626; font-weight: bold; }

/* FOOTER STAT ROWS */
table.bs-table tfoot tr.s-avg td  { background: #eff6ff; color: #1d4ed8; font-weight: bold; }
table.bs-table tfoot tr.s-high td { background: #f0fdf4; color: #16a34a; font-weight: bold; }
table.bs-table tfoot tr.s-low td  { background: #fff7ed; color: #b45309; font-weight: bold; }
table.bs-table tfoot tr.s-pass td { background: #f0fdf4; color: #15803d; }
table.bs-table tfoot tr.s-fail td { background: #fef2f2; color: #dc2626; }
table.bs-table tfoot tr td {
    height: 12pt;
    padding: 1.5pt 2pt;
    border: 0.4pt solid #d1d5db;
    text-align: center;
    font-size: 5.5pt;
    vertical-align: middle;
}
table.bs-table tfoot td.c-name {
    text-align: left;
    font-weight: 800;
    font-size: 5.5pt;
}

/* GPA cells */
.gpa-cell  { color: #7c3aed; font-weight: bold; }
.cgpa-cell { color: #0891b2; font-weight: bold; }

/* ══════════════════════════════════════════════════
   PASS/FAIL SUMMARY
══════════════════════════════════════════════════ */
.pf-section {
    margin-top: 10pt;
    page-break-inside: avoid;
}
.pf-title {
    font-size: 8pt;
    font-weight: bold;
    color: white;
    background: #0f2744;
    padding: 4pt 8pt;
}
.pf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 6pt;
}
.pf-table th {
    background: #f1f5f9;
    color: #0f2744;
    font-weight: bold;
    padding: 3pt 5pt;
    border: 0.5pt solid #e2e8f0;
    text-align: left;
}
.pf-table td {
    padding: 2.5pt 5pt;
    border: 0.5pt solid #e2e8f0;
    vertical-align: middle;
}
.pf-table tr:nth-child(even) td { background: #f8fafc; }

/* ══════════════════════════════════════════════════
   SIGNATURE BLOCK
══════════════════════════════════════════════════ */
.sig-block {
    margin-top: 16pt;
    width: 100%;
    page-break-inside: avoid;
}
.sig-table { width: 100%; border-collapse: collapse; }
.sig-table td { border: none; padding: 0 8pt; text-align: center; vertical-align: bottom; }
.sig-line { border-top: 0.8pt solid #374151; margin: 20pt 10pt 2pt; }
.sig-lbl  { font-size: 6pt; color: #6b7280; text-transform: uppercase; letter-spacing: .3pt; }

/* ── Generated footer ── */
.gen-footer {
    margin-top: 4pt;
    text-align: right;
    font-size: 5.5pt;
    color: #9ca3af;
}
</style>
</head>
<body>

{{-- ═══════════ SCHOOL HEADER ═══════════ --}}
<div class="school-header">
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(!empty($school_logo_base64) && $school_logo_base64 !== '')
                    <img src="{{ $school_logo_base64 }}" alt="Logo" class="school-logo">
                @endif
            </td>
            <td class="meta-cell">
                <div class="school-name">{{ $schoolInfo->school_name ?? 'School Name' }}</div>
                @if(!empty($schoolInfo->school_address))
                    <div class="school-addr">{{ $schoolInfo->school_address }}</div>
                @endif
                @if(!empty($schoolInfo->school_phone))
                    <div class="school-addr">Tel: {{ $schoolInfo->school_phone }}</div>
                @endif
                @if(!empty($schoolInfo->school_motto))
                    <div class="school-motto">"{{ $schoolInfo->school_motto }}"</div>
                @endif
                <div style="margin-top:3pt;font-size:7pt;font-weight:bold;color:#1d4ed8;">
                    CLASS BROADSHEET
                </div>
            </td>
            <td class="pills-cell">
                <table class="mp-table">
                    <tr>
                        <td>
                            <div class="mp-val">{{ $schoolclass->schoolclass ?? '—' }} {{ $schoolclass->arm_name ?? '' }}</div>
                            <div class="mp-lbl">Class</div>
                        </td>
                        <td>
                            <div class="mp-val">{{ $schoolsession->session ?? '—' }}</div>
                            <div class="mp-lbl">Session</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="mp-val">{{ $schoolterm->term ?? '—' }}</div>
                            <div class="mp-lbl">Term</div>
                        </td>
                        <td>
                            <div class="mp-val">{{ $totalStudents }}</div>
                            <div class="mp-lbl">Students</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════ GRADE KEY ═══════════ --}}
<div class="grade-key">
    <strong>Grade Key:</strong>
    <span class="gk-item">A1: 75–100</span>
    <span class="gk-item">B2: 70–74</span>
    <span class="gk-item">B3: 65–69</span>
    <span class="gk-item">C4: 60–64</span>
    <span class="gk-item">C5: 55–59</span>
    <span class="gk-item">C6: 50–54</span>
    <span class="gk-item">D7: 45–49</span>
    <span class="gk-item">E8: 40–44</span>
    <span class="gk-item" style="color:#dc2626;font-weight:bold;">F9: 0–39</span>
</div>

{{-- ═══════════ BROADSHEET TABLE ═══════════ --}}
<div class="bs-table-wrap">
<table class="bs-table">

{{-- ── THEAD ── --}}
<thead>
{{-- Row 1: Subject group headers --}}
<tr class="subj-hdr">
    @php
        $showAsm   = $assessments->count() > 0;
        $showTotal = in_array('total',         $selectedColumns) || empty($selectedColumns);
        $showBf    = in_array('bf',            $selectedColumns) || empty($selectedColumns);
        $showCum   = in_array('cum',           $selectedColumns) || empty($selectedColumns);
        $showGrade = in_array('grade',         $selectedColumns) || empty($selectedColumns);
        $showPos   = in_array('position',      $selectedColumns) || empty($selectedColumns);
        $showAvg   = in_array('class_average', $selectedColumns) || empty($selectedColumns);
        $showRemark= in_array('remark',        $selectedColumns);

        $perSubjCols = 0;
        foreach($assessments as $a) {
            if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns)) $perSubjCols++;
        }
        if($showTotal)  $perSubjCols++;
        if($showBf)     $perSubjCols++;
        if($showCum)    $perSubjCols++;
        if($showGrade)  $perSubjCols++;
        if($showPos)    $perSubjCols++;
        if($showAvg)    $perSubjCols++;
        if($showRemark) $perSubjCols++;
    @endphp

    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))
        <th class="c-sn" rowspan="2">SN</th>
    @endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))
        <th class="c-adm" rowspan="2">Adm No.</th>
    @endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <th class="c-name" rowspan="2" style="text-align:left;">Student Name</th>
    @endif
    @if(in_array('gender', $selectedColumns))
        <th rowspan="2" style="width:10pt;">Sex</th>
    @endif

    @php $subColors = ['#1d4ed8','#0f766e','#7c3aed','#c2410c','#0369a1','#15803d','#b45309','#be185d']; @endphp
    @foreach($subjects as $subId => $subInfo)
        <th colspan="{{ $perSubjCols }}"
            style="background:{{ $subColors[($loop->index) % 8] }};border-left:1pt solid rgba(255,255,255,.4);">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
                ({{ $subInfo['subject_code'] }})
            @endif
        </th>
    @endforeach

    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        <th rowspan="2" style="background:#7c3aed;width:18pt;">GPA</th>
    @endif
    @if(in_array('cgpa', $selectedColumns))
        <th rowspan="2" style="background:#6d28d9;width:18pt;">CGPA</th>
    @endif
    @if(in_array('gpa_grade', $selectedColumns))
        <th rowspan="2" style="background:#5b21b6;width:16pt;">GPA Grd</th>
    @endif
    @if(in_array('num_subjects', $selectedColumns))
        <th rowspan="2" style="background:#4c1d95;width:14pt;">N</th>
    @endif
    @if(in_array('total_grade_points', $selectedColumns))
        <th rowspan="2" style="background:#3b0764;width:16pt;">GP</th>
    @endif
</tr>

{{-- Row 2: Assessment sub-headers --}}
<tr class="asm-hdr">
    @foreach($subjects as $subId => $subInfo)
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))
                <th class="c-asm">{{ $a->name }}<br>({{ $a->max_score }})</th>
            @endif
        @endforeach
        @if($showTotal)  <th class="c-score">Total</th> @endif
        @if($showBf)     <th class="c-score">BF</th>    @endif
        @if($showCum)    <th class="c-score">Cum</th>   @endif
        @if($showGrade)  <th class="c-score">Grd</th>   @endif
        @if($showPos)    <th class="c-score">Pos</th>   @endif
        @if($showAvg)    <th class="c-score">Avg</th>   @endif
        @if($showRemark) <th class="c-score">Rmk</th>   @endif
    @endforeach
</tr>
</thead>

{{-- ── TBODY ── --}}
<tbody>
@forelse($studentRows as $idx => $stu)
@php
    $fullName  = trim($stu['lastname'] . ' ' . $stu['firstname']);
    $subScores = $stu['subjects'] ?? [];
@endphp
<tr>
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))
        <td class="c-sn">{{ $idx + 1 }}</td>
    @endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))
        <td class="c-adm">{{ $stu['admissionno'] }}</td>
    @endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="c-name">{{ $fullName }}</td>
    @endif
    @if(in_array('gender', $selectedColumns))
        <td>{{ strtoupper(substr($stu['gender'] ?? '-', 0, 1)) }}</td>
    @endif

    @foreach($subjects as $subId => $subInfo)
        @php $ss = $subScores[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))
                <td class="c-asm">{{ $ss ? ($ss['assessments'][$a->id] ?? '–') : '–' }}</td>
            @endif
        @endforeach

        @if($showTotal)
            <td class="c-score">{{ $ss ? $ss['total'] : '–' }}</td>
        @endif
        @if($showBf)
            <td class="c-score">{{ $ss ? $ss['bf'] : '–' }}</td>
        @endif
        @if($showCum)
            <td class="c-score">{{ $ss ? $ss['cum'] : '–' }}</td>
        @endif
        @if($showGrade)
            @php
                $grRaw = $ss ? ($ss['grade'] ?? '–') : '–';
                $grClass = 'g-' . strtolower(str_replace(['/','\\',' '], '', $grRaw));
            @endphp
            <td class="c-score {{ $grClass }}">{{ $grRaw }}</td>
        @endif
        @if($showPos)
            <td class="c-score">{{ $ss ? ($ss['position'] ?? '–') : '–' }}</td>
        @endif
        @if($showAvg)
            <td class="c-score">{{ $ss ? number_format($ss['class_average'],1) : '–' }}</td>
        @endif
        @if($showRemark)
            <td class="c-score">{{ $ss ? ($ss['remark'] ?? '–') : '–' }}</td>
        @endif
    @endforeach

    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        <td class="c-score gpa-cell">{{ $stu['gpa'] }}</td>
    @endif
    @if(in_array('cgpa', $selectedColumns))
        <td class="c-score cgpa-cell">{{ $stu['cgpa'] }}</td>
    @endif
    @if(in_array('gpa_grade', $selectedColumns))
        <td class="c-score">{{ $stu['gpa_grade'] }}</td>
    @endif
    @if(in_array('num_subjects', $selectedColumns))
        <td class="c-score">{{ $stu['num_subjects'] }}</td>
    @endif
    @if(in_array('total_grade_points', $selectedColumns))
        <td class="c-score">{{ $stu['total_grade_points'] }}</td>
    @endif
</tr>
@empty
<tr>
    <td colspan="999" style="text-align:center;padding:8pt;color:#9ca3af;">No student records found.</td>
</tr>
@endforelse
</tbody>

{{-- ── TFOOT ── --}}
<tfoot>
@php
    $footRows = [
        ['class'=>'s-avg',  'label'=>'CLASS AVG', 'stat'=>'avg',     'gpaStat'=> count($studentRows)>0 ? round(collect($studentRows)->avg('gpa'),2) : 0],
        ['class'=>'s-high', 'label'=>'HIGHEST',   'stat'=>'highest', 'gpaStat'=> count($studentRows)>0 ? collect($studentRows)->max('gpa') : 0],
        ['class'=>'s-low',  'label'=>'LOWEST',    'stat'=>'lowest',  'gpaStat'=> count($studentRows)>0 ? collect($studentRows)->min('gpa') : 0],
        ['class'=>'s-pass', 'label'=>'PASS',       'stat'=>'passed',  'gpaStat'=>'—'],
        ['class'=>'s-fail', 'label'=>'FAIL',       'stat'=>'failed',  'gpaStat'=>'—'],
    ];
@endphp
@foreach($footRows as $fr)
<tr class="{{ $fr['class'] }}">
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))
        <td class="c-sn">—</td>
    @endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))
        <td class="c-adm">—</td>
    @endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="c-name">{{ $fr['label'] }}</td>
    @endif
    @if(in_array('gender', $selectedColumns))<td>—</td>@endif

    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))
                <td>—</td>
            @endif
        @endforeach
        @if($showTotal)  <td>{{ $st ? $st[$fr['stat']] : '—' }}</td> @endif
        @if($showBf)     <td>—</td> @endif
        @if($showCum)    <td>—</td> @endif
        @if($showGrade)  <td>—</td> @endif
        @if($showPos)    <td>—</td> @endif
        @if($showAvg)    <td>{{ ($fr['stat']==='avg' && $st) ? $st['avg'] : '—' }}</td> @endif
        @if($showRemark) <td>—</td> @endif
    @endforeach

    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        <td class="gpa-cell">{{ $fr['gpaStat'] }}</td>
    @endif
    @if(in_array('cgpa', $selectedColumns))<td>—</td>@endif
    @if(in_array('gpa_grade', $selectedColumns))<td>—</td>@endif
    @if(in_array('num_subjects', $selectedColumns))<td>—</td>@endif
    @if(in_array('total_grade_points', $selectedColumns))<td>—</td>@endif
</tr>
@endforeach
</tfoot>

</table>
</div>

{{-- ═══════════ PASS/FAIL SUMMARY ═══════════ --}}
<div class="pf-section">
    <div class="pf-title">Subject Pass / Fail Analysis</div>
    <table class="pf-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Students</th>
                <th>Class Avg</th>
                <th>Highest</th>
                <th>Lowest</th>
                <th>Passed</th>
                <th>Failed</th>
                <th>Pass Rate</th>
            </tr>
        </thead>
        <tbody>
        @foreach($subjects as $subId => $subInfo)
            @php
                $st = $subjectStats[$subId] ?? ['avg'=>0,'highest'=>0,'lowest'=>0,'passed'=>0,'failed'=>0];
                $total = $st['passed'] + $st['failed'];
                $passRate = $total > 0 ? round($st['passed'] / $total * 100, 1) : 0;
            @endphp
            <tr>
                <td style="color:#9ca3af;">{{ $loop->iteration }}</td>
                <td style="font-weight:600;text-align:left;">{{ $subInfo['subject_name'] }}</td>
                <td>{{ $total }}</td>
                <td><strong>{{ $st['avg'] }}</strong></td>
                <td style="color:#16a34a;font-weight:bold;">{{ $st['highest'] }}</td>
                <td style="color:#d97706;font-weight:bold;">{{ $st['lowest'] }}</td>
                <td style="color:#16a34a;font-weight:bold;">{{ $st['passed'] }}</td>
                <td style="color:#dc2626;font-weight:bold;">{{ $st['failed'] }}</td>
                <td style="font-weight:bold;color:{{ $passRate>=50?'#16a34a':'#dc2626' }};">{{ $passRate }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

{{-- ═══════════ SIGNATURES ═══════════ --}}
<div class="sig-block">
    <table class="sig-table">
        <tr>
            @foreach(['Class Teacher','Head of Department','Vice Principal (Academics)','Principal'] as $role)
            <td>
                <div class="sig-line"></div>
                <div class="sig-lbl">{{ $role }}</div>
            </td>
            @endforeach
        </tr>
    </table>
</div>

<div class="gen-footer">
    Generated: {{ $generatedAt }} &nbsp;|&nbsp;
    {{ $schoolInfo->school_name ?? '' }} &nbsp;|&nbsp;
    {{ $schoolclass->schoolclass ?? '' }} {{ $schoolclass->arm_name ?? '' }} &nbsp;|&nbsp;
    {{ $schoolsession->session ?? '' }} {{ $schoolterm->term ?? '' }}
</div>

</body>
</html>
