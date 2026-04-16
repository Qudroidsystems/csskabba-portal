{{--
    broadsheet/web.blade.php
    Full scrollable broadsheet web view with smart locate/filter toolbar
--}}
@extends('layouts.master')

@section('content')
<style>
/* ══════════════════════════════════════════════════════════
   CSS VARIABLES & RESET
══════════════════════════════════════════════════════════ */
:root {
    --c-navy:    #0f2744;
    --c-blue:    #1d4ed8;
    --c-sky:     #3b82f6;
    --c-green:   #16a34a;
    --c-red:     #dc2626;
    --c-amber:   #d97706;
    --c-purple:  #7c3aed;
    --c-teal:    #0891b2;
    --c-muted:   #6b7280;
    --c-border:  #e2e8f0;
    --c-bg:      #f1f5f9;
    --c-card:    #ffffff;
    --c-text:    #1e293b;
    --c-subtext: #64748b;

    /* Table specifics */
    --th-height:  36px;
    --td-height:  30px;
    --sticky-w:   42px;   /* SN column */
    --name-w:     180px;  /* Name column */
    --adm-w:      90px;

    --zoom: 1;
    --font-size-base: 11px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body { background: var(--c-bg); font-family: 'Segoe UI', system-ui, sans-serif; color: var(--c-text); }

/* ══════════════════════════════════════════════════════════
   TOOLBAR (sticky top)
══════════════════════════════════════════════════════════ */
#bs-toolbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: var(--c-navy);
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    box-shadow: 0 4px 12px rgba(0,0,0,.35);
}
#bs-toolbar .tb-title {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
    margin-right: auto;
}
#bs-toolbar .tb-title small {
    font-size: 10px;
    font-weight: 400;
    color: rgba(255,255,255,.6);
    display: block;
}

/* Locate dropdown */
.locate-wrap {
    position: relative;
    display: flex;
    align-items: center;
    gap: 6px;
}
.locate-wrap label {
    font-size: 11px;
    color: rgba(255,255,255,.7);
    white-space: nowrap;
}
#locateSelect {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 6px;
    color: #fff;
    font-size: 12px;
    padding: 6px 28px 6px 10px;
    appearance: none;
    cursor: pointer;
    min-width: 220px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 12px;
}
#locateSelect:focus { outline: 2px solid rgba(59,130,246,.6); }
#locateSelect option { background: #1e293b; color: #fff; }

/* Subject locate select */
#subjectLocateSelect {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 6px;
    color: #fff;
    font-size: 12px;
    padding: 6px 28px 6px 10px;
    appearance: none;
    cursor: pointer;
    min-width: 160px;
    display: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 12px;
}
#subjectLocateSelect option { background: #1e293b; }

/* Score input for custom threshold */
#scoreThresholdWrap {
    display: none;
    align-items: center;
    gap: 6px;
}
#scoreThresholdWrap label { font-size:11px; color:rgba(255,255,255,.7); }
#scoreThresholdInput {
    width: 70px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 6px;
    color: #fff;
    font-size: 12px;
    padding: 6px 10px;
    text-align: center;
}
#scoreThresholdInput::placeholder { color: rgba(255,255,255,.4); }

/* Toolbar buttons */
.tb-btn {
    display: flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 6px;
    color: #fff;
    font-size: 11.5px;
    font-weight: 600;
    padding: 6px 12px;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
}
.tb-btn:hover { background: rgba(255,255,255,.2); }
.tb-btn.danger { background: rgba(220,38,38,.35); border-color: rgba(220,38,38,.5); }
.tb-btn.success { background: rgba(22,163,74,.35); border-color: rgba(22,163,74,.5); }

/* Zoom controls */
.zoom-group { display: flex; align-items: center; gap: 4px; }
.zoom-group span { font-size: 11px; color: rgba(255,255,255,.6); min-width: 38px; text-align: center; }
.zoom-btn {
    width: 26px; height: 26px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 4px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .1s;
}
.zoom-btn:hover { background: rgba(255,255,255,.25); }

/* Result count badge */
#resultBadge {
    display: none;
    background: var(--c-sky);
    color: white;
    font-size: 10.5px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
}

/* ══════════════════════════════════════════════════════════
   SCHOOL HEADER BLOCK
══════════════════════════════════════════════════════════ */
.bs-header {
    background: white;
    border-bottom: 3px solid var(--c-navy);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}
.bs-header img.school-logo {
    width: 70px; height: 70px;
    object-fit: contain;
    border-radius: 6px;
    flex-shrink: 0;
}
.bs-header .sch-meta { flex: 1; }
.bs-header .sch-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--c-navy);
    text-transform: uppercase;
    letter-spacing: .5px;
    line-height: 1.2;
}
.bs-header .sch-address {
    font-size: 11px;
    color: var(--c-subtext);
    margin-top: 2px;
}
.bs-header .sch-motto {
    font-size: 11px;
    font-style: italic;
    color: var(--c-blue);
    margin-top: 1px;
}
.bs-header .bs-meta-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: flex-start;
}
.meta-pill {
    background: var(--c-bg);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    padding: 6px 12px;
    text-align: center;
    min-width: 90px;
}
.meta-pill .mp-val {
    font-size: 13px;
    font-weight: 700;
    color: var(--c-navy);
    display: block;
}
.meta-pill .mp-lbl {
    font-size: 9.5px;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
}

/* ══════════════════════════════════════════════════════════
   GRADE KEY BAR
══════════════════════════════════════════════════════════ */
.grade-key-bar {
    background: #fefce8;
    border-top: 1px solid #fde047;
    border-bottom: 1px solid #fde047;
    padding: 6px 24px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 10.5px;
}
.grade-key-bar strong { color: var(--c-navy); margin-right: 4px; }
.gk-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 2px 7px;
    font-size: 10.5px;
    white-space: nowrap;
}

/* ══════════════════════════════════════════════════════════
   TABLE WRAPPER (the scrollable viewport)
══════════════════════════════════════════════════════════ */
#bs-table-outer {
    overflow: auto;
    width: 100%;
    background: white;
    /* Leave room for toolbar */
    max-height: calc(100vh - 120px);
    position: relative;
}

/* The zoom container */
#bs-zoom-wrap {
    display: inline-block;
    transform-origin: top left;
    min-width: 100%;
}

/* ══════════════════════════════════════════════════════════
   BROADSHEET TABLE
══════════════════════════════════════════════════════════ */
#bs-table {
    border-collapse: collapse;
    font-size: var(--font-size-base);
    white-space: nowrap;
    width: max-content;
    min-width: 100%;
}

/* Header rows */
#bs-table thead tr th {
    background: var(--c-navy);
    color: white;
    font-weight: 700;
    font-size: 10px;
    text-align: center;
    padding: 0 5px;
    height: var(--th-height);
    border: 1px solid rgba(255,255,255,.12);
    position: sticky;
    top: 0;
    z-index: 20;
    vertical-align: middle;
}
/* Subject group header cells */
#bs-table thead tr.subj-header th {
    background: #1e3a5f;
    font-size: 9.5px;
    letter-spacing: .2px;
}
/* Assessment sub-headers */
#bs-table thead tr.asm-header th {
    background: #263f6a;
    font-size: 9px;
    font-weight: 600;
    color: rgba(255,255,255,.85);
}

/* Sticky left columns in thead */
#bs-table thead th.col-sn   { position: sticky; left: 0;                        z-index: 30; min-width: var(--sticky-w); max-width: var(--sticky-w); }
#bs-table thead th.col-adm  { position: sticky; left: var(--sticky-w);          z-index: 30; min-width: var(--adm-w);    max-width: var(--adm-w); }
#bs-table thead th.col-name { position: sticky; left: calc(var(--sticky-w) + var(--adm-w)); z-index: 30; min-width: var(--name-w); max-width: var(--name-w); }

/* TD cells */
#bs-table tbody tr td {
    height: var(--td-height);
    padding: 0 5px;
    border: 1px solid #e5e7eb;
    text-align: center;
    vertical-align: middle;
    font-size: 10.5px;
    color: var(--c-text);
    transition: background .1s;
}
#bs-table tbody tr td.col-name {
    text-align: left;
    font-weight: 600;
}
/* Sticky left cols in tbody */
#bs-table tbody td.col-sn {
    position: sticky; left: 0; z-index: 10;
    background: #f8fafc;
    font-weight: 700;
    color: var(--c-navy);
    border-right: 2px solid #cbd5e1;
}
#bs-table tbody td.col-adm {
    position: sticky; left: var(--sticky-w); z-index: 10;
    background: #f8fafc;
    font-size: 9.5px;
    color: var(--c-subtext);
    border-right: 1px solid #e2e8f0;
}
#bs-table tbody td.col-name {
    position: sticky; left: calc(var(--sticky-w) + var(--adm-w)); z-index: 10;
    background: white;
    border-right: 2px solid #cbd5e1;
    min-width: var(--name-w);
    max-width: var(--name-w);
    overflow: hidden;
    text-overflow: ellipsis;
}
/* Alternate row shading */
#bs-table tbody tr:nth-child(even) td { background: #f8fafc; }
#bs-table tbody tr:nth-child(even) td.col-sn,
#bs-table tbody tr:nth-child(even) td.col-adm  { background: #f0f4f8; }
#bs-table tbody tr:nth-child(even) td.col-name { background: #f9fafb; }

/* Hover */
#bs-table tbody tr:hover td { background: #eff6ff !important; }
#bs-table tbody tr:hover td.col-sn,
#bs-table tbody tr:hover td.col-adm,
#bs-table tbody tr:hover td.col-name { background: #dbeafe !important; }

/* Grade colouring */
.grade-a1 { color: #15803d; font-weight: 700; }
.grade-b2, .grade-b3 { color: #1d4ed8; font-weight: 600; }
.grade-c4, .grade-c5, .grade-c6 { color: #d97706; font-weight: 600; }
.grade-d7 { color: #ea580c; font-weight: 600; }
.grade-e8, .grade-f9 { color: #dc2626; font-weight: 700; }

/* GPA footer area */
.gpa-cell { font-weight: 700; color: var(--c-purple); }
.cgpa-cell { font-weight: 700; color: var(--c-teal); }

/* Stats footer row */
#bs-table tfoot tr td {
    height: 28px;
    padding: 0 5px;
    border: 1px solid #e2e8f0;
    font-size: 9.5px;
    text-align: center;
    vertical-align: middle;
}
#bs-table tfoot tr.stat-avg   td { background: #eff6ff; color: #1d4ed8; font-weight: 700; }
#bs-table tfoot tr.stat-high  td { background: #f0fdf4; color: #16a34a; font-weight: 700; }
#bs-table tfoot tr.stat-low   td { background: #fff7ed; color: #d97706; font-weight: 700; }
#bs-table tfoot tr.stat-pass  td { background: #f0fdf4; color: #15803d; }
#bs-table tfoot tr.stat-fail  td { background: #fef2f2; color: #dc2626; }

#bs-table tfoot td.col-sn,
#bs-table tfoot td.col-adm,
#bs-table tfoot td.col-name {
    position: sticky;
    z-index: 10;
    background: #e2e8f0;
    font-weight: 700;
    color: var(--c-navy);
    text-align: left;
    font-size: 9.5px;
}
#bs-table tfoot td.col-sn   { left: 0; }
#bs-table tfoot td.col-adm  { left: var(--sticky-w); }
#bs-table tfoot td.col-name { left: calc(var(--sticky-w) + var(--adm-w)); border-right: 2px solid #94a3b8; }

/* ══════════════════════════════════════════════════════════
   HIGHLIGHT STATES (from locate/filter)
══════════════════════════════════════════════════════════ */
#bs-table tbody tr.hl-match td { background: #fef9c3 !important; }
#bs-table tbody tr.hl-match td.col-sn,
#bs-table tbody tr.hl-match td.col-adm,
#bs-table tbody tr.hl-match td.col-name { background: #fef08a !important; }

#bs-table tbody tr.hl-primary td { background: #dbeafe !important; }
#bs-table tbody tr.hl-primary td.col-sn,
#bs-table tbody tr.hl-primary td.col-adm,
#bs-table tbody tr.hl-primary td.col-name { background: #bfdbfe !important; }

#bs-table tbody tr.hl-danger td { background: #fee2e2 !important; }
#bs-table tbody tr.hl-danger td.col-sn,
#bs-table tbody tr.hl-danger td.col-adm,
#bs-table tbody tr.hl-danger td.col-name { background: #fecaca !important; }

#bs-table tbody tr.hl-success td { background: #dcfce7 !important; }
#bs-table tbody tr.hl-success td.col-sn,
#bs-table tbody tr.hl-success td.col-adm,
#bs-table tbody tr.hl-success td.col-name { background: #bbf7d0 !important; }

#bs-table tbody tr.hl-warning td { background: #fef3c7 !important; }
#bs-table tbody tr.hl-warning td.col-sn,
#bs-table tbody tr.hl-warning td.col-adm,
#bs-table tbody tr.hl-warning td.col-name { background: #fde68a !important; }

/* Cell-level highlight for locate-in-subject */
td.cell-hl { outline: 2.5px solid #f59e0b; background: #fef9c3 !important; position: relative; z-index: 5; }
td.cell-hl-danger { outline: 2.5px solid #ef4444; background: #fee2e2 !important; }
td.cell-hl-success { outline: 2.5px solid #22c55e; background: #dcfce7 !important; }

/* Dim non-matching rows */
#bs-table tbody tr.hl-dim td { opacity: .35; }

/* ══════════════════════════════════════════════════════════
   PASS / FAIL SUMMARY TABLE
══════════════════════════════════════════════════════════ */
.pf-summary {
    background: white;
    margin: 24px;
    border-radius: 10px;
    border: 1px solid var(--c-border);
    overflow: hidden;
}
.pf-summary h3 {
    padding: 14px 20px;
    background: var(--c-navy);
    color: white;
    font-size: 13px;
    font-weight: 700;
}
.pf-summary table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}
.pf-summary th {
    background: #f1f5f9;
    color: var(--c-navy);
    font-weight: 700;
    font-size: 11px;
    padding: 8px 14px;
    text-align: left;
    border-bottom: 1px solid var(--c-border);
}
.pf-summary td {
    padding: 7px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.pf-summary tr:hover td { background: #f8fafc; }
.pass-rate-bar {
    background: #e2e8f0;
    border-radius: 4px;
    height: 8px;
    width: 100%;
    overflow: hidden;
    display: inline-block;
    vertical-align: middle;
    margin-left: 6px;
    min-width: 60px;
}
.pass-rate-fill {
    height: 100%;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    border-radius: 4px;
    transition: width .3s;
}

/* ══════════════════════════════════════════════════════════
   SIGNATURE BLOCK
══════════════════════════════════════════════════════════ */
.sig-block {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    padding: 20px 24px 30px;
    background: white;
    margin: 0 24px 24px;
    border-radius: 10px;
    border: 1px solid var(--c-border);
}
.sig-item {
    flex: 1;
    min-width: 140px;
    text-align: center;
}
.sig-item .sig-line {
    border-top: 1.5px solid #374151;
    margin: 28px 10px 4px;
}
.sig-item .sig-lbl {
    font-size: 10px;
    color: var(--c-subtext);
    text-transform: uppercase;
    letter-spacing: .5px;
}

/* ══════════════════════════════════════════════════════════
   TOAST NOTIFICATION
══════════════════════════════════════════════════════════ */
#bs-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: var(--c-navy);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    z-index: 9999;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity .25s, transform .25s;
    pointer-events: none;
}
#bs-toast.show { opacity: 1; transform: translateY(0); }

/* Scroll to first match button */
#scrollToFirst {
    display: none;
    align-items: center; gap: 5px;
    background: var(--c-amber);
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 11.5px;
    font-weight: 700;
    padding: 6px 12px;
    cursor: pointer;
    animation: pulse 1.5s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.7} }

@media (max-width: 600px) {
    #bs-toolbar { gap: 8px; }
    #locateSelect { min-width: 150px; }
    .bs-header { flex-direction: column; gap: 10px; }
}
</style>

{{-- ── TOOLBAR ── --}}
<div id="bs-toolbar">
    <div class="tb-title">
        {{ $schoolclass->schoolclass ?? 'Class' }}
        {{ $schoolclass->arm_name ?? '' }}
        <small>
            {{ $schoolsession->session ?? '' }} &nbsp;·&nbsp; {{ $schoolterm->term ?? '' }}
            &nbsp;·&nbsp; {{ $totalStudents }} Students
        </small>
    </div>

    {{-- Smart locate --}}
    <div class="locate-wrap">
        <label><i class="ri-search-eye-line me-1"></i>Locate:</label>
        <select id="locateSelect" onchange="onLocateChange()">
            <optgroup label="── Quick View ──">
                <option value="">— Select what to find —</option>
                <option value="all">Show All (clear filter)</option>
            </optgroup>
            <optgroup label="── By Performance ──">
                <option value="top5">Top 5 Students (by GPA)</option>
                <option value="top10">Top 10 Students (by GPA)</option>
                <option value="bottom5">Bottom 5 Students (by GPA)</option>
                <option value="below_avg">Students Below Class Average</option>
                <option value="above_avg">Students Above Class Average</option>
                <option value="distinction">Distinction (all A1/B2)</option>
                <option value="at_risk">At-Risk (2+ F9 grades)</option>
            </optgroup>
            <optgroup label="── By Score Range ──">
                <option value="score_gt80">Score ≥ 80 in Any Subject</option>
                <option value="score_gt70">Score ≥ 70 in Any Subject</option>
                <option value="score_lt40">Score < 40 (Fail) in Any Subject</option>
                <option value="score_lt50">Score < 50 in Any Subject</option>
                <option value="custom_min">Custom Minimum Score…</option>
                <option value="custom_max">Custom Maximum Score…</option>
            </optgroup>
            <optgroup label="── By Grade ──">
                <option value="grade_a1">Grade A1 in Any Subject</option>
                <option value="grade_b2b3">Grade B2 or B3 in Any Subject</option>
                <option value="grade_f9">Grade F9 in Any Subject</option>
                <option value="grade_e8f9">Grade E8 or F9 (Fail)</option>
            </optgroup>
            <optgroup label="── By Subject ──">
                <option value="subj_top">Top Scorer per Subject</option>
                <option value="subj_fail">All Fails in Subject</option>
                <option value="subj_pass">All Passes in Subject</option>
                <option value="subj_above_avg">Above Average in Subject</option>
                <option value="subj_below_avg">Below Average in Subject</option>
            </optgroup>
            <optgroup label="── By Completion ──">
                <option value="missing_scores">Students with Missing Scores (0s)</option>
                <option value="complete">Students with All Scores Entered</option>
            </optgroup>
        </select>
    </div>

    {{-- Subject select (shown when needed) --}}
    <select id="subjectLocateSelect" onchange="runLocate()">
        <option value="">— Pick subject —</option>
        @foreach($subjects as $subId => $subInfo)
            <option value="{{ $subId }}">{{ $subInfo['subject_name'] }}</option>
        @endforeach
    </select>

    {{-- Custom score threshold --}}
    <div id="scoreThresholdWrap">
        <label>Score:</label>
        <input type="number" id="scoreThresholdInput" min="0" max="100" placeholder="e.g. 60"
               oninput="runLocate()" />
    </div>

    {{-- Result badge --}}
    <span id="resultBadge">0 found</span>

    {{-- Scroll to first --}}
    <button id="scrollToFirst" onclick="scrollToFirstMatch()">
        <i class="ri-arrow-down-line"></i> Go to First
    </button>

    {{-- Zoom controls --}}
    <div class="zoom-group">
        <button class="zoom-btn" onclick="changeZoom(-0.1)" title="Zoom out">−</button>
        <span id="zoomLabel">100%</span>
        <button class="zoom-btn" onclick="changeZoom(+0.1)" title="Zoom in">+</button>
        <button class="zoom-btn" onclick="resetZoom()" title="Reset zoom" style="font-size:10px;width:auto;padding:0 6px;">Fit</button>
    </div>

    {{-- Print --}}
    <button class="tb-btn" onclick="window.print()">
        <i class="ri-printer-line"></i> Print
    </button>

    {{-- Back --}}
    <a href="{{ route('broadsheet.index') }}" class="tb-btn">
        <i class="ri-arrow-left-line"></i> Back
    </a>
</div>

{{-- ── SCHOOL HEADER ── --}}
<div class="bs-header">
    <img src="{{ $school_logo_base64 ?? '' }}" alt="School Logo" class="school-logo"
         onerror="this.style.display='none'">
    <div class="sch-meta">
        <div class="sch-name">{{ $schoolInfo->school_name ?? 'School Name' }}</div>
        @if(!empty($schoolInfo->school_address))
            <div class="sch-address">{{ $schoolInfo->school_address }}</div>
        @endif
        @if(!empty($schoolInfo->school_motto))
            <div class="sch-motto">"{{ $schoolInfo->school_motto }}"</div>
        @endif
    </div>
    <div class="bs-meta-pills">
        <div class="meta-pill">
            <span class="mp-val">{{ $schoolclass->schoolclass ?? '—' }} {{ $schoolclass->arm_name ?? '' }}</span>
            <span class="mp-lbl">Class</span>
        </div>
        <div class="meta-pill">
            <span class="mp-val">{{ $schoolsession->session ?? '—' }}</span>
            <span class="mp-lbl">Session</span>
        </div>
        <div class="meta-pill">
            <span class="mp-val">{{ $schoolterm->term ?? '—' }}</span>
            <span class="mp-lbl">Term</span>
        </div>
        <div class="meta-pill">
            <span class="mp-val">{{ $totalStudents }}</span>
            <span class="mp-lbl">Students</span>
        </div>
        <div class="meta-pill">
            <span class="mp-val">{{ count($subjects) }}</span>
            <span class="mp-lbl">Subjects</span>
        </div>
        <div class="meta-pill">
            <span class="mp-val">{{ $assessments->count() }}</span>
            <span class="mp-lbl">Assessments</span>
        </div>
    </div>
</div>

{{-- ── GRADE KEY ── --}}
<div class="grade-key-bar">
    <strong>Grade Key:</strong>
    <span class="gk-item">A1: 75–100</span>
    <span class="gk-item">B2: 70–74</span>
    <span class="gk-item">B3: 65–69</span>
    <span class="gk-item">C4: 60–64</span>
    <span class="gk-item">C5: 55–59</span>
    <span class="gk-item">C6: 50–54</span>
    <span class="gk-item">D7: 45–49</span>
    <span class="gk-item">E8: 40–44</span>
    <span class="gk-item" style="color:#dc2626;font-weight:700;">F9: 0–39</span>
    <span style="margin-left:auto;font-size:10px;color:#6b7280;">Generated: {{ $generatedAt }}</span>
</div>

{{-- ── TABLE WRAPPER ── --}}
<div id="bs-table-outer">
<div id="bs-zoom-wrap">
<table id="bs-table">

{{-- ════════════════ THEAD ════════════════ --}}
<thead>
{{-- Row 1: Subject group headers --}}
<tr class="subj-header">
    {{-- Fixed left cols --}}
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))
        <th class="col-sn" rowspan="2">SN</th>
    @endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))
        <th class="col-adm" rowspan="2">Adm. No.</th>
    @endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <th class="col-name" rowspan="2" style="text-align:left;">Student Name</th>
    @endif
    @if(in_array('gender', $selectedColumns))
        <th rowspan="2">Sex</th>
    @endif

    {{-- Subject spans --}}
    @php
        $showAsm  = $assessments->count() > 0;
        $showTotal = in_array('total',  $selectedColumns) || empty($selectedColumns);
        $showBf    = in_array('bf',     $selectedColumns) || empty($selectedColumns);
        $showCum   = in_array('cum',    $selectedColumns) || empty($selectedColumns);
        $showGrade = in_array('grade',  $selectedColumns) || empty($selectedColumns);
        $showPos   = in_array('position', $selectedColumns) || empty($selectedColumns);
        $showAvg   = in_array('class_average', $selectedColumns) || empty($selectedColumns);
        $showRemark= in_array('remark', $selectedColumns);

        $colsPerSubj = $assessments->count();
        foreach(['total','bf','cum','grade','position','class_average','remark'] as $col) {
            if(in_array($col, $selectedColumns) || empty($selectedColumns)) {
                if(!($col === 'remark' && !$showRemark)) $colsPerSubj++;
            }
        }
        // Simpler: count per subject columns
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

    @foreach($subjects as $subId => $subInfo)
        <th colspan="{{ $perSubjCols }}"
            style="background:{{ ['#1d4ed8','#0f766e','#7c3aed','#c2410c','#0369a1','#15803d','#b45309','#be185d'][array_search($subId, array_keys($subjects)) % 8] }};
                   border-left:2px solid rgba(255,255,255,.3);">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
                <span style="opacity:.7;font-size:8.5px;">({{ $subInfo['subject_code'] }})</span>
            @endif
        </th>
    @endforeach

    {{-- GPA cols --}}
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        <th rowspan="2" style="background:#7c3aed;">GPA</th>
    @endif
    @if(in_array('cgpa', $selectedColumns))
        <th rowspan="2" style="background:#6d28d9;">CGPA</th>
    @endif
    @if(in_array('gpa_grade', $selectedColumns))
        <th rowspan="2" style="background:#5b21b6;">GPA<br>Grade</th>
    @endif
    @if(in_array('num_subjects', $selectedColumns))
        <th rowspan="2" style="background:#4c1d95;">No.<br>Subj</th>
    @endif
    @if(in_array('total_grade_points', $selectedColumns))
        <th rowspan="2" style="background:#3b0764;">Total<br>GP</th>
    @endif
</tr>

{{-- Row 2: Assessment sub-headers per subject --}}
<tr class="asm-header">
    @foreach($subjects as $subId => $subInfo)
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))
                <th style="background:#263f6a;min-width:36px;max-width:44px;font-size:8.5px;">
                    {{ $a->name }}<br><span style="opacity:.65;">({{ $a->max_score }})</span>
                </th>
            @endif
        @endforeach
        @if($showTotal)  <th style="min-width:38px;background:#1a3356;">Total</th> @endif
        @if($showBf)     <th style="min-width:34px;background:#1a3356;">BF</th>    @endif
        @if($showCum)    <th style="min-width:36px;background:#1a3356;">Cum</th>   @endif
        @if($showGrade)  <th style="min-width:32px;background:#1a3356;">Grd</th>   @endif
        @if($showPos)    <th style="min-width:32px;background:#1a3356;">Pos</th>   @endif
        @if($showAvg)    <th style="min-width:38px;background:#1a3356;">Avg</th>   @endif
        @if($showRemark) <th style="min-width:50px;background:#1a3356;">Rmk</th>   @endif
    @endforeach
</tr>
</thead>

{{-- ════════════════ TBODY ════════════════ --}}
<tbody>
@forelse($studentRows as $idx => $stu)
@php
    $fullName = trim($stu['lastname'] . ' ' . $stu['firstname']);
    $subScores = $stu['subjects'] ?? [];

    // Compute class average across subjects for this student
    $cumVals = collect($subScores)->pluck('cum')->filter(fn($v)=>$v>0);
    $stuAvg  = $cumVals->count() > 0 ? round($cumVals->avg(),1) : 0;
    $f9Count = collect($subScores)->filter(fn($s)=>($s['grade']??'') === 'F9')->count();
@endphp
<tr data-student-id="{{ $stu['id'] }}"
    data-gpa="{{ $stu['gpa'] }}"
    data-avg="{{ $stuAvg }}"
    data-f9="{{ $f9Count }}">

    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))
        <td class="col-sn">{{ $idx + 1 }}</td>
    @endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))
        <td class="col-adm">{{ $stu['admissionno'] }}</td>
    @endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="col-name" title="{{ $fullName }}">{{ $fullName }}</td>
    @endif
    @if(in_array('gender', $selectedColumns))
        <td>{{ strtoupper(substr($stu['gender'] ?? '-', 0, 1)) }}</td>
    @endif

    @foreach($subjects as $subId => $subInfo)
        @php $ss = $subScores[$subId] ?? null; @endphp

        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))
                <td data-sub="{{ $subId }}" data-col="asm{{ $a->id }}"
                    data-score="{{ $ss['assessments'][$a->id] ?? 0 }}">
                    {{ $ss ? ($ss['assessments'][$a->id] ?? '–') : '–' }}
                </td>
            @endif
        @endforeach

        @if($showTotal)
            <td data-sub="{{ $subId }}" data-col="total"
                data-score="{{ $ss['total'] ?? 0 }}">
                {{ $ss ? $ss['total'] : '–' }}
            </td>
        @endif
        @if($showBf)
            <td data-sub="{{ $subId }}" data-col="bf"
                data-score="{{ $ss['bf'] ?? 0 }}">
                {{ $ss ? $ss['bf'] : '–' }}
            </td>
        @endif
        @if($showCum)
            <td data-sub="{{ $subId }}" data-col="cum"
                data-score="{{ $ss['cum'] ?? 0 }}">
                {{ $ss ? $ss['cum'] : '–' }}
            </td>
        @endif
        @if($showGrade)
            @php $gr = strtolower($ss['grade'] ?? 'f9'); @endphp
            <td data-sub="{{ $subId }}" data-col="grade" data-grade="{{ $ss['grade'] ?? '' }}"
                class="grade-{{ str_replace(['/','\\',' '], '', $gr) }}">
                {{ $ss ? ($ss['grade'] ?? '–') : '–' }}
            </td>
        @endif
        @if($showPos)
            <td data-sub="{{ $subId }}" data-col="position">
                {{ $ss ? ($ss['position'] ?? '–') : '–' }}
            </td>
        @endif
        @if($showAvg)
            <td data-sub="{{ $subId }}" data-col="avg"
                data-score="{{ $ss['class_average'] ?? 0 }}">
                {{ $ss ? number_format($ss['class_average'], 1) : '–' }}
            </td>
        @endif
        @if($showRemark)
            <td data-sub="{{ $subId }}" data-col="remark">
                {{ $ss ? ($ss['remark'] ?? '–') : '–' }}
            </td>
        @endif
    @endforeach

    {{-- GPA columns --}}
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        <td class="gpa-cell" data-gpa="{{ $stu['gpa'] }}">{{ $stu['gpa'] }}</td>
    @endif
    @if(in_array('cgpa', $selectedColumns))
        <td class="cgpa-cell">{{ $stu['cgpa'] }}</td>
    @endif
    @if(in_array('gpa_grade', $selectedColumns))
        <td>{{ $stu['gpa_grade'] }}</td>
    @endif
    @if(in_array('num_subjects', $selectedColumns))
        <td>{{ $stu['num_subjects'] }}</td>
    @endif
    @if(in_array('total_grade_points', $selectedColumns))
        <td>{{ $stu['total_grade_points'] }}</td>
    @endif
</tr>
@empty
<tr><td colspan="999" style="text-align:center;padding:24px;color:#9ca3af;">No student data found.</td></tr>
@endforelse
</tbody>

{{-- ════════════════ TFOOT (stats) ════════════════ --}}
<tfoot>
{{-- Class Average row --}}
<tr class="stat-avg">
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))
        <td class="col-sn">—</td>
    @endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))
        <td class="col-adm">—</td>
    @endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="col-name" style="text-align:left;font-weight:800;">CLASS AVG</td>
    @endif
    @if(in_array('gender', $selectedColumns))<td>—</td>@endif

    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))
                <td>—</td>
            @endif
        @endforeach
        @if($showTotal)  <td>{{ $st ? $st['avg'] : '—' }}</td> @endif
        @if($showBf)     <td>—</td>  @endif
        @if($showCum)    <td>—</td>  @endif
        @if($showGrade)  <td>—</td>  @endif
        @if($showPos)    <td>—</td>  @endif
        @if($showAvg)    <td>{{ $st ? $st['avg'] : '—' }}</td> @endif
        @if($showRemark) <td>—</td>  @endif
    @endforeach
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        @php $avgGpa = count($studentRows) > 0 ? round(collect($studentRows)->avg('gpa'),2) : 0; @endphp
        <td>{{ $avgGpa }}</td>
    @endif
    @if(in_array('cgpa', $selectedColumns))<td>—</td>@endif
    @if(in_array('gpa_grade', $selectedColumns))<td>—</td>@endif
    @if(in_array('num_subjects', $selectedColumns))<td>—</td>@endif
    @if(in_array('total_grade_points', $selectedColumns))<td>—</td>@endif
</tr>

{{-- Highest row --}}
<tr class="stat-high">
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))<td class="col-sn">—</td>@endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))<td class="col-adm">—</td>@endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="col-name" style="text-align:left;font-weight:800;">HIGHEST</td>
    @endif
    @if(in_array('gender', $selectedColumns))<td>—</td>@endif
    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))<td>—</td>@endif
        @endforeach
        @if($showTotal)  <td>{{ $st ? $st['highest'] : '—' }}</td> @endif
        @if($showBf)     <td>—</td> @endif
        @if($showCum)    <td>—</td> @endif
        @if($showGrade)  <td>—</td> @endif
        @if($showPos)    <td>—</td> @endif
        @if($showAvg)    <td>—</td> @endif
        @if($showRemark) <td>—</td> @endif
    @endforeach
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        @php $maxGpa = count($studentRows) > 0 ? collect($studentRows)->max('gpa') : 0; @endphp
        <td>{{ $maxGpa }}</td>
    @endif
    @if(in_array('cgpa', $selectedColumns))<td>—</td>@endif
    @if(in_array('gpa_grade', $selectedColumns))<td>—</td>@endif
    @if(in_array('num_subjects', $selectedColumns))<td>—</td>@endif
    @if(in_array('total_grade_points', $selectedColumns))<td>—</td>@endif
</tr>

{{-- Lowest row --}}
<tr class="stat-low">
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))<td class="col-sn">—</td>@endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))<td class="col-adm">—</td>@endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="col-name" style="text-align:left;font-weight:800;">LOWEST</td>
    @endif
    @if(in_array('gender', $selectedColumns))<td>—</td>@endif
    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))<td>—</td>@endif
        @endforeach
        @if($showTotal)  <td>{{ $st ? $st['lowest'] : '—' }}</td> @endif
        @if($showBf)     <td>—</td> @endif
        @if($showCum)    <td>—</td> @endif
        @if($showGrade)  <td>—</td> @endif
        @if($showPos)    <td>—</td> @endif
        @if($showAvg)    <td>—</td> @endif
        @if($showRemark) <td>—</td> @endif
    @endforeach
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))
        @php $minGpa = count($studentRows) > 0 ? collect($studentRows)->min('gpa') : 0; @endphp
        <td>{{ $minGpa }}</td>
    @endif
    @if(in_array('cgpa', $selectedColumns))<td>—</td>@endif
    @if(in_array('gpa_grade', $selectedColumns))<td>—</td>@endif
    @if(in_array('num_subjects', $selectedColumns))<td>—</td>@endif
    @if(in_array('total_grade_points', $selectedColumns))<td>—</td>@endif
</tr>

{{-- Pass count --}}
<tr class="stat-pass">
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))<td class="col-sn">—</td>@endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))<td class="col-adm">—</td>@endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="col-name" style="text-align:left;font-weight:800;">PASS COUNT</td>
    @endif
    @if(in_array('gender', $selectedColumns))<td>—</td>@endif
    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))<td>—</td>@endif
        @endforeach
        @if($showTotal)  <td>{{ $st ? $st['passed'] : '—' }}</td> @endif
        @if($showBf)     <td>—</td> @endif
        @if($showCum)    <td>—</td> @endif
        @if($showGrade)  <td>—</td> @endif
        @if($showPos)    <td>—</td> @endif
        @if($showAvg)    <td>—</td> @endif
        @if($showRemark) <td>—</td> @endif
    @endforeach
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))<td>—</td>@endif
    @if(in_array('cgpa', $selectedColumns))<td>—</td>@endif
    @if(in_array('gpa_grade', $selectedColumns))<td>—</td>@endif
    @if(in_array('num_subjects', $selectedColumns))<td>—</td>@endif
    @if(in_array('total_grade_points', $selectedColumns))<td>—</td>@endif
</tr>

{{-- Fail count --}}
<tr class="stat-fail">
    @if(in_array('sn', $selectedColumns) || empty($selectedColumns))<td class="col-sn">—</td>@endif
    @if(in_array('admission_no', $selectedColumns) || empty($selectedColumns))<td class="col-adm">—</td>@endif
    @if(in_array('name', $selectedColumns) || empty($selectedColumns))
        <td class="col-name" style="text-align:left;font-weight:800;">FAIL COUNT</td>
    @endif
    @if(in_array('gender', $selectedColumns))<td>—</td>@endif
    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? null; @endphp
        @foreach($assessments as $a)
            @if(in_array('assessment_'.$a->id, $selectedColumns) || empty($selectedColumns))<td>—</td>@endif
        @endforeach
        @if($showTotal)  <td>{{ $st ? $st['failed'] : '—' }}</td> @endif
        @if($showBf)     <td>—</td> @endif
        @if($showCum)    <td>—</td> @endif
        @if($showGrade)  <td>—</td> @endif
        @if($showPos)    <td>—</td> @endif
        @if($showAvg)    <td>—</td> @endif
        @if($showRemark) <td>—</td> @endif
    @endforeach
    @if(in_array('gpa', $selectedColumns) || empty($selectedColumns))<td>—</td>@endif
    @if(in_array('cgpa', $selectedColumns))<td>—</td>@endif
    @if(in_array('gpa_grade', $selectedColumns))<td>—</td>@endif
    @if(in_array('num_subjects', $selectedColumns))<td>—</td>@endif
    @if(in_array('total_grade_points', $selectedColumns))<td>—</td>@endif
</tr>
</tfoot>

</table>
</div>{{-- /zoom-wrap --}}
</div>{{-- /table-outer --}}

{{-- ── PASS/FAIL SUMMARY ── --}}
<div class="pf-summary">
    <h3><i class="ri-bar-chart-2-line me-2"></i>Subject Pass / Fail Summary</h3>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Students</th>
                    <th>Avg Score</th>
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
                    $total = ($st['passed'] + $st['failed']);
                    $passRate = $total > 0 ? round($st['passed'] / $total * 100, 1) : 0;
                    $n = $loop->iteration;
                @endphp
                <tr>
                    <td style="color:#9ca3af;font-size:10px;">{{ $n }}</td>
                    <td style="font-weight:600;text-align:left;">{{ $subInfo['subject_name'] }}</td>
                    <td>{{ $total }}</td>
                    <td><strong>{{ $st['avg'] }}</strong></td>
                    <td style="color:#16a34a;font-weight:700;">{{ $st['highest'] }}</td>
                    <td style="color:#d97706;font-weight:700;">{{ $st['lowest'] }}</td>
                    <td style="color:#16a34a;font-weight:700;">{{ $st['passed'] }}</td>
                    <td style="color:#dc2626;font-weight:700;">{{ $st['failed'] }}</td>
                    <td>
                        <span style="font-weight:700;color:{{ $passRate >= 50 ? '#16a34a' : '#dc2626' }};">
                            {{ $passRate }}%
                        </span>
                        <span class="pass-rate-bar">
                            <span class="pass-rate-fill" style="width:{{ $passRate }}%;background:{{ $passRate>=50?'linear-gradient(90deg,#22c55e,#16a34a)':'linear-gradient(90deg,#f87171,#dc2626)' }};"></span>
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── SIGNATURE BLOCK ── --}}
<div class="sig-block">
    @foreach(['Class Teacher','Head of Department','Vice Principal (Academics)','Principal'] as $role)
    <div class="sig-item">
        <div class="sig-line"></div>
        <div class="sig-lbl">{{ $role }}</div>
    </div>
    @endforeach
</div>

{{-- Toast --}}
<div id="bs-toast"></div>

{{-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ --}}
<script>
/* ── Subject data from PHP ── */
const SUBJECTS = @json(collect($subjects)->map(fn($s) => ['id' => $s['subject_id'], 'name' => $s['subject_name']])->values());
const SUBJECT_STATS = @json($subjectStats);

/* ── State ── */
let currentZoom    = 1.0;
let currentLocate  = '';
let matchIndices   = [];
let matchCursor    = 0;

/* ══════════════════════════════════════════════════
   ZOOM
══════════════════════════════════════════════════ */
function changeZoom(delta) {
    currentZoom = Math.min(2.5, Math.max(0.3, currentZoom + delta));
    applyZoom();
}
function resetZoom() {
    // Fit table to window width
    const outer = document.getElementById('bs-table-outer');
    const table = document.getElementById('bs-table');
    const ratio = outer.clientWidth / table.scrollWidth;
    currentZoom = Math.min(1.0, Math.max(0.3, ratio));
    applyZoom();
}
function applyZoom() {
    const wrap = document.getElementById('bs-zoom-wrap');
    wrap.style.transform = `scale(${currentZoom})`;
    wrap.style.transformOrigin = 'top left';
    // Adjust outer height to match scaled content
    const table = document.getElementById('bs-table');
    const scaledH = table.scrollHeight * currentZoom;
    document.getElementById('bs-table-outer').style.minHeight = scaledH + 'px';
    document.getElementById('zoomLabel').textContent = Math.round(currentZoom * 100) + '%';
}

/* ══════════════════════════════════════════════════
   LOCATE / FILTER ENGINE
══════════════════════════════════════════════════ */
const SUBJECT_DEPENDENT = ['subj_top','subj_fail','subj_pass','subj_above_avg','subj_below_avg'];
const SCORE_DEPENDENT   = ['custom_min','custom_max'];

function onLocateChange() {
    const val = document.getElementById('locateSelect').value;
    currentLocate = val;

    const subjSel   = document.getElementById('subjectLocateSelect');
    const scoreSel  = document.getElementById('scoreThresholdWrap');

    subjSel.style.display  = SUBJECT_DEPENDENT.includes(val) ? 'inline-block' : 'none';
    scoreSel.style.display = SCORE_DEPENDENT.includes(val)   ? 'flex'         : 'none';

    if (!SUBJECT_DEPENDENT.includes(val) && !SCORE_DEPENDENT.includes(val)) {
        runLocate();
    }
}

function runLocate() {
    const val       = currentLocate;
    const subjectId = document.getElementById('subjectLocateSelect').value;
    const threshold = parseFloat(document.getElementById('scoreThresholdInput').value) || 0;

    const rows = Array.from(document.querySelectorAll('#bs-table tbody tr[data-student-id]'));

    // Clear all highlights first
    rows.forEach(r => {
        r.classList.remove('hl-match','hl-primary','hl-danger','hl-success','hl-warning','hl-dim');
        r.querySelectorAll('td').forEach(td => td.classList.remove('cell-hl','cell-hl-danger','cell-hl-success'));
    });
    document.getElementById('resultBadge').style.display = 'none';
    document.getElementById('scrollToFirst').style.display = 'none';
    matchIndices = [];

    if (!val || val === 'all') {
        showToast('Filter cleared — showing all students');
        return;
    }

    // Compute class average GPA
    const allGpas = rows.map(r => parseFloat(r.dataset.gpa) || 0);
    const classGpaAvg = allGpas.length ? allGpas.reduce((a,b)=>a+b,0)/allGpas.length : 0;

    const matchedRows = [];

    rows.forEach((row, idx) => {
        const gpa  = parseFloat(row.dataset.gpa)  || 0;
        const avg  = parseFloat(row.dataset.avg)   || 0;
        const f9   = parseInt(row.dataset.f9)      || 0;
        let match  = false;
        let hlClass = 'hl-match';

        // All subject score cells for this row
        const scoreCells = Array.from(row.querySelectorAll('td[data-sub][data-score]'));
        const gradeCells = Array.from(row.querySelectorAll('td[data-sub][data-grade]'));

        // Subject-specific cells
        const subjScoreCells = subjectId
            ? Array.from(row.querySelectorAll(`td[data-sub="${subjectId}"][data-score]`))
            : [];
        const subjGradeCells = subjectId
            ? Array.from(row.querySelectorAll(`td[data-sub="${subjectId}"][data-grade]`))
            : [];

        // ── Avg for a specific subject
        const subjAvg = subjectId && SUBJECT_STATS[subjectId] ? SUBJECT_STATS[subjectId].avg : 0;

        switch(val) {
            /* ── performance ── */
            case 'top5':
            case 'top10': {
                // will handle after loop
                break;
            }
            case 'bottom5': break; // handled after loop
            case 'below_avg':
                match = gpa < classGpaAvg;
                hlClass = 'hl-warning';
                break;
            case 'above_avg':
                match = gpa > classGpaAvg;
                hlClass = 'hl-success';
                break;
            case 'distinction':
                // all grades in graded cells are A1 or B2
                match = gradeCells.length > 0 &&
                    gradeCells.every(td => ['A1','B2'].includes(td.dataset.grade));
                hlClass = 'hl-success';
                break;
            case 'at_risk':
                match = f9 >= 2;
                hlClass = 'hl-danger';
                break;

            /* ── score range ── */
            case 'score_gt80':
                match = scoreCells.some(td => parseFloat(td.dataset.score) >= 80);
                hlClass = 'hl-success';
                if(match) scoreCells.forEach(td => {
                    if(parseFloat(td.dataset.score)>=80) td.classList.add('cell-hl-success');
                });
                break;
            case 'score_gt70':
                match = scoreCells.some(td => parseFloat(td.dataset.score) >= 70);
                hlClass = 'hl-success';
                if(match) scoreCells.forEach(td => {
                    if(parseFloat(td.dataset.score)>=70) td.classList.add('cell-hl-success');
                });
                break;
            case 'score_lt40':
                match = scoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < 40;
                });
                hlClass = 'hl-danger';
                if(match) scoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if(s>0 && s<40) td.classList.add('cell-hl-danger');
                });
                break;
            case 'score_lt50':
                match = scoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < 50;
                });
                hlClass = 'hl-warning';
                if(match) scoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if(s>0 && s<50) td.classList.add('cell-hl');
                });
                break;
            case 'custom_min':
                match = threshold > 0 && scoreCells.some(td => parseFloat(td.dataset.score) >= threshold);
                hlClass = 'hl-primary';
                if(match) scoreCells.forEach(td => {
                    if(parseFloat(td.dataset.score)>=threshold) td.classList.add('cell-hl');
                });
                break;
            case 'custom_max':
                match = threshold > 0 && scoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s <= threshold;
                });
                hlClass = 'hl-warning';
                if(match) scoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if(s>0 && s<=threshold) td.classList.add('cell-hl');
                });
                break;

            /* ── by grade ── */
            case 'grade_a1':
                match = gradeCells.some(td => td.dataset.grade === 'A1');
                hlClass = 'hl-success';
                if(match) gradeCells.forEach(td => {
                    if(td.dataset.grade==='A1') td.classList.add('cell-hl-success');
                });
                break;
            case 'grade_b2b3':
                match = gradeCells.some(td => ['B2','B3'].includes(td.dataset.grade));
                hlClass = 'hl-primary';
                if(match) gradeCells.forEach(td => {
                    if(['B2','B3'].includes(td.dataset.grade)) td.classList.add('cell-hl');
                });
                break;
            case 'grade_f9':
                match = gradeCells.some(td => td.dataset.grade === 'F9');
                hlClass = 'hl-danger';
                if(match) gradeCells.forEach(td => {
                    if(td.dataset.grade==='F9') td.classList.add('cell-hl-danger');
                });
                break;
            case 'grade_e8f9':
                match = gradeCells.some(td => ['E8','F9'].includes(td.dataset.grade));
                hlClass = 'hl-danger';
                if(match) gradeCells.forEach(td => {
                    if(['E8','F9'].includes(td.dataset.grade)) td.classList.add('cell-hl-danger');
                });
                break;

            /* ── by subject ── */
            case 'subj_fail':
                if(!subjectId) return;
                match = subjGradeCells.some(td => ['E8','F9'].includes(td.dataset.grade));
                hlClass = 'hl-danger';
                break;
            case 'subj_pass':
                if(!subjectId) return;
                match = subjGradeCells.some(td => !['E8','F9'].includes(td.dataset.grade) && td.dataset.grade !== '');
                hlClass = 'hl-success';
                break;
            case 'subj_above_avg':
                if(!subjectId) return;
                match = subjScoreCells.some(td => parseFloat(td.dataset.score) > subjAvg);
                hlClass = 'hl-success';
                break;
            case 'subj_below_avg':
                if(!subjectId) return;
                match = subjScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < subjAvg;
                });
                hlClass = 'hl-warning';
                break;
            case 'subj_top':
                if(!subjectId) return;
                // handled after loop
                break;

            /* ── completion ── */
            case 'missing_scores':
                match = scoreCells.some(td => parseFloat(td.dataset.score) === 0);
                hlClass = 'hl-warning';
                if(match) scoreCells.forEach(td => {
                    if(parseFloat(td.dataset.score)===0) td.classList.add('cell-hl');
                });
                break;
            case 'complete':
                match = scoreCells.length > 0 && scoreCells.every(td => parseFloat(td.dataset.score) > 0);
                hlClass = 'hl-success';
                break;
        }

        if(match) {
            row.classList.add(hlClass);
            matchedRows.push({ idx, row, gpa, hlClass });
        }
    });

    /* ── Handle top/bottom N (requires sort after loop) ── */
    if(['top5','top10','bottom5','subj_top'].includes(val)) {
        const sorted = [...rows].map((r,i)=>({
            row: r,
            idx: i,
            gpa: parseFloat(r.dataset.gpa)||0,
            subjScore: subjectId ? (()=>{
                const c = r.querySelector(`td[data-sub="${subjectId}"][data-col="total"]`);
                return c ? parseFloat(c.dataset.score)||0 : 0;
            })() : 0,
        }));

        if(val === 'top5' || val === 'top10') {
            const n = val === 'top5' ? 5 : 10;
            sorted.sort((a,b) => b.gpa - a.gpa);
            sorted.slice(0, n).forEach(({row}) => { row.classList.add('hl-success'); matchedRows.push({row}); });
        } else if(val === 'bottom5') {
            sorted.sort((a,b) => a.gpa - b.gpa);
            sorted.slice(0, 5).forEach(({row}) => { row.classList.add('hl-danger'); matchedRows.push({row}); });
        } else if(val === 'subj_top') {
            if(subjectId) {
                sorted.sort((a,b) => b.subjScore - a.subjScore);
                const maxScore = sorted[0]?.subjScore || 0;
                sorted.filter(s => s.subjScore === maxScore && s.subjScore > 0)
                    .forEach(({row}) => { row.classList.add('hl-success'); matchedRows.push({row}); });
            }
        }
    }

    /* ── Dim non-matching rows ── */
    const allMatchedRows = document.querySelectorAll(
        '#bs-table tbody tr.hl-match, #bs-table tbody tr.hl-primary, ' +
        '#bs-table tbody tr.hl-danger, #bs-table tbody tr.hl-success, #bs-table tbody tr.hl-warning'
    );
    if(allMatchedRows.length > 0) {
        rows.forEach(r => {
            if(!r.classList.contains('hl-match') && !r.classList.contains('hl-primary') &&
               !r.classList.contains('hl-danger') && !r.classList.contains('hl-success') &&
               !r.classList.contains('hl-warning')) {
                r.classList.add('hl-dim');
            }
        });
    }

    /* ── Build scroll index ── */
    matchIndices = Array.from(allMatchedRows);
    matchCursor = 0;

    /* ── Update badge ── */
    const badge = document.getElementById('resultBadge');
    badge.textContent = allMatchedRows.length + ' found';
    badge.style.display = allMatchedRows.length > 0 ? 'inline-block' : 'none';

    const btn = document.getElementById('scrollToFirst');
    btn.style.display = allMatchedRows.length > 0 ? 'flex' : 'none';

    showToast(allMatchedRows.length + ' student(s) matched');
}

function scrollToFirstMatch() {
    if(matchIndices.length === 0) return;
    const row = matchIndices[matchCursor % matchIndices.length];
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    // Flash the row
    row.style.boxShadow = 'inset 0 0 0 2px #f59e0b';
    setTimeout(() => row.style.boxShadow = '', 1500);
    matchCursor++;
    if(matchCursor >= matchIndices.length) matchCursor = 0;
    const btn = document.getElementById('scrollToFirst');
    btn.innerHTML = `<i class="ri-arrow-down-line"></i> Next (${matchCursor}/${matchIndices.length})`;
    if(matchCursor === 0) btn.innerHTML = `<i class="ri-arrow-up-line"></i> Wrap (${matchIndices.length})`;
}

/* ══════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════ */
let toastTimer = null;
function showToast(msg) {
    const t = document.getElementById('bs-toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

/* ══════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
══════════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if(e.ctrlKey || e.metaKey) {
        if(e.key === '=' || e.key === '+') { e.preventDefault(); changeZoom(+0.1); }
        if(e.key === '-')                  { e.preventDefault(); changeZoom(-0.1); }
        if(e.key === '0')                  { e.preventDefault(); resetZoom(); }
    }
    if(e.key === 'Escape') {
        document.getElementById('locateSelect').value = '';
        onLocateChange();
    }
    if(e.key === 'Enter' && matchIndices.length > 0) {
        scrollToFirstMatch();
    }
});

/* ══════════════════════════════════════════════════
   COLUMN HEADER CLICK → SCROLL TO SUBJECT
══════════════════════════════════════════════════ */
document.querySelectorAll('#bs-table thead tr.subj-header th[colspan]').forEach(th => {
    th.style.cursor = 'pointer';
    th.title = 'Click to go to this subject in the locate dropdown';
});

/* ══════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    // Auto-fit zoom on load
    setTimeout(resetZoom, 100);
    showToast('Broadsheet loaded — use Ctrl+/- to zoom, Esc to clear filters');
});
</script>
@endsection
