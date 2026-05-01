<!-- resources/views/users/partials/mass-student-modal.blade.php -->

<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mass Student Account Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Filters and Student Selection -->
                <div id="step1">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" id="mass-student-search" class="form-control" placeholder="Name, Admission No...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select id="mass-class-filter" class="form-control">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Arm</label>
                            <select id="mass-arm-filter" class="form-control">
                                <option value="">All Arms</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Account Status</label>
                            <select id="mass-account-status" class="form-control">
                                <option value="all">All Students</option>
                                <option value="yes">Has Account</option>
                                <option value="no">No Account</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <input type="checkbox" id="select-all-students">
                                <label for="select-all-students" class="ms-1">Select All ({{ $studentsCount ?? 0 }})</label>
                            </div>
                            <div>
                                <span class="badge bg-info" id="selected-count">0 students selected</span>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40"><input type="checkbox" id="select-all-students-table"></th>
                                        <th>Admission No</th>
                                        <th>Name</th>
                                        <th>Class/Arm</th>
                                        <th>Account Status</th>
                                        <th>Username</th>
                                    </tr>
                                </thead>
                                <tbody id="mass-student-list">
                                    <tr>
                                        <td colspan="6" class="text-center">Loading students...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Actions:</strong>
                        <ul class="mb-0 mt-1">
                            <li><strong>Create</strong> - Creates new user accounts for students without accounts</li>
                            <li><strong>Reset Password</strong> - Generates new password for existing accounts</li>
                            <li><strong>Revoke Account</strong> - Removes user access (student record remains)</li>
                            <li><strong>Reprint</strong> - Shows existing credentials (without revealing passwords)</li>
                        </ul>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary" id="proceed-to-action">Continue to Action <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Step 2: Action Selection -->
                <div id="step2" style="display: none;">
                    <h6 class="mb-3">Selected Students: <span id="step2-selected-count">0</span></h6>

                    <div class="table-responsive mb-3" style="max-height: 200px;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Current Status</th>
                                </tr>
                            </thead>
                            <tbody id="selected-students-list"></tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Action Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input type="radio" name="mass-action-type" id="action-create" value="create" class="form-check-input">
                                    <label class="form-check-label" for="action-create">
                                        <strong>Create Accounts</strong>
                                        <small class="d-block text-muted">For students without accounts only</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="mass-action-type" id="action-reset" value="reset" class="form-check-input">
                                    <label class="form-check-label" for="action-reset">
                                        <strong>Reset Passwords</strong>
                                        <small class="d-block text-muted">Generate new passwords for existing accounts</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="mass-action-type" id="action-revoke" value="revoke" class="form-check-input">
                                    <label class="form-check-label" for="action-revoke">
                                        <strong>Revoke Accounts</strong>
                                        <small class="d-block text-muted">Remove user access completely</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="mass-action-type" id="action-reprint" value="reprint" class="form-check-input">
                                    <label class="form-check-label" for="action-reprint">
                                        <strong>Reprint Credentials</strong>
                                        <small class="d-block text-muted">Show existing credentials (no password)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="password-settings" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Password Type</label>
                                <select id="mass-password-type" class="form-control">
                                    <option value="same">Same password for all</option>
                                    <option value="individual">Individual random passwords</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="shared-password-container" style="display: none;">
                                <label class="form-label">Shared Password</label>
                                <input type="text" id="mass-shared-password" class="form-control" placeholder="Enter password">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>
                    </div>

                    <div id="roles-settings" style="display: none;">
                        <div class="mt-3">
                            <label class="form-label">Assign Roles <span class="text-danger">*</span></label>
                            <select id="mass-roles" class="form-control" multiple>
                                @foreach (Spatie\Permission\Models\Role::all() as $role)
                                    <option value="{{ $role->name }}" {{ $role->name == 'student' ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple roles</small>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3" id="action-warning" style="display: none;"></div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="back-to-step1">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn btn-success" id="execute-mass-action">
                            Execute Action
                        </button>
                    </div>
                </div>

                <!-- Step 3: Results -->
                <div id="step3" style="display: none;">
                    <div id="results-content"></div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="new-mass-action">
                            New Action
                        </button>
                        <button type="button" class="btn btn-primary" id="print-results">
                            <i class="bi bi-printer"></i> Print Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Printable Credentials Template -->
<div id="printable-credentials" style="display: none;">
    <div style="font-family: Arial, sans-serif; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2>Student Account Credentials</h2>
            <p>Generated on: <span id="print-date"></span></p>
            <hr>
        </div>
        <div id="printable-content"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let selectedStudents = [];
    let allStudentsData = [];
    let currentResults = null;

    // Load students when modal opens
    $('#massStudentModal').on('show.bs.modal', function() {
        loadMassStudents();
    });

    // Load students with filters
    function loadMassStudents() {
        const search = $('#mass-student-search').val();
        const classId = $('#mass-class-filter').val();
        const armId = $('#mass-arm-filter').val();
        const accountStatus = $('#mass-account-status').val();

        $('#mass-student-list').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');

        let url = '{{ route("get.students") }}?limit=2000';
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (classId) url += `&class_id=${classId}`;
        if (armId) url += `&arm_id=${armId}`;
        if (accountStatus !== 'all') url += `&has_account=${accountStatus}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    allStudentsData = data.students;
                    renderStudentList(allStudentsData);

                    // Populate filters if empty
                    if ($('#mass-class-filter option').length <= 1) {
                        populateFilters(data.classes, data.arms);
                    }
                } else {
                    $('#mass-student-list').html('<tr><td colspan="6" class="text-center text-danger">Error loading students</td></tr>');
                }
            })
            .catch(() => {
                $('#mass-student-list').html('<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>');
            });
    }

    function renderStudentList(students) {
        if (!students.length) {
            $('#mass-student-list').html('<tr><td colspan="6" class="text-center">No students found</td></tr>');
            $('#selected-count').text('0 students selected');
            return;
        }

        let html = '';
        students.forEach(student => {
            const statusBadge = student.has_account
                ? '<span class="badge bg-success">Has Account</span>'
                : '<span class="badge bg-secondary">No Account</span>';
            const isSelected = selectedStudents.some(s => s.id === student.id);

            html += `
                <tr>
                    <td><input type="checkbox" class="student-checkbox" data-id="${student.id}" ${isSelected ? 'checked' : ''}></td>
                    <td>${student.admissionNo || 'N/A'}</td>
                    <td>${student.name}</td>
                    <td>${student.class_name || 'N/A'} ${student.arm_name ? '/' + student.arm_name : ''}</td>
                    <td>${statusBadge}</td>
                    <td>${student.username || '-'}</td>
                </tr>
            `;
        });
        $('#mass-student-list').html(html);

        updateSelectedCount();

        // Attach checkbox events
        $('.student-checkbox').off('change').on('change', function() {
            const studentId = parseInt($(this).data('id'));
            const student = allStudentsData.find(s => s.id === studentId);

            if ($(this).is(':checked')) {
                if (!selectedStudents.some(s => s.id === studentId)) {
                    selectedStudents.push(student);
                }
            } else {
                selectedStudents = selectedStudents.filter(s => s.id !== studentId);
            }
            updateSelectedCount();
        });
    }

    function updateSelectedCount() {
        $('#selected-count').text(`${selectedStudents.length} student(s) selected`);
        $('#select-all-students').prop('checked', selectedStudents.length === allStudentsData.length && allStudentsData.length > 0);
        $('#select-all-students-table').prop('checked', selectedStudents.length === allStudentsData.length && allStudentsData.length > 0);
    }

    function populateFilters(classes, arms) {
        // Populate classes
        let classHtml = '<option value="">All Classes</option>';
        classes.forEach(cls => {
            classHtml += `<option value="${cls.id}">${cls.name}</option>`;
        });
        $('#mass-class-filter').html(classHtml);

        // Populate arms
        let armHtml = '<option value="">All Arms</option>';
        arms.forEach(arm => {
            armHtml += `<option value="${arm.id}">${arm.name}</option>`;
        });
        $('#mass-arm-filter').html(armHtml);
    }

    // Filter change events
    $('#mass-student-search, #mass-class-filter, #mass-arm-filter, #mass-account-status').on('input change', function() {
        selectedStudents = [];
        loadMassStudents();
    });

    // Select all functionality
    $('#select-all-students, #select-all-students-table').on('change', function() {
        const isChecked = $(this).is(':checked');
        if (isChecked) {
            selectedStudents = [...allStudentsData];
        } else {
            selectedStudents = [];
        }
        renderStudentList(allStudentsData);
    });

    // Proceed to action step
    $('#proceed-to-action').on('click', function() {
        if (selectedStudents.length === 0) {
            Swal.fire('Warning', 'Please select at least one student', 'warning');
            return;
        }

        // Populate selected students list
        let html = '';
        selectedStudents.forEach(student => {
            html += `
                <tr>
                    <td>${student.name}</td>
                    <td>${student.admissionNo || 'N/A'}</td>
                    <td>${student.has_account ? '<span class="badge bg-success">Has Account</span>' : '<span class="badge bg-secondary">No Account</span>'}</td>
                </tr>
            `;
        });
        $('#selected-students-list').html(html);
        $('#step2-selected-count').text(selectedStudents.length);

        $('#step1').hide();
        $('#step2').show();
    });

    // Action type change handler
    $('input[name="mass-action-type"]').on('change', function() {
        const action = $(this).val();

        // Check if action is appropriate for selected students
        const hasAccountStudents = selectedStudents.filter(s => s.has_account);
        const noAccountStudents = selectedStudents.filter(s => !s.has_account);

        $('#action-warning').hide();

        if (action === 'create' && hasAccountStudents.length > 0) {
            $('#action-warning').html(`
                <i class="bi bi-exclamation-triangle"></i>
                Warning: ${hasAccountStudents.length} selected student(s) already have accounts and will be skipped.
            `).show();
        } else if (action === 'reset' && noAccountStudents.length > 0) {
            $('#action-warning').html(`
                <i class="bi bi-exclamation-triangle"></i>
                Warning: ${noAccountStudents.length} selected student(s) don't have accounts and will be skipped.
            `).show();
        } else if (action === 'revoke' && noAccountStudents.length > 0) {
            $('#action-warning').html(`
                <i class="bi bi-exclamation-triangle"></i>
                Warning: ${noAccountStudents.length} selected student(s) don't have accounts and will be skipped.
            `).show();
        }

        // Show/hide password and role settings
        if (action === 'create' || action === 'reset') {
            $('#password-settings').show();
            $('#roles-settings').show();
            $('#mass-password-type').trigger('change');
        } else {
            $('#password-settings').hide();
            $('#roles-settings').hide();
        }
    });

    $('#mass-password-type').on('change', function() {
        const showShared = $(this).val() === 'same';
        $('#shared-password-container').toggle(showShared);
        if (showShared) {
            $('#mass-shared-password').prop('required', true);
        } else {
            $('#mass-shared-password').prop('required', false);
        }
    });

    // Execute action
    $('#execute-mass-action').on('click', function() {
        const actionType = $('input[name="mass-action-type"]:checked').val();

        if (!actionType) {
            Swal.fire('Error', 'Please select an action', 'error');
            return;
        }

        // Prepare payload
        const students = selectedStudents.map(s => ({
            student_id: s.id,
            action: s.has_account ?
                (actionType === 'create' ? 'skip' : actionType) :
                (actionType === 'reset' || actionType === 'revoke' ? 'skip' : actionType)
        }));

        const payload = {
            _token: '{{ csrf_token() }}',
            students: students,
            action_type: actionType,
        };

        if (actionType === 'create' || actionType === 'reset') {
            payload.password_type = $('#mass-password-type').val();
            if (payload.password_type === 'same') {
                payload.shared_password = $('#mass-shared-password').val();
                if (!payload.shared_password || payload.shared_password.length < 6) {
                    Swal.fire('Error', 'Shared password must be at least 6 characters', 'error');
                    return;
                }
            }

            const roles = $('#mass-roles').val();
            if (!roles || roles.length === 0) {
                Swal.fire('Error', 'Please select at least one role', 'error');
                return;
            }
            payload.roles = roles;
        }

        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we process your request',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();

            if (data.success) {
                currentResults = data;
                displayResults(data);
                $('#step2').hide();
                $('#step3').show();
            } else {
                Swal.fire('Error', data.message || 'Operation failed', 'error');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error', 'Network error occurred', 'error');
        });
    });

    function displayResults(data) {
        let html = '<div class="alert alert-success">';
        html += `<h5>Operation Complete!</h5>`;
        html += `<p>${data.message}</p>`;

        if (data.created_count > 0) {
            html += `<div class="mt-3"><strong>Created Accounts (${data.created_count}):</strong>`;
            html += `<div class="table-responsive"><table class="table table-sm table-bordered">`;
            html += `<thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Password</th></tr></thead><tbody>`;
            data.created.forEach(c => {
                html += `<tr><td>${c.name}</td><td>${c.username}</td><td>${c.email}</td><td><code>${c.password}</code></td></tr>`;
            });
            html += `</tbody></table></div></div>`;
        }

        if (data.reset_count > 0) {
            html += `<div class="mt-3"><strong>Reset Passwords (${data.reset_count}):</strong>`;
            html += `<div class="table-responsive"><table class="table table-sm table-bordered">`;
            html += `<thead><tr><th>Name</th><th>Username</th><th>Email</th><th>New Password</th></tr></thead><tbody>`;
            data.reset.forEach(r => {
                html += `<tr><td>${r.name}</td><td>${r.username}</td><td>${r.email}</td><td><code>${r.password}</code></td></tr>`;
            });
            html += `</tbody></table></div></div>`;
        }

        if (data.revoked_count > 0) {
            html += `<div class="mt-3"><strong>Revoked Accounts (${data.revoked_count}):</strong>`;
            html += `<ul>`;
            data.revoked.forEach(r => {
                html += `<li>${r.name} - Account removed</li>`;
            });
            html += `</ul></div>`;
        }

        if (data.skipped && data.skipped.length > 0) {
            html += `<div class="mt-3 text-warning"><strong>Skipped (${data.skipped.length}):</strong>`;
            html += `<ul>`;
            data.skipped.forEach(s => {
                html += `<li>${s}</li>`;
            });
            html += `</ul></div>`;
        }

        if (data.errors && data.errors.length > 0) {
            html += `<div class="mt-3 text-danger"><strong>Errors (${data.errors.length}):</strong>`;
            html += `<ul>`;
            data.errors.forEach(e => {
                html += `<li>${e}</li>`;
            });
            html += `</ul></div>`;
        }

        html += `</div>`;

        $('#results-content').html(html);
    }

    // Print results
    $('#print-results').on('click', function() {
        if (!currentResults) return;

        const printWindow = window.open('', '_blank');
        let printHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Student Credentials</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .credentials-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    .credentials-table th, .credentials-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    .credentials-table th { background-color: #f2f2f2; }
                    .page-break { page-break-after: always; }
                    .footer { margin-top: 30px; text-align: center; font-size: 12px; }
                    @media print {
                        body { margin: 0; padding: 20px; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>Student Account Credentials</h2>
                    <p>Generated: ${new Date().toLocaleString()}</p>
                    <p>Total Students: ${currentResults.created_count + currentResults.reset_count}</p>
                    <hr>
                </div>
        `;

        // Add created accounts
        if (currentResults.created && currentResults.created.length > 0) {
            printHtml += `<h3>Newly Created Accounts (${currentResults.created.length})</h3>`;
            printHtml += `<table class="credentials-table">`;
            printHtml += `<thead><tr><th>#</th><th>Name</th><th>Username</th><th>Email</th><th>Password</th><th>Admission No</th></tr></thead><tbody>`;
            currentResults.created.forEach((c, idx) => {
                printHtml += `<tr>
                    <td>${idx + 1}</td>
                    <td>${c.name}</td>
                    <td>${c.username}</td>
                    <td>${c.email}</td>
                    <td><strong>${c.password}</strong></td>
                    <td>${c.admissionNo || 'N/A'}</td>
                </tr>`;
            });
            printHtml += `</tbody></table>`;
        }

        // Add reset accounts
        if (currentResults.reset && currentResults.reset.length > 0) {
            printHtml += `<h3>Password Reset Accounts (${currentResults.reset.length})</h3>`;
            printHtml += `<table class="credentials-table">`;
            printHtml += `<thead><tr><th>#</th><th>Name</th><th>Username</th><th>Email</th><th>New Password</th><th>Admission No</th></tr></thead><tbody>`;
            currentResults.reset.forEach((r, idx) => {
                printHtml += `<tr>
                    <td>${idx + 1}</td>
                    <td>${r.name}</td>
                    <td>${r.username}</td>
                    <td>${r.email}</td>
                    <td><strong>${r.password}</strong></td>
                    <td>${r.admissionNo || 'N/A'}</td>
                </tr>`;
            });
            printHtml += `</tbody></table>`;
        }

        printHtml += `
                <div class="footer">
                    <p>This is a system-generated document. Please keep these credentials secure.</p>
                </div>
                <script>
                    window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 1000); };
                <\/script>
            </body>
            </html>
        `;

        printWindow.document.write(printHtml);
        printWindow.document.close();
    });

    // Back and new action buttons
    $('#back-to-step1').on('click', function() {
        $('#step2').hide();
        $('#step1').show();
    });

    $('#new-mass-action').on('click', function() {
        selectedStudents = [];
        currentResults = null;
        $('#step3').hide();
        $('#step1').show();
        loadMassStudents();
    });
});
</script>
