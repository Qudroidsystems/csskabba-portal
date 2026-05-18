@extends('layouts.master')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
:root {
    --cb-navy:      #0f2342;
    --cb-teal:      #0d9488;
    --cb-sky:       #0ea5e9;
    --cb-amber:     #f59e0b;
    --cb-rose:      #f43f5e;
    --cb-green:     #22c55e;
    --cb-muted:     #64748b;
    --cb-border:    #e2e8f0;
    --cb-surface:   #f8fafc;
    --cb-white:     #ffffff;
    --cb-radius:    14px;
    --cb-shadow:    0 4px 16px rgba(15,35,66,.10);
    --cb-shadow-lg: 0 8px 32px rgba(15,35,66,.14);
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; }

/* Hero */
.cb-hero { background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%); border-radius: var(--cb-radius); padding: 32px 36px; margin-bottom: 28px; position: relative; overflow: hidden; }
.cb-hero::before { content: ''; position: absolute; top: -80px; right: -80px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%); border-radius: 50%; }
.cb-hero h1 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #fff; margin: 0 0 8px; }
.cb-hero p { font-size: 13px; color: rgba(255,255,255,.72); margin: 0; }
.cb-hero .meta-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
.cb-meta-pill { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; color: #fff; display: inline-flex; align-items: center; gap: 5px; }

/* Stat cards */
.cb-stat { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); padding: 20px 22px; position: relative; overflow: hidden; transition: transform .15s, box-shadow .15s; }
.cb-stat:hover { transform: translateY(-2px); box-shadow: var(--cb-shadow); }
.cb-stat .stat-accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--cb-radius) var(--cb-radius) 0 0; }
.cb-stat .stat-value { font-size: 30px; font-weight: 700; color: var(--cb-navy); line-height: 1; margin-top: 8px; }
.cb-stat .stat-label { font-size: 12px; color: var(--cb-muted); margin-top: 5px; font-weight: 500; }
.cb-stat .stat-ico { font-size: 36px; opacity: .08; position: absolute; right: 16px; top: 50%; transform: translateY(-50%); }

/* Column Toggle */
.col-toggle-panel { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); padding: 18px 22px; margin-bottom: 22px; box-shadow: var(--cb-shadow); }
.col-toggle-panel h6 { font-size: 13px; font-weight: 700; color: var(--cb-navy); margin: 0 0 14px; display: flex; align-items: center; gap: 7px; }
.toggle-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.toggle-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--cb-border); background: var(--cb-surface); color: var(--cb-muted); transition: all .18s ease; user-select: none; }
.toggle-chip:hover { border-color: var(--cb-teal); color: var(--cb-teal); }
.toggle-chip.active { background: var(--cb-teal); border-color: var(--cb-teal); color: #fff; box-shadow: 0 2px 8px rgba(13,148,136,.3); }

/* Main card */
.cb-card { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); box-shadow: var(--cb-shadow); overflow: hidden; }
.cb-card-header { padding: 18px 24px; border-bottom: 1px solid var(--cb-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(to right, #f8fafc, #f0fdf9); }
.cb-card-header h5 { font-size: 15px; font-weight: 700; color: var(--cb-navy); margin: 0; display: flex; align-items: center; gap: 8px; }

/* Table */
.cb-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.cb-table thead th { background: var(--cb-navy); color: #fff; padding: 11px 14px; font-weight: 600; font-size: 11.5px; white-space: nowrap; text-align: center; border-right: 1px solid rgba(255,255,255,.08); }
.cb-table thead th.col-name-hdr { text-align: left; }
.cb-table tbody td { padding: 10px 14px; vertical-align: middle; border-bottom: 1px solid var(--cb-border); text-align: center; }
.cb-table tbody td.td-name { text-align: left; }
.cb-table tbody tr:hover td { background: #f0fdf9; }
.cb-table tbody tr:last-child td { border-bottom: none; }

/* Score dual rows */
.score-dual { display: flex; flex-direction: column; gap: 2px; min-width: 80px; }
.score-row { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 2px 5px; border-radius: 5px; font-size: 11px; font-weight: 700; }
.score-row-term { background: rgba(14,165,233,.08); border-left: 2.5px solid #0ea5e9; }
.score-row-cum  { background: rgba(15,35,66,.06);   border-left: 2.5px solid var(--cb-navy); }
.score-lbl { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; opacity: .7; }

/* Grade badges */
.grade-badge { display: inline-block; padding: 1px 5px; border-radius: 8px; font-size: 9px; font-weight: 700; }
.g-a,.g-a1             { background: #dcfce7; color: #15803d; }
.g-b,.g-b2,.g-b3       { background: #dbeafe; color: #1d4ed8; }
.g-c,.g-c4,.g-c5,.g-c6 { background: #fef9c3; color: #a16207; }
.g-d,.g-d7             { background: #ffedd5; color: #c2410c; }
.g-e,.g-e8             { background: #ffe4e6; color: #be123c; }
.g-f,.g-f9             { background: #fee2e2; color: #b91c1c; }
.score-red   { color: #dc2626 !important; }
.score-amber { color: #d97706 !important; }
.score-green { color: #16a34a !important; }

/* Analytics cell */
.analytics-cell { min-width: 130px; font-size: 11px; line-height: 1.4; }
.analytics-row  { display: flex; justify-content: space-between; align-items: center; padding: 2px 0; gap: 6px; }
.analytics-lbl  { color: var(--cb-muted); font-size: 10px; font-weight: 500; }
.analytics-val  { font-weight: 700; color: var(--cb-navy); font-size: 11.5px; }
.pct-bar-wrap   { background: #e2e8f0; border-radius: 4px; height: 5px; margin-top: 3px; overflow: hidden; }
.pct-bar        { height: 100%; border-radius: 4px; }
.grade-trigger-btn { background: none; border: none; cursor: pointer; color: var(--cb-sky); font-size: 15px; padding: 2px 5px; border-radius: 5px; transition: color .15s, background .15s; }
.grade-trigger-btn:hover { color: var(--cb-teal); background: rgba(13,148,136,.08); }

/* Inputs */
.cb-input { border: 1.5px solid var(--cb-border); border-radius: 8px; padding: 6px 10px; font-size: 12px; width: 100%; transition: border .15s, box-shadow .15s; background: var(--cb-surface); font-family: 'DM Sans', sans-serif; }
.cb-input:focus { border-color: var(--cb-teal); outline: none; box-shadow: 0 0 0 3px rgba(13,148,136,.12); background: #fff; }
.cb-input.required-field { border-left: 3px solid var(--cb-teal); }
.cb-input.field-error    { border-color: var(--cb-rose) !important; border-left-color: var(--cb-rose) !important; background: #fff1f2; }
.absence-input { width: 72px !important; text-align: center; }

/* Avatar */
.student-name-cell { display: flex; align-items: center; gap: 9px; }
.cb-avatar { width: 38px; height: 38px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid var(--cb-border); cursor: pointer; transition: border-color .15s, transform .15s; display: flex; align-items: center; justify-content: center; }
.cb-avatar:hover { border-color: var(--cb-teal); transform: scale(1.08); }
.cb-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cb-avatar-initials { background: linear-gradient(135deg, var(--cb-teal), var(--cb-sky)); color: #fff; font-size: 13px; font-weight: 700; }
.student-name-text { font-weight: 600; font-size: 12.5px; color: var(--cb-navy); }
.student-adm { font-size: 10.5px; color: var(--cb-muted); margin-top: 1px; }

/* Save bar */
.save-bar { position: sticky; bottom: 0; z-index: 100; background: rgba(15,35,66,.97); backdrop-filter: blur(10px); border-top: 2px solid var(--cb-teal); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-radius: 0 0 var(--cb-radius) var(--cb-radius); }
.save-bar label { color: rgba(255,255,255,.7); font-size: 12px; font-weight: 500; margin-bottom: 0; }
.file-input-styled { padding: 6px 12px; border-radius: 8px; border: 1.5px solid rgba(255,255,255,.2); background: rgba(255,255,255,.08); color: #fff; font-size: 12px; cursor: pointer; }
.file-input-styled:hover { border-color: var(--cb-teal); }
.btn-save-all { background: var(--cb-teal); color: #fff; border: none; border-radius: 10px; padding: 10px 28px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .18s, transform .12s, box-shadow .18s; display: flex; align-items: center; gap: 8px; font-family: 'DM Sans', sans-serif; }
.btn-save-all:hover { background: #0b7c72; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(13,148,136,.4); }
.btn-save-all:disabled { opacity: .65; transform: none; cursor: not-allowed; }

/* Toast */
.cb-toast { position: fixed; bottom: 80px; right: 24px; min-width: 300px; z-index: 99999; padding: 14px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; box-shadow: var(--cb-shadow-lg); animation: cbSlideUp .3s ease; }
.cb-toast-success { background: #ecfdf5; border: 1.5px solid #86efac; color: #15803d; }
.cb-toast-error   { background: #fff1f2; border: 1.5px solid #fca5a5; color: #be123c; }
@keyframes cbSlideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
@keyframes cbSpin    { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
.spin { display:inline-block; animation: cbSpin 1s linear infinite; }

/* Search */
.cb-search { position: relative; }
.cb-search input { width: 100%; padding: 9px 14px 9px 38px; border: 1.5px solid var(--cb-border); border-radius: 10px; font-size: 13px; background: var(--cb-surface); font-family: 'DM Sans', sans-serif; transition: border .15s; }
.cb-search input:focus { border-color: var(--cb-teal); outline: none; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.cb-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--cb-muted); pointer-events: none; }

/* Mobile cards */
.cb-student-card { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); margin-bottom: 18px; box-shadow: var(--cb-shadow); overflow: hidden; }
.cb-student-card .card-top { background: linear-gradient(135deg, #f8fafc, #f0fdf9); padding: 14px 16px; border-bottom: 1px solid var(--cb-border); display: flex; align-items: center; gap: 12px; }
.cb-student-card .card-body-pad { padding: 16px; }
.performance-strip { background: linear-gradient(135deg, var(--cb-navy), #1e5f74); border-radius: 10px; padding: 12px 16px; color: #fff; margin-bottom: 14px; }
.ps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px; }
.ps-item { text-align: center; background: rgba(255,255,255,.1); border-radius: 8px; padding: 8px; }
.ps-lbl  { font-size: 9px; opacity: .8; text-transform: uppercase; letter-spacing: .4px; }
.ps-val  { font-size: 16px; font-weight: 700; }
.subjects-scroll { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 14px; }
.subjects-scroll::-webkit-scrollbar { height: 3px; }
.subjects-scroll::-webkit-scrollbar-thumb { background: var(--cb-border); border-radius: 2px; }
.subj-chip { flex-shrink: 0; text-align: center; border: 1px solid var(--cb-border); border-radius: 10px; padding: 8px 10px; min-width: 80px; background: var(--cb-surface); }
.subj-chip .sc-name { font-size: 9.5px; font-weight: 600; color: var(--cb-muted); margin-bottom: 4px; line-height: 1.2; }
.subj-chip .sc-t { font-size: 10px; font-weight: 700; border-radius: 4px; padding: 1px 4px; margin-bottom: 2px; background: rgba(14,165,233,.1); color: #0369a1; }
.subj-chip .sc-c { font-size: 10px; font-weight: 700; border-radius: 4px; padding: 1px 4px; background: rgba(15,35,66,.07); color: var(--cb-navy); }
.comment-field-group { margin-bottom: 10px; }
.comment-field-group label { font-size: 11px; font-weight: 600; color: var(--cb-muted); margin-bottom: 4px; display: block; }

/* Responsive */
@media (max-width: 1199px) { .desktop-only { display: none !important; } }
@media (min-width: 1200px) { .mobile-only  { display: none !important; } }

/* Image zoom modal */
#cbImgZoomModal .modal-content { background: transparent; border: none; box-shadow: none; }
#cbImgZoomModal .modal-dialog  { max-width: 92vw; }
#cbImgZoomModal .modal-body    { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 75vh; padding: 20px; }
.cb-zoomed-img { max-width: 88vw; max-height: 70vh; border-radius: 16px; border: 4px solid #fff; box-shadow: 0 24px 60px rgba(0,0,0,.4); object-fit: contain; animation: cbZoomIn .3s ease; }
@keyframes cbZoomIn { from { opacity:0; transform:scale(.82); } to { opacity:1; transform:scale(1); } }
.cb-zoom-close { position: absolute; top: 16px; right: 22px; background: rgba(0,0,0,.7); border: none; color: #fff; border-radius: 50%; width: 36px; height: 36px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: background .15s; }
.cb-zoom-close:hover { background: rgba(0,0,0,.9); }
.cb-zoom-name { color: #fff; margin-top: 16px; font-size: 17px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,.4); background: rgba(0,0,0,.45); padding: 7px 20px; border-radius: 40px; }
.cb-zoom-meta { color: rgba(255,255,255,.8); margin-top: 6px; font-size: 13px; }

/* Grade breakdown popup */
#cbGradePopup { display: none; position: fixed; z-index: 9999; background: var(--cb-white); border: 2px solid var(--cb-teal); border-radius: 14px; box-shadow: var(--cb-shadow-lg); width: 360px; max-height: 490px; overflow: hidden; }
#cbGradePopup.is-open { display: block; }
.gpop-hdr { background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal)); color: #fff; padding: 11px 16px; border-radius: 12px 12px 0 0; font-weight: 700; font-size: 13px; display: flex; justify-content: space-between; align-items: center; }
.gpop-close-btn { background: rgba(255,255,255,.18); border: none; color: #fff; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: background .15s; }
.gpop-close-btn:hover { background: rgba(255,255,255,.35); }
.gpop-body { padding: 14px; max-height: 400px; overflow-y: auto; }
.gpop-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
.gpop-table th { background: var(--cb-surface); color: var(--cb-muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: 6px 8px; border-bottom: 1px solid var(--cb-border); text-align: center; }
.gpop-table th:first-child { text-align: left; }
.gpop-table td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-weight: 600; text-align: center; vertical-align: middle; }
.gpop-table td:first-child { text-align: left; font-size: 11px; color: var(--cb-navy); }
.gpop-summary { background: var(--cb-surface); border-radius: 8px; padding: 10px 12px; margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.gpop-sum-item { text-align: center; }
.gpop-sum-lbl  { font-size: 9px; color: var(--cb-muted); text-transform: uppercase; letter-spacing: .4px; }
.gpop-sum-val  { font-size: 16px; font-weight: 700; color: var(--cb-navy); }

/* Popup backdrop */
#cbPopupBackdrop { display: none; position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,.28); }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

{{-- Hero --}}
<div class="cb-hero">
    <h1><i class="ri-clipboard-line me-2"></i>Class Broadsheet</h1>
    <p>Review student performance, assign comments, and track attendance for your class.</p>
    <div class="meta-pills">
        <span class="cb-meta-pill"><i class="ri-building-line"></i>{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-line"></i>{{ $schoolterm }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-event-line"></i>{{ $schoolsession }}</span>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
            <div class="stat-ico"><i class="ri-group-line"></i></div>
            <div class="stat-value">{{ $students->count() }}</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-book-open-line"></i></div>
            <div class="stat-value text-info">{{ $subjects->count() }}</div>
            <div class="stat-label">Subjects</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-green),#4ade80);"></div>
            <div class="stat-ico"><i class="ri-percent-line"></i></div>
            <div class="stat-value text-success" id="statPassRate">—</div>
            <div class="stat-label">Avg Cum %</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-amber),#fcd34d);"></div>
            <div class="stat-ico"><i class="ri-award-line"></i></div>
            <div class="stat-value text-warning" id="statTop">—</div>
            <div class="stat-label">Top Performer</div>
        </div>
    </div>
</div>

{{-- Column Toggle --}}
<div class="col-toggle-panel">
    <h6><i class="ri-layout-column-line" style="color:var(--cb-teal)"></i> Show / Hide Columns</h6>
    <div class="toggle-chips">
        <span class="toggle-chip active" data-colkey="scores">
            <i class="ri-bar-chart-line"></i> Subject Scores
        </span>
        <span class="toggle-chip active" data-colkey="summary">
            <i class="ri-pie-chart-line"></i> Summary
        </span>
        <span class="toggle-chip active" data-colkey="teacher">
            <i class="ri-chat-3-line"></i> Teacher's Comment <sup style="color:var(--cb-rose);font-size:9px;">★</sup>
        </span>
        <span class="toggle-chip active" data-colkey="guidance">
            <i class="ri-mental-health-line"></i> Counselor's Comment
        </span>
        <span class="toggle-chip active" data-colkey="activities">
            <i class="ri-football-line"></i> Remark on Activities
        </span>
        <span class="toggle-chip active" data-colkey="absence">
            <i class="ri-calendar-close-line"></i> Absences
        </span>
    </div>
    <p class="mt-2 mb-0" style="font-size:11px;color:var(--cb-muted);">
        <i class="ri-information-line me-1"></i>
        <strong style="color:var(--cb-rose);">★</strong> Teacher's Comment is required. All other columns are optional.
    </p>
</div>

@if ($students->isNotEmpty())

{{-- Pass analytics to JS once, avoiding @push issues --}}
@php $cbAnalyticsJson = json_encode($studentAnalytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); @endphp

{{-- Grade popup (OUTSIDE the table so position:fixed works) --}}
<div id="cbGradePopup">
    <div class="gpop-hdr">
        <span id="gpopTitle"><i class="ri-bar-chart-line me-1"></i>Grade Breakdown</span>
        <button type="button" class="gpop-close-btn" id="gpopCloseBtn">&times;</button>
    </div>
    <div class="gpop-body" id="gpopBody"></div>
</div>
<div id="cbPopupBackdrop"></div>

<div class="cb-card">
    <div class="cb-card-header">
        <h5>
            <i class="ri-table-alt-line" style="color:var(--cb-teal)"></i>
            Student Performance &amp; Comments
            <span class="badge" style="background:var(--cb-teal);color:#fff;font-size:11px;">{{ $students->count() }} Students</span>
        </h5>
        <div class="cb-search" style="max-width:260px;">
            <i class="ri-search-line"></i>
            <input type="text" id="searchInput" placeholder="Search students…">
        </div>
    </div>

    <form id="commentsForm">
        @csrf

        {{-- DESKTOP TABLE --}}
        <div class="desktop-only" style="overflow-x:auto;">
            <table class="cb-table">
                <thead>
                    <tr>
                        <th style="width:34px;">#</th>
                        <th class="col-name-hdr" style="min-width:190px;">Student</th>
                        @foreach ($subjects as $subject)
                            <th class="cbcol-scores" style="min-width:86px;">{{ $subject->subject }}</th>
                        @endforeach
                        <th class="cbcol-summary" style="min-width:140px;">Summary</th>
                        <th class="cbcol-teacher" style="min-width:180px;">Teacher's Comment <sup style="color:var(--cb-rose);">★</sup></th>
                        <th class="cbcol-guidance" style="min-width:160px;">Counselor's Comment</th>
                        <th class="cbcol-activities" style="min-width:160px;">Remark on Activities</th>
                        <th class="cbcol-absence" style="min-width:80px;">Absent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $index => $student)
                        @php
                            $sid      = $student->id;
                            $initials = strtoupper(substr($student->fname ?? '', 0, 1) . substr($student->lastname ?? '', 0, 1)) ?: 'ST';
                            $hasPic   = !empty($student->picture) && $student->picture !== 'unnamed.jpg';
                            $imgUrl   = $hasPic ? asset('storage/student_avatars/' . basename($student->picture)) : null;
                            $fullName = trim(($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
                            $profile  = $personalityProfiles->where('studentid', $sid)->first();
                            $an       = $studentAnalytics[$sid] ?? [];
                        @endphp
                        <tr class="cb-student-row"
                            data-student-id="{{ $sid }}"
                            data-searchkey="{{ strtolower($fullName . ' ' . $student->admissionNo) }}">

                            <td>{{ $index + 1 }}</td>

                            <td class="td-name">
                                <div class="student-name-cell">
                                    @if($imgUrl)
                                        <div class="cb-avatar cb-avatar-trigger"
                                             data-img="{{ $imgUrl }}"
                                             data-name="{{ $fullName }}"
                                             data-adm="{{ $student->admissionNo }}"
                                             data-class="{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : '' }}"
                                             data-gender="{{ $student->gender ?? '' }}">
                                            <img src="{{ $imgUrl }}" alt="{{ $fullName }}"
                                                 onerror="this.closest('.cb-avatar').classList.add('cb-avatar-initials'); this.remove(); this.closest('.cb-avatar').textContent='{{ $initials }}'">
                                        </div>
                                    @else
                                        <div class="cb-avatar cb-avatar-initials cb-avatar-trigger"
                                             data-img=""
                                             data-name="{{ $fullName }}"
                                             data-adm="{{ $student->admissionNo }}"
                                             data-class="{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : '' }}"
                                             data-gender="{{ $student->gender ?? '' }}"
                                             data-initials="{{ $initials }}">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="student-name-text">{{ $fullName }}</div>
                                        <div class="student-adm">{{ $student->admissionNo }} · {{ $student->gender ?? '' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Subject scores (grade computed inline — no controller call) --}}
                            @foreach ($subjects as $subject)
                                @php
                                    $tScore = $termScoreMap[$sid][$subject->subject] ?? 0;
                                    $cScore = $cumScoreMap[$sid][$subject->subject]  ?? 0;
                                    $tGrade = $cGrade = '-';
                                    if ($isSenior) {
                                        if ($tScore >= 75) $tGrade='A1'; elseif ($tScore >= 70) $tGrade='B2';
                                        elseif ($tScore >= 65) $tGrade='B3'; elseif ($tScore >= 60) $tGrade='C4';
                                        elseif ($tScore >= 55) $tGrade='C5'; elseif ($tScore >= 50) $tGrade='C6';
                                        elseif ($tScore >= 45) $tGrade='D7'; elseif ($tScore >= 40) $tGrade='E8';
                                        elseif ($tScore > 0)  $tGrade='F9';
                                        if ($cScore >= 75) $cGrade='A1'; elseif ($cScore >= 70) $cGrade='B2';
                                        elseif ($cScore >= 65) $cGrade='B3'; elseif ($cScore >= 60) $cGrade='C4';
                                        elseif ($cScore >= 55) $cGrade='C5'; elseif ($cScore >= 50) $cGrade='C6';
                                        elseif ($cScore >= 45) $cGrade='D7'; elseif ($cScore >= 40) $cGrade='E8';
                                        elseif ($cScore > 0)  $cGrade='F9';
                                    } else {
                                        if ($tScore >= 70) $tGrade='A'; elseif ($tScore >= 60) $tGrade='B';
                                        elseif ($tScore >= 50) $tGrade='C'; elseif ($tScore >= 40) $tGrade='D';
                                        elseif ($tScore > 0)  $tGrade='F';
                                        if ($cScore >= 70) $cGrade='A'; elseif ($cScore >= 60) $cGrade='B';
                                        elseif ($cScore >= 50) $cGrade='C'; elseif ($cScore >= 40) $cGrade='D';
                                        elseif ($cScore > 0)  $cGrade='F';
                                    }
                                    $tGL = strtolower($tGrade); $cGL = strtolower($cGrade);
                                    $tC  = $tScore < 40 ? 'score-red' : ($tScore < 50 ? 'score-amber' : 'score-green');
                                    $cC  = $cScore < 40 ? 'score-red' : ($cScore < 50 ? 'score-amber' : 'score-green');
                                @endphp
                                <td class="cbcol-scores">
                                    <div class="score-dual">
                                        <div class="score-row score-row-term">
                                            <span class="score-lbl" style="color:#0891b2;">T</span>
                                            <span class="{{ $tC }}">{{ $tScore ?: '—' }}</span>
                                            @if($tGrade !== '-')<span class="grade-badge g-{{ $tGL }}">{{ $tGrade }}</span>@endif
                                        </div>
                                        <div class="score-row score-row-cum">
                                            <span class="score-lbl" style="color:var(--cb-navy);">C</span>
                                            <span class="{{ $cC }}">{{ $cScore ?: '—' }}</span>
                                            @if($cGrade !== '-')<span class="grade-badge g-{{ $cGL }}">{{ $cGrade }}</span>@endif
                                        </div>
                                    </div>
                                </td>
                            @endforeach

                            {{-- Summary --}}
                            <td class="cbcol-summary analytics-cell">
                                <div class="analytics-row">
                                    <span class="analytics-lbl">Term Avg</span>
                                    <span class="analytics-val {{ ($an['term_average']??0)<50?'score-red':'score-green' }}">{{ $an['term_average']??0 }}</span>
                                </div>
                                <div class="analytics-row">
                                    <span class="analytics-lbl">Cum Avg</span>
                                    <span class="analytics-val {{ ($an['cum_average']??0)<50?'score-red':'score-green' }}">{{ $an['cum_average']??0 }}</span>
                                </div>
                                <div class="analytics-row">
                                    <span class="analytics-lbl">Obtainable</span>
                                    <span class="analytics-val">{{ $an['total_obtainable']??0 }}</span>
                                </div>
                                <div class="analytics-row">
                                    <span class="analytics-lbl">Cum %</span>
                                    <span class="analytics-val {{ ($an['cum_percentage']??0)<50?'score-red':'score-green' }}">{{ $an['cum_percentage']??0 }}%</span>
                                </div>
                                <div class="pct-bar-wrap mt-1">
                                    <div class="pct-bar" style="width:{{ $an['cum_percentage']??0 }}%;background:{{ ($an['cum_percentage']??0)>=50?'var(--cb-green)':'var(--cb-rose)' }};"></div>
                                </div>
                                <div class="text-center mt-1">
                                    <button type="button" class="grade-trigger-btn"
                                            data-sid="{{ $sid }}"
                                            data-sname="{{ $fullName }}"
                                            title="View grade breakdown">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                </div>
                            </td>

                            <td class="cbcol-teacher">
                                <input type="text" class="cb-input required-field teacher-comment"
                                       name="teacher_comments[{{ $sid }}]"
                                       value="{{ $profile ? $profile->classteachercomment : '' }}"
                                       placeholder="Required comment…">
                            </td>
                            <td class="cbcol-guidance">
                                <input type="text" class="cb-input"
                                       name="guidance_comments[{{ $sid }}]"
                                       value="{{ $profile ? $profile->guidancescomment : '' }}"
                                       placeholder="Optional…">
                            </td>
                            <td class="cbcol-activities">
                                <input type="text" class="cb-input"
                                       name="remarks_on_other_activities[{{ $sid }}]"
                                       value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                       placeholder="Optional…">
                            </td>
                            <td class="cbcol-absence">
                                <input type="number" class="cb-input absence-input"
                                       name="no_of_times_school_absent[{{ $sid }}]"
                                       value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                       min="0" placeholder="0">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="mobile-only" style="padding:16px;">
            @foreach ($students as $index => $student)
                @php
                    $sid      = $student->id;
                    $initials = strtoupper(substr($student->fname ?? '', 0, 1) . substr($student->lastname ?? '', 0, 1)) ?: 'ST';
                    $hasPic   = !empty($student->picture) && $student->picture !== 'unnamed.jpg';
                    $imgUrl   = $hasPic ? asset('storage/student_avatars/' . basename($student->picture)) : null;
                    $fullName = trim(($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
                    $profile  = $personalityProfiles->where('studentid', $sid)->first();
                    $an       = $studentAnalytics[$sid] ?? [];
                @endphp
                <div class="cb-student-card cb-student-row"
                     data-student-id="{{ $sid }}"
                     data-searchkey="{{ strtolower($fullName . ' ' . $student->admissionNo) }}">
                    <div class="card-top">
                        @if($imgUrl)
                            <div class="cb-avatar cb-avatar-trigger" style="width:48px;height:48px;"
                                 data-img="{{ $imgUrl }}" data-name="{{ $fullName }}"
                                 data-adm="{{ $student->admissionNo }}"
                                 data-class="{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : '' }}"
                                 data-gender="{{ $student->gender ?? '' }}">
                                <img src="{{ $imgUrl }}" alt="{{ $fullName }}"
                                     style="width:100%;height:100%;object-fit:cover;"
                                     onerror="var p=this.closest('.cb-avatar');p.classList.add('cb-avatar-initials');p.textContent='{{ $initials }}'">
                            </div>
                        @else
                            <div class="cb-avatar cb-avatar-initials cb-avatar-trigger"
                                 style="width:48px;height:48px;font-size:16px;"
                                 data-img="" data-name="{{ $fullName }}"
                                 data-adm="{{ $student->admissionNo }}"
                                 data-class="{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : '' }}"
                                 data-gender="{{ $student->gender ?? '' }}"
                                 data-initials="{{ $initials }}">
                                {{ $initials }}
                            </div>
                        @endif
                        <div>
                            <div style="font-weight:700;font-size:14px;color:var(--cb-navy);">{{ $fullName }}</div>
                            <div style="font-size:11px;color:var(--cb-muted);">{{ $student->admissionNo }} · {{ $student->gender ?? '' }}</div>
                            @if($profile && $profile->classteachercomment)
                                <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;display:inline-block;margin-top:3px;">✓ Comment saved</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body-pad">
                        <div class="performance-strip">
                            <div style="font-size:11px;font-weight:700;opacity:.8;margin-bottom:4px;"><i class="ri-bar-chart-line me-1"></i>Performance Summary</div>
                            <div class="ps-grid">
                                <div class="ps-item"><div class="ps-lbl">Term Avg</div><div class="ps-val">{{ $an['term_average']??0 }}</div></div>
                                <div class="ps-item"><div class="ps-lbl">Cum Avg</div><div class="ps-val">{{ $an['cum_average']??0 }}</div></div>
                                <div class="ps-item"><div class="ps-lbl">Cum %</div><div class="ps-val">{{ $an['cum_percentage']??0 }}%</div></div>
                            </div>
                            <div style="margin-top:8px;font-size:10px;opacity:.8;">
                                Obtainable: <strong>{{ $an['total_obtainable']??0 }}</strong> &nbsp;|&nbsp;
                                T Total: <strong>{{ $an['term_total']??0 }}</strong> &nbsp;|&nbsp;
                                C Total: <strong>{{ $an['cum_total']??0 }}</strong>
                            </div>
                        </div>
                        <div class="subjects-scroll">
                            @foreach ($subjects as $subject)
                                @php $tS=$termScoreMap[$sid][$subject->subject]??0; $cS=$cumScoreMap[$sid][$subject->subject]??0; @endphp
                                <div class="subj-chip">
                                    <div class="sc-name">{{ Str::limit($subject->subject, 10) }}</div>
                                    <div class="sc-t {{ $tS<50?'score-red':'score-green' }}">T: {{ $tS?:'—' }}</div>
                                    <div class="sc-c {{ $cS<50?'score-red':'score-green' }}">C: {{ $cS?:'—' }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="comment-field-group">
                            <label>Teacher's Comment <span style="color:var(--cb-rose);">★ Required</span></label>
                            <input type="text" class="cb-input required-field teacher-comment"
                                   name="teacher_comments[{{ $sid }}]"
                                   value="{{ $profile ? $profile->classteachercomment : '' }}"
                                   placeholder="Enter teacher comment…">
                        </div>
                        <div class="comment-field-group mobile-col-guidance">
                            <label>Counselor's Comment</label>
                            <input type="text" class="cb-input" name="guidance_comments[{{ $sid }}]"
                                   value="{{ $profile ? $profile->guidancescomment : '' }}" placeholder="Optional…">
                        </div>
                        <div class="comment-field-group mobile-col-activities">
                            <label>Remark on Activities</label>
                            <input type="text" class="cb-input" name="remarks_on_other_activities[{{ $sid }}]"
                                   value="{{ $profile ? $profile->remark_on_other_activities : '' }}" placeholder="Optional…">
                        </div>
                        <div class="comment-field-group mobile-col-absence">
                            <label>Times Absent</label>
                            <input type="number" class="cb-input" name="no_of_times_school_absent[{{ $sid }}]"
                                   value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                   min="0" placeholder="0" style="max-width:100px;">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Save Bar --}}
        <div class="save-bar">
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <label style="margin:0;"><i class="ri-pen-nib-line me-1"></i>Signature (optional)</label>
                <input type="file" id="signatureFile" accept=".jpg,.jpeg,.png,.pdf" class="file-input-styled">
            </div>
            <div style="display:flex;align-items:center;gap:14px;">
                <span id="savingText" style="display:none;color:rgba(255,255,255,.75);font-size:13px;align-items:center;gap:6px;">
                    <i class="spin ri-loader-4-line"></i> Saving…
                </span>
                <button type="button" id="saveBtn" class="btn-save-all">
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

</div>{{-- /container-fluid --}}
</div>{{-- /page-content --}}
</div>{{-- /main-content --}}

{{-- IMAGE ZOOM MODAL — plain Bootstrap, no data-bs-target binding on triggers to avoid conflicts --}}
<div class="modal fade" id="cbImgZoomModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:transparent;border:none;box-shadow:none;">
            <button type="button" class="cb-zoom-close" data-bs-dismiss="modal">&times;</button>
            <div class="modal-body" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:75vh;padding:20px;">
                <img id="cbZoomedImg" src="" alt="Student Photo" class="cb-zoomed-img" style="cursor:pointer;">
                <div class="cb-zoom-name" id="cbZoomedName"></div>
                <div class="cb-zoom-meta" id="cbZoomedMeta"></div>
            </div>
        </div>
    </div>
</div>

{{--
    ════════════════════════════════════════════════════════
    ALL JAVASCRIPT — inline <script> tag so it runs whether
    or not the layout has @stack('scripts').
    Wrapped in DOMContentLoaded so DOM is guaranteed ready.
    ════════════════════════════════════════════════════════
--}}
<script>
(function () {
    'use strict';

    /* ── PHP → JS ──────────────────────────────────────── */
    var SA       = {!! $cbAnalyticsJson !!};   // studentAnalytics
    var SAVE_URL = '{{ route("classbroadsheet.updateComments", [$schoolclassid, $sessionid, $termid]) }}';
    var CSRF     = document.querySelector('meta[name="csrf-token"]')
                    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    : '{{ csrf_token() }}';

    /* ── Helper: escapeHTML ────────────────────────────── */
    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    /* ── Helper: Toast ─────────────────────────────────── */
    function toast(msg, type) {
        document.querySelectorAll('.cb-toast').forEach(function (t) { t.remove(); });
        var el = document.createElement('div');
        el.className = 'cb-toast cb-toast-' + (type === 'success' ? 'success' : 'error');
        el.innerHTML = '<i class="ri-' + (type === 'success' ? 'checkbox-circle-fill' : 'error-warning-fill') + '" style="font-size:18px;flex-shrink:0;"></i> ' + esc(msg);
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function () {

        /* ══════════════════════════════════════════════════
           1. COLUMN TOGGLE
           Uses prefix "cbcol-" on <th>/<td> elements.
           data-colkey on chips maps to that prefix.
        ══════════════════════════════════════════════════ */
        document.querySelectorAll('.toggle-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var key      = this.getAttribute('data-colkey');
                var isActive = this.classList.toggle('active');
                var show     = isActive ? '' : 'none';

                // Desktop table cells + header
                document.querySelectorAll('.cbcol-' + key).forEach(function (el) {
                    el.style.display = show;
                });

                // Mobile optional sections
                var mobileClass = { guidance: '.mobile-col-guidance', activities: '.mobile-col-activities', absence: '.mobile-col-absence' }[key];
                if (mobileClass) {
                    document.querySelectorAll(mobileClass).forEach(function (el) {
                        el.style.display = show;
                    });
                }
            });
        });

        /* ══════════════════════════════════════════════════
           2. SEARCH
        ══════════════════════════════════════════════════ */
        var searchEl = document.getElementById('searchInput');
        if (searchEl) {
            searchEl.addEventListener('input', function () {
                var q = this.value.toLowerCase().trim();
                document.querySelectorAll('.cb-student-row').forEach(function (row) {
                    var key = (row.getAttribute('data-searchkey') || '').toLowerCase();
                    row.style.display = (!q || key.includes(q)) ? '' : 'none';
                });
            });
        }

        /* ══════════════════════════════════════════════════
           3. IMAGE ZOOM MODAL
           We manually open Bootstrap's modal and set the
           image src BEFORE showing it — avoids the race
           where Bootstrap opens first and src is blank.
        ══════════════════════════════════════════════════ */
        var imgModalEl = document.getElementById('cbImgZoomModal');
        var imgModal   = (imgModalEl && typeof bootstrap !== 'undefined')
                            ? new bootstrap.Modal(imgModalEl) : null;

        function openImgModal(imgUrl, name, adm, cls, gender, initials) {
            var imgEl  = document.getElementById('cbZoomedImg');
            var nameEl = document.getElementById('cbZoomedName');
            var metaEl = document.getElementById('cbZoomedMeta');

            nameEl.textContent = name || 'Student';
            metaEl.innerHTML =
                (adm    ? '<i class="ri-id-card-line me-1"></i>' + esc(adm) : '') +
                (cls    ? ' &nbsp;|&nbsp; <i class="ri-building-line me-1"></i>' + esc(cls) : '') +
                (gender ? ' &nbsp;|&nbsp; ' + esc(gender) : '');

            if (imgUrl && imgUrl !== '' && imgUrl !== 'null' && imgUrl !== 'undefined') {
                imgEl.src = imgUrl;
            } else {
                /* Draw initials on canvas when no photo */
                var canvas = document.createElement('canvas');
                canvas.width = canvas.height = 400;
                var ctx  = canvas.getContext('2d');
                var grad = ctx.createLinearGradient(0, 0, 400, 400);
                grad.addColorStop(0, '#0d9488');
                grad.addColorStop(1, '#0ea5e9');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, 400, 400);
                ctx.fillStyle    = '#fff';
                ctx.font         = 'bold 150px "DM Sans", Arial, sans-serif';
                ctx.textAlign    = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(((initials || 'ST') + '').substring(0, 2).toUpperCase(), 200, 200);
                imgEl.src = canvas.toDataURL();
            }

            if (imgModal) { imgModal.show(); }
        }

        /* Event delegation — catches dynamically inserted elements too */
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.cb-avatar-trigger');
            if (!trigger) return;
            openImgModal(
                trigger.getAttribute('data-img'),
                trigger.getAttribute('data-name'),
                trigger.getAttribute('data-adm'),
                trigger.getAttribute('data-class'),
                trigger.getAttribute('data-gender'),
                trigger.getAttribute('data-initials') || ''
            );
        });

        /* Click on zoomed image closes modal */
        if (imgModalEl) {
            imgModalEl.addEventListener('click', function (e) {
                if (e.target && e.target.id === 'cbZoomedImg' && imgModal) {
                    imgModal.hide();
                }
            });
        }

        /* ══════════════════════════════════════════════════
           4. GRADE BREAKDOWN POPUP
           Lives outside any table so position:fixed works.
        ══════════════════════════════════════════════════ */
        var gpop      = document.getElementById('cbGradePopup');
        var gpopBody  = document.getElementById('gpopBody');
        var gpopTitle = document.getElementById('gpopTitle');
        var backdrop  = document.getElementById('cbPopupBackdrop');
        var activeSid = null;

        function closeGradePop() {
            if (gpop)     gpop.classList.remove('is-open');
            if (backdrop) backdrop.style.display = 'none';
            activeSid = null;
        }

        function gradeClass(g) {
            return (g || '-').toLowerCase().replace(/[\s-]/g, '');
        }

        function openGradePop(sid, name, triggerEl) {
            var an = SA[sid];
            if (!an || !gpop) return;

            gpopTitle.innerHTML = '<i class="ri-bar-chart-line me-1"></i>' + esc(name) + "'s Grade Breakdown";

            var grades = an.grades || [];
            var rows   = grades.map(function (g) {
                var tgl = gradeClass(g.term_grade);
                var cgl = gradeClass(g.cum_grade);
                var tC  = (g.term_score > 0 && g.term_score < 50) ? 'score-red' : '';
                var cC  = (g.cum_score  > 0 && g.cum_score  < 50) ? 'score-red' : '';
                var tBadge = (g.term_grade && g.term_grade !== '-')
                    ? '<span class="grade-badge g-' + tgl + '">' + esc(g.term_grade) + '</span>' : '—';
                var cBadge = (g.cum_grade && g.cum_grade !== '-')
                    ? '<span class="grade-badge g-' + cgl + '">' + esc(g.cum_grade)  + '</span>' : '—';
                return '<tr>' +
                    '<td>' + esc(g.subject) + '</td>' +
                    '<td class="' + tC + '">' + (g.term_score || '—') + '</td>' +
                    '<td>' + tBadge + '</td>' +
                    '<td class="' + cC + '">' + (g.cum_score || '—') + '</td>' +
                    '<td>' + cBadge + '</td>' +
                    '</tr>';
            }).join('');

            var ob     = an.total_obtainable || 0;
            var tTotal = an.term_total || 0;
            var cTotal = an.cum_total  || 0;
            var cPct   = an.cum_percentage || 0;

            gpopBody.innerHTML =
                '<table class="gpop-table"><thead><tr>' +
                '<th>Subject</th>' +
                '<th style="color:#0891b2;">T.Score</th><th style="color:#0891b2;">T.Grade</th>' +
                '<th style="color:var(--cb-navy);">C.Score</th><th style="color:var(--cb-navy);">C.Grade</th>' +
                '</tr></thead><tbody>' + (rows || '<tr><td colspan="5" class="text-center text-muted py-2">No grades available</td></tr>') + '</tbody></table>' +
                '<div class="gpop-summary">' +
                '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Term Total</div><div class="gpop-sum-val ' + (tTotal < ob * .5 ? 'score-red' : '') + '">' + tTotal + '</div></div>' +
                '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Cum Total</div><div class="gpop-sum-val">' + cTotal + '</div></div>' +
                '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Obtainable</div><div class="gpop-sum-val">' + ob + '</div></div>' +
                '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Cum %</div><div class="gpop-sum-val ' + (cPct < 50 ? 'score-red' : 'score-green') + '">' + cPct + '%</div></div>' +
                '</div>';

            /* Position near trigger */
            var rect = triggerEl.getBoundingClientRect();
            var pw   = 360, ph = 490;
            var top  = rect.bottom + window.scrollY + 8;
            var left = rect.left   + window.scrollX - (pw / 2) + (rect.width / 2);
            if (rect.bottom + ph > window.innerHeight) { top = rect.top + window.scrollY - ph - 8; }
            if (left < 8)               left = 8;
            if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;

            gpop.style.top  = top  + 'px';
            gpop.style.left = left + 'px';
            gpop.classList.add('is-open');
            if (backdrop) backdrop.style.display = 'block';
            activeSid = sid;
        }

        document.getElementById('gpopCloseBtn')?.addEventListener('click', closeGradePop);
        if (backdrop) backdrop.addEventListener('click', closeGradePop);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeGradePop(); });

        /* Delegation for grade triggers */
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.grade-trigger-btn');
            if (!btn) return;
            e.stopPropagation();
            var sid  = btn.getAttribute('data-sid');
            var name = btn.getAttribute('data-sname');
            if (sid === activeSid) { closeGradePop(); return; }
            closeGradePop();
            openGradePop(sid, name, btn);
        });

        /* ══════════════════════════════════════════════════
           5. STAT CARDS
        ══════════════════════════════════════════════════ */
        (function () {
            var vals = Object.values(SA);
            if (!vals.length) return;
            var avgPct = Math.round(vals.reduce(function (s, d) { return s + (d.cum_percentage || 0); }, 0) / vals.length);
            var prEl   = document.getElementById('statPassRate');
            if (prEl) prEl.textContent = avgPct + '%';

            var topAvg = -1, topName = '—';
            document.querySelectorAll('.cb-student-row[data-student-id]').forEach(function (row) {
                var sid = row.getAttribute('data-student-id');
                if (!sid || !SA[sid]) return;
                var avg = SA[sid].cum_average || 0;
                if (avg > topAvg) {
                    topAvg = avg;
                    var el = row.querySelector('.student-name-text');
                    if (el) {
                        var parts = el.textContent.trim().split(' ');
                        topName = parts[0] + (parts[1] ? ' ' + parts[1][0] + '.' : '');
                    }
                }
            });
            var topEl = document.getElementById('statTop');
            if (topEl) topEl.textContent = topName;
        })();

        /* ══════════════════════════════════════════════════
           6. VALIDATION
        ══════════════════════════════════════════════════ */
        function validate() {
            var ok = true, first = null;
            document.querySelectorAll('.teacher-comment').forEach(function (inp) {
                var row = inp.closest('.cb-student-row');
                if (row && (row.style.display === 'none' || window.getComputedStyle(row).display === 'none')) return;
                if (!inp.value.trim()) {
                    inp.classList.add('field-error');
                    if (!first) first = inp;
                    ok = false;
                } else {
                    inp.classList.remove('field-error');
                }
            });
            if (!ok && first) {
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                first.focus();
                toast("Please fill in the Teacher's Comment for all visible students.", 'error');
            }
            return ok;
        }

        /* Live clear */
        document.querySelectorAll('.teacher-comment').forEach(function (inp) {
            inp.addEventListener('input', function () {
                this.classList.toggle('field-error', !this.value.trim());
            });
        });

        /* ══════════════════════════════════════════════════
           7. SAVE — AJAX POST returning JSON
        ══════════════════════════════════════════════════ */
        var saveBtn    = document.getElementById('saveBtn');
        var savingText = document.getElementById('savingText');

        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                if (!validate()) return;

                var origHtml      = saveBtn.innerHTML;
                saveBtn.disabled  = true;
                saveBtn.innerHTML = '<i class="spin ri-loader-4-line"></i> Saving…';
                if (savingText) savingText.style.display = 'inline-flex';

                /* Collect the form — the form element includes @csrf hidden input */
                var form = document.getElementById('commentsForm');
                var fd   = new FormData(form);

                /* Ensure CSRF is present (FormData gets it from the hidden _token field) */
                if (!fd.get('_token')) fd.set('_token', CSRF);

                /* Signature file */
                var sigFile = document.getElementById('signatureFile');
                if (sigFile && sigFile.files && sigFile.files[0]) {
                    fd.set('signature', sigFile.files[0]);
                }

                fetch(SAVE_URL, {
                    method:  'POST',
                    headers: {
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN':     CSRF,
                    },
                    body: fd,
                })
                .then(function (res) {
                    if (!res.ok) {
                        /* Try to read JSON error body, fall back to status text */
                        return res.json().catch(function () {
                            throw new Error('HTTP ' + res.status + ' ' + res.statusText);
                        }).then(function (d) {
                            throw new Error(d.message || 'HTTP ' + res.status);
                        });
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (data.success) {
                        toast(data.message || 'Saved successfully!', 'success');
                        document.querySelectorAll('.teacher-comment').forEach(function (inp) {
                            if (inp.value.trim()) inp.classList.remove('field-error');
                        });
                    } else {
                        toast(data.message || 'Save failed.', 'error');
                    }
                })
                .catch(function (err) {
                    console.error('ClassBroadsheet save error:', err);
                    toast('Error: ' + err.message, 'error');
                })
                .finally(function () {
                    saveBtn.disabled  = false;
                    saveBtn.innerHTML = origHtml;
                    if (savingText) savingText.style.display = 'none';
                });
            });
        }

    }); /* end DOMContentLoaded */
})();
</script>
@endsection
