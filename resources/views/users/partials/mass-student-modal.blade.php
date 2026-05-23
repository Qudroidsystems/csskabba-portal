{{--
    Mass Student Account Management Modal
    – Dense, paper-optimised credential slips (4 per page landscape / 3 per page portrait)
    – Improved UI matching schoolpayment blade aesthetic
--}}

<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content msm-modal-content">

            {{-- HEADER --}}
            <div class="modal-header msm-header">
                <div class="msm-header-inner">
                    <div class="msm-header-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <h5 class="modal-title msm-title">Mass Student Account Management</h5>
                        <p class="msm-subtitle">Create, reset, revoke or reprint student credentials in bulk</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- STEP BAR --}}
            <div class="msm-steps-bar">
                <div class="msm-step active" id="stepBar1">
                    <div class="msm-step-circle">1</div><span>Select Students</span>
                </div>
                <div class="msm-step-line"></div>
                <div class="msm-step" id="stepBar2">
                    <div class="msm-step-circle">2</div><span>Choose Action</span>
                </div>
                <div class="msm-step-line"></div>
                <div class="msm-step" id="stepBar3">
                    <div class="msm-step-circle">3</div><span>Results</span>
                </div>
            </div>

            <div class="modal-body msm-body">

                {{-- ═══════════════ STEP 1 ═══════════════ --}}
                <div id="massStep1">
                    <div class="msm-card msm-filter-card mb-3">
                        <div class="msm-card-header"><i class="bi bi-funnel-fill me-2"></i>Filter Students</div>
                        <div class="msm-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="msm-label">Search</label>
                                    <div class="position-relative">
                                        <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none;"></i>
                                        <input type="text" id="massStudentSearch" class="form-control msm-input ps-4" placeholder="Name or Admission No…">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="msm-label">Class</label>
                                    <select id="massClassFilter" class="form-select msm-input">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="msm-label">Account Status</label>
                                    <select id="massAccountStatus" class="form-select msm-input">
                                        <option value="all">All Students</option>
                                        <option value="no">No Account Only</option>
                                        <option value="yes">Has Account Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="msm-info-banner mb-3">
                        <i class="bi bi-envelope-at-fill me-2"></i>
                        Emails are auto-generated as <code>firstname.lastname@csskabba.ng</code> — special characters removed.
                    </div>

                    <div class="msm-card mb-3">
                        <div class="msm-card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-lines-fill me-2"></i>Select Students</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="msm-count-badge" id="massSelectedCount">0 selected</span>
                                <button type="button" class="msm-btn-sm" id="selectAllStudents">
                                    <i class="bi bi-check-all me-1"></i>Select All
                                </button>
                                <button type="button" class="msm-btn-sm" id="deselectAll" style="border-color:#94a3b8;color:#94a3b8;">
                                    <i class="bi bi-x me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                        <div class="msm-card-body p-0">
                            <div class="table-responsive msm-table-wrap">
                                <table class="table msm-table mb-0">
                                    <thead>
                                        <tr>
                                            <th width="40"><input type="checkbox" id="selectAllCheckbox" class="msm-checkbox"></th>
                                            <th>Admission No</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="massStudentList">
                                        <tr><td colspan="6" class="msm-loading-cell">
                                            <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading students…
                                        </td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="msm-legend mb-3">
                        <div class="msm-legend-title"><i class="bi bi-lightbulb-fill me-1"></i>Available Actions</div>
                        <div class="msm-legend-grid">
                            <div class="msm-legend-item create"><i class="bi bi-person-plus-fill"></i><div><strong>Create</strong> — new accounts for students without one</div></div>
                            <div class="msm-legend-item reset"><i class="bi bi-key-fill"></i><div><strong>Reset</strong> — new passwords for existing accounts</div></div>
                            <div class="msm-legend-item revoke"><i class="bi bi-person-x-fill"></i><div><strong>Revoke</strong> — remove user access (student record kept)</div></div>
                            <div class="msm-legend-item reprint"><i class="bi bi-printer-fill"></i><div><strong>Reprint</strong> — show existing credentials (password hidden)</div></div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="msm-btn-primary" id="proceedToAction">
                            Continue to Action <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ═══════════════ STEP 2 ═══════════════ --}}
                <div id="massStep2" style="display:none;">
                    <div class="msm-card mb-3">
                        <div class="msm-card-header">
                            <i class="bi bi-check-square-fill me-2"></i>
                            Selected Students — <span id="step2SelectedCount" class="fw-bold">0</span>
                        </div>
                        <div class="msm-card-body p-0">
                            <div class="table-responsive msm-summary-wrap">
                                <table class="table msm-table msm-table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Admission No</th>
                                            <th>Class</th>
                                            <th>Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedStudentsList"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="msm-card mb-3">
                        <div class="msm-card-header"><i class="bi bi-lightning-charge-fill me-2"></i>Choose Action</div>
                        <div class="msm-card-body">
                            <div class="msm-action-grid">
                                <div class="msm-action-card" data-action="create">
                                    <div class="msm-action-icon create"><i class="bi bi-person-plus-fill"></i></div>
                                    <div class="msm-action-label">Create Accounts</div>
                                    <div class="msm-action-desc">For students without accounts</div>
                                </div>
                                <div class="msm-action-card" data-action="reset">
                                    <div class="msm-action-icon reset"><i class="bi bi-key-fill"></i></div>
                                    <div class="msm-action-label">Reset Passwords</div>
                                    <div class="msm-action-desc">New password for existing accounts</div>
                                </div>
                                <div class="msm-action-card" data-action="revoke">
                                    <div class="msm-action-icon revoke"><i class="bi bi-person-x-fill"></i></div>
                                    <div class="msm-action-label">Revoke Accounts</div>
                                    <div class="msm-action-desc">Remove user access</div>
                                </div>
                                <div class="msm-action-card" data-action="reprint">
                                    <div class="msm-action-icon reprint"><i class="bi bi-printer-fill"></i></div>
                                    <div class="msm-action-label">Reprint Credentials</div>
                                    <div class="msm-action-desc">Print without password shown</div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedAction" value="">
                        </div>
                    </div>

                    <div id="passwordSettings" style="display:none;">
                        <div class="msm-card mb-3">
                            <div class="msm-card-header"><i class="bi bi-lock-fill me-2"></i>Password Settings</div>
                            <div class="msm-card-body">
                                <div class="msm-radio-group">
                                    <label class="msm-radio-card">
                                        <input type="radio" name="passwordTypeRadio" value="individual" checked>
                                        <div class="msm-radio-inner">
                                            <i class="bi bi-shuffle"></i>
                                            <div><strong>Individual Random Passwords</strong><p>Each student gets a unique random password</p></div>
                                        </div>
                                    </label>
                                    <label class="msm-radio-card">
                                        <input type="radio" name="passwordTypeRadio" value="same">
                                        <div class="msm-radio-inner">
                                            <i class="bi bi-people-fill"></i>
                                            <div><strong>Same Password for All</strong><p>All selected students share one password</p></div>
                                        </div>
                                    </label>
                                </div>
                                <div id="sharedPasswordContainer" style="display:none;" class="mt-3">
                                    <label class="msm-label">Shared Password</label>
                                    <input type="text" id="sharedPassword" class="form-control msm-input" placeholder="Minimum 6 characters">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="roleSettings" style="display:none;">
                        <div class="msm-card mb-3">
                            <div class="msm-card-header"><i class="bi bi-tags-fill me-2"></i>Role Assignment</div>
                            <div class="msm-card-body">
                                <div class="msm-role-notice">
                                    <i class="bi bi-shield-check-fill me-2"></i>
                                    Student accounts are automatically assigned the <strong>Student</strong> role.
                                </div>
                                @php
                                    $allRoles   = Spatie\Permission\Models\Role::all();
                                    $studentRole = $allRoles->where('name','Student')->first();
                                    $otherRoles  = $allRoles->where('name','!=','Student');
                                @endphp
                                @if($studentRole)
                                <div class="msm-role-assigned">
                                    <i class="bi bi-person-badge-fill me-2 text-success"></i>
                                    <strong class="text-success">{{ $studentRole->name }}</strong>
                                    <span class="msm-badge-default ms-2">Auto-assigned</span>
                                </div>
                                @endif
                                @if($otherRoles->count() > 0)
                                <div class="mt-3">
                                    <p class="text-muted small mb-2"><i class="bi bi-lock-fill me-1"></i>These roles cannot be assigned to student accounts:</p>
                                    <div class="msm-disabled-roles">
                                        @foreach($otherRoles as $role)
                                        <span class="msm-disabled-role"><i class="bi bi-lock-fill me-1"></i>{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="actionWarning" class="msm-warning-banner mb-3" style="display:none;"></div>

                    <div class="d-flex justify-content-between mt-1">
                        <button type="button" class="msm-btn-secondary" id="backToStep1">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </button>
                        <button type="button" class="msm-btn-primary" id="executeAction">
                            <i class="bi bi-check-circle me-1"></i>Execute Action
                        </button>
                    </div>
                </div>

                {{-- ═══════════════ STEP 3 ═══════════════ --}}
                <div id="massStep3" style="display:none;">
                    <div id="resultsContainer"></div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="msm-btn-secondary" id="newAction">
                            <i class="bi bi-plus-circle me-1"></i>New Action
                        </button>
                        <button type="button" class="msm-btn-primary" id="printResults">
                            <i class="bi bi-printer me-1"></i>Print Credentials
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════ --}}
<style>
:root {
    --msm-primary: #1e3a5f;
    --msm-accent:  #2563eb;
    --msm-indigo:  #4f46e5;
    --msm-success: #16a34a;
    --msm-warning: #d97706;
    --msm-danger:  #dc2626;
    --msm-info:    #0ea5e9;
    --msm-border:  #e2e8f0;
    --msm-radius:  12px;
}
.msm-modal-content { border:none; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.msm-header { background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 55%,#4f46e5 100%); padding:20px 24px; border:none; }
.msm-header-inner { display:flex; align-items:center; gap:14px; }
.msm-header-icon { width:44px; height:44px; background:rgba(255,255,255,.15); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; }
.msm-title    { color:#fff; font-size:17px; font-weight:700; margin:0; }
.msm-subtitle { color:rgba(255,255,255,.75); font-size:12px; margin:2px 0 0; }
.msm-steps-bar { display:flex; align-items:center; justify-content:center; padding:14px 24px; background:#f1f5f9; border-bottom:1px solid var(--msm-border); }
.msm-step { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#94a3b8; }
.msm-step.active { color:var(--msm-accent); }
.msm-step.done   { color:var(--msm-success); }
.msm-step-circle { width:28px; height:28px; border-radius:50%; background:#e2e8f0; color:#94a3b8; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; transition:all .3s; }
.msm-step.active .msm-step-circle { background:var(--msm-accent); color:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.2); }
.msm-step.done   .msm-step-circle { background:var(--msm-success); color:#fff; }
.msm-step-line { flex:1; height:2px; background:#e2e8f0; margin:0 12px; max-width:80px; }
.msm-body { padding:20px 24px; background:#f8fafc; max-height:76vh; overflow-y:auto; }
.msm-card { background:#fff; border:1px solid var(--msm-border); border-radius:var(--msm-radius); overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.msm-filter-card { border-top:3px solid var(--msm-accent); }
.msm-card-header { padding:12px 18px; background:#f8fafc; border-bottom:1px solid var(--msm-border); font-size:13px; font-weight:600; color:#1e293b; }
.msm-card-body { padding:16px 18px; }
.msm-label { font-size:11.5px; font-weight:700; color:#64748b; margin-bottom:5px; display:block; text-transform:uppercase; letter-spacing:.4px; }
.msm-input { border:1.5px solid var(--msm-border); border-radius:8px; font-size:13px; transition:border-color .2s, box-shadow .2s; }
.msm-input:focus { border-color:var(--msm-accent); outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.msm-info-banner    { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; font-size:12.5px; color:#1e40af; }
.msm-warning-banner { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; font-size:12.5px; color:#92400e; }
.msm-count-badge { background:var(--msm-accent); color:#fff; border-radius:20px; padding:3px 12px; font-size:12px; font-weight:600; }
.msm-btn-sm { background:#fff; border:1.5px solid var(--msm-accent); color:var(--msm-accent); border-radius:7px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; }
.msm-btn-sm:hover { background:var(--msm-accent); color:#fff; }
.msm-btn-primary { background:linear-gradient(135deg,var(--msm-accent),var(--msm-indigo)); color:#fff; border:none; border-radius:9px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; box-shadow:0 3px 12px rgba(37,99,235,.3); transition:transform .15s, box-shadow .15s; }
.msm-btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(37,99,235,.35); }
.msm-btn-secondary { background:#fff; color:#1e293b; border:1.5px solid var(--msm-border); border-radius:9px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; transition:background .15s; }
.msm-btn-secondary:hover { background:#f1f5f9; }
.msm-table-wrap   { max-height:360px; overflow-y:auto; }
.msm-summary-wrap { max-height:200px; overflow-y:auto; }
.msm-table { font-size:12.5px; }
.msm-table thead th { background:#1e3a5f; color:#fff; border-bottom:none; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:10px 12px; position:sticky; top:0; z-index:5; }
.msm-table tbody tr:hover { background:#f0f9ff; }
.msm-table tbody td { padding:9px 12px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.msm-table-sm tbody td { padding:6px 12px; }
.msm-checkbox, .student-checkbox { accent-color:var(--msm-accent); width:15px; height:15px; cursor:pointer; }
.msm-loading-cell { text-align:center; padding:32px; color:#64748b; font-size:13px; }
.msm-badge-has  { background:#dcfce7; color:#166534; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:600; }
.msm-badge-none { background:#f1f5f9; color:#475569; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:600; }
.msm-legend { background:#fff; border:1px solid var(--msm-border); border-radius:var(--msm-radius); padding:14px 16px; }
.msm-legend-title { font-size:11.5px; font-weight:700; color:#64748b; margin-bottom:10px; text-transform:uppercase; letter-spacing:.5px; }
.msm-legend-grid  { display:grid; grid-template-columns:repeat(2,1fr); gap:8px; }
.msm-legend-item  { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; font-size:12px; border:1px solid transparent; }
.msm-legend-item i { font-size:16px; }
.msm-legend-item.create  { background:#f0fdf4; border-color:#bbf7d0; } .msm-legend-item.create  i { color:var(--msm-success); }
.msm-legend-item.reset   { background:#fffbeb; border-color:#fde68a; } .msm-legend-item.reset   i { color:var(--msm-warning); }
.msm-legend-item.revoke  { background:#fef2f2; border-color:#fecaca; } .msm-legend-item.revoke  i { color:var(--msm-danger); }
.msm-legend-item.reprint { background:#f0f9ff; border-color:#bae6fd; } .msm-legend-item.reprint i { color:var(--msm-info); }
.msm-action-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
.msm-action-card { border:2px solid var(--msm-border); border-radius:var(--msm-radius); padding:18px 12px; text-align:center; cursor:pointer; background:#fff; transition:all .2s; }
.msm-action-card:hover  { transform:translateY(-3px); box-shadow:0 6px 20px rgba(0,0,0,.08); }
.msm-action-card.selected { border-color:var(--msm-accent); background:#eff6ff; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.msm-action-icon { font-size:28px; margin-bottom:8px; }
.msm-action-icon.create  { color:var(--msm-success); }
.msm-action-icon.reset   { color:var(--msm-warning); }
.msm-action-icon.revoke  { color:var(--msm-danger); }
.msm-action-icon.reprint { color:var(--msm-info); }
.msm-action-label { font-size:13px; font-weight:700; color:#1e293b; }
.msm-action-desc  { font-size:11px; color:#64748b; margin-top:3px; }
.msm-radio-group { display:flex; gap:12px; flex-wrap:wrap; }
.msm-radio-card  { flex:1; min-width:200px; cursor:pointer; }
.msm-radio-card input { display:none; }
.msm-radio-inner { display:flex; align-items:center; gap:12px; border:2px solid var(--msm-border); border-radius:10px; padding:14px; background:#fff; transition:all .2s; }
.msm-radio-inner i { font-size:22px; color:#64748b; }
.msm-radio-inner p { font-size:11px; color:#64748b; margin:2px 0 0; }
.msm-radio-card input:checked ~ .msm-radio-inner { border-color:var(--msm-accent); background:#eff6ff; }
.msm-radio-card input:checked ~ .msm-radio-inner i { color:var(--msm-accent); }
.msm-role-notice   { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; font-size:12.5px; color:#166534; margin-bottom:12px; }
.msm-role-assigned { display:flex; align-items:center; background:#f8fafc; border:1.5px solid var(--msm-border); border-radius:8px; padding:10px 14px; font-size:13px; }
.msm-badge-default { background:var(--msm-success); color:#fff; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:600; }
.msm-disabled-roles { display:flex; flex-wrap:wrap; gap:8px; }
.msm-disabled-role  { background:#f1f5f9; color:#94a3b8; border-radius:20px; padding:3px 12px; font-size:11px; border:1px solid #e2e8f0; }
@media (max-width:768px) {
    .msm-action-grid { grid-template-columns:repeat(2,1fr); }
    .msm-legend-grid { grid-template-columns:1fr; }
}
</style>

{{-- ══════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    let selectedStudents = [];
    let allStudents      = [];
    let currentResults   = null;

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }
    function classLabel(s) {
        const c = (s.class_name||'').trim();
        const a = (s.arm_name||'').trim();
        return c && a ? `${c} ${a}` : c || a || '—';
    }
    function genEmail(first, last) {
        const c = s => (s||'').toLowerCase().replace(/[^a-z0-9]/g,'') || 'user';
        return c(first) + '.' + c(last) + '@csskabba.ng';
    }
    function statusBadge(has) {
        return has
            ? '<span class="msm-badge-has"><i class="bi bi-check-circle-fill me-1"></i>Has Account</span>'
            : '<span class="msm-badge-none"><i class="bi bi-circle me-1"></i>No Account</span>';
    }
    function setStep(n) {
        [1,2,3].forEach(i => {
            const el = document.getElementById('stepBar'+i);
            const circle = el.querySelector('.msm-step-circle');
            el.classList.remove('active','done');
            if (i < n) { el.classList.add('done'); circle.innerHTML='<i class="bi bi-check-lg"></i>'; }
            else { circle.textContent = i; if (i===n) el.classList.add('active'); }
        });
    }

    // ── Load students ──────────────────────────────────────
    function loadStudents() {
        const search  = document.getElementById('massStudentSearch').value;
        const classId = document.getElementById('massClassFilter').value;
        const status  = document.getElementById('massAccountStatus').value;

        document.getElementById('massStudentList').innerHTML =
            '<tr><td colspan="6" class="msm-loading-cell"><div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading students…</td></tr>';

        let url = '{{ route("get.students") }}?limit=2000';
        if (search)           url += `&search=${encodeURIComponent(search)}`;
        if (classId)          url += `&class_id=${classId}`;
        if (status !== 'all') url += `&has_account=${status}`;

        fetch(url).then(r=>r.json()).then(data => {
            if (!data.success) {
                document.getElementById('massStudentList').innerHTML =
                    '<tr><td colspan="6" class="msm-loading-cell text-danger">Error loading students.</td></tr>';
                return;
            }
            allStudents = data.students.map(s => ({
                ...s, generatedEmail: genEmail(s.firstname, s.lastname)
            }));
            renderStudentTable(allStudents);

            if (document.getElementById('massClassFilter').options.length <= 1) {
                let html = '<option value="">All Classes</option>';
                if (data.classes?.length) {
                    data.classes.forEach(c => {
                        html += `<option value="${escHtml(String(c.id))}">${escHtml(c.name || c.class_name || '')}</option>`;
                    });
                } else {
                    const seen = new Map();
                    allStudents.forEach(s => { if (s.class_id && !seen.has(s.class_id)) seen.set(s.class_id, classLabel(s)); });
                    [...seen.entries()].sort((a,b)=>a[1].localeCompare(b[1])).forEach(([id,lbl]) => {
                        html += `<option value="${escHtml(String(id))}">${escHtml(lbl)}</option>`;
                    });
                }
                document.getElementById('massClassFilter').innerHTML = html;
            }
        }).catch(() => {
            document.getElementById('massStudentList').innerHTML =
                '<tr><td colspan="6" class="msm-loading-cell text-danger">Network error.</td></tr>';
        });
    }

    function renderStudentTable(students) {
        if (!students.length) {
            document.getElementById('massStudentList').innerHTML =
                '<tr><td colspan="6" class="msm-loading-cell">No students found.</td></tr>';
            updateSelectedCount(); return;
        }
        let html = '';
        students.forEach(s => {
            const checked = selectedStudents.some(x=>x.id===s.id) ? 'checked' : '';
            html += `<tr>
                <td><input type="checkbox" class="student-checkbox" data-id="${s.id}" ${checked}></td>
                <td><strong>${escHtml(s.admissionNo||'N/A')}</strong></td>
                <td>${escHtml(s.name)}</td>
                <td>${escHtml(classLabel(s))}</td>
                <td>${statusBadge(s.has_account)}</td>
                <td><small class="text-muted font-monospace">${escHtml(s.generatedEmail)}</small></td>
            </tr>`;
        });
        document.getElementById('massStudentList').innerHTML = html;
        updateSelectedCount();
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const id  = parseInt(this.dataset.id);
                const stu = allStudents.find(x=>x.id===id);
                if (this.checked) { if (!selectedStudents.some(x=>x.id===id)) selectedStudents.push(stu); }
                else selectedStudents = selectedStudents.filter(x=>x.id!==id);
                updateSelectedCount();
            });
        });
    }

    function updateSelectedCount() {
        document.getElementById('massSelectedCount').textContent = `${selectedStudents.length} selected`;
        document.getElementById('selectAllCheckbox').checked =
            allStudents.length > 0 && selectedStudents.length === allStudents.length;
    }

    function applyClientFilters() {
        const search = document.getElementById('massStudentSearch').value.toLowerCase();
        const status = document.getElementById('massAccountStatus').value;
        const filtered = allStudents.filter(s => {
            if (search && !s.name.toLowerCase().includes(search) && !(s.admissionNo||'').toLowerCase().includes(search)) return false;
            if (status==='yes' && !s.has_account) return false;
            if (status==='no'  &&  s.has_account) return false;
            return true;
        });
        renderStudentTable(filtered);
    }

    document.getElementById('massStudentSearch').addEventListener('input', applyClientFilters);
    document.getElementById('massAccountStatus').addEventListener('change', applyClientFilters);
    document.getElementById('massClassFilter').addEventListener('change', () => { selectedStudents=[]; loadStudents(); });

    document.getElementById('selectAllStudents').addEventListener('click', () => {
        selectedStudents = [...allStudents]; renderStudentTable(allStudents);
    });
    document.getElementById('deselectAll').addEventListener('click', () => {
        selectedStudents = []; renderStudentTable(allStudents);
    });
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        selectedStudents = this.checked ? [...allStudents] : [];
        renderStudentTable(allStudents);
    });

    // ── Step 1 → 2 ────────────────────────────────────────
    document.getElementById('proceedToAction').addEventListener('click', () => {
        if (!selectedStudents.length) {
            Swal.fire({ icon:'warning', title:'No Students Selected', text:'Select at least one student.', confirmButtonColor:'#2563eb' });
            return;
        }
        let html = '';
        selectedStudents.forEach(s => {
            html += `<tr>
                <td>${escHtml(s.name)}</td>
                <td>${escHtml(s.admissionNo||'N/A')}</td>
                <td>${escHtml(classLabel(s))}</td>
                <td>${statusBadge(s.has_account)}</td>
                <td><small class="font-monospace">${escHtml(s.generatedEmail)}</small></td>
            </tr>`;
        });
        document.getElementById('selectedStudentsList').innerHTML = html;
        document.getElementById('step2SelectedCount').textContent = selectedStudents.length;
        document.getElementById('massStep1').style.display = 'none';
        document.getElementById('massStep2').style.display = '';
        setStep(2);
    });

    document.querySelectorAll('.msm-action-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.msm-action-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const action = this.dataset.action;
            document.getElementById('selectedAction').value = action;
            const showPwd = action==='create' || action==='reset';
            document.getElementById('passwordSettings').style.display = showPwd ? '' : 'none';
            document.getElementById('roleSettings').style.display     = showPwd ? '' : 'none';

            const hasAcc = selectedStudents.filter(s=>s.has_account).length;
            const noAcc  = selectedStudents.filter(s=>!s.has_account).length;
            let warn = '';
            if (action==='create' && hasAcc) warn=`<i class="bi bi-exclamation-triangle-fill me-2"></i>${hasAcc} student(s) already have accounts and will be skipped.`;
            if (action==='reset'  && noAcc)  warn=`<i class="bi bi-exclamation-triangle-fill me-2"></i>${noAcc} student(s) have no accounts and will be skipped.`;
            if (action==='revoke' && noAcc)  warn=`<i class="bi bi-exclamation-triangle-fill me-2"></i>${noAcc} student(s) have no accounts and will be skipped.`;
            const w = document.getElementById('actionWarning');
            if (warn) { w.innerHTML=warn; w.style.display=''; } else w.style.display='none';
        });
    });

    document.querySelectorAll('input[name="passwordTypeRadio"]').forEach(r => {
        r.addEventListener('change', function() {
            document.getElementById('sharedPasswordContainer').style.display = this.value==='same' ? '' : 'none';
        });
    });

    document.getElementById('backToStep1').addEventListener('click', () => {
        document.getElementById('massStep2').style.display='none';
        document.getElementById('massStep1').style.display='';
        setStep(1);
    });

    // ── Execute ────────────────────────────────────────────
    document.getElementById('executeAction').addEventListener('click', () => {
        const actionType = document.getElementById('selectedAction').value;
        if (!actionType) {
            Swal.fire({ icon:'error', title:'No Action', text:'Choose an action first.', confirmButtonColor:'#2563eb' });
            return;
        }
        const payload = {
            _token: '{{ csrf_token() }}',
            students: selectedStudents.map(s=>({student_id:s.id})),
            action_type: actionType,
        };
        if (actionType==='create' || actionType==='reset') {
            const pwdType = document.querySelector('input[name="passwordTypeRadio"]:checked').value;
            payload.password_type = pwdType;
            if (pwdType==='same') {
                payload.shared_password = document.getElementById('sharedPassword').value;
                if (!payload.shared_password || payload.shared_password.length < 6) {
                    Swal.fire({ icon:'error', title:'Password Too Short', text:'Minimum 6 characters.', confirmButtonColor:'#2563eb' }); return;
                }
            }
            payload.roles = ['Student'];
        }
        Swal.fire({ title:'Processing…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
        fetch('{{ route("users.mass-create-students") }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body:JSON.stringify(payload)
        })
        .then(r=>r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                currentResults = data;
                displayResults(data);
                document.getElementById('massStep2').style.display='none';
                document.getElementById('massStep3').style.display='';
                setStep(3);
            } else {
                Swal.fire({ icon:'error', title:'Error', text:data.message||'Operation failed.', confirmButtonColor:'#2563eb' });
            }
        })
        .catch(()=>{ Swal.close(); Swal.fire({ icon:'error', title:'Network Error', confirmButtonColor:'#2563eb' }); });
    });

    // ── Results ────────────────────────────────────────────
    function displayResults(data) {
        let html = `<div class="alert alert-success border-0 rounded-3" style="background:#f0fdf4;border-left:4px solid #16a34a !important;">
            <h5 class="mb-1"><i class="bi bi-check-circle-fill me-2 text-success"></i>Operation Complete</h5>
            <p class="mb-0 text-muted">${escHtml(data.message)}</p>
        </div>`;
        if (data.created?.length) html += mkTable('Created Accounts',data.created,'success','person-plus-fill',
            ['Name','Username','Email','Password','Admission No','Class'],
            c=>`<tr><td>${escHtml(c.name)}</td><td><code>${escHtml(c.username)}</code></td><td><small>${escHtml(c.email)}</small></td><td><code class="text-success fw-bold">${escHtml(c.password)}</code></td><td>${escHtml(c.admissionNo||'N/A')}</td><td>${escHtml(c.class_name||'')}</td></tr>`
        );
        if (data.reset?.length) html += mkTable('Password Resets',data.reset,'warning','key-fill',
            ['Name','Username','Email','New Password','Admission No','Class'],
            r=>`<tr><td>${escHtml(r.name)}</td><td><code>${escHtml(r.username)}</code></td><td><small>${escHtml(r.email)}</small></td><td><code class="text-warning fw-bold">${escHtml(r.password)}</code></td><td>${escHtml(r.admissionNo||'N/A')}</td><td>${escHtml(r.class_name||'')}</td></tr>`
        );
        if (data.revoked?.length) {
            html+=`<div class="mt-3 p-3 border rounded-3"><strong><i class="bi bi-person-x-fill text-danger me-2"></i>Revoked (${data.revoked.length})</strong><ul class="mt-2 mb-0">`;
            data.revoked.forEach(r=>{ html+=`<li>${escHtml(r.name)} (${escHtml(r.admissionNo||'N/A')}) — account removed</li>`; });
            html+='</ul></div>';
        }
        if (data.reprinted?.length) html += mkTable('Reprinted Credentials',data.reprinted,'info','printer-fill',
            ['Name','Username','Email','Admission No','Note'],
            r=>`<tr><td>${escHtml(r.name)}</td><td><code>${escHtml(r.username)}</code></td><td><small>${escHtml(r.email)}</small></td><td>${escHtml(r.admissionNo||'N/A')}</td><td><small class="text-muted">Password hidden</small></td></tr>`
        );
        if (data.skipped?.length) {
            html+=`<div class="mt-3 p-3 border rounded-3 bg-light"><strong><i class="bi bi-skip-forward-fill text-muted me-2"></i>Skipped (${data.skipped.length})</strong><ul class="mt-2 mb-0">`;
            data.skipped.forEach(s=>{ html+=`<li class="text-muted">${escHtml(s)}</li>`; });
            html+='</ul></div>';
        }
        document.getElementById('resultsContainer').innerHTML = html;
    }

    function mkTable(title,rows,color,icon,headers,rowFn) {
        return `<div class="mt-3">
            <strong><i class="bi bi-${icon} text-${color} me-2"></i>${title} (${rows.length})</strong>
            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered msm-table">
                    <thead class="table-${color}"><tr>${headers.map(h=>`<th>${h}</th>`).join('')}</tr></thead>
                    <tbody>${rows.map(rowFn).join('')}</tbody>
                </table>
            </div>
        </div>`;
    }

    // ── PRINT — Dense 3-column layout ─────────────────────
    document.getElementById('printResults').addEventListener('click', () => {
        if (!currentResults) return;
        const school  = document.querySelector('meta[name="school-name"]')?.content || 'CSS Kabba';
        const today   = new Date().toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
        const allCreds = [
            ...(currentResults.created||[]).map(c=>({...c,type:'created'})),
            ...(currentResults.reset  ||[]).map(r=>({...r,type:'reset'})),
        ];

        if (!allCreds.length) {
            Swal.fire({ icon:'info', title:'Nothing to Print', text:'No created or reset credentials available.', confirmButtonColor:'#2563eb' });
            return;
        }

        // Build slips — 3 per row, compact
        const slipHtml = allCreds.map(s => {
            const isReset = s.type==='reset';
            const tag = isReset ? 'RESET' : 'NEW';
            const tagColor = isReset ? '#d97706' : '#16a34a';
            return `
            <div class="slip">
                <div class="slip-tag" style="background:${tagColor}">${tag}</div>
                <div class="slip-school">${escHtml(school)}</div>
                <div class="slip-name">${escHtml(s.name)}</div>
                <div class="slip-row"><span class="sl">Adm No</span><span class="sv">${escHtml(s.admissionNo||'N/A')}</span></div>
                <div class="slip-row"><span class="sl">Class</span><span class="sv">${escHtml(s.class_name||'—')}</span></div>
                <div class="slip-row"><span class="sl">Email</span><span class="sv mono">${escHtml(s.email)}</span></div>
                <div class="slip-row"><span class="sl">Username</span><span class="sv mono">${escHtml(s.username||'')}</span></div>
                <div class="slip-pwd"><span class="pwd-label">${isReset?'New Password':'Password'}</span><span class="pwd-val">${escHtml(s.password)}</span></div>
                <div class="slip-note">Change password after first login &bull; ${window.location.hostname}</div>
            </div>`;
        }).join('');

        const printWin = window.open('','_blank');
        printWin.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<title>Credentials — ${today}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Segoe UI', Arial, sans-serif; background:#f0f4f8; font-size:10px; }

/* Summary page */
.summary-page { page-break-after: always; padding:20mm; }
.summary-page h2 { font-size:18px; color:#1e3a5f; margin-bottom:8px; }
.summary-page .meta { color:#666; font-size:12px; margin-bottom:16px; }
.sum-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
.sum-stat { background:#fff; border-radius:8px; padding:14px; text-align:center; border:1px solid #e2e8f0; }
.sum-stat .n { font-size:28px; font-weight:800; color:#1e3a5f; }
.sum-stat .l { font-size:11px; color:#64748b; margin-top:2px; }
.sum-table { width:100%; border-collapse:collapse; font-size:12px; }
.sum-table th { background:#1e3a5f; color:#fff; padding:8px 12px; text-align:left; }
.sum-table td { border:1px solid #e2e8f0; padding:7px 12px; }
.sum-table tr:nth-child(even) td { background:#f8fafc; }
.print-note { margin-top:12px; font-size:10.5px; color:#94a3b8; text-align:center; }

/* Slip grid */
.slips-page { padding:8mm; }
.slip-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:5mm; }

/* Individual slip */
.slip {
    border:1px solid #d1d5db;
    border-radius:8px;
    padding:9px 11px;
    background:#fff;
    page-break-inside: avoid;
    break-inside: avoid;
    position: relative;
    overflow: hidden;
}
.slip::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#1e3a5f,#2563eb);
}
.slip-tag {
    display:inline-block;
    color:#fff; font-size:8px; font-weight:700;
    padding:1px 6px; border-radius:10px;
    margin-bottom:5px; letter-spacing:.5px;
}
.slip-school { font-size:9px; font-weight:700; color:#1e3a5f; margin-bottom:3px; text-transform:uppercase; letter-spacing:.5px; }
.slip-name   { font-size:13px; font-weight:800; color:#0f172a; margin-bottom:7px; line-height:1.2; }
.slip-row    { display:flex; justify-content:space-between; align-items:center; padding:2px 0; border-bottom:1px dashed #f1f5f9; }
.sl { color:#64748b; font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; white-space:nowrap; margin-right:6px; }
.sv { color:#1e293b; font-size:9px; font-weight:500; text-align:right; word-break:break-all; }
.mono { font-family:'Courier New',monospace; font-size:8.5px; }
.slip-pwd {
    margin-top:7px;
    background:linear-gradient(135deg,#f0f9ff,#eff6ff);
    border:1.5px solid #bfdbfe;
    border-radius:6px;
    padding:7px 9px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.pwd-label { font-size:8px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:.3px; }
.pwd-val   { font-family:'Courier New',monospace; font-size:14px; font-weight:900; color:#1e40af; letter-spacing:2px; }
.slip-note { margin-top:5px; font-size:7.5px; color:#94a3b8; text-align:center; border-top:1px dashed #f1f5f9; padding-top:4px; }

/* Cut lines between rows - shown as dashed separator */
.cut-row { text-align:center; font-size:8px; color:#cbd5e1; letter-spacing:2px; margin:2mm 0; font-family:monospace; }

@media print {
    body { background:#fff; }
    .summary-page { padding:15mm; }
    .slips-page   { padding:6mm; }
    .slip-grid { gap:4mm; }
    .pwd-val, .slip-pwd, .slip-tag { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .summary-page { page-break-after:always; break-after:page; }
}
</style>
</head>
<body>

<!-- Summary Page -->
<div class="summary-page">
    <h2>🎓 Student Portal Credentials — ${school}</h2>
    <div class="meta">Printed: ${today} &nbsp;|&nbsp; Total slips: ${allCreds.length}</div>
    <div class="sum-grid">
        <div class="sum-stat"><div class="n" style="color:#2563eb">${allCreds.length}</div><div class="l">Total Slips</div></div>
        <div class="sum-stat"><div class="n" style="color:#16a34a">${currentResults.created?.length||0}</div><div class="l">New Accounts</div></div>
        <div class="sum-stat"><div class="n" style="color:#d97706">${currentResults.reset?.length||0}</div><div class="l">Password Resets</div></div>
        <div class="sum-stat"><div class="n" style="color:#64748b">${currentResults.skipped?.length||0}</div><div class="l">Skipped</div></div>
    </div>
    <table class="sum-table">
        <thead><tr><th>#</th><th>Student Name</th><th>Admission No</th><th>Class</th><th>Email</th><th>Type</th></tr></thead>
        <tbody>
        ${allCreds.map((s,i)=>`<tr>
            <td>${i+1}</td>
            <td><strong>${escHtml(s.name)}</strong></td>
            <td style="font-family:monospace">${escHtml(s.admissionNo||'N/A')}</td>
            <td>${escHtml(s.class_name||'—')}</td>
            <td style="font-family:monospace;font-size:10px">${escHtml(s.email)}</td>
            <td style="color:${s.type==='reset'?'#d97706':'#16a34a'};font-weight:700">${s.type==='reset'?'RESET':'NEW'}</td>
        </tr>`).join('')}
        </tbody>
    </table>
    <div class="print-note">✂ Cut individual slips along the borders &nbsp;|&nbsp; Keep credentials secure &nbsp;|&nbsp; ${school} School Management System</div>
</div>

<!-- Credential Slips — 3 per row -->
<div class="slips-page">
    <div class="slip-grid">${slipHtml}</div>
</div>

<script>
window.onload = function() {
    setTimeout(function() { window.print(); setTimeout(function() { window.close(); }, 1500); }, 600);
};
<\/script>
</body></html>`);
        printWin.document.close();
    });

    // ── Modal lifecycle ────────────────────────────────────
    function resetModal() {
        selectedStudents = [];
        currentResults   = null;
        document.getElementById('selectedAction').value = '';
        document.getElementById('massStep2').style.display='none';
        document.getElementById('massStep3').style.display='none';
        document.getElementById('massStep1').style.display='';
        document.querySelectorAll('.msm-action-card').forEach(c=>c.classList.remove('selected'));
        document.getElementById('actionWarning').style.display='none';
        document.getElementById('passwordSettings').style.display='none';
        document.getElementById('roleSettings').style.display='none';
        setStep(1);
        loadStudents();
    }

    document.getElementById('newAction').addEventListener('click', resetModal);
    document.getElementById('massStudentModal').addEventListener('hidden.bs.modal', resetModal);
    document.getElementById('massStudentModal').addEventListener('show.bs.modal', () => {
        selectedStudents = [];
        loadStudents();
    });
});
</script>
