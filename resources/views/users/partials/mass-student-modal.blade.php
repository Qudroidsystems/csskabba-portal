{{-- ============================================================
     resources/views/users/partials/mass-student-modal.blade.php
     Include in users/index.blade.php with:
       @include('users.partials.mass-student-modal')
     ============================================================ --}}

{{-- ══════════════════════════════════════════════════════════════
     MASS STUDENT ACCOUNT MANAGEMENT MODAL (Enhanced)
     ══════════════════════════════════════════════════════════════ --}}
<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-people-fill me-2 text-warning"></i>Mass Student Account Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- Step indicator --}}
                <div class="px-4 pt-3 pb-0">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="mass-step active" data-step="1">
                            <span class="step-circle">1</span>
                            <span class="step-label">Select Students</span>
                        </div>
                        <div class="step-line flex-grow-1"></div>
                        <div class="mass-step" data-step="2">
                            <span class="step-circle">2</span>
                            <span class="step-label">Configure</span>
                        </div>
                        <div class="step-line flex-grow-1"></div>
                        <div class="mass-step" data-step="3">
                            <span class="step-circle">3</span>
                            <span class="step-label">Review & Execute</span>
                        </div>
                        <div class="step-line flex-grow-1"></div>
                        <div class="mass-step" data-step="4">
                            <span class="step-circle">4</span>
                            <span class="step-label">Results</span>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 1: Select Students ── --}}
                <div id="massStep1" class="mass-step-panel px-4 pb-4">

                    <div class="row g-2 mb-3">
                        {{-- Search --}}
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="massStudentSearch" class="form-control"
                                       placeholder="Search name or admission no...">
                            </div>
                        </div>

                        {{-- Account Status Filter (NEW) --}}
                        <div class="col-md-2">
                            <select id="massAccountStatus" class="form-select">
                                <option value="all">All Students</option>
                                <option value="no">No Account Only</option>
                                <option value="yes">Has Account Only</option>
                            </select>
                        </div>

                        {{-- Arm filter --}}
                        <div class="col-md-2">
                            <select id="massArmFilter" class="form-select">
                                <option value="">All Arms</option>
                            </select>
                        </div>

                        {{-- Class filter --}}
                        <div class="col-md-2">
                            <select id="massClassFilter" class="form-select">
                                <option value="">All Classes</option>
                            </select>
                        </div>

                        {{-- Select / Clear buttons --}}
                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="massSelectAll">
                                <i class="bi bi-check-all me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="massClearAll">
                                <i class="bi bi-x-lg me-1"></i>Clear
                            </button>
                        </div>
                    </div>

                    {{-- Email format info --}}
                    <div class="alert alert-info py-1 mb-2 small">
                        <i class="bi bi-envelope-fill me-1"></i>
                        Emails will be generated as: <code>firstname.lastname@csskabba.ng</code> (special characters removed)
                    </div>

                    {{-- Students table --}}
                    <div class="table-responsive" style="max-height:380px;overflow-y:auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" class="form-check-input" id="massCheckAll">
                                    </th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class</th>
                                    <th>Arm</th>
                                    <th>Status</th>
                                    <th>Generated Email</th>
                                </tr>
                            </thead>
                            <tbody id="massStudentTableBody">
                                <tr><td colspan="7" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div>Loading students...
                                </td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <small class="text-muted">
                            <span id="massSelectedCount">0</span> selected &nbsp;·&nbsp;
                            <span id="massAlreadyCount" class="text-warning fw-semibold">0</span> already have accounts
                        </small>
                        <button type="button" class="btn btn-primary" id="massStep1Next" disabled>
                            Next: Configure <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 2: Configure ── --}}
                <div id="massStep2" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="row g-4">

                        {{-- Role selector: Student only, all others disabled --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Assign Role <span class="text-danger">*</span>
                            </label>

                            <input type="hidden" id="massRoleHidden" value="Student">

                            <div class="border rounded p-3 bg-light">
                                @php
                                    $allRoles = \Spatie\Permission\Models\Role::orderBy('name')->get();
                                    $studentRole = $allRoles->where('name', 'Student')->first();
                                    $otherRoles = $allRoles->where('name', '!=', 'Student');
                                @endphp

                                {{-- Student Role (Enabled and Required) --}}
                                @if($studentRole)
                                    <div class="form-check mb-2 p-2 bg-success bg-opacity-10 rounded">
                                        <input class="form-check-input" type="checkbox"
                                               id="massRole_{{ $studentRole->name }}"
                                               value="{{ $studentRole->name }}"
                                               checked disabled>
                                        <label class="form-check-label fw-semibold text-success" for="massRole_{{ $studentRole->name }}">
                                            <i class="bi bi-person-badge-fill me-1"></i>
                                            {{ $studentRole->name }}
                                            <span class="badge bg-success ms-1">
                                                <i class="bi bi-lock-fill me-1"></i>Required
                                            </span>
                                        </label>
                                        <p class="small text-muted mt-1 mb-0 ms-4">
                                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                                            Automatically assigned to all student accounts
                                        </p>
                                    </div>
                                @endif

                                {{-- Other Roles (All Disabled) --}}
                                @foreach($otherRoles as $role)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox"
                                               id="massRole_{{ $role->name }}"
                                               value="{{ $role->name }}"
                                               disabled>
                                        <label class="form-check-label text-muted" for="massRole_{{ $role->name }}">
                                            <i class="bi bi-shield-lock-fill me-1"></i>
                                            {{ $role->name }}
                                            <span class="badge bg-secondary ms-1">
                                                <i class="bi bi-lock me-1"></i>Disabled
                                            </span>
                                        </label>
                                        <small class="text-danger d-block ms-4">
                                            Cannot assign {{ $role->name }} role to student accounts
                                        </small>
                                    </div>
                                @endforeach

                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Only the <strong>Student</strong> role can be assigned via mass creation
                                        to prevent accidental privilege escalation.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Password strategy --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Password Strategy <span class="text-danger">*</span>
                            </label>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="massPasswordType"
                                       id="pwdTypeSame" value="same" checked>
                                <label class="form-check-label" for="pwdTypeSame">
                                    <strong>Same password for all</strong>
                                    <br><small class="text-muted">All selected students get the same password</small>
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="massPasswordType"
                                       id="pwdTypeIndividual" value="individual">
                                <label class="form-check-label" for="pwdTypeIndividual">
                                    <strong>Individual random passwords</strong>
                                    <br><small class="text-muted">Each student gets a unique auto-generated password</small>
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="massPasswordType"
                                       id="pwdTypeRevoke" value="revoke">
                                <label class="form-check-label" for="pwdTypeRevoke">
                                    <span class="text-danger fw-semibold">
                                        <i class="bi bi-key me-1"></i>Revoke passwords
                                    </span>
                                    <small class="text-muted d-block ms-0 mt-1">
                                        Resets selected students to <code>ChangeMe@123</code>.
                                        Only affects students who already have accounts.
                                    </small>
                                </label>
                            </div>

                            {{-- Shared password input --}}
                            <div id="sharedPasswordGroup">
                                <div class="input-group">
                                    <input type="text" id="massSharedPassword" class="form-control"
                                           placeholder="Shared password (min 6 chars)" minlength="6">
                                    <button type="button" class="btn btn-outline-secondary" id="generateSharedPwd">
                                        <i class="bi bi-shuffle"></i> Generate
                                    </button>
                                </div>
                                <small class="text-muted">All selected students will receive this password.</small>
                            </div>

                            {{-- Individual password info --}}
                            <div id="individualPasswordGroup" class="d-none">
                                <div class="alert alert-info py-2 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    A unique password will be auto-generated for each student.
                                    You'll see all passwords in the printout on the results step.
                                </div>
                            </div>

                            {{-- Revoke warning --}}
                            <div id="revokePasswordGroup" class="d-none">
                                <div class="alert alert-danger py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    <strong>Warning:</strong> This resets selected students' passwords to
                                    <code>ChangeMe@123</code>. Students without accounts will be skipped.
                                </div>
                            </div>
                        </div>
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

                {{-- ── STEP 3: Review & Execute ── --}}
                <div id="massStep3" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-warning py-2 mb-3" id="step3Warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Review details below, then click <strong>Execute Action</strong>. This cannot be undone.
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-auto">
                            <span class="badge bg-primary fs-6" id="reviewStudentCount">0 students</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success fs-6" id="reviewRoles">Role: Student</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-info text-dark fs-6" id="reviewPwdType">—</span>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class / Arm</th>
                                    <th>Email (will be used)</th>
                                    <th id="reviewStatusHeader">Action</th>
                                </tr>
                            </thead>
                            <tbody id="reviewTableBody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="massStep3Back">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </button>
                        <button type="button" class="btn btn-success px-4" id="massExecuteBtn">
                            <i class="bi bi-check-circle me-2"></i><span id="massExecuteBtnLabel">Execute Action</span>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 4: Results ── --}}
                <div id="massStep4" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-success mb-3" id="massResultAlert"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold" id="step4Title">Accounts Processed</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="printCredentialsBtn">
                                <i class="bi bi-printer me-1"></i>Print Credential Slips
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="newMassActionBtn">
                                <i class="bi bi-plus-circle me-1"></i>New Action
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-dismiss="modal" onclick="location.reload()">
                                <i class="bi bi-x me-1"></i>Close & Refresh
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light sticky-top" id="step4TableHead">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Email / Username</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                            <tbody id="createdResultsBody"></tbody>
                        </table>
                    </div>

                    <div id="massSkippedInfo" class="mt-3 d-none">
                        <small class="text-warning fw-semibold">
                            <i class="bi bi-skip-forward me-1"></i>Skipped:
                        </small>
                        <span id="massSkippedNames" class="text-muted small"></span>
                    </div>

                    <div id="massErrorsInfo" class="mt-2 d-none">
                        <small class="text-danger fw-semibold">
                            <i class="bi bi-exclamation-circle me-1"></i>Errors:
                        </small>
                        <span id="massErrorsNames" class="text-danger small"></span>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     REVOKE PASSWORD MODAL (single user — triggered from user list)
     ══════════════════════════════════════════════════════════════ --}}
<div id="revokePasswordModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger bg-opacity-10">
                <h5 class="modal-title text-danger fw-bold">
                    <i class="bi bi-key me-2"></i>Reset Student Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">You are about to reset the password for:</p>
                <p class="fw-bold fs-5 mb-3 text-primary" id="revokeTargetName">—</p>
                <div class="alert alert-warning py-2 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Their password will be set to a <strong>randomly generated password</strong>.
                    You will see the new password in the confirmation.
                </div>
                <input type="hidden" id="revokeTargetUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRevokeBtn">
                    <i class="bi bi-key me-1"></i>Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STYLES
     ══════════════════════════════════════════════════════════════ --}}
<style>
/* Step indicator */
.mass-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.step-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: .85rem;
    transition: background .3s, color .3s;
}
.step-label {
    font-size: .72rem;
    color: #6c757d;
    white-space: nowrap;
}
.mass-step.active .step-circle { background: #0d6efd; color: #fff; }
.mass-step.done   .step-circle { background: #198754; color: #fff; }
.mass-step.active .step-label  { color: #0d6efd; font-weight: 600; }
.step-line {
    height: 2px;
    background: #dee2e6;
    min-width: 20px;
}

/* Print styles */
@media print {
    body * { visibility: hidden !important; }
    #credentialPrintArea,
    #credentialPrintArea * { visibility: visible !important; }
    #credentialPrintArea { position: fixed; top: 0; left: 0; width: 100%; }
}
</style>

{{-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ═══════════════════════════════════════════════════════════
       STATE
    ═══════════════════════════════════════════════════════════ */
    let allStudents      = [];
    let filteredStudents = [];
    let selectedIds      = new Set();
    let processedResults = null;
    let currentAction    = 'create';

    /* ═══════════════════════════════════════════════════════════
       DOM REFS
    ═══════════════════════════════════════════════════════════ */
    const modalEl       = document.getElementById('massStudentModal');
    const tbody         = document.getElementById('massStudentTableBody');
    const searchInput   = document.getElementById('massStudentSearch');
    const classFilter   = document.getElementById('massClassFilter');
    const armFilter     = document.getElementById('massArmFilter');
    const accountStatus = document.getElementById('massAccountStatus');
    const checkAll      = document.getElementById('massCheckAll');
    const selectedCount = document.getElementById('massSelectedCount');
    const alreadyCount  = document.getElementById('massAlreadyCount');
    const step1Next     = document.getElementById('massStep1Next');

    /* ═══════════════════════════════════════════════════════════
       Helper: Generate preview email (clean special chars)
    ═══════════════════════════════════════════════════════════ */
    function generatePreviewEmail(firstname, lastname) {
        let cleanFirst = (firstname || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        let cleanLast = (lastname || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        if (!cleanFirst) cleanFirst = 'student';
        if (!cleanLast) cleanLast = 'user';
        return cleanFirst + '.' + cleanLast + '@csskabba.ng';
    }

    /* ═══════════════════════════════════════════════════════════
       LOAD STUDENTS ON MODAL OPEN
    ═══════════════════════════════════════════════════════════ */
    modalEl?.addEventListener('show.bs.modal', () => {
        resetMassModal();
        loadAllStudents();
    });

    function loadAllStudents() {
        fetch('{{ route("get.students") }}?limit=2000')
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(data => {
                if (!data.success) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-3">
                        <i class="bi bi-exclamation-circle me-2"></i>Failed to load students: ${escHtml(data.message || '')}
                    </td></tr>`;
                    return;
                }

                allStudents = data.students.map(s => ({
                    ...s,
                    generatedEmail: generatePreviewEmail(s.firstname, s.lastname)
                }));

                /* Populate filters */
                if (data.arms?.length) {
                    armFilter.innerHTML = '<option value="">All Arms</option>' +
                        data.arms.map(a => `<option value="${escAttr(String(a.id))}">${escHtml(a.name)}</option>`).join('');
                }

                if (data.classes?.length) {
                    classFilter.innerHTML = '<option value="">All Classes</option>' +
                        data.classes.map(c => `<option value="${escAttr(String(c.id))}" data-arm-id="${escAttr(String(c.arm_id || ''))}">`
                            + escHtml(c.name) + (c.arm_name ? ` (${escHtml(c.arm_name)})` : '') + `</option>`).join('');
                } else {
                    const seen = new Set();
                    const opts = [];
                    allStudents.forEach(s => {
                        if (s.class_id && !seen.has(s.class_id)) {
                            seen.add(s.class_id);
                            opts.push(`<option value="${escAttr(String(s.class_id))}" data-arm-id="${escAttr(String(s.arm_id || ''))}">`
                                + escHtml(s.class_name) + (s.arm_name ? ` (${escHtml(s.arm_name)})` : '') + `</option>`);
                        }
                    });
                    classFilter.innerHTML = '<option value="">All Classes</option>' + opts.join('');
                }

                applyFilters();
            })
            .catch(err => {
                console.error('loadAllStudents error:', err);
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-3">
                    <i class="bi bi-wifi-off me-2"></i>Network error – please try again.
                </td></tr>`;
            });
    }

    /* ═══════════════════════════════════════════════════════════
       FILTER AND RENDER
    ═══════════════════════════════════════════════════════════ */
    function applyFilters() {
        const q = (searchInput.value || '').toLowerCase();
        const cls = classFilter.value;
        const arm = armFilter.value;
        const accStatus = accountStatus.value;

        filteredStudents = allStudents.filter(s => {
            const matchQ = !q || s.name.toLowerCase().includes(q) || (s.admissionNo || '').toLowerCase().includes(q);
            const matchCls = !cls || String(s.class_id) === cls;
            const matchArm = !arm || String(s.arm_id) === arm;
            const matchAcc = accStatus === 'all' ? true : (accStatus === 'yes' ? s.has_account : !s.has_account);
            return matchQ && matchCls && matchArm && matchAcc;
        });
        renderStudentTable();
    }

    function renderStudentTable() {
        if (!filteredStudents.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">No students found</td></tr>`;
            updateSelectedCount();
            return;
        }

        let alreadyHave = 0;
        tbody.innerHTML = filteredStudents.map(s => {
            const has = s.has_account;
            const checked = selectedIds.has(String(s.id)) ? 'checked' : '';
            const rowClass = has ? 'table-secondary text-muted' : '';
            if (has) alreadyHave++;

            return `<tr class="${rowClass}">
                <td>
                    <input type="checkbox" class="form-check-input mass-row-check"
                           value="${s.id}" ${checked}
                           data-name="${escAttr(s.name)}"
                           data-admission="${escAttr(s.admissionNo || '')}"
                           data-email="${escAttr(s.email || '')}"
                           data-generated-email="${escAttr(s.generatedEmail)}"
                           data-has-account="${has ? '1' : '0'}">
                </td>
                <td>${escHtml(s.name)}</td>
                <td><code>${escHtml(s.admissionNo || '—')}</code></td>
                <td>${escHtml(s.class_name || '—')}</td>
                <td>${escHtml(s.arm_name || '—')}</td>
                <td>
                    ${has ? '<span class="badge bg-secondary">Has Account</span>' : '<span class="badge bg-success">No Account</span>'}
                </td>
                <td><small class="text-muted">${escHtml(s.generatedEmail)}</small></td>
            </tr>`;
        }).join('');

        alreadyCount.textContent = alreadyHave;
        updateSelectedCount();
        attachRowListeners();
    }

    function attachRowListeners() {
        document.querySelectorAll('.mass-row-check').forEach(cb => {
            cb.addEventListener('change', function () {
                if (this.checked) selectedIds.add(this.value);
                else selectedIds.delete(this.value);
                updateSelectedCount();
            });
        });
    }

    function updateSelectedCount() {
        selectedCount.textContent = selectedIds.size;
        step1Next.disabled = selectedIds.size === 0;
        const eligible = filteredStudents.filter(s => !s.has_account).length;
        checkAll.checked = selectedIds.size > 0 && selectedIds.size === eligible;
        checkAll.indeterminate = selectedIds.size > 0 && !checkAll.checked;
    }

    /* Filter event listeners */
    searchInput?.addEventListener('input', debounce(applyFilters, 250));
    classFilter?.addEventListener('change', applyFilters);
    armFilter?.addEventListener('change', applyFilters);
    accountStatus?.addEventListener('change', applyFilters);

    /* Select All / Clear */
    checkAll?.addEventListener('change', function () {
        filteredStudents.forEach(s => {
            if (this.checked) selectedIds.add(String(s.id));
            else selectedIds.delete(String(s.id));
        });
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

    /* ═══════════════════════════════════════════════════════════
       STEP NAVIGATION
    ═══════════════════════════════════════════════════════════ */
    function goStep(n) {
        [1, 2, 3, 4].forEach(i => {
            document.getElementById(`massStep${i}`)?.classList.toggle('d-none', i !== n);
            const ind = document.querySelector(`.mass-step[data-step="${i}"]`);
            if (ind) {
                ind.classList.toggle('active', i === n);
                ind.classList.toggle('done', i < n);
            }
        });
    }

    document.getElementById('massStep1Next')?.addEventListener('click', () => {
        if (selectedIds.size === 0) return;
        goStep(2);
    });

    document.getElementById('massStep2Back')?.addEventListener('click', () => goStep(1));
    document.getElementById('massStep3Back')?.addEventListener('click', () => goStep(2));

    /* ═══════════════════════════════════════════════════════════
       PASSWORD TYPE TOGGLE
    ═══════════════════════════════════════════════════════════ */
    document.querySelectorAll('input[name="massPasswordType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('sharedPasswordGroup').classList.toggle('d-none', this.value !== 'same');
            document.getElementById('individualPasswordGroup').classList.toggle('d-none', this.value !== 'individual');
            document.getElementById('revokePasswordGroup').classList.toggle('d-none', this.value !== 'revoke');

            currentAction = this.value === 'revoke' ? 'revoke' : 'create';
            document.getElementById('massExecuteBtnLabel').textContent = currentAction === 'revoke' ? 'Revoke Passwords' : 'Execute Action';

            const warning = document.getElementById('step3Warning');
            if (warning) {
                warning.className = currentAction === 'revoke'
                    ? 'alert alert-danger py-2 mb-3'
                    : 'alert alert-warning py-2 mb-3';
                warning.innerHTML = currentAction === 'revoke'
                    ? '<i class="bi bi-exclamation-triangle me-1"></i> <strong>Warning:</strong> You are about to reset passwords. This cannot be undone.'
                    : '<i class="bi bi-exclamation-triangle me-1"></i> Review details below, then click <strong>Execute Action</strong>. This cannot be undone.';
            }
        });
    });

    document.getElementById('generateSharedPwd')?.addEventListener('click', () => {
        const pwd = Math.random().toString(36).slice(-6).toUpperCase() + Math.floor(1000 + Math.random() * 9000);
        document.getElementById('massSharedPassword').value = pwd;
    });

    /* ═══════════════════════════════════════════════════════════
       BUILD REVIEW STEP
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('massStep2Next')?.addEventListener('click', () => {
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;

        if (pwdType === 'same') {
            const pwd = (document.getElementById('massSharedPassword').value || '').trim();
            if (pwd.length < 6) {
                alert('Please enter a shared password of at least 6 characters.');
                return;
            }
        }

        buildReviewStep();
        goStep(3);
    });

    function buildReviewStep() {
        const selected = allStudents.filter(s => selectedIds.has(String(s.id)));
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const isRevoke = pwdType === 'revoke';

        document.getElementById('reviewStudentCount').textContent = `${selected.length} student(s)`;
        document.getElementById('reviewRoles').textContent = 'Role: Student';
        document.getElementById('reviewPwdType').textContent = isRevoke
            ? '⚠ Reset Passwords'
            : (pwdType === 'same' ? 'Shared Password' : 'Individual Passwords');

        document.getElementById('reviewStatusHeader').textContent = isRevoke ? 'Will' : 'Action';

        document.getElementById('reviewTableBody').innerHTML = selected
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((s, i) => `<tr>
                <td>${i + 1}</td>
                <td><strong>${escHtml(s.name)}</strong></td>
                <td><code>${escHtml(s.admissionNo || '—')}</code></td>
                <td>${escHtml(s.class_name || '—')} ${s.arm_name ? `<span class="text-muted">/ ${escHtml(s.arm_name)}</span>` : ''}</td>
                <td>${s.email ? escHtml(s.email) : `<small class="text-muted">${escHtml(s.generatedEmail)}</small>`}</td>
                <td>
                    ${isRevoke
                        ? (s.has_account
                            ? '<span class="badge bg-danger">Reset password</span>'
                            : '<span class="badge bg-secondary">No account – skip</span>')
                        : (s.has_account
                            ? '<span class="badge bg-warning text-dark">⏭ Skip (has account)</span>'
                            : '<span class="badge bg-primary">✓ Create account</span>')
                    }
                </td>
            </tr>`).join('');
    }

    /* ═══════════════════════════════════════════════════════════
       EXECUTE ACTION (CREATE or REVOKE)
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('massExecuteBtn')?.addEventListener('click', function () {
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        if (pwdType === 'revoke') {
            doRevokePasswords(this);
        } else {
            doCreateAccounts(this, pwdType);
        }
    });

    /* Create Accounts */
    function doCreateAccounts(btn, pwdType) {
        const sharedPwd = document.getElementById('massSharedPassword').value;
        const selectedStudents = allStudents.filter(s => selectedIds.has(String(s.id)));

        const payload = {
            students: selectedStudents.map(s => ({ student_id: s.id })),
            action_type: 'create',
            password_type: pwdType,
            shared_password: pwdType === 'same' ? sharedPwd : null,
            roles: ['Student'],
        };

        setBtn(btn, true, '<span class="spinner-border spinner-border-sm me-2"></span>Executing...');

        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            setBtn(btn, false, '<i class="bi bi-check-circle me-2"></i>Execute Action');
            processedResults = data;
            showResultsStep(data, false);
        })
        .catch(err => {
            setBtn(btn, false, '<i class="bi bi-check-circle me-2"></i>Execute Action');
            console.error(err);
            alert('Network error. Please try again.');
        });
    }

    /* Revoke Passwords */
    function doRevokePasswords(btn) {
        const targets = allStudents.filter(s => selectedIds.has(String(s.id)) && s.has_account);

        if (!targets.length) {
            alert('None of the selected students have accounts to revoke.');
            return;
        }

        setBtn(btn, true, '<span class="spinner-border spinner-border-sm me-2"></span>Revoking...');

        fetch('{{ route("users.revoke-student-password") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify({ student_ids: targets.map(s => s.id) }),
        })
        .then(r => r.json())
        .then(data => {
            setBtn(btn, false, '<i class="bi bi-key me-2"></i>Revoke Passwords');
            processedResults = data;
            showResultsStep(data, true);
        })
        .catch(err => {
            setBtn(btn, false, '<i class="bi bi-key me-2"></i>Revoke Passwords');
            console.error(err);
            alert('Network error. Please try again.');
        });
    }

    /* Show Results */
    function showResultsStep(data, isRevoke) {
        document.getElementById('step4Title').textContent = isRevoke ? 'Passwords Reset' : 'Accounts Created';
        document.getElementById('printCredentialsBtn').classList.toggle('d-none', isRevoke);

        let message = data.message || (isRevoke ? 'Passwords reset successfully.' : 'Accounts created successfully.');
        if (data.created_count) message = `${data.created_count} account(s) created successfully.`;
        if (data.revoked_count) message = `${data.revoked_count} password(s) reset successfully.`;

        document.getElementById('massResultAlert').innerHTML = `<i class="bi bi-check-circle me-2"></i>${escHtml(message)}`;

        if (isRevoke) {
            document.getElementById('step4TableHead').innerHTML = `
                <tr><th>#</th><th>Name</th><th>Status</th></tr>`;
            document.getElementById('createdResultsBody').innerHTML = (data.revoked || []).map((r, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><strong>${escHtml(r.name)}</strong><br><small class="text-muted">${escHtml(r.admissionNo || '—')}</small></td>
                    <td><span class="badge bg-danger">Password reset to: <code>${escHtml(r.password || 'ChangeMe@123')}</code></span></td>
                </tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted">No passwords reset.</td></tr>';
        } else {
            document.getElementById('step4TableHead').innerHTML = `
                <tr><th>#</th><th>Name</th><th>Admission No</th><th>Email / Username</th><th>Password</th></tr>`;
            document.getElementById('createdResultsBody').innerHTML = (data.created || []).map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><strong>${escHtml(u.name)}</strong></td>
                    <td><code>${escHtml(u.admissionNo)}</code></td>
                    <td>${escHtml(u.email)}<br><small class="text-muted">@${escHtml(u.username)}</small></td>
                    <td><code class="text-success fw-bold">${escHtml(u.password)}</code></td>
                </tr>`).join('') || '<td><td colspan="5" class="text-center text-muted">No accounts created.</td></tr>';
        }

        if (data.skipped?.length) {
            document.getElementById('massSkippedInfo').classList.remove('d-none');
            document.getElementById('massSkippedNames').textContent = data.skipped.join(', ');
        }

        if (data.errors?.length) {
            document.getElementById('massErrorsInfo').classList.remove('d-none');
            document.getElementById('massErrorsNames').textContent = data.errors.join(', ');
        }

        goStep(4);
    }

    /* ═══════════════════════════════════════════════════════════
       PRINT CREDENTIAL SLIPS
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('printCredentialsBtn')?.addEventListener('click', () => {
        if (!processedResults?.created?.length) {
            alert('No accounts to print.');
            return;
        }
        printSlips(processedResults.created);
    });

    function printSlips(accounts) {
        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'CSS Kabba';
        const today = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

        const slipsHtml = accounts.map((u, idx) => `
            <div class="slip" style="border: 1.5px solid #333; border-radius: 4px; padding: 15px; margin-bottom: 20px; page-break-inside: avoid;">
                <div style="display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px solid #ccc; padding-bottom: 8px; margin-bottom: 12px;">
                    <span style="font-weight: 800;">${escHtml(schoolName)}</span>
                    <span style="font-size: 11px; color: #555;">Student Portal Access</span>
                    <span style="font-size: 9px; color: #888;">${today}</span>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr><td style="width: 35%; padding: 4px;"><strong>Student Name:</strong></td><td>${escHtml(u.name)}</td></tr>
                    <tr><td style="padding: 4px;"><strong>Admission No:</strong></td><td>${escHtml(u.admissionNo || '—')}</td></tr>
                    <tr><td style="padding: 4px;"><strong>Login Email:</strong></td><td>${escHtml(u.email)}</td></tr>
                    <tr><td style="padding: 4px;"><strong>Username:</strong></td><td>${escHtml(u.username)}</td></tr>
                    <tr><td style="padding: 4px;"><strong>Password:</strong></td><td><code style="font-size: 14px; font-weight: bold; color: #0a3;">${escHtml(u.password)}</code></td></tr>
                </table>
                <p style="font-size: 8px; color: #888; margin-top: 12px; font-style: italic;">⚠ Change your password after first login. Keep this slip safe.</p>
            </div>
        `).join('');

        const printWin = window.open('', '_blank', 'width=800,height=900');
        if (!printWin) {
            alert('Pop-up blocked. Please allow pop-ups for this site.');
            return;
        }

        printWin.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Credential Slips</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12pt; background: #fff; color: #111; padding: 20px; }
  .page-title { text-align: center; font-size: 14pt; font-weight: bold; padding-bottom: 10px; border-bottom: 2px solid #000; margin-bottom: 20px; }
  @media print {
      @page { margin: 10mm 12mm; size: A4 portrait; }
      body { padding: 0; }
      .slip { break-inside: avoid; }
  }
</style>
</head>
<body>
  <div class="page-title">Student Portal Login Credentials — ${today}</div>
  ${slipsHtml}
</body>
</html>`);
        printWin.document.close();
        printWin.focus();
        printWin.onload = () => printWin.print();
    }

    /* ═══════════════════════════════════════════════════════════
       NEW ACTION BUTTON
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('newMassActionBtn')?.addEventListener('click', () => {
        resetMassModal();
        loadAllStudents();
        goStep(1);
    });

    /* ═══════════════════════════════════════════════════════════
       SINGLE-USER REVOKE (from user list)
    ═══════════════════════════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-revoke-user-id]');
        if (!btn) return;

        document.getElementById('revokeTargetUserId').value = btn.dataset.revokeUserId;
        document.getElementById('revokeTargetName').textContent = btn.dataset.revokeUserName || '—';

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('revokePasswordModal'));
        modal.show();
    });

    document.getElementById('confirmRevokeBtn')?.addEventListener('click', function () {
        const userId = document.getElementById('revokeTargetUserId').value;
        if (!userId) return;

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...');

        fetch('{{ route("users.reset-single-password", "") }}/' + userId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
        })
        .then(r => r.json())
        .then(data => {
            setBtn(this, false, '<i class="bi bi-key me-1"></i>Reset Password');

            bootstrap.Modal.getInstance(document.getElementById('revokePasswordModal')).hide();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset!',
                    html: `New password for <strong>${data.user.name}</strong>:<br>
                           <code style="font-size: 20px; background: #f0f0f0; padding: 10px; display: inline-block; margin: 10px 0;">${data.password}</code><br>
                           <button class="btn btn-info mt-2" onclick="navigator.clipboard.writeText('${data.password}')">Copy Password</button>`,
                    showConfirmButton: true
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to reset password', 'error');
            }
        })
        .catch(err => {
            setBtn(this, false, '<i class="bi bi-key me-1"></i>Reset Password');
            console.error(err);
            Swal.fire('Error', 'Network error. Please try again.', 'error');
        });
    });

    /* ═══════════════════════════════════════════════════════════
       RESET MODAL
    ═══════════════════════════════════════════════════════════ */
    function resetMassModal() {
        selectedIds.clear();
        processedResults = null;
        allStudents = [];
        filteredStudents = [];

        if (searchInput) searchInput.value = '';
        if (classFilter) classFilter.value = '';
        if (armFilter) armFilter.value = '';
        if (accountStatus) accountStatus.value = 'all';

        document.getElementById('massSharedPassword').value = '';
        document.getElementById('massSkippedInfo')?.classList.add('d-none');
        document.getElementById('massErrorsInfo')?.classList.add('d-none');
        document.getElementById('massResultAlert').innerHTML = '';
        document.getElementById('massSelectedCount').textContent = '0';
        document.getElementById('massAlreadyCount').textContent = '0';

        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading students...
        </td></tr>`;

        // Reset step indicators
        document.querySelectorAll('.mass-step').forEach((s, i) => {
            s.classList.toggle('active', i === 0);
            s.classList.remove('done');
        });

        // Reset password radio to "same"
        const samePwd = document.getElementById('pwdTypeSame');
        if (samePwd) {
            samePwd.checked = true;
            samePwd.dispatchEvent(new Event('change'));
        }
    }

    /* ═══════════════════════════════════════════════════════════
       UTILITIES
    ═══════════════════════════════════════════════════════════ */
    function debounce(fn, ms) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function escHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return escHtml(str).replace(/'/g, '&#39;');
    }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function setBtn(btn, disabled, html) {
        btn.disabled = disabled;
        btn.innerHTML = html;
    }
});
</script>
