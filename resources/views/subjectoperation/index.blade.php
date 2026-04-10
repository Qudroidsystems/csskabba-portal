{{-- resources/views/subjectoperation/index.blade.php --}}
@extends('layouts.app')

@section('title', $pagetitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chalkboard-teacher"></i> {{ $pagetitle }}
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('subjectoperation.index') }}" id="filterForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control select2">
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
                                <div class="form-group">
                                    <label for="session_id">Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control select2">
                                        <option value="ALL">All Sessions</option>
                                        @foreach($schoolsessions as $session)
                                            <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                                {{ $session->session }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select name="gender" id="gender" class="form-control">
                                        <option value="ALL">All Genders</option>
                                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="admissionno">Admission No</label>
                                    <select name="admissionno" id="admissionno" class="form-control">
                                        <option value="ALL">All Students</option>
                                        @if($students)
                                            @foreach($students as $student)
                                                <option value="{{ $student->admissionno }}" {{ request('admissionno') == $student->admissionno ? 'selected' : '' }}>
                                                    {{ $student->admissionno }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" name="search" id="search" class="form-control"
                                           placeholder="Name or Admission No" value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter Students
                                </button>
                                <button type="button" id="btnRegisteredClasses" class="btn btn-info">
                                    <i class="fas fa-chalkboard-teacher"></i> Registered Classes Overview
                                </button>
                                <button type="button" id="btnBatchRegister" class="btn btn-success" disabled>
                                    <i class="fas fa-check-double"></i> Batch Register Selected
                                </button>
                                <button type="button" id="btnBatchUnregister" class="btn btn-danger" disabled>
                                    <i class="fas fa-trash-alt"></i> Batch Unregister Selected
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="resetFilters()">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Subject Teachers Info -->
                    @if($subjectTeachers && count($subjectTeachers) > 0)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Available Subjects for Selected Class & Session:</h6>
                            <div class="row">
                                @foreach($subjectTeachers as $teacher)
                                    <div class="col-md-4">
                                        <span class="badge badge-primary mr-2">{{ $teacher->subjectname }}</span>
                                        <small>Teacher: {{ $teacher->staffname }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Students Table -->
            @if($students && count($students) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i> Student List
                            <span class="badge badge-primary ml-2">{{ $students->total() }} Students</span>
                        </h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <input type="text" id="tableSearch" class="form-control float-right" placeholder="Search table...">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <form id="studentSelectionForm">
                            <table class="table table-hover table-head-fixed text-nowrap" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="selectAll">
                                                <label class="custom-control-label" for="selectAll"></label>
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Admission No</th>
                                        <th>Full Name</th>
                                        <th>Gender</th>
                                        <th>Class</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input student-checkbox"
                                                           id="student_{{ $student->id }}" value="{{ $student->id }}">
                                                    <label class="custom-control-label" for="student_{{ $student->id }}"></label>
                                                </div>
                                            </td>
                                            <td>{{ $students->firstItem() + $index }}</td>
                                            <td>
                                                @if($student->picture)
                                                    <img src="{{ asset('storage/' . $student->picture) }}" class="img-circle" width="40" height="40" alt="Student">
                                                @else
                                                    <img src="{{ asset('dist/img/avatar.png') }}" class="img-circle" width="40" height="40" alt="Avatar">
                                                @endif
                                            </td>
                                            <td>{{ $student->admissionno }}</td>
                                            <td>
                                                {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $student->gender == 'Male' ? 'badge-info' : 'badge-danger' }}">
                                                    {{ $student->gender }}
                                                </span>
                                            </td>
                                            <td>{{ $student->class_name }} {{ $student->arm_name }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info subject-info-btn"
                                                        data-student-id="{{ $student->id }}"
                                                        data-class-id="{{ request('class_id') }}"
                                                        data-term-id="{{ request('term_id') ?? 1 }}"
                                                        data-session-id="{{ request('session_id') }}">
                                                    <i class="fas fa-book"></i> Subjects
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $students->appends(request()->query())->links() }}
                    </div>
                </div>
            @elseif(request('class_id') && request('session_id') && request('class_id') !== 'ALL' && request('session_id') !== 'ALL')
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i> No students found for the selected criteria.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Subject Info Modal -->
<div class="modal fade" id="subjectInfoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-book-open"></i> Student Subject Registration
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="subjectInfoContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subject information...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Batch Register Modal -->
<div class="modal fade" id="batchRegisterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-double"></i> Batch Register Students
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Selected Students: <span id="selectedCount">0</span>
                </div>
                <form id="batchRegisterForm">
                    @csrf
                    <div class="form-group">
                        <label>Select Subject <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="batchSubjectId" required>
                            <option value="">Select Subject</option>
                            @if($subjectTeachers)
                                @foreach($subjectTeachers as $teacher)
                                    <option value="{{ $teacher->subjectclassid }}" data-staffid="{{ $teacher->userid }}" data-termid="{{ $teacher->termid }}">
                                        {{ $teacher->subjectname }} - {{ $teacher->staffname }} ({{ $teacher->termname }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Session</label>
                        <input type="text" class="form-control" value="{{ request('session_id') ? \App\Models\Schoolsession::find(request('session_id'))->session : '' }}" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmBatchRegister">
                    <i class="fas fa-check"></i> Confirm Registration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Unregister Modal -->
<div class="modal fade" id="batchUnregisterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash-alt"></i> Batch Unregister Students
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Warning: This will archive all scores and remove students from the selected subject.
                </div>
                <div class="alert alert-info">
                    Selected Students: <strong id="unregisterSelectedCount">0</strong>
                </div>
                <form id="batchUnregisterForm">
                    @csrf
                    <div class="form-group">
                        <label>Select Subject <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="unregisterSubjectId" required>
                            <option value="">Select Subject</option>
                            @if($subjectTeachers)
                                @foreach($subjectTeachers as $teacher)
                                    <option value="{{ $teacher->subjectclassid }}" data-staffid="{{ $teacher->userid }}" data-termid="{{ $teacher->termid }}">
                                        {{ $teacher->subjectname }} - {{ $teacher->staffname }} ({{ $teacher->termname }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Snapshot Name (Optional)</label>
                        <input type="text" class="form-control" id="snapshotName"
                               placeholder="Leave empty for auto-generated name">
                    </div>
                    <div class="form-group">
                        <label>Snapshot Notes (Optional)</label>
                        <textarea class="form-control" id="snapshotNotes" rows="3"
                                  placeholder="Reason for unregistration..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBatchUnregister">
                    <i class="fas fa-trash-alt"></i> Confirm Unregistration
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


<style>
    .table-head-fixed thead tr th {
        position: sticky;
        top: 0;
        background-color: #f8f9fc;
        z-index: 10;
    }

    .teacher-item {
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 5px;
    }

    .badge-pill {
        font-size: 12px;
        padding: 5px 10px;
    }

    .select2-container .select2-selection--single {
        height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    @media print {
        .btn, .card-tools, .filter-form, .pagination, .modal-footer {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Select All functionality
    $('#selectAll').change(function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
        updateBatchButtons();
    });

    $('.student-checkbox').change(function() {
        updateBatchButtons();
    });

    function updateBatchButtons() {
        const selectedCount = $('.student-checkbox:checked').length;
        $('#btnBatchRegister, #btnBatchUnregister').prop('disabled', selectedCount === 0);
        $('#selectedCount').text(selectedCount);
        $('#unregisterSelectedCount').text(selectedCount);
    }

    // Reset filters
    window.resetFilters = function() {
        $('#class_id').val('ALL').trigger('change');
        $('#session_id').val('ALL').trigger('change');
        $('#gender').val('ALL');
        $('#admissionno').val('ALL');
        $('#search').val('');
        $('#filterForm').submit();
    };

    // Table search
    $('#tableSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#studentsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Subject Info Button
    $('.subject-info-btn').click(function() {
        const studentId = $(this).data('student-id');
        const classId = $(this).data('class-id');
        const termId = $(this).data('term-id');
        const sessionId = $(this).data('session-id');

        $('#subjectInfoModal').modal('show');

        $.ajax({
            url: "{{ url('subjectoperation/subjectinfo') }}/" + studentId + "/" + classId + "/" + termId + "/" + sessionId,
            method: 'GET',
            success: function(response) {
                if (typeof response === 'object' && response.html) {
                    $('#subjectInfoContent').html(response.html);
                } else {
                    $('#subjectInfoContent').html(response);
                }
            },
            error: function(xhr) {
                $('#subjectInfoContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Failed to load subject information. Please try again.
                    </div>
                `);
            }
        });
    });

    // Batch Register Button
    $('#btnBatchRegister').click(function() {
        const selectedCount = $('.student-checkbox:checked').length;
        if (selectedCount === 0) {
            Swal.fire('Warning', 'Please select at least one student', 'warning');
            return;
        }
        $('#batchRegisterModal').modal('show');
    });

    // Confirm Batch Register
    $('#confirmBatchRegister').click(function() {
        const subjectClassId = $('#batchSubjectId').val();
        const selectedOption = $('#batchSubjectId option:selected');
        const staffId = selectedOption.data('staffid');
        const termId = selectedOption.data('termid');
        const sessionId = {{ request('session_id') ?? 'null' }};
        const studentIds = $('.student-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!subjectClassId) {
            Swal.fire('Error', 'Please select a subject', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Registration',
            text: `Register ${studentIds.length} student(s) for ${selectedOption.text()}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, register!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('subjectoperation.batchRegister') }}",
                    method: 'POST',
                    data: {
                        studentids: studentIds,
                        subjectclasses: [{
                            subjectclassid: subjectClassId,
                            staffid: staffId,
                            termid: termId
                        }],
                        sessionid: sessionId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Registration failed. Please try again.', 'error');
                    }
                });
            }
        });
    });

    // Batch Unregister Button
    $('#btnBatchUnregister').click(function() {
        const selectedCount = $('.student-checkbox:checked').length;
        if (selectedCount === 0) {
            Swal.fire('Warning', 'Please select at least one student', 'warning');
            return;
        }
        $('#batchUnregisterModal').modal('show');
    });

    // Confirm Batch Unregister
    $('#confirmBatchUnregister').click(function() {
        const subjectClassId = $('#unregisterSubjectId').val();
        const selectedOption = $('#unregisterSubjectId option:selected');
        const staffId = selectedOption.data('staffid');
        const termId = selectedOption.data('termid');
        const sessionId = {{ request('session_id') ?? 'null' }};
        const studentIds = $('.student-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        const snapshotName = $('#snapshotName').val();
        const snapshotNotes = $('#snapshotNotes').val();

        if (!subjectClassId) {
            Swal.fire('Error', 'Please select a subject', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Unregistration',
            text: `Unregister ${studentIds.length} student(s) from ${selectedOption.text()}? This will archive all scores.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, unregister!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('subjectoperation.destroy') }}",
                    method: 'DELETE',
                    data: {
                        studentids: studentIds,
                        subjectclasses: [{
                            subjectclassid: subjectClassId,
                            staffid: staffId,
                            termid: termId
                        }],
                        sessionid: sessionId,
                        snapshot_name: snapshotName,
                        snapshot_notes: snapshotNotes,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Unregistration failed. Please try again.', 'error');
                    }
                });
            }
        });
    });
});

// =========================================================================
// REGISTERED CLASSES OVERVIEW FUNCTIONALITY
// =========================================================================

function initRegisteredClassesButton() {
    $('#btnRegisteredClasses').click(function(e) {
        e.preventDefault();

        const classId = $('#class_id').val();
        const sessionId = $('#session_id').val();
        const termId = $('#term_id').val();

        if (!classId || classId === 'ALL') {
            Swal.fire('Warning', 'Please select a class', 'warning');
            return;
        }

        if (!sessionId || sessionId === 'ALL') {
            Swal.fire('Warning', 'Please select a session', 'warning');
            return;
        }

        Swal.fire({
            title: 'Loading...',
            text: 'Fetching registered classes overview',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetchRegisteredClasses(classId, sessionId, termId);
    });
}

function fetchRegisteredClasses(classId, sessionId, termId = null) {
    let url = '{{ route("registered.classes") }}';
    let params = {
        class_id: classId,
        session_id: sessionId
    };

    if (termId && termId !== 'ALL' && termId !== '') {
        params.term_id = termId;
    }

    $.ajax({
        url: url,
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            Swal.close();
            if (response.success) {
                if (response.data && response.data.length > 0) {
                    displayRegisteredClassesModal(response.data);
                } else {
                    Swal.fire('Info', 'No registered classes found for the selected criteria', 'info');
                }
            } else {
                Swal.fire('Error', response.message || 'Failed to load registered classes', 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            let errorMessage = 'An error occurred while fetching registered classes';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

function displayRegisteredClassesModal(data) {
    if ($('#registeredClassesModal').length) {
        $('#registeredClassesModal').remove();
    }

    let modalHtml = `
        <div class="modal fade" id="registeredClassesModal" tabindex="-1" role="dialog" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title text-white" id="registeredClassesModalLabel">
                            <i class="fas fa-chalkboard-teacher"></i> Registered Classes Overview
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="form-group mb-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="subjectSearchInput" placeholder="Search by subject or teacher...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="registeredClassesContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printRegisteredClassesModal()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportRegisteredClassesToExcel()">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('body').append(modalHtml);

    // Populate content
    const contentContainer = $('#registeredClassesContent');
    let contentHtml = '';

    data.forEach((termData, termIndex) => {
        const termName = termData.term_name || 'N/A';
        const sessionName = termData.session_name || 'N/A';
        const className = termData.class_name || 'N/A';
        const armName = termData.arm_name || 'N/A';
        const totalStudents = termData.student_count || 0;
        const totalSubjects = termData.subject_count || 0;
        const subjects = termData.subjects_teachers || [];

        contentHtml += `
            <div class="card mb-4 shadow-sm" data-term="${termName}">
                <div class="card-header" style="background-color: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt text-primary"></i>
                                <strong>${escapeHtml(termName)} Term</strong>
                                <span class="text-muted">(${escapeHtml(sessionName)})</span>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-school"></i> ${escapeHtml(className)} ${escapeHtml(armName)}
                            </small>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <span class="badge badge-primary badge-pill mr-2">
                                <i class="fas fa-users"></i> Students: ${totalStudents}
                            </span>
                            <span class="badge badge-success badge-pill">
                                <i class="fas fa-book"></i> Subjects: ${totalSubjects}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th width="35%">Subject</th>
                                    <th width="40%">Teacher(s)</th>
                                    <th width="20%" class="text-center">Registered Students</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        if (subjects.length === 0) {
            contentHtml += `
                <tr class="no-subjects-row">
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-info-circle"></i> No subjects found for this term
                    </td>
                </tr>
            `;
        } else {
            subjects.forEach((subject, index) => {
                const subjectName = subject.name || 'N/A';
                const subjectCode = subject.code || '';
                const studentCount = subject.student_count || 0;
                const teachers = subject.teachers || [];

                let teacherHtml = '';
                if (teachers.length === 0) {
                    teacherHtml = '<span class="text-muted"><i class="fas fa-user-slash"></i> No teacher assigned</span>';
                } else {
                    teacherHtml = teachers.map(teacher => {
                        const teacherName = teacher.name || 'Unknown';
                        return `
                            <div class="teacher-item mb-1">
                                <i class="fas fa-user-circle mr-1"></i>
                                <span>${escapeHtml(teacherName)}</span>
                            </div>
                        `;
                    }).join('');
                }

                contentHtml += `
                    <tr data-subject="${subjectName.toLowerCase()}" data-teacher="${teachers.map(t => t.name.toLowerCase()).join(' ')}">
                        <td class="text-center align-middle">
                            <span class="badge badge-secondary badge-pill">${index + 1}</span>
                        </td>
                        <td class="align-middle">
                            <strong>${escapeHtml(subjectName)}</strong>
                            ${subjectCode ? `<br><small class="text-muted"><i class="fas fa-code"></i> ${escapeHtml(subjectCode)}</small>` : ''}
                        </td>
                        <td class="align-middle">
                            ${teacherHtml}
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-info badge-pill" style="font-size: 14px;">
                                <i class="fas fa-user-graduate"></i> ${studentCount}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }

        contentHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-chart-line"></i>
                        Average students per subject: ${totalSubjects > 0 ? (totalStudents / totalSubjects).toFixed(1) : 0}
                    </small>
                </div>
            </div>
        `;
    });

    contentContainer.html(contentHtml);

    // Search functionality
    $('#subjectSearchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        let visibleCount = 0;

        $('.card').each(function() {
            let cardHasVisible = false;
            $(this).find('tbody tr').each(function() {
                const subject = $(this).data('subject') || '';
                const teacher = $(this).data('teacher') || '';

                if (subject.includes(searchTerm) || teacher.includes(searchTerm)) {
                    $(this).show();
                    cardHasVisible = true;
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            if (cardHasVisible) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        if (visibleCount === 0 && searchTerm) {
            if ($('#noSearchResultsMsg').length === 0) {
                contentContainer.append(`
                    <div id="noSearchResultsMsg" class="alert alert-info text-center">
                        <i class="fas fa-search"></i> No matching subjects found for "${escapeHtml(searchTerm)}"
                    </div>
                `);
            }
        } else {
            $('#noSearchResultsMsg').remove();
        }
    });

    $('#clearSearchBtn').click(function() {
        $('#subjectSearchInput').val('').trigger('keyup');
    });

    $('#registeredClassesModal').modal('show');
}

function printRegisteredClassesModal() {
    const printContent = $('#registeredClassesModal .modal-body').clone();
    const modalHeader = $('#registeredClassesModal .modal-header').clone();

    // Remove search input from print
    printContent.find('.form-group').remove();

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Registered Classes Overview</title>
            <meta charset="utf-8">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    padding: 20px;
                    margin: 0;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #333;
                }
                .print-header h2 {
                    margin: 0;
                    color: #333;
                }
                .print-header p {
                    margin: 5px 0;
                    color: #666;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 10px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }
                .badge {
                    display: inline-block;
                    padding: 3px 8px;
                    background-color: #007bff;
                    color: white;
                    border-radius: 12px;
                    font-size: 12px;
                }
                .teacher-item {
                    margin-bottom: 5px;
                }
                @media print {
                    .no-print {
                        display: none;
                    }
                    body {
                        padding: 0;
                    }
                    .card {
                        break-inside: avoid;
                        page-break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h2>Registered Classes Overview</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            ${modalHeader.prop('outerHTML')}
            ${printContent.prop('outerHTML')}
            <div class="print-footer text-center" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <small>Printed on: ${new Date().toLocaleString()}</small>
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}

function exportRegisteredClassesToExcel() {
    const data = [['#', 'Subject', 'Teacher(s)', 'Registered Students', 'Term', 'Session', 'Class']];

    $('#registeredClassesContent .card').each(function() {
        const termText = $(this).find('.card-header strong').first().text();
        const sessionText = $(this).find('.card-header .text-muted').first().text().replace(/[()]/g, '');
        const classText = $(this).find('.card-header small').text().replace(/School:/, '').trim();

        $(this).find('tbody tr').each(function() {
            if (!$(this).hasClass('no-subjects-row')) {
                const number = $(this).find('td:eq(0) .badge').text().trim();
                const subject = $(this).find('td:eq(1) strong').text().trim();
                const teachers = $(this).find('.teacher-item span').map(function() {
                    return $(this).text().trim();
                }).get().join(', ');
                const students = $(this).find('td:eq(3) .badge').text().trim();

                data.push([number, subject, teachers, students, termText, sessionText, classText]);
            }
        });
    });

    const csvContent = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `registered_classes_${new Date().toISOString().slice(0,19)}.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    Swal.fire('Success', 'Export completed successfully!', 'success');
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    };
    return String(text).replace(/[&<>"']/g, function(char) {
        return map[char];
    });
}

// Initialize on page load
$(document).ready(function() {
    initRegisteredClassesButton();
});
</script>

