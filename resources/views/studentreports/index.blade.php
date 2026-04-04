@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Subject Registration</h4>
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
                <div class="alert alert-danger">
                    <strong>Error!</strong> There were some problems with your input.<br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="subjectList">
                {{-- Class & Session Filter --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idclass">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idsession">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                            <i class="bi bi-funnel align-baseline me-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Subject Teachers Card --}}
                <div class="row" id="subjectTeachersCard">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Subject Teachers
                                        <span class="badge bg-primary-subtle text-primary ms-1" id="subjectTeacherCount">0</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects();">Select All</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="deselectAllSubjects();">Deselect All</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    Select the subjects you want to register or unregister students for.
                                </div>
                                <div id="subjectTeachersContainer">
                                    @foreach ($schoolterms as $term)
                                        @if ($subjectTeachers && $subjectTeachers->where('termid', $term->id)->isNotEmpty())
                                            <h6 class="mt-3">{{ $term->term }}</h6>
                                            <div class="row">
                                                @foreach ($subjectTeachers->where('termid', $term->id) as $teacher)
                                                    <div class="col-md-4">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input subject-checkbox" type="checkbox"
                                                                id="subject-{{ $teacher->subjectclassid }}"
                                                                data-subjectclassid="{{ $teacher->subjectclassid }}"
                                                                data-staffid="{{ $teacher->userid }}"
                                                                data-termid="{{ $teacher->termid }}">
                                                            <label class="form-check-label" for="subject-{{ $teacher->subjectclassid }}">
                                                                {{ $teacher->subjectname }}
                                                                <small class="text-muted">({{ $teacher->staffname }})</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Student Filters --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search students">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idgender">
                                            <option value="ALL">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idadmission">
                                            <option value="ALL">Select Admission No</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                            <i class="bi bi-funnel align-baseline me-1"></i> Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Students Table --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Students
                                        <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $students->total() }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0 d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary d-none" id="register-selected-btn"
                                        onclick="registerSelectedStudentsBatch();">
                                        <i class="ri-checkbox-circle-line me-1"></i> Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn"
                                        onclick="unregisterSelectedStudentsBatch();">
                                        <i class="ri-delete-bin-line me-1"></i> Unregister Selected
                                    </button>
                                    <div class="spinner-border text-primary d-none" id="register-loading-spinner" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line me-1"></i> View Registered
                                    </button>
                                    <button type="button" class="btn btn-warning" id="viewArchivedBtn" onclick="openArchivedModal();">
                                        <i class="ri-archive-line me-1"></i> Unregistered History
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="subjectListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                                        <label class="form-check-label" for="checkAll"></label>
                                                    </div>
                                                </th>
                                                <th>SN</th>
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
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $students->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Registered Classes --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="registeredClassesModalLabel">
                                    <i class="ri-group-line me-2"></i>Registered Classes Overview
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="registeredClassesContent">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-3 text-muted">Loading registered classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Unregistered History (Archive) --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning-subtle">
                                <h5 class="modal-title" id="archivedModalLabel">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                {{-- Toolbar --}}
                                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="ri-search-line"></i>
                                            </span>
                                            <input type="text" class="form-control" id="archiveSearch"
                                                placeholder="Search student name or admission number...">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <select class="form-select form-select-sm" id="archiveTermFilter" style="width:auto;">
                                            <option value="">All Terms</option>
                                            @foreach($schoolterms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1);">
                                            <i class="ri-refresh-line me-1"></i> Refresh
                                        </button>
                                        <button class="btn btn-sm btn-success d-none" id="restoreSelectedBtn" onclick="restoreSelected();">
                                            <i class="ri-refresh-line me-1"></i> Restore Selected
                                        </button>
                                        <button class="btn btn-sm btn-danger d-none" id="deleteSelectedBtn" onclick="permanentDeleteSelected();">
                                            <i class="ri-delete-bin-line me-1"></i> Delete Selected
                                        </button>
                                        <div class="spinner-border spinner-border-sm text-warning d-none" id="archiveSpinner" role="status"></div>
                                    </div>
                                </div>

                                {{-- Table --}}
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-warning sticky-top">
                                            <tr>
                                                <th style="width:36px;">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" id="archiveCheckAll">
                                                    </div>
                                                </th>
                                                <th>Student</th>
                                                <th>Adm. No</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Term</th>
                                                <th>Unregistered Date</th>
                                                <th>Unregistered By</th>
                                                <th style="width:100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="archiveTableBody">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="ri-information-line ri-2x mb-2 d-block"></i>
                                                    Select a class and session first, then click "Unregistered History"
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
                                <div class="d-flex justify-content-between align-items-center p-3 border-top" id="archivePaginationWrap">
                                    <small class="text-muted" id="archiveMeta"></small>
                                    <div id="archivePagination" class="d-flex gap-1"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <small class="text-muted me-auto">
                                    <i class="ri-information-line me-1"></i>
                                    <strong>Restore:</strong> Re-registers the student for this subject |
                                    <strong>Delete:</strong> Permanently removes from history (cannot be undone)
                                </small>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image View Modal --}}
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid"
                                    onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /subjectList --}}
        </div>
    </div>
</div>

<style>
.subject-details-cell {
    max-width: 450px;
}

.subject-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.subject-item {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding: 4px 0;
}

.subject-item .badge {
    font-size: 0.85rem;
    padding: 6px 12px;
    font-weight: 500;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transition: background-color 0.2s ease;
}

.modal-xl {
    max-width: 90%;
}

@media (min-width: 1400px) {
    .modal-xl {
        max-width: 1200px;
    }
}

.modal-header.bg-primary {
    border-bottom: none;
}

.modal-footer.bg-light {
    border-top: 1px solid rgba(0,0,0,0.05);
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

.subject-checkbox {
    cursor: pointer;
}

.subject-checkbox:checked + label {
    font-weight: 600;
    color: #0d6efd;
}

.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection

@section('scripts')
<script>
// ============================================================================
// GLOBALS
// ============================================================================
const ROUTES = {
    batchRegister   : '{{ route("subjectregistration.batch") }}',
    destroy         : '{{ route("subjectregistration.destroy") }}',
    getRegistered   : '{{ route("subjects.registered-classes") }}',
    getArchived     : '{{ route("subjectoperation.archived") }}',
    restore         : '{{ route("subjectoperation.restore") }}',
    permanentDelete : '{{ route("subjectoperation.archive.batch-delete") }}',
    index           : '{{ route("subjects.index") }}',
};
const CSRF = '{{ csrf_token() }}';

// Archive state
let archiveCurrentPage = 1;
let archiveMeta        = {};
let archiveSearchTimer = null;

// ============================================================================
// IMAGE MODAL
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
    const imgModal = document.getElementById('imageViewModal');
    if (imgModal) {
        imgModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const src = btn?.getAttribute('data-image');
            const img = imgModal.querySelector('#enlargedImage');
            img.src   = src || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
        });
    }

    // Load registered classes when that modal opens
    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
});

// ============================================================================
// FILTER / SEARCH
// ============================================================================
function filterData() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const search    = document.querySelector('.search')?.value ?? '';
    const gender    = document.getElementById('idgender').value;
    const admission = document.getElementById('idadmission').value;

    const params = new URLSearchParams({
        class_id  : classId,
        session_id: sessionId,
        search,
        gender,
        admissionno: admission,
    });

    window.location.href = ROUTES.index + '?' + params.toString();
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true);
    updateSubjectCount();
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
    updateSubjectCount();
}

function updateSubjectCount() {
    const count = document.querySelectorAll('.subject-checkbox:checked').length;
    document.getElementById('subjectTeacherCount').textContent = count;
}

document.querySelectorAll('.subject-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSubjectCount);
});
updateSubjectCount();

// ============================================================================
// CHECK ALL STUDENTS
// ============================================================================
document.getElementById('checkAll')?.addEventListener('change', function () {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => {
        cb.checked = this.checked;
    });
    toggleBatchButtons();
});

document.addEventListener('change', function (e) {
    if (e.target?.name === 'chk_child') toggleBatchButtons();
});

function toggleBatchButtons() {
    const anyChecked = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length > 0;
    document.getElementById('register-selected-btn')?.classList.toggle('d-none', !anyChecked);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', !anyChecked);
}

// ============================================================================
// GET SELECTED STUDENT IDS
// ============================================================================
function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.value));
}

// ============================================================================
// GET SELECTED SUBJECT CLASSES
// ============================================================================
function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid: parseInt(cb.dataset.subjectclassid),
        staffid       : parseInt(cb.dataset.staffid),
        termid        : parseInt(cb.dataset.termid),
    }));
}

// ============================================================================
// REGISTER BATCH
// ============================================================================
async function registerSelectedStudentsBatch() {
    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    if (!studentIds.length) return showToast('Please select at least one student.', 'warning');
    if (!subjectClasses.length) return showToast('Please select at least one subject.', 'warning');
    if (sessionId === 'ALL') return showToast('Please select a session.', 'warning');

    if (!confirm(`Register ${studentIds.length} student(s) for ${subjectClasses.length} subject(s)?`)) return;

    setSpinner(true);

    try {
        const res  = await apiFetch(ROUTES.batchRegister, 'POST', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });

        showToast(res.message || 'Registration complete.', res.success ? 'success' : 'warning');
        if (res.success && res.redirect) {
            setTimeout(() => { window.location.href = res.redirect; }, 1500);
        }
    } catch (err) {
        showToast('Registration failed: ' + err.message, 'danger');
    } finally {
        setSpinner(false);
    }
}

// ============================================================================
// UNREGISTER BATCH
// ============================================================================
async function unregisterSelectedStudentsBatch() {
    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    if (!studentIds.length) return showToast('Please select at least one student.', 'warning');
    if (!subjectClasses.length) return showToast('Please select at least one subject.', 'warning');
    if (sessionId === 'ALL') return showToast('Please select a session.', 'warning');

    if (!confirm(`Unregister ${studentIds.length} student(s) from ${subjectClasses.length} subject(s)?\n\nThis will be saved to the unregistration history and can be restored.`)) return;

    setSpinner(true);

    try {
        const res = await apiFetch(ROUTES.destroy, 'DELETE', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });

        showToast(res.message || 'Unregistration complete.', res.success ? 'success' : 'warning');
        if (res.success && res.redirect) {
            setTimeout(() => { window.location.href = res.redirect; }, 1500);
        }
    } catch (err) {
        showToast('Unregistration failed: ' + err.message, 'danger');
    } finally {
        setSpinner(false);
    }
}

// ============================================================================
// REGISTERED CLASSES MODAL
// ============================================================================
async function loadRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-error-warning-line ri-3x text-warning mb-3"></i>
                <p class="text-muted">Please select a specific class and session first.</p>
            </div>`;
        return;
    }

    container.innerHTML = `<div class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading registered classes...</p>
    </div>`;

    try {
        const params = new URLSearchParams({
            class_id: classId,
            session_id: sessionId,
            include_teachers: true
        });
        const res    = await fetch(ROUTES.getRegistered + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-information-line ri-3x text-muted mb-3"></i>
                    <p class="text-muted">No registered classes found for the selected filters.</p>
                    <small class="text-muted">Try selecting a different class or session.</small>
                </div>`;
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><i class="ri-calendar-event-line me-1"></i> Term</th>
                            <th><i class="ri-book-line me-1"></i> Subject</th>
                            <th><i class="ri-code-line me-1"></i> Code</th>
                            <th><i class="ri-user-smile-line me-1"></i> Teacher</th>
                            <th><i class="ri-user-line me-1"></i> Students</th>
                        </tr>
                    </thead>
                    <tbody>`;

        data.data.forEach(term => {
            term.subjects.forEach(subject => {
                html += `
                    <tr>
                        <td class="fw-semibold">
                            <span class="badge bg-warning-subtle text-warning">
                                ${term.term_name}
                            </span>
                        </td>
                        <td>
                            <i class="ri-book-open-line me-1 text-primary"></i>
                            ${subject.subject_name}
                        </td>
                        <td><code>${subject.subject_code || 'N/A'}</code></td>
                        <td>
                            <span class="badge bg-info-subtle text-info">
                                <i class="ri-user-smile-line me-1"></i>
                                ${subject.teacher_name}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary rounded-pill">${subject.student_count}</span>
                        </td>
                    </tr>`;
            });
        });

        html += `
                    </tbody>
                </table>
            </div>
            <div class="mt-3 p-3 bg-light border-top">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="ri-information-line me-1"></i>
                            Total Terms: ${data.data.length} |
                            Total Subjects: ${data.data.reduce((sum, t) => sum + t.subject_count, 0)}
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            <i class="ri-time-line me-1"></i>
                            Last updated: ${new Date().toLocaleString()}
                        </small>
                    </div>
                </div>
            </div>`;

        container.innerHTML = html;

    } catch (err) {
        console.error('Error loading registered classes:', err);
        container.innerHTML = `
            <div class="alert alert-danger m-3">
                <i class="ri-error-warning-line me-2"></i>
                Failed to load registered classes: ${err.message}
            </div>`;
    }
}

// ============================================================================
// ARCHIVE MODAL — OPEN
// ============================================================================
function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        showToast('Please select a class and session first.', 'warning');
        return;
    }

    archiveCurrentPage = 1;
    const modal = new bootstrap.Modal(document.getElementById('archivedModal'));
    modal.show();
    loadArchivedPage(1);
}

// ============================================================================
// ARCHIVE MODAL — LOAD PAGE
// ============================================================================
async function loadArchivedPage(page) {
    archiveCurrentPage = page;

    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId    = document.getElementById('archiveTermFilter').value;
    const search    = document.getElementById('archiveSearch').value.trim();

    if (classId === 'ALL' || sessionId === 'ALL') return;

    const spinner = document.getElementById('archiveSpinner');
    const tbody   = document.getElementById('archiveTableBody');

    spinner.classList.remove('d-none');
    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading...
    </td></tr>`;

    try {
        const params = new URLSearchParams({
            class_id  : classId,
            session_id: sessionId,
            page,
            per_page  : 50,
        });
        if (termId)  params.set('term_id', termId);
        if (search)  params.set('search', search);

        const res  = await fetch(ROUTES.getArchived + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">${data.message}</td></tr>`;
            return;
        }

        archiveMeta = data.meta;
        renderArchiveRows(data.data);
        renderArchivePagination(data.meta);
        updateArchiveMeta(data.meta);

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">Error: ${err.message}</td></tr>`;
    } finally {
        spinner.classList.add('d-none');
    }
}

// ============================================================================
// ARCHIVE MODAL — RENDER ROWS
// ============================================================================
function renderArchiveRows(rows) {
    const tbody = document.getElementById('archiveTableBody');

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">No archived records found.</td></tr>`;
        toggleArchiveBatchButtons();
        return;
    }

    let html = '';
    rows.forEach(row => {
        const studentName = `${row.lastname ?? ''} ${row.firstname ?? ''}`.trim();
        const unregDate   = row.unregistered_at
            ? new Date(row.unregistered_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
            : '—';

        html += `<tr data-archive-id="${row.archive_id}">
            <td>
                <div class="form-check mb-0">
                    <input class="form-check-input archive-chk" type="checkbox" value="${row.archive_id}">
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-xs">
                        <div class="avatar-title rounded-circle bg-light">
                            <img src="${row.picture ? '{{ asset("storage/student_avatars/") }}/' + row.picture + '"' : ''}"
                                 class="rounded-circle" style="width:32px;height:32px;object-fit:cover;"
                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                        </div>
                    </div>
                    <span class="fw-medium">${studentName}</span>
                </div>
            </td>
            <td>${row.admissionno || '—'}</td>
            <td>
                <span class="badge bg-primary-subtle text-primary">${row.subjectname || '—'}</span>
                <small class="text-muted d-block">${row.subjectcode || ''}</small>
            </td>
            <td><small>${row.staffname || '—'}</small></td>
            <td><span class="badge bg-warning-subtle text-warning-emphasis">${row.termname || '—'}</span></td>
            <td><small>${unregDate}</small></td>
            <td><small>${row.unregistered_by_name || '—'}</small></td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-xs btn-success" title="Restore this registration"
                        onclick="restoreSingle(${row.archive_id})">
                        <i class="ri-refresh-line"></i>
                    </button>
                    <button class="btn btn-xs btn-danger" title="Permanently delete"
                        onclick="permanentDeleteSingle(${row.archive_id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;

    // Check-all binding
    const checkAll = document.getElementById('archiveCheckAll');
    if (checkAll) {
        checkAll.checked = false;
        checkAll.onchange = function() {
            document.querySelectorAll('.archive-chk').forEach(cb => cb.checked = this.checked);
            toggleArchiveBatchButtons();
        };
    }
    document.querySelectorAll('.archive-chk').forEach(cb => {
        cb.onchange = toggleArchiveBatchButtons;
    });
}

// ============================================================================
// ARCHIVE — PAGINATION RENDER
// ============================================================================
function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }

    let html = '';
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === 1 ? 'disabled' : ''}"
        onclick="loadArchivedPage(${meta.current_page - 1})">‹</button>`;

    const delta = 3;
    for (let p = 1; p <= meta.last_page; p++) {
        if (p === 1 || p === meta.last_page || (p >= meta.current_page - delta && p <= meta.current_page + delta)) {
            html += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}"
                onclick="loadArchivedPage(${p})">${p}</button>`;
        } else if (p === meta.current_page - delta - 1 || p === meta.current_page + delta + 1) {
            html += `<span class="btn btn-sm btn-outline-secondary disabled">…</span>`;
        }
    }

    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}"
        onclick="loadArchivedPage(${meta.current_page + 1})">›</button>`;

    container.innerHTML = html;
}

function updateArchiveMeta(meta) {
    const el = document.getElementById('archiveMeta');
    if (!meta) { el.textContent = ''; return; }
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to   = Math.min(meta.current_page * meta.per_page, meta.total);
    el.textContent = `Showing ${from}–${to} of ${meta.total} records`;
}

function toggleArchiveBatchButtons() {
    const anyChecked = document.querySelectorAll('.archive-chk:checked').length > 0;
    document.getElementById('restoreSelectedBtn')?.classList.toggle('d-none', !anyChecked);
    document.getElementById('deleteSelectedBtn')?.classList.toggle('d-none', !anyChecked);
}

// ============================================================================
// ARCHIVE — SEARCH (debounced)
// ============================================================================
document.getElementById('archiveSearch')?.addEventListener('input', function () {
    clearTimeout(archiveSearchTimer);
    archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400);
});

document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));

// ============================================================================
// RESTORE — SINGLE
// ============================================================================
async function restoreSingle(archiveId) {
    if (!confirm('Restore this registration? The student will be re-registered for this subject.')) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: [archiveId] });
        showToast(res.message || 'Restored.', res.success ? 'success' : 'warning');
        if (res.success) loadArchivedPage(archiveCurrentPage);
    } catch (err) {
        showToast('Restore failed: ' + err.message, 'danger');
    } finally {
        spinner.classList.add('d-none');
    }
}

// ============================================================================
// RESTORE — BATCH
// ============================================================================
async function restoreSelected() {
    const ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    if (!confirm(`Restore ${ids.length} registration(s)?`)) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: ids });
        showToast(res.message || 'Restored.', res.success ? 'success' : 'warning');
        if (res.success) loadArchivedPage(archiveCurrentPage);
    } catch (err) {
        showToast('Restore failed: ' + err.message, 'danger');
    } finally {
        spinner.classList.add('d-none');
    }
}

// ============================================================================
// PERMANENT DELETE — SINGLE
// ============================================================================
async function permanentDeleteSingle(archiveId) {
    if (!confirm('Permanently delete this archive record? This cannot be undone.')) return;

    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: [archiveId] });
        showToast(res.message || 'Deleted.', res.success ? 'success' : 'danger');
        if (res.success) loadArchivedPage(archiveCurrentPage);
    } catch (err) {
        showToast('Delete failed: ' + err.message, 'danger');
    }
}

// ============================================================================
// PERMANENT DELETE — BATCH
// ============================================================================
async function permanentDeleteSelected() {
    const ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    if (!confirm(`Permanently delete ${ids.length} archive record(s)? This CANNOT be undone.`)) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });
        showToast(res.message || 'Deleted.', res.success ? 'success' : 'danger');
        if (res.success) loadArchivedPage(archiveCurrentPage);
    } catch (err) {
        showToast('Delete failed: ' + err.message, 'danger');
    } finally {
        spinner.classList.add('d-none');
    }
}

// ============================================================================
// SPINNER HELPER
// ============================================================================
function setSpinner(on) {
    document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on);
}

// ============================================================================
// FETCH HELPER
// ============================================================================
async function apiFetch(url, method, body) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type' : 'application/json',
            'Accept'       : 'application/json',
            'X-CSRF-TOKEN' : CSRF,
        },
        body: JSON.stringify(body),
    });

    const data = await res.json();
    if (!res.ok && !data.success) {
        throw new Error(data.message || `HTTP ${res.status}`);
    }
    return data;
}

// ============================================================================
// TOAST HELPER
// ============================================================================
function showToast(message, type = 'info') {
    document.querySelectorAll('.sop-toast').forEach(t => t.remove());

    const colorMap = { success: 'bg-success', danger: 'bg-danger', warning: 'bg-warning text-dark', info: 'bg-info text-dark' };
    const bg = colorMap[type] || 'bg-secondary';

    const toast = document.createElement('div');
    toast.className = `toast sop-toast align-items-center text-white border-0 show ${bg}`;
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;min-width:280px;max-width:420px;border-radius:0.5rem;box-shadow:0 0.5rem 1rem rgba(0,0,0,0.15);';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>`;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>
@endsection
