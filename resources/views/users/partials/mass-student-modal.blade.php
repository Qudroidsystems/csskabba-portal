{{-- ============================================================
     resources/views/users/partials/mass-student-modal.blade.php
     Include in users/index.blade.php with:
       @include('users.partials.mass-student-modal')
     ============================================================ --}}

{{-- ══════════════════════════════════════════════════════════════
     MASS CREATE STUDENT ACCOUNTS MODAL
     ══════════════════════════════════════════════════════════════ --}}
<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-people-fill me-2 text-warning"></i>Mass Create Student Accounts
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

                        {{-- Select / Clear buttons --}}
                        <div class="col-md-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="massSelectAll">
                                <i class="bi bi-check-all me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="massClearAll">
                                <i class="bi bi-x-lg me-1"></i>Clear
                            </button>
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
                                    <th>Status</th>
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

                        {{-- Role selector: student locked, all others disabled --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Assign Role <span class="text-danger">*</span>
                            </label>

                            {{-- Hidden input always submits "student" --}}
                            <input type="hidden" id="massRoleHidden" value="student">

                            <div class="border rounded p-3 bg-light">
                                @foreach (\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox"
                                               id="massRole_{{ $role->name }}"
                                               value="{{ $role->name }}"
                                               {{ $role->name === 'Student' ? 'checked' : '' }}
                                               disabled>
                                        <label class="form-check-label {{ $role->name !== 'student' ? 'text-muted' : 'fw-semibold text-success' }}"
                                               for="massRole_{{ $role->name }}">
                                            {{ $role->name }}
                                            @if($role->name === 'Student')
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
                                        Only the <strong>student</strong> role can be assigned via mass creation
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
                                    Same password for all
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="massPasswordType"
                                       id="pwdTypeIndividual" value="individual">
                                <label class="form-check-label" for="pwdTypeIndividual">
                                    Auto-generate unique password per student
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
                                        <i class="bi bi-shuffle"></i>
                                    </button>
                                </div>
                                <small class="text-muted">All students will receive this password.</small>
                            </div>

                            {{-- Individual password info --}}
                            <div id="individualPasswordGroup" class="d-none">
                                <div class="alert alert-info py-2 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    A unique password will be auto-generated for each student.
                                    You'll see all passwords in the printout on the next step.
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
                        <button type="button" class="btn btn-success px-4" id="massCreateBtn">
                            <i class="bi bi-person-check me-2"></i><span id="massCreateBtnLabel">Create Accounts</span>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 4: Results ── --}}
                <div id="massStep4" class="mass-step-panel px-4 pb-4 d-none">

                    <div class="alert alert-success mb-3" id="massResultAlert"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold" id="step4Title">Created Accounts</h6>
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
                            <i class="bi bi-skip-forward me-1"></i>Skipped (already had accounts):
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
    let allStudents      = [];   // full list from server
    let filteredStudents = [];   // after search/filter
    let selectedIds      = new Set();
    let createdAccounts  = [];   // returned after creation for printing
    let currentMode      = 'create'; // 'create' | 'revoke'

    /* ═══════════════════════════════════════════════════════════
       DOM REFS
    ═══════════════════════════════════════════════════════════ */
    const modalEl       = document.getElementById('massStudentModal');
    const tbody         = document.getElementById('massStudentTableBody');
    const searchInput   = document.getElementById('massStudentSearch');
    const classFilter   = document.getElementById('massClassFilter');
    const armFilter     = document.getElementById('massArmFilter');
    const checkAll      = document.getElementById('massCheckAll');
    const selectedCount = document.getElementById('massSelectedCount');
    const alreadyCount  = document.getElementById('massAlreadyCount');
    const step1Next     = document.getElementById('massStep1Next');

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

                /* ── Populate Arm filter ── */
                if (data.arms?.length) {
                    armFilter.innerHTML = '<option value="">All Arms</option>' +
                        data.arms.map(a =>
                            `<option value="${escAttr(String(a.id))}">${escHtml(a.name)}</option>`
                        ).join('');
                }

                /* ── Populate Class filter ── */
                if (data.classes?.length) {
                    classFilter.innerHTML = '<option value="">All Classes</option>' +
                        data.classes.map(c =>
                            `<option value="${escAttr(String(c.id))}" data-arm-id="${escAttr(String(c.arm_id || ''))}">`
                            + escHtml(c.name) + (c.arm_name ? ` (${escHtml(c.arm_name)})` : '') + `</option>`
                        ).join('');
                } else {
                    // Fallback: build from student list
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
       RENDER TABLE
    ═══════════════════════════════════════════════════════════ */
    function renderStudentTable() {
        if (!filteredStudents.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No students found</td></tr>`;
            updateSelectedCount();
            return;
        }

        let alreadyHave = 0;
        tbody.innerHTML = filteredStudents.map(s => {
            const has      = s.has_account;
            const checked  = selectedIds.has(String(s.id)) ? 'checked' : '';
            const rowClass = has ? 'table-secondary text-muted' : '';
            if (has) alreadyHave++;

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
                        ? '<span class="badge bg-secondary">Has Account</span>'
                        : '<span class="badge bg-success">No Account</span>'
                    }
                </td>
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
        checkAll.checked       = selectedIds.size > 0 && selectedIds.size === eligible;
        checkAll.indeterminate = selectedIds.size > 0 && !checkAll.checked;
    }

    /* ═══════════════════════════════════════════════════════════
       FILTERS
    ═══════════════════════════════════════════════════════════ */
    function applyFilter() {
        const q   = (searchInput.value || '').toLowerCase();
        const cls = classFilter.value;
        const arm = armFilter.value;

        filteredStudents = allStudents.filter(s => {
            const matchQ   = !q   || s.name.toLowerCase().includes(q) || (s.admissionNo || '').toLowerCase().includes(q);
            const matchCls = !cls || String(s.class_id) === cls;
            const matchArm = !arm || String(s.arm_id)   === arm;
            return matchQ && matchCls && matchArm;
        });
        renderStudentTable();
    }

    /* When arm changes, hide classes that don't belong to that arm */
    armFilter?.addEventListener('change', function () {
        const armId = this.value;

        Array.from(classFilter.options).forEach(opt => {
            if (!opt.value) return; // keep "All Classes"
            const optArm = opt.dataset.armId || '';
            opt.hidden = armId ? (optArm !== armId) : false;
        });

        // If currently selected class is now hidden, reset it
        const sel = classFilter.options[classFilter.selectedIndex];
        if (sel?.hidden) classFilter.value = '';

        applyFilter();
    });

    searchInput?.addEventListener('input',  debounce(applyFilter, 250));
    classFilter?.addEventListener('change', applyFilter);

    /* ═══════════════════════════════════════════════════════════
       SELECT ALL / CLEAR
    ═══════════════════════════════════════════════════════════ */
    checkAll?.addEventListener('change', function () {
        filteredStudents.filter(s => !s.has_account).forEach(s => {
            if (this.checked) selectedIds.add(String(s.id));
            else selectedIds.delete(String(s.id));
        });
        renderStudentTable();
    });

    document.getElementById('massSelectAll')?.addEventListener('click', () => {
        allStudents.filter(s => !s.has_account).forEach(s => selectedIds.add(String(s.id)));
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
        goStep(2);
    });

    document.getElementById('massStep2Back')?.addEventListener('click', () => goStep(1));
    document.getElementById('massStep3Back')?.addEventListener('click', () => goStep(2));

    document.getElementById('massStep2Next')?.addEventListener('click', () => {
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        currentMode   = (pwdType === 'revoke') ? 'revoke' : 'create';

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

    /* ═══════════════════════════════════════════════════════════
       PASSWORD TYPE TOGGLE
    ═══════════════════════════════════════════════════════════ */
    document.querySelectorAll('input[name="massPasswordType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('sharedPasswordGroup')    .classList.toggle('d-none', this.value !== 'same');
            document.getElementById('individualPasswordGroup').classList.toggle('d-none', this.value !== 'individual');
            document.getElementById('revokePasswordGroup')    .classList.toggle('d-none', this.value !== 'revoke');

            const isRevoke = this.value === 'revoke';
            document.getElementById('massCreateBtnLabel').textContent = isRevoke ? 'Revoke Passwords' : 'Create Accounts';

            const warning = document.getElementById('step3Warning');
            if (warning) {
                warning.className = isRevoke
                    ? 'alert alert-danger py-2 mb-3'
                    : 'alert alert-warning py-2 mb-3';
                warning.innerHTML = isRevoke
                    ? '<i class="bi bi-exclamation-triangle me-1"></i> <strong>Warning:</strong> You are about to reset passwords. This cannot be undone.'
                    : '<i class="bi bi-exclamation-triangle me-1"></i> Review details below, then click <strong>Create Accounts</strong>. This cannot be undone.';
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
    function buildReviewStep() {
        const selected = allStudents.filter(s => selectedIds.has(String(s.id)));
        const pwdType  = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const isRevoke = pwdType === 'revoke';

        document.getElementById('reviewStudentCount').textContent = `${selected.length} student(s)`;
        document.getElementById('reviewRoles').textContent        = 'Role: student';
        document.getElementById('reviewPwdType').textContent      = isRevoke
            ? '⚠ Revoke Passwords'
            : (pwdType === 'same' ? 'Shared Password' : 'Individual Passwords');

        document.getElementById('reviewStatusHeader').textContent = isRevoke ? 'Will' : 'Action';
        document.getElementById('massCreateBtnLabel').textContent = isRevoke ? 'Revoke Passwords' : 'Create Accounts';

        document.getElementById('reviewTableBody').innerHTML = selected
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((s, i) => `<tr>
                <td>${i + 1}</td>
                <td>${escHtml(s.name)}</td>
                <td><code>${escHtml(s.admissionNo || '—')}</code></td>
                <td>
                    ${escHtml(s.class_name || '—')}
                    ${s.arm_name ? `<span class="text-muted"> / ${escHtml(s.arm_name)}</span>` : ''}
                </td>
                <td>${s.email ? escHtml(s.email) : '<em class="text-muted">auto-generate</em>'}</td>
                <td>
                    ${isRevoke
                        ? (s.has_account
                            ? '<span class="badge bg-danger">Revoke password</span>'
                            : '<span class="badge bg-secondary">No account – skip</span>')
                        : (s.has_account
                            ? '<span class="badge bg-warning text-dark">Skip (has account)</span>'
                            : '<span class="badge bg-primary">Create account</span>')
                    }
                </td>
            </tr>`
            ).join('');
    }

    /* ═══════════════════════════════════════════════════════════
       MAIN ACTION BUTTON — CREATE or REVOKE
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('massCreateBtn')?.addEventListener('click', function () {
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        if (pwdType === 'revoke') {
            doRevokePasswords(this);
        } else {
            doCreateAccounts(this, pwdType);
        }
    });

    /* ── CREATE ACCOUNTS ── */
    function doCreateAccounts(btn, pwdType) {
        const sharedPwd = document.getElementById('massSharedPassword').value;

        const payload = {
            students:        allStudents
                                .filter(s => selectedIds.has(String(s.id)))
                                .map(s => ({ student_id: s.id })),
            roles:           ['student'],
            password_type:   pwdType,
            shared_password: pwdType === 'same' ? sharedPwd : null,
        };

        setBtn(btn, true, '<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        fetch('{{ route("users.mass-create-students") }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            setBtn(btn, false, '<i class="bi bi-person-check me-2"></i>Create Accounts');

            if (!data.success && !data.created_count) {
                alert('Error: ' + (data.message || 'Unknown error'));
                return;
            }
            createdAccounts = data.created || [];
            showResultsStep(data, false);
        })
        .catch(err => {
            setBtn(btn, false, '<i class="bi bi-person-check me-2"></i>Create Accounts');
            console.error(err);
            alert('Network error. Please try again.');
        });
    }

    /* ── REVOKE PASSWORDS (mass) ── */
    function doRevokePasswords(btn) {
        // Only submit students who already have accounts
        const targets = allStudents.filter(s => selectedIds.has(String(s.id)) && s.has_account);

        if (!targets.length) {
            alert('None of the selected students have accounts to revoke.');
            return;
        }

        setBtn(btn, true, '<span class="spinner-border spinner-border-sm me-2"></span>Revoking...');

        fetch('{{ route("users.revoke-student-password") }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify({ student_ids: targets.map(s => s.id) }),
        })
        .then(r => r.json())
        .then(data => {
            setBtn(btn, false, '<i class="bi bi-key me-2"></i>Revoke Passwords');
            showResultsStep(data, true);
        })
        .catch(err => {
            setBtn(btn, false, '<i class="bi bi-key me-2"></i>Revoke Passwords');
            console.error(err);
            alert('Network error. Please try again.');
        });
    }

    /* ── SHOW RESULTS STEP ── */
    function showResultsStep(data, isRevoke) {
        document.getElementById('step4Title').textContent = isRevoke ? 'Revoked Passwords' : 'Created Accounts';

        // Hide print button for revoke (no new credentials to print)
        document.getElementById('printCredentialsBtn').classList.toggle('d-none', isRevoke);

        document.getElementById('massResultAlert').innerHTML =
            `<i class="bi bi-check-circle me-2"></i>${escHtml(data.message || 'Done.')}`;

        if (isRevoke) {
            document.getElementById('step4TableHead').innerHTML = `
                <tr><th>#</th><th>Result</th></tr>`;
            document.getElementById('createdResultsBody').innerHTML =
                `<tr><td>—</td><td>${data.count || 0} password(s) revoked. New password: <code>ChangeMe@123</code></td></tr>`;
        } else {
            document.getElementById('step4TableHead').innerHTML = `
                <tr>
                    <th>#</th><th>Name</th><th>Admission No</th>
                    <th>Email / Username</th><th>Password</th>
                </tr>`;
            document.getElementById('createdResultsBody').innerHTML =
                (data.created || []).map((u, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td><strong>${escHtml(u.name)}</strong></td>
                        <td><code>${escHtml(u.admissionNo)}</code></td>
                        <td>
                            ${escHtml(u.email)}<br>
                            <small class="text-muted">@${escHtml(u.username)}</small>
                        </td>
                        <td><code class="text-success">${escHtml(u.password)}</code></td>
                    </tr>
                `).join('') || '<tr><td colspan="5" class="text-center text-muted">No accounts created.</td></tr>';

            createdAccounts = data.created || [];
        }

        if (data.skipped?.length) {
            document.getElementById('massSkippedInfo').classList.remove('d-none');
            document.getElementById('massSkippedNames').textContent = data.skipped.join(', ');
        }

        goStep(4);
    }

    /* ═══════════════════════════════════════════════════════════
       PRINT CREDENTIAL SLIPS
    ═══════════════════════════════════════════════════════════ */
    document.getElementById('printCredentialsBtn')?.addEventListener('click', () => {
        if (!createdAccounts.length) { alert('No accounts to print.'); return; }
        printSlips(createdAccounts);
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
                    <tr><td class="label">Login Email</td><td class="value">${escHtml(u.email)}</td></tr>
                    <tr><td class="label">Username</td><td class="value">${escHtml(u.username)}</td></tr>
                    <tr><td class="label">Password</td><td class="value password-cell">${escHtml(u.password)}</td></tr>
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
       SINGLE-USER REVOKE (triggered from user list table row)
       Add this button to each student row in users/index.blade.php:
         <button data-revoke-user-id="{{ $user->id }}"
                 data-revoke-user-name="{{ $user->name }}"
                 class="btn btn-subtle-warning btn-icon btn-sm"
                 title="Revoke Password">
             <i class="bi bi-key"></i>
         </button>
    ═══════════════════════════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-revoke-user-id]');
        if (!btn) return;

        document.getElementById('revokeTargetUserId').value       = btn.dataset.revokeUserId;
        document.getElementById('revokeTargetName').textContent   = btn.dataset.revokeUserName || '—';

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('revokePasswordModal'));
        modal.show();
    });

    document.getElementById('confirmRevokeBtn')?.addEventListener('click', function () {
        const userId = document.getElementById('revokeTargetUserId').value;
        if (!userId) return;

        setBtn(this, true, '<span class="spinner-border spinner-border-sm me-2"></span>Revoking...');

        fetch('{{ route("users.revoke-student-password") }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify({ user_ids: [userId] }),
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
        createdAccounts  = [];
        allStudents      = [];
        filteredStudents = [];
        currentMode      = 'create';

        if (searchInput) searchInput.value = '';
        if (classFilter) classFilter.value = '';
        if (armFilter)   armFilter.value   = '';

        document.getElementById('massSharedPassword').value = '';
        document.getElementById('massSkippedInfo')?.classList.add('d-none');
        document.getElementById('massResultAlert').innerHTML = '';
        document.getElementById('massSelectedCount').textContent = '0';
        document.getElementById('massAlreadyCount').textContent  = '0';

        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading students...
        </td></tr>`;

        // Reset step indicators
        document.querySelectorAll('.mass-step').forEach((s, i) => {
            s.classList.toggle('active', i === 0);
            s.classList.remove('done');
        });

        // Reset password radio to "same" and trigger change to restore UI
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

    function escAttr(str) {
        return escHtml(str).replace(/'/g, '&#39;');
    }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function setBtn(btn, disabled, html) {
        btn.disabled   = disabled;
        btn.innerHTML  = html;
    }
});
</script>
