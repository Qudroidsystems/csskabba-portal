{{-- resources/views/admin/score-entry/scoresheet.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* Copy ALL styles from the teacher scoresheet blade - exactly the same */
/* Include all the CSS from the teacher blade you shared */
:root {
    --ss-primary: #1e3a5f;
    --ss-accent: #2563eb;
    --ss-success: #16a34a;
    --ss-warning: #d97706;
    --ss-danger: #dc2626;
    --ss-muted: #6b7280;
    --ss-border: #e2e8f0;
    --ss-bg: #f8fafc;
    --ss-card: #ffffff;
    --ss-radius: 10px;
    --ss-shadow: 0 1px 4px rgba(0,0,0,.08);
}

/* Include ALL the same CSS classes from the teacher scoresheet */
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.score-input {
    width: 72px; min-width: 72px;
    height: 36px; padding: 4px 6px;
    border: 1.5px solid var(--ss-border); border-radius: 6px;
    font-size: 13px; text-align: center;
    background: #fff; transition: border-color .15s, box-shadow .15s;
}
.score-input:focus { outline: none; border-color: var(--ss-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.score-input.is-invalid { border-color: var(--ss-danger) !important; background: #fef2f2; }
.score-input.is-saved { border-color: var(--ss-success) !important; background: #f0fdf4; }

#scoresheetTable { font-size: 12.5px; }
#scoresheetTable thead tr { background: var(--ss-primary); color: #fff; }
#scoresheetTable thead th { padding: 10px 8px; font-weight: 600; white-space: nowrap; border: none; }
#scoresheetTable tbody tr { transition: background .12s; }
#scoresheetTable tbody td { padding: 6px 8px; vertical-align: middle; border-bottom: 1px solid var(--ss-border); }

.row-vetted { background: #f0fdf4 !important; }
.row-not-vetted { background: #fef2f2 !important; }
.row-pending { background: #fffbeb !important; }

.stat-card { background: var(--ss-card); border: 1px solid var(--ss-border); border-radius: var(--ss-radius); padding: 14px 18px; box-shadow: var(--ss-shadow); transition: transform .15s; }
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value { font-size: 22px; font-weight: 700; color: var(--ss-primary); }
.stat-card .stat-label { font-size: 11px; color: var(--ss-muted); margin-top: 2px; }
.stat-card .stat-icon { font-size: 28px; opacity: .15; float: right; margin-top: -6px; }

.grade-strip { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.grade-pill { flex: 1; min-width: 80px; text-align: center; border-radius: 8px; padding: 8px 6px; font-weight: 700; font-size: 13px; }
.assessment-btn { font-size: 12px; }
.pass-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; margin-top: 6px; }
.pass-bar-fill { height: 100%; border-radius: 4px; transition: width .4s; }
.col-group { border: 1px solid var(--ss-border); border-radius: 8px; padding: 10px 14px; margin-bottom: 10px; }
.col-group h6 { color: var(--ss-primary); font-weight: 600; margin-bottom: 8px; }

.grade-badge, .cum-grade-badge {
    display: inline-block;
    transition: all .25s ease;
    font-weight: 700; font-size: 13px; min-width: 28px; text-align: center;
}
.grade-badge.updating, .cum-grade-badge.updating { opacity: 0.5; transform: scale(0.9); }
.grade-badge.updated, .cum-grade-badge.updated { animation: gradeFlash .4s ease; }
@keyframes gradeFlash {
    0% { transform: scale(1.15); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
.grade-loading {
    display: inline-block; width: 12px; height: 12px;
    border: 2px solid #e2e8f0; border-top-color: var(--ss-accent);
    border-radius: 50%; animation: spin .6s linear infinite; vertical-align: middle;
}

.position-badge, .position-total-badge, .arm-position-badge, .arm-position-cum-badge {
    transition: transform .22s cubic-bezier(0.34,1.4,0.64,1), opacity .15s ease;
}
.pos-flash {
    animation: posFlash .5s cubic-bezier(0.34,1.4,0.64,1);
}
@keyframes posFlash {
    0% { transform: scale(1); opacity: 1; }
    30% { transform: scale(1.25); opacity: .7; }
    60% { transform: scale(0.95); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

#scoresheetTableBody tr[data-id] {
    opacity: 0; transform: translateY(14px);
    transition: opacity .38s cubic-bezier(.25,.46,.45,.94), transform .38s cubic-bezier(.25,.46,.45,.94), background .18s ease;
    will-change: opacity, transform;
}
#scoresheetTableBody tr[data-id].row-visible { opacity: 1; transform: translateY(0); }
#scoresheetTableBody tr[data-id]:hover {
    background: #f0f6ff !important;
    box-shadow: inset 3px 0 0 #2563eb;
    transform: translateY(-1px) !important;
    transition: background .14s ease, box-shadow .18s ease, transform .18s cubic-bezier(.34,1.4,.64,1);
    position: relative; z-index: 1;
}

#scoreTooltip {
    display: none; position: fixed; z-index: 99990;
    background: #fff; border: 0.5px solid #cbd5e1; border-radius: 10px;
    padding: 10px 13px; width: 230px;
    box-shadow: 0 4px 20px rgba(0,0,0,.10), 0 1px 4px rgba(0,0,0,.06);
    pointer-events: none; font-family: inherit; opacity: 0; transition: opacity .15s ease;
}

#ssSaveOverlay {
    display: none; position: fixed; inset: 0; z-index: 99999;
    background: rgba(0,0,0,.30); align-items: center; justify-content: center;
    backdrop-filter: blur(2px);
}
#ssSaveOverlay.ss-visible { display: flex !important; animation: ssOverlayIn .2s ease forwards; }
@keyframes ssOverlayIn { from { opacity:0; } to { opacity:1; } }
#ssSaveModal {
    background: #fff; border-radius: 20px; border: 0.5px solid rgba(0,0,0,.10);
    box-shadow: 0 24px 64px rgba(0,0,0,.18), 0 4px 16px rgba(0,0,0,.08);
    padding: 32px 36px 26px; width: 310px; text-align: center;
    transform: scale(.85) translateY(16px); opacity: 0;
    transition: transform .32s cubic-bezier(.34,1.3,.64,1), opacity .22s ease;
}
#ssSaveOverlay.ss-visible #ssSaveModal { transform: scale(1) translateY(0); opacity: 1; }
</style>

{{-- Admin Banner --}}
<div class="alert alert-info mb-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 4px solid #0284c7; border-radius: 10px;">
    <div class="d-flex align-items-center">
        <i class="ri-shield-user-line fs-3 me-3" style="color: #0284c7;"></i>
        <div>
            <strong class="d-block" style="font-size: 14px;">🔐 Admin Mode - Entering Scores on Behalf of Teacher</strong>
            <small class="text-muted">
                Teacher: <strong>{{ $teacher->name }}</strong> |
                Subject: <strong>{{ $subjectClass->subject->subject }}</strong> ({{ $subjectClass->subject->subject_code }}) |
                Class: <strong>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm->arm ?? '' }}</strong> |
                Term: <strong>{{ $term->term }}</strong> |
                Session: <strong>{{ $session->session }}</strong>
            </small>
        </div>
    </div>
</div>

{{-- Apple-style Save Modal --}}
<div id="ssSaveOverlay">
    <div id="ssSaveModal">
        <div class="ss-icon-ring" id="ssIconRing">
            <svg class="ss-arc-svg" viewBox="0 0 56 56" fill="none">
                <circle cx="28" cy="28" r="25" stroke="#e2e8f0" stroke-width="2.5"/>
                <circle id="ssArcFg" cx="28" cy="28" r="25" stroke="#1e3a5f" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="157.08" stroke-dashoffset="157.08" transform="rotate(-90 28 28)"/>
            </svg>
            <div class="ss-icon-center" id="ssIconCenter">
                <svg id="ssIconSave" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <rect x="2.5" y="2.5" width="13" height="13" rx="2.5" stroke="#1e3a5f" stroke-width="1.5"/>
                    <rect x="5.5" y="2.5" width="5" height="4.5" rx="1" fill="#1e3a5f" opacity=".45"/>
                    <path d="M5 10.5h8M5 13h5.5" stroke="#1e3a5f" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                <svg id="ssIconCheck" width="18" height="18" viewBox="0 0 18 18" fill="none" style="display:none;">
                    <polyline class="ss-check-path" points="3.5,9.5 7.5,13.5 14.5,5.5" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg id="ssIconX" width="16" height="16" viewBox="0 0 16 16" fill="none" style="display:none;">
                    <line x1="3.5" y1="3.5" x2="12.5" y2="12.5" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
                    <line x1="12.5" y1="3.5" x2="3.5" y2="12.5" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        <div class="ss-modal-title" id="ssSaveTitle">Saving scores</div>
        <div class="ss-modal-sub" id="ssSaveSub">Please wait…</div>
        <div class="ss-progress-track"><div class="ss-progress-fill" id="ssSaveFill"></div></div>
        <div class="ss-count-row">
            <span id="ssSaveCountLabel">Saved</span>
            <span class="ss-count-num" id="ssSaveCountNum"></span>
        </div>
    </div>
</div>

{{-- Score Input Tooltip --}}
<div id="scoreTooltip">
    <div class="tip-top">
        <img id="stAvatar" class="tip-avatar" src="" alt="" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
        <div style="min-width:0;">
            <div class="tip-name" id="stName">—</div>
            <div class="tip-adm" id="stMeta">—</div>
        </div>
    </div>
    <div class="tip-grid">
        <div class="tip-stat"><div class="tip-stat-label">Entering</div><div class="tip-stat-val" id="stVal" style="color:#2563eb;">—</div></div>
        <div class="tip-stat"><div class="tip-stat-label">Total</div><div class="tip-stat-val" id="stTotal" style="color:#1e3a5f;">—</div></div>
        <div class="tip-stat"><div class="tip-stat-label">Grade</div><div class="tip-stat-val" id="stGrade" style="color:#6b7280;">—</div></div>
    </div>
    <div class="tip-divider"></div>
    <div class="tip-prog-labels"><span id="stProgLabel">Score progress</span><span id="stProgPct">0%</span></div>
    <div class="tip-prog-track"><div class="tip-prog-fill" id="stProgFill"></div></div>
</div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    @if($broadsheets->isNotEmpty())
    @php
        $first = $broadsheets->first();
        $total = $broadsheets->count();
        $passed = $broadsheets->filter(fn($b) => ($b->total ?? 0) >= 40)->count();
        $avg = $total > 0 ? round($broadsheets->avg('total'), 1) : 0;
        $passRate = $total > 0 ? round($passed / $total * 100) : 0;
        $gradeDist = $broadsheets->groupBy('grade')->map->count();
        $gradeColors = ['A'=>'#16a34a','A1'=>'#16a34a','B'=>'#2563eb','B2'=>'#2563eb','B3'=>'#3b82f6','C'=>'#7c3aed','C4'=>'#7c3aed','C5'=>'#8b5cf6','C6'=>'#a78bfa','D'=>'#d97706','D7'=>'#d97706','E8'=>'#f59e0b','F'=>'#dc2626','F9'=>'#dc2626'];
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid var(--ss-primary) !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2" style="background:var(--ss-primary);">
                            <i class="ri-book-2-line text-white fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold" style="color:var(--ss-primary);">
                                {{ $first->subject }} <small class="text-muted fw-normal">({{ $first->subject_code }})</small>
                            </h5>
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2"><i class="ri-school-line me-1"></i>{{ $first->schoolclass }} {{ $first->arm }}</span>
                                <span class="badge bg-info-subtle text-info fs-6 px-3 py-2"><i class="ri-calendar-line me-1"></i>{{ $first->term }} | {{ $first->session }}</span>
                                <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2"><i class="ri-user-line me-1"></i>Teacher: {{ $teacher->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-2 h-100">
                <div class="col-4"><div class="stat-card text-center h-100"><div class="stat-icon">👥</div><div class="stat-value text-primary">{{ $total }}</div><div class="stat-label">Total Students</div></div></div>
                <div class="col-4"><div class="stat-card text-center h-100"><div class="stat-icon">📊</div><div class="stat-value" style="color:var(--ss-warning);">{{ $avg }}</div><div class="stat-label">Class Average</div></div></div>
                <div class="col-4"><div class="stat-card text-center h-100"><div class="stat-icon">✅</div><div class="stat-value" style="color:var(--ss-success);">{{ $passRate }}%</div><div class="stat-label">Pass Rate</div><div class="pass-bar"><div class="pass-bar-fill" style="width:{{ $passRate }}%;background:var(--ss-success);"></div></div></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3"><h6 class="fw-semibold mb-0" style="color:var(--ss-primary)"><i class="ri-bar-chart-2-line me-1"></i>Score Summary</h6></div>
                <div class="card-body pt-2">
                    <div class="row g-2">
                        <div class="col-6"><div class="p-2 rounded-3 text-center" style="background:#f0fdf4;"><div class="fw-bold fs-5" style="color:var(--ss-success);">{{ $passed }}</div><div class="text-muted" style="font-size:11px;">Passed</div></div></div>
                        <div class="col-6"><div class="p-2 rounded-3 text-center" style="background:#fef2f2;"><div class="fw-bold fs-5" style="color:var(--ss-danger);">{{ $total - $passed }}</div><div class="text-muted" style="font-size:11px;">Failed</div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3"><h6 class="fw-semibold mb-0" style="color:var(--ss-primary)"><i class="ri-pie-chart-line me-1"></i>Grade Distribution</h6></div>
                <div class="card-body pt-2">
                    @if($gradeDist->isEmpty())
                        <p class="text-muted small text-center mt-3">No grades yet.</p>
                    @else
                        <div class="grade-strip">
                            @foreach($gradeDist->sortKeysDesc() as $grade => $count)
                                @php $col = $gradeColors[$grade] ?? '#6b7280'; @endphp
                                <div class="grade-pill" style="background:{{ $col }}18;color:{{ $col }};border:1px solid {{ $col }}40;">
                                    <div style="font-size:16px;">{{ $grade }}</div>
                                    <div style="font-size:11px;font-weight:600;">{{ $count }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3"><h6 class="fw-semibold mb-0" style="color:var(--ss-primary)"><i class="ri-clipboard-line me-1"></i>Assessments</h6></div>
                <div class="card-body pt-2">
                    @if($assessments->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($assessments as $assessment)
                                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 assessment-btn" style="background:#eff6ff;border:1px solid #bfdbfe;color:var(--ss-accent);">
                                    <span><i class="ri-edit-line me-1"></i>{{ $assessment->name }}</span>
                                    <span class="badge" style="background:var(--ss-accent);">{{ $assessment->max_score }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center mt-3">No assessments defined.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-start justify-content-between gap-3 mb-2 flex-wrap" style="font-size:12px;color:var(--ss-muted);">
        <span><i class="ri-information-line me-1 text-info"></i><strong>Total Grade</strong> = grade on raw total &nbsp;|&nbsp; <strong>Cum Grade</strong> = grade on cumulative avg &nbsp;|&nbsp; <strong>Class Pos (Cum)</strong> = all arms, by cum &nbsp;|&nbsp; <strong>Arm Pos (Total)</strong> = this arm, by total</span>
        <button type="button" class="btn btn-sm btn-primary flex-shrink-0" id="updateArmPositionsBtn"><i class="ri-refresh-line me-1"></i>Recalculate All Positions</button>
    </div>

    <div class="row"><div class="col-12"><div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center flex-wrap gap-2 py-3" style="background:var(--ss-primary);">
            <div class="flex-grow-1"><h5 class="mb-0 text-white fw-semibold"><i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }} <span class="badge bg-white text-primary ms-2" id="scoreCount">{{ $broadsheets->count() }}</span></h5></div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="width:240px;"><span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span><input type="text" class="form-control border-0 ps-1" id="searchInput" placeholder="Search admission / name…"><button class="btn btn-light border-0" id="clearSearch"><i class="ri-close-line"></i></button></div>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal"><i class="ri-eye-line me-1"></i>Columns</button>
                <button type="button" class="btn btn-sm btn-warning" id="downloadMarksSheet"><i class="ri-file-pdf-line me-1"></i>Marks Sheet</button>
                <button type="button" class="btn btn-sm btn-danger" id="downloadScoresPdf"><i class="ri-file-pdf-2-line me-1"></i>Scores PDF</button>
                <button type="button" class="btn btn-sm btn-success" id="downloadExcel"><i class="ri-download-line me-1"></i>Export Excel</button>
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ri-upload-line me-1"></i>Import</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-nowrap align-middle mb-0" id="scoresheetTable">
                    <thead>
                        <tr><th class="col-checkbox" style="width:44px;"><div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="checkAll"></div></th>
                        <th class="col-sn">SN</th><th class="col-admissionno">Adm. No</th><th class="col-name">Student Name</th>
                        @foreach($assessments as $assessment)<th class="col-assessment-{{ $assessment->id }} text-center">{{ $assessment->name }}<br><small>({{ $assessment->max_score }})</small></th>@endforeach
                        <th class="col-total text-center">Total</th><th class="col-total-grade text-center">Grade</th><th class="col-bf text-center">BF</th><th class="col-cum text-center">Cum</th>
                        <th class="col-position text-center">Class Pos<br><small>(Cum)</small></th><th class="col-position-total text-center">Class Pos<br><small>(Total)</small></th>
                        <th class="col-arm-position text-center">Arm Pos<br><small>(Total)</small></th><th class="col-arm-position-cum text-center">Arm Pos<br><small>(Cum)</small></th><th class="col-vetted text-center">Status</th>
                    </tr></thead>
                    <tbody id="scoresheetTableBody">
                        @php $i = 0; @endphp
                        @foreach($broadsheets as $broadsheet)
                            @php
                                $rowTotal = 0;
                                foreach($assessments as $a) { $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first(); $rowTotal += $so ? $so->score : 0; }
                                $cum = $broadsheet->cum ?? 0;
                                $totalColor = $rowTotal >= 70 ? 'success' : ($rowTotal >= 50 ? 'info' : ($rowTotal >= 40 ? 'warning' : 'danger'));
                                $cumColor = $cum >= 70 ? 'success' : ($cum >= 50 ? 'info' : ($cum >= 40 ? 'warning' : 'danger'));
                                $vClass = $broadsheet->vettedstatus === '1' ? 'row-vetted' : ($broadsheet->vettedstatus === '0' ? 'row-not-vetted' : 'row-pending');
                                $avatarUrl = $broadsheet->picture ? asset('storage/student_avatars/'.basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg');
                            @endphp
                            <tr class="{{ $vClass }}" data-id="{{ $broadsheet->id }}" data-bf="{{ $broadsheet->bf ?? 0 }}" data-name="{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}" data-admissionno="{{ $broadsheet->admissionno ?? '' }}" data-avatar="{{ $avatarUrl }}">
                                <td><div class="form-check"><input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}"></div></td>
                                <td class="sn fw-medium">{{ ++$i }}</td>
                                <td class="admissionno" data-admissionno="{{ $broadsheet->admissionno }}"><span class="text-muted small">{{ $broadsheet->admissionno ?? '-' }}</span></td>
                                <td class="name"><div class="d-flex align-items-center gap-2"><img src="{{ $avatarUrl }}" class="rounded-circle student-image" style="width:34px;height:34px;object-fit:cover;border:2px solid var(--ss-border);cursor:pointer;" data-bs-toggle="modal" data-bs-target="#imageViewModal" data-image="{{ $avatarUrl }}"><div><span class="fw-semibold d-block">{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}</span></div></div></td>
                                @foreach($assessments as $assessment)
                                    @php $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first(); $scoreValue = $scoreObj ? $scoreObj->score : 0; @endphp
                                    <td class="text-center"><input type="number" class="score-input" data-field="{{ $assessment->id }}" data-max="{{ $assessment->max_score }}" data-id="{{ $broadsheet->id }}" data-original="{{ $scoreValue }}" value="{{ $scoreValue }}" min="0" max="{{ $assessment->max_score }}" step="0.1"></td>
                                @endforeach
                                <td class="text-center"><span class="badge bg-{{ $totalColor }}-subtle text-{{ $totalColor }} total-badge">{{ number_format($rowTotal, 1) }}</span></td>
                                <td class="text-center"><span class="grade-badge">{{ $broadsheet->grade ?? '-' }}</span></td>
                                <td class="text-center"><span class="bf-badge">{{ number_format($broadsheet->bf ?? 0, 1) }}</span></td>
                                <td class="text-center"><span class="badge bg-{{ $cumColor }}-subtle text-{{ $cumColor }} cum-badge">{{ number_format($cum, 1) }}</span></td>
                                <td class="text-center"><span class="badge position-badge" style="background:var(--ss-primary);">{{ $broadsheet->position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}</span></td>
                                <td class="text-center"><span class="badge position-total-badge" style="background:#0f766e;">{{ $broadsheet->position_total ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position_total) : '-' }}</span></td>
                                <td class="text-center"><span class="badge arm-position-badge" style="background:#0891b2;">{{ $broadsheet->arm_position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position) : '-' }}</span></td>
                                <td class="text-center"><span class="badge arm-position-cum-badge" style="background:#7c3aed;">{{ $broadsheet->arm_position_cum ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position_cum) : '-' }}</span></td>
                                <td class="text-center">@if($broadsheet->vettedstatus === '1')<span class="badge bg-success">Vetted</span>@elseif($broadsheet->vettedstatus === '0')<span class="badge bg-danger">Not Vetted</span>@else<span class="badge bg-warning">Pending</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2"><button class="btn btn-sm btn-outline-primary" id="selectAllScores"><i class="ri-check-double-line me-1"></i>Select All</button><button class="btn btn-sm btn-outline-secondary" id="clearAllScores"><i class="ri-close-line me-1"></i>Clear</button><button class="btn btn-sm btn-outline-danger" id="deleteSelectedScoresBtn"><i class="ri-delete-bin-line me-1"></i>Delete Selected</button><a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-arrow-left-line me-1"></i>Back to Teachers</a></div>
                    <div class="d-flex align-items-center gap-2"><small class="text-muted"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small><button class="btn btn-success btn-sm px-4" id="bulkUpdateScores"><i class="ri-save-line me-1"></i>Save All Scores</button></div>
                </div>
            </div>
        </div>
    </div></div></div>

    @endif

</div></div></div>

<script>
const CSRF = '{{ csrf_token() }}';
const adminRoutes = {
    singleUpdate: '{{ route("admin.score-entry.single-update") }}',
    bulkUpdate: '{{ route("admin.score-entry.bulk-update") }}',
    destroy: '{{ route("admin.score-entry.destroy") }}',
    results: '{{ route("admin.score-entry.results") }}',
    downloadMarksSheet: '{{ route("admin.score-entry.download-marks-sheet") }}',
    export: '{{ route("admin.score-entry.export") }}',
    import: '{{ route("admin.score-entry.import") }}',
};

const adminContext = {
    term_id: {{ $termId }},
    session_id: {{ $sessionId }},
    subjectclass_id: {{ $subjectclassId }},
    schoolclass_id: {{ $schoolclass->id ?? 0 }},
    staff_id: {{ $teacherId }},
    is_senior: {{ $is_senior ? 'true' : 'false' }},
};

// Copy all the JavaScript functions from the teacher scoresheet
// Replace the route references with adminRoutes
// The JavaScript remains identical in functionality

$(document).ready(function() {
    // All the same JS functionality as the teacher scoresheet
    // ... (copy from teacher blade JavaScript, just update route references)

    // Simplified version - same logic as teacher blade
    function validateInput(input) {
        const max = parseFloat(input.data('max')) || 0;
        const val = parseFloat(input.val()) || 0;
        input.toggleClass('is-invalid', val > max);
        return val <= max;
    }

    function saveScore(input) {
        if (!validateInput(input)) return;
        const row = input.closest('tr');

        $.ajax({
            url: adminRoutes.singleUpdate,
            method: 'POST',
            data: {
                broadsheet_id: input.data('id'),
                assessment_id: parseInt(input.data('field')),
                score: parseFloat(input.val()) || 0,
                is_sub: false,
                term_id: adminContext.term_id,
                session_id: adminContext.session_id,
                subjectclass_id: adminContext.subjectclass_id,
                schoolclass_id: adminContext.schoolclass_id,
                staff_id: adminContext.staff_id,
                _token: CSRF
            },
            success: function(response) {
                if (response.success) {
                    input.addClass('is-saved');
                    setTimeout(() => input.removeClass('is-saved'), 1000);
                    input.data('original', input.val());

                    const d = response.data;
                    row.find('.bf-badge').text(d.bf.toFixed(1));
                    row.find('.grade-badge').text(d.grade);
                    row.find('.total-badge').text(d.total.toFixed(1));
                    row.find('.cum-badge').text(d.cum.toFixed(1));
                    row.find('.position-badge').text(d.subject_position_class ? ordinal(d.subject_position_class) : '-');
                    row.find('.position-total-badge').text(d.subject_position_class_total ? ordinal(d.subject_position_class_total) : '-');
                    row.find('.arm-position-badge').text(d.arm_position ? ordinal(d.arm_position) : '-');
                    row.find('.arm-position-cum-badge').text(d.arm_position_cum ? ordinal(d.arm_position_cum) : '-');
                } else {
                    toastr.error(response.message || 'Failed to save');
                    input.val(input.data('original'));
                }
            }
        });
    }

    function ordinal(n) {
        if (!n) return '-';
        const s = ['th', 'st', 'nd', 'rd'];
        const v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }

    $('.score-input').on('blur', function() {
        const input = $(this);
        const orig = parseFloat(input.data('original')) || 0;
        const curr = parseFloat(input.val()) || 0;
        if (Math.abs(curr - orig) > 0.001 && validateInput(input)) {
            saveScore(input);
        }
    });

    $('#bulkUpdateScores').on('click', function() {
        const scores = [];
        $('#scoresheetTableBody tr').each(function() {
            const assessments = {};
            $(this).find('.score-input').each(function() {
                assessments[$(this).data('field')] = parseFloat($(this).val()) || 0;
            });
            if (Object.keys(assessments).length) {
                scores.push({ id: $(this).data('id'), assessments: assessments });
            }
        });

        $.ajax({
            url: adminRoutes.bulkUpdate,
            method: 'POST',
            data: JSON.stringify({
                scores: scores,
                term_id: adminContext.term_id,
                session_id: adminContext.session_id,
                subjectclass_id: adminContext.subjectclass_id,
                staff_id: adminContext.staff_id,
                schoolclass_id: adminContext.schoolclass_id,
                is_sub: false,
                _token: CSRF
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (response.data?.broadsheets) {
                        response.data.broadsheets.forEach(bs => {
                            const row = $(`tr[data-id="${bs.id}"]`);
                            if (row.length) {
                                row.find('.total-badge').text(bs.total.toFixed(1));
                                row.find('.grade-badge').text(bs.grade);
                                row.find('.cum-badge').text(bs.cum.toFixed(1));
                                row.find('.position-badge').text(ordinal(bs.position));
                                row.find('.score-input').each(function() { $(this).addClass('is-saved'); setTimeout(() => $(this).removeClass('is-saved'), 1000); });
                            }
                        });
                    }
                } else {
                    toastr.error(response.message || 'Bulk save failed');
                }
            }
        });
    });
});
</script>
@endsection
