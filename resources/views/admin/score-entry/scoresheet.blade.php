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

.admin-banner {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-left: 4px solid #0284c7;
    border-radius: var(--ss-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
}

.lock-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.lock-badge.global { background: #fee2e2; color: #dc2626; }
.lock-badge.individual { background: #fef3c7; color: #d97706; }
.lock-badge.disabled { background: #e5e7eb; color: #6b7280; }

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
    border-bottom: 1px solid #e5e7eb;
}
.score-entry-modal .assessment-score-row:last-child {
    border-bottom: none;
}
.score-entry-modal .score-input-large {
    width: 100px;
    height: 40px;
    text-align: center;
    font-size: 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
}
.score-entry-modal .score-input-large:focus {
    outline: none;
    border-color: var(--ss-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.15);
}

@media (max-width: 768px) {
    .score-input { width: 64px; min-width: 64px; height: 42px; font-size: 1rem; }
}
</style>

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
                <button type="button" class="btn btn-primary" id="saveModalScoresBtn">
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
        $first = $broadsheets->first();
        $total = $broadsheets->count();
        $passed = $broadsheets->filter(fn($b) => ($b->total ?? 0) >= 40)->count();
        $avg = $total > 0 ? round($broadsheets->avg('total'), 1) : 0;
        $passRate = $total > 0 ? round($passed / $total * 100) : 0;
    @endphp

    {{-- Stats Row --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2" style="background:var(--ss-primary);">
                            <i class="ri-book-2-line text-white fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold" style="color:var(--ss-primary);">
                                {{ $first->subject }} <small class="text-muted">({{ $first->subject_code }})</small>
                            </h5>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <span class="badge bg-primary-subtle text-primary">{{ $first->schoolclass }} {{ $first->arm }}</span>
                                <span class="badge bg-info-subtle text-info">{{ $first->term }} | {{ $first->session }}</span>
                                <span class="badge bg-warning-subtle text-warning">{{ $teacher->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-2 h-100">
                <div class="col-4"><div class="stat-card text-center"><div class="stat-value text-primary">{{ $total }}</div><div class="stat-label">Students</div></div></div>
                <div class="col-4"><div class="stat-card text-center"><div class="stat-value" style="color:var(--ss-warning);">{{ $avg }}</div><div class="stat-label">Avg</div></div></div>
                <div class="col-4"><div class="stat-card text-center"><div class="stat-value" style="color:var(--ss-success);">{{ $passRate }}%</div><div class="stat-label">Pass Rate</div></div></div>
            </div>
        </div>
    </div>
    @endif

    {{-- MAIN SCORESHEET CARD --}}
    <div class="row"><div class="col-12"><div class="card border-0 shadow-sm">

        <div class="card-header d-flex align-items-center flex-wrap gap-2 py-3" style="background:var(--ss-primary);">
            <div class="flex-grow-1">
                <h5 class="mb-0 text-white fw-semibold">{{ $pagetitle }}</h5>
            </div>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width:200px;">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                    <button class="btn btn-light" id="clearSearch"><i class="ri-close-line"></i></button>
                </div>
                @if($broadsheets->isNotEmpty())
                    <button class="btn btn-sm btn-success" id="downloadExcel"><i class="ri-download-line"></i> Excel</button>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ri-upload-line"></i> Import</button>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0" id="scoresheetTable">
                <thead>
                    <tr>
                        <th style="width:30px;"><input type="checkbox" id="checkAll"></th>
                        <th>SN</th>
                        <th>Adm. No</th>
                        <th>Student Name</th>
                        @foreach($assessments as $assessment)
                            <th class="text-center">{{ $assessment->name }}<br><small>({{ $assessment->max_score }})</small></th>
                        @endforeach
                        <th class="text-center">Total</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">BF</th>
                        <th class="text-center">Cum</th>
                        <th class="text-center">Action</th>
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
                            $avatarUrl = $broadsheet->picture ? asset('storage/student_avatars/'.basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg');
                        @endphp
                        <tr data-id="{{ $broadsheet->id }}"
                            data-name="{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}"
                            data-admission="{{ $broadsheet->admissionno ?? '' }}"
                            data-avatar="{{ $avatarUrl }}"
                            data-bf="{{ $broadsheet->bf ?? 0 }}">

                            <td><input type="checkbox" class="score-checkbox" data-id="{{ $broadsheet->id }}" {{ $isLocked ? 'disabled' : '' }}></td>
                            <td>{{ ++$i }}</td>
                            <td>{{ $broadsheet->admissionno ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $avatarUrl }}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                    {{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}
                                </div>
                            </td>
                            @foreach($assessments as $assessment)
                                @php $scoreValue = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first()->score ?? 0; @endphp
                                <td class="text-center">
                                    <input type="number" class="score-input" data-field="{{ $assessment->id }}"
                                           data-max="{{ $assessment->max_score }}" data-id="{{ $broadsheet->id }}"
                                           value="{{ $scoreValue }}" step="0.1" {{ $isLocked ? 'disabled' : '' }}>
                                </td>
                            @endforeach
                            <td class="text-center"><span class="total-badge">{{ number_format($rowTotal, 1) }}</span></td>
                            <td class="text-center"><span class="grade-badge">{{ $broadsheet->grade ?? '-' }}</span></td>
                            <td class="text-center"><span class="bf-badge">{{ number_format($broadsheet->bf ?? 0, 1) }}</span></td>
                            <td class="text-center"><span class="cum-badge">{{ number_format($broadsheet->cum ?? 0, 1) }}</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary edit-scores-btn"
                                        data-id="{{ $broadsheet->id }}"
                                        data-name="{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}"
                                        data-admission="{{ $broadsheet->admissionno ?? '' }}"
                                        data-avatar="{{ $avatarUrl }}"
                                        data-bf="{{ $broadsheet->bf ?? 0 }}">
                                    <i class="ri-edit-line"></i> Edit Scores
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 6 + count($assessments) }}" class="text-center py-4">No scores available.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @if($broadsheets->isNotEmpty())
            <div class="p-3 border-top">
                <div class="d-flex justify-content-between gap-2">
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" id="selectAllBtn">Select All</button>
                        <button class="btn btn-sm btn-danger" id="deleteSelectedBtn">Delete Selected</button>
                    </div>
                    <button class="btn btn-success btn-sm" id="bulkSaveBtn"><i class="ri-save-line"></i> Save All</button>
                </div>
            </div>
            @endif
        </div>
    </div></div></div>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Import Scores</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-body">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Upload Excel file exported from this scoresheet</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div></div></div>

<script>
// CSRF Token
const CSRF = '{{ csrf_token() }}';

// Routes
const routes = {
    singleUpdate: '{{ route("admin.score-entry.single-update") }}',
    bulkUpdate: '{{ route("admin.score-entry.bulk-update") }}',
    destroy: '{{ route("admin.score-entry.destroy") }}',
    export: '{{ route("admin.score-entry.export") }}',
    import: '{{ route("admin.score-entry.import") }}',
};

// Global variables
let currentEditBroadsheetId = null;

// Toast function
function showToast(msg, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
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
            term_id: {{ $termId ?? 0 }},
            session_id: {{ $sessionId ?? 0 }},
            subjectclass_id: {{ $subjectclassId ?? 0 }},
            schoolclass_id: {{ $schoolclass->id ?? 0 }},
            staff_id: {{ $teacherId ?? 0 }}
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.classList.add('is-saved');
            setTimeout(() => input.classList.remove('is-saved'), 1000);
            input.dataset.original = input.value;

            if (data.data?.total) {
                row.querySelector('.total-badge').textContent = parseFloat(data.data.total).toFixed(1);
            }
            if (data.data?.grade) {
                row.querySelector('.grade-badge').textContent = data.data.grade;
            }
            if (data.data?.cum) {
                row.querySelector('.cum-badge').textContent = parseFloat(data.data.cum).toFixed(1);
            }
        } else {
            showToast(data.message || 'Error saving score', 'danger');
            input.value = originalValue;
        }
    })
    .catch(err => {
        showToast('Network error', 'danger');
        input.value = originalValue;
    });
}

// Open Score Entry Modal
function openScoreEntryModal(broadsheetId, studentName, studentAdmission, studentAvatar, bf) {
    console.log('Opening modal for:', broadsheetId, studentName);

    const modalElement = document.getElementById('scoreEntryModal');
    const modal = new bootstrap.Modal(modalElement);

    // Set student info
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalStudentAdmission').textContent = `Admission No: ${studentAdmission}`;
    document.getElementById('modalStudentAvatar').src = studentAvatar;

    // Get current scores from the table row
    const row = document.querySelector(`#scoresheetTableBody tr[data-id="${broadsheetId}"]`);
    const assessmentsData = @json($assessments->map(function($a) {
        return ['id' => $a->id, 'name' => $a->name, 'max_score' => $a->max_score];
    }));

    let currentTotal = 0;
    const scoresMap = {};

    if (row) {
        row.querySelectorAll('.score-input').forEach(input => {
            const assessmentId = input.dataset.field;
            const score = parseFloat(input.value) || 0;
            scoresMap[assessmentId] = score;
            currentTotal += score;
        });
    }

    const grade = currentTotal >= 70 ? 'A' : (currentTotal >= 60 ? 'B' : (currentTotal >= 50 ? 'C' : (currentTotal >= 40 ? 'D' : 'F')));
    const cum = parseFloat(bf) || 0;
    const cumGrade = cum >= 70 ? 'A' : (cum >= 60 ? 'B' : (cum >= 50 ? 'C' : (cum >= 40 ? 'D' : 'F')));

    document.getElementById('modalCurrentTotal').textContent = currentTotal.toFixed(1);
    document.getElementById('modalCurrentGrade').textContent = grade;
    document.getElementById('modalCurrentCum').textContent = cum.toFixed(1);
    document.getElementById('modalCurrentCumGrade').textContent = cumGrade;

    // Build assessment inputs
    const container = document.getElementById('modalAssessmentsList');
    let html = '';
    assessmentsData.forEach(a => {
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
                       min="0" max="${a.max_score}" step="0.1">
            </div>
        `;
    });
    container.innerHTML = html;

    // Store current broadsheet ID
    currentEditBroadsheetId = broadsheetId;

    // Show modal
    modal.show();
}

// Save scores from modal
function saveModalScores() {
    const container = document.getElementById('modalAssessmentsList');
    const assessmentInputs = container.querySelectorAll('.score-input-large');

    let hasError = false;
    const scores = {};

    assessmentInputs.forEach(input => {
        const assessmentId = input.dataset.assessmentId;
        const maxScore = parseFloat(input.dataset.max);
        let value = parseFloat(input.value) || 0;

        if (value > maxScore) {
            showToast(`Score cannot exceed ${maxScore}`, 'danger');
            hasError = true;
            return;
        }
        if (value < 0) {
            value = 0;
            input.value = 0;
        }
        scores[assessmentId] = value;
    });

    if (hasError) return;

    // Show saving indicator
    const saveBtn = document.getElementById('saveModalScoresBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Saving...';

    const savePromises = [];
    for (const [assessmentId, score] of Object.entries(scores)) {
        savePromises.push(fetch(routes.singleUpdate, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                broadsheet_id: currentEditBroadsheetId,
                assessment_id: parseInt(assessmentId),
                score: score,
                is_sub: false,
                term_id: {{ $termId ?? 0 }},
                session_id: {{ $sessionId ?? 0 }},
                subjectclass_id: {{ $subjectclassId ?? 0 }},
                schoolclass_id: {{ $schoolclass->id ?? 0 }},
                staff_id: {{ $teacherId ?? 0 }}
            })
        }));
    }

    Promise.all(savePromises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
            let allSuccess = true;
            let newTotal = 0;

            results.forEach((data, index) => {
                if (data.success) {
                    const assessmentId = Object.keys(scores)[index];
                    newTotal += scores[assessmentId];
                } else {
                    allSuccess = false;
                }
            });

            if (allSuccess) {
                // Update the table row
                const row = document.querySelector(`#scoresheetTableBody tr[data-id="${currentEditBroadsheetId}"]`);
                if (row) {
                    for (const [assessmentId, score] of Object.entries(scores)) {
                        const input = row.querySelector(`.score-input[data-field="${assessmentId}"]`);
                        if (input) {
                            input.value = score;
                            input.dataset.original = score;
                        }
                    }

                    // Update totals
                    const totalBadge = row.querySelector('.total-badge');
                    if (totalBadge) totalBadge.textContent = newTotal.toFixed(1);

                    const newGrade = newTotal >= 70 ? 'A' : (newTotal >= 60 ? 'B' : (newTotal >= 50 ? 'C' : (newTotal >= 40 ? 'D' : 'F')));
                    const gradeBadge = row.querySelector('.grade-badge');
                    if (gradeBadge) gradeBadge.textContent = newGrade;
                }

                showToast('Scores saved successfully!', 'success');

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('scoreEntryModal'));
                modal.hide();
            } else {
                showToast('Some scores failed to save', 'warning');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error saving scores', 'danger');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
}

// Bulk save all scores
function bulkSaveScores() {
    const scores = [];
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
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

    const btn = document.getElementById('bulkSaveBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Saving...';

    fetch(routes.bulkUpdate, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            scores: scores,
            term_id: {{ $termId ?? 0 }},
            session_id: {{ $sessionId ?? 0 }},
            subjectclass_id: {{ $subjectclassId ?? 0 }},
            staff_id: {{ $teacherId ?? 0 }},
            schoolclass_id: {{ $schoolclass->id ?? 0 }},
            is_sub: false
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('All scores saved successfully', 'success');
            if (data.data?.broadsheets) {
                data.data.broadsheets.forEach(bs => {
                    const row = document.querySelector(`tr[data-id="${bs.id}"]`);
                    if (row) {
                        row.querySelector('.total-badge').textContent = bs.total?.toFixed(1) || '0';
                        row.querySelector('.grade-badge').textContent = bs.grade || '-';
                        row.querySelector('.cum-badge').textContent = bs.cum?.toFixed(1) || '0';
                    }
                });
            }
        } else {
            showToast(data.message || 'Error saving scores', 'danger');
        }
    })
    .catch(err => showToast('Network error', 'danger'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
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
        title: `Delete ${selectedIds.length} record(s)?`,
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete'
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
                            if (document.querySelectorAll('#scoresheetTableBody tr').length === 1) {
                                location.reload();
                            }
                        }
                    }
                });
            });
        }
    });
}

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {

    // Load SweetAlert2 if not present
    if (typeof Swal === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.head.appendChild(script);
    }

    // Initialize Bootstrap
    window.bootstrap = window.bootstrap || {};

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });

    document.getElementById('clearSearch')?.addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.querySelectorAll('#scoresheetTableBody tr').forEach(row => row.style.display = '');
    });

    // Select All checkbox
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = true);
        const ca = document.getElementById('checkAll');
        if (ca) ca.checked = true;
    });

    // Delete button
    document.getElementById('deleteSelectedBtn')?.addEventListener('click', deleteSelectedScores);

    // Bulk save button
    document.getElementById('bulkSaveBtn')?.addEventListener('click', bulkSaveScores);

    // Score inputs - auto-save on blur
    document.querySelectorAll('.score-input').forEach(inp => {
        inp.dataset.original = inp.value;
        inp.addEventListener('blur', function() {
            if (this.disabled) return;
            saveIndividualScore(this);
        });
        inp.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') this.blur();
        });
    });

    // Edit buttons - Open modal
    document.querySelectorAll('.edit-scores-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const admission = this.dataset.admission;
            const avatar = this.dataset.avatar;
            const bf = this.dataset.bf;

            console.log('Edit button clicked:', { id, name, admission });
            openScoreEntryModal(id, name, admission, avatar, bf);
        });
    });

    // Save modal scores button
    document.getElementById('saveModalScoresBtn')?.addEventListener('click', saveModalScores);

    // Export Excel
    document.getElementById('downloadExcel')?.addEventListener('click', function() {
        window.location.href = routes.export + `?subjectclass_id={{ $subjectclassId }}&staff_id={{ $teacherId }}&term_id={{ $termId }}&session_id={{ $sessionId }}&schoolclass_id={{ $schoolclass->id ?? 0 }}`;
    });

    // Import form
    document.getElementById('importForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line spin"></i> Uploading...';

        fetch(routes.import, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000, showConfirmButton: false });
                setTimeout(() => location.reload(), 2000);
            } else {
                Swal.fire({ icon: 'error', title: 'Import Failed', text: data.message });
            }
        })
        .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' }))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // Keyboard shortcut Ctrl+S
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            bulkSaveScores();
        }
    });

    console.log('DOM ready - Edit buttons found:', document.querySelectorAll('.edit-scores-btn').length);
});
</script>
@endsection
