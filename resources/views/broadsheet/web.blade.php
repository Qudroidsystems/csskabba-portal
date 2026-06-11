@extends('layouts.master')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">

<style>
/* ─────────────────────────────────────────────
   CSS VARIABLES & RESETS
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

body {
    font-family: 'DM Sans', sans-serif;
    background: #f1f5f9;
    margin: 0;
    padding: 0;
}

/* Keyframes */
@keyframes fadeInUp { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInDown { from { opacity:0; transform:translateY(-22px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInLeft { from { opacity:0; transform:translateX(-22px); } to { opacity:1; transform:translateX(0); } }
@keyframes fadeInRight { from { opacity:0; transform:translateX(22px); } to { opacity:1; transform:translateX(0); } }
@keyframes scaleIn { from { opacity:0; transform:scale(.88); } to { opacity:1; transform:scale(1); } }
@keyframes slideInRight { from { transform:translateX(110%); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes popIn { 0% { opacity:0; transform:scale(.7) translateY(12px); } 60% { transform:scale(1.04) translateY(-3px); } 100% { opacity:1; transform:scale(1) translateY(0); } }
@keyframes floatUp { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-6px); } }
@keyframes glowPulse { 0%,100% { box-shadow:0 0 0 0 rgba(13,148,136,.4); } 50% { box-shadow:0 0 0 8px rgba(13,148,136,0); } }
@keyframes progressFill { from { width:0; } }
@keyframes rowSlide { from { opacity:0; transform:translateX(-12px); } to { opacity:1; transform:translateX(0); } }
@keyframes countUp { from { opacity:0; transform:scale(.6); } to { opacity:1; transform:scale(1); } }
@keyframes backdropIn { from { opacity:0; } to { opacity:1; } }

/* Hero Section */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.cb-hero h1 { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; color:#fff; margin:0 0 8px; }
.cb-hero p  { font-size:13px; color:rgba(255,255,255,.72); margin:0; }
.cb-meta-pill {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    border-radius:20px; padding:4px 14px; font-size:12px; font-weight:600; color:#fff;
    display:inline-flex; align-items:center; gap:5px;
}
.btn-back {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    border-radius:10px; padding:8px 18px; color:#fff; font-size:12px; font-weight:600;
    text-decoration:none; display:inline-flex; align-items:center; gap:8px;
}
.btn-back:hover { background:rgba(255,255,255,.22); color:#fff; }

/* Stats Cards */
.cb-stat {
    background:var(--cb-white); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); padding:20px 22px;
    position:relative; overflow:hidden;
    transition:all .35s ease;
}
.cb-stat .stat-accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--cb-radius) var(--cb-radius) 0 0; }
.cb-stat .stat-value  { font-size:30px; font-weight:700; color:var(--cb-navy); line-height:1; margin-top:8px; }
.cb-stat .stat-label  { font-size:12px; color:var(--cb-muted); margin-top:5px; font-weight:500; }
.cb-stat .stat-ico    { font-size:36px; opacity:.08; position:absolute; right:16px; top:50%; transform:translateY(-50%); }

/* Meta Grid */
.meta-grid {
    display:flex; border:1px solid var(--cb-border); background:var(--cb-surface);
    border-radius:8px; overflow:hidden; margin-bottom:14px;
}
.meta-cell { flex:1; padding:10px 14px; border-right:1px solid var(--cb-border); text-align:center; }
.meta-cell:last-child { border-right:none; }
.meta-label { font-size:10px; color:var(--cb-muted); text-transform:uppercase; letter-spacing:.4px; display:block; }
.meta-value { font-size:13px; font-weight:700; color:var(--cb-navy); }

/* Grade Key */
.grade-key {
    display:flex; align-items:center; border:1px solid var(--cb-border);
    padding:6px 14px; background:#fafafa; border-radius:8px; margin-bottom:14px;
    flex-wrap:wrap; gap:6px;
}

/* Card */
.cb-card {
    background:var(--cb-white); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); box-shadow:var(--cb-shadow);
    margin-bottom: 24px;
}
.cb-card-header {
    padding:18px 24px; border-bottom:1px solid var(--cb-border);
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
    background:linear-gradient(to right,#f8fafc,#f0fdf9);
    border-radius:var(--cb-radius) var(--cb-radius) 0 0;
}

/* Broadsheet Table - CRITICAL FIXES */
.broadsheet-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    background: white;
    border: 1.5px solid var(--cb-navy);
    table-layout: auto;
}

.broadsheet-table thead tr.subject-header th {
    background: var(--cb-navy);
    color: #fff;
    text-align: center;
    padding: 8px 4px;
    border: 0.5px solid rgba(37,99,235,.35);
    font-weight: 600;
    font-size: 11px;
    white-space: nowrap;
}

.broadsheet-table thead tr.subject-header th.student-col {
    background: #0f2040;
    text-align: left;
    padding-left: 8px;
}

.broadsheet-table thead tr.subject-header th.subj-name-hdr {
    background: #163562;
    border-left: 1.5px solid #2563eb;
    font-size: 10px;
    white-space: normal;
    word-break: break-word;
    min-width: 60px;
    max-width: 120px;
}

.broadsheet-table thead tr.assessment-header th {
    background: #1a3d6a;
    color: #a8d4ef;
    text-align: center;
    padding: 6px 3px;
    border: 0.5px solid rgba(37,99,235,.2);
    font-size: 9px;
    white-space: nowrap;
}

.broadsheet-table thead tr.assessment-header th.sub-boundary {
    border-left: 1.5px solid #2563eb;
}

.broadsheet-table tbody tr {
    transition: all .2s ease;
}

.broadsheet-table tbody tr:nth-child(odd)  { background: #ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background: #f8fafc; }
.broadsheet-table tbody tr:hover { background: #e8f0fe !important; }

.broadsheet-table tbody td {
    padding: 6px 4px;
    border: 0.5px solid #e2e8f0;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    font-size: 11px;
}

.broadsheet-table tbody td.student-info-cell {
    text-align: left;
    padding-left: 8px;
    font-weight: 600;
    background: inherit;
    min-width: 200px;
}

/* Score cell hover */
.score-cell {
    transition: all .2s ease;
    cursor: pointer;
}
.score-cell:hover {
    background: #fef3c7 !important;
    transform: scale(1.02);
}

/* Promotion status cells */
.promo-cell { text-align: center; border-left: 2px solid #7c3aed !important; }

.promo-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 700; white-space: nowrap;
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

/* Position cells */
.pos-term-cell {
    background:#fef3c7 !important; color:#92400e; font-weight:700;
    border-left:1.5px solid #f59e0b !important;
}
.pos-cum-cell {
    background:#dbeafe !important; color:#1e40af; font-weight:700;
    border-left:1.5px solid #3b82f6 !important;
}

/* Per-subject position cells */
.sub-pos-class-cum-cell { background:#f0fdf4 !important; color:#166534; font-weight:700; border-left:1px solid #86efac !important; }
.sub-pos-class-total-cell { background:#fefce8 !important; color:#854d0e; font-weight:700; }
.sub-pos-arm-total-cell { background:#eff6ff !important; color:#1e40af; font-weight:700; border-left:1px solid #93c5fd !important; }
.sub-pos-arm-cum-cell { background:#f5f3ff !important; color:#5b21b6; font-weight:700; }

/* Grade colors */
.grade-a1 { background:#dcfce7 !important; color:#166534; font-weight:700; }
.grade-b2 { background:#dbeafe !important; color:#1e40af; }
.grade-b3 { background:#e0eeff !important; color:#1e40af; }
.grade-c4 { background:#fef9c3 !important; color:#854d0e; }
.grade-c5 { background:#fef3c7 !important; color:#92400e; }
.grade-c6 { background:#fde68a !important; color:#78350f; }
.grade-d7 { background:#ffedd5 !important; color:#9a3412; }
.grade-e8 { background:#fed7aa !important; color:#9a3412; }
.grade-f9 { background:#fee2e2 !important; color:#991b1b; font-weight:700; }

/* Score colors */
.score-red   { color:#dc2626 !important; font-weight:700; }
.score-amber { color:#d97706 !important; font-weight:700; }
.score-green { color:#16a34a !important; font-weight:700; }

/* GPA cells */
.gpa-cell { background:#eff6ff !important; color:#1e3a8a; font-weight:700; border-left:1.5px solid #3b82f6 !important; }

/* Dual position badge */
.pos-dual {
    display:inline-flex; flex-direction:column; align-items:center;
    gap:2px;
}
.pos-dual .pos-term-lbl { font-size:9px; font-weight:700; color:#92400e; background:#fef3c7; border-radius:4px; padding:1px 5px; line-height:1.4; }
.pos-dual .pos-cum-lbl  { font-size:9px; font-weight:700; color:#1e40af; background:#dbeafe; border-radius:4px; padding:1px 5px; line-height:1.4; }

/* Student avatar */
.cb-avatar {
    width:30px; height:30px; border-radius:50%; overflow:hidden;
    border:2px solid var(--cb-border); flex-shrink:0;
    display:inline-flex; align-items:center; justify-content:center;
}
.cb-avatar img { width:100%; height:100%; object-fit:cover; }
.cb-avatar-initials { background:linear-gradient(135deg,var(--cb-teal),var(--cb-sky)); color:#fff; font-size:11px; font-weight:700; }

/* Eye button */
.grade-trigger-btn {
    background:none; border:none; cursor:pointer;
    color:var(--cb-sky); font-size:17px; padding:5px 8px; border-radius:8px;
    transition:all .25s ease;
}
.grade-trigger-btn:hover {
    color:#fff; background:var(--cb-teal); transform:scale(1.15);
}

/* Stats rows */
.stats-row td { background:var(--cb-navy) !important; color:white; font-weight:700; padding:6px 4px; text-align:center; border:0.5px solid #163785; font-size:11px; }
.stats-row td.stats-label { text-align:left; padding-left:8px; font-size:10px; }
.stats-hi td { background:#0a2240 !important; }
.stats-lo td { background:#111c2a !important; }

/* Search */
.cb-search { position:relative; }
.cb-search input {
    width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--cb-border);
    border-radius:10px; font-size:13px; background:var(--cb-surface);
}
.cb-search i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--cb-muted); }

/* Modal */
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
.slist-modal-close {
    background: rgba(255,255,255,.18); border: none; color: white;
    width: 30px; height: 30px; border-radius: 50%; cursor: pointer;
    font-size: 17px;
}
.slist-modal-body { padding: 20px 22px; overflow-y: auto; flex: 1; }
.slist-modal-footer { padding: 14px 22px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; }

/* Drag-and-drop */
.promo-order-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; background: white;
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    margin-bottom: 6px; cursor: grab;
}
.drag-handle { color: #94a3b8; font-size: 18px; cursor: grab; }

/* Field checkboxes */
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.field-checkbox-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; border: 1.5px solid #e2e8f0;
    border-radius: 8px; cursor: pointer;
    font-size: 12.5px;
}
.field-checkbox-item.checked { border-color: #7c3aed; background: #f5f3ff; }

/* Popup */
#cbGradePopup {
    display:none; position:fixed; z-index:99999;
    background:white; border:2px solid var(--cb-teal);
    border-radius:16px; box-shadow:0 20px 60px rgba(15,35,66,.22);
    width:520px; max-height:620px; overflow:hidden; flex-direction:column;
}
#cbGradePopup.is-open { display:flex; }
.gpop-hdr {
    background:linear-gradient(135deg,var(--cb-navy),var(--cb-teal));
    color:#fff; padding:14px 18px; font-weight:700;
    display:flex; justify-content:space-between; align-items:center;
}
.gpop-close-btn { background:rgba(255,255,255,.18); border:none; color:#fff; border-radius:50%; width:28px; height:28px; cursor:pointer; }
.gpop-body { padding:16px; overflow-y:auto; flex:1; }

#cbPopupBackdrop { display:none; position:fixed; inset:0; z-index:99998; background:rgba(0,0,0,.3); }

/* Print */
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; font-size: 10px; }
    .cb-hero::before, .cb-hero::after { display: none; }
    .broadsheet-table tbody tr { break-inside: avoid; }
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
<div class="school-header-bar" style="background:linear-gradient(135deg,var(--cb-navy) 0%,#2563eb 100%);border-radius:10px;padding:18px 24px;margin-bottom:16px;color:white;">
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
    <div class="meta-cell"><span class="meta-label">Class</span><span class="meta-value">{{ ($schoolclass->schoolclass ?? '-') . ' ' . ($schoolclass->arm_name ?? '') }}</span></div>
    <div class="meta-cell"><span class="meta-label">Academic Session</span><span class="meta-value">{{ $schoolsession->session ?? '-' }}</span></div>
    <div class="meta-cell"><span class="meta-label">Term</span><span class="meta-value">{{ $schoolterm->term ?? '-' }}</span></div>
    <div class="meta-cell"><span class="meta-label">Generated</span><span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span></div>
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
</div>

{{-- Toolbar --}}
<div class="cb-card mb-3 no-print">
    <div class="cb-card-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="cb-search" style="max-width:260px;">
                <i class="ri-search-line"></i>
                <input type="text" id="searchStudent" placeholder="Search name or admission no…">
            </div>
            <select class="form-select form-select-sm" id="locateStudent" style="max-width:220px;">
                <option value="">🔍 Quick Locate…</option>
                <option value="top5">🏆 Top 5 (by Cum)</option>
                <option value="top10">⭐ Top 10</option>
                <option value="failures">⚠️ Students with F9</option>
                <option value="below_avg">📉 Below Class Average</option>
                <option disabled>──────────</option>
                @foreach($studentRows as $student)
                    <option value="student_{{ $student['id'] }}">👤 {{ $student['lastname'] }}, {{ $student['firstname'] }} ({{ $student['admissionno'] }})</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="ri-printer-line me-1"></i>Print</button>
            <button class="btn btn-sm" onclick="openStudentListModal()" style="background:linear-gradient(135deg,#3b0764,#7c3aed);color:#fff;border:none;"><i class="ri-list-check-2 me-1"></i>Print Student List</button>
            <button class="btn btn-sm" onclick="scrollToTop()" style="background:var(--cb-teal);color:#fff;border:none;"><i class="ri-arrow-up-line me-1"></i>Top</button>
        </div>
    </div>
</div>

{{-- Grade Popup --}}
<div id="cbPopupBackdrop"></div>
<div id="cbGradePopup">
    <div class="gpop-hdr"><span id="gpopTitle"><i class="ri-bar-chart-line me-1"></i>Performance Summary</span><button class="gpop-close-btn" id="gpopCloseBtn">&times;</button></div>
    <div class="gpop-body" id="gpopBody"></div>
</div>

@php
    $selected = $selectedColumns ?? [];
    $showAll  = empty($selected);

    // Student info columns
    $showAdmNo   = $showAll || in_array('admission_no',   $selected);
    $showGender  = in_array('gender', $selected);

    // Score columns
    $showTotal   = $showAll || in_array('total',          $selected);
    $showBF      = $showAll || in_array('bf',             $selected);
    $showCum     = $showAll || in_array('cum',            $selected);
    $showGrade   = $showAll || in_array('grade',          $selected);
    $showAvg     = $showAll || in_array('class_average',  $selected);
    $showRemark  = in_array('remark', $selected);

    // Overall student positions
    $showPosTerm = $showAll || in_array('position_term',  $selected);
    $showPosCum  = $showAll || in_array('position_cum',   $selected);

    // Per-subject position flags
    $showSubPosClassCum   = $showAll || in_array('pos_class_cum',   $selected);
    $showSubPosClassTotal = $showAll || in_array('pos_class_total', $selected);
    $showSubPosArmTotal   = $showAll || in_array('pos_arm_total',   $selected);
    $showSubPosArmCum     = $showAll || in_array('pos_arm_cum',     $selected);

    // GPA columns
    $showGPA     = $showAll || in_array('gpa',            $selected);
    $showCGPA    = in_array('cgpa', $selected);
    $showGPAGrade = in_array('gpa_grade', $selected);
    $showNumSub  = in_array('num_subjects', $selected);
    $showTotalGP = in_array('total_grade_points', $selected);

    // Promotion columns
    $showPromoStatus = $showAll || in_array('promotion_status', $selected);
    $showPromoLabel  = in_array('promotion_label', $selected);
    $showPromoRule   = in_array('promotion_rule_applied', $selected);
    $promoColspan = ($showPromoStatus ? 1 : 0) + ($showPromoLabel ? 1 : 0) + ($showPromoRule ? 1 : 0);

    $activeAssessments = $assessments->filter(fn($a) => empty($selected) || in_array('assessment_' . $a->id, $selected));
    $gradeColors = ['A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3','C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6','D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9','-'=>''];
    $frozenCols = 1 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0);
    $gpaColspan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);

    $subColspan = $activeAssessments->count();
    if($showTotal) $subColspan++;
    if($showBF) $subColspan++;
    if($showCum) $subColspan++;
    if($showGrade) $subColspan++;
    if($showSubPosClassCum) $subColspan++;
    if($showSubPosClassTotal) $subColspan++;
    if($showSubPosArmTotal) $subColspan++;
    if($showSubPosArmCum) $subColspan++;
    if($showAvg) $subColspan++;
    if($showRemark) $subColspan++;
    $subColspan = max(1, $subColspan);
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
                    <th class="student-col" rowspan="2" style="width:36px;">#</th>
                    @if($showAdmNo)<th class="student-col" rowspan="2" style="min-width:72px;">Adm. No</th>@endif
                    <th class="student-col" rowspan="2" style="min-width:180px;text-align:left;padding-left:8px;">Student Name</th>
                    @if($showGender)<th class="student-col" rowspan="2" style="width:38px;">Sex</th>@endif
                    @if($showPosTerm || $showPosCum)<th class="student-col" rowspan="2" style="width:70px;">Position</th>@endif

                    @foreach($subjects as $subId => $subInfo)
                        <th class="subj-name-hdr" colspan="{{ $subColspan }}">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))<br><small>({{ $subInfo['subject_code'] }})</small>@endif
                        </th>
                    @endforeach

                    <th class="subj-name-hdr" colspan="1" style="background:#0a2240;border-left:2px solid var(--cb-teal);min-width:46px;"><i class="ri-eye-line"></i></th>
                    @if($gpaColspan > 0)<th colspan="{{ $gpaColspan }}" style="background:#0a1e38;">GPA METRICS</th>@endif
                    @if($promoColspan > 0)<th colspan="{{ $promoColspan }}" class="promo-header-th">🎓 PROMOTION</th>@endif
                </tr>

                <tr class="assessment-header">
                    @foreach($subjects as $subId => $subInfo)
                        @foreach($activeAssessments as $aIdx => $a)
                            <th class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}">{{ $a->name }}<br><span style="font-size:8px;">/{{ $a->max_score }}</span></th>
                        @endforeach
                        @if($showTotal)<th>Total</th>@endif
                        @if($showBF)<th>BF</th>@endif
                        @if($showCum)<th>Cum</th>@endif
                        @if($showGrade)<th>Grd</th>@endif
                        @if($showSubPosClassCum)<th class="pos-class-hdr">CC</th>@endif
                        @if($showSubPosClassTotal)<th class="pos-class-hdr">CT</th>@endif
                        @if($showSubPosArmTotal)<th class="pos-arm-hdr">AC</th>@endif
                        @if($showSubPosArmCum)<th class="pos-arm-hdr">AK</th>@endif
                        @if($showAvg)<th>Avg</th>@endif
                        @if($showRemark)<th>Rmk</th>@endif
                    @endforeach
                    <th style="min-width:44px;background:#0a2240;border-left:2px solid var(--cb-teal);">View</th>
                    @if($showGPA)<th style="background:#0a1e38;">GPA</th>@endif
                    @if($showCGPA)<th style="background:#0a1e38;">CGPA</th>@endif
                    @if($showGPAGrade)<th style="background:#0a1e38;">GGrd</th>@endif
                    @if($showNumSub)<th style="background:#0a1e38;">NS</th>@endif
                    @if($showTotalGP)<th style="background:#0a1e38;">TGP</th>@endif
                    @if($showPromoStatus)<th class="promo-header-th">Status</th>@endif
                    @if($showPromoLabel)<th class="promo-header-th">Label</th>@endif
                    @if($showPromoRule)<th class="promo-header-th">Rule</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($studentRows as $idx => $stu)
                    @php
                        $sid = $stu['id'];
                        $hasPic = !empty($stu['picture']) && $stu['picture'] !== 'unnamed.jpg';
                        $imgSrc = $hasPic ? asset('storage/student_avatars/' . basename($stu['picture'])) : null;
                        $initials = strtoupper(substr($stu['lastname']??'',0,1) . substr($stu['firstname']??'',0,1)) ?: 'ST';
                        $fullName = trim(($stu['lastname']??'') . ' ' . ($stu['firstname']??''));
                        $totalObtainable = count($subjects) * 100;
                        $totalObtained = $stu['total_cum'] ?? 0;
                        $termObtained = $stu['total_term'] ?? 0;
                        $hasBF = false;
                        foreach($stu['subjects'] as $sd) { if(($sd['bf'] ?? 0) > 0) { $hasBF = true; break; } }
                        $termPct = $totalObtainable > 0 ? round(($termObtained / $totalObtainable) * 100, 1) : 0;
                        $cumPct = $totalObtainable > 0 ? round(($totalObtained / $totalObtainable) * 100, 1) : 0;
                        $posTotal = count($studentRows);

                        $gradesForPopup = [];
                        foreach ($subjects as $subId => $subInfo) {
                            $sd = $stu['subjects'][$subId] ?? [];
                            $gradesForPopup[] = [
                                'subject' => $subInfo['subject_name'],
                                'term_score' => $sd['total'] ?? 0,
                                'cum_score' => $sd['cum'] ?? 0,
                                'bf_score' => $sd['bf'] ?? 0,
                                'grade' => $sd['grade'] ?? '-',
                                'pos_class_cum' => $sd['pos_class_cum'] ?? null,
                                'pos_class_total' => $sd['pos_class_total'] ?? null,
                                'pos_arm_total' => $sd['pos_arm_total'] ?? null,
                                'pos_arm_cum' => $sd['pos_arm_cum'] ?? null,
                            ];
                        }
                    @endphp
                    <tr data-student-id="{{ $sid }}" data-student-name="{{ strtolower($fullName) }}" data-admission="{{ strtolower($stu['admissionno']) }}" data-total-cum="{{ $totalObtained }}" data-total-term="{{ $termObtained }}" data-term-pct="{{ $termPct }}" data-cum-pct="{{ $cumPct }}" data-has-bf="{{ $hasBF ? 'true' : 'false' }}" data-grades='@json($gradesForPopup)'>
                        <td>{{ $idx + 1 }}</td>
                        @if($showAdmNo)<td>{{ $stu['admissionno'] }}</td>@endif
                        <td class="student-info-cell">
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($imgSrc)<div class="cb-avatar"><img src="{{ $imgSrc }}" alt="{{ $fullName }}" onerror="this.parentElement.classList.add('cb-avatar-initials');this.parentElement.textContent='{{ $initials }}'"></div>
                                @else<div class="cb-avatar cb-avatar-initials">{{ $initials }}</div>@endif
                                <div><div style="font-weight:700;">{{ strtoupper($stu['lastname']) }}, {{ $stu['firstname'] }}</div>@if(!empty($stu['arm']))<div style="font-size:10px;color:var(--cb-muted);">Arm {{ $stu['arm'] }}</div>@endif</div>
                            </div>
                        </td>
                        @if($showGender)<td>{{ substr($stu['gender']??'',0,1) }}</td>@endif
                        @if($showPosTerm || $showPosCum)
                            <td><div class="pos-dual"><span class="pos-term-lbl">T:{{ $stu['position_term'] }}</span><span class="pos-cum-lbl">C:{{ $stu['position_cum'] }}</span></div></td>
                        @endif

                        @foreach($subjects as $subId => $subInfo)
                            @php $sd = $stu['subjects'][$subId] ?? []; $g = $sd['grade'] ?? '-'; $gc = $gradeColors[$g] ?? ''; $ord = function($n) { if(!$n) return '—'; $n=(int)$n; $s=['th','st','nd','rd']; $v=$n%100; return $n.($s[($v-20)%10]??$s[$v]??$s[0]); }; @endphp
                            @foreach($activeAssessments as $aIdx => $a)
                                @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
                                <td class="score-cell {{ $aIdx === 0 ? 'sub-boundary' : '' }}">{{ $as > 0 ? number_format($as,1) : '—' }}</td>
                            @endforeach
                            @if($showTotal)<td class="score-cell {{ $gc }}">{{ ($sd['total']??0) > 0 ? number_format($sd['total'],1) : '—' }}</td>@endif
                            @if($showBF)<td class="score-cell">{{ ($sd['bf']??0) > 0 ? number_format($sd['bf'],1) : '—' }}</td>@endif
                            @if($showCum)<td class="score-cell {{ $gc }}">{{ ($sd['cum']??0) > 0 ? number_format($sd['cum'],1) : '—' }}</td>@endif
                            @if($showGrade)<td class="score-cell {{ $gc }}">{{ $g }}</td>@endif
                            @if($showSubPosClassCum)<td class="score-cell sub-pos-class-cum-cell">{{ $ord($sd['pos_class_cum']??null) }}</td>@endif
                            @if($showSubPosClassTotal)<td class="score-cell sub-pos-class-total-cell">{{ $ord($sd['pos_class_total']??null) }}</td>@endif
                            @if($showSubPosArmTotal)<td class="score-cell sub-pos-arm-total-cell">{{ $ord($sd['pos_arm_total']??null) }}</td>@endif
                            @if($showSubPosArmCum)<td class="score-cell sub-pos-arm-cum-cell">{{ $ord($sd['pos_arm_cum']??null) }}</td>@endif
                            @if($showAvg)<td class="score-cell">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td>@endif
                            @if($showRemark)<td class="score-cell">{{ $sd['remark'] ?? '—' }}</td>@endif
                        @endforeach

                        <td><button type="button" class="grade-trigger-btn" data-sid="{{ $sid }}" data-sname="{{ $fullName }}" data-sadm="{{ $stu['admissionno'] }}" data-term-obtained="{{ $termObtained }}" data-cum-obtained="{{ $totalObtained }}" data-obtainable="{{ $totalObtainable }}" data-term-pct="{{ $termPct }}" data-cum-pct="{{ $cumPct }}" data-gpa="{{ $stu['gpa'] }}" data-gpa-grade="{{ $stu['gpa_grade'] ?? '-' }}" data-pos-cum="{{ $stu['position_cum'] }}" data-pos-term="{{ $stu['position_term'] }}" data-pos-total="{{ $posTotal }}" data-has-bf="{{ $hasBF ? 'true' : 'false' }}" data-grades='@json($gradesForPopup)'><i class="ri-eye-line"></i></button></td>

                        @if($showGPA)<td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>@endif
                        @if($showCGPA)<td class="gpa-cell">{{ number_format($stu['cgpa'],2) }}</td>@endif
                        @if($showGPAGrade)<td class="gpa-cell {{ $gradeColors[$stu['gpa_grade']??'-'] ?? '' }}">{{ $stu['gpa_grade'] ?? '—' }}</td>@endif
                        @if($showNumSub)<td>{{ $stu['num_subjects'] ?? '—' }}</td>@endif
                        @if($showTotalGP)<td>{{ number_format($stu['total_grade_points'],1) }}</td>@endif

                        @if($showPromoStatus)
                            @php
                                $pStatus = $stu['promotion_status'] ?? 'awaiting';
                                $pBadgeClass = match($pStatus) {
                                    'promoted' => 'promo-promoted', 'trial' => 'promo-trial',
                                    'see_principal' => 'promo-see_principal', 'repeated' => 'promo-repeated',
                                    default => 'promo-awaiting',
                                };
                                $pIcon = match($pStatus) {
                                    'promoted' => '✅', 'trial' => '⚠️', 'see_principal' => '👤', 'repeated' => '🔁',
                                    default => '⏳',
                                };
                            @endphp
                            <td class="promo-cell"><span class="promo-badge {{ $pBadgeClass }}">{{ $pIcon }} {{ ucfirst($pStatus) }}</span></td>
                        @endif
                        @if($showPromoLabel)<td class="promo-cell">{{ $stu['promotion_label'] ?? '—' }}</td>@endif
                        @if($showPromoRule)<td class="promo-cell">{{ $stu['promotion_rule_applied'] ? Str::limit($stu['promotion_rule_applied'], 20) : '—' }}</td>@endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Student List Modal --}}
<div class="slist-modal-overlay" id="slistModalOverlay">
    <div class="slist-modal">
        <div class="slist-modal-header"><h5>📋 Print Student List Preferences</h5><button class="slist-modal-close" onclick="closeSlistModal()">&times;</button></div>
        <div class="slist-modal-body">
            <div class="d-flex gap-2 flex-wrap mb-4">
                <span class="badge" style="background:#ede9fe;color:#5b21b6;">{{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
                <span class="badge" style="background:#dbeafe;color:#1e40af;">{{ $schoolsession->session ?? '' }}</span>
                <span class="badge" style="background:#fef3c7;color:#92400e;">{{ $schoolterm->term ?? '' }}</span>
                <span class="badge" style="background:#f1f5f9;color:#475569;">{{ $totalStudents }} Students</span>
            </div>
            <div class="mb-4">
                <div class="slist-section-title"><i class="ri-table-line"></i> Student Fields to Include</div>
                <div class="field-grid" id="slistFieldGrid">
                    @php $fieldOptions = [['key'=>'admissionno','label'=>'Admission Number','default'=>true],['key'=>'lastname','label'=>'Last Name','default'=>true],['key'=>'firstname','label'=>'First Name','default'=>true],['key'=>'gender','label'=>'Gender','default'=>false],['key'=>'dateofbirth','label'=>'Date of Birth','default'=>false],['key'=>'arm','label'=>'Arm / Class','default'=>false],['key'=>'total_cum','label'=>'Cum Total Score','default'=>true],['key'=>'total_term','label'=>'Term Total Score','default'=>false],['key'=>'position_cum','label'=>'Overall Pos (Cum)','default'=>true],['key'=>'position_term','label'=>'Overall Pos (Term)','default'=>false],['key'=>'gpa','label'=>'GPA','default'=>false]]; @endphp
                    @foreach($fieldOptions as $fo)<label class="field-checkbox-item {{ $fo['default'] ? 'checked' : '' }}"><input type="checkbox" name="list_fields[]" value="{{ $fo['key'] }}" {{ $fo['default'] ? 'checked' : '' }} onchange="this.closest('.field-checkbox-item').classList.toggle('checked', this.checked)"> {{ $fo['label'] }}</label>@endforeach
                </div>
                <div class="d-flex gap-3 mt-3"><label class="field-checkbox-item" style="flex:1;"><input type="checkbox" id="slistShowPhotos"><i class="ri-image-line"></i> Show Student Photos</label><label class="field-checkbox-item" style="flex:1;"><input type="checkbox" id="slistShowSn" checked><i class="ri-list-ordered-2"></i> Show Serial Number</label></div>
            </div>
            <div>
                <div class="slist-section-title"><i class="ri-drag-move-line"></i> Recommendation Order <span style="font-size:10px;">— drag to reorder</span></div>
                <ul class="promo-order-list" id="promoOrderList">
                    @php $defaultStatusOrder = [['key'=>'promoted','icon'=>'✅'],['key'=>'trial','icon'=>'⚠️'],['key'=>'see_principal','icon'=>'👤'],['key'=>'repeated','icon'=>'🔁'],['key'=>'awaiting','icon'=>'⏳']]; @endphp
                    @foreach($defaultStatusOrder as $so)<li class="promo-order-item" data-status="{{ $so['key'] }}" draggable="true"><span class="drag-handle">⠿</span><span>{{ $so['icon'] }}</span><span>{{ ucfirst($so['key']) }}</span></li>@endforeach
                </ul>
            </div>
        </div>
        <div class="slist-modal-footer"><button class="btn btn-secondary btn-sm" onclick="closeSlistModal()">Cancel</button><button class="btn btn-primary btn-sm" onclick="generateStudentList()" style="background:linear-gradient(135deg,#3b0764,#7c3aed);border:none;"><i class="ri-file-list-line me-1"></i>Generate List</button></div>
    </div>
</div>

<form id="slistForm" method="POST" action="{{ route('broadsheet.student-list') }}" target="_blank" style="display:none;">@csrf<input type="hidden" name="schoolclassid" value="{{ request('schoolclassid') }}"><input type="hidden" name="sessionid" value="{{ request('sessionid') }}"><input type="hidden" name="termid" value="{{ request('termid') }}"><input type="hidden" name="show_photos" id="sf_show_photos" value="0"><input type="hidden" name="show_sn" id="sf_show_sn" value="1"><div id="sf_fields"></div><div id="sf_order"></div></form>

</div></div></div>

<script>
(function() {
    // Helper functions
    function ordinal(n) { n = parseInt(n,10); if(!n) return '—'; var s=['th','st','nd','rd']; var v=n%100; return n+(s[(v-20)%10]||s[v]||s[0]); }
    function getPctClass(p) { return p < 40 ? 'score-red' : (p < 70 ? 'score-amber' : 'score-green'); }
    function esc(str) { var d=document.createElement('div'); d.textContent=str||''; return d.innerHTML; }

    // Grade popup
    var gpop = document.getElementById('cbGradePopup');
    var backdrop = document.getElementById('cbPopupBackdrop');

    window.closeGradePop = function() { if(gpop) gpop.classList.remove('is-open'); if(backdrop) backdrop.style.display = 'none'; };

    window.openGradePop = function(btn) {
        var sid = btn.dataset.sid || '';
        var name = btn.dataset.sname || '';
        var adm = btn.dataset.sadm || '';
        var termObtained = parseFloat(btn.dataset.termObtained || 0);
        var cumObtained = parseFloat(btn.dataset.cumObtained || 0);
        var obtainable = parseFloat(btn.dataset.obtainable || 0);
        var termPct = parseFloat(btn.dataset.termPct || 0);
        var cumPct = parseFloat(btn.dataset.cumPct || 0);
        var gpa = parseFloat(btn.dataset.gpa || 0);
        var gpaGrade = btn.dataset.gpaGrade || '—';
        var posCum = parseInt(btn.dataset.posCum || 0,10);
        var posTerm = parseInt(btn.dataset.posTerm || 0,10);
        var posTotal = parseInt(btn.dataset.posTotal || 0,10);
        var hasBF = btn.dataset.hasBf === 'true';
        var grades = [];
        try { grades = JSON.parse(btn.dataset.grades || '[]'); } catch(e) {}

        document.getElementById('gpopTitle').innerHTML = '<i class="ri-bar-chart-line me-1"></i>' + esc(name) + '\'s Performance';

        var rows = '';
        if(grades.length) {
            grades.forEach(function(g) {
                var tC = g.term_score > 0 ? (g.term_score < 50 ? 'score-red' : (g.term_score >= 70 ? 'score-green' : 'score-amber')) : '';
                var cC = g.cum_score > 0 ? (g.cum_score < 50 ? 'score-red' : (g.cum_score >= 70 ? 'score-green' : 'score-amber')) : '';
                var tS = g.term_score > 0 ? parseFloat(g.term_score).toFixed(1) : '—';
                var cS = g.cum_score > 0 ? parseFloat(g.cum_score).toFixed(1) : '—';
                var bS = g.bf_score > 0 ? parseFloat(g.bf_score).toFixed(1) : '—';
                rows += '<tr><td style="text-align:left;font-weight:600;">' + esc(g.subject) + '</td><td><div><div>' + tS + '</div><div>BF:' + bS + '</div></div></td><td>' + cS + '</td><td>' + (g.grade && g.grade !== '-' ? g.grade : '—') + '</td><td>' + (g.pos_class_cum || g.pos_class_total || g.pos_arm_total || g.pos_arm_cum ? (g.pos_class_cum ? 'CC:'+g.pos_class_cum+' ' : '') + (g.pos_class_total ? 'CT:'+g.pos_class_total+' ' : '') + (g.pos_arm_total ? 'AC:'+g.pos_arm_total+' ' : '') + (g.pos_arm_cum ? 'AK:'+g.pos_arm_cum : '') : '—') + '</td></tr>';
            });
        } else { rows = '<tr><td colspan="5" style="text-align:center;">No subject records</td></tr>'; }

        document.getElementById('gpopBody').innerHTML = '<div style="background:linear-gradient(135deg,#0f2342,#1e5f74);border-radius:10px;padding:12px;color:#fff;margin-bottom:14px;"><div>Performance Snapshot</div><div><div>Adm: '+esc(adm)+'</div><div>Term: '+termObtained.toFixed(1)+'</div><div>Cum: '+cumObtained.toFixed(1)+(hasBF?'':' (no BF)')+'</div><div>Obtainable: '+obtainable.toFixed(0)+'</div><div>% Term: '+termPct.toFixed(1)+'%</div><div>% Cum: '+cumPct.toFixed(1)+'%</div></div><div><div>Term % — '+termPct.toFixed(1)+'%</div><div style="background:rgba(255,255,255,.15);border-radius:4px;height:6px;"><div style="width:'+termPct+'%;height:100%;border-radius:4px;background:#22c55e;"></div></div><div>Cum % — '+cumPct.toFixed(1)+'%'+(hasBF?'':' (no BF)')+'</div><div style="background:rgba(255,255,255,.15);border-radius:4px;height:6px;"><div style="width:'+cumPct+'%;height:100%;border-radius:4px;background:#22c55e;"></div></div></div><div><span>T-Pos: '+ordinal(posTerm)+'/'+posTotal+'</span> <span>C-Pos: '+ordinal(posCum)+'/'+posTotal+'</span></div></div><div class="gpop-scroll" style="max-height:260px;overflow-y:auto;"><table style="width:100%;border-collapse:collapse;"><thead><tr><th>Subject</th><th>Term/BF</th><th>Cum</th><th>Grade</th><th>Positions</th></tr></thead><tbody>'+rows+'</tbody></table></div>';

        var rect = btn.getBoundingClientRect();
        var pw = 560, ph = Math.min(640, window.innerHeight-40);
        var top = rect.bottom+8, left = rect.left+rect.width/2-pw/2;
        if(top+ph > window.innerHeight-8) top = Math.max(8, rect.top-ph-8);
        if(left < 8) left = 8;
        if(left+pw > window.innerWidth-8) left = window.innerWidth-pw-8;
        gpop.style.cssText = 'width:'+pw+'px;top:'+top+'px;left:'+left+'px;max-height:'+ph+'px;';
        gpop.classList.add('is-open');
        backdrop.style.display = 'block';
    };

    // Search functionality
    var tableRows = [];
    function initSearch() {
        tableRows = Array.from(document.querySelectorAll('#broadsheetTable tbody tr[data-student-id]'));
        var searchEl = document.getElementById('searchStudent');
        if(searchEl) {
            searchEl.addEventListener('input', function() {
                var q = this.value.toLowerCase().trim();
                tableRows.forEach(function(row) {
                    var name = (row.dataset.studentName || '').toLowerCase();
                    var adm = (row.dataset.admission || '').toLowerCase();
                    row.style.display = (!q || name.indexOf(q) !== -1 || adm.indexOf(q) !== -1) ? '' : 'none';
                });
            });
        }
    }

    // Locate functionality
    function initLocate() {
        var el = document.getElementById('locateStudent');
        if(el) {
            el.addEventListener('change', function() {
                var val = this.value;
                if(!val) return;
                if(val === 'top5') { tableRows.slice(0,5).forEach(function(r){ r.style.backgroundColor='#fef9c3'; }); }
                else if(val === 'top10') { tableRows.slice(0,10).forEach(function(r){ r.style.backgroundColor='#fef9c3'; }); }
                else if(val === 'failures') { tableRows.forEach(function(r){ if(r.querySelector('.grade-f9')) r.style.backgroundColor='#fee2e2'; }); }
                else if(val.indexOf('student_') === 0) {
                    var id = val.replace('student_','');
                    var row = document.querySelector('tr[data-student-id="'+id+'"]');
                    if(row) { row.scrollIntoView({ behavior:'smooth', block:'center' }); row.style.backgroundColor='#f0fdf9'; }
                }
                setTimeout(function(){ el.value=''; }, 200);
            });
        }
    }

    // Modal functions
    window.closeSlistModal = function() { document.getElementById('slistModalOverlay').classList.remove('open'); };
    window.openStudentListModal = function() { document.getElementById('slistModalOverlay').classList.add('open'); };
    window.generateStudentList = function() {
        var btn = document.getElementById('generateListBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Opening…';

        var fieldDiv = document.getElementById('sf_fields');
        fieldDiv.innerHTML = '';
        document.querySelectorAll('#slistFieldGrid input[name="list_fields[]"]:checked').forEach(function(cb,i){
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'list_fields['+i+']'; inp.value = cb.value;
            fieldDiv.appendChild(inp);
        });

        var orderDiv = document.getElementById('sf_order');
        orderDiv.innerHTML = '';
        document.querySelectorAll('#promoOrderList .promo-order-item').forEach(function(item,i){
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'recommendation_order['+i+']'; inp.value = item.dataset.status;
            orderDiv.appendChild(inp);
        });

        document.getElementById('sf_show_photos').value = document.getElementById('slistShowPhotos').checked ? '1' : '0';
        document.getElementById('sf_show_sn').value = document.getElementById('slistShowSn').checked ? '1' : '0';

        document.getElementById('slistForm').submit();
        setTimeout(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-file-list-line me-1"></i>Generate List';
            document.getElementById('slistModalOverlay').classList.remove('open');
        }, 1500);
    };

    // Drag and drop
    (function initDnD() {
        var list = document.getElementById('promoOrderList');
        if(!list) return;
        var draggingEl = null;
        list.addEventListener('dragstart', function(e) {
            draggingEl = e.target.closest('.promo-order-item');
            if(draggingEl) e.dataTransfer.effectAllowed = 'move';
        });
        list.addEventListener('dragover', function(e) {
            e.preventDefault();
            var target = e.target.closest('.promo-order-item');
            if(!target || target === draggingEl) return;
            var rect = target.getBoundingClientRect();
            if(e.clientY < rect.top + rect.height/2) list.insertBefore(draggingEl, target);
            else list.insertBefore(draggingEl, target.nextSibling);
        });
    })();

    // Event listeners
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.grade-trigger-btn');
        if(btn) { e.preventDefault(); closeGradePop(); setTimeout(function(){ openGradePop(btn); }, 16); }
    });
    document.getElementById('gpopCloseBtn')?.addEventListener('click', closeGradePop);
    document.addEventListener('click', function(e) { if(e.target.id === 'cbPopupBackdrop') closeGradePop(); });
    document.addEventListener('keydown', function(e) { if(e.key === 'Escape') { closeGradePop(); closeSlistModal(); } });
    document.getElementById('slistModalOverlay')?.addEventListener('click', function(e) { if(e.target === this) closeSlistModal(); });

    window.scrollToTop = function() { window.scrollTo({ top:0, behavior:'smooth' }); };

    // Initialize
    initSearch();
    initLocate();
})();
</script>
@endsection
