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
                {{-- MODAL: Registered Classes (Wider & Improved UI)              --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="registeredClassesModalLabel">
                                    <i class="ri-graduation-cap-line me-2"></i>Registered Classes Overview
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="registeredClassesContent">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary mb-3" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-muted">Loading registered classes...</p>
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
                {{-- MODAL: Unregistered History (Archive with Per-Page Selector) --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title text-dark" id="archivedModalLabel">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">

                                {{-- Toolbar --}}
                                <div class="p-3 border-bottom bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <div class="search-box">
                                                <input type="text" class="form-control" id="archiveSearch"
                                                    placeholder="Search student name, admission no or subject...">
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
                                                    <i class="ri-restart-line me-1"></i> Restore Selected
                                                </button>
                                                <button class="btn btn-sm btn-danger d-none" id="deleteSelectedBtn" onclick="permanentDeleteSelected();">
                                                    <i class="ri-delete-bin-line me-1"></i> Delete Selected
                                                </button>
                                                <div class="spinner-border spinner-border-sm text-warning d-none" id="archiveSpinner" role="status"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Table --}}
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
                                                <th style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="archiveTableBody">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-5">
                                                    <i class="ri-inbox-line ri-2x mb-2 d-block"></i>
                                                    Select a class and session first, then open this panel.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
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
@endsection

<style>
/* Modal enhancements */
.modal-xl {
    --bs-modal-width: 1200px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Table enhancements */
.table th {
    font-weight: 600;
    border-bottom-width: 2px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
    cursor: pointer;
}

/* Badge styles */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

/* Search box enhancement */
.search-box {
    position: relative;
}

.search-box .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-box .form-control {
    padding-left: 38px;
}

/* Button group enhancements */
.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

/* Animation for row deletion */
tr {
    transition: opacity 0.3s ease;
}
</style>

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

// Archive state
let archiveCurrentPage = 1;
let archivePerPage = 50;
let archiveMeta = {};
let archiveSearchTimer = null;

// ============================================================================
// SWEETALERT HELPER WITH EMOJIS
// ============================================================================
function showSweetAlert(type, title, message, emoji = null) {
    const config = {
        icon: type,
        title: title,
        text: message,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    };

    // Add emoji to title if provided
    if (emoji) {
        config.title = `${emoji} ${title}`;
    }

    // Customize based on type
    if (type === 'success') {
        config.confirmButtonColor = '#28a745';
        if (!emoji) config.title = '😊 ' + title;
    } else if (type === 'error') {
        config.confirmButtonColor = '#dc3545';
        if (!emoji) config.title = '😞 ' + title;
    } else if (type === 'warning') {
        config.confirmButtonColor = '#ffc107';
        if (!emoji) config.title = '⚠️ ' + title;
    } else if (type === 'info') {
        if (!emoji) config.title = 'ℹ️ ' + title;
    }

    return Swal.fire(config);
}

function showSuccessAlert(message, emoji = '😊') {
    return showSweetAlert('success', 'Success!', message, emoji);
}

function showErrorAlert(message, emoji = '😞') {
    return showSweetAlert('error', 'Error!', message, emoji);
}

function showWarningAlert(message, emoji = '⚠️') {
    return showSweetAlert('warning', 'Warning!', message, emoji);
}

function showInfoAlert(message, emoji = 'ℹ️') {
    return showSweetAlert('info', 'Information', message, emoji);
}

// Confirmation dialog
async function showConfirmDialog(title, message, confirmText = 'Yes, proceed!') {
    const result = await Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel'
    });
    return result.isConfirmed;
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

    // Load registered classes when that modal opens
    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);

    // Per-page change listener
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
        await showWarningAlert('Please select at least one student.', '📝');
        return;
    }
    if (!subjectClasses.length) {
        await showWarningAlert('Please select at least one subject.', '📚');
        return;
    }
    if (sessionId === 'ALL') {
        await showWarningAlert('Please select a session.', '📅');
        return;
    }

    const confirmed = await showConfirmDialog(
        'Confirm Registration',
        `Register ${studentIds.length} student(s) for ${subjectClasses.length} subject(s)?`,
        'Yes, Register!'
    );

    if (!confirmed) return;

    setSpinner(true);

    try {
        const res  = await apiFetch(ROUTES.batchRegister, 'POST', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });

        if (res.success) {
            await showSuccessAlert(res.message || 'Registration completed successfully!', '🎉');
            // Refresh the page to show updated data
            setTimeout(() => window.location.reload(), 1500);
        } else {
            await showErrorAlert(res.message || 'Registration failed.', '😞');
        }
    } catch (err) {
        await showErrorAlert('Registration failed: ' + err.message, '😭');
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
        await showWarningAlert('Please select at least one student.', '📝');
        return;
    }
    if (!subjectClasses.length) {
        await showWarningAlert('Please select at least one subject.', '📚');
        return;
    }
    if (sessionId === 'ALL') {
        await showWarningAlert('Please select a session.', '📅');
        return;
    }

    const confirmed = await showConfirmDialog(
        '⚠️ Confirm Unregistration',
        `Unregister ${studentIds.length} student(s) from ${subjectClasses.length} subject(s)?\n\nThis will be saved to the unregistration history and can be restored.`,
        'Yes, Unregister!'
    );

    if (!confirmed) return;

    setSpinner(true);

    try {
        const res = await apiFetch(ROUTES.destroy, 'DELETE', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });

        if (res.success) {
            await showSuccessAlert(res.message || 'Unregistration completed successfully!', '🗑️');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            await showErrorAlert(res.message || 'Unregistration failed.', '😞');
        }
    } catch (err) {
        await showErrorAlert('Unregistration failed: ' + err.message, '😭');
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

    container.innerHTML = `<div class="text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted">Loading registered classes...</p>
    </div>`;

    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId });
        const res    = await fetch(ROUTES.getRegistered + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `<div class="text-center py-5">
                <i class="ri-inbox-line ri-3x text-muted mb-3 d-block"></i>
                <p class="text-muted">No registered classes found for the selected filters.</p>
            </div>`;
            return;
        }

        let html = `<div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>Class</th>
                        <th>Session</th>
                        <th>Term</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Subjects</th>
                        <th>Subjects List</th>
                        <th>Teachers</th>
                    </tr>
                </thead>
                <tbody>`;

        data.data.forEach(row => {
            html += `<tr>
                <td class="fw-medium">
                    <i class="ri-group-line me-1 text-primary"></i>
                    ${escapeHtml(row.class_name ?? '')} ${escapeHtml(row.arm_name ?? '')}
                </td>
                <td>${escapeHtml(row.session_name ?? '')}</td>
                <td><span class="badge bg-info-subtle text-info">${escapeHtml(row.term_name ?? '')}</span></td>
                <td class="text-center"><span class="badge bg-primary rounded-pill">${row.student_count}</span></td>
                <td class="text-center"><span class="badge bg-secondary rounded-pill">${row.subject_count}</span></td>
                <td><small class="text-muted">${escapeHtml(row.subjects ?? 'None')}</small></td>
                <td><small><i class="ri-user-star-line me-1 text-warning"></i>${escapeHtml(row.teachers ?? 'None')}</small></td>
            </tr>`;
        });

        html += `</tbody>
            </table>
        </div>
        <div class="p-3 bg-light border-top">
            <small class="text-muted">
                <i class="ri-information-line me-1"></i>
                Total ${data.data.length} class(es) with registered subjects
            </small>
        </div>`;

        container.innerHTML = html;

    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger m-3">Failed to load: ${err.message}</div>`;
    }
}

// ============================================================================
// ARCHIVE MODAL — OPEN
// ============================================================================
function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        showWarningAlert('Please select a class and session first.', '🔍');
        return;
    }

    archiveCurrentPage = 1;
    archivePerPage = parseInt(document.getElementById('archivePerPage').value);
    const modal = new bootstrap.Modal(document.getElementById('archivedModal'));
    modal.show();
    loadArchivedPage(1);
}

// ============================================================================
// ARCHIVE MODAL — LOAD PAGE
// ============================================================================
async function loadArchivedPage(page) {
    archiveCurrentPage = page;
    archivePerPage = parseInt(document.getElementById('archivePerPage').value);

    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId    = document.getElementById('archiveTermFilter').value;
    const search    = document.getElementById('archiveSearch').value.trim();

    if (classId === 'ALL' || sessionId === 'ALL') return;

    const spinner = document.getElementById('archiveSpinner');
    const tbody   = document.getElementById('archiveTableBody');

    spinner.classList.remove('d-none');
    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5">
        <div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading...
    </td></tr>`;

    try {
        const params = new URLSearchParams({
            class_id  : classId,
            session_id: sessionId,
            page,
            per_page  : archivePerPage,
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
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-5">
            <i class="ri-inbox-line ri-2x mb-2 d-block"></i>
            No archived records found.
        </td></tr>`;
        updateArchiveToolbar(false);
        return;
    }

    updateArchiveToolbar(true);

    let html = '';
    rows.forEach(row => {
        const studentName = `${row.lastname ?? ''} ${row.firstname ?? ''} ${row.othername ?? ''}`.trim();
        const unregDate   = row.unregistered_at
            ? new Date(row.unregistered_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
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
                         class="rounded-circle" style="width:36px;height:36px;object-fit:cover;"
                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                    <div>
                        <div class="fw-medium">${escapeHtml(studentName)}</div>
                        <small class="text-muted">${row.gender || 'N/A'}</small>
                    </div>
                </div>
            </td>
            <td><code>${escapeHtml(row.admissionno ?? '—')}</code></td>
            <td>
                <span class="badge bg-primary-subtle text-primary">${escapeHtml(row.subjectname ?? '—')}</span>
                <small class="text-muted d-block">${escapeHtml(row.subjectcode ?? '')}</small>
            </td>
            <td>${escapeHtml(row.staffname ?? '—')}</td>
            <td><span class="badge bg-info-subtle text-info">${escapeHtml(row.termname ?? '—')}</span></td>
            <td><small>${unregDate}</small></td>
            <td><small>${escapeHtml(row.unregistered_by_name ?? '—')}</small></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success" title="Restore this registration"
                        onclick="restoreSingle(${row.archive_id})">
                        <i class="ri-refresh-line"></i>
                    </button>
                    <button class="btn btn-outline-danger" title="Permanently delete"
                        onclick="permanentDeleteSingle(${row.archive_id}, this)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;

    // Re-bind check-all event
    const checkAll = document.getElementById('archiveCheckAll');
    if (checkAll) {
        checkAll.checked = false;
        checkAll.removeEventListener('change', handleArchiveCheckAll);
        checkAll.addEventListener('change', handleArchiveCheckAll);
    }

    document.querySelectorAll('.archive-chk').forEach(cb => {
        cb.removeEventListener('change', toggleArchiveBatchButtons);
        cb.addEventListener('change', toggleArchiveBatchButtons);
    });
}

function handleArchiveCheckAll(e) {
    document.querySelectorAll('.archive-chk').forEach(cb => cb.checked = e.target.checked);
    toggleArchiveBatchButtons();
}

// ============================================================================
// ARCHIVE — PAGINATION RENDER
// ============================================================================
function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // First page
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === 1 ? 'disabled' : ''}"
        onclick="loadArchivedPage(1)">«</button>`;

    // Previous
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === 1 ? 'disabled' : ''}"
        onclick="loadArchivedPage(${meta.current_page - 1})">‹</button>`;

    // Page numbers
    const delta = 2;
    const range = [];
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === 1 || i === meta.last_page ||
            (i >= meta.current_page - delta && i <= meta.current_page + delta)) {
            range.push(i);
        } else if (i === meta.current_page - delta - 1 || i === meta.current_page + delta + 1) {
            range.push('...');
        }
    }

    // Remove duplicates
    const uniqueRange = [];
    for (let i = 0; i < range.length; i++) {
        if (range[i] !== range[i - 1]) {
            uniqueRange.push(range[i]);
        }
    }

    for (const p of uniqueRange) {
        if (p === '...') {
            html += `<span class="btn btn-sm btn-outline-secondary disabled">…</span>`;
        } else {
            html += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}"
                onclick="loadArchivedPage(${p})">${p}</button>`;
        }
    }

    // Next
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}"
        onclick="loadArchivedPage(${meta.current_page + 1})">›</button>`;

    // Last page
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}"
        onclick="loadArchivedPage(${meta.last_page})">»</button>`;

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
    const confirmed = await showConfirmDialog(
        'Restore Registration',
        'Restore this registration? The student will be re-registered for this subject.',
        'Yes, Restore!'
    );

    if (!confirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: [archiveId] });

        if (res.success) {
            await showSuccessAlert(res.message || 'Registration restored successfully!', '🔄');
            loadArchivedPage(archiveCurrentPage);
        } else {
            await showErrorAlert(res.message || 'Restore failed.', '😞');
        }
    } catch (err) {
        await showErrorAlert('Restore failed: ' + err.message, '😭');
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

    const confirmed = await showConfirmDialog(
        'Batch Restore',
        `Restore ${ids.length} registration(s)?`,
        'Yes, Restore All!'
    );

    if (!confirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: ids });

        if (res.success) {
            await showSuccessAlert(res.message || `${res.total_restored} registration(s) restored successfully!`, '🔄');
            loadArchivedPage(archiveCurrentPage);
        } else {
            await showErrorAlert(res.message || 'Restore failed.', '😞');
        }
    } catch (err) {
        await showErrorAlert('Restore failed: ' + err.message, '😭');
    } finally {
        spinner.classList.add('d-none');
    }
}

// ============================================================================
// PERMANENT DELETE — SINGLE
// ============================================================================
async function permanentDeleteSingle(archiveId, btn) {
    const confirmed = await showConfirmDialog(
        '⚠️ Permanent Deletion',
        'Permanently delete this archive record? This CANNOT be undone.',
        'Yes, Delete Permanently!'
    );

    if (!confirmed) return;

    btn.disabled = true;
    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: [archiveId] });

        if (res.success) {
            await showSuccessAlert(res.message || 'Record permanently deleted.', '🗑️');
            const row = btn.closest('tr');
            row.style.transition = 'opacity .3s';
            row.style.opacity = '0';
            setTimeout(() => { row.remove(); updateArchiveEmpty(); }, 300);
        } else {
            await showErrorAlert(res.message || 'Delete failed.', '😞');
            btn.disabled = false;
        }
    } catch (err) {
        await showErrorAlert('Delete failed: ' + err.message, '😭');
        btn.disabled = false;
    }
}

// ============================================================================
// PERMANENT DELETE — BATCH
// ============================================================================
async function permanentDeleteSelected() {
    const ids = [...document.querySelectorAll('.archive-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    const confirmed = await showConfirmDialog(
        '⚠️ Permanent Deletion',
        `Permanently delete ${ids.length} archive record(s)? This CANNOT be undone.`,
        'Yes, Delete All Permanently!'
    );

    if (!confirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner.classList.remove('d-none');

    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });

        if (res.success) {
            await showSuccessAlert(res.message || `${res.deleted} record(s) permanently deleted.`, '🗑️');
            loadArchivedPage(archiveCurrentPage);
        } else {
            await showErrorAlert(res.message || 'Delete failed.', '😞');
        }
    } catch (err) {
        await showErrorAlert('Delete failed: ' + err.message, '😭');
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
// ESCAPE HTML HELPER
// ============================================================================
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>
