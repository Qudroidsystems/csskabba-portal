{{-- resources/views/subjectoperation/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Title --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 fw-semibold">
                            <i class="ri-user-star-line me-2 text-primary"></i>Subject Registration
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Student Management</a></li>
                                <li class="breadcrumb-item active">Subject Registration</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong><i class="ri-error-warning-line me-1"></i> Error!</strong> There were some problems with your input.
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div id="subjectList">

                {{-- CLASS & SESSION FILTER --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom-0 pb-0" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);">
                                <h6 class="text-white mb-0 py-1"><i class="ri-filter-3-line me-2"></i>Filter Students</h6>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Class</label>
                                        <select class="form-select" id="idclass">
                                            <option value="ALL">— Select Class —</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->schoolclass }} {{ $class->schoolarm }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Session</label>
                                        <select class="form-select" id="idsession">
                                            <option value="ALL">— Select Session —</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->session }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-primary w-100" onclick="filterData();">
                                            <i class="ri-search-line me-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBJECT TEACHERS CARD --}}
                <div class="row" id="subjectTeachersCard">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-book-open-line me-2 text-primary"></i>Subject Teachers
                                    </h5>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects();">
                                        <i class="ri-checkbox-multiple-line me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllSubjects();">
                                        <i class="ri-checkbox-blank-line me-1"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info border-0 mb-3 d-flex align-items-center gap-2" style="background:#eff6ff;">
                                    <i class="ri-information-line fs-5 text-primary flex-shrink-0"></i>
                                    <span class="small">Select the subjects you want to register or unregister students for. Subjects are grouped by term.</span>
                                </div>
                                <div id="subjectTeachersContainer">
                                    @foreach ($schoolterms as $term)
                                        @php $termSubjects = $subjectTeachers ? $subjectTeachers->where('termid', $term->id) : collect(); @endphp
                                        @if ($termSubjects->isNotEmpty())
                                            <div class="term-group mb-4">
                                                <div class="d-flex align-items-center mb-2 gap-2">
                                                    <span class="badge text-white px-3 py-2 rounded-pill" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                                                        <i class="ri-calendar-2-line me-1"></i>{{ $term->term }}
                                                    </span>
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                                                        {{ $termSubjects->count() }} subject{{ $termSubjects->count() !== 1 ? 's' : '' }}
                                                    </span>
                                                </div>
                                                <div class="row g-2">
                                                    @foreach ($termSubjects as $teacher)
                                                        <div class="col-xl-3 col-md-4 col-sm-6">
                                                            <div class="subject-check-card p-2 border rounded-3 d-flex align-items-center gap-2 bg-light bg-opacity-50" style="cursor:pointer;" onclick="toggleSubjectCard(this)">
                                                                <input class="form-check-input subject-checkbox flex-shrink-0 mt-0" type="checkbox"
                                                                    id="subject-{{ $teacher->subjectclassid }}"
                                                                    data-subjectclassid="{{ $teacher->subjectclassid }}"
                                                                    data-staffid="{{ $teacher->userid }}"
                                                                    data-termid="{{ $teacher->termid }}" checked>
                                                                <label class="form-check-label small lh-sm mb-0 w-100" for="subject-{{ $teacher->subjectclassid }}" style="cursor:pointer;">
                                                                    <span class="fw-semibold d-block text-truncate">{{ $teacher->subjectname }}</span>
                                                                    <span class="text-muted" style="font-size:0.75rem;">{{ $teacher->staffname }}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(!$subjectTeachers || $subjectTeachers->isEmpty())
                                        <div class="text-center text-muted py-4">
                                            <i class="ri-book-2-line ri-2x mb-2 d-block"></i>
                                            Select a class and session to view subjects.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STUDENT FILTERS --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xxl-4">
                                        <label class="form-label fw-medium small text-muted">Search Students</label>
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Name or admission no…" value="{{ request('search') }}">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Gender</label>
                                        <select class="form-select" id="idgender">
                                            <option value="ALL">All Genders</option>
                                            <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Admission No</label>
                                        <select class="form-select" id="idadmission">
                                            <option value="ALL">All Admission Nos</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                            <i class="ri-filter-line me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STUDENTS TABLE --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-group-line me-2 text-primary"></i>Students
                                        <span class="badge bg-dark-subtle text-dark ms-1 rounded-pill" id="studentcount">{{ $students ? $students->total() : 0 }}</span>
                                    </h5>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-success d-none" id="register-selected-btn" onclick="registerSelectedStudentsBatch();">
                                        <i class="ri-user-add-line me-1"></i>Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn" onclick="openUnregisterModal();">
                                        <i class="ri-user-unfollow-line me-1"></i>Unregister Selected
                                    </button>
                                    <div class="spinner-border spinner-border-sm text-primary d-none" id="register-loading-spinner" role="status"></div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line me-1"></i>View Registered
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="openArchivedModal();">
                                        <i class="ri-archive-line me-1"></i>Unregistered History
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40" class="text-center">
                                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                                </th>
                                                <th width="50">S/N</th>
                                                <th>Admission No</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Gender</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('subjectoperation.partials.student_rows')
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end p-3">
                                    {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- REGISTERED CLASSES MODAL --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">

                            <div class="modal-header px-4 py-3 border-0" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 60%,#7c3aed 100%);">
                                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-wrap">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:44px;height:44px;background:rgba(255,255,255,0.15);">
                                        <i class="ri-graduation-cap-line fs-5 text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="modal-title text-white fw-bold mb-0">Registered Classes Overview</h5>
                                        <small class="text-white opacity-75">Subject–Teacher assignments by term</small>
                                    </div>
                                    <div class="ms-auto d-flex gap-2">
                                        <button type="button" class="btn btn-sm px-3 text-white border-white border-opacity-50"
                                                style="background:rgba(255,255,255,0.15);backdrop-filter:blur(4px);"
                                                onclick="printRegisteredClasses()">
                                            <i class="ri-printer-line me-1"></i>Print
                                        </button>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                </div>
                            </div>

                            <div id="regModalTermTabsWrap" class="border-bottom px-4 pt-2" style="background:#f8faff;display:none;">
                                <ul class="nav nav-tabs border-0" id="regModalTermTabs" role="tablist"></ul>
                            </div>

                            <div class="modal-body p-4" style="background:#f4f7fe;max-height:72vh;overflow-y:auto;">
                                <div id="registeredClassesContent">
                                    <div class="text-center py-5">
                                        <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                             style="width:64px;height:64px;background:linear-gradient(135deg,#667eea,#764ba2);">
                                            <i class="ri-search-line fs-4 text-white"></i>
                                        </div>
                                        <p class="text-muted">Select a class and session, then open this modal to view registered subjects.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 px-4 py-3" style="background:#f8faff;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Other modals (Snapshot, Archived, Detail) remain unchanged --}}
                <div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                            <div class="modal-header border-0" style="background:linear-gradient(135deg,#f5576c 0%,#f093fb 100%);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
                                        <i class="ri-camera-line text-white"></i>
                                    </div>
                                    <h5 class="modal-title text-white mb-0">Name this Unregistration</h5>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Snapshot Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="snapshotNameInput" placeholder="e.g., Term 2 Corrections - June 2025">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                    <textarea class="form-control" id="snapshotNotesInput" rows="3" placeholder="Reason for unregistration…"></textarea>
                                </div>
                                <div class="alert border-0 d-flex gap-2 align-items-center" style="background:#fff8e1;">
                                    <i class="ri-error-warning-line text-warning fs-5 flex-shrink-0"></i>
                                    <small>All scores will be saved in a snapshot and can be fully restored later.</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" onclick="proceedUnregister();">
                                    <i class="ri-delete-bin-line me-1"></i>Unregister &amp; Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="archivedModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                            <div class="modal-header border-0" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
                                        <i class="ri-archive-line text-white"></i>
                                    </div>
                                    <h5 class="modal-title text-white mb-0">Unregistered History</h5>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div id="snapshotCardsContainer">
                                    <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                                </div>
                                <div id="archivePagination" class="d-flex justify-content-center mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="snapshotDetailModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                            <div class="modal-header border-0" style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
                                        <i class="ri-file-list-3-line text-white"></i>
                                    </div>
                                    <h5 class="modal-title text-white mb-0" id="snapshotDetailTitle">Snapshot Detail</h5>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div id="snapshotDetailBody">
                                    <div class="text-center py-4"><div class="spinner-border text-info"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* ── Print Styles ──────────────────────────────────────────── */
@media print {
    body * { visibility: hidden; }
    #printableArea, #printableArea * { visibility: visible; }
    #printableArea { position: absolute; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
    @page { size: A4; margin: 15mm; }
}

/* ── Subject check card ─────────────────────────────── */
.subject-check-card {
    transition: all .18s ease;
    border-color: #e2e8f0 !important;
}
.subject-check-card:hover {
    border-color: #667eea !important;
    background: #f0f0ff !important;
}
.subject-check-card.is-checked {
    border-color: #667eea !important;
    background: #ede9fe !important;
}

/* ── Subject List in Modal ───────────────────────────── */
.subject-list {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}

.subject-item {
    transition: all 0.2s ease;
}

.subject-item:hover {
    background: #f8fbff !important;
}

.subject-num {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-weight: 700;
    font-size: 13px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ── Other styles (keep your existing ones) ───────────────── */
.subject-row:nth-child(even) { background: #fafbff; }
.subject-row:hover { background: #eff6ff !important; }

.stats-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ════════════════════════════════════════════════════════════════════════════
// CONFIGURATION
// ════════════════════════════════════════════════════════════════════════════
const ROUTES = {
    batchRegister:  '{{ route("subjectregistration.batch") }}',
    unregister:     '{{ route("subjects.destroy") }}',
    getRegistered:  '{{ route("subjects.registered-classes") }}',
    getArchived:    '{{ route("subjectoperation.archived") }}',
    getSnapshot:    '{{ route("subjectoperation.snapshot.detail") }}',
    restore:        '{{ route("subjectoperation.restore") }}',
    permanentDelete:'{{ route("subjectoperation.archive.batch-delete") }}',
    index:          '{{ route("subjects.index") }}',
    getSchoolInfo:  '{{ route("school.information.get") }}',
};
const CSRF      = '{{ csrf_token() }}';
const ASSET_URL = '{{ asset("storage") }}';

// Utility functions
function esc(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function toast(title, msg, icon) {
    Swal.fire({ title, html: msg, icon, confirmButtonColor: icon === 'success' ? '#28a745' : '#dc3545' });
}

function filterData() {
    const params = new URLSearchParams({
        class_id:    document.getElementById('idclass').value,
        session_id:  document.getElementById('idsession').value,
        search:      document.querySelector('.search')?.value || '',
        gender:      document.getElementById('idgender').value,
        admissionno: document.getElementById('idadmission').value,
    });
    window.location.href = ROUTES.index + '?' + params.toString();
}

// Subject selection helpers
function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    cb.checked = !cb.checked;
    card.classList.toggle('is-checked', cb.checked);
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = true;
        cb.closest('.subject-check-card')?.classList.add('is-checked');
    });
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.subject-check-card')?.classList.remove('is-checked');
    });
}

// Mark checked cards on load
document.querySelectorAll('.subject-checkbox:checked').forEach(cb => {
    cb.closest('.subject-check-card')?.classList.add('is-checked');
});

// ════════════════════════════════════════════════════════════════════════════
// REGISTERED CLASSES MODAL
// ════════════════════════════════════════════════════════════════════════════
async function loadRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');
    const tabsWrap  = document.getElementById('regModalTermTabsWrap');
    const tabs      = document.getElementById('regModalTermTabs');

    if (classId === 'ALL' || sessionId === 'ALL') {
        tabsWrap.style.display = 'none';
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:#fef3c7;">
                    <i class="ri-error-warning-line fs-4 text-warning"></i>
                </div>
                <p class="text-muted mb-0">Please select a <strong>class</strong> and <strong>session</strong> first.</p>
            </div>`;
        return;
    }

    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
            <p class="text-muted">Loading registered subjects…</p>
        </div>`;
    tabsWrap.style.display = 'none';

    try {
        const res  = await fetch(`${ROUTES.getRegistered}?class_id=${classId}&session_id=${sessionId}`,
            { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `<div class="alert alert-info text-center py-4">
                <i class="ri-information-line fs-4 d-block mb-2"></i>No registered classes found for this selection.</div>`;
            return;
        }

        // Build tab nav
        tabs.innerHTML = '';
        data.data.forEach((termData, idx) => {
            tabs.innerHTML += `
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2 ${idx === 0 ? 'active' : ''}"
                            id="tab-term-${idx}"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-pane-${idx}"
                            type="button" role="tab">
                        <i class="ri-calendar-2-line me-1"></i>${esc(termData.term_name)}
                        <span class="badge bg-primary rounded-pill ms-1">${termData.subject_count ?? termData.subjects_teachers?.length ?? 0}</span>
                    </button>
                </li>`;
        });
        tabsWrap.style.display = 'block';

        // Build tab panes
        let panesHtml = '<div class="tab-content mt-3">';
        data.data.forEach((termData, idx) => {
            const subjects = termData.subjects_teachers || [];
            panesHtml += `<div class="tab-pane fade ${idx === 0 ? 'show active' : ''}" id="tab-pane-${idx}" role="tabpanel">`;
            panesHtml += buildTermPane(termData, subjects);
            panesHtml += '</div>';
        });
        panesHtml += '</div>';
        container.innerHTML = panesHtml;

    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>${esc(err.message)}</div>`;
    }
}

function buildTermPane(termData, subjects) {
    const studentCount = termData.student_count ?? 0;

    // Sort subjects alphabetically
    const sortedSubjects = [...subjects].sort((a, b) =>
        (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' })
    );

    const subjectCount = sortedSubjects.length;

    let html = `
        <div class="card border-0 shadow-sm mb-0" style="border-radius:12px;overflow:hidden;">
            <div class="card-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 70%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:42px;height:42px;background:rgba(255,255,255,0.15);">
                            <i class="ri-school-line text-white fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-white fw-bold mb-0">${esc(termData.class_name)} ${esc(termData.arm_name)}</h6>
                            <small class="text-white opacity-75">${esc(termData.session_name)} &bull; ${esc(termData.term_name)}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="stats-pill" style="background:rgba(255,255,255,0.2);color:#fff;">
                            <i class="ri-user-line"></i> ${studentCount} Student${studentCount !== 1 ? 's' : ''}
                        </span>
                        <span class="stats-pill" style="background:rgba(255,255,255,0.2);color:#fff;">
                            <i class="ri-book-open-line"></i> ${subjectCount} Subject${subjectCount !== 1 ? 's' : ''}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">`;

    if (!subjectCount) {
        html += `<div class="text-center text-muted py-5">
                    <i class="ri-book-2-line ri-3x d-block mb-3 text-muted"></i>
                    <p>No subjects found for this term.</p>
                 </div>`;
    } else {
        html += `<div class="p-4"><div class="subject-list">`;

        sortedSubjects.forEach((subject, index) => {
            const sc = subject.student_count ?? 0;
            let teachersHtml = subject.teachers && subject.teachers.length > 0
                ? subject.teachers.map(t => esc(t.name)).join(', ')
                : '<span class="text-muted">— Not assigned</span>';

            html += `
                <div class="subject-item d-flex align-items-start gap-3 p-3 border-bottom">
                    <div class="subject-num flex-shrink-0">${index + 1}</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">${esc(subject.name)}</div>
                        <div class="small text-muted mt-1">
                            <i class="ri-user-follow-line me-1"></i>${teachersHtml}
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <span class="badge rounded-pill px-3 py-1" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:12px;">
                            ${sc} student${sc !== 1 ? 's' : ''}
                        </span>
                    </div>
                </div>`;
        });

        html += `</div></div>`;
    }

    html += `
            <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2"
                 style="background:#f8faff;border-top:1px solid #e8eaf6;font-size:12px;">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    <strong>${subjectCount}</strong> subjects &nbsp;&bull;&nbsp;
                    <strong>${studentCount}</strong> students
                </span>
                <span class="text-muted">
                    <i class="ri-time-line me-1"></i>Generated: ${new Date().toLocaleString()}
                </span>
            </div>
        </div>
    </div>`;

    return html;
}

// ════════════════════════════════════════════════════════════════════════════
// PRINT FUNCTION (Refined Spacing)
// ════════════════════════════════════════════════════════════════════════════
async function printRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire('Cannot Print', 'Please select a class and session first.', 'warning');
        return;
    }

    Swal.fire({ title: 'Preparing document…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const [schoolRes, regRes] = await Promise.all([
            fetch(ROUTES.getSchoolInfo, { headers: { 'Accept': 'application/json' } }),
            fetch(`${ROUTES.getRegistered}?class_id=${classId}&session_id=${sessionId}`,
                { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }),
        ]);

        const [schoolData, regData] = await Promise.all([schoolRes.json(), regRes.json()]);
        Swal.close();

        if (!regData.success || !regData.data.length) {
            Swal.fire('No Data', 'No registered classes found.', 'info');
            return;
        }

        const pw = window.open('', '_blank');
        pw.document.write(buildPrintHtml(schoolData, regData.data));
        pw.document.close();
        pw.focus();
        setTimeout(() => pw.print(), 600);

    } catch (err) {
        Swal.close();
        Swal.fire('Error', err.message, 'error');
    }
}

function buildPrintHtml(schoolData, registeredData) {
    const classEl   = document.getElementById('idclass');
    const sessionEl = document.getElementById('idsession');
    const className  = classEl.options[classEl.selectedIndex]?.text || '';
    const sessionName = sessionEl.options[sessionEl.selectedIndex]?.text || '';

    const school      = schoolData.success ? schoolData.data : {};
    const schoolName  = school.school_name    || 'School Management System';
    const schoolAddr  = school.school_address || '';
    const schoolPhone = school.school_phone   || '';
    const schoolEmail = school.school_email   || '';
    const schoolMotto = school.school_motto   || '';
    const logoSrc     = school.school_logo
        ? (school.school_logo.startsWith('http') ? school.school_logo : `{{ asset('storage') }}/${school.school_logo}`)
        : '';

    let totalSubjects = 0;
    let termsHtml = '';

    registeredData.forEach(termData => {
        const subjects = termData.subjects_teachers || [];
        const sortedSubjects = [...subjects].sort((a, b) =>
            (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' })
        );

        totalSubjects += sortedSubjects.length;

        let rows = '';
        sortedSubjects.forEach((subject, index) => {
            const studentCount = subject.student_count ?? 0;
            let teachers = '— Not assigned';

            if (subject.teachers && subject.teachers.length > 0) {
                teachers = subject.teachers.map(t => esc(t.name)).join(', ');
            }

            rows += `
                <tr>
                    <td class="center">${index + 1}</td>
                    <td><strong>${esc(subject.name)}</strong></td>
                    <td>${teachers}</td>
                    <td class="center">${studentCount}</td>
                </tr>`;
        });

        termsHtml += `
            <div class="term-block">
                <div class="term-header">
                    <span class="term-title">${esc(termData.class_name)} ${esc(termData.arm_name)} — ${esc(termData.term_name)}</span>
                    <span class="term-meta">${esc(termData.session_name)} &nbsp;|&nbsp; ${sortedSubjects.length} Subjects &nbsp;|&nbsp; ${termData.student_count ?? 0} Students</span>
                </div>

                <table class="subject-table">
                    <thead>
                        <tr>
                            <th class="center" style="width:50px;">#</th>
                            <th>Subject — Teacher</th>
                            <th class="center" style="width:100px;">Students</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>

                <div class="term-summary">
                    <strong>${sortedSubjects.length}</strong> subjects •
                    <strong>${termData.student_count ?? 0}</strong> students registered
                </div>
            </div>`;
    });

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subject Registration — ${esc(schoolName)}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 11.5pt;
      color: #1a1a2e;
      background: #fff;
      line-height: 1.55;
  }

  @page {
      size: A4 portrait;
      margin: 15mm 12mm;
  }

  .school-header {
      display: flex;
      align-items: center;
      gap: 22px;
      border-bottom: 4px solid #2563eb;
      padding-bottom: 22px;
      margin-bottom: 28px;
  }
  .school-logo { width: 82px; height: 82px; object-fit: contain; flex-shrink: 0; }
  .school-logo-placeholder {
      width: 82px; height: 82px; border-radius: 50%;
      background: linear-gradient(135deg,#667eea,#764ba2);
      display: flex; align-items: center; justify-content: center;
      color: white; font-size: 34px; font-weight: 700; flex-shrink: 0;
  }
  .school-info { flex: 1; }
  .school-name { font-size: 21pt; font-weight: 700; color: #1e3a5f; line-height: 1.15; }
  .school-motto { font-style: italic; color: #555; font-size: 11.5pt; margin-top: 6px; }
  .school-contact { font-size: 10.5pt; color: #666; margin-top: 8px; }

  .doc-title {
      text-align: center;
      background: linear-gradient(135deg,#1e3a5f,#2563eb);
      color: white;
      padding: 16px 25px;
      border-radius: 8px;
      margin-bottom: 26px;
  }
  .doc-title h2 { font-size: 17pt; font-weight: 700; letter-spacing: 0.6px; }

  .meta-strip {
      background: #f0f4ff;
      border: 1px solid #c7d2fe;
      border-radius: 8px;
      padding: 14px 20px;
      margin-bottom: 30px;
      font-size: 11pt;
      display: flex;
      flex-wrap: wrap;
      gap: 24px;
  }

  .term-block {
      margin-bottom: 35px;
      page-break-inside: avoid;
      border: 1px solid #e0e7ff;
      border-radius: 10px;
      overflow: hidden;
  }
  .term-header {
      background: linear-gradient(135deg,#1e3a5f 0%,#2563eb 70%,#7c3aed 100%);
      color: white;
      padding: 16px 20px;
  }
  .term-title {
      font-size: 14.5pt;
      font-weight: 700;
      display: block;
  }
  .term-meta {
      font-size: 10.5pt;
      opacity: 0.92;
      display: block;
      margin-top: 6px;
  }

  .subject-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11pt;
  }
  .subject-table th {
      background: #e0e7ff;
      padding: 12px 14px;
      font-weight: 600;
      color: #1e3a5f;
      text-align: left;
      border-bottom: 2px solid #c7d2fe;
  }
  .subject-table td {
      padding: 11px 14px;
      border-bottom: 1px solid #f0f0f0;
      vertical-align: top;
  }
  .subject-table tr:nth-child(even) td {
      background: #fafbff;
  }
  .subject-table .center {
      text-align: center;
  }

  .term-summary {
      background: #f8faff;
      padding: 12px 20px;
      font-size: 10.5pt;
      color: #1e3a5f;
      border-top: 1px solid #e0e7ff;
      text-align: right;
  }

  .doc-footer {
      text-align: center;
      font-size: 9.5pt;
      color: #888;
      border-top: 1px solid #ddd;
      padding-top: 18px;
      margin-top: 35px;
  }
</style>
</head>
<body>

  <div class="school-header">
    ${logoSrc
        ? `<img src="${logoSrc}" class="school-logo" onerror="this.style.display='none'">`
        : `<div class="school-logo-placeholder">${esc(schoolName).charAt(0)}</div>`}
    <div class="school-info">
      <div class="school-name">${esc(schoolName)}</div>
      ${schoolMotto ? `<div class="school-motto">"${esc(schoolMotto)}"</div>` : ''}
      <div class="school-contact">
        ${schoolAddr ? esc(schoolAddr) + ' • ' : ''}
        ${schoolPhone ? esc(schoolPhone) : ''}
        ${schoolEmail ? ' • ' + esc(schoolEmail) : ''}
      </div>
    </div>
  </div>

  <div class="doc-title">
    <h2>Subject Registration Report</h2>
    <p>${esc(className)} &nbsp;&bull;&nbsp; ${esc(sessionName)}</p>
  </div>

  <div class="meta-strip">
    <div><strong>Class:</strong> ${esc(className)}</div>
    <div><strong>Session:</strong> ${esc(sessionName)}</div>
    <div><strong>Terms:</strong> ${registeredData.length}</div>
    <div><strong>Printed:</strong> ${new Date().toLocaleString()}</div>
  </div>

  ${termsHtml}

  <div class="doc-footer">
    <strong>${esc(schoolName)}</strong> &bull;
    Subject Registration Report &bull;
    Generated on ${new Date().toLocaleString()} &bull;
    School Management System
  </div>

</body>
</html>`;
}

// Remaining script functions (registration, archive, etc.) remain the same
// ... (your existing registerSelectedStudentsBatch, openUnregisterModal, etc.)

// Event listeners
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => cb.checked = this.checked);
    const count = this.checked ? document.querySelectorAll('#studentTableBody input[name="chk_child"]').length : 0;
    document.getElementById('register-selected-btn')?.classList.toggle('d-none', count === 0);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', count === 0);
});

document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);

document.addEventListener('change', function(e) {
    if (e.target.matches('#studentTableBody input[name="chk_child"]')) {
        const checked = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length;
        document.getElementById('register-selected-btn')?.classList.toggle('d-none', checked === 0);
        document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', checked === 0);
    }
});
</script>
@endsection
