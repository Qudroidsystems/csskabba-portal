<!-- Mass Student Account Management Modal -->
<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-people-fill me-2"></i> Mass Student Account Management
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">

                <!-- ==================== STEP 1: SELECT STUDENTS ==================== -->
                <div id="massStep1">
                    <!-- Filter Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-funnel me-1"></i> Filter Students</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" id="massStudentSearch" class="form-control" placeholder="Name, Admission No...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Class</label>
                                    <select id="massClassFilter" class="form-select">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Arm</label>
                                    <select id="massArmFilter" class="form-select">
                                        <option value="">All Arms</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Account Status</label>
                                    <select id="massAccountStatus" class="form-select">
                                        <option value="all">All Students</option>
                                        <option value="no">No Account Only</option>
                                        <option value="yes">Has Account Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Email Format:</strong> Student emails are automatically generated as <code>firstname.lastname@csskabba.ng</code> with all special characters removed.
                    </div>

                    <!-- Student Selection Table -->
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-person-lines-fill me-1"></i> Select Students</h6>
                            <div>
                                <span class="badge bg-primary" id="massSelectedCount">0 selected</span>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="selectAllStudents">
                                    <i class="bi bi-check-all"></i> Select All
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="selectAllCheckbox">
                                            </th>
                                            <th>Admission No</th>
                                            <th>Student Name</th>
                                            <th>Class/Arm</th>
                                            <th>Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="massStudentList">
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm me-2"></div>
                                                Loading students...
                                            /N/A
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Cards Preview -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-secondary">
                                <i class="bi bi-lightbulb-fill me-2"></i>
                                <strong>Available Actions:</strong>
                                <ul class="mb-0 mt-1">
                                    <li><strong>Create Accounts</strong> - Creates new user accounts for students WITHOUT accounts (auto-generates email: firstname.lastname@csskabba.ng)</li>
                                    <li><strong>Reset Passwords</strong> - Generates new passwords for students WITH accounts</li>
                                    <li><strong>Revoke Accounts</strong> - Removes user access (student record remains)</li>
                                    <li><strong>Reprint Credentials</strong> - Shows existing credentials (passwords hidden for security)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-lg" id="proceedToAction">
                            Continue to Action <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- ==================== STEP 2: CONFIGURE ACTION ==================== -->
                <div id="massStep2" style="display: none;">
                    <!-- Selected Students Summary -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-check-square-fill me-1"></i> Selected Students (<span id="step2SelectedCount">0</span>)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 200px;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Admission No</th>
                                            <th>Current Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedStudentsList"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Type Selection Cards -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-lightning-charge-fill me-1"></i> Choose Action</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="card action-card" data-action="create" style="cursor: pointer; border: 2px solid #e0e0e0;">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-plus-fill fs-1 text-success"></i>
                                            <h6 class="mt-2 mb-0">Create Accounts</h6>
                                            <small class="text-muted">For students without accounts</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card action-card" data-action="reset" style="cursor: pointer; border: 2px solid #e0e0e0;">
                                        <div class="card-body text-center">
                                            <i class="bi bi-key-fill fs-1 text-warning"></i>
                                            <h6 class="mt-2 mb-0">Reset Passwords</h6>
                                            <small class="text-muted">New password for existing</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card action-card" data-action="revoke" style="cursor: pointer; border: 2px solid #e0e0e0;">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-x-fill fs-1 text-danger"></i>
                                            <h6 class="mt-2 mb-0">Revoke Accounts</h6>
                                            <small class="text-muted">Remove user access</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card action-card" data-action="reprint" style="cursor: pointer; border: 2px solid #e0e0e0;">
                                        <div class="card-body text-center">
                                            <i class="bi bi-printer-fill fs-1 text-info"></i>
                                            <h6 class="mt-2 mb-0">Reprint Credentials</h6>
                                            <small class="text-muted">Print without password</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedAction" value="">
                        </div>
                    </div>

                    <!-- Password Settings (shown for create/reset) - RADIO BUTTONS -->
                    <div id="passwordSettings" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-lock-fill me-1"></i> Password Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Password Type</label>
                                        <div class="d-flex gap-4 mt-2">
                                            <div class="form-check">
                                                <input type="radio" id="passwordTypeIndividual" name="passwordTypeRadio" value="individual" class="form-check-input" checked>
                                                <label class="form-check-label" for="passwordTypeIndividual">
                                                    <strong>Individual Random Passwords</strong>
                                                    <br><small class="text-muted">Each student gets a unique random password</small>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="passwordTypeSame" name="passwordTypeRadio" value="same" class="form-check-input">
                                                <label class="form-check-label" for="passwordTypeSame">
                                                    <strong>Same Password for All</strong>
                                                    <br><small class="text-muted">All selected students get the same password</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3" id="sharedPasswordContainer" style="display: none;">
                                        <label class="form-label">Shared Password</label>
                                        <input type="text" id="sharedPassword" class="form-control" placeholder="Enter password">
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Settings - ONLY Student role is enabled -->
                    <div id="roleSettings" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-tags-fill me-1"></i> Assign Role</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-shield-exclamation me-2"></i>
                                    <strong>Security Notice:</strong> Student accounts can only have the <strong>"Student"</strong> role for security purposes. Administrative roles cannot be assigned to student accounts.
                                </div>

                                <div class="row">
                                    @php
                                        $allRoles = Spatie\Permission\Models\Role::all();
                                        $studentRole = $allRoles->where('name', 'Student')->first();
                                        $otherRoles = $allRoles->where('name', '!=', 'Student');
                                    @endphp

                                    <!-- Student Role (Enabled and Checked) -->
                                    @if($studentRole)
                                        <div class="col-md-12 mb-3">
                                            <div class="card border-success">
                                                <div class="card-body bg-success bg-opacity-10">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input"
                                                               name="roles[]" value="{{ $studentRole->name }}"
                                                               id="role_{{ $studentRole->name }}" checked>
                                                        <label class="form-check-label fw-bold text-success fs-5" for="role_{{ $studentRole->name }}">
                                                            <i class="bi bi-person-badge-fill me-2"></i>
                                                            {{ $studentRole->name }}
                                                            <span class="badge bg-success ms-2">Default Role</span>
                                                        </label>
                                                        <p class="text-muted mt-2 mb-0 ms-4">
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                            This role is automatically assigned to all student accounts.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Other Roles (All Disabled) -->
                                    @if($otherRoles->count() > 0)
                                        <div class="col-md-12">
                                            <hr>
                                            <p class="text-muted mb-2"><i class="bi bi-shield-lock-fill me-1"></i> The following roles cannot be assigned to student accounts:</p>
                                            <div class="row">
                                                @foreach($otherRoles as $role)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="card bg-light">
                                                            <div class="card-body py-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input"
                                                                           value="{{ $role->name }}"
                                                                           id="role_{{ $role->name }}" disabled>
                                                                    <label class="form-check-label text-muted" for="role_{{ $role->name }}">
                                                                        <i class="bi bi-shield-lock-fill me-1"></i>
                                                                        {{ $role->name }}
                                                                        <span class="badge bg-secondary ms-1">Disabled</span>
                                                                    </label>
                                                                </div>
                                                                <small class="text-danger d-block mt-1">
                                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                    Cannot assign {{ $role->name }} role to student account
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Message -->
                    <div id="actionWarning" class="alert alert-warning" style="display: none;"></div>

                    <!-- Email Format Note -->
                    <div class="alert alert-info" id="emailFormatNote" style="display: none;">
                        <i class="bi bi-envelope-fill me-2"></i>
                        <strong>Email Format:</strong> Student emails will be generated as <code>firstname.lastname@csskabba.ng</code> with all special characters removed.
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" id="backToStep1">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn btn-success btn-lg" id="executeAction">
                            <i class="bi bi-check-circle"></i> Execute Action
                        </button>
                    </div>
                </div>

                <!-- ==================== STEP 3: RESULTS ==================== -->
                <div id="massStep3" style="display: none;">
                    <div id="resultsContainer"></div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="newAction">
                            <i class="bi bi-plus-circle"></i> New Action
                        </button>
                        <button type="button" class="btn btn-primary" id="printResults">
                            <i class="bi bi-printer"></i> Print Results
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedStudents = [];
    let allStudents = [];
    let currentResults = null;

    // Helper function to generate preview email with @csskabba.ng domain
    function generatePreviewEmail(firstname, lastname) {
        let cleanFirst = (firstname || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        let cleanLast = (lastname || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        if (!cleanFirst) cleanFirst = 'student';
        if (!cleanLast) cleanLast = 'user';
        return cleanFirst + '.' + cleanLast + '@csskabba.ng';
    }

    // Load students function
    function loadStudents() {
        const search = $('#massStudentSearch').val();
        const classId = $('#massClassFilter').val();
        const armId = $('#massArmFilter').val();
        const accountStatus = $('#massAccountStatus').val();

        $('#massStudentList').html('<tr><td colspan="6" class="text-center"><div class="spinner-border spinner-border-sm"></div> Loading...</td></tr>');

        let url = '{{ route("get.students") }}?limit=2000';
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (classId) url += `&class_id=${classId}`;
        if (armId) url += `&arm_id=${armId}`;
        if (accountStatus !== 'all') url += `&has_account=${accountStatus}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    allStudents = data.students.map(s => ({
                        ...s,
                        generatedEmail: generatePreviewEmail(s.firstname, s.lastname)
                    }));
                    renderStudentTable();
                    if (data.classes && $('#massClassFilter option').length <= 1) {
                        populateFilters(data.classes, data.arms);
                    }
                } else {
                    $('#massStudentList').html('<tr><td colspan="6" class="text-center text-danger">Error loading students</td></tr>');
                }
            })
            .catch(() => {
                $('#massStudentList').html('<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>');
            });
    }

    function renderStudentTable() {
        if (!allStudents.length) {
            $('#massStudentList').html('<tr><td colspan="6" class="text-center">No students found</td></tr>');
            $('#massSelectedCount').text('0 selected');
            return;
        }

        let html = '';
        allStudents.forEach(student => {
            const isSelected = selectedStudents.some(s => s.id === student.id);
            const statusBadge = student.has_account
                ? '<span class="badge bg-success">Has Account</span>'
                : '<span class="badge bg-secondary">No Account</span>';
            const previewEmail = student.generatedEmail;

            html += `
                <tr>
                    <td><input type="checkbox" class="student-checkbox" data-id="${student.id}" ${isSelected ? 'checked' : ''}></td>
                    <td><strong>${escapeHtml(student.admissionNo || 'N/A')}</strong></td>
                    <td>${escapeHtml(student.name)}</td>
                    <td>${escapeHtml(student.class_name || 'N/A')} ${student.arm_name ? '/' + escapeHtml(student.arm_name) : ''}</td>
                    <td>${statusBadge}</td>
                    <td><small class="text-muted">${escapeHtml(previewEmail)}</small></td>
                </tr>
            `;
        });
        $('#massStudentList').html(html);

        updateSelectedCount();

        $('.student-checkbox').off('change').on('change', function() {
            const studentId = parseInt($(this).data('id'));
            const student = allStudents.find(s => s.id === studentId);
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
        $('#massSelectedCount').text(`${selectedStudents.length} selected`);
        $('#selectAllCheckbox').prop('checked', selectedStudents.length === allStudents.length && allStudents.length > 0);
    }

    function populateFilters(classes, arms) {
        let classHtml = '<option value="">All Classes</option>';
        classes.forEach(cls => classHtml += `<option value="${cls.id}">${escapeHtml(cls.name)}</option>`);
        $('#massClassFilter').html(classHtml);

        let armHtml = '<option value="">All Arms</option>';
        arms.forEach(arm => armHtml += `<option value="${arm.id}">${escapeHtml(arm.name)}</option>`);
        $('#massArmFilter').html(armHtml);
    }

    // Filter change events
    $('#massStudentSearch, #massClassFilter, #massArmFilter, #massAccountStatus').on('input change', function() {
        selectedStudents = [];
        loadStudents();
    });

    // Select all functionality
    $('#selectAllStudents, #selectAllCheckbox').on('click', function() {
        const isChecked = $(this).prop('checked');
        selectedStudents = isChecked ? [...allStudents] : [];
        renderStudentTable();
    });

    // Proceed to step 2
    $('#proceedToAction').on('click', function() {
        if (selectedStudents.length === 0) {
            Swal.fire('Warning', 'Please select at least one student', 'warning');
            return;
        }

        let summaryHtml = '';
        selectedStudents.forEach(student => {
            const statusBadge = student.has_account
                ? '<span class="badge bg-success">Has Account</span>'
                : '<span class="badge bg-secondary">No Account</span>';
            const previewEmail = student.generatedEmail;
            summaryHtml += `
                <tr>
                    <td>${escapeHtml(student.name)}</td>
                    <td>${escapeHtml(student.admissionNo || 'N/A')}</td>
                    <td>${statusBadge}</td>
                    <td><small>${escapeHtml(previewEmail)}</small></td>
                </tr>
            `;
        });
        $('#selectedStudentsList').html(summaryHtml);
        $('#step2SelectedCount').text(selectedStudents.length);

        $('#massStep1').hide();
        $('#massStep2').show();
    });

    // Action card click handler
    $('.action-card').on('click', function() {
        $('.action-card').css('border', '2px solid #e0e0e0').css('background', 'white');
        $(this).css('border', '2px solid #0d6efd').css('background', '#f0f8ff');

        const action = $(this).data('action');
        $('#selectedAction').val(action);

        if (action === 'create' || action === 'reset') {
            $('#passwordSettings').show();
            $('#roleSettings').show();
            $('#emailFormatNote').show();
        } else {
            $('#passwordSettings').hide();
            $('#roleSettings').hide();
            $('#emailFormatNote').hide();
        }

        const hasAccountStudents = selectedStudents.filter(s => s.has_account);
        const noAccountStudents = selectedStudents.filter(s => !s.has_account);

        let warningHtml = '';
        if (action === 'create' && hasAccountStudents.length > 0) {
            warningHtml = `<i class="bi bi-exclamation-triangle-fill me-2"></i> ${hasAccountStudents.length} selected student(s) already have accounts and will be skipped.`;
        } else if (action === 'reset' && noAccountStudents.length > 0) {
            warningHtml = `<i class="bi bi-exclamation-triangle-fill me-2"></i> ${noAccountStudents.length} selected student(s) don't have accounts and will be skipped.`;
        } else if (action === 'revoke' && noAccountStudents.length > 0) {
            warningHtml = `<i class="bi bi-exclamation-triangle-fill me-2"></i> ${noAccountStudents.length} selected student(s) don't have accounts and will be skipped.`;
        }

        if (warningHtml) {
            $('#actionWarning').html(warningHtml).show();
        } else {
            $('#actionWarning').hide();
        }
    });

    // Password type radio button change handler
    $('input[name="passwordTypeRadio"]').on('change', function() {
        $('#sharedPasswordContainer').toggle($(this).val() === 'same');
    });

    // Back button
    $('#backToStep1').on('click', function() {
        $('#massStep2').hide();
        $('#massStep1').show();
    });

    // Execute action
    $('#executeAction').on('click', function() {
        const actionType = $('#selectedAction').val();

        if (!actionType) {
            Swal.fire('Error', 'Please select an action', 'error');
            return;
        }

        const students = selectedStudents.map(s => ({ student_id: s.id }));
        const payload = {
            _token: '{{ csrf_token() }}',
            students: students,
            action_type: actionType,
        };

        if (actionType === 'create' || actionType === 'reset') {
            const passwordType = $('input[name="passwordTypeRadio"]:checked').val();
            payload.password_type = passwordType;

            if (passwordType === 'same') {
                payload.shared_password = $('#sharedPassword').val();
                if (!payload.shared_password || payload.shared_password.length < 6) {
                    Swal.fire('Error', 'Shared password must be at least 6 characters', 'error');
                    return;
                }
            }

            payload.roles = ['Student'];
        }

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we process your request',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                currentResults = data;
                displayResults(data);
                $('#massStep2').hide();
                $('#massStep3').show();
            } else {
                Swal.fire('Error', data.message || 'Operation failed', 'error');
            }
        })
        .catch(() => {
            Swal.close();
            Swal.fire('Error', 'Network error occurred', 'error');
        });
    });

    // Display results
    function displayResults(data) {
        let html = '<div class="alert alert-success"><h5><i class="bi bi-check-circle-fill"></i> Operation Complete!</h5><p>' + escapeHtml(data.message) + '</p>';

        if (data.created && data.created.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-person-plus-fill"></i> Created Accounts (${data.created.length}):</strong>
                <div class="table-responsive mt-2"><table class="table table-sm table-bordered">
                <thead class="table-success"><tr><th>Name</th><th>Username</th><th>Email</th><th>Password</th><th>Admission No</th></tr></thead><tbody>`;
            data.created.forEach(c => {
                html += `<tr><td>${escapeHtml(c.name)}</td><td><code>${escapeHtml(c.username)}</code></td><td>${escapeHtml(c.email)}</td><td><code class="bg-light p-1">${escapeHtml(c.password)}</code></td><td>${escapeHtml(c.admissionNo || 'N/A')}</td></tr>`;
            });
            html += `</tbody></table></div></div>`;
        }

        if (data.reset && data.reset.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-key-fill"></i> Reset Passwords (${data.reset.length}):</strong>
                <div class="table-responsive mt-2"><table class="table table-sm table-bordered">
                <thead class="table-warning"><tr><th>Name</th><th>Username</th><th>Email</th><th>New Password</th><th>Admission No</th></tr></thead><tbody>`;
            data.reset.forEach(r => {
                html += `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td><code class="bg-light p-1">${escapeHtml(r.password)}</code></td><td>${escapeHtml(r.admissionNo || 'N/A')}</td></tr>`;
            });
            html += `</tbody></table></div></div>`;
        }

        if (data.revoked && data.revoked.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-person-x-fill"></i> Revoked Accounts (${data.revoked.length}):</strong><ul class="mt-2">`;
            data.revoked.forEach(r => {
                html += `<li>${escapeHtml(r.name)} (${escapeHtml(r.admissionNo || 'N/A')}) - Account removed</li>`;
            });
            html += `</ul></div>`;
        }

        if (data.reprinted && data.reprinted.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-printer-fill"></i> Reprinted Credentials (${data.reprinted.length}):</strong>
                <div class="table-responsive mt-2"><table class="table table-sm table-bordered">
                <thead class="table-info"><tr><th>Name</th><th>Username</th><th>Email</th><th>Admission No</th><th>Note</th></tr></thead><tbody>`;
            data.reprinted.forEach(r => {
                html += `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td>${escapeHtml(r.admissionNo || 'N/A')}</td><td><small class="text-muted">Password hidden for security</small></td></tr>`;
            });
            html += `</tbody></table></div></div>`;
        }

        if (data.skipped && data.skipped.length > 0) {
            html += `<div class="mt-3 text-warning"><strong><i class="bi bi-skip-forward-fill"></i> Skipped (${data.skipped.length}):</strong><ul>`;
            data.skipped.forEach(s => html += `<li>${escapeHtml(s)}</li>`);
            html += `</ul></div>`;
        }

        html += `</div>`;
        $('#resultsContainer').html(html);
    }

    // ==================== IMPROVED PRINT FUNCTION WITH CUTTING LINES ====================
    $('#printResults').on('click', function() {
        if (!currentResults) return;

        const printWindow = window.open('', '_blank');
        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'CSS Kabba';
        const today = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

        let credentialsHtml = '';

        // Process created accounts
        if (currentResults.created && currentResults.created.length > 0) {
            currentResults.created.forEach((student, index) => {
                credentialsHtml += `
                    <div class="credential-card">
                        <div class="cutting-line cutting-line-top">
                            <span class="cutting-text">✂ CUT HERE ✂</span>
                        </div>
                        <div class="credential-content">
                            <div class="school-header">
                                <h2>${escapeHtml(schoolName)}</h2>
                                <p class="subtitle">Student Portal Access Credentials</p>
                                <p class="issue-date">Issued: ${today}</p>
                            </div>
                            <table class="credentials-table">
                                <tr>
                                    <td class="label">Student Name:</td>
                                    <td class="value"><strong>${escapeHtml(student.name)}</strong></td>
                                </tr>
                                <tr>
                                    <td class="label">Admission Number:</td>
                                    <td class="value">${escapeHtml(student.admissionNo || 'N/A')}</td>
                                </tr>
                                <tr>
                                    <td class="label">Login Email:</td>
                                    <td class="value email-value">${escapeHtml(student.email)}</td>
                                </tr>
                                <tr>
                                    <td class="label">Username:</td>
                                    <td class="value">${escapeHtml(student.username)}</td>
                                </tr>
                                <tr>
                                    <td class="label">Password:</td>
                                    <td class="value password-value">${escapeHtml(student.password)}</td>
                                </tr>
                                <tr>
                                    <td class="label">Role:</td>
                                    <td class="value"><span class="role-badge">Student</span></td>
                                </tr>
                            </table>
                            <div class="footer-note">
                                <p><i class="bi bi-exclamation-triangle"></i> Please change your password after first login.</p>
                                <p class="portal-link">Portal: ${window.location.origin}</p>
                            </div>
                        </div>
                        <div class="cutting-line cutting-line-bottom">
                            <span class="cutting-text">✂ CUT HERE ✂</span>
                        </div>
                    </div>
                `;
            });
        }

        // Process reset passwords
        if (currentResults.reset && currentResults.reset.length > 0) {
            currentResults.reset.forEach((student, index) => {
                credentialsHtml += `
                    <div class="credential-card">
                        <div class="cutting-line cutting-line-top">
                            <span class="cutting-text">✂ CUT HERE ✂</span>
                        </div>
                        <div class="credential-content">
                            <div class="school-header">
                                <h2>${escapeHtml(schoolName)}</h2>
                                <p class="subtitle">Password Reset Confirmation</p>
                                <p class="issue-date">Issued: ${today}</p>
                            </div>
                            <table class="credentials-table">
                                <tr>
                                    <td class="label">Student Name:</td>
                                    <td class="value"><strong>${escapeHtml(student.name)}</strong></td>
                                </tr>
                                <tr>
                                    <td class="label">Admission Number:</td>
                                    <td class="value">${escapeHtml(student.admissionNo || 'N/A')}</td>
                                </tr>
                                <tr>
                                    <td class="label">Login Email:</td>
                                    <td class="value email-value">${escapeHtml(student.email)}</td>
                                </tr>
                                <tr>
                                    <td class="label">Username:</td>
                                    <td class="value">${escapeHtml(student.username)}</td>
                                </tr>
                                <tr>
                                    <td class="label">New Password:</td>
                                    <td class="value password-value">${escapeHtml(student.password)}</td>
                                </tr>
                                <tr>
                                    <td class="label">Role:</td>
                                    <td class="value"><span class="role-badge">Student</span></td>
                                </tr>
                            </table>
                            <div class="footer-note">
                                <p><i class="bi bi-exclamation-triangle"></i> This is a newly generated password. Please change after login.</p>
                                <p class="portal-link">Portal: ${window.location.origin}</p>
                            </div>
                        </div>
                        <div class="cutting-line cutting-line-bottom">
                            <span class="cutting-text">✂ CUT HERE ✂</span>
                        </div>
                    </div>
                `;
            });
        }

        const printHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Credentials - ${today}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            background: #fff;
            padding: 20px;
        }

        /* Print container */
        .print-container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Section */
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }

        .print-header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .print-header .subtitle {
            color: #7f8c8d;
            font-size: 12px;
        }

        .print-header .date {
            color: #95a5a6;
            font-size: 11px;
            margin-top: 5px;
        }

        /* Credential Card - Each student gets one card with cutting lines */
        .credential-card {
            position: relative;
            margin-bottom: 30px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Cutting Lines */
        .cutting-line {
            height: 15px;
            background: repeating-linear-gradient(
                45deg,
                #ccc,
                #ccc 10px,
                #fff 10px,
                #fff 20px
            );
            position: relative;
            margin: 0 -5px;
        }

        .cutting-line-top {
            margin-bottom: 10px;
            border-radius: 8px 8px 0 0;
        }

        .cutting-line-bottom {
            margin-top: 10px;
            border-radius: 0 0 8px 8px;
        }

        .cutting-text {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: -2px;
            background: white;
            padding: 0 10px;
            font-size: 9px;
            color: #999;
            font-family: monospace;
            white-space: nowrap;
        }

        /* Credential Content */
        .credential-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #2c3e50;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* School Header */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .school-header h2 {
            color: #2c3e50;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .school-header .subtitle {
            color: #3498db;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .school-header .issue-date {
            color: #7f8c8d;
            font-size: 10px;
            margin-top: 5px;
        }

        /* Credentials Table */
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .credentials-table tr {
            border-bottom: 1px solid #ecf0f1;
        }

        .credentials-table td {
            padding: 12px 8px;
            vertical-align: top;
        }

        .credentials-table .label {
            width: 35%;
            font-weight: 600;
            color: #34495e;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .credentials-table .value {
            width: 65%;
            color: #2c3e50;
            font-weight: 500;
        }

        .email-value {
            color: #2980b9;
            font-family: monospace;
            font-size: 13px;
        }

        .password-value {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
            background: #f0fff4;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 1px;
        }

        .role-badge {
            background: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Footer Note */
        .footer-note {
            text-align: center;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
            font-size: 10px;
            color: #7f8c8d;
        }

        .footer-note i {
            color: #e74c3c;
        }

        .portal-link {
            font-family: monospace;
            margin-top: 5px;
            color: #3498db;
        }

        /* Summary Page */
        .summary-page {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }

        .summary-table th {
            background: #e9ecef;
            font-weight: 600;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .credential-card {
                page-break-after: always;
                break-after: page;
                margin-bottom: 0;
            }

            .credential-card:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .cutting-line {
                background: repeating-linear-gradient(
                    45deg,
                    #aaa,
                    #aaa 8px,
                    #fff 8px,
                    #fff 16px
                );
            }

            .cutting-text {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .password-value {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .summary-page {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        /* Responsive */
        @media (max-width: 600px) {
            .credential-content {
                padding: 15px;
            }

            .credentials-table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Summary Page -->
        <div class="summary-page">
            <h3 style="margin-bottom: 10px;">📋 Credentials Summary</h3>
            <p><strong>Total Students:</strong> ${(currentResults.created?.length || 0) + (currentResults.reset?.length || 0)}</p>
            <p><strong>Generated:</strong> ${today}</p>
            <p><strong>Email Domain:</strong> @csskabba.ng</p>
            <p><strong>Default Role:</strong> Student</p>

            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>✅ Newly Created Accounts</td>
                        <td>${currentResults.created?.length || 0}</td>
                    </tr>
                    <tr>
                        <td>🔄 Password Resets</td>
                        <td>${currentResults.reset?.length || 0}</td>
                    </tr>
                    <tr>
                        <td>⏭️ Skipped</td>
                        <td>${currentResults.skipped?.length || 0}</td>
                    </tr>
                </tbody>
            </table>
            <p style="margin-top: 15px; font-size: 11px; color: #666;">
                <i class="bi bi-info-circle"></i> Each student's credential slip can be cut along the dashed lines.
            </p>
        </div>

        <!-- Individual Credential Slips -->
        ${credentialsHtml}

        <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #999; padding: 20px;">
            <p>This is an official document generated by CSS Kabba School Management System.</p>
            <p>Please keep these credentials secure.</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                setTimeout(function() { window.close(); }, 1000);
            }, 500);
        };
    <\/script>
</body>
</html>`;

        printWindow.document.write(printHtml);
        printWindow.document.close();
    });

    // Reset and new action
    $('#newAction, #massStudentModal').on('hidden.bs.modal', function() {
        selectedStudents = [];
        currentResults = null;
        $('#massStep2, #massStep3').hide();
        $('#massStep1').show();
        $('#selectedAction').val('');
        $('.action-card').css('border', '2px solid #e0e0e0').css('background', 'white');
        loadStudents();
    });

    // Initial load
    $('#massStudentModal').on('show.bs.modal', function() {
        selectedStudents = [];
        loadStudents();
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
});
</script>

<style>
.action-card {
    transition: all 0.3s ease;
}
.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
}
.form-check-input:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
</style>
