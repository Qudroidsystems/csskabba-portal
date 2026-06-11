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

/* ── Meta grid ── */
.meta-grid {
    display:flex; border:1px solid var(--cb-border); background:var(--cb-surface);
    border-radius:8px; overflow:hidden; margin-bottom:14px;
}
.meta-cell { flex:1; padding:10px 14px; border-right:1px solid var(--cb-border); transition:all .2s ease; }
.meta-cell:last-child { border-right:none; }
.meta-label { font-size:10px; color:var(--cb-muted); text-transform:uppercase; letter-spacing:.4px; display:block; }
.meta-value { font-size:13px; font-weight:700; color:var(--cb-navy); }

/* ── Grade key ── */
.grade-key {
    display:flex; align-items:center; border:1px solid var(--cb-border);
    padding:6px 14px; background:#fafafa; border-radius:8px; margin-bottom:14px;
    flex-wrap:wrap; gap:6px;
}

/* ── Card & Table ── */
.cb-card {
    background:var(--cb-white); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); box-shadow:var(--cb-shadow);
    overflow:visible;
}
.cb-card-header {
    padding:18px 24px; border-bottom:1px solid var(--cb-border);
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
    background:linear-gradient(to right,#f8fafc,#f0fdf9);
    border-radius:var(--cb-radius) var(--cb-radius) 0 0;
}

/* ── Broadsheet table ── */
.broadsheet-table {
    width:100%;
    border-collapse:collapse;
    font-size:11px;
    background:white;
    border:1.5px solid var(--cb-navy);
}
.broadsheet-table thead tr.subject-header th {
    background:var(--cb-navy); color:#fff; text-align:center;
    padding:7px 4px; border:0.5px solid rgba(37,99,235,.35);
    font-weight:600; font-size:11.5px; white-space:nowrap;
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

.broadsheet-table tbody tr { transition:all .25s ease; }
.broadsheet-table tbody tr:nth-child(odd)  { background:#ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background:#f0f4fa; }
.broadsheet-table tbody tr:hover { background-color:#e8f0fe !important; }

.broadsheet-table tbody td {
    padding:5px 4px; border:0.5px solid #c5d3e8;
    text-align:center; vertical-align:middle;
    white-space:nowrap; font-size:11px;
}
.broadsheet-table tbody td.student-info-cell {
    text-align:left; padding-left:8px; font-weight:600;
    min-width:200px;
}

/* ── Promotion status cells ── */
.promo-cell { text-align: center; border-left: 2px solid #7c3aed !important; }

.promo-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 700; white-space: nowrap;
    transition: all .2s ease;
}
.promo-promoted     { background: #d1fae5; color: #065f46; }
.promo-trial        { background: #fef3c7; color: #92400e; }
.promo-see_principal{ background: #dbeafe; color: #1e40af; }
.promo-repeated     { background: #fee2e2; color: #991b1b; }
.promo-awaiting     { background: #f1f5f9; color: #475569; }

.promo-header-th {
    background: #3b0764 !important;
    border-left: 2px solid #7c3aed !important;
    min-width: 110px;
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

/* ── GPA cells ── */
.gpa-cell { background:#eff6ff !important; color:#1e3a8a; font-weight:700; border-left:1.5px solid #3b82f6 !important; }

/* ── Position badge ── */
.pos-dual {
    display:inline-flex; flex-direction:column; align-items:center;
    gap:2px; cursor:pointer;
}
.pos-dual .pos-term-lbl { font-size:9px; font-weight:700; color:#92400e; background:#fef3c7; border-radius:4px; padding:1px 5px; line-height:1.4; }
.pos-dual .pos-cum-lbl  { font-size:9px; font-weight:700; color:#1e40af; background:#dbeafe; border-radius:4px; padding:1px 5px; line-height:1.4; }

/* ── Stats rows ── */
.stats-row td { background:var(--cb-navy) !important; color:white; font-weight:700; padding:5px 4px; text-align:center; border:0.5px solid #163785; font-size:11px; }
.stats-row td.stats-label { text-align:left; padding-left:8px; font-size:10px; }
.stats-hi td { background:#0a2240 !important; }
.stats-lo td { background:#111c2a !important; }

/* ── Toolbar ── */
.cb-search { position:relative; }
.cb-search input {
    width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--cb-border);
    border-radius:10px; font-size:13px; background:var(--cb-surface);
    transition:all .25s ease;
}
.cb-search input:focus { border-color:var(--cb-teal); outline:none; }
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

/* ── Student List Modal ── */
.slist-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 99990;
    align-items: center; justify-content: center;
}
.slist-modal-overlay.open { display: flex; }

.slist-modal {
    background: white; border-radius: 16px;
    width: 620px; max-width: calc(100vw - 32px);
    max-height: calc(100vh - 40px); overflow: hidden;
    display: flex; flex-direction: column;
    box-shadow: 0 24px 64px rgba(0,0,0,.25);
}
.slist-modal-header {
    background: linear-gradient(135deg, #3b0764, #7c3aed);
    color: white; padding: 18px 22px;
    display: flex; align-items: center; justify-content: space-between;
}
.slist-modal-body { padding: 20px 22px; overflow-y: auto; flex: 1; }
.slist-modal-footer {
    padding: 14px 22px; border-top: 1px solid #e2e8f0;
    display: flex; justify-content: flex-end; gap: 10px;
    background: #f8fafc;
}
.promo-order-list { list-style: none; padding: 0; margin: 0; }
.promo-order-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; background: white;
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    margin-bottom: 6px; cursor: grab;
}
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.field-checkbox-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; border: 1.5px solid #e2e8f0;
    border-radius: 8px; cursor: pointer;
}
.slist-section-title {
    font-size: 12px; font-weight: 700; color: #3b0764;
    text-transform: uppercase; margin-bottom: 10px;
}

/* ── Performance popup ── */
#cbGradePopup {
    display: none; position: fixed; z-index: 99999;
    background: white; border: 2px solid var(--cb-teal);
    border-radius: 16px; width: 560px; max-width: 90vw;
    max-height: 80vh; overflow: hidden; flex-direction: column;
}
#cbGradePopup.is-open { display: flex; }
.gpop-hdr {
    background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal));
    color: white; padding: 12px 16px;
    display: flex; justify-content: space-between;
}
.gpop-body { padding: 16px; overflow-y: auto; flex: 1; }
.gpop-scroll { max-height: 300px; overflow-y: auto; }
.gpop-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.gpop-table th, .gpop-table td { padding: 6px; border: 1px solid #e2e8f0; text-align: center; }
.gpop-table th:first-child, .gpop-table td:first-child { text-align: left; }
#cbPopupBackdrop { display: none; position: fixed; inset: 0; z-index: 99998; background: rgba(0,0,0,.3); }

/* ── School header ── */
.school-header-bar {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #2563eb 100%);
    border-radius: 10px; padding: 18px 24px; margin-bottom: 16px; color: white;
}

@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; font-size: 10px; }
    .cb-hero::before, .cb-hero::after { display: none; }
    .cb-stat, .cb-card { box-shadow: none !important; }
    @page { margin: 1.5cm 1.2cm; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

{{-- Hero --}}
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
                    <span class="cb-meta-pill"><i class="ri-links-line"></i>Combined Arms</span>
                @endif
            </div>
        </div>
        <a href="javascript:history.back()" class="btn-back"><i class="ri-arrow-left-line"></i> Back</a>
    </div>
</div>

{{-- Stats Cards --}}
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

{{-- School Header --}}
<div class="school-header-bar">
    <div class="d-flex align-items-center">
        @if(!empty($school_logo_base64))
            <img src="{{ $school_logo_base64 }}" alt="Logo" style="width:65px;height:65px;object-fit:contain;border-radius:50%;border:2px solid rgba(255,255,255,.4);margin-right:18px;">
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

{{-- Title Strip --}}
<div style="background:var(--cb-navy);color:white;text-align:center;padding:10px;font-size:15px;font-weight:700;letter-spacing:1.5px;border-radius:8px;margin-bottom:14px;">
    CLASS ACADEMIC BROADSHEET
    @if(!empty($is_combined))<span style="font-size:11px;opacity:.7;font-weight:400;margin-left:10px;">— Combined Arms</span>@endif
</div>

{{-- Meta Grid --}}
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
        <span class="meta-label">Generated</span>
        <span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span>
    </div>
</div>

{{-- Grade Key --}}
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
        <span class="badge" style="background:{{ $info[1] }};font-size:11px;border-radius:12px;padding:3px 9px;">{{ $grade }} ({{ $info[0] }})</span>
    @endforeach
    <span class="text-muted ms-2" style="font-size:11px;">
        <strong>BF</strong>=Brought Forward &nbsp; <strong>CUM</strong>=(BF+Total)÷2 &nbsp;
        <span style="background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 5px;">T-POS</span>=Overall Term Pos &nbsp;
        <span style="background:#dbeafe;color:#1e40af;border-radius:4px;padding:1px 5px;">C-POS</span>=Overall Cum Pos
    </span>
</div>

{{-- Toolbar --}}
<div class="cb-card mb-3 no-print">
    <div class="cb-card-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="cb-search" style="max-width:260px;">
                <i class="ri-search-line"></i>
                <input type="text" id="searchStudent" placeholder="Search name or admission no…">
            </div>
            <select class="form-select form-select-sm" id="locateStudent" style="max-width:260px;">
                <option value="">🔍 Quick Locate…</option>
                <option value="top5">🏆 Top 5 (by Cum)</option>
                <option value="top10">⭐ Top 10</option>
                <option value="failures">⚠️ Students with F9</option>
                <option value="below_avg">📉 Below Class Average</option>
                <option disabled>──────────</option>
                <option value="promoted">✅ Promoted Students</option>
                <option value="trial">⚠️ On Trial</option>
                <option value="see_principal">👤 See Principal</option>
                <option value="repeated">🔁 Repeat Students</option>
                <option value="awaiting">⏳ Awaiting Decision</option>
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="ri-printer-line me-1"></i>Print
            </button>
            <button class="btn btn-sm" onclick="openStudentListModal()" style="background:linear-gradient(135deg,#3b0764,#7c3aed);color:#fff;border:none;">
                <i class="ri-list-check-2 me-1"></i>Print Student List
            </button>
        </div>
    </div>
</div>

{{-- Grade Popup --}}
<div id="cbPopupBackdrop"></div>
<div id="cbGradePopup">
    <div class="gpop-hdr">
        <span id="gpopTitle">Performance Summary</span>
        <button type="button" id="gpopCloseBtn" style="background:transparent;border:none;color:white;font-size:20px;">&times;</button>
    </div>
    <div class="gpop-body" id="gpopBody"></div>
</div>

@php
    $selected = $selectedColumns ?? [];
    $showAll  = empty($selected);

    $showAdmNo   = $showAll || in_array('admission_no',   $selected);
    $showGender  = in_array('gender', $selected);
    $showPosTerm = $showAll || in_array('position_term',  $selected);
    $showPosCum  = $showAll || in_array('position_cum',   $selected);
    $showPromoStatus = $showAll || in_array('promotion_status', $selected);
    $showPromoLabel  = in_array('promotion_label', $selected);
    $showPromoRule   = in_array('promotion_rule_applied', $selected);

    $promoColspan = ($showPromoStatus ? 1 : 0) + ($showPromoLabel ? 1 : 0) + ($showPromoRule ? 1 : 0);
    $activeAssessments = $assessments->filter(fn($a) => empty($selected) || in_array('assessment_' . $a->id, $selected));

    $gradeColors = [
        'A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3',
        'C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6',
        'D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9',
    ];

    $frozenCols = 2 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0);
@endphp

{{-- Main Broadsheet Table --}}
<div class="cb-card mb-4">
    <div class="cb-card-header">
        <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--cb-navy);">
            <i class="ri-table-alt-line me-1" style="color:var(--cb-teal)"></i>
            Student Performance &amp; Scores
            <span class="badge ms-2" style="background:var(--cb-teal);color:#fff;">{{ $totalStudents }} Students</span>
        </h5>
    </div>
    <div style="overflow-x:auto;">
        <table class="broadsheet-table" id="broadsheetTable">
            <thead>
                <tr class="subject-header">
                    <th class="student-col" rowspan="2" style="width:40px;">#</th>
                    @if($showPosTerm || $showPosCum)
                        <th class="student-col" rowspan="2" style="width:70px;">Position</th>
                    @endif
                    @if($showAdmNo)
                        <th class="student-col" rowspan="2" style="min-width:90px;">Adm. No</th>
                    @endif
                    <th class="student-col" rowspan="2" style="min-width:200px;text-align:left;">Student Name</th>
                    @if($showGender)
                        <th class="student-col" rowspan="2" style="width:45px;">Sex</th>
                    @endif

                    @foreach($subjects as $subId => $subInfo)
                        <th class="subj-name-hdr" colspan="8">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))
                                <br><small>({{ $subInfo['subject_code'] }})</small>
                            @endif
                        </th>
                    @endforeach

                    <th class="subj-name-hdr" style="background:#0a2240;border-left:2px solid var(--cb-teal);">View</th>
                    <th style="background:#0a1e38;">GPA</th>
                    @if($promoColspan > 0)
                        <th colspan="{{ $promoColspan }}" class="promo-header-th">🎓 PROMOTION</th>
                    @endif
                </tr>
                <tr class="assessment-header">
                    @foreach($subjects as $subId => $subInfo)
                        <th style="min-width:36px;">CA1<br><small>/20</small></th>
                        <th style="min-width:36px;">CA2<br><small>/20</small></th>
                        <th style="min-width:40px;">EXAM<br><small>/60</small></th>
                        <th style="min-width:36px;">Total</th>
                        <th style="min-width:30px;">BF</th>
                        <th style="min-width:36px;">Cum</th>
                        <th style="min-width:30px;">Grd</th>
                        <th style="min-width:32px;">Pos</th>
                    @endforeach
                    <th style="min-width:44px;background:#0a2240;">View</th>
                    <th style="min-width:36px;">GPA</th>
                    @if($showPromoStatus)
                        <th style="background:#3b0764;color:#d8b4fe;min-width:100px;">Status</th>
                    @endif
                    @if($showPromoLabel)
                        <th style="background:#3b0764;color:#d8b4fe;min-width:120px;">Label</th>
                    @endif
                    @if($showPromoRule)
                        <th style="background:#3b0764;color:#d8b4fe;min-width:100px;">Rule</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($studentRows as $idx => $stu)
                <tr data-student-id="{{ $stu['id'] }}" data-student-name="{{ strtolower($stu['lastname'] . ' ' . $stu['firstname']) }}" data-admission="{{ strtolower($stu['admissionno']) }}">
                    <td>{{ $idx + 1 }}</td>

                    @if($showPosTerm || $showPosCum)
                        <td>
                            <div class="pos-dual">
                                <span class="pos-term-lbl">T:{{ $stu['position_term'] ?? 0 }}</span>
                                <span class="pos-cum-lbl">C:{{ $stu['position_cum'] ?? 0 }}</span>
                            </div>
                        </td>
                    @endif

                    @if($showAdmNo)
                        <td>{{ $stu['admissionno'] }}</td>
                    @endif

                    <td class="student-info-cell">
                        <div style="font-weight:700;">{{ strtoupper($stu['lastname']) }}, {{ $stu['firstname'] }}</div>
                    </td>

                    @if($showGender)
                        <td>{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
                    @endif

                    @foreach($subjects as $subId => $subInfo)
                        @php
                            $sd = $stu['subjects'][$subId] ?? [];
                            $g = $sd['grade'] ?? '-';
                            $gc = $gradeColors[$g] ?? '';
                        @endphp
                        <td class="score-cell">{{ number_format($sd['assessments'][$activeAssessments[0]->id] ?? 0, 1) }}</td>
                        <td class="score-cell">{{ number_format($sd['assessments'][$activeAssessments[1]->id] ?? 0, 1) }}</td>
                        <td class="score-cell">{{ number_format($sd['assessments'][$activeAssessments[2]->id] ?? 0, 1) }}</td>
                        <td class="score-cell {{ $gc }}">{{ number_format($sd['total'] ?? 0, 1) }}</td>
                        <td class="score-cell">{{ number_format($sd['bf'] ?? 0, 1) }}</td>
                        <td class="score-cell {{ $gc }}">{{ number_format($sd['cum'] ?? 0, 1) }}</td>
                        <td class="score-cell {{ $gc }}">{{ $g }}</td>
                        <td class="score-cell">{{ $sd['pos_class_cum'] ?? '—' }}</td>
                    @endforeach

                    <td style="text-align:center;">
                        <button class="grade-trigger-btn" data-sid="{{ $stu['id'] }}" data-sname="{{ $stu['lastname'] . ' ' . $stu['firstname'] }}" data-grades='@json(array_values($stu['subjects']))'>
                            <i class="ri-eye-line"></i>
                        </button>
                    </td>
                    <td class="gpa-cell">{{ number_format($stu['gpa'] ?? 0, 2) }}</td>

                    @if($showPromoStatus)
                        @php
                            $pStatus = $stu['promotion_status'] ?? 'awaiting';
                            $pBadgeClass = match($pStatus) {
                                'promoted' => 'promo-promoted',
                                'trial' => 'promo-trial',
                                'see_principal' => 'promo-see_principal',
                                'repeated' => 'promo-repeated',
                                default => 'promo-awaiting',
                            };
                            $pIcon = match($pStatus) {
                                'promoted' => '✅',
                                'trial' => '⚠️',
                                'see_principal' => '👤',
                                'repeated' => '🔁',
                                default => '⏳',
                            };
                        @endphp
                        <td class="promo-cell">
                            <span class="promo-badge {{ $pBadgeClass }}">{{ $pIcon }} {{ ucfirst(str_replace('_', ' ', $pStatus)) }}</span>
                        </td>
                    @endif

                    @if($showPromoLabel)
                        <td class="promo-cell">{{ $stu['promotion_label'] ?? '—' }}</td>
                    @endif

                    @if($showPromoRule)
                        <td class="promo-cell">{{ $stu['promotion_rule_applied'] ? Str::limit($stu['promotion_rule_applied'], 15) : '—' }}</td>
                    @endif
                </tr>
                @endforeach

                {{-- Stats rows --}}
                @php
                    $statRows = [['CLASS AVG','avg'],['HIGHEST','highest'],['LOWEST','lowest']];
                    $statStyles = ['avg'=>'','highest'=>'stats-hi','lowest'=>'stats-lo'];
                @endphp
                @foreach($statRows as [$label, $key])
                <tr class="stats-row {{ $statStyles[$key] }}">
                    <td class="stats-label" colspan="{{ $frozenCols + ($showPosTerm || $showPosCum ? 1 : 0) }}">{{ $label }}</td>
                    @foreach($subjects as $subId => $subInfo)
                        @php $st = $subjectStats[$subId] ?? []; @endphp
                        <td>—</td><td>—</td><td>—</td>
                        <td>{{ $st[$key] ?? '—' }}</td>
                        <td>—</td><td>—</td><td>—</td><td>—</td>
                    @endforeach
                    <td>—</td><td>—</td>
                    @if($showPromoStatus) <td>—</td> @endif
                    @if($showPromoLabel) <td>—</td> @endif
                    @if($showPromoRule) <td>—</td> @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>
</div>
</div>

{{-- Student List Modal --}}
<div class="slist-modal-overlay" id="slistModalOverlay">
    <div class="slist-modal">
        <div class="slist-modal-header">
            <h5>Print Student List Preferences</h5>
            <button class="slist-modal-close" onclick="closeSlistModal()">&times;</button>
        </div>
        <div class="slist-modal-body">
            <div class="mb-4">
                <div class="slist-section-title">Student Fields to Include</div>
                <div class="field-grid" id="slistFieldGrid">
                    <label><input type="checkbox" name="list_fields[]" value="admissionno" checked> Admission Number</label>
                    <label><input type="checkbox" name="list_fields[]" value="lastname" checked> Last Name</label>
                    <label><input type="checkbox" name="list_fields[]" value="firstname" checked> First Name</label>
                    <label><input type="checkbox" name="list_fields[]" value="gender"> Gender</label>
                    <label><input type="checkbox" name="list_fields[]" value="total_cum" checked> Cum Total</label>
                    <label><input type="checkbox" name="list_fields[]" value="position_cum" checked> Overall Pos</label>
                </div>
                <div class="mt-3">
                    <label><input type="checkbox" id="slistShowSn" checked> Show Serial Number</label>
                </div>
            </div>
            <div>
                <div class="slist-section-title">Recommendation Order (drag to reorder, uncheck to exclude)</div>
                <ul class="promo-order-list" id="promoOrderList">
                    <li class="promo-order-item" data-status="promoted" draggable="true">
                        <input type="checkbox" class="promo-group-checkbox" checked><span>✅ Promoted</span>
                    </li>
                    <li class="promo-order-item" data-status="trial" draggable="true">
                        <input type="checkbox" class="promo-group-checkbox" checked><span>⚠️ On Trial</span>
                    </li>
                    <li class="promo-order-item" data-status="see_principal" draggable="true">
                        <input type="checkbox" class="promo-group-checkbox" checked><span>👤 See Principal</span>
                    </li>
                    <li class="promo-order-item" data-status="repeated" draggable="true">
                        <input type="checkbox" class="promo-group-checkbox" checked><span>🔁 Repeat</span>
                    </li>
                    <li class="promo-order-item" data-status="awaiting" draggable="true">
                        <input type="checkbox" class="promo-group-checkbox" checked><span>⏳ Awaiting</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="slist-modal-footer">
            <button class="btn btn-secondary" onclick="closeSlistModal()">Cancel</button>
            <button class="btn btn-primary" onclick="generateStudentList()">Generate List</button>
        </div>
    </div>
</div>

<form id="slistForm" method="POST" action="{{ route('broadsheet.student-list') }}" target="_blank" style="display:none;">
    @csrf
    <input type="hidden" name="schoolclassid" value="{{ request('schoolclassid') }}">
    <input type="hidden" name="sessionid" value="{{ request('sessionid') }}">
    <input type="hidden" name="termid" value="{{ request('termid') }}">
    <input type="hidden" name="show_sn" id="sf_show_sn" value="1">
    <div id="sf_fields"></div>
    <div id="sf_order"></div>
</form>

<script>
function toast(msg, type) {
    var el = document.createElement('div');
    el.className = 'cb-toast cb-toast-' + (type || 'info');
    el.innerHTML = msg;
    document.body.appendChild(el);
    setTimeout(function() { el.remove(); }, 3000);
}

function highlightPromoted() {
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        var statusCell = row.querySelector('.promo-badge');
        if (statusCell && statusCell.textContent.includes('Promoted')) {
            row.style.backgroundColor = '#d1fae5';
            row.style.outline = '2px solid #10b981';
        }
    });
    toast('Promoted students highlighted', 'success');
}

function highlightTrial() {
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        var statusCell = row.querySelector('.promo-badge');
        if (statusCell && statusCell.textContent.includes('Trial')) {
            row.style.backgroundColor = '#fef3c7';
            row.style.outline = '2px solid #f59e0b';
        }
    });
    toast('Students on trial highlighted', 'warning');
}

function highlightSeePrincipal() {
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        var statusCell = row.querySelector('.promo-badge');
        if (statusCell && statusCell.textContent.includes('See Principal')) {
            row.style.backgroundColor = '#dbeafe';
            row.style.outline = '2px solid #3b82f6';
        }
    });
    toast('Students to see principal highlighted', 'info');
}

function highlightRepeated() {
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        var statusCell = row.querySelector('.promo-badge');
        if (statusCell && statusCell.textContent.includes('Repeat')) {
            row.style.backgroundColor = '#fee2e2';
            row.style.outline = '2px solid #ef4444';
        }
    });
    toast('Repeat students highlighted', 'error');
}

function highlightAwaiting() {
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        var statusCell = row.querySelector('.promo-badge');
        if (statusCell && statusCell.textContent.includes('Awaiting')) {
            row.style.backgroundColor = '#f1f5f9';
            row.style.outline = '2px solid #94a3b8';
        }
    });
    toast('Students awaiting decision highlighted', 'info');
}

function highlightTop(n) {
    var rows = Array.from(document.querySelectorAll('#broadsheetTable tbody tr'));
    rows.sort(function(a, b) {
        var aCum = parseFloat(a.querySelector('.gpa-cell')?.previousElementSibling?.previousElementSibling?.textContent || 0);
        var bCum = parseFloat(b.querySelector('.gpa-cell')?.previousElementSibling?.previousElementSibling?.textContent || 0);
        return bCum - aCum;
    });
    rows.slice(0, n).forEach(function(r) { r.style.backgroundColor = '#fef9c3'; r.style.outline = '2px solid #d97706'; });
    toast('Top ' + n + ' students highlighted', 'success');
}

function highlightFailures() {
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        if (row.querySelector('.grade-f9')) {
            row.style.backgroundColor = '#fee2e2';
            row.style.outline = '2px solid #dc2626';
        }
    });
    toast('Students with F9 highlighted', 'warning');
}

function highlightBelowAvg() {
    var rows = Array.from(document.querySelectorAll('#broadsheetTable tbody tr'));
    var totals = rows.map(function(r) { return parseFloat(r.querySelector('.gpa-cell')?.previousElementSibling?.previousElementSibling?.textContent || 0); }).filter(function(v) { return v > 0; });
    var avg = totals.length ? totals.reduce(function(a, b) { return a + b; }, 0) / totals.length : 0;
    rows.forEach(function(r) {
        var v = parseFloat(r.querySelector('.gpa-cell')?.previousElementSibling?.previousElementSibling?.textContent || 0);
        if (v > 0 && v < avg) { r.style.backgroundColor = '#fff7ed'; r.style.outline = '2px solid #f97316'; }
    });
    toast('Students below class average highlighted', 'info');
}

document.getElementById('locateStudent')?.addEventListener('change', function() {
    var val = this.value;
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(r) { r.style.outline = ''; r.style.backgroundColor = ''; });
    if (val === 'top5') highlightTop(5);
    else if (val === 'top10') highlightTop(10);
    else if (val === 'failures') highlightFailures();
    else if (val === 'below_avg') highlightBelowAvg();
    else if (val === 'promoted') highlightPromoted();
    else if (val === 'trial') highlightTrial();
    else if (val === 'see_principal') highlightSeePrincipal();
    else if (val === 'repeated') highlightRepeated();
    else if (val === 'awaiting') highlightAwaiting();
    this.value = '';
});

document.getElementById('searchStudent')?.addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(row) {
        var name = row.querySelector('.student-info-cell')?.textContent.toLowerCase() || '';
        var adm = row.querySelector('td:nth-child(' + (document.querySelector('.adm-cell') ? '3' : '2') + ')')?.textContent.toLowerCase() || '';
        row.style.display = (!q || name.includes(q) || adm.includes(q)) ? '' : 'none';
    });
});

window.openStudentListModal = function() { document.getElementById('slistModalOverlay').classList.add('open'); };
window.closeSlistModal = function() { document.getElementById('slistModalOverlay').classList.remove('open'); };
window.generateStudentList = function() {
    var fieldDiv = document.getElementById('sf_fields');
    fieldDiv.innerHTML = '';
    document.querySelectorAll('#slistFieldGrid input:checked').forEach(function(cb, i) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'list_fields[' + i + ']'; inp.value = cb.value;
        fieldDiv.appendChild(inp);
    });
    var orderDiv = document.getElementById('sf_order');
    orderDiv.innerHTML = '';
    document.querySelectorAll('#promoOrderList .promo-order-item').forEach(function(item, i) {
        var cb = item.querySelector('.promo-group-checkbox');
        if (cb && cb.checked) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'recommendation_order[' + i + ']'; inp.value = item.dataset.status;
            orderDiv.appendChild(inp);
        }
    });
    document.getElementById('sf_show_sn').value = document.getElementById('slistShowSn').checked ? '1' : '0';
    document.getElementById('slistForm').submit();
    closeSlistModal();
};

// Drag and drop
(function() {
    var list = document.getElementById('promoOrderList');
    if (!list) return;
    var dragSrc = null;
    list.addEventListener('dragstart', function(e) {
        dragSrc = e.target.closest('.promo-order-item');
        e.dataTransfer.effectAllowed = 'move';
    });
    list.addEventListener('dragover', function(e) { e.preventDefault(); });
    list.addEventListener('drop', function(e) {
        var target = e.target.closest('.promo-order-item');
        if (!dragSrc || !target || dragSrc === target) return;
        list.insertBefore(dragSrc, target.nextSibling);
        e.preventDefault();
    });
})();

// Performance Modal
var gradePopup = document.getElementById('cbGradePopup');
var gradeBackdrop = document.getElementById('cbPopupBackdrop');

function closeGradePop() {
    gradePopup.classList.remove('is-open');
    gradeBackdrop.style.display = 'none';
}

document.getElementById('gpopCloseBtn')?.addEventListener('click', closeGradePop);
gradeBackdrop?.addEventListener('click', closeGradePop);

document.querySelectorAll('.grade-trigger-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var name = this.dataset.sname;
        var grades = JSON.parse(this.dataset.grades || '[]');
        var html = '<div class="gpop-scroll"><table class="gpop-table"><thead><tr><th>Subject</th><th>Total</th><th>Cum</th><th>Grade</th></tr></thead><tbody>';
        grades.forEach(function(g) {
            html += '<tr><td>' + (g.subject_name || '—') + '</td><td>' + (g.total || '—') + '</td><td>' + (g.cum || '—') + '</td><td>' + (g.grade || '—') + '</td></tr>';
        });
        html += '</tbody></table></div>';
        document.getElementById('gpopTitle').innerHTML = name + "'s Performance";
        document.getElementById('gpopBody').innerHTML = html;
        gradePopup.classList.add('is-open');
        gradeBackdrop.style.display = 'block';
    });
});

// Stats animation
var totalPct = 0, topCum = -1, topName = '';
document.querySelectorAll('#broadsheetTable tbody tr').forEach(function(r) {
    var cum = parseFloat(r.querySelector('.gpa-cell')?.previousElementSibling?.previousElementSibling?.textContent || 0);
    totalPct += cum;
    if (cum > topCum) { topCum = cum; topName = r.querySelector('.student-info-cell')?.textContent || ''; }
});
var avg = totalPct / (document.querySelectorAll('#broadsheetTable tbody tr').length || 1);
document.getElementById('statAvgPct').textContent = avg.toFixed(1) + '%';
document.getElementById('statTopPerformer').textContent = topName.trim();
</script>
@endsection
