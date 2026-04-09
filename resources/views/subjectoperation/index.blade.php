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
                                        Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn"
                                        onclick="openUnregisterModal();">
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
                                    <table class="table table-centered align-middle table-nowrap mb-0">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"></div></th>
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

                {{-- MODAL: Snapshot Name --}}
                <div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#f5576c 0%,#f093fb 100%);">
                                <div class="py-1">
                                    <h5 class="modal-title text-white fw-semibold">
                                        <i class="ri-archive-line me-2"></i>Name this Unregistration
                                    </h5>
                                    <p class="text-white-50 small mb-0">Give this snapshot a name so you can find it later.</p>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex gap-2 flex-wrap mb-4">
                                    <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2" id="snapshotStudentCount"></span>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-3 py-2" id="snapshotSubjectCount"></span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Snapshot Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="snapshotNameInput" maxlength="191">
                                    <div class="invalid-feedback">Please enter a snapshot name.</div>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                    <textarea class="form-control" id="snapshotNotesInput" rows="3" maxlength="1000"></textarea>
                                    <div class="form-text text-end"><span id="snapshotNotesCount">0</span>/1000</div>
                                </div>
                                <div class="alert alert-warning d-flex gap-2 align-items-start mt-3 mb-0 py-2">
                                    <i class="ri-error-warning-line fs-5"></i>
                                    <div class="small">All existing scores will be saved and can be fully restored later.</div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger px-4" onclick="proceedUnregister();">
                                    <i class="ri-user-unfollow-line me-1"></i> Unregister & Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Registered Classes Overview --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="modal-title text-white">
                                    <i class="ri-graduation-cap-line me-2"></i>Registered Classes Overview
                                </h5>
                                <div class="ms-auto me-2">
                                    <button type="button" class="btn btn-light btn-sm" onclick="printRegisteredClasses()">
                                        <i class="ri-printer-line me-1"></i> Print
                                    </button>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" style="background: #f8f9fc; max-height: 70vh; overflow-y: auto;">
                                <div id="registeredClassesContent">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                                        <p class="mt-3">Loading registration data...</p>
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
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="modal-title text-white">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control form-control-sm" id="archiveSearch" placeholder="Search snapshots..." style="max-width:300px;">
                                    </div>
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
                                    </select>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1);">
                                        <i class="ri-refresh-line"></i> Refresh
                                    </button>
                                    <div class="spinner-border spinner-border-sm text-warning d-none" id="archiveSpinner"></div>
                                </div>
                                <div class="p-3" id="snapshotCardsContainer">
                                    <div class="text-center text-muted py-4">Select a class and session first.</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                                    <small class="text-muted" id="archiveMeta"></small>
                                    <div id="archivePagination" class="d-flex gap-1"></div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <small class="text-muted me-auto">Click a snapshot to view details. Restored records keep original scores.</small>
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
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="snapshotNotesBanner" class="alert alert-info d-none m-3 mb-0"></div>
                                <div class="px-3 pt-3 pb-2 border-bottom">
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm" style="max-width:340px;">
                                            <span class="input-group-text bg-white border-end-0"><i class="ri-search-line"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="detailSearchInput" placeholder="Search students...">
                                            <button class="btn btn-outline-secondary" onclick="document.getElementById('detailSearchInput').value='';filterDetailRows('');">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-success" onclick="restoreEntireSnapshot();"><i class="ri-refresh-line"></i> Restore All</button>
                                        <button class="btn btn-sm btn-success d-none" id="detailRestoreSelectedBtn" onclick="restoreDetailSelected();"><i class="ri-refresh-line"></i> Restore Selected</button>
                                        <button class="btn btn-sm btn-danger d-none" id="detailDeleteSelectedBtn" onclick="deleteDetailSelected();"><i class="ri-delete-bin-line"></i> Delete Selected</button>
                                        <div class="spinner-border spinner-border-sm text-primary d-none" id="detailSpinner"></div>
                                        <span class="text-muted small ms-auto" id="detailStudentMeta"></span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr id="snapshotDetailHeaderRow">
                                                <th style="width:36px;"><div class="form-check"><input class="form-check-input" type="checkbox" id="detailCheckAll"></div></th>
                                                <th>Student</th>
                                                <th>Adm. No</th>
                                                <th>Gender</th>
                                            </tr>
                                        </thead>
                                        <tbody id="snapshotDetailBody">
                                            <tr><td colspan="10" class="text-center py-4">Loading...</td></tr>
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
                <div id="imageViewModal" class="modal fade" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" class="img-fluid" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .teacher-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .subject-card { transition: all 0.3s ease; }
    .subject-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .stats-card { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 10px; padding: 10px 15px; }
    @media print {
        body * { visibility: hidden; }
        #printableContent, #printableContent * { visibility: visible; }
        #printableContent { position: absolute; top: 0; left: 0; width: 100%; padding: 20px; }
        .no-print, .no-print * { display: none !important; }
        .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; }
        .print-header img { max-height: 80px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #f2f2f2; }
    }
</style>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// GLOBALS
// ============================================================================
const ROUTES = {
    batchRegister: '{{ route("subjectregistration.batch") }}',
    unregister: '{{ route("subjects.destroy") }}',
    getRegistered: '{{ route("subjects.registered-classes") }}',
    getArchived: '{{ route("subjectoperation.archived") }}',
    getSnapshot: '{{ route("subjectoperation.snapshot.detail") }}',
    restore: '{{ route("subjectoperation.restore") }}',
    permanentDelete: '{{ route("subjectoperation.archive.batch-delete") }}',
    index: '{{ route("subjects.index") }}',
    getSchoolInfo: '{{ route("school.information.get") }}',
};
const CSRF = '{{ csrf_token() }}';
const AVATAR_URL = '{{ asset("storage") }}';

let archiveCurrentPage = 1;
let archiveSearchTimer = null;
let currentSnapshotMeta = null;
let currentSnapshotRows = [];

function escapeHtml(str) { if (!str) return ''; return String(str).replace(/[&<>"']/g, function(m) { if (m === '&') return '&amp;'; if (m === '<') return '&lt;'; if (m === '>') return '&gt;'; if (m === '"') return '&quot;'; return '&#039;'; }); }

function showSweetAlert(title, message, type, success) { Swal.fire({ title: title, html: message, icon: success ? 'success' : 'error', confirmButtonColor: success ? '#28a745' : '#dc3545', confirmButtonText: success ? 'Great!' : 'Okay', timer: success ? 3000 : 5000 }); }

function filterData() { const params = new URLSearchParams({ class_id: document.getElementById('idclass').value, session_id: document.getElementById('idsession').value, search: document.querySelector('.search')?.value || '', gender: document.getElementById('idgender').value, admissionno: document.getElementById('idadmission').value }); window.location.href = ROUTES.index + '?' + params.toString(); }

function selectAllSubjects() { document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true); updateSubjectCount(); }
function deselectAllSubjects() { document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false); updateSubjectCount(); }
function updateSubjectCount() { document.getElementById('subjectTeacherCount').textContent = document.querySelectorAll('.subject-checkbox:checked').length; }
document.querySelectorAll('.subject-checkbox').forEach(cb => cb.addEventListener('change', updateSubjectCount));
updateSubjectCount();

document.getElementById('checkAll')?.addEventListener('change', function() { document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => cb.checked = this.checked); toggleBatchButtons(); });
function toggleBatchButtons() { const any = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length > 0; document.getElementById('register-selected-btn')?.classList.toggle('d-none', !any); document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', !any); }

function getSelectedStudentIds() { return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')].map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id)); }
function getSelectedSubjectClasses() { return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({ subjectclassid: parseInt(cb.dataset.subjectclassid), staffid: parseInt(cb.dataset.staffid), termid: parseInt(cb.dataset.termid) })); }
function setSpinner(on) { document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on); }

async function apiFetch(url, method, body) { const res = await fetch(url, { method: method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) }); const data = await res.json(); if (!res.ok && !data.success) throw new Error(data.message || `HTTP ${res.status}`); return data; }

async function registerSelectedStudentsBatch() {
    const studentIds = getSelectedStudentIds(), subjectClasses = getSelectedSubjectClasses(), sessionId = document.getElementById('idsession').value;
    if (!studentIds.length) return showSweetAlert('Error', 'No students selected', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('Error', 'No subjects selected', 'warning', false);
    if (sessionId === 'ALL') return showSweetAlert('Error', 'Please select a session', 'warning', false);
    const ok = await Swal.fire({ title: 'Confirm Registration', html: `<p>Register ${studentIds.length} student(s) for ${subjectClasses.length} subject(s)?</p>`, icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Yes!' });
    if (!ok.isConfirmed) return;
    setSpinner(true);
    try { const res = await apiFetch(ROUTES.batchRegister, 'POST', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) }); if (res.success) { showSweetAlert('Success!', res.message, 'success', true); setTimeout(() => location.reload(), 2000); } else showSweetAlert('Failed', res.message || 'Registration failed', 'error', false); } catch (err) { showSweetAlert('Error', err.message, 'error', false); } finally { setSpinner(false); }
}

function openUnregisterModal() {
    const studentIds = getSelectedStudentIds(), subjectClasses = getSelectedSubjectClasses(), sessionId = document.getElementById('idsession').value;
    if (!studentIds.length) return showSweetAlert('Error', 'No students selected', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('Error', 'No subjects selected', 'warning', false);
    if (sessionId === 'ALL') return showSweetAlert('Error', 'Please select a session', 'warning', false);
    document.getElementById('snapshotStudentCount').textContent = `${studentIds.length} student(s)`;
    document.getElementById('snapshotSubjectCount').textContent = `${subjectClasses.length} subject(s)`;
    document.getElementById('snapshotNameInput').value = 'Unregistration — ' + new Date().toLocaleString();
    document.getElementById('snapshotNotesInput').value = '';
    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const name = document.getElementById('snapshotNameInput').value.trim();
    if (!name) { document.getElementById('snapshotNameInput').classList.add('is-invalid'); return; }
    const studentIds = getSelectedStudentIds(), subjectClasses = getSelectedSubjectClasses(), sessionId = document.getElementById('idsession').value;
    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal'))?.hide();
    setSpinner(true);
    try { const res = await apiFetch(ROUTES.unregister, 'DELETE', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId), snapshot_name: name, snapshot_notes: document.getElementById('snapshotNotesInput').value }); if (res.success) { showSweetAlert('Complete', `${res.success_count} student(s) unregistered`, 'success', true); setTimeout(() => location.reload(), 2000); } else showSweetAlert('Failed', res.message, 'error', false); } catch (err) { showSweetAlert('Error', err.message, 'error', false); } finally { setSpinner(false); }
}

// ============================================================================
// LOAD REGISTERED CLASSES WITH ALPHABETICAL SUBJECTS & TEACHER MATCHING
// ============================================================================

async function loadRegisteredClasses() {
    const classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');
    if (classId === 'ALL' || sessionId === 'ALL') { container.innerHTML = '<div class="text-center py-5"><p>Please select a class and session first.</p></div>'; return; }
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3">Loading...</p></div>';
    try {
        const res = await fetch(ROUTES.getRegistered + '?' + new URLSearchParams({ class_id: classId, session_id: sessionId }), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.success || !data.data.length) { container.innerHTML = '<div class="text-center py-5"><p>No registered classes found.</p></div>'; return; }
        let html = '';
        data.data.forEach(row => {
            const subjects = row.subjects_teachers || [];
            const uniqueTeachers = new Set();
            subjects.forEach(s => { if (s.teachers) s.teachers.forEach(t => { if (t.id) uniqueTeachers.add(t.id); }); });
            html += `<div class="card mb-4 border-0 shadow-sm subject-card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div><i class="ri-school-line me-2"></i><strong class="fs-5">${escapeHtml(row.class_name)} ${escapeHtml(row.arm_name)}</strong><span class="ms-2 badge bg-light text-dark">${escapeHtml(row.session_name)}</span><span class="badge bg-warning text-dark ms-1">${escapeHtml(row.term_name)}</span></div>
                        <div class="mt-2 mt-sm-0"><span class="badge bg-info me-2 p-2"><i class="ri-user-line me-1"></i> Students: ${row.student_count}</span><span class="badge bg-success p-2"><i class="ri-book-open-line me-1"></i> Subjects: ${row.subject_count}</span><span class="badge bg-secondary p-2 ms-2"><i class="ri-user-star-line me-1"></i> Teachers: ${uniqueTeachers.size}</span></div>
                    </div>
                </div>
                <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th width="60" class="text-center">#</th><th>Subject Name</th><th width="120">Subject Code</th><th>Teacher(s)</th><th width="100" class="text-center">Students</th></tr></thead><tbody>`;
            subjects.forEach((subject, idx) => {
                let teachersHtml = '';
                if (subject.teachers && subject.teachers.length) {
                    teachersHtml = '<div class="d-flex flex-wrap gap-2">';
                    subject.teachers.forEach(t => { const pic = t.picture ? `${AVATAR_URL}/staff_avatars/${t.picture.split('/').pop()}` : `${AVATAR_URL}/staff_avatars/default.png`; teachersHtml += `<div class="d-flex align-items-center gap-2 bg-light rounded-3 px-2 py-1" style="border:1px solid #e0e0e0;"><img src="${pic}" class="teacher-avatar" onerror="this.src='${AVATAR_URL}/staff_avatars/default.png'"><span class="fw-medium" style="font-size:0.85rem;">${escapeHtml(t.name)}</span></div>`; });
                    teachersHtml += '</div>';
                } else teachersHtml = '<span class="text-muted">Not assigned</span>';
                html += `<tr><td class="text-center fw-bold text-primary">${idx + 1}</td><td><i class="ri-book-2-line text-primary me-2"></i><strong>${escapeHtml(subject.name)}</strong></td><td><span class="badge bg-secondary-subtle text-secondary px-3 py-2">${escapeHtml(subject.code || '—')}</span></td><td>${teachersHtml}</td><td class="text-center"><span class="badge bg-primary rounded-pill px-3 py-2"><i class="ri-user-line me-1"></i> ${subject.student_count || '—'}</span></td></tr>`;
            });
            html += `</tbody></table></div></div><div class="card-footer bg-light"><div class="row"><div class="col-md-6"><div class="stats-card"><i class="ri-bar-chart-2-line text-primary me-2"></i><strong>Summary:</strong> ${row.subject_count} subjects | ${uniqueTeachers.size} teacher(s) | ${row.student_count} student(s)</div></div><div class="col-md-6 text-md-end"><small class="text-muted"><i class="ri-calendar-line me-1"></i>As of ${new Date().toLocaleString()}</small></div></div></div></div>`;
        });
        container.innerHTML = html;
    } catch (err) { container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`; }
}

// ============================================================================
// PRINT FUNCTIONALITY
// ============================================================================

async function printRegisteredClasses() {
    const classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') { Swal.fire('Cannot Print', 'Select a class and session first', 'warning'); return; }
    Swal.fire({ title: 'Preparing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const schoolRes = await fetch(ROUTES.getSchoolInfo, { headers: { 'Accept': 'application/json' } });
        const schoolData = await schoolRes.json();
        const regRes = await fetch(ROUTES.getRegistered + '?' + new URLSearchParams({ class_id: classId, session_id: sessionId }), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const regData = await regRes.json();
        Swal.close();
        if (!regData.success || !regData.data.length) { Swal.fire('No Data', 'No registered classes found', 'info'); return; }
        const printWindow = window.open('', '_blank');
        printWindow.document.write(await getPrintHTML(schoolData, regData.data));
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    } catch (err) { Swal.close(); Swal.fire('Error', err.message, 'error'); }
}

async function getPrintHTML(schoolData, registeredData) {
    const school = schoolData.success ? schoolData.data : null;
    const schoolLogo = school?.school_logo ? (school.school_logo.startsWith('http') ? school.school_logo : `{{ asset('storage') }}/${school.school_logo}`) : '{{ asset("theme/layouts/assets/images/logo-dark.png") }}';
    let html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Registered Classes</title><style>body{font-family:Arial;padding:20px;} .print-header{text-align:center;margin-bottom:30px;border-bottom:2px solid #667eea;} .print-header img{max-height:80px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f2f2f2;} @page{size:A4;margin:15mm;}</style></head><body>
    <div class="print-header"><img src="${schoolLogo}"><h2>${escapeHtml(school?.school_name || 'School Name')}</h2><p>${escapeHtml(school?.school_address || '')}</p><p>${escapeHtml(school?.school_phone || '')} ${school?.school_email || ''}</p></div>`;
    let totalSubjects = 0, totalStudents = 0;
    registeredData.forEach(row => {
        const subjects = row.subjects_teachers || [];
        html += `<div style="margin-bottom:30px;"><div style="background:#667eea;color:white;padding:10px;"><strong>${escapeHtml(row.class_name)} ${escapeHtml(row.arm_name)}</strong> - ${escapeHtml(row.session_name)} | ${escapeHtml(row.term_name)}</div>
        <table><thead><tr><th>S/N</th><th>Subject Name</th><th>Subject Code</th><th>Teacher(s)</th><th>Students</th></tr></thead><tbody>`;
        subjects.forEach((subject, idx) => {
            const teachers = (subject.teachers || []).map(t => t.name).join(', ');
            html += `<tr><td>${idx + 1}</td><td>${escapeHtml(subject.name)}</td><td>${escapeHtml(subject.code || '—')}</td><td>${escapeHtml(teachers || '—')}</td><td class="text-center">${subject.student_count || '—'}</td></tr>`;
        });
        html += `</tbody></table><div style="margin-top:8px;"><strong>Summary:</strong> ${row.subject_count} subjects | ${row.student_count} students</div></div>`;
        totalSubjects += row.subject_count;
        totalStudents += row.student_count;
    });
    html += `<div style="margin-top:30px;text-align:center;border-top:1px solid #ddd;padding-top:15px;"><p>Total Subjects: ${totalSubjects} | Total Students: ${totalStudents}</p><p>Generated on ${new Date().toLocaleString()}</p></div></body></html>`;
    return html;
}

// ============================================================================
// ARCHIVE FUNCTIONS
// ============================================================================

function openArchivedModal() {
    const classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') { showSweetAlert('Selection Required', 'Select a class and session first', 'warning', false); return; }
    archiveCurrentPage = 1;
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    archiveCurrentPage = page;
    const classId = document.getElementById('idclass').value, sessionId = document.getElementById('idsession').value, termId = document.getElementById('archiveTermFilter').value, search = document.getElementById('archiveSearch').value.trim(), perPage = document.getElementById('archivePerPage').value;
    if (classId === 'ALL' || sessionId === 'ALL') return;
    const spinner = document.getElementById('archiveSpinner'), container = document.getElementById('snapshotCardsContainer');
    spinner.classList.remove('d-none');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>';
    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId, page, per_page: perPage });
        if (termId) params.set('term_id', termId);
        if (search) params.set('search', search);
        const res = await fetch(ROUTES.getArchived + '?' + params.toString(), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.success) { container.innerHTML = `<div class="text-center text-danger py-4">${data.message}</div>`; return; }
        renderSnapshotCards(data.data);
        renderArchivePagination(data.meta);
    } catch (err) { container.innerHTML = `<div class="text-center text-danger py-4">${err.message}</div>`; } finally { spinner.classList.add('d-none'); }
}

function renderSnapshotCards(rows) {
    const container = document.getElementById('snapshotCardsContainer');
    if (!rows.length) { container.innerHTML = '<div class="text-center text-muted py-5">No snapshots found.</div>'; return; }
    const groups = {};
    rows.forEach(row => { const key = `${row.snapshot_name}__${row.subjectclassid}__${row.termid}`; if (!groups[key]) groups[key] = { ...row, subjects: [] }; groups[key].subjects.push(row); });
    let html = '<div class="row g-3">';
    Object.values(groups).forEach(group => {
        const metaEncoded = encodeURIComponent(JSON.stringify({ snapshot_name: group.snapshot_name, subjectclassid: group.subjectclassid, termid: group.termid, sessionid: group.sessionid, staffid: group.staffid }));
        html += `<div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100" style="cursor:pointer;" onclick="openSnapshotDetail('${metaEncoded}')"><div class="card-body"><h6><i class="ri-camera-line text-danger me-1"></i>${escapeHtml(group.snapshot_name)}</h6><small>${new Date(group.unregistered_at).toLocaleString()}</small><div class="mt-2">${group.subjects.map(s => `<span class="badge bg-primary me-1">${escapeHtml(s.subjectname)}</span>`).join('')}</div></div><div class="card-footer bg-light"><button class="btn btn-sm btn-outline-primary w-100" onclick="event.stopPropagation();openSnapshotDetail('${metaEncoded}')">View Details</button></div></div></div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }
    let html = `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === 1 ? 'disabled' : ''}" onclick="loadArchivedPage(${meta.current_page - 1})">‹</button>`;
    for (let p = 1; p <= meta.last_page; p++) { if (p === 1 || p === meta.last_page || (p >= meta.current_page - 2 && p <= meta.current_page + 2)) html += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}" onclick="loadArchivedPage(${p})">${p}</button>`; }
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}" onclick="loadArchivedPage(${meta.current_page + 1})">›</button>`;
    container.innerHTML = html;
}

document.getElementById('archiveSearch')?.addEventListener('input', function() { clearTimeout(archiveSearchTimer); archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400); });
document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));

// ============================================================================
// SNAPSHOT DETAIL
// ============================================================================

async function openSnapshotDetail(metaEncoded) {
    currentSnapshotMeta = JSON.parse(decodeURIComponent(metaEncoded));
    document.getElementById('snapshotDetailTitle').textContent = currentSnapshotMeta.snapshot_name;
    new bootstrap.Modal(document.getElementById('snapshotDetailModal')).show();
    try {
        const params = new URLSearchParams(currentSnapshotMeta);
        const res = await fetch(ROUTES.getSnapshot + '?' + params.toString(), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        currentSnapshotRows = data.rows;
        document.getElementById('detailStudentMeta').textContent = `${data.total_students} student(s)`;
        renderSnapshotDetailTable(data.rows, data.assessment_headers);
    } catch (err) { document.getElementById('snapshotDetailBody').innerHTML = `<tr><td colspan="10" class="text-center text-danger">${err.message}</td></tr>`; }
}

function renderSnapshotDetailTable(rows, assessmentHeaders) {
    const headerRow = document.getElementById('snapshotDetailHeaderRow');
    while (headerRow.cells.length > 4) headerRow.deleteCell(headerRow.cells.length - 1);
    (assessmentHeaders || []).forEach(a => { const th = document.createElement('th'); th.textContent = a.assessment_name; headerRow.appendChild(th); });
    const th = document.createElement('th'); th.textContent = 'Total'; headerRow.appendChild(th);
    let html = '';
    rows.forEach(row => {
        const name = [row.lastname, row.firstname, row.othername].filter(Boolean).join(' ');
        const pic = row.picture ? `${AVATAR_URL}/student_avatars/${row.picture.split('/').pop()}` : `${AVATAR_URL}/student_avatars/unnamed.jpg`;
        let scores = '', total = 0;
        (assessmentHeaders || []).forEach(a => { const score = (row.assessment_scores || []).find(s => s.assessment_id == a.assessment_id); const val = score ? parseFloat(score.score) : 0; total += val; scores += `<td class="text-center">${val > 0 ? val.toFixed(1) : '—'}</td>`; });
        scores += `<td class="text-center fw-bold">${total > 0 ? total.toFixed(1) : '—'}</td>`;
        html += `<tr><td><div class="form-check"><input class="form-check-input detail-chk" type="checkbox" value="${row.archive_id}"></div></td><td><div class="d-flex align-items-center gap-2"><img src="${pic}" class="rounded-circle" style="width:34px;height:34px;">${escapeHtml(name)}</div></td><td>${escapeHtml(row.admissionno || '—')}</td><td>${row.gender || '—'}</td>${scores}</tr>`;
    });
    document.getElementById('snapshotDetailBody').innerHTML = html;
    document.getElementById('detailCheckAll')?.addEventListener('change', function() { document.querySelectorAll('.detail-chk').forEach(cb => cb.checked = this.checked); });
}

function filterDetailRows(query) { const q = query.toLowerCase(); document.querySelectorAll('#snapshotDetailBody tr').forEach(tr => { const match = !q || (tr.innerText || '').toLowerCase().includes(q); tr.style.display = match ? '' : 'none'; }); }

async function restoreEntireSnapshot() { if (!currentSnapshotRows.length) return; await doRestore(currentSnapshotRows.map(r => r.archive_id), 'all students'); }
async function restoreDetailSelected() { const ids = [...document.querySelectorAll('.detail-chk:checked')].map(cb => parseInt(cb.value)); if (!ids.length) return; await doRestore(ids, `${ids.length} selected student(s)`); }
async function doRestore(ids, label) { const ok = await Swal.fire({ title: 'Restore?', html: `<p>Restore ${label}?</p>`, icon: 'question', showCancelButton: true }); if (!ok.isConfirmed) return; try { const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: ids }); showSweetAlert('Restored!', `${res.total_restored || ids.length} restored`, 'success', true); bootstrap.Modal.getInstance(document.getElementById('snapshotDetailModal'))?.hide(); loadArchivedPage(archiveCurrentPage); } catch (err) { showSweetAlert('Error', err.message, 'error', false); } }
async function deleteDetailSelected() { const ids = [...document.querySelectorAll('.detail-chk:checked')].map(cb => parseInt(cb.value)); if (!ids.length) return; const ok = await Swal.fire({ title: 'Delete Permanently?', html: `<p class="text-danger">Delete ${ids.length} record(s)?</p>`, icon: 'error', showCancelButton: true, confirmButtonColor: '#dc3545' }); if (!ok.isConfirmed) return; try { const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids }); showSweetAlert('Deleted', `${res.deleted || ids.length} deleted`, 'success', false); bootstrap.Modal.getInstance(document.getElementById('snapshotDetailModal'))?.hide(); loadArchivedPage(archiveCurrentPage); } catch (err) { showSweetAlert('Error', err.message, 'error', false); } }
</script>
