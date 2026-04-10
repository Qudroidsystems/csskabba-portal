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

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Snapshot Name — shown BEFORE unregistration         --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
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

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Registered Classes Overview (Card-based UI)         --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
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

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Unregistered History (Snapshot List)                --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
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

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Snapshot Detail (students + scores inside a snapshot) --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
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

            </div>{{-- /subjectList --}}
        </div>
    </div>
</div>
@endsection

@section('scripts')
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
// HELPER FUNCTIONS
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

async function apiFetch(url, method, body) {
    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok && !data.success) throw new Error(data.message || `HTTP ${res.status}`);
    return data;
}

function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id));
}

function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid: parseInt(cb.dataset.subjectclassid),
        staffid: parseInt(cb.dataset.staffid),
        termid: parseInt(cb.dataset.termid),
    }));
}

function setSpinner(on) {
    document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on);
}

function toggleBatchButtons() {
    const any = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length > 0;
    document.getElementById('register-selected-btn')?.classList.toggle('d-none', !any);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', !any);
}

// ============================================================================
// REGISTER SELECTED STUDENTS
// ============================================================================
async function registerSelectedStudentsBatch() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;

    if (!studentIds.length) return showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
    if (sessionId === 'ALL') return showSweetAlert('Session Required', 'Please select a session.', 'warning', false);

    const ok = await Swal.fire({
        title: 'Confirm Registration',
        html: `<div class="text-center"><span style="font-size:3rem;">📚</span><p class="mt-2">Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?</p></div>`,
        icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, register!',
    });
    if (!ok.isConfirmed) return;

    setSpinner(true);
    try {
        const res = await apiFetch(ROUTES.batchRegister, 'POST', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) });
        if (res.success) {
            showSweetAlert('Registration Successful!', res.message, 'success', true);
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
// OPEN UNREGISTER MODAL
// ============================================================================
function openUnregisterModal() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;

    if (!studentIds.length) return showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
    if (sessionId === 'ALL') return showSweetAlert('Session Required', 'Please select a session.', 'warning', false);

    document.getElementById('snapshotStudentCount').textContent = `${studentIds.length} student${studentIds.length !== 1 ? 's' : ''}`;
    document.getElementById('snapshotSubjectCount').textContent = `${subjectClasses.length} subject${subjectClasses.length !== 1 ? 's' : ''}`;

    const nameInput = document.getElementById('snapshotNameInput');
    nameInput.value = '';
    nameInput.classList.remove('is-invalid');
    document.getElementById('snapshotNotesInput').value = '';
    document.getElementById('snapshotNotesCount').textContent = '0';

    const now = new Date();
    const dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    nameInput.value = `Unregistration — ${dateStr} ${timeStr}`;

    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const nameInput = document.getElementById('snapshotNameInput');
    const notesInput = document.getElementById('snapshotNotesInput');
    const name = nameInput.value.trim();

    if (!name) {
        nameInput.classList.add('is-invalid');
        return;
    }
    nameInput.classList.remove('is-invalid');

    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal'))?.hide();
    setSpinner(true);

    try {
        const res = await apiFetch(ROUTES.unregister, 'DELETE', {
            studentids: studentIds,
            subjectclasses: subjectClasses,
            sessionid: parseInt(sessionId),
            snapshot_name: name,
            snapshot_notes: notesInput.value.trim() || null,
        });

        if (res.success || res.success_count > 0) {
            showSweetAlert('Unregistration Complete', `${res.success_count} student(s) unregistered.<br><small class="text-muted">Snapshot saved as "<strong>${escapeHtml(name)}</strong>"</small>`, 'success', true);
            setTimeout(() => location.reload(), 2500);
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
// REGISTERED CLASSES MODAL - Card-based UI
// ============================================================================
function loadRegisteredClasses() {
    const modalContent = document.getElementById('registeredClassesContent');
    if (!modalContent) return;

    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (!classId || classId === 'ALL' || !sessionId || sessionId === 'ALL') {
        modalContent.innerHTML = '<p class="text-center text-muted">Please select a valid class and session.</p>';
        return;
    }

    modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div><p class="mt-3 text-muted">Loading registration data...</p></div>';

    axios.get('/subjects/registered-classes', {
        params: { class_id: classId, session_id: sessionId },
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(response => {
        if (response.data.success) {
            const classes = response.data.data;

            if (!classes || classes.length === 0) {
                modalContent.innerHTML = '<div class="text-center py-5"><i class="ri-information-line ri-3x text-muted"></i><p class="text-muted mt-3 mb-0">No registered classes found.</p></div>';
                return;
            }

            // Store for printing
            currentRegisteredClassesData = {
                data: classes,
                class_name: classes[0]?.class_name || 'N/A',
                arm_name: classes[0]?.arm_name || 'N/A',
                session_name: classes[0]?.session_name || 'N/A'
            };

            let html = '';
            classes.forEach((termGroup) => {
                const subjectsArray = termGroup.subjects ? termGroup.subjects.split(',').map(s => s.trim()) : [];
                const teachersArray = termGroup.teachers ? termGroup.teachers.split(',').map(t => t.trim()) : [];
                const totalStudents = termGroup.student_count || 0;
                const totalSubjects = termGroup.subject_count || subjectsArray.length;

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
                    let teacherName = '— Not assigned';
                    if (teachersArray.length > idx) {
                        teacherName = teachersArray[idx];
                    } else if (teachersArray.length > 0) {
                        teacherName = teachersArray[0];
                    }

                    html += `
                    <div class="subject-card p-3 d-flex gap-3 align-items-start" style="border-right:0.5px solid #e2e8f0; border-bottom:0.5px solid #e2e8f0; transition:all 0.2s ease;">
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

                html += `</div></div>`;
            });
            modalContent.innerHTML = html;
        } else {
            modalContent.innerHTML = '<div class="text-center py-5 text-danger">Failed to load registered classes.</div>';
        }
    }).catch(error => {
        console.error('Error:', error);
        modalContent.innerHTML = '<div class="text-center py-5 text-danger">Error loading registered classes. Please try again.</div>';
    });
}

function printRegisteredClasses() {
    if (!currentRegisteredClassesData || !currentRegisteredClassesData.data) {
        Swal.fire({ icon: 'warning', title: 'No Data', text: 'Please load registered classes first.' });
        return;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Registered Classes - ${currentRegisteredClassesData.class_name} ${currentRegisteredClassesData.arm_name}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                .school-name { font-size: 24px; font-weight: bold; color: #1e3a5f; }
                .title { font-size: 18px; font-weight: bold; margin: 20px 0; }
                .term-card { margin-bottom: 30px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
                .term-header { background: #f0f4f8; padding: 12px 15px; border-bottom: 2px solid #1e3a5f; }
                .term-title { font-size: 16px; font-weight: bold; margin: 0; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
                th { background: #e9ecef; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="school-name">School Name</div>
                <div class="title">Registered Classes Overview</div>
                <div>Class: ${escapeHtml(currentRegisteredClassesData.class_name)} ${escapeHtml(currentRegisteredClassesData.arm_name)}</div>
                <div>Session: ${escapeHtml(currentRegisteredClassesData.session_name)}</div>
                <div>Date: ${new Date().toLocaleDateString()}</div>
            </div>
    `);

    currentRegisteredClassesData.data.forEach(term => {
        const subjects = term.subjects ? term.subjects.split(',').map(s => s.trim()) : [];
        const teachers = term.teachers ? term.teachers.split(',').map(t => t.trim()) : [];

        printWindow.document.write(`
            <div class="term-card">
                <div class="term-header">
                    <div class="term-title">${escapeHtml(term.term_name)} Term</div>
                    <div>Students: ${term.student_count} | Subjects: ${term.subject_count}</div>
                </div>
                <table>
                    <thead><tr><th>#</th><th>Subject</th><th>Teacher</th><th>Students</th></tr></thead>
                    <tbody>
        `);

        subjects.forEach((subject, idx) => {
            let teacher = teachers[idx] || '— Not assigned';
            printWindow.document.write(`<tr><td>${idx + 1}</td><td>${escapeHtml(subject)}</td><td>${escapeHtml(teacher)}</td><td>${term.student_count}</td></tr>`);
        });

        printWindow.document.write(`</tbody></table></div>`);
    });

    printWindow.document.write(`<div class="footer">Generated on ${new Date().toLocaleString()}</div></body></html>`);
    printWindow.document.close();
    printWindow.print();
}

// ============================================================================
// OPEN ARCHIVED MODAL (Unregistered History)
// ============================================================================
function openArchivedModal() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({ icon: 'warning', title: 'Selection Required', text: 'Please select a class and session first.' });
        return;
    }

    new bootstrap.Modal(document.getElementById('archivedModal')).show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    archiveCurrentPage = page;
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId = document.getElementById('archiveTermFilter')?.value || '';
    const search = document.getElementById('archiveSearch')?.value.trim() || '';
    const perPage = document.getElementById('archivePerPage')?.value || 50;

    const spinner = document.getElementById('archiveSpinner');
    const container = document.getElementById('snapshotCardsContainer');

    spinner?.classList.remove('d-none');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading snapshots…</div>';

    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId, page, per_page: perPage });
        if (termId) params.set('term_id', termId);
        if (search) params.set('search', search);

        const res = await fetch(ROUTES.getArchived + '?' + params.toString(), { headers: { 'X-CSRF-TOKEN': CSRF } });
        const data = await res.json();

        if (!data.success) {
            container.innerHTML = `<div class="text-center text-danger py-4">${data.message}</div>`;
            return;
        }

        archiveMeta = data.meta;
        renderSnapshotCards(data.data);
        renderArchivePagination(data.meta);
        updateArchiveMeta(data.meta);
    } catch (err) {
        container.innerHTML = `<div class="text-center text-danger py-4">Error: ${err.message}</div>`;
    } finally {
        spinner?.classList.add('d-none');
    }
}

function renderSnapshotCards(rows) {
    const container = document.getElementById('snapshotCardsContainer');
    if (!rows.length) {
        container.innerHTML = '<div class="text-center text-muted py-5"><i class="ri-archive-line ri-3x d-block mb-2"></i>No unregistration snapshots found.</div>';
        return;
    }

    let html = '<div class="row g-3">';
    rows.forEach(row => {
        const unregDate = row.unregistered_at ? new Date(row.unregistered_at).toLocaleDateString('en-GB') : '—';
        html += `
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold"><i class="ri-camera-line text-danger me-1"></i>${escapeHtml(row.snapshot_name)}</h6>
                    <small class="text-muted">${unregDate}</small>
                    <p class="mt-2 mb-0"><strong>${row.student_count}</strong> students</p>
                    <p class="text-muted small mb-0">${escapeHtml(row.subjectname)}</p>
                </div>
                <div class="card-footer bg-light border-0">
                    <button class="btn btn-sm btn-outline-primary w-100" onclick="alert('View details coming soon')">View Details</button>
                </div>
            </div>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }

    let html = `<button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(${meta.current_page - 1})" ${meta.current_page === 1 ? 'disabled' : ''}>‹</button>`;
    for (let p = 1; p <= meta.last_page; p++) {
        if (p === 1 || p === meta.last_page || (p >= meta.current_page - 2 && p <= meta.current_page + 2)) {
            html += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}" onclick="loadArchivedPage(${p})">${p}</button>`;
        }
    }
    html += `<button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(${meta.current_page + 1})" ${meta.current_page === meta.last_page ? 'disabled' : ''}>›</button>`;
    container.innerHTML = html;
}

function updateArchiveMeta(meta) {
    const el = document.getElementById('archiveMeta');
    if (!meta || !meta.total) { el.textContent = ''; return; }
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to = Math.min(meta.current_page * meta.per_page, meta.total);
    el.textContent = `Showing ${from}–${to} of ${meta.total} snapshots`;
}

// Placeholder functions for snapshot detail
function restoreEntireSnapshot() { alert('Restore functionality coming soon'); }
function restoreDetailSelected() { alert('Restore selected coming soon'); }
function deleteDetailSelected() { alert('Delete selected coming soon'); }
function filterDetailRows(query) { }

// Event listeners
document.getElementById('archiveSearch')?.addEventListener('input', function() {
    clearTimeout(archiveSearchTimer);
    archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400);
});
document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));
document.getElementById('archivePerPage')?.addEventListener('change', () => loadArchivedPage(1));
document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => cb.checked = this.checked);
    toggleBatchButtons();
});
document.addEventListener('change', function(e) {
    if (e.target?.name === 'chk_child') toggleBatchButtons();
});
document.querySelectorAll('.subject-checkbox').forEach(cb => cb.addEventListener('change', function() {
    document.getElementById('subjectTeacherCount').textContent = document.querySelectorAll('.subject-checkbox:checked').length;
}));
</script>
@endsection
