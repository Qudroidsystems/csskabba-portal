@extends('layouts.master')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">

<style>
:root {
    --cb-navy: #0f2342;
    --cb-teal: #0d9488;
    --cb-sky: #0ea5e9;
    --cb-amber: #f59e0b;
    --cb-rose: #f43f5e;
    --cb-green: #22c55e;
    --cb-muted: #64748b;
    --cb-border: #e2e8f0;
    --cb-surface: #f8fafc;
    --cb-white: #ffffff;
    --cb-radius: 14px;
    --cb-shadow: 0 4px 16px rgba(15,35,66,.10);
}

*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: #f1f5f9; }

/* Animations */
@keyframes fadeInUp { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInDown { from { opacity:0; transform:translateY(-22px); } to { opacity:1; transform:translateY(0); } }
@keyframes scaleIn { from { opacity:0; transform:scale(.88); } to { opacity:1; transform:scale(1); } }
@keyframes slideInRight { from { transform:translateX(110%); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes popIn { 0% { opacity:0; transform:scale(.7) translateY(12px); } 100% { opacity:1; transform:scale(1) translateY(0); } }
@keyframes backdropIn { from { opacity:0; } to { opacity:1; } }
@keyframes rowSlide { from { opacity:0; transform:translateX(-12px); } to { opacity:1; transform:translateX(0); } }

/* Hero */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.cb-hero h1 { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; color:#fff; margin:0 0 8px; }
.cb-hero p { font-size:13px; color:rgba(255,255,255,.72); margin:0; }
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
.cb-stat .stat-accent { position:absolute; top:0; left:0; right:0; height:3px; }
.cb-stat .stat-value { font-size:30px; font-weight:700; color:var(--cb-navy); line-height:1; margin-top:8px; }
.cb-stat .stat-label { font-size:12px; color:var(--cb-muted); margin-top:5px; font-weight:500; }
.cb-stat .stat-ico { font-size:36px; opacity:.08; position:absolute; right:16px; top:50%; transform:translateY(-50%); }

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

/* Broadsheet Table */
.broadsheet-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    background: white;
    border: 1.5px solid var(--cb-navy);
}
.broadsheet-table thead tr.subject-header th {
    background: var(--cb-navy);
    color: #fff;
    text-align: center;
    padding: 8px 4px;
    border: 0.5px solid #2563eb;
    font-weight: 600;
    font-size: 11px;
}
.broadsheet-table thead tr.subject-header th.student-col {
    background: #0f2040;
    text-align: left;
    padding-left: 8px;
}
.broadsheet-table thead tr.subject-header th.subj-name-hdr {
    background: #163562;
    font-size: 10px;
}
.broadsheet-table thead tr.assessment-header th {
    background: #1a3d6a;
    color: #a8d4ef;
    text-align: center;
    padding: 6px 3px;
    border: 0.5px solid #2563eb;
    font-size: 9px;
}
.broadsheet-table tbody tr:nth-child(odd) { background: #ffffff; }
.broadsheet-table tbody tr:nth-child(even) { background: #f8fafc; }
.broadsheet-table tbody tr:hover { background: #e8f0fe !important; }
.broadsheet-table tbody td {
    padding: 6px 4px;
    border: 0.5px solid #e2e8f0;
    text-align: center;
    vertical-align: middle;
    font-size: 11px;
}
.broadsheet-table tbody td.student-info-cell {
    text-align: left;
    padding-left: 8px;
    font-weight: 600;
}

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
.score-red { color:#dc2626 !important; font-weight:700; }
.score-amber { color:#d97706 !important; font-weight:700; }
.score-green { color:#16a34a !important; font-weight:700; }

/* GPA cells */
.gpa-cell { background:#eff6ff !important; color:#1e3a8a; font-weight:700; border-left:1.5px solid #3b82f6 !important; }

/* Promotion cells */
.promo-cell { text-align: center; border-left: 2px solid #7c3aed !important; }
.promo-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 700;
}
.promo-promoted { background: #d1fae5; color: #065f46; }
.promo-trial { background: #fef3c7; color: #92400e; }
.promo-see_principal { background: #dbeafe; color: #1e40af; }
.promo-repeated { background: #fee2e2; color: #991b1b; }
.promo-awaiting { background: #f1f5f9; color: #475569; }
.promo-header-th { background: #3b0764 !important; border-left: 2px solid #7c3aed !important; }

/* Search */
.cb-search { position:relative; }
.cb-search input {
    width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--cb-border);
    border-radius:10px; font-size:13px; background:var(--cb-surface);
}
.cb-search i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--cb-muted); }

/* Student List Modal */
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

/* Print */
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; font-size: 10px; }
    @page { margin: 1.5cm 1.2cm; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

{{-- Hero Section --}}
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
            <div class="stat-value">{{ $totalStudents }}</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-book-open-line"></i></div>
            <div class="stat-value text-info">{{ count($subjects) }}</div>
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
<div style="background:linear-gradient(135deg,var(--cb-navy) 0%,#2563eb 100%);border-radius:10px;padding:18px 24px;margin-bottom:16px;color:white;">
    <div class="d-flex align-items-center">
        @if(!empty($school_logo_base64))
            <img src="{{ $school_logo_base64 }}" alt="Logo" style="width:65px;height:65px;object-fit:contain;border-radius:50%;border:2px solid rgba(255,255,255,.4);margin-right:18px;">
        @endif
        <div class="flex-grow-1 text-center">
            <h4 class="mb-1 fw-bold text-uppercase">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</h4>
            @if(!empty($schoolInfo->school_address))
                <p class="mb-1 opacity-75" style="font-size:13px;">{{ $schoolInfo->school_address }}</p>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <p class="mb-0 fst-italic opacity-75" style="font-size:12px;">"{{ $schoolInfo->school_motto }}"</p>
            @endif
        </div>
    </div>
</div>

{{-- Title Strip --}}
<div style="background:var(--cb-navy);color:white;text-align:center;padding:10px;font-size:15px;font-weight:700;letter-spacing:1.5px;border-radius:8px;margin-bottom:14px;">
    CLASS ACADEMIC BROADSHEET
    @if(!empty($is_combined))<span style="font-size:11px;opacity:.7;margin-left:10px;">— Combined Arms</span>@endif
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
    <span class="text-muted ms-2" style="font-size:11px;"><strong>BF</strong>=Brought Forward &nbsp; <strong>CUM</strong>=(BF+Total)÷2</span>
</div>

{{-- Toolbar --}}
<div class="cb-card mb-3 no-print">
    <div class="cb-card-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="cb-search" style="max-width:260px;">
                <i class="ri-search-line"></i>
                <input type="text" id="searchStudent" placeholder="Search name or admission no…">
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="ri-printer-line me-1"></i>Print</button>
            <button class="btn btn-sm" onclick="openStudentListModal()" style="background:linear-gradient(135deg,#3b0764,#7c3aed);color:#fff;border:none;"><i class="ri-list-check-2 me-1"></i>Print Student List</button>
        </div>
    </div>
</div>

@php
    $selected = $selectedColumns ?? [];
    $showAll = empty($selected);

    // Column visibility
    $showAdmNo = $showAll || in_array('admission_no', $selected);
    $showGender = in_array('gender', $selected);
    $showTotal = $showAll || in_array('total', $selected);
    $showBF = $showAll || in_array('bf', $selected);
    $showCum = $showAll || in_array('cum', $selected);
    $showGrade = $showAll || in_array('grade', $selected);
    $showGPA = $showAll || in_array('gpa', $selected);
    $showPosTerm = $showAll || in_array('position_term', $selected);
    $showPosCum = $showAll || in_array('position_cum', $selected);

    // Promotion columns
    $showPromoStatus = $showAll || in_array('promotion_status', $selected);
    $showPromoLabel = in_array('promotion_label', $selected);
    $showPromoRule = in_array('promotion_rule_applied', $selected);
    $promoColspan = ($showPromoStatus ? 1 : 0) + ($showPromoLabel ? 1 : 0) + ($showPromoRule ? 1 : 0);

    $activeAssessments = $assessments->filter(fn($a) => empty($selected) || in_array('assessment_' . $a->id, $selected));
    $gradeColors = ['A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3','C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6','D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9','-'=>''];

    $subColspan = $activeAssessments->count() + ($showTotal?1:0) + ($showBF?1:0) + ($showCum?1:0) + ($showGrade?1:0);
@endphp

{{-- Main Table --}}
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
                {{-- Header Row 1 --}}
                <tr class="subject-header">
                    <th class="student-col" rowspan="2" style="width:36px;">#</th>
                    @if($showAdmNo)<th class="student-col" rowspan="2" style="min-width:80px;">Adm. No</th>@endif
                    <th class="student-col" rowspan="2" style="min-width:180px;text-align:left;padding-left:8px;">Student Name</th>
                    @if($showGender)<th class="student-col" rowspan="2" style="width:40px;">Sex</th>@endif
                    @if($showPosTerm || $showPosCum)<th class="student-col" rowspan="2" style="width:70px;">Position</th>@endif

                    @foreach($subjects as $subId => $subInfo)
                        <th class="subj-name-hdr" colspan="{{ $subColspan }}">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))<br><small>({{ $subInfo['subject_code'] }})</small>@endif
                        </th>
                    @endforeach

                    @if($showGPA)<th rowspan="2" style="background:#0a1e38;">GPA</th>@endif
                    @if($promoColspan > 0)<th colspan="{{ $promoColspan }}" class="promo-header-th">🎓 PROMOTION</th>@endif
                </tr>

                {{-- Header Row 2 --}}
                <tr class="assessment-header">
                    @foreach($subjects as $subId => $subInfo)
                        @foreach($activeAssessments as $a)
                            <th style="min-width:40px;">{{ $a->name }}<br><span style="font-size:8px;">/{{ $a->max_score }}</span></th>
                        @endforeach
                        @if($showTotal)<th>Total</th>@endif
                        @if($showBF)<th>BF</th>@endif
                        @if($showCum)<th>Cum</th>@endif
                        @if($showGrade)<th>Grd</th>@endif
                    @endforeach

                    @if($showPromoStatus)<th class="promo-header-th">Status</th>@endif
                    @if($showPromoLabel)<th class="promo-header-th">Label</th>@endif
                    @if($showPromoRule)<th class="promo-header-th">Rule</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($studentRows as $idx => $stu)
                @php
                    $fullName = trim(($stu['lastname'] ?? '') . ' ' . ($stu['firstname'] ?? ''));
                    $hasPic = !empty($stu['picture']) && $stu['picture'] !== 'unnamed.jpg';
                    $imgSrc = $hasPic ? asset('storage/student_avatars/' . basename($stu['picture'])) : null;
                    $initials = strtoupper(substr($stu['lastname']??'',0,1) . substr($stu['firstname']??'',0,1)) ?: 'ST';
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    @if($showAdmNo)<td>{{ $stu['admissionno'] ?? '—' }}</td>@endif
                    <td class="student-info-cell">
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if($imgSrc)
                                <div class="cb-avatar" style="width:30px;height:30px;border-radius:50%;overflow:hidden;"><img src="{{ $imgSrc }}" style="width:100%;height:100%;object-fit:cover;"></div>
                            @else
                                <div class="cb-avatar-initials" style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">{{ $initials }}</div>
                            @endif
                            <div><strong>{{ strtoupper($stu['lastname'] ?? '') }}, {{ $stu['firstname'] ?? '' }}</strong></div>
                        </div>
                    </td>
                    @if($showGender)<td>{{ substr($stu['gender'] ?? '-', 0, 1) }}</td>@endif
                    @if($showPosTerm || $showPosCum)
                        <td style="text-align:center;">
                            <div style="display:inline-flex;flex-direction:column;align-items:center;gap:2px;">
                                <span style="font-size:9px;background:#fef3c7;padding:1px 5px;border-radius:4px;">T:{{ $stu['position_term'] ?? '—' }}</span>
                                <span style="font-size:9px;background:#dbeafe;padding:1px 5px;border-radius:4px;">C:{{ $stu['position_cum'] ?? '—' }}</span>
                            </div>
                        </td>
                    @endif

                    {{-- Subject Columns --}}
                    @foreach($subjects as $subId => $subInfo)
                        @php
                            $subjectData = $stu['subjects'][$subId] ?? [];
                            $total = $subjectData['total'] ?? 0;
                            $bf = $subjectData['bf'] ?? 0;
                            $cum = $subjectData['cum'] ?? 0;
                            $grade = $subjectData['grade'] ?? '-';
                            $gradeClass = $gradeColors[$grade] ?? '';
                        @endphp

                        {{-- Assessment Scores --}}
                        @foreach($activeAssessments as $a)
                            @php $as = $subjectData['assessments'][$a->id] ?? 0; @endphp
                            <td>{{ $as > 0 ? number_format($as, 1) : '—' }}</td>
                        @endforeach

                        {{-- Total --}}
                        @if($showTotal)
                            <td class="{{ $gradeClass }}" style="font-weight:600;">{{ $total > 0 ? number_format($total, 1) : '—' }}</td>
                        @endif

                        {{-- BF --}}
                        @if($showBF)
                            <td>{{ $bf > 0 ? number_format($bf, 1) : '—' }}</td>
                        @endif

                        {{-- Cum --}}
                        @if($showCum)
                            <td class="{{ $gradeClass }}" style="font-weight:700;">{{ $cum > 0 ? number_format($cum, 1) : '—' }}</td>
                        @endif

                        {{-- Grade --}}
                        @if($showGrade)
                            <td class="{{ $gradeClass }}" style="font-weight:700;">{{ $grade }}</td>
                        @endif
                    @endforeach

                    {{-- GPA --}}
                    @if($showGPA)
                        <td class="gpa-cell">{{ number_format($stu['gpa'] ?? 0, 2) }}</td>
                    @endif

                    {{-- Promotion Columns --}}
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

                    @if($showPromoLabel)
                        <td class="promo-cell">{{ $stu['promotion_label'] ?? '—' }}</td>
                    @endif

                    @if($showPromoRule)
                        <td class="promo-cell">{{ $stu['promotion_rule_applied'] ? Str::limit($stu['promotion_rule_applied'], 20) : '—' }}</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:40px;">
                        <i class="ri-information-line" style="font-size:40px;color:#94a3b8;"></i>
                        <p style="margin-top:10px;">No student records found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Student List Modal --}}
<div class="slist-modal-overlay" id="slistModalOverlay">
    <div class="slist-modal">
        <div class="slist-modal-header">
            <h5>📋 Print Student List Preferences</h5>
            <button class="slist-modal-close" onclick="closeSlistModal()">&times;</button>
        </div>
        <div class="slist-modal-body">
            <div class="mb-4">
                <div class="slist-section-title"><i class="ri-table-line"></i> Student Fields to Include</div>
                <div class="field-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <label><input type="checkbox" name="list_fields[]" value="admissionno" checked> Admission Number</label>
                    <label><input type="checkbox" name="list_fields[]" value="firstname" checked> First Name</label>
                    <label><input type="checkbox" name="list_fields[]" value="lastname" checked> Last Name</label>
                    <label><input type="checkbox" name="list_fields[]" value="gender"> Gender</label>
                    <label><input type="checkbox" name="list_fields[]" value="total_cum" checked> Cum Total Score</label>
                    <label><input type="checkbox" name="list_fields[]" value="position_cum" checked> Overall Pos (Cum)</label>
                </div>
            </div>
        </div>
        <div class="slist-modal-footer">
            <button class="btn btn-secondary" onclick="closeSlistModal()">Cancel</button>
            <button class="btn btn-primary" onclick="generateStudentList()" style="background:linear-gradient(135deg,#3b0764,#7c3aed);border:none;">Generate List</button>
        </div>
    </div>
</div>

<form id="slistForm" method="POST" action="{{ route('broadsheet.student-list') }}" target="_blank" style="display:none;">
    @csrf
    <input type="hidden" name="schoolclassid" value="{{ request('schoolclassid') }}">
    <input type="hidden" name="sessionid" value="{{ request('sessionid') }}">
    <input type="hidden" name="termid" value="{{ request('termid') }}">
    <div id="sf_fields"></div>
</form>

</div></div></div>

<script>
// Student List Modal Functions
function closeSlistModal() {
    document.getElementById('slistModalOverlay').classList.remove('open');
}

function openStudentListModal() {
    document.getElementById('slistModalOverlay').classList.add('open');
}

function generateStudentList() {
    var fieldDiv = document.getElementById('sf_fields');
    fieldDiv.innerHTML = '';
    document.querySelectorAll('#slistModalOverlay input[name="list_fields[]"]:checked').forEach(function(cb, i) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'list_fields[' + i + ']';
        inp.value = cb.value;
        fieldDiv.appendChild(inp);
    });
    document.getElementById('slistForm').submit();
    closeSlistModal();
}

// Close modal on overlay click
document.getElementById('slistModalOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeSlistModal();
});

// ESC closes modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSlistModal();
});

// Search functionality
document.getElementById('searchStudent')?.addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var rows = document.querySelectorAll('#broadsheetTable tbody tr');
    var visibleCount = 0;
    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();
        var isVisible = text.includes(searchTerm);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });
    if (searchTerm) {
        console.log('Found ' + visibleCount + ' student(s)');
    }
});

// Calculate average percentage
function calculateAverage() {
    var rows = document.querySelectorAll('#broadsheetTable tbody tr');
    var totalPct = 0;
    var count = 0;
    var topCum = -1;
    var topName = '';

    rows.forEach(function(row) {
        var cumCell = row.querySelector('td:nth-child(' + (document.querySelectorAll('.gpa-cell').length > 0 ? 'last' : '5') + ')');
        // This is simplified - in production you'd parse properly
        count++;
    });

    document.getElementById('statAvgPct').textContent = '0%';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    calculateAverage();
});
</script>
@endsection
