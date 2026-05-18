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

/* Row status indicators */
.cb-table tbody tr.row-has-comment td.td-name { border-left: 3px solid var(--cb-teal); }
.cb-table tbody tr.row-no-comment  td.td-name { border-left: 3px solid var(--cb-border); }

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
.cb-input.has-value { border-left-color: var(--cb-teal); }
.absence-input { width: 72px !important; text-align: center; }

/* Avatar */
.student-name-cell { display: flex; align-items: center; gap: 9px; }
.cb-avatar { width: 38px; height: 38px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid var(--cb-border); cursor: pointer; transition: border-color .15s, transform .15s; display: flex; align-items: center; justify-content: center; }
.cb-avatar:hover { border-color: var(--cb-teal); transform: scale(1.08); }
.cb-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cb-avatar-initials { background: linear-gradient(135deg, var(--cb-teal), var(--cb-sky)); color: #fff; font-size: 13px; font-weight: 700; }
.student-name-text { font-weight: 600; font-size: 12.5px; color: var(--cb-navy); }
.student-adm { font-size: 10.5px; color: var(--cb-muted); margin-top: 1px; }

/* Autosave chip */
.autosave-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 20px; margin-top: 3px; transition: all .2s;
}
.ac-idle    { background: #f1f5f9; color: #94a3b8; }
.ac-saving  { background: #fef3c7; color: #92400e; }
.ac-saved   { background: #dcfce7; color: #15803d; }
.ac-err     { background: #ffe4e6; color: #be123c; }

/* Comment status badge */
.comment-status-dot {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700; padding: 1px 7px;
    border-radius: 20px; margin-top: 2px;
}
.dot-saved    { background: #dcfce7; color: #15803d; }
.dot-unsaved  { background: #f1f5f9; color: #94a3b8; }

/* Save bar */
.save-bar { position: sticky; bottom: 0; z-index: 100; background: rgba(15,35,66,.97); backdrop-filter: blur(10px); border-top: 2px solid var(--cb-teal); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-radius: 0 0 var(--cb-radius) var(--cb-radius); }
.save-bar label { color: rgba(255,255,255,.7); font-size: 12px; font-weight: 500; margin-bottom: 0; }
.file-input-styled { padding: 6px 12px; border-radius: 8px; border: 1.5px solid rgba(255,255,255,.2); background: rgba(255,255,255,.08); color: #fff; font-size: 12px; cursor: pointer; }
.file-input-styled:hover { border-color: var(--cb-teal); }
.btn-save-all { background: var(--cb-teal); color: #fff; border: none; border-radius: 10px; padding: 10px 28px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .18s, transform .12s, box-shadow .18s; display: flex; align-items: center; gap: 8px; font-family: 'DM Sans', sans-serif; }
.btn-save-all:hover { background: #0b7c72; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(13,148,136,.4); }
.btn-save-all:disabled { opacity: .65; transform: none; cursor: not-allowed; }

/* Save counter */
.save-counter { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.save-counter-pill { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px; }
.sc-pill-done    { background: rgba(34,197,94,.2);  color: #86efac; }
.sc-pill-pending { background: rgba(255,255,255,.1); color: rgba(255,255,255,.6); }

/* Toast */
.cb-toast { position: fixed; bottom: 80px; right: 24px; min-width: 300px; z-index: 99999; padding: 14px 18px; border-radius: 12px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; box-shadow: var(--cb-shadow-lg); animation: cbSlideUp .3s ease; }
.cb-toast-success { background: #ecfdf5; border: 1.5px solid #86efac; color: #15803d; }
.cb-toast-error   { background: #fff1f2; border: 1.5px solid #fca5a5; color: #be123c; }
.cb-toast-info    { background: #eff6ff; border: 1.5px solid #93c5fd; color: #1d4ed8; }
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
.cb-student-card.card-has-comment .card-top { border-left: 4px solid var(--cb-teal); }
.cb-student-card.card-no-comment  .card-top { border-left: 4px solid var(--cb-border); }
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
#cbPopupBackdrop { display: none; position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,.28); }

/* Enhanced Modal Styles */
.past-comment-item {
    transition: all 0.2s ease;
}

.past-comments-timeline {
    position: relative;
}

.ps-item .ps-val {
    transition: all 0.3s ease;
}

#modalTextarea:focus {
    border-color: var(--cb-teal) !important;
    box-shadow: 0 0 0 3px rgba(13,148,136,0.1) !important;
}

/* Performance strip improvements */
.performance-strip .ps-item {
    transition: transform 0.2s ease;
}

.performance-strip .ps-item:hover {
    transform: translateY(-2px);
}
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
        <span class="toggle-chip active" data-colkey="scores"><i class="ri-bar-chart-line"></i> Subject Scores</span>
        <span class="toggle-chip active" data-colkey="summary"><i class="ri-pie-chart-line"></i> Summary</span>
        <span class="toggle-chip active" data-colkey="teacher"><i class="ri-chat-3-line"></i> Teacher's Comment</span>
        <span class="toggle-chip active" data-colkey="guidance"><i class="ri-mental-health-line"></i> Counselor's Comment</span>
        <span class="toggle-chip active" data-colkey="activities"><i class="ri-football-line"></i> Remark on Activities</span>
        <span class="toggle-chip active" data-colkey="absence"><i class="ri-calendar-close-line"></i> Absences</span>
    </div>
    <p class="mt-2 mb-0" style="font-size:11px;color:var(--cb-muted);">
        <i class="ri-information-line me-1"></i>
        Click on any comment field to open the rich text editor with past comments history.
    </p>
</div>

@if ($students->isNotEmpty())

@php $cbAnalyticsJson = json_encode($studentAnalytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); @endphp

{{-- Grade popup --}}
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
        <input type="hidden" name="_method" value="PATCH">

        @foreach ($students as $student)
            @php
                $sid     = $student->id;
                $profile = $personalityProfiles->where('studentid', $sid)->first();
            @endphp
            <input type="hidden" class="canonical-teacher"    id="c_teacher_{{ $sid }}"    name="teacher_comments[{{ $sid }}]"            value="{{ $profile ? $profile->classteachercomment : '' }}">
            <input type="hidden" class="canonical-guidance"   id="c_guidance_{{ $sid }}"   name="guidance_comments[{{ $sid }}]"           value="{{ $profile ? $profile->guidancescomment : '' }}">
            <input type="hidden" class="canonical-activities" id="c_activities_{{ $sid }}" name="remarks_on_other_activities[{{ $sid }}]"  value="{{ $profile ? $profile->remark_on_other_activities : '' }}">
            <input type="hidden" class="canonical-absence"    id="c_absence_{{ $sid }}"    name="no_of_times_school_absent[{{ $sid }}]"   value="{{ $profile ? $profile->no_of_times_school_absent : '' }}">
        @endforeach

        {{-- DESKTOP TABLE --}}
        <div class="desktop-only" style="overflow-x:auto;">
            <table class="cb-table">
                <thead>
                    <tr>
                        <th style="width:34px;">#</th>
                        <th class="col-name-hdr" style="min-width:200px;">Student</th>
                        @foreach ($subjects as $subject)
                            <th class="cbcol-scores" style="min-width:86px;">{{ $subject->subject }}</th>
                        @endforeach
                        <th class="cbcol-summary" style="min-width:140px;">Summary</th>
                        <th class="cbcol-teacher" style="min-width:200px;">Teacher's Comment</th>
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
                            $hasComment = $profile && !empty(trim($profile->classteachercomment ?? ''));
                        @endphp
                        <tr class="cb-student-row {{ $hasComment ? 'row-has-comment' : 'row-no-comment' }}"
                            data-student-id="{{ $sid }}"
                            data-student-name="{{ $fullName }}"
                            data-student-adm="{{ $student->admissionNo }}"
                            data-student-img="{{ $imgUrl }}"
                            data-student-analytics='@json($an)'
                            data-searchkey="{{ strtolower($fullName . ' ' . $student->admissionNo) }}">

                            <td>{{ $index + 1 }}</td>

                            <td class="td-name">
                                <div class="student-name-cell">
                                    @if($imgUrl)
                                        <div class="cb-avatar cb-avatar-trigger"
                                             data-img="{{ $imgUrl }}" data-name="{{ $fullName }}"
                                             data-adm="{{ $student->admissionNo }}"
                                             data-class="{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : '' }}"
                                             data-gender="{{ $student->gender ?? '' }}">
                                            <img src="{{ $imgUrl }}" alt="{{ $fullName }}"
                                                 onerror="this.closest('.cb-avatar').classList.add('cb-avatar-initials'); this.remove(); this.closest('.cb-avatar').textContent='{{ $initials }}'">
                                        </div>
                                    @else
                                        <div class="cb-avatar cb-avatar-initials cb-avatar-trigger"
                                             data-img="" data-name="{{ $fullName }}"
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
                                        <span class="comment-status-dot {{ $hasComment ? 'dot-saved' : 'dot-unsaved' }}"
                                              id="status-{{ $sid }}">
                                            {{ $hasComment ? '✓ Commented' : '○ No comment' }}
                                        </span>
                                        <span class="autosave-chip ac-idle" id="autosave-{{ $sid }}"></span>
                                    </div>
                                </div>
                            </td>

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
                                            data-sid="{{ $sid }}" data-sname="{{ $fullName }}"
                                            title="View grade breakdown">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                </div>
                            </td>

                            <td class="cbcol-teacher">
                                <input type="text"
                                       class="cb-input desk-teacher comment-input {{ $hasComment ? 'has-value' : '' }}"
                                       data-sid="{{ $sid }}" data-field="teacher"
                                       value="{{ $profile ? $profile->classteachercomment : '' }}"
                                       placeholder="Click to add comment..."
                                       autocomplete="off">
                            </td>
                            <td class="cbcol-guidance">
                                <input type="text"
                                       class="cb-input desk-guidance comment-input"
                                       data-sid="{{ $sid }}" data-field="guidance"
                                       value="{{ $profile ? $profile->guidancescomment : '' }}"
                                       placeholder="Click to add comment..."
                                       autocomplete="off">
                            </td>
                            <td class="cbcol-activities">
                                <input type="text"
                                       class="cb-input desk-activities comment-input"
                                       data-sid="{{ $sid }}" data-field="activities"
                                       value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                       placeholder="Click to add comment..."
                                       autocomplete="off">
                            </td>
                            <td class="cbcol-absence">
                                <input type="number"
                                       class="cb-input absence-input desk-absence"
                                       data-sid="{{ $sid }}" data-field="absence"
                                       value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                       min="0" placeholder="0"
                                       autocomplete="off">
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
                    $hasComment = $profile && !empty(trim($profile->classteachercomment ?? ''));
                @endphp
                <div class="cb-student-card cb-student-row {{ $hasComment ? 'card-has-comment' : 'card-no-comment' }}"
                     data-student-id="{{ $sid }}"
                     data-student-name="{{ $fullName }}"
                     data-student-adm="{{ $student->admissionNo }}"
                     data-student-img="{{ $imgUrl }}"
                     data-student-analytics='@json($an)'
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
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:14px;color:var(--cb-navy);">{{ $fullName }}</div>
                            <div style="font-size:11px;color:var(--cb-muted);">{{ $student->admissionNo }} · {{ $student->gender ?? '' }}</div>
                            <span class="comment-status-dot {{ $hasComment ? 'dot-saved' : 'dot-unsaved' }}"
                                  id="status-m-{{ $sid }}">
                                {{ $hasComment ? '✓ Commented' : '○ No comment' }}
                            </span>
                            <span class="autosave-chip ac-idle" id="autosave-m-{{ $sid }}"></span>
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
                            <label>Teacher's Comment</label>
                            <input type="text"
                                   class="cb-input mob-teacher comment-input {{ $hasComment ? 'has-value' : '' }}"
                                   data-sid="{{ $sid }}" data-field="teacher"
                                   value="{{ $profile ? $profile->classteachercomment : '' }}"
                                   placeholder="Click to add comment..."
                                   autocomplete="off">
                        </div>
                        <div class="comment-field-group mobile-col-guidance">
                            <label>Counselor's Comment</label>
                            <input type="text"
                                   class="cb-input mob-guidance comment-input"
                                   data-sid="{{ $sid }}" data-field="guidance"
                                   value="{{ $profile ? $profile->guidancescomment : '' }}"
                                   placeholder="Click to add comment..."
                                   autocomplete="off">
                        </div>
                        <div class="comment-field-group mobile-col-activities">
                            <label>Remark on Activities</label>
                            <input type="text"
                                   class="cb-input mob-activities comment-input"
                                   data-sid="{{ $sid }}" data-field="activities"
                                   value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                   placeholder="Click to add comment..."
                                   autocomplete="off">
                        </div>
                        <div class="comment-field-group mobile-col-absence">
                            <label>Times Absent</label>
                            <input type="number"
                                   class="cb-input mob-absence"
                                   data-sid="{{ $sid }}" data-field="absence"
                                   value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                   min="0" placeholder="0"
                                   style="max-width:100px;"
                                   autocomplete="off">
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
            <div class="save-counter">
                <span class="save-counter-pill sc-pill-done" id="counterDone">
                    <i class="ri-checkbox-circle-line"></i>
                    <span id="counterDoneNum">0</span> commented
                </span>
                <span class="save-counter-pill sc-pill-pending" id="counterPending">
                    <i class="ri-time-line"></i>
                    <span id="counterPendingNum">0</span> pending
                </span>
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

</div></div></div>

{{-- Image zoom modal --}}
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

{{-- Enhanced Comment Modal for Rich Text Editing --}}
<div class="modal fade" id="cbCommentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: var(--cb-radius); overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal)); color: #fff; border: none; padding: 20px 24px;">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div id="modalStudentAvatar" style="width: 65px; height: 65px; border-radius: 50%; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); background: linear-gradient(135deg, var(--cb-teal), var(--cb-sky));"></div>
                    <div>
                        <h5 class="mb-1 text-white" id="modalStudentName" style="font-weight: 700;"></h5>
                        <div class="text-white-50 small" id="modalStudentMeta"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                {{-- Performance Strip - Matching the blade design --}}
                <div class="performance-strip" style="background: linear-gradient(135deg, var(--cb-navy), #1e5f74); border-radius: 12px; padding: 16px 20px; color: #fff; margin-bottom: 24px;">
                    <div style="font-size: 12px; font-weight: 600; opacity: 0.9; margin-bottom: 12px;">
                        <i class="ri-bar-chart-line me-1"></i> Performance Summary
                    </div>
                    <div class="ps-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                        <div class="ps-item" style="text-align: center; background: rgba(255,255,255,0.12); border-radius: 10px; padding: 10px;">
                            <div class="ps-lbl" style="font-size: 10px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Term Avg</div>
                            <div class="ps-val" id="modalTermAvg" style="font-size: 20px; font-weight: 700; margin-top: 5px;">0</div>
                        </div>
                        <div class="ps-item" style="text-align: center; background: rgba(255,255,255,0.12); border-radius: 10px; padding: 10px;">
                            <div class="ps-lbl" style="font-size: 10px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Cum Avg</div>
                            <div class="ps-val" id="modalCumAvg" style="font-size: 20px; font-weight: 700; margin-top: 5px;">0</div>
                        </div>
                        <div class="ps-item" style="text-align: center; background: rgba(255,255,255,0.12); border-radius: 10px; padding: 10px;">
                            <div class="ps-lbl" style="font-size: 10px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Cum %</div>
                            <div class="ps-val" id="modalCumPct" style="font-size: 20px; font-weight: 700; margin-top: 5px;">0%</div>
                        </div>
                        <div class="ps-item" style="text-align: center; background: rgba(255,255,255,0.12); border-radius: 10px; padding: 10px;">
                            <div class="ps-lbl" style="font-size: 10px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Subjects</div>
                            <div class="ps-val" id="modalSubjects" style="font-size: 20px; font-weight: 700; margin-top: 5px;">0</div>
                        </div>
                    </div>
                </div>

                {{-- Comment Type Indicator --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0" id="modalCommentType" style="font-weight: 700; color: var(--cb-navy);">
                            <i class="ri-chat-3-line me-1" style="color: var(--cb-teal);"></i> Teacher's Comment
                        </h6>
                        <small class="text-muted" id="modalCommentHint">Click on any past comment to load it below</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnLoadPastComments" style="border-radius: 20px; padding: 6px 16px;">
                        <i class="ri-history-line me-1"></i> View Past Comments
                        <span id="pastCommentCount" class="badge bg-secondary ms-1" style="border-radius: 20px;">0</span>
                    </button>
                </div>

                {{-- Rich Text Toolbar --}}
                <div class="mb-2" style="background: #f8fafc; border-radius: 10px; padding: 8px; border: 1px solid var(--cb-border);">
                    <button type="button" class="btn btn-sm btn-light me-1" onclick="formatCommentText('bold')" style="border-radius: 6px;" title="Bold">
                        <i class="ri-bold"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light me-1" onclick="formatCommentText('italic')" style="border-radius: 6px;" title="Italic">
                        <i class="ri-italic"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light me-1" onclick="formatCommentText('bullet')" style="border-radius: 6px;" title="Bullet List">
                        <i class="ri-list-unordered"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light me-1" onclick="formatCommentText('number')" style="border-radius: 6px;" title="Number List">
                        <i class="ri-list-ordered"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light" onclick="formatCommentText('clear')" style="border-radius: 6px;" title="Clear">
                        <i class="ri-delete-back-line"></i>
                    </button>
                </div>

                {{-- Text Area --}}
                <textarea id="modalTextarea" class="form-control" rows="6" style="resize: vertical; font-family: inherit; font-size: 14px; line-height: 1.6; border-radius: 10px; border: 1.5px solid var(--cb-border); padding: 12px;"></textarea>

                {{-- Past Comments Panel --}}
                <div id="pastCommentsPanel" style="display: none; margin-top: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="small fw-bold mb-0" style="color: var(--cb-navy);">
                            <i class="ri-history-line me-1" style="color: var(--cb-teal);"></i> Past Comments from Previous Terms
                        </h6>
                        <button type="button" class="btn-close btn-sm" onclick="document.getElementById('pastCommentsPanel').style.display='none'"></button>
                    </div>
                    <div id="pastCommentsList" style="max-height: 350px; overflow-y: auto; border-radius: 10px;"></div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid var(--cb-border); padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px;">Cancel</button>
                <button type="button" class="btn btn-success" id="modalSaveBtn" style="border-radius: 8px; padding: 8px 24px; background: var(--cb-teal); border: none;">
                    <i class="ri-save-line me-1"></i> Save Comment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var SA       = {!! $cbAnalyticsJson !!};
    var SAVE_URL = '{{ route("classbroadsheet.updateComments", [$schoolclassid, $sessionid, $termid]) }}';
    var CSRF     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    var FIELD_MAP = {
        teacher:    'c_teacher_',
        guidance:   'c_guidance_',
        activities: 'c_activities_',
        absence:    'c_absence_',
    };

    var debounceTimers = {};
    var AUTOSAVE_DELAY = 1200;

    // Modal variables
    var currentModalSid = null;
    var currentModalField = null;
    var commentModal = null;

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    function toast(msg, type) {
        document.querySelectorAll('.cb-toast').forEach(function (t) { t.remove(); });
        var icons = { success: 'checkbox-circle-fill', error: 'error-warning-fill', info: 'information-fill' };
        var el = document.createElement('div');
        el.className = 'cb-toast cb-toast-' + (type || 'info');
        el.innerHTML = '<i class="ri-' + (icons[type] || icons.info) + '" style="font-size:18px;flex-shrink:0;"></i> ' + esc(msg);
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 5000);
    }

    function getCanonical(sid, field) {
        var el = document.getElementById(FIELD_MAP[field] + sid);
        return el ? el.value : '';
    }

    function setCanonical(sid, field, value) {
        var el = document.getElementById(FIELD_MAP[field] + sid);
        if (el) el.value = value;
    }

    function setChipState(sid, state, text) {
        ['autosave-' + sid, 'autosave-m-' + sid].forEach(function (id) {
            var chip = document.getElementById(id);
            if (!chip) return;
            chip.className = 'autosave-chip ' + state;
            chip.textContent = text || '';
        });
    }

    function autoSaveStudent(sid) {
        var fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('_method', 'PATCH');
        fd.append('teacher_comments[' + sid + ']', getCanonical(sid, 'teacher'));
        fd.append('guidance_comments[' + sid + ']', getCanonical(sid, 'guidance'));
        fd.append('remarks_on_other_activities[' + sid + ']', getCanonical(sid, 'activities'));
        fd.append('no_of_times_school_absent[' + sid + ']', getCanonical(sid, 'absence'));

        var sigFile = document.getElementById('signatureFile');
        if (sigFile && sigFile.files && sigFile.files[0]) {
            fd.append('signature', sigFile.files[0]);
        }

        setChipState(sid, 'ac-saving', '⏳ Saving…');

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: fd,
        })
        .then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));
                return data;
            });
        })
        .then(function (data) {
            if (data.success) {
                setChipState(sid, 'ac-saved', '✓ Saved');
                refreshCommentStatusForStudent(sid);
                setTimeout(function () { setChipState(sid, 'ac-idle', ''); }, 3000);
            } else {
                setChipState(sid, 'ac-err', '✗ Failed');
            }
        })
        .catch(function (err) {
            console.error('Autosave error sid=' + sid, err);
            setChipState(sid, 'ac-err', '✗ Error');
        });
    }

    function scheduleAutosave(sid) {
        if (debounceTimers[sid]) clearTimeout(debounceTimers[sid]);
        debounceTimers[sid] = setTimeout(function () { autoSaveStudent(sid); }, AUTOSAVE_DELAY);
    }

    function onInputChange(e) {
        var inp = e.target;
        var sid = inp.getAttribute('data-sid');
        var field = inp.getAttribute('data-field');
        if (!sid || !field) return;

        var val = inp.value;
        setCanonical(sid, field, val);

        var isDesktop = inp.classList.contains('desk-' + field);
        var twinClass = isDesktop ? 'mob-' + field : 'desk-' + field;
        document.querySelectorAll('.' + twinClass + '[data-sid="' + sid + '"]').forEach(function (twin) {
            if (twin !== inp) twin.value = val;
        });

        refreshCommentStatusForStudent(sid);
        scheduleAutosave(sid);
    }

    function refreshCommentStatusForStudent(sid) {
        var hasVal = getCanonical(sid, 'teacher').trim() !== '';

        ['status-' + sid, 'status-m-' + sid].forEach(function (id) {
            var badge = document.getElementById(id);
            if (!badge) return;
            badge.textContent = hasVal ? '✓ Commented' : '○ No comment';
            badge.className = 'comment-status-dot ' + (hasVal ? 'dot-saved' : 'dot-unsaved');
        });

        document.querySelectorAll('.desk-teacher[data-sid="' + sid + '"], .mob-teacher[data-sid="' + sid + '"]').forEach(function (inp) {
            inp.classList.toggle('has-value', hasVal);
        });

        document.querySelectorAll('[data-student-id="' + sid + '"]').forEach(function (row) {
            row.classList.toggle('row-has-comment', hasVal);
            row.classList.toggle('row-no-comment', !hasVal);
            row.classList.toggle('card-has-comment', hasVal);
            row.classList.toggle('card-no-comment', !hasVal);
        });
    }

    function refreshCommentStatus() {
        var done = 0, pending = 0;
        document.querySelectorAll('.cb-student-row').forEach(function (row) {
            var sid = row.getAttribute('data-student-id');
            if (!sid) return;
            if (getCanonical(sid, 'teacher').trim() !== '') done++;
            else pending++;
            refreshCommentStatusForStudent(sid);
        });
        var dNum = document.getElementById('counterDoneNum');
        var pNum = document.getElementById('counterPendingNum');
        if (dNum) dNum.textContent = done;
        if (pNum) pNum.textContent = pending;
    }

    // Enhanced formatCommentText function
    window.formatCommentText = function(type) {
        var ta = document.getElementById('modalTextarea');
        if (!ta) return;

        var start = ta.selectionStart;
        var end = ta.selectionEnd;
        var text = ta.value;
        var selectedText = text.substring(start, end);

        if (type === 'bold') {
            var formatted = '**' + (selectedText || 'bold text') + '**';
            ta.value = text.substring(0, start) + formatted + text.substring(end);
            ta.focus();
            ta.setSelectionRange(start + formatted.length, start + formatted.length);
        } else if (type === 'italic') {
            var formatted = '*' + (selectedText || 'italic text') + '*';
            ta.value = text.substring(0, start) + formatted + text.substring(end);
            ta.focus();
            ta.setSelectionRange(start + formatted.length, start + formatted.length);
        } else if (type === 'bullet') {
            if (selectedText) {
                var formatted = selectedText.split('\n').map(function(line) { return '• ' + line; }).join('\n');
                ta.value = text.substring(0, start) + formatted + text.substring(end);
            } else {
                ta.value = text.substring(0, start) + '• ' + text.substring(end);
            }
            ta.focus();
        } else if (type === 'number') {
            if (selectedText) {
                var formatted = selectedText.split('\n').map(function(line, idx) { return (idx + 1) + '. ' + line; }).join('\n');
                ta.value = text.substring(0, start) + formatted + text.substring(end);
            } else {
                ta.value = text.substring(0, start) + '1. ' + text.substring(end);
            }
            ta.focus();
        } else if (type === 'clear') {
            ta.value = '';
            ta.focus();
        }
    };

    // Enhanced openCommentModal function
    function openCommentModal(sid, field, studentName, studentAdm, studentImg, analytics) {
        currentModalSid = sid;
        currentModalField = field;

        if (!commentModal) {
            commentModal = new bootstrap.Modal(document.getElementById('cbCommentModal'));
        }

        // Set student info
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('modalStudentMeta').innerHTML = '<i class="ri-id-card-line me-1"></i> ' + (studentAdm || '');

        // Set avatar
        var avatarDiv = document.getElementById('modalStudentAvatar');
        if (studentImg && studentImg !== 'null' && studentImg !== '') {
            avatarDiv.innerHTML = '<img src="' + studentImg + '" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            var initials = studentName.split(' ').map(function(n) { return n[0]; }).join('').substring(0, 2).toUpperCase();
            avatarDiv.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;">' + esc(initials) + '</div>';
        }

        // Update performance metrics
        document.getElementById('modalTermAvg').textContent = analytics.term_average || 0;
        document.getElementById('modalCumAvg').textContent = analytics.cum_average || 0;
        document.getElementById('modalCumPct').textContent = (analytics.cum_percentage || 0) + '%';
        document.getElementById('modalSubjects').textContent = analytics.subject_count || 0;

        // Color code the percentages
        var cumPctEl = document.getElementById('modalCumPct');
        if (analytics.cum_percentage >= 70) {
            cumPctEl.style.color = '#4ade80';
        } else if (analytics.cum_percentage >= 50) {
            cumPctEl.style.color = '#fbbf24';
        } else {
            cumPctEl.style.color = '#f87171';
        }

        // Set comment type label
        var labels = {
            teacher: "Teacher's Comment",
            guidance: "Counselor's Comment",
            activities: "Remark on Activities"
        };
        var icons = {
            teacher: 'ri-chat-quote-line',
            guidance: 'ri-mental-health-line',
            activities: 'ri-football-line'
        };
        document.getElementById('modalCommentType').innerHTML = '<i class="' + (icons[field] || 'ri-chat-3-line') + ' me-1" style="color: var(--cb-teal);"></i> ' + (labels[field] || field);

        // Set current value
        var currentValue = getCanonical(sid, field);
        document.getElementById('modalTextarea').value = currentValue;
        document.getElementById('pastCommentsPanel').style.display = 'none';

        commentModal.show();
    }

    // Enhanced loadPastComments function with better UI
    async function loadPastComments() {
        if (!currentModalSid) return;

        var listEl = document.getElementById('pastCommentsList');
        listEl.innerHTML = '<div class="text-center py-4"><i class="ri-loader-4-line ri-spin" style="font-size: 24px; color: var(--cb-teal);"></i><br><span class="text-muted mt-2 d-block">Loading past comments...</span></div>';

        try {
            var url = '/classbroadsheet/past-comments/' + currentModalSid;
            var response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            var data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                document.getElementById('pastCommentCount').textContent = data.data.length;

                // Summary badges
                var summaryHtml = `
                    <div class="mb-3" style="background: #f1f5f9; border-radius: 10px; padding: 12px;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="small fw-bold text-muted"><i class="ri-bar-chart-line"></i> Comment History</span>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge" style="background: #0ea5e9; color: white;">Teacher: ${data.counts.classteacher || 0}</span>
                                <span class="badge" style="background: #8b5cf6; color: white;">Guidance: ${data.counts.guidance || 0}</span>
                                <span class="badge" style="background: #f59e0b; color: white;">Activities: ${data.counts.activities || 0}</span>
                                <span class="badge" style="background: #1e293b;">Total: ${data.counts.total}</span>
                            </div>
                        </div>
                    </div>
                `;

                // Group comments by session/term
                var commentsHtml = '<div class="past-comments-timeline">';
                data.data.forEach(function(comment) {
                    var badgeColor = '';
                    var badgeIcon = '';
                    if (comment.comment_type === 'Teacher') {
                        badgeColor = '#0ea5e9';
                        badgeIcon = 'ri-chat-quote-line';
                    } else if (comment.comment_type === 'Guidance') {
                        badgeColor = '#8b5cf6';
                        badgeIcon = 'ri-mental-health-line';
                    } else if (comment.comment_type === 'Activities') {
                        badgeColor = '#f59e0b';
                        badgeIcon = 'ri-football-line';
                    } else {
                        badgeColor = '#64748b';
                        badgeIcon = 'ri-chat-3-line';
                    }

                    commentsHtml += `
                        <div class="past-comment-item" style="border-left: 4px solid ${badgeColor}; background: #ffffff; padding: 14px; margin-bottom: 12px; border-radius: 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;"
                             onclick="usePastComment('${escapeHtml(comment.comment_text)}')"
                             onmouseover="this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                             onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)';">

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background: ${badgeColor}; color: white; padding: 4px 10px;">
                                        <i class="${badgeIcon} me-1" style="font-size: 11px;"></i> ${comment.comment_type}
                                    </span>
                                    <span class="small text-muted"><i class="ri-calendar-line me-1"></i> ${comment.date}</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="small fw-semibold" style="color: var(--cb-navy);">
                                    <i class="ri-calendar-event-line me-1"></i> ${escapeHtml(comment.session)} · ${escapeHtml(comment.term)}
                                </span>
                                <span class="mx-1 text-muted">•</span>
                                <span class="small text-muted"><i class="ri-building-line me-1"></i> ${escapeHtml(comment.class)}</span>
                            </div>

                            <div class="small" style="color: #334155; line-height: 1.5;">
                                ${escapeHtml(comment.comment_text.length > 200 ? comment.comment_text.substring(0, 200) + '...' : comment.comment_text)}
                            </div>

                            <div class="mt-2 text-end">
                                <span class="small text-primary"><i class="ri-double-quotes-r"></i> Click to load this comment</span>
                            </div>
                        </div>
                    `;
                });
                commentsHtml += '</div>';

                listEl.innerHTML = summaryHtml + commentsHtml;
                document.getElementById('pastCommentsPanel').style.display = 'block';
            } else {
                listEl.innerHTML = `
                    <div class="text-center py-5">
                        <i class="ri-inbox-line" style="font-size: 48px; color: #cbd5e1;"></i>
                        <p class="text-muted mt-2 mb-0">No past comments found for this student.</p>
                        <small class="text-muted">Comments from previous terms will appear here</small>
                    </div>
                `;
                document.getElementById('pastCommentCount').textContent = '0';
                document.getElementById('pastCommentsPanel').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading past comments:', error);
            listEl.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="ri-error-warning-line" style="font-size: 48px;"></i>
                    <p class="mt-2 mb-0">Failed to load past comments</p>
                    <small>Please refresh and try again</small>
                </div>
            `;
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Enhanced usePastComment function
    window.usePastComment = function(text) {
        var ta = document.getElementById('modalTextarea');
        ta.value = text;
        ta.focus();

        // Show a temporary notification
        var notification = document.createElement('div');
        notification.className = 'cb-toast cb-toast-success';
        notification.innerHTML = '<i class="ri-checkbox-circle-fill"></i> Past comment loaded! You can edit it before saving.';
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '10000';
        document.body.appendChild(notification);
        setTimeout(function() { notification.remove(); }, 2000);
    };

    function saveCommentFromModal() {
        var newValue = document.getElementById('modalTextarea').value;
        setCanonical(currentModalSid, currentModalField, newValue);

        document.querySelectorAll('[data-sid="' + currentModalSid + '"][data-field="' + currentModalField + '"]').forEach(function(inp) {
            inp.value = newValue;
        });

        refreshCommentStatusForStudent(currentModalSid);
        scheduleAutosave(currentModalSid);

        commentModal.hide();
        toast(currentModalField.charAt(0).toUpperCase() + currentModalField.slice(1) + ' comment saved!', 'success');
    }

    function attachModalTriggers() {
        document.querySelectorAll('.comment-input').forEach(function(input) {
            input.removeEventListener('focus', handleInputFocus);
            input.addEventListener('focus', handleInputFocus);
        });
    }

    function handleInputFocus(e) {
        var input = e.target;
        var sid = input.getAttribute('data-sid');
        var field = input.getAttribute('data-field');
        if (!sid || !field) return;

        var row = document.querySelector('[data-student-id="' + sid + '"]');
        var studentName = row ? row.getAttribute('data-student-name') : 'Student';
        var studentAdm = row ? (row.getAttribute('data-student-adm') || '') : '';
        var studentImg = row ? (row.getAttribute('data-student-img') || '') : '';

        var analyticsRaw = row ? row.getAttribute('data-student-analytics') : null;
        var analytics = {};
        if (analyticsRaw) {
            try { analytics = JSON.parse(analyticsRaw); } catch(e) { analytics = {}; }
        }

        openCommentModal(sid, field, studentName, studentAdm, studentImg, analytics);
    }

    // Grade popup functions
    function closeGradePop() {
        var gpop = document.getElementById('cbGradePopup');
        var backdrop = document.getElementById('cbPopupBackdrop');
        if (gpop) gpop.classList.remove('is-open');
        if (backdrop) backdrop.style.display = 'none';
    }

    function gradeClass(g) { return (g || '-').toLowerCase().replace(/[\s-]/g, ''); }

    function openGradePop(sid, name, triggerEl) {
        var an = SA[sid];
        var gpop = document.getElementById('cbGradePopup');
        var gpopBody = document.getElementById('gpopBody');
        var gpopTitle = document.getElementById('gpopTitle');
        if (!an || !gpop) return;

        gpopTitle.innerHTML = '<i class="ri-bar-chart-line me-1"></i>' + esc(name) + "'s Grade Breakdown";
        var grades = an.grades || [];
        var rows = grades.map(function(g) {
            var tgl = gradeClass(g.term_grade);
            var cgl = gradeClass(g.cum_grade);
            var tC = (g.term_score > 0 && g.term_score < 50) ? 'score-red' : '';
            var cC = (g.cum_score > 0 && g.cum_score < 50) ? 'score-red' : '';
            var tBadge = (g.term_grade && g.term_grade !== '-') ? '<span class="grade-badge g-' + tgl + '">' + esc(g.term_grade) + '</span>' : '—';
            var cBadge = (g.cum_grade && g.cum_grade !== '-') ? '<span class="grade-badge g-' + cgl + '">' + esc(g.cum_grade) + '</span>' : '—';
            return '<tr><td style="text-align:left;">' + esc(g.subject) + '<td><td class="' + tC + '">' + (g.term_score || '—') + '</td><td>' + tBadge + '</td><td class="' + cC + '">' + (g.cum_score || '—') + '</td><td>' + cBadge + '</td></tr>';
        }).join('');

        gpopBody.innerHTML = '<table class="gpop-table"><thead><tr><th>Subject</th><th style="color:#0891b2;">T.Score</th><th style="color:#0891b2;">T.Grade</th><th>C.Score</th><th>C.Grade</th></tr></thead><tbody>' + (rows || '<tr><td colspan="5" class="text-center text-muted py-2">No grades</td></tr>') + '</tbody></table>' +
            '<div class="gpop-summary">' +
            '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Term Total</div><div class="gpop-sum-val">' + (an.term_total || 0) + '</div></div>' +
            '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Cum Total</div><div class="gpop-sum-val">' + (an.cum_total || 0) + '</div></div>' +
            '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Obtainable</div><div class="gpop-sum-val">' + (an.total_obtainable || 0) + '</div></div>' +
            '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Cum %</div><div class="gpop-sum-val ' + ((an.cum_percentage || 0) < 50 ? 'score-red' : 'score-green') + '">' + (an.cum_percentage || 0) + '%</div></div>' +
            '</div>';

        var rect = triggerEl.getBoundingClientRect();
        var pw = 360, ph = 490;
        var top = rect.bottom + window.scrollY + 8;
        var left = rect.left + window.scrollX - (pw / 2) + (rect.width / 2);
        if (rect.bottom + ph > window.innerHeight) top = rect.top + window.scrollY - ph - 8;
        if (left < 8) left = 8;
        if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
        gpop.style.top = top + 'px';
        gpop.style.left = left + 'px';
        gpop.classList.add('is-open');
        var backdrop = document.getElementById('cbPopupBackdrop');
        if (backdrop) backdrop.style.display = 'block';
    }

    function doSaveAll() {
        var fd = new FormData(document.getElementById('commentsForm'));
        fd.append('_token', CSRF);
        fd.append('_method', 'PATCH');

        var sigFile = document.getElementById('signatureFile');
        if (sigFile && sigFile.files && sigFile.files[0]) {
            fd.append('signature', sigFile.files[0]);
        }

        var saveBtn = document.getElementById('saveBtn');
        var savingText = document.getElementById('savingText');
        var origHtml = saveBtn.innerHTML;

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="spin ri-loader-4-line"></i> Saving…';
        if (savingText) savingText.style.display = 'inline-flex';

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: fd,
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                toast(data.message || 'Saved successfully!', 'success');
                refreshCommentStatus();
            } else {
                toast(data.message || 'Save failed.', 'error');
            }
        })
        .catch(function(err) {
            console.error(err);
            toast('Error: ' + err.message, 'error');
        })
        .finally(function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = origHtml;
            if (savingText) savingText.style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Column toggle
        document.querySelectorAll('.toggle-chip').forEach(function(chip) {
            chip.addEventListener('click', function() {
                var key = this.getAttribute('data-colkey');
                var show = this.classList.toggle('active') ? '' : 'none';
                document.querySelectorAll('.cbcol-' + key).forEach(function(el) { el.style.display = show; });
                var mobileClass = { guidance: '.mobile-col-guidance', activities: '.mobile-col-activities', absence: '.mobile-col-absence' }[key];
                if (mobileClass) document.querySelectorAll(mobileClass).forEach(function(el) { el.style.display = show; });
            });
        });

        // Search
        var searchEl = document.getElementById('searchInput');
        if (searchEl) {
            searchEl.addEventListener('input', function() {
                var q = this.value.toLowerCase().trim();
                document.querySelectorAll('.cb-student-row').forEach(function(row) {
                    var key = (row.getAttribute('data-searchkey') || '').toLowerCase();
                    row.style.display = (!q || key.includes(q)) ? '' : 'none';
                });
            });
        }

        // Image zoom
        var imgModal = null;
        var imgModalEl = document.getElementById('cbImgZoomModal');
        if (imgModalEl && typeof bootstrap !== 'undefined') {
            imgModal = new bootstrap.Modal(imgModalEl);
        }

        document.addEventListener('click', function(e) {
            var trigger = e.target.closest('.cb-avatar-trigger');
            if (!trigger) return;
            var img = trigger.querySelector('img');
            var imgUrl = img ? img.src : null;
            var name = trigger.getAttribute('data-name') || 'Student';
            var adm = trigger.getAttribute('data-adm') || '';
            var cls = trigger.getAttribute('data-class') || '';
            var gender = trigger.getAttribute('data-gender') || '';
            var initials = trigger.getAttribute('data-initials') || name.substring(0,2).toUpperCase();

            var zoomedImg = document.getElementById('cbZoomedImg');
            var zoomedName = document.getElementById('cbZoomedName');
            var zoomedMeta = document.getElementById('cbZoomedMeta');

            zoomedName.textContent = name;
            zoomedMeta.innerHTML = (adm ? '<i class="ri-id-card-line me-1"></i>' + esc(adm) : '') +
                (cls ? ' &nbsp;|&nbsp; <i class="ri-building-line me-1"></i>' + esc(cls) : '') +
                (gender ? ' &nbsp;|&nbsp; ' + esc(gender) : '');

            if (imgUrl && imgUrl !== 'null' && imgUrl !== '') {
                zoomedImg.src = imgUrl;
            } else {
                var canvas = document.createElement('canvas');
                canvas.width = canvas.height = 400;
                var ctx = canvas.getContext('2d');
                var grad = ctx.createLinearGradient(0, 0, 400, 400);
                grad.addColorStop(0, '#0d9488');
                grad.addColorStop(1, '#0ea5e9');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, 400, 400);
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 150px "DM Sans",Arial,sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(initials.substring(0, 2).toUpperCase(), 200, 200);
                zoomedImg.src = canvas.toDataURL();
            }
            if (imgModal) imgModal.show();
        });

        // Grade popup
        var gpopCloseBtn = document.getElementById('gpopCloseBtn');
        if (gpopCloseBtn) gpopCloseBtn.addEventListener('click', closeGradePop);
        var backdrop = document.getElementById('cbPopupBackdrop');
        if (backdrop) backdrop.addEventListener('click', closeGradePop);
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeGradePop(); });

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.grade-trigger-btn');
            if (!btn) return;
            e.stopPropagation();
            var sid = btn.getAttribute('data-sid');
            var name = btn.getAttribute('data-sname');
            closeGradePop();
            openGradePop(sid, name, btn);
        });

        // Stats
        (function() {
            var vals = Object.values(SA);
            if (vals.length) {
                var avgPct = Math.round(vals.reduce(function(s, d) { return s + (d.cum_percentage || 0); }, 0) / vals.length);
                var prEl = document.getElementById('statPassRate');
                if (prEl) prEl.textContent = avgPct + '%';
            }
            var topAvg = -1, topName = '—';
            document.querySelectorAll('.cb-student-row[data-student-id]').forEach(function(row) {
                var sid = row.getAttribute('data-student-id');
                if (!sid || !SA[sid]) return;
                var avg = SA[sid].cum_average || 0;
                if (avg > topAvg) {
                    topAvg = avg;
                    var name = row.getAttribute('data-student-name') || '';
                    var p = name.trim().split(' ');
                    topName = p[0] + (p[1] ? ' ' + p[1][0] + '.' : '');
                }
            });
            var topEl = document.getElementById('statTop');
            if (topEl) topEl.textContent = topName;
        })();

        // Input listeners
        var fieldSelectors = [
            '.desk-teacher', '.desk-guidance', '.desk-activities', '.desk-absence',
            '.mob-teacher', '.mob-guidance', '.mob-activities', '.mob-absence',
        ];
        fieldSelectors.forEach(function(sel) {
            document.querySelectorAll(sel).forEach(function(inp) {
                inp.addEventListener('input', onInputChange);
            });
        });

        // Modal triggers
        attachModalTriggers();

        // Modal buttons
        var loadBtn = document.getElementById('btnLoadPastComments');
        if (loadBtn) loadBtn.addEventListener('click', loadPastComments);

        var modalSaveBtn = document.getElementById('modalSaveBtn');
        if (modalSaveBtn) modalSaveBtn.addEventListener('click', saveCommentFromModal);

        // Save all button
        var saveBtn = document.getElementById('saveBtn');
        if (saveBtn) saveBtn.addEventListener('click', doSaveAll);

        // Initial refresh
        refreshCommentStatus();
    });
})();
</script>
@endsection
