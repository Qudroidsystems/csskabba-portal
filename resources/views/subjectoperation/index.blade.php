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
                                        <p class="mt-3">Select a class and session to view registered subjects...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Snapshot Name for Unregistration --}}
                <div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);">
                                <h5 class="modal-title text-white">Name this Unregistration</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Snapshot Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="snapshotNameInput" placeholder="e.g., Term 2 Corrections - June 2025">
                                    <div class="invalid-feedback">Please enter a snapshot name.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes (optional)</label>
                                    <textarea class="form-control" id="snapshotNotesInput" rows="3" placeholder="Reason for unregistration..."></textarea>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="ri-error-warning-line me-2"></i>
                                    All scores will be saved and can be restored later.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" onclick="proceedUnregister();">Unregister & Save</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL: Unregistered History --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="modal-title text-white">Unregistered History</h5>
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
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <h5 class="modal-title text-white" id="snapshotDetailTitle">Snapshot Detail</h5>
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

<style>
    /* Print Styles */
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; top: 0; left: 0; width: 100%; padding: 20px; }
        .no-print { display: none !important; }
        .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .print-header img { max-height: 80px; }
        .print-header h2 { margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    }

    /* Custom UI Styles */
    .subject-card { transition: transform 0.2s, box-shadow 0.2s; }
    .subject-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .teacher-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .stats-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
    .subject-number { display: inline-block; width: 28px; height: 28px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 50%; text-align: center; line-height: 28px; font-size: 12px; font-weight: bold; margin-right: 10px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// CONFIGURATION
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

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function showSweetAlert(title, message, type, success = true) {
    Swal.fire({ title, html: message, icon: type, confirmButtonColor: success ? '#28a745' : '#dc3545' });
}

function filterData() {
    const params = new URLSearchParams({
        class_id: document.getElementById('idclass').value,
        session_id: document.getElementById('idsession').value,
        search: document.querySelector('.search')?.value || '',
        gender: document.getElementById('idgender').value,
        admissionno: document.getElementById('idadmission').value,
    });
    window.location.href = ROUTES.index + '?' + params.toString();
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('subjectTeacherCount').textContent = document.querySelectorAll('.subject-checkbox:checked').length;
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('subjectTeacherCount').textContent = 0;
}

// ============================================================================
// REGISTERED CLASSES MODAL - ALPHABETICAL SUBJECTS WITH TEACHERS
// ============================================================================
async function loadRegisteredClasses() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        container.innerHTML = '<div class="alert alert-warning">Please select a class and session first.</div>';
        return;
    }

    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';

    try {
        const res = await fetch(`${ROUTES.getRegistered}?class_id=${classId}&session_id=${sessionId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = '<div class="alert alert-info">No registered classes found.</div>';
            return;
        }

        let html = '';
        for (const classData of data.data) {
            // Subjects are already alphabetically ordered from backend
            const subjects = classData.subjects_teachers || [];

            html += `
                <div class="card mb-4 subject-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <i class="ri-school-line me-2"></i>
                                <strong class="fs-5">${escapeHtml(classData.class_name)} ${escapeHtml(classData.arm_name)}</strong>
                                <span class="ms-2 badge bg-light text-dark">${escapeHtml(classData.session_name)}</span>
                                <span class="badge bg-warning text-dark ms-1">${escapeHtml(classData.term_name)}</span>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <span class="badge bg-info me-2 p-2"><i class="ri-user-line me-1"></i> Students: ${classData.student_count || 0}</span>
                                <span class="badge bg-success p-2"><i class="ri-book-open-line me-1"></i> Subjects: ${subjects.length}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="70" class="text-center">#</th>
                                        <th>Subject Name</th>
                                        <th width="120">Subject Code</th>
                                        <th>Teacher(s)</th>
                                        <th width="100" class="text-center">Students</th>
                                    </tr>
                                </thead>
                                <tbody>`;

            // Display subjects with sequential numbering (already alphabetical)
            subjects.forEach((subject, index) => {
                const studentCount = subject.student_count || 0;
                let teachersHtml = '';

                if (subject.teachers && subject.teachers.length > 0) {
                    teachersHtml = '<div class="d-flex flex-wrap gap-2">';
                    subject.teachers.forEach(teacher => {
                        const picUrl = teacher.picture ?
                            `${AVATAR_URL}/staff_avatars/${teacher.picture.split('/').pop()}` :
                            `${AVATAR_URL}/staff_avatars/default.png`;
                        teachersHtml += `
                            <div class="d-flex align-items-center gap-2 bg-light rounded-3 px-2 py-1" style="border: 1px solid #e0e0e0;">
                                <img src="${picUrl}" class="teacher-avatar" onerror="this.src='${AVATAR_URL}/staff_avatars/default.png'">
                                <span class="fw-medium">${escapeHtml(teacher.name)}</span>
                            </div>
                        `;
                    });
                    teachersHtml += '</div>';
                } else {
                    teachersHtml = '<span class="text-muted"><i class="ri-user-unfollow-line me-1"></i> Not assigned</span>';
                }

                html += `
                    <tr>
                        <td class="text-center"><span class="subject-number">${index + 1}</span></td>
                        <td><strong><i class="ri-book-2-line text-primary me-2"></i>${escapeHtml(subject.name)}</strong></td>
                        <td><span class="badge bg-secondary-subtle text-secondary">${escapeHtml(subject.code || '—')}</span></td>
                        <td>${teachersHtml}</td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill px-3 py-2">${studentCount}</span></td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="ri-bar-chart-line me-1"></i>
                                    <strong>Total Subjects:</strong> ${subjects.length} |
                                    <strong>Total Students:</strong> ${classData.student_count || 0}
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    <i class="ri-calendar-line me-1"></i>
                                    Generated: ${new Date().toLocaleString()}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
    }
}

// ============================================================================
// PRINT FUNCTIONALITY WITH SCHOOL INFORMATION
// ============================================================================
async function printRegisteredClasses() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire('Cannot Print', 'Please select a class and session first.', 'warning');
        return;
    }

    Swal.fire({ title: 'Preparing print...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const schoolRes = await fetch(ROUTES.getSchoolInfo, { headers: { 'Accept': 'application/json' } });
        const schoolData = await schoolRes.json();

        const regRes = await fetch(`${ROUTES.getRegistered}?class_id=${classId}&session_id=${sessionId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const regData = await regRes.json();

        Swal.close();

        if (!regData.success || !regData.data.length) {
            Swal.fire('No Data', 'No registered classes found.', 'info');
            return;
        }

        const printWindow = window.open('', '_blank');
        printWindow.document.write(await getPrintHTML(schoolData, regData.data));
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    } catch (err) {
        Swal.close();
        Swal.fire('Error', err.message, 'error');
    }
}

async function getPrintHTML(schoolData, registeredData) {
    const classSelect = document.getElementById('idclass');
    const sessionSelect = document.getElementById('idsession');
    const className = classSelect.options[classSelect.selectedIndex]?.text || 'Selected Class';
    const sessionName = sessionSelect.options[sessionSelect.selectedIndex]?.text || 'Selected Session';

    const school = schoolData.success ? schoolData.data : null;
    const schoolName = school?.school_name || 'School Name';
    const schoolAddress = school?.school_address || '';
    const schoolPhone = school?.school_phone || '';
    const schoolEmail = school?.school_email || '';
    const schoolMotto = school?.school_motto || '';
    const schoolLogo = school?.school_logo ?
        (school.school_logo.startsWith('http') ? school.school_logo : `{{ asset('storage') }}/${school.school_logo}`) : '';

    let subjectsHtml = '';
    let totalSubjectsOverall = 0;

    for (const classData of registeredData) {
        const subjects = classData.subjects_teachers || [];
        totalSubjectsOverall += subjects.length;

        subjectsHtml += `
            <div class="class-section" style="margin-bottom: 30px; page-break-inside: avoid;">
                <div class="class-header" style="background: #667eea; color: white; padding: 12px; border-radius: 5px 5px 0 0;">
                    <strong>${escapeHtml(classData.class_name)} ${escapeHtml(classData.arm_name)}</strong>
                    <span style="float: right;">Session: ${escapeHtml(classData.session_name)} | Term: ${escapeHtml(classData.term_name)}</span>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">S/N</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Subject Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Subject Code</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Teacher(s)</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Students</th>
                        </tr>
                    </thead>
                    <tbody>`;

        subjects.forEach((subject, index) => {
            const teachersNames = (subject.teachers || []).map(t => t.name).join(', ');
            subjectsHtml += `
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${index + 1}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(subject.name)}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(subject.code || '—')}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(teachersNames || '—')}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${subject.student_count || 0}</td>
                </tr>
            `;
        });

        subjectsHtml += `
                    </tbody>
                </table>
                <div style="padding: 5px 0; font-size: 11px;">Total Subjects: ${subjects.length} | Total Students: ${classData.student_count || 0}</div>
            </div>
        `;
    }

    return `<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Registered Classes - ${escapeHtml(schoolName)}</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
            .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #667eea; padding-bottom: 15px; }
            .print-header img { max-height: 80px; }
            .print-header h2 { margin: 10px 0; }
            .print-info { margin-bottom: 20px; padding: 10px; background: #f8f9fc; }
            .class-section { margin-bottom: 30px; page-break-inside: avoid; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background: #f2f2f2; }
            @page { size: A4; margin: 15mm; }
        </style>
    </head>
    <body>
        <div class="print-header">
            ${schoolLogo ? `<img src="${schoolLogo}" onerror="this.style.display='none'">` : ''}
            <h2>${escapeHtml(schoolName)}</h2>
            <div>${escapeHtml(schoolMotto)}</div>
            <div>${escapeHtml(schoolAddress)}</div>
            <div>${escapeHtml(schoolPhone)} ${schoolEmail ? '| ' + escapeHtml(schoolEmail) : ''}</div>
        </div>
        <div class="print-info">
            <strong>Class:</strong> ${escapeHtml(className)} |
            <strong>Session:</strong> ${escapeHtml(sessionName)} |
            <strong>Print Date:</strong> ${new Date().toLocaleString()}
        </div>
        <h3>Subject Registration Summary</h3>
        ${subjectsHtml}
        <div style="text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd;">
            <small>Total Subjects: ${totalSubjectsOverall} | Generated by School Management System</small>
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
        staffid: parseInt(cb.dataset.staffid),
        termid: parseInt(cb.dataset.termid),
    }));
}

async function registerSelectedStudentsBatch() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;

    if (!studentIds.length) return showSweetAlert('Error', 'No students selected', 'warning');
    if (!subjectClasses.length) return showSweetAlert('Error', 'No subjects selected', 'warning');
    if (sessionId === 'ALL') return showSweetAlert('Error', 'Please select a session', 'warning');

    const result = await Swal.fire({
        title: 'Confirm Registration',
        html: `Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?`,
        icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745'
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch(ROUTES.batchRegister, {
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
    }
}

function openUnregisterModal() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    if (!studentIds.length) return showSweetAlert('Error', 'No students selected', 'warning');
    if (!subjectClasses.length) return showSweetAlert('Error', 'No subjects selected', 'warning');

    document.getElementById('snapshotNameInput').value = `Unregistration - ${new Date().toLocaleString()}`;
    document.getElementById('snapshotNotesInput').value = '';
    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const name = document.getElementById('snapshotNameInput').value.trim();
    if (!name) return showSweetAlert('Error', 'Please enter a snapshot name', 'warning');

    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;
    const notes = document.getElementById('snapshotNotesInput').value;

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal')).hide();

    try {
        const res = await fetch(ROUTES.unregister, {
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
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') {
        return showSweetAlert('Error', 'Please select a class and session first', 'warning');
    }
    loadArchivedPage(1);
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
}

async function loadArchivedPage(page) {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('snapshotCardsContainer');

    container.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';

    try {
        const res = await fetch(`${ROUTES.getArchived}?class_id=${classId}&session_id=${sessionId}&page=${page}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = '<div class="alert alert-info">No archived records found.</div>';
            return;
        }

        let html = '<div class="row">';
        data.data.forEach(snapshot => {
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6><i class="ri-camera-line"></i> ${escapeHtml(snapshot.snapshot_name)}</h6>
                            <small class="text-muted">${new Date(snapshot.unregistered_at).toLocaleString()}</small>
                            <p class="mt-2"><strong>Subject:</strong> ${escapeHtml(snapshot.subjectname)}</p>
                            <p><strong>Students:</strong> ${snapshot.student_count}</p>
                            <button class="btn btn-sm btn-primary" onclick="viewSnapshotDetail(${snapshot.archive_id})">View Details</button>
                            <button class="btn btn-sm btn-success" onclick="restoreSnapshot(${snapshot.archive_id})">Restore</button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
    }
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => cb.checked = this.checked);
});

document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
</script>
@endsection
