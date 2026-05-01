{{-- ============================================================
     resources/views/users/partials/mass-student-modal.blade.php
     ============================================================ --}}

<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-people-fill me-2 text-warning"></i>Mass Manage Student Accounts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- Step Indicator --}}
                <div class="px-4 pt-3 pb-0">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="mass-step active" data-step="1">
                            <span class="step-circle">1</span>
                            <span class="step-label">Select Students</span>
                        </div>
                        <div class="step-line flex-grow-1"></div>
                        <div class="mass-step" data-step="2">
                            <span class="step-circle">2</span>
                            <span class="step-label">Configure Action</span>
                        </div>
                        <div class="step-line flex-grow-1"></div>
                        <div class="mass-step" data-step="3">
                            <span class="step-circle">3</span>
                            <span class="step-label">Review & Execute</span>
                        </div>
                    </div>
                </div>

                {{-- STEP 1: Select Students --}}
                <div id="massStep1" class="mass-step-panel px-4 pb-4">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="massStudentSearch" class="form-control"
                                       placeholder="Search name or admission no...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select id="massArmFilter" class="form-select">
                                <option value="">All Arms</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="massClassFilter" class="form-select">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="massAccountFilter" class="form-select">
                                <option value="">All Students</option>
                                <option value="no_account">No Account</option>
                                <option value="has_account">Has Account</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="massSelectAll">
                                <i class="bi bi-check-all me-1"></i>Select All Visible
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="massClearAll">
                                <i class="bi bi-x-lg me-1"></i>Clear All
                            </button>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-2 justify-content-end flex-wrap">
                            <span class="badge bg-primary fs-6 px-3">
                                <i class="bi bi-person-check me-1"></i><span id="massSelectedCount">0</span> selected
                            </span>
                            <span class="badge bg-success fs-6 px-3" id="newCountBadge">
                                <i class="bi bi-plus-circle me-1"></i><span id="massNewCount">0</span> new
                            </span>
                            <span class="badge bg-warning text-dark fs-6 px-3" id="existingCountBadge">
                                <i class="bi bi-key me-1"></i><span id="massExistingCount">0</span> existing
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:380px; overflow-y:auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width:40px"><input type="checkbox" class="form-check-input" id="massCheckAll"></th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class</th>
                                    <th>Arm</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="massStudentTableBody">
                                <tr><td colspan="6" class="text-center py-4 text-muted">Loading students...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <small class="text-muted">
                            You can select both students with and without accounts.<br>
                            <span class="badge bg-success">No Account</span> = Create |
                            <span class="badge bg-warning text-dark">Has Account</span> = Reset Password &amp; Reprint
                        </small>
                        <button type="button" class="btn btn-primary" id="massStep1Next" disabled>
                            Next: Configure <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: Configure Action --}}
                <div id="massStep2" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="alert alert-info py-2 mb-4" id="batchSummaryAlert">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="batchSummaryText"></span>
                    </div>

                    <div class="row g-4">
                        <!-- New Students -->
                        <div class="col-md-6" id="newStudentsConfig">
                            <div class="card h-100">
                                <div class="card-header bg-success bg-opacity-10">
                                    <h6 class="mb-0 text-success">
                                        <i class="bi bi-plus-circle me-1"></i>New Accounts
                                        (<span id="newStudentsConfigCount">0</span>)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="massPasswordType" id="pwdTypeSame" value="same" checked>
                                        <label class="form-check-label" for="pwdTypeSame">Same password for all new students</label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="massPasswordType" id="pwdTypeIndividual" value="individual">
                                        <label class="form-check-label" for="pwdTypeIndividual">Auto-generate unique password per student</label>
                                    </div>

                                    <div id="sharedPasswordGroup">
                                        <div class="input-group">
                                            <input type="text" id="massSharedPassword" class="form-control" placeholder="Enter shared password (min 6 chars)" minlength="6">
                                            <button type="button" class="btn btn-outline-secondary" id="generateSharedPwd">
                                                <i class="bi bi-shuffle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Students -->
                        <div class="col-md-6" id="existingStudentsConfig">
                            <div class="card h-100">
                                <div class="card-header bg-warning bg-opacity-10">
                                    <h6 class="mb-0 text-warning">
                                        <i class="bi bi-key me-1"></i>Existing Accounts
                                        (<span id="existingStudentsConfigCount">0</span>)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="revokeExistingPasswords" checked>
                                        <label class="form-check-label fw-semibold text-danger" for="revokeExistingPasswords">
                                            Reset Password (Revoke &amp; Reprint)
                                        </label>
                                        <small class="text-muted d-block mt-1">
                                            Password will be set to <code>ChangeMe@123</code>. Credential slips will be printable.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="onlyExistingAlert" class="alert alert-warning d-none mt-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Reprint Mode:</strong> You have selected only students who already have accounts.
                        You can reset their passwords and reprint credentials.
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="massStep2Back">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </button>
                        <button type="button" class="btn btn-primary" id="massStep2Next">
                            Next: Review <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Review --}}
                <div id="massStep3" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="alert alert-warning py-2 mb-3" id="step3Warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Please review the actions below before executing.
                    </div>

                    <div class="mb-3">
                        <span class="badge bg-primary fs-6" id="reviewStudentCount">0 students</span>
                        <span class="badge bg-info text-dark fs-6 ms-2" id="reviewActionSummary"></span>
                    </div>

                    <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class / Arm</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="reviewTableBody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="massStep3Back">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </button>
                        <button type="button" class="btn btn-success px-4" id="massCreateBtn">
                            <i class="bi bi-check2-circle me-2"></i><span id="massCreateBtnLabel">Confirm & Execute</span>
                        </button>
                    </div>
                </div>

                {{-- STEP 4: Results --}}
                <div id="massStep4" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="alert alert-success mb-3" id="massResultAlert"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">Operation Completed</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="printCredentialsBtn">
                                <i class="bi bi-printer me-1"></i>Print All Credential Slips
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" onclick="location.reload()">
                                <i class="bi bi-x me-1"></i>Close & Refresh
                            </button>
                        </div>
                    </div>

                    <div id="step4CreatedSection" class="d-none">
                        <h6 class="text-success"><i class="bi bi-plus-circle me-1"></i>New Accounts Created (<span id="step4CreatedCount">0</span>)</h6>
                        <div class="table-responsive mb-3" style="max-height:280px;overflow-y:auto;">
                            <table class="table table-sm"><tbody id="createdResultsBody"></tbody></table>
                        </div>
                    </div>

                    <div id="step4RevokedSection" class="d-none">
                        <h6 class="text-warning"><i class="bi bi-key me-1"></i>Passwords Reset (<span id="step4RevokedCount">0</span>)</h6>
                        <div class="table-responsive mb-3" style="max-height:280px;overflow-y:auto;">
                            <table class="table table-sm"><tbody id="revokedResultsBody"></tbody></table>
                        </div>
                    </div>

                    <div id="massSkippedInfo" class="mt-3 d-none">
                        <small class="text-muted">Skipped: <span id="massSkippedNames" class="text-muted"></span></small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- STYLES --}}
<style>
.mass-step { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.step-circle {
    width: 32px; height: 32px; border-radius: 50%; background: #dee2e6; color: #6c757d;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem;
}
.mass-step.active .step-circle { background: #0d6efd; color: #fff; }
.mass-step.done .step-circle { background: #198754; color: #fff; }
.step-line { height: 2px; background: #dee2e6; min-width: 20px; }

tr.row-has-account td { background-color: rgba(255, 193, 7, 0.08) !important; }
</style>

{{-- JAVASCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    let allStudents = [];
    let filteredStudents = [];
    let selectedIds = new Set();
    let allPrintable = [];

    // DOM Elements
    const modalEl = document.getElementById('massStudentModal');
    const tbody = document.getElementById('massStudentTableBody');
    const searchInput = document.getElementById('massStudentSearch');
    const classFilter = document.getElementById('massClassFilter');
    const armFilter = document.getElementById('massArmFilter');
    const accountFilter = document.getElementById('massAccountFilter');
    const checkAll = document.getElementById('massCheckAll');
    const selectedCountEl = document.getElementById('massSelectedCount');
    const newCountEl = document.getElementById('massNewCount');
    const existingCountEl = document.getElementById('massExistingCount');
    const step1NextBtn = document.getElementById('massStep1Next');

    modalEl?.addEventListener('show.bs.modal', () => resetMassModal() || loadAllStudents());

    function loadAllStudents() {
        fetch('{{ route("get.students") }}?limit=2000')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                allStudents = data.students;
                populateFilters(data);
                filteredStudents = [...allStudents];
                renderStudentTable();
            })
            .catch(() => tbody.innerHTML = `<tr><td colspan="6" class="text-danger text-center py-3">Failed to load students</td></tr>`);
    }

    function populateFilters(data) {
        // Arm Filter
        if (data.arms?.length) {
            armFilter.innerHTML = '<option value="">All Arms</option>' + data.arms.map(a =>
                `<option value="${a.id}">${a.name}</option>`).join('');
        }
        // Class Filter (simplified - you can enhance)
        if (data.classes?.length) {
            classFilter.innerHTML = '<option value="">All Classes</option>' + data.classes.map(c =>
                `<option value="${c.id}">${c.name}</option>`).join('');
        }
    }

    function renderStudentTable() {
        tbody.innerHTML = filteredStudents.map(s => {
            const has = !!s.has_account;
            return `
                <tr class="${has ? 'row-has-account' : ''}">
                    <td><input type="checkbox" class="form-check-input mass-row-check" value="${s.id}"
                        ${selectedIds.has(String(s.id)) ? 'checked' : ''}
                        data-has-account="${has ? 1 : 0}"></td>
                    <td>${escHtml(s.name)}</td>
                    <td><code>${escHtml(s.admissionNo || '—')}</code></td>
                    <td>${escHtml(s.class_name || '—')}</td>
                    <td>${escHtml(s.arm_name || '—')}</td>
                    <td>${has
                        ? '<span class="badge bg-warning text-dark">Has Account</span>'
                        : '<span class="badge bg-success">No Account</span>'}</td>
                </tr>`;
        }).join('');

        updateSelectedCount();
        attachRowListeners();
    }

    function attachRowListeners() {
        document.querySelectorAll('.mass-row-check').forEach(cb => {
            cb.addEventListener('change', () => {
                if (cb.checked) selectedIds.add(cb.value);
                else selectedIds.delete(cb.value);
                updateSelectedCount();
            });
        });
    }

    function getSelectionBreakdown() {
        const selected = allStudents.filter(s => selectedIds.has(String(s.id)));
        return {
            all: selected,
            newOnes: selected.filter(s => !s.has_account),
            existing: selected.filter(s => s.has_account)
        };
    }

    function updateSelectedCount() {
        const { all, newOnes, existing } = getSelectionBreakdown();
        selectedCountEl.textContent = all.length;
        newCountEl.textContent = newOnes.length;
        existingCountEl.textContent = existing.length;
        step1NextBtn.disabled = all.length === 0;
    }

    // Filters
    function applyFilter() {
        const q = searchInput.value.toLowerCase().trim();
        const cls = classFilter.value;
        const arm = armFilter.value;
        const acct = accountFilter.value;

        filteredStudents = allStudents.filter(s => {
            const matchSearch = !q || s.name.toLowerCase().includes(q) || (s.admissionNo || '').toLowerCase().includes(q);
            const matchClass = !cls || String(s.class_id) === cls;
            const matchArm = !arm || String(s.arm_id) === arm;
            const matchAcct = !acct ||
                (acct === 'no_account' && !s.has_account) ||
                (acct === 'has_account' && s.has_account);
            return matchSearch && matchClass && matchArm && matchAcct;
        });
        renderStudentTable();
    }

    searchInput?.addEventListener('input', debounce(applyFilter, 300));
    classFilter?.addEventListener('change', applyFilter);
    armFilter?.addEventListener('change', applyFilter);
    accountFilter?.addEventListener('change', applyFilter);

    // Select All / Clear
    checkAll?.addEventListener('change', function() {
        filteredStudents.forEach(s => this.checked ? selectedIds.add(String(s.id)) : selectedIds.delete(String(s.id)));
        renderStudentTable();
    });

    document.getElementById('massSelectAll')?.addEventListener('click', () => {
        filteredStudents.forEach(s => selectedIds.add(String(s.id)));
        renderStudentTable();
    });

    document.getElementById('massClearAll')?.addEventListener('click', () => {
        selectedIds.clear();
        renderStudentTable();
    });

    // Step Navigation
    function goStep(n) {
        [1,2,3,4].forEach(i => {
            document.getElementById(`massStep${i}`)?.classList.toggle('d-none', i !== n);
            const stepEl = document.querySelector(`.mass-step[data-step="${i}"]`);
            if (stepEl) {
                stepEl.classList.toggle('active', i === n);
                stepEl.classList.toggle('done', i < n);
            }
        });
    }

    document.getElementById('massStep1Next')?.addEventListener('click', () => {
        setupStep2();
        goStep(2);
    });

    document.getElementById('massStep2Back')?.addEventListener('click', () => goStep(1));
    document.getElementById('massStep3Back')?.addEventListener('click', () => goStep(2));

    function setupStep2() {
        const { newOnes, existing } = getSelectionBreakdown();
        const hasNew = newOnes.length > 0;
        const hasExisting = existing.length > 0;

        document.getElementById('batchSummaryText').innerHTML = `
            ${hasNew ? `<strong>${newOnes.length}</strong> student(s) will have new accounts created.` : ''}
            ${hasNew && hasExisting ? '<br>' : ''}
            ${hasExisting ? `<strong>${existing.length}</strong> existing account(s) can have passwords reset.` : ''}
        `;

        document.getElementById('newStudentsConfig').classList.toggle('d-none', !hasNew);
        document.getElementById('existingStudentsConfig').classList.toggle('d-none', !hasExisting);
        document.getElementById('onlyExistingAlert').classList.toggle('d-none', hasNew || !hasExisting);

        document.getElementById('newStudentsConfigCount').textContent = newOnes.length;
        document.getElementById('existingStudentsConfigCount').textContent = existing.length;
    }

    // Password Type Toggle
    document.querySelectorAll('input[name="massPasswordType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('sharedPasswordGroup').classList.toggle('d-none', this.value !== 'same');
        });
    });

    document.getElementById('generateSharedPwd')?.addEventListener('click', () => {
        const pwd = Math.random().toString(36).slice(-8).toUpperCase();
        document.getElementById('massSharedPassword').value = pwd;
    });

    // Main Execute Button
    document.getElementById('massStep2Next')?.addEventListener('click', () => {
        buildReviewStep();
        goStep(3);
    });

    function buildReviewStep() {
        const { all, newOnes, existing } = getSelectionBreakdown();
        const willRevoke = document.getElementById('revokeExistingPasswords')?.checked || false;

        document.getElementById('reviewStudentCount').textContent = `${all.length} student(s)`;

        let actionText = '';
        if (newOnes.length) actionText += `${newOnes.length} new account(s)`;
        if (willRevoke && existing.length) actionText += (actionText ? ' + ' : '') + `${existing.length} password reset(s)`;
        document.getElementById('reviewActionSummary').textContent = actionText || 'Reprint only';

        const tbody = document.getElementById('reviewTableBody');
        tbody.innerHTML = all.map((s, i) => {
            let action = '';
            if (!s.has_account) {
                action = `<span class="badge bg-success">Create Account</span>`;
            } else if (willRevoke) {
                action = `<span class="badge bg-danger">Reset Password</span>`;
            } else {
                action = `<span class="badge bg-secondary">Reprint Only</span>`;
            }
            return `<tr>
                <td>${i+1}</td>
                <td>${escHtml(s.name)}</td>
                <td><code>${escHtml(s.admissionNo)}</code></td>
                <td>${escHtml(s.class_name || '—')} ${s.arm_name ? `/ ${s.arm_name}` : ''}</td>
                <td>${action}</td>
            </tr>`;
        }).join('');
    }

    document.getElementById('massCreateBtn')?.addEventListener('click', async function () {
        const { newOnes, existing } = getSelectionBreakdown();
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke = document.getElementById('revokeExistingPasswords')?.checked || false;
        const sharedPwd = document.getElementById('massSharedPassword').value.trim();

        if (newOnes.length > 0 && pwdType === 'same' && sharedPwd.length < 6) {
            alert("Shared password must be at least 6 characters long.");
            return;
        }

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        let created = [], revoked = [], skipped = [];

        try {
            // Create new accounts
            if (newOnes.length > 0) {
                const res = await fetch('{{ route("users.mass-create-students") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                    body: JSON.stringify({
                        students: newOnes.map(s => ({ student_id: s.id })),
                        roles: ['student'],
                        password_type: pwdType,
                        shared_password: pwdType === 'same' ? sharedPwd : null
                    })
                });
                const data = await res.json();
                if (data.created) created = data.created;
                if (data.skipped) skipped = data.skipped;
            }

            // Reset passwords for existing accounts
            if (willRevoke && existing.length > 0) {
                const res = await fetch('{{ route("users.revoke-student-password") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                    body: JSON.stringify({ student_ids: existing.map(s => s.id) })
                });
                const data = await res.json();
                if (data.revoked) revoked = data.revoked;
            }

            allPrintable = [...created, ...revoked];

            showResultsStep(created, revoked, skipped);

        } catch (e) {
            console.error(e);
            alert("An error occurred. Please try again.");
        } finally {
            setBtn(this, false, '<i class="bi bi-check2-circle me-2"></i>Confirm & Execute');
        }
    });

    function showResultsStep(created, revoked, skipped) {
        document.getElementById('massResultAlert').innerHTML =
            `<i class="bi bi-check-circle me-2"></i>Operation completed successfully!`;

        document.getElementById('printCredentialsBtn').classList.toggle('d-none', allPrintable.length === 0);

        // Created Section
        const createdSec = document.getElementById('step4CreatedSection');
        createdSec.classList.toggle('d-none', created.length === 0);
        if (created.length) {
            document.getElementById('step4CreatedCount').textContent = created.length;
            document.getElementById('createdResultsBody').innerHTML = created.map((u,i) => `
                <tr>
                    <td>${i+1}</td>
                    <td><strong>${escHtml(u.name)}</strong></td>
                    <td><code>${escHtml(u.admissionNo)}</code></td>
                    <td>${escHtml(u.email || u.username)}<br><small class="text-success">${escHtml(u.password)}</small></td>
                </tr>
            `).join('');
        }

        // Revoked Section
        const revokedSec = document.getElementById('step4RevokedSection');
        revokedSec.classList.toggle('d-none', revoked.length === 0);
        if (revoked.length) {
            document.getElementById('step4RevokedCount').textContent = revoked.length;
            document.getElementById('revokedResultsBody').innerHTML = revoked.map((u,i) => `
                <tr>
                    <td>${i+1}</td>
                    <td><strong>${escHtml(u.name)}</strong></td>
                    <td><code>${escHtml(u.admissionNo)}</code></td>
                    <td><span class="text-danger">${escHtml(u.password || 'ChangeMe@123')}</span></td>
                </tr>
            `).join('');
        }

        if (skipped?.length) {
            document.getElementById('massSkippedInfo').classList.remove('d-none');
            document.getElementById('massSkippedNames').textContent = skipped.join(', ');
        }

        goStep(4);
    }

    // Print Function
    document.getElementById('printCredentialsBtn')?.addEventListener('click', () => {
        if (allPrintable.length === 0) return alert("Nothing to print.");
        printSlips(allPrintable);
    });

    function printSlips(accounts) {
        // Your existing printSlips function (keep it as is - it's already good)
        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'School Portal';
        const today = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

        const slipsHtml = accounts.map(u => `
            <div class="slip">
                <div class="slip-header">
                    <span class="school-name">${escHtml(schoolName)}</span>
                    <span class="slip-title">Student Portal Access</span>
                    <span class="slip-date">${today}</span>
                </div>
                <table class="slip-table">
                    <tr><td class="label">Name</td><td class="value"><strong>${escHtml(u.name)}</strong></td></tr>
                    <tr><td class="label">Admission No</td><td class="value">${escHtml(u.admissionNo || '—')}</td></tr>
                    <tr><td class="label">Username</td><td class="value">${escHtml(u.username || u.email || '—')}</td></tr>
                    <tr><td class="label">Password</td><td class="value password-cell">${escHtml(u.password || 'ChangeMe@123')}</td></tr>
                </table>
                <p class="slip-note">Change password after first login. Keep this slip safe.</p>
            </div>
        `).join('');

        const printWin = window.open('', '_blank');
        printWin.document.write(`<!DOCTYPE html><html><head><title>Credentials</title><style>
            body{font-family:Arial,sans-serif;} .slip{border:2px solid #333;padding:15px;margin:10px 0;}
            .password-cell{font-family:monospace;font-size:16px;font-weight:bold;color:green;}
        </style></head><body><h2>Student Credentials - ${today}</h2>${slipsHtml}</body></html>`);
        printWin.document.close();
        printWin.focus();
        printWin.print();
    }

    // Utilities
    function escHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function debounce(fn, ms) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function setBtn(btn, disabled, html) {
        btn.disabled = disabled;
        btn.innerHTML = html;
    }

    function resetMassModal() {
        selectedIds.clear();
        allPrintable = [];
        searchInput.value = '';
        classFilter.value = '';
        armFilter.value = '';
        accountFilter.value = '';
        document.getElementById('massSharedPassword').value = '';
        document.getElementById('revokeExistingPasswords').checked = true;
        goStep(1);
    }
});
</script>
