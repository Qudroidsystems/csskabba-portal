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

    /* Position column colours */
    --pos-cc-bg:  #dbeafe; --pos-cc-txt: #1e40af; --pos-cc-bdr: #3b82f6;   /* Class/Cum   - blue   */
    --pos-ct-bg:  #ede9fe; --pos-ct-txt: #5b21b6; --pos-ct-bdr: #7c3aed;   /* Class/Total - violet */
    --pos-at-bg:  #fef3c7; --pos-at-txt: #92400e; --pos-at-bdr: #f59e0b;   /* Arm/Total   - amber  */
    --pos-ac-bg:  #dcfce7; --pos-ac-txt: #166534; --pos-ac-bdr: #22c55e;   /* Arm/Cum     - green  */
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
@keyframes rowSlide    { from { opacity:0; transform:translateX(-12px); } to { opacity:1; transform:translateX(0); } }
@keyframes countUp     { from { opacity:0; transform:scale(.6); } to { opacity:1; transform:scale(1); } }
@keyframes backdropIn  { from { opacity:0; } to { opacity:1; } }
@keyframes posFlash    { 0%,100% { opacity:1; } 50% { opacity:.5; transform:scale(1.12); } }
@keyframes chipSlide   { from { opacity:0; transform:translateY(-6px) scale(.9); } to { opacity:1; transform:translateY(0) scale(1); } }

/* ── Hero ── */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius); padding: 32px 36px; margin-bottom: 28px;
    position: relative; overflow: hidden;
    animation: fadeInDown .6s cubic-bezier(.22,1,.36,1) both;
}
.cb-hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:280px; height:280px;
    background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 70%);
    border-radius:50%; animation:floatUp 6s ease-in-out infinite;
}
.cb-hero h1 { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; color:#fff; margin:0 0 8px; }
.cb-hero p  { font-size:13px; color:rgba(255,255,255,.72); margin:0; }
.cb-meta-pill {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    border-radius:20px; padding:4px 14px; font-size:12px; font-weight:600; color:#fff;
    display:inline-flex; align-items:center; gap:5px;
    transition:all .3s ease; animation:fadeInUp .5s ease both;
}
.cb-meta-pill:hover { background:rgba(255,255,255,.22); transform:translateY(-2px); }
.btn-back {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    border-radius:10px; padding:8px 18px; color:#fff; font-size:12px; font-weight:600;
    text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    transition:all .3s ease;
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
.cb-stat:hover { transform:translateY(-6px) scale(1.01); box-shadow:var(--cb-shadow-lg); }
.cb-stat .stat-accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--cb-radius) var(--cb-radius) 0 0; }
.cb-stat .stat-value  { font-size:30px; font-weight:700; color:var(--cb-navy); line-height:1; margin-top:8px; animation:countUp .6s ease both; animation-delay:.4s; }
.cb-stat .stat-label  { font-size:12px; color:var(--cb-muted); margin-top:5px; font-weight:500; }
.cb-stat .stat-ico    { font-size:36px; opacity:.08; position:absolute; right:16px; top:50%; transform:translateY(-50%); }

/* ── Grade key ── */
.grade-key {
    display:flex; align-items:center; border:1px solid var(--cb-border);
    padding:6px 14px; background:#fafafa; border-radius:8px; margin-bottom:14px;
    flex-wrap:wrap; gap:6px; animation:fadeInLeft .5s ease;
}

/* ── Card ── */
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

/* ── Meta grid ── */
.meta-grid { display:flex; border:1px solid var(--cb-border); background:var(--cb-surface); border-radius:8px; overflow:hidden; margin-bottom:14px; }
.meta-cell { flex:1; padding:10px 14px; border-right:1px solid var(--cb-border); transition:all .2s ease; }
.meta-cell:last-child { border-right:none; }
.meta-cell:hover { background:#e8f0fe; transform:translateY(-2px); }
.meta-label { font-size:10px; color:var(--cb-muted); text-transform:uppercase; letter-spacing:.4px; display:block; }
.meta-value { font-size:13px; font-weight:700; color:var(--cb-navy); }

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

.broadsheet-table tbody tr { animation:rowSlide .4s ease both; transition:all .25s ease; }
.broadsheet-table tbody tr:nth-child(odd)  { background:#ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background:#f0f4fa; }
.broadsheet-table tbody tr:hover { background-color:#e8f0fe !important; transform:scale(1.005); box-shadow:0 2px 8px rgba(0,0,0,.08); }

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

/* ── POSITION CELLS — 4 distinct colours ── */
/* 1. Class position by Cumulative (blue) */
.pos-cc-cell {
    background:var(--pos-cc-bg) !important; color:var(--pos-cc-txt);
    font-weight:800; border-left:2px solid var(--pos-cc-bdr) !important;
    font-size:11px; position:relative;
}
/* 2. Class position by Total (violet) */
.pos-ct-cell {
    background:var(--pos-ct-bg) !important; color:var(--pos-ct-txt);
    font-weight:800; border-left:1.5px solid var(--pos-ct-bdr) !important;
    font-size:11px;
}
/* 3. Arm position by Total (amber) */
.pos-at-cell {
    background:var(--pos-at-bg) !important; color:var(--pos-at-txt);
    font-weight:800; border-left:1.5px solid var(--pos-at-bdr) !important;
    font-size:11px;
}
/* 4. Arm position by Cumulative (green) */
.pos-ac-cell {
    background:var(--pos-ac-bg) !important; color:var(--pos-ac-txt);
    font-weight:800; border-left:1.5px solid var(--pos-ac-bdr) !important;
    font-size:11px;
}

/* Medal decoration for top-3 positions */
.pos-medal-1 { animation: posFlash 2s ease-in-out 3; }
.pos-medal-1::after { content:'🥇'; font-size:9px; margin-left:2px; }
.pos-medal-2::after { content:'🥈'; font-size:9px; margin-left:2px; }
.pos-medal-3::after { content:'🥉'; font-size:9px; margin-left:2px; }

/* ── Overall position badge (term vs cum) ── */
.pos-dual { display:inline-flex; flex-direction:column; align-items:center; gap:2px; cursor:pointer; }
.pos-dual .pos-term-lbl { font-size:9px; font-weight:800; color:var(--pos-at-txt); background:var(--pos-at-bg); border-radius:4px; padding:1px 5px; }
.pos-dual .pos-cum-lbl  { font-size:9px; font-weight:800; color:var(--pos-cc-txt); background:var(--pos-cc-bg); border-radius:4px; padding:1px 5px; }

/* ── Grade colours ── */
.grade-a1 { background:#dcfce7 !important; color:#166534; font-weight:700; }
.grade-b2 { background:#dbeafe !important; color:#1e40af; }
.grade-b3 { background:#e0eeff !important; color:#1e40af; }
.grade-c4 { background:#fef9c3 !important; color:#854d0e; }
.grade-c5 { background:#fef3c7 !important; color:#92400e; }
.grade-c6 { background:#fde68a !important; color:#78350f; }
.grade-d7 { background:#ffedd5 !important; color:#9a3412; }
.grade-e8 { background:#fed7aa !important; color:#9a3412; }
.grade-f9 { background:#fee2e2 !important; color:#991b1b; font-weight:700; }

/* ── Score colours ── */
.score-red   { color:#dc2626 !important; font-weight:700; }
.score-amber { color:#d97706 !important; font-weight:700; }
.score-green { color:#16a34a !important; font-weight:700; }

/* ── GPA cells ── */
.gpa-cell { background:#eff6ff !important; color:#1e3a8a; font-weight:700; border-left:1.5px solid #3b82f6 !important; }

/* ── Position legend strip ── */
.pos-legend-strip {
    display:flex; gap:8px; flex-wrap:wrap; padding:8px 14px;
    background:var(--cb-surface); border:1px solid var(--cb-border);
    border-radius:8px; margin-bottom:14px; align-items:center;
    animation:fadeInUp .4s ease;
}
.pos-chip {
    display:inline-flex; align-items:center; gap:5px; padding:3px 10px;
    border-radius:20px; font-size:11px; font-weight:700; border:1.5px solid;
    animation:chipSlide .3s ease both;
}
.pos-chip-cc { background:var(--pos-cc-bg); color:var(--pos-cc-txt); border-color:var(--pos-cc-bdr); animation-delay:.05s; }
.pos-chip-ct { background:var(--pos-ct-bg); color:var(--pos-ct-txt); border-color:var(--pos-ct-bdr); animation-delay:.10s; }
.pos-chip-at { background:var(--pos-at-bg); color:var(--pos-at-txt); border-color:var(--pos-at-bdr); animation-delay:.15s; }
.pos-chip-ac { background:var(--pos-ac-bg); color:var(--pos-ac-txt); border-color:var(--pos-ac-bdr); animation-delay:.20s; }

/* ── Avatar ── */
.cb-avatar {
    width:30px; height:30px; border-radius:50%; overflow:hidden;
    border:2px solid var(--cb-border); flex-shrink:0;
    transition:all .3s cubic-bezier(.22,1,.36,1);
    display:inline-flex; align-items:center; justify-content:center;
}
.cb-avatar:hover { border-color:var(--cb-teal); transform:scale(1.12); }
.cb-avatar img { width:100%; height:100%; object-fit:cover; }
.cb-avatar-initials { background:linear-gradient(135deg,var(--cb-teal),var(--cb-sky)); color:#fff; font-size:11px; font-weight:700; }

/* ── Eye button ── */
.grade-trigger-btn {
    background:none; border:none; cursor:pointer;
    color:var(--cb-sky); font-size:17px; padding:5px 8px; border-radius:8px;
    transition:all .25s ease;
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

/* ── Search ── */
.cb-search { position:relative; }
.cb-search input { width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--cb-border); border-radius:10px; font-size:13px; background:var(--cb-surface); font-family:'DM Sans',sans-serif; transition:all .25s ease; }
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

/* ── Performance popup ── */
#cbGradePopup {
    display:none; position:fixed; z-index:99999;
    background:var(--cb-white); border:2px solid var(--cb-teal);
    border-radius:16px; box-shadow:0 20px 60px rgba(15,35,66,.22);
    width:520px; max-height:620px; overflow:hidden; flex-direction:column;
}
#cbGradePopup.is-open { display:flex; animation:popIn .28s cubic-bezier(.22,1,.36,1); }
.gpop-hdr {
    background:linear-gradient(135deg,var(--cb-navy),var(--cb-teal)); color:#fff;
    padding:14px 18px; border-radius:14px 14px 0 0;
    font-weight:700; font-size:14px;
    display:flex; justify-content:space-between; align-items:center; flex-shrink:0;
}
.gpop-close-btn { background:rgba(255,255,255,.18); border:none; color:#fff; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; transition:all .25s ease; }
.gpop-close-btn:hover { background:rgba(255,255,255,.4); transform:rotate(90deg); }
.gpop-body { padding:16px; overflow-y:auto; flex:1; }
.gpop-perf-strip { background:linear-gradient(135deg,var(--cb-navy),#1e5f74); border-radius:10px; padding:12px 16px; color:#fff; margin-bottom:14px; }
.gpop-perf-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:8px; }
.gpop-perf-item { text-align:center; background:rgba(255,255,255,.1); border-radius:8px; padding:8px; }
.gpop-perf-lbl { font-size:9px; opacity:.8; text-transform:uppercase; letter-spacing:.4px; }
.gpop-perf-val { font-size:15px; font-weight:700; margin-top:2px; }
.gpop-scroll { max-height:280px; overflow-y:auto; border:1px solid var(--cb-border); border-radius:10px; }
.gpop-table { width:100%; border-collapse:collapse; font-size:12px; }
.gpop-table thead th { background:var(--cb-navy); color:#fff; font-size:11px; font-weight:600; text-transform:uppercase; padding:9px 8px; border-right:1px solid rgba(255,255,255,.08); text-align:center; position:sticky; top:0; z-index:2; }
.gpop-table thead th:first-child { text-align:left; padding-left:12px; }
.gpop-table tbody td { padding:8px 6px; border-bottom:1px solid #f1f5f9; font-weight:500; text-align:center; }
.gpop-table tbody td:first-child { text-align:left; font-weight:600; color:var(--cb-navy); padding-left:12px; }
.gpop-table tbody tr:hover td { background:#f0fdf9; }
.gpop-summary { background:linear-gradient(135deg,#f8fafc,#f0fdf9); border-radius:12px; padding:12px; margin-top:14px; display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.gpop-sum-item { text-align:center; padding:10px 6px; border-radius:10px; background:white; border:1px solid #e2e8f0; transition:all .2s ease; }
.gpop-sum-item:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.09); }
.gpop-sum-lbl { font-size:9px; color:var(--cb-muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; font-weight:600; }
.gpop-sum-val { font-size:16px; font-weight:800; color:var(--cb-navy); }
#cbPopupBackdrop { display:none; position:fixed; inset:0; z-index:99998; background:rgba(0,0,0,.3); animation:backdropIn .2s ease; }

/* ── Tooltips ── */
[data-tooltip] { position:relative; cursor:pointer; }
[data-tooltip]:before { content:attr(data-tooltip); position:absolute; bottom:100%; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:4px 8px; border-radius:6px; font-size:10px; white-space:nowrap; opacity:0; visibility:hidden; transition:all .2s ease; pointer-events:none; z-index:1000; }
[data-tooltip]:hover:before { opacity:1; visibility:visible; transform:translateX(-50%) translateY(-5px); }

/* ── School header ── */
.school-header-bar { background:linear-gradient(135deg,var(--cb-navy) 0%,#2563eb 100%); border-radius:10px; padding:18px 24px; margin-bottom:16px; color:white; }

/* ── Subject summary ── */
.subj-summary-card { background:var(--cb-white); border:1px solid var(--cb-border); border-radius:var(--cb-radius); box-shadow:var(--cb-shadow); animation:fadeInUp .5s ease .4s both; }
.subj-summary-card .card-header-custom { background:var(--cb-navy); color:#fff; padding:14px 20px; border-radius:var(--cb-radius) var(--cb-radius) 0 0; font-weight:700; font-size:14px; }

/* ── BF indicator ── */
.bf-derived { border-bottom:2px dotted #0d9488 !important; }

@media print {
    .no-print { display:none !important; }
    body { background:#fff !important; }
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
            <p>Academic performance — scores, grades, all four position types &amp; analytics.</p>
            <div class="d-flex gap-2 flex-wrap mt-2">
                <span class="cb-meta-pill"><i class="ri-building-line"></i>{{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
                <span class="cb-meta-pill"><i class="ri-calendar-event-line"></i>{{ $schoolsession->session ?? '-' }}</span>
                <span class="cb-meta-pill"><i class="ri-bookmark-line"></i>{{ $schoolterm->term ?? '-' }}</span>
                @if(!empty($is_combined))
                    <span class="cb-meta-pill" style="background:rgba(245,158,11,.2);"><i class="ri-links-line"></i>Combined Arms</span>
                @endif
            </div>
        </div>
        <a href="javascript:history.back()" class="btn-back"><i class="ri-arrow-left-line"></i> Back</a>
    </div>
</div>

{{-- ── Stats Cards ── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['statTotalStudents', $totalStudents, 'Total Students',    'ri-group-line',   'linear-gradient(90deg,var(--cb-navy),var(--cb-teal))', 'text-navy'],
        ['statTotalSubjects', count($subjects), 'Subjects',        'ri-book-open-line','linear-gradient(90deg,var(--cb-sky),#38bdf8)',         'text-info'],
        ['statAvgPct',        0,               'Avg % (Cum)',       'ri-percent-line',  'linear-gradient(90deg,var(--cb-green),#4ade80)',        'text-success'],
        ['statTopPerformer',  '—',             'Top Performer',    'ri-award-line',    'linear-gradient(90deg,var(--cb-amber),#fcd34d)',        'text-warning'],
    ] as $i => [$elId, $val, $lbl, $ico, $grad, $cls])
    <div class="col-6 col-md-3">
        <div class="cb-stat" style="animation-delay:{{ $i * 0.08 }}s">
            <div class="stat-accent" style="background:{{ $grad }};"></div>
            <div class="stat-ico"><i class="{{ $ico }}"></i></div>
            <div class="stat-value {{ $cls }}" id="{{ $elId }}">{{ $val }}</div>
            <div class="stat-label">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
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
<div style="background:var(--cb-navy);color:white;text-align:center;padding:10px;font-size:15px;font-weight:700;letter-spacing:1.5px;border-radius:8px;margin-bottom:14px;">
    CLASS ACADEMIC BROADSHEET
    @if(!empty($is_combined))<span style="font-size:11px;opacity:.7;font-weight:400;margin-left:10px;">— Combined Arms</span>@endif
</div>

{{-- ── Meta Grid ── --}}
<div class="meta-grid">
    <div class="meta-cell"><span class="meta-label">Class</span><span class="meta-value">{{ ($schoolclass->schoolclass ?? '-') . ' ' . ($schoolclass->arm_name ?? '') }}</span></div>
    <div class="meta-cell"><span class="meta-label">Session</span><span class="meta-value">{{ $schoolsession->session ?? '-' }}</span></div>
    <div class="meta-cell"><span class="meta-label">Term</span><span class="meta-value">{{ $schoolterm->term ?? '-' }}</span></div>
    <div class="meta-cell"><span class="meta-label">Generated</span><span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span></div>
</div>

{{-- ── Grade Key ── --}}
<div class="grade-key">
    <strong style="color:var(--cb-navy);margin-right:8px;font-size:12px;">GRADING SCALE:</strong>
    @php $gradeKey = ['A1'=>['75-100','#16a34a'],'B2'=>['70-74','#1d4ed8'],'B3'=>['65-69','#2563eb'],'C4'=>['60-64','#d97706'],'C5'=>['55-59','#b45309'],'C6'=>['50-54','#92400e'],'D7'=>['45-49','#ea580c'],'E8'=>['40-44','#c2410c'],'F9'=>['0-39','#dc2626']]; @endphp
    @foreach($gradeKey as $grade => $info)
        <span class="badge" style="background:{{ $info[1] }};font-size:11px;border-radius:12px;padding:3px 9px;">{{ $grade }} ({{ $info[0] }})</span>
    @endforeach
    <span class="text-muted ms-2" style="font-size:11px;">
        <strong>BF</strong>=Brought Forward (prev. term cum)
        &nbsp; <strong>CUM</strong>=(BF+Total)÷2
        &nbsp; <span style="border-bottom:2px dotted #0d9488;padding-bottom:1px;">dotted underline</span>=BF from prev. term
    </span>
</div>

{{-- ── Position Legend ── --}}
<div class="pos-legend-strip no-print">
    <strong style="font-size:12px;color:var(--cb-navy);">POSITION COLUMNS:</strong>
    <span class="pos-chip pos-chip-cc"><i class="ri-trophy-line" style="font-size:10px;"></i>Class Pos / Cum</span>
    <span class="pos-chip pos-chip-ct"><i class="ri-medal-line" style="font-size:10px;"></i>Class Pos / Total</span>
    <span class="pos-chip pos-chip-at"><i class="ri-shield-star-line" style="font-size:10px;"></i>Arm Pos / Total</span>
    <span class="pos-chip pos-chip-ac"><i class="ri-star-line" style="font-size:10px;"></i>Arm Pos / Cum</span>
    <span style="margin-left:auto;font-size:11px;color:var(--cb-muted);">All positions stored as raw integers from <code>calculateClassPositionsAndAverages()</code></span>
</div>

{{-- ── Toolbar ── --}}
<div class="cb-card mb-3 no-print">
    <div class="cb-card-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="cb-search" style="max-width:260px;">
                <i class="ri-search-line"></i>
                <input type="text" id="searchStudent" placeholder="Search name or admission no…">
            </div>
            <select class="form-select form-select-sm" id="locateStudent" style="max-width:220px;border-radius:8px;border:1.5px solid var(--cb-border);font-size:12px;">
                <option value="">🔍 Quick Locate…</option>
                <option value="top5">🏆 Top 5 (by Cum)</option>
                <option value="top10">⭐ Top 10</option>
                <option value="failures">⚠️ Students with F9</option>
                <option value="below_avg">📉 Below Class Average</option>
                <option value="first_class">🥇 All 1st positions</option>
                <option disabled>──────────</option>
                @foreach($studentRows as $student)
                    <option value="student_{{ $student['id'] }}">👤 {{ $student['lastname'] }}, {{ $student['firstname'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()" style="border-radius:8px;"><i class="ri-printer-line me-1"></i>Print</button>
            <button class="btn btn-sm" onclick="scrollToTop()" style="background:var(--cb-teal);color:#fff;border-radius:8px;border:none;"><i class="ri-arrow-up-line me-1"></i>Top</button>
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
    $selected = $selectedColumns ?? [];
    $showAll  = empty($selected);

    // Student info
    $showAdmNo  = $showAll || in_array('admission_no',   $selected);
    $showGender = in_array('gender', $selected);

    // Score columns (default:true)
    $showTotal = $showAll || in_array('total',  $selected);
    $showBF    = $showAll || in_array('bf',     $selected);
    $showCum   = $showAll || in_array('cum',    $selected);
    $showGrade = $showAll || in_array('grade',  $selected);
    $showAvg   = $showAll || in_array('class_average', $selected);

    // 4 position columns
    $showPosCC = $showAll || in_array('pos_class_cum',   $selected);  // Class/Cum   (default true)
    $showPosCT = in_array('pos_class_total', $selected);               // Class/Total (default false)
    $showPosAT = in_array('pos_arm_total',   $selected);               // Arm/Total   (default false)
    $showPosAC = in_array('pos_arm_cum',     $selected);               // Arm/Cum     (default false)

    // Overall position (student-level)
    $showOvPosCum  = $showAll || in_array('position_cum',  $selected);
    $showOvPosTerm = $showAll || in_array('position_term', $selected);

    $showRemark = in_array('remark', $selected);

    // GPA
    $showGPA     = $showAll || in_array('gpa',                $selected);
    $showCGPA    = in_array('cgpa',               $selected);
    $showGPAGrade= in_array('gpa_grade',          $selected);
    $showNumSub  = in_array('num_subjects',        $selected);
    $showTotalGP = in_array('total_grade_points',  $selected);

    $activeAssessments = $assessments->filter(fn($a) =>
        empty($selected) || in_array('assessment_' . $a->id, $selected)
    );

    $gradeColors = ['A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3','C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6','D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9','-'=>''];

    // colspan per subject
    $subColspan = $activeAssessments->count();
    if($showTotal)  $subColspan++;
    if($showBF)     $subColspan++;
    if($showCum)    $subColspan++;
    if($showGrade)  $subColspan++;
    if($showPosCC)  $subColspan++;
    if($showPosCT)  $subColspan++;
    if($showPosAT)  $subColspan++;
    if($showPosAC)  $subColspan++;
    if($showAvg)    $subColspan++;
    if($showRemark) $subColspan++;
    $subColspan = max(1,$subColspan);

    // frozen cols for stats row
    $frozenCols  = 1; // sn
    if($showOvPosCum || $showOvPosTerm) $frozenCols++;
    if($showAdmNo)  $frozenCols++;
    $frozenCols++; // name always
    if($showGender) $frozenCols++;

    $gpaColspan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);

    function ordinalSuffix($n) {
        if(!is_numeric($n)||$n<=0) return '-';
        $l2 = $n%100; $l1 = $n%10;
        if($l2>=11&&$l2<=13) return $n.'th';
        return $n.match($l1){1=>'st',2=>'nd',3=>'rd',default=>'th'};
    }
@endphp

<div class="cb-card mb-4">
    <div class="cb-card-header">
        <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--cb-navy);">
            <i class="ri-table-alt-line me-1" style="color:var(--cb-teal)"></i>
            Student Performance &amp; Scores
            <span class="badge ms-2" style="background:var(--cb-teal);color:#fff;font-size:11px;border-radius:20px;padding:3px 10px;">{{ $totalStudents }} Students</span>
        </h5>
    </div>
    <div style="overflow-x:auto;">
        <table class="broadsheet-table" id="broadsheetTable">
            <thead>
                {{-- Row 1: Subject names --}}
                <tr class="subject-header">
                    <th class="student-col" rowspan="2" style="width:30px;">#</th>
                    @if($showOvPosCum || $showOvPosTerm)
                        <th class="student-col" rowspan="2" style="min-width:52px;">Pos</th>
                    @endif
                    @if($showAdmNo)
                        <th class="student-col" rowspan="2" style="min-width:72px;">Adm No</th>
                    @endif
                    <th class="student-col" rowspan="2" style="min-width:180px;text-align:left;padding-left:8px;">Student Name</th>
                    @if($showGender)
                        <th class="student-col" rowspan="2" style="width:36px;">Sex</th>
                    @endif

                    @foreach($subjects as $subId => $subInfo)
                        <th class="subj-name-hdr" colspan="{{ $subColspan }}">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))
                                <br><small style="opacity:.75;font-size:9px;">({{ $subInfo['subject_code'] }})</small>
                            @endif
                        </th>
                    @endforeach

                    {{-- Eye column --}}
                    <th class="subj-name-hdr" colspan="1" style="background:#0a2240;border-left:2px solid var(--cb-teal);min-width:40px;">
                        <i class="ri-eye-line" style="font-size:13px;"></i>
                    </th>

                    @if($gpaColspan > 0)
                        <th colspan="{{ $gpaColspan }}" style="background:#0a1e38;border-left:2px solid #3b82f6;font-size:10px;">GPA METRICS</th>
                    @endif
                </tr>

                {{-- Row 2: Sub-headers --}}
                <tr class="assessment-header">
                    @foreach($subjects as $subId => $subInfo)
                        @foreach($activeAssessments as $aIdx => $a)
                            <th class="{{ $aIdx===0?'sub-boundary':'' }}" style="min-width:36px;">
                                {{ $a->name }}<br><span style="font-size:9px;opacity:.75;">/{{ $a->max_score }}</span>
                            </th>
                        @endforeach
                        @if($showTotal)  <th style="min-width:34px;">Total</th>  @endif
                        @if($showBF)     <th style="min-width:28px;">BF</th>     @endif
                        @if($showCum)    <th style="min-width:34px;" title="(BF+Total)÷2">Cum</th> @endif
                        @if($showGrade)  <th style="min-width:28px;">Grd</th>   @endif
                        {{-- 4 position sub-headers with colour coding --}}
                        @if($showPosCC)  <th style="min-width:32px;background:#1a2d5a;color:var(--pos-cc-bg);" title="Class position ranked by Cumulative (all arms)">C/C</th>  @endif
                        @if($showPosCT)  <th style="min-width:32px;background:#1a1540;color:var(--pos-ct-bg);" title="Class position ranked by Total (all arms)">C/T</th>   @endif
                        @if($showPosAT)  <th style="min-width:32px;background:#2a1e00;color:var(--pos-at-bg);" title="Arm position ranked by Total (this arm only)">A/T</th>   @endif
                        @if($showPosAC)  <th style="min-width:32px;background:#0a2210;color:var(--pos-ac-bg);" title="Arm position ranked by Cumulative (this arm only)">A/C</th>  @endif
                        @if($showAvg)    <th style="min-width:30px;">Avg</th>   @endif
                        @if($showRemark) <th style="min-width:42px;">Rmk</th>   @endif
                    @endforeach

                    <th style="min-width:40px;background:#0a2240;border-left:2px solid var(--cb-teal);">View</th>

                    @if($showGPA)       <th style="background:#0a1e38;color:#93c5fd;min-width:34px;border-left:2px solid #3b82f6;">GPA</th>   @endif
                    @if($showCGPA)      <th style="background:#0a1e38;color:#86efac;min-width:34px;">CGPA</th>  @endif
                    @if($showGPAGrade)  <th style="background:#0a1e38;color:#fcd34d;min-width:28px;">GGrd</th> @endif
                    @if($showNumSub)    <th style="background:#0a1e38;color:#a8d4ef;min-width:28px;">NS</th>   @endif
                    @if($showTotalGP)   <th style="background:#0a1e38;color:#a8d4ef;min-width:34px;">TGP</th>  @endif
                </tr>
            </thead>
            <tbody>
                @foreach($studentRows as $idx => $stu)
                    @php
                        $sid      = $stu['id'];
                        $posCum   = $stu['position_cum']  ?? 0;
                        $posTerm  = $stu['position_term'] ?? 0;
                        $hasFailure = false;
                        foreach($stu['subjects'] as $sd) { if(($sd['grade']??'')=='F9'){$hasFailure=true;break;} }
                        $hasPic   = !empty($stu['picture']) && $stu['picture'] !== 'unnamed.jpg';
                        $imgSrc   = $hasPic ? asset('storage/student_avatars/' . basename($stu['picture'])) : null;
                        $initials = strtoupper(substr($stu['lastname']??'',0,1).substr($stu['firstname']??'',0,1)) ?: 'ST';
                        $fullName = trim(($stu['lastname']??'').' '.($stu['firstname']??''));

                        $subjectCount    = count($subjects);
                        $totalObtainable = $subjectCount * 100;
                        $totalObtained   = $stu['total_cum']  ?? 0;
                        $termObtained    = $stu['total_term'] ?? 0;
                        $termPct = $totalObtainable>0 ? round($termObtained/$totalObtainable*100,1) : 0;
                        $cumPct  = $totalObtainable>0 ? round($totalObtained/$totalObtainable*100,1) : 0;

                        $gradesForPopup = [];
                        foreach($subjects as $subId => $subInfo) {
                            $sd = $stu['subjects'][$subId] ?? [];
                            $gradesForPopup[] = [
                                'subject'    => $subInfo['subject_name'],
                                'term_score' => $sd['total'] ?? 0,
                                'cum_score'  => $sd['cum']   ?? 0,
                                'bf_score'   => $sd['bf']    ?? 0,
                                'grade'      => $sd['grade'] ?? '-',
                                // 4 positions per subject
                                'pos_cc' => $sd['pos_class_cum']   ?? null,
                                'pos_ct' => $sd['pos_class_total'] ?? null,
                                'pos_at' => $sd['pos_arm_total']   ?? null,
                                'pos_ac' => $sd['pos_arm_cum']     ?? null,
                            ];
                        }
                        $posTotal = count($studentRows);
                    @endphp
                    <tr data-student-id="{{ $sid }}"
                        data-student-name="{{ strtolower($fullName) }}"
                        data-admission="{{ strtolower($stu['admissionno']) }}"
                        data-gpa="{{ $stu['gpa'] }}"
                        data-total-cum="{{ $totalObtained }}"
                        data-total-term="{{ $termObtained }}"
                        data-has-failure="{{ $hasFailure?'true':'false' }}"
                        data-cum-pct="{{ $cumPct }}"
                        data-term-pct="{{ $termPct }}"
                        data-pos-cum="{{ $posCum }}"
                        style="animation-delay:{{ min($idx * 0.04, 0.5) }}s;">

                        <td>{{ $idx + 1 }}</td>

                        @if($showOvPosCum || $showOvPosTerm)
                        <td style="text-align:center;white-space:nowrap;">
                            <div class="pos-dual" data-tooltip="Term: {{ ordinalSuffix($posTerm) }} · Cum: {{ ordinalSuffix($posCum) }}">
                                @if($showOvPosTerm)<span class="pos-term-lbl">T:{{ $posTerm }}</span>@endif
                                @if($showOvPosCum) <span class="pos-cum-lbl">C:{{ $posCum }}</span>@endif
                            </div>
                        </td>
                        @endif

                        @if($showAdmNo)
                            <td class="adm-cell">{{ $stu['admissionno'] }}</td>
                        @endif

                        <td class="student-info-cell">
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($imgSrc)
                                    <div class="cb-avatar"><img src="{{ $imgSrc }}" alt="{{ $fullName }}" onerror="var p=this.closest('.cb-avatar');p.classList.add('cb-avatar-initials');p.textContent='{{ $initials }}'"></div>
                                @else
                                    <div class="cb-avatar cb-avatar-initials">{{ $initials }}</div>
                                @endif
                                <div>
                                    <div style="font-weight:700;font-size:12px;color:var(--cb-navy);">{{ strtoupper($stu['lastname']) }}, {{ $stu['firstname'] }}</div>
                                    @if(!empty($stu['arm']))<div style="font-size:10px;color:var(--cb-muted);">Arm {{ $stu['arm'] }}</div>@endif
                                </div>
                            </div>
                        </td>

                        @if($showGender)<td style="font-size:10px;">{{ substr($stu['gender']??'',0,1) }}</td>@endif

                        {{-- Subject cells --}}
                        @foreach($subjects as $subId => $subInfo)
                            @php
                                $sd  = $stu['subjects'][$subId] ?? [];
                                $g   = $sd['grade'] ?? '-';
                                $gc  = $gradeColors[$g] ?? '';
                                $bfVal      = $sd['bf']    ?? 0;
                                $totalVal   = $sd['total'] ?? 0;
                                $cumVal     = $sd['cum']   ?? 0;
                                // If BF > 0 it was derived from previous term — show dotted underline
                                $bfDerived  = $bfVal > 0;
                                // Position values
                                $pCC = $sd['pos_class_cum']   ?? null;
                                $pCT = $sd['pos_class_total'] ?? null;
                                $pAT = $sd['pos_arm_total']   ?? null;
                                $pAC = $sd['pos_arm_cum']     ?? null;
                            @endphp
                            @foreach($activeAssessments as $aIdx => $a)
                                @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
                                <td class="score-cell {{ $aIdx===0?'sub-boundary':'' }}"
                                    style="{{ $aIdx===0?'border-left:1.5px solid #2563eb;':'' }}">
                                    {{ $as>0 ? number_format($as,1) : '—' }}
                                </td>
                            @endforeach
                            @if($showTotal)
                                <td class="score-cell {{ $gc }}">
                                    {{ $totalVal>0 ? number_format($totalVal,1) : '—' }}
                                </td>
                            @endif
                            @if($showBF)
                                {{-- Dotted underline when BF is derived from prev. term --}}
                                <td class="score-cell {{ $bfDerived?'bf-derived':'' }}"
                                    title="{{ $bfDerived?'Carried forward from previous term cum':'No BF (first term)' }}">
                                    {{ $bfVal>0 ? number_format($bfVal,1) : '—' }}
                                </td>
                            @endif
                            @if($showCum)
                                <td class="score-cell {{ $gc }}" style="font-weight:700;"
                                    title="Cum = (BF {{ number_format($bfVal,1) }} + Total {{ number_format($totalVal,1) }}) ÷ 2">
                                    {{ $cumVal>0 ? number_format($cumVal,1) : '—' }}
                                </td>
                            @endif
                            @if($showGrade)
                                <td class="score-cell {{ $gc }}" style="font-weight:700;">{{ $g }}</td>
                            @endif

                            {{-- 4 position columns with distinct colour classes + medal decoration --}}
                            @if($showPosCC)
                                @php $pn=(int)($pCC??0); $medal=$pn===1?'pos-medal-1':($pn===2?'pos-medal-2':($pn===3?'pos-medal-3':'')); @endphp
                                <td class="score-cell pos-cc-cell {{ $medal }}"
                                    data-tooltip="Class pos by Cumulative: {{ ordinalSuffix($pCC) }}">
                                    {{ ordinalSuffix($pCC) }}
                                </td>
                            @endif
                            @if($showPosCT)
                                @php $pn=(int)($pCT??0); $medal=$pn===1?'pos-medal-1':($pn===2?'pos-medal-2':($pn===3?'pos-medal-3':'')); @endphp
                                <td class="score-cell pos-ct-cell {{ $medal }}"
                                    data-tooltip="Class pos by Total: {{ ordinalSuffix($pCT) }}">
                                    {{ ordinalSuffix($pCT) }}
                                </td>
                            @endif
                            @if($showPosAT)
                                @php $pn=(int)($pAT??0); $medal=$pn===1?'pos-medal-1':($pn===2?'pos-medal-2':($pn===3?'pos-medal-3':'')); @endphp
                                <td class="score-cell pos-at-cell {{ $medal }}"
                                    data-tooltip="Arm pos by Total: {{ ordinalSuffix($pAT) }}">
                                    {{ ordinalSuffix($pAT) }}
                                </td>
                            @endif
                            @if($showPosAC)
                                @php $pn=(int)($pAC??0); $medal=$pn===1?'pos-medal-1':($pn===2?'pos-medal-2':($pn===3?'pos-medal-3':'')); @endphp
                                <td class="score-cell pos-ac-cell {{ $medal }}"
                                    data-tooltip="Arm pos by Cumulative: {{ ordinalSuffix($pAC) }}">
                                    {{ ordinalSuffix($pAC) }}
                                </td>
                            @endif

                            @if($showAvg)
                                <td class="score-cell" style="font-size:10px;color:var(--cb-muted);">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td>
                            @endif
                            @if($showRemark)
                                <td class="score-cell" style="font-size:10px;white-space:nowrap;">{{ $sd['remark'] ?? '—' }}</td>
                            @endif
                        @endforeach

                        {{-- Eye button --}}
                        <td style="text-align:center;border-left:2px solid var(--cb-teal);background:#f0fdf9;">
                            <button type="button" class="grade-trigger-btn"
                                    data-sid="{{ $sid }}"
                                    data-sname="{{ $fullName }}"
                                    data-sadm="{{ $stu['admissionno'] }}"
                                    data-term-obtained="{{ $termObtained }}"
                                    data-cum-obtained="{{ $totalObtained }}"
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

                        @if($showGPA)       <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>    @endif
                        @if($showCGPA)      <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td> @endif
                        @if($showGPAGrade)  @php $ggc=$gradeColors[$stu['gpa_grade']??'-']??''; @endphp
                                            <td class="gpa-cell {{ $ggc }}" style="font-weight:700;">{{ $stu['gpa_grade']??'—' }}</td> @endif
                        @if($showNumSub)    <td>{{ $stu['num_subjects']??'—' }}</td>  @endif
                        @if($showTotalGP)   <td>{{ number_format($stu['total_grade_points'],1) }}</td> @endif
                    </tr>
                @endforeach

                {{-- Stats rows --}}
                @foreach([['CLASS AVG','avg',''],['HIGHEST','highest','stats-hi'],['LOWEST','lowest','stats-lo']] as [$lbl,$key,$cls])
                <tr class="stats-row {{ $cls }}">
                    <td class="stats-label" colspan="{{ $frozenCols }}">{{ $lbl }}</td>
                    @foreach($subjects as $subId => $subInfo)
                        @php $st=$subjectStats[$subId]??[]; @endphp
                        @foreach($activeAssessments as $a) <td>—</td> @endforeach
                        @if($showTotal)  <td>{{ $st[$key]??'—' }}</td>  @endif
                        @if($showBF)     <td>—</td>                     @endif
                        @if($showCum)    <td>—</td>                     @endif
                        @if($showGrade)  <td>—</td>                     @endif
                        @if($showPosCC)  <td>—</td>                     @endif
                        @if($showPosCT)  <td>—</td>                     @endif
                        @if($showPosAT)  <td>—</td>                     @endif
                        @if($showPosAC)  <td>—</td>                     @endif
                        @if($showAvg)    <td>{{ $key==='avg'?($st['avg']??'—'):'—' }}</td> @endif
                        @if($showRemark) <td>—</td>                     @endif
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
    <div class="card-header-custom"><i class="ri-bar-chart-2-line me-2"></i>Subject Performance Summary</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:12px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="min-width:160px;color:var(--cb-navy);">Subject</th>
                    <th style="text-align:center;color:var(--cb-navy);">Avg</th>
                    <th style="text-align:center;color:var(--cb-navy);">Highest</th>
                    <th style="text-align:center;color:var(--cb-navy);">Lowest</th>
                    <th style="text-align:center;color:var(--cb-navy);">Passed</th>
                    <th style="text-align:center;color:var(--cb-navy);">Failed</th>
                    <th style="text-align:center;color:var(--cb-navy);">Pass Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subId => $subInfo)
                    @php $st=$subjectStats[$subId]??[]; $p=$st['passed']??0; $f=$st['failed']??0; $t=$p+$f; $pr=$t>0?round($p/$t*100):0; @endphp
                    <tr onmouseover="this.style.background='#f0fdf9'" onmouseout="this.style.background=''">
                        <td style="font-weight:600;color:var(--cb-navy);">{{ $subInfo['subject_name'] }}@if(!empty($subInfo['subject_code']))<span class="text-muted" style="font-size:10px;"> ({{ $subInfo['subject_code'] }})</span>@endif</td>
                        <td style="text-align:center;font-weight:700;">{{ $st['avg']??'—' }}</td>
                        <td style="text-align:center;color:#16a34a;font-weight:700;">{{ $st['highest']??'—' }}</td>
                        <td style="text-align:center;color:#dc2626;font-weight:700;">{{ $st['lowest']??'—' }}</td>
                        <td style="text-align:center;color:#16a34a;">{{ $p }}</td>
                        <td style="text-align:center;color:#dc2626;">{{ $f }}</td>
                        <td style="text-align:center;"><span class="{{ $pr>=50?'score-green':'score-red' }}">{{ $pr }}%</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Signature Block ── --}}
<div class="cb-card mb-4 no-print">
    <div class="cb-card-header"><h6 style="margin:0;font-size:13px;font-weight:700;color:var(--cb-navy);"><i class="ri-pen-nib-line me-1" style="color:var(--cb-teal)"></i>Authorisation Signatures</h6></div>
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

</div></div></div>

<script>
(function(){
'use strict';

var GRADE_COLORS = {'A1':'grade-a1','B2':'grade-b2','B3':'grade-b3','C4':'grade-c4','C5':'grade-c5','C6':'grade-c6','D7':'grade-d7','E8':'grade-e8','F9':'grade-f9','-':''};

function esc(s){ var d=document.createElement('div');d.textContent=s||'';return d.innerHTML; }

function toast(msg,type){
    document.querySelectorAll('.cb-toast').forEach(function(t){t.remove();});
    var icons={success:'checkbox-circle-fill',error:'error-warning-fill',info:'information-fill',warning:'alert-fill'};
    var el=document.createElement('div');
    el.className='cb-toast cb-toast-'+(type||'info');
    el.innerHTML='<i class="ri-'+(icons[type]||icons.info)+'" style="font-size:18px;flex-shrink:0;"></i> '+esc(msg);
    document.body.appendChild(el);
    setTimeout(function(){el.remove();},4000);
}

function closeGradePop(){
    var g=document.getElementById('cbGradePopup');
    var b=document.getElementById('cbPopupBackdrop');
    if(g){g.classList.remove('is-open');delete g.dataset.activeSid;}
    if(b) b.style.display='none';
}

function getPctClass(p){ return p<40?'score-red':(p<70?'score-amber':'score-green'); }
function ordinal(n){ if(!n||n<=0)return '—'; var l2=n%100,l1=n%10; if(l2>=11&&l2<=13)return n+'th'; return n+({1:'st',2:'nd',3:'rd'}[l1]||'th'); }

function posChipHtml(val, type){
    if(!val||val<=0) return '<span style="color:#94a3b8;font-size:10px;">—</span>';
    var n=parseInt(val);
    var medal = n===1?' 🥇':(n===2?' 🥈':(n===3?' 🥉':''));
    var styles={
        cc:'background:var(--pos-cc-bg);color:var(--pos-cc-txt);border:1px solid var(--pos-cc-bdr);',
        ct:'background:var(--pos-ct-bg);color:var(--pos-ct-txt);border:1px solid var(--pos-ct-bdr);',
        at:'background:var(--pos-at-bg);color:var(--pos-at-txt);border:1px solid var(--pos-at-bdr);',
        ac:'background:var(--pos-ac-bg);color:var(--pos-ac-txt);border:1px solid var(--pos-ac-bdr);',
    };
    var st=styles[type]||'';
    return '<span style="'+st+'border-radius:4px;padding:1px 5px;font-size:10px;font-weight:800;">'+ordinal(n)+medal+'</span>';
}

function openGradePop(btn){
    var sid          = btn.getAttribute('data-sid');
    var name         = btn.getAttribute('data-sname');
    var adm          = btn.getAttribute('data-sadm')||'';
    var termObtained = parseFloat(btn.getAttribute('data-term-obtained')||0);
    var cumObtained  = parseFloat(btn.getAttribute('data-cum-obtained')||0);
    var obtainable   = parseFloat(btn.getAttribute('data-obtainable')||0);
    var termPct      = parseFloat(btn.getAttribute('data-term-pct')||0);
    var cumPct       = parseFloat(btn.getAttribute('data-cum-pct')||0);
    var gpa          = parseFloat(btn.getAttribute('data-gpa')||0);
    var gpaGrade     = btn.getAttribute('data-gpa-grade')||'—';
    var posCum       = parseInt(btn.getAttribute('data-pos-cum')||0);
    var posTerm      = parseInt(btn.getAttribute('data-pos-term')||0);
    var posTotal     = parseInt(btn.getAttribute('data-pos-total')||0);
    var grades=[];
    try{grades=JSON.parse(btn.getAttribute('data-grades')||'[]');}catch(e){}

    var gpop=document.getElementById('cbGradePopup');
    if(!gpop) return;

    document.getElementById('gpopTitle').innerHTML='<i class="ri-bar-chart-line me-1"></i>'+esc(name)+"'s Performance";

    var termColor=termPct<40?'#f43f5e':(termPct<70?'#f59e0b':'#22c55e');
    var cumColor =cumPct <40?'#f43f5e':(cumPct <70?'#f59e0b':'#22c55e');
    var posCumDisplay =posCum  ? ordinal(posCum) +' / '+posTotal : '—';
    var posTermDisplay=posTerm ? ordinal(posTerm)+' / '+posTotal : '—';

    // Build grades table with all 4 position columns
    var rows='';
    if(grades.length){
        grades.forEach(function(g){
            var tC=(g.term_score>0&&g.term_score<50)?'score-red':(g.term_score>=70?'score-green':(g.term_score>0?'score-amber':''));
            var cC=(g.cum_score >0&&g.cum_score <50)?'score-red':(g.cum_score >=70?'score-green':(g.cum_score >0?'score-amber':''));
            var grBadge=g.grade&&g.grade!=='-'
                ?'<span class="badge '+(GRADE_COLORS[g.grade]||'')+'" style="font-size:9px;border-radius:6px;">'+esc(g.grade)+'</span>'
                :'<span style="color:#94a3b8;">—</span>';
            var tS=g.term_score>0?parseFloat(g.term_score).toFixed(1):'—';
            var cS=g.cum_score >0?parseFloat(g.cum_score).toFixed(1) :'—';
            var bS=g.bf_score  >0?parseFloat(g.bf_score).toFixed(1)  :'—';
            rows+='<tr>';
            rows+='<td style="text-align:left;padding-left:10px;font-weight:700;">'+esc(g.subject)+'</td>';
            rows+='<td><span class="'+tC+'">'+tS+'</span><br><small style="color:#64748b;font-size:9px;">BF:'+bS+'</small></td>';
            rows+='<td><span class="'+cC+'" style="font-weight:800;">'+cS+'</span></td>';
            rows+='<td>'+grBadge+'</td>';
            rows+='<td>'+posChipHtml(g.pos_cc,'cc')+'</td>';
            rows+='<td>'+posChipHtml(g.pos_ct,'ct')+'</td>';
            rows+='<td>'+posChipHtml(g.pos_at,'at')+'</td>';
            rows+='<td>'+posChipHtml(g.pos_ac,'ac')+'</td>';
            rows+='</tr>';
        });
    } else {
        rows='<tr><td colspan="8" class="text-center text-muted py-3">No subject records</td></tr>';
    }

    var body=document.getElementById('gpopBody');
    body.innerHTML=
        '<div class="gpop-perf-strip">'
       +'<div style="font-size:11px;font-weight:700;opacity:.8;margin-bottom:6px;"><i class="ri-dashboard-line me-1"></i>Performance Snapshot</div>'
       +'<div class="gpop-perf-grid">'
       +'<div class="gpop-perf-item"><div class="gpop-perf-lbl">Adm No</div><div class="gpop-perf-val" style="font-size:12px;">'+esc(adm)+'</div></div>'
       +'<div class="gpop-perf-item"><div class="gpop-perf-lbl">Term Total</div><div class="gpop-perf-val">'+termObtained.toFixed(1)+'</div></div>'
       +'<div class="gpop-perf-item"><div class="gpop-perf-lbl">Cum Total</div><div class="gpop-perf-val">'+cumObtained.toFixed(1)+'</div></div>'
       +'<div class="gpop-perf-item"><div class="gpop-perf-lbl">% (Term)</div><div class="gpop-perf-val '+getPctClass(termPct)+'" data-anim-pct="'+termPct+'">0%</div></div>'
       +'<div class="gpop-perf-item"><div class="gpop-perf-lbl">% (Cum)</div><div class="gpop-perf-val '+getPctClass(cumPct)+'" data-anim-pct="'+cumPct+'">0%</div></div>'
       +'<div class="gpop-perf-item"><div class="gpop-perf-lbl">GPA</div><div class="gpop-perf-val">'+gpa.toFixed(2)+'</div></div>'
       +'</div>'
       // progress bars
       +'<div style="margin-top:8px;">'
       +'<div style="font-size:9px;opacity:.7;margin-bottom:2px;">Term %</div>'
       +'<div style="background:rgba(255,255,255,.2);border-radius:20px;height:6px;overflow:hidden;margin-bottom:4px;">'
       +'<div style="width:'+termPct+'%;height:100%;border-radius:20px;background:'+termColor+';transition:width 1s ease;"></div></div>'
       +'<div style="font-size:9px;opacity:.7;margin-bottom:2px;">Cum %</div>'
       +'<div style="background:rgba(255,255,255,.2);border-radius:20px;height:6px;overflow:hidden;">'
       +'<div style="width:'+cumPct+'%;height:100%;border-radius:20px;background:'+cumColor+';transition:width 1s ease;"></div></div>'
       +'</div></div>'
       // position badges
       +'<div style="display:flex;gap:6px;align-items:center;margin-bottom:10px;flex-wrap:wrap;">'
       +'<strong style="font-size:11px;color:var(--cb-navy);">Overall:</strong>'
       +'<span style="background:var(--pos-at-bg);color:var(--pos-at-txt);border:1.5px solid var(--pos-at-bdr);border-radius:12px;padding:2px 10px;font-size:11px;font-weight:800;">Term: '+posTermDisplay+'</span>'
       +'<span style="background:var(--pos-cc-bg);color:var(--pos-cc-txt);border:1.5px solid var(--pos-cc-bdr);border-radius:12px;padding:2px 10px;font-size:11px;font-weight:800;">Cum: '+posCumDisplay+'</span>'
       +'</div>'
       // grades table with 4 position columns
       +'<div class="gpop-scroll">'
       +'<table class="gpop-table"><thead><tr>'
       +'<th style="text-align:left;padding-left:10px;width:28%;">Subject</th>'
       +'<th style="width:14%;">Term/BF</th>'
       +'<th style="width:12%;">Cum</th>'
       +'<th style="width:10%;">Grd</th>'
       +'<th style="width:9%;background:#1a2d5a;color:var(--pos-cc-bg);" title="Class pos by Cum">C/C</th>'
       +'<th style="width:9%;background:#1a1540;color:var(--pos-ct-bg);" title="Class pos by Total">C/T</th>'
       +'<th style="width:9%;background:#2a1e00;color:var(--pos-at-bg);" title="Arm pos by Total">A/T</th>'
       +'<th style="width:9%;background:#0a2210;color:var(--pos-ac-bg);" title="Arm pos by Cum">A/C</th>'
       +'</tr></thead><tbody>'+rows+'</tbody></table>'
       +'</div>'
       // summary
       +'<div class="gpop-summary">'
       +'<div class="gpop-sum-item"><div class="gpop-sum-lbl">Term Total</div><div class="gpop-sum-val">'+termObtained.toFixed(1)+'</div></div>'
       +'<div class="gpop-sum-item"><div class="gpop-sum-lbl">Cum Total</div><div class="gpop-sum-val">'+cumObtained.toFixed(1)+'</div></div>'
       +'<div class="gpop-sum-item"><div class="gpop-sum-lbl">Obtainable</div><div class="gpop-sum-val">'+obtainable+'</div></div>'
       +'<div class="gpop-sum-item"><div class="gpop-sum-lbl">% (Term)</div><div class="gpop-sum-val '+getPctClass(termPct)+'">'+termPct.toFixed(1)+'%</div></div>'
       +'<div class="gpop-sum-item"><div class="gpop-sum-lbl">% (Cum)</div><div class="gpop-sum-val '+getPctClass(cumPct)+'">'+cumPct.toFixed(1)+'%</div></div>'
       +'<div class="gpop-sum-item"><div class="gpop-sum-lbl">GPA / Grade</div><div class="gpop-sum-val" style="font-size:13px;">'+gpa.toFixed(2)+' / '+esc(gpaGrade)+'</div></div>'
       +'</div>';

    var pw=580, ph=Math.min(640,window.innerHeight-40);
    var rect=btn.getBoundingClientRect(), vw=window.innerWidth, vh=window.innerHeight;
    var top=rect.bottom+8, left=rect.left+(rect.width/2)-(pw/2);
    if(top+ph>vh-8) top=Math.max(8,rect.top-ph-8);
    if(left<8) left=8;
    if(left+pw>vw-8) left=vw-pw-8;
    gpop.style.width=pw+'px'; gpop.style.top=top+'px'; gpop.style.left=left+'px'; gpop.style.maxHeight=ph+'px';
    gpop.dataset.activeSid=sid;
    gpop.classList.add('is-open');
    document.getElementById('cbPopupBackdrop').style.display='block';

    // Animate percentage counters
    setTimeout(function(){
        body.querySelectorAll('[data-anim-pct]').forEach(function(el){
            var target=parseFloat(el.getAttribute('data-anim-pct')||0);
            var steps=50,step=0,current=0,inc=target/steps;
            var t=setInterval(function(){
                step++;current+=inc;
                if(step>=steps){current=target;clearInterval(t);}
                el.textContent=current.toFixed(1)+'%';
            },800/steps);
        });
    },60);
}

/* ── Search ── */
var tableRows=document.querySelectorAll('#broadsheetTable tbody tr:not(.stats-row)');

document.getElementById('searchStudent').addEventListener('input',function(){
    var q=this.value.toLowerCase().trim();
    var count=0;
    tableRows.forEach(function(r){
        var n=r.getAttribute('data-student-name')||'';
        var a=r.getAttribute('data-admission')||'';
        var show=!q||n.includes(q)||a.includes(q);
        r.style.display=show?'':'none';
        if(show) count++;
    });
    if(q) toast('Found '+count+' student(s)','info');
});

document.getElementById('locateStudent').addEventListener('change',function(){
    var val=this.value; if(!val) return;
    tableRows.forEach(function(r){r.style.outline='';r.style.backgroundColor='';});
    if(val==='top5') highlightTop(5);
    else if(val==='top10') highlightTop(10);
    else if(val==='failures') highlightFailures();
    else if(val==='below_avg') highlightBelowAvg();
    else if(val==='first_class') highlightFirstClass();
    else if(val.startsWith('student_')){
        var id=val.replace('student_','');
        var row=document.querySelector('tr[data-student-id="'+id+'"]');
        if(row){
            row.style.outline='3px solid var(--cb-teal)';row.style.backgroundColor='#f0fdf9';
            row.scrollIntoView({behavior:'smooth',block:'center'});
            toast('Located: '+(row.getAttribute('data-student-name')||''),'success');
        }
    }
    setTimeout(function(){document.getElementById('locateStudent').value='';},120);
});

function highlightTop(n){
    var rows=Array.from(tableRows).filter(function(r){return r.style.display!=='none';});
    rows.sort(function(a,b){return parseFloat(b.dataset.totalCum||0)-parseFloat(a.dataset.totalCum||0);});
    rows.slice(0,n).forEach(function(r){r.style.backgroundColor='#fef9c3';r.style.outline='2px solid #d97706';});
    toast('Top '+n+' students highlighted','success');
}
function highlightFailures(){
    var c=0;
    tableRows.forEach(function(r){if(r.dataset.hasFailure==='true'){r.style.backgroundColor='#fee2e2';r.style.outline='2px solid #dc2626';c++;}});
    toast(c+' student(s) with F9 highlighted','warning');
}
function highlightBelowAvg(){
    var totals=Array.from(tableRows).map(function(r){return parseFloat(r.dataset.totalCum||0);}).filter(function(v){return v>0;});
    var avg=totals.length?totals.reduce(function(a,b){return a+b;},0)/totals.length:0;
    var c=0;
    tableRows.forEach(function(r){var v=parseFloat(r.dataset.totalCum||0);if(v>0&&v<avg){r.style.backgroundColor='#fff7ed';r.style.outline='2px solid #f97316';c++;}});
    toast(c+' student(s) below class average','info');
}
function highlightFirstClass(){
    var c=0;
    tableRows.forEach(function(r){if(parseInt(r.dataset.posCum||0)===1){r.style.backgroundColor='#fef9c3';r.style.outline='3px solid #f59e0b';c++;}});
    toast(c+' student(s) in 1st position highlighted','success');
}

window.scrollToTop=function(){window.scrollTo({top:0,behavior:'smooth'});};

function animateNumber(elId,target,suffix,decimals){
    var el=document.getElementById(elId);if(!el)return;
    var steps=60,step=0,current=0,inc=target/steps;
    var t=setInterval(function(){step++;current+=inc;if(step>=steps){current=target;clearInterval(t);}el.textContent=current.toFixed(decimals||0)+(suffix||'');},800/steps);
}

document.addEventListener('DOMContentLoaded',function(){
    // Move popup to body
    ['cbGradePopup','cbPopupBackdrop'].forEach(function(id){
        var el=document.getElementById(id);
        if(el&&el.parentNode!==document.body) document.body.appendChild(el);
    });

    // Open popup
    document.addEventListener('click',function(e){
        var btn=e.target.closest('.grade-trigger-btn');
        if(!btn) return;
        e.stopPropagation();e.preventDefault();
        var gpop=document.getElementById('cbGradePopup');
        if(gpop&&gpop.classList.contains('is-open')&&gpop.dataset.activeSid===btn.getAttribute('data-sid')){closeGradePop();return;}
        closeGradePop();
        setTimeout(function(){openGradePop(btn);},16);
    });

    document.getElementById('gpopCloseBtn').addEventListener('click',closeGradePop);
    document.addEventListener('click',function(e){if(e.target===document.getElementById('cbPopupBackdrop'))closeGradePop();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closeGradePop();});

    // Animate stats
    (function(){
        var rows=Array.from(document.querySelectorAll('#broadsheetTable tbody tr[data-cum-pct]'));
        if(!rows.length) return;
        var totalPct=0,topCum=-1,topName='—';
        rows.forEach(function(r){
            totalPct+=parseFloat(r.getAttribute('data-cum-pct')||0);
            var cum=parseFloat(r.getAttribute('data-total-cum')||0);
            if(cum>topCum){topCum=cum;topName=r.getAttribute('data-student-name')||'—';}
        });
        var avg=rows.length?totalPct/rows.length:0;
        animateNumber('statAvgPct',avg,'%',1);
        var topEl=document.getElementById('statTopPerformer');
        if(topEl) topEl.textContent=topName.split(' ').map(function(w){return w.charAt(0).toUpperCase()+w.slice(1);}).join(' ');
    })();

    // Animate position-1 medal cells on load
    setTimeout(function(){
        document.querySelectorAll('.pos-medal-1').forEach(function(el){
            el.style.animation='posFlash 2s ease-in-out 3';
        });
    },800);
});
})();
</script>
@endsection
