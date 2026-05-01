{{-- ============================================================
     resources/views/users/partials/mass-student-modal.blade.php
     IMPROVED: full revoke + reprint for existing accounts
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
                            <span class="step-label">Review & Execute</span>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 1: Select Students ── --}}
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
                                <option value="no_account">No Account Yet</option>
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
                                <i class="bi bi-person-check me-1"></i>
                                <span id="massSelectedCount">0</span> selected
                            </span>
                            <span class="badge bg-success fs-6 px-3" title="Students without accounts — will be created">
                                <i class="bi bi-plus-circle me-1"></i>
                                <span id="massNewCount">0</span> new
                            </span>
                            <span class="badge bg-warning text-dark fs-6 px-3" title="Students with existing accounts">
                                <i class="bi bi-key me-1"></i>
                                <span id="massExistingCount">0</span> existing
                            </span>
                        </div>
                    </div>

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
                            <span class="badge bg-success">No Account</span> = will be created. &nbsp;
                            <span class="badge bg-warning text-dark">Has Account</span> = can revoke password &amp; reprint credentials anytime.
                        </small>
                        <button type="button" class="btn btn-primary" id="massStep1Next" disabled>
                            Next: Configure <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 2: Configure ── --}}
                <div id="massStep2" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-info py-2 mb-4" id="batchSummaryAlert">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="batchSummaryText"></span>
                    </div>

                    <div class="row g-4">

                        {{-- Role selector --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign Role <span class="text-danger">*</span></label>
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
                                                <span class="badge bg-success ms-1"><i class="bi bi-lock-fill me-1"></i>Required</span>
                                            @else
                                                <span class="badge bg-secondary ms-1"><i class="bi bi-lock me-1"></i>Restricted</span>
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
                            <label class="form-label fw-semibold">Password Strategy <span class="text-danger">*</span></label>

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

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="revokeExistingPasswords" value="1">
                                    <label class="form-check-label" for="revokeExistingPasswords">
                                        <span class="fw-semibold text-danger">
                                            <i class="bi bi-key me-1"></i>Revoke &amp; reset passwords
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            Resets to <code>ChangeMe@123</code>. Useful when students forget their password.
                                            Credential slips will be printable after.
                                        </small>
                                    </label>
                                </div>

                                {{-- NEW: Print-only option --}}
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="printExistingOnly" value="1">
                                    <label class="form-check-label" for="printExistingOnly">
                                        <span class="fw-semibold text-primary">
                                            <i class="bi bi-printer me-1"></i>Reprint credential slips only
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            Fetches current login details and generates printable slips —
                                            <strong>without changing any passwords</strong>.
                                        </small>
                                    </label>
                                </div>

                                <div class="alert alert-warning py-2 mt-2 mb-0" id="bothOptionsWarning" style="display:none!important">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    "Revoke" takes priority over "Reprint only" — passwords <em>will</em> be reset.
                                </div>
                            </div>

                            {{-- Only-existing alert (no new students) --}}
                            <div id="onlyExistingAlert" class="d-none">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    All selected students already have accounts.
                                    Either <strong>Revoke passwords</strong> to reset them,
                                    <strong>Reprint</strong> to get their current slips,
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

                {{-- ── STEP 3: Review & Execute ── --}}
                <div id="massStep3" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-warning py-2 mb-3" id="step3Warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Review details below, then click <strong>Confirm & Execute</strong>.
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
                        <div class="col-auto d-none" id="reviewReprintBadge">
                            <span class="badge bg-primary fs-6">
                                <i class="bi bi-printer me-1"></i>+ Reprint existing credentials
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
                            <button type="button" class="btn btn-outline-primary btn-sm d-none" id="printCreatedBtn">
                                <i class="bi bi-printer me-1"></i>Print New Accounts
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm d-none" id="printRevokedBtn">
                                <i class="bi bi-printer me-1"></i>Print Revoked Passwords
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm d-none" id="printReprintedBtn">
                                <i class="bi bi-printer me-1"></i>Print Existing Credentials
                            </button>
                            <button type="button" class="btn btn-success btn-sm d-none" id="printAllBtn">
                                <i class="bi bi-printer-fill me-1"></i>Print All Slips
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-dismiss="modal" onclick="location.reload()">
                                <i class="bi bi-x me-1"></i>Close &amp; Refresh
                            </button>
                        </div>
                    </div>

                    {{-- Created --}}
                    <div id="step4CreatedSection" class="d-none mb-3">
                        <h6 class="text-success mb-2">
                            <i class="bi bi-plus-circle me-1"></i>
                            New Accounts Created
                            <span class="badge bg-success ms-1" id="step4CreatedCount">0</span>
                        </h6>
                        <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr><th>#</th><th>Name</th><th>Admission No</th><th>Email / Username</th><th>Password</th></tr>
                                </thead>
                                <tbody id="createdResultsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Revoked --}}
                    <div id="step4RevokedSection" class="d-none mb-3">
                        <h6 class="text-danger mb-2">
                            <i class="bi bi-key me-1"></i>
                            Passwords Revoked &amp; Reset
                            <span class="badge bg-danger ms-1" id="step4RevokedCount">0</span>
                        </h6>
                        <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr><th>#</th><th>Name</th><th>Admission No</th><th>Email / Username</th><th>New Password</th></tr>
                                </thead>
                                <tbody id="revokedResultsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Reprinted (fetch-only, no password change) --}}
                    <div id="step4ReprintSection" class="d-none mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-printer me-1"></i>
                            Existing Credentials Fetched for Reprint
                            <span class="badge bg-primary ms-1" id="step4ReprintCount">0</span>
                        </h6>
                        <div class="alert alert-info py-2 mb-2">
                            <i class="bi bi-info-circle me-1"></i>
                            These passwords were <strong>not changed</strong>. Slips show the reset placeholder
                            <code>ChangeMe@123</code> — if the student has already changed their password,
                            use <em>Revoke</em> instead to generate a fresh known password.
                        </div>
                        <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr><th>#</th><th>Name</th><th>Admission No</th><th>Email / Username</th><th>Slip Password</th></tr>
                                </thead>
                                <tbody id="reprintResultsBody"></tbody>
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
                    They should change it immediately after logging in.
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
     PRINT CREDENTIALS MODAL (reprint for a single user anytime)
     ══════════════════════════════════════════════════════════════ --}}
<div id="printCredentialModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-printer me-2"></i>Print / View Credentials
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="printCredentialBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading credentials...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning d-none" id="revokeAndPrintBtn">
                    <i class="bi bi-key me-1"></i>Revoke &amp; Print New Password
                </button>
                <button type="button" class="btn btn-primary" id="doPrintCredentialBtn">
                    <i class="bi bi-printer me-1"></i>Print Slip
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
.mass-step { display:flex; flex-direction:column; align-items:center; gap:4px; }
.step-circle {
    width:32px; height:32px; border-radius:50%;
    background:#dee2e6; color:#6c757d;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.85rem; transition:background .3s,color .3s;
}
.step-label { font-size:.72rem; color:#6c757d; white-space:nowrap; }
.mass-step.active .step-circle { background:#0d6efd; color:#fff; }
.mass-step.done   .step-circle { background:#198754; color:#fff; }
.mass-step.active .step-label  { color:#0d6efd; font-weight:600; }
.step-line { height:2px; background:#dee2e6; min-width:20px; }

/* Row tints */
tr.row-has-account td        { background-color:rgba(255,193,7,.06)!important; }
tr.row-has-account:hover td  { background-color:rgba(255,193,7,.14)!important; }

/* Credential preview card inside modal */
.cred-card {
    border:1.5px solid #dee2e6; border-radius:8px; padding:1rem 1.25rem;
    background:#f8f9fa;
}
.cred-card .cred-label { font-size:.75rem; text-transform:uppercase; color:#6c757d; letter-spacing:.5px; font-weight:600; }
.cred-card .cred-value { font-size:1rem; font-weight:600; }
.cred-card .cred-pwd   { font-family:'Courier New',monospace; font-size:1.15rem; color:#0a7c3e; letter-spacing:2px; font-weight:700; }

/* Print styles */
@media print {
    body * { visibility:hidden!important; }
    #credentialPrintArea, #credentialPrintArea * { visibility:visible!important; }
    #credentialPrintArea { position:fixed; top:0; left:0; width:100%; }
}
</style>

{{-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ═══════════════════════════════════════
       STATE
    ═══════════════════════════════════════ */
    let allStudents      = [];
    let filteredStudents = [];
    let selectedIds      = new Set();

    // Result buckets (for per-group print buttons)
    let bucket = { created: [], revoked: [], reprinted: [] };

    // Single-user print state
    let printCredUser = null; // { id, name, email, username, admissionNo, password }

    /* ═══════════════════════════════════════
       DOM REFS
    ═══════════════════════════════════════ */
    const modalEl       = document.getElementById('massStudentModal');
    const tbody         = document.getElementById('massStudentTableBody');
    const searchInput   = document.getElementById('massStudentSearch');
    const classFilter   = document.getElementById('massClassFilter');
    const armFilter     = document.getElementById('massArmFilter');
    const accountFilter = document.getElementById('massAccountFilter');
    const checkAll      = document.getElementById('massCheckAll');
    const selectedCount = document.getElementById('massSelectedCount');
    const newCount      = document.getElementById('massNewCount');
    const existingCount = document.getElementById('massExistingCount');
    const step1Next     = document.getElementById('massStep1Next');

    /* ═══════════════════════════════════════
       LOAD STUDENTS ON OPEN
    ═══════════════════════════════════════ */
    modalEl?.addEventListener('show.bs.modal', () => {
        resetMassModal();
        loadAllStudents();
    });

    function loadAllStudents() {
        fetch('{{ route("get.students") }}?limit=2000')
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(data => {
                if (!data.success) {
                    tbody.innerHTML = errRow(`Failed to load: ${escHtml(data.message || '')}`);
                    return;
                }
                allStudents = data.students;

                if (data.arms?.length) {
                    armFilter.innerHTML = '<option value="">All Arms</option>' +
                        data.arms.map(a => `<option value="${escAttr(String(a.id))}">${escHtml(a.name)}</option>`).join('');
                }

                if (data.classes?.length) {
                    classFilter.innerHTML = '<option value="">All Classes</option>' +
                        data.classes.map(c =>
                            `<option value="${escAttr(String(c.id))}" data-arm-id="${escAttr(String(c.arm_id||''))}">`
                            + escHtml(c.name) + (c.arm_name ? ` (${escHtml(c.arm_name)})` : '') + `</option>`
                        ).join('');
                }

                filteredStudents = [...allStudents];
                renderStudentTable();
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = errRow('Network error – please try again.');
            });
    }

    function errRow(msg) {
        return `<tr><td colspan="6" class="text-center text-danger py-3"><i class="bi bi-exclamation-circle me-2"></i>${msg}</td></tr>`;
    }

    /* ═══════════════════════════════════════
       RENDER TABLE
    ═══════════════════════════════════════ */
    function renderStudentTable() {
        if (!filteredStudents.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No students found</td></tr>`;
            updateSelectedCount();
            return;
        }

        tbody.innerHTML = filteredStudents.map(s => {
            const has     = s.has_account;
            const checked = selectedIds.has(String(s.id)) ? 'checked' : '';
            const rowCls  = has ? 'row-has-account' : '';
            return `<tr class="${rowCls}">
                <td>
                    <input type="checkbox" class="form-check-input mass-row-check"
                           value="${s.id}" ${checked}
                           data-name="${escAttr(s.name)}"
                           data-admission="${escAttr(s.admissionNo||'')}"
                           data-email="${escAttr(s.email||'')}"
                           data-has-account="${has?'1':'0'}">
                </td>
                <td>${escHtml(s.name)}</td>
                <td><code>${escHtml(s.admissionNo||'—')}</code></td>
                <td>${escHtml(s.class_name||'—')}</td>
                <td>${escHtml(s.arm_name||'—')}</td>
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
                else              selectedIds.delete(this.value);
                updateSelectedCount();
            });
        });
    }

    function getSelectionBreakdown() {
        const sel = allStudents.filter(s => selectedIds.has(String(s.id)));
        return {
            all:      sel,
            newOnes:  sel.filter(s => !s.has_account),
            existing: sel.filter(s =>  s.has_account),
        };
    }

    function updateSelectedCount() {
        const { all, newOnes, existing } = getSelectionBreakdown();
        selectedCount.textContent = all.length;
        newCount.textContent      = newOnes.length;
        existingCount.textContent = existing.length;
        step1Next.disabled        = all.length === 0;
        checkAll.checked       = all.length > 0 && all.length === filteredStudents.length;
        checkAll.indeterminate = all.length > 0 && !checkAll.checked;
    }

    /* ═══════════════════════════════════════
       FILTERS
    ═══════════════════════════════════════ */
    function applyFilter() {
        const q       = (searchInput.value||'').toLowerCase();
        const cls     = classFilter.value;
        const arm     = armFilter.value;
        const acctVal = accountFilter.value;

        filteredStudents = allStudents.filter(s => {
            const mQ    = !q       || s.name.toLowerCase().includes(q) || (s.admissionNo||'').toLowerCase().includes(q);
            const mCls  = !cls     || String(s.class_id) === cls;
            const mArm  = !arm     || String(s.arm_id)   === arm;
            const mAcct = !acctVal
                || (acctVal === 'no_account'  && !s.has_account)
                || (acctVal === 'has_account' &&  s.has_account);
            return mQ && mCls && mArm && mAcct;
        });
        renderStudentTable();
    }

    armFilter?.addEventListener('change', function () {
        const armId = this.value;
        Array.from(classFilter.options).forEach(opt => {
            if (!opt.value) return;
            opt.hidden = armId ? (opt.dataset.armId !== armId) : false;
        });
        if (classFilter.options[classFilter.selectedIndex]?.hidden) classFilter.value = '';
        applyFilter();
    });

    searchInput?.addEventListener('input',    debounce(applyFilter, 250));
    classFilter?.addEventListener('change',   applyFilter);
    accountFilter?.addEventListener('change', applyFilter);

    /* Select All / Clear */
    checkAll?.addEventListener('change', function () {
        filteredStudents.forEach(s => {
            if (this.checked) selectedIds.add(String(s.id));
            else              selectedIds.delete(String(s.id));
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

    /* ═══════════════════════════════════════
       STEP NAVIGATION
    ═══════════════════════════════════════ */
    function goStep(n) {
        [1,2,3,4].forEach(i => {
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

    /* ═══════════════════════════════════════
       STEP 2 SETUP
    ═══════════════════════════════════════ */
    function setupStep2() {
        const { newOnes, existing } = getSelectionBreakdown();
        const hasNew      = newOnes.length > 0;
        const hasExisting = existing.length > 0;

        const parts = [];
        if (hasNew)      parts.push(`<strong>${newOnes.length}</strong> without accounts <span class="badge bg-success">will be created</span>`);
        if (hasExisting) parts.push(`<strong>${existing.length}</strong> with existing accounts <span class="badge bg-warning text-dark">manageable</span>`);
        document.getElementById('batchSummaryText').innerHTML = parts.join(' &nbsp;+&nbsp; ');

        document.getElementById('newStudentsConfig').classList.toggle('d-none',     !hasNew);
        document.getElementById('existingStudentsConfig').classList.toggle('d-none', !hasExisting);
        document.getElementById('onlyExistingAlert').classList.toggle('d-none',      hasNew || !hasExisting);

        document.getElementById('newStudentsConfigCount').textContent      = newOnes.length;
        document.getElementById('existingStudentsConfigCount').textContent = existing.length;

        // Reset checkboxes
        const revokeEl = document.getElementById('revokeExistingPasswords');
        const reprintEl = document.getElementById('printExistingOnly');
        if (revokeEl)  revokeEl.checked  = false;
        if (reprintEl) reprintEl.checked = false;
    }

    // Mutual-awareness warning when both revoke + reprint checked
    ['revokeExistingPasswords','printExistingOnly'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => {
            const r = document.getElementById('revokeExistingPasswords')?.checked;
            const p = document.getElementById('printExistingOnly')?.checked;
            const w = document.getElementById('bothOptionsWarning');
            if (w) w.style.setProperty('display', (r && p) ? 'block' : 'none', 'important');
        });
    });

    /* Password type toggle */
    document.querySelectorAll('input[name="massPasswordType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('sharedPasswordGroup')    .classList.toggle('d-none', this.value !== 'same');
            document.getElementById('individualPasswordGroup').classList.toggle('d-none', this.value !== 'individual');
        });
    });

    document.getElementById('generateSharedPwd')?.addEventListener('click', () => {
        document.getElementById('massSharedPassword').value =
            Math.random().toString(36).slice(-6).toUpperCase() + Math.floor(1000 + Math.random()*9000);
    });

    /* ═══════════════════════════════════════
       STEP 2 → STEP 3 VALIDATION
    ═══════════════════════════════════════ */
    document.getElementById('massStep2Next')?.addEventListener('click', () => {
        const { newOnes, existing } = getSelectionBreakdown();
        const pwdType    = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke = document.getElementById('revokeExistingPasswords')?.checked;
        const willReprint = document.getElementById('printExistingOnly')?.checked;

        if (newOnes.length > 0 && pwdType === 'same') {
            if ((document.getElementById('massSharedPassword').value||'').trim().length < 6) {
                alert('Please enter a shared password of at least 6 characters.'); return;
            }
        }

        if (newOnes.length === 0 && !willRevoke && !willReprint) {
            alert('All selected students already have accounts. Choose "Revoke passwords" and/or "Reprint credential slips" to proceed, or go back and select students without accounts.');
            return;
        }

        buildReviewStep();
        goStep(3);
    });

    /* ═══════════════════════════════════════
       BUILD REVIEW STEP
    ═══════════════════════════════════════ */
    function buildReviewStep() {
        const { all, newOnes, existing } = getSelectionBreakdown();
        const pwdType     = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke  = document.getElementById('revokeExistingPasswords')?.checked;
        const willReprint = document.getElementById('printExistingOnly')?.checked;

        document.getElementById('reviewStudentCount').textContent = `${all.length} student(s)`;
        document.getElementById('reviewRoles').textContent        = 'Role: student';
        document.getElementById('reviewPwdType').textContent      =
            newOnes.length === 0 ? 'No new accounts'
            : (pwdType === 'same' ? 'Shared Password' : 'Individual Passwords');

        document.getElementById('reviewRevokeBadge')?.classList.toggle('d-none',  !willRevoke  || existing.length === 0);
        document.getElementById('reviewReprintBadge')?.classList.toggle('d-none', !willReprint || existing.length === 0);

        const actions = [];
        if (newOnes.length > 0)           actions.push(`create ${newOnes.length} new account(s)`);
        if (willRevoke && existing.length) actions.push(`revoke passwords for ${existing.length} existing account(s)`);
        if (willReprint && existing.length && !willRevoke) actions.push(`fetch credentials for ${existing.length} existing account(s) to reprint`);

        const w = document.getElementById('step3Warning');
        if (w) w.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>
            You are about to: <strong>${actions.join(' and ')}</strong>.
            ${willRevoke ? 'Password changes <strong>cannot</strong> be undone.' : ''}`;

        const btnLabel = document.getElementById('massCreateBtnLabel');
        if (btnLabel) btnLabel.textContent = 'Confirm & Execute';

        document.getElementById('reviewTableBody').innerHTML = all
            .slice().sort((a,b) => a.name.localeCompare(b.name))
            .map((s, i) => {
                let badge;
                if (!s.has_account)       badge = '<span class="badge bg-primary">Create account</span>';
                else if (willRevoke)      badge = '<span class="badge bg-danger">Revoke password</span>';
                else if (willReprint)     badge = '<span class="badge bg-info text-dark">Reprint only</span>';
                else                     badge = '<span class="badge bg-secondary">No change</span>';

                return `<tr>
                    <td>${i+1}</td>
                    <td>${escHtml(s.name)}</td>
                    <td><code>${escHtml(s.admissionNo||'—')}</code></td>
                    <td>${escHtml(s.class_name||'—')}${s.arm_name ? `<span class="text-muted"> / ${escHtml(s.arm_name)}</span>` : ''}</td>
                    <td>${s.email ? escHtml(s.email) : '<em class="text-muted">auto-generate</em>'}</td>
                    <td>${badge}</td>
                </tr>`;
            }).join('');
    }

    /* ═══════════════════════════════════════
       MAIN EXECUTE
    ═══════════════════════════════════════ */
    document.getElementById('massCreateBtn')?.addEventListener('click', async function () {
        const { newOnes, existing } = getSelectionBreakdown();
        const pwdType     = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const willRevoke  = document.getElementById('revokeExistingPasswords')?.checked;
        const willReprint = document.getElementById('printExistingOnly')?.checked;
        const sharedPwd   = document.getElementById('massSharedPassword').value;

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        bucket = { created: [], revoked: [], reprinted: [] };
        const skippedNames = [];
        const messages     = [];

        /* 1 — Create new accounts */
        if (newOnes.length > 0) {
            try {
                const r    = await apiFetch('{{ route("users.mass-create-students") }}', {
                    students:        newOnes.map(s => ({ student_id: s.id })),
                    roles:           ['student'],
                    password_type:   pwdType,
                    shared_password: pwdType === 'same' ? sharedPwd : null,
                });
                if (r.created?.length) bucket.created = r.created;
                if (r.skipped?.length) skippedNames.push(...r.skipped);
                if (r.message)         messages.push(r.message);
            } catch (e) {
                console.error('Create error:', e);
                messages.push('Error creating some accounts.');
            }
        }

        /* 2 — Revoke existing passwords (takes priority over reprint) */
        if (willRevoke && existing.length > 0) {
            try {
                const r = await apiFetch('{{ route("users.revoke-student-password") }}', {
                    student_ids: existing.map(s => s.id),
                });
                if (r.revoked?.length) {
                    bucket.revoked = r.revoked;
                } else if (r.count) {
                    bucket.revoked = existing.map(s => ({
                        name: s.name, admissionNo: s.admissionNo,
                        email: s.email||'', username: (s.admissionNo||'').replace(/[\/\\]/g,'_'),
                        password: 'ChangeMe@123',
                    }));
                }
                if (r.message) messages.push(r.message);
            } catch (e) {
                console.error('Revoke error:', e);
                messages.push('Error revoking some passwords.');
            }
        }

        /* 3 — Reprint only (no password change) */
        else if (willReprint && existing.length > 0) {
            try {
                const r = await apiFetch('{{ route("users.get-student-credentials") }}', {
                    student_ids: existing.map(s => s.id),
                });
                if (r.credentials?.length) {
                    bucket.reprinted = r.credentials;
                } else {
                    // Fallback: build skeleton (password unknown, show placeholder)
                    bucket.reprinted = existing.map(s => ({
                        name: s.name, admissionNo: s.admissionNo,
                        email: s.email||'', username: (s.admissionNo||'').replace(/[\/\\]/g,'_'),
                        password: 'ChangeMe@123',
                    }));
                }
                if (r.message) messages.push(r.message || `Fetched credentials for ${existing.length} student(s).`);
            } catch (e) {
                console.error('Reprint fetch error:', e);
                // Still build skeleton on failure
                bucket.reprinted = existing.map(s => ({
                    name: s.name, admissionNo: s.admissionNo,
                    email: s.email||'', username: (s.admissionNo||'').replace(/[\/\\]/g,'_'),
                    password: 'ChangeMe@123',
                }));
                messages.push(`Fetched ${existing.length} credential slip(s) from selection.`);
            }
        }

        setBtn(this, false, '<i class="bi bi-person-check me-2"></i>Confirm & Execute');

        showResultsStep(skippedNames, messages.join(' '));
    });

    /* ═══════════════════════════════════════
       SHOW RESULTS STEP
    ═══════════════════════════════════════ */
    function showResultsStep(skipped, message) {
        document.getElementById('massResultAlert').innerHTML =
            `<i class="bi bi-check-circle me-2"></i>${escHtml(message || 'Done.')}`;

        /* Created */
        const createdSec = document.getElementById('step4CreatedSection');
        createdSec.classList.toggle('d-none', bucket.created.length === 0);
        if (bucket.created.length) {
            document.getElementById('step4CreatedCount').textContent = bucket.created.length;
            document.getElementById('createdResultsBody').innerHTML  = credRows(bucket.created);
        }

        /* Revoked */
        const revokedSec = document.getElementById('step4RevokedSection');
        revokedSec.classList.toggle('d-none', bucket.revoked.length === 0);
        if (bucket.revoked.length) {
            document.getElementById('step4RevokedCount').textContent = bucket.revoked.length;
            document.getElementById('revokedResultsBody').innerHTML  = credRows(bucket.revoked, 'text-danger');
        }

        /* Reprinted */
        const reprintSec = document.getElementById('step4ReprintSection');
        reprintSec.classList.toggle('d-none', bucket.reprinted.length === 0);
        if (bucket.reprinted.length) {
            document.getElementById('step4ReprintCount').textContent = bucket.reprinted.length;
            document.getElementById('reprintResultsBody').innerHTML  = credRows(bucket.reprinted, 'text-primary');
        }

        /* Print buttons */
        const pCreated  = document.getElementById('printCreatedBtn');
        const pRevoked  = document.getElementById('printRevokedBtn');
        const pReprint  = document.getElementById('printReprintedBtn');
        const pAll      = document.getElementById('printAllBtn');

        pCreated?.classList.toggle('d-none',  bucket.created.length === 0);
        pRevoked?.classList.toggle('d-none',  bucket.revoked.length === 0);
        pReprint?.classList.toggle('d-none',  bucket.reprinted.length === 0);

        const total = bucket.created.length + bucket.revoked.length + bucket.reprinted.length;
        pAll?.classList.toggle('d-none', total < 2); // show "Print All" only when multiple groups

        /* Skipped */
        if (skipped?.length) {
            document.getElementById('massSkippedInfo').classList.remove('d-none');
            document.getElementById('massSkippedNames').textContent = skipped.join(', ');
        }

        goStep(4);
    }

    function credRows(accounts, pwdClass = 'text-success') {
        return accounts.map((u, i) => `
            <tr>
                <td>${i+1}</td>
                <td><strong>${escHtml(u.name)}</strong></td>
                <td><code>${escHtml(u.admissionNo||'—')}</code></td>
                <td>${escHtml(u.email||'—')}<br><small class="text-muted">@${escHtml(u.username||'—')}</small></td>
                <td><code class="${pwdClass}">${escHtml(u.password||'ChangeMe@123')}</code></td>
            </tr>
        `).join('');
    }

    /* ═══════════════════════════════════════
       PER-BUCKET PRINT BUTTONS
    ═══════════════════════════════════════ */
    document.getElementById('printCreatedBtn')?.addEventListener('click',  () => printSlips(bucket.created,   'New Accounts'));
    document.getElementById('printRevokedBtn')?.addEventListener('click',  () => printSlips(bucket.revoked,   'Revoked Passwords'));
    document.getElementById('printReprintedBtn')?.addEventListener('click',() => printSlips(bucket.reprinted, 'Existing Credentials'));
    document.getElementById('printAllBtn')?.addEventListener('click', () =>
        printSlips([...bucket.created, ...bucket.revoked, ...bucket.reprinted], 'All Credentials')
    );

    /* ═══════════════════════════════════════
       PRINT SLIP RENDERER
    ═══════════════════════════════════════ */
    function printSlips(accounts, titleSuffix = 'Credentials') {
        if (!accounts.length) { alert('No credentials to print.'); return; }

        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'School Portal';
        const today      = new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'long', year:'numeric' });

        const slipsHtml = accounts.map(u => `
            <div class="slip">
                <div class="slip-header">
                    <span class="school-name">${escHtml(schoolName)}</span>
                    <span class="slip-title">Student Portal Access</span>
                    <span class="slip-date">${today}</span>
                </div>
                <table class="slip-table">
                    <tr><td class="label">Student Name</td><td class="value"><strong>${escHtml(u.name)}</strong></td></tr>
                    <tr><td class="label">Admission No</td><td class="value">${escHtml(u.admissionNo||'—')}</td></tr>
                    <tr><td class="label">Login Email</td> <td class="value">${escHtml(u.email||'—')}</td></tr>
                    <tr><td class="label">Username</td>    <td class="value">${escHtml(u.username||'—')}</td></tr>
                    <tr><td class="label">Password</td>    <td class="value password-cell">${escHtml(u.password||'ChangeMe@123')}</td></tr>
                </table>
                <p class="slip-note">⚠ Change your password after first login. Keep this slip safe.</p>
            </div>
        `).join('');

        const win = window.open('', '_blank', 'width=820,height=940');
        if (!win) { alert('Pop-up blocked — please allow pop-ups for this site.'); return; }

        win.document.write(`<!DOCTYPE html><html><head>
<meta charset="UTF-8">
<title>Credential Slips — ${escHtml(titleSuffix)}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:12pt;background:#fff;color:#111}
.page-title{text-align:center;font-size:14pt;font-weight:bold;padding:10mm 0 4mm;border-bottom:2px solid #000;margin-bottom:6mm}
.slip{border:1.5px solid #333;border-radius:4px;padding:5mm 7mm;page-break-inside:avoid}
.slip+.slip{border-top:2px dashed #888}
.slip-header{display:flex;justify-content:space-between;align-items:baseline;border-bottom:1px solid #ccc;padding-bottom:3mm;margin-bottom:3mm}
.school-name{font-weight:800;font-size:11pt}
.slip-title{font-size:9pt;color:#555;font-style:italic}
.slip-date{font-size:8pt;color:#888}
.slip-table{width:100%;border-collapse:collapse}
.slip-table td{padding:1.5mm 2mm;vertical-align:top}
.label{width:38%;font-size:9pt;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.4px}
.value{font-size:11pt}
.password-cell{font-family:'Courier New',monospace;font-size:13pt;font-weight:bold;letter-spacing:2px;color:#0a3}
.slip-note{font-size:7.5pt;color:#888;margin-top:3mm;font-style:italic}
@media print{@page{margin:10mm 12mm;size:A4 portrait}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
</style></head><body>
<div class="page-title">Student Portal — ${escHtml(titleSuffix)} — ${today}</div>
${slipsHtml}
</body></html>`);
        win.document.close();
        win.focus();
        win.onload = () => win.print();
    }

    /* ═══════════════════════════════════════
       SINGLE-USER REVOKE (from user list)
    ═══════════════════════════════════════ */
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

        apiFetch('{{ route("users.revoke-student-password") }}', { user_ids: [userId] })
            .then(data => {
                setBtn(this, false, '<i class="bi bi-key me-1"></i>Revoke Password');
                bootstrap.Modal.getInstance(document.getElementById('revokePasswordModal')).hide();

                if (data.success && data.revoked?.length) {
                    // After revoke, offer to print the slip immediately
                    printCredUser = data.revoked[0];
                    showPrintCredentialModal(printCredUser, true);
                } else {
                    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Done!' : 'Error', text: data.message });
                }
            })
            .catch(err => {
                setBtn(this, false, '<i class="bi bi-key me-1"></i>Revoke Password');
                console.error(err);
                alert('Network error. Please try again.');
            });
    });

    /* ═══════════════════════════════════════
       SINGLE-USER PRINT CREDENTIAL (from user list)
       Triggered by [data-print-user-id] buttons in the table
    ═══════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-print-user-id]');
        if (!btn) return;

        const userId   = btn.dataset.printUserId;
        const userName = btn.dataset.printUserName || '—';

        // Show modal with loading state
        const credModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('printCredentialModal'));
        document.getElementById('printCredentialBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading credentials for <strong>${escHtml(userName)}</strong>...</p>
            </div>`;
        document.getElementById('doPrintCredentialBtn').classList.remove('d-none');
        document.getElementById('revokeAndPrintBtn').classList.remove('d-none');
        credModal.show();

        // Fetch credentials
        apiFetch('{{ route("users.get-student-credentials") }}', { user_ids: [userId] })
            .then(data => {
                const cred = data.credentials?.[0] || null;
                if (cred) {
                    printCredUser = cred;
                    renderCredPreview(cred, false);
                } else {
                    document.getElementById('printCredentialBody').innerHTML =
                        `<div class="alert alert-warning">Could not fetch credentials for this student. Try revoking their password first to generate a known credential slip.</div>`;
                    document.getElementById('revokeAndPrintBtn').classList.remove('d-none');
                }
            })
            .catch(() => {
                document.getElementById('printCredentialBody').innerHTML =
                    `<div class="alert alert-danger">Network error. Please try again.</div>`;
            });
    });

    function renderCredPreview(cred, justRevoked) {
        const notice = justRevoked
            ? `<div class="alert alert-success py-2 mb-3"><i class="bi bi-check-circle me-1"></i>Password has been reset to the value below.</div>`
            : `<div class="alert alert-info py-2 mb-3"><i class="bi bi-info-circle me-1"></i>
                Showing login details for this student. If they've changed their password since last reset, use
                <strong>Revoke &amp; Print</strong> to generate a fresh known password.</div>`;

        document.getElementById('printCredentialBody').innerHTML = `
            ${notice}
            <div class="cred-card">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="cred-label">Student Name</div>
                        <div class="cred-value">${escHtml(cred.name)}</div>
                    </div>
                    <div class="col-6">
                        <div class="cred-label">Admission No</div>
                        <div class="cred-value"><code>${escHtml(cred.admissionNo||'—')}</code></div>
                    </div>
                    <div class="col-6">
                        <div class="cred-label">Login Email</div>
                        <div class="cred-value">${escHtml(cred.email||'—')}</div>
                    </div>
                    <div class="col-6">
                        <div class="cred-label">Username</div>
                        <div class="cred-value">${escHtml(cred.username||'—')}</div>
                    </div>
                    <div class="col-12">
                        <div class="cred-label">Password</div>
                        <div class="cred-pwd">${escHtml(cred.password||'ChangeMe@123')}</div>
                        ${!justRevoked ? '<small class="text-muted">This is the last known reset password. Student may have changed it.</small>' : ''}
                    </div>
                </div>
            </div>`;

        // Hide "Revoke & Print" if just revoked
        document.getElementById('revokeAndPrintBtn').classList.toggle('d-none', justRevoked);
    }

    function showPrintCredentialModal(cred, justRevoked) {
        printCredUser = cred;
        renderCredPreview(cred, justRevoked);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('printCredentialModal')).show();
    }

    document.getElementById('doPrintCredentialBtn')?.addEventListener('click', () => {
        if (printCredUser) printSlips([printCredUser], 'Credential Slip');
    });

    document.getElementById('revokeAndPrintBtn')?.addEventListener('click', function () {
        const userId = document.getElementById('revokeTargetUserId').value
            || document.getElementById('printCredentialBody')
                .querySelector('[data-user-id]')?.dataset.userId;

        // Find user id from the loaded cred if available
        const uid = printCredUser?.id;
        if (!uid) { alert('Cannot determine user ID. Please use the Revoke button in the user list.'); return; }

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Revoking...');

        apiFetch('{{ route("users.revoke-student-password") }}', { user_ids: [uid] })
            .then(data => {
                setBtn(this, false, '<i class="bi bi-key me-1"></i>Revoke &amp; Print New Password');
                if (data.success && data.revoked?.length) {
                    printCredUser = data.revoked[0];
                    renderCredPreview(printCredUser, true);
                } else {
                    alert(data.message || 'Revoke failed.');
                }
            })
            .catch(err => {
                setBtn(this, false, '<i class="bi bi-key me-1"></i>Revoke &amp; Print New Password');
                console.error(err);
                alert('Network error.');
            });
    });

    /* ═══════════════════════════════════════
       RESET MODAL
    ═══════════════════════════════════════ */
    function resetMassModal() {
        selectedIds.clear();
        bucket = { created: [], revoked: [], reprinted: [] };
        allStudents = filteredStudents = [];

        ['massStudentSearch','massSharedPassword'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        ['massClassFilter','massArmFilter','massAccountFilter'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

        document.getElementById('massSkippedInfo')?.classList.add('d-none');
        document.getElementById('massResultAlert').innerHTML = '';
        document.getElementById('massSelectedCount').textContent = '0';
        document.getElementById('massNewCount').textContent      = '0';
        document.getElementById('massExistingCount').textContent = '0';

        const revokeEl  = document.getElementById('revokeExistingPasswords');
        const reprintEl = document.getElementById('printExistingOnly');
        if (revokeEl)  revokeEl.checked  = false;
        if (reprintEl) reprintEl.checked = false;

        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading students...
        </td></tr>`;

        document.querySelectorAll('.mass-step').forEach((s, i) => {
            s.classList.toggle('active', i === 0);
            s.classList.remove('done');
        });

        const samePwd = document.getElementById('pwdTypeSame');
        if (samePwd) { samePwd.checked = true; samePwd.dispatchEvent(new Event('change')); }

        goStep(1);
    }

    /* ═══════════════════════════════════════
       UTILITIES
    ═══════════════════════════════════════ */
    function debounce(fn, ms) {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    }
    function escHtml(s) {
        if (s == null) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function escAttr(s) { return escHtml(s).replace(/'/g,'&#39;'); }
    function getCsrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
    function setBtn(btn, disabled, html) { btn.disabled = disabled; btn.innerHTML = html; }

    async function apiFetch(url, payload) {
        const r = await fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body:    JSON.stringify(payload),
        });
        return r.json();
    }
});
</script>
