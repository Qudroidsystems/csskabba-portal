{{-- resources/views/timetable/index.blade.php --}}
@extends('layouts.master')


<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<style>
/* ── Design tokens ────────────────────────────────── */
:root {
    --tt-blue:     #1565C0;
    --tt-purple:   #6A1B9A;
    --tt-green:    #1B5E20;
    --tt-orange:   #E65100;
    --tt-pink:     #880E4F;
    --tt-surface:  #F8FAFC;
    --tt-border:   #E2E8F0;
    --tt-radius:   12px;
    --tt-shadow:   0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
}

/* ── Reset & Base ─────────────────────────────────── */
.timetable-container * {
    box-sizing: border-box;
}

/* ── Page header ──────────────────────────────────── */
.tt-page-header {
    background: linear-gradient(135deg, #1565C0 0%, #6A1B9A 100%);
    border-radius: 16px;
    padding: 24px 28px;
    color: #fff;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.tt-page-header h4 {
    color: #fff;
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}
.tt-page-header p {
    color: rgba(255,255,255,.75);
    margin: 4px 0 0;
    font-size: 13px;
}
.tt-page-header .btn {
    border-color: rgba(255,255,255,.3);
    color: #fff;
}
.tt-page-header .btn:hover {
    background: rgba(255,255,255,.15);
    border-color: rgba(255,255,255,.5);
}

/* ── Cards ────────────────────────────────────────── */
.tt-card {
    background: #fff;
    border: 1px solid var(--tt-border);
    border-radius: var(--tt-radius);
    box-shadow: var(--tt-shadow);
    overflow: hidden;
}
.tt-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--tt-border);
    background: var(--tt-surface);
    flex-wrap: wrap;
    gap: 8px;
}
.tt-card-header h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1E293B;
}
.tt-card-body {
    padding: 20px;
}

/* ── Setting cards list ───────────────────────────── */
.setting-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border: 1px solid var(--tt-border);
    border-radius: 10px;
    background: #fff;
    transition: all 0.18s ease;
    margin-bottom: 10px;
    cursor: pointer;
}
.setting-card:hover {
    border-color: var(--tt-blue);
    box-shadow: 0 0 0 3px rgba(21,101,192,.08);
    transform: translateY(-1px);
}
.setting-card:last-child {
    margin-bottom: 0;
}
.setting-card .sc-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, #E3F2FD, #EDE7F6);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.setting-card .sc-icon i {
    font-size: 20px;
    color: var(--tt-blue);
}
.setting-card .sc-body {
    flex: 1;
    margin: 0 14px;
    min-width: 0;
}
.setting-card .sc-body .sc-title {
    font-size: 14px;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 2px;
}
.setting-card .sc-body .sc-meta {
    font-size: 12px;
    color: #64748B;
    word-break: break-word;
}
.setting-card .sc-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

/* ── Tabs ─────────────────────────────────────────── */
.tt-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--tt-border);
    margin-bottom: 24px;
    overflow-x: auto;
    flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch;
}
.tt-tab {
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: #64748B;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.15s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    background: none;
    border-top: none;
    border-left: none;
    border-right: none;
    flex-shrink: 0;
}
.tt-tab:hover {
    color: var(--tt-blue);
    background: rgba(21,101,192,.04);
}
.tt-tab.active {
    color: var(--tt-blue);
    border-bottom-color: var(--tt-blue);
    font-weight: 600;
}
.tt-tab .tab-badge {
    font-size: 10px;
    padding: 1px 6px;
    background: #EF4444;
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
}

/* ── Timetable grid ───────────────────────────────── */
.tt-grid-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.tt-grid {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}
.tt-grid th {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 12px 10px;
    text-align: center;
    white-space: nowrap;
}
.tt-grid th.period-th {
    background: #1E293B;
    color: #fff;
    width: 100px;
    text-align: left;
    padding-left: 14px;
}
.tt-grid th.monday-th    { background: var(--tt-blue);   color: #fff; }
.tt-grid th.tuesday-th   { background: var(--tt-purple); color: #fff; }
.tt-grid th.wednesday-th { background: var(--tt-green);  color: #fff; }
.tt-grid th.thursday-th  { background: var(--tt-orange); color: #fff; }
.tt-grid th.friday-th    { background: var(--tt-pink);   color: #fff; }
.tt-grid td {
    border: 1px solid var(--tt-border);
    vertical-align: middle;
    padding: 0;
    transition: all 0.15s;
}
.tt-grid td.period-td {
    background: var(--tt-surface);
    padding: 10px 14px;
    min-width: 100px;
}
.tt-grid .period-td .pname {
    font-size: 12px;
    font-weight: 700;
    color: #1E293B;
}
.tt-grid .period-td .ptime {
    font-size: 11px;
    color: #94A3B8;
    margin-top: 2px;
}

.tt-cell {
    cursor: pointer;
    padding: 8px;
    min-height: 68px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    transition: all 0.15s;
    min-width: 80px;
}
.tt-cell:hover {
    background: rgba(21,101,192,.06) !important;
}
.tt-cell.is-free {
    background: #FAFAFA;
}
.tt-cell.is-double {
    background: rgba(21,101,192,.05);
}
.tt-cell.is-break {
    background: #FFFBEB;
    cursor: default;
}
.tt-cell.is-break:hover {
    background: #FFFBEB !important;
}
.tt-cell .cell-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.8);
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
    margin-bottom: 5px;
}
.tt-cell .cell-avatar-placeholder {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E3F2FD, #EDE7F6);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 5px;
}
.tt-cell .cell-avatar-placeholder i {
    font-size: 16px;
    color: var(--tt-blue);
}
.tt-cell .cell-subject {
    font-size: 11px;
    font-weight: 700;
    color: #1E293B;
    line-height: 1.3;
}
.tt-cell .cell-teacher {
    font-size: 10px;
    color: #64748B;
    margin-top: 1px;
}
.tt-cell .cell-room {
    font-size: 10px;
    color: #94A3B8;
    margin-top: 1px;
}
.tt-cell .cell-room i {
    font-size: 9px;
    margin-right: 2px;
}
.tt-cell .cell-free {
    font-size: 11px;
    color: #CBD5E1;
}
.tt-cell .cell-break {
    font-size: 11px;
    color: #D97706;
    font-weight: 600;
}
.tt-cell .cell-double-badge {
    font-size: 9px;
    padding: 1px 5px;
    background: rgba(21,101,192,.12);
    color: var(--tt-blue);
    border-radius: 4px;
    font-weight: 700;
    margin-top: 3px;
}
.tt-cell.has-subject {
    border-left: 3px solid;
}

/* ── Constraints table ────────────────────────────── */
#constraintsTable {
    font-size: 13px;
}
#constraintsTable td {
    vertical-align: middle;
    padding: 8px 6px;
}
#constraintsTable input[type="number"] {
    width: 70px;
}
#constraintsTable select[multiple] {
    min-height: 50px;
    font-size: 12px;
}

/* ── Conflict items ───────────────────────────────── */
.conflict-item {
    border: 1px solid #FEE2E2;
    background: #FFF5F5;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.conflict-item.room-conflict {
    border-color: #FED7AA;
    background: #FFF7ED;
}
.conflict-item:last-child {
    margin-bottom: 0;
}
.conflict-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.conflict-avatar-ph {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #FEE2E2;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.conflict-avatar-ph.room {
    background: #FED7AA;
}
.conflict-avatar-ph i {
    color: #EF4444;
}
.conflict-avatar-ph.room i {
    color: #EA580C;
}

/* ── Real-time conflict panel ─────── */
.rtc-panel {
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 8px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 12px;
    animation: rtcSlideIn 0.2s ease;
}
.rtc-panel:last-child {
    margin-bottom: 0;
}
@keyframes rtcSlideIn {
    from { opacity:0; transform:translateY(-6px); }
    to { opacity:1; transform:translateY(0); }
}
.rtc-error   { background: #FFF1F2; border: 1px solid #FECDD3; }
.rtc-warning { background: #FFFBEB; border: 1px solid #FDE68A; }
.rtc-clear   { background: #F0FDF4; border: 1px solid #BBF7D0; }
.rtc-icon    { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
.rtc-body    { flex: 1; min-width: 0; }
.rtc-msg     { font-weight: 600; color: #1E293B; margin-bottom: 4px; line-height: 1.4; }
.rtc-msg.green { color: #15803d; }
.rtc-detail  { color: #64748B; font-size: 11px; margin-bottom: 6px; }
.rtc-alts    { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
.rtc-alt-badge {
    font-size: 10px;
    padding: 3px 8px;
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.rtc-alt-badge:hover {
    background: #16a34a;
    color: #fff;
    border-color: #16a34a;
}
.rtc-room-alt {
    font-size: 10px;
    padding: 3px 8px;
    background: #EFF6FF;
    color: #1565C0;
    border: 1px solid #BFDBFE;
    border-radius: 6px;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.rtc-room-alt:hover {
    background: #1565C0;
    color: #fff;
}
.rtc-spinner {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #64748B;
    padding: 10px 0;
}
.rtc-spinner .spinner-border {
    width: 14px;
    height: 14px;
    border-width: 2px;
}

/* ── Conflict suggestion box ─────── */
.conflict-suggestion {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    margin-top: 8px;
}
.conflict-suggestion .alt-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 6px;
}
.alt-badge {
    font-size: 11px;
    padding: 4px 8px;
    background: #dcfce7;
    color: #15803d;
    border-radius: 6px;
    cursor: pointer;
    border: 1px solid #bbf7d0;
    transition: all .15s;
}
.alt-badge:hover {
    background: #16a34a;
    color: #fff;
}

/* ── Export buttons ───────────────────────────────── */
.export-group {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

/* ── Tom Select overrides ─────── */
.ts-wrapper .ts-control {
    border-color: #D1D5DB;
    border-radius: 6px;
    min-height: 38px;
    font-size: 14px;
}
.ts-wrapper.focus .ts-control {
    border-color: #1565C0;
    box-shadow: 0 0 0 3px rgba(21,101,192,.12);
}
.ts-dropdown {
    font-size: 13px;
}
.ts-dropdown .option {
    padding: 8px 12px;
}
.ts-dropdown .option:hover,
.ts-dropdown .option.active {
    background: #EFF6FF;
    color: #1565C0;
}

/* ── Editing presence banner ──────────────────────── */
#editingBanner {
    border: 1px solid #FDE68A;
    background: #FFFBEB;
    color: #92400E;
    border-radius: 8px;
    padding: 10px 16px;
}

/* ── Periods table ────────────────────────────────── */
#periodsTable .form-control-sm,
#periodsTable .form-select-sm {
    font-size: 13px;
    padding: 4px 8px;
}
#periodsTable td {
    padding: 6px 4px;
    vertical-align: middle;
}
#periodsTable .period-order {
    font-size: 13px;
    font-weight: 600;
    color: #94A3B8;
}

/* ── Modal styles ────────────────────────────────── */
.modal-content {
    border-radius: 14px;
    overflow: hidden;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
.modal-header.bg-gradient-primary {
    background: linear-gradient(135deg, #1565C0, #6A1B9A);
}
.modal-header .modal-title {
    color: #fff;
}
.modal-header .btn-close-white {
    filter: brightness(0) invert(1);
}

/* ── Half days rows ──────────────────────────────── */
.wiz-half-day-row {
    background: var(--tt-surface);
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--tt-border);
}

/* ── Utility ──────────────────────────────────────── */
.cursor-pointer {
    cursor: pointer;
}
.flex-1 {
    flex: 1;
}
.opacity-30 {
    opacity: 0.3;
}
.opacity-50 {
    opacity: 0.5;
}
.bg-success-subtle {
    background: #DCFCE7;
}
.text-success {
    color: #15803d;
}
.bg-warning-subtle {
    background: #FEF3C7;
}
.text-warning {
    color: #D97706;
}
.bg-primary-subtle {
    background: #EFF6FF;
}
.text-primary {
    color: #1565C0;
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 768px) {
    .tt-page-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .tt-page-header .d-flex {
        justify-content: center;
    }
    .tt-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .tt-tab {
        font-size: 12px;
        padding: 8px 12px;
    }
    .export-group {
        flex-wrap: wrap;
        justify-content: center;
    }
    .tt-card-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .tt-card-header .d-flex {
        justify-content: center;
    }
    .setting-card {
        flex-wrap: wrap;
        gap: 8px;
    }
    .setting-card .sc-actions {
        width: 100%;
        justify-content: flex-end;
    }
    .tt-grid-wrapper {
        margin: 0 -12px;
        padding: 0 12px;
    }
    #constraintsTable {
        font-size: 12px;
    }
    #constraintsTable input[type="number"] {
        width: 50px;
    }
    #constraintsTable select[multiple] {
        min-height: 40px;
        font-size: 11px;
    }
    .conflict-item {
        flex-direction: column;
        align-items: stretch;
    }
    .conflict-avatar,
    .conflict-avatar-ph {
        align-self: center;
    }
}

@media (max-width: 576px) {
    .tt-page-header {
        padding: 16px 18px;
        border-radius: 12px;
    }
    .tt-page-header h4 {
        font-size: 17px;
    }
    .tt-card-body {
        padding: 14px;
    }
    .setting-card {
        padding: 10px 12px;
    }
    .tt-grid td.period-td {
        padding: 6px 8px;
        min-width: 70px;
    }
    .tt-cell {
        min-height: 50px;
        padding: 4px;
        min-width: 60px;
    }
    .tt-cell .cell-subject {
        font-size: 10px;
    }
    .tt-cell .cell-avatar {
        width: 28px;
        height: 28px;
    }
    .tt-cell .cell-avatar-placeholder {
        width: 28px;
        height: 28px;
    }
    .tt-cell .cell-avatar-placeholder i {
        font-size: 13px;
    }
}
</style>


@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid timetable-container">

    {{-- Page Header --}}
    <div class="tt-page-header">
        <div>
            <h4><i class="ri-calendar-todo-line me-2"></i>Timetable Management</h4>
            <p>Create, manage, and export class timetables for your school.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('timetable.teacher') }}" class="btn btn-light btn-sm">
                <i class="ri-user-line me-1"></i>My Timetable
            </a>
            <button class="btn btn-outline-light btn-sm" onclick="openGenerationWizardModal()">
                <i class="ri-magic-line me-1"></i>Generation Wizard
            </button>
            <button class="btn btn-outline-light btn-sm" onclick="openWholeSchoolExportModal()">
                <i class="ri-school-line me-1"></i>Whole School
            </button>
            <a href="{{ route('timetable.reports.index') }}" class="btn btn-outline-light btn-sm">
                <i class="ri-bar-chart-2-line me-1"></i>Reports
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <i class="ri-error-warning-line me-2"></i><strong>Validation Error:</strong>
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Selection + Existing timetables --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="tt-card h-100">
                <div class="tt-card-header">
                    <h6><i class="ri-add-circle-line me-2 text-primary"></i>Load / Create Timetable</h6>
                </div>
                <div class="tt-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Class</label>
                            <select class="form-select" id="classSelect">
                                <option value="">— Select Class —</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">
                                        {{ $class->schoolclass }}{{ $class->arm_name ? ' ' . $class->arm_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Session</label>
                            <select class="form-select" id="sessionSelect">
                                <option value="">— Select Session —</option>
                                @foreach ($schoolsessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->session }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Term <span class="text-muted fw-normal">(optional)</span></label>
                            <select class="form-select" id="termSelect">
                                <option value="">All Terms</option>
                                @foreach ($schoolterms as $term)
                                    <option value="{{ $term->id }}">{{ $term->term }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100" onclick="loadOrCreateSetting()">
                                <i class="ri-settings-4-line me-2"></i>Load / Create Timetable
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="tt-card h-100">
                <div class="tt-card-header">
                    <h6><i class="ri-history-line me-2 text-success"></i>Existing Timetables
                        <span class="badge bg-success-subtle text-success ms-2">{{ $settings->count() }}</span>
                    </h6>
                </div>
                <div class="tt-card-body" style="max-height:280px;overflow-y:auto">
                    @forelse ($settings as $setting)
                    <div class="setting-card" onclick="loadSetting({{ $setting->id }})" data-updated-at="{{ $setting->updated_at->toISOString() }}">
                        <div class="sc-icon"><i class="ri-school-line"></i></div>
                        <div class="sc-body">
                            <div class="sc-title">{{ $setting->resolved_class_name ?: 'Unknown Class' }}</div>
                            <div class="sc-meta">
                                <span>{{ $setting->session->session ?? '—' }}</span>
                                @if($setting->term)
                                    <span class="mx-1">·</span><span>{{ $setting->term->term }}</span>
                                @endif
                                <span class="mx-1">·</span>
                                <span class="text-muted">Updated {{ $setting->updated_at->diffForHumans() }}</span>
                                @if($setting->creator)
                                    <span class="mx-1">·</span>
                                    <span class="text-muted">by {{ $setting->creator->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="sc-actions" onclick="event.stopPropagation()">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSetting({{ $setting->id }})" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="cloneSetting({{ $setting->id }})" title="Clone">
                                <i class="ri-file-copy-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSetting({{ $setting->id }}, '{{ $setting->updated_at->toISOString() }}')" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="ri-calendar-line ri-3x d-block mb-3 opacity-30"></i>
                        <p>No timetables yet. Create your first one.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TIMETABLE EDITOR ===== --}}
    <div id="timetableEditor" style="display:none">
        <div class="tt-card">
            <div class="tt-card-header" style="background:linear-gradient(135deg,#EFF6FF,#F5F3FF)">
                <div>
                    <h6 id="editorContext" class="mb-0"><i class="ri-school-line me-2 text-primary"></i>Loading…</h6>
                    <small class="text-muted" id="editorSubContext"></small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary" onclick="closeEditor()">
                        <i class="ri-arrow-go-back-line me-1"></i>Close Editor
                    </button>
                </div>
            </div>

            <div class="tt-card-body">
                <div id="editingBanner" class="alert d-flex align-items-center gap-2 mb-3" style="display:none">
                    <i class="ri-user-shared-line ri-lg"></i>
                    <span id="editingBannerText"></span>
                </div>
                <div class="tt-tabs" role="tablist">
                    <button class="tt-tab active" onclick="showTab('periodsTab', this)">
                        <i class="ri-time-line"></i> Periods &amp; Settings
                    </button>
                    <button class="tt-tab" onclick="showTab('constraintsTab', this)">
                        <i class="ri-bar-chart-2-line"></i> Constraints
                    </button>
                    <button class="tt-tab" onclick="showTab('gridTab', this); loadTimetableGrid()">
                        <i class="ri-table-line"></i> Grid
                    </button>
                    <button class="tt-tab" onclick="showTab('conflictsTab', this)">
                        <i class="ri-alert-line"></i> Conflicts
                        <span class="tab-badge" id="conflictBadgeTab" style="display:none">!</span>
                    </button>
                </div>

                {{-- ── TAB: Periods & Settings ── --}}
                <div id="periodsTab" class="tab-content-pane">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="tt-card border">
                                <div class="tt-card-header"><h6><i class="ri-settings-3-line me-2"></i>Day Settings</h6></div>
                                <div class="tt-card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Day Start</label>
                                            <input type="time" class="form-control" id="schoolDayStart">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Day End</label>
                                            <input type="time" class="form-control" id="schoolDayEnd">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Period (min)</label>
                                            <input type="number" class="form-control" id="periodDuration" min="20" max="90">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Short Break (min)</label>
                                            <input type="number" class="form-control" id="shortBreakDuration" min="5" max="60">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Long Break (min)</label>
                                            <input type="number" class="form-control" id="longBreakDuration" min="10" max="90">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Active Days</label>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day)
                                                <label class="d-flex align-items-center gap-1 cursor-pointer" style="font-size:13px">
                                                    <input class="form-check-input active-day-checkbox mt-0" type="checkbox" value="{{ $day }}" id="day_{{ $day }}">
                                                    {{ $day }}
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="tt-card border">
                                <div class="tt-card-header">
                                    <h6><i class="ri-list-check-2-line me-2"></i>Period Schedule</h6>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-info" onclick="openAnchorRebuildPanel()">
                                            <i class="ri-flashlight-line"></i> Rebuild
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="addPeriodRow()">
                                            <i class="ri-add-line"></i> Add
                                        </button>
                                    </div>
                                </div>
                                <div class="tt-card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0" id="periodsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:36px">#</th>
                                                    <th>Name</th>
                                                    <th style="width:140px">Type</th>
                                                    <th style="width:44px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="periodsBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tt-card-header border-top-0 border-bottom-0" style="border-top:1px solid var(--tt-border)">
                                    <div></div>
                                    <button class="btn btn-success" onclick="saveSettings()">
                                        <i class="ri-save-line me-2"></i>Save Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── TAB: Constraints ── --}}
                <div id="constraintsTab" class="tab-content-pane" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="mb-1">Subject Constraints</h6>
                            <p class="text-muted mb-0" style="font-size:13px">Define how many times per week each subject is taught and preferred scheduling rules.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-success" onclick="saveConstraints()">
                                <i class="ri-save-line me-2"></i>Save
                            </button>
                            <button class="btn btn-primary" onclick="generateTimetable()">
                                <i class="ri-magic-line me-2"></i>Auto-Generate
                            </button>
                        </div>
                    </div>
                    <div class="tt-card border">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="constraintsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subject</th><th>Teacher</th><th>Periods / Week</th>
                                        <th>Allow Double</th><th>Max Doubles</th>
                                        <th>Preferred Days</th><th>Avoid Days</th><th>Compulsory</th>
                                    </tr>
                                </thead>
                                <tbody id="constraintsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ── TAB: Grid ── --}}
                <div id="gridTab" class="tab-content-pane" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="mb-1">Weekly Timetable Grid</h6>
                            <p class="text-muted mb-0" style="font-size:13px">Click any cell to assign a subject and teacher.</p>
                        </div>
                        <div class="export-group">
                            <select id="exportOrientation" class="form-select form-select-sm" style="width:auto">
                                <option value="horizontal">Horizontal Layout</option>
                                <option value="vertical">Vertical Layout</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary" onclick="loadTimetableGrid()">
                                <i class="ri-refresh-line me-1"></i>Refresh
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="exportTimetable('csv')">
                                <i class="ri-file-excel-line me-1"></i>CSV
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="exportTimetable('pdf')">
                                <i class="ri-file-pdf-line me-1"></i>PDF
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="sendNotifications()">
                                <i class="ri-mail-send-line me-1"></i>Notify
                            </button>
                        </div>
                    </div>
                    <div class="tt-card border">
                        <div class="tt-grid-wrapper" id="timetableGridContainer">
                            <div class="text-center py-5 text-muted">
                                <i class="ri-table-line ri-3x d-block mb-3 opacity-30"></i>
                                <p>Select the <strong>Timetable Grid</strong> tab to load the schedule.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── TAB: Conflicts ── --}}
                <div id="conflictsTab" class="tab-content-pane" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="mb-1">Conflict Checker</h6>
                            <p class="text-muted mb-0" style="font-size:13px">
                                Detects teacher double-booking and room conflicts across <strong>all classes</strong>.
                            </p>
                        </div>
                        <button class="btn btn-primary" onclick="checkConflicts()">
                            <i class="ri-search-line me-2"></i>Run Check
                        </button>
                    </div>
                    <div id="conflictCheckedAt" class="text-muted mb-2" style="font-size:12px;display:none">
                        <i class="ri-time-line me-1"></i><span id="conflictCheckedAtText"></span>
                    </div>
                    <div id="conflictsList">
                        <div class="text-center py-5 text-muted">
                            <i class="ri-check-double-line ri-3x d-block mb-3 text-success opacity-50"></i>
                            <p>Click <strong>Run Conflict Check</strong> to validate teacher and room assignments across all classes.</p>
                        </div>
                    </div>
                </div>

            </div>{{-- /card-body --}}
        </div>{{-- /tt-card --}}
    </div>{{-- /timetableEditor --}}

</div>
</div>
</div>

{{-- ============================================================ --}}
{{-- EDIT SLOT MODAL                                              --}}
{{-- ============================================================ --}}
<div class="modal fade" id="editSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary pb-0" style="padding:20px 24px 0">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div id="editTeacherAvatar" style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ri-user-line text-white ri-xl"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-0" style="font-size:15px">Edit Timetable Slot</h5>
                        <small class="text-white opacity-75" id="editSlotContext">—</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto mt-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="w-100 mt-3 d-flex gap-2 pb-0">
                    <div class="flex-1 px-2 py-2 rounded-top" style="background:rgba(255,255,255,.1)">
                        <div class="text-white opacity-60" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px">Period</div>
                        <div class="text-white fw-semibold" id="editSlotPeriodName" style="font-size:13px">—</div>
                    </div>
                    <div class="flex-1 px-2 py-2 rounded-top" style="background:rgba(255,255,255,.1)">
                        <div class="text-white opacity-60" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px">Day</div>
                        <div class="text-white fw-semibold" id="editSlotDayName" style="font-size:13px">—</div>
                    </div>
                </div>
            </div>

            <div class="modal-body" style="padding:20px 24px">
                <input type="hidden" id="editSlotSettingId">
                <input type="hidden" id="editSlotPeriodId">
                <input type="hidden" id="editSlotDay">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Subject</label>
                        <select class="form-select" id="editSlotSubject" onchange="onSubjectChange()">
                            <option value="">— Free Period —</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teacher</label>
                        <select class="form-select" id="editSlotTeacher" onchange="onTeacherChange()">
                            <option value="">— No Teacher —</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Room / Venue</label>
                        <select id="editSlotRoom" placeholder="Search or type a room…"></select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="editSlotIsDouble">
                            <label class="form-check-label" for="editSlotIsDouble">Double Period</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="editSlotNotes" rows="2" placeholder="Optional notes…"></textarea>
                    </div>
                </div>

                {{-- Real-time conflict panel --}}
                <div id="slotConflictPanel" style="display:none;margin-top:16px">
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748B;margin-bottom:8px">
                        <i class="ri-shield-check-line me-1"></i>Conflict Check
                    </div>
                    <div id="slotConflictInner"></div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0" style="padding:0 24px 20px">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="saveSlotBtn" onclick="saveSlot()">
                    <i class="ri-save-line me-2"></i>Save Slot
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Whole School Export Modal --}}
<div class="modal fade" id="wholeSchoolExportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-school-line me-2"></i>Export Whole School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size:13px">Export timetables for all classes in the selected session and term.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                    <select class="form-select" id="wholeSchoolSessionId">
                        <option value="">— Select Session —</option>
                        @foreach($schoolsessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Term <span class="text-muted fw-normal">(optional)</span></label>
                    <select class="form-select" id="wholeSchoolTermId">
                        <option value="">All Terms</option>
                        @foreach($schoolterms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Orientation</label>
                    <select class="form-select" id="wholeSchoolOrientation">
                        <option value="horizontal">Horizontal Layout (Days as columns)</option>
                        <option value="vertical">Vertical Layout (Days as rows)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="exportWholeSchoolTimetable()">
                    <i class="ri-file-pdf-line me-2"></i>Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Clone Modal --}}
<div class="modal fade" id="cloneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-file-copy-line me-2"></i>Clone Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size:13px">Copies all periods, constraints and slots. Optionally change session or term.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Session <span class="text-muted fw-normal">(optional)</span></label>
                    <select class="form-select" id="cloneSessionId">
                        <option value="">Same Session</option>
                        @foreach($schoolsessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label fw-semibold">New Term <span class="text-muted fw-normal">(optional)</span></label>
                    <select class="form-select" id="cloneTermId">
                        <option value="">Same Term</option>
                        @foreach($schoolterms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="confirmClone()"><i class="ri-file-copy-line me-2"></i>Clone</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- GENERATION WIZARD MODAL                                      --}}
{{-- ============================================================ --}}
<div class="modal fade" id="generationWizardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content" style="border-radius:14px;overflow:hidden">
      <div class="modal-header" style="background:linear-gradient(135deg,#1565C0,#6A1B9A)">
        <h5 class="modal-title text-white"><i class="ri-magic-line me-2"></i>Generation Wizard</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height:70vh;overflow-y:auto">
        <p class="text-muted" style="font-size:13px">Set up the day structure for many classes at once, then optionally auto-generate timetables for all of them.</p>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
            <select class="form-select" id="wizSessionId">
              <option value="">— Select —</option>
              @foreach($schoolsessions as $session)
                <option value="{{ $session->id }}">{{ $session->session }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Term <span class="text-muted fw-normal">(optional)</span></label>
            <select class="form-select" id="wizTermId">
              <option value="">All Terms</option>
              @foreach($schoolterms as $term)
                <option value="{{ $term->id }}">{{ $term->term }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Scope</label>
            <select class="form-select" id="wizScope" onchange="toggleWizardClassPicker()">
              <option value="all">All Classes (with subjects assigned)</option>
              <option value="selected">Selected Classes Only</option>
            </select>
          </div>
          <div class="col-12" id="wizClassPickerWrap" style="display:none">
            <label class="form-label fw-semibold">Classes</label>
            <select class="form-select" id="wizClassIds" multiple size="6">
              @foreach($schoolclasses as $class)
                <option value="{{ $class->id }}">{{ $class->schoolclass }}{{ $class->arm_name ? ' '.$class->arm_name : '' }}</option>
              @endforeach
            </select>
            <small class="text-muted">Ctrl/Cmd-click to select multiple.</small>
          </div>
        </div>

        <hr>
        <h6 class="mb-3"><i class="ri-time-line me-2"></i>Day Structure</h6>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Day Start</label>
            <input type="time" class="form-control" id="wizDayStart" value="08:00">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Day End</label>
            <input type="time" class="form-control" id="wizDayEnd" value="14:30">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Lessons / Day</label>
            <input type="number" class="form-control" id="wizLessonsPerDay" min="1" max="12" value="8">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Period Length (min)</label>
            <input type="number" class="form-control" id="wizPeriodDuration" min="20" max="90" value="40">
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Short Break After Period</label>
            <input type="number" class="form-control" id="wizShortBreakAfter" min="1" placeholder="e.g. 2">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Short Break (min)</label>
            <input type="number" class="form-control" id="wizShortBreakDuration" min="5" max="60" value="20">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Long Break After Period</label>
            <input type="number" class="form-control" id="wizLongBreakAfter" min="1" placeholder="e.g. 4">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Long Break (min)</label>
            <input type="number" class="form-control" id="wizLongBreakDuration" min="10" max="90" value="40">
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="wizAssemblyFirstPeriod" onchange="toggleWizardAssemblyDay()">
              <label class="form-check-label" for="wizAssemblyFirstPeriod">Assembly as First Period</label>
            </div>
          </div>
          <div class="col-md-3" id="wizAssemblyDayWrap" style="display:none">
            <label class="form-label fw-semibold">Assembly Day</label>
            <select class="form-select" id="wizAssemblyDay">
              @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day)
                <option value="{{ $day }}">{{ $day }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Free Periods / Week</label>
            <input type="number" class="form-control" id="wizFreePeriods" min="0" value="0">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Max Lessons / Day <span class="text-muted fw-normal">(optional)</span></label>
            <input type="number" class="form-control" id="wizMaxLessonsPerDay" min="1" placeholder="No cap">
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Active Days</label>
            <div class="d-flex flex-wrap gap-3 mt-1">
              @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day)
                <label class="d-flex align-items-center gap-1" style="font-size:13px">
                  <input class="form-check-input wiz-active-day mt-0" type="checkbox" value="{{ $day }}" checked>
                  {{ $day }}
                </label>
              @endforeach
            </div>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="wizDeprioritizeBreakAdjacent" checked>
              <label class="form-check-label" for="wizDeprioritizeBreakAdjacent">
                Deprioritize periods next to a break when auto-generating
              </label>
            </div>
          </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><i class="ri-calendar-event-line me-2"></i>Half-Days
                <span class="text-muted fw-normal" style="font-size:12px">(optional — cap lessons on specific days)</span>
            </h6>
            <button class="btn btn-sm btn-outline-primary" onclick="addWizardHalfDayRow()"><i class="ri-add-line"></i> Add</button>
        </div>
        <div id="wizHalfDaysBody"></div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-outline-primary" onclick="submitGenerationWizard(false)">
            <i class="ri-save-line me-1"></i>Apply Structure Only
        </button>
        <button class="btn btn-primary" onclick="submitGenerationWizard(true)">
            <i class="ri-magic-line me-1"></i>Apply &amp; Generate
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- ANCHOR-BASED QUICK REBUILD MODAL (single class)              --}}
{{-- ============================================================ --}}
<div class="modal fade" id="anchorRebuildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;overflow:hidden">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-flashlight-line me-2"></i>Quick Rebuild Periods</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" style="font-size:13px">Rebuilds this class's period list from lesson count + break/assembly anchors, replacing manually-edited rows.</p>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label fw-semibold">Day Start</label>
            <input type="time" class="form-control" id="arDayStart" value="08:00">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Lessons / Day</label>
            <input type="number" class="form-control" id="arLessonsPerDay" min="1" max="12" value="8">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Period Length (min)</label>
            <input type="number" class="form-control" id="arPeriodDuration" min="20" max="90" value="40">
          </div>
          <div class="col-6"></div>
          <div class="col-6">
            <label class="form-label fw-semibold">Short Break After Period</label>
            <input type="number" class="form-control" id="arShortBreakAfter" min="1" placeholder="e.g. 2">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Short Break (min)</label>
            <input type="number" class="form-control" id="arShortBreakDuration" min="5" max="60" value="20">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Long Break After Period</label>
            <input type="number" class="form-control" id="arLongBreakAfter" min="1" placeholder="e.g. 4">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Long Break (min)</label>
            <input type="number" class="form-control" id="arLongBreakDuration" min="10" max="90" value="40">
          </div>
          <div class="col-6 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="arAssemblyEnabled" onchange="document.getElementById('arAssemblyDayWrap').style.display=this.checked?'':'none'">
              <label class="form-check-label" for="arAssemblyEnabled">Assembly First Period</label>
            </div>
          </div>
          <div class="col-6" id="arAssemblyDayWrap" style="display:none">
            <label class="form-label fw-semibold">Assembly Day</label>
            <select class="form-select" id="arAssemblyDay">
              @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day)
                <option value="{{ $day }}">{{ $day }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="submitAnchorRebuild()"><i class="ri-flashlight-line me-1"></i>Rebuild</button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// GLOBALS
// ============================================================================
let currentSettingId  = null;
let currentSetting    = null;
let currentSettingVersion = null;
let editingHeartbeatTimer = null;
let currentPeriods    = [];
let currentGrid       = {};
let currentDays       = [];
let availableSubjects = [];
let allTeachers       = [];
let availableRooms    = [];
let pendingCloneId    = null;
let roomTomSelect     = null;
let conflictCheckTimer = null;

const SUBJECT_COLORS = ['#3B82F6','#8B5CF6','#10B981','#F59E0B','#EF4444','#06B6D4','#F97316','#EC4899','#14B8A6','#84CC16'];
const subjectColorMap = {};
let colorSeq = 0;

function getSubjectColor(subjectId) {
    if (!subjectId) return null;
    if (!subjectColorMap[subjectId]) subjectColorMap[subjectId] = SUBJECT_COLORS[colorSeq++ % SUBJECT_COLORS.length];
    return subjectColorMap[subjectId];
}

const ROUTES = {
    setup:                      '{{ route("timetable.setup") }}',
    saveSettings:               '{{ route("timetable.save-settings") }}',
    saveConstraints:            '{{ route("timetable.save-constraints") }}',
    autoGenerate:               '{{ route("timetable.auto-generate") }}',
    saveSlot:                   '{{ route("timetable.save-slot") }}',
    sendNotifications:          '{{ route("timetable.send-notifications") }}',
    cloneSetting:               '{{ route("timetable.clone-setting") }}',
    getSetting:                 '{{ url("/timetable/get-setting") }}',
    getGrid:                    '{{ url("/timetable/get-grid") }}',
    checkConflicts:             '{{ url("/timetable/check-conflicts") }}',
    checkSlotConflict:          '{{ route("timetable.check-slot-conflict") }}',
    export:                     '{{ url("/timetable/export") }}',
    deleteSetting:              '{{ url("/timetable/delete-setting") }}',
    exportWholeSchool:          '{{ route("timetable.export-whole-school") }}',
    heartbeat:                  '{{ url("/timetable/heartbeat") }}',
    releaseEditing:             '{{ url("/timetable/release-editing") }}',
    applyGenerationTemplate:    '{{ route("timetable.apply-generation-template") }}',
    autoGenerateWholeSchool:    '{{ route("timetable.auto-generate-whole-school") }}',
    rebuildPeriodsFromAnchors:  '{{ route("timetable.rebuild-periods-from-anchors") }}',
    saveHalfDays:               '{{ route("timetable.save-half-days") }}',
};
const CSRF = '{{ csrf_token() }}';

function url(base, id) { return base.replace(/\/$/, '') + '/' + id; }

// ============================================================================
// UTILITIES
// ============================================================================
function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}

function apiFetch(endpoint, method = 'GET', body = null) {
    const opts = { method, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } };
    if (body && method !== 'GET') { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    return fetch(endpoint, opts);
}

function showLoader() {
    Swal.fire({ title: 'Processing…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
}
function hideLoader() { Swal.close(); }

function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content-pane').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tt-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(tabId).style.display = '';
    if (btn) btn.classList.add('active');
}

function closeEditor() {
    stopEditingHeartbeat();
    document.getElementById('timetableEditor').style.display = 'none';
    currentSettingId = null;
    currentSettingVersion = null;
}

// ============================================================================
// LOAD / CREATE
// ============================================================================
async function loadOrCreateSetting() {
    const classId   = document.getElementById('classSelect').value;
    const sessionId = document.getElementById('sessionSelect').value;
    const termId    = document.getElementById('termSelect').value || null;
    if (!classId || !sessionId) return Swal.fire('Required', 'Please select Class and Session.', 'warning');

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.setup, 'POST', { schoolclass_id: classId, session_id: sessionId, term_id: termId });
        const data = await res.json();
        if (data.success) {
            await loadSetting(data.setting_id); // loadSetting manages its own loader lifecycle
        } else {
            hideLoader();
            Swal.fire('Error', data.message || 'Failed', 'error');
        }
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

async function loadSetting(settingId) {
    showLoader();
    try {
        const res  = await apiFetch(url(ROUTES.getSetting, settingId), 'GET');
        const data = await res.json();
        if (!data.success) {
            hideLoader();
            Swal.fire('Error', 'Failed to load timetable: ' + (data.message || 'Failed to load'), 'error');
            return;
        }

        currentSettingId      = settingId;
        currentSetting        = data.setting;
        currentSettingVersion = data.setting.updated_at;
        availableSubjects     = data.available_subjects || [];

        if (data.editing_info) {
            document.getElementById('editingBanner').style.display = '';
            document.getElementById('editingBannerText').textContent =
                `${data.editing_info.user_name} is also editing this timetable (since ${data.editing_info.since}).`;
        } else {
            document.getElementById('editingBanner').style.display = 'none';
        }
        startEditingHeartbeat(settingId);

        const className   = (data.setting.schoolclass?.schoolclass || '')
            + (data.setting.schoolclass?.arm_name ? ' ' + data.setting.schoolclass.arm_name : '');
        const sessionName = data.setting.session?.session || '—';
        const termName    = data.setting.term?.term || 'All Terms';

        document.getElementById('editorContext').innerHTML    = `<i class="ri-school-line me-2 text-primary"></i>${escapeHtml(className || '—')}`;
        document.getElementById('editorSubContext').textContent = `${sessionName} · ${termName}`;

        document.getElementById('schoolDayStart').value     = (data.setting.school_day_start || '08:00').slice(0, 5);
        document.getElementById('schoolDayEnd').value       = (data.setting.school_day_end   || '14:30').slice(0, 5);
        document.getElementById('periodDuration').value     = data.setting.period_duration_minutes      || 40;
        document.getElementById('shortBreakDuration').value = data.setting.short_break_duration_minutes || 20;
        document.getElementById('longBreakDuration').value  = data.setting.long_break_duration_minutes  || 40;

        const activeDays = data.setting.active_days || ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        document.querySelectorAll('.active-day-checkbox').forEach(cb => cb.checked = activeDays.includes(cb.value));

        loadPeriodsIntoTable(data.setting.periods?.length ? data.setting.periods : [
            {name:'Period 1',type:'lesson'},{name:'Period 2',type:'lesson'},
            {name:'Short Break',type:'short_break'},{name:'Period 3',type:'lesson'},
            {name:'Period 4',type:'lesson'},{name:'Long Break',type:'long_break'},
            {name:'Period 5',type:'lesson'},{name:'Period 6',type:'lesson'},
        ]);

        loadConstraintsIntoTable(data.setting.constraints || []);

        hideLoader();
        document.getElementById('timetableEditor').style.display = '';
        document.getElementById('timetableEditor').scrollIntoView({ behavior: 'smooth', block: 'start' });
        showTab('periodsTab', document.querySelector('.tt-tab'));

    } catch (e) {
        hideLoader();
        Swal.fire('Error', 'Failed to load timetable: ' + e.message, 'error');
    }
}

// ============================================================================
// EDITING HEARTBEAT
// ============================================================================
function startEditingHeartbeat(settingId) {
    stopEditingHeartbeat();
    editingHeartbeatTimer = setInterval(() => {
        apiFetch(url(ROUTES.heartbeat, settingId), 'POST').catch(() => {});
    }, 60000);
}

function stopEditingHeartbeat() {
    if (editingHeartbeatTimer) {
        clearInterval(editingHeartbeatTimer);
        editingHeartbeatTimer = null;
    }
    if (currentSettingId) {
        apiFetch(url(ROUTES.releaseEditing, currentSettingId), 'POST').catch(() => {});
    }
}

window.addEventListener('beforeunload', stopEditingHeartbeat);

// ============================================================================
// PERIODS
// ============================================================================
function loadPeriodsIntoTable(periods) {
    document.getElementById('periodsBody').innerHTML = '';
    periods.forEach((p, i) => addPeriodRow(p.name, p.type, i + 1));
}

function addPeriodRow(name = '', type = 'lesson', order = null) {
    const tbody  = document.getElementById('periodsBody');
    const rowNum = order ?? (tbody.querySelectorAll('tr').length + 1);
    const tr     = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-center fw-bold text-muted period-order">${rowNum}</td>
        <td><input type="text" class="form-control form-control-sm period-name" value="${escapeHtml(name)}" placeholder="e.g. Period 1"></td>
        <td>
            <select class="form-select form-select-sm period-type">
                ${['lesson','short_break','long_break','assembly','free'].map(v =>
                    `<option value="${v}" ${type===v?'selected':''}>${v.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>`
                ).join('')}
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove();reorderPeriods()">
                <i class="ri-delete-bin-line ri-lg"></i>
            </button>
        </td>`;
    tbody.appendChild(tr);
    reorderPeriods();
}

function reorderPeriods() {
    document.querySelectorAll('#periodsBody tr').forEach((tr, i) => {
        const cell = tr.querySelector('.period-order');
        if (cell) cell.textContent = i + 1;
    });
}

function getPeriodsFromTable() {
    return [...document.querySelectorAll('#periodsBody tr')].map(tr => ({
        name: tr.querySelector('.period-name')?.value?.trim(),
        type: tr.querySelector('.period-type')?.value,
    })).filter(p => p.name);
}

async function saveSettings() {
    const periods    = getPeriodsFromTable();
    const activeDays = [...document.querySelectorAll('.active-day-checkbox:checked')].map(cb => cb.value);
    if (!periods.length)    return Swal.fire('Error', 'Add at least one period.', 'error');
    if (!activeDays.length) return Swal.fire('Error', 'Select at least one active day.', 'error');

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.saveSettings, 'POST', {
            setting_id:                   currentSettingId,
            expected_updated_at:          currentSettingVersion,
            school_day_start:             document.getElementById('schoolDayStart').value,
            school_day_end:               document.getElementById('schoolDayEnd').value,
            period_duration_minutes:      parseInt(document.getElementById('periodDuration').value),
            short_break_duration_minutes: parseInt(document.getElementById('shortBreakDuration').value),
            long_break_duration_minutes:  parseInt(document.getElementById('longBreakDuration').value),
            active_days: activeDays, periods,
        });
        const data = await res.json();
        if (data.success) {
            currentSettingVersion = data.setting.updated_at;
            hideLoader();
            Swal.fire({ icon:'success', title:'Saved!', timer:1600, showConfirmButton:false });
            await loadSetting(currentSettingId);
        } else if (data.has_version_conflict) {
            hideLoader();
            handleVersionConflict(data);
        } else {
            hideLoader();
            Swal.fire('Error', data.message || 'Failed', 'error');
        }
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// CONSTRAINTS
// ============================================================================
function loadConstraintsIntoTable(constraints) {
    const tbody = document.getElementById('constraintsBody');
    tbody.innerHTML = '';
    if (!availableSubjects.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">
            <i class="ri-information-line ri-2x d-block mb-2"></i>No subjects assigned to this class.</td></tr>`;
        return;
    }
    const cMap = new Map(constraints.map(c => [c.subject_id, c]));
    availableSubjects.forEach(subj => {
        const c  = cMap.get(subj.subject_id) || {};
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="fw-semibold" style="font-size:13px">
                ${escapeHtml(subj.subject_name)}
                <input type="hidden" class="constraint-subject-id" value="${subj.subject_id}">
            </td>
            <td class="text-muted" style="font-size:12px">${escapeHtml(subj.teacher_name)}</td>
            <td><input type="number" class="form-control form-control-sm periods-per-week" value="${c.periods_per_week||2}" min="1" max="10" style="width:70px"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input allow-double" ${c.allow_double_period?'checked':''}></td>
            <td><input type="number" class="form-control form-control-sm max-double" value="${c.max_double_periods_per_week??1}" min="0" max="5" style="width:60px" ${!c.allow_double_period?'disabled':''}></td>
            <td><select class="form-select form-select-sm preferred-days" multiple size="3">${genDayOptions(c.preferred_days||[])}</select></td>
            <td><select class="form-select form-select-sm avoid-days" multiple size="3">${genDayOptions(c.avoid_days||[])}</select></td>
            <td class="text-center"><input type="checkbox" class="form-check-input is-compulsory" ${c.is_compulsory!==false?'checked':''}></td>
        `;
        tr.querySelector('.allow-double').addEventListener('change', function() {
            tr.querySelector('.max-double').disabled = !this.checked;
        });
        tbody.appendChild(tr);
    });
}

function genDayOptions(selected) {
    return ['Monday','Tuesday','Wednesday','Thursday','Friday']
        .map(d => `<option value="${d}" ${selected.includes(d)?'selected':''}>${d}</option>`).join('');
}

function getConstraintsFromTable() {
    return [...document.querySelectorAll('#constraintsBody tr')].map(tr => {
        const sid = tr.querySelector('.constraint-subject-id')?.value;
        if (!sid) return null;
        return {
            subject_id:       parseInt(sid),
            periods_per_week: parseInt(tr.querySelector('.periods-per-week').value),
            allow_double:     tr.querySelector('.allow-double').checked,
            max_double:       parseInt(tr.querySelector('.max-double').value),
            preferred_days:   [...tr.querySelector('.preferred-days').selectedOptions].map(o => o.value),
            avoid_days:       [...tr.querySelector('.avoid-days').selectedOptions].map(o => o.value),
            is_compulsory:    tr.querySelector('.is-compulsory').checked,
        };
    }).filter(Boolean);
}

async function saveConstraints() {
    const constraints = getConstraintsFromTable();
    if (!constraints.length) return Swal.fire('Error', 'No constraints to save.', 'error');
    showLoader();
    try {
        const res  = await apiFetch(ROUTES.saveConstraints, 'POST', { setting_id: currentSettingId, expected_updated_at: currentSettingVersion, constraints });
        const data = await res.json();
        if (data.success) {
            currentSettingVersion = data.updated_at;
            hideLoader();
            Swal.fire({ icon:'success', title:'Saved!', timer:1400, showConfirmButton:false });
        } else if (data.has_version_conflict) {
            hideLoader();
            handleVersionConflict(data);
        } else {
            hideLoader();
            Swal.fire('Error', data.message || 'Failed', 'error');
        }
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// AUTO-GENERATE
// ============================================================================
async function generateTimetable() {
    const result = await Swal.fire({
        title: 'Auto-Generate Timetable?',
        html: 'This will <strong>clear the existing timetable</strong> and generate a new one based on your constraints. The generator now respects teacher assignments across all classes.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#1565C0', confirmButtonText: 'Yes, generate!',
    });
    if (!result.isConfirmed) return;
    showLoader();
    try {
        const res  = await apiFetch(ROUTES.autoGenerate, 'POST', { setting_id: currentSettingId, expected_updated_at: currentSettingVersion });
        const data = await res.json();
        if (data.success) {
            currentSettingVersion = data.setting_updated_at || currentSettingVersion;
            await loadTimetableGrid();
            showTab('gridTab', document.querySelectorAll('.tt-tab')[2]);
            silentConflictCheck();
            hideLoader();
            Swal.fire({ icon:'success', title:'Generated!', timer:1800, showConfirmButton:false });
        } else if (data.has_version_conflict) {
            hideLoader();
            handleVersionConflict(data);
        } else {
            hideLoader();
            Swal.fire('Error', data.message || 'Failed', 'error');
        }
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// TIMETABLE GRID
// ============================================================================
async function loadTimetableGrid() {
    if (!currentSettingId) return;
    const container = document.getElementById('timetableGridContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3 text-muted">Loading timetable…</p></div>';
    try {
        const res  = await apiFetch(url(ROUTES.getGrid, currentSettingId), 'GET');
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed');
        currentPeriods = data.periods || [];
        currentGrid    = data.grid    || {};
        currentDays    = data.days    || ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        allTeachers    = data.teachers|| [];
        availableRooms = data.rooms   || [];
        updateRoomDropdown(availableRooms);
        renderGrid();
    } catch (e) {
        container.innerHTML = `<div class="alert alert-danger m-3">Failed to load grid: ${escapeHtml(e.message)}</div>`;
    }
}

function renderGrid() {
    const container = document.getElementById('timetableGridContainer');
    if (!currentPeriods.length) {
        container.innerHTML = '<div class="alert alert-warning m-3">No periods configured. Save settings first.</div>';
        return;
    }
    const dayThClasses = {Monday:'monday-th',Tuesday:'tuesday-th',Wednesday:'wednesday-th',Thursday:'thursday-th',Friday:'friday-th'};
    let html = `<table class="tt-grid"><thead><tr>
        <th class="period-th">Period</th>
        ${currentDays.map(d => `<th class="${dayThClasses[d]||''}">${escapeHtml(d)}</th>`).join('')}
    </tr></thead><tbody>`;

    currentPeriods.forEach(period => {
        const isBreak   = period.is_break || ['short_break','long_break'].includes(period.type);
        const startTime = (period.start_time || '').slice(0, 5);
        const endTime   = (period.end_time   || '').slice(0, 5);

        html += `<tr><td class="period-td">
            <div class="pname">${escapeHtml(period.name)}</div>
            <div class="ptime">${startTime} – ${endTime}</div>
        </td>`;

        currentDays.forEach(day => {
            const slot   = currentGrid[period.id]?.[day] || null;
            const isFree = !slot || slot.is_free || (!slot.subject_id && !slot.teacher_id);

            if (isBreak) {
                html += `<td><div class="tt-cell is-break"><span class="cell-break">☕ Break</span></div></td>`;
            } else if (isFree) {
                html += `<td onclick="openSlotModal(${period.id},'${day}')">
                    <div class="tt-cell is-free">
                        <i class="ri-add-line ri-lg text-muted opacity-30"></i>
                        <span class="cell-free">Free</span>
                    </div></td>`;
            } else {
                const sc          = getSubjectColor(slot.subject_id);
                const borderStyle = sc ? `style="border-left:3px solid ${sc}"` : '';
                const avatarHtml  = slot.teacher_picture
                    ? `<img src="${slot.teacher_picture}" class="cell-avatar" onerror="this.style.display='none'">`
                    : `<div class="cell-avatar-placeholder"><i class="ri-user-line"></i></div>`;
                const doubleBadge = slot.is_double ? '<span class="cell-double-badge">Double</span>' : '';
                const roomHtml    = slot.room_name
                    ? `<span class="cell-room"><i class="ri-door-line"></i> ${escapeHtml(slot.room_name)}</span>`
                    : '';
                const teacherHtml = slot.teacher
                    ? `<span class="cell-teacher">${escapeHtml(slot.teacher.split(' ')[0])}</span>`
                    : '';

                html += `<td onclick="openSlotModal(${period.id},'${day}')" ${borderStyle}>
                    <div class="tt-cell has-subject${slot.is_double?' is-double':''}">
                        ${avatarHtml}
                        <span class="cell-subject">${escapeHtml(slot.subject_code || slot.subject || '—')}</span>
                        ${teacherHtml}${roomHtml}${doubleBadge}
                    </div></td>`;
            }
        });
        html += '</tr>';
    });
    html += '</tbody></table>';
    container.innerHTML = html;
}

// ============================================================================
// ROOM DROPDOWN (Tom Select)
// ============================================================================
function updateRoomDropdown(rooms) {
    if (roomTomSelect) {
        roomTomSelect.destroy();
        roomTomSelect = null;
    }
    const el = document.getElementById('editSlotRoom');
    if (!el) return;

    roomTomSelect = new TomSelect(el, {
        valueField: 'id',
        labelField: 'label',
        searchField: ['label', 'name', 'code'],
        options: rooms,
        create: false,
        placeholder: 'Search or select a room…',
        onChange: function() { debounceConflictCheck(); }
    });
}

// ============================================================================
// EDIT SLOT MODAL
// ============================================================================
function openSlotModal(periodId, day) {
    const period = currentPeriods.find(p => p.id == periodId);
    if (!period) return;
    const slot = currentGrid[periodId]?.[day] || {};

    document.getElementById('editSlotSettingId').value = currentSettingId;
    document.getElementById('editSlotPeriodId').value  = periodId;
    document.getElementById('editSlotDay').value       = day;

    const startFmt = (period.start_time || '').slice(0, 5);
    const endFmt   = (period.end_time   || '').slice(0, 5);
    document.getElementById('editSlotPeriodName').textContent = period.name + ' · ' + startFmt + ' – ' + endFmt;
    document.getElementById('editSlotDayName').textContent    = day;
    document.getElementById('editSlotContext').textContent    = period.name + ' · ' + day;
    document.getElementById('editSlotNotes').value            = slot.notes || '';
    document.getElementById('editSlotIsDouble').checked       = slot.is_double || false;

    resetConflictPanel();

    if (roomTomSelect) {
        roomTomSelect.setValue(slot.room_id ? slot.room_id.toString() : '', true);
    }

    const avatarDiv = document.getElementById('editTeacherAvatar');
    if (slot.teacher_picture) {
        avatarDiv.innerHTML = `<img src="${slot.teacher_picture}" style="width:44px;height:44px;border-radius:50%;object-fit:cover">`;
    } else {
        avatarDiv.innerHTML = `<i class="ri-user-line text-white ri-xl"></i>`;
    }

    const subjectSel = document.getElementById('editSlotSubject');
    subjectSel.innerHTML = '<option value="">— Free Period —</option>';
    availableSubjects.forEach(s => {
        const opt      = new Option(`${s.subject_name} (${s.teacher_name})`, s.subject_id);
        opt.dataset.teacherId   = s.teacher_id;
        opt.dataset.teacherName = s.teacher_name;
        opt.selected = (slot.subject_id == s.subject_id);
        subjectSel.appendChild(opt);
    });

    const teacherSel = document.getElementById('editSlotTeacher');
    teacherSel.innerHTML = '<option value="">— No Teacher —</option>';
    const uniqueTeachers = new Map();
    availableSubjects.forEach(s => {
        if (s.teacher_id && !uniqueTeachers.has(s.teacher_id)) uniqueTeachers.set(s.teacher_id, s.teacher_name);
    });
    uniqueTeachers.forEach((name, id) => {
        const opt = new Option(name, id);
        opt.selected = (slot.teacher_id == id);
        teacherSel.appendChild(opt);
    });

    new bootstrap.Modal(document.getElementById('editSlotModal')).show();

    if (slot.teacher_id || slot.room_id) {
        setTimeout(runRealtimeConflictCheck, 300);
    }
}

function onSubjectChange() {
    const sel = document.getElementById('editSlotSubject');
    const opt = sel.options[sel.selectedIndex];
    const tid = opt?.dataset?.teacherId;
    if (tid) document.getElementById('editSlotTeacher').value = tid;
    onTeacherChange();
    debounceConflictCheck();
}

function onTeacherChange() {
    const tid = document.getElementById('editSlotTeacher').value;
    if (!tid) { debounceConflictCheck(); return; }
    const t = allTeachers.find(t => t.id == tid);
    const avatarDiv = document.getElementById('editTeacherAvatar');
    if (t?.picture) {
        avatarDiv.innerHTML = `<img src="${t.picture}" style="width:44px;height:44px;border-radius:50%;object-fit:cover" onerror="this.parentElement.innerHTML='<i class=\\'ri-user-line text-white ri-xl\\'></i>'">`;
    }
    debounceConflictCheck();
}

// ============================================================================
// REAL-TIME CONFLICT CHECK
// ============================================================================
function debounceConflictCheck() {
    clearTimeout(conflictCheckTimer);
    const panel = document.getElementById('slotConflictPanel');
    const inner = document.getElementById('slotConflictInner');
    const teacherId = document.getElementById('editSlotTeacher').value;
    const roomId    = roomTomSelect ? roomTomSelect.getValue() : '';
    if (!teacherId && !roomId) {
        resetConflictPanel();
        return;
    }
    panel.style.display = '';
    inner.innerHTML = `<div class="rtc-spinner"><div class="spinner-border text-primary"></div><span>Checking for conflicts…</span></div>`;
    conflictCheckTimer = setTimeout(runRealtimeConflictCheck, 400);
}

async function runRealtimeConflictCheck() {
    const teacherId = document.getElementById('editSlotTeacher').value;
    const roomId    = roomTomSelect ? roomTomSelect.getValue() : '';
    const periodId  = document.getElementById('editSlotPeriodId').value;
    const day       = document.getElementById('editSlotDay').value;
    const settingId = document.getElementById('editSlotSettingId').value;

    const panel = document.getElementById('slotConflictPanel');
    const inner = document.getElementById('slotConflictInner');

    if (!teacherId && !roomId) { resetConflictPanel(); return; }

    try {
        const res  = await apiFetch(ROUTES.checkSlotConflict, 'POST', {
            setting_id: parseInt(settingId),
            period_id:  parseInt(periodId),
            day:        day,
            teacher_id: teacherId ? parseInt(teacherId) : null,
            room_id:    roomId    ? parseInt(roomId)    : null,
        });
        const data = await res.json();
        if (!data.success) return;

        inner.innerHTML = '';
        panel.style.display = '';

        data.conflicts.forEach(c => {
            const div = document.createElement('div');
            div.className = 'rtc-panel ' + (c.severity === 'error' ? 'rtc-error' : 'rtc-warning');

            let altsHtml = '';
            if (c.alternatives?.length) {
                altsHtml += '<div class="rtc-alts">'
                    + c.alternatives.slice(0, 4).map(a =>
                        `<span class="rtc-alt-badge" onclick="closeModalAndOpenSlot(${a.period_id}, '${escapeHtml(a.day)}')">
                            📅 ${escapeHtml(a.day)} · ${escapeHtml(a.period_name)}
                        </span>`
                    ).join('') + '</div>';
            }
            if (c.alternative_rooms?.length) {
                altsHtml += '<div class="rtc-alts" style="margin-top:4px">'
                    + c.alternative_rooms.slice(0, 4).map(r =>
                        `<span class="rtc-room-alt" onclick="switchToRoom(${r.id}, '${escapeHtml(r.label)}')">
                            🏫 ${escapeHtml(r.label)}
                        </span>`
                    ).join('') + '</div>';
            }

            div.innerHTML = `
                <div class="rtc-icon">${c.icon}</div>
                <div class="rtc-body">
                    <div class="rtc-msg">${escapeHtml(c.message)}</div>
                    ${c.detail ? `<div class="rtc-detail">${escapeHtml(c.detail)}</div>` : ''}
                    ${altsHtml}
                </div>`;
            inner.appendChild(div);
        });

        data.warnings.forEach(w => {
            const div = document.createElement('div');
            div.className = 'rtc-panel rtc-warning';
            div.innerHTML = `<div class="rtc-icon">${w.icon}</div>
                <div class="rtc-body"><div class="rtc-msg">${escapeHtml(w.message)}</div></div>`;
            inner.appendChild(div);
        });

        if (!data.conflicts.length && !data.warnings.length) {
            inner.innerHTML = `<div class="rtc-panel rtc-clear">
                <div class="rtc-icon">✅</div>
                <div class="rtc-body"><div class="rtc-msg green">No conflicts detected for this slot.</div></div>
            </div>`;
        }

        const saveBtn = document.getElementById('saveSlotBtn');
        if (data.has_error) {
            saveBtn.innerHTML = '<i class="ri-alert-line me-2"></i>Save Anyway (Override)';
            saveBtn.className = 'btn btn-danger px-4';
        } else {
            saveBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Slot';
            saveBtn.className = 'btn btn-primary px-4';
        }

    } catch (e) {
        inner.innerHTML = '';
    }
}

function resetConflictPanel() {
    document.getElementById('slotConflictPanel').style.display = 'none';
    document.getElementById('slotConflictInner').innerHTML = '';
    const saveBtn = document.getElementById('saveSlotBtn');
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Slot';
        saveBtn.className = 'btn btn-primary px-4';
    }
}

function closeModalAndOpenSlot(periodId, day) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('editSlotModal'));
    if (modal) modal.hide();
    loadTimetableGrid().then(() => openSlotModal(periodId, day));
}

function switchToRoom(roomId, label) {
    if (!roomTomSelect) return;
    const idStr = roomId.toString();
    if (!roomTomSelect.getOption(idStr)) {
        roomTomSelect.addOption({ value: idStr, label: label });
    }
    roomTomSelect.setValue(idStr);
}

async function silentConflictCheck() {
    if (!currentSettingId) return;
    try {
        const res  = await apiFetch(url(ROUTES.checkConflicts, currentSettingId), 'GET');
        const data = await res.json();
        if (!data.success) return;
        const badge = document.getElementById('conflictBadgeTab');
        if (data.conflict_count > 0) {
            badge.style.display = '';
            badge.textContent   = data.conflict_count;
        } else {
            badge.style.display = 'none';
        }
    } catch (e) { /* silent */ }
}

// ============================================================================
// SAVE SLOT
// ============================================================================
async function saveSlot() {
    const roomId = roomTomSelect ? (roomTomSelect.getValue() || null) : null;
    const payload = {
        setting_id: parseInt(document.getElementById('editSlotSettingId').value),
        expected_updated_at: currentSettingVersion,
        period_id:  parseInt(document.getElementById('editSlotPeriodId').value),
        day:        document.getElementById('editSlotDay').value,
        subject_id: document.getElementById('editSlotSubject').value || null,
        teacher_id: document.getElementById('editSlotTeacher').value || null,
        room_id:    roomId ? parseInt(roomId) : null,
        notes:      document.getElementById('editSlotNotes').value || null,
        is_double:  document.getElementById('editSlotIsDouble').checked,
    };

    showLoader();
    try {
        const res    = await apiFetch(ROUTES.saveSlot, 'POST', payload);
        const result = await res.json();

        if (result.success) {
            currentSettingVersion = result.setting_updated_at;
            hideLoader();
            bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
            await loadTimetableGrid();
            silentConflictCheck();
            Swal.fire({ icon:'success', title:'Saved!', timer:1200, showConfirmButton:false });
            return;
        }

        if (result.has_version_conflict) {
            hideLoader();
            bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
            return handleVersionConflict(result);
        }

        if (result.has_conflict) {
            hideLoader();

            const isRoomConflict = (result.conflict_type || '').startsWith('room');
            const icon           = isRoomConflict ? '🏫' : '⚠️';
            const title          = isRoomConflict ? 'Room Already In Use' : 'Teacher Conflict Detected';

            let altsHtml = '';
            if (result.alternatives?.length) {
                altsHtml += `<div class="mt-3 text-start">
                    <div class="fw-semibold mb-2" style="font-size:13px">
                        <i class="ri-lightbulb-flash-line text-warning me-1"></i>Available alternative slots:
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        ${result.alternatives.slice(0, 5).map(a =>
                            `<span class="badge p-2" style="background:#dcfce7;color:#15803d;font-size:11px">
                                📅 ${escapeHtml(a.day)} · ${escapeHtml(a.period_name)} (${escapeHtml(a.period_time)})
                            </span>`
                        ).join('')}
                    </div></div>`;
            }
            if (result.alternative_rooms?.length) {
                altsHtml += `<div class="mt-2 text-start">
                    <div class="fw-semibold mb-2" style="font-size:13px">
                        <i class="ri-door-line text-info me-1"></i>Available alternative rooms:
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        ${result.alternative_rooms.slice(0, 4).map(r =>
                            `<span class="badge p-2" style="background:#EFF6FF;color:#1565C0;font-size:11px;cursor:pointer"
                                onclick="switchToRoom(${r.id}, '${escapeHtml(r.label)}')">
                                🏫 ${escapeHtml(r.label)}
                            </span>`
                        ).join('')}
                    </div></div>`;
            }

            const { isConfirmed } = await Swal.fire({
                title: `${icon} ${title}`,
                html: `<div style="font-size:14px;text-align:left">
                    <p class="mb-2">${escapeHtml(result.message)}</p>
                    ${altsHtml}
                    <hr class="my-3">
                    <p class="text-muted mb-0" style="font-size:12px">Override to save anyway, or cancel to choose differently.</p>
                </div>`,
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#DC2626', cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="ri-save-line me-1"></i>Override & Save',
                cancelButtonText:  'Cancel', width: 520,
            });

            if (!isConfirmed) return;

            showLoader();
            const res2    = await apiFetch(ROUTES.saveSlot, 'POST', { ...payload, force_save: true });
            const result2 = await res2.json();
            if (result2.success) {
                currentSettingVersion = result2.setting_updated_at;
                hideLoader();
                bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
                await loadTimetableGrid();
                silentConflictCheck();
                Swal.fire({ icon:'success', title:'Saved (Override)!', timer:1400, showConfirmButton:false });
            } else if (result2.has_version_conflict) {
                hideLoader();
                bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
                return handleVersionConflict(result2);
            } else {
                hideLoader();
                Swal.fire('Error', result2.message || 'Save failed', 'error');
            }
            return;
        }

        hideLoader();
        Swal.fire('Error', result.message || 'Save failed', 'error');
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// CONFLICT CHECKER TAB
// ============================================================================
async function checkConflicts() {
    if (!currentSettingId) return;
    showLoader();
    try {
        const res  = await apiFetch(url(ROUTES.checkConflicts, currentSettingId), 'GET');
        const data = await res.json();
        if (!data.success) {
            hideLoader();
            Swal.fire('Error', data.message || 'Failed', 'error');
            return;
        }

        const container = document.getElementById('conflictsList');
        const badge     = document.getElementById('conflictBadgeTab');

        if (data.checked_at) {
            document.getElementById('conflictCheckedAt').style.display = '';
            document.getElementById('conflictCheckedAtText').textContent = 'Last checked: ' + data.checked_at;
        }

        if (!data.conflict_count) {
            badge.style.display = 'none';
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-check-double-line ri-3x d-block mb-3 text-success"></i>
                    <h6 class="text-success">No Conflicts Found</h6>
                    <p class="text-muted mb-0">All teachers and rooms are properly scheduled with no overlaps across any class.</p>
                </div>`;
            hideLoader();
            return;
        }

        badge.style.display = '';
        badge.textContent   = data.conflict_count;

        const teacherConflicts = data.conflicts.filter(c => c.conflict_category === 'teacher');
        const roomConflicts    = data.conflicts.filter(c => c.conflict_category === 'room');

        let html = `<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
            <i class="ri-alert-line ri-xl"></i>
            Found <strong class="mx-1">${data.conflict_count}</strong> conflict(s) across all classes
            ${teacherConflicts.length ? `<span class="badge bg-danger ms-1">${teacherConflicts.length} teacher</span>` : ''}
            ${roomConflicts.length    ? `<span class="badge bg-warning text-dark ms-1">${roomConflicts.length} room</span>` : ''}
        </div>`;

        data.conflicts.forEach(c => {
            const isRoomConflict = c.conflict_category === 'room';
            const avatarHtml     = isRoomConflict
                ? `<div class="conflict-avatar-ph room"><i class="ri-home-3-line ri-xl" style="color:#EA580C"></i></div>`
                : (c.teacher_picture
                    ? `<img src="${c.teacher_picture}" class="conflict-avatar">`
                    : `<div class="conflict-avatar-ph"><i class="ri-user-line ri-xl"></i></div>`);

            const crossArmBadge = c.is_cross_arm
                ? `<span class="badge bg-warning-subtle text-warning ms-1" style="font-size:10px">
                       <i class="ri-git-branch-line"></i> Cross-Arm
                   </span>` : '';

            const classesHtml = (c.all_classes && c.all_classes.length > 2)
                ? c.all_classes.map(cls => `<span class="badge bg-primary-subtle text-primary me-1">${escapeHtml(cls)}</span>`).join('')
                : `<span class="badge bg-primary-subtle text-primary">${escapeHtml(c.class_a || '')}</span>
                   <span class="mx-1 text-muted">&amp;</span>
                   <span class="badge bg-primary-subtle text-primary">${escapeHtml(c.class_b || '')}</span>`;

            const altHtml = c.alternatives?.length
                ? `<div class="conflict-suggestion">
                       <div><i class="ri-lightbulb-line text-success me-1"></i>
                           <strong>Suggestion:</strong> ${escapeHtml(c.resolution_suggestion)}
                       </div>
                       <div class="alt-badges">
                           ${c.alternatives.slice(0, 4).map(a =>
                               `<span class="alt-badge" onclick="switchToGridAndOpen(${a.period_id}, '${a.day}')">
                                    📅 ${escapeHtml(a.day)} · ${escapeHtml(a.period_name)} (${escapeHtml(a.period_time)})
                                </span>`
                           ).join('')}
                       </div>
                   </div>`
                : `<div class="mt-2 text-muted" style="font-size:12px">
                       <i class="ri-information-line me-1"></i>${escapeHtml(c.resolution_suggestion)}
                   </div>`;

            html += `<div class="conflict-item ${isRoomConflict ? 'room-conflict' : ''}">
                ${avatarHtml}
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1">
                        ${escapeHtml(c.teacher || '—')} ${crossArmBadge}
                        ${isRoomConflict ? '<span class="badge bg-warning-subtle text-warning ms-1" style="font-size:10px">Room Conflict</span>' : ''}
                    </div>
                    <div class="text-danger fw-semibold" style="font-size:12px">
                        <i class="ri-time-line me-1"></i>${escapeHtml(c.day)} · ${escapeHtml(c.period)}
                        ${c.period_time ? ' (' + escapeHtml(c.period_time) + ')' : ''}
                    </div>
                    <div class="mt-1" style="font-size:12px">
                        ${classesHtml}
                        <span class="text-muted ms-2">${escapeHtml(c.subject_a || '—')} vs ${escapeHtml(c.subject_b || '—')}</span>
                    </div>
                    ${altHtml}
                </div>
            </div>`;
        });

        container.innerHTML = html;
        hideLoader();
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

function switchToGridAndOpen(periodId, day) {
    showTab('gridTab', document.querySelectorAll('.tt-tab')[2]);
    loadTimetableGrid().then(() => openSlotModal(periodId, day));
}

// ============================================================================
// NOTIFICATIONS / EXPORT / DELETE / CLONE
// ============================================================================
async function sendNotifications() {
    const result = await Swal.fire({
        title: 'Send Notifications', text: 'Send timetable notifications to all assigned teachers?',
        icon: 'question', showCancelButton: true, confirmButtonColor: '#1565C0', confirmButtonText: 'Yes, send!',
    });
    if (!result.isConfirmed) return;
    showLoader();
    try {
        const res  = await apiFetch(ROUTES.sendNotifications, 'POST', { setting_id: currentSettingId, type: 'weekly_preview' });
        const data = await res.json();
        hideLoader();
        if (data.success) Swal.fire({ icon:'success', title:'Sent!', text: data.message, timer:2000, showConfirmButton:false });
        else Swal.fire('Error', data.message || 'Failed', 'error');
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

function exportTimetable(format) {
    if (!currentSettingId) return Swal.fire('Error', 'No timetable loaded.', 'error');
    const orientation = document.getElementById('exportOrientation')?.value || 'horizontal';
    const exportUrl   = url(ROUTES.export, currentSettingId) + '?format=' + format + '&orientation=' + orientation;
    if (format === 'pdf') window.open(exportUrl, '_blank');
    else window.location.href = exportUrl;
}

function openWholeSchoolExportModal() {
    new bootstrap.Modal(document.getElementById('wholeSchoolExportModal')).show();
}

function exportWholeSchoolTimetable() {
    const sessionId   = document.getElementById('wholeSchoolSessionId').value;
    const termId      = document.getElementById('wholeSchoolTermId').value;
    const orientation = document.getElementById('wholeSchoolOrientation').value;
    if (!sessionId) return Swal.fire('Error', 'Please select a session.', 'error');
    window.open(
        ROUTES.exportWholeSchool + '?session_id=' + sessionId + '&term_id=' + (termId || '') + '&orientation=' + orientation,
        '_blank'
    );
}

async function deleteSetting(settingId, updatedAt) {
    const result = await Swal.fire({
        title: 'Delete Timetable?', text: 'This will permanently delete this timetable and all its slots.',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#DC2626', confirmButtonText: 'Yes, delete!',
    });
    if (!result.isConfirmed) return;
    showLoader();
    try {
        const res  = await apiFetch(url(ROUTES.deleteSetting, settingId), 'DELETE', { expected_updated_at: updatedAt });
        const data = await res.json();
        hideLoader();
        if (data.success) {
            Swal.fire({ icon:'success', title:'Deleted!', timer:1400, showConfirmButton:false });
            setTimeout(() => location.reload(), 1400);
        } else if (data.has_version_conflict) {
            await Swal.fire({ title: 'Changed since you last saw it', text: data.message, icon: 'warning', confirmButtonText: 'Reload List' });
            location.reload();
        } else {
            Swal.fire('Error', data.message || 'Failed', 'error');
        }
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

function cloneSetting(settingId) {
    pendingCloneId = settingId;
    new bootstrap.Modal(document.getElementById('cloneModal')).show();
}

async function confirmClone(force = false) {
    if (!pendingCloneId) return;
    if (!force) bootstrap.Modal.getInstance(document.getElementById('cloneModal')).hide();

    const settingId = pendingCloneId;
    showLoader();
    try {
        const res  = await apiFetch(ROUTES.cloneSetting, 'POST', {
            setting_id:     settingId,
            new_session_id: document.getElementById('cloneSessionId').value || null,
            new_term_id:    document.getElementById('cloneTermId').value    || null,
            force,
        });
        const data = await res.json();
        hideLoader();

        if (data.success) {
            pendingCloneId = null;
            Swal.fire({ icon:'success', title:'Cloned!', timer:1400, showConfirmButton:false });
            setTimeout(() => location.reload(), 1400);
            return;
        }

        if (data.is_being_edited) {
            const confirmResult = await Swal.fire({
                title: 'Being Edited', text: data.message, icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Clone Anyway', confirmButtonColor: '#DC2626',
            });
            if (confirmResult.isConfirmed) {
                pendingCloneId = settingId;
                return confirmClone(true);
            }
            pendingCloneId = null;
            return;
        }

        pendingCloneId = null;
        Swal.fire('Error', data.message || 'Failed', 'error');
    } catch (e) {
        hideLoader();
        pendingCloneId = null;
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// GENERATION WIZARD
// ============================================================================
function openGenerationWizardModal() {
    document.getElementById('wizHalfDaysBody').innerHTML = '';
    new bootstrap.Modal(document.getElementById('generationWizardModal')).show();
}

function toggleWizardClassPicker() {
    document.getElementById('wizClassPickerWrap').style.display =
        document.getElementById('wizScope').value === 'selected' ? '' : 'none';
}

function toggleWizardAssemblyDay() {
    document.getElementById('wizAssemblyDayWrap').style.display =
        document.getElementById('wizAssemblyFirstPeriod').checked ? '' : 'none';
}

function addWizardHalfDayRow() {
    const wrap = document.getElementById('wizHalfDaysBody');
    const row  = document.createElement('div');
    row.className = 'row g-2 align-items-center mb-2 wiz-half-day-row';
    row.innerHTML = `
        <div class="col-md-5">
            <select class="form-select form-select-sm half-day-select">
                ${['Monday','Tuesday','Wednesday','Thursday','Friday'].map(d => `<option value="${d}">${d}</option>`).join('')}
            </select>
        </div>
        <div class="col-md-5">
            <input type="number" class="form-control form-control-sm half-day-lessons" min="1" placeholder="Lessons that day">
        </div>
        <div class="col-md-2 text-end">
            <button class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.wiz-half-day-row').remove()">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>`;
    wrap.appendChild(row);
}

function getWizardHalfDays() {
    return [...document.querySelectorAll('.wiz-half-day-row')].map(row => {
        const day     = row.querySelector('.half-day-select').value;
        const lessons = parseInt(row.querySelector('.half-day-lessons').value);
        return (day && lessons) ? { day, lessons } : null;
    }).filter(Boolean);
}

function buildWizardResultsSummary(results) {
    if (!Array.isArray(results) || !results.length) return '';
    const skipped = results.filter(r => r.skipped);
    const applied = results.filter(r => !r.skipped);

    const classNameById = {};
    document.querySelectorAll('#wizClassIds option').forEach(opt => {
        classNameById[opt.value] = opt.textContent.trim();
    });
    const nameFor = (id) => classNameById[id] || `Class #${id}`;

    let html = `<div class="text-start mt-2" style="font-size:12px">
        <div class="text-success mb-1"><i class="ri-checkbox-circle-line"></i> Applied to ${applied.length} class(es)</div>`;

    if (skipped.length) {
        html += `<div class="text-warning mb-1"><i class="ri-alert-line"></i> Skipped ${skipped.length} class(es) — published/locked:</div>
            <ul class="mb-0 ps-4">
                ${skipped.map(s => `<li>${escapeHtml(nameFor(s.schoolclass_id))}</li>`).join('')}
            </ul>`;
    }
    html += '</div>';
    return html;
}

async function submitGenerationWizard(alsoGenerate) {
    const sessionId = document.getElementById('wizSessionId').value;
    if (!sessionId) return Swal.fire('Required', 'Please select a session.', 'warning');

    const activeDays = [...document.querySelectorAll('.wiz-active-day:checked')].map(cb => cb.value);
    if (!activeDays.length) return Swal.fire('Required', 'Select at least one active day.', 'warning');

    const scope    = document.getElementById('wizScope').value;
    const classIds = scope === 'selected'
        ? [...document.getElementById('wizClassIds').selectedOptions].map(o => parseInt(o.value))
        : null;
    if (scope === 'selected' && !classIds.length) {
        return Swal.fire('Required', 'Select at least one class, or switch scope to "All Classes".', 'warning');
    }

    const payload = {
        session_id:                  parseInt(sessionId),
        term_id:                     document.getElementById('wizTermId').value || null,
        schoolclass_ids:             classIds,
        school_day_start:            document.getElementById('wizDayStart').value,
        school_day_end:              document.getElementById('wizDayEnd').value,
        period_duration_minutes:     parseInt(document.getElementById('wizPeriodDuration').value),
        short_break_duration:        parseInt(document.getElementById('wizShortBreakDuration').value),
        long_break_duration:         parseInt(document.getElementById('wizLongBreakDuration').value),
        lessons_per_day:             parseInt(document.getElementById('wizLessonsPerDay').value),
        short_break_after:           document.getElementById('wizShortBreakAfter').value ? parseInt(document.getElementById('wizShortBreakAfter').value) : null,
        long_break_after:            document.getElementById('wizLongBreakAfter').value ? parseInt(document.getElementById('wizLongBreakAfter').value) : null,
        assembly_first_period:       document.getElementById('wizAssemblyFirstPeriod').checked,
        assembly_day:                document.getElementById('wizAssemblyFirstPeriod').checked
                                          ? document.getElementById('wizAssemblyDay').value : null,
        active_days:                 activeDays,
        free_periods_per_week:       parseInt(document.getElementById('wizFreePeriods').value) || 0,
        max_lessons_per_day:         document.getElementById('wizMaxLessonsPerDay').value ? parseInt(document.getElementById('wizMaxLessonsPerDay').value) : null,
        half_days:                   getWizardHalfDays(),
        deprioritize_break_adjacent: document.getElementById('wizDeprioritizeBreakAdjacent').checked,
    };

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.applyGenerationTemplate, 'POST', payload);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to apply structure.');

        const summaryHtml = buildWizardResultsSummary(data.results);

        if (!alsoGenerate) {
            hideLoader();
            bootstrap.Modal.getInstance(document.getElementById('generationWizardModal')).hide();
            Swal.fire({
                icon: 'success', title: 'Structure Applied',
                html: `Applied to <strong>${data.applied_to}</strong> class(es).${summaryHtml}`,
            }).then(() => location.reload());
            return;
        }

        const genRes  = await apiFetch(ROUTES.autoGenerateWholeSchool, 'POST', {
            session_id: payload.session_id, term_id: payload.term_id, schoolclass_ids: payload.schoolclass_ids,
        });
        const genData = await genRes.json();
        hideLoader();

        if (genData.success) {
            bootstrap.Modal.getInstance(document.getElementById('generationWizardModal')).hide();
            const shortfallNote = genData.had_shortfalls
                ? '<p class="text-warning mt-2" style="font-size:12px"><i class="ri-alert-line"></i> Some subjects could not be fully placed in one or more classes — check their Constraints/Conflicts tabs.</p>'
                : '';
            Swal.fire({
                icon: 'success', title: 'Generated!',
                html: `Generated timetables for <strong>${genData.classes.length}</strong> class(es).${summaryHtml}${shortfallNote}`,
            }).then(() => location.reload());
        } else if (genData.has_locked) {
            const confirmResult = await Swal.fire({
                title: 'Some Timetables Are Locked', text: genData.message + ' Unpublish and regenerate anyway?',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#DC2626', confirmButtonText: 'Unpublish & Generate',
            });
            if (confirmResult.isConfirmed) {
                showLoader();
                const forceRes  = await apiFetch(ROUTES.autoGenerateWholeSchool, 'POST', {
                    session_id: payload.session_id, term_id: payload.term_id,
                    schoolclass_ids: payload.schoolclass_ids, force_unpublish: true,
                });
                const forceData = await forceRes.json();
                hideLoader();
                if (forceData.success) {
                    bootstrap.Modal.getInstance(document.getElementById('generationWizardModal')).hide();
                    const forceShortfallNote = forceData.had_shortfalls
                        ? '<p class="text-warning mt-2" style="font-size:12px"><i class="ri-alert-line"></i> Some subjects could not be fully placed in one or more classes — check their Constraints/Conflicts tabs.</p>'
                        : '';
                    Swal.fire({
                        icon: 'success', title: 'Generated!',
                        html: `Generated timetables for <strong>${forceData.classes.length}</strong> class(es).${summaryHtml}${forceShortfallNote}`,
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', forceData.message || 'Failed', 'error');
                }
            }
        } else {
            throw new Error(genData.message || 'Generation failed.');
        }
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// QUICK REBUILD
// ============================================================================
function openAnchorRebuildPanel() {
    if (!currentSettingId) return Swal.fire('No Class Loaded', 'Load or create a class timetable first.', 'warning');
    new bootstrap.Modal(document.getElementById('anchorRebuildModal')).show();
}

async function submitAnchorRebuild() {
    const assemblyChecked = document.getElementById('arAssemblyEnabled').checked;
    const payload = {
        setting_id:                    currentSettingId,
        lessons_per_day:                parseInt(document.getElementById('arLessonsPerDay').value),
        short_break_after_period:       document.getElementById('arShortBreakAfter').value ? parseInt(document.getElementById('arShortBreakAfter').value) : null,
        long_break_after_period:        document.getElementById('arLongBreakAfter').value ? parseInt(document.getElementById('arLongBreakAfter').value) : null,
        assembly_day:                   assemblyChecked ? document.getElementById('arAssemblyDay').value : null,
        short_break_duration_minutes:   parseInt(document.getElementById('arShortBreakDuration').value),
        long_break_duration_minutes:    parseInt(document.getElementById('arLongBreakDuration').value),
        period_duration_minutes:        parseInt(document.getElementById('arPeriodDuration').value),
        school_day_start:               document.getElementById('arDayStart').value,
    };

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.rebuildPeriodsFromAnchors, 'POST', payload);
        const data = await res.json();
        if (!data.success) {
            hideLoader();
            Swal.fire('Error', data.message || 'Failed to rebuild periods.', 'error');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('anchorRebuildModal')).hide();
        hideLoader();
        await loadSetting(currentSettingId);
        Swal.fire({ icon:'success', title:'Periods Rebuilt!', timer:1600, showConfirmButton:false });
    } catch (e) {
        hideLoader();
        Swal.fire('Error', e.message, 'error');
    }
}

// ============================================================================
// VERSION CONFLICT HANDLER
// ============================================================================
async function handleVersionConflict(data) {
    const result = await Swal.fire({
        title: 'Timetable Changed',
        text: data.message || 'This timetable was modified by someone else. Reload to get the latest version.',
        icon: 'warning',
        confirmButtonText: 'Reload Now',
        showCancelButton: true,
        cancelButtonText: 'Stay',
    });
    if (result.isConfirmed && currentSettingId) {
        await loadSetting(currentSettingId);
    }
}
</script>
@endsection