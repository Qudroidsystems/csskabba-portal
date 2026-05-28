{{-- resources/views/admin/score-entry/scoresheet.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* ── Scoresheet Design System ────────────────────────────────────── */
:root {
    --ss-primary:   #1e3a5f;
    --ss-accent:    #2563eb;
    --ss-success:   #16a34a;
    --ss-warning:   #d97706;
    --ss-danger:    #dc2626;
    --ss-muted:     #6b7280;
    --ss-border:    #e2e8f0;
    --ss-bg:        #f8fafc;
    --ss-card:      #ffffff;
    --ss-radius:    10px;
    --ss-shadow:    0 1px 4px rgba(0,0,0,.08);
}

.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.score-input {
    width: 72px; min-width: 72px;
    height: 36px; padding: 4px 6px;
    border: 1.5px solid var(--ss-border); border-radius: 6px;
    font-size: 13px; text-align: center;
    background: #fff; transition: border-color .15s, box-shadow .15s;
}
.score-input:focus      { outline: none; border-color: var(--ss-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.score-input.is-invalid { border-color: var(--ss-danger)  !important; background: #fef2f2; }
.score-input.is-saved   { border-color: var(--ss-success) !important; background: #f0fdf4; }
.score-input:disabled   { background: #f3f4f6; cursor: not-allowed; opacity: 0.7; }

#scoresheetTable { font-size: 12.5px; }
#scoresheetTable thead tr { background: var(--ss-primary); color: #fff; }
#scoresheetTable thead th { padding: 10px 8px; font-weight: 600; white-space: nowrap; border: none; }
#scoresheetTable tbody tr { transition: background .12s; }
#scoresheetTable tbody td { padding: 6px 8px; vertical-align: middle; border-bottom: 1px solid var(--ss-border); }

.row-vetted     { background: #f0fdf4 !important; }
.row-not-vetted { background: #fef2f2 !important; }
.row-pending    { background: #fffbeb !important; }
.row-locked     { background: #fef2f2 !important; opacity: 0.85; }

.stat-card { background: var(--ss-card); border: 1px solid var(--ss-border); border-radius: var(--ss-radius); padding: 14px 18px; box-shadow: var(--ss-shadow); transition: transform .15s; }
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value { font-size: 22px; font-weight: 700; color: var(--ss-primary); }
.stat-card .stat-label { font-size: 11px; color: var(--ss-muted); margin-top: 2px; }
.stat-card .stat-icon  { font-size: 28px; opacity: .15; float: right; margin-top: -6px; }

.grade-strip { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.grade-pill  { flex: 1; min-width: 80px; text-align: center; border-radius: 8px; padding: 8px 6px; font-weight: 700; font-size: 13px; }
.assessment-btn { font-size: 12px; }
.pass-bar      { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; margin-top: 6px; }
.pass-bar-fill { height: 100%; border-radius: 4px; transition: width .4s; }
.col-group     { border: 1px solid var(--ss-border); border-radius: 8px; padding: 10px 14px; margin-bottom: 10px; }
.col-group h6  { color: var(--ss-primary); font-weight: 600; margin-bottom: 8px; }

.grade-badge, .cum-grade-badge {
    display: inline-block; transition: all .25s ease;
    font-weight: 700; font-size: 13px; min-width: 28px; text-align: center;
}
.grade-badge.updating, .cum-grade-badge.updating { opacity: 0.5; transform: scale(0.9); }
.grade-badge.updated,  .cum-grade-badge.updated  { animation: gradeFlash .4s ease; }
@keyframes gradeFlash { 0% { transform: scale(1.15); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
.grade-loading {
    display: inline-block; width: 12px; height: 12px;
    border: 2px solid #e2e8f0; border-top-color: var(--ss-accent);
    border-radius: 50%; animation: spin .6s linear infinite; vertical-align: middle;
}

.position-badge, .position-total-badge, .arm-position-badge, .arm-position-cum-badge {
    transition: transform .22s cubic-bezier(0.34,1.4,0.64,1), opacity .15s ease;
}
.pos-flash { animation: posFlash .5s cubic-bezier(0.34,1.4,0.64,1); }
@keyframes posFlash { 0%{transform:scale(1);opacity:1;} 30%{transform:scale(1.25);opacity:.7;} 60%{transform:scale(0.95);opacity:1;} 100%{transform:scale(1);opacity:1;} }

/* ROW ENTRANCE */
#scoresheetTableBody tr[data-id] {
    opacity: 0; transform: translateY(14px);
    transition: opacity .38s cubic-bezier(.25,.46,.45,.94), transform .38s cubic-bezier(.25,.46,.45,.94), background .18s ease;
    will-change: opacity, transform;
}
#scoresheetTableBody tr[data-id].row-visible { opacity: 1; transform: translateY(0); }
#scoresheetTableBody tr[data-id]:hover {
    background: #f0f6ff !important; box-shadow: inset 3px 0 0 #2563eb;
    transform: translateY(-1px) !important; position: relative; z-index: 1;
}

.student-image { transition: transform .18s ease, box-shadow .18s ease; }

/* APPLE-STYLE SAVE MODAL */
#ssSaveOverlay {
    display: none; position: fixed; inset: 0; z-index: 99999;
    background: rgba(0,0,0,.30); align-items: center; justify-content: center;
    backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);
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
#ssSaveOverlay.ss-visible  #ssSaveModal { transform: scale(1) translateY(0); opacity: 1; }
#ssSaveOverlay.ss-closing  #ssSaveModal { transform: scale(.88) translateY(10px); opacity: 0; }
</style>

{{-- ══ APPLE-STYLE SAVE MODAL ══════════════════════════════════════ --}}
<div id="ssSaveOverlay">
    <div id="ssSaveModal">
        <div class="ss-icon-ring" id="ssIconRing">
            <svg class="ss-arc-svg" viewBox="0 0 56 56" fill="none">
                <circle cx="28" cy="28" r="25" stroke="#e2e8f0" stroke-width="2.5"/>
                <circle id="ssArcFg" cx="28" cy="28" r="25"
                    stroke="#1e3a5f" stroke-width="2.5" stroke-linecap="round"
                    stroke-dasharray="157.08" stroke-dashoffset="157.08"
                    transform="rotate(-90 28 28)"
                    style="transition: stroke-dashoffset .38s cubic-bezier(.4,0,.2,1), stroke .3s ease;"/>
            </svg>
            <div class="ss-icon-center" id="ssIconCenter">
                <svg id="ssIconSave" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <rect x="2.5" y="2.5" width="13" height="13" rx="2.5" stroke="#1e3a5f" stroke-width="1.5"/>
                    <rect x="5.5" y="2.5" width="5" height="4.5" rx="1" fill="#1e3a5f" opacity=".45"/>
                    <path d="M5 10.5h8M5 13h5.5" stroke="#1e3a5f" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                <svg id="ssIconCheck" width="18" height="18" viewBox="0 0 18 18" fill="none" style="display:none;">
                    <polyline class="ss-check-path" id="ssCheckPath"
                        points="3.5,9.5 7.5,13.5 14.5,5.5"
                        stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg id="ssIconX" width="16" height="16" viewBox="0 0 16 16" fill="none" style="display:none;">
                    <line x1="3.5" y1="3.5" x2="12.5" y2="12.5" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
                    <line x1="12.5" y1="3.5" x2="3.5" y2="12.5" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        <div class="ss-modal-title" id="ssSaveTitle">Saving scores</div>
        <div class="ss-modal-sub"  id="ssSaveSub">Please wait…</div>
        <div class="ss-progress-track"><div class="ss-progress-fill" id="ssSaveFill"></div></div>
        <div class="ss-count-row">
            <span id="ssSaveCountLabel">Saved</span>
            <span class="ss-count-num" id="ssSaveCountNum"></span>
        </div>
    </div>
</div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Admin Banner --}}
    <div class="admin-banner" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 4px solid #0284c7; border-radius: var(--ss-radius); padding: 14px 20px; margin-bottom: 20px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <i class="ri-shield-user-line fs-2" style="color: #0284c7;"></i>
                <div>
                    <strong class="d-block" style="font-size: 15px;">Admin Score Entry Mode</strong>
                    <small class="text-muted">
                        Entering scores on behalf of: <strong>{{ $teacher->name }}</strong> |
                        Subject: <strong>{{ $subjectClass->subject->subject }}</strong> ({{ $subjectClass->subject->subject_code }}) |
                        Class: <strong>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm->arm ?? '' }}</strong>
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary"><i class="ri-calendar-line me-1"></i>{{ $term->term }}</span>
                <span class="badge bg-info"><i class="ri-calendar-event-line me-1"></i>{{ $session->session }}</span>
            </div>
        </div>
    </div>

    {{-- Lock Status Banner --}}
    @if($globalLock || ($lockedCount ?? 0) > 0 || !$teacherEditingEnabled)
    <div class="alert alert-warning mb-3" style="border-left: 4px solid #d97706;">
        <div class="d-flex align-items-center gap-3">
            <i class="ri-lock-line fs-3 text-warning"></i>
            <div class="flex-grow-1">
                @if(!$teacherEditingEnabled)
                    <strong><i class="ri-alert-line me-1"></i> Teacher Editing Disabled</strong><br>
                    <small>Teacher editing has been disabled for this subject by an administrator.</small>
                @elseif($globalLock)
                    <strong><i class="ri-global-line me-1"></i> Global Lock Active</strong><br>
                    <small>This entire scoresheet is locked. Reason: {{ $globalLock->reason ?? 'No reason provided' }}</small>
                @elseif(($lockedCount ?? 0) > 0)
                    <strong><i class="ri-lock-line me-1"></i> {{ $lockedCount }} of {{ $broadsheets->count() }} scoresheets are locked</strong>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($broadsheets->isNotEmpty())
    @php
        $first    = $broadsheets->first();
        $total    = $broadsheets->count();
        $passed   = $broadsheets->filter(fn($b) => ($b->total ?? 0) >= 40)->count();
        $failed   = $total - $passed;
        $avg      = $total > 0 ? round($broadsheets->avg('total'), 1) : 0;
        $highest  = $total > 0 ? round($broadsheets->max('total'), 1) : 0;
        $lowest   = $total > 0 ? round($broadsheets->min('total'), 1) : 0;
        $passRate = $total > 0 ? round($passed / $total * 100) : 0;
        $gradeDist = $broadsheets->groupBy('grade')->map->count();
        $gradeColors = [
            'A'  => '#16a34a', 'A1' => '#16a34a',
            'B'  => '#2563eb', 'B2' => '#2563eb', 'B3' => '#3b82f6',
            'C'  => '#7c3aed', 'C4' => '#7c3aed', 'C5' => '#8b5cf6', 'C6' => '#a78bfa',
            'D'  => '#d97706', 'D7' => '#d97706', 'E8' => '#f59e0b',
            'F'  => '#dc2626', 'F9' => '#dc2626',
        ];
    @endphp

    {{-- ══ INFO + STATS ════════════════════════════════════════════════ --}}
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
                                {{ $first->subject }}
                                <small class="text-muted fw-normal">({{ $first->subject_code }})</small>
                            </h5>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">
                                    <i class="ri-school-line me-1"></i>{{ $first->schoolclass }} {{ $first->arm }}
                                </span>
                                <span class="badge bg-info-subtle text-info fs-6 px-3 py-2">
                                    <i class="ri-calendar-line me-1"></i>{{ $first->term }} | {{ $first->session }}
                                </span>
                                <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2">
                                    <i class="ri-user-line me-1"></i>{{ $teacher->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-2 h-100">
                <div class="col-4"><div class="stat-card text-center h-100">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value text-primary">{{ $total }}</div>
                    <div class="stat-label">Total Students</div>
                </div></div>
                <div class="col-4"><div class="stat-card text-center h-100">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value" style="color:var(--ss-warning);">{{ $avg }}</div>
                    <div class="stat-label">Class Average</div>
                </div></div>
                <div class="col-4"><div class="stat-card text-center h-100">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value" style="color:var(--ss-success);">{{ $passRate }}%</div>
                    <div class="stat-label">Pass Rate</div>
                    <div class="pass-bar">
                        <div class="pass-bar-fill" style="width:{{ $passRate }}%;background:var(--ss-success);"></div>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MAIN SCORESHEET CARD ════════════════════════════════════════ --}}
    <div class="row"><div class="col-12"><div class="card border-0 shadow-sm">

        <div class="card-header d-flex align-items-center flex-wrap gap-2 py-3"
             style="background:var(--ss-primary);">
            <div class="flex-grow-1">
                <h5 class="mb-0 text-white fw-semibold">
                    <i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }}
                    @if($broadsheets->isNotEmpty())
                        <span class="badge bg-white text-primary ms-2" id="scoreCount">{{ $broadsheets->count() }}</span>
                    @endif
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="width:240px;">
                    <span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span>
                    <input type="text" class="form-control border-0 ps-1" id="searchInput"
                           placeholder="Search admission / name…"
                           {{ $broadsheets->isEmpty() ? 'disabled' : '' }}>
                    <button class="btn btn-light border-0" id="clearSearch"><i class="ri-close-line"></i></button>
                </div>
                @if($broadsheets->isNotEmpty())
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal">
                        <i class="ri-eye-line me-1"></i>Columns
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" id="downloadMarksSheet">
                        <i class="ri-file-pdf-line me-1"></i>Marks Sheet
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="downloadScoresPdf">
                        <i class="ri-file-pdf-2-line me-1"></i>Scores PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="downloadExcel">
                        <i class="ri-download-line me-1"></i>Export Excel
                    </button>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="ri-upload-line me-1"></i>Import
                    </button>
                @endif
                <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}"
                   class="btn btn-sm btn-outline-light">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0" id="scoresheetTable">
                <thead>
                    <tr>
                        <th class="col-checkbox" style="width:44px;">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="checkAll">
                            </div>
                        </th>
                        <th class="col-sn">SN</th>
                        <th class="col-admissionno">Adm. No</th>
                        <th class="col-name">Student Name</th>

                        @forelse($assessments as $assessment)
                            <th class="col-assessment-{{ $assessment->id }} text-center">
                                {{ $assessment->name }}<br>
                                <small class="fw-normal opacity-75">({{ $assessment->max_score }})</small>
                            </th>
                        @empty
                            <th colspan="4" class="col-no-assessments text-center text-white opacity-75">No Assessments Defined</th>
                        @endforelse

                        <th class="col-total text-center">Total</th>
                        <th class="col-total-grade text-center">Grade</th>
                        <th class="col-bf text-center">BF</th>
                        <th class="col-cum text-center">Cum</th>
                        <th class="col-cum-grade text-center">Cum Grade</th>
                        <th class="col-avg text-center">Class Avg</th>
                        <th class="col-position text-center">Position</th>
                        <th class="col-vetted text-center">Status</th>
                        <th class="col-lock-status text-center" style="width: 80px;">Lock</th>
                    </tr>
                </thead>
                <tbody id="scoresheetTableBody">
                    @php $i = 0; @endphp
                    @forelse($broadsheets as $broadsheet)
                        @php
                            $rowTotal = 0;
                            foreach ($assessments as $a) {
                                $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                                $rowTotal += $so ? $so->score : 0;
                            }
                            $isLocked = $broadsheet->is_locked || $globalLock || !$teacherEditingEnabled;
                            $vClass = match(true) {
                                $isLocked => 'row-locked',
                                $broadsheet->vettedstatus === '1' => 'row-vetted',
                                $broadsheet->vettedstatus === '0' => 'row-not-vetted',
                                default => 'row-pending',
                            };
                            $avatarUrl = $broadsheet->picture
                                ? asset('storage/student_avatars/'.basename($broadsheet->picture))
                                : asset('storage/student_avatars/unnamed.jpg');
                        @endphp
                        <tr class="{{ $vClass }}"
                            data-id="{{ $broadsheet->id }}"
                            data-bf="{{ $broadsheet->bf ?? 0 }}"
                            data-termid="{{ $termId }}"
                            data-schoolclassid="{{ $broadsheet->schoolclass_id ?? $schoolclass->id }}"
                            data-name="{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}"
                            data-admissionno="{{ $broadsheet->admissionno ?? '' }}"
                            data-avatar="{{ $avatarUrl }}"
                            data-is-locked="{{ $isLocked ? 'true' : 'false' }}">

                            <td class="col-checkbox">
                                <div class="form-check mb-0">
                                    <input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}" {{ $isLocked ? 'disabled' : '' }}>
                                </div>
                            </td>
                            <td class="col-sn fw-medium">{{ ++$i }}</td>
                            <td class="col-admissionno">{{ $broadsheet->admissionno ?? '-' }}</td>
                            <td class="col-name">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $avatarUrl }}"
                                         class="rounded-circle student-image"
                                         style="width:34px;height:34px;object-fit:cover;border:2px solid var(--ss-border);cursor:pointer;"
                                         data-bs-toggle="modal" data-bs-target="#imageViewModal"
                                         data-image="{{ $avatarUrl }}"
                                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                    <div>
                                        <span class="fw-semibold d-block" style="font-size:12.5px;">
                                            {{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            @forelse($assessments as $assessment)
                                @php
                                    $scoreObj   = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                    $scoreValue = $scoreObj ? $scoreObj->score : 0;
                                @endphp
                                <td class="col-assessment-{{ $assessment->id }} assessment-col text-center">
                                    <input type="number"
                                           class="score-input"
                                           data-field="{{ $assessment->id }}"
                                           data-max="{{ $assessment->max_score }}"
                                           data-id="{{ $broadsheet->id }}"
                                           data-original="{{ $scoreValue }}"
                                           data-assessment-name="{{ $assessment->name }}"
                                           value="{{ $scoreValue }}"
                                           min="0" max="{{ $assessment->max_score }}" step="0.1"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </td>
                            @empty
                                <td colspan="4" class="col-no-assessments text-center text-muted">-</td>
                            @endforelse

                            <td class="col-total text-center">
                                <span class="badge bg-secondary-subtle text-secondary fw-bold total-badge">
                                    {{ number_format($rowTotal, 1) }}
                                </span>
                            </td>
                            <td class="col-total-grade text-center">
                                <span class="grade-badge">{{ $broadsheet->grade ?? '-' }}</span>
                            </td>
                            <td class="col-bf text-center">
                                <span class="badge bg-secondary-subtle text-secondary bf-badge">
                                    {{ number_format($broadsheet->bf ?? 0, 1) }}
                                </span>
                            </td>
                            <td class="col-cum text-center">
                                <span class="badge bg-secondary-subtle text-secondary fw-bold cum-badge">
                                    {{ number_format($broadsheet->cum ?? 0, 1) }}
                                </span>
                            </td>
                            <td class="col-cum-grade text-center">
                                <span class="cum-grade-badge">{{ $broadsheet->grade ?? '-' }}</span>
                            </td>
                            <td class="col-avg text-center">
                                <span class="badge avg-badge" style="background:#f3e8ff;color:#7c3aed;">
                                    {{ number_format($broadsheet->avg ?? 0, 1) }}
                                </span>
                            </td>
                            <td class="col-position text-center">
                                <span class="badge position-badge" style="background:var(--ss-primary);">
                                    {{ $broadsheet->position ? $broadsheet->position . ($broadsheet->position == 1 ? 'st' : ($broadsheet->position == 2 ? 'nd' : ($broadsheet->position == 3 ? 'rd' : 'th'))) : '-' }}
                                </span>
                            </td>
                            <td class="col-vetted text-center">
                                @if($broadsheet->vettedstatus === '1')
                                    <span class="badge bg-success-subtle text-success">Vetted</span>
                                @elseif($broadsheet->vettedstatus === '0')
                                    <span class="badge bg-danger-subtle text-danger">Not Vetted</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                @endif
                            </td>
                            <td class="col-lock-status text-center">
                                @if($globalLock)
                                    <span class="badge bg-danger">Global Lock</span>
                                @elseif($broadsheet->is_locked)
                                    <span class="badge bg-warning">Locked</span>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary lock-individual-btn"
                                            data-id="{{ $broadsheet->id }}"
                                            style="padding: 2px 8px; font-size: 11px;">
                                        <i class="ri-lock-unlock-line"></i> Lock
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="noDataRow">
                            <td colspan="{{ ($assessments->count() ?: 4) + 13 }}" class="text-center py-4 text-muted">
                                No scores available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @if($broadsheets->isNotEmpty())
            <div class="p-3 border-top" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-primary" id="selectAllBtn" {{ (!$teacherEditingEnabled || $globalLock) ? 'disabled' : '' }}>
                            <i class="ri-check-double-line me-1"></i>Select All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="clearAllScoresBtn" {{ (!$teacherEditingEnabled || $globalLock) ? 'disabled' : '' }}>
                            <i class="ri-close-line me-1"></i>Clear All Scores
                        </button>
                        <button class="btn btn-sm btn-outline-warning" id="clearSelectedScoresBtn" {{ (!$teacherEditingEnabled || $globalLock) ? 'disabled' : '' }}>
                            <i class="ri-delete-bin-line me-1"></i>Clear Selected
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="deleteSelectedScoresBtn" {{ (!$teacherEditingEnabled || $globalLock) ? 'disabled' : '' }}>
                            <i class="ri-delete-bin-2-line me-1"></i>Delete Selected
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small>
                        <button class="btn btn-success btn-sm px-4" id="bulkUpdateScores" {{ (!$teacherEditingEnabled || $globalLock) ? 'disabled' : '' }}>
                            <i class="ri-save-line me-1"></i>Save All Scores
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div></div></div>

    {{-- ══ MODALS ══════════════════════════════════════════════════════ --}}
    @if($broadsheets->isNotEmpty())
    <div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="background:var(--ss-primary);">
                    <h5 class="modal-title text-white">Column Visibility</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="col-group">
                                <h6>Student Info</h6>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-col-checkbox" data-col="col-checkbox" checked><label>Select</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-col-sn" data-col="col-sn" checked><label>SN</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-col-admissionno" data-col="col-admissionno" checked><label>Adm. No</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-col-name" data-col="col-name" checked><label>Name</label></div>
                            </div>
                        </div>
                        @if($assessments->isNotEmpty())
                        <div class="col-md-4">
                            <div class="col-group">
                                <h6>Assessments</h6>
                                @foreach($assessments as $a)
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="chk-col-assessment-{{ $a->id }}" data-col="col-assessment-{{ $a->id }}" checked>
                                    <label>{{ $a->name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <div class="col-group">
                                <h6>Scores &amp; Metrics</h6>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-total" checked><label>Total</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-total-grade" checked><label>Grade</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-bf" checked><label>BF</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-cum" checked><label>Cum</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-cum-grade" checked><label>Cum Grade</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-avg" checked><label>Class Avg</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-position" checked><label>Position</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-vetted" checked><label>Status</label></div>
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-lock-status" checked><label>Lock</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="background:var(--ss-primary);">
                    <h5 class="modal-title text-white">Import Scores</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">Upload the Excel file exported from this scoresheet.</div>
                    <form method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <input type="hidden" name="schoolclass_id" value="{{ $schoolclass->id }}">
                        <input type="hidden" name="subjectclass_id" value="{{ $subjectclassId }}">
                        <input type="hidden" name="staff_id" value="{{ $teacherId }}">
                        <input type="hidden" name="term_id" value="{{ $termId }}">
                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Excel File (.xlsx)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header"><h5 class="modal-title">Student Photo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body text-center p-4">
                    <img id="enlargedImage" src="" alt="Student" class="img-fluid rounded-3" style="max-height:400px;">
                </div>
            </div>
        </div>
    </div>

</div></div></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// Helper functions
function showToast(msg, type = 'info') {
    const colors = { success:'#16a34a', warning:'#d97706', danger:'#dc2626', info:'#2563eb' };
    const id = 'toast_' + Date.now();
    document.body.insertAdjacentHTML('beforeend',
        `<div id="${id}" class="toast align-items-center border-0 text-white show" role="alert"
          style="position:fixed;bottom:20px;right:20px;z-index:99999;background:${colors[type]||colors.info};min-width:280px;border-radius:10px;">
          <div class="d-flex p-3"><div class="me-auto">${msg}</div>
          <button class="btn-close btn-close-white ms-2" onclick="this.closest('.toast').remove()"></button></div></div>`);
    setTimeout(() => document.getElementById(id)?.remove(), 4500);
}

function validateInput(inp) {
    const max = parseFloat(inp.dataset.max) || 0;
    const val = parseFloat(inp.value) || 0;
    inp.classList.toggle('is-invalid', val > max);
    return val <= max;
}

function updateRowGrades(row) {
    let totalRaw = 0;
    row.querySelectorAll('.score-input').forEach(inp => { totalRaw += parseFloat(inp.value) || 0; });
    const totalBadge = row.querySelector('.total-badge');
    if (totalBadge) totalBadge.textContent = totalRaw.toFixed(1);
}

function saveIndividualScore(input) {
    const row = input.closest('tr');
    fetch('{{ route("admin.score-entry.single-update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            broadsheet_id: input.dataset.id,
            assessment_id: parseInt(input.dataset.field),
            score: parseFloat(input.value) || 0,
            is_sub: false
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.classList.add('is-saved');
            setTimeout(() => input.classList.remove('is-saved'), 2000);
            if (data.data?.total) {
                const totalBadge = row.querySelector('.total-badge');
                if (totalBadge) totalBadge.textContent = parseFloat(data.data.total).toFixed(1);
            }
            if (data.data?.grade) {
                const gradeBadge = row.querySelector('.grade-badge');
                if (gradeBadge) gradeBadge.textContent = data.data.grade;
            }
        } else {
            showToast(data.message || 'Error saving score', 'danger');
        }
    })
    .catch(err => showToast('Network error', 'danger'));
}

// Clear ALL scores (all students, all assessments)
function clearAllScores() {
    const inputs = document.querySelectorAll('.score-input');
    if (inputs.length === 0) return;

    inputs.forEach(input => {
        if (!input.disabled) {
            input.value = '0';
            input.dispatchEvent(new Event('input'));
            const row = input.closest('tr');
            if (row) updateRowGrades(row);
        }
    });
    showToast('All scores cleared to 0', 'warning');
}

// Clear SELECTED scores (only checked rows)
function clearSelectedScores() {
    const selectedRows = document.querySelectorAll('.score-checkbox:checked');
    if (selectedRows.length === 0) {
        showToast('No rows selected', 'warning');
        return;
    }

    selectedRows.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row) {
            row.querySelectorAll('.score-input').forEach(input => {
                if (!input.disabled) {
                    input.value = '0';
                    input.dispatchEvent(new Event('input'));
                }
            });
            updateRowGrades(row);
        }
    });

    showToast(`Cleared scores for ${selectedRows.length} student(s)`, 'warning');
    // Uncheck all after clearing
    document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = false);
    const ca = document.getElementById('checkAll');
    if (ca) ca.checked = false;
}

// Bulk save all scores
function bulkSaveScores() {
    const scores = [];
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
        if (row.dataset.isLocked === 'true') return;
        const assessments = {};
        row.querySelectorAll('.score-input').forEach(inp => {
            assessments[inp.dataset.field] = parseFloat(inp.value) || 0;
        });
        if (Object.keys(assessments).length) {
            scores.push({ id: row.dataset.id, assessments });
        }
    });

    if (scores.length === 0) {
        showToast('No scores to save', 'warning');
        return;
    }

    const btn = document.getElementById('bulkUpdateScores');
    const origHtml = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Saving...'; }

    fetch('{{ route("admin.score-entry.bulk-update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            scores: scores,
            term_id: {{ $termId }},
            session_id: {{ $sessionId }},
            subjectclass_id: {{ $subjectclassId }},
            staff_id: {{ $teacherId }},
            schoolclass_id: {{ $schoolclass->id ?? 0 }},
            is_sub: false
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('All scores saved successfully', 'success');
            // Update displayed totals and grades from response
            if (data.data?.broadsheets) {
                data.data.broadsheets.forEach(bs => {
                    const row = document.querySelector(`tr[data-id="${bs.id}"]`);
                    if (row) {
                        const totalBadge = row.querySelector('.total-badge');
                        if (totalBadge) totalBadge.textContent = bs.total?.toFixed(1) || '0';
                        const gradeBadge = row.querySelector('.grade-badge');
                        if (gradeBadge) gradeBadge.textContent = bs.grade || '-';
                        const cumBadge = row.querySelector('.cum-badge');
                        if (cumBadge) cumBadge.textContent = bs.cum?.toFixed(1) || '0';
                    }
                });
            }
        } else {
            showToast(data.message || 'Error saving scores', 'danger');
        }
    })
    .catch(err => showToast('Network error', 'danger'))
    .finally(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
    });
}

// Delete selected scoresheets
function deleteSelectedScores() {
    const selectedIds = Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb => cb.dataset.id);
    if (selectedIds.length === 0) {
        showToast('No rows selected', 'warning');
        return;
    }

    if (!confirm(`Delete ${selectedIds.length} selected score record(s)? This cannot be undone.`)) return;

    let deleted = 0;
    selectedIds.forEach(id => {
        fetch('{{ route("admin.score-entry.destroy") }}', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ id: id, type: 'terminal' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`tr[data-id="${id}"]`)?.remove();
                deleted++;
                if (deleted === selectedIds.length) {
                    showToast(`${deleted} record(s) deleted`, 'success');
                    if (document.querySelectorAll('#scoresheetTableBody tr[data-id]').length === 0) {
                        location.reload();
                    }
                }
            }
        });
    });
}

// Lock individual scoresheet
function lockScoresheet(id, name) {
    const reason = prompt(`Enter reason for locking ${name}'s scoresheet:`, 'Locked by admin');
    fetch('{{ route("admin.score-entry.lock-scoresheet") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ broadsheet_id: id, reason: reason })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Scoresheet locked', 'success');
            location.reload();
        } else {
            showToast(data.message, 'danger');
        }
    });
}

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {

    // Image modal
    document.getElementById('imageViewModal')?.addEventListener('show.bs.modal', function(e) {
        const src = e.relatedTarget?.dataset?.image;
        document.getElementById('enlargedImage').src = src || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
    });

    // Column visibility
    document.querySelectorAll('.col-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            document.querySelectorAll(`th.${this.dataset.col}, td.${this.dataset.col}`)
                .forEach(el => el.style.display = this.checked ? '' : 'none');
        });
    });

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
            const adm = row.querySelector('.col-admissionno')?.textContent.toLowerCase() || '';
            const name = row.querySelector('.col-name')?.textContent.toLowerCase() || '';
            const match = searchTerm === '' || adm.includes(searchTerm) || name.includes(searchTerm);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        const countSpan = document.getElementById('scoreCount');
        if (countSpan) countSpan.textContent = visibleCount;
    });

    document.getElementById('clearSearch')?.addEventListener('click', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    // Select All checkbox
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Select All button
    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        const ca = document.getElementById('checkAll');
        if (ca) ca.checked = true;
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = true);
    });

    // CLEAR ALL SCORES button - resets ALL score inputs to 0
    document.getElementById('clearAllScoresBtn')?.addEventListener('click', function() {
        if (confirm('⚠️ WARNING: This will reset ALL scores to 0 for ALL students. This cannot be undone. Are you sure?')) {
            clearAllScores();
        }
    });

    // CLEAR SELECTED SCORES button - resets only selected rows
    document.getElementById('clearSelectedScoresBtn')?.addEventListener('click', function() {
        const selected = document.querySelectorAll('.score-checkbox:checked').length;
        if (selected === 0) {
            showToast('No rows selected', 'warning');
            return;
        }
        if (confirm(`Clear scores for ${selected} selected student(s)? This will reset their scores to 0.`)) {
            clearSelectedScores();
        }
    });

    // Delete selected button
    document.getElementById('deleteSelectedScoresBtn')?.addEventListener('click', deleteSelectedScores);

    // Bulk save button
    document.getElementById('bulkUpdateScores')?.addEventListener('click', bulkSaveScores);

    // Individual score inputs
    document.querySelectorAll('.score-input').forEach(inp => {
        inp.addEventListener('input', function() {
            validateInput(this);
            const row = this.closest('tr');
            if (row) updateRowGrades(row);
        });
        inp.addEventListener('blur', function() {
            if (!validateInput(this)) return;
            const orig = parseFloat(this.dataset.original) || 0;
            const curr = parseFloat(this.value) || 0;
            if (Math.abs(curr - orig) > 0.001) {
                this.dataset.original = this.value;
                saveIndividualScore(this);
            }
        });
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
            }
        });
    });

    // Keyboard shortcut Ctrl+S
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            bulkSaveScores();
        }
    });

    // Lock individual buttons
    document.querySelectorAll('.lock-individual-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const row = this.closest('tr');
            const name = row?.querySelector('.col-name')?.textContent.trim() || 'Student';
            lockScoresheet(id, name);
        });
    });

    // Download buttons
    document.getElementById('downloadExcel')?.addEventListener('click', function() {
        window.location.href = '{{ route("admin.score-entry.export") }}?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}';
    });

    document.getElementById('downloadMarksSheet')?.addEventListener('click', function() {
        window.location.href = '{{ route("admin.score-entry.download-marks-sheet") }}?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}';
    });

    document.getElementById('downloadScoresPdf')?.addEventListener('click', function() {
        window.location.href = '{{ route("admin.score-entry.download-scores-pdf") }}?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}';
    });

    // Import form
    document.getElementById('importForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const origHtml = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Uploading...'; }

        fetch('{{ route("admin.score-entry.import") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Import failed', 'danger');
            }
        })
        .catch(err => showToast('Network error', 'danger'))
        .finally(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
        });
    });

    // Staggered row entrance animation
    const rows = document.querySelectorAll('#scoresheetTableBody tr[data-id]');
    rows.forEach((row, index) => {
        setTimeout(() => row.classList.add('row-visible'), index * 30);
    });

});
</script>
@endsection
