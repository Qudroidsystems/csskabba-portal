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

.position-badge, .position-total-badge, .arm-position-badge, .arm-position-cum-badge {
    transition: transform .22s cubic-bezier(0.34,1.4,0.64,1), opacity .15s ease;
}
.pos-flash { animation: posFlash .5s cubic-bezier(0.34,1.4,0.64,1); }

/* ROW ENTRANCE */
#scoresheetTableBody tr[data-id] {
    opacity: 0; transform: translateY(14px);
    transition: opacity .38s cubic-bezier(.25,.46,.45,.94), transform .38s cubic-bezier(.25,.46,.45,.94), background .18s ease;
    will-change: opacity, transform;
}
#scoresheetTableBody tr[data-id].row-visible { opacity: 1; transform: translateY(0); }

/* Lock Badge */
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
.lock-badge.scheduled { background: #e0e7ff; color: #4338ca; }

/* Admin Banner */
.admin-banner {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-left: 4px solid #0284c7;
    border-radius: var(--ss-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
}

/* SCORE TOOLTIP */
#scoreTooltip {
    display: none; position: fixed; z-index: 99990;
    background: #fff; border: 0.5px solid #cbd5e1; border-radius: 10px;
    padding: 10px 13px; width: 230px;
    box-shadow: 0 4px 20px rgba(0,0,0,.10), 0 1px 4px rgba(0,0,0,.06);
    pointer-events: none; font-family: inherit; opacity: 0; transition: opacity .15s ease;
}
</style>

{{-- Apple-style Save Modal --}}
<div id="ssSaveOverlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.30); align-items:center; justify-content:center;">
    <div id="ssSaveModal" style="background:#fff; border-radius:20px; padding:32px 36px 26px; width:310px; text-align:center;">
        <div class="ss-modal-title" id="ssSaveTitle">Saving scores</div>
        <div class="ss-modal-sub" id="ssSaveSub">Please wait…</div>
        <div class="ss-progress-track" style="height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden; margin-bottom:10px;">
            <div class="ss-progress-fill" id="ssSaveFill" style="height:100%; width:0%; background:#1e3a5f; transition:width .38s;"></div>
        </div>
        <div class="ss-count-row" style="display:flex; justify-content:space-between; font-size:11px;">
            <span>Saved</span>
            <span id="ssSaveCountNum">0 / 0</span>
        </div>
    </div>
</div>

{{-- Score Input Tooltip --}}
<div id="scoreTooltip">
    <div class="tip-top" style="display:flex; gap:8px; margin-bottom:8px;">
        <img id="stAvatar" style="width:28px;height:28px;border-radius:50%;">
        <div><div id="stName" style="font-weight:600;"></div><div id="stMeta" style="font-size:10px;color:#666;"></div></div>
    </div>
    <div id="stProgFill"></div>
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
                    <strong class="d-block">Admin Score Entry Mode</strong>
                    <small>Entering scores on behalf of: <strong>{{ $teacher->name }}</strong> |
                        Subject: <strong>{{ $subjectClass->subject->subject }}</strong> |
                        Class: <strong>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm->arm ?? '' }}</strong>
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">{{ $term->term }}</span>
                <span class="badge bg-info">{{ $session->session }}</span>
            </div>
        </div>
    </div>

    {{-- Lock Status Banner --}}
    @if($globalLock || ($lockedCount ?? 0) > 0 || !$teacherEditingEnabled)
    <div class="alert alert-warning mb-3">
        <div class="d-flex align-items-center gap-3">
            <i class="ri-lock-line fs-3"></i>
            <div>
                @if(!$teacherEditingEnabled)
                    <strong>Teacher Editing Disabled</strong><br>
                    <small>Teacher editing has been disabled for this subject by an administrator.</small>
                @elseif($globalLock)
                    <strong>Global Lock Active</strong><br>
                    <small>{{ $globalLock->reason ?? 'No reason provided' }}</small>
                    @if($globalLock->scheduled_unlock_at)
                        <small class="d-block">Scheduled unlock: {{ \Carbon\Carbon::parse($globalLock->scheduled_unlock_at)->format('Y-m-d H:i:s') }}</small>
                    @endif
                @elseif(($lockedCount ?? 0) > 0)
                    <strong>{{ $lockedCount }} of {{ $broadsheets->count() }} scoresheets are locked</strong>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($broadsheets->isNotEmpty())
    @php
        $first = $broadsheets->first();
        $total = $broadsheets->count();
        $passed = $broadsheets->filter(fn($b) => ($b->total ?? 0) >= 40)->count();
        $avg = $total > 0 ? round($broadsheets->avg('total'), 1) : 0;
        $passRate = $total > 0 ? round($passed / $total * 100) : 0;
        $gradeColors = ['A'=>'#16a34a','B'=>'#2563eb','C'=>'#7c3aed','D'=>'#d97706','F'=>'#dc2626'];
    @endphp

    {{-- Stats Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-primary">{{ $total }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-warning">{{ $avg }}</div>
                <div class="stat-label">Class Average</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-success">{{ $passRate }}%</div>
                <div class="stat-label">Pass Rate</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value">{{ $passed }}</div>
                <div class="stat-label">Passed</div>
            </div>
        </div>
    </div>

    {{-- Admin Controls Card --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="ri-settings-4-line me-2"></i>Admin Controls & Lock Management</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="ri-lock-line me-1"></i> Lock Controls</h6>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button class="btn btn-sm btn-outline-warning" id="lockAllBtn"><i class="ri-lock-line me-1"></i>Lock All</button>
                        <button class="btn btn-sm btn-outline-danger" id="globalLockBtn"><i class="ri-global-line me-1"></i>Global Lock</button>
                        <button class="btn btn-sm btn-outline-info" id="lockWithScheduleBtn"><i class="ri-calendar-line me-1"></i>Lock with Schedule</button>
                        <button class="btn btn-sm btn-outline-success" id="unlockAllBtn"><i class="ri-lock-unlock-line me-1"></i>Unlock All</button>
                        <button class="btn btn-sm btn-outline-secondary" id="toggleTeacherEditBtn">
                            <i class="ri-user-settings-line me-1"></i>{{ $teacherEditingEnabled ? 'Disable' : 'Enable' }} Teacher Editing
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6><i class="ri-history-line me-1"></i> Audit Summary</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="p-2 bg-success-light rounded text-center">
                                <small>Admin Entered</small>
                                <div class="fw-bold">{{ $broadsheets->where('entry_source', 'admin')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-primary-light rounded text-center">
                                <small>Teacher Entered</small>
                                <div class="fw-bold">{{ $broadsheets->where('entry_source', 'teacher')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Scoresheet Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $pagetitle }}</h5>
                <div class="d-flex gap-2">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search..." style="width:200px;">
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal"><i class="ri-eye-line"></i> Columns</button>
                    <button class="btn btn-sm btn-warning" id="downloadMarksSheet"><i class="ri-file-pdf-line"></i> Marks Sheet</button>
                    <button class="btn btn-sm btn-danger" id="downloadScoresPdf"><i class="ri-file-pdf-2-line"></i> Scores PDF</button>
                    <button class="btn btn-sm btn-success" id="downloadExcel"><i class="ri-download-line"></i> Export</button>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ri-upload-line"></i> Import</button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="scoresheetTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="40"><input type="checkbox" id="checkAll"></th>
                            <th>#</th>
                            <th>Adm No</th>
                            <th>Student Name</th>
                            @foreach($assessments as $assessment)
                                <th class="text-center">{{ $assessment->name }}<br><small>({{ $assessment->max_score }})</small></th>
                            @endforeach
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Cum</th>
                            <th class="text-center">Class Pos</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Lock</th>
                            <th class="text-center">Last Modified</th>
                        </tr>
                    </thead>
                    <tbody id="scoresheetTableBody">
                        @php $sn = 0; @endphp
                        @forelse($broadsheets as $broadsheet)
                            @php
                                $sn++;
                                $totalRaw = 0;
                                foreach($assessments as $a) {
                                    $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                                    $totalRaw += $so ? $so->score : 0;
                                }
                                $cum = $broadsheet->cum ?? 0;
                                $isLocked = $broadsheet->is_locked || $globalLock || !$teacherEditingEnabled;
                                $hasScheduledUnlock = !is_null($broadsheet->scheduled_unlock_at);
                                $rowClass = $isLocked ? 'row-locked' : ($broadsheet->vettedstatus === '1' ? 'row-vetted' : ($broadsheet->vettedstatus === '0' ? 'row-not-vetted' : 'row-pending'));
                                $totalColor = $totalRaw >= 70 ? 'success' : ($totalRaw >= 50 ? 'info' : ($totalRaw >= 40 ? 'warning' : 'danger'));
                            @endphp
                            <tr class="{{ $rowClass }}" data-id="{{ $broadsheet->id }}" data-name="{{ $broadsheet->lname }}, {{ $broadsheet->fname }}" data-admissionno="{{ $broadsheet->admissionno }}">
                                <td><input type="checkbox" class="score-checkbox" data-id="{{ $broadsheet->id }}" {{ $isLocked ? 'disabled' : '' }}></td>
                                <td>{{ $sn }}</td>
                                <td>{{ $broadsheet->admissionno }}</td>
                                <td>{{ $broadsheet->lname }}, {{ $broadsheet->fname }}</td>
                                @foreach($assessments as $assessment)
                                    @php $scoreVal = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first()->score ?? 0; @endphp
                                    <td class="text-center">
                                        <input type="number" class="score-input" data-field="{{ $assessment->id }}" data-max="{{ $assessment->max_score }}" data-id="{{ $broadsheet->id }}" value="{{ $scoreVal }}" min="0" max="{{ $assessment->max_score }}" step="0.1" {{ $isLocked ? 'disabled' : '' }} style="width:70px;">
                                    </td>
                                @endforeach
                                <td class="text-center"><span class="badge bg-{{ $totalColor }}-subtle text-{{ $totalColor }}">{{ number_format($totalRaw, 1) }}</span></td>
                                <td class="text-center"><span class="grade-badge">{{ $broadsheet->grade ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ number_format($cum, 1) }}</span></td>
                                <td class="text-center"><span class="badge bg-primary">{{ $broadsheet->position ?? '-' }}</span></td>
                                <td class="text-center">
                                    @if($broadsheet->vettedstatus === '1') <span class="badge bg-success">Vetted</span>
                                    @elseif($broadsheet->vettedstatus === '0') <span class="badge bg-danger">Not Vetted</span>
                                    @else <span class="badge bg-warning">Pending</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($globalLock)
                                        <span class="lock-badge global"><i class="ri-global-line"></i> Global</span>
                                    @elseif($broadsheet->is_locked)
                                        <span class="lock-badge individual" title="{{ $broadsheet->lock_reason }}">
                                            <i class="ri-lock-line"></i> Locked
                                            @if($hasScheduledUnlock)<br><small class="text-muted">Unlocks: {{ \Carbon\Carbon::parse($broadsheet->scheduled_unlock_at)->format('d/m H:i') }}</small>@endif
                                        </span>
                                    @elseif(!$teacherEditingEnabled)
                                        <span class="lock-badge disabled"><i class="ri-user-settings-line"></i> Disabled</span>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary lock-individual-btn" data-id="{{ $broadsheet->id }}" data-name="{{ $broadsheet->lname }}, {{ $broadsheet->fname }}"><i class="ri-lock-unlock-line"></i> Lock</button>
                                    @endif
                                </td>
                                <td class="text-center small">
                                    @if($broadsheet->last_modified_at)
                                        {{ \Carbon\Carbon::parse($broadsheet->last_modified_at)->format('d/m H:i') }}<br>
                                        <span class="text-muted">{{ optional($broadsheet->lastModifiedBy)->name ?? 'Unknown' }}</span>
                                        @if($broadsheet->entry_source === 'admin')<span class="badge bg-info">Admin</span>@endif
                                    @else -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="20" class="text-center py-4">No scores available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($broadsheets->isNotEmpty())
            <div class="p-3 border-top bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <button class="btn btn-sm btn-primary" id="selectAllScores">Select All</button>
                        <button class="btn btn-sm btn-secondary" id="clearAllScores">Clear</button>
                        <button class="btn btn-sm btn-danger" id="deleteSelectedScoresBtn">Delete Selected</button>
                    </div>
                    <button class="btn btn-sm btn-success" id="bulkUpdateScores"><i class="ri-save-line"></i> Save All</button>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modals --}}
    <div class="modal fade" id="columnVisibilityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Column Visibility</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Student Info</h6>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-checkbox" checked> <label>Select</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-sn" checked> <label>SN</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-admissionno" checked> <label>Adm No</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-name" checked> <label>Name</label></div>
                        </div>
                        <div class="col-md-6">
                            <h6>Scores & Metrics</h6>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-total" checked> <label>Total</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-grade" checked> <label>Grade</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-cum" checked> <label>Cum</label></div>
                            <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" data-col="col-position" checked> <label>Position</label></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Import Scores</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="schoolclass_id" value="{{ $schoolclass->id }}">
                        <input type="hidden" name="subjectclass_id" value="{{ $subjectclassId }}">
                        <input type="hidden" name="staff_id" value="{{ $teacherId }}">
                        <input type="hidden" name="term_id" value="{{ $termId }}">
                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                        <div class="mb-3">
                            <label>Excel File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
const routes = {
    singleUpdate: '{{ route("admin.score-entry.single-update") }}',
    bulkUpdate: '{{ route("admin.score-entry.bulk-update") }}',
    destroy: '{{ route("admin.score-entry.destroy") }}',
    results: '{{ route("admin.score-entry.results") }}',
    export: '{{ route("admin.score-entry.export") }}',
    import: '{{ route("admin.score-entry.import") }}',
    downloadMarksSheet: '{{ route("admin.score-entry.download-marks-sheet") }}',
    downloadScoresPdf: '{{ route("admin.score-entry.download-scores-pdf") }}',
    lockScoresheet: '{{ route("admin.score-entry.lock-scoresheet") }}',
    lockBatch: '{{ route("admin.score-entry.lock-batch") }}',
    lockBatchWithSchedule: '{{ route("admin.score-entry.lock-batch-with-schedule") }}',
    unlockBatch: '{{ route("admin.score-entry.unlock-batch") }}',
    disableTeacherEditing: '{{ route("admin.score-entry.disable-teacher-editing") }}',
    enableTeacherEditing: '{{ route("admin.score-entry.enable-teacher-editing") }}',
};

function showToast(msg, type) {
    let bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#2563eb';
    let toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white show position-fixed bottom-0 end-0 m-3';
    toast.style.background = bg;
    toast.innerHTML = `<div class="d-flex p-3"><div>${msg}</div><button class="btn-close btn-close-white ms-2" onclick="this.closest(\'.toast\').remove()"></button></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// Lock Functions
function lockScoresheet(id, name) {
    Swal.fire({
        title: `Lock ${name}?`,
        input: 'textarea',
        inputLabel: 'Reason (optional)',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Lock'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(routes.lockScoresheet, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ broadsheet_id: id, reason: result.value })
            }).then(r => r.json()).then(data => {
                if (data.success) { showToast('Locked successfully', 'success'); location.reload(); }
                else showToast(data.message, 'error');
            });
        }
    });
}

document.getElementById('lockAllBtn')?.addEventListener('click', () => {
    Swal.fire({
        title: 'Lock all scoresheets?',
        input: 'textarea',
        inputLabel: 'Reason',
        showCancelButton: true,
        confirmButtonColor: '#dc2626'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(routes.lockBatch, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    subjectclass_id: {{ $subjectclassId }},
                    lock_type: 'individual',
                    reason: result.value
                })
            }).then(r => r.json()).then(data => { showToast(data.message, 'success'); location.reload(); });
        }
    });
});

document.getElementById('globalLockBtn')?.addEventListener('click', () => {
    Swal.fire({
        title: 'Apply Global Lock?',
        input: 'textarea',
        inputLabel: 'Reason',
        showCancelButton: true,
        confirmButtonColor: '#dc2626'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(routes.lockBatch, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    subjectclass_id: {{ $subjectclassId }},
                    lock_type: 'global',
                    reason: result.value
                })
            }).then(r => r.json()).then(data => { showToast(data.message, 'success'); location.reload(); });
        }
    });
});

document.getElementById('lockWithScheduleBtn')?.addEventListener('click', () => {
    Swal.fire({
        title: 'Schedule Lock',
        html: `
            <textarea id="lockReason" class="swal2-textarea" placeholder="Reason"></textarea>
            <input type="datetime-local" id="scheduleDate" class="form-control mt-2" required>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        preConfirm: () => {
            return { reason: document.getElementById('lockReason').value, date: document.getElementById('scheduleDate').value };
        }
    }).then(result => {
        if (result.isConfirmed && result.value.date) {
            fetch(routes.lockBatchWithSchedule, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    subjectclass_id: {{ $subjectclassId }},
                    lock_type: 'individual',
                    reason: result.value.reason,
                    scheduled_unlock_at: result.value.date
                })
            }).then(r => r.json()).then(data => { showToast(data.message, 'success'); location.reload(); });
        }
    });
});

document.getElementById('unlockAllBtn')?.addEventListener('click', () => {
    Swal.fire({
        title: 'Unlock all?',
        text: 'This will unlock all scoresheets in this subject.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(routes.unlockBatch, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    term_id: {{ $termId }},
                    session_id: {{ $sessionId }},
                    subjectclass_id: {{ $subjectclassId }},
                    unlock_type: 'individual'
                })
            }).then(r => r.json()).then(data => { showToast(data.message, 'success'); location.reload(); });
        }
    });
});

document.getElementById('toggleTeacherEditBtn')?.addEventListener('click', () => {
    let isEnabled = {{ $teacherEditingEnabled ? 'true' : 'false' }};
    let url = isEnabled ? routes.disableTeacherEditing : routes.enableTeacherEditing;
    Swal.fire({
        title: isEnabled ? 'Disable Teacher Editing?' : 'Enable Teacher Editing?',
        text: isEnabled ? 'Teachers will not be able to edit scores.' : 'Teachers will regain editing access.',
        icon: 'warning',
        showCancelButton: true
    }).then(result => {
        if (result.isConfirmed) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ subjectclass_id: {{ $subjectclassId }} })
            }).then(r => r.json()).then(data => { showToast(data.message, 'success'); location.reload(); });
        }
    });
});

document.querySelectorAll('.lock-individual-btn').forEach(btn => {
    btn.addEventListener('click', () => lockScoresheet(btn.dataset.id, btn.dataset.name));
});

// Save Functions
function bulkSave() {
    let scores = [];
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
        if (row.querySelector('.lock-badge')) return;
        let assessments = {};
        row.querySelectorAll('.score-input').forEach(inp => { assessments[inp.dataset.field] = parseFloat(inp.value) || 0; });
        if (Object.keys(assessments).length) scores.push({ id: row.dataset.id, assessments });
    });
    if (!scores.length) return;

    fetch(routes.bulkUpdate, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ scores, term_id: {{ $termId }}, session_id: {{ $sessionId }}, subjectclass_id: {{ $subjectclassId }}, staff_id: {{ $teacherId }}, schoolclass_id: {{ $schoolclass->id }}, is_sub: false })
    }).then(r => r.json()).then(data => { showToast(data.message, 'success'); location.reload(); });
}

document.getElementById('bulkUpdateScores')?.addEventListener('click', bulkSave);

// Search
document.getElementById('searchInput')?.addEventListener('input', function() {
    let term = this.value.toLowerCase();
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
        let name = (row.dataset.name || '').toLowerCase();
        let adm = (row.dataset.admissionno || '').toLowerCase();
        row.style.display = name.includes(term) || adm.includes(term) ? '' : 'none';
    });
});

// Checkboxes
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.score-checkbox:not(:disabled)').forEach(cb => cb.checked = this.checked);
});
document.getElementById('selectAllScores')?.addEventListener('click', () => {
    document.querySelectorAll('.score-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
});
document.getElementById('clearAllScores')?.addEventListener('click', () => {
    document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = false);
});

// Delete Selected
document.getElementById('deleteSelectedScoresBtn')?.addEventListener('click', () => {
    let ids = Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb => cb.dataset.id);
    if (!ids.length) return;
    Swal.fire({ title: 'Delete selected?', text: 'This cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626' }).then(r => {
        if (r.isConfirmed) {
            Promise.all(ids.map(id => fetch(routes.destroy, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }, body: JSON.stringify({ id, type: 'terminal' }) }).then(r => r.json()))).then(() => location.reload());
        }
    });
});

// Downloads
document.getElementById('downloadMarksSheet')?.addEventListener('click', () => window.open(routes.downloadMarksSheet + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id }}`, '_blank'));
document.getElementById('downloadScoresPdf')?.addEventListener('click', () => window.open(routes.downloadScoresPdf + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id }}`, '_blank'));
document.getElementById('downloadExcel')?.addEventListener('click', () => window.open(routes.export + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id }}`, '_blank'));

// Import
document.getElementById('importForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    fetch(routes.import, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: formData }).then(r => r.json()).then(data => {
        if (data.success) { showToast(data.message, 'success'); location.reload(); }
        else showToast(data.message, 'error');
    });
});

// Column visibility
document.querySelectorAll('.col-toggle').forEach(cb => {
    cb.addEventListener('change', function() {
        document.querySelectorAll(`.${this.dataset.col}`).forEach(el => el.style.display = this.checked ? '' : 'none');
    });
});

// Row entrance animation
document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach((row, i) => {
    setTimeout(() => row.classList.add('row-visible'), i * 30);
});
</script>
@endsection
