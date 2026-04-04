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
                                        <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $students ? $students->total() : 0 }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0 d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary d-none" id="register-selected-btn"
                                        onclick="registerSelectedStudentsBatch();">
                                        <i class="ri-user-add-line me-1"></i> Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn"
                                        onclick="unregisterSelectedStudentsBatch();">
                                        <i class="ri-user-unfollow-line me-1"></i> Unregister Selected
                                    </button>
                                    <div class="spinner-border text-primary d-none" id="register-loading-spinner" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line me-1"></i> View Registered
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="openArchivedModal();">
                                        <i class="ri-archive-line me-1"></i> Unregistered History
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0">
                                        <thead class="table-active">
                                            <tr>
                                                <th style="width: 40px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                                    </div>
                                                </th>
                                                <th>#</th>
                                                <th>Admission No</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Gender</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @if($students && $students->count())
                                                @foreach($students as $key => $student)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input chk_child" type="checkbox" name="chk_child" value="{{ $student->id }}">
                                                        </div>
                                                    </td>
                                                    <td class="id" data-id="{{ $student->id }}">{{ $students->firstItem() + $key }}</td>
                                                    <td><code>{{ $student->admissionno }}</code></td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="{{ asset('storage/student_avatars/'.$student->picture) }}"
                                                                 class="rounded-circle" width="35" height="35"
                                                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                                                            <span class="fw-medium">{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $student->class_name ?? '' }} {{ $student->arm_name ?? '' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $student->gender == 'Male' ? 'info' : 'danger' }}-subtle text-{{ $student->gender == 'Male' ? 'info' : 'danger' }}">
                                                            {{ $student->gender }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary view-image" data-image="{{ asset('storage/student_avatars/'.$student->picture) }}">
                                                            <i class="ri-image-line"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">
                                                        <i class="ri-inbox-line ri-2x mb-2 d-block"></i>
                                                        No students found. Please select a class and session.
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3">
                                        {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Registered Classes --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="ri-graduation-cap-line me-2"></i>Registered Classes Overview
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="registeredClassesContent">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary mb-3"></div>
                                        <p class="text-muted">Loading registered classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Unregistered History --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title text-dark">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="p-3 border-bottom bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <div class="search-box">
                                                <input type="text" class="form-control" id="archiveSearch" placeholder="Search...">
                                                <i class="ri-search-line search-icon"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" id="archiveTermFilter">
                                                <option value="">All Terms</option>
                                                @foreach($schoolterms as $term)
                                                    <option value="{{ $term->id }}">{{ $term->term }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" id="archivePerPage">
                                                <option value="20">20 per page</option>
                                                <option value="50" selected>50 per page</option>
                                                <option value="100">100 per page</option>
                                                <option value="150">150 per page</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1);">
                                                    <i class="ri-refresh-line me-1"></i> Refresh
                                                </button>
                                                <button class="btn btn-sm btn-success d-none" id="restoreSelectedBtn" onclick="restoreSelected();">
                                                    <i class="ri-restart-line me-1"></i> Restore
                                                </button>
                                                <button class="btn btn-sm btn-danger d-none" id="deleteSelectedBtn" onclick="permanentDeleteSelected();">
                                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                                </button>
                                                <div class="spinner-border spinner-border-sm text-warning d-none" id="archiveSpinner"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-warning sticky-top">
                                            <tr>
                                                <th style="width: 40px;">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" id="archiveCheckAll">
                                                    </div>
                                                </th>
                                                <th>Student</th>
                                                <th>Admission No</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Term</th>
                                                <th>Unregistered Date</th>
                                                <th>Unregistered By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="archiveTableBody">
                                            <tr><td colspan="9" class="text-center py-5 text-muted">Select a class and session first.15ne</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
                                    <small class="text-muted" id="archiveMeta"></small>
                                    <div id="archivePagination" class="d-flex gap-1"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
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
                <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.modal-xl { --bs-modal-width: 1200px; }
.sticky-top { position: sticky; top: 0; z-index: 10; }
.search-box { position: relative; }
.search-box .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; }
.search-box .form-control { padding-left: 38px; }
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// CONFIGURATION
// ============================================================================
const ROUTES = {
    batchRegister: '{{ route("subjectregistration.batch") }}',
    destroy: '{{ route("subjectregistration.destroy") }}',
    getRegistered: '{{ route("subjects.registered-classes") }}',
    getArchived: '{{ route("subjectoperation.archived") }}',
    restore: '{{ route("subjectoperation.restore") }}',
    permanentDelete: '{{ route("subjectoperation.archive.batch-delete") }}',
    index: '{{ route("subjects.index") }}',
};
const CSRF = '{{ csrf_token() }}';

let archiveCurrentPage = 1;
let archivePerPage = 50;
let archiveMeta = {};
let archiveSearchTimer = null;

// ============================================================================
// SWEETALERT HELPERS
// ============================================================================
function showSuccess(message, emoji = '🎉') {
    Swal.fire({ icon: 'success', title: `${emoji} Success!`, text: message, confirmButtonColor: '#28a745' });
}

function showError(message, emoji = '😞') {
    Swal.fire({ icon: 'error', title: `${emoji} Error!`, text: message, confirmButtonColor: '#dc3545' });
}

function showWarning(message, emoji = '⚠️') {
    Swal.fire({ icon: 'warning', title: `${emoji} Warning!`, text: message, confirmButtonColor: '#ffc107' });
}

async function showConfirm(title, message, confirmText = 'Yes, proceed!') {
    const result = await Swal.fire({
        title, text: message, icon: 'question', showCancelButton: true,
        confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
        confirmButtonText: confirmText, cancelButtonText: 'Cancel'
    });
    return result.isConfirmed;
}

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
    document.getElementById('archivePerPage')?.addEventListener('change', () => loadArchivedPage(1));
    document.getElementById('archiveSearch')?.addEventListener('input', function() {
        clearTimeout(archiveSearchTimer);
        archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400);
    });
    document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));

    document.querySelectorAll('.view-image').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('enlargedImage').src = this.dataset.image;
            new bootstrap.Modal(document.getElementById('imageViewModal')).show();
        });
    });

    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('#studentTableBody .chk_child').forEach(cb => cb.checked = this.checked);
        toggleBatchButtons();
    });

    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.addEventListener('change', updateSubjectCount));
    updateSubjectCount();
});

function updateSubjectCount() {
    document.getElementById('subjectTeacherCount').textContent = document.querySelectorAll('.subject-checkbox:checked').length;
}

function selectAllSubjects() { document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true); updateSubjectCount(); }
function deselectAllSubjects() { document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false); updateSubjectCount(); }
function toggleBatchButtons() {
    let hasChecked = document.querySelectorAll('#studentTableBody .chk_child:checked').length > 0;
    document.getElementById('register-selected-btn')?.classList.toggle('d-none', !hasChecked);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', !hasChecked);
}
function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody .chk_child:checked')].map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id));
}
function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid: parseInt(cb.dataset.subjectclassid), staffid: parseInt(cb.dataset.staffid), termid: parseInt(cb.dataset.termid)
    }));
}
function filterData() {
    let params = new URLSearchParams({
        class_id: document.getElementById('idclass').value, session_id: document.getElementById('idsession').value,
        search: document.querySelector('.search')?.value ?? '', gender: document.getElementById('idgender').value,
        admissionno: document.getElementById('idadmission').value
    });
    window.location.href = ROUTES.index + '?' + params.toString();
}

// ============================================================================
// REGISTER / UNREGISTER
// ============================================================================
async function registerSelectedStudentsBatch() {
    let studentIds = getSelectedStudentIds(), subjectClasses = getSelectedSubjectClasses(), sessionId = document.getElementById('idsession').value;
    if (!studentIds.length) return showWarning('Please select at least one student.', '📝');
    if (!subjectClasses.length) return showWarning('Please select at least one subject.', '📚');
    if (sessionId === 'ALL') return showWarning('Please select a session.', '📅');
    if (!await showConfirm('Confirm Registration', `Register ${studentIds.length} student(s) for ${subjectClasses.length} subject(s)?`, 'Yes, Register!')) return;

    document.getElementById('register-loading-spinner')?.classList.remove('d-none');
    try {
        let res = await apiFetch(ROUTES.batchRegister, 'POST', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) });
        if (res.success) { showSuccess(res.message || 'Registration completed!', '🎉'); setTimeout(() => window.location.reload(), 1500); }
        else showError(res.message || 'Registration failed.', '😞');
    } catch(err) { showError('Registration failed: ' + err.message, '😭'); }
    finally { document.getElementById('register-loading-spinner')?.classList.add('d-none'); }
}

async function unregisterSelectedStudentsBatch() {
    let studentIds = getSelectedStudentIds(), subjectClasses = getSelectedSubjectClasses(), sessionId = document.getElementById('idsession').value;
    if (!studentIds.length) return showWarning('Please select at least one student.', '📝');
    if (!subjectClasses.length) return showWarning('Please select at least one subject.', '📚');
    if (sessionId === 'ALL') return showWarning('Please select a session.', '📅');
    if (!await showConfirm('Confirm Unregistration', `Unregister ${studentIds.length} student(s) from ${subjectClasses.length} subject(s)?`, 'Yes, Unregister!')) return;

    document.getElementById('register-loading-spinner')?.classList.remove('d-none');
    try {
        let res = await apiFetch(ROUTES.destroy, 'DELETE', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) });
        if (res.success) { showSuccess(res.message || 'Unregistration completed!', '🗑️'); setTimeout(() => window.location.reload(), 1500); }
        else showError(res.message || 'Unregistration failed.', '😞');
    } catch(err) { showError('Unregistration failed: ' + err.message, '😭'); }
    finally { document.getElementById('register-loading-spinner')?.classList.add('d-none'); }
}

// ============================================================================
// REGISTERED CLASSES MODAL
// ============================================================================
async function loadRegisteredClasses() {
    let classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value;
    let container = document.getElementById('registeredClassesContent');

    if (!classId || classId === 'ALL' || !sessionId || sessionId === 'ALL') {
        container.innerHTML = `<div class="text-center py-5"><i class="ri-error-warning-line ri-3x text-warning mb-3"></i><p>Please select a specific class and session.</p></div>`;
        return;
    }

    container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary mb-3"></div><p>Loading...</p></div>`;

    try {
        let res = await fetch(ROUTES.getRegistered + '?class_id=' + classId + '&session_id=' + sessionId, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        let data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `<div class="text-center py-5"><i class="ri-inbox-line ri-3x text-muted mb-3"></i><p>No registered classes found.</p></div>`;
            return;
        }

        let html = `<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-primary"><tr>
            <th>#</th><th>Class & Arm</th><th>Session</th><th>Term</th><th class="text-center">Students</th><th class="text-center">Subjects</th><th>Subjects List</th><th>Teachers</th>
        </tr></thead><tbody>`;

        data.data.forEach((row, idx) => {
            let termColor = row.term_name === 'First Term' ? 'success' : (row.term_name === 'Second Term' ? 'warning' : 'info');
            let subjectsList = row.subjects && row.subjects !== 'None' ? row.subjects.split(', ').map(s => `<span class="badge bg-primary-subtle text-primary me-1 mb-1">${escapeHtml(s)}</span>`).join('') : '<span class="text-muted">None</span>';
            let teachersList = row.teachers && row.teachers !== 'None' ? row.teachers.split(', ').map(t => `<span class="badge bg-secondary-subtle text-secondary me-1 mb-1"><i class="ri-user-star-line me-1"></i>${escapeHtml(t)}</span>`).join('') : '<span class="text-warning">No teachers assigned</span>';

            html += `<tr>
                <td class="fw-bold">${idx + 1}</td>
                <td><i class="ri-group-line me-2 text-primary"></i>${escapeHtml(row.class_name)} ${row.arm_name && row.arm_name !== 'None' ? '/ ' + escapeHtml(row.arm_name) : ''}</td>
                <td><span class="badge bg-dark-subtle text-dark">${escapeHtml(row.session_name)}</span></td>
                <td><span class="badge bg-${termColor}-subtle text-${termColor}">${escapeHtml(row.term_name)}</span></td>
                <td class="text-center"><span class="badge bg-primary rounded-pill fs-6 px-3">${row.student_count}</span></td>
                <td class="text-center"><span class="badge bg-info rounded-pill fs-6 px-3">${row.subject_count}</span></td>
                <td style="min-width: 250px;">${subjectsList}</td>
                <td style="min-width: 200px;">${teachersList}</td>
            </tr>`;
        });

        html += `</tbody></table></div><div class="p-3 bg-light border-top"><small class="text-muted">Total ${data.data.length} class(es) with registered subjects</small></div>`;
        container.innerHTML = html;
    } catch(err) { container.innerHTML = `<div class="alert alert-danger m-3">Error: ${err.message}</div>`; }
}

// ============================================================================
// ARCHIVE MODAL
// ============================================================================
function openArchivedModal() {
    let classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') return showWarning('Please select a class and session first.', '🔍');
    archiveCurrentPage = 1; archivePerPage = parseInt(document.getElementById('archivePerPage').value);
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    archiveCurrentPage = page; archivePerPage = parseInt(document.getElementById('archivePerPage').value);
    let classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value;
    let termId = document.getElementById('archiveTermFilter').value, search = document.getElementById('archiveSearch').value.trim();

    let spinner = document.getElementById('archiveSpinner'), tbody = document.getElementById('archiveTableBody');
    spinner.classList.remove('d-none');
    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5"><div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading...</td></tr>`;

    try {
        let params = new URLSearchParams({ class_id: classId, session_id: sessionId, page, per_page: archivePerPage });
        if (termId) params.set('term_id', termId);
        if (search) params.set('search', search);

        let res = await fetch(ROUTES.getArchived + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        let data = await res.json();

        if (!data.success) { tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">${data.message}</td></tr>`; return; }

        archiveMeta = data.meta;
        renderArchiveRows(data.data);
        renderArchivePagination(data.meta);
        document.getElementById('archiveMeta').textContent = `Showing ${(data.meta.current_page-1)*data.meta.per_page+1}–${Math.min(data.meta.current_page*data.meta.per_page, data.meta.total)} of ${data.meta.total} records`;
    } catch(err) { tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error: ${err.message}</td></tr>`; }
    finally { spinner.classList.add('d-none'); }
}

function renderArchiveRows(rows) {
    let tbody = document.getElementById('archiveTableBody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted"><i class="ri-inbox-line ri-2x mb-2 d-block"></i>No archived records found.</td></tr>`;
        return;
    }

    let html = '';
    rows.forEach(row => {
        let studentName = `${row.lastname ?? ''} ${row.firstname ?? ''} ${row.othername ?? ''}`.trim();
        let unregDate = row.unregistered_at ? new Date(row.unregistered_at).toLocaleDateString('en-GB') : '—';

        html += `<tr data-archive-id="${row.archive_id}">
            <td><div class="form-check"><input class="form-check-input archive-chk" type="checkbox" value="${row.archive_id}"></div></td>
            <td><div class="d-flex align-items-center gap-2"><img src="{{ asset('storage/student_avatars/') }}/${row.picture || 'unnamed.jpg'}" class="rounded-circle" width="35" height="35" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'"><span class="fw-medium">${escapeHtml(studentName)}</span></div></td>
            <td><code>${escapeHtml(row.admissionno || '—')}</code></td>
            <td><span class="badge bg-primary-subtle text-primary">${escapeHtml(row.subjectname || '—')}</span></td>
            <td>${escapeHtml(row.staffname || '—')}</td>
            <td><span class="badge bg-info-subtle text-info">${escapeHtml(row.termname || '—')}</span></td>
            <td><small>${unregDate}</small></td>
            <td><small>${escapeHtml(row.unregistered_by_name || '—')}</small></td>
            <td><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" onclick="restoreSingle(${row.archive_id})"><i class="ri-refresh-line"></i></button><button class="btn btn-outline-danger" onclick="permanentDeleteSingle(${row.archive_id}, this)"><i class="ri-delete-bin-line"></i></button></div></td>
        </tr>`;
    });
    tbody.innerHTML = html;

    document.getElementById('archiveCheckAll').checked = false;
    document.getElementById('archiveCheckAll').onchange = (e) => document.querySelectorAll('.archive-chk').forEach(cb => cb.checked = e.target.checked);
    document.querySelectorAll('.archive-chk').forEach(cb => cb.onchange = () => {
        let anyChecked = document.querySelectorAll('.archive-chk:checked').length > 0;
        document.getElementById('restoreSelectedBtn')?.classList.toggle('d-none', !anyChecked);
        document.getElementById('deleteSelectedBtn')?.classList.toggle('d-none', !anyChecked);
    });
}

function renderArchivePagination(meta) {
    let container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }
    let html = `<button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1)" ${meta.current_page === 1 ? 'disabled' : ''}>«</button>`;
    html += `<button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(${meta.current_page - 1})" ${meta.current_page === 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === 1 || i === meta.last_page || (i >= meta.current_page - 2 && i <= meta.current_page + 2)) {
            html += `<button class="btn btn-sm ${i === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}" onclick="loadArchivedPage(${i})">${i}</button>`;
        } else if (i === meta.current_page - 3 || i === meta.current_page + 3) {
            html += `<span class="btn btn-sm btn-outline-secondary disabled">…</span>`;
        }
    }
    html += `<button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(${meta.current_page + 1})" ${meta.current_page === meta.last_page ? 'disabled' : ''}>›</button>`;
    html += `<button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(${meta.last_page})" ${meta.current_page === meta.last_page ? 'disabled' : ''}>»</button>`;
    container.innerHTML = html;
}

async function restoreSingle(archiveId) {
    if (!await showConfirm('Restore Registration', 'Restore this registration?', 'Yes, Restore!')) return;
    let spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');
    try {
        let res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: [archiveId] });
        if (res.success) { showSuccess('Registration restored!', '🔄'); loadArchivedPage(archiveCurrentPage); }
        else showError(res.message || 'Restore failed.', '😞');
    } catch(err) { showError('Restore failed: ' + err.message, '😭'); }
    finally { spinner.classList.add('d-none'); }
}

async function restoreSelected() {
    let ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;
    if (!await showConfirm('Batch Restore', `Restore ${ids.length} registration(s)?`, 'Yes, Restore All!')) return;
    let spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');
    try {
        let res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: ids });
        if (res.success) { showSuccess(res.message || `${res.total_restored} restored!`, '🔄'); loadArchivedPage(archiveCurrentPage); }
        else showError(res.message || 'Restore failed.', '😞');
    } catch(err) { showError('Restore failed: ' + err.message, '😭'); }
    finally { spinner.classList.add('d-none'); }
}

async function permanentDeleteSingle(archiveId, btn) {
    if (!await showConfirm('Permanent Deletion', 'This CANNOT be undone!', 'Yes, Delete!')) return;
    btn.disabled = true;
    try {
        let res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: [archiveId] });
        if (res.success) { showSuccess('Deleted permanently.', '🗑️'); loadArchivedPage(archiveCurrentPage); }
        else showError(res.message || 'Delete failed.', '😞');
    } catch(err) { showError('Delete failed: ' + err.message, '😭'); btn.disabled = false; }
}

async function permanentDeleteSelected() {
    let ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;
    if (!await showConfirm('Permanent Deletion', `Delete ${ids.length} record(s)? This CANNOT be undone.`, 'Yes, Delete All!')) return;
    let spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');
    try {
        let res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });
        if (res.success) { showSuccess(res.message || `${res.deleted} deleted.`, '🗑️'); loadArchivedPage(archiveCurrentPage); }
        else showError(res.message || 'Delete failed.', '😞');
    } catch(err) { showError('Delete failed: ' + err.message, '😭'); }
    finally { spinner.classList.add('d-none'); }
}

async function apiFetch(url, method, body) {
    let res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(body)
    });
    let data = await res.json();
    if (!res.ok && !data.success) throw new Error(data.message || `HTTP ${res.status}`);
    return data;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
}
</script>
@endsection
