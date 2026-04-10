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
.subject-list {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}
.subject-item {
    padding: 16px 20px;
    transition: all 0.25s ease;
}
.subject-item:hover {
    background: #f0f4ff;
}
.subject-item:not(:last-child) {
    border-bottom: 1px solid #f1f3f9;
}
.subject-num {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// ================================================
// FULL CLEAN JAVASCRIPT
// ================================================

const CSRF = '{{ csrf_token() }}';

function esc(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

// Checkbox handling
function refreshCheckboxHandlers() {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
        cb.removeEventListener('change', handleCheckboxChange);
        cb.addEventListener('change', handleCheckboxChange);
    });
}

function handleCheckboxChange() {
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    document.getElementById("register-selected-btn")?.classList.toggle("d-none", checkedCount === 0);
    document.getElementById("unregister-selected-btn")?.classList.toggle("d-none", checkedCount === 0);
}

// Main Filter
function filterData() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({ icon: "warning", title: "Missing Selection", text: "Please select class and session" });
        return;
    }

    const params = new URLSearchParams({
        class_id: classId,
        session_id: sessionId,
        search: document.querySelector('.search')?.value || '',
        gender: document.getElementById('idgender').value,
        admissionno: document.getElementById('idadmission').value,
    });

    window.location.href = '{{ route("subjects.index") }}?' + params.toString();
}

// Subject helpers
function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    cb.checked = !cb.checked;
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
}

// Clean Modal Loader
async function loadRegisteredClasses() {
    const content = document.getElementById('registeredClassesContent');
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        content.innerHTML = `<div class="text-center py-5"><p class="text-muted">Please select a class and session first.</p></div>`;
        return;
    }

    content.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary mb-4" style="width:3.5rem;height:3.5rem;"></div><p class="text-muted">Loading registered subjects...</p></div>`;

    try {
        const res = await axios.get('{{ route("subjects.registered-classes") }}', {
            params: { class_id: classId, session_id: sessionId },
            headers: { 'X-CSRF-TOKEN': CSRF }
        });

        if (res.data.success && res.data.data.length) {
            let html = '';
            res.data.data.forEach(term => html += buildTermPane(term));
            content.innerHTML = html;
        } else {
            content.innerHTML = `<div class="alert alert-info text-center py-5">No registered subjects found.</div>`;
        }
    } catch (err) {
        content.innerHTML = `<div class="alert alert-danger text-center py-5">Failed to load data.</div>`;
    }
}

function buildTermPane(term) {
    const sorted = [...(term.subjects_teachers || [])].sort((a,b) => (a.name||'').localeCompare(b.name||''));

    let items = '';
    sorted.forEach((subject, i) => {
        const teachers = subject.teachers && subject.teachers.length
            ? subject.teachers.map(t => esc(t.name)).join(', ')
            : '<span class="text-muted">— Not assigned</span>';

        items += `
            <div class="subject-item d-flex align-items-start gap-3">
                <div class="subject-num">${i+1}</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${esc(subject.name)}</div>
                    <div class="small text-muted mt-1">${teachers}</div>
                </div>
                <span class="badge bg-primary-subtle text-primary">${subject.student_count || 0} students</span>
            </div>`;
    });

    return `
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header py-3" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);color:white;">
                <h5>${esc(term.class_name)} ${esc(term.arm_name)}</h5>
                <small>${esc(term.session_name)} — ${esc(term.term_name)}</small>
            </div>
            <div class="card-body p-0">
                <div class="subject-list">
                    ${items || '<div class="text-center text-muted py-5">No subjects in this term.</div>'}
                </div>
            </div>
        </div>`;
}

// Basic stubs
function registerSelectedStudentsBatch() {
    Swal.fire('Info', 'Registration started', 'info');
}

function openUnregisterModal() {
    Swal.fire('Info', 'Unregister modal opened', 'info');
}

// Initialize
document.addEventListener("DOMContentLoaded", function () {
    refreshCheckboxHandlers();

    const checkAllEl = document.getElementById("checkAll");
    if (checkAllEl) {
        checkAllEl.addEventListener('change', function () {
            document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
                cb.checked = this.checked;
                const row = cb.closest("tr");
                if (row) row.classList.toggle("table-active", this.checked);
            });
            handleCheckboxChange();
        });
    }

    document.getElementById('registeredClassesModal').addEventListener('show.bs.modal', loadRegisteredClasses);
});
</script>
@endsection
