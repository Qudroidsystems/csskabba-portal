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
                                                                data-termid="{{ $teacher->termid }}"
                                                                data-subjectname="{{ $teacher->subjectname }}"
                                                                data-teachername="{{ $teacher->staffname }}" checked>
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
                                        onclick="registerSelectedStudentsBatch();" aria-label="Register selected students">
                                        Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn"
                                        onclick="openUnregisterModal();" aria-label="Unregister selected students">
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

                {{-- MODAL: Snapshot Name --}}
                <div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-labelledby="snapshotNameModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                        <div class="modal-content border-0 shadow-lg overflow-hidden">
                            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#f5576c 0%,#f093fb 100%);">
                                <div class="py-1">
                                    <h5 class="modal-title text-white fw-semibold" id="snapshotNameModalLabel">
                                        <i class="ri-archive-line me-2"></i>Name this Unregistration
                                    </h5>
                                    <p class="text-white-50 small mb-0">Give this snapshot a name so you can find it later.</p>
                                </div>
                                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex gap-2 flex-wrap mb-4" id="snapshotSummaryPills">
                                    <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2" id="snapshotStudentCount"></span>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-3 py-2" id="snapshotSubjectCount"></span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="snapshotNameInput">
                                        Snapshot Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="snapshotNameInput"
                                        placeholder="e.g. Term 2 Corrections — June 2025"
                                        maxlength="191" autocomplete="off">
                                    <div class="invalid-feedback" id="snapshotNameError">Please enter a snapshot name.</div>
                                    <div class="form-text">
                                        <i class="ri-lightbulb-line me-1 text-warning"></i>
                                        A descriptive name helps staff identify this batch when restoring it later.
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fw-semibold" for="snapshotNotesInput">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                    <textarea class="form-control" id="snapshotNotesInput" rows="3"
                                        placeholder="Reason for unregistration or any extra context…"
                                        maxlength="1000"></textarea>
                                    <div class="form-text text-end">
                                        <span id="snapshotNotesCount">0</span>/1000
                                    </div>
                                </div>
                                <div class="alert alert-warning d-flex gap-2 align-items-start mt-3 mb-0 py-2">
                                    <i class="ri-error-warning-line fs-5 flex-shrink-0"></i>
                                    <div class="small">
                                        All existing scores for these students in the selected subjects will be saved to the snapshot and can be fully restored later.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger px-4" id="confirmUnregisterBtn" onclick="proceedUnregister();">
                                    <i class="ri-user-unfollow-line me-1"></i> Unregister & Save Snapshot
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Registered Classes with Print Button --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                                <h5 class="modal-title text-white fw-medium">
                                    <i class="ri-graduation-cap-line me-2"></i>Registered Classes Overview
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-light" onclick="printRegisteredClasses();" style="border-radius: 6px;">
                                        <i class="ri-printer-line me-1"></i> Print / PDF
                                    </button>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            </div>
                            <div class="modal-body p-4" style="background: #f4f7fc;">
                                <div id="registeredClassesContent">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                                        <p class="mt-3 text-muted">Loading registration data...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-transparent">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Unregistered History --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="modal-title text-white" id="archivedModalLabel">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control form-control-sm" id="archiveSearch"
                                            placeholder="Search snapshot name or subject…" style="max-width:300px;">
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
                                <div class="p-3" id="snapshotCardsContainer">
                                    <div class="text-center text-muted py-4">
                                        Select a class and session first, then open this panel.
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="archivePaginationWrap">
                                    <small class="text-muted" id="archiveMeta"></small>
                                    <div id="archivePagination" class="d-flex gap-1"></div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <small class="text-muted me-auto">
                                    <i class="ri-information-line me-1"></i>
                                    Click a snapshot to view student details. Restored records are fully re-registered with original scores.
                                </small>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Snapshot Detail --}}
                <div class="modal fade" id="snapshotDetailModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0" style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);">
                                <div>
                                    <h5 class="modal-title text-white fw-semibold" id="snapshotDetailTitle">Snapshot Detail</h5>
                                    <p class="text-white-50 small mb-0" id="snapshotDetailSubtitle"></p>
                                </div>
                                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="snapshotNotesBanner" class="alert alert-info d-flex gap-2 align-items-start m-3 mb-0 d-none">
                                    <i class="ri-sticky-note-line fs-5 flex-shrink-0"></i>
                                    <div id="snapshotNotesText" class="small"></div>
                                </div>
                                <div class="px-3 pt-3 pb-2 border-bottom">
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm" style="max-width:340px;">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="ri-search-line text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0"
                                                id="detailSearchInput"
                                                placeholder="Search by name or admission no…"
                                                oninput="filterDetailRows(this.value);">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="document.getElementById('detailSearchInput').value='';filterDetailRows('');"
                                                title="Clear search">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-success" id="detailRestoreAllBtn" onclick="restoreEntireSnapshot();">
                                            <i class="ri-refresh-line me-1"></i> Restore All
                                        </button>
                                        <button class="btn btn-sm btn-success d-none" id="detailRestoreSelectedBtn" onclick="restoreDetailSelected();">
                                            <i class="ri-refresh-line me-1"></i> Restore Selected
                                        </button>
                                        <button class="btn btn-sm btn-danger d-none" id="detailDeleteSelectedBtn" onclick="deleteDetailSelected();">
                                            <i class="ri-delete-bin-line me-1"></i> Delete Selected
                                        </button>
                                        <div class="spinner-border spinner-border-sm text-primary d-none ms-1" id="detailSpinner" role="status"></div>
                                        <span class="text-muted small ms-auto" id="detailStudentMeta"></span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr id="snapshotDetailHeaderRow">
                                                <th style="width:36px;">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" id="detailCheckAll">
                                                    </div>
                                                </th>
                                                <th>Student</th>
                                                <th>Adm. No</th>
                                                <th>Gender</th>
                                            </tr>
                                        </thead>
                                        <tbody id="snapshotDetailBody">
                                            <tr><td colspan="10" class="text-center text-muted py-4">Loading…</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
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

            </div>
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
    unregister      : '{{ route("subjects.destroy") }}',
    getRegistered   : '{{ route("subjects.registered-classes") }}',
    getArchived     : '{{ route("subjectoperation.archived") }}',
    getSnapshot     : '{{ route("subjectoperation.snapshot.detail") }}',
    restore         : '{{ route("subjectoperation.restore") }}',
    permanentDelete : '{{ route("subjectoperation.archive.batch-delete") }}',
    index           : '{{ route("subjects.index") }}',
};
const CSRF       = '{{ csrf_token() }}';
const AVATAR_URL = '{{ asset("storage") }}';

// Archive / snapshot state
let archiveCurrentPage = 1;
let archiveMeta        = {};
let archiveSearchTimer = null;

// Current snapshot being viewed in the detail modal
let currentSnapshotMeta = null;
let currentSnapshotRows = [];

// Store current registered classes data for printing
let currentRegisteredClassesData = null;

// ============================================================================
// SWEET ALERT HELPER
// ============================================================================
function showSweetAlert(title, message, type, success = true) {
    Swal.fire({
        title,
        html: `<div class="d-flex align-items-center justify-content-center gap-2">
                <span style="font-size:2rem;">${success ? '🎉' : '😞'}</span>
                <span>${message}</span>
               </div>`,
        icon: success ? 'success' : 'error',
        confirmButtonColor: success ? '#28a745' : '#dc3545',
        confirmButtonText: success ? 'Great!' : 'Okay',
        timer: success ? 3000 : 5000,
        showConfirmButton: true,
    });
}

// ============================================================================
// BUILD SUBJECT-TEACHER MAPPING FROM CHECKBOXES
// ============================================================================
function buildSubjectTeacherMapping() {
    const mapping = {};
    document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
        const subjectName = checkbox.dataset.subjectname;
        const teacherName = checkbox.dataset.teachername;
        const termId = checkbox.dataset.termid;

        if (subjectName && teacherName) {
            const key = `${subjectName}|${termId}`;
            mapping[key] = teacherName;
        }
    });
    console.log('Built teacher mapping:', mapping);
    return mapping;
}

// Helper function to map term name to ID
function getTermIdFromName(termName) {
    const termMap = {
        'First Term': 1,
        'Second Term': 2,
        'Third Term': 3
    };
    return termMap[termName] || '';
}

// ============================================================================
// ESCAPE HTML
// ============================================================================
function escapeHtml(str) {
    if (!str) return str ?? '';
    return String(str).replace(/[&<>"']/g, function(match) {
        if (match === '&') return '&amp;';
        if (match === '<') return '&lt;';
        if (match === '>') return '&gt;';
        if (match === '"') return '&quot;';
        if (match === "'") return '&#039;';
        return match;
    });
}

// ============================================================================
// LOAD REGISTERED CLASSES WITH PROPER TEACHER MAPPING
// ============================================================================
function loadRegisteredClasses() {
    if (typeof axios === 'undefined') {
        console.error('Axios not initialized.');
        return;
    }

    const modalContent = document.getElementById('registeredClassesContent');
    if (!modalContent) {
        console.error('Modal content element not found.');
        return;
    }

    const classSelect = document.getElementById('idclass');
    const sessionSelect = document.getElementById('idsession');

    if (!classSelect || !sessionSelect) {
        modalContent.innerHTML = '<p class="text-center text-red-500">Class or session selector not found.</p>';
        return;
    }

    const classId = classSelect.value;
    const sessionId = sessionSelect.value;

    if (!classId || classId === 'ALL' || !sessionId || sessionId === 'ALL') {
        modalContent.innerHTML = '<p class="text-center text-muted">Please select a valid class and session.</p>';
        Swal.fire({
            icon: 'warning',
            title: 'Missing Selection',
            text: 'Please select a valid class and session.',
            showConfirmButton: true
        });
        return;
    }

    modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div><p class="mt-3 text-muted">Loading registration data...</p></div>';

    // Build subject-teacher mapping from the Subject Teachers section
    const teacherMapping = buildSubjectTeacherMapping();

    axios.get('/subjects/registered-classes', {
        params: { class_id: classId, session_id: sessionId },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 15000
    }).then(response => {
        if (response.data.success) {
            const classes = response.data.data;

            currentRegisteredClassesData = {
                data: classes,
                class_id: classId,
                session_id: sessionId,
                class_name: classes[0]?.class_name || 'N/A',
                arm_name: classes[0]?.arm_name || 'N/A',
                session_name: classes[0]?.session_name || 'N/A',
                teacher_mapping: teacherMapping
            };

            if (!classes || classes.length === 0) {
                modalContent.innerHTML = '<div class="text-center py-5"><i class="ri-information-line ri-3x text-muted"></i><p class="text-muted mt-3 mb-0">No registered classes found.</p></div>';
                return;
            }

            let html = '';

            classes.forEach((termGroup) => {
                const subjectsArray = termGroup.subjects ? termGroup.subjects.split(',').map(s => s.trim()) : [];
                const totalStudents = termGroup.student_count || 0;
                const totalSubjects = termGroup.subject_count || subjectsArray.length;
                const termId = getTermIdFromName(termGroup.term_name);

                html += `
                <div class="term-card mb-4" style="background:#fff; border-radius:12px; border:0.5px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="term-header p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom:0.5px solid #e2e8f0; background:#fff;">
                        <div>
                            <h5 class="fw-semibold mb-0" style="font-size:1rem;">${escapeHtml(termGroup.class_name)} ${escapeHtml(termGroup.arm_name)} — ${escapeHtml(termGroup.session_name)}</h5>
                            <span class="text-muted small">${escapeHtml(termGroup.term_name)}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge" style="background:#E6F1FB; color:#0C447C; padding:4px 12px; border-radius:20px; font-weight:500;">
                                <i class="ri-user-line me-1"></i>${totalStudents} students
                            </span>
                            <span class="badge" style="background:#EEEDFE; color:#3C3489; padding:4px 12px; border-radius:20px; font-weight:500;">
                                <i class="ri-book-open-line me-1"></i>${totalSubjects} subjects
                            </span>
                        </div>
                    </div>
                    <div class="subjects-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr));">
                `;

                subjectsArray.forEach((subjectName, idx) => {
                    const mappingKey = `${subjectName}|${termId}`;
                    let teacherName = teacherMapping[mappingKey] || '— Not assigned';

                    // Fallback: try to find by subject name only
                    if (teacherName === '— Not assigned') {
                        for (const key in teacherMapping) {
                            if (key.startsWith(subjectName)) {
                                teacherName = teacherMapping[key];
                                break;
                            }
                        }
                    }

                    html += `
                    <div class="subject-card p-3 d-flex gap-3 align-items-start" style="border-right:0.5px solid #e2e8f0; border-bottom:0.5px solid #e2e8f0; transition:all 0.2s ease;"
                         onmouseenter="this.style.backgroundColor='#fafbff'"
                         onmouseleave="this.style.backgroundColor='transparent'">
                        <div class="subject-num" style="width:30px; height:30px; background:#EEEDFE; color:#3C3489; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; flex-shrink:0;">${idx + 1}</div>
                        <div class="subject-info flex-grow-1">
                            <div class="fw-semibold" style="font-size:0.9rem; color:#1e293b;">${escapeHtml(subjectName)}</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">
                                <i class="ri-user-star-line me-1"></i>${escapeHtml(teacherName)}
                            </div>
                            <span class="badge mt-2" style="background:#EAF3DE; color:#27500A; font-size:10px; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                <i class="ri-group-line" style="font-size:10px;"></i>${totalStudents} students
                            </span>
                        </div>
                    </div>`;
                });

                html += `
                    </div>
                </div>`;
            });

            modalContent.innerHTML = html;

        } else {
            modalContent.innerHTML = '<div class="text-center py-5 text-danger"><i class="ri-error-warning-line ri-3x"></i><p class="mt-2">Failed to load registered classes.</p></div>';
        }
    }).catch(error => {
        console.error('Error loading registered classes:', error);
        modalContent.innerHTML = '<div class="text-center py-5 text-danger"><i class="ri-error-warning-line ri-3x"></i><p class="mt-2">Error loading registered classes. Please try again.</p></div>';
    });
}

// ============================================================================
// PRINT / PDF FUNCTION
// ============================================================================
async function printRegisteredClasses() {
    if (!currentRegisteredClassesData || !currentRegisteredClassesData.data) {
        Swal.fire({
            icon: 'warning',
            title: 'No Data',
            text: 'Please load registered classes first.',
            showConfirmButton: true
        });
        return;
    }

    Swal.fire({
        title: 'Preparing PDF...',
        text: 'Please wait while we generate your document.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const schoolInfoResponse = await axios.get('/api/school-information/active', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const schoolInfo = schoolInfoResponse.data.data || {};
        const printHtml = generatePrintHtml(currentRegisteredClassesData, schoolInfo);

        const printWindow = window.open('', '_blank');
        printWindow.document.write(printHtml);
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.print();
            Swal.close();
        };

    } catch (error) {
        console.error('Error preparing print:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to prepare print document. Please try again.',
            showConfirmButton: true
        });
    }
}

// ============================================================================
// GENERATE PRINT HTML
// ============================================================================
function generatePrintHtml(data, schoolInfo) {
    const classes = data.data;
    const teacherMapping = data.teacher_mapping || {};
    const currentDate = new Date().toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
    const currentTime = new Date().toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    });

    const schoolLogo = schoolInfo.logo_url || (schoolInfo.school_logo ?
        (schoolInfo.school_logo.startsWith('http') ? schoolInfo.school_logo : `/storage/${schoolInfo.school_logo}`) :
        '');

    let subjectsHtml = '';

    classes.forEach((termGroup) => {
        const subjectsArray = termGroup.subjects ? termGroup.subjects.split(',').map(s => s.trim()) : [];
        const totalStudents = termGroup.student_count || 0;
        const termId = getTermIdFromName(termGroup.term_name);

        subjectsHtml += `
            <div class="print-term-card">
                <div class="print-term-header">
                    <h3 class="print-term-title">${escapeHtml(termGroup.class_name)} ${escapeHtml(termGroup.arm_name)} — ${escapeHtml(termGroup.session_name)}</h3>
                    <p class="print-term-subtitle">${escapeHtml(termGroup.term_name)} Term</p>
                </div>
                <table class="print-subjects-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="45%">Subject Name</th>
                            <th width="35%">Teacher</th>
                            <th width="15%">Students</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        subjectsArray.forEach((subjectName, idx) => {
            const mappingKey = `${subjectName}|${termId}`;
            let teacherName = teacherMapping[mappingKey] || '— Not assigned';

            if (teacherName === '— Not assigned') {
                for (const key in teacherMapping) {
                    if (key.startsWith(subjectName)) {
                        teacherName = teacherMapping[key];
                        break;
                    }
                }
            }

            subjectsHtml += `
                <tr>
                    <td style="text-align: center;">${idx + 1}</td>
                    <td><strong>${escapeHtml(subjectName)}</strong></td>
                    <td>${escapeHtml(teacherName)}</td>
                    <td style="text-align: center;">${totalStudents}</td>
                </tr>
            `;
        });

        subjectsHtml += `
                    </tbody>
                </table>
            </div>
        `;
    });

    return `<!DOCTYPE html>
    <html>
    <head>
        <title>Registered Classes Overview - ${escapeHtml(data.class_name)} ${escapeHtml(data.arm_name)}</title>
        <meta charset="utf-8">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background: white;
                padding: 20px;
            }

            .print-container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
            }

            .print-header {
                margin-bottom: 30px;
                border-bottom: 3px solid #1e3a5f;
                padding-bottom: 20px;
            }

            .print-school-info {
                display: flex;
                align-items: center;
                gap: 25px;
                margin-bottom: 20px;
            }

            .print-logo {
                max-width: 90px;
                max-height: 90px;
                object-fit: contain;
            }

            .print-school-details {
                flex: 1;
            }

            .print-school-name {
                font-size: 26px;
                font-weight: bold;
                color: #1e3a5f;
                margin: 0 0 8px 0;
                letter-spacing: 1px;
            }

            .print-school-motto {
                font-style: italic;
                color: #666;
                margin: 0 0 10px 0;
                font-size: 14px;
            }

            .print-school-address, .print-school-contact {
                font-size: 12px;
                color: #555;
                margin: 3px 0;
            }

            .print-title-section {
                text-align: center;
                margin: 30px 0 20px 0;
            }

            .print-title {
                font-size: 22px;
                font-weight: bold;
                color: #1e3a5f;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .print-subtitle {
                font-size: 14px;
                color: #666;
                margin: 5px 0 0 0;
            }

            .print-meta {
                text-align: center;
                font-size: 12px;
                color: #888;
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 1px dashed #ddd;
            }

            .print-term-card {
                margin-bottom: 35px;
                page-break-inside: avoid;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .print-term-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 15px 20px;
                border-bottom: 2px solid #1e3a5f;
            }

            .print-term-title {
                font-size: 18px;
                font-weight: bold;
                color: #1e3a5f;
                margin: 0;
            }

            .print-term-subtitle {
                font-size: 13px;
                color: #666;
                margin: 5px 0 0 0;
            }

            .print-subjects-table {
                width: 100%;
                border-collapse: collapse;
            }

            .print-subjects-table th {
                background: #f1f3f5;
                padding: 12px 15px;
                text-align: left;
                font-size: 13px;
                font-weight: bold;
                color: #495057;
                border: 1px solid #dee2e6;
            }

            .print-subjects-table td {
                padding: 10px 15px;
                font-size: 12px;
                border: 1px solid #dee2e6;
                vertical-align: top;
            }

            .print-summary {
                display: flex;
                justify-content: space-around;
                margin: 20px 0 30px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
            }

            .print-summary-item {
                text-align: center;
            }

            .print-summary-label {
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }

            .print-summary-value {
                font-size: 20px;
                font-weight: bold;
                color: #1e3a5f;
            }

            .print-footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
                text-align: center;
                font-size: 10px;
                color: #999;
            }

            @media print {
                body {
                    padding: 0;
                    margin: 0;
                }
                .print-container {
                    max-width: 100%;
                    padding: 20px;
                }
                .print-term-card {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <div class="print-header">
                <div class="print-school-info">
                    ${schoolLogo ? `<img src="${schoolLogo}" alt="School Logo" class="print-logo" onerror="this.style.display='none'">` : ''}
                    <div class="print-school-details">
                        <h1 class="print-school-name">${escapeHtml(schoolInfo.school_name || 'School Name')}</h1>
                        ${schoolInfo.school_motto ? `<p class="print-school-motto">"${escapeHtml(schoolInfo.school_motto)}"</p>` : ''}
                        ${schoolInfo.school_address ? `<p class="print-school-address">📍 ${escapeHtml(schoolInfo.school_address)}</p>` : ''}
                        <p class="print-school-contact">
                            ${schoolInfo.school_phone ? `📞 ${escapeHtml(schoolInfo.school_phone)}` : ''}
                            ${schoolInfo.school_phone && schoolInfo.school_email ? ' | ' : ''}
                            ${schoolInfo.school_email ? `✉️ ${escapeHtml(schoolInfo.school_email)}` : ''}
                        </p>
                    </div>
                </div>
            </div>

            <div class="print-title-section">
                <h2 class="print-title">Registered Classes Overview</h2>
                <p class="print-subtitle">Academic Session: ${escapeHtml(data.session_name)}</p>
            </div>

            <div class="print-meta">
                <p>Generated on: ${currentDate} at ${currentTime}</p>
                <p>Class: ${escapeHtml(data.class_name)} ${escapeHtml(data.arm_name)}</p>
            </div>

            <div class="print-summary">
                <div class="print-summary-item">
                    <div class="print-summary-label">Total Terms</div>
                    <div class="print-summary-value">${classes.length}</div>
                </div>
                <div class="print-summary-item">
                    <div class="print-summary-label">Total Subjects</div>
                    <div class="print-summary-value">${classes.reduce((sum, c) => sum + parseInt(c.subject_count || 0), 0)}</div>
                </div>
                <div class="print-summary-item">
                    <div class="print-summary-label">Total Students</div>
                    <div class="print-summary-value">${classes[0]?.student_count || 0}</div>
                </div>
            </div>

            ${subjectsHtml}

            <div class="print-footer">
                <p>This is a computer-generated document. No signature is required.</p>
                <p>© ${new Date().getFullYear()} ${escapeHtml(schoolInfo.school_name || 'School Name')}. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>`;
}

// ============================================================================
// ADD API ROUTE FOR SCHOOL INFORMATION (Add to your routes file)
// ============================================================================
// In routes/web.php or routes/api.php add:
// Route::get('/api/school-information/active', function () {
//     $schoolInfo = App\Models\SchoolInformation::getActiveSchool();
//     return response()->json([
//         'success' => true,
//         'data' => $schoolInfo ? [
//             'school_name' => $schoolInfo->school_name,
//             'school_address' => $schoolInfo->school_address,
//             'school_phone' => $schoolInfo->school_phone,
//             'school_email' => $schoolInfo->school_email,
//             'school_motto' => $schoolInfo->school_motto,
//             'logo_url' => $schoolInfo->logo_url,
//         ] : null
//     ]);
// })->name('api.school-information.active');

// ============================================================================
// OTHER HELPER FUNCTIONS (Keep your existing ones)
// ============================================================================
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error("Axios is not defined.");
        return false;
    }
    return true;
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
    const countEl = document.getElementById('subjectTeacherCount');
    if (countEl) countEl.innerText = count;
}

function filterData() {
    // Your existing filterData function
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({ icon: 'warning', title: 'Missing filters', text: 'Please select a class and session' });
        return;
    }
    window.location.href = ROUTES.index + '?' + new URLSearchParams({
        class_id: classId,
        session_id: sessionId,
        search: document.querySelector('.search')?.value || '',
        gender: document.getElementById('idgender')?.value || 'ALL',
        admissionno: document.getElementById('idadmission')?.value || 'ALL'
    });
}

// Initialize on DOM load
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById('registeredClassesModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', loadRegisteredClasses);
    }

    // Initialize subject checkboxes
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSubjectCount);
    });
    updateSubjectCount();
});
</script>
