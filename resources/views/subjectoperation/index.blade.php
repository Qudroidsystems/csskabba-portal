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

                {{-- CLASS & SESSION FILTER --}}
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
                                        <button type="button" class="btn btn-primary w-100" onclick="filterData()">
                                            <i class="ri-search-line me-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBJECT TEACHERS CARD --}}
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
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects()">
                                        <i class="ri-checkbox-multiple-line me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllSubjects()">
                                        <i class="ri-checkbox-blank-line me-1"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info border-0 mb-3 d-flex align-items-center gap-2" style="background:#eff6ff;">
                                    <i class="ri-information-line fs-5 text-primary flex-shrink-0"></i>
                                    <span class="small">Select the subjects you want to register or unregister students for.</span>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STUDENT FILTERS & TABLE --}}
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
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success d-none" id="register-selected-btn" onclick="registerSelectedStudentsBatch()">
                                        <i class="ri-user-add-line me-1"></i>Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn" onclick="openUnregisterModal()">
                                        <i class="ri-user-unfollow-line me-1"></i>Unregister Selected
                                    </button>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line me-1"></i>View Registered
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
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
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- CLEAN MODAL - No Tabs --}}
<div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden;">
            <div class="modal-header px-5 py-4 border-0" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 60%,#7c3aed 100%);">
                <h5 class="modal-title text-white fw-bold">Registered Classes Overview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-5" style="background:#f8f9fc;" id="registeredClassesContent">
                <!-- Content loaded by JavaScript -->
            </div>
            <div class="modal-footer border-0 px-5 py-4" style="background:#f8f9fc;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.subject-check-card.selected {
    background: #eff6ff !important;
    border-color: #3b82f6 !important;
}
.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    border-top: 1px solid #e2e8f0;
}
.subject-reg-card {
    padding: 12px 14px;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.subject-reg-card:nth-child(even) { border-right: none; }
.subject-num-circle {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: #EEEDFE; color: #3C3489;
    font-size: 11px; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.subject-reg-name { font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.3; }
.subject-reg-teacher { font-size: 11px; color: #64748b; margin-top: 3px; }
.subject-reg-count {
    font-size: 10px; background: #EAF3DE; color: #27500A;
    padding: 2px 8px; border-radius: 20px;
    display: inline-block; margin-top: 4px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// ============================================================
// SUBJECT REGISTRATION — UNIFIED SCRIPT
// ============================================================

const CSRF = '{{ csrf_token() }}';
var checkAll = document.getElementById("checkAll");

function esc(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
    );
}

function ensureAxios() {
    if (typeof axios === 'undefined') {
        Swal.fire({ icon: "error", title: "Configuration error", text: "Axios library is missing" });
        return false;
    }
    return true;
}

// ── Checkbox Handling ────────────────────────────────────────

function ischeckboxcheck() {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
        cb.removeEventListener("change", handleCheckboxChange);
        cb.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const target = e?.target;
    if (target) {
        const row = target.closest("tr");
        if (row) row.classList.toggle("table-active", target.checked);
    }

    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    document.getElementById("register-selected-btn")?.classList.toggle("d-none", checkedCount === 0);
    document.getElementById("unregister-selected-btn")?.classList.toggle("d-none", checkedCount === 0);

    const allCbs = document.querySelectorAll('tbody input[name="chk_child"]');
    if (checkAll) checkAll.checked = allCbs.length > 0 && allCbs.length === checkedCount;
}

function refreshCallbacks() {
    ischeckboxcheck();
}

// ── Subject Card Helpers ─────────────────────────────────────

function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    if (cb) cb.checked = !cb.checked;
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
}

// ── Admission No Dropdown ─────────────────────────────────────

function updateAdmissionNoOptions(students) {
    const select = document.getElementById("idadmission");
    if (!select) return;
    select.innerHTML = '<option value="ALL">All Admission Nos</option>';
    const unique = [...new Set(students.map(s => s.admissionno).filter(Boolean))].sort();
    unique.forEach(no => {
        const opt = document.createElement("option");
        opt.value = no; opt.text = no;
        select.appendChild(opt);
    });
}

// ── Main Filter ───────────────────────────────────────────────

function filterData() {
    if (!ensureAxios()) return;

    const classValue    = document.getElementById("idclass").value;
    const sessionValue  = document.getElementById("idsession").value;
    const searchValue   = document.querySelector(".search")?.value.toLowerCase() || '';
    const genderValue   = document.getElementById("idgender")?.value || 'ALL';
    const admissionValue = document.getElementById("idadmission")?.value || 'ALL';

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        Swal.fire({ icon: "warning", title: "Missing Selection", text: "Please select a class and session" });
        return;
    }

    const tableBody = document.getElementById('studentTableBody');
    const subjectContainer = document.getElementById('subjectTeachersContainer');

    if (tableBody) tableBody.innerHTML =
        '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

    axios.get('/subjects', {
        params: { search: searchValue, class_id: classValue, session_id: sessionValue,
                  gender: genderValue, admissionno: admissionValue },
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    }).then(response => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(response.data, 'text/html');

        const newBody = doc.querySelector('#studentTableBody');
        if (newBody && tableBody) tableBody.innerHTML = newBody.innerHTML;

        const newPagination = doc.querySelector('.pagination');
        const curPagination = document.querySelector('.pagination');
        if (newPagination && curPagination) curPagination.innerHTML = newPagination.innerHTML;

        const newCount = doc.querySelector('#studentcount');
        const curCount = document.getElementById('studentcount');
        if (newCount && curCount) curCount.textContent = newCount.textContent;

        const newSubjectContainer = doc.querySelector('#subjectTeachersContainer');
        if (newSubjectContainer && subjectContainer)
            subjectContainer.innerHTML = newSubjectContainer.innerHTML;

        const students = [];
        doc.querySelectorAll('#studentTableBody tr').forEach(row => {
            const cell = row.querySelector('.admissionno');
            if (cell) students.push({ admissionno: cell.dataset.admissionno || cell.textContent.trim() });
        });

        updateAdmissionNoOptions(students);
        refreshCallbacks();
        setupPaginationLinks();

    }).catch(error => {
        console.error("Filter error:", error);
        if (tableBody)
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error loading data.</td></tr>';
        Swal.fire({ icon: "error", title: "Error",
            text: error.response?.data?.message || "Failed to load data" });
    });
}

// ── Pagination ────────────────────────────────────────────────

function setupPaginationLinks() {
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            if (!this.classList.contains('disabled')) loadPage(this.href);
        });
    });
}

function loadPage(url) {
    if (!ensureAxios()) return;
    const tableBody = document.getElementById('studentTableBody');
    if (tableBody) tableBody.innerHTML =
        '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

    axios.get(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    }).then(response => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(response.data, 'text/html');
        const newBody = doc.querySelector('#studentTableBody');
        if (newBody && tableBody) tableBody.innerHTML = newBody.innerHTML;
        refreshCallbacks();
        setupPaginationLinks();
    }).catch(() => {
        if (tableBody) tableBody.innerHTML =
            '<tr><td colspan="7" class="text-center text-danger">Failed to load page.</td></tr>';
    });
}

// ── Registered Classes Modal ──────────────────────────────────

async function loadRegisteredClasses() {
    if (!ensureAxios()) return;

    const content   = document.getElementById('registeredClassesContent');
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        content.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-error-warning-line fs-1 text-warning d-block mb-3"></i>
                <h5 class="text-muted">Please select a class and session first</h5>
            </div>`;
        return;
    }

    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-4" style="width:3rem;height:3rem;"></div>
            <p class="text-muted">Loading registered subjects...</p>
        </div>`;

    try {
        const res = await axios.get('{{ route("subjects.registered-classes") }}', {
            params: { class_id: classId, session_id: sessionId },
            headers: { 'X-CSRF-TOKEN': CSRF }
        });

        if (res.data.success && res.data.data.length) {
            content.innerHTML = res.data.data.map(term => buildTermCard(term)).join('');
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center py-5">
                    <i class="ri-information-line fs-3 d-block mb-2"></i>
                    No registered subjects found for the selected class and session.
                </div>`;
        }
    } catch (err) {
        console.error("loadRegisteredClasses error:", err);
        content.innerHTML = `<div class="alert alert-danger text-center py-4">Failed to load data. Please try again.</div>`;
    }
}

function buildTermCard(term) {
    const subjects = [...(term.subjects_teachers || [])]
        .sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' }));

    const subjectItems = subjects.map((subject, i) => {
        const teachers = subject.teachers && subject.teachers.length
            ? subject.teachers.map(t => esc(t.name)).join(', ')
            : '<span class="text-muted fst-italic">Not assigned</span>';

        return `
            <div class="subject-reg-card">
                <div class="subject-num-circle">${i + 1}</div>
                <div class="flex-grow-1">
                    <div class="subject-reg-name">${esc(subject.name)}</div>
                    <div class="subject-reg-teacher">
                        <i class="ri-user-line me-1" style="font-size:10px;"></i>${teachers}
                    </div>
                    <span class="subject-reg-count">${subject.student_count || 0} students</span>
                </div>
            </div>`;
    }).join('');

    return `
        <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
            <div class="card-header d-flex justify-content-between align-items-center py-3 px-4"
                 style="background:#1e3a5f;">
                <div>
                    <h6 class="mb-0 text-white fw-semibold">${esc(term.class_name)} ${esc(term.arm_name)}</h6>
                    <small class="text-white opacity-75">${esc(term.session_name)} — ${esc(term.term_name)}</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill" style="background:#E6F1FB;color:#0C447C;">
                        ${term.student_count || 0} students
                    </span>
                    <span class="badge rounded-pill" style="background:#EEEDFE;color:#3C3489;">
                        ${subjects.length} subjects
                    </span>
                </div>
            </div>
            <div class="card-body p-0 bg-white">
                <div class="subjects-grid">
                    ${subjectItems || '<div class="p-4 text-center text-muted">No subjects found.</div>'}
                </div>
            </div>
        </div>`;
}

// ── Registration Actions ──────────────────────────────────────

async function registerSelectedStudentsBatch() {
    if (!ensureAxios()) return;
    Swal.fire('Info', 'Registration function triggered', 'info');
    // Add your axios.post logic here
}

function openUnregisterModal() {
    Swal.fire('Info', 'Unregister modal opened', 'info');
}

// ── DOM Ready ─────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", function () {
    refreshCallbacks();
    setupPaginationLinks();

    if (typeof Choices !== 'undefined') {
        ['idclass', 'idsession', 'idgender', 'idadmission'].forEach(id => {
            const el = document.getElementById(id);
            if (el) new Choices(el, { searchEnabled: true });
        });
    }

    checkAll = document.getElementById("checkAll");
    if (checkAll) {
        checkAll.addEventListener('click', function () {
            document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
                cb.checked = this.checked;
                const row = cb.closest("tr");
                if (row) row.classList.toggle("table-active", this.checked);
            });
            handleCheckboxChange({ target: { checked: this.checked } });
        });
    }

    const registeredModal = document.getElementById('registeredClassesModal');
    if (registeredModal) {
        registeredModal.addEventListener('show.bs.modal', loadRegisteredClasses);
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>


@endsection
