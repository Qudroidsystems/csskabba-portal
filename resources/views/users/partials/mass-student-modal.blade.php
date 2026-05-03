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

                <!-- STEP 1: SELECT STUDENTS -->
                <div id="massStep1">
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

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Email Format:</strong> Student emails are automatically generated as <code>firstname.lastname@csskabba.ng</code> with all special characters removed.
                    </div>

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
                                            <th width="40"><input type="checkbox" id="selectAllCheckbox"></th>
                                            <th>Admission No</th>
                                            <th>Student Name</th>
                                            <th>Class/Arm</th>
                                            <th>Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="massStudentList">
                                        <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2"></div> Loading students...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-secondary">
                                <i class="bi bi-lightbulb-fill me-2"></i>
                                <strong>Available Actions:</strong>
                                <ul class="mb-0 mt-1">
                                    <li><strong>Create Accounts</strong> - Creates new user accounts for students WITHOUT accounts</li>
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

                <!-- STEP 2: CONFIGURE ACTION -->
                <div id="massStep2" style="display: none;">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-check-square-fill me-1"></i> Selected Students (<span id="step2SelectedCount">0</span>)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 200px;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr><th>Student Name</th><th>Admission No</th><th>Current Status</th><th>Generated Email</th></tr>
                                    </thead>
                                    <tbody id="selectedStudentsList"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

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

                    <div id="roleSettings" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-tags-fill me-1"></i> Assign Role</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-shield-exclamation me-2"></i>
                                    <strong>Security Notice:</strong> Student accounts can only have the <strong>"Student"</strong> role.
                                </div>
                                @php
                                    $allRoles = Spatie\Permission\Models\Role::all();
                                    $studentRole = $allRoles->where('name', 'Student')->first();
                                    $otherRoles = $allRoles->where('name', '!=', 'Student');
                                @endphp
                                @if($studentRole)
                                    <div class="card border-success mb-3">
                                        <div class="card-body bg-success bg-opacity-10">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="roles[]" value="{{ $studentRole->name }}" id="role_{{ $studentRole->name }}" checked>
                                                <label class="form-check-label fw-bold text-success fs-5" for="role_{{ $studentRole->name }}">
                                                    <i class="bi bi-person-badge-fill me-2"></i>
                                                    {{ $studentRole->name }}
                                                    <span class="badge bg-success ms-2">Default Role</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($otherRoles->count() > 0)
                                    <hr>
                                    <p class="text-muted mb-2"><i class="bi bi-shield-lock-fill me-1"></i> The following roles cannot be assigned to student accounts:</p>
                                    <div class="row">
                                        @foreach($otherRoles as $role)
                                            <div class="col-md-4 mb-2">
                                                <div class="card bg-light">
                                                    <div class="card-body py-2">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" value="{{ $role->name }}" id="role_{{ $role->name }}" disabled>
                                                            <label class="form-check-label text-muted" for="role_{{ $role->name }}">
                                                                <i class="bi bi-shield-lock-fill me-1"></i>
                                                                {{ $role->name }}
                                                                <span class="badge bg-secondary ms-1">Disabled</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="actionWarning" class="alert alert-warning" style="display: none;"></div>
                    <div class="alert alert-info" id="emailFormatNote" style="display: none;">
                        <i class="bi bi-envelope-fill me-2"></i>
                        <strong>Email Format:</strong> Student emails will be generated as <code>firstname.lastname@csskabba.ng</code>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" id="backToStep1"><i class="bi bi-arrow-left"></i> Back</button>
                        <button type="button" class="btn btn-success btn-lg" id="executeAction"><i class="bi bi-check-circle"></i> Execute Action</button>
                    </div>
                </div>

                <!-- STEP 3: RESULTS -->
                <div id="massStep3" style="display: none;">
                    <div id="resultsContainer"></div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="newAction"><i class="bi bi-plus-circle"></i> New Action</button>
                        <button type="button" class="btn btn-primary" id="printResults"><i class="bi bi-printer"></i> Print Results</button>
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
    let classArmsData = [];

    function generatePreviewEmail(firstname, lastname) {
        let cleanFirst = (firstname || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        let cleanLast = (lastname || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        if (!cleanFirst) cleanFirst = 'student';
        if (!cleanLast) cleanLast = 'user';
        return cleanFirst + '.' + cleanLast + '@csskabba.ng';
    }

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
                    allStudents = data.students.map(s => ({ ...s, generatedEmail: generatePreviewEmail(s.firstname, s.lastname) }));
                    renderStudentTable();
                    if (data.classes) {
                        populateFilters(data.classes, data.arms, data.class_arms);
                    }
                } else {
                    $('#massStudentList').html('<tr><td colspan="6" class="text-center text-danger">Error loading students</td></tr>');
                }
            })
            .catch(() => {
                $('#massStudentList').html('<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>');
            });
    }

    function populateFilters(classes, arms, classArms) {
        classArmsData = classArms || [];

        let classHtml = '<option value="">All Classes</option>';
        const uniqueClasses = [];
        const classIds = new Set();
        classes.forEach(cls => {
            if (!classIds.has(cls.id)) {
                classIds.add(cls.id);
                uniqueClasses.push(cls);
            }
        });
        uniqueClasses.forEach(cls => {
            classHtml += `<option value="${cls.id}">${escapeHtml(cls.name)}</option>`;
        });
        $('#massClassFilter').html(classHtml);

        let armHtml = '<option value="">All Arms</option>';
        arms.forEach(arm => {
            armHtml += `<option value="${arm.id}">${escapeHtml(arm.name)}</option>`;
        });
        $('#massArmFilter').html(armHtml);

        $('#massClassFilter').off('change').on('change', function() {
            const selectedClassId = $(this).val();
            if (!selectedClassId) {
                $('#massArmFilter option').show();
                $('#massArmFilter').val('');
            } else {
                const relatedArmIds = classArmsData.filter(ca => ca.class_id == selectedClassId).map(ca => String(ca.arm_id));
                $('#massArmFilter option').each(function() {
                    const val = $(this).val();
                    if (val && !relatedArmIds.includes(val)) $(this).hide();
                    else $(this).show();
                });
                const currentArm = $('#massArmFilter').val();
                if (currentArm && !relatedArmIds.includes(currentArm)) $('#massArmFilter').val('');
            }
            applyFilters();
        });
    }

    function applyFilters() {
        const search = $('#massStudentSearch').val().toLowerCase();
        const classId = $('#massClassFilter').val();
        const armId = $('#massArmFilter').val();
        const accountStatus = $('#massAccountStatus').val();

        let filtered = [...allStudents];
        if (search) filtered = filtered.filter(s => s.name.toLowerCase().includes(search) || (s.admissionNo || '').toLowerCase().includes(search));
        if (classId) filtered = filtered.filter(s => String(s.class_id) === classId);
        if (armId) filtered = filtered.filter(s => String(s.arm_id) === armId);
        if (accountStatus === 'yes') filtered = filtered.filter(s => s.has_account);
        else if (accountStatus === 'no') filtered = filtered.filter(s => !s.has_account);

        renderFilteredTable(filtered);
    }

    function renderFilteredTable(students) {
        if (!students.length) {
            $('#massStudentList').html('<tr><td colspan="6" class="text-center">No students found</td></tr>');
            $('#massSelectedCount').text('0 selected');
            return;
        }
        let html = '';
        students.forEach(student => {
            const isSelected = selectedStudents.some(s => s.id === student.id);
            const statusBadge = student.has_account ? '<span class="badge bg-success">Has Account</span>' : '<span class="badge bg-secondary">No Account</span>';
            html += `<tr><td><input type="checkbox" class="student-checkbox" data-id="${student.id}" ${isSelected ? 'checked' : ''}></td>
                <td><strong>${escapeHtml(student.admissionNo || 'N/A')}</strong></td>
                <td>${escapeHtml(student.name)}</td>
                <td>${escapeHtml(student.class_name || 'N/A')} ${student.arm_name ? '/' + escapeHtml(student.arm_name) : ''}</td>
                <td>${statusBadge}</td>
                <td><small class="text-muted">${escapeHtml(student.generatedEmail)}</small></td></tr>`;
        });
        $('#massStudentList').html(html);
        updateSelectedCount();
        $('.student-checkbox').off('change').on('change', function() {
            const studentId = parseInt($(this).data('id'));
            const student = allStudents.find(s => s.id === studentId);
            if ($(this).is(':checked')) { if (!selectedStudents.some(s => s.id === studentId)) selectedStudents.push(student); }
            else { selectedStudents = selectedStudents.filter(s => s.id !== studentId); }
            updateSelectedCount();
        });
    }

    function renderStudentTable() {
        renderFilteredTable(allStudents);
    }

    function updateSelectedCount() {
        $('#massSelectedCount').text(`${selectedStudents.length} selected`);
        $('#selectAllCheckbox').prop('checked', selectedStudents.length === allStudents.length && allStudents.length > 0);
    }

    $('#massStudentSearch, #massClassFilter, #massArmFilter, #massAccountStatus').on('input change', function() { applyFilters(); });
    $('#selectAllStudents, #selectAllCheckbox').on('click', function() { selectedStudents = $(this).prop('checked') ? [...allStudents] : []; renderStudentTable(); });

    $('#proceedToAction').on('click', function() {
        if (selectedStudents.length === 0) { Swal.fire('Warning', 'Please select at least one student', 'warning'); return; }
        let summaryHtml = '';
        selectedStudents.forEach(student => {
            const statusBadge = student.has_account ? '<span class="badge bg-success">Has Account</span>' : '<span class="badge bg-secondary">No Account</span>';
            summaryHtml += `<tr><td>${escapeHtml(student.name)}</td><td>${escapeHtml(student.admissionNo || 'N/A')}</td><td>${statusBadge}</td><td><small>${escapeHtml(student.generatedEmail)}</small></td></tr>`;
        });
        $('#selectedStudentsList').html(summaryHtml);
        $('#step2SelectedCount').text(selectedStudents.length);
        $('#massStep1').hide();
        $('#massStep2').show();
    });

    $('.action-card').on('click', function() {
        $('.action-card').css('border', '2px solid #e0e0e0').css('background', 'white');
        $(this).css('border', '2px solid #0d6efd').css('background', '#f0f8ff');
        const action = $(this).data('action');
        $('#selectedAction').val(action);
        if (action === 'create' || action === 'reset') { $('#passwordSettings, #roleSettings, #emailFormatNote').show(); }
        else { $('#passwordSettings, #roleSettings, #emailFormatNote').hide(); }
        const hasAccountStudents = selectedStudents.filter(s => s.has_account);
        const noAccountStudents = selectedStudents.filter(s => !s.has_account);
        let warningHtml = '';
        if (action === 'create' && hasAccountStudents.length > 0) warningHtml = `${hasAccountStudents.length} selected student(s) already have accounts and will be skipped.`;
        else if (action === 'reset' && noAccountStudents.length > 0) warningHtml = `${noAccountStudents.length} selected student(s) don't have accounts and will be skipped.`;
        else if (action === 'revoke' && noAccountStudents.length > 0) warningHtml = `${noAccountStudents.length} selected student(s) don't have accounts and will be skipped.`;
        if (warningHtml) $('#actionWarning').html(`<i class="bi bi-exclamation-triangle-fill me-2"></i> ${warningHtml}`).show();
        else $('#actionWarning').hide();
    });

    $('input[name="passwordTypeRadio"]').on('change', function() { $('#sharedPasswordContainer').toggle($(this).val() === 'same'); });
    $('#backToStep1').on('click', function() { $('#massStep2').hide(); $('#massStep1').show(); });

    $('#executeAction').on('click', function() {
        const actionType = $('#selectedAction').val();
        if (!actionType) { Swal.fire('Error', 'Please select an action', 'error'); return; }
        const students = selectedStudents.map(s => ({ student_id: s.id }));
        const payload = { _token: '{{ csrf_token() }}', students: students, action_type: actionType };
        if (actionType === 'create' || actionType === 'reset') {
            const passwordType = $('input[name="passwordTypeRadio"]:checked').val();
            payload.password_type = passwordType;
            if (passwordType === 'same') {
                payload.shared_password = $('#sharedPassword').val();
                if (!payload.shared_password || payload.shared_password.length < 6) { Swal.fire('Error', 'Shared password must be at least 6 characters', 'error'); return; }
            }
            payload.roles = ['Student'];
        }
        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) { currentResults = data; displayResults(data); $('#massStep2').hide(); $('#massStep3').show(); }
            else { Swal.fire('Error', data.message || 'Operation failed', 'error'); }
        })
        .catch(() => { Swal.close(); Swal.fire('Error', 'Network error occurred', 'error'); });
    });

    function displayResults(data) {
        let html = '<div class="alert alert-success"><h5><i class="bi bi-check-circle-fill"></i> Operation Complete!</h5><p>' + escapeHtml(data.message) + '</p>';
        if (data.created && data.created.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-person-plus-fill"></i> Created Accounts (${data.created.length}):</strong>
                <div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="table-success"><tr><th>Name</th><th>Username</th><th>Email</th><th>Password</th><th>Admission No</th></tr></thead><tbody>`;
            data.created.forEach(c => { html += `<tr><td>${escapeHtml(c.name)}</td><td><code>${escapeHtml(c.username)}</code></td><td>${escapeHtml(c.email)}</td><td><code class="bg-light p-1">${escapeHtml(c.password)}</code></td><td>${escapeHtml(c.admissionNo || 'N/A')}</td></tr>`; });
            html += `</tbody></table></div></div>`;
        }
        if (data.reset && data.reset.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-key-fill"></i> Reset Passwords (${data.reset.length}):</strong>
                <div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="table-warning"><tr><th>Name</th><th>Username</th><th>Email</th><th>New Password</th><th>Admission No</th></tr></thead><tbody>`;
            data.reset.forEach(r => { html += `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td><code class="bg-light p-1">${escapeHtml(r.password)}</code></td><td>${escapeHtml(r.admissionNo || 'N/A')}</td></tr>`; });
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

    // MODERN DIV-BASED PRINT FUNCTION WITH CUTTING LINES
    $('#printResults').on('click', function() {
        if (!currentResults) return;
        const printWindow = window.open('', '_blank');
        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'CSS Kabba';
        const today = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

        let credentialsHtml = '';

        if (currentResults.created && currentResults.created.length > 0) {
            currentResults.created.forEach((student, index) => {
                credentialsHtml += `
                    <div class="credential-card">
                        <div class="cutting-line cutting-line-top">
                            <span class="cutting-text">✂ - - - - - - - - - - CUT HERE - - - - - - - - - - ✂</span>
                        </div>
                        <div class="credential-content">
                            <div class="school-header">
                                <div class="school-logo">
                                    <div class="logo-icon">🎓</div>
                                    <h2>${escapeHtml(schoolName)}</h2>
                                </div>
                                <p class="subtitle">STUDENT PORTAL ACCESS CREDENTIALS</p>
                                <div class="issue-info">
                                    <span class="issue-date"><i class="bi bi-calendar3"></i> Issued: ${today}</span>
                                    <span class="credential-id">#${String(index + 1).padStart(3, '0')}</span>
                                </div>
                            </div>
                            <div class="student-details">
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-person-fill"></i> Student Name:</div>
                                    <div class="detail-value"><strong>${escapeHtml(student.name)}</strong></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-upc-scan"></i> Admission Number:</div>
                                    <div class="detail-value">${escapeHtml(student.admissionNo || 'N/A')}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-envelope-fill"></i> Email Address:</div>
                                    <div class="detail-value email-value">${escapeHtml(student.email)}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-person-badge-fill"></i> Username:</div>
                                    <div class="detail-value">${escapeHtml(student.username)}</div>
                                </div>
                                <div class="detail-row highlight">
                                    <div class="detail-label"><i class="bi bi-key-fill"></i> Password:</div>
                                    <div class="detail-value password-value">${escapeHtml(student.password)}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-tag-fill"></i> Role:</div>
                                    <div class="detail-value"><span class="role-badge">Student</span></div>
                                </div>
                            </div>
                            <div class="footer-note">
                                <div class="warning-box">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>Please change your password after first login. Keep this slip safe and confidential.</span>
                                </div>
                                <div class="portal-link">
                                    <i class="bi bi-globe2"></i> Portal Access: ${window.location.origin}
                                </div>
                            </div>
                        </div>
                        <div class="cutting-line cutting-line-bottom">
                            <span class="cutting-text">✂ - - - - - - - - - - CUT HERE - - - - - - - - - - ✂</span>
                        </div>
                    </div>
                `;
            });
        }

        if (currentResults.reset && currentResults.reset.length > 0) {
            currentResults.reset.forEach((student, index) => {
                credentialsHtml += `
                    <div class="credential-card">
                        <div class="cutting-line cutting-line-top">
                            <span class="cutting-text">✂ - - - - - - - - - - CUT HERE - - - - - - - - - - ✂</span>
                        </div>
                        <div class="credential-content reset-card">
                            <div class="school-header">
                                <div class="school-logo">
                                    <div class="logo-icon">🔄</div>
                                    <h2>${escapeHtml(schoolName)}</h2>
                                </div>
                                <p class="subtitle">PASSWORD RESET CONFIRMATION</p>
                                <div class="issue-info">
                                    <span class="issue-date"><i class="bi bi-calendar3"></i> Issued: ${today}</span>
                                    <span class="reset-badge">NEW PASSWORD</span>
                                </div>
                            </div>
                            <div class="student-details">
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-person-fill"></i> Student Name:</div>
                                    <div class="detail-value"><strong>${escapeHtml(student.name)}</strong></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-upc-scan"></i> Admission Number:</div>
                                    <div class="detail-value">${escapeHtml(student.admissionNo || 'N/A')}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-envelope-fill"></i> Email Address:</div>
                                    <div class="detail-value email-value">${escapeHtml(student.email)}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label"><i class="bi bi-person-badge-fill"></i> Username:</div>
                                    <div class="detail-value">${escapeHtml(student.username)}</div>
                                </div>
                                <div class="detail-row highlight">
                                    <div class="detail-label"><i class="bi bi-key-fill"></i> New Password:</div>
                                    <div class="detail-value password-value">${escapeHtml(student.password)}</div>
                                </div>
                            </div>
                            <div class="footer-note">
                                <div class="warning-box">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>This is a newly generated password. Please change it after your first login.</span>
                                </div>
                                <div class="portal-link">
                                    <i class="bi bi-globe2"></i> Portal Access: ${window.location.origin}
                                </div>
                            </div>
                        </div>
                        <div class="cutting-line cutting-line-bottom">
                            <span class="cutting-text">✂ - - - - - - - - - - CUT HERE - - - - - - - - - - ✂</span>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: #eef2f7;
            padding: 30px 20px;
        }
        .print-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Summary Page */
        .summary-page {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        .summary-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .summary-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .summary-header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        .stat-card .stat-number { font-size: 36px; font-weight: bold; }
        .stat-card .stat-label { font-size: 14px; opacity: 0.9; margin-top: 5px; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }

        /* Credential Card */
        .credential-card {
            margin-bottom: 30px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Cutting Lines */
        .cutting-line {
            height: 20px;
            background: repeating-linear-gradient(45deg, #ccc, #ccc 8px, #fff 8px, #fff 16px);
            position: relative;
            margin: 0 -10px;
        }
        .cutting-line-top { border-radius: 8px 8px 0 0; margin-bottom: 10px; }
        .cutting-line-bottom { border-radius: 0 0 8px 8px; margin-top: 10px; }
        .cutting-text {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 0px;
            background: #eef2f7;
            padding: 0 15px;
            font-size: 9px;
            color: #999;
            font-family: monospace;
            white-space: nowrap;
            letter-spacing: 2px;
        }

        /* Credential Content */
        .credential-content {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            border: 1px solid #e8e8e8;
        }
        .reset-card { border-left: 5px solid #f39c12; }

        /* School Header */
        .school-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .school-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .logo-icon { font-size: 40px; }
        .school-logo h2 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .subtitle {
            color: #667eea;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }
        .issue-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #95a5a6;
        }
        .reset-badge {
            background: #f39c12;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }

        /* Student Details - Modern Grid Layout */
        .student-details {
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            width: 140px;
            font-weight: 600;
            color: #7f8c8d;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-label i { font-size: 16px; color: #667eea; width: 20px; }
        .detail-value {
            flex: 1;
            font-size: 14px;
            color: #2c3e50;
        }
        .highlight {
            background: #f0fff4;
            border-radius: 12px;
            margin: 8px 0;
            padding: 8px 0;
        }
        .email-value { color: #2980b9; font-family: monospace; font-size: 13px; }
        .password-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            background: #f0fff4;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .role-badge {
            background: #3498db;
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Footer */
        .footer-note { margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ddd; }
        .warning-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff8e7;
            padding: 12px 15px;
            border-radius: 10px;
            font-size: 11px;
            color: #856404;
            margin-bottom: 12px;
        }
        .warning-box i { font-size: 18px; color: #f39c12; }
        .portal-link {
            text-align: center;
            font-family: monospace;
            font-size: 11px;
            color: #7f8c8d;
        }

        /* Print Styles */
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .credential-card { page-break-after: always; break-after: page; margin-bottom: 0; }
            .credential-card:last-child { page-break-after: auto; break-after: auto; }
            .cutting-line { background: repeating-linear-gradient(45deg, #aaa, #aaa 6px, #fff 6px, #fff 12px); }
            .cutting-text, .password-value, .stat-card, .warning-box { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }

        @media (max-width: 600px) {
            .detail-row { flex-direction: column; align-items: flex-start; gap: 5px; }
            .detail-label { width: 100%; }
            .credential-content { padding: 18px; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="summary-page">
            <div class="summary-header">
                <h1>🏫 Student Credentials Report</h1>
                <p class="subtitle">CSS KABBA - STUDENT PORTAL ACCESS</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${(currentResults.created?.length || 0) + (currentResults.reset?.length || 0)}</div><div class="stat-label">Total Students</div></div>
                <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);"><div class="stat-number">${currentResults.created?.length || 0}</div><div class="stat-label">New Accounts</div></div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);"><div class="stat-number">${currentResults.reset?.length || 0}</div><div class="stat-label">Password Resets</div></div>
                <div class="stat-card" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);"><div class="stat-number">${currentResults.skipped?.length || 0}</div><div class="stat-label">Skipped</div></div>
            </div>
            <div class="info-row">
                <span><i class="bi bi-envelope-fill"></i> Email Domain: @csskabba.ng</span>
                <span><i class="bi bi-tag-fill"></i> Role: Student Only</span>
                <span><i class="bi bi-calendar3"></i> Generated: ${today}</span>
            </div>
            <p style="margin-top: 20px; font-size: 12px; color: #666; text-align: center;">
                <i class="bi bi-scissors"></i> Each student's credential slip can be cut along the dashed lines.
            </p>
        </div>
        ${credentialsHtml}
        <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #999; padding: 20px;">
            <p>This is an official document generated by CSS Kabba School Management System.</p>
            <p>Please keep these credentials secure and confidential.</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); setTimeout(function() { window.close(); }, 1500); }, 500);
        };
    </script>
</body>
</html>`;
        printWindow.document.write(printHtml);
        printWindow.document.close();
    });

    $('#newAction, #massStudentModal').on('hidden.bs.modal', function() {
        selectedStudents = []; currentResults = null;
        $('#massStep2, #massStep3').hide(); $('#massStep1').show();
        $('#selectedAction').val(''); $('.action-card').css('border', '2px solid #e0e0e0').css('background', 'white');
        loadStudents();
    });

    $('#massStudentModal').on('show.bs.modal', function() { selectedStudents = []; loadStudents(); });

    function escapeHtml(str) { if (!str) return ''; return String(str).replace(/[&<>]/g, function(m) { if (m === '&') return '&amp;'; if (m === '<') return '&lt;'; if (m === '>') return '&gt;'; return m; }); }
});
</script>

<style>
.action-card { transition: all 0.3s ease; }
.action-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.sticky-top { position: sticky; top: 0; z-index: 10; background: white; }
.form-check-input:disabled { cursor: not-allowed; opacity: 0.5; }
</style>
