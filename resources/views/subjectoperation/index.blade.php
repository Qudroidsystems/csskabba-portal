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

                {{-- ── CLASS & SESSION FILTER ─────────────────────────────────── --}}
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

                {{-- ── SUBJECT TEACHERS CARD ──────────────────────────────────── --}}
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

                {{-- ── STUDENT FILTERS ────────────────────────────────────────── --}}
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

                {{-- ── STUDENTS TABLE ─────────────────────────────────────────── --}}
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

                {{-- ════════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Registered Classes Overview                          --}}
                {{-- ════════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">

                            {{-- Header --}}
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

                            {{-- Term tabs (populated dynamically) --}}
                            <div id="regModalTermTabsWrap" class="border-bottom px-4 pt-2" style="background:#f8faff;display:none;">
                                <ul class="nav nav-tabs border-0" id="regModalTermTabs" role="tablist"></ul>
                            </div>

                            {{-- Body --}}
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

                {{-- ════════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Snapshot Name for Unregistration                     --}}
                {{-- ════════════════════════════════════════════════════════════ --}}
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

                {{-- ════════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Unregistered History                                  --}}
                {{-- ════════════════════════════════════════════════════════════ --}}
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

                {{-- ════════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Snapshot Detail                                       --}}
                {{-- ════════════════════════════════════════════════════════════ --}}
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

            </div>{{-- #subjectList --}}
        </div>
    </div>
</div>

<style>
/* ── Print ──────────────────────────────────────────── */
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

/* ── Registered modal subject rows ─────────────────── */
.subject-row:nth-child(even) { background: #fafbff; }
.subject-row:hover { background: #eff6ff !important; }

/* ── Stats strip ────────────────────────────────────── */
.stats-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

/* ── Term section heading ───────────────────────────── */
.term-section-header {
    background: linear-gradient(90deg, rgba(102,126,234,0.12) 0%, rgba(118,75,162,0.05) 100%);
    border-left: 4px solid #667eea;
    border-radius: 0 8px 8px 0;
    padding: 10px 16px;
    margin-bottom: 12px;
}

/* ── Teacher chip ───────────────────────────────────── */
.teacher-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 20px;
    padding: 3px 10px 3px 5px;
    font-size: 12px;
}
.teacher-chip img {
    width: 22px; height: 22px;
    border-radius: 50%;
    object-fit: cover;
}

/* ── Number badge ───────────────────────────────────── */
.subject-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg,#667eea,#764ba2);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
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

// ════════════════════════════════════════════════════════════════════════════
// UTILITY
// ════════════════════════════════════════════════════════════════════════════
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

function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    cb.checked = !cb.checked;
    card.classList.toggle('is-checked', cb.checked);
    updateSubjectCount();
}

function updateSubjectCount() {
    const total = document.querySelectorAll('.subject-checkbox:checked').length;
    document.getElementById('subjectTeacherCount') && (document.getElementById('subjectTeacherCount').textContent = total);
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = true;
        cb.closest('.subject-check-card')?.classList.add('is-checked');
    });
    updateSubjectCount();
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.subject-check-card')?.classList.remove('is-checked');
    });
    updateSubjectCount();
}

// Mark already-checked cards on load
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

    // Sort subjects alphabetically by name
    const sortedSubjects = [...subjects].sort((a, b) =>
        (a.name || '').localeCompare(b.name || '')
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
        html += `<div class="text-center text-muted py-4"><i class="ri-book-2-line ri-2x d-block mb-2"></i>No subjects found for this term.</div>`;
    } else {
        html += `
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead style="background:#f0f4ff;">
                        <tr>
                            <th width="60" class="text-center ps-3">#</th>
                            <th>Subject Name</th>
                            <th width="120">Code</th>
                            <th>Teacher(s)</th>
                            <th width="100" class="text-center">Students</th>
                        </tr>
                    </thead>
                    <tbody>`;

        sortedSubjects.forEach((subject, index) => {
            const sc = subject.student_count ?? 0;
            let teachersHtml = '';

            if (subject.teachers && subject.teachers.length > 0) {
                teachersHtml = '<div class="d-flex flex-wrap gap-1">';
                subject.teachers.forEach(t => {
                    const pic = t.picture
                        ? `${ASSET_URL}/staff_avatars/${t.picture.split('/').pop()}`
                        : `${ASSET_URL}/staff_avatars/default.png`;
                    teachersHtml += `
                        <span class="teacher-chip">
                            <img src="${pic}" onerror="this.src='${ASSET_URL}/staff_avatars/default.png'">
                            <span>${esc(t.name)}</span>
                        </span>`;
                });
                teachersHtml += '</div>';
            } else {
                teachersHtml = '<span class="text-muted small"><i class="ri-user-unfollow-line me-1"></i>Not assigned</span>';
            }

            html += `
                <tr class="subject-row">
                    <td class="text-center ps-3">
                        <span class="subject-num">${index + 1}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-book-2-line text-primary"></i>
                            <span class="fw-semibold">${esc(subject.name)}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill px-3" style="background:#e0e7ff;color:#4338ca;font-size:11px;">
                            ${esc(subject.code || '—')}
                        </span>
                    </td>
                    <td>${teachersHtml}</td>
                    <td class="text-center">
                        <span class="badge rounded-pill px-3 py-2" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:12px;">
                            ${sc}
                        </span>
                    </td>
                </tr>`;
        });

        html += `
                    </tbody>
                </table>
            </div>`;
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
// PRINT
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
        totalSubjects += subjects.length;

        let rows = '';
        subjects.forEach((s, i) => {
            const teachers = (s.teachers || []).map(t => t.name).join(', ') || '—';
            rows += `
                <tr>
                    <td class="center">${i + 1}</td>
                    <td><strong>${esc(s.name)}</strong></td>
                    <td>${esc(s.code || '—')}</td>
                    <td>${esc(teachers)}</td>
                    <td class="center">${s.student_count ?? 0}</td>
                </tr>`;
        });

        termsHtml += `
            <div class="term-block">
                <div class="term-header">
                    <span class="term-title">${esc(termData.class_name)} ${esc(termData.arm_name)}</span>
                    <span class="term-meta">${esc(termData.session_name)} &nbsp;|&nbsp; ${esc(termData.term_name)}</span>
                    <div style="margin-top:4px;">
                        <span class="badge-pill">&#128100; ${termData.student_count ?? 0} Students</span>
                        <span class="badge-pill">&#128218; ${subjects.length} Subjects</span>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="center" style="width:44px;">S/N</th>
                            <th>Subject Name</th>
                            <th style="width:100px;">Code</th>
                            <th>Teacher(s)</th>
                            <th class="center" style="width:80px;">Students</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="font-weight:600;padding:6px 10px;background:#f0f4ff;">
                                Total: ${subjects.length} subject${subjects.length !== 1 ? 's' : ''}
                            </td>
                            <td colspan="3" style="text-align:right;font-weight:600;padding:6px 10px;background:#f0f4ff;">
                                ${termData.student_count ?? 0} student${(termData.student_count ?? 0) !== 1 ? 's' : ''} registered
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
    });

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subject Registration — ${esc(schoolName)}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #1a1a2e; background: #fff; padding: 10mm; }

  /* ── School Header ── */
  .school-header { display: flex; align-items: center; gap: 18px; border-bottom: 3px solid #2563eb; padding-bottom: 14px; margin-bottom: 18px; }
  .school-logo { width: 72px; height: 72px; object-fit: contain; flex-shrink: 0; }
  .school-logo-placeholder { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; font-weight: 700; flex-shrink: 0; }
  .school-info { flex: 1; }
  .school-name { font-size: 18pt; font-weight: 700; color: #1e3a5f; line-height: 1.2; }
  .school-motto { font-style: italic; color: #555; font-size: 10pt; margin-top: 2px; }
  .school-contact { font-size: 9.5pt; color: #666; margin-top: 4px; }

  /* ── Document Title ── */
  .doc-title { text-align: center; background: linear-gradient(135deg,#1e3a5f,#2563eb); color: white; padding: 10px 20px; border-radius: 6px; margin-bottom: 14px; }
  .doc-title h2 { font-size: 14pt; font-weight: 700; letter-spacing: 0.5px; }
  .doc-title p { font-size: 10pt; opacity: 0.85; margin-top: 2px; }

  /* ── Meta strip ── */
  .meta-strip { display: flex; gap: 16px; background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 6px; padding: 8px 14px; margin-bottom: 18px; font-size: 10pt; }
  .meta-item strong { color: #1e3a5f; }

  /* ── Term block ── */
  .term-block { margin-bottom: 22px; page-break-inside: avoid; border: 1px solid #e0e7ff; border-radius: 8px; overflow: hidden; }
  .term-header { background: linear-gradient(135deg,#1e3a5f 0%,#2563eb 70%,#7c3aed 100%); color: white; padding: 10px 16px; }
  .term-title { font-size: 13pt; font-weight: 700; display: block; }
  .term-meta { font-size: 9.5pt; opacity: 0.85; display: block; margin-top: 2px; }
  .badge-pill { display: inline-block; background: rgba(255,255,255,0.2); color: white; font-size: 9pt; padding: 2px 10px; border-radius: 12px; margin-right: 6px; margin-top: 4px; }

  /* ── Table ── */
  table { width: 100%; border-collapse: collapse; font-size: 10pt; }
  thead tr { background: #e0e7ff; }
  th { padding: 8px 10px; font-weight: 600; color: #1e3a5f; border-bottom: 2px solid #c7d2fe; text-align: left; }
  td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
  tbody tr:nth-child(even) td { background: #fafbff; }
  tbody tr:last-child td { border-bottom: none; }
  .center { text-align: center; }

  /* ── Footer ── */
  .doc-footer { text-align: center; font-size: 9pt; color: #888; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 16px; }
  .doc-footer strong { color: #2563eb; }

  /* ── Summary ── */
  .summary-box { border: 1px solid #c7d2fe; border-radius: 8px; padding: 10px 16px; background: #f0f4ff; margin-bottom: 18px; font-size: 10pt; display: flex; gap: 24px; flex-wrap: wrap; }
  .summary-box .sum-item { font-weight: 600; color: #1e3a5f; }
  .summary-box .sum-val { font-size: 14pt; font-weight: 700; color: #2563eb; display: block; margin-top: 2px; }
</style>
</head>
<body>

  <!-- School Header -->
  <div class="school-header">
    ${logoSrc
        ? `<img src="${logoSrc}" class="school-logo" onerror="this.style.display='none'">`
        : `<div class="school-logo-placeholder">${esc(schoolName).charAt(0)}</div>`}
    <div class="school-info">
      <div class="school-name">${esc(schoolName)}</div>
      ${schoolMotto ? `<div class="school-motto">"${esc(schoolMotto)}"</div>` : ''}
      <div class="school-contact">
        ${schoolAddr ? esc(schoolAddr) + ' &nbsp;|&nbsp; ' : ''}
        ${schoolPhone ? esc(schoolPhone) : ''}
        ${schoolEmail ? ' &nbsp;|&nbsp; ' + esc(schoolEmail) : ''}
      </div>
    </div>
  </div>

  <!-- Document Title -->
  <div class="doc-title">
    <h2>Subject Registration Report</h2>
    <p>Official Academic Record — ${esc(className)} &nbsp;&bull;&nbsp; ${esc(sessionName)}</p>
  </div>

  <!-- Meta Info -->
  <div class="meta-strip">
    <div class="meta-item"><strong>Class:</strong> ${esc(className)}</div>
    <div class="meta-item"><strong>Session:</strong> ${esc(sessionName)}</div>
    <div class="meta-item"><strong>Terms:</strong> ${registeredData.length}</div>
    <div class="meta-item"><strong>Printed:</strong> ${new Date().toLocaleString()}</div>
  </div>

  <!-- Quick Summary -->
  <div class="summary-box">
    <div class="sum-item">Total Terms
      <span class="sum-val">${registeredData.length}</span>
    </div>
    <div class="sum-item">Total Subjects
      <span class="sum-val">${totalSubjects}</span>
    </div>
    <div class="sum-item">Enrolled Students
      <span class="sum-val">${registeredData[0]?.student_count ?? '—'}</span>
    </div>
  </div>

  <!-- Term Blocks -->
  ${termsHtml}

  <!-- Footer -->
  <div class="doc-footer">
    <strong>${esc(schoolName)}</strong> &bull;
    Subject Registration Report &bull;
    Generated on ${new Date().toLocaleString()} &bull;
    School Management System
  </div>

</body>
</html>`;
}

// ════════════════════════════════════════════════════════════════════════════
// REGISTRATION
// ════════════════════════════════════════════════════════════════════════════
function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.closest('tr').querySelector('.id')?.dataset.id));
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

    if (!studentIds.length)     return toast('Error', 'No students selected', 'warning');
    if (!subjectClasses.length) return toast('Error', 'No subjects selected', 'warning');
    if (sessionId === 'ALL')    return toast('Error', 'Please select a session', 'warning');

    const confirm = await Swal.fire({
        title: 'Confirm Registration',
        html: `Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?`,
        icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Register',
    });
    if (!confirm.isConfirmed) return;

    document.getElementById('register-loading-spinner').classList.remove('d-none');

    try {
        const res  = await fetch(ROUTES.batchRegister, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) }),
        });
        const data = await res.json();
        if (data.success) {
            toast('Success', 'Students registered successfully!', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            toast('Error', data.message || 'Registration failed', 'error');
        }
    } catch (err) {
        toast('Error', err.message, 'error');
    } finally {
        document.getElementById('register-loading-spinner').classList.add('d-none');
    }
}

function openUnregisterModal() {
    const studentIds    = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    if (!studentIds.length)     return toast('Error', 'No students selected', 'warning');
    if (!subjectClasses.length) return toast('Error', 'No subjects selected', 'warning');

    document.getElementById('snapshotNameInput').value  = `Unregistration — ${new Date().toLocaleString()}`;
    document.getElementById('snapshotNotesInput').value = '';
    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const name = document.getElementById('snapshotNameInput').value.trim();
    if (!name) return toast('Error', 'Please enter a snapshot name', 'warning');

    const studentIds    = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId     = document.getElementById('idsession').value;
    const notes         = document.getElementById('snapshotNotesInput').value;

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal')).hide();

    Swal.fire({ title: 'Processing…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res  = await fetch(ROUTES.unregister, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId), snapshot_name: name, snapshot_notes: notes }),
        });
        const data = await res.json();
        Swal.close();
        if (data.success) {
            toast('Success', `${data.success_count} student(s) unregistered`, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            toast('Error', data.message || 'Unregistration failed', 'error');
        }
    } catch (err) {
        Swal.close();
        toast('Error', err.message, 'error');
    }
}

// ════════════════════════════════════════════════════════════════════════════
// ARCHIVE
// ════════════════════════════════════════════════════════════════════════════
function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') {
        return toast('Error', 'Please select a class and session first', 'warning');
    }
    loadArchivedPage(1);
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
}

async function loadArchivedPage(page) {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('snapshotCardsContainer');

    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    try {
        const res  = await fetch(`${ROUTES.getArchived}?class_id=${classId}&session_id=${sessionId}&page=${page}`,
            { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = '<div class="alert alert-info text-center"><i class="ri-archive-line fs-4 d-block mb-2"></i>No archived records found.</div>';
            return;
        }

        let html = '<div class="row g-3">';
        data.data.forEach(snapshot => {
            html += `
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;overflow:hidden;">
                        <div class="card-header py-2 px-3 border-0" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-camera-line text-white"></i>
                                <span class="text-white fw-semibold small text-truncate">${esc(snapshot.snapshot_name)}</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-1 small text-muted"><i class="ri-time-line me-1"></i>${new Date(snapshot.unregistered_at).toLocaleString()}</div>
                            <div class="mb-1 small"><strong>Subject:</strong> ${esc(snapshot.subjectname)}</div>
                            <div class="mb-2 small"><strong>Students:</strong> <span class="badge bg-primary rounded-pill">${snapshot.student_count}</span></div>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewSnapshotDetail(${snapshot.archive_id})">
                                    <i class="ri-eye-line me-1"></i>Details
                                </button>
                                <button class="btn btn-sm btn-success flex-fill" onclick="restoreSnapshot(${snapshot.archive_id})">
                                    <i class="ri-refresh-line me-1"></i>Restore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        html += '</div>';
        container.innerHTML = html;

    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger">${esc(err.message)}</div>`;
    }
}

// ════════════════════════════════════════════════════════════════════════════
// EVENT LISTENERS
// ════════════════════════════════════════════════════════════════════════════
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => cb.checked = this.checked);
    const count = this.checked ? document.querySelectorAll('#studentTableBody input[name="chk_child"]').length : 0;
    document.getElementById('register-selected-btn')?.classList.toggle('d-none', count === 0);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', count === 0);
});

document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);

// Show/hide batch buttons when individual checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.matches('#studentTableBody input[name="chk_child"]')) {
        const checked = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length;
        document.getElementById('register-selected-btn')?.classList.toggle('d-none', checked === 0);
        document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', checked === 0);
    }
});
</script>
@endsection
