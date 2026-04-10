{{-- resources/views/subjectoperation/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Subject Operation</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ $pagetitle ?? 'Subject Operation Management' }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('subjectoperation.index') }}" id="filterForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select name="class_id" id="class_id" class="form-select">
                                        <option value="ALL">All Classes</option>
                                        @foreach($schoolclass as $class)
                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->schoolclass }} {{ $class->schoolarm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="session_id" class="form-label">Session</label>
                                    <select name="session_id" id="session_id" class="form-select">
                                        <option value="ALL">All Sessions</option>
                                        @foreach($schoolsessions as $session)
                                            <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                                {{ $session->session }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select name="gender" id="gender" class="form-select">
                                        <option value="ALL">All Genders</option>
                                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" name="search" id="search" class="form-control"
                                           placeholder="Name or Admission No..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <button type="button" id="viewRegisteredClassesBtn" class="btn btn-info"
                                        {{ !request('class_id') || request('class_id') == 'ALL' || !request('session_id') || request('session_id') == 'ALL' ? 'disabled' : '' }}>
                                    <i class="fas fa-chalkboard"></i> View Registered Classes
                                </button>
                                <button type="button" id="batchRegisterBtn" class="btn btn-success" disabled>
                                    <i class="fas fa-users"></i> Batch Register
                                </button>
                                <button type="button" id="batchUnregisterBtn" class="btn btn-danger" disabled>
                                    <i class="fas fa-user-minus"></i> Batch Unregister
                                </button>
                                <a href="{{ route('subjectoperation.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    @if(request()->filled('class_id') && request('class_id') !== 'ALL' &&
                        request()->filled('session_id') && request('session_id') !== 'ALL')

                        <!-- Subject Teachers Table -->
                        @if($subjectTeachers && $subjectTeachers->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">Subjects for Selected Class</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllSubjects">
                                            </th>
                                            <th>S/N</th>
                                            <th>Subject</th>
                                            <th>Subject Code</th>
                                            <th>Teacher</th>
                                            <th>Term</th>
                                            <th>Session</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjectTeachers as $index => $subject)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="subject-checkbox"
                                                       data-subjectclassid="{{ $subject->subjectclassid }}"
                                                       data-staffid="{{ $subject->userid }}"
                                                       data-termid="{{ $subject->termid }}">
                                            </td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $subject->subjectname }}</strong>
                                                <input type="hidden" class="subject-name" value="{{ $subject->subjectname }}">
                                            </td>
                                            <td>{{ $subject->subjectcode }}</td>
                                            <td>
                                                @if($subject->avatar)
                                                    <img src="{{ asset($subject->avatar) }}" class="rounded-circle me-1" width="30" height="30">
                                                @else
                                                    <i class="fas fa-chalkboard-user"></i>
                                                @endif
                                                {{ $subject->staffname }}
                                            </td>
                                            <td>{{ $subject->termname }}</td>
                                            <td>{{ $subject->sessionname }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Students Table -->
                        @if($students && $students->count() > 0)
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Students in Class</h5>
                                <span class="badge bg-primary">{{ $students->total() }} Total Students</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllStudents">
                                            </th>
                                            <th>S/N</th>
                                            <th>Admission No</th>
                                            <th>Student Name</th>
                                            <th>Gender</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $student)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="student-checkbox" value="{{ $student->id }}"
                                                       data-studentname="{{ $student->firstname }} {{ $student->lastname }}">
                                            </td>
                                            <td>{{ $students->firstItem() + $index }}</td>
                                            <td>{{ $student->admissionno }}</td>
                                            <td>
                                                @if($student->picture)
                                                    <img src="{{ asset($student->picture) }}" class="rounded-circle me-1" width="30" height="30">
                                                @else
                                                    <i class="fas fa-user-graduate"></i>
                                                @endif
                                                {{ $student->firstname }} {{ $student->lastname }} {{ $student->othername }}
                                            </td>
                                            <td>{{ $student->gender }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-subjects-btn"
                                                        data-studentid="{{ $student->id }}"
                                                        data-studentname="{{ $student->firstname }} {{ $student->lastname }}">
                                                    <i class="fas fa-book"></i> View Subjects
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $students->links() }}
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No students found for the selected class and session.
                        </div>
                        @endif
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Please select a class and session to view students and subjects.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Subjects Modal -->
<div class="modal fade" id="viewSubjectsModal" tabindex="-1" aria-labelledby="viewSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewSubjectsModalLabel">
                    <i class="fas fa-book-open"></i> Student Subject Registration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSubjectsModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading subject information...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Registered Classes Overview Modal -->
<div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="registeredClassesModalLabel">
                    <i class="fas fa-chalkboard"></i> Registered Classes Overview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="registeredClassesModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading registered classes data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printRegisteredClasses()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Register Modal -->
<div class="modal fade" id="batchRegisterModal" tabindex="-1" aria-labelledby="batchRegisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="batchRegisterModalLabel">
                    <i class="fas fa-users"></i> Batch Register Students
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="batchRegisterSummary"></div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Are you sure you want to register the selected students for the selected subjects?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmBatchRegister">Confirm Registration</button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Unregister Modal -->
<div class="modal fade" id="batchUnregisterModal" tabindex="-1" aria-labelledby="batchUnregisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="batchUnregisterModalLabel">
                    <i class="fas fa-user-minus"></i> Batch Unregister Students
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="batchUnregisterSummary"></div>
                <div class="mb-3">
                    <label for="snapshot_name" class="form-label">Snapshot Name</label>
                    <input type="text" class="form-control" id="snapshot_name"
                           placeholder="Optional - Auto-generated if empty">
                </div>
                <div class="mb-3">
                    <label for="snapshot_notes" class="form-label">Snapshot Notes</label>
                    <textarea class="form-control" id="snapshot_notes" rows="3"
                              placeholder="Reason for unregistration..."></textarea>
                </div>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action will archive all scores for these students.
                    This action can be reversed by restoring from the archive.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBatchUnregister">Confirm Unregistration</button>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
$(document).ready(function() {
    let selectedStudents = [];
    let selectedSubjects = [];

    // ========================================================================
    // SELECT ALL CHECKBOXES
    // ========================================================================

    $('#selectAllStudents').change(function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
        updateBatchButtons();
    });

    $('#selectAllSubjects').change(function() {
        $('.subject-checkbox').prop('checked', $(this).prop('checked'));
        updateBatchButtons();
    });

    $('.student-checkbox').change(function() {
        updateBatchButtons();
    });

    $('.subject-checkbox').change(function() {
        updateBatchButtons();
    });

    function updateBatchButtons() {
        selectedStudents = $('.student-checkbox:checked').map(function() {
            return {
                id: $(this).val(),
                name: $(this).data('studentname')
            };
        }).get();

        selectedSubjects = $('.subject-checkbox:checked').map(function() {
            return {
                subjectclassid: $(this).data('subjectclassid'),
                staffid: $(this).data('staffid'),
                termid: $(this).data('termid'),
                name: $(this).closest('tr').find('.subject-name').val()
            };
        }).get();

        const hasStudents = selectedStudents.length > 0;
        const hasSubjects = selectedSubjects.length > 0;

        $('#batchRegisterBtn').prop('disabled', !(hasStudents && hasSubjects));
        $('#batchUnregisterBtn').prop('disabled', !(hasStudents && hasSubjects));
    }

    // ========================================================================
    // VIEW REGISTERED CLASSES OVERVIEW
    // ========================================================================

    $('#viewRegisteredClassesBtn').click(function() {
        const classId = $('#class_id').val();
        const sessionId = $('#session_id').val();
        const termId = $('#term_id').val() || null;

        if (!classId || classId === 'ALL' || !sessionId || sessionId === 'ALL') {
            Swal.fire('Error', 'Please select a specific class and session', 'error');
            return;
        }

        showRegisteredClassesModal(classId, sessionId, termId);
    });

    function showRegisteredClassesModal(classId, sessionId, termId = null) {
        $('#registeredClassesModal').modal('show');

        let url = `{{ route('subjectoperation.registeredClasses') }}?class_id=${classId}&session_id=${sessionId}`;
        if (termId && termId !== 'ALL') {
            url += `&term_id=${termId}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    renderRegisteredClassesModal(response.data);
                } else {
                    $('#registeredClassesModalBody').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No registered classes found for the selected criteria.
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load registered classes data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#registeredClassesModalBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        ${errorMsg}
                    </div>
                `);
            }
        });
    }

    function renderRegisteredClassesModal(data) {
        let html = '';

        if (data.length > 1) {
            // Create tabs for multiple terms
            html += `<ul class="nav nav-tabs mb-3" id="registeredClassesTabs" role="tablist">`;
            data.forEach((termData, index) => {
                const activeClass = index === 0 ? 'active' : '';
                html += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${activeClass}"
                                id="tab-${termData.term_id}"
                                data-bs-toggle="tab"
                                data-bs-target="#content-${termData.term_id}"
                                type="button"
                                role="tab">
                            <i class="fas fa-calendar-alt"></i> ${termData.term_name} Term
                            <span class="badge bg-secondary ms-1">${termData.subject_count}</span>
                        </button>
                    </li>
                `;
            });
            html += `</ul>`;

            html += `<div class="tab-content">`;
            data.forEach((termData, index) => {
                const activeClass = index === 0 ? 'show active' : '';
                html += `
                    <div class="tab-pane fade ${activeClass}"
                         id="content-${termData.term_id}"
                         role="tabpanel">
                        ${renderTermContent(termData)}
                    </div>
                `;
            });
            html += `</div>`;
        } else if (data.length === 1) {
            html = renderTermContent(data[0]);
        } else {
            html = `<div class="alert alert-info">No data available.</div>`;
        }

        $('#registeredClassesModalBody').html(html);

        // Initialize tooltips if any
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    function renderTermContent(termData) {
        let html = `
            <div class="mb-4">
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong><i class="fas fa-school"></i> Class:</strong><br>
                            <span class="fs-5">${escapeHtml(termData.class_name)} ${escapeHtml(termData.arm_name || '')}</span>
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-calendar"></i> Session:</strong><br>
                            ${escapeHtml(termData.session_name)}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-calendar-alt"></i> Term:</strong><br>
                            ${escapeHtml(termData.term_name)}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-users"></i> Total Students:</strong><br>
                            <span class="badge bg-primary fs-6">${termData.student_count}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (!termData.subjects_teachers || termData.subjects_teachers.length === 0) {
            html += `<div class="alert alert-warning">No subjects found for this term.</div>`;
            return html;
        }

        // Build the subjects table with sequential numbering and alphabetical order
        html += `
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="registeredClassesTable">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">#</th>
                            <th>Subject Name</th>
                            <th>Subject Code</th>
                            <th>Teacher(s)</th>
                            <th width="120" class="text-center">Registered Students</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        termData.subjects_teachers.forEach((subject, index) => {
            const number = index + 1;

            let teachersHtml = '';
            if (subject.teachers && subject.teachers.length > 0) {
                teachersHtml = '<div class="teacher-list">';
                subject.teachers.forEach(teacher => {
                    const avatarHtml = teacher.picture
                        ? `<img src="${teacher.picture}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">`
                        : `<i class="fas fa-chalkboard-user me-2 fs-5"></i>`;
                    teachersHtml += `
                        <div class="d-flex align-items-center mb-2">
                            ${avatarHtml}
                            <div>
                                <strong>${escapeHtml(teacher.name)}</strong>
                            </div>
                        </div>
                    `;
                });
                teachersHtml += '</div>';
            } else {
                teachersHtml = '<span class="text-muted"><i class="fas fa-user-slash"></i> No teacher assigned</span>';
            }

            // Get student count display with appropriate badge color
            let studentBadgeClass = 'bg-info';
            if (subject.student_count === 0) studentBadgeClass = 'bg-secondary';
            else if (subject.student_count === termData.student_count) studentBadgeClass = 'bg-success';

            html += `
                <tr>
                    <td class="text-center fw-bold fs-5">${number}.</td>
                    <td>
                        <strong class="fs-6">${escapeHtml(subject.name)}</strong>
                    </td>
                    <td>
                        <code>${escapeHtml(subject.code || 'N/A')}</code>
                    </td>
                    <td>${teachersHtml}</td>
                    <td class="text-center">
                        <span class="badge ${studentBadgeClass} fs-6 p-2">
                            <i class="fas fa-user-graduate"></i> ${subject.student_count || 0}
                        </span>
                        ${subject.student_count > 0 ? `<br><small class="text-muted">out of ${termData.student_count}</small>` : ''}
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        // Add summary statistics
        const totalRegisteredStudents = termData.subjects_teachers.reduce((sum, s) => sum + (s.student_count || 0), 0);
        const avgPerSubject = (totalRegisteredStudents / termData.subjects_teachers.length).toFixed(1);

        html += `
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-light border">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h6><i class="fas fa-book"></i> Total Subjects</h6>
                                <h4 class="text-primary">${termData.subject_count}</h4>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-user-graduate"></i> Total Registrations</h6>
                                <h4 class="text-success">${totalRegisteredStudents}</h4>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-chart-line"></i> Avg Students/Subject</h6>
                                <h4 class="text-info">${avgPerSubject}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return html;
    }

    // ========================================================================
    // VIEW STUDENT SUBJECTS
    // ========================================================================

    $('.view-subjects-btn').click(function() {
        const studentId = $(this).data('studentid');
        const studentName = $(this).data('studentname');
        const classId = $('#class_id').val();
        const termId = $('#term_id').val() || $('#subjectTeachersTable select[name="term_id"]').val();
        const sessionId = $('#session_id').val();

        if (!classId || !sessionId) {
            Swal.fire('Error', 'Class and Session are required', 'error');
            return;
        }

        $('#viewSubjectsModalLabel').html(`<i class="fas fa-book-open"></i> Subject Registration - ${studentName}`);
        $('#viewSubjectsModal').modal('show');

        $.ajax({
            url: `{{ url('subjectoperation/subjectinfo') }}/${studentId}/${classId}/${termId}/${sessionId}`,
            method: 'GET',
            success: function(response) {
                if (typeof response === 'object' && response.success === false) {
                    $('#viewSubjectsModalBody').html(`
                        <div class="alert alert-danger">${response.message}</div>
                    `);
                } else {
                    $('#viewSubjectsModalBody').html(response);
                }
            },
            error: function(xhr) {
                $('#viewSubjectsModalBody').html(`
                    <div class="alert alert-danger">
                        Failed to load subject information. Please try again.
                    </div>
                `);
            }
        });
    });

    // ========================================================================
    // BATCH REGISTER
    // ========================================================================

    $('#batchRegisterBtn').click(function() {
        if (selectedStudents.length === 0 || selectedSubjects.length === 0) {
            Swal.fire('Warning', 'Please select at least one student and one subject', 'warning');
            return;
        }

        let summary = `
            <div class="alert alert-info">
                <h6><i class="fas fa-users"></i> Students to Register (${selectedStudents.length})</h6>
                <ul class="mb-2">
                    ${selectedStudents.map(s => `<li>${escapeHtml(s.name)}</li>`).join('')}
                </ul>
                <hr>
                <h6><i class="fas fa-book"></i> Subjects to Register (${selectedSubjects.length})</h6>
                <ul>
                    ${selectedSubjects.map(s => `<li>${escapeHtml(s.name)}</li>`).join('')}
                </ul>
            </div>
        `;

        $('#batchRegisterSummary').html(summary);
        $('#batchRegisterModal').modal('show');
    });

    $('#confirmBatchRegister').click(function() {
        const studentIds = selectedStudents.map(s => s.id);
        const subjectClasses = selectedSubjects.map(s => ({
            subjectclassid: s.subjectclassid,
            staffid: s.staffid,
            termid: s.termid
        }));
        const sessionId = $('#session_id').val();

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: '{{ route("subjectoperation.batchRegister") }}',
            method: 'POST',
            data: {
                studentids: studentIds,
                subjectclasses: subjectClasses,
                sessionid: sessionId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#batchRegisterModal').modal('hide');
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    let errorHtml = '<ul>';
                    if (response.error_details) {
                        response.error_details.forEach(err => {
                            errorHtml += `<li>${err.message || JSON.stringify(err)}</li>`;
                        });
                    }
                    errorHtml += '</ul>';
                    Swal.fire('Partial Success', response.message + errorHtml, 'warning');
                    setTimeout(() => location.reload(), 3000);
                }
            },
            error: function(xhr) {
                $('#batchRegisterModal').modal('hide');
                let errorMsg = 'Batch registration failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                $('#confirmBatchRegister').prop('disabled', false).html('Confirm Registration');
            }
        });
    });

    // ========================================================================
    // BATCH UNREGISTER
    // ========================================================================

    $('#batchUnregisterBtn').click(function() {
        if (selectedStudents.length === 0 || selectedSubjects.length === 0) {
            Swal.fire('Warning', 'Please select at least one student and one subject', 'warning');
            return;
        }

        let summary = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-user-minus"></i> Students to Unregister (${selectedStudents.length})</h6>
                <ul class="mb-2">
                    ${selectedStudents.map(s => `<li>${escapeHtml(s.name)}</li>`).join('')}
                </ul>
                <hr>
                <h6><i class="fas fa-book"></i> Subjects to Unregister (${selectedSubjects.length})</h6>
                <ul>
                    ${selectedSubjects.map(s => `<li>${escapeHtml(s.name)}</li>`).join('')}
                </ul>
            </div>
        `;

        $('#batchUnregisterSummary').html(summary);
        $('#snapshot_name').val('');
        $('#snapshot_notes').val('');
        $('#batchUnregisterModal').modal('show');
    });

    $('#confirmBatchUnregister').click(function() {
        const studentIds = selectedStudents.map(s => s.id);
        const subjectClasses = selectedSubjects.map(s => ({
            subjectclassid: s.subjectclassid,
            staffid: s.staffid,
            termid: s.termid
        }));
        const sessionId = $('#session_id').val();
        const snapshotName = $('#snapshot_name').val();
        const snapshotNotes = $('#snapshot_notes').val();

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: '{{ route("subjectoperation.destroy") }}',
            method: 'DELETE',
            data: {
                studentids: studentIds,
                subjectclasses: subjectClasses,
                sessionid: sessionId,
                snapshot_name: snapshotName,
                snapshot_notes: snapshotNotes,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#batchUnregisterModal').modal('hide');
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Unregistration failed', 'error');
                }
            },
            error: function(xhr) {
                $('#batchUnregisterModal').modal('hide');
                let errorMsg = 'Batch unregistration failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                $('#confirmBatchUnregister').prop('disabled', false).html('Confirm Unregistration');
            }
        });
    });
});

// ========================================================================
// HELPER FUNCTIONS
// ========================================================================

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function printRegisteredClasses() {
    const printContents = document.getElementById('registeredClassesModalBody').innerHTML;
    const originalTitle = document.title;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Registered Classes Overview</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body { padding: 20px; font-family: Arial, sans-serif; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .table th { background-color: #f2f2f2; }
                .badge { padding: 3px 8px; border-radius: 4px; color: white; }
                .bg-primary { background-color: #007bff; }
                .bg-success { background-color: #28a745; }
                .bg-info { background-color: #17a2b8; }
                .bg-secondary { background-color: #6c757d; }
                .text-center { text-align: center; }
                @media print {
                    .no-print { display: none; }
                    body { margin: 0; padding: 10px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                ${printContents}
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() { window.close(); }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

<style>
.teacher-list {
    max-height: 100px;
    overflow-y: auto;
}
.teacher-list .d-flex {
    border-bottom: 1px solid #f0f0f0;
    padding: 5px 0;
}
.teacher-list .d-flex:last-child {
    border-bottom: none;
}
#registeredClassesTable tbody tr:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}
.badge.fs-6 {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>

