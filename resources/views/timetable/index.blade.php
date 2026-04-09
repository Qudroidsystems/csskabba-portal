{{-- resources/views/timetable/index.blade.php --}}
@extends('layouts.master')

{{-- Tom Select for searchable dropdowns --}}
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
    --tt-shadow-lg: 0 4px 16px rgba(0,0,0,.10);
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
}
.tt-page-header h4 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
.tt-page-header p  { color: rgba(255,255,255,.75); margin: 4px 0 0; font-size: 13px; }

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
}
.tt-card-header h6 { margin: 0; font-size: 14px; font-weight: 600; color: #1E293B; }
.tt-card-body { padding: 20px; }

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
.setting-card:hover { border-color: var(--tt-blue); box-shadow: 0 0 0 3px rgba(21,101,192,.08); transform: translateY(-1px); }
.setting-card:last-child { margin-bottom: 0; }
.setting-card .sc-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, #E3F2FD, #EDE7F6);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.setting-card .sc-icon i { font-size: 20px; color: var(--tt-blue); }
.setting-card .sc-body { flex: 1; margin: 0 14px; }
.setting-card .sc-body .sc-title { font-size: 14px; font-weight: 600; color: #1E293B; margin-bottom: 2px; }
.setting-card .sc-body .sc-meta  { font-size: 12px; color: #64748B; }
.setting-card .sc-actions { display: flex; gap: 6px; flex-shrink: 0; }

/* ── Tabs ─────────────────────────────────────────── */
.tt-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--tt-border);
    margin-bottom: 24px;
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
}
.tt-tab:hover { color: var(--tt-blue); background: rgba(21,101,192,.04); }
.tt-tab.active { color: var(--tt-blue); border-bottom-color: var(--tt-blue); font-weight: 600; }
.tt-tab .tab-badge {
    font-size: 10px;
    padding: 1px 6px;
    background: #EF4444;
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
}

/* ── Timetable grid ───────────────────────────────── */
.tt-grid-wrapper { overflow-x: auto; }
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
}
.tt-grid .period-td .pname { font-size: 12px; font-weight: 700; color: #1E293B; }
.tt-grid .period-td .ptime { font-size: 11px; color: #94A3B8; margin-top: 2px; }

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
}
.tt-cell:hover { background: rgba(21,101,192,.06) !important; }
.tt-cell.is-free { background: #FAFAFA; }
.tt-cell.is-double { background: rgba(21,101,192,.05); }
.tt-cell.is-break { background: #FFFBEB; cursor: default; }
.tt-cell.is-break:hover { background: #FFFBEB !important; }

.tt-cell .cell-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.8);
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
    margin-bottom: 5px;
}
.tt-cell .cell-avatar-placeholder {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E3F2FD, #EDE7F6);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 5px;
}
.tt-cell .cell-avatar-placeholder i { font-size: 16px; color: var(--tt-blue); }

.tt-cell .cell-subject { font-size: 11px; font-weight: 700; color: #1E293B; line-height: 1.3; }
.tt-cell .cell-teacher { font-size: 10px; color: #64748B; margin-top: 1px; }
.tt-cell .cell-room    { font-size: 10px; color: #94A3B8; margin-top: 1px; }
.tt-cell .cell-room i { font-size: 9px; margin-right: 2px; }
.tt-cell .cell-free    { font-size: 11px; color: #CBD5E1; }
.tt-cell .cell-break   { font-size: 11px; color: #D97706; font-weight: 600; }
.tt-cell .cell-double-badge {
    font-size: 9px;
    padding: 1px 5px;
    background: rgba(21,101,192,.12);
    color: var(--tt-blue);
    border-radius: 4px;
    font-weight: 700;
    margin-top: 3px;
}

/* Subject color stripes on left edge */
.tt-cell.has-subject { border-left: 3px solid; }

/* ── Constraints table ────────────────────────────── */
#constraintsTable { font-size: 13px; }
#constraintsTable td { vertical-align: middle; }
#constraintsTable input[type="number"] { width: 70px; }
#constraintsTable select[multiple] { font-size: 12px; }

/* ── Periods table ────────────────────────────────── */
#periodsBody tr { animation: rowFadeIn 0.2s ease; }
@keyframes rowFadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }

/* ── Conflict items ───────────────────────────────── */
.conflict-item {
    border: 1px solid #FEE2E2;
    background: #FFF5F5;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.conflict-item:last-child { margin-bottom: 0; }
.conflict-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.conflict-avatar-ph {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #FEE2E2;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.conflict-avatar-ph i { color: #EF4444; }

/* ── Export buttons ───────────────────────────────── */
.export-group { display: flex; gap: 8px; align-items: center; }

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 768px) {
    .tt-page-header { flex-direction: column; gap: 12px; }
    .tt-tabs { overflow-x: auto; }
    .export-group { flex-wrap: wrap; }
}

/* ── Status badge ─────────────────────────────────── */
.status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
    background: #22C55E;
    margin-right: 5px;
}

/* ── Tom Select overrides to match app style ─────── */
.ts-wrapper .ts-control {
    border-color: #D1D5DB;
    border-radius: 6px;
    min-height: 38px;
    font-size: 14px;
}
.ts-wrapper.focus .ts-control { border-color: #1565C0; box-shadow: 0 0 0 3px rgba(21,101,192,.12); }
.ts-dropdown { font-size: 13px; }
.ts-dropdown .option { padding: 8px 12px; }
.ts-dropdown .option:hover,.ts-dropdown .option.active { background: #EFF6FF; color: #1565C0; }
</style>


@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

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
            <button class="btn btn-outline-light btn-sm" onclick="openWholeSchoolExportModal()">
                <i class="ri-school-line me-1"></i>Whole School Timetable
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

    {{-- Selection + Existing timetables side by side --}}
    <div class="row g-3 mb-4">

        {{-- Class/Session Selector --}}
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
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
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

        {{-- Existing timetables --}}
        <div class="col-lg-7">
            <div class="tt-card h-100">
                <div class="tt-card-header">
                    <h6><i class="ri-history-line me-2 text-success"></i>Existing Timetables
                        <span class="badge bg-success-subtle text-success ms-2">{{ $settings->count() }}</span>
                    </h6>
                </div>
                <div class="tt-card-body" style="max-height:280px;overflow-y:auto">
                    @forelse ($settings as $setting)
                    <div class="setting-card" onclick="loadSetting({{ $setting->id }})">
                        <div class="sc-icon"><i class="ri-school-line"></i></div>
                        <div class="sc-body">
                            <div class="sc-title">
                                {{ $setting->schoolclass->schoolclass ?? 'Unknown Class' }}
                                @if($setting->schoolclass?->arm)
                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:11px;font-weight:600">
                                        {{ is_object($setting->schoolclass->arm) ? $setting->schoolclass->arm->arm : $setting->schoolclass->arm }}
                                    </span>
                                @endif
                            </div>
                            <div class="sc-meta">
                                <span>{{ $setting->session->session ?? '—' }}</span>
                                @if($setting->term) <span class="mx-1">·</span><span>{{ $setting->term->term }}</span> @endif
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
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSetting({{ $setting->id }})" title="Delete">
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

            {{-- Editor header with context --}}
            <div class="tt-card-header" style="background:linear-gradient(135deg,#EFF6FF,#F5F3FF)">
                <div>
                    <h6 id="editorContext" class="mb-0"><i class="ri-school-line me-2 text-primary"></i>Loading…</h6>
                    <small class="text-muted" id="editorSubContext"></small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('timetableEditor').style.display='none'">
                        <i class="ri-arrow-go-back-line me-1"></i>Close Editor
                    </button>
                </div>
            </div>

            <div class="tt-card-body">

                {{-- Tabs --}}
                <div class="tt-tabs" role="tablist">
                    <button class="tt-tab active" onclick="showTab('periodsTab', this)">
                        <i class="ri-time-line"></i> Periods & Settings
                    </button>
                    <button class="tt-tab" onclick="showTab('constraintsTab', this)">
                        <i class="ri-bar-chart-2-line"></i> Subject Constraints
                    </button>
                    <button class="tt-tab" onclick="showTab('gridTab', this); loadTimetableGrid()">
                        <i class="ri-table-line"></i> Timetable Grid
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
                                    <button class="btn btn-sm btn-primary" onclick="addPeriodRow()">
                                        <i class="ri-add-line"></i> Add Period
                                    </button>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Subject Constraints</h6>
                            <p class="text-muted mb-0" style="font-size:13px">Define how many times per week each subject is taught, and preferred scheduling rules.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" onclick="saveConstraints()">
                                <i class="ri-save-line me-2"></i>Save Constraints
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
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Periods / Week</th>
                                        <th>Allow Double</th>
                                        <th>Max Doubles</th>
                                        <th>Preferred Days</th>
                                        <th>Avoid Days</th>
                                        <th>Compulsory</th>
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
                            <select id="exportOrientation" class="form-select form-select-sm" style="width: auto;">
                                <option value="horizontal">Horizontal Layout (Days as columns)</option>
                                <option value="vertical">Vertical Layout (Days as rows)</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary" onclick="loadTimetableGrid()">
                                <i class="ri-refresh-line me-1"></i>Refresh
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="exportTimetable('csv')">
                                <i class="ri-file-excel-line me-1"></i>Export CSV
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="exportTimetable('pdf')">
                                <i class="ri-file-pdf-line me-1"></i>Export PDF
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="sendNotifications()">
                                <i class="ri-mail-send-line me-1"></i>Notify Teachers
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Conflict Checker</h6>
                            <p class="text-muted mb-0" style="font-size:13px">Checks for teacher double-booking across all classes in the same session.</p>
                        </div>
                        <button class="btn btn-primary" onclick="checkConflicts()">
                            <i class="ri-search-line me-2"></i>Run Conflict Check
                        </button>
                    </div>
                    <div id="conflictsList">
                        <div class="text-center py-5 text-muted">
                            <i class="ri-check-double-line ri-3x d-block mb-3 text-success opacity-50"></i>
                            <p>Click <strong>Run Conflict Check</strong> to validate teacher assignments.</p>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;border:none;box-shadow:0 20px 60px rgba(0,0,0,.18)">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#1565C0,#6A1B9A);padding:20px 24px 0">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div id="editTeacherAvatar" style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ri-user-line text-white ri-xl"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-0" style="font-size:15px">Edit Timetable Slot</h5>
                        <small class="text-white opacity-75" id="editSlotContext">—</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
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

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject</label>
                    <select class="form-select" id="editSlotSubject" onchange="onSubjectChange()">
                        <option value="">— Free Period —</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Teacher</label>
                    <select class="form-select" id="editSlotTeacher" onchange="onTeacherChange()">
                        <option value="">— No Teacher —</option>
                    </select>
                </div>

                {{-- Room/Venue dropdown with Tom Select --}}
                <div class="row g-3 mb-3">
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
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control" id="editSlotNotes" rows="2" placeholder="Optional notes…"></textarea>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0" style="padding:0 24px 20px">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveSlot()">
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
                <h5 class="modal-title"><i class="ri-school-line me-2"></i>Export Whole School Timetable</h5>
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
                        <option value="horizontal">Horizontal Layout (Days as columns, Periods as rows)</option>
                        <option value="vertical">Vertical Layout (Days as rows, Periods as columns)</option>
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

{{-- Clone modal --}}
<div class="modal fade" id="cloneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-file-copy-line me-2"></i>Clone Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size:13px">The cloned timetable will copy all periods, constraints, and slots. You can optionally change the session or term.</p>
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

@endsection

{{-- Tom Select JS (must come before our script) --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// GLOBALS
// ============================================================================
let currentSettingId = null;
let currentSetting   = null;
let currentPeriods   = [];
let currentGrid      = {};
let currentDays      = [];
let availableSubjects= [];
let allTeachers      = [];
let availableRooms   = [];
let pendingCloneId   = null;

// Tom Select instance for room dropdown
let roomTomSelect = null;

const SUBJECT_COLORS = ['#3B82F6','#8B5CF6','#10B981','#F59E0B','#EF4444','#06B6D4','#F97316','#EC4899','#14B8A6','#84CC16'];
const subjectColorMap = {};
let colorSeq = 0;

function getSubjectColor(subjectId) {
    if (!subjectId) return null;
    if (!subjectColorMap[subjectId]) {
        subjectColorMap[subjectId] = SUBJECT_COLORS[colorSeq++ % SUBJECT_COLORS.length];
    }
    return subjectColorMap[subjectId];
}

const ROUTES = {
    setup:             '{{ route("timetable.setup") }}',
    saveSettings:      '{{ route("timetable.save-settings") }}',
    saveConstraints:   '{{ route("timetable.save-constraints") }}',
    autoGenerate:      '{{ route("timetable.auto-generate") }}',
    saveSlot:          '{{ route("timetable.save-slot") }}',
    sendNotifications: '{{ route("timetable.send-notifications") }}',
    cloneSetting:      '{{ route("timetable.clone-setting") }}',
    getSetting:        '{{ url("/timetable/get-setting") }}',
    getGrid:           '{{ url("/timetable/get-grid") }}',
    checkConflicts:    '{{ url("/timetable/check-conflicts") }}',
    export:            '{{ url("/timetable/export") }}',
    deleteSetting:     '{{ url("/timetable/delete-setting") }}',
    exportWholeSchool: '{{ route("timetable.export-whole-school") }}',
};
const CSRF = '{{ csrf_token() }}';

function url(base, id) { return base.replace(/\/$/, '') + '/' + id; }

// ============================================================================
// INITIALISE TOM SELECT for room dropdown
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
    roomTomSelect = new TomSelect('#editSlotRoom', {
        valueField:   'value',
        labelField:   'label',
        searchField:  ['label'],
        options:      [],
        create:       true,
        createOnBlur: true,
        placeholder:  'Search or type a room…',
        maxItems:     1,
        allowEmptyOption: true,
        render: {
            option: function(data) {
                return '<div class="d-flex justify-content-between align-items-center">'
                     + '<span>' + escapeHtml(data.label) + '</span>'
                     + (data.type ? '<small class="text-muted ms-2">' + escapeHtml(data.type) + '</small>' : '')
                     + '</div>';
            }
        }
    });
});

// Update Tom Select with rooms from server
function updateRoomDropdown(rooms) {
    if (!roomTomSelect) return;
    roomTomSelect.clearOptions();
    roomTomSelect.addOption({ value: '', label: '— No Room —' });
    rooms.forEach(function(r) {
        roomTomSelect.addOption({
            value: r.id.toString(),
            label: r.label,
            type:  r.type || '',
        });
    });
}

// ============================================================================
// TABS
// ============================================================================
function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content-pane').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tt-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).style.display = '';
    if (btn) btn.classList.add('active');
}

// ============================================================================
// LOAD / CREATE SETTING
// ============================================================================
async function loadOrCreateSetting() {
    const classId   = document.getElementById('classSelect').value;
    const sessionId = document.getElementById('sessionSelect').value;
    const termId    = document.getElementById('termSelect').value;

    if (!classId || !sessionId) {
        return Swal.fire({ title: 'Required', text: 'Please select both a class and session.', icon: 'warning', confirmButtonColor: '#1565C0' });
    }

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.setup, 'POST', { schoolclass_id: classId, session_id: sessionId, term_id: termId || null });
        const data = await res.json();
        if (data.success) {
            currentSettingId = data.setting_id;
            await loadSetting(currentSettingId);
        } else throw new Error(data.message || 'Failed');
    } catch (e) {
        Swal.fire('Error', e.message, 'error');
    } finally { hideLoader(); }
}

async function loadSetting(settingId) {
    showLoader();
    try {
        const res  = await apiFetch(url(ROUTES.getSetting, settingId), 'GET');
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed');

        currentSetting   = data.setting;
        currentSettingId = settingId;
        availableSubjects= data.available_subjects || [];

        const className   = data.setting.schoolclass?.schoolclass || '—';
        const sessionName = data.setting.session?.session || '—';
        const termName    = data.setting.term?.term || 'All Terms';
        document.getElementById('editorContext').innerHTML = `<i class="ri-school-line me-2 text-primary"></i>${escapeHtml(className)}`;
        document.getElementById('editorSubContext').textContent = `${sessionName} · ${termName}`;

        document.getElementById('schoolDayStart').value      = data.setting.school_day_start?.slice(0,5) || '08:00';
        document.getElementById('schoolDayEnd').value        = data.setting.school_day_end?.slice(0,5) || '14:30';
        document.getElementById('periodDuration').value      = data.setting.period_duration_minutes || 40;
        document.getElementById('shortBreakDuration').value  = data.setting.short_break_duration_minutes || 20;
        document.getElementById('longBreakDuration').value   = data.setting.long_break_duration_minutes || 40;

        const activeDays = data.setting.active_days || ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        document.querySelectorAll('.active-day-checkbox').forEach(cb => cb.checked = activeDays.includes(cb.value));

        loadPeriodsIntoTable(data.setting.periods?.length ? data.setting.periods : [
            {name:'Period 1',type:'lesson'},{name:'Period 2',type:'lesson'},
            {name:'Short Break',type:'short_break'},{name:'Period 3',type:'lesson'},
            {name:'Period 4',type:'lesson'},{name:'Long Break',type:'long_break'},
            {name:'Period 5',type:'lesson'},{name:'Period 6',type:'lesson'},
        ]);

        loadConstraintsIntoTable(data.setting.constraints || []);

        document.getElementById('timetableEditor').style.display = '';
        document.getElementById('timetableEditor').scrollIntoView({ behavior: 'smooth', block: 'start' });
        showTab('periodsTab', document.querySelector('.tt-tab'));

    } catch (e) {
        Swal.fire('Error', 'Failed to load timetable: ' + e.message, 'error');
    } finally { hideLoader(); }
}

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
        const res = await apiFetch(ROUTES.saveSettings, 'POST', {
            setting_id:                   currentSettingId,
            school_day_start:             document.getElementById('schoolDayStart').value,
            school_day_end:               document.getElementById('schoolDayEnd').value,
            period_duration_minutes:      parseInt(document.getElementById('periodDuration').value),
            short_break_duration_minutes: parseInt(document.getElementById('shortBreakDuration').value),
            long_break_duration_minutes:  parseInt(document.getElementById('longBreakDuration').value),
            active_days: activeDays,
            periods,
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon:'success', title:'Saved!', text:'Settings saved successfully.', timer:1600, showConfirmButton:false });
            await loadSetting(currentSettingId);
        } else throw new Error(data.message || 'Failed');
    } catch (e) {
        Swal.fire('Error', e.message, 'error');
    } finally { hideLoader(); }
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
            <td>
                <select class="form-select form-select-sm preferred-days" multiple size="3">
                    ${genDayOptions(c.preferred_days||[])}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm avoid-days" multiple size="3">
                    ${genDayOptions(c.avoid_days||[])}
                </select>
            </td>
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
        const res  = await apiFetch(ROUTES.saveConstraints, 'POST', { setting_id: currentSettingId, constraints });
        const data = await res.json();
        if (data.success) Swal.fire({ icon:'success', title:'Saved!', timer:1400, showConfirmButton:false });
        else throw new Error(data.message || 'Failed');
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); }
}

// ============================================================================
// AUTO-GENERATE
// ============================================================================
async function generateTimetable() {
    const result = await Swal.fire({
        title: 'Auto-Generate Timetable?',
        html: 'This will <strong>clear the existing timetable</strong> and generate a new one based on your constraints.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1565C0',
        confirmButtonText: 'Yes, generate!',
    });
    if (!result.isConfirmed) return;

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.autoGenerate, 'POST', { setting_id: currentSettingId });
        const data = await res.json();
        if (data.success) {
            await loadTimetableGrid();
            showTab('gridTab', document.querySelectorAll('.tt-tab')[2]);
            Swal.fire({ icon:'success', title:'Generated!', text:'Timetable generated successfully.', timer:1800, showConfirmButton:false });
        } else throw new Error(data.message || 'Failed');
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); }
}

// ============================================================================
// TIMETABLE GRID
// ============================================================================
async function loadTimetableGrid() {
    if (!currentSettingId) return;

    const container = document.getElementById('timetableGridContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 text-muted">Loading timetable…</p></div>';

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
        const isBreak = period.is_break;
        html += `<tr>
            <td class="period-td">
                <div class="pname">${escapeHtml(period.name)}</div>
                <div class="ptime">${period.start_time} – ${period.end_time}</div>
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
                const sc = getSubjectColor(slot.subject_id);
                const borderStyle = sc ? `style="border-left:3px solid ${sc}"` : '';
                const avatarHtml = slot.teacher_picture
                    ? `<img src="${slot.teacher_picture}" class="cell-avatar" onerror="this.style.display='none'">`
                    : `<div class="cell-avatar-placeholder"><i class="ri-user-line"></i></div>`;
                const doubleBadge = slot.is_double ? '<span class="cell-double-badge">Double</span>' : '';
                const roomHtml = slot.room_name ? `<span class="cell-room"><i class="ri-door-line"></i> ${escapeHtml(slot.room_name)}</span>` : '';

                html += `<td onclick="openSlotModal(${period.id},'${day}')" ${borderStyle}>
                    <div class="tt-cell has-subject${slot.is_double?' is-double':''}">
                        ${avatarHtml}
                        <span class="cell-subject">${escapeHtml(slot.subject_code || slot.subject || '—')}</span>
                        <span class="cell-teacher">${escapeHtml((slot.teacher || '').split(' ')[0])}</span>
                        ${roomHtml}
                        ${doubleBadge}
                    </div></td>`;
            }
        });
        html += '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;
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
    document.getElementById('editSlotPeriodName').textContent = period.name + ' · ' + period.start_time + ' – ' + period.end_time;
    document.getElementById('editSlotDayName').textContent    = day;
    document.getElementById('editSlotContext').textContent    = period.name + ' · ' + day;
    document.getElementById('editSlotNotes').value   = slot.notes || '';
    document.getElementById('editSlotIsDouble').checked = slot.is_double || false;

    // Set room dropdown value using room_id
    if (roomTomSelect) {
        if (slot.room_id) {
            roomTomSelect.setValue(slot.room_id.toString(), true);
        } else {
            roomTomSelect.setValue('', true);
        }
    }

    // Avatar
    const avatarDiv = document.getElementById('editTeacherAvatar');
    if (slot.teacher_picture) {
        avatarDiv.innerHTML = `<img src="${slot.teacher_picture}" style="width:44px;height:44px;border-radius:50%;object-fit:cover">`;
    } else {
        avatarDiv.innerHTML = `<i class="ri-user-line text-white ri-xl"></i>`;
    }

    // Subject dropdown with term info
    const subjectSel = document.getElementById('editSlotSubject');
    subjectSel.innerHTML = '<option value="">— Free Period —</option>';
    availableSubjects.forEach(s => {
        const termText = s.term_name ? ` - ${s.term_name}` : '';
        const opt = new Option(`${s.subject_name} (${s.teacher_name})${termText}`, s.subject_id);
        opt.dataset.teacherId   = s.teacher_id;
        opt.dataset.teacherName = s.teacher_name;
        opt.selected = (slot.subject_id == s.subject_id);
        subjectSel.appendChild(opt);
    });

    // Teacher dropdown
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
}

function onSubjectChange() {
    const sel = document.getElementById('editSlotSubject');
    const opt = sel.options[sel.selectedIndex];
    const tid = opt?.dataset?.teacherId;
    if (tid) document.getElementById('editSlotTeacher').value = tid;
}

function onTeacherChange() {
    const tid = document.getElementById('editSlotTeacher').value;
    if (!tid) return;
    const t = allTeachers.find(t => t.id == tid);
    const avatarDiv = document.getElementById('editTeacherAvatar');
    if (t?.picture) {
        avatarDiv.innerHTML = `<img src="${t.picture}" style="width:44px;height:44px;border-radius:50%;object-fit:cover" onerror="this.parentElement.innerHTML='<i class=\\'ri-user-line text-white ri-xl\\'></i>'">`;
    }
}

// ============================================================================
// SAVE SLOT — FIXED with room_id
// ============================================================================
async function saveSlot() {
    const roomId = roomTomSelect ? (roomTomSelect.getValue() || null) : null;

    const payload = {
        setting_id: parseInt(document.getElementById('editSlotSettingId').value),
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
            bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
            await loadTimetableGrid();
            Swal.fire({ icon:'success', title:'Saved!', timer:1200, showConfirmButton:false });
        } else if (result.conflict) {
            Swal.fire('Teacher Conflict', result.message, 'warning');
        } else {
            throw new Error(result.message || 'Save failed');
        }
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); }
}

// ============================================================================
// CONFLICTS
// ============================================================================
async function checkConflicts() {
    if (!currentSettingId) return;

    showLoader();
    try {
        const res  = await apiFetch(url(ROUTES.checkConflicts, currentSettingId), 'GET');
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed');

        const container = document.getElementById('conflictsList');
        const badge     = document.getElementById('conflictBadgeTab');

        if (!data.conflict_count) {
            badge.style.display = 'none';
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-check-double-line ri-3x d-block mb-3 text-success"></i>
                    <h6 class="text-success">No Conflicts Found</h6>
                    <p class="text-muted mb-0">All teachers are properly scheduled with no double-bookings.</p>
                </div>`;
        } else {
            badge.style.display = '';
            badge.textContent   = data.conflict_count;
            let html = `<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                <i class="ri-alert-line ri-xl"></i>
                Found <strong>${data.conflict_count}</strong> conflict(s) requiring attention.
            </div>`;
            data.conflicts.forEach(c => {
                const avatarHtml = c.teacher_picture
                    ? `<img src="${c.teacher_picture}" class="conflict-avatar">`
                    : `<div class="conflict-avatar-ph"><i class="ri-user-line ri-xl"></i></div>`;
                html += `<div class="conflict-item">
                    ${avatarHtml}
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${escapeHtml(c.teacher || '—')}</div>
                        <div class="text-muted" style="font-size:13px">${c.day} &bull; ${escapeHtml(c.period)} ${c.period_time ? '(' + c.period_time + ')' : ''}</div>
                        <div class="mt-1" style="font-size:12px">
                            <span class="badge bg-primary-subtle text-primary">${escapeHtml(c.class_a||'')}</span>
                            <span class="mx-1 text-muted">&amp;</span>
                            <span class="badge bg-primary-subtle text-primary">${escapeHtml(c.class_b||'')}</span>
                            <span class="text-muted ms-2">are both scheduled for ${escapeHtml(c.subject_a||'—')}</span>
                        </div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); }
}

// ============================================================================
// NOTIFICATIONS
// ============================================================================
async function sendNotifications() {
    const result = await Swal.fire({
        title: 'Send Notifications',
        text: 'Send timetable notifications to all assigned teachers?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1565C0',
        confirmButtonText: 'Yes, send!',
    });
    if (!result.isConfirmed) return;

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.sendNotifications, 'POST', { setting_id: currentSettingId, type: 'weekly_preview' });
        const data = await res.json();
        if (data.success) Swal.fire({ icon:'success', title:'Sent!', text: data.message, timer:2000, showConfirmButton:false });
        else throw new Error(data.message || 'Failed');
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); }
}

// ============================================================================
// EXPORT
// ============================================================================
function exportTimetable(format) {
    if (!currentSettingId) return Swal.fire('Error', 'No timetable loaded.', 'error');
    const orientation = document.getElementById('exportOrientation')?.value || 'horizontal';
    const exportUrl = url(ROUTES.export, currentSettingId) + '?format=' + format + '&orientation=' + orientation;
    if (format === 'pdf') { window.open(exportUrl, '_blank'); }
    else { window.location.href = exportUrl; }
}

// ============================================================================
// WHOLE SCHOOL EXPORT
// ============================================================================
function openWholeSchoolExportModal() {
    new bootstrap.Modal(document.getElementById('wholeSchoolExportModal')).show();
}

async function exportWholeSchoolTimetable() {
    const sessionId = document.getElementById('wholeSchoolSessionId').value;
    const termId = document.getElementById('wholeSchoolTermId').value;
    const orientation = document.getElementById('wholeSchoolOrientation').value;

    if (!sessionId) {
        return Swal.fire('Error', 'Please select a session.', 'error');
    }

    const exportUrl = ROUTES.exportWholeSchool +
        '?session_id=' + sessionId +
        '&term_id=' + (termId || '') +
        '&orientation=' + orientation;

    window.open(exportUrl, '_blank');
}

// ============================================================================
// DELETE / CLONE
// ============================================================================
async function deleteSetting(settingId) {
    const result = await Swal.fire({
        title: 'Delete Timetable?',
        text: 'This will permanently delete this timetable and all its slots.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'Yes, delete!',
    });
    if (!result.isConfirmed) return;

    showLoader();
    try {
        const res  = await apiFetch(url(ROUTES.deleteSetting, settingId), 'DELETE');
        const data = await res.json();
        if (data.success) { Swal.fire({ icon:'success', title:'Deleted!', timer:1400, showConfirmButton:false }); setTimeout(() => location.reload(), 1400); }
        else throw new Error(data.message || 'Failed');
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); }
}

function cloneSetting(settingId) {
    pendingCloneId = settingId;
    new bootstrap.Modal(document.getElementById('cloneModal')).show();
}

async function confirmClone() {
    if (!pendingCloneId) return;
    bootstrap.Modal.getInstance(document.getElementById('cloneModal')).hide();

    showLoader();
    try {
        const res  = await apiFetch(ROUTES.cloneSetting, 'POST', {
            setting_id:     pendingCloneId,
            new_session_id: document.getElementById('cloneSessionId').value || null,
            new_term_id:    document.getElementById('cloneTermId').value || null,
        });
        const data = await res.json();
        if (data.success) { Swal.fire({ icon:'success', title:'Cloned!', timer:1400, showConfirmButton:false }); setTimeout(() => location.reload(), 1400); }
        else throw new Error(data.message || 'Failed');
    } catch (e) { Swal.fire('Error', e.message, 'error'); }
    finally { hideLoader(); pendingCloneId = null; }
}

// ============================================================================
// UTILITIES
// ============================================================================
function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}

function apiFetch(endpoint, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    };
    if (body && method !== 'GET') {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(body);
    }
    return fetch(endpoint, opts);
}

function showLoader() {
    Swal.fire({ title: 'Processing…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
}
function hideLoader() { Swal.close(); }
</script>
