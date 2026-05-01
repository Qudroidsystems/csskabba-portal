{{-- ============================================================
     resources/views/users/partials/mass-student-modal.blade.php
     ============================================================ --}}

{{-- ══════════════════════════════════════════════════════════════
     MASS CREATE / REVOKE STUDENT ACCOUNTS MODAL
     ══════════════════════════════════════════════════════════════ --}}
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
                            <span class="step-label">Review & Create</span>
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

                        {{-- Arm filter --}}
                        <div class="col-md-2">
                            <select id="massArmFilter" class="form-select">
                                <option value="">All Arms</option>
                            </select>
                        </div>

                        {{-- Class filter --}}
                        <div class="col-md-3">
                            <select id="massClassFilter" class="form-select">
                                <option value="">All Classes</option>
                            </select>
                        </div>

                        {{-- Account filter --}}
                        <div class="col-md-3">
                            <select id="massAccountFilter" class="form-select">
                                <option value="">All Students</option>
                                <option value="no_account">No Account Yet</option>
                                <option value="has_account">Has Account</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        {{-- Select / Clear buttons --}}
                        <div class="col-md-6 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="massSelectAll">
                                <i class="bi bi-check-all me-1"></i>Select All Visible
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="massClearAll">
                                <i class="bi bi-x-lg me-1"></i>Clear All
                            </button>
                        </div>
                        {{-- Selection summary badges --}}
                        <div class="col-md-6 d-flex align-items-center gap-2 justify-content-end flex-wrap">
                            <span class="badge bg-primary fs-6 px-3">
                                <i class="bi bi-person-check me-1"></i>
                                <span id="massSelectedCount">0</span> selected
                            </span>
                            <span class="badge bg-success fs-6 px-3" title="Students without accounts (will be created)">
                                <i class="bi bi-plus-circle me-1"></i>
                                <span id="massNewCount">0</span> new
                            </span>
                            <span class="badge bg-warning text-dark fs-6 px-3" title="Students with existing accounts (password can be managed)">
                                <i class="bi bi-key me-1"></i>
                                <span id="massExistingCount">0</span> existing
                            </span>
                        </div>
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
                                    <th>Account Status</th>
                                </tr>
                            </thead>
                            <tbody id="massStudentTableBody">
                                <tr><td colspan="6" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div>Loading students...
                                </td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <small class="text-muted">
                            Select students to create accounts or manage existing passwords.
                            <span class="badge bg-success">No Account</span> = will be created.
                            <span class="badge bg-warning text-dark">Has Account</span> = password can be revoked &amp; reprinted.
                        </small>
                        <button type="button" class="btn btn-primary" id="massStep1Next" disabled>
                            Next: Configure <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 2: Configure ── --}}
                <div id="massStep2" class="mass-step-panel px-4 pb-4 d-none">

                    {{-- Smart batch summary --}}
                    <div class="alert alert-info py-2 mb-4" id="batchSummaryAlert">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="batchSummaryText"></span>
                    </div>

                    <div class="row g-4">

                        {{-- Role selector --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Assign Role <span class="text-danger">*</span>
                            </label>
                            <input type="hidden" id="massRoleHidden" value="student">
                            <div class="border rounded p-3 bg-light">
                                @foreach (\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox"
                                               id="massRole_{{ $role->name }}"
                                               value="{{ $role->name }}"
                                               {{ strtolower($role->name) === 'student' ? 'checked' : '' }}
                                               disabled>
                                        <label class="form-check-label {{ strtolower($role->name) !== 'student' ? 'text-muted' : 'fw-semibold text-success' }}"
                                               for="massRole_{{ $role->name }}">
                                            {{ $role->name }}
                                            @if(strtolower($role->name) === 'student')
                                                <span class="badge bg-success ms-1">
                                                    <i class="bi bi-lock-fill me-1"></i>Required
                                                </span>
                                            @else
                                                <span class="badge bg-secondary ms-1">
                                                    <i class="bi bi-lock me-1"></i>Restricted
                                                </span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Only the <strong>student</strong> role can be assigned via mass creation.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Password strategy --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Password Strategy <span class="text-danger">*</span>
                            </label>

                            {{-- New students section --}}
                            <div id="newStudentsConfig" class="mb-3">
                                <p class="text-muted small mb-2 fw-semibold">
                                    <i class="bi bi-plus-circle text-success me-1"></i>
                                    For <span id="newStudentsConfigCount">0</span> students WITHOUT accounts:
                                </p>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="massPasswordType"
                                           id="pwdTypeSame" value="same" checked>
                                    <label class="form-check-label" for="pwdTypeSame">
                                        Same password for all new students
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="massPasswordType"
                                           id="pwdTypeIndividual" value="individual">
                                    <label class="form-check-label" for="pwdTypeIndividual">
                                        Auto-generate unique password per student
                                    </label>
                                </div>

                                {{-- Shared password input --}}
                                <div id="sharedPasswordGroup" class="mt-2">
                                    <div class="input-group">
                                        <input type="text" id="massSharedPassword" class="form-control"
                                               placeholder="Shared password (min 6 chars)" minlength="6">
                                        <button type="button" class="btn btn-outline-secondary" id="generateSharedPwd">
                                            <i class="bi bi-shuffle"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">All new students will receive this password.</small>
                                </div>

                                {{-- Individual password info --}}
                                <div id="individualPasswordGroup" class="mt-2 d-none">
                                    <div class="alert alert-info py-2 mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        A unique password will be auto-generated for each student.
                                        You'll see all passwords in the printout.
                                    </div>
                                </div>
                            </div>

                            {{-- Existing students section --}}
                            <div id="existingStudentsConfig" class="mb-3 d-none">
                                <hr class="my-2">
                                <p class="text-muted small mb-2 fw-semibold">
                                    <i class="bi bi-key text-warning me-1"></i>
                                    For <span id="existingStudentsConfigCount">0</span> students WITH existing accounts:
                                </p>

                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="revokeExistingPasswords" value="1">
                                    <label class="form-check-label" for="revokeExistingPasswords">
                                        <span class="fw-semibold text-danger">
                                            <i class="bi bi-key me-1"></i>Revoke their passwords
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            Resets to <code>ChangeMe@123</code>. Useful when students forget their password.
                                            Their credential slip will be printable after.
                                        </small>
                                    </label>
                                </div>
                            </div>

                            {{-- Only-existing alert (no new students in selection) --}}
                            <div id="onlyExistingAlert" class="d-none">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    All selected students already have accounts.
                                    Enable <strong>Revoke passwords</strong> above to reset them,
                                    or go back and select students without accounts.
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

                {{-- ── STEP 3: Review & Create ── --}}
                <div id="massStep3" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-warning py-2 mb-3" id="step3Warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Review details below, then click <strong>Create Accounts</strong>. This cannot be undone.
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-auto">
                            <span class="badge bg-primary fs-6" id="reviewStudentCount">0 students</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success fs-6" id="reviewRoles">Role: student</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-info text-dark fs-6" id="reviewPwdType">—</span>
                        </div>
                        <div class="col-auto d-none" id="reviewRevokeBadge">
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-key me-1"></i>+ Revoke existing passwords
                            </span>
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
                                    <th>Email</th>
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
                            <i class="bi bi-person-check me-2"></i><span id="massCreateBtnLabel">Confirm & Execute</span>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 4: Results ── --}}
                <div id="massStep4" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-success mb-3" id="massResultAlert"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold" id="step4Title">Results</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="printCredentialsBtn">
                                <i class="bi bi-printer me-1"></i>Print Credential Slips
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-dismiss="modal" onclick="location.reload()">
                                <i class="bi bi-x me-1"></i>Close & Refresh
                            </button>
                        </div>
                    </div>

                    {{-- Created tab --}}
                    <div id="step4CreatedSection" class="d-none">
                        <h6 class="text-success mb-2">
                            <i class="bi bi-plus-circle me-1"></i>
                            Created Accounts
                            <span class="badge bg-success ms-1" id="step4CreatedCount">0</span>
                        </h6>
                        <div class="table-responsive mb-3" style="max-height:280px;overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light sticky-top" id="step4TableHead">
                                    <tr>
                                        <th>#</th><th>Name</th><th>Admission No</th>
                                        <th>Email / Username</th><th>Password</th>
                                    </tr>
                                </thead>
                                <tbody id="createdResultsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Revoked tab --}}
                    <div id="step4RevokedSection" class="d-none">
                        <h6 class="text-warning mb-2">
                            <i class="bi bi-key me-1"></i>
                            Revoked Passwords
                            <span class="badge bg-warning text-dark ms-1" id="step4RevokedCount">0</span>
                        </h6>
                        <div class="table-responsive mb-3" style="max-height:280px;overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th><th>Name</th><th>Admission No</th>
                                        <th>Email / Username</th><th>New Password</th>
                                    </tr>
                                </thead>
                                <tbody id="revokedResultsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="massSkippedInfo" class="mt-3 d-none">
                        <small class="text-muted fw-semibold">
                            <i class="bi bi-skip-forward me-1"></i>Skipped:
                        </small>
                        <span id="massSkippedNames" class="text-muted small"></span>
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
                    <i class="bi bi-key me-2"></i>Revoke Student Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">You are about to reset the password for:</p>
                <p class="fw-bold fs-5 mb-3" id="revokeTargetName">—</p>
                <div class="alert alert-warning py-2 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Their password will be set to <code>ChangeMe@123</code>.
                    They will need to use this to log in and should change it immediately.
                </div>
                <input type="hidden" id="revokeTargetUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRevokeBtn">
                    <i class="bi bi-key me-1"></i>Revoke Password
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
/* Row highlights */
tr.row-has-account td { background-color: rgba(255, 193, 7, 0.06) !important; }
tr.row-has-account:hover td { background-color: rgba(255, 193, 7, 0.12) !important; }

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
    // allPrintable holds both created + revoked accounts for the print sheet
    let allPrintable     = [];

    /* ═══════════════════════════════════════════════════════════
       DOM REFS
    ═══════════════════════════════════════════════════════════ */
    const modalEl         = document.getElementById('massStudentModal');
    const tbody           = document.getElementById('massStudentTableBody');
    const searchInput     = document.getElementById('massStudentSearch');
    const classFilter     = document.getElementById('massClassFilter');
    const armFilter       = document.getElementById('massArmFilter');
    const accountFilter   = document.getElementById('massAccountFilter');
    const checkAll        = document.getElementById('massCheckAll');
    const selectedCount   = document.getElementById('massSelectedCount');
    const newCount        = document.getElementById('massNewCount');
    const existingCount   = document.getElementById('massExistingCount');
    const step1Next       = document.getElementById('massStep1Next');

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
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-3">
                        <i class="bi bi-exclamation-circle me-2"></i>Failed to load students: ${escHtml(data.message || '')}
                    </td></tr>`;
                    return;
                }

                allStudents = data.students;

                /* Populate Arm filter */
                if (data.arms?.length) {
                    armFilter.innerHTML = '<option value="">All Arms</option>' +
                        data.arms.map(a =>
                            `<option value="${escAttr(String(a.id))}">${escHtml(a.name)}</option>`
                        ).join('');
                }

                /* Populate Class filter */
                if (data.classes?.length) {
                    classFilter.innerHTML = '<option value="">All Classes</option>' +
                        data.classes.map(c =>
                            `<option value="${escAttr(String(c.id))}" data-arm-id="${escAttr(String(c.arm_id || ''))}">`
                            + escHtml(c.name) + (c.arm_name ? ` (${escHtml(c.arm_name)})` : '') + `</option>`
                        ).join('');
                } else {
                    const seen = new Set();
                    const opts = [];
                    allStudents.forEach(s => {
                        if (s.class_id && !seen.has(s.class_id)) {
                            seen.add(s.class_id);
                            opts.push(
                                `<option value="${escAttr(String(s.class_id))}" data-arm-id="${escAttr(String(s.arm_id || ''))}">`
                                + escHtml(s.class_name) + (s.arm_name ? ` (${escHtml(s.arm_name)})` : '') + `</option>`
                            );
                        }
                    });
                    classFilter.innerHTML = '<option value="">All Classes</option>' + opts.join('');
                }

                filteredStudents = [...allStudents];
                renderStudentTable();
            })
            .catch(err => {
                console.error('loadAllStudents error:', err);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-3">
                    <i class="bi bi-wifi-off me-2"></i>Network error – please try again.
                </td></tr>`;
            });
    }

    /* ═══════════════════════════════════════════════════════════
       RENDER TABLE — all students selectable regardless of account status
    ═══════════════════════════════════════════════════════════ */
    function renderStudentTable() {
        if (!filteredStudents.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No students found</td></tr>`;
            updateSelectedCount();
            return;
        }

        tbody.innerHTML = filteredStudents.map(s => {
            const has      = s.has_account;
            const checked  = selectedIds.has(String(s.id)) ? 'checked' : '';
            const rowClass = has ? 'row-has-account' : '';

            return `<tr class="${rowClass}">
                <td>
                    <input type="checkbox" class="form-check-input mass-row-check"
                           value="${s.id}" ${checked}
                           data-name="${escAttr(s.name)}"
                           data-admission="${escAttr(s.admissionNo || '')}"
                           data-email="${escAttr(s.email || '')}"
                           data-has-account="${has ? '1' : '0'}">
                </td>
                <td>${escHtml(s.name)}</td>
                <td><code>${escHtml(s.admissionNo || '—')}</code></td>
                <td>${escHtml(s.class_name || '—')}</td>
                <td>${escHtml(s.arm_name || '—')}</td>
                <td>
                    ${has
                        ? '<span class="badge bg-warning text-dark"><i class="bi bi-person-check me-1"></i>Has Account</span>'
                        : '<span class="badge bg-success"><i class="bi bi-person-plus me-1"></i>No Account</span>'
                    }
                </td>
            </tr>`;
        }).join('');

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

    function getSelectionBreakdown() {
        const selected = allStudents.filter(s => selectedIds.has(String(s.id)));
        return {
            all:      selected,
            newOnes:  selected.filter(s => !s.has_account),
            existing: selected.filter(s => s.has_account),
        };
    }

    function updateSelectedCount() {
        const { all, newOnes, existing } = getSelectionBreakdown();
        selectedCount.textContent = all.length;
        newCount.textContent      = newOnes.length;
        existingCount.textContent = existing.length;
        step1Next.disabled        = all.length === 0;
        checkAll.checked          = all.length > 0 && all.length === filteredStudents.length;
        checkAll.indeterminate    = all.length > 0 && !checkAll.checked;
    }

    /* ═══════════════════════════════════════════════════════════
       FILTERS
    ═══════════════════════════════════════════════════════════ */
    function applyFilter() {
        const q       = (searchInput.value || '').toLowerCase();
        const cls     = classFilter.value;
        const arm     = armFilter.value;
        const acctVal = accountFilter.value;

        filteredStudents = allStudents.filter(s => {
            const matchQ    = !q       || s.name.toLowerCase().includes(q) || (s.admissionNo || '').toLowerCase().includes(q);
            const matchCls  = !cls     || String(s.class_id) === cls;
            const matchArm  = !arm     || String(s.arm_id)   === arm;
            const matchAcct = !acctVal
                || (acctVal === 'no_account'  && !s.has_account)
                || (acctVal === 'has_account' &&  s.has_account);
            return matchQ && matchCls && matchArm && matchAcct;
        });
        renderStudentTable();
    }

    armFilter?.addEventListener('change', function () {
        const armId = this.value;
        Array.from(classFilter.options).forEach(opt => {
            if (!opt.value) return;
            opt.hidden = armId ? (opt.dataset.armId !== armId) : false;
        });
        const sel = classFilter.options[classFilter.selectedIndex];
        if (sel?.hidden) classFilter.value = '';
        applyFilter();
    });

    searchInput?.addEventListener('input',    debounce(applyFilter, 250));
    classFilter?.addEventListener('change',   applyFilter);
    accountFilter?.addEventListener('change', applyFilter);

    /* ═══════════════════════════════════════════════════════════
       SELECT ALL / CLEAR — now selects ALL visible (not just no-account)
    ═══════════════════════════════════════════════════════════ */
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
                ind.classList.toggle('done',   i <  n);
            }
        });
    }

    document.getElementById('massStep1Next')?.addEventListener('click', () => {
        if (selectedIds.size === 0) return;
        setupStep2();
        goStep(2);
    });

    document.getElementById('massStep2Back')?.addEventListener('click', () => goStep(1));
    document.getElementById('massStep3Back')?.addEventListener('click', () => goStep(2));

    /* ═══════════════════════════════════════════════════════════
       STEP 2 — configure UI based on selection breakdown
    ═══════════════════════════════════════════════════════════ */
    function setupStep2() {
        const { newOnes, existing } = getSelectionBreakdown();
        const hasNew      = newOnes.length > 0;
        const hasExisting = existing.length > 0;

        // Batch summary
        const parts = [];
        if (hasNew)      parts.push(`<strong>${newOnes.length}</strong> student(s) without accounts <span class="badge bg-success">will be created</span>`);
        if (hasExisting) parts.push(`<strong>${existing.length}</strong> student(s) already have accounts <span class="badge bg-warning text-dark">manage passwords</span>`);
        document.getElementById('batchSummaryText').innerHTML = parts.join(' &nbsp;+&nbsp; ');

        // Show/hide sections
        document.getElementById('newStudentsConfig').classList.toggle('d-none', !hasNew);
        document.getElementById('existingStudentsConfig').classList.toggle('d-none', !hasExisting);
        document.getElementById('onlyExistingAlert').classList.toggle('d-none', hasNew || !hasExisting);

        document.getElementById('newStudentsConfigCount').textContent      = newOnes.length;
        document.getElementById('existingStudentsConfigCount').textContent = existing.length;

        // Reset revoke checkbox
        const revokeEl = document.getElementById('revokeExistingPasswords');
        if (revokeEl) revokeEl.checked = false;
    }

    document.getElementById('massStep2Next')?.addEventListener('click', () => {
        const { newOnes, existing } = getSelectionBreakdown();
        const pwdType  = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke = document.getElementById('revokeExistingPasswords')?.checked;

        if (newOnes.length > 0 && pwdType === 'same') {
            const pwd = (document.getElementById('massSharedPassword').value || '').trim();
            if (pwd.length < 6) {
                alert('Please enter a shared password of at least 6 characters.');
                return;
            }
        }

        if (newOnes.length === 0 && !willRevoke) {
            alert('All selected students already have accounts. Either check "Revoke their passwords" or go back and select students without accounts.');
            return;
        }

        buildReviewStep();
        goStep(3);
    });

    /* ═══════════════════════════════════════════════════════════
       PASSWORD TYPE TOGGLE (new students section)
    ═══════════════════════════════════════════════════════════ */
    document.querySelectorAll('input[name="massPasswordType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('sharedPasswordGroup')    .classList.toggle('d-none', this.value !== 'same');
            document.getElementById('individualPasswordGroup').classList.toggle('d-none', this.value !== 'individual');
        });
    });

    document.getElementById('generateSharedPwd')?.addEventListener('click', () => {
        const pwd = Math.random().toString(36).slice(-6).toUpperCase() + Math.floor(1000 + Math.random() * 9000);
        document.getElementById('massSharedPassword').value = pwd;
    });

    /* ═══════════════════════════════════════════════════════════
       BUILD REVIEW STEP
    ═══════════════════════════════════════════════════════════ */
    function buildReviewStep() {
        const { all, newOnes, existing } = getSelectionBreakdown();
        const pwdType    = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke = document.getElementById('revokeExistingPasswords')?.checked;

        document.getElementById('reviewStudentCount').textContent = `${all.length} student(s)`;
        document.getElementById('reviewRoles').textContent        = 'Role: student';
        document.getElementById('reviewPwdType').textContent      =
            newOnes.length === 0 ? 'No new accounts'
            : (pwdType === 'same' ? 'Shared Password' : 'Individual Passwords');

        const revokeBadge = document.getElementById('reviewRevokeBadge');
        if (revokeBadge) revokeBadge.classList.toggle('d-none', !willRevoke || existing.length === 0);

        const warning = document.getElementById('step3Warning');
        if (warning) {
            const actions = [];
            if (newOnes.length > 0)          actions.push(`create ${newOnes.length} account(s)`);
            if (willRevoke && existing.length) actions.push(`revoke passwords for ${existing.length} existing account(s)`);
            warning.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>
                You are about to: <strong>${actions.join(' and ')}</strong>. This cannot be undone.`;
        }

        document.getElementById('massCreateBtnLabel').textContent = 'Confirm & Execute';

        document.getElementById('reviewTableBody').innerHTML = all
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((s, i) => {
                let actionBadge;
                if (!s.has_account) {
                    actionBadge = '<span class="badge bg-primary">Create account</span>';
                } else if (willRevoke) {
                    actionBadge = '<span class="badge bg-danger">Revoke password</span>';
                } else {
                    actionBadge = '<span class="badge bg-secondary">No change</span>';
                }
                return `<tr>
                    <td>${i + 1}</td>
                    <td>${escHtml(s.name)}</td>
                    <td><code>${escHtml(s.admissionNo || '—')}</code></td>
                    <td>
                        ${escHtml(s.class_name || '—')}
                        ${s.arm_name ? `<span class="text-muted"> / ${escHtml(s.arm_name)}</span>` : ''}
                    </td>
                    <td>${s.email ? escHtml(s.email) : '<em class="text-muted">auto-generate</em>'}</td>
                    <td>${actionBadge}</td>
                </tr>`;
            }).join('');
    }

    /* ═══════════════════════════════════════════════════════════
       MAIN ACTION — CREATE + optional REVOKE in one go
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('massCreateBtn')?.addEventListener('click', async function () {
        const { newOnes, existing } = getSelectionBreakdown();
        const pwdType    = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke = document.getElementById('revokeExistingPasswords')?.checked;
        const sharedPwd  = document.getElementById('massSharedPassword').value;

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        let createdAccounts = [];
        let revokedAccounts = [];
        let skippedNames    = [];
        let messages        = [];

        /* ── 1. Create new accounts ── */
        if (newOnes.length > 0) {
            try {
                const payload = {
                    students:        newOnes.map(s => ({ student_id: s.id })),
                    roles:           ['student'],
                    password_type:   pwdType,
                    shared_password: pwdType === 'same' ? sharedPwd : null,
                };

                const r    = await fetch('{{ route("users.mass-create-students") }}', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                    body:    JSON.stringify(payload),
                });
                const data = await r.json();

                if (data.created?.length) createdAccounts = data.created;
                if (data.skipped?.length) skippedNames    = [...skippedNames, ...data.skipped];
                if (data.message)         messages.push(data.message);
            } catch (err) {
                console.error('Create error:', err);
                messages.push('Error creating some accounts.');
            }
        }

        /* ── 2. Revoke existing passwords ── */
        if (willRevoke && existing.length > 0) {
            try {
                const r    = await fetch('{{ route("users.revoke-student-password") }}', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                    body:    JSON.stringify({ student_ids: existing.map(s => s.id) }),
                });
                const data = await r.json();

                if (data.revoked?.length) {
                    revokedAccounts = data.revoked;
                } else if (data.count) {
                    // Fallback: build from existing list
                    revokedAccounts = existing.map(s => ({
                        name:        s.name,
                        admissionNo: s.admissionNo,
                        email:       s.email || '',
                        username:    (s.admissionNo || '').replace(/[\/\\]/g, '_'),
                        password:    'ChangeMe@123',
                    }));
                }
                if (data.message) messages.push(data.message);
            } catch (err) {
                console.error('Revoke error:', err);
                messages.push('Error revoking some passwords.');
            }
        }

        setBtn(this, false, '<i class="bi bi-person-check me-2"></i>Confirm & Execute');

        // All printable = created + revoked
        allPrintable = [
            ...createdAccounts,
            ...revokedAccounts,
        ];

        showResultsStep(createdAccounts, revokedAccounts, skippedNames, messages.join(' '));
    });

    /* ═══════════════════════════════════════════════════════════
       SHOW RESULTS STEP
    ═══════════════════════════════════════════════════════════ */
    function showResultsStep(created, revoked, skipped, message) {
        document.getElementById('step4Title').textContent = 'Results';
        document.getElementById('massResultAlert').innerHTML =
            `<i class="bi bi-check-circle me-2"></i>${escHtml(message || 'Done.')}`;

        // Print button — always visible if there's anything printable
        document.getElementById('printCredentialsBtn').classList.toggle('d-none', allPrintable.length === 0);

        /* Created section */
        const createdSection = document.getElementById('step4CreatedSection');
        createdSection.classList.toggle('d-none', created.length === 0);
        if (created.length) {
            document.getElementById('step4CreatedCount').textContent = created.length;
            document.getElementById('createdResultsBody').innerHTML = created.map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><strong>${escHtml(u.name)}</strong></td>
                    <td><code>${escHtml(u.admissionNo)}</code></td>
                    <td>${escHtml(u.email)}<br><small class="text-muted">@${escHtml(u.username)}</small></td>
                    <td><code class="text-success">${escHtml(u.password)}</code></td>
                </tr>
            `).join('');
        }

        /* Revoked section */
        const revokedSection = document.getElementById('step4RevokedSection');
        revokedSection.classList.toggle('d-none', revoked.length === 0);
        if (revoked.length) {
            document.getElementById('step4RevokedCount').textContent = revoked.length;
            document.getElementById('revokedResultsBody').innerHTML = revoked.map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><strong>${escHtml(u.name)}</strong></td>
                    <td><code>${escHtml(u.admissionNo || '—')}</code></td>
                    <td>${escHtml(u.email || '—')}<br><small class="text-muted">@${escHtml(u.username || '—')}</small></td>
                    <td><code class="text-danger">${escHtml(u.password || 'ChangeMe@123')}</code></td>
                </tr>
            `).join('');
        }

        /* Skipped */
        if (skipped?.length) {
            document.getElementById('massSkippedInfo').classList.remove('d-none');
            document.getElementById('massSkippedNames').textContent = skipped.join(', ');
        }

        goStep(4);
    }

    /* ═══════════════════════════════════════════════════════════
       PRINT CREDENTIAL SLIPS — prints both created + revoked
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('printCredentialsBtn')?.addEventListener('click', () => {
        if (!allPrintable.length) { alert('No credentials to print.'); return; }
        printSlips(allPrintable);
    });

    function printSlips(accounts) {
        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'School Portal';
        const today      = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

        const slipsHtml = accounts.map(u => `
            <div class="slip">
                <div class="slip-header">
                    <span class="school-name">${escHtml(schoolName)}</span>
                    <span class="slip-title">Student Portal Access</span>
                    <span class="slip-date">${today}</span>
                </div>
                <table class="slip-table">
                    <tr><td class="label">Student Name</td><td class="value"><strong>${escHtml(u.name)}</strong></td></tr>
                    <tr><td class="label">Admission No</td><td class="value">${escHtml(u.admissionNo || '—')}</td></tr>
                    <tr><td class="label">Login Email</td><td class="value">${escHtml(u.email || '—')}</td></tr>
                    <tr><td class="label">Username</td><td class="value">${escHtml(u.username || '—')}</td></tr>
                    <tr><td class="label">Password</td><td class="value password-cell">${escHtml(u.password || 'ChangeMe@123')}</td></tr>
                </table>
                <p class="slip-note">⚠ Change your password after first login. Keep this slip safe.</p>
            </div>
        `).join('');

        const printWin = window.open('', '_blank', 'width=800,height=900');
        if (!printWin) { alert('Pop-up blocked. Please allow pop-ups for this site.'); return; }

        printWin.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Credential Slips</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12pt; background: #fff; color: #111; }
  .page-title {
      text-align: center; font-size: 14pt; font-weight: bold;
      padding: 10mm 0 4mm; border-bottom: 2px solid #000; margin-bottom: 6mm;
  }
  .slip {
      border: 1.5px solid #333; border-radius: 4px;
      padding: 5mm 7mm; page-break-inside: avoid;
  }
  .slip + .slip { border-top: 2px dashed #888; }
  .slip-header {
      display: flex; justify-content: space-between; align-items: baseline;
      border-bottom: 1px solid #ccc; padding-bottom: 3mm; margin-bottom: 3mm;
  }
  .school-name { font-weight: 800; font-size: 11pt; }
  .slip-title  { font-size: 9pt; color: #555; font-style: italic; }
  .slip-date   { font-size: 8pt; color: #888; }
  .slip-table  { width: 100%; border-collapse: collapse; }
  .slip-table td { padding: 1.5mm 2mm; vertical-align: top; }
  .label { width: 38%; font-size: 9pt; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
  .value { font-size: 11pt; }
  .password-cell { font-family: 'Courier New', monospace; font-size: 13pt; font-weight: bold; letter-spacing: 2px; color: #0a3; }
  .slip-note { font-size: 7.5pt; color: #888; margin-top: 3mm; font-style: italic; }
  @media print {
      @page { margin: 10mm 12mm; size: A4 portrait; }
      body  { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
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
       SINGLE-USER REVOKE (from user list table)
    ═══════════════════════════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-revoke-user-id]');
        if (!btn) return;

        document.getElementById('revokeTargetUserId').value     = btn.dataset.revokeUserId;
        document.getElementById('revokeTargetName').textContent = btn.dataset.revokeUserName || '—';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('revokePasswordModal')).show();
    });

    document.getElementById('confirmRevokeBtn')?.addEventListener('click', function () {
        const userId = document.getElementById('revokeTargetUserId').value;
        if (!userId) return;

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Revoking...');

        fetch('{{ route("users.revoke-student-password") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body:    JSON.stringify({ user_ids: [userId] }),
        })
        .then(r => r.json())
        .then(data => {
            setBtn(this, false, '<i class="bi bi-key me-1"></i>Revoke Password');
            bootstrap.Modal.getInstance(document.getElementById('revokePasswordModal')).hide();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon:  data.success ? 'success' : 'error',
                    title: data.success ? 'Done!' : 'Error',
                    text:  data.message,
                });
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            setBtn(this, false, '<i class="bi bi-key me-1"></i>Revoke Password');
            console.error(err);
            alert('Network error. Please try again.');
        });
    });

    /* ═══════════════════════════════════════════════════════════
       RESET MODAL
    ═══════════════════════════════════════════════════════════ */
    function resetMassModal() {
        selectedIds.clear();
        allPrintable     = [];
        allStudents      = [];
        filteredStudents = [];

        if (searchInput)   searchInput.value   = '';
        if (classFilter)   classFilter.value   = '';
        if (armFilter)     armFilter.value     = '';
        if (accountFilter) accountFilter.value = '';

        document.getElementById('massSharedPassword').value        = '';
        document.getElementById('massSkippedInfo')?.classList.add('d-none');
        document.getElementById('massResultAlert').innerHTML       = '';
        document.getElementById('massSelectedCount').textContent   = '0';
        document.getElementById('newCount') && (document.getElementById('massNewCount').textContent      = '0');
        document.getElementById('massExistingCount').textContent   = '0';

        const revokeEl = document.getElementById('revokeExistingPasswords');
        if (revokeEl) revokeEl.checked = false;

        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading students...
        </td></tr>`;

        document.querySelectorAll('.mass-step').forEach((s, i) => {
            s.classList.toggle('active', i === 0);
            s.classList.remove('done');
        });

        const samePwd = document.getElementById('pwdTypeSame');
        if (samePwd) {
            samePwd.checked = true;
            samePwd.dispatchEvent(new Event('change'));
        }

        goStep(1);
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

    function escAttr(str) { return escHtml(str).replace(/'/g, '&#39;'); }
    function getCsrf()    { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
    function setBtn(btn, disabled, html) { btn.disabled = disabled; btn.innerHTML = html; }
});
</script>
