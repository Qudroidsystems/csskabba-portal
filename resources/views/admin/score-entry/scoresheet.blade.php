{{-- resources/views/admin/score-entry/scoresheet.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* ── Admin Scoresheet Design System ─────────────────────────────── */
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

/* Admin Banner */
.admin-banner {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-left: 4px solid #0284c7;
    border-radius: var(--ss-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
    animation: slideIn 0.4s ease;
}
@keyframes slideIn {
    from { transform: translateY(-10px); opacity: 0; }
    to   { transform: translateY(0);     opacity: 1; }
}

/* Score Inputs */
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

/* Table */
#scoresheetTable { font-size: 12.5px; }
#scoresheetTable thead tr { background: var(--ss-primary); color: #fff; }
#scoresheetTable thead th { padding: 10px 8px; font-weight: 600; white-space: nowrap; border: none; }
#scoresheetTable tbody tr { transition: background .12s; }
#scoresheetTable tbody td { padding: 6px 8px; vertical-align: middle; border-bottom: 1px solid var(--ss-border); }

.row-vetted     { background: #f0fdf4 !important; }
.row-not-vetted { background: #fef2f2 !important; }
.row-pending    { background: #fffbeb !important; }

/* Stat Cards */
.stat-card { background: var(--ss-card); border: 1px solid var(--ss-border); border-radius: var(--ss-radius); padding: 14px 18px; box-shadow: var(--ss-shadow); transition: transform .15s; }
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value { font-size: 22px; font-weight: 700; color: var(--ss-primary); }
.stat-card .stat-label { font-size: 11px; color: var(--ss-muted); margin-top: 2px; }
.stat-card .stat-icon  { font-size: 28px; opacity: .15; float: right; margin-top: -6px; }

/* Grade strip */
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

/* Position badge flash */
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

/* Row entrance + hover */
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
@media (prefers-reduced-motion: reduce) {
    #scoresheetTableBody tr[data-id],
    #scoresheetTableBody tr[data-id]:hover { transition: background .15s ease !important; transform: none !important; opacity: 1 !important; }
}

/* Score Input Tooltip */
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

/* Apple-style Save Modal */
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
.ss-icon-ring {
    width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 16px;
    display: flex; align-items: center; justify-content: center; position: relative;
}
.ss-icon-ring svg.ss-arc-svg { position: absolute; top:0; left:0; width:56px; height:56px; }
.ss-icon-center {
    width: 36px; height: 36px; border-radius: 50%; background: rgba(30,58,95,.09);
    display: flex; align-items: center; justify-content: center;
    z-index: 1; position: relative; transition: background .3s;
}
.ss-modal-title { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 3px; letter-spacing: -.015em; }
.ss-modal-sub   { font-size: 12px; color: #64748b; margin-bottom: 20px; min-height: 16px; transition: opacity .2s; }
.ss-progress-track { height: 5px; border-radius: 3px; background: #f1f5f9; overflow: hidden; margin-bottom: 10px; }
.ss-progress-fill  { height: 100%; border-radius: 3px; background: var(--ss-primary); width: 0%; transition: width .38s cubic-bezier(.4,0,.2,1), background .3s ease; }
.ss-count-row  { display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #94a3b8; }
.ss-count-num  { font-size: 11px; font-weight: 600; color: #334155; font-variant-numeric: tabular-nums; }
.ss-check-path { stroke-dasharray: 22; stroke-dashoffset: 22; transition: stroke-dashoffset .38s ease .08s; }
.ss-check-path.drawn { stroke-dashoffset: 0; }

@media (max-width: 768px) {
    .score-input  { width: 64px; min-width: 64px; height: 42px; font-size: 1rem; }
    .stat-card    { padding: 10px 12px; }
    .stat-card .stat-value { font-size: 18px; }
    #ssSaveModal  { width: 280px; padding: 26px 24px 22px; }
    #scoreTooltip { width: calc(100vw - 24px); }
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

{{-- ══ SCORE INPUT TOOLTIP ════════════════════════════════════════ --}}
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

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Admin Banner --}}
    <div class="admin-banner">
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
        $gradeDist  = $broadsheets->groupBy('grade')->map->count();
        $gradeColors = [
            'A'  => '#16a34a', 'A1' => '#16a34a',
            'B'  => '#2563eb', 'B2' => '#2563eb', 'B3' => '#3b82f6',
            'C'  => '#7c3aed', 'C4' => '#7c3aed', 'C5' => '#8b5cf6', 'C6' => '#a78bfa',
            'D'  => '#d97706', 'D7' => '#d97706', 'E8' => '#f59e0b',
            'F'  => '#dc2626', 'F9' => '#dc2626',
        ];
    @endphp

    {{-- Stats Cards --}}
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
                                {{ $subjectClass->subject->subject }}
                                <small class="text-muted fw-normal">({{ $subjectClass->subject->subject_code }})</small>
                            </h5>
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">
                                    <i class="ri-school-line me-1"></i>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm->arm ?? '' }}
                                </span>
                                <span class="badge bg-info-subtle text-info fs-6 px-3 py-2">
                                    <i class="ri-calendar-line me-1"></i>{{ $term->term }} | {{ $session->session }}
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
                                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 assessment-btn"
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

    {{-- Position Legend + Recalculate --}}
    <div class="d-flex align-items-start justify-content-between gap-3 mb-2 flex-wrap"
         style="font-size:12px;color:var(--ss-muted);">
        <span>
            <i class="ri-information-line me-1 text-info"></i>
            <strong>Total Grade</strong> = grade on raw total (saved) &nbsp;|&nbsp;
            <strong>Cum Grade</strong>   = grade on cumulative avg (display) &nbsp;|&nbsp;
            <strong>Class Pos (Cum)</strong>   = all arms, ranked by cum &nbsp;|&nbsp;
            <strong>Class Pos (Total)</strong>  = all arms, ranked by total &nbsp;|&nbsp;
            <strong>Arm Pos (Total)</strong>   = this arm, ranked by total &nbsp;|&nbsp;
            <strong>Arm Pos (Cum)</strong>     = this arm, ranked by cum
        </span>
        <button type="button" class="btn btn-sm btn-primary flex-shrink-0" id="updateArmPositionsBtn">
            <i class="ri-refresh-line me-1"></i>Recalculate All Positions
        </button>
    </div>

    {{-- Main Scoresheet Card --}}
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
                    <button type="button" class="btn btn-sm btn-warning" id="downloadMarksSheetBtn">
                        <i class="ri-file-pdf-line me-1"></i>Marks Sheet
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="downloadExcelBtn">
                        <i class="ri-download-line me-1"></i>Export Excel
                    </button>
                @endif
                <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}"
                   class="btn btn-sm btn-outline-light">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="card-body p-0">

            {{-- Download progress --}}
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

                        @foreach($assessments as $assessment)
                            <th class="col-assessment-{{ $assessment->id }} text-center">
                                {{ $assessment->name }}<br>
                                <small class="fw-normal opacity-75">({{ $assessment->max_score }})</small>
                            </th>
                        @endforeach

                        <th class="col-total text-center">Total</th>
                        <th class="col-total-grade text-center" title="Grade based on raw total score">
                            Total<br><small class="fw-normal opacity-75">Grade</small>
                        </th>
                        <th class="col-bf text-center">BF</th>
                        <th class="col-cum text-center">Cum</th>
                        <th class="col-cum-grade text-center" title="Grade based on cumulative average">
                            Cum<br><small class="fw-normal opacity-75">Grade</small>
                        </th>
                        <th class="col-avg text-center" title="Subject class average">Class Avg</th>
                        <th class="col-gpa  text-center">GPA</th>
                        <th class="col-cgpa text-center">CGPA</th>
                        <th class="col-position text-center"
                            title="All arms of this class, ranked by cumulative average">
                            Class Pos<br><small class="fw-normal opacity-75">(Cum)</small>
                        </th>
                        <th class="col-position-total text-center"
                            title="All arms of this class, ranked by raw total">
                            Class Pos<br><small class="fw-normal opacity-75">(Total)</small>
                        </th>
                        <th class="col-arm-position text-center"
                            title="This arm only, ranked by raw total">
                            Arm Pos<br><small class="fw-normal opacity-75">(Total)</small>
                        </th>
                        <th class="col-arm-position-cum text-center"
                            title="This arm only, ranked by cumulative average">
                            Arm Pos<br><small class="fw-normal opacity-75">(Cum)</small>
                        </th>
                        <th class="col-vetted text-center">Status</th>
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
                            $cum        = $broadsheet->cum ?? 0;
                            $totalGrade = $broadsheet->grade ?? '-';
                            $gradeForCum = '-';
                            if (isset($broadsheet->classcategoryid)) {
                                $cat = \App\Models\Classcategory::find($broadsheet->classcategoryid);
                                $gradeForCum = $cat ? $cat->calculateGrade($cum) : '-';
                            }
                            $cumColor        = $cum      >= 70 ? 'success' : ($cum      >= 50 ? 'info' : ($cum      >= 40 ? 'warning' : 'danger'));
                            $totalColor      = $rowTotal >= 70 ? 'success' : ($rowTotal >= 50 ? 'info' : ($rowTotal >= 40 ? 'warning' : 'danger'));
                            $vClass = match(true) {
                                $broadsheet->vettedstatus === '1' => 'row-vetted',
                                $broadsheet->vettedstatus === '0' => 'row-not-vetted',
                                default => 'row-pending',
                            };
                            $totalGradeColor = $gradeColors[$totalGrade]  ?? '#6b7280';
                            $cumGradeColor   = $gradeColors[$gradeForCum] ?? '#6b7280';
                            $avatarUrl = $broadsheet->picture
                                ? asset('storage/student_avatars/'.basename($broadsheet->picture))
                                : asset('storage/student_avatars/unnamed.jpg');
                        @endphp
                        <tr class="{{ $vClass }}"
                            data-id="{{ $broadsheet->id }}"
                            data-bf="{{ $broadsheet->bf ?? 0 }}"
                            data-termid="{{ $termId }}"
                            data-schoolclassid="{{ $schoolclass->id ?? 0 }}"
                            data-categoryid="{{ $broadsheet->classcategoryid ?? '' }}"
                            data-name="{{ ($broadsheet->lname ?? '') }}, {{ ($broadsheet->fname ?? '') }}{{ $broadsheet->mname ? ' '.$broadsheet->mname : '' }}"
                            data-admissionno="{{ $broadsheet->admissionno ?? '' }}"
                            data-avatar="{{ $avatarUrl }}">

                            <td class="col-checkbox">
                                <div class="form-check mb-0">
                                    <input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}">
                                </div>
                            </td>
                            <td class="col-sn fw-medium">{{ ++$i }}</td>
                            <td class="col-admissionno" data-admissionno="{{ $broadsheet->admissionno }}">
                                <span class="text-muted small">{{ $broadsheet->admissionno ?? '-' }}</span>
                            </td>
                            <td class="col-name"
                                data-name="{{ strtolower(($broadsheet->lname ?? '').' '.($broadsheet->fname ?? '').' '.($broadsheet->mname ?? '')) }}">
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

                            @foreach($assessments as $assessment)
                                @php
                                    $scoreObj   = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                    $scoreValue = $scoreObj ? $scoreObj->score : 0;
                                @endphp
                                <td class="col-assessment-{{ $assessment->id }} text-center">
                                    <input type="number"
                                           class="score-input"
                                           data-field="{{ $assessment->id }}"
                                           data-max="{{ $assessment->max_score }}"
                                           data-id="{{ $broadsheet->id }}"
                                           data-original="{{ $scoreValue }}"
                                           data-assessment-name="{{ $assessment->name }}"
                                           value="{{ $scoreValue }}"
                                           min="0" max="{{ $assessment->max_score }}" step="0.1">
                                </td>
                            @endforeach

                            <td class="col-total text-center">
                                <span class="badge bg-{{ $totalColor }}-subtle text-{{ $totalColor }} fw-bold total-badge" style="font-size:12px;">
                                    {{ number_format($rowTotal, 1) }}
                                </span>
                            </td>
                            <td class="col-total-grade text-center">
                                <span class="grade-badge" style="color:{{ $totalGradeColor }};">{{ $totalGrade }}</span>
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
                                <span class="cum-grade-badge" style="color:{{ $cumGradeColor }};">{{ $gradeForCum }}</span>
                            </td>
                            <td class="col-avg text-center">
                                <span class="badge avg-badge" style="background:#f3e8ff;color:#7c3aed;">
                                    {{ number_format($broadsheet->avg ?? 0, 1) }}
                                </span>
                            </td>
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
                            {{-- subject_position_class → all arms, by cum --}}
                            <td class="col-position text-center">
                                <span class="badge position-badge" style="background:var(--ss-primary);"
                                      title="All arms, ranked by cumulative average">
                                    {{ $broadsheet->position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}
                                </span>
                            </td>
                            {{-- subject_position_class_total → all arms, by total --}}
                            <td class="col-position-total text-center">
                                <span class="badge position-total-badge" style="background:#0f766e;"
                                      title="All arms, ranked by raw total">
                                    {{ $broadsheet->position_total ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position_total) : '-' }}
                                </span>
                            </td>
                            {{-- arm_position → this arm, by total --}}
                            <td class="col-arm-position text-center">
                                <span class="badge arm-position-badge" style="background:#0891b2;"
                                      title="This arm only, ranked by raw total">
                                    {{ $broadsheet->arm_position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position) : '-' }}
                                </span>
                            </td>
                            {{-- arm_position_cum → this arm, by cum --}}
                            <td class="col-arm-position-cum text-center">
                                <span class="badge arm-position-cum-badge" style="background:#7c3aed;"
                                      title="This arm only, ranked by cumulative average">
                                    {{ $broadsheet->arm_position_cum ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position_cum) : '-' }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($assessments->count() ?: 4) + 17 }}" class="text-center py-5 text-muted">
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
                        <button class="btn btn-sm btn-outline-primary"   id="selectAllScores">
                            <i class="ri-check-double-line me-1"></i>Select All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="clearAllScores">
                            <i class="ri-close-line me-1"></i>Clear
                        </button>
                        <button class="btn btn-sm btn-outline-danger"    id="deleteSelectedScoresBtn">
                            <i class="ri-delete-bin-line me-1"></i>Delete Selected
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
                        <div class="col-md-4"><div class="col-group">
                            <h6>Student Info</h6>
                            @foreach([['col-checkbox','Select'],['col-sn','SN'],['col-admissionno','Adm. No'],['col-name','Name']] as [$cls,$lbl])
                            <div class="form-check">
                                <input class="form-check-input col-toggle" type="checkbox" id="chk-{{ $cls }}" data-col="{{ $cls }}" checked>
                                <label class="form-check-label" for="chk-{{ $cls }}">{{ $lbl }}</label>
                            </div>
                            @endforeach
                        </div></div>
                        @if($assessments->isNotEmpty())
                        <div class="col-md-4"><div class="col-group">
                            <h6>Assessments</h6>
                            @foreach($assessments as $a)
                            <div class="form-check">
                                <input class="form-check-input col-toggle" type="checkbox"
                                       id="chk-col-assessment-{{ $a->id }}" data-col="col-assessment-{{ $a->id }}" checked>
                                <label class="form-check-label" for="chk-col-assessment-{{ $a->id }}">{{ $a->name }}</label>
                            </div>
                            @endforeach
                        </div></div>
                        @endif
                        <div class="col-md-4"><div class="col-group">
                            <h6>Scores &amp; Metrics</h6>
                            @foreach([
                                ['col-total',            'Total'],
                                ['col-total-grade',      'Total Grade (saved)'],
                                ['col-bf',               'BF'],
                                ['col-cum',              'Cum'],
                                ['col-cum-grade',        'Cum Grade (display)'],
                                ['col-avg',              'Class Avg'],
                                ['col-gpa',              'GPA'],
                                ['col-cgpa',             'CGPA'],
                                ['col-position',         'Class Pos (Cum)'],
                                ['col-position-total',   'Class Pos (Total)'],
                                ['col-arm-position',     'Arm Pos (Total)'],
                                ['col-arm-position-cum', 'Arm Pos (Cum)'],
                                ['col-vetted',           'Status'],
                            ] as [$cls,$lbl])
                            <div class="form-check">
                                <input class="form-check-input col-toggle" type="checkbox" id="chk-{{ $cls }}" data-col="{{ $cls }}" checked>
                                <label class="form-check-label" for="chk-{{ $cls }}">{{ $lbl }}</label>
                            </div>
                            @endforeach
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
/* ══════════════════════════════════════════════════════════════════════
   CSRF & ROUTES
   ══════════════════════════════════════════════════════════════════════ */
if (!document.querySelector('meta[name="csrf-token"]')) {
    const m = document.createElement('meta'); m.name = 'csrf-token';
    m.content = '{{ csrf_token() }}'; document.head.appendChild(m);
}
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

window.routes = {
    singleUpdate      : '{{ route("admin.score-entry.single-update") }}',
    bulkUpdate        : '{{ route("admin.score-entry.bulk-update") }}',
    destroy           : '{{ route("admin.score-entry.destroy") }}',
    results           : '{{ route("admin.score-entry.results") }}',
    downloadMarksSheet: '{{ route("admin.score-entry.download-marks-sheet") }}',
    export            : '{{ route("admin.score-entry.export") }}',
    gradeForScore     : '{{ route("admin.score-entry.grade-preview") }}',
    updateArmPositions: '{{ route("update.arm.positions.all") }}',
};

window.term_id         = {{ $termId }};
window.session_id      = {{ $sessionId }};
window.subjectclass_id = {{ $subjectclassId }};
window.schoolclass_id  = {{ $schoolclass->id ?? 0 }};
window.staff_id        = {{ $teacherId }};
window.is_senior       = {{ $is_senior ? 'true' : 'false' }};

/* ══════════════════════════════════════════════════════════════════════
   CONSTANTS
   ══════════════════════════════════════════════════════════════════════ */
const GRADE_COLORS = {
    'A' :'#16a34a','A1':'#16a34a',
    'B' :'#2563eb','B2':'#2563eb','B3':'#3b82f6',
    'C' :'#7c3aed','C4':'#7c3aed','C5':'#8b5cf6','C6':'#a78bfa',
    'D' :'#d97706','D7':'#d97706','E8':'#f59e0b',
    'F' :'#dc2626','F9':'#dc2626',
};

/* ══════════════════════════════════════════════════════════════════════
   UTILITIES
   ══════════════════════════════════════════════════════════════════════ */
const fmtN = (n, d = 1) => parseFloat(n || 0).toFixed(d);

const ord = n => {
    if (!n || isNaN(n)) return '-';
    n = +n; const s = n % 100;
    return n + (s >= 11 && s <= 13 ? 'th' : (['th','st','nd','rd'][n % 10] || 'th'));
};

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

function clientGrade(score) {
    score = parseFloat(score) || 0;
    if (window.is_senior) {
        if (score >= 75) return 'A1'; if (score >= 70) return 'B2';
        if (score >= 65) return 'B3'; if (score >= 60) return 'C4';
        if (score >= 55) return 'C5'; if (score >= 50) return 'C6';
        if (score >= 45) return 'D7'; if (score >= 40) return 'E8';
        return 'F9';
    }
    if (score >= 70) return 'A'; if (score >= 60) return 'B';
    if (score >= 50) return 'C'; if (score >= 40) return 'D';
    return 'F';
}

function applyGrade(badge, grade) {
    if (!badge) return;
    badge.textContent = grade || '-';
    badge.style.color = GRADE_COLORS[grade] || '#6b7280';
    badge.classList.remove('updating');
    badge.classList.add('updated');
    setTimeout(() => badge.classList.remove('updated'), 500);
}

function validateInput(inp) {
    const max = parseFloat(inp.dataset.max) || 0;
    const val = parseFloat(inp.value) || 0;
    inp.classList.toggle('is-invalid', val > max);
    return val <= max;
}

/* ══════════════════════════════════════════════════════════════════════
   APPLY ALL POSITIONS — patches all 4 badge columns for every row
   ══════════════════════════════════════════════════════════════════════ */
function applyAllPositions(broadsheets) {
    if (!Array.isArray(broadsheets) || !broadsheets.length) return;
    const map = {};
    broadsheets.forEach(bs => { map[String(bs.id)] = bs; });
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
        const bs = map[row.dataset.id];
        if (!bs) return;
        [
            [row.querySelector('.position-badge'),         bs.position],
            [row.querySelector('.position-total-badge'),   bs.position_total],
            [row.querySelector('.arm-position-badge'),     bs.arm_position],
            [row.querySelector('.arm-position-cum-badge'), bs.arm_position_cum],
        ].forEach(([badge, val]) => {
            if (!badge) return;
            const newText = ord(val);
            if (badge.textContent.trim() !== newText) {
                badge.textContent = newText;
                badge.classList.remove('pos-flash');
                void badge.offsetWidth;
                badge.classList.add('pos-flash');
                setTimeout(() => badge.classList.remove('pos-flash'), 520);
            }
        });
    });
}

/* ══════════════════════════════════════════════════════════════════════
   LIVE GRADE + TOTAL PREVIEW
   ══════════════════════════════════════════════════════════════════════ */
const gradeTimers = {};

function updateRowGrades(row) {
    const bid         = row.dataset.id;
    const bf          = parseFloat(row.dataset.bf) || 0;
    const termId      = parseInt(row.dataset.termid) || window.term_id;
    const schoolclsId = parseInt(row.dataset.schoolclassid) || window.schoolclass_id;

    let totalRaw = 0;
    row.querySelectorAll('.score-input').forEach(inp => { totalRaw += parseFloat(inp.value) || 0; });
    const cum = (termId == 1 || bf === 0) ? totalRaw : (totalRaw + bf) / 2;

    const totalBadge = row.querySelector('.total-badge');
    if (totalBadge) {
        totalBadge.textContent = fmtN(totalRaw);
        const tc = totalRaw >= 70 ? 'success' : totalRaw >= 50 ? 'info' : totalRaw >= 40 ? 'warning' : 'danger';
        totalBadge.className = `badge fw-bold total-badge bg-${tc}-subtle text-${tc}`;
        totalBadge.style.fontSize = '12px';
    }

    const cumBadge = row.querySelector('.cum-badge');
    if (cumBadge) {
        cumBadge.textContent = fmtN(cum);
        const cc = cum >= 70 ? 'success' : cum >= 50 ? 'info' : cum >= 40 ? 'warning' : 'danger';
        cumBadge.className = `badge fw-bold cum-badge bg-${cc}-subtle text-${cc}`;
        cumBadge.style.fontSize = '12px';
    }

    applyGrade(row.querySelector('.grade-badge'),     clientGrade(totalRaw));
    applyGrade(row.querySelector('.cum-grade-badge'), clientGrade(cum));

    clearTimeout(gradeTimers[bid]);
    gradeTimers[bid] = setTimeout(async () => {
        const tgb = row.querySelector('.grade-badge');
        const cgb = row.querySelector('.cum-grade-badge');
        try {
            if (tgb) { tgb.classList.add('updating'); tgb.innerHTML = '<span class="grade-loading"></span>'; }
            if (cgb) { cgb.classList.add('updating'); cgb.innerHTML = '<span class="grade-loading"></span>'; }
            const res  = await fetch(window.routes.gradeForScore, {
                method: 'POST',
                headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF },
                body: JSON.stringify({ schoolclass_id: schoolclsId, total: totalRaw, cum }),
            });
            const data = await res.json();
            if (data.success) {
                applyGrade(tgb, data.total_grade);
                applyGrade(cgb, data.cum_grade);
            } else {
                applyGrade(tgb, clientGrade(totalRaw));
                applyGrade(cgb, clientGrade(cum));
            }
        } catch {
            applyGrade(tgb, clientGrade(totalRaw));
            applyGrade(cgb, clientGrade(cum));
        }
    }, 400);
}

/* ══════════════════════════════════════════════════════════════════════
   SINGLE SCORE SAVE
   ══════════════════════════════════════════════════════════════════════ */
function saveIndividualScore(input) {
    const row = input.closest('tr');
    fetch(window.routes.singleUpdate, {
        method : 'POST',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF },
        body   : JSON.stringify({
            broadsheet_id   : input.dataset.id,
            assessment_id   : parseInt(input.dataset.field),
            score           : parseFloat(input.value) || 0,
            is_sub          : false,
            term_id         : window.term_id,
            session_id      : window.session_id,
            subjectclass_id : window.subjectclass_id,
            schoolclass_id  : window.schoolclass_id,
            staff_id        : window.staff_id,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { showToast(data.message || 'Could not save.', 'warning'); return; }
        const d = data.data;

        const bfBadge = row.querySelector('.bf-badge');
        if (bfBadge && d.bf != null) bfBadge.textContent = fmtN(d.bf);

        const cumBadge = row.querySelector('.cum-badge');
        if (cumBadge && d.cum != null) {
            const cum = parseFloat(d.cum);
            cumBadge.textContent = fmtN(cum);
            const cc = cum >= 70 ? 'success' : cum >= 50 ? 'info' : cum >= 40 ? 'warning' : 'danger';
            cumBadge.className = `badge fw-bold cum-badge bg-${cc}-subtle text-${cc}`;
            cumBadge.style.fontSize = '12px';
        }

        const tgb = row.querySelector('.grade-badge');
        if (tgb && d.grade != null) applyGrade(tgb, d.grade);
        const cgb = row.querySelector('.cum-grade-badge');
        if (cgb && d.cum != null) applyGrade(cgb, clientGrade(parseFloat(d.cum)));

        const gpaBadge  = row.querySelector('.gpa-badge');
        const cgpaBadge = row.querySelector('.cgpa-badge');
        if (gpaBadge  && d.gpa  != null) gpaBadge.textContent  = fmtN(d.gpa,  2);
        if (cgpaBadge && d.cgpa != null) cgpaBadge.textContent = fmtN(d.cgpa, 2);

        input.classList.add('is-saved');
        setTimeout(() => input.classList.remove('is-saved'), 2000);

        refreshAllPositions();
    })
    .catch(err => {
        console.warn('singleUpdate error:', err.message);
        showToast('Network issue — score may not have saved.', 'danger');
    });
}

/* ══════════════════════════════════════════════════════════════════════
   REFRESH ALL POSITIONS (debounced)
   ══════════════════════════════════════════════════════════════════════ */
let positionRefreshTimer = null;
function refreshAllPositions() {
    clearTimeout(positionRefreshTimer);
    positionRefreshTimer = setTimeout(() => {
        fetch(window.routes.results, {
            headers: { 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && Array.isArray(data.scores)) applyAllPositions(data.scores);
        })
        .catch(() => {});
    }, 120);
}

/* ══════════════════════════════════════════════════════════════════════
   APPLE-STYLE SAVE MODAL
   ══════════════════════════════════════════════════════════════════════ */
const SS_ARC_CIRC = 157.08;
function ssEl(id) { return document.getElementById(id); }
function ssResetIcons() {
    ssEl('ssIconSave').style.display  = ''; ssEl('ssIconCheck').style.display = 'none'; ssEl('ssIconX').style.display = 'none';
    ssEl('ssCheckPath').style.strokeDashoffset = '22'; ssEl('ssCheckPath').classList.remove('drawn');
    ssEl('ssIconCenter').style.background = 'rgba(30,58,95,0.09)'; ssEl('ssArcFg').style.stroke = '#1e3a5f';
}
function ssSetArc(pct) { ssEl('ssArcFg').style.strokeDashoffset = (SS_ARC_CIRC * (1 - pct / 100)).toFixed(3); }
function ssOpen(total) {
    ssResetIcons(); ssSetArc(0);
    ssEl('ssSaveFill').style.width = '0%'; ssEl('ssSaveFill').style.background = '#1e3a5f';
    ssEl('ssSaveTitle').textContent = 'Saving scores'; ssEl('ssSaveSub').textContent = 'Preparing…';
    ssEl('ssSaveCountLabel').textContent = 'Saved'; ssEl('ssSaveCountNum').textContent = `0 / ${total}`;
    const o = ssEl('ssSaveOverlay'); o.classList.remove('ss-closing'); o.classList.add('ss-visible');
}
function ssUpdate(saved, total, pct) {
    ssEl('ssSaveFill').style.width = pct.toFixed(1) + '%'; ssEl('ssSaveCountNum').textContent = `${saved} / ${total}`; ssSetArc(pct);
    if      (pct < 25) ssEl('ssSaveSub').textContent = 'Uploading data…';
    else if (pct < 55) ssEl('ssSaveSub').textContent = 'Processing records…';
    else if (pct < 85) ssEl('ssSaveSub').textContent = 'Recalculating grades & positions…';
    else               ssEl('ssSaveSub').textContent  = 'Finalising…';
}
function ssSuccess(total) {
    ssEl('ssSaveFill').style.width = '100%'; ssEl('ssSaveFill').style.background = '#16a34a';
    ssEl('ssArcFg').style.strokeDashoffset = '0'; ssEl('ssArcFg').style.stroke = '#16a34a';
    ssEl('ssIconCenter').style.background  = '#dcfce7';
    ssEl('ssIconSave').style.display = 'none'; ssEl('ssIconCheck').style.display = '';
    requestAnimationFrame(() => ssEl('ssCheckPath').classList.add('drawn'));
    ssEl('ssSaveTitle').textContent = 'All saved';
    ssEl('ssSaveSub').textContent   = `${total} score${total !== 1 ? 's' : ''} saved successfully`;
    ssEl('ssSaveCountNum').textContent = `${total} / ${total}`;
    setTimeout(ssClose, 1900);
}
function ssError(msg) {
    ssEl('ssSaveFill').style.background = '#dc2626'; ssEl('ssArcFg').style.stroke = '#dc2626';
    ssEl('ssIconCenter').style.background = '#fee2e2';
    ssEl('ssIconSave').style.display = 'none'; ssEl('ssIconX').style.display = '';
    ssEl('ssSaveTitle').textContent = 'Save failed'; ssEl('ssSaveSub').textContent = msg || 'Something went wrong.';
    setTimeout(ssClose, 2400);
}
function ssClose() {
    const o = ssEl('ssSaveOverlay'); o.classList.add('ss-closing');
    setTimeout(() => o.classList.remove('ss-visible','ss-closing'), 260);
}

/* ══════════════════════════════════════════════════════════════════════
   BULK SAVE
   ══════════════════════════════════════════════════════════════════════ */
function bulkSave() {
    const invalid = document.querySelectorAll('.score-input.is-invalid').length;
    if (invalid) { Swal.fire({ icon:'warning', title:'Invalid Scores', text:`${invalid} score(s) exceed their maximum.` }); return; }

    const scores = [];
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
        const assessments = {};
        row.querySelectorAll('.score-input').forEach(inp => { assessments[inp.dataset.field] = parseFloat(inp.value) || 0; });
        if (Object.keys(assessments).length) scores.push({ id: row.dataset.id, assessments });
    });
    if (!scores.length) return;

    const total = scores.length;
    ssOpen(total);
    let fakeProgress = 0;
    const fakeIv = setInterval(() => {
        fakeProgress = Math.min(fakeProgress + Math.random() * 4 + 2, 88);
        ssUpdate(Math.round((fakeProgress / 100) * total), total, fakeProgress);
    }, 130);

    const btn = document.getElementById('bulkUpdateScores');
    const origHtml = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line"></i> Saving…'; }

    fetch(window.routes.bulkUpdate, {
        method : 'POST',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF },
        body   : JSON.stringify({
            scores, term_id: window.term_id, session_id: window.session_id,
            subjectclass_id: window.subjectclass_id, staff_id: window.staff_id,
            schoolclass_id: window.schoolclass_id, is_sub: false,
        }),
    })
    .then(r => r.json())
    .then(data => {
        clearInterval(fakeIv);
        if (!data.success) { ssError(data.message || 'Server error.'); return; }
        ssUpdate(total, total, 100);
        setTimeout(() => ssSuccess(total), 220);

        (data.data?.broadsheets ?? []).forEach(bs => {
            const row = document.querySelector(`#scoresheetTableBody tr[data-id="${bs.id}"]`);
            if (!row) return;

            const ts = parseFloat(bs.total ?? 0);
            const tb = row.querySelector('.total-badge');
            if (tb) {
                tb.textContent = fmtN(ts);
                const tc = ts >= 70 ? 'success' : ts >= 50 ? 'info' : ts >= 40 ? 'warning' : 'danger';
                tb.className = `badge fw-bold total-badge bg-${tc}-subtle text-${tc}`;
                tb.style.fontSize = '12px';
            }
            const tgb = row.querySelector('.grade-badge');
            if (tgb) applyGrade(tgb, bs.grade ?? '-');

            const bfB = row.querySelector('.bf-badge');
            if (bfB) bfB.textContent = fmtN(bs.bf);

            const cum = parseFloat(bs.cum ?? 0);
            const cb  = row.querySelector('.cum-badge');
            if (cb) {
                cb.textContent = fmtN(cum);
                const cc = cum >= 70 ? 'success' : cum >= 50 ? 'info' : cum >= 40 ? 'warning' : 'danger';
                cb.className = `badge fw-bold cum-badge bg-${cc}-subtle text-${cc}`;
                cb.style.fontSize = '12px';
            }
            const cgb = row.querySelector('.cum-grade-badge');
            if (cgb) applyGrade(cgb, clientGrade(cum));

            const ab = row.querySelector('.avg-badge');
            if (ab && bs.avg != null) ab.textContent = fmtN(bs.avg);

            const gb    = row.querySelector('.gpa-badge');
            const cgpab = row.querySelector('.cgpa-badge');
            if (gb)    gb.textContent    = fmtN(bs.gpa,  2);
            if (cgpab) cgpab.textContent = fmtN(bs.cgpa, 2);

            row.querySelectorAll('.score-input').forEach(i => {
                i.classList.add('is-saved');
                setTimeout(() => i.classList.remove('is-saved'), 2000);
            });
        });

        applyAllPositions(data.data?.broadsheets ?? []);
    })
    .catch(err => { clearInterval(fakeIv); ssError('Please check your connection and try again.'); console.error(err); })
    .finally(() => { if (btn) { btn.disabled = false; btn.innerHTML = origHtml || '<i class="ri-save-line me-1"></i>Save All Scores'; } });
}

/* ══════════════════════════════════════════════════════════════════════
   PDF / EXCEL DOWNLOADS
   ══════════════════════════════════════════════════════════════════════ */
function startDownload(url, filename, label) {
    const cont = document.getElementById('downloadProgressContainer');
    const bar  = document.getElementById('downloadProgressBar');
    const lbl  = document.getElementById('downloadProgressLabel');
    if (cont) cont.style.display = 'block';
    if (bar)  bar.style.width    = '10%';
    if (lbl)  lbl.textContent    = label || 'Downloading…';
    fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF } })
    .then(async r => {
        if (!r.ok) { const e = await r.json().catch(()=>({})); throw new Error(e.message || 'Download failed.'); }
        if (bar) bar.style.width = '90%';
        return r.blob();
    })
    .then(blob => {
        if (bar) bar.style.width = '100%';
        const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = filename;
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(a.href);
        showToast('Downloaded successfully!', 'success');
    })
    .catch(err => Swal.fire({ icon:'error', title:'Download Failed', text: err.message }))
    .finally(() => setTimeout(() => { if (cont) cont.style.display='none'; if (bar) bar.style.width='0%'; }, 1200));
}

/* ══════════════════════════════════════════════════════════════════════
   SCORE INPUT TOOLTIP
   ══════════════════════════════════════════════════════════════════════ */
const tip = document.getElementById('scoreTooltip');
let tipInput = null, tipHideTimer = null;

function tipPosition(inp) {
    const r = inp.getBoundingClientRect(), tw = 230, margin = 8;
    let left = r.left + r.width / 2 - tw / 2;
    left = Math.max(margin, Math.min(left, window.innerWidth - tw - margin));
    tip.style.left = left + 'px';
    tip.classList.remove('tip-above', 'tip-below');
    if (r.top > 155) {
        tip.style.top = (r.top + window.scrollY - 8) + 'px'; tip.classList.add('tip-above');
    } else {
        tip.style.top = (r.bottom + window.scrollY + 8) + 'px'; tip.classList.add('tip-below');
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
    const col   = GRADE_COLORS[grade] || '#6b7280';

    document.getElementById('stAvatar').src        = row.dataset.avatar || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
    document.getElementById('stName').textContent  = row.dataset.name || '—';
    document.getElementById('stMeta').textContent  = (row.dataset.admissionno || '—') + ' · ' + asmtName + ' (max ' + max + ')';
    document.getElementById('stVal').textContent   = val % 1 === 0 ? String(val) : val.toFixed(1);
    document.getElementById('stTotal').textContent = fmtN(total);
    const gEl = document.getElementById('stGrade'); gEl.textContent = grade; gEl.style.color = col;
    document.getElementById('stProgLabel').textContent = fmtN(total) + ' / ' + totalMax + ' marks';
    document.getElementById('stProgPct').textContent   = Math.round(pct) + '%';
    const fill = document.getElementById('stProgFill');
    fill.style.width      = pct.toFixed(1) + '%';
    fill.style.background = pct >= 70 ? '#16a34a' : pct >= 50 ? '#2563eb' : pct >= 40 ? '#d97706' : '#dc2626';
    tipPosition(inp);
}

function tipShow(inp) {
    clearTimeout(tipHideTimer); tipInput = inp;
    tip.style.position = 'absolute'; tip.style.display = 'block';
    tipRefresh(inp); requestAnimationFrame(() => { tip.style.opacity = '1'; });
}
function tipHide() {
    tip.style.opacity = '0';
    tipHideTimer = setTimeout(() => { if (tip.style.opacity === '0') tip.style.display = 'none'; }, 160);
    tipInput = null;
}

/* ══════════════════════════════════════════════════════════════════════
   DOM READY
   ══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('imageViewModal')?.addEventListener('show.bs.modal', function(e) {
        const src = e.relatedTarget?.dataset?.image || e.relatedTarget?.getAttribute('data-image');
        document.getElementById('enlargedImage').src = src || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
    });

    // Column visibility
    document.querySelectorAll('.col-toggle').forEach(cb => {
        cb.addEventListener('change', function () {
            document.querySelectorAll(`th.${this.dataset.col}, td.${this.dataset.col}`)
                .forEach(el => el.style.display = this.checked ? '' : 'none');
        });
    });

    // Search
    function applySearch() {
        const q = (document.getElementById('searchInput')?.value ?? '').trim().toLowerCase();
        let vis = 0;
        document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
            const adm  = (row.querySelector('.col-admissionno')?.dataset?.admissionno ?? '').toLowerCase();
            const name = (row.querySelector('.col-name')?.dataset?.name ?? '').toLowerCase();
            const show = !q || adm.includes(q) || name.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        const sc = document.getElementById('scoreCount'); if (sc) sc.textContent = vis;
    }
    document.getElementById('searchInput')?.addEventListener('input', applySearch);
    document.getElementById('clearSearch')?.addEventListener('click', () => {
        const si = document.getElementById('searchInput'); if (si) si.value = ''; applySearch();
    });

    // Checkboxes
    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = this.checked);
    });
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('score-checkbox')) return;
        const all = document.querySelectorAll('.score-checkbox');
        const chk = document.querySelectorAll('.score-checkbox:checked');
        const ca  = document.getElementById('checkAll');
        if (ca) { ca.checked = chk.length === all.length && all.length > 0; ca.indeterminate = chk.length > 0 && chk.length < all.length; }
    });
    document.getElementById('selectAllScores')?.addEventListener('click', () => {
        const ca = document.getElementById('checkAll'); if (ca) ca.checked = true;
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('clearAllScores')?.addEventListener('click', () => {
        const ca = document.getElementById('checkAll'); if (ca) ca.checked = false;
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = false);
    });

    /* Score inputs */
    document.querySelectorAll('.score-input').forEach(inp => {
        inp.addEventListener('focus', function () { this.select(); tipShow(this); });
        inp.addEventListener('input', function () {
            validateInput(this);
            const row = this.closest('tr');
            if (row) updateRowGrades(row);
            if (tipInput === this) tipRefresh(this);
        });
        inp.addEventListener('blur', function () {
            setTimeout(() => { if (tipInput === this) tipHide(); }, 80);
            if (!validateInput(this)) return;
            const orig = parseFloat(this.dataset.original) || 0;
            const curr = parseFloat(this.value) || 0;
            if (Math.abs(curr - orig) > 0.001) {
                this.dataset.original = this.value;
                saveIndividualScore(this);
            }
        });
        inp.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { e.preventDefault(); tipHide(); this.blur(); return; }
            if (e.key !== 'Enter') return;
            e.preventDefault();
            if (validateInput(this)) saveIndividualScore(this);
            const all = Array.from(document.querySelectorAll('.score-input'));
            const idx = all.indexOf(this);
            if (idx < all.length - 1) all[idx + 1].focus();
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && tipInput) { tipHide(); document.activeElement?.blur(); return; }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); bulkSave(); }
    });

    document.getElementById('bulkUpdateScores')?.addEventListener('click', bulkSave);

    // Delete selected
    document.getElementById('deleteSelectedScoresBtn')?.addEventListener('click', function () {
        const ids = Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb => cb.dataset.id);
        if (!ids.length) { Swal.fire({ icon:'warning', title:'No Selection', text:'Select rows to delete.' }); return; }
        Swal.fire({
            title:'Delete selected scores?', text:'This cannot be undone.', icon:'warning',
            showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Yes, delete'
        }).then(r => {
            if (!r.isConfirmed) return;
            Promise.all(ids.map(id =>
                fetch(window.routes.destroy, {
                    method: 'DELETE',
                    headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ id, type: 'terminal' }),
                }).then(r => r.json())
            ))
            .then(results => {
                let deleted = 0;
                results.forEach((res, i) => {
                    if (res.success) { document.querySelector(`tr[data-id="${ids[i]}"]`)?.remove(); deleted++; }
                });
                showToast(`${deleted} score(s) deleted.`, 'success');
                if (!document.querySelectorAll('#scoresheetTableBody tr[data-id]').length) location.reload();
                else refreshAllPositions();
            });
        });
    });

    // Recalculate All Positions
    document.getElementById('updateArmPositionsBtn')?.addEventListener('click', async function () {
        const btn = this, origHtml = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Recalculating…';
        try {
            const response = await fetch(window.routes.updateArmPositions, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body: JSON.stringify({ schoolclass_id: window.schoolclass_id, term_id: window.term_id, session_id: window.session_id }),
            });
            const data = await response.json();
            if (data.success) {
                await fetch(window.routes.results, { headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF} })
                    .then(r => r.json())
                    .then(d => { if (d.success && Array.isArray(d.scores)) applyAllPositions(d.scores); });
                Swal.fire({ icon:'success', title:'Positions Updated!', html: data.message, timer:3000, showConfirmButton:true });
            } else {
                Swal.fire({ icon:'error', title:'Update Failed', text: data.message });
            }
        } catch {
            Swal.fire({ icon:'error', title:'Error', text:'Network error while updating positions.' });
        } finally {
            btn.disabled = false; btn.innerHTML = origHtml;
        }
    });

    // Downloads
    document.getElementById('downloadMarksSheetBtn')?.addEventListener('click', () => {
        const url = `${window.routes.downloadMarksSheet}?subjectclass_id=${window.subjectclass_id}&staff_id=${window.staff_id}&term_id=${window.term_id}&session_id=${window.session_id}&schoolclass_id=${window.schoolclass_id}&type=terminal`;
        startDownload(url, 'admin-marks-sheet.pdf', 'Generating Marks Sheet…');
    });

    document.getElementById('downloadExcelBtn')?.addEventListener('click', () => {
        const btn = document.getElementById('downloadExcelBtn'), origHtml = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line"></i> Generating…'; }
        const url = `${window.routes.export}?subjectclass_id=${window.subjectclass_id}&staff_id=${window.staff_id}&term_id=${window.term_id}&session_id=${window.session_id}&schoolclass_id=${window.schoolclass_id}`;
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' } })
        .then(r => {
            const cd = r.headers.get('content-disposition')||'', m = cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            const fn = m ? m[1].replace(/['"]/g,'') : 'admin-scoresheet.xlsx';
            return r.blob().then(b => ({ blob: b, filename: fn }));
        })
        .then(({blob, filename}) => {
            const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = filename;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            showToast('Excel downloaded!', 'success');
        })
        .catch(err => Swal.fire({ icon:'error', title:'Download Failed', text: err.message }))
        .finally(() => { if (btn) { btn.disabled = false; btn.innerHTML = origHtml; } });
    });

    // Staggered row entrance animation
    (function initRowEntrance() {
        const rows = Array.from(document.querySelectorAll('#scoresheetTableBody tr[data-id]'));
        if (!rows.length) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            rows.forEach(r => r.classList.add('row-visible')); return;
        }
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const row = entry.target, index = rows.indexOf(row);
                setTimeout(() => row.classList.add('row-visible'), Math.min(index * 38, 15 * 38) + 60);
                observer.unobserve(row);
            });
        }, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });
        rows.forEach(row => observer.observe(row));
    })();

}); // end DOMContentLoaded

/* SweetAlert2 lazy load */
if (typeof Swal === 'undefined') {
    const s = document.createElement('script'); s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11'; document.head.appendChild(s);
}
</script>
@endsection
