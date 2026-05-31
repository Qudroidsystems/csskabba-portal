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

/* Grade badges */
.grade-badge, .cum-grade-badge {
    display: inline-block;
    transition: all .25s ease;
    font-weight: 700; font-size: 13px; min-width: 28px; text-align: center;
}
.grade-badge.updating, .cum-grade-badge.updating { opacity: 0.5; transform: scale(0.9); }
.grade-badge.updated,  .cum-grade-badge.updated  { animation: gradeFlash .4s ease; }
@keyframes gradeFlash {
    0%   { transform: scale(1.15); }
    50%  { transform: scale(1.2);  }
    100% { transform: scale(1);    }
}
.grade-loading {
    display: inline-block; width: 12px; height: 12px;
    border: 2px solid #e2e8f0; border-top-color: var(--ss-accent);
    border-radius: 50%; animation: spin .6s linear infinite; vertical-align: middle;
}

/* Position badges */
.position-badge, .position-total-badge, .arm-position-badge, .arm-position-cum-badge {
    transition: transform .22s cubic-bezier(0.34,1.4,0.64,1), opacity .15s ease;
}
.pos-flash { animation: posFlash .5s cubic-bezier(0.34,1.4,0.64,1); }
@keyframes posFlash {
    0%   { transform: scale(1);    opacity: 1; }
    30%  { transform: scale(1.25); opacity: .7; }
    60%  { transform: scale(0.95); opacity: 1; }
    100% { transform: scale(1);    opacity: 1; }
}

/* ROW ENTRANCE & HOVER */
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
#scoresheetTableBody tr.row-vetted:hover     { background: #e6faf0 !important; }
#scoresheetTableBody tr.row-not-vetted:hover { background: #fff0f0 !important; }
#scoresheetTableBody tr.row-pending:hover    { background: #fff8e6 !important; }
#scoresheetTableBody tr.row-locked:hover     { background: #fef2f2 !important; }
#scoresheetTableBody tr[data-id]:hover .student-image {
    transform: scale(1.12);
    transition: transform .22s cubic-bezier(.34,1.4,.64,1);
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.student-image { transition: transform .18s ease, box-shadow .18s ease; }
#scoresheetTableBody tr[data-id]:hover .score-input {
    border-color: #93c5fd; box-shadow: 0 1px 6px rgba(37,99,235,.10);
}
#scoresheetTableBody tr[data-id]:hover .badge {
    transition: transform .18s cubic-bezier(.34,1.4,.64,1);
    transform: scale(1.06);
}
#scoresheetTableBody tr[data-id] .score-checkbox {
    opacity: .35; transform: scale(.85);
    transition: opacity .18s ease, transform .18s cubic-bezier(.34,1.4,.64,1);
}
#scoresheetTableBody tr[data-id]:hover .score-checkbox,
#scoresheetTableBody tr[data-id] .score-checkbox:checked { opacity: 1; transform: scale(1); }

/* Lock Badge Styles */
.lock-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.lock-badge.global { background: #fee2e2; color: #dc2626; }
.lock-badge.individual { background: #fef3c7; color: #d97706; }
.lock-badge.disabled { background: #e5e7eb; color: #6b7280; }

/* Lock Status Banner */
.lock-alert {
    border-left: 4px solid #d97706;
    background: #fffbeb;
}
.lock-alert.global { border-left-color: #dc2626; background: #fef2f2; }
.lock-alert.disabled { border-left-color: #6b7280; background: #f3f4f6; }

/* GPA/CGPA Badges */
.gpa-badge, .cgpa-badge {
    font-size: 12px;
    font-weight: 600;
}

@media (prefers-reduced-motion: reduce) {
    #scoresheetTableBody tr[data-id],
    #scoresheetTableBody tr[data-id]:hover { transition: background .15s ease !important; transform: none !important; opacity: 1 !important; }
}

/* SCORE INPUT TOOLTIP */
#scoreTooltip {
    display: none; position: fixed; z-index: 99990;
    background: #fff; border: 0.5px solid #cbd5e1; border-radius: 10px;
    padding: 10px 13px; width: 230px;
    box-shadow: 0 4px 20px rgba(0,0,0,.10), 0 1px 4px rgba(0,0,0,.06);
    pointer-events: none; font-family: inherit; opacity: 0; transition: opacity .15s ease;
}
#scoreTooltip.tip-above { transform: translateY(-100%); }
#scoreTooltip.tip-below { transform: translateY(0); }
.tip-top { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 0.5px solid #e8ecf0; }
.tip-avatar   { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1.5px solid #e2e8f0; }
.tip-name     { font-size: 12px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tip-adm      { font-size: 10px; color: #64748b; margin-top: 1px; }
.tip-grid     { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; margin-bottom: 8px; }
.tip-stat     { text-align: center; }
.tip-stat-label { font-size: 9px; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; font-weight: 600; margin-bottom: 2px; }
.tip-stat-val   { font-size: 15px; font-weight: 700; font-variant-numeric: tabular-nums; line-height: 1; }
.tip-divider    { height: 0.5px; background: #e8ecf0; margin-bottom: 8px; }
.tip-prog-labels { display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; margin-bottom: 3px; }
.tip-prog-track  { height: 3px; background: #f1f5f9; border-radius: 2px; overflow: hidden; }
.tip-prog-fill   { height: 100%; border-radius: 2px; background: #2563eb; width: 0%; transition: width .3s ease, background .3s ease; }

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
#ssSaveOverlay.ss-closing  { animation: ssOverlayOut .22s ease forwards; }
@keyframes ssOverlayOut { from { opacity:1; } to { opacity:0; } }

/* Score Entry Modal */
.score-entry-modal .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
}
.score-entry-modal .modal-header {
    background: var(--ss-primary);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 20px 24px;
}
.score-entry-modal .modal-body {
    padding: 24px;
}
.score-entry-modal .student-avatar-large {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.score-entry-modal .assessment-score-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--ss-border);
}
.score-entry-modal .assessment-score-row:last-child {
    border-bottom: none;
}
.score-entry-modal .score-input-large {
    width: 100px;
    height: 40px;
    text-align: center;
    font-size: 16px;
    border: 1.5px solid var(--ss-border);
    border-radius: 8px;
}
.score-entry-modal .score-input-large:focus {
    outline: none;
    border-color: var(--ss-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.15);
}
.score-entry-modal .modal-footer {
    border-top: 1px solid var(--ss-border);
    padding: 16px 24px;
}

@media (max-width: 768px) {
    .score-input  { width: 64px; min-width: 64px; height: 42px; font-size: 1rem; }
    .stat-card    { padding: 10px 12px; }
    .stat-card .stat-value { font-size: 18px; }
    #ssSaveModal  { width: 280px; padding: 26px 24px 22px; }
    #scoreTooltip { width: calc(100vw - 24px); }
    .score-entry-modal .score-input-large { width: 80px; font-size: 14px; }
}
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

{{-- ══ SCORE INPUT TOOLTIP ═════════════════════════════════════════ --}}
<div id="scoreTooltip">
    <div class="tip-top">
        <img id="stAvatar" class="tip-avatar" src="" alt=""
             onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
        <div style="min-width:0;">
            <div class="tip-name" id="stName">—</div>
            <div class="tip-adm"  id="stMeta">—</div>
        </div>
    </div>
    <div class="tip-grid">
        <div class="tip-stat">
            <div class="tip-stat-label">Entering</div>
            <div class="tip-stat-val" id="stVal" style="color:#2563eb;">—</div>
        </div>
        <div class="tip-stat">
            <div class="tip-stat-label">Total</div>
            <div class="tip-stat-val" id="stTotal" style="color:#1e3a5f;">—</div>
        </div>
        <div class="tip-stat">
            <div class="tip-stat-label">Grade</div>
            <div class="tip-stat-val" id="stGrade" style="color:#6b7280;">—</div>
        </div>
    </div>
    <div class="tip-divider"></div>
    <div class="tip-prog-labels">
        <span id="stProgLabel">Score progress</span>
        <span id="stProgPct">0%</span>
    </div>
    <div class="tip-prog-track">
        <div class="tip-prog-fill" id="stProgFill"></div>
    </div>
</div>

{{-- ══ SCORE ENTRY MODAL ═══════════════════════════════════════════ --}}
<div class="modal fade score-entry-modal" id="scoreEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <img id="modalStudentAvatar" src="" alt="Student" class="student-avatar-large"
                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                    <div>
                        <h5 class="modal-title" id="modalStudentName">Student Name</h5>
                        <p class="mb-0 text-white-50" id="modalStudentAdmission">Admission No: -</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Current Scores</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="fw-bold fs-3" id="modalCurrentTotal">0.0</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold fs-3" id="modalCurrentGrade">-</div>
                                        <small class="text-muted">Grade</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Cumulative</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="fw-bold fs-3" id="modalCurrentCum">0.0</div>
                                        <small class="text-muted">Cumulative</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold fs-3" id="modalCurrentCumGrade">-</div>
                                        <small class="text-muted">Grade</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="fw-semibold mb-3"><i class="ri-edit-line me-2"></i>Assessment Scores</h6>
                <div id="modalAssessmentsList" class="border rounded-3 overflow-hidden">
                    <!-- Dynamic assessment inputs will appear here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveModalScores">
                    <i class="ri-save-line me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Admin Banner --}}
    <div class="admin-banner" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 4px solid #0284c7; border-radius: var(--ss-radius); padding: 14px 20px; margin-bottom: 20px; animation: slideIn 0.4s ease;">
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
    @if(isset($globalLock) || ($lockedCount ?? 0) > 0 || isset($teacherEditingEnabled) && !$teacherEditingEnabled)
    <div class="alert lock-alert {{ isset($globalLock) && $globalLock ? 'global' : (isset($teacherEditingEnabled) && !$teacherEditingEnabled ? 'disabled' : '') }} mb-3">
        <div class="d-flex align-items-center gap-3">
            <i class="ri-lock-line fs-3 {{ isset($globalLock) && $globalLock ? 'text-danger' : (isset($teacherEditingEnabled) && !$teacherEditingEnabled ? 'text-secondary' : 'text-warning') }}"></i>
            <div class="flex-grow-1">
                @if(isset($teacherEditingEnabled) && !$teacherEditingEnabled)
                    <strong><i class="ri-alert-line me-1"></i> Teacher Editing Disabled</strong><br>
                    <small>Teacher editing has been disabled for this subject by an administrator.</small>
                @elseif(isset($globalLock) && $globalLock)
                    <strong><i class="ri-global-line me-1"></i> Global Lock Active</strong><br>
                    <small>This entire scoresheet is locked. Reason: {{ $globalLock->reason ?? 'No reason provided' }}</small><br>
                    <small>Locked by: {{ optional($globalLock->lockedBy)->name }} on {{ $globalLock->locked_at->format('Y-m-d H:i:s') }}</small>
                @elseif(($lockedCount ?? 0) > 0)
                    <strong><i class="ri-lock-line me-1"></i> {{ $lockedCount }} of {{ $broadsheets->count() }} scoresheets are locked</strong>
                    <small>Locked records cannot be edited by teachers.</small>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
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

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0" style="color:var(--ss-primary)">
                        <i class="ri-bar-chart-2-line me-1"></i>Score Summary
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div class="row g-2">
                        <div class="col-6"><div class="p-2 rounded-3 text-center" style="background:#f0fdf4;">
                            <div class="fw-bold fs-5" style="color:var(--ss-success);">{{ $passed }}</div>
                            <div class="text-muted" style="font-size:11px;">Passed (Total)</div>
                        </div></div>
                        <div class="col-6"><div class="p-2 rounded-3 text-center" style="background:#fef2f2;">
                            <div class="fw-bold fs-5" style="color:var(--ss-danger);">{{ $failed }}</div>
                            <div class="text-muted" style="font-size:11px;">Failed (Total)</div>
                        </div></div>
                        <div class="col-6"><div class="p-2 rounded-3 text-center" style="background:#eff6ff;">
                            <div class="fw-bold fs-5" style="color:var(--ss-accent);">{{ $highest }}</div>
                            <div class="text-muted" style="font-size:11px;">Highest Total</div>
                        </div></div>
                        <div class="col-6"><div class="p-2 rounded-3 text-center" style="background:#fffbeb;">
                            <div class="fw-bold fs-5" style="color:var(--ss-warning);">{{ $lowest }}</div>
                            <div class="text-muted" style="font-size:11px;">Lowest Total</div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0" style="color:var(--ss-primary)">
                        <i class="ri-pie-chart-line me-1"></i>Grade Distribution (Total)
                    </h6>
                </div>
                <div class="card-body pt-2">
                    @if($gradeDist->isEmpty())
                        <p class="text-muted small text-center mt-3">No grades yet.</p>
                    @else
                        <div class="grade-strip">
                            @foreach($gradeDist->sortKeysDesc() as $grade => $count)
                                @php $pct = $total > 0 ? round($count/$total*100) : 0; $col = $gradeColors[$grade] ?? '#6b7280'; @endphp
                                <div class="grade-pill" style="background:{{ $col }}18;color:{{ $col }};border:1px solid {{ $col }}40;">
                                    <div style="font-size:16px;">{{ $grade }}</div>
                                    <div style="font-size:11px;font-weight:600;">{{ $count }} <span style="opacity:.7;">({{ $pct }}%)</span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0" style="color:var(--ss-primary)">
                        <i class="ri-clipboard-line me-1"></i>Assessments
                    </h6>
                </div>
                <div class="card-body pt-2">
                    @if($assessments->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($assessments as $assessment)
                                <div class="d-flex align-items-center justify-between p-2 rounded-3 assessment-btn"
                                     style="background:#eff6ff;border:1px solid #bfdbfe;color:var(--ss-accent);">
                                    <span><i class="ri-edit-line me-1"></i>{{ $assessment->name }}</span>
                                    <span class="badge" style="background:var(--ss-accent);">{{ $assessment->max_score }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center mt-3">
                            <i class="ri-information-line me-1"></i>No assessments defined.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ POSITION LEGEND + ADMIN CONTROLS ═══════════════════════════ --}}
    <div class="d-flex align-items-start justify-content-between gap-3 mb-2 flex-wrap"
         style="font-size:12px;color:var(--ss-muted);">
        <span>
            <i class="ri-information-line me-1 text-info"></i>
            <strong>Total Grade</strong> = grade on raw total &nbsp;|&nbsp;
            <strong>Cum Grade</strong> = grade on cumulative avg &nbsp;|&nbsp;
            <strong>Class Pos (Cum)</strong> = all arms, ranked by cumulative avg &nbsp;|&nbsp;
            <strong>Class Pos (Total)</strong> = all arms, ranked by raw total &nbsp;|&nbsp;
            <strong>Arm Pos (Total)</strong> = this arm only, ranked by raw total &nbsp;|&nbsp;
            <strong>Arm Pos (Cum)</strong> = this arm only, ranked by cumulative avg
        </span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" id="updateArmPositionsBtn">
                <i class="ri-refresh-line me-1"></i>Recalculate All Positions
            </button>
        </div>
    </div>

    {{-- Admin Controls & Lock Management Card --}}
    @if($broadsheets->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--ss-border);">
            <h6 class="mb-0 fw-semibold" style="color: var(--ss-primary);">
                <i class="ri-settings-4-line me-2"></i>Admin Controls & Lock Management
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <h6 class="mb-3"><i class="ri-lock-line me-1"></i> Lock Controls</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-warning" id="lockAllBtn">
                                <i class="ri-lock-line me-1"></i> Lock All (Individual)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="globalLockBtn">
                                <i class="ri-global-line me-1"></i> Global Lock
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" id="unlockAllBtn">
                                <i class="ri-lock-unlock-line me-1"></i> Unlock All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleTeacherEditBtn">
                                <i class="ri-user-settings-line me-1"></i>
                                {{ isset($teacherEditingEnabled) && $teacherEditingEnabled ? 'Disable' : 'Enable' }} Teacher Editing
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i>
                                <strong>Individual Lock:</strong> Locks each student record separately.<br>
                                <strong>Global Lock:</strong> Prevents ANY edits from teachers via a central lock.<br>
                                <strong>Toggle Teacher Editing:</strong> Completely disable/enable all teacher access.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <h6 class="mb-3"><i class="ri-history-line me-1"></i> Audit Summary</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: #f0fdf4;">
                                    <div class="small text-muted">Entered by Admin</div>
                                    <div class="fw-bold fs-5 text-success">
                                        {{ $broadsheets->where('entry_source', 'admin')->count() }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: #eff6ff;">
                                    <div class="small text-muted">Entered by Teacher</div>
                                    <div class="fw-bold fs-5 text-primary">
                                        {{ $broadsheets->where('entry_source', 'teacher')->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">
                            <i class="ri-time-line me-1"></i> Last activity:
                            {{ $broadsheets->max('last_modified_at') ? \Carbon\Carbon::parse($broadsheets->max('last_modified_at'))->diffForHumans() : 'Never' }}
                        </div>
                        <div class="mt-2 small">
                            <i class="ri-information-line me-1"></i>
                            <span class="text-muted">Teacher editing is currently
                                <strong class="{{ isset($teacherEditingEnabled) && $teacherEditingEnabled ? 'text-success' : 'text-danger' }}">
                                    {{ isset($teacherEditingEnabled) && $teacherEditingEnabled ? 'ENABLED' : 'DISABLED' }}
                                </strong>
                            </span>
                        </div>
                    </div>
                </div>
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
            <div class="alert alert-info text-center m-3 mb-0" id="noDataAlert"
                 style="display:{{ $broadsheets->isEmpty() ? 'block' : 'none' }};">
                <i class="ri-information-line me-2"></i>No scores available.
            </div>

            {{-- Download progress bar --}}
            <div id="downloadProgressContainer" style="display:none;" class="px-3 pt-3">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#fefce8;">
                    <div class="spinner-border spinner-border-sm text-warning"></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1" style="font-size:13px;" id="downloadProgressLabel">Downloading…</div>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar progress-bar-animated bg-warning" id="downloadProgressBar" style="width:0%"></div>
                        </div>
                    </div>
                </div>
            </div>

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
                        <th class="col-total-grade text-center" title="Grade on raw total (saved)">
                            Total<br><small class="fw-normal opacity-75">Grade</small>
                        </th>
                        <th class="col-bf text-center">BF</th>
                        <th class="col-cum text-center">Cum</th>
                        <th class="col-cum-grade text-center" title="Grade on cumulative average (display)">
                            Cum<br><small class="fw-normal opacity-75">Grade</small>
                        </th>
                        <th class="col-avg text-center" title="Subject class average">Class Avg</th>

                        {{-- GPA/CGPA Columns --}}
                        <th class="col-gpa text-center">GPA</th>
                        <th class="col-cgpa text-center">CGPA</th>

                        {{-- POSITION COLUMNS --}}
                        <th class="col-position text-center" title="All arms of this class combined, ranked by cumulative average">
                            Class Pos<br><small class="fw-normal opacity-75">(Cum)</small>
                        </th>
                        <th class="col-position-total text-center" title="All arms of this class combined, ranked by raw total">
                            Class Pos<br><small class="fw-normal opacity-75">(Total)</small>
                        </th>
                        <th class="col-arm-position text-center" title="This arm only, ranked by raw total">
                            Arm Pos<br><small class="fw-normal opacity-75">(Total)</small>
                        </th>
                        <th class="col-arm-position-cum text-center" title="This arm only, ranked by cumulative average">
                            Arm Pos<br><small class="fw-normal opacity-75">(Cum)</small>
                        </th>

                        <th class="col-vetted text-center">Status</th>
                        <th class="col-lock-status text-center" style="width: 100px;">
                            <i class="ri-lock-line"></i><br>
                            <small>Lock Status</small>
                        </th>
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
                            $cum = $broadsheet->cum ?? 0;
                            $totalGrade = $broadsheet->grade ?? '-';
                            $cumGrade = $broadsheet->grade ?? '-';

                            // Get grade colors
                            $totalGradeColor = $gradeColors[$totalGrade] ?? '#6b7280';
                            $cumGradeColor = $gradeColors[$cumGrade] ?? '#6b7280';

                            // Get color classes for totals and cum
                            $totalColor = $rowTotal >= 70 ? 'success' : ($rowTotal >= 50 ? 'info' : ($rowTotal >= 40 ? 'warning' : 'danger'));
                            $cumColor = $cum >= 70 ? 'success' : ($cum >= 50 ? 'info' : ($cum >= 40 ? 'warning' : 'danger'));

                            $isGloballyLocked = isset($globalLock) && $globalLock;
                            $isTeacherEditingDisabled = isset($teacherEditingEnabled) && !$teacherEditingEnabled;
                            $isLocked = $broadsheet->is_locked || $isGloballyLocked || $isTeacherEditingDisabled;
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
                                        @if($broadsheet->mname)
                                            <span class="text-muted small">{{ $broadsheet->mname }}</span>
                                        @endif
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
                                <span class="badge bg-{{ $totalColor }}-subtle text-{{ $totalColor }} fw-bold total-badge" style="font-size:12px;">
                                    {{ number_format($rowTotal, 1) }}
                                </span>
                            </td>
                            <td class="col-total-grade text-center">
                                <span class="grade-badge" style="color: {{ $totalGradeColor }};">{{ $totalGrade }}</span>
                            </td>
                            <td class="col-bf text-center">
                                <span class="badge bg-secondary-subtle text-secondary bf-badge">
                                    {{ number_format($broadsheet->bf ?? 0, 1) }}
                                </span>
                            </td>
                            <td class="col-cum text-center">
                                <span class="badge bg-{{ $cumColor }}-subtle text-{{ $cumColor }} fw-bold cum-badge" style="font-size:12px;">
                                    {{ number_format($cum, 1) }}
                                </span>
                            </td>
                            <td class="col-cum-grade text-center">
                                <span class="cum-grade-badge" style="color: {{ $cumGradeColor }};">{{ $cumGrade }}</span>
                            </td>
                            <td class="col-avg text-center">
                                <span class="badge avg-badge" style="background:#f3e8ff;color:#7c3aed;">
                                    {{ number_format($broadsheet->avg ?? 0, 1) }}
                                </span>
                            </td>

                            {{-- GPA/CGPA Cells --}}
                            <td class="col-gpa text-center">
                                <span class="badge bg-warning-subtle text-warning fw-semibold gpa-badge">
                                    {{ number_format($broadsheet->gpa ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="col-cgpa text-center">
                                <span class="badge bg-dark-subtle text-dark cgpa-badge">
                                    {{ number_format($broadsheet->cgpa ?? 0, 2) }}
                                </span>
                            </td>

                            {{-- POSITION CELLS --}}
                            <td class="col-position text-center">
                                <span class="badge position-badge" style="background:var(--ss-primary);">
                                    {{ $broadsheet->position ? $broadsheet->position . ($broadsheet->position == 1 ? 'st' : ($broadsheet->position == 2 ? 'nd' : ($broadsheet->position == 3 ? 'rd' : 'th'))) : '-' }}
                                </span>
                            </td>
                            <td class="col-position-total text-center">
                                <span class="badge position-total-badge" style="background:#0f766e;">
                                    {{ $broadsheet->position_total ? $broadsheet->position_total . ($broadsheet->position_total == 1 ? 'st' : ($broadsheet->position_total == 2 ? 'nd' : ($broadsheet->position_total == 3 ? 'rd' : 'th'))) : '-' }}
                                </span>
                            </td>
                            <td class="col-arm-position text-center">
                                <span class="badge arm-position-badge" style="background:#0891b2;">
                                    {{ $broadsheet->arm_position ? $broadsheet->arm_position . ($broadsheet->arm_position == 1 ? 'st' : ($broadsheet->arm_position == 2 ? 'nd' : ($broadsheet->arm_position == 3 ? 'rd' : 'th'))) : '-' }}
                                </span>
                            </td>
                            <td class="col-arm-position-cum text-center">
                                <span class="badge arm-position-cum-badge" style="background:#7c3aed;">
                                    {{ $broadsheet->arm_position_cum ? $broadsheet->arm_position_cum . ($broadsheet->arm_position_cum == 1 ? 'st' : ($broadsheet->arm_position_cum == 2 ? 'nd' : ($broadsheet->arm_position_cum == 3 ? 'rd' : 'th'))) : '-' }}
                                </span>
                            </td>

                            <td class="col-vetted text-center">
                                @if($broadsheet->vettedstatus === '1')
                                    <span class="badge bg-success-subtle text-success"><i class="ri-check-line me-1"></i>Vetted</span>
                                @elseif($broadsheet->vettedstatus === '0')
                                    <span class="badge bg-danger-subtle text-danger"><i class="ri-close-line me-1"></i>Not Vetted</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning"><i class="ri-time-line me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td class="col-lock-status text-center">
                                @if($isGloballyLocked)
                                    <span class="lock-badge global" title="{{ $globalLock->reason ?? 'Global lock active' }}">
                                        <i class="ri-global-line me-1"></i>Global Lock
                                    </span>
                                @elseif($broadsheet->is_locked)
                                    <span class="lock-badge individual" title="{{ $broadsheet->lock_reason ?? 'Locked by admin' }}">
                                        <i class="ri-lock-line me-1"></i>Locked
                                    </span>
                                @elseif($isTeacherEditingDisabled)
                                    <span class="lock-badge disabled" title="Teacher editing disabled">
                                        <i class="ri-user-settings-line me-1"></i>Read Only
                                    </span>
                                @else
                                    <button class="btn btn-sm btn-outline-primary edit-scores-btn"
                                            data-id="{{ $broadsheet->id }}"
                                            data-name="{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}"
                                            data-admission="{{ $broadsheet->admissionno ?? '' }}"
                                            data-avatar="{{ $avatarUrl }}"
                                            data-bf="{{ $broadsheet->bf ?? 0 }}"
                                            style="padding: 2px 8px; font-size: 11px;">
                                        <i class="ri-edit-line me-1"></i> Edit Scores
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="noDataRow">
                            <td colspan="{{ ($assessments->count() ?: 4) + 20 }}" class="text-center py-4 text-muted">
                                <i class="ri-inbox-line ri-2x d-block mb-2"></i>No scores available.
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
                        <button class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                            <i class="ri-check-double-line me-1"></i>Select All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="clearAllScoresBtn">
                            <i class="ri-close-line me-1"></i>Clear All Scores
                        </button>
                        <button class="btn btn-sm btn-outline-warning" id="clearSelectedScoresBtn">
                            <i class="ri-delete-bin-line me-1"></i>Clear Selected
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="deleteSelectedScoresBtn">
                            <i class="ri-delete-bin-2-line me-1"></i>Delete Selected
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small>
                        <button class="btn btn-success btn-sm px-4" id="bulkUpdateScores">
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
                    <h5 class="modal-title text-white"><i class="ri-eye-line me-2"></i>Column Visibility</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3"><div class="col-group">
                            <h6>Student Info</h6>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-checkbox" checked><label>Select</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-sn" checked><label>SN</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-admissionno" checked><label>Adm. No</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-name" checked><label>Name</label></div>
                        </div></div>
                        @if($assessments->isNotEmpty())
                        <div class="col-md-3"><div class="col-group">
                            <h6>Assessments</h6>
                            @foreach($assessments as $a)
                            <div class="form-check">
                                <input class="form-check-input col-toggle" type="checkbox" data-col="col-assessment-{{ $a->id }}" checked>
                                <label>{{ $a->name }}</label>
                            </div>
                            @endforeach
                        </div></div>
                        @endif
                        <div class="col-md-3"><div class="col-group">
                            <h6>Scores &amp; Metrics</h6>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-total" checked><label>Total</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-total-grade" checked><label>Total Grade</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-bf" checked><label>BF</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-cum" checked><label>Cum</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-cum-grade" checked><label>Cum Grade</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-avg" checked><label>Class Avg</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-gpa" checked><label>GPA</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-cgpa" checked><label>CGPA</label></div>
                        </div></div>
                        <div class="col-md-3"><div class="col-group">
                            <h6>Rankings &amp; Status</h6>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-position" checked><label>Class Pos (Cum)</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-position-total" checked><label>Class Pos (Total)</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-arm-position" checked><label>Arm Pos (Total)</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-arm-position-cum" checked><label>Arm Pos (Cum)</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-vetted" checked><label>Status</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-lock-status" checked><label>Lock</label></div>
                        </div></div>
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
                    <h5 class="modal-title text-white"><i class="ri-upload-line me-2"></i>Import Scores</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info"><i class="ri-information-line me-2"></i>Upload the Excel file exported from this scoresheet.</div>
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
                            <small class="text-muted">Only upload files exported from this system</small>
                        </div>
                        <div id="importLoader" style="display:none;" class="mb-3">
                            <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f0fdf4;">
                                <div class="spinner-border spinner-border-sm text-success"></div>
                                <div class="flex-grow-1">
                                    <div style="font-size:12px;margin-bottom:3px;">Uploading...</div>
                                    <div class="progress" style="height:5px;">
                                        <div class="progress-bar progress-bar-animated bg-success" id="uploadProgressBar" style="width:0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="importSubmit">
                                <i class="ri-upload-line me-1"></i>Upload
                            </button>
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
// CSRF Token
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// Routes
const routes = {
    singleUpdate: '{{ route("admin.score-entry.single-update") }}',
    bulkUpdate: '{{ route("admin.score-entry.bulk-update") }}',
    destroy: '{{ route("admin.score-entry.destroy") }}',
    export: '{{ route("admin.score-entry.export") }}',
    import: '{{ route("admin.score-entry.import") }}',
    downloadMarksSheet: '{{ route("admin.score-entry.download-marks-sheet") }}',
    downloadScoresPdf: '{{ route("admin.score-entry.download-scores-pdf") }}',
    updateArmPositions: '{{ route("admin.score-entry.update-arm-positions") }}',
    lockScoresheet: '{{ route("admin.score-entry.lock-scoresheet") }}',
    unlockScoresheet: '{{ route("admin.score-entry.unlock-scoresheet") }}',
    lockBatch: '{{ route("admin.score-entry.lock-batch") }}',
    unlockBatch: '{{ route("admin.score-entry.unlock-batch") }}',
    disableTeacherEditing: '{{ route("admin.score-entry.disable-teacher-editing") }}',
    enableTeacherEditing: '{{ route("admin.score-entry.enable-teacher-editing") }}',
};

// Grade colors mapping
const GRADE_COLORS = {
    'A': '#16a34a', 'A1': '#16a34a',
    'B': '#2563eb', 'B2': '#2563eb', 'B3': '#3b82f6',
    'C': '#7c3aed', 'C4': '#7c3aed', 'C5': '#8b5cf6', 'C6': '#a78bfa',
    'D': '#d97706', 'D7': '#d97706', 'E8': '#f59e0b',
    'F': '#dc2626', 'F9': '#dc2626',
};

// Helper function to get grade color
function getGradeColor(grade) {
    return GRADE_COLORS[grade] || '#6b7280';
}

// Apply grade with color
function applyGrade(badge, grade) {
    if (!badge) return;
    badge.textContent = grade || '-';
    badge.style.color = getGradeColor(grade);
    badge.classList.remove('updating');
    badge.classList.add('updated');
    setTimeout(() => badge.classList.remove('updated'), 500);
}

// Client-side grade calculation (for preview)
function clientGrade(score) {
    score = parseFloat(score) || 0;
    if (score >= 70) return 'A';
    if (score >= 60) return 'B';
    if (score >= 50) return 'C';
    if (score >= 40) return 'D';
    return 'F';
}

// Toast notification
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

// Validate input
function validateInput(inp) {
    const max = parseFloat(inp.dataset.max) || 0;
    const val = parseFloat(inp.value) || 0;
    inp.classList.toggle('is-invalid', val > max);
    return val <= max;
}

// Update row grades with colors
function updateRowGrades(row) {
    let totalRaw = 0;
    row.querySelectorAll('.score-input').forEach(inp => {
        totalRaw += parseFloat(inp.value) || 0;
    });

    const cum = parseFloat(row.dataset.bf) || 0;
    const finalCum = cum > 0 ? (totalRaw + cum) / 2 : totalRaw;

    // Update total badge with color
    const totalBadge = row.querySelector('.total-badge');
    if (totalBadge) {
        totalBadge.textContent = totalRaw.toFixed(1);
        const totalColor = totalRaw >= 70 ? 'success' : (totalRaw >= 50 ? 'info' : (totalRaw >= 40 ? 'warning' : 'danger'));
        totalBadge.className = `badge fw-bold total-badge bg-${totalColor}-subtle text-${totalColor}`;
        totalBadge.style.fontSize = '12px';
    }

    // Update cum badge with color
    const cumBadge = row.querySelector('.cum-badge');
    if (cumBadge) {
        cumBadge.textContent = finalCum.toFixed(1);
        const cumColor = finalCum >= 70 ? 'success' : (finalCum >= 50 ? 'info' : (finalCum >= 40 ? 'warning' : 'danger'));
        cumBadge.className = `badge fw-bold cum-badge bg-${cumColor}-subtle text-${cumColor}`;
        cumBadge.style.fontSize = '12px';
    }

    // Update grade badges with colors
    const totalGrade = clientGrade(totalRaw);
    const cumGrade = clientGrade(finalCum);

    applyGrade(row.querySelector('.grade-badge'), totalGrade);
    applyGrade(row.querySelector('.cum-grade-badge'), cumGrade);
}

// Save individual score
function saveIndividualScore(input) {
    const row = input.closest('tr');
    const originalValue = parseFloat(input.dataset.original) || 0;
    const newValue = parseFloat(input.value) || 0;

    if (Math.abs(newValue - originalValue) < 0.01) return;

    fetch(routes.singleUpdate, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            broadsheet_id: input.dataset.id,
            assessment_id: parseInt(input.dataset.field),
            score: newValue,
            is_sub: false,
            term_id: {{ $termId }},
            session_id: {{ $sessionId }},
            subjectclass_id: {{ $subjectclassId }},
            schoolclass_id: {{ $schoolclass->id ?? 0 }},
            staff_id: {{ $teacherId }}
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.classList.add('is-saved');
            setTimeout(() => input.classList.remove('is-saved'), 2000);
            input.dataset.original = input.value;

            if (data.data?.total) {
                const totalBadge = row.querySelector('.total-badge');
                if (totalBadge) {
                    totalBadge.textContent = parseFloat(data.data.total).toFixed(1);
                    const totalColor = parseFloat(data.data.total) >= 70 ? 'success' : (parseFloat(data.data.total) >= 50 ? 'info' : (parseFloat(data.data.total) >= 40 ? 'warning' : 'danger'));
                    totalBadge.className = `badge fw-bold total-badge bg-${totalColor}-subtle text-${totalColor}`;
                }
            }
            if (data.data?.grade) {
                applyGrade(row.querySelector('.grade-badge'), data.data.grade);
            }
            if (data.data?.cum) {
                const cumBadge = row.querySelector('.cum-badge');
                if (cumBadge) {
                    cumBadge.textContent = parseFloat(data.data.cum).toFixed(1);
                    const cumColor = parseFloat(data.data.cum) >= 70 ? 'success' : (parseFloat(data.data.cum) >= 50 ? 'info' : (parseFloat(data.data.cum) >= 40 ? 'warning' : 'danger'));
                    cumBadge.className = `badge fw-bold cum-badge bg-${cumColor}-subtle text-${cumColor}`;
                }
                applyGrade(row.querySelector('.cum-grade-badge'), clientGrade(parseFloat(data.data.cum)));
            }
            if (data.data?.gpa) {
                const gpaBadge = row.querySelector('.gpa-badge');
                if (gpaBadge) gpaBadge.textContent = parseFloat(data.data.gpa).toFixed(2);
            }
            if (data.data?.cgpa) {
                const cgpaBadge = row.querySelector('.cgpa-badge');
                if (cgpaBadge) cgpaBadge.textContent = parseFloat(data.data.cgpa).toFixed(2);
            }
        } else {
            showToast(data.message || 'Error saving score', 'danger');
            input.value = originalValue;
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Network error', 'danger');
        input.value = originalValue;
    });
}

// Clear ALL scores with SweetAlert
function clearAllScores() {
    Swal.fire({
        title: 'Clear All Scores?',
        text: 'This will reset ALL scores to 0 for ALL students. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, clear all',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelectorAll('.score-input:not(:disabled)').forEach(input => {
                input.value = '0';
                input.dispatchEvent(new Event('input'));
                const row = input.closest('tr');
                if (row) updateRowGrades(row);
            });
            showToast('All scores cleared to 0', 'success');
        }
    });
}

// Clear SELECTED scores with SweetAlert
function clearSelectedScores() {
    const selectedRows = document.querySelectorAll('.score-checkbox:checked');
    if (selectedRows.length === 0) {
        showToast('No rows selected', 'warning');
        return;
    }

    Swal.fire({
        title: `Clear scores for ${selectedRows.length} student(s)?`,
        text: 'This will reset all scores to 0 for selected students. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, clear selected',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedRows.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    row.querySelectorAll('.score-input:not(:disabled)').forEach(input => {
                        input.value = '0';
                        input.dispatchEvent(new Event('input'));
                    });
                    updateRowGrades(row);
                }
            });
            showToast(`Cleared scores for ${selectedRows.length} student(s)`, 'success');
            document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = false);
            const ca = document.getElementById('checkAll');
            if (ca) ca.checked = false;
        }
    });
}

// Apple-style save modal functions
const SS_ARC_CIRC = 157.08;
let ssCloseTimeout = null;

function ssOpen(total) {
    const overlay = document.getElementById('ssSaveOverlay');
    if (!overlay) return;

    document.getElementById('ssIconSave').style.display = '';
    document.getElementById('ssIconCheck').style.display = 'none';
    document.getElementById('ssIconX').style.display = 'none';
    document.getElementById('ssCheckPath')?.classList.remove('drawn');
    document.getElementById('ssIconCenter').style.background = 'rgba(30,58,95,0.09)';
    document.getElementById('ssArcFg').style.stroke = '#1e3a5f';
    document.getElementById('ssArcFg').style.strokeDashoffset = SS_ARC_CIRC;
    document.getElementById('ssSaveFill').style.width = '0%';
    document.getElementById('ssSaveTitle').textContent = 'Saving scores';
    document.getElementById('ssSaveSub').textContent = 'Preparing…';
    document.getElementById('ssSaveCountNum').textContent = `0 / ${total}`;

    overlay.classList.remove('ss-closing');
    overlay.classList.add('ss-visible');
}

function ssUpdate(saved, total, pct) {
    const fill = document.getElementById('ssSaveFill');
    const arc = document.getElementById('ssArcFg');
    if (fill) fill.style.width = pct.toFixed(1) + '%';
    if (arc) arc.style.strokeDashoffset = (SS_ARC_CIRC * (1 - pct / 100)).toFixed(3);
    document.getElementById('ssSaveCountNum').textContent = `${saved} / ${total}`;

    if (pct < 25) document.getElementById('ssSaveSub').textContent = 'Uploading data…';
    else if (pct < 55) document.getElementById('ssSaveSub').textContent = 'Processing records…';
    else if (pct < 85) document.getElementById('ssSaveSub').textContent = 'Recalculating grades…';
    else document.getElementById('ssSaveSub').textContent = 'Finalising…';
}

function ssSuccess(total) {
    document.getElementById('ssSaveFill').style.width = '100%';
    document.getElementById('ssSaveFill').style.background = '#16a34a';
    document.getElementById('ssArcFg').style.strokeDashoffset = '0';
    document.getElementById('ssArcFg').style.stroke = '#16a34a';
    document.getElementById('ssIconCenter').style.background = '#dcfce7';
    document.getElementById('ssIconSave').style.display = 'none';
    document.getElementById('ssIconCheck').style.display = '';
    setTimeout(() => document.getElementById('ssCheckPath')?.classList.add('drawn'), 10);
    document.getElementById('ssSaveTitle').textContent = 'All saved';
    document.getElementById('ssSaveSub').textContent = `${total} score(s) saved successfully`;
    document.getElementById('ssSaveCountNum').textContent = `${total} / ${total}`;

    if (ssCloseTimeout) clearTimeout(ssCloseTimeout);
    ssCloseTimeout = setTimeout(ssClose, 1900);
}

function ssError(msg) {
    document.getElementById('ssSaveFill').style.background = '#dc2626';
    document.getElementById('ssArcFg').style.stroke = '#dc2626';
    document.getElementById('ssIconCenter').style.background = '#fee2e2';
    document.getElementById('ssIconSave').style.display = 'none';
    document.getElementById('ssIconX').style.display = '';
    document.getElementById('ssSaveTitle').textContent = 'Save failed';
    document.getElementById('ssSaveSub').textContent = msg || 'Something went wrong.';

    if (ssCloseTimeout) clearTimeout(ssCloseTimeout);
    ssCloseTimeout = setTimeout(ssClose, 2400);
}

function ssClose() {
    const overlay = document.getElementById('ssSaveOverlay');
    if (!overlay) return;
    overlay.classList.add('ss-closing');
    setTimeout(() => overlay.classList.remove('ss-visible', 'ss-closing'), 260);
}

// Bulk save with modal
function bulkSaveScores() {
    const invalid = document.querySelectorAll('.score-input.is-invalid').length;
    if (invalid) {
        Swal.fire({ icon:'warning', title:'Invalid Scores', text:`${invalid} score(s) exceed their maximum.` });
        return;
    }

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

    const total = scores.length;
    ssOpen(total);

    let fakeProgress = 0;
    const fakeIv = setInterval(() => {
        fakeProgress = Math.min(fakeProgress + Math.random() * 4 + 2, 88);
        ssUpdate(Math.round((fakeProgress / 100) * total), total, fakeProgress);
    }, 130);

    const btn = document.getElementById('bulkUpdateScores');
    const origHtml = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Saving...'; }

    fetch(routes.bulkUpdate, {
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
        clearInterval(fakeIv);
        if (!data.success) {
            ssError(data.message || 'Server error.');
            return;
        }

        ssUpdate(total, total, 100);
        setTimeout(() => ssSuccess(total), 220);

        if (data.data?.broadsheets) {
            data.data.broadsheets.forEach(bs => {
                const row = document.querySelector(`tr[data-id="${bs.id}"]`);
                if (row) {
                    // Update total badge
                    const totalBadge = row.querySelector('.total-badge');
                    if (totalBadge) {
                        totalBadge.textContent = bs.total?.toFixed(1) || '0';
                        const totalColor = (bs.total || 0) >= 70 ? 'success' : ((bs.total || 0) >= 50 ? 'info' : ((bs.total || 0) >= 40 ? 'warning' : 'danger'));
                        totalBadge.className = `badge fw-bold total-badge bg-${totalColor}-subtle text-${totalColor}`;
                    }

                    // Update grade badge
                    const gradeBadge = row.querySelector('.grade-badge');
                    if (gradeBadge) applyGrade(gradeBadge, bs.grade || '-');

                    // Update cum badge
                    const cumBadge = row.querySelector('.cum-badge');
                    if (cumBadge && bs.cum !== undefined) {
                        cumBadge.textContent = parseFloat(bs.cum).toFixed(1);
                        const cumColor = parseFloat(bs.cum) >= 70 ? 'success' : (parseFloat(bs.cum) >= 50 ? 'info' : (parseFloat(bs.cum) >= 40 ? 'warning' : 'danger'));
                        cumBadge.className = `badge fw-bold cum-badge bg-${cumColor}-subtle text-${cumColor}`;
                    }

                    // Update cum grade badge
                    const cumGradeBadge = row.querySelector('.cum-grade-badge');
                    if (cumGradeBadge && bs.cum !== undefined) {
                        applyGrade(cumGradeBadge, clientGrade(parseFloat(bs.cum)));
                    }

                    // Update GPA/CGPA
                    const gpaBadge = row.querySelector('.gpa-badge');
                    const cgpaBadge = row.querySelector('.cgpa-badge');
                    if (gpaBadge && bs.gpa !== undefined) gpaBadge.textContent = parseFloat(bs.gpa).toFixed(2);
                    if (cgpaBadge && bs.cgpa !== undefined) cgpaBadge.textContent = parseFloat(bs.cgpa).toFixed(2);

                    row.querySelectorAll('.score-input').forEach(inp => {
                        const assessmentId = inp.dataset.field;
                        const newScore = bs.assessment_scores?.find(a => a.assessment_id == assessmentId)?.score;
                        if (newScore !== undefined) {
                            inp.dataset.original = newScore;
                            inp.classList.add('is-saved');
                            setTimeout(() => inp.classList.remove('is-saved'), 2000);
                        }
                    });
                }
            });
        }

        refreshAllPositions();
    })
    .catch(err => {
        clearInterval(fakeIv);
        ssError('Please check your connection and try again.');
        console.error(err);
    })
    .finally(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = origHtml || '<i class="ri-save-line me-1"></i>Save All Scores'; }
    });
}

// Delete selected scores
function deleteSelectedScores() {
    const selectedIds = Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb => cb.dataset.id);
    if (selectedIds.length === 0) {
        showToast('No rows selected', 'warning');
        return;
    }

    Swal.fire({
        title: `Delete ${selectedIds.length} score record(s)?`,
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            let deleted = 0;
            selectedIds.forEach(id => {
                fetch(routes.destroy, {
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
    });
}

// Refresh positions
let positionRefreshTimer = null;

function refreshAllPositions() {
    clearTimeout(positionRefreshTimer);
    positionRefreshTimer = setTimeout(() => {
        fetch(routes.updateArmPositions, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                schoolclass_id: {{ $schoolclass->id ?? 0 }},
                term_id: {{ $termId }},
                session_id: {{ $sessionId }}
            })
        }).catch(() => {});
    }, 500);
}

// Open Score Entry Modal
function openScoreEntryModal(broadsheetId, studentName, studentAdmission, studentAvatar, currentScores, assessments, bf) {
    console.log('Opening modal for:', studentName);

    const modal = new bootstrap.Modal(document.getElementById('scoreEntryModal'));
    const modalBody = document.getElementById('modalAssessmentsList');

    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalStudentAdmission').textContent = `Admission No: ${studentAdmission}`;
    document.getElementById('modalStudentAvatar').src = studentAvatar;

    // Calculate current total
    let currentTotal = 0;
    const scoresMap = {};
    assessments.forEach(a => {
        const score = currentScores[a.id] || 0;
        scoresMap[a.id] = score;
        currentTotal += score;
    });

    const grade = currentTotal >= 70 ? 'A' : (currentTotal >= 60 ? 'B' : (currentTotal >= 50 ? 'C' : (currentTotal >= 40 ? 'D' : 'F')));
    const cum = parseFloat(bf) || 0;
    const cumGrade = cum >= 70 ? 'A' : (cum >= 60 ? 'B' : (cum >= 50 ? 'C' : (cum >= 40 ? 'D' : 'F')));

    document.getElementById('modalCurrentTotal').textContent = currentTotal.toFixed(1);
    document.getElementById('modalCurrentGrade').textContent = grade;
    document.getElementById('modalCurrentCum').textContent = cum.toFixed(1);
    document.getElementById('modalCurrentCumGrade').textContent = cumGrade;

    // Build assessment inputs
    let html = '';
    assessments.forEach(a => {
        const scoreValue = scoresMap[a.id] || 0;
        html += `
            <div class="assessment-score-row">
                <div>
                    <strong>${a.name}</strong>
                    <small class="text-muted ms-2">(Max: ${a.max_score})</small>
                </div>
                <input type="number"
                       class="form-control score-input-large"
                       data-assessment-id="${a.id}"
                       data-max="${a.max_score}"
                       value="${scoreValue}"
                       min="0" max="${a.max_score}" step="0.1"
                       style="width: 100px;">
            </div>
        `;
    });
    modalBody.innerHTML = html;

    // Store broadsheet ID for save
    modalBody.dataset.broadsheetId = broadsheetId;

    modal.show();
}

// Save scores from modal
function saveModalScores() {
    const modalBody = document.getElementById('modalAssessmentsList');
    const broadsheetId = modalBody.dataset.broadsheetId;
    const assessmentInputs = modalBody.querySelectorAll('.score-input-large');

    let hasError = false;
    const scores = {};

    assessmentInputs.forEach(input => {
        const assessmentId = input.dataset.assessmentId;
        const maxScore = parseFloat(input.dataset.max);
        let value = parseFloat(input.value) || 0;

        if (value > maxScore) {
            Swal.fire({ icon: 'error', title: 'Invalid Score', text: `Score cannot exceed ${maxScore}` });
            hasError = true;
            return;
        }

        scores[assessmentId] = value;
    });

    if (hasError) return;

    Swal.fire({
        title: 'Saving Scores...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const savePromises = [];
    for (const [assessmentId, score] of Object.entries(scores)) {
        savePromises.push(fetch(routes.singleUpdate, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                broadsheet_id: broadsheetId,
                assessment_id: parseInt(assessmentId),
                score: score,
                is_sub: false,
                term_id: {{ $termId }},
                session_id: {{ $sessionId }},
                subjectclass_id: {{ $subjectclassId }},
                schoolclass_id: {{ $schoolclass->id ?? 0 }},
                staff_id: {{ $teacherId }}
            })
        }));
    }

    Promise.all(savePromises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(() => {
            Swal.close();
            bootstrap.Modal.getInstance(document.getElementById('scoreEntryModal'))?.hide();
            showToast('Scores saved successfully', 'success');

            // Update the row in the table
            const row = document.querySelector(`tr[data-id="${broadsheetId}"]`);
            if (row) {
                let newTotal = 0;
                for (const [assessmentId, score] of Object.entries(scores)) {
                    const input = row.querySelector(`.score-input[data-field="${assessmentId}"]`);
                    if (input) {
                        input.value = score;
                        input.dataset.original = score;
                        newTotal += score;
                    }
                }
                updateRowGrades(row);
            }

            refreshAllPositions();
        })
        .catch(err => {
            Swal.close();
            showToast('Error saving scores', 'danger');
            console.error(err);
        });
}

// Lock management functions
function lockAllScoresheets() {
    Swal.fire({
        title: 'Lock all scoresheets?',
        text: 'This will lock every student record in this subject individually.',
        input: 'textarea',
        inputLabel: 'Reason for locking (optional)',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, lock all',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(routes.lockBatch, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    subjectclass_ids: [{{ $subjectclassId }}],
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    lock_type: 'individual',
                    reason: result.value
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }
    });
}

function globalLock() {
    Swal.fire({
        title: 'Apply Global Lock?',
        html: 'This will prevent <strong>ALL teacher edits</strong> to this subject.',
        input: 'textarea',
        inputLabel: 'Reason for global lock',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Apply Global Lock',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(routes.lockBatch, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    subjectclass_ids: [{{ $subjectclassId }}],
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    lock_type: 'global',
                    reason: result.value
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }
    });
}

function unlockAllScoresheets() {
    Swal.fire({
        title: 'Unlock all scoresheets?',
        text: 'This will unlock all scoresheets in this subject.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        confirmButtonText: 'Yes, unlock all',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(routes.unlockBatch, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    subjectclass_ids: [{{ $subjectclassId }}],
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    unlock_type: 'individual'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }
    });
}

function toggleTeacherEditing() {
    const isEnabled = {{ isset($teacherEditingEnabled) && $teacherEditingEnabled ? 'true' : 'false' }};
    const url = isEnabled ? routes.disableTeacherEditing : routes.enableTeacherEditing;
    const action = isEnabled ? 'disable' : 'enable';

    Swal.fire({
        title: isEnabled ? 'Disable Teacher Editing?' : 'Enable Teacher Editing?',
        html: isEnabled
            ? 'Teachers will <strong>NOT be able to edit</strong> any scores for this subject.'
            : 'Teachers will regain the ability to edit scores for this subject.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isEnabled ? '#dc2626' : '#16a34a',
        confirmButtonText: isEnabled ? 'Disable' : 'Enable',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ subjectclass_ids: [{{ $subjectclassId }}] })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }
    });
}

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize grade colors on existing rows
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
        updateRowGrades(row);
    });

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

    // Search
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

    // Select All
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        const ca = document.getElementById('checkAll');
        if (ca) ca.checked = true;
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = true);
    });

    // Clear buttons with SweetAlert
    document.getElementById('clearAllScoresBtn')?.addEventListener('click', clearAllScores);
    document.getElementById('clearSelectedScoresBtn')?.addEventListener('click', clearSelectedScores);
    document.getElementById('deleteSelectedScoresBtn')?.addEventListener('click', deleteSelectedScores);
    document.getElementById('bulkUpdateScores')?.addEventListener('click', bulkSaveScores);

    // Lock management buttons
    document.getElementById('lockAllBtn')?.addEventListener('click', lockAllScoresheets);
    document.getElementById('globalLockBtn')?.addEventListener('click', globalLock);
    document.getElementById('unlockAllBtn')?.addEventListener('click', unlockAllScoresheets);
    document.getElementById('toggleTeacherEditBtn')?.addEventListener('click', toggleTeacherEditing);

    // ASSESSMENTS DATA FOR MODAL
    const assessmentsData = @json($assessments->map(function($a) {
        return ['id' => $a->id, 'name' => $a->name, 'max_score' => $a->max_score];
    }));

    // EDIT BUTTON HANDLER - Using event delegation
    document.getElementById('scoresheetTableBody')?.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-scores-btn');
        if (!editBtn) return;

        e.preventDefault();
        e.stopPropagation();

        const broadsheetId = editBtn.dataset.id;
        const studentName = editBtn.dataset.name;
        const studentAdmission = editBtn.dataset.admission;
        const studentAvatar = editBtn.dataset.avatar;
        const bf = editBtn.dataset.bf;

        // Get current scores from the row
        const row = document.querySelector(`tr[data-id="${broadsheetId}"]`);
        const currentScores = {};
        if (row) {
            row.querySelectorAll('.score-input').forEach(input => {
                const assessmentId = input.dataset.field;
                currentScores[assessmentId] = parseFloat(input.value) || 0;
            });
        }

        // Open modal
        openScoreEntryModal(broadsheetId, studentName, studentAdmission, studentAvatar, currentScores, assessmentsData, bf);
    });

    // Save modal scores button
    document.getElementById('saveModalScores')?.addEventListener('click', saveModalScores);

    // Individual score inputs
    document.querySelectorAll('.score-input').forEach(inp => {
        inp.addEventListener('input', function() {
            validateInput(this);
            const row = this.closest('tr');
            if (row) updateRowGrades(row);
        });
        inp.addEventListener('blur', function() {
            if (!validateInput(this)) {
                this.value = this.dataset.original || 0;
                return;
            }
            saveIndividualScore(this);
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

    // Download buttons
    document.getElementById('downloadExcel')?.addEventListener('click', function() {
        window.location.href = routes.export + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}`;
    });

    document.getElementById('downloadMarksSheet')?.addEventListener('click', function() {
        window.location.href = routes.downloadMarksSheet + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}`;
    });

    document.getElementById('downloadScoresPdf')?.addEventListener('click', function() {
        window.location.href = routes.downloadScoresPdf + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}`;
    });

    // Import form
    document.getElementById('importForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = document.getElementById('importSubmit');
        const loader = document.getElementById('importLoader');
        const bar = document.getElementById('uploadProgressBar');
        const origHtml = btn?.innerHTML;

        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Uploading...'; }
        if (loader) loader.style.display = 'block';
        if (bar) bar.style.width = '10%';

        fetch(routes.import, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (bar) bar.style.width = '100%';
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000, showConfirmButton: false });
                setTimeout(() => location.reload(), 2000);
            } else {
                Swal.fire({ icon: 'error', title: 'Import Failed', text: data.message });
            }
        })
        .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' }))
        .finally(() => {
            setTimeout(() => {
                if (loader) loader.style.display = 'none';
                if (bar) bar.style.width = '0%';
            }, 1000);
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
        });
    });

    // Update arm positions
    document.getElementById('updateArmPositionsBtn')?.addEventListener('click', function() {
        const btn = this;
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Recalculating...';

        fetch(routes.updateArmPositions, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                schoolclass_id: {{ $schoolclass->id ?? 0 }},
                term_id: {{ $termId }},
                session_id: {{ $sessionId }}
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Positions Updated!', text: data.message, timer: 2000, showConfirmButton: false });
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire({ icon: 'error', title: 'Update Failed', text: data.message });
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' });
            btn.disabled = false;
            btn.innerHTML = origHtml;
        });
    });

    // Staggered row entrance
    const rows = document.querySelectorAll('#scoresheetTableBody tr[data-id]');
    rows.forEach((row, index) => {
        setTimeout(() => row.classList.add('row-visible'), index * 30);
    });

    // Tooltip functionality
    let tipInput = null;
    let tipHideTimer = null;
    const tip = document.getElementById('scoreTooltip');

    function tipPosition(inp) {
        const r = inp.getBoundingClientRect(), tw = 230, margin = 8;
        let left = r.left + r.width / 2 - tw / 2;
        left = Math.max(margin, Math.min(left, window.innerWidth - tw - margin));
        tip.style.left = left + 'px';
        tip.classList.remove('tip-above', 'tip-below');
        if (r.top > 155) {
            tip.style.top = (r.top + window.scrollY - 8) + 'px';
            tip.classList.add('tip-above');
        } else {
            tip.style.top = (r.bottom + window.scrollY + 8) + 'px';
            tip.classList.add('tip-below');
        }
    }

    function tipRefresh(inp) {
        if (!inp) return;
        const row = inp.closest('tr');
        const val = parseFloat(inp.value) || 0, max = parseFloat(inp.dataset.max) || 100;
        const asmtName = inp.dataset.assessmentName || 'Score';
        let total = 0, totalMax = 0;
        row.querySelectorAll('.score-input').forEach(i => { total += parseFloat(i.value)||0; totalMax += parseFloat(i.dataset.max)||0; });
        const grade = clientGrade(total);
        const pct   = totalMax > 0 ? Math.min(total / totalMax * 100, 100) : 0;
        const col   = getGradeColor(grade);

        document.getElementById('stAvatar').src = row.dataset.avatar || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
        document.getElementById('stName').textContent = row.dataset.name || '—';
        document.getElementById('stMeta').textContent = (row.dataset.admissionno || '—') + ' · ' + asmtName + ' (max ' + max + ')';
        document.getElementById('stVal').textContent = val % 1 === 0 ? String(val) : val.toFixed(1);
        document.getElementById('stTotal').textContent = total.toFixed(1);
        const gEl = document.getElementById('stGrade'); gEl.textContent = grade; gEl.style.color = col;
        document.getElementById('stProgLabel').textContent = total.toFixed(1) + ' / ' + totalMax + ' marks';
        document.getElementById('stProgPct').textContent = Math.round(pct) + '%';
        const fill = document.getElementById('stProgFill');
        fill.style.width = pct.toFixed(1) + '%';
        fill.style.background = pct >= 70 ? '#16a34a' : pct >= 50 ? '#2563eb' : pct >= 40 ? '#d97706' : '#dc2626';
        tipPosition(inp);
    }

    function tipShow(inp) {
        clearTimeout(tipHideTimer);
        tipInput = inp;
        tip.style.position = 'absolute';
        tip.style.display = 'block';
        tipRefresh(inp);
        requestAnimationFrame(() => { tip.style.opacity = '1'; });
    }

    function tipHide() {
        tip.style.opacity = '0';
        tipHideTimer = setTimeout(() => { if (tip.style.opacity === '0') tip.style.display = 'none'; }, 160);
        tipInput = null;
    }

    // Attach tooltip to score inputs
    document.querySelectorAll('.score-input').forEach(inp => {
        inp.addEventListener('focus', function() { tipShow(this); });
        inp.addEventListener('blur', function() { setTimeout(() => { if (tipInput === this) tipHide(); }, 80); });
        inp.addEventListener('input', function() { if (tipInput === this) tipRefresh(this); });
    });
});
</script>
@endsection
