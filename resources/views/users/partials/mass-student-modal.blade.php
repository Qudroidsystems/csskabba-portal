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
                                        <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2"></div>Loading students...</td></tr>
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
                                    <li><strong>Create Accounts</strong> - Creates new accounts for students WITHOUT accounts</li>
                                    <li><strong>Reset Passwords</strong> - Generates new passwords for students WITH accounts</li>
                                    <li><strong>Revoke Accounts</strong> - Removes user access (student record remains)</li>
                                    <li><strong>Reprint Credentials</strong> - Shows existing credentials (passwords hidden)</li>
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
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-check-square-fill me-1"></i> Selected Students (<span id="step2SelectedCount">0</span>)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 200px;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>Student Name</th><th>Admission No</th><th>Status</th><th>Generated Email</th></tr></thead>
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
                                <div class="col-md-3"><div class="card action-card" data-action="create" style="cursor:pointer;border:2px solid #e0e0e0;"><div class="card-body text-center"><i class="bi bi-person-plus-fill fs-1 text-success"></i><h6 class="mt-2 mb-0">Create Accounts</h6><small class="text-muted">For students without accounts</small></div></div></div>
                                <div class="col-md-3"><div class="card action-card" data-action="reset" style="cursor:pointer;border:2px solid #e0e0e0;"><div class="card-body text-center"><i class="bi bi-key-fill fs-1 text-warning"></i><h6 class="mt-2 mb-0">Reset Passwords</h6><small class="text-muted">New password for existing</small></div></div></div>
                                <div class="col-md-3"><div class="card action-card" data-action="revoke" style="cursor:pointer;border:2px solid #e0e0e0;"><div class="card-body text-center"><i class="bi bi-person-x-fill fs-1 text-danger"></i><h6 class="mt-2 mb-0">Revoke Accounts</h6><small class="text-muted">Remove user access</small></div></div></div>
                                <div class="col-md-3"><div class="card action-card" data-action="reprint" style="cursor:pointer;border:2px solid #e0e0e0;"><div class="card-body text-center"><i class="bi bi-printer-fill fs-1 text-info"></i><h6 class="mt-2 mb-0">Reprint Credentials</h6><small class="text-muted">Print without password</small></div></div></div>
                            </div>
                            <input type="hidden" id="selectedAction" value="">
                        </div>
                    </div>

                    <div id="passwordSettings" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-lock-fill me-1"></i> Password Settings</h6></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Password Type</label>
                                        <div class="d-flex gap-4 mt-2">
                                            <div class="form-check"><input type="radio" id="passwordTypeIndividual" name="passwordTypeRadio" value="individual" class="form-check-input" checked><label class="form-check-label" for="passwordTypeIndividual"><strong>Individual Random Passwords</strong><br><small class="text-muted">Each student gets a unique random password</small></label></div>
                                            <div class="form-check"><input type="radio" id="passwordTypeSame" name="passwordTypeRadio" value="same" class="form-check-input"><label class="form-check-label" for="passwordTypeSame"><strong>Same Password for All</strong><br><small class="text-muted">All selected students get the same password</small></label></div>
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
                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-tags-fill me-1"></i> Assign Role</h6></div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-3"><i class="bi bi-shield-exclamation me-2"></i><strong>Security Notice:</strong> Student accounts can only have the <strong>"Student"</strong> role.</div>
                                @php
                                    $allRoles = Spatie\Permission\Models\Role::all();
                                    $studentRole = $allRoles->where('name', 'Student')->first();
                                    $otherRoles = $allRoles->where('name', '!=', 'Student');
                                @endphp
                                @if($studentRole)
                                    <div class="card border-success mb-3"><div class="card-body bg-success bg-opacity-10"><div class="form-check"><input type="checkbox" class="form-check-input" name="roles[]" value="{{ $studentRole->name }}" id="role_{{ $studentRole->name }}" checked disabled><label class="form-check-label fw-bold text-success fs-5" for="role_{{ $studentRole->name }}"><i class="bi bi-person-badge-fill me-2"></i>{{ $studentRole->name }}<span class="badge bg-success ms-2">Default</span></label><p class="text-muted mt-2 mb-0 ms-4"><i class="bi bi-check-circle-fill text-success me-1"></i>Automatically assigned to all student accounts.</p></div></div></div>
                                @endif
                                @if($otherRoles->count() > 0)
                                    <hr><p class="text-muted mb-2"><i class="bi bi-shield-lock-fill me-1"></i> The following roles cannot be assigned to student accounts:</p>
                                    <div class="row">@foreach($otherRoles as $role)<div class="col-md-4 mb-2"><div class="card bg-light"><div class="card-body py-2"><div class="form-check"><input type="checkbox" class="form-check-input" value="{{ $role->name }}" id="role_{{ $role->name }}" disabled><label class="form-check-label text-muted" for="role_{{ $role->name }}"><i class="bi bi-shield-lock-fill me-1"></i>{{ $role->name }}<span class="badge bg-secondary ms-1">Disabled</span></label></div><small class="text-danger d-block mt-1">Cannot assign {{ $role->name }} role to student</small></div></div></div>@endforeach</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="actionWarning" class="alert alert-warning" style="display: none;"></div>
                    <div class="alert alert-info" id="emailFormatNote" style="display: none;"><i class="bi bi-envelope-fill me-2"></i><strong>Email Format:</strong> firstname.lastname@csskabba.ng</div>

                    <div class="d-flex justify-content-between"><button type="button" class="btn btn-secondary" id="backToStep1"><i class="bi bi-arrow-left"></i> Back</button><button type="button" class="btn btn-success btn-lg" id="executeAction"><i class="bi bi-check-circle"></i> Execute Action</button></div>
                </div>

                <!-- ==================== STEP 3: RESULTS ==================== -->
                <div id="massStep3" style="display: none;">
                    <div id="resultsContainer"></div>
                    <div class="d-flex justify-content-between mt-4"><button type="button" class="btn btn-secondary" id="newAction"><i class="bi bi-plus-circle"></i> New Action</button><button type="button" class="btn btn-primary" id="printResults"><i class="bi bi-printer"></i> Print Results</button></div>
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
                    if (data.classes && $('#massClassFilter option').length <= 1) {
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

    function renderStudentTable() {
        if (!allStudents.length) {
            $('#massStudentList').html('<tr><td colspan="6" class="text-center">No students found</td></tr>');
            $('#massSelectedCount').text('0 selected');
            return;
        }

        let html = '';
        allStudents.forEach(student => {
            const isSelected = selectedStudents.some(s => s.id === student.id);
            const statusBadge = student.has_account ? '<span class="badge bg-success">Has Account</span>' : '<span class="badge bg-secondary">No Account</span>';
            html += `<tr>
                        <td><input type="checkbox" class="student-checkbox" data-id="${student.id}" ${isSelected ? 'checked' : ''}></td>
                        <td><strong>${escapeHtml(student.admissionNo || 'N/A')}</strong></td>
                        <td>${escapeHtml(student.name)}</td>
                        <td>${escapeHtml(student.class_name || 'N/A')} ${student.arm_name ? '/' + escapeHtml(student.arm_name) : ''}</td>
                        <td>${statusBadge}</td>
                        <td><small class="text-muted">${escapeHtml(student.generatedEmail)}</small></td>
                    </tr>`;
        });
        $('#massStudentList').html(html);
        updateSelectedCount();
        $('.student-checkbox').off('change').on('change', function() {
            const studentId = parseInt($(this).data('id'));
            const student = allStudents.find(s => s.id === studentId);
            if ($(this).is(':checked')) {
                if (!selectedStudents.some(s => s.id === studentId)) selectedStudents.push(student);
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

    function populateFilters(classes, arms, classArms) {
        window.classArmsData = classArms || [];

        // Populate Class dropdown with UNIQUE classes
        let classHtml = '<option value="">All Classes</option>';
        classes.forEach(cls => {
            classHtml += `<option value="${cls.id}">${escapeHtml(cls.name)}</option>`;
        });
        $('#massClassFilter').html(classHtml);

        // Populate Arm dropdown
        let armHtml = '<option value="">All Arms</option>';
        arms.forEach(arm => {
            armHtml += `<option value="${arm.id}">${escapeHtml(arm.name)}</option>`;
        });
        $('#massArmFilter').html(armHtml);

        // Class change event - filter arms based on selected class
        $('#massClassFilter').off('change').on('change', function() {
            const selectedClassId = $(this).val();

            if (!selectedClassId) {
                $('#massArmFilter option').show();
                $('#massArmFilter').val('');
            } else {
                const relatedArmIds = window.classArmsData
                    .filter(ca => String(ca.class_id) === String(selectedClassId))
                    .map(ca => String(ca.arm_id));

                $('#massArmFilter option').each(function() {
                    const optionValue = $(this).val();
                    if (optionValue && !relatedArmIds.includes(optionValue)) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });

                const currentArm = $('#massArmFilter').val();
                if (currentArm && !relatedArmIds.includes(currentArm)) {
                    $('#massArmFilter').val('');
                }
            }
            applyFilters();
        });
    }

    function applyFilters() {
        const search = $('#massStudentSearch').val();
        const classId = $('#massClassFilter').val();
        const armId = $('#massArmFilter').val();
        const accountStatus = $('#massAccountStatus').val();

        let filtered = [...allStudents];
        if (search) { const s = search.toLowerCase(); filtered = filtered.filter(st => st.name.toLowerCase().includes(s) || (st.admissionNo || '').toLowerCase().includes(s)); }
        if (classId) filtered = filtered.filter(st => String(st.class_id) === classId);
        if (armId) filtered = filtered.filter(st => String(st.arm_id) === armId);
        if (accountStatus === 'yes') filtered = filtered.filter(st => st.has_account);
        else if (accountStatus === 'no') filtered = filtered.filter(st => !st.has_account);

        renderFilteredTable(filtered);
    }

    function renderFilteredTable(students) {
        if (!students.length) { $('#massStudentList').html('<tr><td colspan="6" class="text-center">No students found</td></tr>'); return; }
        let html = '';
        students.forEach(student => {
            const isSelected = selectedStudents.some(s => s.id === student.id);
            const statusBadge = student.has_account ? '<span class="badge bg-success">Has Account</span>' : '<span class="badge bg-secondary">No Account</span>';
            html += `<tr>
                        <td><input type="checkbox" class="student-checkbox" data-id="${student.id}" ${isSelected ? 'checked' : ''}></td>
                        <td><strong>${escapeHtml(student.admissionNo || 'N/A')}</strong></td>
                        <td>${escapeHtml(student.name)}</td>
                        <td>${escapeHtml(student.class_name || 'N/A')} ${student.arm_name ? '/' + escapeHtml(student.arm_name) : ''}</td>
                        <td>${statusBadge}</td>
                        <td><small class="text-muted">${escapeHtml(student.generatedEmail)}</small></td>
                    </tr>`;
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

    $('#massStudentSearch, #massClassFilter, #massArmFilter, #massAccountStatus').on('input change', function() { selectedStudents = []; applyFilters(); });
    $('#selectAllStudents, #selectAllCheckbox').on('click', function() { const isChecked = $(this).prop('checked'); selectedStudents = isChecked ? [...allStudents] : []; renderFilteredTable([...allStudents]); });

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
        if (action === 'create' || action === 'reset') { $('#passwordSettings').show(); $('#roleSettings').show(); $('#emailFormatNote').show(); }
        else { $('#passwordSettings').hide(); $('#roleSettings').hide(); $('#emailFormatNote').hide(); }

        const hasAccount = selectedStudents.filter(s => s.has_account).length;
        const noAccount = selectedStudents.filter(s => !s.has_account).length;
        let warningHtml = '';
        if (action === 'create' && hasAccount > 0) warningHtml = `${hasAccount} selected student(s) already have accounts and will be skipped.`;
        else if (action === 'reset' && noAccount > 0) warningHtml = `${noAccount} selected student(s) don't have accounts and will be skipped.`;
        else if (action === 'revoke' && noAccount > 0) warningHtml = `${noAccount} selected student(s) don't have accounts and will be skipped.`;
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
        fetch('{{ route("users.mass-create-students") }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(payload) })
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
            html += `<div class="mt-3"><strong><i class="bi bi-person-plus-fill"></i> Created Accounts (${data.created.length}):</strong><div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="table-success"><tr><th>Name</th><th>Username</th><th>Email</th><th>Password</th><th>Admission No</th></tr></thead><tbody>`;
            data.created.forEach(c => { html += `<tr><td>${escapeHtml(c.name)}</td><td><code>${escapeHtml(c.username)}</code></td><td>${escapeHtml(c.email)}</td><td><code>${escapeHtml(c.password)}</code></td><td>${escapeHtml(c.admissionNo || 'N/A')}</td></tr>`; });
            html += `</tbody></table></div></div>`;
        }
        if (data.reset && data.reset.length > 0) {
            html += `<div class="mt-3"><strong><i class="bi bi-key-fill"></i> Reset Passwords (${data.reset.length}):</strong><div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="table-warning"><tr><th>Name</th><th>Username</th><th>Email</th><th>New Password</th><th>Admission No</th></tr></thead><tbody>`;
            data.reset.forEach(r => { html += `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td><code>${escapeHtml(r.password)}</code></td><td>${escapeHtml(r.admissionNo || 'N/A')}</td></tr>`; });
            html += `</tbody></table></div></div>`;
        }
        if (data.revoked && data.revoked.length > 0) { html += `<div class="mt-3"><strong><i class="bi bi-person-x-fill"></i> Revoked Accounts (${data.revoked.length}):</strong><ul>`; data.revoked.forEach(r => { html += `<li>${escapeHtml(r.name)} (${escapeHtml(r.admissionNo || 'N/A')}) - Account removed</li>`; }); html += `</ul></div>`; }
        if (data.reprinted && data.reprinted.length > 0) { html += `<div class="mt-3"><strong><i class="bi bi-printer-fill"></i> Reprinted Credentials (${data.reprinted.length}):</strong><div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="table-info"><tr><th>Name</th><th>Username</th><th>Email</th><th>Admission No</th><th>Note</th></tr></thead><tbody>`; data.reprinted.forEach(r => { html += `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td>${escapeHtml(r.admissionNo || 'N/A')}</td><td><small>Password hidden for security</small></td></tr>`; }); html += `</tbody></table></div></div>`; }
        if (data.skipped && data.skipped.length > 0) { html += `<div class="mt-3 text-warning"><strong><i class="bi bi-skip-forward-fill"></i> Skipped (${data.skipped.length}):</strong><ul>`; data.skipped.forEach(s => html += `<li>${escapeHtml(s)}</li>`); html += `</ul></div>`; }
        html += `</div>`;
        $('#resultsContainer').html(html);
    }

    // Print function with beautiful DIV-based layout
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
                        <div class="cutting-line-top">
                            <span class="cutting-text">✂ - - - - - - - - - - CUT HERE - - - - - - - - - - ✂</span>
                        </div>
                        <div class="credential-content">
                            <div class="school-header">
                                <div class="school-logo">🎓</div>
                                <h2>${escapeHtml(schoolName)}</h2>
                                <p class="subtitle">STUDENT PORTAL ACCESS CREDENTIALS</p>
                                <div class="issue-date">Issued: ${today}</div>
                            </div>
                            <div class="info-grid">
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-person-circle"></i> Student Name:</div>
                                    <div class="info-value"><strong>${escapeHtml(student.name)}</strong></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-upc-scan"></i> Admission Number:</div>
                                    <div class="info-value">${escapeHtml(student.admissionNo || 'N/A')}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-envelope"></i> Login Email:</div>
                                    <div class="info-value email-value">${escapeHtml(student.email)}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-person-badge"></i> Username:</div>
                                    <div class="info-value">${escapeHtml(student.username)}</div>
                                </div>
                                <div class="info-row password-row">
                                    <div class="info-label"><i class="bi bi-key"></i> Password:</div>
                                    <div class="info-value password-value">${escapeHtml(student.password)}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-tag"></i> Role:</div>
                                    <div class="info-value"><span class="role-badge">Student</span></div>
                                </div>
                            </div>
                            <div class="footer-note">
                                <p><i class="bi bi-shield-check"></i> Please change your password after first login for security.</p>
                                <p class="portal-link"><i class="bi bi-globe2"></i> Portal: ${window.location.origin}</p>
                            </div>
                        </div>
                        <div class="cutting-line-bottom">
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
                        <div class="cutting-line-top">
                            <span class="cutting-text">✂ - - - - - - - - - - CUT HERE - - - - - - - - - - ✂</span>
                        </div>
                        <div class="credential-content reset-card">
                            <div class="school-header">
                                <div class="school-logo">🔄</div>
                                <h2>${escapeHtml(schoolName)}</h2>
                                <p class="subtitle">PASSWORD RESET CONFIRMATION</p>
                                <div class="issue-date">Issued: ${today}</div>
                            </div>
                            <div class="info-grid">
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-person-circle"></i> Student Name:</div>
                                    <div class="info-value"><strong>${escapeHtml(student.name)}</strong></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-upc-scan"></i> Admission Number:</div>
                                    <div class="info-value">${escapeHtml(student.admissionNo || 'N/A')}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-envelope"></i> Login Email:</div>
                                    <div class="info-value email-value">${escapeHtml(student.email)}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-person-badge"></i> Username:</div>
                                    <div class="info-value">${escapeHtml(student.username)}</div>
                                </div>
                                <div class="info-row password-row">
                                    <div class="info-label"><i class="bi bi-key"></i> New Password:</div>
                                    <div class="info-value password-value">${escapeHtml(student.password)}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-tag"></i> Role:</div>
                                    <div class="info-value"><span class="role-badge">Student</span></div>
                                </div>
                            </div>
                            <div class="footer-note">
                                <p><i class="bi bi-shield-check"></i> This is a newly generated password. Please change after login.</p>
                                <p class="portal-link"><i class="bi bi-globe2"></i> Portal: ${window.location.origin}</p>
                            </div>
                        </div>
                        <div class="cutting-line-bottom">
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
        body { font-family: 'Segoe UI', 'Roboto', Arial, sans-serif; background: #eef2f5; padding: 30px; }
        .print-container { max-width: 850px; margin: 0 auto; }

        .summary-page {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }
        .summary-page h3 { color: #2c3e50; margin-bottom: 15px; font-size: 22px; }
        .summary-stats { display: flex; gap: 20px; flex-wrap: wrap; margin: 15px 0; }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            min-width: 150px;
            text-align: center;
        }
        .stat-card .number { font-size: 28px; font-weight: bold; }
        .stat-card .label { font-size: 12px; opacity: 0.9; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .summary-table th, .summary-table td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        .summary-table th { background: #e9ecef; font-weight: 600; }

        .credential-card { margin-bottom: 25px; page-break-inside: avoid; break-inside: avoid; }
        .cutting-line-top, .cutting-line-bottom {
            height: 20px;
            background: repeating-linear-gradient(45deg, #ccc, #ccc 8px, #fff 8px, #fff 16px);
            position: relative;
            margin: 0 -5px;
        }
        .cutting-line-top { border-radius: 10px 10px 0 0; margin-bottom: 10px; }
        .cutting-line-bottom { border-radius: 0 0 10px 10px; margin-top: 10px; }
        .cutting-text {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: -2px;
            background: white;
            padding: 0 15px;
            font-size: 10px;
            color: #999;
            font-family: monospace;
            white-space: nowrap;
            letter-spacing: 2px;
        }

        .credential-content {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        .reset-card { border-left: 5px solid #f39c12; }

        .school-header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #ecf0f1; }
        .school-logo { font-size: 40px; margin-bottom: 5px; }
        .school-header h2 { color: #2c3e50; font-size: 22px; font-weight: 700; }
        .school-header .subtitle { color: #667eea; font-size: 11px; font-weight: 600; letter-spacing: 2px; margin-top: 5px; }
        .issue-date { color: #95a5a6; font-size: 10px; margin-top: 8px; }

        .info-grid { margin-bottom: 20px; }
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px dashed #ecf0f1; }
        .info-label { width: 35%; font-weight: 600; color: #34495e; display: flex; align-items: center; gap: 8px; }
        .info-label i { color: #667eea; font-size: 16px; }
        .info-value { width: 65%; color: #2c3e50; word-break: break-word; }
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 25px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .footer-note { text-align: center; padding-top: 15px; border-top: 1px dashed #ddd; font-size: 10px; color: #7f8c8d; }
        .footer-note i { color: #e74c3c; margin-right: 4px; }
        .portal-link { font-family: monospace; margin-top: 8px; color: #667eea; font-weight: 500; }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .credential-card { page-break-after: always; break-after: page; margin-bottom: 0; }
            .credential-card:last-child { page-break-after: auto; break-after: auto; }
            .cutting-line-top, .cutting-line-bottom { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .password-value { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .role-badge { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .stat-card { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="summary-page">
            <h3><i class="bi bi-file-text-fill"></i> Credentials Summary</h3>
            <div class="summary-stats">
                <div class="stat-card"><div class="number">${(currentResults.created?.length || 0) + (currentResults.reset?.length || 0)}</div><div class="label">Total Students</div></div>
                <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);"><div class="number">${currentResults.created?.length || 0}</div><div class="label">Newly Created</div></div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);"><div class="number">${currentResults.reset?.length || 0}</div><div class="label">Password Resets</div></div>
                <div class="stat-card" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);"><div class="number">${currentResults.skipped?.length || 0}</div><div class="label">Skipped</div></div>
            </div>
            <table class="summary-table">
                <thead><tr><th>Category</th><th>Count</th></tr></thead>
                <tbody><tr><td>✅ Newly Created Accounts</td><td>${currentResults.created?.length || 0}</td><tr>
                            <td>🔄 Password Resets</td><td>${currentResults.reset?.length || 0}</td>
                        </tr>
                        <tr><td>⏭️ Skipped</td><td>${currentResults.skipped?.length || 0}</td></tr>
                </tbody>
            </table>
            <p style="margin-top: 15px; font-size: 11px; color: #666;"><i class="bi bi-scissors"></i> Each student's credential slip can be cut along the dashed lines.</p>
        </div>
        ${credentialsHtml}
        <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #999; padding: 20px;">
            <p>This is an official document generated by CSS Kabba School Management System.</p>
            <p>Please keep these credentials secure.</p>
        </div>
    </div>
    <script>window.onload = function() { setTimeout(function() { window.print(); setTimeout(function() { window.close(); }, 1000); }, 500); };<\/script>
</body>
</html>`;
        printWindow.document.write(printHtml);
        printWindow.document.close();
    });

    $('#newAction, #massStudentModal').on('hidden.bs.modal', function() {
        selectedStudents = []; currentResults = null; $('#massStep2, #massStep3').hide(); $('#massStep1').show(); $('#selectedAction').val('');
        $('.action-card').css('border', '2px solid #e0e0e0').css('background', 'white'); loadStudents();
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
