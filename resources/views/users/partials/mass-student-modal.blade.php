{{-- ============================================================
     resources/views/users/partials/mass-student-modal.blade.php
     Include this in your users/index.blade.php with:
       @include('users.partials.mass-student-modal')
     and add a trigger button:
       <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#massStudentModal">
           <i class="bi bi-people-fill me-1"></i> Mass Create Students
       </button>
     ============================================================ --}}

<!-- ── Step 1: Select Students ─────────────────────────────── -->
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

                <!-- Progress bar -->
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

                <!-- ── STEP 1 ── -->
                <div id="massStep1" class="mass-step-panel px-4 pb-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="massStudentSearch" class="form-control"
                                       placeholder="Search by name or admission no...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select id="massClassFilter" class="form-select">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="massSelectAll">
                                <i class="bi bi-check-all me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="massClearAll">
                                <i class="bi bi-x-lg me-1"></i>Clear
                            </button>
                        </div>
                    </div>

                    <!-- Students table -->
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" class="form-check-input" id="massCheckAll">
                                    </th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="massStudentTableBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2"></div>
                                        Loading students...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <small class="text-muted">
                            <span id="massSelectedCount">0</span> student(s) selected •
                            <span id="massAlreadyCount" class="text-warning">0</span> already have accounts (will be skipped)
                        </small>
                        <button type="button" class="btn btn-primary" id="massStep1Next" disabled>
                            Next: Configure <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- ── STEP 2 ── -->
                <div id="massStep2" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign Role(s) <span class="text-danger">*</span></label>
                            <select id="massRoleSelect" name="roles[]" class="form-select" multiple required>
                                @foreach (\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                                    <option value="{{ $role->name }}"
                                        {{ $role->name === 'student' ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password Strategy <span class="text-danger">*</span></label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="massPasswordType"
                                       id="pwdTypeSame" value="same" checked>
                                <label class="form-check-label" for="pwdTypeSame">
                                    Same password for all
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="massPasswordType"
                                       id="pwdTypeIndividual" value="individual">
                                <label class="form-check-label" for="pwdTypeIndividual">
                                    Auto-generate unique password per student
                                </label>
                            </div>

                            <div id="sharedPasswordGroup">
                                <div class="input-group">
                                    <input type="text" id="massSharedPassword" class="form-control"
                                           placeholder="Enter shared password (min 6 chars)" minlength="6">
                                    <button type="button" class="btn btn-outline-secondary" id="generateSharedPwd">
                                        <i class="bi bi-shuffle"></i>
                                    </button>
                                </div>
                                <small class="text-muted">All students will receive this password.</small>
                            </div>

                            <div id="individualPasswordGroup" class="d-none">
                                <div class="alert alert-info py-2 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    A unique password will be auto-generated for each student. You'll see all passwords in the printout.
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

                <!-- ── STEP 3 ── -->
                <div id="massStep3" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Review the details below then click <strong>Create Accounts</strong>. This cannot be undone.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-auto">
                            <span class="badge bg-primary fs-6" id="reviewStudentCount">0 students</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-secondary fs-6" id="reviewRoles">—</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-info text-dark fs-6" id="reviewPwdType">—</span>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Email (will be used)</th>
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
                            <i class="bi bi-person-check me-2"></i>Create Accounts
                        </button>
                    </div>
                </div>

                <!-- ── STEP 4: Results + Print ── -->
                <div id="massStep4" class="mass-step-panel px-4 pb-4 d-none">
                    <div class="alert alert-success mb-3" id="massResultAlert"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">Created Accounts</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="printCredentialsBtn">
                                <i class="bi bi-printer me-1"></i>Print Credential Slips
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" onclick="location.reload()">
                                <i class="bi bi-x me-1"></i>Close & Refresh
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
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
                        <small class="text-warning fw-semibold">Skipped (already had accounts):</small>
                        <span id="massSkippedNames" class="text-muted small"></span>
                    </div>
                </div>

            </div><!-- /.modal-body -->
        </div>
    </div>
</div>

<!-- Hidden print area — rendered here, printed via JS -->
<div id="credentialPrintArea" style="display:none;"></div>


<style>
/* Step indicator */
.mass-step { display:flex; flex-direction:column; align-items:center; gap:4px; }
.step-circle {
    width:32px; height:32px; border-radius:50%;
    background:#dee2e6; color:#6c757d;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.85rem;
    transition: background .3s, color .3s;
}
.step-label { font-size:.72rem; color:#6c757d; white-space:nowrap; }
.mass-step.active .step-circle  { background:#0d6efd; color:#fff; }
.mass-step.done   .step-circle  { background:#198754; color:#fff; }
.mass-step.active .step-label   { color:#0d6efd; font-weight:600; }
.step-line { height:2px; background:#dee2e6; min-width:20px; }

/* Print styles */
@media print {
    body * { visibility: hidden !important; }
    #credentialPrintArea,
    #credentialPrintArea * { visibility: visible !important; }
    #credentialPrintArea { position:fixed; top:0; left:0; width:100%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── State ─────────────────────────────────────────────── */
    let allStudents     = [];   // full list from server
    let filteredStudents = [];  // after search/filter
    let selectedIds     = new Set();
    let createdAccounts = [];   // returned after creation

    /* ── DOM refs ───────────────────────────────────────────── */
    const modalEl       = document.getElementById('massStudentModal');
    const tbody         = document.getElementById('massStudentTableBody');
    const searchInput   = document.getElementById('massStudentSearch');
    const classFilter   = document.getElementById('massClassFilter');
    const checkAll      = document.getElementById('massCheckAll');
    const selectedCount = document.getElementById('massSelectedCount');
    const alreadyCount  = document.getElementById('massAlreadyCount');
    const step1Next     = document.getElementById('massStep1Next');

    /* ── Load students on modal open ───────────────────────── */
    modalEl?.addEventListener('show.bs.modal', () => {
        resetMassModal();
        loadAllStudents();
    });

    function loadAllStudents() {
        fetch('{{ route("get.students") }}?limit=2000')
            .then(r => r.json())
            .then(data => {
                if (!data.success) { tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load students</td></tr>`; return; }
                allStudents = data.students;

                // Populate class filter
                const classes = [...new Set(allStudents.map(s => s.class_name).filter(Boolean))].sort();
                classFilter.innerHTML = '<option value="">All Classes</option>' +
                    classes.map(c => `<option value="${c}">${c}</option>`).join('');

                filteredStudents = [...allStudents];
                renderStudentTable();
            })
            .catch(() => {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Network error</td></tr>`;
            });
    }

    function renderStudentTable() {
        if (!filteredStudents.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No students found</td></tr>`;
            return;
        }

        let alreadyHave = 0;
        tbody.innerHTML = filteredStudents.map(s => {
            const has = s.has_account;
            if (has) alreadyHave++;
            const checked = selectedIds.has(String(s.id)) ? 'checked' : '';
            const disabled = has ? 'disabled' : '';
            const rowClass = has ? 'table-secondary text-muted' : '';
            return `<tr class="${rowClass}">
                <td>
                    <input type="checkbox" class="form-check-input mass-row-check"
                           value="${s.id}" ${checked} ${disabled}
                           data-name="${s.name}" data-admission="${s.admissionNo}"
                           data-email="${s.email || ''}" data-has-account="${has ? '1' : '0'}">
                </td>
                <td>${s.name}</td>
                <td><code>${s.admissionNo || '—'}</code></td>
                <td>${s.class_name || '—'}</td>
                <td>${has
                    ? '<span class="badge bg-secondary">Has Account</span>'
                    : '<span class="badge bg-success">No Account</span>'}</td>
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
        checkAll.checked   = selectedIds.size > 0 && selectedIds.size === filteredStudents.filter(s => !s.has_account).length;
        checkAll.indeterminate = selectedIds.size > 0 && !checkAll.checked;
    }

    /* ── Search & filter ─────────────────────────────────── */
    function applyFilter() {
        const q   = (searchInput.value || '').toLowerCase();
        const cls = classFilter.value;
        filteredStudents = allStudents.filter(s => {
            const matchQ   = !q || s.name.toLowerCase().includes(q) || (s.admissionNo || '').toLowerCase().includes(q);
            const matchCls = !cls || s.class_name === cls;
            return matchQ && matchCls;
        });
        renderStudentTable();
    }

    searchInput?.addEventListener('input',  debounce(applyFilter, 250));
    classFilter?.addEventListener('change', applyFilter);

    /* ── Select all / clear ──────────────────────────────── */
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

    /* ── Step navigation ─────────────────────────────────── */
    function goStep(n) {
        [1,2,3,4].forEach(i => {
            const panel = document.getElementById(`massStep${i}`);
            if (panel) panel.classList.toggle('d-none', i !== n);

            const indicator = document.querySelector(`.mass-step[data-step="${i}"]`);
            if (indicator) {
                indicator.classList.toggle('active', i === n);
                indicator.classList.toggle('done',   i < n);
            }
        });
    }

    document.getElementById('massStep1Next')?.addEventListener('click', () => {
        if (selectedIds.size === 0) return;
        goStep(2);
    });

    document.getElementById('massStep2Back')?.addEventListener('click', () => goStep(1));

    document.getElementById('massStep2Next')?.addEventListener('click', () => {
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        if (pwdType === 'same' && (document.getElementById('massSharedPassword').value || '').length < 6) {
            alert('Please enter a shared password of at least 6 characters.');
            return;
        }
        buildReviewStep();
        goStep(3);
    });

    document.getElementById('massStep3Back')?.addEventListener('click', () => goStep(2));

    /* ── Password type toggle ─────────────────────────────── */
    document.querySelectorAll('input[name="massPasswordType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('sharedPasswordGroup').classList.toggle('d-none', this.value !== 'same');
            document.getElementById('individualPasswordGroup').classList.toggle('d-none', this.value !== 'individual');
        });
    });

    document.getElementById('generateSharedPwd')?.addEventListener('click', () => {
        const pwd = Math.random().toString(36).slice(-6).toUpperCase() + Math.floor(1000 + Math.random() * 9000);
        document.getElementById('massSharedPassword').value = pwd;
    });

    /* ── Build review step ───────────────────────────────── */
    function buildReviewStep() {
        const selected = allStudents.filter(s => selectedIds.has(String(s.id)));
        const roles    = Array.from(document.getElementById('massRoleSelect').selectedOptions).map(o => o.value);
        const pwdType  = document.querySelector('input[name="massPasswordType"]:checked')?.value;

        document.getElementById('reviewStudentCount').textContent = `${selected.length} student(s)`;
        document.getElementById('reviewRoles').textContent        = `Roles: ${roles.join(', ')}`;
        document.getElementById('reviewPwdType').textContent      = pwdType === 'same' ? 'Shared password' : 'Individual passwords';

        document.getElementById('reviewTableBody').innerHTML = selected
            .sort((a,b) => a.name.localeCompare(b.name))
            .map((s, i) => `<tr>
                <td>${i+1}</td>
                <td>${s.name}</td>
                <td><code>${s.admissionNo || '—'}</code></td>
                <td>${s.email || '<em class="text-muted">will be auto-generated</em>'}</td>
            </tr>`).join('');
    }

    /* ── Create accounts ─────────────────────────────────── */
    document.getElementById('massCreateBtn')?.addEventListener('click', function () {
        const btn     = this;
        const roles   = Array.from(document.getElementById('massRoleSelect').selectedOptions).map(o => o.value);
        const pwdType = document.querySelector('input[name="massPasswordType"]:checked')?.value;
        const sharedPwd = document.getElementById('massSharedPassword').value;

        const payload = {
            students: allStudents
                .filter(s => selectedIds.has(String(s.id)))
                .map(s => ({ student_id: s.id })),
            roles,
            password_type:    pwdType,
            shared_password:  pwdType === 'same' ? sharedPwd : null,
        };

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check me-2"></i>Create Accounts';

            if (!data.success && !data.created_count) {
                alert('Error: ' + (data.message || 'Unknown error'));
                return;
            }

            createdAccounts = data.created || [];
            showResultsStep(data);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check me-2"></i>Create Accounts';
            alert('Network error. Please try again.');
            console.error(err);
        });
    });

    function showResultsStep(data) {
        document.getElementById('massResultAlert').innerHTML =
            `<i class="bi bi-check-circle me-2"></i>
             <strong>${data.created_count}</strong> account(s) created successfully.
             ${data.skipped_count ? `<span class="ms-2 text-warning">${data.skipped_count} skipped (already had accounts).</span>` : ''}`;

        document.getElementById('createdResultsBody').innerHTML = data.created
            .map((u, i) => `<tr>
                <td>${i+1}</td>
                <td><strong>${u.name}</strong></td>
                <td><code>${u.admissionNo}</code></td>
                <td>${u.email}<br><small class="text-muted">@${u.username}</small></td>
                <td><code class="text-success">${u.password}</code></td>
            </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted">No accounts created.</td></tr>';

        if (data.skipped?.length) {
            document.getElementById('massSkippedInfo').classList.remove('d-none');
            document.getElementById('massSkippedNames').textContent = data.skipped.join(', ');
        }

        goStep(4);
    }

    /* ── Print credential slips ──────────────────────────── */
    document.getElementById('printCredentialsBtn')?.addEventListener('click', () => {
        if (!createdAccounts.length) { alert('No accounts to print.'); return; }
        printSlips(createdAccounts);
    });

    function printSlips(accounts) {
        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'School Portal';
        const today      = new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'long', year:'numeric' });

        const slipsHtml = accounts.map(u => `
            <div class="slip">
                <div class="slip-header">
                    <span class="school-name">${schoolName}</span>
                    <span class="slip-title">Student Portal Access</span>
                    <span class="slip-date">${today}</span>
                </div>
                <table class="slip-table">
                    <tr><td class="label">Student Name</td><td class="value"><strong>${u.name}</strong></td></tr>
                    <tr><td class="label">Admission No</td><td class="value">${u.admissionNo || '—'}</td></tr>
                    <tr><td class="label">Login Email</td><td class="value">${u.email}</td></tr>
                    <tr><td class="label">Username</td><td class="value">${u.username}</td></tr>
                    <tr><td class="label">Password</td><td class="value password-cell">${u.password}</td></tr>
                </table>
                <p class="slip-note">⚠ Change your password after first login. Keep this slip safe.</p>
            </div>
        `).join('');

        const printHtml = `<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Credential Slips</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 12pt;
    background: #fff;
    color: #111;
  }
  .page-title {
    text-align: center;
    font-size: 14pt;
    font-weight: bold;
    padding: 10mm 0 4mm;
    border-bottom: 2px solid #000;
    margin-bottom: 6mm;
  }
  .slip {
    border: 1.5px solid #333;
    border-radius: 4px;
    padding: 5mm 7mm;
    margin: 0 0 0 0;
    page-break-inside: avoid;
    background: #fff;
  }
  /* Dashed cut line between slips */
  .slip + .slip {
    border-top: 2px dashed #888;
    margin-top: 0;
  }
  .cut-line {
    text-align: center;
    font-size: 8pt;
    color: #999;
    letter-spacing: 2px;
    padding: 1mm 0;
    border-top: 1px dashed #bbb;
    border-bottom: 1px dashed #bbb;
    margin: 0;
  }
  .slip-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    border-bottom: 1px solid #ccc;
    padding-bottom: 3mm;
    margin-bottom: 3mm;
  }
  .school-name { font-weight: 800; font-size: 11pt; }
  .slip-title  { font-size: 9pt; color: #555; font-style: italic; }
  .slip-date   { font-size: 8pt; color: #888; }
  .slip-table  { width: 100%; border-collapse: collapse; }
  .slip-table td { padding: 1.5mm 2mm; vertical-align: top; }
  .slip-table .label {
    width: 38%;
    font-size: 9pt;
    color: #555;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
  }
  .slip-table .value { font-size: 11pt; }
  .password-cell {
    font-family: 'Courier New', monospace;
    font-size: 13pt;
    font-weight: bold;
    letter-spacing: 2px;
    color: #0a3;
  }
  .slip-note {
    font-size: 7.5pt;
    color: #888;
    margin-top: 3mm;
    font-style: italic;
  }
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
</html>`;

        const printWin = window.open('', '_blank', 'width=800,height=900');
        printWin.document.write(printHtml);
        printWin.document.close();
        printWin.focus();
        printWin.onload = () => { printWin.print(); };
    }

    /* ── Reset modal ────────────────────────────────────────── */
    function resetMassModal() {
        selectedIds.clear();
        createdAccounts = [];
        allStudents = [];
        filteredStudents = [];
        goStep(1);
        if (searchInput) searchInput.value = '';
        if (classFilter) classFilter.value = '';
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading...</td></tr>`;
        document.getElementById('massSharedPassword').value = '';
        document.getElementById('massSkippedInfo')?.classList.add('d-none');
        // Reset step indicators
        document.querySelectorAll('.mass-step').forEach((s,i) => {
            s.classList.toggle('active', i === 0);
            s.classList.remove('done');
        });
    }

    /* ── Utility ─────────────────────────────────────────────── */
    function debounce(fn, ms) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }
});
</script>
