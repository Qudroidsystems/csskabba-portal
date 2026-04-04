{{-- resources/views/subjectoperation/index.blade.php --}}
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
                {{-- ── Class & Session Filter ── --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idclass">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->schoolarm }}</option>
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

                {{-- ── Subject Teachers Card ── --}}
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
                                                                data-termid="{{ $teacher->termid }}" checked>
                                                            <label class="form-check-label" for="subject-{{ $teacher->subjectclassid }}">
                                                                {{ $teacher->subjectname }} ({{ $teacher->staffname }})
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

                {{-- ── Student Filters ── --}}
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

                {{-- ── Students Table ── --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Students
                                        <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $students ? $students->total() : 0 }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0 d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary d-none" id="register-selected-btn"
                                        onclick="registerSelectedStudentsBatch();" aria-label="Register selected students">
                                        Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn"
                                        onclick="unregisterSelectedStudentsBatch();" aria-label="Unregister selected students">
                                        Unregister Selected
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
                                        {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Registered Classes (Beautiful Design with Teacher Pictures) --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="modal-title text-white">
                                    <i class="ri-graduation-cap-line me-2"></i>Registered Classes Overview
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="background: #f8f9fc;">
                                <div id="registeredClassesContent">
                                    <div class="text-center text-muted py-5">
                                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-3 mb-0">Loading registration data...</p>
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

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Unregistered History (Wider + Pagination Controls)   --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="modal-title text-white" id="archivedModalLabel">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">

                                {{-- Toolbar --}}
                                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control form-control-sm" id="archiveSearch"
                                            placeholder="Search student name or admission no..." style="max-width:280px;">
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <select class="form-select form-select-sm" id="archiveTermFilter" style="width:auto;">
                                            <option value="">All Terms</option>
                                            @foreach($schoolterms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select form-select-sm" id="archivePerPage" style="width:auto;">
                                            <option value="20">20 per page</option>
                                            <option value="50" selected>50 per page</option>
                                            <option value="100">100 per page</option>
                                            <option value="150">150 per page</option>
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1);">
                                            <i class="ri-refresh-line"></i> Refresh
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
                                                <th>Unregistered</th>
                                                <th>By</th>
                                                <th style="width:120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="archiveTableBody">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    Select a class and session first, then open this panel.
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
                            <div class="modal-footer bg-light">
                                <small class="text-muted me-auto">
                                    <i class="ri-information-line me-1"></i>
                                    Restored records are re-registered. Permanently deleted records cannot be recovered.
                                </small>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
const AVATAR_URL = '{{ asset("storage") }}';

// Archive state
let archiveCurrentPage = 1;
let archiveMeta        = {};
let archiveSearchTimer = null;

// ============================================================================
// SWEET ALERT HELPER
// ============================================================================
function showSweetAlert(title, message, type, success = true) {
    let icon = success ? 'success' : 'error';
    let customIcon = success ? '🎉' : '😞';

    Swal.fire({
        title: title,
        html: `<div class="d-flex align-items-center justify-content-center gap-2">
                <span style="font-size: 2rem;">${customIcon}</span>
                <span>${message}</span>
               </div>`,
        icon: icon,
        confirmButtonColor: success ? '#28a745' : '#dc3545',
        confirmButtonText: success ? 'Great!' : 'Okay',
        timer: success ? 3000 : 5000,
        showConfirmButton: true,
        backdrop: true
    });
}

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

    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
    document.getElementById('archivePerPage')?.addEventListener('change', function() {
        loadArchivedPage(1);
    });
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
        .map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id));
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

    if (!studentIds.length) {
        showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
        return;
    }
    if (!subjectClasses.length) {
        showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
        return;
    }
    if (sessionId === 'ALL') {
        showSweetAlert('Session Required', 'Please select a session.', 'warning', false);
        return;
    }

    const confirmResult = await Swal.fire({
        title: 'Confirm Registration',
        html: `<div class="text-center">
                <span style="font-size: 3rem;">📚</span>
                <p class="mt-2">Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?</p>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, register!',
        cancelButtonText: 'Cancel'
    });

    if (!confirmResult.isConfirmed) return;

    setSpinner(true);

    try {
        const res  = await apiFetch(ROUTES.batchRegister, 'POST', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });

        if (res.success) {
            showSweetAlert('Registration Successful!', res.message || `${studentIds.length} student(s) registered successfully.`, 'success', true);
            setTimeout(() => location.reload(), 2000);
        } else {
            showSweetAlert('Registration Failed', res.message || 'Some students could not be registered.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Registration failed: ' + err.message, 'error', false);
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

    if (!studentIds.length) {
        showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
        return;
    }
    if (!subjectClasses.length) {
        showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
        return;
    }
    if (sessionId === 'ALL') {
        showSweetAlert('Session Required', 'Please select a session.', 'warning', false);
        return;
    }

    const confirmResult = await Swal.fire({
        title: 'Confirm Unregistration',
        html: `<div class="text-center">
                <span style="font-size: 3rem;">⚠️</span>
                <p class="mt-2">Unregister <strong>${studentIds.length}</strong> student(s) from <strong>${subjectClasses.length}</strong> subject(s)?</p>
                <p class="text-danger small">This will be saved to the unregistration history and can be restored.</p>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, unregister!',
        cancelButtonText: 'Cancel'
    });

    if (!confirmResult.isConfirmed) return;

    setSpinner(true);

    try {
        const res = await apiFetch(ROUTES.destroy, 'DELETE', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });

        if (res.success || res.success_count > 0) {
            showSweetAlert('Unregistration Complete', res.message || `${res.success_count} student(s) unregistered.`, 'success', true);
            setTimeout(() => location.reload(), 2000);
        } else {
            showSweetAlert('Unregistration Failed', res.message || 'No students were unregistered.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Unregistration failed: ' + err.message, 'error', false);
    } finally {
        setSpinner(false);
    }
}

// ============================================================================
// REGISTERED CLASSES MODAL (Beautiful Design with Teacher Pictures)
// ============================================================================
async function loadRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-error-warning-line ri-3x text-warning"></i>
                <p class="text-muted mt-3 mb-0">Please select a class and session first.</p>
            </div>`;
        return;
    }

    container.innerHTML = `<div class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Loading registration data...</p>
    </div>`;

    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId });
        const res    = await fetch(ROUTES.getRegistered + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-information-line ri-3x text-muted"></i>
                    <p class="text-muted mt-3 mb-0">No registered classes found for the selected filters.</p>
                </div>`;
            return;
        }

        let html = `<div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th class="fw-semibold py-3"><i class="ri-group-line me-2"></i>Class</th>
                        <th class="fw-semibold py-3"><i class="ri-calendar-line me-2"></i>Session</th>
                        <th class="fw-semibold py-3"><i class="ri-time-line me-2"></i>Term</th>
                        <th class="fw-semibold py-3 text-center"><i class="ri-user-line me-2"></i>Students</th>
                        <th class="fw-semibold py-3 text-center"><i class="ri-book-open-line me-2"></i>Subjects</th>
                        <th class="fw-semibold py-3"><i class="ri-user-star-line me-2"></i>Teachers</th>
                        <th class="fw-semibold py-3">Subjects List</th>
                    </tr>
                </thead>
                <tbody>`;

        data.data.forEach((row, index) => {
            const bgClass = index % 2 === 0 ? 'bg-light' : '';

            let teachersHtml = '';
            if (row.teachers && row.teachers.length > 0) {
                teachersHtml = '<div class="d-flex flex-wrap gap-2">';
                row.teachers.forEach(teacher => {
                    const picturePath = teacher.picture ? `${AVATAR_URL}/staff_avatars/${teacher.picture}` : `${AVATAR_URL}/staff_avatars/default.png`;
                    teachersHtml += `
                        <div class="d-flex align-items-center gap-2 bg-white rounded-3 px-2 py-1 shadow-sm" style="border: 1px solid #e0e0e0;">
                            <img src="${picturePath}"
                                 class="rounded-circle"
                                 style="width: 32px; height: 32px; object-fit: cover;"
                                 onerror="this.src='${AVATAR_URL}/staff_avatars/default.png'">
                            <span class="fw-medium" style="font-size: 0.85rem;">${escapeHtml(teacher.name)}</span>
                        </div>
                    `;
                });
                teachersHtml += '</div>';
            } else {
                teachersHtml = '<span class="text-muted">—</span>';
            }

            html += `<tr class="${bgClass}">
                <td class="fw-medium">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-school-line text-primary"></i>
                        ${escapeHtml(row.class_name)} ${escapeHtml(row.arm_name)}
                    </div>
                 </td>
                 <td><span class="badge bg-info-subtle text-info">${escapeHtml(row.session_name)}</span></td>
                 <td><span class="badge bg-secondary-subtle text-secondary">${escapeHtml(row.term_name)}</span></td>
                <td class="text-center">
                    <span class="badge bg-primary rounded-pill px-3 py-2">${row.student_count}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-success rounded-pill px-3 py-2">${row.subject_count}</span>
                </td>
                <td>${teachersHtml}</td>
                <td><small class="text-muted">${escapeHtml(row.subjects)}</small></td>
             </tr>`;
        });

        const totalStudents = data.data.reduce((sum, row) => sum + row.student_count, 0);
        const uniqueTeachers = new Set();
        data.data.forEach(row => {
            if (row.teachers) {
                row.teachers.forEach(t => uniqueTeachers.add(t.name));
            }
        });

        html += `</tbody>
            </table>
            <div class="alert alert-info mt-3 mb-0" style="background: linear-gradient(135deg, #e0e7ff 0%, #e6e9ff 100%); border: none;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="ri-information-line me-2"></i>
                        <strong>Summary</strong> | Total Classes: <strong>${data.data.length}</strong> | Total Students: <strong>${totalStudents}</strong>
                    </div>
                    <div>
                        <i class="ri-user-star-line me-1"></i>
                        Teachers: <strong>${uniqueTeachers.size}</strong>
                    </div>
                </div>
            </div>
        </div>`;
        container.innerHTML = html;

    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger m-3">Failed to load data: ${err.message}</div>`;
    }
}

// ============================================================================
// ARCHIVE MODAL FUNCTIONS
// ============================================================================
function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        showSweetAlert('Selection Required', 'Please select a class and session first.', 'warning', false);
        return;
    }

    archiveCurrentPage = 1;
    const modal = new bootstrap.Modal(document.getElementById('archivedModal'));
    modal.show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    archiveCurrentPage = page;

    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId    = document.getElementById('archiveTermFilter').value;
    const search    = document.getElementById('archiveSearch').value.trim();
    const perPage   = document.getElementById('archivePerPage').value;

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
            per_page  : perPage,
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

function renderArchiveRows(rows) {
    const tbody = document.getElementById('archiveTableBody');

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">No archived records found.</td></tr>`;
        updateArchiveToolbar(false);
        return;
    }

    updateArchiveToolbar(true);

    let html = '';
    rows.forEach(row => {
        const studentName = `${row.lastname ?? ''} ${row.firstname ?? ''} ${row.othername ?? ''}`.trim();
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
                    <img src="${AVATAR_URL}/student_avatars/${row.picture ? row.picture.split('/').pop() : 'unnamed.jpg'}"
                         class="rounded-circle" style="width:32px;height:32px;object-fit:cover;"
                         onerror="this.src='${AVATAR_URL}/student_avatars/unnamed.jpg'">
                    <span class="fw-medium">${escapeHtml(studentName)}</span>
                </div>
            </td>
            <td>${escapeHtml(row.admissionno ?? '—')}</td>
            <td>
                <span class="badge bg-primary-subtle text-primary">${escapeHtml(row.subjectname ?? '—')}</span>
                <small class="text-muted d-block">${escapeHtml(row.subjectcode ?? '')}</small>
            </td>
            <td>${escapeHtml(row.staffname ?? '—')}</td>
            <td><span class="badge bg-warning-subtle text-warning-emphasis">${escapeHtml(row.termname ?? '—')}</span></td>
            <td><small>${unregDate}</small></td>
            <td><small>${escapeHtml(row.unregistered_by_name ?? '—')}</small></td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-xs btn-success py-0 px-2" title="Restore this registration"
                        onclick="restoreSingle(${row.archive_id})">
                        <i class="ri-refresh-line"></i>
                    </button>
                    <button class="btn btn-xs btn-danger py-0 px-2" title="Permanently delete"
                        onclick="permanentDeleteSingle(${row.archive_id}, this)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
         </tr>`;
    });

    tbody.innerHTML = html;

    document.getElementById('archiveCheckAll').checked = false;
    document.getElementById('archiveCheckAll').removeEventListener('change', handleArchiveCheckAll);
    document.getElementById('archiveCheckAll').addEventListener('change', handleArchiveCheckAll);

    document.querySelectorAll('.archive-chk').forEach(cb => {
        cb.removeEventListener('change', toggleArchiveBatchButtons);
        cb.addEventListener('change', toggleArchiveBatchButtons);
    });
}

function handleArchiveCheckAll(e) {
    document.querySelectorAll('.archive-chk').forEach(cb => cb.checked = e.target.checked);
    toggleArchiveBatchButtons();
}

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

function updateArchiveToolbar(hasRows) {
    toggleArchiveBatchButtons();
}

function toggleArchiveBatchButtons() {
    const anyChecked = document.querySelectorAll('.archive-chk:checked').length > 0;
    document.getElementById('restoreSelectedBtn')?.classList.toggle('d-none', !anyChecked);
    document.getElementById('deleteSelectedBtn')?.classList.toggle('d-none', !anyChecked);
}

document.getElementById('archiveSearch')?.addEventListener('input', function () {
    clearTimeout(archiveSearchTimer);
    archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400);
});

document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));

// ============================================================================
// RESTORE FUNCTIONS
// ============================================================================
async function restoreSingle(archiveId) {
    const confirmResult = await Swal.fire({
        title: 'Restore Registration?',
        html: '<p>This student will be re-registered for this subject.</p>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, restore!'
    });

    if (!confirmResult.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: [archiveId] });
        if (res.success) {
            showSweetAlert('Restored!', res.message || 'Registration restored successfully.', 'success', true);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Restore Failed', res.message || 'Could not restore registration.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Restore failed: ' + err.message, 'error', false);
    } finally {
        spinner.classList.add('d-none');
    }
}

async function restoreSelected() {
    const ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    const confirmResult = await Swal.fire({
        title: 'Restore Selected?',
        html: `<p>Restore <strong>${ids.length}</strong> registration(s)?</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, restore all'
    });

    if (!confirmResult.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: ids });
        if (res.success || res.total_restored > 0) {
            showSweetAlert('Restored!', `${res.total_restored || ids.length} registration(s) restored.`, 'success', true);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Restore Failed', res.message || 'Could not restore registrations.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Restore failed: ' + err.message, 'error', false);
    } finally {
        spinner.classList.add('d-none');
    }
}

// ============================================================================
// DELETE FUNCTIONS
// ============================================================================
async function permanentDeleteSingle(archiveId, btn) {
    const confirmResult = await Swal.fire({
        title: 'Permanently Delete?',
        html: '<p class="text-danger">This action cannot be undone. The record will be permanently removed.</p>',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete permanently'
    });

    if (!confirmResult.isConfirmed) return;

    btn.disabled = true;
    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: [archiveId] });
        if (res.success) {
            showSweetAlert('Deleted', 'Record permanently deleted.', 'success', false);
            const row = btn.closest('tr');
            row.style.transition = 'opacity .3s';
            row.style.opacity = '0';
            setTimeout(() => { row.remove(); updateArchiveEmpty(); }, 300);
        } else {
            showSweetAlert('Delete Failed', res.message || 'Could not delete record.', 'error', false);
            btn.disabled = false;
        }
    } catch (err) {
        showSweetAlert('Error', 'Delete failed: ' + err.message, 'error', false);
        btn.disabled = false;
    }
}

async function permanentDeleteSelected() {
    const ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    const confirmResult = await Swal.fire({
        title: 'Permanently Delete Selected?',
        html: `<p class="text-danger">You are about to permanently delete <strong>${ids.length}</strong> record(s). This CANNOT be undone.</p>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete permanently'
    });

    if (!confirmResult.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });
        if (res.success) {
            showSweetAlert('Deleted', `${res.deleted || ids.length} record(s) permanently deleted.`, 'success', false);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Delete Failed', res.message || 'Could not delete records.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Delete failed: ' + err.message, 'error', false);
    } finally {
        spinner.classList.add('d-none');
    }
}

function updateArchiveEmpty() {
    const tbody = document.getElementById('archiveTableBody');
    if (!tbody.querySelector('tr[data-archive-id]')) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">No archived records found.</td></tr>`;
        document.getElementById('restoreSelectedBtn')?.classList.add('d-none');
        document.getElementById('deleteSelectedBtn')?.classList.add('d-none');
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function escapeHtml(str) {
    if (!str) return str;
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function setSpinner(on) {
    document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on);
}

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
</script>
