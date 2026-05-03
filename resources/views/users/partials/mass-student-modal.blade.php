{{-- Mass Student Account Management Modal --}}
<div id="massStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content msm-modal-content">

            {{-- HEADER --}}
            <div class="modal-header msm-header">
                <div class="msm-header-inner">
                    <div class="msm-header-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title msm-title">Mass Student Account Management</h5>
                        <p class="msm-subtitle">Create, reset, revoke or reprint student credentials in bulk</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- STEP PROGRESS BAR --}}
            <div class="msm-steps-bar">
                <div class="msm-step active" id="stepBar1">
                    <div class="msm-step-circle">1</div>
                    <span>Select Students</span>
                </div>
                <div class="msm-step-line"></div>
                <div class="msm-step" id="stepBar2">
                    <div class="msm-step-circle">2</div>
                    <span>Choose Action</span>
                </div>
                <div class="msm-step-line"></div>
                <div class="msm-step" id="stepBar3">
                    <div class="msm-step-circle">3</div>
                    <span>Results</span>
                </div>
            </div>

            <div class="modal-body msm-body">

                {{-- ==================== STEP 1: SELECT STUDENTS ==================== --}}
                <div id="massStep1">

                    {{-- Filter Card --}}
                    <div class="msm-card msm-filter-card mb-3">
                        <div class="msm-card-header">
                            <i class="bi bi-funnel-fill me-2"></i> Filter Students
                        </div>
                        <div class="msm-card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="msm-label">Search</label>
                                    <div class="msm-input-wrap">
                                        <i class="bi bi-search msm-input-icon"></i>
                                        <input type="text" id="massStudentSearch" class="form-control msm-input ps-4" placeholder="Name or Admission No…">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="msm-label">Class</label>
                                    <select id="massClassFilter" class="form-select msm-input">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="msm-label">Arm</label>
                                    <select id="massArmFilter" class="form-select msm-input">
                                        <option value="">All Arms</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
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

                    {{-- Info Banner --}}
                    <div class="msm-info-banner mb-3">
                        <i class="bi bi-envelope-at-fill me-2"></i>
                        <span>Emails are auto-generated as <code>firstname.lastname@csskabba.ng</code> — special characters removed.</span>
                    </div>

                    {{-- Student Table Card --}}
                    <div class="msm-card mb-3">
                        <div class="msm-card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-lines-fill me-2"></i>Select Students</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="msm-count-badge" id="massSelectedCount">0 selected</span>
                                <button type="button" class="msm-btn-sm" id="selectAllStudents">
                                    <i class="bi bi-check-all me-1"></i> Select All
                                </button>
                            </div>
                        </div>
                        <div class="msm-card-body p-0">
                            <div class="table-responsive msm-table-wrap">
                                <table class="table msm-table mb-0">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="selectAllCheckbox" class="msm-checkbox">
                                            </th>
                                            <th>Admission No</th>
                                            <th>Student Name</th>
                                            <th>Class / Arm</th>
                                            <th>Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="massStudentList">
                                        <tr>
                                            <td colspan="6" class="msm-loading-cell">
                                                <div class="spinner-border spinner-border-sm me-2 text-primary"></div>
                                                Loading students…
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Legend --}}
                    <div class="msm-legend mb-3">
                        <div class="msm-legend-title"><i class="bi bi-lightbulb-fill me-1"></i> Available Actions</div>
                        <div class="msm-legend-grid">
                            <div class="msm-legend-item create">
                                <i class="bi bi-person-plus-fill"></i>
                                <div><strong>Create</strong> — new accounts for students without one</div>
                            </div>
                            <div class="msm-legend-item reset">
                                <i class="bi bi-key-fill"></i>
                                <div><strong>Reset</strong> — new passwords for existing accounts</div>
                            </div>
                            <div class="msm-legend-item revoke">
                                <i class="bi bi-person-x-fill"></i>
                                <div><strong>Revoke</strong> — remove user access (student record kept)</div>
                            </div>
                            <div class="msm-legend-item reprint">
                                <i class="bi bi-printer-fill"></i>
                                <div><strong>Reprint</strong> — show existing credentials (password hidden)</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="msm-btn-primary" id="proceedToAction">
                            Continue to Action <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ==================== STEP 2: CONFIGURE ACTION ==================== --}}
                <div id="massStep2" style="display:none;">

                    {{-- Selected Summary --}}
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
                                            <th>Status</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedStudentsList"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Action Chooser --}}
                    <div class="msm-card mb-3">
                        <div class="msm-card-header">
                            <i class="bi bi-lightning-charge-fill me-2"></i> Choose Action
                        </div>
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

                    {{-- Password Settings --}}
                    <div id="passwordSettings" style="display:none;">
                        <div class="msm-card mb-3">
                            <div class="msm-card-header">
                                <i class="bi bi-lock-fill me-2"></i> Password Settings
                            </div>
                            <div class="msm-card-body">
                                <div class="msm-radio-group">
                                    <label class="msm-radio-card">
                                        <input type="radio" id="passwordTypeIndividual" name="passwordTypeRadio" value="individual" checked>
                                        <div class="msm-radio-inner">
                                            <i class="bi bi-shuffle"></i>
                                            <div>
                                                <strong>Individual Random Passwords</strong>
                                                <p>Each student gets a unique random password</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="msm-radio-card">
                                        <input type="radio" id="passwordTypeSame" name="passwordTypeRadio" value="same">
                                        <div class="msm-radio-inner">
                                            <i class="bi bi-people-fill"></i>
                                            <div>
                                                <strong>Same Password for All</strong>
                                                <p>All selected students share one password</p>
                                            </div>
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

                    {{-- Role Settings --}}
                    <div id="roleSettings" style="display:none;">
                        <div class="msm-card mb-3">
                            <div class="msm-card-header"><i class="bi bi-tags-fill me-2"></i> Role Assignment</div>
                            <div class="msm-card-body">
                                <div class="msm-role-notice">
                                    <i class="bi bi-shield-check-fill me-2"></i>
                                    Student accounts are automatically assigned the <strong>Student</strong> role only.
                                </div>
                                @php
                                    $allRoles = Spatie\Permission\Models\Role::all();
                                    $studentRole = $allRoles->where('name', 'Student')->first();
                                    $otherRoles = $allRoles->where('name', '!=', 'Student');
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
                                        <p class="text-muted small mb-2"><i class="bi bi-shield-lock-fill me-1"></i> Roles below cannot be assigned to student accounts:</p>
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

                    <div id="actionWarning" class="msm-warning-banner" style="display:none;"></div>
                    <div class="msm-info-banner" id="emailFormatNote" style="display:none;">
                        <i class="bi bi-envelope-at-fill me-2"></i> Email format: <code>firstname.lastname@csskabba.ng</code>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="msm-btn-secondary" id="backToStep1">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                        <button type="button" class="msm-btn-primary" id="executeAction">
                            <i class="bi bi-check-circle me-1"></i> Execute Action
                        </button>
                    </div>
                </div>

                {{-- ==================== STEP 3: RESULTS ==================== --}}
                <div id="massStep3" style="display:none;">
                    <div id="resultsContainer"></div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="msm-btn-secondary" id="newAction">
                            <i class="bi bi-plus-circle me-1"></i> New Action
                        </button>
                        <button type="button" class="msm-btn-primary" id="printResults">
                            <i class="bi bi-printer me-1"></i> Print Credentials
                        </button>
                    </div>
                </div>

            </div>{{-- /modal-body --}}
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- STYLES --}}
{{-- ============================================================ --}}
<style>
/* ---- Variables ---- */
:root {
    --msm-primary:   #4f46e5;
    --msm-primary-h: #4338ca;
    --msm-success:   #10b981;
    --msm-warning:   #f59e0b;
    --msm-danger:    #ef4444;
    --msm-info:      #0ea5e9;
    --msm-surface:   #ffffff;
    --msm-surface2:  #f8fafc;
    --msm-border:    #e2e8f0;
    --msm-text:      #1e293b;
    --msm-muted:     #64748b;
    --msm-radius:    12px;
    --msm-shadow:    0 4px 24px rgba(0,0,0,.08);
}

/* ---- Modal shell ---- */
.msm-modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}

/* ---- Header ---- */
.msm-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 20px 24px;
    border-bottom: none;
}
.msm-header-inner { display: flex; align-items: center; gap: 14px; }
.msm-header-icon {
    width: 44px; height: 44px;
    background: rgba(255,255,255,.18);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff;
    flex-shrink: 0;
}
.msm-title  { color: #fff; font-size: 17px; font-weight: 700; margin: 0; }
.msm-subtitle { color: rgba(255,255,255,.75); font-size: 12px; margin: 2px 0 0; }

/* ---- Steps bar ---- */
.msm-steps-bar {
    display: flex; align-items: center; justify-content: center;
    gap: 0;
    padding: 14px 24px;
    background: #f1f5f9;
    border-bottom: 1px solid var(--msm-border);
}
.msm-step {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 600; color: #94a3b8;
    transition: color .3s;
}
.msm-step.active  { color: var(--msm-primary); }
.msm-step.done    { color: var(--msm-success); }
.msm-step-circle {
    width: 28px; height: 28px; border-radius: 50%;
    background: #e2e8f0; color: #94a3b8;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    transition: all .3s;
    flex-shrink: 0;
}
.msm-step.active .msm-step-circle {
    background: var(--msm-primary); color: #fff;
    box-shadow: 0 0 0 3px rgba(79,70,229,.2);
}
.msm-step.done .msm-step-circle {
    background: var(--msm-success); color: #fff;
}
.msm-step-line { flex: 1; height: 2px; background: #e2e8f0; margin: 0 12px; max-width: 80px; }

/* ---- Body ---- */
.msm-body { padding: 20px 24px; background: var(--msm-surface2); max-height: 76vh; overflow-y: auto; }

/* ---- Cards ---- */
.msm-card {
    background: var(--msm-surface);
    border: 1px solid var(--msm-border);
    border-radius: var(--msm-radius);
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.msm-filter-card { border-top: 3px solid var(--msm-primary); }
.msm-card-header {
    padding: 12px 18px;
    background: var(--msm-surface2);
    border-bottom: 1px solid var(--msm-border);
    font-size: 13px; font-weight: 600; color: var(--msm-text);
}
.msm-card-body { padding: 16px 18px; }

/* ---- Inputs ---- */
.msm-label { font-size: 12px; font-weight: 600; color: var(--msm-muted); margin-bottom: 5px; display: block; text-transform: uppercase; letter-spacing: .4px; }
.msm-input { border: 1.5px solid var(--msm-border); border-radius: 8px; font-size: 13px; transition: border-color .2s, box-shadow .2s; }
.msm-input:focus { border-color: var(--msm-primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); outline: none; }
.msm-input-wrap { position: relative; }
.msm-input-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; }

/* ---- Info / warning banners ---- */
.msm-info-banner {
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;
    padding: 10px 14px; font-size: 12.5px; color: #1e40af;
}
.msm-warning-banner {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
    padding: 10px 14px; font-size: 12.5px; color: #92400e;
}

/* ---- Count badge ---- */
.msm-count-badge {
    background: var(--msm-primary); color: #fff;
    border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 600;
}

/* ---- Small action button ---- */
.msm-btn-sm {
    background: #fff; border: 1.5px solid var(--msm-primary);
    color: var(--msm-primary); border-radius: 7px;
    padding: 4px 12px; font-size: 12px; font-weight: 600; cursor: pointer;
    transition: all .2s;
}
.msm-btn-sm:hover { background: var(--msm-primary); color: #fff; }

/* ---- Primary / secondary buttons ---- */
.msm-btn-primary {
    background: linear-gradient(135deg, var(--msm-primary), #7c3aed);
    color: #fff; border: none; border-radius: 9px;
    padding: 10px 22px; font-size: 14px; font-weight: 600; cursor: pointer;
    box-shadow: 0 3px 12px rgba(79,70,229,.3);
    transition: transform .15s, box-shadow .15s;
}
.msm-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(79,70,229,.35); }
.msm-btn-secondary {
    background: #fff; color: var(--msm-text); border: 1.5px solid var(--msm-border);
    border-radius: 9px; padding: 10px 22px; font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background .15s;
}
.msm-btn-secondary:hover { background: #f1f5f9; }

/* ---- Table ---- */
.msm-table-wrap  { max-height: 360px; overflow-y: auto; }
.msm-summary-wrap { max-height: 180px; overflow-y: auto; }
.msm-table { font-size: 12.5px; }
.msm-table thead th {
    background: #f8fafc; border-bottom: 2px solid var(--msm-border);
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: var(--msm-muted); padding: 10px 12px;
    position: sticky; top: 0; z-index: 5;
}
.msm-table tbody tr { transition: background .12s; }
.msm-table tbody tr:hover { background: #f8faff; }
.msm-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.msm-table-sm .msm-table tbody td { padding: 6px 12px; }

/* ---- Checkboxes ---- */
.msm-checkbox { accent-color: var(--msm-primary); width: 15px; height: 15px; cursor: pointer; }
.student-checkbox { accent-color: var(--msm-primary); width: 15px; height: 15px; cursor: pointer; }

/* ---- Loading cell ---- */
.msm-loading-cell { text-align: center; padding: 32px; color: var(--msm-muted); font-size: 13px; }

/* ---- Status badges ---- */
.msm-badge-has  { background: #dcfce7; color: #166534; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
.msm-badge-none { background: #f1f5f9; color: #475569; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 600; }

/* ---- Legend ---- */
.msm-legend {
    background: #fff; border: 1px solid var(--msm-border); border-radius: var(--msm-radius);
    padding: 14px 16px;
}
.msm-legend-title { font-size: 12px; font-weight: 700; color: var(--msm-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: .5px; }
.msm-legend-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.msm-legend-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; border-radius: 8px;
    font-size: 12px; color: var(--msm-text);
    border: 1px solid transparent;
}
.msm-legend-item i { font-size: 16px; flex-shrink: 0; }
.msm-legend-item.create { background: #f0fdf4; border-color: #bbf7d0; }
.msm-legend-item.create i { color: var(--msm-success); }
.msm-legend-item.reset  { background: #fffbeb; border-color: #fde68a; }
.msm-legend-item.reset  i { color: var(--msm-warning); }
.msm-legend-item.revoke { background: #fef2f2; border-color: #fecaca; }
.msm-legend-item.revoke i { color: var(--msm-danger); }
.msm-legend-item.reprint { background: #f0f9ff; border-color: #bae6fd; }
.msm-legend-item.reprint i { color: var(--msm-info); }

/* ---- Action cards ---- */
.msm-action-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.msm-action-card {
    border: 2px solid var(--msm-border); border-radius: var(--msm-radius);
    padding: 18px 12px; text-align: center; cursor: pointer;
    background: #fff; transition: all .2s;
}
.msm-action-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
.msm-action-card.selected { border-color: var(--msm-primary); background: #f5f3ff; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.msm-action-icon { font-size: 28px; margin-bottom: 8px; }
.msm-action-icon.create  { color: var(--msm-success); }
.msm-action-icon.reset   { color: var(--msm-warning); }
.msm-action-icon.revoke  { color: var(--msm-danger); }
.msm-action-icon.reprint { color: var(--msm-info); }
.msm-action-label { font-size: 13px; font-weight: 700; color: var(--msm-text); }
.msm-action-desc  { font-size: 11px; color: var(--msm-muted); margin-top: 3px; }

/* ---- Radio password cards ---- */
.msm-radio-group { display: flex; gap: 12px; flex-wrap: wrap; }
.msm-radio-card  { flex: 1; min-width: 200px; cursor: pointer; }
.msm-radio-card input { display: none; }
.msm-radio-inner {
    display: flex; align-items: center; gap: 12px;
    border: 2px solid var(--msm-border); border-radius: 10px;
    padding: 14px; background: #fff; transition: all .2s;
}
.msm-radio-inner i { font-size: 22px; color: var(--msm-muted); flex-shrink: 0; }
.msm-radio-inner strong { font-size: 13px; }
.msm-radio-inner p { font-size: 11px; color: var(--msm-muted); margin: 2px 0 0; }
.msm-radio-card input:checked ~ .msm-radio-inner {
    border-color: var(--msm-primary); background: #f5f3ff;
}
.msm-radio-card input:checked ~ .msm-radio-inner i { color: var(--msm-primary); }

/* ---- Role section ---- */
.msm-role-notice {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
    padding: 10px 14px; font-size: 12.5px; color: #166534; margin-bottom: 12px;
}
.msm-role-assigned {
    display: flex; align-items: center;
    background: #f8fafc; border: 1.5px solid var(--msm-border); border-radius: 8px;
    padding: 10px 14px; font-size: 13px;
}
.msm-badge-default {
    background: var(--msm-success); color: #fff;
    border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 600;
}
.msm-disabled-roles { display: flex; flex-wrap: wrap; gap: 8px; }
.msm-disabled-role {
    background: #f1f5f9; color: #94a3b8;
    border-radius: 20px; padding: 3px 12px; font-size: 11px;
    border: 1px solid #e2e8f0;
}

/* ---- Results ---- */
#resultsContainer .alert { border-radius: var(--msm-radius); border: none; }
#resultsContainer table { font-size: 12.5px; }

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .msm-action-grid { grid-template-columns: repeat(2,1fr); }
    .msm-legend-grid { grid-template-columns: 1fr; }
}
</style>

{{-- ============================================================ --}}
{{-- JAVASCRIPT --}}
{{-- ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    let selectedStudents = [];
    let allStudents      = [];
    let currentResults   = null;

    // ---- Helpers ----

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m =>
            ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
    }

    function generatePreviewEmail(firstname, lastname) {
        const clean = s => (s||'').toLowerCase().replace(/[^a-z0-9]/g,'') || 'user';
        return clean(firstname) + '.' + clean(lastname) + '@csskabba.ng';
    }

    function statusBadge(hasAccount) {
        return hasAccount
            ? '<span class="msm-badge-has"><i class="bi bi-check-circle-fill me-1"></i>Has Account</span>'
            : '<span class="msm-badge-none"><i class="bi bi-circle me-1"></i>No Account</span>';
    }

    function setStep(n) {
        [1,2,3].forEach(i => {
            document.getElementById('stepBar'+i).classList.remove('active','done');
            if (i < n) document.getElementById('stepBar'+i).classList.add('done');
        });
        document.getElementById('stepBar'+n).classList.add('active');
        // Replace ✓ circle for done steps
        document.querySelectorAll('.msm-step.done .msm-step-circle').forEach(el => {
            if (!el.dataset.orig) el.dataset.orig = el.textContent;
            el.innerHTML = '<i class="bi bi-check-lg"></i>';
        });
    }

    // ---- Load students ----

    function loadStudents() {
        const search  = $('#massStudentSearch').val();
        const classId = $('#massClassFilter').val();
        const armId   = $('#massArmFilter').val();
        const status  = $('#massAccountStatus').val();

        $('#massStudentList').html(
            '<tr><td colspan="6" class="msm-loading-cell"><div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading students…</td></tr>'
        );

        let url = '{{ route("get.students") }}?limit=2000';
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (classId) url += `&class_id=${classId}`;
        if (armId)   url += `&arm_id=${armId}`;
        if (status !== 'all') url += `&has_account=${status}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    allStudents = data.students.map(s => ({
                        ...s,
                        generatedEmail: generatePreviewEmail(s.firstname, s.lastname)
                    }));
                    renderStudentTable(allStudents);

                    if (data.classes && $('#massClassFilter option').length <= 1) {
                        populateFilters(data.classes, data.arms, data.class_arms);
                    }
                } else {
                    $('#massStudentList').html('<tr><td colspan="6" class="msm-loading-cell text-danger">Error loading students.</td></tr>');
                }
            })
            .catch(() => {
                $('#massStudentList').html('<tr><td colspan="6" class="msm-loading-cell text-danger">Network error.</td></tr>');
            });
    }

    // ---- Render table ----

    function renderStudentTable(students) {
        if (!students.length) {
            $('#massStudentList').html('<tr><td colspan="6" class="msm-loading-cell">No students found.</td></tr>');
            updateSelectedCount();
            return;
        }
        let html = '';
        students.forEach(student => {
            const checked = selectedStudents.some(s => s.id === student.id) ? 'checked' : '';
            html += `<tr>
                <td><input type="checkbox" class="student-checkbox" data-id="${student.id}" ${checked}></td>
                <td><strong>${escapeHtml(student.admissionNo || 'N/A')}</strong></td>
                <td>${escapeHtml(student.name)}</td>
                <td>${escapeHtml(student.class_name || '—')}${student.arm_name ? ' <span class="text-muted">/</span> ' + escapeHtml(student.arm_name) : ''}</td>
                <td>${statusBadge(student.has_account)}</td>
                <td><small class="text-muted font-monospace">${escapeHtml(student.generatedEmail)}</small></td>
            </tr>`;
        });
        $('#massStudentList').html(html);
        updateSelectedCount();

        $('.student-checkbox').off('change').on('change', function () {
            const id      = parseInt($(this).data('id'));
            const student = allStudents.find(s => s.id === id);
            if ($(this).is(':checked')) {
                if (!selectedStudents.some(s => s.id === id)) selectedStudents.push(student);
            } else {
                selectedStudents = selectedStudents.filter(s => s.id !== id);
            }
            updateSelectedCount();
        });
    }

    function updateSelectedCount() {
        $('#massSelectedCount').text(`${selectedStudents.length} selected`);
        const allVisible = allStudents.length > 0 && selectedStudents.length === allStudents.length;
        $('#selectAllCheckbox').prop('checked', allVisible);
    }

    // ---- Populate filters ----
    // KEY FIX: build a map of className → Set<armId> from classArms so that
    // deduplicated class names still reveal ALL their associated arms.

    function populateFilters(classes, arms, classArms) {
        window.classArmsData = classArms || [];

        // ----- Classes dropdown (already deduplicated by server) -----
        let classHtml = '<option value="">All Classes</option>';
        classes.forEach(cls => {
            classHtml += `<option value="${cls.id}" data-name="${escapeHtml(cls.name)}">${escapeHtml(cls.name)}</option>`;
        });
        $('#massClassFilter').html(classHtml);

        // ----- Arms dropdown -----
        let armHtml = '<option value="">All Arms</option>';
        arms.forEach(arm => {
            armHtml += `<option value="${arm.id}">${escapeHtml(arm.name)}</option>`;
        });
        $('#massArmFilter').html(armHtml);

        // Build: className (lowercase) → Set of arm IDs
        // We use class *name* as key — not ID — because the server may deduplicate
        // JSS 1 to a single representative ID while classArms still has all rows.
        window.classNameToArmIds = {};
        classArms.forEach(ca => {
            const key = (ca.class_name || '').toLowerCase().trim();
            if (!window.classNameToArmIds[key]) window.classNameToArmIds[key] = new Set();
            window.classNameToArmIds[key].add(String(ca.arm_id));
        });

        // ----- Class change → filter arms -----
        $('#massClassFilter').off('change').on('change', function () {
            const selectedOption = $(this).find(':selected');
            const selectedName   = (selectedOption.data('name') || '').toLowerCase().trim();

            if (!selectedName) {
                // Show all arms
                $('#massArmFilter option').show();
                $('#massArmFilter').val('');
            } else {
                const relatedArmIds = window.classNameToArmIds[selectedName] || new Set();

                $('#massArmFilter option').each(function () {
                    const val = $(this).val();
                    if (!val || relatedArmIds.has(val)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // If currently selected arm is no longer visible, reset it
                const currentArm = $('#massArmFilter').val();
                if (currentArm && !relatedArmIds.has(currentArm)) {
                    $('#massArmFilter').val('');
                }
            }
            applyFilters();
        });
    }

    // ---- Client-side filter ----

    function applyFilters() {
        const search  = $('#massStudentSearch').val().toLowerCase();
        const classId = $('#massClassFilter').val();
        const armId   = $('#massArmFilter').val();
        const status  = $('#massAccountStatus').val();

        let filtered = allStudents.filter(st => {
            if (search && !st.name.toLowerCase().includes(search) && !(st.admissionNo||'').toLowerCase().includes(search)) return false;
            if (classId && String(st.class_id) !== classId) return false;
            if (armId   && String(st.arm_id)   !== armId)   return false;
            if (status === 'yes' && !st.has_account) return false;
            if (status === 'no'  && st.has_account)  return false;
            return true;
        });
        renderStudentTable(filtered);
    }

    // ---- Filter event bindings ----

    $('#massStudentSearch, #massArmFilter, #massAccountStatus').on('input change', function () {
        applyFilters();
    });

    // Select all / deselect all
    $('#selectAllStudents').on('click', function () {
        selectedStudents = [...allStudents];
        renderStudentTable(allStudents);
    });

    $('#selectAllCheckbox').on('change', function () {
        selectedStudents = $(this).is(':checked') ? [...allStudents] : [];
        renderStudentTable(allStudents);
    });

    // ---- Proceed to step 2 ----

    $('#proceedToAction').on('click', function () {
        if (!selectedStudents.length) {
            Swal.fire({ icon:'warning', title:'No Students', text:'Please select at least one student.', confirmButtonColor: '#4f46e5' });
            return;
        }
        let html = '';
        selectedStudents.forEach(s => {
            html += `<tr>
                <td>${escapeHtml(s.name)}</td>
                <td>${escapeHtml(s.admissionNo||'N/A')}</td>
                <td>${statusBadge(s.has_account)}</td>
                <td><small class="font-monospace">${escapeHtml(s.generatedEmail)}</small></td>
            </tr>`;
        });
        $('#selectedStudentsList').html(html);
        $('#step2SelectedCount').text(selectedStudents.length);
        $('#massStep1').hide();
        $('#massStep2').show();
        setStep(2);
    });

    // ---- Action card selection ----

    $('.msm-action-card').on('click', function () {
        $('.msm-action-card').removeClass('selected');
        $(this).addClass('selected');
        const action = $(this).data('action');
        $('#selectedAction').val(action);

        const show = (action === 'create' || action === 'reset');
        $('#passwordSettings').toggle(show);
        $('#roleSettings').toggle(show);
        $('#emailFormatNote').toggle(show);

        const hasAcc = selectedStudents.filter(s => s.has_account).length;
        const noAcc  = selectedStudents.filter(s => !s.has_account).length;
        let warn = '';
        if (action === 'create' && hasAcc)  warn = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${hasAcc} student(s) already have accounts and will be skipped.`;
        if (action === 'reset'  && noAcc)   warn = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${noAcc} student(s) have no accounts and will be skipped.`;
        if (action === 'revoke' && noAcc)   warn = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${noAcc} student(s) have no accounts and will be skipped.`;
        if (warn) $('#actionWarning').html(warn).show(); else $('#actionWarning').hide();
    });

    $('input[name="passwordTypeRadio"]').on('change', function () {
        $('#sharedPasswordContainer').toggle($(this).val() === 'same');
    });

    // ---- Back ----

    $('#backToStep1').on('click', function () {
        $('#massStep2').hide();
        $('#massStep1').show();
        setStep(1);
    });

    // ---- Execute ----

    $('#executeAction').on('click', function () {
        const actionType = $('#selectedAction').val();
        if (!actionType) {
            Swal.fire({ icon:'error', title:'No Action', text:'Please select an action.', confirmButtonColor:'#4f46e5' });
            return;
        }

        const payload = {
            _token: '{{ csrf_token() }}',
            students: selectedStudents.map(s => ({ student_id: s.id })),
            action_type: actionType,
        };

        if (actionType === 'create' || actionType === 'reset') {
            const passwordType = $('input[name="passwordTypeRadio"]:checked').val();
            payload.password_type = passwordType;
            if (passwordType === 'same') {
                payload.shared_password = $('#sharedPassword').val();
                if (!payload.shared_password || payload.shared_password.length < 6) {
                    Swal.fire({ icon:'error', title:'Password Too Short', text:'Shared password must be at least 6 characters.', confirmButtonColor:'#4f46e5' });
                    return;
                }
            }
            payload.roles = ['Student'];
        }

        Swal.fire({ title:'Processing…', text:'Please wait while accounts are being updated.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('{{ route("users.mass-create-students") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                currentResults = data;
                displayResults(data);
                $('#massStep2').hide();
                $('#massStep3').show();
                setStep(3);
            } else {
                Swal.fire({ icon:'error', title:'Error', text: data.message || 'Operation failed.', confirmButtonColor:'#4f46e5' });
            }
        })
        .catch(() => {
            Swal.close();
            Swal.fire({ icon:'error', title:'Network Error', text:'A network error occurred.', confirmButtonColor:'#4f46e5' });
        });
    });

    // ---- Display results ----

    function displayResults(data) {
        let html = `<div class="alert alert-success" style="border-radius:12px;">
            <h5 class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>Operation Complete</h5>
            <p class="mb-0">${escapeHtml(data.message)}</p>
        </div>`;

        if (data.created?.length) {
            html += tableHtml('Created Accounts', data.created, 'success', 'person-plus-fill',
                ['Name','Username','Email','Password','Admission No'],
                c => `<tr><td>${escapeHtml(c.name)}</td><td><code>${escapeHtml(c.username)}</code></td><td>${escapeHtml(c.email)}</td><td><code class="text-success fw-bold">${escapeHtml(c.password)}</code></td><td>${escapeHtml(c.admissionNo||'N/A')}</td></tr>`
            );
        }
        if (data.reset?.length) {
            html += tableHtml('Password Resets', data.reset, 'warning', 'key-fill',
                ['Name','Username','Email','New Password','Admission No'],
                r => `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td><code class="text-warning fw-bold">${escapeHtml(r.password)}</code></td><td>${escapeHtml(r.admissionNo||'N/A')}</td></tr>`
            );
        }
        if (data.revoked?.length) {
            html += `<div class="mt-3 p-3 border rounded-3"><strong><i class="bi bi-person-x-fill text-danger me-2"></i>Revoked (${data.revoked.length})</strong><ul class="mt-2 mb-0">`;
            data.revoked.forEach(r => { html += `<li>${escapeHtml(r.name)} (${escapeHtml(r.admissionNo||'N/A')}) — account removed</li>`; });
            html += '</ul></div>';
        }
        if (data.reprinted?.length) {
            html += tableHtml('Reprinted Credentials', data.reprinted, 'info', 'printer-fill',
                ['Name','Username','Email','Admission No','Note'],
                r => `<tr><td>${escapeHtml(r.name)}</td><td><code>${escapeHtml(r.username)}</code></td><td>${escapeHtml(r.email)}</td><td>${escapeHtml(r.admissionNo||'N/A')}</td><td><small class="text-muted">Password hidden</small></td></tr>`
            );
        }
        if (data.skipped?.length) {
            html += `<div class="mt-3 p-3 border rounded-3 bg-light"><strong><i class="bi bi-skip-forward-fill text-muted me-2"></i>Skipped (${data.skipped.length})</strong><ul class="mt-2 mb-0">`;
            data.skipped.forEach(s => { html += `<li class="text-muted">${escapeHtml(s)}</li>`; });
            html += '</ul></div>';
        }
        $('#resultsContainer').html(html);
    }

    function tableHtml(title, rows, color, icon, headers, rowFn) {
        const headCols = headers.map(h => `<th>${h}</th>`).join('');
        return `<div class="mt-3"><strong><i class="bi bi-${icon} text-${color} me-2"></i>${title} (${rows.length})</strong>
            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered msm-table">
                    <thead class="table-${color}"><tr>${headCols}</tr></thead>
                    <tbody>${rows.map(rowFn).join('')}</tbody>
                </table>
            </div></div>`;
    }

    // ---- Print ----

    $('#printResults').on('click', function () {
        if (!currentResults) return;

        const schoolName = document.querySelector('meta[name="school-name"]')?.content || 'CSS Kabba';
        const today      = new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'long', year:'numeric' });
        const printWindow = window.open('', '_blank');

        let credHtml = '';
        const allCreds = [
            ...(currentResults.created || []).map(c => ({ ...c, type:'created' })),
            ...(currentResults.reset   || []).map(r => ({ ...r, type:'reset' })),
        ];

        allCreds.forEach(s => {
            const isReset   = s.type === 'reset';
            const accentClr = isReset ? '#f59e0b' : '#4f46e5';
            const subtitle  = isReset ? 'PASSWORD RESET CONFIRMATION' : 'STUDENT PORTAL ACCESS CREDENTIALS';
            const logo      = isReset ? '🔄' : '🎓';
            const pwdLabel  = isReset ? 'New Password' : 'Password';

            credHtml += `
            <div class="slip">
                <div class="slip-inner" style="border-top:4px solid ${accentClr};">
                    <div class="slip-head">
                        <div class="slip-logo">${logo}</div>
                        <h2>${escapeHtml(schoolName)}</h2>
                        <p class="slip-sub">${subtitle}</p>
                        <p class="slip-date">Issued: ${today}</p>
                    </div>
                    <div class="slip-body">
                        <div class="slip-row"><span class="slip-lbl">Student Name</span><span class="slip-val fw">${escapeHtml(s.name)}</span></div>
                        <div class="slip-row"><span class="slip-lbl">Admission No</span><span class="slip-val">${escapeHtml(s.admissionNo||'N/A')}</span></div>
                        <div class="slip-row"><span class="slip-lbl">Login Email</span><span class="slip-val mono blue">${escapeHtml(s.email)}</span></div>
                        <div class="slip-row"><span class="slip-lbl">Username</span><span class="slip-val mono">${escapeHtml(s.username)}</span></div>
                        <div class="slip-row highlight"><span class="slip-lbl">${pwdLabel}</span><span class="slip-val mono green big">${escapeHtml(s.password)}</span></div>
                        <div class="slip-row"><span class="slip-lbl">Role</span><span class="slip-val"><span class="role-pill">Student</span></span></div>
                    </div>
                    <div class="slip-foot">
                        🔒 Change your password after first login &nbsp;|&nbsp; 🌐 ${window.location.origin}
                    </div>
                </div>
                <div class="cut-line">✂ ·· ·· ·· CUT HERE ·· ·· ·· ✂</div>
            </div>`;
        });

        const printHtml = `<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<title>Student Credentials — ${today}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4f8;padding:30px;}
.page-wrap{max-width:820px;margin:0 auto;}
.summary{background:#fff;border-radius:16px;padding:24px;margin-bottom:28px;box-shadow:0 4px 16px rgba(0,0,0,.08);}
.summary h3{font-size:18px;color:#1e293b;margin-bottom:16px;}
.stats{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:18px;}
.stat{padding:14px 20px;border-radius:10px;color:#fff;text-align:center;min-width:130px;}
.stat .n{font-size:26px;font-weight:700;}
.stat .l{font-size:11px;opacity:.9;}
.sum-table{width:100%;border-collapse:collapse;}
.sum-table th,.sum-table td{border:1px solid #e2e8f0;padding:9px 12px;font-size:13px;}
.sum-table th{background:#f8fafc;font-weight:600;}
.slip{margin-bottom:10px;page-break-inside:avoid;break-inside:avoid;}
.slip-inner{background:#fff;border-radius:14px;padding:22px 24px;box-shadow:0 4px 16px rgba(0,0,0,.07);}
.slip-head{text-align:center;padding-bottom:14px;border-bottom:1.5px dashed #e2e8f0;margin-bottom:14px;}
.slip-logo{font-size:36px;margin-bottom:6px;}
.slip-head h2{font-size:20px;color:#1e293b;font-weight:700;}
.slip-sub{font-size:10px;letter-spacing:1.5px;color:#4f46e5;font-weight:600;margin-top:4px;}
.slip-date{font-size:11px;color:#94a3b8;margin-top:6px;}
.slip-row{display:flex;padding:9px 0;border-bottom:1px dashed #f1f5f9;align-items:center;}
.slip-row.highlight{background:#f0fdf4;border-radius:8px;padding:10px 8px;margin:4px -8px;border-bottom:none;}
.slip-lbl{width:34%;font-size:12px;font-weight:600;color:#64748b;}
.slip-val{width:66%;font-size:13px;color:#1e293b;word-break:break-all;}
.fw{font-weight:700;}
.mono{font-family:'Courier New',monospace;}
.blue{color:#2563eb;}
.green{color:#059669;}
.big{font-size:17px;font-weight:700;background:#f0fdf4;padding:4px 10px;border-radius:6px;display:inline-block;}
.role-pill{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border-radius:20px;padding:3px 14px;font-size:11px;font-weight:600;}
.slip-foot{text-align:center;margin-top:14px;padding-top:10px;border-top:1px dashed #e2e8f0;font-size:10px;color:#94a3b8;}
.cut-line{text-align:center;font-size:11px;color:#cbd5e1;letter-spacing:2px;margin:10px 0 18px;font-family:monospace;}
@media print{
  body{background:#fff;padding:0;}
  .slip{page-break-after:always;break-after:page;}
  .slip:last-child{page-break-after:auto;break-after:auto;}
  .big,.role-pill{print-color-adjust:exact;-webkit-print-color-adjust:exact;}
}
</style></head><body>
<div class="page-wrap">
  <div class="summary">
    <h3>📋 Credentials Summary — ${today}</h3>
    <div class="stats">
      <div class="stat" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><div class="n">${(currentResults.created?.length||0)+(currentResults.reset?.length||0)}</div><div class="l">Total</div></div>
      <div class="stat" style="background:linear-gradient(135deg,#10b981,#059669)"><div class="n">${currentResults.created?.length||0}</div><div class="l">Created</div></div>
      <div class="stat" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><div class="n">${currentResults.reset?.length||0}</div><div class="l">Resets</div></div>
      <div class="stat" style="background:linear-gradient(135deg,#94a3b8,#64748b)"><div class="n">${currentResults.skipped?.length||0}</div><div class="l">Skipped</div></div>
    </div>
    <table class="sum-table"><thead><tr><th>Category</th><th>Count</th></tr></thead>
    <tbody>
      <tr><td>✅ New Accounts</td><td>${currentResults.created?.length||0}</td></tr>
      <tr><td>🔄 Resets</td><td>${currentResults.reset?.length||0}</td></tr>
      <tr><td>⏭️ Skipped</td><td>${currentResults.skipped?.length||0}</td></tr>
    </tbody></table>
    <p style="margin-top:12px;font-size:11px;color:#94a3b8;">✂ Cut along each slip's dashed line.</p>
  </div>
  ${credHtml}
  <p style="text-align:center;font-size:10px;color:#94a3b8;margin-top:20px;">
    Official document — CSS Kabba School Management System. Keep credentials secure.
  </p>
</div>
<script>window.onload=function(){setTimeout(function(){window.print();setTimeout(function(){window.close();},1000);},500);};<\/script>
</body></html>`;

        printWindow.document.write(printHtml);
        printWindow.document.close();
    });

    // ---- New action / modal reset ----

    $('#newAction').on('click', function () {
        resetModal();
    });

    $('#massStudentModal').on('hidden.bs.modal', function () {
        resetModal();
    });

    function resetModal() {
        selectedStudents = [];
        currentResults   = null;
        $('#selectedAction').val('');
        $('#massStep2, #massStep3').hide();
        $('#massStep1').show();
        $('.msm-action-card').removeClass('selected');
        setStep(1);
        loadStudents();
    }

    $('#massStudentModal').on('show.bs.modal', function () {
        selectedStudents = [];
        loadStudents();
    });

});
</script>
