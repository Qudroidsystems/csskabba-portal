@extends('layouts.master')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
/* ─────────────────────────────────────────────
   CSS VARIABLES
───────────────────────────────────────────── */
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
body { font-family: 'DM Sans', sans-serif; background: #f1f5f9; }

/* ── Keyframes ── */
@keyframes fadeInUp    { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInDown  { from { opacity:0; transform:translateY(-22px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInLeft  { from { opacity:0; transform:translateX(-22px); } to { opacity:1; transform:translateX(0); } }
@keyframes fadeInRight { from { opacity:0; transform:translateX(22px); }  to { opacity:1; transform:translateX(0); } }
@keyframes scaleIn     { from { opacity:0; transform:scale(.88); } to { opacity:1; transform:scale(1); } }
@keyframes pulse       { 0%,100% { transform:scale(1); } 50% { transform:scale(1.06); } }
@keyframes shimmer     { 0% { background-position:-800px 0; } 100% { background-position:800px 0; } }
@keyframes slideInRight{ from { transform:translateX(110%); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes spin        { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
@keyframes popIn       { 0% { opacity:0; transform:scale(.7) translateY(12px); } 60% { transform:scale(1.04) translateY(-3px); } 100% { opacity:1; transform:scale(1) translateY(0); } }
@keyframes floatUp     { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-6px); } }
@keyframes glowPulse   { 0%,100% { box-shadow:0 0 0 0 rgba(13,148,136,.4); } 50% { box-shadow:0 0 0 8px rgba(13,148,136,0); } }
@keyframes progressFill{ from { width:0; } }
@keyframes rowSlide    { from { opacity:0; transform:translateX(-12px); } to { opacity:1; transform:translateX(0); } }
@keyframes countUp     { from { opacity:0; transform:scale(.6); } to { opacity:1; transform:scale(1); } }
@keyframes backdropIn  { from { opacity:0; } to { opacity:1; } }
@keyframes barGrow     { from { transform:scaleX(0); transform-origin:left; } to { transform:scaleX(1); transform-origin:left; } }

.spin { animation:spin .8s linear infinite; }

/* ── Hero ── */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    animation: fadeInDown .6s cubic-bezier(.22,1,.36,1) both;
}
.cb-hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:280px; height:280px;
    background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 70%);
    border-radius:50%; animation:floatUp 6s ease-in-out infinite;
}
.cb-hero::after {
    content:''; position:absolute; bottom:-60px; left:-60px;
    width:200px; height:200px;
    background:radial-gradient(circle,rgba(255,255,255,.05) 0%,transparent 70%);
    border-radius:50%; animation:floatUp 8s ease-in-out infinite reverse;
}
.cb-hero h1 { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; color:#fff; margin:0 0 8px; }
.cb-hero p  { font-size:13px; color:rgba(255,255,255,.72); margin:0; }
.cb-hero .meta-pills { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
.cb-meta-pill {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    border-radius:20px; padding:4px 14px; font-size:12px; font-weight:600; color:#fff;
    display:inline-flex; align-items:center; gap:5px;
    transition:all .3s ease; animation:fadeInUp .5s ease both;
}
.cb-meta-pill:nth-child(1){animation-delay:.15s}
.cb-meta-pill:nth-child(2){animation-delay:.25s}
.cb-meta-pill:nth-child(3){animation-delay:.35s}
.cb-meta-pill:hover { background:rgba(255,255,255,.22); transform:translateY(-2px); }
.btn-back {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    border-radius:10px; padding:8px 18px; color:#fff; font-size:12px; font-weight:600;
    text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    transition:all .3s ease; animation:fadeInRight .5s ease .2s both;
}
.btn-back:hover { background:rgba(255,255,255,.22); color:#fff; transform:translateX(-4px); }

/* ── Stats Cards ── */
.cb-stat {
    background:var(--cb-white); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); padding:20px 22px;
    position:relative; overflow:hidden;
    transition:all .35s cubic-bezier(.22,1,.36,1);
    animation:scaleIn .5s cubic-bezier(.22,1,.36,1) both;
}
.cb-stat:nth-child(1){animation-delay:.08s}
.cb-stat:nth-child(2){animation-delay:.16s}
.cb-stat:nth-child(3){animation-delay:.24s}
.cb-stat:nth-child(4){animation-delay:.32s}
.cb-stat:hover { transform:translateY(-6px) scale(1.01); box-shadow:var(--cb-shadow-lg); }
.cb-stat .stat-accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--cb-radius) var(--cb-radius) 0 0; }
.cb-stat .stat-value  { font-size:30px; font-weight:700; color:var(--cb-navy); line-height:1; margin-top:8px; animation:countUp .6s ease both; animation-delay:.4s; }
.cb-stat .stat-label  { font-size:12px; color:var(--cb-muted); margin-top:5px; font-weight:500; }
.cb-stat .stat-ico    { font-size:36px; opacity:.08; position:absolute; right:16px; top:50%; transform:translateY(-50%); }

/* ── Meta grid ── */
.meta-grid {
    display:flex; border:1px solid var(--cb-border); background:var(--cb-surface);
    border-radius:8px; overflow:hidden; margin-bottom:14px; animation:fadeInUp .5s ease;
}
.meta-cell { flex:1; padding:10px 14px; border-right:1px solid var(--cb-border); transition:all .2s ease; }
.meta-cell:last-child { border-right:none; }
.meta-cell:hover { background:#e8f0fe; transform:translateY(-2px); }
.meta-label { font-size:10px; color:var(--cb-muted); text-transform:uppercase; letter-spacing:.4px; display:block; }
.meta-value { font-size:13px; font-weight:700; color:var(--cb-navy); }

/* ── Grade key ── */
.grade-key {
    display:flex; align-items:center; border:1px solid var(--cb-border);
    padding:6px 14px; background:#fafafa; border-radius:8px; margin-bottom:14px;
    flex-wrap:wrap; gap:6px; animation:fadeInLeft .5s ease;
}

/* ── Card & Table ── */
.cb-card {
    background:var(--cb-white); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); box-shadow:var(--cb-shadow);
    overflow:visible; animation:fadeInUp .5s ease .2s both;
}
.cb-card-header {
    padding:18px 24px; border-bottom:1px solid var(--cb-border);
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
    background:linear-gradient(to right,#f8fafc,#f0fdf9);
    border-radius:var(--cb-radius) var(--cb-radius) 0 0;
}

/* ── Broadsheet table ── */
.broadsheet-table { width:100%; border-collapse:collapse; font-size:11px; background:white; border:1.5px solid var(--cb-navy); }
.broadsheet-table thead tr.subject-header th {
    background:var(--cb-navy); color:#fff; text-align:center;
    padding:7px 4px; border:0.5px solid rgba(37,99,235,.35);
    font-weight:600; font-size:11.5px; white-space:nowrap;
    position:sticky; top:0; z-index:10;
}
.broadsheet-table thead tr.subject-header th.student-col { background:#0f2040; text-align:left; padding-left:6px; }
.broadsheet-table thead tr.subject-header th.subj-name-hdr {
    background:#163562; border-left:1.5px solid #2563eb;
    font-size:10px; white-space:normal; word-break:break-word; min-width:60px;
}
.broadsheet-table thead tr.assessment-header th {
    background:#1a3d6a; color:#a8d4ef; text-align:center;
    padding:5px 3px; border:0.5px solid rgba(37,99,235,.2);
    font-size:10px; white-space:nowrap;
}
.broadsheet-table thead tr.assessment-header th.sub-boundary { border-left:1.5px solid #2563eb; }

.broadsheet-table tbody tr {
    animation:rowSlide .4s ease both;
    transition:all .25s ease;
}
.broadsheet-table tbody tr:nth-child(odd)  { background:#ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background:#f0f4fa; }
.broadsheet-table tbody tr:hover { background-color:#e8f0fe !important; transform:scale(1.005); box-shadow:0 2px 8px rgba(0,0,0,.08); }
.broadsheet-table tbody tr:nth-child(1){animation-delay:.05s}
.broadsheet-table tbody tr:nth-child(2){animation-delay:.08s}
.broadsheet-table tbody tr:nth-child(3){animation-delay:.11s}
.broadsheet-table tbody tr:nth-child(4){animation-delay:.14s}
.broadsheet-table tbody tr:nth-child(5){animation-delay:.17s}
.broadsheet-table tbody tr:nth-child(n+6){animation-delay:.20s}

.broadsheet-table tbody td {
    padding:5px 4px; border:0.5px solid #c5d3e8;
    text-align:center; vertical-align:middle;
    white-space:nowrap; font-size:11px; transition:all .2s ease;
}
.broadsheet-table tbody td.student-info-cell {
    text-align:left; padding-left:8px; font-weight:600;
    position:sticky; left:0; background:inherit; z-index:5; min-width:200px;
}
.score-cell { transition:all .2s ease; }
.score-cell:hover { transform:scale(1.05); filter:brightness(.95); }

/* ── Position cells ── */
.pos-term-cell {
    background:#fef3c7 !important; color:#92400e; font-weight:700;
    border-left:1.5px solid #f59e0b !important; font-size:11px;
}
.pos-cum-cell {
    background:#dbeafe !important; color:#1e40af; font-weight:700;
    border-left:1.5px solid #3b82f6 !important; font-size:11px;
}

/* ── Grade colors ── */
.grade-a1 { background:#dcfce7 !important; color:#166534; font-weight:700; }
.grade-b2 { background:#dbeafe !important; color:#1e40af; }
.grade-b3 { background:#e0eeff !important; color:#1e40af; }
.grade-c4 { background:#fef9c3 !important; color:#854d0e; }
.grade-c5 { background:#fef3c7 !important; color:#92400e; }
.grade-c6 { background:#fde68a !important; color:#78350f; }
.grade-d7 { background:#ffedd5 !important; color:#9a3412; }
.grade-e8 { background:#fed7aa !important; color:#9a3412; }
.grade-f9 { background:#fee2e2 !important; color:#991b1b; font-weight:700; }

/* ── Score colors ── */
.score-red   { color:#dc2626 !important; font-weight:700; }
.score-amber { color:#d97706 !important; font-weight:700; }
.score-green { color:#16a34a !important; font-weight:700; }

/* ── GPA cells ── */
.gpa-cell { background:#eff6ff !important; color:#1e3a8a; font-weight:700; border-left:1.5px solid #3b82f6 !important; transition:all .2s ease; }
.gpa-cell:hover { background:#dbeafe !important; transform:scale(1.02); }

/* ── Dual position badge ── */
.pos-dual {
    display:inline-flex; flex-direction:column; align-items:center;
    gap:2px; cursor:pointer;
}
.pos-dual .pos-term-lbl {
    font-size:9px; font-weight:700; color:#92400e;
    background:#fef3c7; border-radius:4px; padding:1px 5px; line-height:1.4;
    min-width:28px; text-align:center;
}
.pos-dual .pos-cum-lbl {
    font-size:9px; font-weight:700; color:#1e40af;
    background:#dbeafe; border-radius:4px; padding:1px 5px; line-height:1.4;
    min-width:28px; text-align:center;
}

/* ── Student avatar ── */
.cb-avatar {
    width:30px; height:30px; border-radius:50%; overflow:hidden;
    border:2px solid var(--cb-border); flex-shrink:0;
    transition:all .3s cubic-bezier(.22,1,.36,1);
    display:inline-flex; align-items:center; justify-content:center;
}
.cb-avatar:hover { border-color:var(--cb-teal); transform:scale(1.12); box-shadow:0 3px 10px rgba(13,148,136,.25); }
.cb-avatar img { width:100%; height:100%; object-fit:cover; }
.cb-avatar-initials { background:linear-gradient(135deg,var(--cb-teal),var(--cb-sky)); color:#fff; font-size:11px; font-weight:700; }

/* ── Eye / Grade trigger button ── */
.grade-trigger-btn {
    background:none; border:none; cursor:pointer;
    color:var(--cb-sky); font-size:17px; padding:5px 8px; border-radius:8px;
    transition:all .25s ease; position:relative; z-index:1;
}
.grade-trigger-btn:hover {
    color:#fff; background:var(--cb-teal); transform:scale(1.15);
    box-shadow:0 3px 10px rgba(13,148,136,.4); animation:glowPulse .8s ease infinite;
}

/* ── Stats rows ── */
.stats-row td { background:var(--cb-navy) !important; color:white; font-weight:700; padding:5px 4px; text-align:center; border:0.5px solid #163785; font-size:11px; }
.stats-row td.stats-label { text-align:left; padding-left:8px; font-size:10px; }
.stats-hi td { background:#0a2240 !important; }
.stats-lo td { background:#111c2a !important; }

/* ── Toolbar / search ── */
.cb-search { position:relative; }
.cb-search input {
    width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--cb-border);
    border-radius:10px; font-size:13px; background:var(--cb-surface);
    font-family:'DM Sans',sans-serif; transition:all .25s ease;
}
.cb-search input:focus { border-color:var(--cb-teal); outline:none; box-shadow:0 0 0 3px rgba(13,148,136,.1); }
.cb-search i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--cb-muted); pointer-events:none; }

/* ── Toast ── */
.cb-toast {
    position:fixed; bottom:80px; right:24px; min-width:260px; z-index:99999;
    padding:12px 16px; border-radius:12px; display:flex; align-items:center; gap:10px;
    font-size:13px; font-weight:600; box-shadow:var(--cb-shadow-lg);
    animation:slideInRight .3s cubic-bezier(.22,1,.36,1);
}
.cb-toast-success { background:#ecfdf5; border:1.5px solid #86efac; color:#15803d; }
.cb-toast-error   { background:#fff1f2; border:1.5px solid #fca5a5; color:#be123c; }
.cb-toast-info    { background:#eff6ff; border:1.5px solid #93c5fd; color:#1d4ed8; }
.cb-toast-warning { background:#fffbeb; border:1.5px solid #fcd34d; color:#92400e; }

/* ── Performance (grade) popup ── */
#cbGradePopup {
    display:none; position:fixed; z-index:99999;
    background:var(--cb-white); border:2px solid var(--cb-teal);
    border-radius:16px; box-shadow:0 20px 60px rgba(15,35,66,.22);
    width:520px; max-height:620px; overflow:hidden; flex-direction:column;
}
#cbGradePopup.is-open { display:flex; animation:popIn .28s cubic-bezier(.22,1,.36,1); }
.gpop-hdr {
    background:linear-gradient(135deg,var(--cb-navy),var(--cb-teal));
    color:#fff; padding:14px 18px; border-radius:14px 14px 0 0;
    font-weight:700; font-size:14px;
    display:flex; justify-content:space-between; align-items:center; flex-shrink:0;
}
.gpop-close-btn { background:rgba(255,255,255,.18); border:none; color:#fff; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; transition:all .25s ease; }
.gpop-close-btn:hover { background:rgba(255,255,255,.4); transform:rotate(90deg) scale(1.1); }
.gpop-body { padding:16px; overflow-y:auto; flex:1; }

/* ── Performance summary card inside popup ── */
.gpop-perf-strip {
    background:linear-gradient(135deg,var(--cb-navy),#1e5f74);
    border-radius:10px; padding:12px 16px; color:#fff; margin-bottom:14px;
}
.gpop-perf-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:8px; }
.gpop-perf-item { text-align:center; background:rgba(255,255,255,.1); border-radius:8px; padding:8px; transition:all .2s ease; }
.gpop-perf-item:hover { background:rgba(255,255,255,.2); transform:scale(1.03); }
.gpop-perf-lbl { font-size:9px; opacity:.8; text-transform:uppercase; letter-spacing:.4px; }
.gpop-perf-val { font-size:15px; font-weight:700; margin-top:2px; }

/* Progress bars inside popup */
.pct-bar-wrap { background:rgba(255,255,255,.15); border-radius:99px; height:6px; overflow:hidden; }
.pct-bar { height:100%; border-radius:99px; background:#22c55e; transition:background .8s ease; animation:progressFill .8s ease both; }

.gpop-legend { display:flex; align-items:center; gap:12px; margin-bottom:10px; padding:6px 10px; background:var(--cb-surface); border-radius:8px; border:1px solid var(--cb-border); flex-wrap:wrap; }
.gpop-legend-item { display:flex; align-items:center; gap:4px; font-size:10px; font-weight:700; color:var(--cb-muted); }
.gpop-legend-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.gpop-legend-dot.t { background:#0ea5e9; }
.gpop-legend-dot.c { background:var(--cb-navy); }

.gpop-scroll { max-height:260px; overflow-y:auto; border:1px solid var(--cb-border); border-radius:10px; }
.gpop-table { width:100%; border-collapse:collapse; font-size:12px; table-layout:fixed; }
.gpop-table thead th { background:var(--cb-navy); color:#fff; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:9px 8px; border-right:1px solid rgba(255,255,255,.08); text-align:center; position:sticky; top:0; z-index:2; }
.gpop-table thead th:first-child { text-align:left; padding-left:12px; width:40%; }
.gpop-table tbody td { padding:8px 6px; border-bottom:1px solid #f1f5f9; font-weight:500; text-align:center; vertical-align:middle; }
.gpop-table tbody td:first-child { text-align:left; font-weight:600; color:var(--cb-navy); padding-left:12px; }
.gpop-table tbody tr:hover td { background:#f0fdf9; }

.score-pair { display:flex; flex-direction:column; gap:2px; }
.score-cell-inner { display:flex; align-items:center; justify-content:center; gap:3px; padding:2px 4px; border-radius:4px; font-size:11px; font-weight:700; }
.score-cell-inner.term { background:rgba(14,165,233,.08); border-left:2px solid #0ea5e9; }
.score-cell-inner.cum  { background:rgba(15,35,66,.06);   border-left:2px solid var(--cb-navy); }

.gpop-summary { background:linear-gradient(135deg,#f8fafc,#f0fdf9); border-radius:12px; padding:12px; margin-top:14px; display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.gpop-sum-item { text-align:center; padding:10px 6px; border-radius:10px; background:white; transition:all .2s ease; border:1px solid #e2e8f0; }
.gpop-sum-item:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.09); border-color:var(--cb-teal); }
.gpop-sum-lbl { font-size:9px; color:var(--cb-muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; font-weight:600; line-height:1.4; }
.gpop-sum-val { font-size:16px; font-weight:800; color:var(--cb-navy); }
.gpop-sum-val.score-red   { color:#dc2626; }
.gpop-sum-val.score-amber { color:#d97706; }
.gpop-sum-val.score-green { color:#16a34a; }

#cbPopupBackdrop { display:none; position:fixed; inset:0; z-index:99998; background:rgba(0,0,0,.3); animation:backdropIn .2s ease; }

/* ── Tooltips ── */
[data-tooltip] { position:relative; cursor:pointer; }
[data-tooltip]:before {
    content:attr(data-tooltip); position:absolute; bottom:100%; left:50%;
    transform:translateX(-50%); background:#1e293b; color:white;
    padding:4px 8px; border-radius:6px; font-size:10px; white-space:nowrap;
    opacity:0; visibility:hidden; transition:all .2s ease;
    pointer-events:none; z-index:1000;
}
[data-tooltip]:hover:before { opacity:1; visibility:visible; transform:translateX(-50%) translateY(-5px); }

/* ── Subject performance summary card ── */
.subj-summary-card {
    background:var(--cb-white); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); box-shadow:var(--cb-shadow);
    animation:fadeInUp .5s ease .4s both;
}
.subj-summary-card .card-header-custom {
    background:var(--cb-navy); color:#fff; padding:14px 20px;
    border-radius:var(--cb-radius) var(--cb-radius) 0 0;
    font-weight:700; font-size:14px;
}

/* ── School header ── */
.school-header-bar {
    background:linear-gradient(135deg,var(--cb-navy) 0%,#2563eb 100%);
    border-radius:10px; padding:18px 24px; margin-bottom:16px; color:white;
    animation:fadeInUp .6s ease;
}

/* ── BF info note ── */
.bf-note {
    display:inline-flex; align-items:center; gap:4px;
    background:#fffbeb; border:1px solid #fcd34d; color:#92400e;
    border-radius:6px; padding:3px 8px; font-size:10px; font-weight:600;
}

/* ── Print ── */
@media print {
    .no-print { display:none !important; }
    body { background:#fff !important; font-size:10px; }
    .cb-hero::before, .cb-hero::after { display:none; }
    .cb-hero { animation:none !important; }
    .cb-stat, .cb-card { box-shadow:none !important; animation:none !important; }
    .broadsheet-table tbody tr { animation:none !important; }
    #cbGradePopup, #cbPopupBackdrop { display:none !important; }
    @page { margin:1.5cm 1.2cm; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

{{-- ── Hero ── --}}
<div class="cb-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1><i class="ri-table-alt-line me-2"></i>Class Broadsheet</h1>
            <p>Academic performance overview — scores, grades, positions and analytics for every student.</p>
            <div class="meta-pills">
                <span class="cb-meta-pill"><i class="ri-building-line"></i>{{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
                <span class="cb-meta-pill"><i class="ri-calendar-event-line"></i>{{ $schoolsession->session ?? '-' }}</span>
                <span class="cb-meta-pill"><i class="ri-bookmark-line"></i>{{ $schoolterm->term ?? '-' }}</span>
                @if(!empty($is_combined))
                    <span class="cb-meta-pill" style="background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.4);"><i class="ri-links-line"></i>Combined Arms</span>
                @endif
            </div>
        </div>
        <a href="javascript:history.back()" class="btn-back"><i class="ri-arrow-left-line"></i> Back</a>
    </div>
</div>

{{-- ── Stats Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
            <div class="stat-ico"><i class="ri-group-line"></i></div>
            <div class="stat-value" id="statTotalStudents">{{ $totalStudents }}</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-book-open-line"></i></div>
            <div class="stat-value text-info" id="statTotalSubjects">{{ count($subjects) }}</div>
            <div class="stat-label">Subjects</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-green),#4ade80);"></div>
            <div class="stat-ico"><i class="ri-percent-line"></i></div>
            <div class="stat-value text-success" id="statAvgPct">0%</div>
            <div class="stat-label">Avg % (Cumulative)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-amber),#fcd34d);"></div>
            <div class="stat-ico"><i class="ri-award-line"></i></div>
            <div class="stat-value text-warning" id="statTopPerformer" style="font-size:16px;">—</div>
            <div class="stat-label">Top Performer (Cum)</div>
        </div>
    </div>
</div>

{{-- ── School Header ── --}}
<div class="school-header-bar">
    <div class="d-flex align-items-center">
        @if(!empty($school_logo_base64))
            <img src="{{ $school_logo_base64 }}" alt="Logo"
                 style="width:65px;height:65px;object-fit:contain;border-radius:50%;border:2px solid rgba(255,255,255,.4);margin-right:18px;animation:floatUp 3s ease-in-out infinite;">
        @endif
        <div class="flex-grow-1 text-center">
            <h4 class="mb-1 fw-bold text-uppercase text-white">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</h4>
            @if(!empty($schoolInfo->school_address))
                <p class="mb-1 opacity-75 text-white" style="font-size:13px;">{{ $schoolInfo->school_address }}</p>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <p class="mb-0 fst-italic opacity-75 text-white" style="font-size:12px;">"{{ $schoolInfo->school_motto }}"</p>
            @endif
        </div>
    </div>
</div>

{{-- ── Title Strip ── --}}
<div style="background:var(--cb-navy);color:white;text-align:center;padding:10px;font-size:15px;font-weight:700;letter-spacing:1.5px;border-radius:8px;margin-bottom:14px;animation:fadeInUp .5s ease;">
    CLASS ACADEMIC BROADSHEET
    @if(!empty($is_combined))<span style="font-size:11px;opacity:.7;font-weight:400;margin-left:10px;">— Combined Arms</span>@endif
</div>

{{-- ── Meta Grid ── --}}
<div class="meta-grid">
    <div class="meta-cell" style="animation:fadeInLeft .5s ease .05s both;">
        <span class="meta-label">Class</span>
        <span class="meta-value">{{ ($schoolclass->schoolclass ?? '-') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
    </div>
    <div class="meta-cell" style="animation:fadeInLeft .5s ease .10s both;">
        <span class="meta-label">Academic Session</span>
        <span class="meta-value">{{ $schoolsession->session ?? '-' }}</span>
    </div>
    <div class="meta-cell" style="animation:fadeInLeft .5s ease .15s both;">
        <span class="meta-label">Term</span>
        <span class="meta-value">{{ $schoolterm->term ?? '-' }}</span>
    </div>
    <div class="meta-cell" style="animation:fadeInLeft .5s ease .20s both;">
        <span class="meta-label">Generated</span>
        <span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span>
    </div>
</div>

{{-- ── Grade Key ── --}}
<div class="grade-key">
    <strong style="color:var(--cb-navy);margin-right:8px;font-size:12px;">GRADING SCALE:</strong>
    @php
    $gradeKey = [
        'A1'=>['75-100','#16a34a'],'B2'=>['70-74','#1d4ed8'],'B3'=>['65-69','#2563eb'],
        'C4'=>['60-64','#d97706'],'C5'=>['55-59','#b45309'],'C6'=>['50-54','#92400e'],
        'D7'=>['45-49','#ea580c'],'E8'=>['40-44','#c2410c'],'F9'=>['0-39','#dc2626'],
    ];
    @endphp
    @foreach($gradeKey as $grade => $info)
        <span style="animation:scaleIn .4s ease {{ $loop->index * 0.03 }}s both;display:inline-block;">
            <span class="badge" style="background:{{ $info[1] }};font-size:11px;border-radius:12px;padding:3px 9px;">{{ $grade }} ({{ $info[0] }})</span>
        </span>
    @endforeach
    <span class="text-muted ms-2" style="font-size:11px;">
        <strong>BF</strong>=Prev-Term Cum &nbsp;
        <strong>CUM</strong>=(BF+Total)÷2 &nbsp;
        <em style="color:#92400e;">(If BF=0, Cum=Total)</em> &nbsp;
        <span class="bf-note"><i class="ri-arrow-left-right-line"></i> T-POS=Term Position</span> &nbsp;
        <span style="background:#dbeafe;color:#1e40af;border-radius:4px;padding:1px 5px;font-weight:700;">C-POS</span>=Cum Position
    </span>
</div>

{{-- ── Toolbar ── --}}
<div class="cb-card mb-3 no-print" style="animation:fadeInUp .4s ease .1s both;">
    <div class="cb-card-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="cb-search" style="max-width:260px;">
                <i class="ri-search-line"></i>
                <input type="text" id="searchStudent" placeholder="Search name or admission no…">
            </div>
            <select class="form-select form-select-sm" id="locateStudent" style="max-width:220px;border-radius:8px;border:1.5px solid var(--cb-border);font-size:12px;">
                <option value="">🔍 Quick Locate…</option>
                <option value="top5">🏆 Top 5 (by Cum)</option>
                <option value="top10">⭐ Top 10 (by Cum)</option>
                <option value="top5term">📊 Top 5 (by Term)</option>
                <option value="failures">⚠️ Students with F9</option>
                <option value="below_avg">📉 Below Class Average</option>
                <option disabled>──────────</option>
                @foreach($studentRows as $student)
                    <option value="student_{{ $student['id'] }}">👤 {{ $student['lastname'] }}, {{ $student['firstname'] }} ({{ $student['admissionno'] }})</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()" style="border-radius:8px;">
                <i class="ri-printer-line me-1"></i>Print
            </button>
            <button class="btn btn-sm" onclick="scrollToTop()" style="background:var(--cb-teal);color:#fff;border-radius:8px;border:none;">
                <i class="ri-arrow-up-line me-1"></i>Top
            </button>
        </div>
    </div>
</div>

{{-- ── Grade Popup ── --}}
<div id="cbPopupBackdrop"></div>
<div id="cbGradePopup">
    <div class="gpop-hdr">
        <span id="gpopTitle"><i class="ri-bar-chart-line me-1"></i>Performance Summary</span>
        <button type="button" class="gpop-close-btn" id="gpopCloseBtn">&times;</button>
    </div>
    <div class="gpop-body" id="gpopBody"></div>
</div>

{{-- ── Main Broadsheet Table ── --}}
@php
    $selected       = $selectedColumns ?? [];
    $showAdmNo      = empty($selected) || in_array('admission_no',     $selected);
    $showGender     = in_array('gender',                                $selected);
    $showTotal      = empty($selected) || in_array('total',            $selected);
    $showBF         = empty($selected) || in_array('bf',               $selected);
    $showCum        = empty($selected) || in_array('cum',              $selected);
    $showGrade      = empty($selected) || in_array('grade',            $selected);
    // Distinct term vs cum positions
    $showPosTerm    = empty($selected) || in_array('position_term',    $selected);
    $showPosCum     = empty($selected) || in_array('position_cum',     $selected);
    $showAvg        = in_array('class_average',                         $selected);
    $showRemark     = in_array('remark',                                $selected);
    $showGPA        = in_array('gpa',                                   $selected);
    $showCGPA       = in_array('cgpa',                                  $selected);
    $showGPAGrade   = in_array('gpa_grade',                             $selected);
    $showNumSub     = in_array('num_subjects',                          $selected);
    $showTotalGP    = in_array('total_grade_points',                    $selected);

    $activeAssessments = $assessments->filter(fn($a) =>
        empty($selected) || in_array('assessment_' . $a->id, $selected)
    );

    $gradeColors = [
        'A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3',
        'C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6',
        'D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9','-'=>'',
    ];

    // How many per-subject columns are visible
    $subColspan = $activeAssessments->count();
    if($showTotal)   $subColspan++;
    if($showBF)      $subColspan++;
    if($showCum)     $subColspan++;
    if($showGrade)   $subColspan++;
    if($showPosTerm) $subColspan++;
    if($showPosCum)  $subColspan++;
    if($showAvg)     $subColspan++;
    if($showRemark)  $subColspan++;
    $subColspan = max(1, $subColspan);

    $frozenCols  = 2 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0);
    $gpaColspan  = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);
@endphp

<div class="cb-card mb-4">
    <div class="cb-card-header">
        <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--cb-navy);">
            <i class="ri-table-alt-line me-1" style="color:var(--cb-teal)"></i>
            Student Performance &amp; Scores
            <span class="badge ms-2" style="background:var(--cb-teal);color:#fff;font-size:11px;border-radius:20px;padding:3px 10px;">{{ $totalStudents }} Students</span>
        </h5>
        <div style="font-size:11px;color:var(--cb-muted);">
            <span class="bf-note"><i class="ri-information-line"></i> BF = Previous Term's Cum Score &nbsp;|&nbsp; CUM = (BF + Total) ÷ 2</span>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="broadsheet-table" id="broadsheetTable">
            <thead>
                {{-- Row 1: Subject names + frozen cols --}}
                <tr class="subject-header">
                    <th class="student-col" rowspan="2" style="width:36px;">#</th>
                    <th class="student-col" rowspan="2" style="width:72px;">Position<br><small style="font-size:8px;font-weight:400;opacity:.75;">T-Pos / C-Pos</small></th>
                    @if($showAdmNo)
                        <th class="student-col" rowspan="2" style="min-width:72px;">Adm. No</th>
                    @endif
                    <th class="student-col" rowspan="2" style="min-width:180px;text-align:left;padding-left:8px;">Student Name</th>
                    @if($showGender)
                        <th class="student-col" rowspan="2" style="width:38px;">Sex</th>
                    @endif

                    @foreach($subjects as $subId => $subInfo)
                        <th class="subj-name-hdr" colspan="{{ $subColspan }}">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))
                                <br><small style="opacity:.75;font-size:9px;">({{ $subInfo['subject_code'] }})</small>
                            @endif
                        </th>
                    @endforeach

                    {{-- Analytics column --}}
                    <th class="subj-name-hdr" colspan="1" style="background:#0a2240;border-left:2px solid var(--cb-teal);min-width:46px;">
                        <i class="ri-eye-line" style="font-size:13px;"></i>
                    </th>

                    @if($gpaColspan > 0)
                        <th colspan="{{ $gpaColspan }}" style="background:#0a1e38;border-left:2px solid #3b82f6;font-size:10px;">GPA METRICS</th>
                    @endif
                </tr>

                {{-- Row 2: Assessment / score sub-headers --}}
                <tr class="assessment-header">
                    @foreach($subjects as $subId => $subInfo)
                        @foreach($activeAssessments as $aIdx => $a)
                            <th class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}" style="min-width:38px;">
                                {{ $a->name }}<br><span style="font-size:9px;opacity:.75;">/{{ $a->max_score }}</span>
                            </th>
                        @endforeach
                        @if($showTotal)
                            <th style="min-width:36px;background:#162a4a;color:#a8d4ef;">Total<br><small style="font-size:8px;opacity:.7;">/100</small></th>
                        @endif
                        @if($showBF)
                            <th style="min-width:30px;background:#1a2f1a;color:#bbf7d0;" title="BF = Previous term's cumulative score">BF<br><small style="font-size:8px;opacity:.7;">Prev</small></th>
                        @endif
                        @if($showCum)
                            <th style="min-width:38px;background:#1a3050;color:#93c5fd;">Cum<br><small style="font-size:8px;opacity:.75;">(BF+T)÷2</small></th>
                        @endif
                        @if($showGrade)
                            <th style="min-width:30px;">Grd</th>
                        @endif
                        @if($showPosTerm)
                            <th style="min-width:32px;background:#2a1a0a;color:#fef3c7;">T-Pos</th>
                        @endif
                        @if($showPosCum)
                            <th style="min-width:32px;background:#0a1e38;color:#bfdbfe;">C-Pos</th>
                        @endif
                        @if($showAvg)
                            <th style="min-width:32px;">Avg</th>
                        @endif
                        @if($showRemark)
                            <th style="min-width:44px;">Rmk</th>
                        @endif
                    @endforeach

                    {{-- Analytics col header --}}
                    <th style="min-width:44px;background:#0a2240;border-left:2px solid var(--cb-teal);">View</th>

                    @if($showGPA)      <th style="background:#0a1e38;color:#93c5fd;min-width:36px;border-left:2px solid #3b82f6;">GPA</th>   @endif
                    @if($showCGPA)     <th style="background:#0a1e38;color:#86efac;min-width:36px;">CGPA</th>  @endif
                    @if($showGPAGrade) <th style="background:#0a1e38;color:#fcd34d;min-width:30px;">GGrd</th>  @endif
                    @if($showNumSub)   <th style="background:#0a1e38;color:#a8d4ef;min-width:30px;">NS</th>    @endif
                    @if($showTotalGP)  <th style="background:#0a1e38;color:#a8d4ef;min-width:36px;">TGP</th>   @endif
                </tr>
            </thead>
            <tbody>
                @foreach($studentRows as $idx => $stu)
                    @php
                        $sid      = $stu['id'];
                        $posCum   = $stu['position_cum']  ?? 0;
                        $posTerm  = $stu['position_term'] ?? 0;

                        $hasFailure = false;
                        foreach($stu['subjects'] as $sd) {
                            if(($sd['grade']??'') === 'F9') { $hasFailure = true; break; }
                        }
                        $hasPic   = !empty($stu['picture']) && $stu['picture'] !== 'unnamed.jpg';
                        $imgSrc   = $hasPic ? asset('storage/student_avatars/' . basename($stu['picture'])) : null;
                        $initials = strtoupper(substr($stu['lastname']??'',0,1) . substr($stu['firstname']??'',0,1)) ?: 'ST';
                        $fullName = trim(($stu['lastname']??'') . ' ' . ($stu['firstname']??''));

                        $subjectCount    = count($subjects);
                        $totalObtainable = $subjectCount * 100;
                        $termObtained    = $stu['total_term'] ?? 0;
                        $cumObtained     = $stu['total_cum']  ?? 0;

                        $termPct = $totalObtainable > 0 ? round(($termObtained / $totalObtainable) * 100, 1) : 0;
                        $cumPct  = $totalObtainable > 0 ? round(($cumObtained  / $totalObtainable) * 100, 1) : 0;
                        $cumClass = $cumPct < 50 ? 'score-red' : ($cumPct < 70 ? 'score-amber' : 'score-green');

                        // Build grades array for popup
                        $gradesForPopup = [];
                        foreach ($subjects as $subId => $subInfo) {
                            $sd = $stu['subjects'][$subId] ?? [];
                            $gradesForPopup[] = [
                                'subject'    => $subInfo['subject_name'],
                                'term_score' => $sd['total'] ?? 0,
                                'cum_score'  => $sd['cum']   ?? 0,
                                'bf_score'   => $sd['bf']    ?? 0,
                                'grade'      => $sd['grade'] ?? '-',
                            ];
                        }

                        $posTotal = count($studentRows);

                        // Ordinal helper (PHP-side for tooltip)
                        $ordT = $posTerm . ($posTerm==1?'st':($posTerm==2?'nd':($posTerm==3?'rd':'th')));
                        $ordC = $posCum  . ($posCum ==1?'st':($posCum ==2?'nd':($posCum ==3?'rd':'th')));
                    @endphp
                    <tr data-student-id="{{ $sid }}"
                        data-student-name="{{ strtolower($fullName) }}"
                        data-admission="{{ strtolower($stu['admissionno']) }}"
                        data-gpa="{{ $stu['gpa'] }}"
                        data-total-cum="{{ $cumObtained }}"
                        data-total-term="{{ $termObtained }}"
                        data-has-failure="{{ $hasFailure ? 'true' : 'false' }}"
                        data-term-pct="{{ $termPct }}"
                        data-cum-pct="{{ $cumPct }}"
                        data-pos-cum="{{ $posCum }}"
                        data-pos-term="{{ $posTerm }}"
                        style="animation-delay:{{ $idx * 0.04 }}s;">

                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align:center;white-space:nowrap;padding:4px 6px;">
                            {{-- Dual position badge: term on top, cum below --}}
                            @if($posTerm > 0 || $posCum > 0)
                                <div class="pos-dual"
                                     data-tooltip="Term: {{ $ordT }} of {{ $posTotal }} · Cum: {{ $ordC }} of {{ $posTotal }}">
                                    <span class="pos-term-lbl">T:{{ $posTerm > 0 ? $posTerm : '—' }}</span>
                                    <span class="pos-cum-lbl">C:{{ $posCum  > 0 ? $posCum  : '—' }}</span>
                                </div>
                            @else
                                <span style="color:#94a3b8;font-size:10px;">—</span>
                            @endif
                        </td>
                        @if($showAdmNo)
                            <td class="adm-cell">{{ $stu['admissionno'] }}</td>
                        @endif
                        <td class="student-info-cell">
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($imgSrc)
                                    <div class="cb-avatar">
                                        <img src="{{ $imgSrc }}" alt="{{ $fullName }}"
                                             onerror="var p=this.closest('.cb-avatar');p.classList.add('cb-avatar-initials');p.textContent='{{ $initials }}'">
                                    </div>
                                @else
                                    <div class="cb-avatar cb-avatar-initials">{{ $initials }}</div>
                                @endif
                                <div>
                                    <div style="font-weight:700;font-size:12px;color:var(--cb-navy);">{{ strtoupper($stu['lastname']) }}, {{ $stu['firstname'] }}</div>
                                    @if(!empty($stu['arm']))
                                        <div style="font-size:10px;color:var(--cb-muted);">Arm {{ $stu['arm'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @if($showGender)
                            <td style="font-size:10px;">{{ substr($stu['gender']??'',0,1) }}</td>
                        @endif

                        {{-- Subject score cells --}}
                        @foreach($subjects as $subId => $subInfo)
                            @php
                                $sd       = $stu['subjects'][$subId] ?? [];
                                $g        = $sd['grade'] ?? '-';
                                $gc       = $gradeColors[$g] ?? '';
                                $total    = $sd['total'] ?? 0;
                                $bf       = $sd['bf']    ?? 0;
                                $cum      = $sd['cum']   ?? 0;
                                // Colour grade based on CUM score
                                $cumGc    = $cum > 0 ? $gc : '';
                                // Indicate when cum differs from total (BF was applied)
                                $hasBF    = $bf > 0;
                            @endphp
                            @foreach($activeAssessments as $aIdx => $a)
                                @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
                                <td class="score-cell {{ $aIdx === 0 ? 'sub-boundary' : '' }}"
                                    style="{{ $aIdx === 0 ? 'border-left:1.5px solid #2563eb;' : '' }}">
                                    {{ $as > 0 ? number_format($as,1) : '—' }}
                                </td>
                            @endforeach
                            @if($showTotal)
                                <td class="score-cell">
                                    {{ $total > 0 ? number_format($total,1) : '—' }}
                                </td>
                            @endif
                            @if($showBF)
                                {{-- BF = previous term's cum score. Highlight if present. --}}
                                <td class="score-cell"
                                    style="{{ $hasBF ? 'background:#f0fdf4!important;color:#16a34a;font-weight:700;' : 'color:#94a3b8;' }}"
                                    title="{{ $hasBF ? 'BF: Previous term cumulative = '.$bf : 'No BF (first term or not entered)' }}">
                                    {{ $hasBF ? number_format($bf,1) : '—' }}
                                </td>
                            @endif
                            @if($showCum)
                                {{-- CUM = (BF + Total) / 2. If no BF, equals Total. --}}
                                <td class="score-cell {{ $cumGc }}" style="font-weight:700;"
                                    title="{{ $hasBF ? 'Cum = (BF '.number_format($bf,1).' + Total '.number_format($total,1).') ÷ 2 = '.number_format($cum,1) : 'No BF — Cum = Total' }}">
                                    {{ $cum > 0 ? number_format($cum,1) : '—' }}
                                </td>
                            @endif
                            @if($showGrade)
                                {{-- Grade is determined by the CUM score (stored in DB) --}}
                                <td class="score-cell {{ $cumGc }}" style="font-weight:700;">{{ $g !== '-' ? $g : '—' }}</td>
                            @endif
                            @if($showPosTerm)
                                {{-- Per-subject position from DB (broadsheets.subject_position_class) --}}
                                <td class="score-cell pos-term-cell">{{ isset($sd['position']) && $sd['position'] !== '-' ? $sd['position'] : '—' }}</td>
                            @endif
                            @if($showPosCum)
                                {{-- Same per-subject position shown in cum column for now (DB stores one per-subject position) --}}
                                <td class="score-cell pos-cum-cell">{{ isset($sd['position']) && $sd['position'] !== '-' ? $sd['position'] : '—' }}</td>
                            @endif
                            @if($showAvg)
                                <td class="score-cell" style="font-size:10px;color:var(--cb-muted);">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td>
                            @endif
                            @if($showRemark)
                                <td class="score-cell" style="font-size:10px;white-space:nowrap;">{{ $sd['remark'] ?? '—' }}</td>
                            @endif
                        @endforeach

                        {{-- Eye button → popup --}}
                        <td style="text-align:center;border-left:2px solid var(--cb-teal);background:#f0fdf9;">
                            <button type="button"
                                    class="grade-trigger-btn"
                                    data-sid="{{ $sid }}"
                                    data-sname="{{ $fullName }}"
                                    data-sadm="{{ $stu['admissionno'] }}"
                                    data-term-obtained="{{ $termObtained }}"
                                    data-cum-obtained="{{ $cumObtained }}"
                                    data-obtainable="{{ $totalObtainable }}"
                                    data-term-pct="{{ $termPct }}"
                                    data-cum-pct="{{ $cumPct }}"
                                    data-gpa="{{ $stu['gpa'] }}"
                                    data-gpa-grade="{{ $stu['gpa_grade'] ?? '-' }}"
                                    data-pos-cum="{{ $posCum }}"
                                    data-pos-term="{{ $posTerm }}"
                                    data-pos-total="{{ $posTotal }}"
                                    data-grades='@json($gradesForPopup)'
                                    data-tooltip="View Performance Summary"
                                    title="View Performance Summary">
                                <i class="ri-eye-line"></i>
                            </button>
                        </td>

                        @if($showGPA)      <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>            @endif
                        @if($showCGPA)     <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td> @endif
                        @if($showGPAGrade) @php $ggc = $gradeColors[$stu['gpa_grade']??'-'] ?? ''; @endphp
                                           <td class="gpa-cell {{ $ggc }}" style="font-weight:700;">{{ $stu['gpa_grade'] ?? '—' }}</td> @endif
                        @if($showNumSub)   <td>{{ $stu['num_subjects'] ?? '—' }}</td> @endif
                        @if($showTotalGP)  <td>{{ number_format($stu['total_grade_points'],1) }}</td> @endif
                    </tr>
                @endforeach

                {{-- Stats rows --}}
                @php $statRows = [['CLASS AVG','avg'],['HIGHEST','highest'],['LOWEST','lowest']]; $statStyles = ['avg'=>'','highest'=>'stats-hi','lowest'=>'stats-lo']; @endphp
                @foreach($statRows as [$label, $key])
                    <tr class="stats-row {{ $statStyles[$key] }}">
                        <td class="stats-label" colspan="{{ $frozenCols }}">{{ $label }}</td>
                        @foreach($subjects as $subId => $subInfo)
                            @php $st = $subjectStats[$subId] ?? []; @endphp
                            @foreach($activeAssessments as $a) <td>—</td> @endforeach
                            @if($showTotal)   <td>{{ $st[$key] ?? '—' }}</td> @endif
                            @if($showBF)      <td>—</td>                      @endif
                            @if($showCum)     <td>—</td>                      @endif
                            @if($showGrade)   <td>—</td>                      @endif
                            @if($showPosTerm) <td>—</td>                      @endif
                            @if($showPosCum)  <td>—</td>                      @endif
                            @if($showAvg)     <td>{{ $key==='avg' ? ($st['avg']??'—') : '—' }}</td> @endif
                            @if($showRemark)  <td>—</td>                      @endif
                        @endforeach
                        <td>—</td>
                        @if($showGPA)      <td>—</td> @endif
                        @if($showCGPA)     <td>—</td> @endif
                        @if($showGPAGrade) <td>—</td> @endif
                        @if($showNumSub)   <td>—</td> @endif
                        @if($showTotalGP)  <td>—</td> @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Subject Performance Summary ── --}}
<div class="subj-summary-card mb-4">
    <div class="card-header-custom">
        <i class="ri-bar-chart-2-line me-2"></i>Subject Performance Summary
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:12px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="min-width:160px;color:var(--cb-navy);">Subject</th>
                    <th style="text-align:center;color:var(--cb-navy);">Class Avg</th>
                    <th style="text-align:center;color:var(--cb-navy);">Highest</th>
                    <th style="text-align:center;color:var(--cb-navy);">Lowest</th>
                    <th style="text-align:center;color:var(--cb-navy);">Passed</th>
                    <th style="text-align:center;color:var(--cb-navy);">Failed</th>
                    <th style="text-align:center;color:var(--cb-navy);">Pass Rate</th>
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
                    <tr style="animation:rowSlide .3s ease both;animation-delay:{{ $loop->index * 0.03 }}s;transition:all .2s ease;"
                        onmouseover="this.style.background='#f0fdf9'" onmouseout="this.style.background=''">
                        <td style="font-weight:600;color:var(--cb-navy);">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))
                                <span class="text-muted" style="font-size:10px;">({{ $subInfo['subject_code'] }})</span>
                            @endif
                        </td>
                        <td style="text-align:center;font-weight:700;">{{ $st['avg'] ?? '—' }}</td>
                        <td style="text-align:center;color:#16a34a;font-weight:700;">{{ $st['highest'] ?? '—' }}</td>
                        <td style="text-align:center;color:#dc2626;font-weight:700;">{{ $st['lowest'] ?? '—' }}</td>
                        <td style="text-align:center;color:#16a34a;">{{ $p }}</td>
                        <td style="text-align:center;color:#dc2626;">{{ $f }}</td>
                        <td style="text-align:center;">
                            <span class="{{ $pr >= 50 ? 'score-green' : 'score-red' }}">{{ $pr }}%</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Signature Block ── --}}
<div class="cb-card mb-4 no-print" style="animation:fadeInUp .5s ease .5s both;">
    <div class="cb-card-header">
        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--cb-navy);"><i class="ri-pen-nib-line me-1" style="color:var(--cb-teal)"></i>Authorisation Signatures</h6>
    </div>
    <div class="card-body p-4">
        <div class="row">
            @foreach(['Class Teacher','Head of Department','Vice Principal','Principal'] as $sig)
                <div class="col-3 text-center">
                    <div style="border-top:2px solid var(--cb-border);padding-top:8px;margin-top:40px;font-size:12px;color:var(--cb-muted);font-weight:600;">{{ $sig }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

</div>{{-- /container --}}
</div>{{-- /page-content --}}
</div>{{-- /main-content --}}

<script>
(function () {
    'use strict';

    var GRADE_COLORS = {
        'A1': 'grade-a1', 'B2': 'grade-b2', 'B3': 'grade-b3',
        'C4': 'grade-c4', 'C5': 'grade-c5', 'C6': 'grade-c6',
        'D7': 'grade-d7', 'E8': 'grade-e8', 'F9': 'grade-f9', '-': ''
    };

    /* ── Utilities ── */
    function esc(str) { var d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML; }

    function toast(msg, type) {
        document.querySelectorAll('.cb-toast').forEach(function (t) { t.remove(); });
        var icons = { success: 'checkbox-circle-fill', error: 'error-warning-fill', info: 'information-fill', warning: 'alert-fill' };
        var el = document.createElement('div');
        el.className = 'cb-toast cb-toast-' + (type || 'info');
        el.innerHTML = '<i class="ri-' + (icons[type] || icons.info) + '" style="font-size:18px;flex-shrink:0;"></i> ' + esc(msg);
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 4000);
    }

    /* ── Grade popup ── */
    function closeGradePop() {
        var gpop     = document.getElementById('cbGradePopup');
        var backdrop = document.getElementById('cbPopupBackdrop');
        if (gpop)     { gpop.classList.remove('is-open'); delete gpop.dataset.activeSid; }
        if (backdrop) backdrop.style.display = 'none';
    }

    function getPctClass(p) { return p < 40 ? 'score-red' : (p < 70 ? 'score-amber' : 'score-green'); }

    function ordinal(n) {
        n = parseInt(n) || 0;
        if (n <= 0) return '—';
        return n + (n === 1 ? 'st' : n === 2 ? 'nd' : n === 3 ? 'rd' : 'th');
    }

    function openGradePop(btn) {
        var sid          = btn.getAttribute('data-sid');
        var name         = btn.getAttribute('data-sname');
        var adm          = btn.getAttribute('data-sadm')          || '';
        var termObtained = parseFloat(btn.getAttribute('data-term-obtained') || 0);
        var cumObtained  = parseFloat(btn.getAttribute('data-cum-obtained')  || 0);
        var obtainable   = parseFloat(btn.getAttribute('data-obtainable')    || 0);
        var termPct      = parseFloat(btn.getAttribute('data-term-pct')      || 0);
        var cumPct       = parseFloat(btn.getAttribute('data-cum-pct')       || 0);
        var gpa          = parseFloat(btn.getAttribute('data-gpa')           || 0);
        var gpaGrade     = btn.getAttribute('data-gpa-grade') || '—';
        // FIX: read both positions cleanly — no dead variable
        var posCum       = parseInt(btn.getAttribute('data-pos-cum')   || 0);
        var posTerm      = parseInt(btn.getAttribute('data-pos-term')  || 0);
        var posTotal     = parseInt(btn.getAttribute('data-pos-total') || 0);
        var grades       = [];
        try { grades = JSON.parse(btn.getAttribute('data-grades') || '[]'); } catch (e) {}

        var gpop = document.getElementById('cbGradePopup');
        if (!gpop) return;

        // FIX: single clean assignment to gpopTitle — no dead variable
        document.getElementById('gpopTitle').innerHTML =
            '<i class="ri-bar-chart-line me-1"></i>' + esc(name) + "'s Performance";

        var posCumDisplay  = posCum  > 0 ? (ordinal(posCum)  + ' / ' + posTotal) : '—';
        var posTermDisplay = posTerm > 0 ? (ordinal(posTerm) + ' / ' + posTotal) : '—';

        var termColor = termPct < 40 ? '#f43f5e' : (termPct < 70 ? '#f59e0b' : '#22c55e');
        var cumColor  = cumPct  < 40 ? '#f43f5e' : (cumPct  < 70 ? '#f59e0b' : '#22c55e');

        /* Grades table rows */
        var rows = '';
        if (grades.length) {
            grades.forEach(function (g) {
                var tC = (g.term_score > 0 && g.term_score < 50) ? 'score-red' : (g.term_score >= 70 ? 'score-green' : (g.term_score > 0 ? 'score-amber' : ''));
                var cC = (g.cum_score  > 0 && g.cum_score  < 50) ? 'score-red' : (g.cum_score  >= 70 ? 'score-green' : (g.cum_score  > 0 ? 'score-amber' : ''));
                var grBadge = (g.grade && g.grade !== '-')
                    ? '<span class="badge ' + (GRADE_COLORS[g.grade] || '') + '" style="font-size:9px;border-radius:6px;">' + esc(g.grade) + '</span>'
                    : '<span style="color:#94a3b8;font-size:11px;">—</span>';
                var tS = (g.term_score && g.term_score > 0) ? parseFloat(g.term_score).toFixed(1) : '—';
                var cS = (g.cum_score  && g.cum_score  > 0) ? parseFloat(g.cum_score).toFixed(1)  : '—';
                var bS = (g.bf_score   && g.bf_score   > 0) ? parseFloat(g.bf_score).toFixed(1)   : '—';
                // Show cumulative formula hint
                var cumHint = (g.bf_score > 0 && g.term_score > 0)
                    ? ' <span style="font-size:8px;opacity:.6;">((' + bS + '+' + tS + ')÷2)</span>' : '';

                rows += '<tr>';
                rows += '<td style="text-align:left;font-weight:600;padding-left:12px;">' + esc(g.subject) + '</td>';
                rows += '<td><div class="score-pair">'
                    + '<div class="score-cell-inner term"><span style="font-size:8px;opacity:.7;">T</span><span class="' + tC + '">' + tS + '</span></div>'
                    + '<div class="score-cell-inner cum"><span style="font-size:8px;opacity:.7;">BF</span><span>' + bS + '</span></div>'
                    + '</div></td>';
                rows += '<td><div class="score-pair">'
                    + '<div class="score-cell-inner cum"><span style="font-size:8px;opacity:.7;">C</span><span class="' + cC + '">' + cS + '</span>' + cumHint + '</div>'
                    + '</div></td>';
                rows += '<td>' + grBadge + '</td>';
                rows += '</tr>';
            });
        } else {
            rows = '<tr><td colspan="4" class="text-center text-muted py-3">No subject records</td></tr>';
        }

        var body = document.getElementById('gpopBody');
        body.innerHTML =
            /* Performance strip */
            '<div class="gpop-perf-strip">'
          + '<div style="font-size:11px;font-weight:700;opacity:.8;margin-bottom:6px;"><i class="ri-dashboard-line me-1"></i>Performance Snapshot</div>'
          + '<div class="gpop-perf-grid">'
          + '<div class="gpop-perf-item"><div class="gpop-perf-lbl">Adm. No</div><div class="gpop-perf-val" style="font-size:12px;">' + esc(adm) + '</div></div>'
          + '<div class="gpop-perf-item"><div class="gpop-perf-lbl">Term Total</div><div class="gpop-perf-val">' + termObtained.toFixed(1) + '</div></div>'
          + '<div class="gpop-perf-item"><div class="gpop-perf-lbl">Cum Total</div><div class="gpop-perf-val">' + cumObtained.toFixed(1) + '</div></div>'
          + '<div class="gpop-perf-item"><div class="gpop-perf-lbl">Obtainable</div><div class="gpop-perf-val">' + obtainable + '</div></div>'
          + '<div class="gpop-perf-item"><div class="gpop-perf-lbl">% (Term)</div><div class="gpop-perf-val ' + getPctClass(termPct) + '" data-popup-pct-term="' + termPct + '">0%</div></div>'
          + '<div class="gpop-perf-item"><div class="gpop-perf-lbl">% (Cum)</div><div class="gpop-perf-val ' + getPctClass(cumPct) + '" data-popup-pct-cum="' + cumPct + '">0%</div></div>'
          + '</div>'
          + '<div style="margin-top:10px;">'
          + '<div style="font-size:9px;opacity:.7;margin-bottom:3px;">Term %</div>'
          + '<div class="pct-bar-wrap"><div class="pct-bar" data-final-color="' + termColor + '" style="width:' + termPct + '%;"></div></div>'
          + '<div style="font-size:9px;opacity:.7;margin:4px 0 3px;">Cum %</div>'
          + '<div class="pct-bar-wrap"><div class="pct-bar" data-final-color="' + cumColor + '" style="width:' + cumPct + '%;"></div></div>'
          + '</div>'
          + '</div>'
          /* Legend */
          + '<div class="gpop-legend">'
          + '<span style="font-size:10px;font-weight:700;color:var(--cb-muted);">Legend:</span>'
          + '<span class="gpop-legend-item"><span class="gpop-legend-dot t"></span>Term score</span>'
          + '<span class="gpop-legend-dot c"></span>BF (prev-term cum)</span>'
          + '<span style="margin-left:auto;display:flex;gap:6px;align-items:center;">'
          + '<span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px;">T-Pos: ' + posTermDisplay + '</span>'
          + '<span class="badge" style="background:#dbeafe;color:#1e40af;font-size:10px;">C-Pos: ' + posCumDisplay + '</span>'
          + '</span>'
          + '</div>'
          /* Grades table */
          + '<div class="gpop-scroll">'
          + '<table class="gpop-table"><thead><tr>'
          + '<th style="text-align:left;padding-left:12px;width:36%;">Subject</th>'
          + '<th style="width:22%;">Term / BF</th>'
          + '<th style="width:22%;">Cum<br><small style="opacity:.65;font-weight:400;font-size:9px;">(BF+T)÷2</small></th>'
          + '<th style="width:20%;">Grade</th>'
          + '</tr></thead><tbody>' + rows + '</tbody></table>'
          + '</div>'
          /* Summary footer */
          + '<div class="gpop-summary">'
          + '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Term Total</div><div class="gpop-sum-val">' + termObtained.toFixed(1) + '</div></div>'
          + '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Cum Total</div><div class="gpop-sum-val">' + cumObtained.toFixed(1) + '</div></div>'
          + '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Obtainable</div><div class="gpop-sum-val">' + obtainable + '</div></div>'
          + '<div class="gpop-sum-item"><div class="gpop-sum-lbl">% (Term)</div><div class="gpop-sum-val ' + getPctClass(termPct) + '">' + termPct.toFixed(1) + '%</div></div>'
          + '<div class="gpop-sum-item"><div class="gpop-sum-lbl">% (Cum)</div><div class="gpop-sum-val ' + getPctClass(cumPct) + '">' + cumPct.toFixed(1) + '%</div></div>'
          + '<div class="gpop-sum-item"><div class="gpop-sum-lbl">Term / Cum Pos</div>'
          + '<div class="gpop-sum-val" style="font-size:12px;display:flex;flex-direction:column;gap:2px;">'
          + '<span style="color:#92400e;font-weight:800;">' + posTermDisplay + '</span>'
          + '<span style="color:#1e40af;font-weight:800;">' + posCumDisplay + '</span>'
          + '</div></div>'
          + '</div>';

        /* Position popup */
        var pw = 520, ph = Math.min(600, window.innerHeight - 40);
        var rect = btn.getBoundingClientRect();
        var vw = window.innerWidth, vh = window.innerHeight;
        var top  = rect.bottom + 8;
        var left = rect.left + (rect.width / 2) - (pw / 2);
        if (top + ph > vh - 8) top  = Math.max(8, rect.top - ph - 8);
        if (left < 8)          left = 8;
        if (left + pw > vw - 8) left = vw - pw - 8;
        gpop.style.width    = pw + 'px';
        gpop.style.top      = top + 'px';
        gpop.style.left     = left + 'px';
        gpop.style.maxHeight = ph + 'px';
        gpop.dataset.activeSid = sid;
        gpop.classList.add('is-open');
        document.getElementById('cbPopupBackdrop').style.display = 'block';

        /* Animate pct counters */
        setTimeout(function () {
            body.querySelectorAll('[data-popup-pct-term]').forEach(function (el) {
                animatePctEl(el, parseFloat(el.getAttribute('data-popup-pct-term')));
            });
            body.querySelectorAll('[data-popup-pct-cum]').forEach(function (el) {
                animatePctEl(el, parseFloat(el.getAttribute('data-popup-pct-cum')));
            });
            body.querySelectorAll('.pct-bar[data-final-color]').forEach(function (bar) {
                setTimeout(function () { bar.style.backgroundColor = bar.getAttribute('data-final-color'); }, 820);
            });
        }, 60);
    }

    function animatePctEl(el, target) {
        var steps = 50, step = 0, current = 0, inc = target / steps;
        var t = setInterval(function () {
            step++; current += inc;
            if (step >= steps) { current = target; clearInterval(t); }
            el.textContent = current.toFixed(1) + '%';
        }, 800 / steps);
    }

    /* ── Search ── */
    var tableRows = document.querySelectorAll('#broadsheetTable tbody tr:not(.stats-row)');

    document.getElementById('searchStudent').addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var count = 0;
        tableRows.forEach(function (row) {
            var name = row.getAttribute('data-student-name') || '';
            var adm  = row.getAttribute('data-admission')    || '';
            var show = !q || name.includes(q) || adm.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) count++;
        });
        if (q) toast('Found ' + count + ' student(s)', 'info');
    });

    /* ── Locate ── */
    document.getElementById('locateStudent').addEventListener('change', function () {
        var val = this.value;
        if (!val) return;
        tableRows.forEach(function (r) { r.style.outline = ''; r.style.backgroundColor = ''; });
        if      (val === 'top5')      highlightTopByCum(5);
        else if (val === 'top10')     highlightTopByCum(10);
        else if (val === 'top5term')  highlightTopByTerm(5);
        else if (val === 'failures')  highlightFailures();
        else if (val === 'below_avg') highlightBelowAvg();
        else if (val.startsWith('student_')) {
            var id  = val.replace('student_', '');
            var row = document.querySelector('tr[data-student-id="' + id + '"]');
            if (row) {
                row.style.outline = '3px solid var(--cb-teal)';
                row.style.backgroundColor = '#f0fdf9';
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                toast('Located: ' + (row.getAttribute('data-student-name') || ''), 'success');
            }
        }
        setTimeout(function () { document.getElementById('locateStudent').value = ''; }, 120);
    });

    function highlightTopByCum(n) {
        var rows = Array.from(tableRows).filter(function (r) { return r.style.display !== 'none'; });
        rows.sort(function (a, b) { return parseFloat(b.dataset.totalCum || 0) - parseFloat(a.dataset.totalCum || 0); });
        rows.slice(0, n).forEach(function (r) { r.style.backgroundColor = '#fef9c3'; r.style.outline = '2px solid #d97706'; });
        toast('Top ' + n + ' by cumulative score highlighted', 'success');
    }

    function highlightTopByTerm(n) {
        var rows = Array.from(tableRows).filter(function (r) { return r.style.display !== 'none'; });
        rows.sort(function (a, b) { return parseFloat(b.dataset.totalTerm || 0) - parseFloat(a.dataset.totalTerm || 0); });
        rows.slice(0, n).forEach(function (r) { r.style.backgroundColor = '#f0fdf9'; r.style.outline = '2px solid #0d9488'; });
        toast('Top ' + n + ' by term score highlighted', 'success');
    }

    function highlightFailures() {
        var c = 0;
        tableRows.forEach(function (r) {
            if (r.dataset.hasFailure === 'true') { r.style.backgroundColor = '#fee2e2'; r.style.outline = '2px solid #dc2626'; c++; }
        });
        toast(c + ' student(s) with F9 highlighted', 'warning');
    }

    function highlightBelowAvg() {
        var totals = Array.from(tableRows).map(function (r) { return parseFloat(r.dataset.totalCum || 0); }).filter(function (v) { return v > 0; });
        var avg = totals.length ? totals.reduce(function (a, b) { return a + b; }, 0) / totals.length : 0;
        var c = 0;
        tableRows.forEach(function (r) {
            var v = parseFloat(r.dataset.totalCum || 0);
            if (v > 0 && v < avg) { r.style.backgroundColor = '#fff7ed'; r.style.outline = '2px solid #f97316'; c++; }
        });
        toast(c + ' student(s) below class average (cum)', 'info');
    }

    window.scrollToTop = function () { window.scrollTo({ top: 0, behavior: 'smooth' }); };

    /* ── Animate number helper ── */
    function animateNumber(elId, target, suffix, decimals) {
        var el = document.getElementById(elId); if (!el) return;
        var steps = 60, step = 0, current = 0, inc = target / steps;
        var t = setInterval(function () {
            step++; current += inc;
            if (step >= steps) { current = target; clearInterval(t); }
            el.textContent = current.toFixed(decimals || 0) + (suffix || '');
        }, 800 / steps);
    }

    /* ── DOM Ready ── */
    document.addEventListener('DOMContentLoaded', function () {
        /* Move popup/backdrop to body so z-index stacking works correctly */
        ['cbGradePopup', 'cbPopupBackdrop'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.parentNode !== document.body) document.body.appendChild(el);
        });

        /* Grade popup: open */
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.grade-trigger-btn');
            if (!btn) return;
            e.stopPropagation(); e.preventDefault();
            var gpop = document.getElementById('cbGradePopup');
            if (gpop && gpop.classList.contains('is-open') && gpop.dataset.activeSid === btn.getAttribute('data-sid')) {
                closeGradePop(); return;
            }
            closeGradePop();
            setTimeout(function () { openGradePop(btn); }, 16);
        });

        /* Grade popup: close button */
        document.getElementById('gpopCloseBtn').addEventListener('click', closeGradePop);

        /* Backdrop click */
        document.addEventListener('click', function (e) {
            if (e.target === document.getElementById('cbPopupBackdrop')) closeGradePop();
        });

        /* Escape key */
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeGradePop(); });

        /* Animate avg % + top performer card */
        (function () {
            var rows = Array.from(document.querySelectorAll('#broadsheetTable tbody tr[data-cum-pct]'));
            if (!rows.length) return;

            var totalPct = 0;
            var topCum   = -1;
            var topName  = '—';

            rows.forEach(function (r) {
                totalPct += parseFloat(r.getAttribute('data-cum-pct') || 0);
                var cum = parseFloat(r.getAttribute('data-total-cum') || 0);
                if (cum > topCum) {
                    topCum  = cum;
                    topName = r.getAttribute('data-student-name') || '—';
                }
            });

            var avg = rows.length ? totalPct / rows.length : 0;
            animateNumber('statAvgPct', avg, '%', 1);

            var topEl = document.getElementById('statTopPerformer');
            if (topEl) {
                topEl.textContent = topName.split(' ').map(function (w) {
                    return w.charAt(0).toUpperCase() + w.slice(1);
                }).join(' ');
            }
        })();
    });
})();
</script>
@endsection
