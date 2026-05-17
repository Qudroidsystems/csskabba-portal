@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
/* ══════════════════════════════════════════════════════════════
   DESIGN SYSTEM — Navy & Teal, DM Sans
══════════════════════════════════════════════════════════════ */
:root {
    --cb-navy:    #0f2342;
    --cb-teal:    #0d9488;
    --cb-sky:     #0ea5e9;
    --cb-amber:   #f59e0b;
    --cb-rose:    #f43f5e;
    --cb-green:   #22c55e;
    --cb-muted:   #64748b;
    --cb-border:  #e2e8f0;
    --cb-surface: #f8fafc;
    --cb-white:   #ffffff;
    --cb-radius:  14px;
    --cb-shadow:  0 4px 16px rgba(15,35,66,.10);
    --cb-shadow-lg: 0 8px 32px rgba(15,35,66,.14);
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; }

/* ── Hero ──────────────────────────────────────────────────── */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.cb-hero::before {
    content: '';
    position: absolute; top: -80px; right: -80px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
    border-radius: 50%;
}
.cb-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 26px; font-weight: 700;
    color: #fff; margin: 0 0 8px;
}
.cb-hero p { font-size: 13px; color: rgba(255,255,255,.72); margin: 0; }
.cb-hero .meta-pills {
    display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px;
}
.cb-meta-pill {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px; font-weight: 600; color: #fff;
    display: inline-flex; align-items: center; gap: 5px;
}

/* ── Stat cards ────────────────────────────────────────────── */
.cb-stat {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    padding: 20px 22px;
    position: relative; overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.cb-stat:hover { transform: translateY(-2px); box-shadow: var(--cb-shadow); }
.cb-stat .stat-accent {
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: var(--cb-radius) var(--cb-radius) 0 0;
}
.cb-stat .stat-value { font-size: 30px; font-weight: 700; color: var(--cb-navy); line-height: 1; margin-top: 8px; }
.cb-stat .stat-label { font-size: 12px; color: var(--cb-muted); margin-top: 5px; font-weight: 500; }
.cb-stat .stat-ico { font-size: 36px; opacity: .08; position: absolute; right: 16px; top: 50%; transform: translateY(-50%); }

/* ── Column Toggle Panel ───────────────────────────────────── */
.col-toggle-panel {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    padding: 18px 22px;
    margin-bottom: 22px;
    box-shadow: var(--cb-shadow);
}
.col-toggle-panel h6 {
    font-size: 13px; font-weight: 700; color: var(--cb-navy);
    margin: 0 0 14px; display: flex; align-items: center; gap: 7px;
}
.toggle-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.toggle-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    border: 1.5px solid var(--cb-border);
    background: var(--cb-surface); color: var(--cb-muted);
    transition: all .18s ease; user-select: none;
}
.toggle-chip:hover { border-color: var(--cb-teal); color: var(--cb-teal); }
.toggle-chip.active {
    background: var(--cb-teal); border-color: var(--cb-teal);
    color: #fff; box-shadow: 0 2px 8px rgba(13,148,136,.3);
}
.toggle-chip i { font-size: 13px; }

/* ── Main Card / Table ─────────────────────────────────────── */
.cb-card {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    box-shadow: var(--cb-shadow);
    overflow: hidden;
}
.cb-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--cb-border);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    background: linear-gradient(to right, #f8fafc, #f0fdf9);
}
.cb-card-header h5 {
    font-size: 15px; font-weight: 700; color: var(--cb-navy); margin: 0;
    display: flex; align-items: center; gap: 8px;
}

.cb-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.cb-table thead th {
    background: var(--cb-navy); color: #fff;
    padding: 11px 14px; font-weight: 600; font-size: 11.5px;
    white-space: nowrap; text-align: center;
    border-right: 1px solid rgba(255,255,255,.08);
    position: sticky; top: 0; z-index: 2;
}
.cb-table thead th.col-name-header { text-align: left; }
.cb-table tbody td {
    padding: 10px 14px; vertical-align: middle;
    border-bottom: 1px solid var(--cb-border);
    text-align: center;
}
.cb-table tbody td.td-name { text-align: left; }
.cb-table tbody tr:hover td { background: #f0fdf9; }
.cb-table tbody tr:last-child td { border-bottom: none; }

/* Score dual rows inside table cell */
.score-dual { display: flex; flex-direction: column; gap: 2px; min-width: 80px; }
.score-row {
    display: flex; align-items: center; justify-content: center; gap: 4px;
    padding: 2px 5px; border-radius: 5px;
    font-size: 11px; font-weight: 700;
}
.score-row-term {
    background: rgba(14,165,233,.08); border-left: 2.5px solid #0ea5e9;
}
.score-row-cum {
    background: rgba(15,35,66,.06); border-left: 2.5px solid var(--cb-navy);
}
.score-lbl {
    font-size: 8.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .4px; opacity: .7;
}
.grade-badge {
    display: inline-block; padding: 1px 5px;
    border-radius: 8px; font-size: 9px; font-weight: 700;
}
/* Grade colours */
.g-a, .g-a1 { background: #dcfce7; color: #15803d; }
.g-b, .g-b2, .g-b3 { background: #dbeafe; color: #1d4ed8; }
.g-c, .g-c4, .g-c5, .g-c6 { background: #fef9c3; color: #a16207; }
.g-d, .g-d7 { background: #ffedd5; color: #c2410c; }
.g-e, .g-e8 { background: #ffe4e6; color: #be123c; }
.g-f, .g-f9 { background: #fee2e2; color: #b91c1c; }

.score-red   { color: #dc2626 !important; }
.score-amber { color: #d97706 !important; }
.score-green { color: #16a34a !important; }

/* Analytics mini card in summary column */
.analytics-cell {
    min-width: 130px;
    font-size: 11px; line-height: 1.4;
}
.analytics-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 2px 0; gap: 6px;
}
.analytics-lbl { color: var(--cb-muted); font-size: 10px; font-weight: 500; }
.analytics-val { font-weight: 700; color: var(--cb-navy); font-size: 11.5px; }
.pct-bar-wrap { background: #e2e8f0; border-radius: 4px; height: 5px; margin-top: 3px; overflow: hidden; }
.pct-bar { height: 100%; border-radius: 4px; transition: width .4s; }

/* Comment inputs */
.cb-input {
    border: 1.5px solid var(--cb-border);
    border-radius: 8px; padding: 6px 10px;
    font-size: 12px; width: 100%;
    transition: border .15s, box-shadow .15s;
    background: var(--cb-surface);
    font-family: 'DM Sans', sans-serif;
}
.cb-input:focus {
    border-color: var(--cb-teal);
    outline: none;
    box-shadow: 0 0 0 3px rgba(13,148,136,.12);
    background: #fff;
}
.cb-input.required-input { border-left: 3px solid var(--cb-teal); }
.cb-input.required-input:invalid,
.cb-input.required-input.empty-required { border-left-color: var(--cb-rose); }

.absence-input { width: 72px !important; text-align: center; }

/* Student name cell */
.student-name-cell { display: flex; align-items: center; gap: 9px; }
.avatar-wrap {
    width: 36px; height: 36px; border-radius: 50%;
    overflow: hidden; flex-shrink: 0;
    border: 2px solid var(--cb-border); cursor: pointer;
    transition: border-color .15s;
}
.avatar-wrap:hover { border-color: var(--cb-teal); }
.avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
.avatar-initials {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--cb-teal), var(--cb-sky));
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
    cursor: pointer;
}
.student-name-text { font-weight: 600; font-size: 12.5px; color: var(--cb-navy); }
.student-adm  { font-size: 10.5px; color: var(--cb-muted); margin-top: 1px; }

/* Tooltip for grade detail */
.grade-tooltip-wrap { position: relative; display: inline-block; }
.grade-tooltip-btn {
    background: none; border: none; cursor: pointer;
    color: var(--cb-sky); font-size: 14px; padding: 2px;
    transition: color .15s;
}
.grade-tooltip-btn:hover { color: var(--cb-teal); }
.grade-detail-box {
    display: none;
    position: fixed; z-index: 9999;
    background: var(--cb-white);
    border: 2px solid var(--cb-teal);
    border-radius: 12px;
    box-shadow: var(--cb-shadow-lg);
    width: 340px;
    font-size: 11.5px;
}
.grade-detail-box.show { display: block; }
.gdb-header {
    background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal));
    color: #fff; padding: 10px 14px; border-radius: 10px 10px 0 0;
    font-weight: 700; font-size: 13px; display: flex; justify-content: space-between; align-items: center;
}
.gdb-close {
    background: rgba(255,255,255,.2); border: none; color: #fff;
    border-radius: 50%; width: 22px; height: 22px;
    cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;
}
.gdb-body { padding: 12px 14px; max-height: 320px; overflow-y: auto; }
.gdb-table { width: 100%; border-collapse: collapse; }
.gdb-table th {
    background: var(--cb-surface); color: var(--cb-muted);
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    padding: 6px 8px; border-bottom: 1px solid var(--cb-border); text-align: center;
}
.gdb-table th:first-child { text-align: left; }
.gdb-table td {
    padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-weight: 600; text-align: center;
}
.gdb-table td:first-child { text-align: left; font-size: 11px; color: var(--cb-navy); }
.gdb-summary {
    background: var(--cb-surface); border-radius: 8px; padding: 8px 12px;
    margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
}
.gdb-sum-item { text-align: center; }
.gdb-sum-lbl { font-size: 9px; color: var(--cb-muted); text-transform: uppercase; letter-spacing: .4px; }
.gdb-sum-val { font-size: 15px; font-weight: 700; color: var(--cb-navy); }

/* Floating save bar */
.save-bar {
    position: sticky; bottom: 0; z-index: 100;
    background: rgba(15,35,66,.96); backdrop-filter: blur(10px);
    border-top: 2px solid var(--cb-teal);
    padding: 14px 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
    border-radius: 0 0 var(--cb-radius) var(--cb-radius);
}
.save-bar .sig-group { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.save-bar label { color: rgba(255,255,255,.7); font-size: 12px; font-weight: 500; margin-bottom: 0; }
.file-input-wrap { position: relative; }
.file-input-wrap input[type=file] {
    padding: 6px 12px; border-radius: 8px;
    border: 1.5px solid rgba(255,255,255,.2);
    background: rgba(255,255,255,.08); color: #fff;
    font-size: 12px; cursor: pointer;
}
.file-input-wrap input[type=file]:hover { border-color: var(--cb-teal); }
.btn-save-all {
    background: var(--cb-teal); color: #fff;
    border: none; border-radius: 10px;
    padding: 10px 28px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background .18s, transform .12s, box-shadow .18s;
    display: flex; align-items: center; gap: 8px;
    font-family: 'DM Sans', sans-serif;
}
.btn-save-all:hover { background: #0b7c72; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(13,148,136,.4); }
.btn-save-all:active { transform: translateY(0); }
.btn-save-all:disabled { opacity: .65; transform: none; cursor: not-allowed; }

/* Toast */
.cb-toast {
    position: fixed; bottom: 80px; right: 24px;
    min-width: 300px; z-index: 99999;
    padding: 14px 18px; border-radius: 12px;
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; font-weight: 600;
    box-shadow: var(--cb-shadow-lg); animation: slideUp .3s ease;
}
.cb-toast-success { background: #ecfdf5; border: 1.5px solid #86efac; color: #15803d; }
.cb-toast-error   { background: #fff1f2; border: 1.5px solid #fca5a5; color: #be123c; }
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Mobile cards */
.mobile-cards { display: none; }
.cb-student-card {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    margin-bottom: 18px;
    box-shadow: var(--cb-shadow);
    overflow: hidden;
}
.cb-student-card .card-top {
    background: linear-gradient(135deg, #f8fafc, #f0fdf9);
    padding: 14px 16px;
    border-bottom: 1px solid var(--cb-border);
    display: flex; align-items: center; gap: 12px;
}
.cb-student-card .card-body-pad { padding: 16px; }
.performance-strip {
    background: linear-gradient(135deg, var(--cb-navy), #1e5f74);
    border-radius: 10px; padding: 12px 16px; color: #fff;
    margin-bottom: 14px;
}
.performance-strip .ps-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px;
}
.ps-item { text-align: center; background: rgba(255,255,255,.1); border-radius: 8px; padding: 8px; }
.ps-lbl { font-size: 9px; opacity: .8; text-transform: uppercase; letter-spacing: .4px; }
.ps-val { font-size: 16px; font-weight: 700; }
.subjects-scroll {
    display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 14px;
}
.subjects-scroll::-webkit-scrollbar { height: 3px; }
.subjects-scroll::-webkit-scrollbar-thumb { background: var(--cb-border); border-radius: 2px; }
.subj-chip {
    flex-shrink: 0; text-align: center;
    border: 1px solid var(--cb-border); border-radius: 10px;
    padding: 8px 10px; min-width: 80px;
    background: var(--cb-surface);
}
.subj-chip .sc-name { font-size: 9.5px; font-weight: 600; color: var(--cb-muted); margin-bottom: 4px; line-height: 1.2; }
.subj-chip .sc-term { font-size: 10px; font-weight: 700; border-radius: 4px; padding: 1px 4px; margin-bottom: 2px; background: rgba(14,165,233,.1); color: #0369a1; }
.subj-chip .sc-cum  { font-size: 10px; font-weight: 700; border-radius: 4px; padding: 1px 4px; background: rgba(15,35,66,.07); color: var(--cb-navy); }
.comment-field-group { margin-bottom: 10px; }
.comment-field-group label { font-size: 11px; font-weight: 600; color: var(--cb-muted); margin-bottom: 4px; display: block; }

/* Search */
.cb-search { position: relative; margin-bottom: 20px; }
.cb-search input {
    width: 100%; padding: 10px 16px 10px 40px;
    border: 1.5px solid var(--cb-border); border-radius: 10px;
    font-size: 13px; background: var(--cb-surface);
    font-family: 'DM Sans', sans-serif;
    transition: border .15s, box-shadow .15s;
}
.cb-search input:focus { border-color: var(--cb-teal); outline: none; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.cb-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--cb-muted); }

/* Responsive breakpoints */
@media (max-width: 1199px) {
    .desktop-table { display: none !important; }
    .mobile-cards  { display: block !important; }
}
@media (min-width: 1200px) {
    .desktop-table { display: block !important; }
    .mobile-cards  { display: none !important; }
}

/* Image zoom modal */
.img-zoom-modal .modal-content { background: transparent; border: none; }
.img-zoom-modal .modal-dialog  { max-width: 90vw; }
.zoomed-img {
    max-width: 85vw; max-height: 70vh; border-radius: 16px;
    border: 4px solid #fff; box-shadow: 0 20px 60px rgba(0,0,0,.4);
    object-fit: contain;
}
.zoom-close {
    position: absolute; top: 12px; right: 20px;
    background: rgba(0,0,0,.7); border: none; color: #fff;
    border-radius: 50%; width: 36px; height: 36px;
    font-size: 18px; cursor: pointer; z-index: 10;
    display: flex; align-items: center; justify-content: center;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- ── Hero ── --}}
    <div class="cb-hero">
        <h1><i class="ri-clipboard-line me-2"></i>Class Broadsheet</h1>
        <p>Review student performance, assign comments, and track attendance for your class.</p>
        <div class="meta-pills">
            <span class="cb-meta-pill"><i class="ri-building-line"></i>{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</span>
            <span class="cb-meta-pill"><i class="ri-calendar-line"></i>{{ $schoolterm }}</span>
            <span class="cb-meta-pill"><i class="ri-calendar-event-line"></i>{{ $schoolsession }}</span>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="cb-stat">
                <div class="stat-accent" style="background: linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
                <div class="stat-ico"><i class="ri-group-line"></i></div>
                <div class="stat-value">{{ $students->count() }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="cb-stat">
                <div class="stat-accent" style="background: linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
                <div class="stat-ico"><i class="ri-book-open-line"></i></div>
                <div class="stat-value text-info">{{ $subjects->count() }}</div>
                <div class="stat-label">Subjects</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="cb-stat">
                <div class="stat-accent" style="background: linear-gradient(90deg,var(--cb-green),#4ade80);"></div>
                <div class="stat-ico"><i class="ri-percent-line"></i></div>
                <div class="stat-value text-success" id="statPassRate">—</div>
                <div class="stat-label">Avg Cum %</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="cb-stat">
                <div class="stat-accent" style="background: linear-gradient(90deg,var(--cb-amber),#fcd34d);"></div>
                <div class="stat-ico"><i class="ri-award-line"></i></div>
                <div class="stat-value text-warning" id="statTop">—</div>
                <div class="stat-label">Top Performer</div>
            </div>
        </div>
    </div>

    {{-- ── Column Toggle Panel ── --}}
    <div class="col-toggle-panel">
        <h6><i class="ri-layout-column-line" style="color:var(--cb-teal)"></i> Show / Hide Columns</h6>
        <div class="toggle-chips">
            <span class="toggle-chip active" data-col="col-scores">
                <i class="ri-bar-chart-line"></i> Subject Scores
            </span>
            <span class="toggle-chip active" data-col="col-summary">
                <i class="ri-pie-chart-line"></i> Summary
            </span>
            <span class="toggle-chip active" data-col="col-teacher">
                <i class="ri-chat-3-line"></i> Teacher's Comment
                <sup style="color:var(--cb-rose);font-size:9px;">★</sup>
            </span>
            <span class="toggle-chip active" data-col="col-guidance">
                <i class="ri-mental-health-line"></i> Counselor's Comment
            </span>
            <span class="toggle-chip active" data-col="col-activities">
                <i class="ri-football-line"></i> Remark on Activities
            </span>
            <span class="toggle-chip active" data-col="col-absence">
                <i class="ri-calendar-close-line"></i> Absences
            </span>
        </div>
        <p class="mt-2 mb-0" style="font-size:11px;color:var(--cb-muted);">
            <i class="ri-information-line me-1"></i>
            <strong style="color:var(--cb-rose);">★</strong> Teacher's Comment is required for each student. Other columns are optional.
        </p>
    </div>

    @if ($students->isNotEmpty())

    {{-- ── Main Form ── --}}
    <div class="cb-card">
        <div class="cb-card-header">
            <h5>
                <i class="ri-table-alt-line" style="color:var(--cb-teal)"></i>
                Student Performance &amp; Comments
                <span class="badge" style="background:var(--cb-teal);color:#fff;font-size:11px;">{{ $students->count() }} Students</span>
            </h5>
            <div class="cb-search" style="margin:0;max-width:260px;">
                <i class="ri-search-line"></i>
                <input type="text" id="searchInput" placeholder="Search students…">
            </div>
        </div>

        <form id="commentsForm">
            @csrf

            {{-- ══════════════════════════════════════════════════════════
                 DESKTOP TABLE
            ══════════════════════════════════════════════════════════ --}}
            <div class="desktop-table" style="overflow-x:auto;">
                <table class="cb-table">
                    <thead>
                        <tr>
                            <th style="width:34px;">#</th>
                            <th class="col-name-header" style="min-width:190px;">Student</th>
                            {{-- Scores --}}
                            @foreach ($subjects as $subject)
                                <th class="col-scores" style="min-width:86px;">{{ $subject->subject }}</th>
                            @endforeach
                            {{-- Summary --}}
                            <th class="col-summary" style="min-width:140px;">Summary</th>
                            {{-- Comments --}}
                            <th class="col-teacher" style="min-width:180px;">
                                Teacher's Comment <sup style="color:var(--cb-rose);">★</sup>
                            </th>
                            <th class="col-guidance" style="min-width:160px;">Counselor's Comment</th>
                            <th class="col-activities" style="min-width:160px;">Remark on Activities</th>
                            <th class="col-absence" style="min-width:80px;">Absent</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @foreach ($students as $index => $student)
                            @php
                                $sid      = $student->id;
                                $initials = strtoupper(substr($student->fname, 0, 1) . substr($student->lastname, 0, 1));
                                $hasPic   = !empty($student->picture) && $student->picture !== 'unnamed.jpg';
                                $imgUrl   = $hasPic ? asset('storage/student_avatars/' . basename($student->picture)) : null;
                                $fullName = trim($student->lastname . ' ' . $student->fname . ' ' . ($student->othername ?? ''));
                                $profile  = $personalityProfiles->where('studentid', $sid)->first();
                                $analytics = $studentAnalytics[$sid] ?? [];
                            @endphp
                            <tr class="student-row" data-student-id="{{ $sid }}" data-search="{{ strtolower($fullName . ' ' . $student->admissionNo) }}">
                                <td>{{ $index + 1 }}</td>

                                {{-- Name cell --}}
                                <td class="td-name">
                                    <div class="student-name-cell">
                                        @if($imgUrl)
                                            <div class="avatar-wrap" data-bs-toggle="modal" data-bs-target="#imgZoomModal"
                                                 data-image="{{ $imgUrl }}" data-name="{{ $fullName }}">
                                                <img src="{{ $imgUrl }}" alt="{{ $fullName }}"
                                                     onerror="this.parentElement.innerHTML='<div class=\'avatar-initials\'>{{ $initials }}</div>'">
                                            </div>
                                        @else
                                            <div class="avatar-initials" data-bs-toggle="modal" data-bs-target="#imgZoomModal"
                                                 data-initials="{{ $initials }}" data-name="{{ $fullName }}">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="student-name-text">{{ $fullName }}</div>
                                            <div class="student-adm">{{ $student->admissionNo }} · {{ $student->gender ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Subject score dual rows --}}
                                @foreach ($subjects as $subject)
                                    @php
                                        $termScore = $termScoreMap[$sid][$subject->subject] ?? 0;
                                        $cumScore  = $cumScoreMap[$sid][$subject->subject]  ?? 0;

                                        // Grades
                                        [$termGrade] = app(\App\Http\Controllers\ClassBroadsheetController::class)->gradeFromScorePublic((float)$termScore, $isSenior);
                                        [$cumGrade]  = app(\App\Http\Controllers\ClassBroadsheetController::class)->gradeFromScorePublic((float)$cumScore,  $isSenior);

                                        $tGradeLower = strtolower(str_replace([' ','-'], '', $termGrade));
                                        $cGradeLower = strtolower(str_replace([' ','-'], '', $cumGrade));

                                        $termColor = $termScore < 40 ? 'score-red' : ($termScore < 50 ? 'score-amber' : 'score-green');
                                        $cumColor  = $cumScore  < 40 ? 'score-red' : ($cumScore  < 50 ? 'score-amber' : 'score-green');
                                    @endphp
                                    <td class="col-scores">
                                        <div class="score-dual">
                                            <div class="score-row score-row-term">
                                                <span class="score-lbl" style="color:#0891b2;">T</span>
                                                <span class="{{ $termColor }}">{{ $termScore ?: '—' }}</span>
                                                @if($termGrade !== '-')
                                                    <span class="grade-badge g-{{ $tGradeLower }}">{{ $termGrade }}</span>
                                                @endif
                                            </div>
                                            <div class="score-row score-row-cum">
                                                <span class="score-lbl" style="color:var(--cb-navy);">C</span>
                                                <span class="{{ $cumColor }}">{{ $cumScore ?: '—' }}</span>
                                                @if($cumGrade !== '-')
                                                    <span class="grade-badge g-{{ $cGradeLower }}">{{ $cumGrade }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endforeach

                                {{-- Summary column --}}
                                <td class="col-summary analytics-cell">
                                    <div class="analytics-row">
                                        <span class="analytics-lbl">Term Avg</span>
                                        <span class="analytics-val {{ ($analytics['term_average'] ?? 0) < 50 ? 'score-red' : 'score-green' }}">
                                            {{ $analytics['term_average'] ?? 0 }}
                                        </span>
                                    </div>
                                    <div class="analytics-row">
                                        <span class="analytics-lbl">Cum Avg</span>
                                        <span class="analytics-val {{ ($analytics['cum_average'] ?? 0) < 50 ? 'score-red' : 'score-green' }}">
                                            {{ $analytics['cum_average'] ?? 0 }}
                                        </span>
                                    </div>
                                    <div class="analytics-row">
                                        <span class="analytics-lbl">Obtainable</span>
                                        <span class="analytics-val">{{ $analytics['total_obtainable'] ?? 0 }}</span>
                                    </div>
                                    <div class="analytics-row">
                                        <span class="analytics-lbl">Cum %</span>
                                        <span class="analytics-val {{ ($analytics['cum_percentage'] ?? 0) < 50 ? 'score-red' : 'score-green' }}">
                                            {{ $analytics['cum_percentage'] ?? 0 }}%
                                        </span>
                                    </div>
                                    <div class="pct-bar-wrap mt-1">
                                        <div class="pct-bar"
                                             style="width:{{ $analytics['cum_percentage'] ?? 0 }}%;
                                                    background: {{ ($analytics['cum_percentage'] ?? 0) >= 50 ? 'var(--cb-green)' : 'var(--cb-rose)' }};">
                                        </div>
                                    </div>
                                    {{-- Grade detail trigger --}}
                                    <div class="text-center mt-1">
                                        <button type="button" class="grade-tooltip-btn grade-trigger"
                                                data-student-id="{{ $sid }}"
                                                data-student-name="{{ $fullName }}"
                                                title="View grade breakdown">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </div>
                                </td>

                                {{-- Teacher comment (required) --}}
                                <td class="col-teacher">
                                    <input type="text"
                                           class="cb-input required-input teacher-comment"
                                           name="teacher_comments[{{ $sid }}]"
                                           data-student-id="{{ $sid }}"
                                           value="{{ $profile ? $profile->classteachercomment : '' }}"
                                           placeholder="Required comment…"
                                           required>
                                </td>

                                {{-- Guidance comment --}}
                                <td class="col-guidance">
                                    <input type="text"
                                           class="cb-input"
                                           name="guidance_comments[{{ $sid }}]"
                                           data-student-id="{{ $sid }}"
                                           value="{{ $profile ? $profile->guidancescomment : '' }}"
                                           placeholder="Optional…">
                                </td>

                                {{-- Remark on activities --}}
                                <td class="col-activities">
                                    <input type="text"
                                           class="cb-input"
                                           name="remarks_on_other_activities[{{ $sid }}]"
                                           data-student-id="{{ $sid }}"
                                           value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                           placeholder="Optional…">
                                </td>

                                {{-- Absence --}}
                                <td class="col-absence">
                                    <input type="number"
                                           class="cb-input absence-input"
                                           name="no_of_times_school_absent[{{ $sid }}]"
                                           data-student-id="{{ $sid }}"
                                           value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                           min="0" placeholder="0">
                                </td>
                            </tr>

                            {{-- HIDDEN grade detail box --}}
                            <tr style="display:none;">
                                <td colspan="99" style="padding:0;">
                                    <div class="grade-detail-box" id="gdb-{{ $sid }}">
                                        <div class="gdb-header">
                                            <span><i class="ri-bar-chart-line me-1"></i>{{ $fullName }}'s Grade Breakdown</span>
                                            <button type="button" class="gdb-close gdb-close-btn">&times;</button>
                                        </div>
                                        <div class="gdb-body">
                                            <table class="gdb-table">
                                                <thead>
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th style="color:#0891b2;">T.Score</th>
                                                        <th style="color:#0891b2;">T.Grade</th>
                                                        <th style="color:var(--cb-navy);">C.Score</th>
                                                        <th style="color:var(--cb-navy);">C.Grade</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($analytics['grades'] ?? [] as $g)
                                                        @php
                                                            $tg = strtolower(str_replace([' ','-'], '', $g['term_grade']));
                                                            $cg = strtolower(str_replace([' ','-'], '', $g['cum_grade']));
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $g['subject'] }}</td>
                                                            <td class="{{ $g['term_score'] < 50 ? 'score-red' : '' }}">{{ $g['term_score'] ?: '—' }}</td>
                                                            <td><span class="grade-badge g-{{ $tg }}">{{ $g['term_grade'] }}</span></td>
                                                            <td class="{{ $g['cum_score'] < 50 ? 'score-red' : '' }}">{{ $g['cum_score'] ?: '—' }}</td>
                                                            <td><span class="grade-badge g-{{ $cg }}">{{ $g['cum_grade'] }}</span></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div class="gdb-summary">
                                                <div class="gdb-sum-item">
                                                    <div class="gdb-sum-lbl">Term Total</div>
                                                    <div class="gdb-sum-val {{ ($analytics['term_total'] ?? 0) < 50 ? 'score-red' : '' }}">
                                                        {{ $analytics['term_total'] ?? 0 }}
                                                    </div>
                                                </div>
                                                <div class="gdb-sum-item">
                                                    <div class="gdb-sum-lbl">Cum Total</div>
                                                    <div class="gdb-sum-val">{{ $analytics['cum_total'] ?? 0 }}</div>
                                                </div>
                                                <div class="gdb-sum-item">
                                                    <div class="gdb-sum-lbl">Obtainable</div>
                                                    <div class="gdb-sum-val">{{ $analytics['total_obtainable'] ?? 0 }}</div>
                                                </div>
                                                <div class="gdb-sum-item">
                                                    <div class="gdb-sum-lbl">Cum %</div>
                                                    <div class="gdb-sum-val {{ ($analytics['cum_percentage'] ?? 0) < 50 ? 'score-red' : 'score-green' }}">
                                                        {{ $analytics['cum_percentage'] ?? 0 }}%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ══════════════════════════════════════════════════════════
                 MOBILE CARDS
            ══════════════════════════════════════════════════════════ --}}
            <div class="mobile-cards" style="padding:16px;">
                @foreach ($students as $index => $student)
                    @php
                        $sid       = $student->id;
                        $initials  = strtoupper(substr($student->fname, 0, 1) . substr($student->lastname, 0, 1));
                        $hasPic    = !empty($student->picture) && $student->picture !== 'unnamed.jpg';
                        $imgUrl    = $hasPic ? asset('storage/student_avatars/' . basename($student->picture)) : null;
                        $fullName  = trim($student->lastname . ' ' . $student->fname . ' ' . ($student->othername ?? ''));
                        $profile   = $personalityProfiles->where('studentid', $sid)->first();
                        $analytics = $studentAnalytics[$sid] ?? [];
                    @endphp
                    <div class="cb-student-card student-row" data-student-id="{{ $sid }}"
                         data-search="{{ strtolower($fullName . ' ' . $student->admissionNo) }}">
                        <div class="card-top">
                            @if($imgUrl)
                                <div class="avatar-wrap" style="width:48px;height:48px;">
                                    <img src="{{ $imgUrl }}" alt="{{ $fullName }}"
                                         onerror="this.parentElement.innerHTML='<div class=\'avatar-initials\' style=\'width:48px;height:48px;font-size:18px;\'>{{ $initials }}</div>'">
                                </div>
                            @else
                                <div class="avatar-initials" style="width:48px;height:48px;font-size:18px;">{{ $initials }}</div>
                            @endif
                            <div>
                                <div style="font-weight:700;font-size:14px;color:var(--cb-navy);">{{ $fullName }}</div>
                                <div style="font-size:11px;color:var(--cb-muted);">
                                    {{ $student->admissionNo }} · {{ $student->gender ?? '' }}
                                </div>
                                @if($profile && $profile->classteachercomment)
                                    <span class="badge" style="background:#dcfce7;color:#15803d;font-size:10px;margin-top:3px;">✓ Comment saved</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body-pad">
                            {{-- Performance strip --}}
                            <div class="performance-strip">
                                <div style="font-size:11px;font-weight:700;opacity:.8;margin-bottom:4px;">
                                    <i class="ri-bar-chart-line me-1"></i>Performance Summary
                                </div>
                                <div class="ps-grid">
                                    <div class="ps-item">
                                        <div class="ps-lbl">Term Avg</div>
                                        <div class="ps-val">{{ $analytics['term_average'] ?? 0 }}</div>
                                    </div>
                                    <div class="ps-item">
                                        <div class="ps-lbl">Cum Avg</div>
                                        <div class="ps-val">{{ $analytics['cum_average'] ?? 0 }}</div>
                                    </div>
                                    <div class="ps-item">
                                        <div class="ps-lbl">Cum %</div>
                                        <div class="ps-val">{{ $analytics['cum_percentage'] ?? 0 }}%</div>
                                    </div>
                                </div>
                                <div style="margin-top:8px;font-size:10px;opacity:.8;">
                                    Obtainable: <strong>{{ $analytics['total_obtainable'] ?? 0 }}</strong> &nbsp;|&nbsp;
                                    Term Total: <strong>{{ $analytics['term_total'] ?? 0 }}</strong> &nbsp;|&nbsp;
                                    Cum Total: <strong>{{ $analytics['cum_total'] ?? 0 }}</strong>
                                </div>
                            </div>

                            {{-- Subject scores scroll --}}
                            <div class="subjects-scroll">
                                @foreach ($subjects as $subject)
                                    @php
                                        $termScore = $termScoreMap[$sid][$subject->subject] ?? 0;
                                        $cumScore  = $cumScoreMap[$sid][$subject->subject]  ?? 0;
                                    @endphp
                                    <div class="subj-chip">
                                        <div class="sc-name">{{ Str::limit($subject->subject, 10) }}</div>
                                        <div class="sc-term {{ $termScore < 50 ? 'score-red' : 'score-green' }}">T: {{ $termScore ?: '—' }}</div>
                                        <div class="sc-cum {{ $cumScore < 50 ? 'score-red' : 'score-green' }}">C: {{ $cumScore ?: '—' }}</div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Comment fields --}}
                            <div class="comment-field-group">
                                <label>Teacher's Comment <span style="color:var(--cb-rose);">★ Required</span></label>
                                <input type="text"
                                       class="cb-input required-input teacher-comment"
                                       name="teacher_comments[{{ $sid }}]"
                                       data-student-id="{{ $sid }}"
                                       value="{{ $profile ? $profile->classteachercomment : '' }}"
                                       placeholder="Enter teacher comment…">
                            </div>
                            <div class="comment-field-group col-guidance-mobile">
                                <label>Counselor's Comment</label>
                                <input type="text" class="cb-input"
                                       name="guidance_comments[{{ $sid }}]"
                                       value="{{ $profile ? $profile->guidancescomment : '' }}"
                                       placeholder="Optional…">
                            </div>
                            <div class="comment-field-group col-activities-mobile">
                                <label>Remark on Activities</label>
                                <input type="text" class="cb-input"
                                       name="remarks_on_other_activities[{{ $sid }}]"
                                       value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                       placeholder="Optional…">
                            </div>
                            <div class="comment-field-group col-absence-mobile">
                                <label>Times Absent</label>
                                <input type="number" class="cb-input"
                                       name="no_of_times_school_absent[{{ $sid }}]"
                                       value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                       min="0" placeholder="0" style="max-width:100px;">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── Floating Save Bar ── --}}
            <div class="save-bar">
                <div class="sig-group">
                    <label><i class="ri-pen-nib-line me-1"></i>Upload Signature (optional)</label>
                    <div class="file-input-wrap">
                        <input type="file" name="signature" id="signatureFile" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
                <div class="d-flex align-items-center gap-12">
                    <span id="savingIndicator" style="display:none;color:rgba(255,255,255,.7);font-size:13px;">
                        <i class="ri-loader-4-line" id="spinIcon" style="animation:spin 1s linear infinite;"></i>
                        Saving…
                    </span>
                    <button type="button" class="btn-save-all" id="saveBtn">
                        <i class="ri-save-3-line"></i> Save All Changes
                    </button>
                </div>
            </div>

        </form>
    </div>

    @else
        <div class="cb-card" style="padding:60px;text-align:center;">
            <i class="ri-information-line" style="font-size:56px;color:var(--cb-border);display:block;margin-bottom:16px;"></i>
            <h5 style="color:var(--cb-navy);">No Students Found</h5>
            <p style="color:var(--cb-muted);">No students are enrolled in this class for the selected session and term.</p>
        </div>
    @endif

</div><!-- /container-fluid -->
</div><!-- /page-content -->
</div><!-- /main-content -->

{{-- ── Image Zoom Modal ── --}}
<div class="modal fade img-zoom-modal" id="imgZoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:30px;">
                <button type="button" class="zoom-close" data-bs-dismiss="modal">&times;</button>
                <img id="zoomedImg" src="" alt="Student" class="zoomed-img">
                <p id="zoomedName" style="color:#fff;font-weight:700;font-size:16px;margin-top:12px;text-shadow:0 1px 4px rgba(0,0,0,.3);"></p>
            </div>
        </div>
    </div>
</div>

{{-- ── Grade Detail Overlay Backdrop ── --}}
<div id="gdbBackdrop" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.35);" onclick="closeAllGDB()"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Student analytics data ────────────────────────────────────────────────────
const studentAnalytics = @json($studentAnalytics);

// ══════════════════════════════════════════════════════════════════════════════
// COLUMN TOGGLE
// ══════════════════════════════════════════════════════════════════════════════
document.querySelectorAll('.toggle-chip').forEach(chip => {
    chip.addEventListener('click', function () {
        const col = this.dataset.col;
        const isActive = this.classList.toggle('active');

        // Desktop table cells
        document.querySelectorAll(`.${col}`).forEach(el => {
            el.style.display = isActive ? '' : 'none';
        });

        // Mobile card sections (guidance / activities / absence)
        if (col === 'col-guidance')    document.querySelectorAll('.col-guidance-mobile').forEach(el => el.style.display = isActive ? '' : 'none');
        if (col === 'col-activities')  document.querySelectorAll('.col-activities-mobile').forEach(el => el.style.display = isActive ? '' : 'none');
        if (col === 'col-absence')     document.querySelectorAll('.col-absence-mobile').forEach(el => el.style.display = isActive ? '' : 'none');
    });
});

// ══════════════════════════════════════════════════════════════════════════════
// SEARCH
// ══════════════════════════════════════════════════════════════════════════════
document.getElementById('searchInput').addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.student-row').forEach(row => {
        const txt = row.dataset.search || '';
        row.style.display = (!term || txt.includes(term)) ? '' : 'none';
    });
});

// ══════════════════════════════════════════════════════════════════════════════
// GRADE DETAIL POPUP
// ══════════════════════════════════════════════════════════════════════════════
let activeGDB = null;

function closeAllGDB() {
    if (activeGDB) {
        activeGDB.classList.remove('show');
        activeGDB = null;
    }
    document.getElementById('gdbBackdrop').style.display = 'none';
}

document.querySelectorAll('.grade-trigger').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const sid = this.dataset.studentId;
        const box = document.getElementById('gdb-' + sid);
        if (!box) return;

        if (activeGDB && activeGDB === box) {
            closeAllGDB();
            return;
        }

        closeAllGDB();

        // Position the box near the click
        const rect = this.getBoundingClientRect();
        const viewH = window.innerHeight;
        const boxH  = 380; // approx
        let top = rect.bottom + 8 + window.scrollY;
        if (rect.bottom + boxH > viewH) {
            top = rect.top - boxH - 8 + window.scrollY;
        }
        let left = rect.left + window.scrollX - 160;
        if (left < 10) left = 10;
        if (left + 340 > window.innerWidth) left = window.innerWidth - 350;

        box.style.top  = top + 'px';
        box.style.left = left + 'px';

        box.classList.add('show');
        activeGDB = box;
        document.getElementById('gdbBackdrop').style.display = 'block';
    });
});

document.querySelectorAll('.gdb-close-btn').forEach(btn => {
    btn.addEventListener('click', closeAllGDB);
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllGDB(); });

// ══════════════════════════════════════════════════════════════════════════════
// IMAGE ZOOM MODAL
// ══════════════════════════════════════════════════════════════════════════════
document.querySelectorAll('[data-bs-target="#imgZoomModal"]').forEach(el => {
    el.addEventListener('click', function () {
        const img  = this.dataset.image;
        const name = this.dataset.name || this.dataset.initials || 'Student';
        document.getElementById('zoomedImg').src   = img || '';
        document.getElementById('zoomedName').textContent = name;
    });
});

// ══════════════════════════════════════════════════════════════════════════════
// STAT CARDS
// ══════════════════════════════════════════════════════════════════════════════
(function () {
    const data = Object.values(studentAnalytics);
    if (!data.length) return;

    const avgCumPct = Math.round(data.reduce((s, d) => s + (d.cum_percentage || 0), 0) / data.length);
    document.getElementById('statPassRate').textContent = avgCumPct + '%';

    // Top performer by cum_average
    let topName = '—';
    let topAvg  = -1;
    document.querySelectorAll('.student-row[data-student-id]').forEach(row => {
        const sid = row.dataset.studentId;
        if (!sid || !studentAnalytics[sid]) return;
        const avg = studentAnalytics[sid].cum_average || 0;
        if (avg > topAvg) {
            topAvg  = avg;
            // Get name from the row
            const nameEl = row.querySelector('.student-name-text') || row.querySelector('.card-top div div');
            if (nameEl) {
                const parts = nameEl.textContent.trim().split(' ');
                topName = (parts[0] || '') + (parts[1] ? ' ' + parts[1][0] + '.' : '');
            }
        }
    });
    document.getElementById('statTop').textContent = topName;
})();

// ══════════════════════════════════════════════════════════════════════════════
// TOAST
// ══════════════════════════════════════════════════════════════════════════════
function showToast(message, type) {
    document.querySelectorAll('.cb-toast').forEach(t => t.remove());
    const toast = document.createElement('div');
    toast.className = 'cb-toast cb-toast-' + (type === 'success' ? 'success' : 'error');
    toast.innerHTML = `<i class="ri-${type === 'success' ? 'checkbox-circle-fill' : 'error-warning-fill'}" style="font-size:18px;"></i> ${escHtml(message)}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

// ══════════════════════════════════════════════════════════════════════════════
// VALIDATION
// ══════════════════════════════════════════════════════════════════════════════
function validateForm() {
    let valid = true;
    let firstEmpty = null;

    document.querySelectorAll('.teacher-comment').forEach(input => {
        const row = input.closest('.student-row');
        if (!row || row.style.display === 'none') return; // skip hidden rows

        const val = input.value.trim();
        if (!val) {
            input.classList.add('empty-required');
            if (!firstEmpty) firstEmpty = input;
            valid = false;
        } else {
            input.classList.remove('empty-required');
        }
    });

    if (!valid && firstEmpty) {
        firstEmpty.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstEmpty.focus();
        showToast('Please fill in the Teacher\'s Comment for all visible students.', 'error');
    }

    return valid;
}

// Live validation on change
document.querySelectorAll('.teacher-comment').forEach(input => {
    input.addEventListener('input', function () {
        this.classList.toggle('empty-required', !this.value.trim());
    });
});

// ══════════════════════════════════════════════════════════════════════════════
// SAVE  (AJAX — JSON)
// ══════════════════════════════════════════════════════════════════════════════
document.getElementById('saveBtn').addEventListener('click', async function () {
    if (!validateForm()) return;

    const btn = this;
    const ind = document.getElementById('savingIndicator');
    const orig = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line" style="animation:spin 1s linear infinite;"></i> Saving…';
    ind.style.display = 'inline-flex';

    // Build FormData from all named inputs inside the form
    const form = document.getElementById('commentsForm');
    const fd   = new FormData(form);

    // Append signature file if selected
    const sigFile = document.getElementById('signatureFile');
    if (sigFile && sigFile.files[0]) {
        fd.set('signature', sigFile.files[0]);
    }

    try {
        const res  = await fetch('{{ route("classbroadsheet.updateComments", [$schoolclassid, $sessionid, $termid]) }}', {
            method:  'POST',
            headers: {
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });

        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');

            // Mark teacher comment fields as saved
            document.querySelectorAll('.teacher-comment').forEach(inp => {
                if (inp.value.trim()) inp.classList.remove('empty-required');
            });
        } else {
            showToast(data.message || 'Save failed.', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
        ind.style.display = 'none';
    }
});

// ── Spin keyframe (injected once) ────────────────────────────────────────────
const style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
@endpush

@endsection
