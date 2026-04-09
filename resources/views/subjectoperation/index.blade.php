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
                                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->schoolclass }} {{ $class->schoolarm }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idsession">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->session }}
                                                </option>
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

                {{-- Subject Teachers Card (improved with per-term counts) --}}
                <div class="row" id="subjectTeachersCard">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-book-open-line me-2 text-primary"></i>
                                        Subject Teachers
                                        <span class="badge bg-primary-subtle text-primary ms-1" id="subjectTeacherCount">
                                            {{ $subjectTeachers ? $subjectTeachers->count() : 0 }}
                                        </span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects();">
                                        <i class="ri-checkbox-multiple-line me-1"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="deselectAllSubjects();">
                                        <i class="ri-checkbox-blank-line me-1"></i> Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($subjectTeachers && $subjectTeachers->isNotEmpty())
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="ri-information-line me-2 fs-5"></i>
                                        <span>Select the subjects you want to register or unregister students for.</span>
                                    </div>

                                    <div id="subjectTeachersContainer">
                                        @foreach ($schoolterms as $term)
                                            @php
                                                $termSubjects = $subjectTeachers->where('termid', $term->id);
                                                $termSubjectCount = $termSubjects->count();
                                            @endphp
                                            @if ($termSubjectCount > 0)
                                                <div class="term-group mb-4">
                                                    <div class="d-flex align-items-center gap-2 mb-3">
                                                        <div class="term-badge">
                                                            <span class="badge bg-primary px-3 py-2 fs-6">
                                                                <i class="ri-calendar-line me-1"></i>
                                                                {{ $term->term }}
                                                            </span>
                                                            <span class="badge bg-success-subtle text-success ms-2 px-3 py-2 fs-6">
                                                                <i class="ri-book-line me-1"></i>
                                                                {{ $termSubjectCount }} {{ Str::plural('Subject', $termSubjectCount) }}
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <hr class="my-0">
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        @foreach ($termSubjects as $teacher)
                                                            <div class="col-md-4 col-lg-3">
                                                                <div class="subject-check-card p-2 border rounded-3 d-flex align-items-center gap-2 {{ true ? 'border-primary-subtle bg-primary-subtle' : '' }}"
                                                                     id="card-{{ $teacher->subjectclassid }}"
                                                                     onclick="toggleSubjectCard('{{ $teacher->subjectclassid }}')">
                                                                    <input class="form-check-input subject-checkbox flex-shrink-0 mt-0"
                                                                        type="checkbox"
                                                                        id="subject-{{ $teacher->subjectclassid }}"
                                                                        data-subjectclassid="{{ $teacher->subjectclassid }}"
                                                                        data-staffid="{{ $teacher->userid }}"
                                                                        data-termid="{{ $teacher->termid }}"
                                                                        checked
                                                                        onclick="event.stopPropagation();">
                                                                    <label class="form-check-label w-100 cursor-pointer" for="subject-{{ $teacher->subjectclassid }}" onclick="event.preventDefault();">
                                                                        <div class="fw-medium small text-truncate">{{ $teacher->subjectname }}</div>
                                                                        <div class="text-muted" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                            <i class="ri-user-line me-1"></i>{{ $teacher->staffname }}
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="ri-book-open-line ri-3x mb-3 d-block opacity-50"></i>
                                        <p>Select a class and session to view subject teachers.</p>
                                    </div>
                                @endif
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
                                            <input type="text" class="form-control search" placeholder="Search students" value="{{ request('search') }}">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idgender">
                                            <option value="ALL">Select Gender</option>
                                            <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Female</option>
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
                                        <i class="ri-team-line me-2 text-primary"></i>
                                        Students
                                        <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $students ? $students->total() : 0 }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0 d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary d-none" id="register-selected-btn" onclick="registerSelectedStudentsBatch();">
                                        <i class="ri-user-add-line me-1"></i> Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn" onclick="openUnregisterModal();">
                                        <i class="ri-user-unfollow-line me-1"></i> Unregister Selected
                                    </button>
                                    <div class="spinner-border text-primary d-none" id="register-loading-spinner" role="status"></div>
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
                                    <table class="table table-centered align-middle table-nowrap mb-0">
                                        <thead class="table-active">
                                            <tr>
                                                <th><input class="form-check-input" type="checkbox" id="checkAll"></th>
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
                                    <div class="d-flex justify-content-end mt-3">
                                        {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- MODAL: Registered Classes Overview (Redesigned) --}}
                {{-- ================================================================ --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                            {{-- Gradient Header --}}
                            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); padding: 1.5rem 1.5rem 0;">
                                <div class="w-100">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h4 class="text-white fw-bold mb-1">
                                                <i class="ri-graduation-cap-line me-2"></i>
                                                Registered Classes Overview
                                            </h4>
                                            <p class="text-white-50 mb-0 small" id="modalSubtitle">Select class &amp; session to load data</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-light" onclick="printRegisteredClasses()" id="printBtn" style="display:none!important;">
                                                <i class="ri-printer-line me-1"></i> Print
                                            </button>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                    </div>

                                    {{-- Summary Stats Bar (populated dynamically) --}}
                                    <div id="modalSummaryBar" class="d-none">
                                        <div class="row g-3 pb-3">
                                            <div class="col-6 col-md-3">
                                                <div class="text-center p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                                                    <div class="text-white fw-bold fs-4" id="statTotalTerms">—</div>
                                                    <div class="text-white-50 small">Terms</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="text-center p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                                                    <div class="text-white fw-bold fs-4" id="statTotalSubjects">—</div>
                                                    <div class="text-white-50 small">Total Subjects</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="text-center p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                                                    <div class="text-white fw-bold fs-4" id="statTotalStudents">—</div>
                                                    <div class="text-white-50 small">Students</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="text-center p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                                                    <div class="text-white fw-bold fs-4" id="statTotalTeachers">—</div>
                                                    <div class="text-white-50 small">Teachers</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tab Nav (terms will be populated dynamically) --}}
                                    <div id="termTabsNav" class="d-none">
                                        <ul class="nav nav-tabs border-0" id="termTabs" role="tablist" style="gap: 4px;">
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Body --}}
                            <div class="modal-body p-0" style="background: #f4f6fb; max-height: 65vh; overflow-y: auto;">
                                <div id="registeredClassesContent" class="p-3">
                                    <div class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="ri-graduation-cap-line" style="font-size: 48px; color: #c5cae9;"></i>
                                        </div>
                                        <p class="text-muted">Select a class and session first, then open this modal.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="modal-footer border-0 bg-white justify-content-between">
                                <small class="text-muted" id="modalGeneratedAt"></small>
                                <div class="d-flex gap-2">
                                    <button type="button" id="printBtnFooter" class="btn btn-primary d-none" onclick="printRegisteredClasses()">
                                        <i class="ri-printer-line me-1"></i> Print / Export PDF
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Snapshot Name for Unregistration --}}
                <div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);">
                                <h5 class="modal-title text-white fw-bold">
                                    <i class="ri-save-line me-2"></i>Name this Unregistration
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Snapshot Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="snapshotNameInput" placeholder="e.g., Term 2 Corrections - June 2025">
                                    <div class="invalid-feedback">Please enter a snapshot name.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Notes <span class="text-muted small">(optional)</span></label>
                                    <textarea class="form-control" id="snapshotNotesInput" rows="3" placeholder="Reason for unregistration..."></textarea>
                                </div>
                                <div class="alert alert-warning d-flex align-items-center gap-2">
                                    <i class="ri-error-warning-line fs-5"></i>
                                    <span>All scores will be saved and can be restored later.</span>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" onclick="proceedUnregister();">
                                    <i class="ri-user-unfollow-line me-1"></i> Unregister & Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Unregistered History --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="modal-title text-white fw-bold">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="snapshotCardsContainer">Loading...</div>
                                <div id="archivePagination" class="d-flex justify-content-center mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Snapshot Detail --}}
                <div class="modal fade" id="snapshotDetailModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <h5 class="modal-title text-white fw-bold" id="snapshotDetailTitle">
                                    <i class="ri-camera-line me-2"></i>Snapshot Detail
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="snapshotDetailBody">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- STYLES --}}
{{-- ============================================================ --}}
<style>
@media print {
    body * { visibility: hidden; }
    #printableArea, #printableArea * { visibility: visible; }
    #printableArea { position: absolute; top: 0; left: 0; width: 100%; padding: 20px; }
    .no-print { display: none !important; }
}

/* Subject check cards */
.subject-check-card {
    cursor: pointer;
    transition: all 0.15s ease;
    background: #fff;
    border-color: #dee2e6 !important;
    min-height: 58px;
}
.subject-check-card:hover {
    border-color: #0d6efd !important;
    background: #f0f6ff !important;
}
.subject-check-card.selected {
    background: #e8f0fe !important;
    border-color: #0d6efd !important;
}

/* Teacher avatar */
.teacher-avatar-sm {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    flex-shrink: 0;
}

/* Subject table rows */
.subject-row:hover { background: #f8faff; }

/* Term tab styling */
#termTabs .nav-link {
    color: rgba(255,255,255,0.65);
    border: 1px solid rgba(255,255,255,0.2);
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 6px 16px;
    font-size: 13px;
    background: rgba(255,255,255,0.08);
}
#termTabs .nav-link.active {
    color: #1a1a2e;
    background: #f4f6fb;
    border-color: rgba(255,255,255,0.3);
    font-weight: 600;
}
#termTabs .nav-link:hover:not(.active) {
    color: #fff;
    background: rgba(255,255,255,0.15);
}

/* Subject number badge */
.subject-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}

/* Stats summary cards */
.stat-mini-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 16px;
    text-align: center;
}

/* Modal loading shimmer */
.shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 6px;
    height: 20px;
    margin: 6px 0;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* cursor pointer */
.cursor-pointer { cursor: pointer; }
</style>

{{-- ============================================================ --}}
{{-- SCRIPTS --}}
{{-- ============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// CONFIGURATION
// ============================================================================
const ROUTES = {
    batchRegister:   '{{ route("subjectregistration.batch") }}',
    unregister:      '{{ route("subjects.destroy") }}',
    getRegistered:   '{{ route("subjects.registered-classes") }}',
    getArchived:     '{{ route("subjectoperation.archived") }}',
    getSnapshot:     '{{ route("subjectoperation.snapshot.detail") }}',
    restore:         '{{ route("subjectoperation.restore") }}',
    permanentDelete: '{{ route("subjectoperation.archive.batch-delete") }}',
    index:           '{{ route("subjects.index") }}',
    getSchoolInfo:   '{{ route("school.information.get") }}',
};
const CSRF = '{{ csrf_token() }}';
const AVATAR_URL = '{{ asset("storage") }}';

// ============================================================================
// UTILITY
// ============================================================================
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function showSweetAlert(title, message, type) {
    Swal.fire({ title, html: message, icon: type, confirmButtonColor: type === 'success' ? '#28a745' : '#dc3545' });
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

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = true;
        cb.closest('.subject-check-card').classList.add('selected');
    });
    updateSubjectCount();
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.subject-check-card').classList.remove('selected');
    });
    updateSubjectCount();
}

function toggleSubjectCard(subjectclassid) {
    const cb   = document.getElementById('subject-' + subjectclassid);
    const card = document.getElementById('card-' + subjectclassid);
    if (!cb) return;
    cb.checked = !cb.checked;
    card.classList.toggle('selected', cb.checked);
    updateSubjectCount();
}

function updateSubjectCount() {
    const count = document.querySelectorAll('.subject-checkbox:checked').length;
    document.getElementById('subjectTeacherCount').textContent = count;
}

// Initialize card states & counts on load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        const card = cb.closest('.subject-check-card');
        if (cb.checked && card) card.classList.add('selected');
        cb.addEventListener('change', updateSubjectCount);
    });
    updateSubjectCount();
});

// ============================================================================
// REGISTERED CLASSES MODAL — full redesign
// ============================================================================
let _regData = null; // cache loaded data

async function loadRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const content   = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        content.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-filter-line" style="font-size:48px;color:#c5cae9;"></i>
                <p class="text-muted mt-3">Please select a class and session to view registered subjects.</p>
            </div>`;
        return;
    }

    // Shimmer loading
    content.innerHTML = `<div class="p-4">${[...Array(4)].map(() => `
        <div class="mb-3">
            <div class="shimmer" style="width:40%;height:16px;"></div>
            <div class="shimmer mt-2" style="width:100%;height:40px;"></div>
            <div class="shimmer mt-1" style="width:100%;height:40px;"></div>
        </div>`).join('')}</div>`;

    try {
        const res  = await fetch(`${ROUTES.getRegistered}?class_id=${classId}&session_id=${sessionId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            content.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-inbox-unarchive-line" style="font-size:48px;color:#c5cae9;"></i>
                    <p class="text-muted mt-3">No registered classes found for the selected class and session.</p>
                </div>`;
            return;
        }

        _regData = data.data;
        renderRegisteredModal(data.data);

    } catch (err) {
        content.innerHTML = `<div class="alert alert-danger m-3"><i class="ri-error-warning-line me-2"></i>${err.message}</div>`;
    }
}

function renderRegisteredModal(dataItems) {
    // Compute summary stats
    let totalSubjects  = 0;
    let totalStudents  = 0;
    const teacherSet   = new Set();

    dataItems.forEach(item => {
        totalSubjects += (item.subjects_teachers || []).length;
        totalStudents  = Math.max(totalStudents, item.student_count || 0);
        (item.subjects_teachers || []).forEach(s => (s.teachers || []).forEach(t => teacherSet.add(t.id)));
    });

    // Class/session info from first item
    const first       = dataItems[0];
    const className   = `${escapeHtml(first.class_name)} ${escapeHtml(first.arm_name)}`;
    const sessionName = escapeHtml(first.session_name);

    // Update header subtitle
    document.getElementById('modalSubtitle').textContent = `${className} — ${sessionName}`;
    document.getElementById('statTotalTerms').textContent    = dataItems.length;
    document.getElementById('statTotalSubjects').textContent = totalSubjects;
    document.getElementById('statTotalStudents').textContent = totalStudents;
    document.getElementById('statTotalTeachers').textContent = teacherSet.size;
    document.getElementById('modalSummaryBar').classList.remove('d-none');
    document.getElementById('modalGeneratedAt').textContent  = `Generated: ${new Date().toLocaleString()}`;
    document.getElementById('printBtnFooter').classList.remove('d-none');

    // Build tabs
    const tabsNav = document.getElementById('termTabsNav');
    tabsNav.classList.remove('d-none');
    const tabsList = document.getElementById('termTabs');
    tabsList.innerHTML = '';

    dataItems.forEach((item, idx) => {
        const subjectCount = (item.subjects_teachers || []).length;
        const li = document.createElement('li');
        li.className = 'nav-item';
        li.innerHTML = `
            <button class="nav-link ${idx === 0 ? 'active' : ''}"
                    id="tab-${idx}" data-bs-toggle="tab" data-bs-target="#tabPane-${idx}"
                    type="button" role="tab">
                <i class="ri-calendar-2-line me-1"></i>
                ${escapeHtml(item.term_name)}
                <span class="badge bg-light text-dark ms-1">${subjectCount}</span>
            </button>`;
        tabsList.appendChild(li);
    });

    // Build tab content
    let contentHtml = '<div class="tab-content" id="termTabContent">';
    dataItems.forEach((item, idx) => {
        contentHtml += buildTermPane(item, idx);
    });
    contentHtml += '</div>';

    document.getElementById('registeredClassesContent').innerHTML = contentHtml;
}

function buildTermPane(item, idx) {
    const subjects = item.subjects_teachers || [];

    // Summary stats for this term
    const teacherIds = new Set();
    subjects.forEach(s => (s.teachers || []).forEach(t => teacherIds.add(t.id)));

    let rows = '';
    subjects.forEach((subject, sIdx) => {
        const teacherHtml = buildTeacherHtml(subject.teachers || []);
        rows += `
            <tr class="subject-row">
                <td class="text-center" style="width:50px;">
                    <span class="subject-num">${sIdx + 1}</span>
                </td>
                <td>
                    <div class="fw-medium">
                        <i class="ri-book-2-line text-primary me-2" style="font-size:14px;"></i>
                        ${escapeHtml(subject.name)}
                    </div>
                    ${subject.code ? `<small class="text-muted ms-4">${escapeHtml(subject.code)}</small>` : ''}
                </td>
                <td style="width:240px;">${teacherHtml}</td>
                <td class="text-center" style="width:90px;">
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        ${subject.student_count || 0}
                    </span>
                </td>
            </tr>`;
    });

    return `
        <div class="tab-pane fade ${idx === 0 ? 'show active' : ''}" id="tabPane-${idx}" role="tabpanel">
            <div class="p-3">
                {{-- Term summary mini-cards --}}
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-mini-card">
                            <div class="text-muted small mb-1"><i class="ri-book-open-line me-1"></i>Subjects</div>
                            <div class="fw-bold fs-5 text-primary">${subjects.length}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-mini-card">
                            <div class="text-muted small mb-1"><i class="ri-team-line me-1"></i>Students</div>
                            <div class="fw-bold fs-5 text-success">${item.student_count || 0}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-mini-card">
                            <div class="text-muted small mb-1"><i class="ri-user-star-line me-1"></i>Teachers</div>
                            <div class="fw-bold fs-5 text-info">${teacherIds.size}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-mini-card">
                            <div class="text-muted small mb-1"><i class="ri-calendar-line me-1"></i>Term</div>
                            <div class="fw-bold small text-warning">${escapeHtml(item.term_name)}</div>
                        </div>
                    </div>
                </div>

                {{-- Subjects table --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2">
                        <span class="fw-medium text-muted small">
                            <i class="ri-list-ordered me-1"></i>
                            ${subjects.length} subjects listed alphabetically
                        </span>
                        <span class="badge bg-success-subtle text-success">
                            <i class="ri-sort-asc me-1"></i>A–Z
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th>Subject</th>
                                    <th style="width:240px;">Teacher(s)</th>
                                    <th class="text-center" style="width:90px;">Students</th>
                                </tr>
                            </thead>
                            <tbody>${rows || '<tr><td colspan="4" class="text-center text-muted py-4">No subjects found.</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>`;
}

function buildTeacherHtml(teachers) {
    if (!teachers || !teachers.length) {
        return `<span class="text-muted small"><i class="ri-user-unfollow-line me-1"></i>Not assigned</span>`;
    }
    return teachers.map(t => {
        const pic = t.picture
            ? `${AVATAR_URL}/staff_avatars/${t.picture.split('/').pop()}`
            : `${AVATAR_URL}/staff_avatars/default.png`;
        return `
            <div class="d-flex align-items-center gap-2 mb-1">
                <img src="${pic}" class="teacher-avatar-sm"
                     onerror="this.src='${AVATAR_URL}/staff_avatars/default.png'">
                <span class="fw-medium" style="font-size:12px;">${escapeHtml(t.name)}</span>
            </div>`;
    }).join('');
}

// ============================================================================
// PRINT — Professional PDF layout
// ============================================================================
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
            fetch(`${ROUTES.getRegistered}?class_id=${classId}&session_id=${sessionId}`, {
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
        ]);

        const schoolData = await schoolRes.json();
        const regData    = await regRes.json();
        Swal.close();

        if (!regData.success || !regData.data.length) {
            Swal.fire('No Data', 'No registered classes found.', 'info');
            return;
        }

        const printWindow = window.open('', '_blank');
        printWindow.document.write(buildPrintHTML(schoolData, regData.data));
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => printWindow.print(), 800);

    } catch (err) {
        Swal.close();
        Swal.fire('Error', err.message, 'error');
    }
}

function buildPrintHTML(schoolData, items) {
    const classSelect   = document.getElementById('idclass');
    const sessionSelect = document.getElementById('idsession');
    const className     = classSelect.options[classSelect.selectedIndex]?.text || '';
    const sessionName   = sessionSelect.options[sessionSelect.selectedIndex]?.text || '';
    const school        = schoolData.success ? schoolData.data : {};
    const schoolName    = school.school_name    || 'School Name';
    const schoolAddress = school.school_address || '';
    const schoolPhone   = school.school_phone   || '';
    const schoolEmail   = school.school_email   || '';
    const schoolMotto   = school.school_motto   || '';
    const schoolLogo    = school.school_logo
        ? (school.school_logo.startsWith('http') ? school.school_logo : `{{ asset('storage') }}/${school.school_logo}`)
        : '';

    // Grand totals
    let grandSubjects = 0;
    items.forEach(item => { grandSubjects += (item.subjects_teachers || []).length; });

    const termsHtml = items.map(item => {
        const subjects    = item.subjects_teachers || [];
        const teacherSet  = new Set();
        subjects.forEach(s => (s.teachers || []).forEach(t => teacherSet.add(t.id)));

        const rows = subjects.map((s, idx) => {
            const teacherNames = (s.teachers || []).map(t => t.name).join(', ') || '—';
            return `
                <tr>
                    <td style="text-align:center;width:40px;color:#764ba2;font-weight:700;">${idx + 1}</td>
                    <td>
                        <strong>${escapeHtml(s.name)}</strong>
                        ${s.code ? `<br><span style="color:#888;font-size:11px;">${escapeHtml(s.code)}</span>` : ''}
                    </td>
                    <td>${escapeHtml(teacherNames)}</td>
                    <td style="text-align:center;">${s.student_count || 0}</td>
                </tr>`;
        }).join('');

        return `
            <div class="term-section">
                <div class="term-header">
                    <span>${escapeHtml(item.term_name)}</span>
                    <span class="term-meta">
                        ${subjects.length} Subjects &nbsp;|&nbsp;
                        ${item.student_count || 0} Students &nbsp;|&nbsp;
                        ${teacherSet.size} Teachers
                    </span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;">#</th>
                            <th>Subject Name</th>
                            <th>Teacher(s)</th>
                            <th style="width:80px;text-align:center;">Students</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr style="background:#f8f9fa;">
                            <td colspan="2" style="text-align:right;padding:6px 8px;font-weight:600;font-size:12px;">
                                Total for ${escapeHtml(item.term_name)}:
                            </td>
                            <td style="padding:6px 8px;font-size:12px;color:#555;">${teacherSet.size} teacher(s)</td>
                            <td style="text-align:center;padding:6px 8px;font-weight:700;color:#1a1a2e;">${item.student_count || 0}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
    }).join('');

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subject Registration — ${escapeHtml(schoolName)}</title>
<style>
  @page { size: A4; margin: 18mm 15mm; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

  /* School Header */
  .school-header { display: flex; align-items: center; gap: 16px; padding-bottom: 14px; border-bottom: 3px solid #667eea; margin-bottom: 20px; }
  .school-logo { width: 75px; height: 75px; object-fit: contain; }
  .school-info { flex: 1; }
  .school-name { font-size: 20px; font-weight: 700; color: #1a1a2e; letter-spacing: 0.3px; }
  .school-motto { font-size: 11px; color: #764ba2; font-style: italic; margin: 2px 0 4px; }
  .school-contact { font-size: 11px; color: #555; }
  .school-contact span { margin-right: 12px; }

  /* Document Meta */
  .doc-meta { background: linear-gradient(135deg, #f0f4ff 0%, #faf0ff 100%); border: 1px solid #e0e0ef; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
  .doc-meta-title { font-size: 15px; font-weight: 700; color: #1a1a2e; }
  .doc-meta-detail { font-size: 11px; color: #555; margin-top: 2px; }
  .doc-meta-right { text-align: right; font-size: 11px; color: #888; }

  /* Grand Summary */
  .grand-summary { display: flex; gap: 12px; margin-bottom: 20px; }
  .summary-box { flex: 1; border: 1px solid #e0e0ef; border-radius: 8px; padding: 8px 12px; text-align: center; }
  .summary-box .val { font-size: 20px; font-weight: 700; color: #667eea; }
  .summary-box .lbl { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

  /* Term Sections */
  .term-section { margin-bottom: 24px; page-break-inside: avoid; }
  .term-header { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); color: #fff; padding: 8px 14px; border-radius: 6px 6px 0 0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; }
  .term-meta { font-size: 11px; font-weight: 400; opacity: 0.8; }

  /* Table */
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  thead tr { background: #f4f6fb; }
  th { padding: 8px 10px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6; }
  td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
  tr:last-child td { border-bottom: none; }
  tbody tr:nth-child(even) { background: #fafafe; }
  tbody tr:hover { background: #f0f4ff; }

  /* Footer */
  .print-footer { margin-top: 28px; padding-top: 12px; border-top: 2px solid #667eea; display: flex; justify-content: space-between; font-size: 10px; color: #999; }

  @media print {
    .term-section { page-break-inside: avoid; }
    .term-section:nth-child(n+2) { page-break-before: auto; }
  }
</style>
</head>
<body>

<div class="school-header">
    ${schoolLogo ? `<img src="${schoolLogo}" class="school-logo" onerror="this.style.display='none'">` : ''}
    <div class="school-info">
        <div class="school-name">${escapeHtml(schoolName)}</div>
        ${schoolMotto ? `<div class="school-motto">${escapeHtml(schoolMotto)}</div>` : ''}
        <div class="school-contact">
            ${schoolAddress ? `<span>&#9679; ${escapeHtml(schoolAddress)}</span>` : ''}
            ${schoolPhone   ? `<span>&#9742; ${escapeHtml(schoolPhone)}</span>` : ''}
            ${schoolEmail   ? `<span>&#9993; ${escapeHtml(schoolEmail)}</span>` : ''}
        </div>
    </div>
</div>

<div class="doc-meta">
    <div>
        <div class="doc-meta-title">Subject Registration Report</div>
        <div class="doc-meta-detail">
            Class: <strong>${escapeHtml(className)}</strong> &nbsp;&nbsp;|&nbsp;&nbsp;
            Session: <strong>${escapeHtml(sessionName)}</strong>
        </div>
    </div>
    <div class="doc-meta-right">
        Print Date:<br><strong>${new Date().toLocaleString()}</strong>
    </div>
</div>

<div class="grand-summary">
    <div class="summary-box">
        <div class="val">${items.length}</div>
        <div class="lbl">Terms</div>
    </div>
    <div class="summary-box">
        <div class="val">${grandSubjects}</div>
        <div class="lbl">Total Subjects</div>
    </div>
    <div class="summary-box">
        <div class="val">${items[0]?.student_count || 0}</div>
        <div class="lbl">Students</div>
    </div>
</div>

${termsHtml}

<div class="print-footer">
    <span>Generated by School Management System</span>
    <span>${escapeHtml(schoolName)} &nbsp;&bull;&nbsp; ${new Date().toLocaleDateString()}</span>
</div>

</body>
</html>`;
}

// ============================================================================
// REGISTRATION FUNCTIONS
// ============================================================================
function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id));
}

function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid: parseInt(cb.dataset.subjectclassid),
        staffid:        parseInt(cb.dataset.staffid),
        termid:         parseInt(cb.dataset.termid),
    }));
}

async function registerSelectedStudentsBatch() {
    const studentIds    = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId     = document.getElementById('idsession').value;

    if (!studentIds.length)    return showSweetAlert('Error', 'No students selected', 'warning');
    if (!subjectClasses.length) return showSweetAlert('Error', 'No subjects selected', 'warning');
    if (sessionId === 'ALL')    return showSweetAlert('Error', 'Please select a session', 'warning');

    const result = await Swal.fire({
        title: 'Confirm Registration',
        html: `Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?`,
        icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745',
    });
    if (!result.isConfirmed) return;

    document.getElementById('register-loading-spinner').classList.remove('d-none');

    try {
        const res  = await fetch(ROUTES.batchRegister, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) })
        });
        const data = await res.json();
        if (data.success) {
            showSweetAlert('Success', 'Students registered successfully!', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showSweetAlert('Error', data.message || 'Registration failed', 'error');
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error');
    } finally {
        document.getElementById('register-loading-spinner').classList.add('d-none');
    }
}

function openUnregisterModal() {
    const studentIds    = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    if (!studentIds.length)    return showSweetAlert('Error', 'No students selected', 'warning');
    if (!subjectClasses.length) return showSweetAlert('Error', 'No subjects selected', 'warning');

    document.getElementById('snapshotNameInput').value  = `Unregistration — ${new Date().toLocaleString()}`;
    document.getElementById('snapshotNotesInput').value = '';
    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const name = document.getElementById('snapshotNameInput').value.trim();
    if (!name) return showSweetAlert('Error', 'Please enter a snapshot name', 'warning');

    const studentIds    = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId     = document.getElementById('idsession').value;
    const notes         = document.getElementById('snapshotNotesInput').value;

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal')).hide();

    try {
        const res  = await fetch(ROUTES.unregister, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId), snapshot_name: name, snapshot_notes: notes })
        });
        const data = await res.json();
        if (data.success) {
            showSweetAlert('Success', `${data.success_count} student(s) unregistered`, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showSweetAlert('Error', data.message || 'Unregistration failed', 'error');
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error');
    }
}

// ============================================================================
// ARCHIVE FUNCTIONS
// ============================================================================
function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') {
        return showSweetAlert('Error', 'Please select a class and session first', 'warning');
    }
    loadArchivedPage(1);
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
}

async function loadArchivedPage(page) {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('snapshotCardsContainer');

    container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

    try {
        const res  = await fetch(`${ROUTES.getArchived}?class_id=${classId}&session_id=${sessionId}&page=${page}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = '<div class="alert alert-info m-3"><i class="ri-inbox-line me-2"></i>No archived records found.</div>';
            return;
        }

        let html = '<div class="row g-3 p-2">';
        data.data.forEach(snapshot => {
            html += `
                <div class="col-md-6">
                    <div class="card border h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><i class="ri-camera-line text-primary me-2"></i>${escapeHtml(snapshot.snapshot_name)}</h6>
                                <small class="text-muted">${new Date(snapshot.unregistered_at).toLocaleDateString()}</small>
                            </div>
                            <p class="text-muted small mb-1"><i class="ri-book-line me-1"></i><strong>Subject:</strong> ${escapeHtml(snapshot.subjectname)}</p>
                            <p class="text-muted small mb-2"><i class="ri-team-line me-1"></i><strong>Students:</strong> ${snapshot.student_count}</p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewSnapshotDetail(${snapshot.archive_id})">
                                    <i class="ri-eye-line me-1"></i>View
                                </button>
                                <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="restoreSnapshot(${snapshot.archive_id})">
                                    <i class="ri-history-line me-1"></i>Restore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger m-3">${err.message}</div>`;
    }
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => cb.checked = this.checked);
    // Show/hide batch buttons
    const anyChecked = this.checked;
    document.getElementById('register-selected-btn').classList.toggle('d-none', !anyChecked);
    document.getElementById('unregister-selected-btn').classList.toggle('d-none', !anyChecked);
});

document.getElementById('studentTableBody')?.addEventListener('change', function(e) {
    if (!e.target.matches('input[name="chk_child"]')) return;
    const anyChecked = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length > 0;
    document.getElementById('register-selected-btn').classList.toggle('d-none', !anyChecked);
    document.getElementById('unregister-selected-btn').classList.toggle('d-none', !anyChecked);
});

document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
</script>
@endsection
