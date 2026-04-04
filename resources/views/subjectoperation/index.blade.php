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
            @endif>
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
                {{-- MODAL: Registered Classes (Improved UI + Teacher Names) --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header border-0 bg-primary-subtle">
                                <h5 class="modal-title" id="registeredClassesModalLabel">
                                    <i class="ri-eye-line me-2"></i>Registered Classes Overview
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="registeredClassesContent" class="p-4">
                                    <div class="text-center text-muted py-5">
                                        <i class="ri-loader-4-line ri-3x mb-3 text-primary"></i>
                                        <p class="mb-0">Loading registered classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Unregistered History (Wider + Per-page selector) --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
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
                                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-3">
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control form-control-sm" id="archiveSearch"
                                            placeholder="Search student name or admission no..." style="max-width:320px;">
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        {{-- Term filter --}}
                                        <select class="form-select form-select-sm" id="archiveTermFilter" style="width:auto;">
                                            <option value="">All Terms</option>
                                            @foreach($schoolterms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Per-page selector --}}
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

<script>
// ============================================================================
// GLOBALS
// ============================================================================
const ROUTES = {
    batchRegister : '{{ route("subjectregistration.batch") }}',
    destroy : '{{ route("subjectregistration.destroy") }}',
    getRegistered : '{{ route("subjects.registered-classes") }}',
    getArchived : '{{ route("subjectoperation.archived") }}',
    restore : '{{ route("subjectoperation.restore") }}',
    permanentDelete : '{{ route("subjectoperation.archive.batch-delete") }}',
    index : '{{ route("subjects.index") }}',
};
const CSRF = '{{ csrf_token() }}';
// Archive state
let archiveCurrentPage = 1;
let archiveMeta = {};
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
            img.src = src || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
        });
    }
    // Load registered classes when that modal opens
    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);

    // Archive per-page listener
    document.getElementById('archivePerPage')?.addEventListener('change', () => {
        archiveCurrentPage = 1;
        loadArchivedPage(1);
    });
});
// ============================================================================
// FILTER / SEARCH
// ============================================================================
function filterData() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const search = document.querySelector('.search')?.value ?? '';
    const gender = document.getElementById('idgender').value;
    const admission = document.getElementById('idadmission').value;
    const params = new URLSearchParams({
        class_id : classId,
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
        staffid : parseInt(cb.dataset.staffid),
        termid : parseInt(cb.dataset.termid),
    }));
}
// ============================================================================
// REGISTER BATCH
// ============================================================================
async function registerSelectedStudentsBatch() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;
    if (!studentIds.length) return showToast('Please select at least one student.', 'warning');
    if (!subjectClasses.length) return showToast('Please select at least one subject.', 'warning');
    if (sessionId === 'ALL') return showToast('Please select a session.', 'warning');
    if (!confirm(`Register ${studentIds.length} student(s) for ${subjectClasses.length} subject(s)?`)) return;
    setSpinner(true);
    try {
        const res = await apiFetch(ROUTES.batchRegister, 'POST', {
            studentids : studentIds,
            subjectclasses: subjectClasses,
            sessionid : parseInt(sessionId),
        });
        showToast(res.message || 'Registration complete.', res.success ? 'success' : 'warning');
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
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;
    if (!studentIds.length) return showToast('Please select at least one student.', 'warning');
    if (!subjectClasses.length) return showToast('Please select at least one subject.', 'warning');
    if (sessionId === 'ALL') return showToast('Please select a session.', 'warning');
    if (!confirm(`Unregister ${studentIds.length} student(s) from ${subjectClasses.length} subject(s)?\n\nThis will be saved to the unregistration history and can be restored.`)) return;
    setSpinner(true);
    try {
        const res = await apiFetch(ROUTES.destroy, 'DELETE', {
            studentids : studentIds,
            subjectclasses: subjectClasses,
            sessionid : parseInt(sessionId),
        });
        showToast(res.message || 'Unregistration complete.', res.success ? 'success' : 'warning');
        if (res.success || res.success_count > 0) {
            showToast('Records saved to Unregistered History — you can restore them anytime.', 'info');
        }
    } catch (err) {
        showToast('Unregistration failed: ' + err.message, 'danger');
    } finally {
        setSpinner(false);
    }
}
// ============================================================================
// REGISTERED CLASSES MODAL (with Teacher Names)
// ============================================================================
async function loadRegisteredClasses() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');
    container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary ri-3x"></div><p class="mt-3 text-muted">Loading registered classes...</p></div>`;
    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId });
        const res = await fetch(ROUTES.getRegistered + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!data.success || !data.data.length) {
            container.innerHTML = `<div class="text-center py-5 text-muted"><i class="ri-inbox-line ri-4x mb-3"></i><p>No registered classes found for the selected filters.</p></div>`;
            return;
        }
        let html = `<div class="table-responsive"><table class="table table-hover table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Class</th>
                    <th>Session</th>
                    <th>Term</th>
                    <th>Students</th>
                    <th>Subjects</th>
                    <th>Teachers</th>
                    <th>Subject Names</th>
                </tr>
            </thead><tbody>`;
        data.data.forEach(row => {
            html += `<tr>
                <td><strong>${row.class_name ?? ''} ${row.arm_name ?? ''}</strong></td>
                <td>${row.session_name ?? ''}</td>
                <td>${row.term_name ?? ''}</td>
                <td><span class="badge bg-primary rounded-pill px-3">${row.student_count}</span></td>
                <td><span class="badge bg-secondary rounded-pill px-3">${row.subject_count}</span></td>
                <td><small class="text-primary">${row.teachers ?? '—'}</small></td>
                <td><small class="text-muted">${row.subjects ?? ''}</small></td>
            </tr>`;
        });
        html += `</tbody></table></div>`;
        container.innerHTML = html;
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger mx-3">Failed to load registered classes: ${err.message}</div>`;
    }
}
// ============================================================================
// ARCHIVE MODAL — OPEN
// ============================================================================
function openArchivedModal() {
    const classId = document.getElementById('idclass').value;
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
// ARCHIVE MODAL — LOAD PAGE (now supports per-page selector + server-side search)
// ============================================================================
async function loadArchivedPage(page) {
    archiveCurrentPage = page;
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId = document.getElementById('archiveTermFilter').value;
    const search = document.getElementById('archiveSearch').value.trim();
    const perPage = parseInt(document.getElementById('archivePerPage')?.value) || 50;

    if (classId === 'ALL' || sessionId === 'ALL') return;

    const spinner = document.getElementById('archiveSpinner');
    const tbody = document.getElementById('archiveTableBody');
    spinner.classList.remove('d-none');
    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading...
    </td></tr>`;

    try {
        const params = new URLSearchParams({
            class_id : classId,
            session_id: sessionId,
            page,
            per_page : perPage,
        });
        if (termId) params.set('term_id', termId);
        if (search) params.set('search', search);

        const res = await fetch(ROUTES.getArchived + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">${data.message}</td></tr>`;
            return;
        }
        archiveMeta = data.meta;
        renderArchiveRows(data.data);           // ← server already filtered
        renderArchivePagination(data.meta);
        updateArchiveMeta(data.meta);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">Error: ${err.message}</td></tr>`;
    } finally {
        spinner.classList.add('d-none');
    }
}
// ============================================================================
// ARCHIVE MODAL — RENDER ROWS (client-side filter removed — server handles search)
// ============================================================================
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
        const unregDate = row.unregistered_at
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
                    <img src="{{ asset('storage/student_avatars/') }}/${row.picture ? row.picture.split('/').pop() : 'unnamed.jpg'}"
                         class="rounded-circle" style="width:32px;height:32px;object-fit:cover;"
                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                    <span class="fw-medium">${studentName}</span>
                </div>
            </td>
            <td>${row.admissionno ?? '—'}</td>
            <td>
                <span class="badge bg-primary-subtle text-primary">${row.subjectname ?? '—'}</span>
                <small class="text-muted d-block">${row.subjectcode ?? ''}</small>
            </td>
            <td>${row.staffname ?? '—'}</td>
            <td><span class="badge bg-warning-subtle text-warning-emphasis">${row.termname ?? '—'}</span></td>
            <td><small>${unregDate}</small></td>
            <td><small>${row.unregistered_by_name ?? '—'}</small></td>
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
    // Check-all binding
    document.getElementById('archiveCheckAll').checked = false;
    document.getElementById('archiveCheckAll').addEventListener('change', function () {
        document.querySelectorAll('.archive-chk').forEach(cb => cb.checked = this.checked);
        toggleArchiveBatchButtons();
    });
    document.querySelectorAll('.archive-chk').forEach(cb => {
        cb.addEventListener('change', toggleArchiveBatchButtons);
    });
}
// ============================================================================
// ARCHIVE — PAGINATION RENDER
// ============================================================================
function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }
    let html = '';
    // Previous
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
    // Next
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}"
        onclick="loadArchivedPage(${meta.current_page + 1})">›</button>`;
    container.innerHTML = html;
}
function updateArchiveMeta(meta) {
    const el = document.getElementById('archiveMeta');
    if (!meta) { el.textContent = ''; return; }
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to = Math.min(meta.current_page * meta.per_page, meta.total);
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
        if (res.success || res.total_restored > 0) loadArchivedPage(archiveCurrentPage);
    } catch (err) {
        showToast('Restore failed: ' + err.message, 'danger');
    } finally {
        spinner.classList.add('d-none');
    }
}
// ============================================================================
// PERMANENT DELETE — SINGLE
// ============================================================================
async function permanentDeleteSingle(archiveId, btn) {
    if (!confirm('Permanently delete this archive record? This cannot be undone.')) return;
    btn.disabled = true;
    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: [archiveId] });
        showToast(res.message || 'Deleted.', res.success ? 'success' : 'danger');
        if (res.success) {
            const row = btn.closest('tr');
            row.style.transition = 'opacity .3s';
            row.style.opacity = '0';
            setTimeout(() => { row.remove(); updateArchiveEmpty(); }, 300);
        }
    } catch (err) {
        showToast('Delete failed: ' + err.message, 'danger');
        btn.disabled = false;
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
function updateArchiveEmpty() {
    const tbody = document.getElementById('archiveTableBody');
    if (!tbody.querySelector('tr[data-archive-id]')) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">No archived records found.</td></tr>`;
        document.getElementById('restoreSelectedBtn')?.classList.add('d-none');
        document.getElementById('deleteSelectedBtn')?.classList.add('d-none');
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
            'Accept' : 'application/json',
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
// TOAST HELPER (with big emoji feedback as requested)
// ============================================================================
function showToast(message, type = 'info') {
    // Remove existing toasts
    document.querySelectorAll('.sop-toast').forEach(t => t.remove());
    const colorMap = { success: 'bg-success', danger: 'bg-danger', warning: 'bg-warning text-dark', info: 'bg-info text-dark' };
    const bg = colorMap[type] || 'bg-secondary';

    // Emoji feedback
    let emoji = '';
    if (type === 'success') emoji = '😊 ';
    else if (type === 'danger') emoji = '😢 ';
    else if (type === 'warning') emoji = '😟 ';
    else if (type === 'info') emoji = 'ℹ️ ';

    const toast = document.createElement('div');
    toast.className = `toast sop-toast align-items-center text-white border-0 show ${bg}`;
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;min-width:320px;max-width:460px;';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${emoji}${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>
