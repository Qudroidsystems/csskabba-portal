<!-- Mass Student Creation/Management Modal -->
<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Student Account Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Filter and Select Students -->
                <div id="step1">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Search</label>
                            <input type="text" id="mass-search" class="form-control" placeholder="Name or Admission No...">
                        </div>
                        <div class="col-md-3">
                            <label>Class</label>
                            <select id="mass-class-filter" class="form-control">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Arm</label>
                            <select id="mass-arm-filter" class="form-control">
                                <option value="">All Arms</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary w-100" id="apply-filters">Apply Filters</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-students"></th>
                                    <th>Admission No</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="students-list">
                                <tr><td colspan="6" class="text-center">Loading students...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success" id="proceed-to-step2" disabled>
                            Proceed (<span id="selected-count">0</span> selected)
                        </button>
                    </div>
                </div>

                <!-- Step 2: Choose Action -->
                <div id="step2" style="display: none;">
                    <h5>Selected Students: <span id="step2-selected-count"></span></h5>
                    <div class="alert alert-info">
                        <strong>Note:</strong> Students with existing accounts can have their passwords reset or credentials reprinted.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Choose Action Mode</h6>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="action-mode" id="action-create" value="create" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary" for="action-create">
                                            <i class="bi bi-person-plus"></i> Create Accounts<br>
                                            <small>For students without accounts</small>
                                        </label>

                                        <input type="radio" class="btn-check" name="action-mode" id="action-reset" value="reset" autocomplete="off">
                                        <label class="btn btn-outline-warning" for="action-reset">
                                            <i class="bi bi-key"></i> Reset Passwords<br>
                                            <small>For students with existing accounts</small>
                                        </label>

                                        <input type="radio" class="btn-check" name="action-mode" id="action-reprint" value="reprint" autocomplete="off">
                                        <label class="btn btn-outline-info" for="action-reprint">
                                            <i class="bi bi-printer"></i> Reprint Only<br>
                                            <small>Print existing credentials</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="create-reset-options" style="display: block;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Password Type</label>
                                <select id="password-type" class="form-control">
                                    <option value="same">Same Password for All</option>
                                    <option value="individual">Individual Random Passwords</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="shared-password-div">
                                <label>Shared Password</label>
                                <div class="input-group">
                                    <input type="text" id="shared-password" class="form-control" placeholder="Enter password">
                                    <button type="button" class="btn btn-secondary" id="generate-shared-password">Generate</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning" id="revoke-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Note:</strong> Resetting passwords will change existing passwords. Students will need to use the new password.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button class="btn btn-secondary" id="back-to-step1">Back</button>
                        <button class="btn btn-primary" id="process-students">Process Selected Students</button>
                    </div>
                </div>

                <!-- Step 3: Results -->
                <div id="step3" style="display: none;">
                    <div id="results-container"></div>
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-secondary" id="start-over">Start Over</button>
                        <button class="btn btn-primary" id="print-credentials" style="display: none;">Print Credentials</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .student-row.selected {
        background-color: #e3f2fd !important;
    }
    .badge-has-account {
        background-color: #28a745;
    }
    .badge-no-account {
        background-color: #dc3545;
    }
    .credential-slip {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        page-break-after: always;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        .credential-slip {
            border: none;
            page-break-after: always;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedStudents = [];
    let allStudents = [];
    let processedResults = null;

    const massModal = document.getElementById('massStudentModal');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const studentsList = document.getElementById('students-list');
    const selectAllCheckbox = document.getElementById('select-all-students');
    const selectedCountSpan = document.getElementById('selected-count');
    const proceedBtn = document.getElementById('proceed-to-step2');
    const backBtn = document.getElementById('back-to-step1');
    const processBtn = document.getElementById('process-students');
    const startOverBtn = document.getElementById('start-over');
    const printBtn = document.getElementById('print-credentials');

    let classFilter = '';
    let armFilter = '';
    let searchTerm = '';

    // Load students when modal opens
    massModal.addEventListener('show.bs.modal', function() {
        loadStudents();
    });

    // Filter events
    document.getElementById('mass-class-filter').addEventListener('change', function() {
        classFilter = this.value;
        loadStudents();
    });

    document.getElementById('mass-arm-filter').addEventListener('change', function() {
        armFilter = this.value;
        loadStudents();
    });

    document.getElementById('mass-search').addEventListener('input', debounce(function(e) {
        searchTerm = e.target.value;
        loadStudents();
    }, 400));

    document.getElementById('apply-filters').addEventListener('click', function() {
        loadStudents();
    });

    function loadStudents() {
        let url = '{{ route("get.students") }}?limit=1000';
        if (classFilter) url += `&class_id=${classFilter}`;
        if (armFilter) url += `&arm_id=${armFilter}`;
        if (searchTerm) url += `&search=${encodeURIComponent(searchTerm)}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    allStudents = data.students;
                    renderStudentsList(allStudents);

                    // Populate filters if empty
                    if (document.getElementById('mass-class-filter').options.length <= 1) {
                        data.classes.forEach(cls => {
                            const opt = document.createElement('option');
                            opt.value = cls.id;
                            opt.textContent = cls.name;
                            document.getElementById('mass-class-filter').appendChild(opt);
                        });
                    }

                    if (document.getElementById('mass-arm-filter').options.length <= 1) {
                        data.arms.forEach(arm => {
                            const opt = document.createElement('option');
                            opt.value = arm.id;
                            opt.textContent = arm.name;
                            document.getElementById('mass-arm-filter').appendChild(opt);
                        });
                    }
                } else {
                    studentsList.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load students</td></tr>';
                }
            })
            .catch(() => {
                studentsList.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>';
            });
    }

    function renderStudentsList(students) {
        if (!students.length) {
            studentsList.innerHTML = '<tr><td colspan="6" class="text-center">No students found</td></tr>';
            return;
        }

        studentsList.innerHTML = students.map(student => `
            <tr class="student-row ${selectedStudents.some(s => s.id === student.id) ? 'selected' : ''}" data-id="${student.id}">
                <td><input type="checkbox" class="student-checkbox" value="${student.id}" ${selectedStudents.some(s => s.id === student.id) ? 'checked' : ''}></td>
                <td>${student.admissionNo}</td>
                <td>${student.name}</td>
                <td>${student.class_name || 'N/A'} ${student.arm_name ? '(' + student.arm_name + ')' : ''}</td>
                <td><span class="badge ${student.has_account ? 'badge-has-account' : 'badge-no-account'}">${student.has_account ? 'Has Account' : 'No Account'}</span></td>
                <td>
                    ${student.has_account ?
                        `<button class="btn btn-sm btn-warning revoke-single" data-id="${student.id}" data-name="${student.name}">Revoke Access</button>
                         <button class="btn btn-sm btn-info print-single" data-id="${student.id}" data-name="${student.name}">Print</button>` :
                        ''
                    }
                </td>
            </tr>
        `).join('');

        // Add event listeners for checkboxes
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const studentId = parseInt(this.value);
                const student = allStudents.find(s => s.id === studentId);

                if (this.checked) {
                    if (!selectedStudents.some(s => s.id === studentId)) {
                        selectedStudents.push(student);
                    }
                } else {
                    selectedStudents = selectedStudents.filter(s => s.id !== studentId);
                }

                updateSelectedCount();
                updateRowSelection();
            });
        });

        // Add revoke single buttons
        document.querySelectorAll('.revoke-single').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = parseInt(this.dataset.id);
                const studentName = this.dataset.name;

                Swal.fire({
                    title: 'Revoke Access?',
                    html: `Are you sure you want to revoke portal access for <strong>${studentName}</strong>?<br><br>This will remove their user account. They will need to be re-added to access the portal.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Revoke Access',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) {
                        revokeAccounts([studentId]);
                    }
                });
            });
        });

        // Add print single buttons
        document.querySelectorAll('.print-single').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = parseInt(this.dataset.id);
                const student = allStudents.find(s => s.id === studentId);
                if (student) {
                    printSingleCredential(student);
                }
            });
        });

        selectAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                selectedStudents = [...allStudents];
            } else {
                selectedStudents = [];
            }
            updateSelectedCount();
            updateRowSelection();

            document.querySelectorAll('.student-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    function updateSelectedCount() {
        selectedCountSpan.textContent = selectedStudents.length;
        proceedBtn.disabled = selectedStudents.length === 0;
    }

    function updateRowSelection() {
        document.querySelectorAll('.student-row').forEach(row => {
            const id = parseInt(row.dataset.id);
            if (selectedStudents.some(s => s.id === id)) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
    }

    // Action mode handling
    const actionCreate = document.getElementById('action-create');
    const actionReset = document.getElementById('action-reset');
    const actionReprint = document.getElementById('action-reprint');
    const createResetOptions = document.getElementById('create-reset-options');
    const revokeWarning = document.getElementById('revoke-warning');

    function updateActionUI() {
        if (actionCreate.checked) {
            createResetOptions.style.display = 'block';
            revokeWarning.style.display = 'none';
        } else if (actionReset.checked) {
            createResetOptions.style.display = 'block';
            revokeWarning.style.display = 'block';
        } else if (actionReprint.checked) {
            createResetOptions.style.display = 'none';
            revokeWarning.style.display = 'none';
        }
    }

    actionCreate.addEventListener('change', updateActionUI);
    actionReset.addEventListener('change', updateActionUI);
    actionReprint.addEventListener('change', updateActionUI);

    // Password type handling
    const passwordType = document.getElementById('password-type');
    const sharedPasswordDiv = document.getElementById('shared-password-div');

    passwordType.addEventListener('change', function() {
        sharedPasswordDiv.style.display = this.value === 'same' ? 'block' : 'none';
    });

    document.getElementById('generate-shared-password').addEventListener('click', function() {
        const password = generateRandomPassword();
        document.getElementById('shared-password').value = password;
    });

    function generateRandomPassword() {
        return strtoupper(Str::random(4)) + Math.floor(100 + Math.random() * 900) + strtolower(Str::random(3));
    }

    // Proceed to step 2
    proceedBtn.addEventListener('click', function() {
        if (selectedStudents.length === 0) return;

        document.getElementById('step2-selected-count').textContent = selectedStudents.length;

        // Pre-select appropriate action based on selected students
        const hasExisting = selectedStudents.some(s => s.has_account);
        const hasNew = selectedStudents.some(s => !s.has_account);

        if (hasNew && !hasExisting) {
            actionCreate.checked = true;
        } else if (hasExisting && !hasNew) {
            actionReset.checked = true;
        }

        updateActionUI();

        step1.style.display = 'none';
        step2.style.display = 'block';
        step3.style.display = 'none';
    });

    // Back to step 1
    backBtn.addEventListener('click', function() {
        step1.style.display = 'block';
        step2.style.display = 'none';
        step3.style.display = 'none';
    });

    // Process students
    processBtn.addEventListener('click', function() {
        const actionMode = document.querySelector('input[name="action-mode"]:checked').value;
        const passwordType = document.getElementById('password-type').value;
        const sharedPassword = document.getElementById('shared-password').value;

        // Validate based on action
        if (actionMode !== 'reprint' && passwordType === 'same' && !sharedPassword) {
            Swal.fire('Error', 'Please enter a shared password or select individual passwords', 'error');
            return;
        }

        if (actionMode === 'reprint') {
            // Just reprint selected students' credentials
            const reprintData = selectedStudents.map(s => ({
                student_id: s.id,
                action: 'reprint'
            }));

            submitMassAction({
                students: reprintData,
                action_mode: 'reprint'
            });
        } else {
            // Filter students based on action
            let studentsToProcess = [];

            if (actionMode === 'create') {
                studentsToProcess = selectedStudents.filter(s => !s.has_account).map(s => ({
                    student_id: s.id,
                    action: 'create'
                }));
            } else if (actionMode === 'reset') {
                studentsToProcess = selectedStudents.filter(s => s.has_account).map(s => ({
                    student_id: s.id,
                    action: 'reset'
                }));
            }

            if (studentsToProcess.length === 0) {
                Swal.fire('Notice', `No ${actionMode === 'create' ? 'students without accounts' : 'students with accounts'} selected`, 'info');
                return;
            }

            submitMassAction({
                students: studentsToProcess,
                action_mode: actionMode,
                password_type: passwordType,
                shared_password: passwordType === 'same' ? sharedPassword : null
            });
        }
    });

    function submitMassAction(data) {
        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                processedResults = data;
                displayResults(data);

                step1.style.display = 'none';
                step2.style.display = 'none';
                step3.style.display = 'block';
            } else {
                Swal.fire('Error', data.message || 'Operation failed', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Network error occurred', 'error');
        });
    }

    function displayResults(data) {
        const container = document.getElementById('results-container');

        let html = '<div class="alert alert-success">';
        html += `<h5>Operation Complete</h5>`;
        html += `<p>${data.message}</p>`;
        if (data.created_count) html += `<p><strong>Created:</strong> ${data.created_count}</p>`;
        if (data.reset_count) html += `<p><strong>Passwords Reset:</strong> ${data.reset_count}</p>`;
        if (data.reprinted_count) html += `<p><strong>Reprinted:</strong> ${data.reprinted_count}</p>`;
        if (data.skipped_count) html += `<p><strong>Skipped:</strong> ${data.skipped_count}</p>`;
        html += '</div>';

        if (data.all_printable && data.all_printable.length) {
            html += '<h6>Credentials Generated:</h6>';
            html += '<div class="credentials-list">';
            data.all_printable.forEach(cred => {
                html += `
                    <div class="credential-slip">
                        <h5>${cred.name}</h5>
                        <p><strong>Admission No:</strong> ${cred.admissionNo || 'N/A'}</p>
                        <p><strong>Username:</strong> ${cred.username || cred.email}</p>
                        <p><strong>Password:</strong> ${cred.password !== '********' ? cred.password : 'Use existing password'}</p>
                        <p><strong>Class:</strong> ${cred.class_name || 'N/A'}</p>
                        <hr>
                        <small>Portal URL: ${window.location.origin}/login</small>
                    </div>
                `;
            });
            html += '</div>';
            printBtn.style.display = 'block';
        } else {
            printBtn.style.display = 'none';
        }

        container.innerHTML = html;
    }

    function revokeAccounts(studentIds) {
        fetch('{{ route("users.revoke-password") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ student_ids: studentIds })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', data.message, 'success');
                loadStudents(); // Refresh the list
                selectedStudents = [];
                updateSelectedCount();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Network error', 'error');
        });
    }

    function printSingleCredential(student) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Credential - ${student.name}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 40px; }
                    .credential { border: 2px solid #333; padding: 20px; max-width: 400px; margin: 0 auto; }
                    h2 { color: #2c3e50; }
                    .info { margin: 10px 0; }
                    .label { font-weight: bold; }
                    hr { margin: 20px 0; }
                    .footer { font-size: 12px; text-align: center; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="credential">
                    <h2>Student Portal Credentials</h2>
                    <div class="info"><span class="label">Name:</span> ${student.name}</div>
                    <div class="info"><span class="label">Admission No:</span> ${student.admissionNo || 'N/A'}</div>
                    <div class="info"><span class="label">Username:</span> ${student.username || student.email}</div>
                    <div class="info"><span class="label">Class:</span> ${student.class_name || 'N/A'}</div>
                    <hr>
                    <div class="info"><span class="label">Portal URL:</span> ${window.location.origin}/login</div>
                    <div class="footer">Please keep this information secure</div>
                </div>
                <script>window.print();<\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    printBtn.addEventListener('click', function() {
        if (processedResults && processedResults.all_printable) {
            const printWindow = window.open('', '_blank');
            let html = `
                <html>
                <head>
                    <title>Student Credentials</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .credential-slip {
                            border: 1px solid #ddd;
                            padding: 15px;
                            margin-bottom: 20px;
                            page-break-after: always;
                        }
                        h3 { color: #2c3e50; margin-top: 0; }
                        .info { margin: 5px 0; }
                        .label { font-weight: bold; }
                        hr { margin: 10px 0; }
                        .footer { font-size: 11px; text-align: center; margin-top: 10px; }
                        @media print {
                            .credential-slip {
                                page-break-after: always;
                            }
                        }
                    </style>
                </head>
                <body>
                    <h2 style="text-align: center;">Student Portal Credentials</h2>
                    <p style="text-align: center;">Generated on: ${new Date().toLocaleDateString()}</p>
            `;

            processedResults.all_printable.forEach(cred => {
                html += `
                    <div class="credential-slip">
                        <h3>${cred.name}</h3>
                        <div class="info"><span class="label">Admission No:</span> ${cred.admissionNo || 'N/A'}</div>
                        <div class="info"><span class="label">Username:</span> ${cred.username || cred.email}</div>
                        <div class="info"><span class="label">Password:</span> ${cred.password !== '********' ? cred.password : 'Use existing password'}</div>
                        <div class="info"><span class="label">Class:</span> ${cred.class_name || 'N/A'}</div>
                        <hr>
                        <div class="info"><span class="label">Portal URL:</span> ${window.location.origin}/login</div>
                        <div class="footer">Please keep this information secure. Do not share with others.</div>
                    </div>
                `;
            });

            html += `
                </body>
                </html>
            `;

            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.print();
        }
    });

    startOverBtn.addEventListener('click', function() {
        selectedStudents = [];
        processedResults = null;
        step1.style.display = 'block';
        step2.style.display = 'none';
        step3.style.display = 'none';
        loadStudents();
    });

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>
