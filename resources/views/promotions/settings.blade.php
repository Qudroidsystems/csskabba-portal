{{-- resources/views/promotions/settings.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent:  #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-danger:  #dc2626;
    --pay-muted:   #6b7280;
    --pay-border:  #e2e8f0;
    --pay-bg:      #f8fafc;
    --pay-radius:  12px;
    --pay-shadow:  0 2px 8px rgba(0,0,0,.08);
}
.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius); padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.pay-hero::before {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px; background: rgba(255,255,255,.06); border-radius: 50%;
}
.pay-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }
.setting-card {
    background: #fff; border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius); padding: 20px; margin-bottom: 20px;
    transition: all .3s ease; height: 100%;
}
.setting-card:hover { box-shadow: var(--pay-shadow); transform: translateY(-2px); }
.rule-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.rule-badge.compulsory_only { background: #fef3c7; color: #92400e; }
.rule-badge.average_only    { background: #dbeafe; color: #1e40af; }
.rule-badge.both            { background: #dcfce7; color: #166534; }

/* ── Modal fix: overflow:visible so modal-dialog-scrollable works ── */
.modal-content {
    border-radius: 16px;
    overflow: visible; /* was: hidden — this was breaking modal-dialog-scrollable */
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 56px); /* Bootstrap default minus some breathing room */
}
.modal-header {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px; border-bottom: none;
    border-radius: 16px 16px 0 0;
    flex-shrink: 0;
}
.modal-header .modal-title { color: #fff; font-weight: 700; }
.modal-header .btn-close { filter: invert(1); }
.modal-footer {
    flex-shrink: 0;
    border-radius: 0 0 16px 16px;
}
.modal-body {
    overflow-y: auto;
    flex: 1 1 auto;
    padding: 1.5rem;
}

.form-section { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
.form-section-title {
    font-size: 14px; font-weight: 700; color: var(--pay-primary);
    margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid var(--pay-border);
}
.info-banner {
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 10px;
}
.info-banner i { font-size: 20px; color: #2563eb; margin-top: 1px; }
.info-banner .text { font-size: 13px; color: #1e40af; }
.info-banner .text strong { display: block; margin-bottom: 4px; }

/* ── Conditional rule builder ─────────────────────────────────────────────── */
.cond-rule-card {
    background: #fff; border: 1.5px solid var(--pay-border); border-radius: 10px;
    padding: 16px; margin-bottom: 12px; position: relative;
}
.cond-rule-card .rule-num {
    position: absolute; top: -10px; left: 16px;
    background: var(--pay-primary); color: #fff;
    font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px;
}
.subject-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;
    border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 500;
    cursor: pointer; transition: all .15s; margin: 3px;
}
.subject-chip:hover { background: #dbeafe; }
.subject-chip.selected {
    background: #2563eb; border-color: #2563eb; color: #fff;
}
.subject-chip.fail-chip.selected { background: #dc2626; border-color: #dc2626; color: #fff; }
.grade-select-inline {
    border: 1px solid #bfdbfe; border-radius: 6px; font-size: 11px;
    padding: 1px 4px; color: #1e40af; background: #fff; cursor: pointer;
}
.other-cond-row {
    display: flex; align-items: center; gap: 8px; margin: 4px 0;
    background: #f8fafc; border-radius: 8px; padding: 6px 10px; font-size: 13px;
}
.remove-cond { background: none; border: none; color: #dc2626; cursor: pointer; padding: 0 4px; }
.label-color-dot {
    width: 14px; height: 14px; border-radius: 50%; display: inline-block;
    vertical-align: middle; margin-right: 4px;
}
.no-rules-placeholder {
    text-align: center; padding: 20px; color: var(--pay-muted);
    background: #f8fafc; border-radius: 10px; border: 2px dashed var(--pay-border);
    font-size: 13px;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="pay-hero">
                <h1><i class="ri-settings-4-line me-2"></i>Promotion Settings</h1>
                <p>Configure promotion rules and intelligent conditional labels for each class.</p>
            </div>

            <div class="info-banner">
                <i class="ri-information-line"></i>
                <div class="text">
                    <strong>How Promotion Rules Work</strong>
                    The system uses compulsory subjects (defined per class/term) plus optional average thresholds to
                    determine promotion. You can also define <strong>Conditional Labels</strong> — smart annotations
                    that appear on a student's promotion card when specific conditions are met, helping admins make
                    informed decisions without forcing an automatic outcome.
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding:16px 20px">
                    <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                        <i class="ri-list-check me-2"></i>Promotion Rules
                        <span class="badge bg-primary ms-2">{{ $settings->count() }}</span>
                    </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                        <i class="ri-add-line me-1"></i>Add New Rule
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse ($settings as $setting)
                        <div class="col-md-6 col-lg-4">
                            <div class="setting-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1 fw-bold">
                                            {{ $setting->schoolclass->schoolclass }}
                                            {{ $setting->schoolclass->arm ?? '' }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $setting->session?->session ?? 'All Sessions' }}
                                            &mdash; {{ $setting->term?->term ?? 'All Terms' }}
                                        </small>
                                    </div>
                                    <span class="rule-badge {{ $setting->rule_type }}">
                                        @if($setting->rule_type==='compulsory_only') Compulsory Only
                                        @elseif($setting->rule_type==='average_only') Average Only
                                        @else Both Rules @endif
                                    </span>
                                </div>

                                @if(in_array($setting->rule_type,['compulsory_only','both']))
                                <div class="mb-3">
                                    <div class="small text-muted mb-1"><i class="ri-book-open-line me-1"></i>Fail Action:</div>
                                    <div class="fw-semibold">
                                        @if($setting->compulsory_fail_action==='repeat') <span class="badge bg-danger">Repeat</span>
                                        @elseif($setting->compulsory_fail_action==='see_principal') <span class="badge bg-info">See Principal</span>
                                        @elseif($setting->compulsory_fail_action==='trial') <span class="badge bg-warning">Trial</span>
                                        @else <span class="badge bg-secondary">Repeat (default)</span>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if(in_array($setting->rule_type,['average_only','both']))
                                <div class="mb-3">
                                    <div class="small text-muted mb-1"><i class="ri-percent-line me-1"></i>Pass Averages:</div>
                                    <div class="fw-semibold">
                                        @if($setting->promotion_pass_average) <div><span class="badge bg-success me-1">{{ $setting->promotion_pass_average }}%</span> Promoted</div> @endif
                                        @if($setting->trial_pass_average) <div><span class="badge bg-warning me-1">{{ $setting->trial_pass_average }}%</span> Trial</div> @endif
                                        @if($setting->see_principal_average) <div><span class="badge bg-info me-1">{{ $setting->see_principal_average }}%</span> See Principal</div> @endif
                                        @if(!$setting->promotion_pass_average && !$setting->trial_pass_average && !$setting->see_principal_average)
                                            <span class="text-muted">No thresholds set</span>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($setting->rule_type==='both')
                                <div class="mb-3">
                                    <div class="small text-muted mb-1"><i class="ri-logic-line me-1"></i>Logic:</div>
                                    <span class="badge bg-primary">{{ strtoupper($setting->combined_logic) }}</span>
                                    {{ $setting->combined_logic==='and' ? 'Both must be met' : 'Either can be met' }}
                                </div>
                                @endif

                                @if(!empty($setting->promotion_rules))
                                <div class="mb-3">
                                    <div class="small text-muted mb-1"><i class="ri-price-tag-3-line me-1"></i>Conditional Labels:</div>
                                    @foreach($setting->promotion_rules as $rule)
                                    <span class="badge me-1 mb-1"
                                          style="background:{{ $rule['color']==='danger'?'#dc2626':($rule['color']==='success'?'#16a34a':($rule['color']==='info'?'#2563eb':'#d97706')) }}">
                                        {{ $rule['label'] }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif

                                <div class="border-top pt-3 mt-2">
                                    <div class="row g-2">
                                        <div class="col-6"><small class="text-muted d-block">Promoted</small><small class="fw-semibold">{{ $setting->promoted_label }}</small></div>
                                        <div class="col-6"><small class="text-muted d-block">Trial</small><small class="fw-semibold">{{ $setting->trial_label }}</small></div>
                                        <div class="col-6"><small class="text-muted d-block">See Principal</small><small class="fw-semibold">{{ $setting->see_principal_label }}</small></div>
                                        <div class="col-6"><small class="text-muted d-block">Repeat</small><small class="fw-semibold">{{ $setting->repeat_label }}</small></div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-setting"
                                        data-id="{{ $setting->id }}"
                                        data-schoolclass_id="{{ $setting->schoolclass_id }}"
                                        data-session_id="{{ $setting->session_id }}"
                                        data-term_id="{{ $setting->term_id }}"
                                        data-rule_type="{{ $setting->rule_type }}"
                                        data-compulsory_fail_action="{{ $setting->compulsory_fail_action }}"
                                        data-promotion_pass_average="{{ $setting->promotion_pass_average }}"
                                        data-trial_pass_average="{{ $setting->trial_pass_average }}"
                                        data-see_principal_average="{{ $setting->see_principal_average }}"
                                        data-combined_logic="{{ $setting->combined_logic }}"
                                        data-promoted_label="{{ $setting->promoted_label }}"
                                        data-trial_label="{{ $setting->trial_label }}"
                                        data-see_principal_label="{{ $setting->see_principal_label }}"
                                        data-repeat_label="{{ $setting->repeat_label }}"
                                        data-promotion_rules="{{ json_encode($setting->promotion_rules ?? []) }}">
                                        <i class="ri-pencil-line"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-setting"
                                        data-id="{{ $setting->id }}"
                                        data-name="{{ $setting->schoolclass->schoolclass }}">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="ri-settings-4-line" style="font-size:48px;opacity:.3"></i>
                                <p class="mt-3 text-muted">No promotion rules configured yet.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                                    Create your first promotion rule
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Add/Edit Setting Modal ══════════════════════════════════════════════════ --}}
<div class="modal fade" id="addSettingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-settings-4-line me-2"></i>Promotion Rule Settings</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="settingForm">
                @csrf
                <input type="hidden" name="id" id="setting_id">
                <input type="hidden" name="promotion_rules" id="promotion_rules_input">

                <div class="modal-body">

                    {{-- ── 1. Class / Session / Term ──────────────────────────────── --}}
                    <div class="form-section">
                        <div class="form-section-title"><i class="ri-book-2-line me-2"></i>Class &amp; Scope</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-select" name="schoolclass_id" id="schoolclass_id" required>
                                    <option value="">-- Select Class --</option>
                                    @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Session <small class="text-muted">(optional)</small></label>
                                <select class="form-select" name="session_id" id="session_id">
                                    <option value="">-- All Sessions --</option>
                                    @foreach ($sessions as $s)
                                    <option value="{{ $s->id }}">{{ $s->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Term <small class="text-muted">(optional)</small></label>
                                <select class="form-select" name="term_id" id="term_id">
                                    <option value="">-- All Terms --</option>
                                    @foreach ($terms as $t)
                                    <option value="{{ $t->id }}">{{ $t->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rule Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="rule_type" id="rule_type" required>
                                    <option value="compulsory_only">Compulsory Subjects Only</option>
                                    <option value="average_only">Pass Average Only</option>
                                    <option value="both">Both Compulsory &amp; Average</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── 2. Compulsory Rules ─────────────────────────────────────── --}}
                    <div id="compulsorySection" class="form-section" style="display:none">
                        <div class="form-section-title"><i class="ri-book-open-line me-2"></i>Compulsory Subject Rules</div>
                        <div class="info-banner mb-0">
                            <i class="ri-information-line"></i>
                            <div class="text">
                                Compulsory subjects and their minimum grades are defined in
                                <strong>Compulsory Subject Class Management</strong>.
                                The system automatically checks all assigned compulsory subjects for the selected term.
                                Choose what happens when a student fails any of them.
                            </div>
                        </div>
                        <div class="row mt-3 g-3">
                            <div class="col-md-6">
                                <label class="form-label">Action if Compulsory Subject(s) Failed</label>
                                <select class="form-select" name="compulsory_fail_action" id="compulsory_fail_action">
                                    <option value="repeat">Advice to Repeat</option>
                                    <option value="see_principal">Advised to See Principal</option>
                                    <option value="trial">Promoted on Trial</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── 3. Average Rules ────────────────────────────────────────── --}}
                    <div id="averageSection" class="form-section" style="display:none">
                        <div class="form-section-title"><i class="ri-percent-line me-2"></i>Pass Average Rules</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Promotion Pass Average (%)</label>
                                <input type="number" step="0.5" class="form-control" name="promotion_pass_average" id="promotion_pass_average" placeholder="e.g. 50">
                                <div class="form-text text-success">Student is fully promoted</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Trial Pass Average (%)</label>
                                <input type="number" step="0.5" class="form-control" name="trial_pass_average" id="trial_pass_average" placeholder="e.g. 45">
                                <div class="form-text text-warning">Promoted on trial basis</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">See Principal Average (%)</label>
                                <input type="number" step="0.5" class="form-control" name="see_principal_average" id="see_principal_average" placeholder="e.g. 40">
                                <div class="form-text text-info">Advised to see principal</div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-2 py-2">
                            <i class="ri-information-line me-1"></i>
                            <small>Evaluated in order: Promotion → Trial → See Principal → Repeat</small>
                        </div>
                    </div>

                    {{-- ── 4. Combined Logic ───────────────────────────────────────── --}}
                    <div id="combinedLogicSection" class="form-section" style="display:none">
                        <div class="form-section-title"><i class="ri-git-merge-line me-2"></i>Combined Logic</div>
                        <div class="col-md-8">
                            <select class="form-select" name="combined_logic" id="combined_logic">
                                <option value="and">AND — Both conditions must be met</option>
                                <option value="or">OR — Either condition can be met</option>
                            </select>
                        </div>
                    </div>

                    {{-- ── 5. Conditional Label Rules ───────────────────────────────── --}}
                    <div class="form-section">
                        <div class="form-section-title d-flex justify-content-between align-items-center">
                            <span><i class="ri-price-tag-3-line me-2"></i>Conditional Label Rules</span>
                            <button type="button" class="btn btn-sm btn-primary" id="addCondRuleBtn" disabled>
                                <i class="ri-add-line me-1"></i>Add Rule
                            </button>
                        </div>
                        <div class="info-banner mb-3">
                            <i class="ri-lightbulb-line"></i>
                            <div class="text">
                                <strong>What are conditional labels?</strong>
                                Define smart annotations that appear on a student's promotion card when specific
                                conditions are met. Example: "Borderline — check Maths" when a student fails
                                Mathematics but passes all other subjects. These labels <em>inform</em> the admin
                                but do not override the promotion decision.
                            </div>
                        </div>
                        <div id="condRulesContainer">
                            <div class="no-rules-placeholder" id="noRulesMsg">
                                <i class="ri-price-tag-3-line d-block mb-2" style="font-size:2rem;opacity:.3"></i>
                                Select a class above, then click <strong>Add Rule</strong> to create a conditional label.
                            </div>
                        </div>
                    </div>

                    {{-- ── 6. Labels ───────────────────────────────────────────────── --}}
                    <div class="form-section">
                        <div class="form-section-title"><i class="ri-price-tag-line me-2"></i>Promotion Status Labels</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Promoted</label>
                                <input type="text" class="form-control" name="promoted_label" id="promoted_label" placeholder="Promoted">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Trial</label>
                                <input type="text" class="form-control" name="trial_label" id="trial_label" placeholder="Promoted on Trial">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">See Principal</label>
                                <input type="text" class="form-control" name="see_principal_label" id="see_principal_label" placeholder="Advised to See Principal">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Repeat</label>
                                <input type="text" class="form-control" name="repeat_label" id="repeat_label" placeholder="Advice to Repeat">
                            </div>
                        </div>
                    </div>

                </div>{{-- /.modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ═══════════════════════════════════════════════════════════════════════════
   State
═══════════════════════════════════════════════════════════════════════════ */
let allSubjectsForClass        = [];   // [{id, subject, subject_code}]
let compulsorySubjectsForClass = [];   // [{id, subject, min_grade}]
let condRules                  = [];   // [{label, color, description, failed_subject_ids, other_subject_conditions}]
const GRADE_SCALES_SENIOR = ['A1','B2','B3','C4','C5','C6','D7','E8','F9'];
const GRADE_SCALES_JUNIOR = ['A','B','C','D','F'];
let gradeScale = GRADE_SCALES_JUNIOR;

/* ═══════════════════════════════════════════════════════════════════════════
   Rule-type section toggle
═══════════════════════════════════════════════════════════════════════════ */
function toggleSections() {
    const v = document.getElementById('rule_type').value;
    document.getElementById('compulsorySection').style.display    = ['compulsory_only','both'].includes(v) ? 'block' : 'none';
    document.getElementById('averageSection').style.display       = ['average_only','both'].includes(v)    ? 'block' : 'none';
    document.getElementById('combinedLogicSection').style.display = v === 'both'                           ? 'block' : 'none';
}
document.getElementById('rule_type').addEventListener('change', toggleSections);

/* ═══════════════════════════════════════════════════════════════════════════
   Fetch subjects when class/session/term changes
═══════════════════════════════════════════════════════════════════════════ */
async function refreshSubjects() {
    const classId   = document.getElementById('schoolclass_id').value;
    const termId    = document.getElementById('term_id').value;
    const sessionId = document.getElementById('session_id').value;
    const addBtn    = document.getElementById('addCondRuleBtn');

    if (!classId) { addBtn.disabled = true; return; }

    try {
        const [subResp, compResp] = await Promise.all([
            fetch(`/promotion-settings/subjects-by-class?classid=${classId}&termid=${termId}&sessionid=${sessionId}`),
            fetch(`/promotion-settings/compulsory-by-class?classid=${classId}&termid=${termId}&sessionid=${sessionId}`)
        ]);
        const subData  = await subResp.json();
        const compData = await compResp.json();

        allSubjectsForClass        = subData.success  ? subData.subjects  : [];
        compulsorySubjectsForClass = compData.success ? compData.subjects : [];

        // Detect grade scale from compulsory subjects or default to junior
        const isSeenSenior = compulsorySubjectsForClass.some(s => s.min_grade && /[0-9]/.test(s.min_grade));
        gradeScale = isSeenSenior ? GRADE_SCALES_SENIOR : GRADE_SCALES_JUNIOR;

        addBtn.disabled = false;
        rerenderAllCondRules();
    } catch(e) {
        console.error(e);
    }
}

['schoolclass_id','session_id','term_id'].forEach(id => {
    document.getElementById(id).addEventListener('change', refreshSubjects);
});

/* ═══════════════════════════════════════════════════════════════════════════
   Conditional Rules renderer
═══════════════════════════════════════════════════════════════════════════ */
function rerenderAllCondRules() {
    const container = document.getElementById('condRulesContainer');
    const noMsg     = document.getElementById('noRulesMsg');

    if (condRules.length === 0) {
        container.innerHTML = '';
        container.appendChild(noMsg);
        noMsg.style.display = 'block';
        return;
    }
    noMsg.style.display = 'none';

    const cards = condRules.map((rule, idx) => buildCondRuleCard(rule, idx)).join('');
    container.innerHTML = cards;
    noMsg.style.display = 'none';

    // Attach chip events
    container.querySelectorAll('.fail-chip').forEach(chip => {
        chip.addEventListener('click', () => toggleFailSubject(chip));
    });
    container.querySelectorAll('.add-other-cond-btn').forEach(btn => {
        btn.addEventListener('click', () => showOtherCondPicker(parseInt(btn.dataset.ruleIdx)));
    });
    container.querySelectorAll('.remove-cond').forEach(btn => {
        btn.addEventListener('click', () => removeOtherCond(parseInt(btn.dataset.ruleIdx), parseInt(btn.dataset.condIdx)));
    });
    container.querySelectorAll('.remove-rule-btn').forEach(btn => {
        btn.addEventListener('click', () => removeCondRule(parseInt(btn.dataset.idx)));
    });
    container.querySelectorAll('.rule-label-input').forEach(inp => {
        inp.addEventListener('input', e => { condRules[parseInt(e.target.dataset.idx)].label = e.target.value; });
    });
    container.querySelectorAll('.rule-desc-input').forEach(inp => {
        inp.addEventListener('input', e => { condRules[parseInt(e.target.dataset.idx)].description = e.target.value; });
    });
    container.querySelectorAll('.rule-color-select').forEach(sel => {
        sel.addEventListener('change', e => { condRules[parseInt(e.target.dataset.idx)].color = e.target.value; });
    });
}

function colorHex(c) {
    return {danger:'#dc2626', success:'#16a34a', warning:'#d97706', info:'#2563eb', primary:'#1e3a5f'}[c] || '#d97706';
}

function buildCondRuleCard(rule, idx) {
    const failChips = compulsorySubjectsForClass.map(s => {
        const sel = (rule.failed_subject_ids || []).includes(s.id) ? 'selected' : '';
        return `<span class="subject-chip fail-chip ${sel}"
                      data-rule-idx="${idx}" data-subject-id="${s.id}">
                    <i class="ri-close-circle-line" style="font-size:11px"></i>
                    ${s.subject}
                </span>`;
    }).join('');

    const otherCondRows = (rule.other_subject_conditions || []).map((cond, ci) => {
        const subName = allSubjectsForClass.find(s => s.id == cond.subject_id)?.subject || `Subject #${cond.subject_id}`;
        return `<div class="other-cond-row">
            <i class="ri-checkbox-circle-line text-success"></i>
            <span class="flex-grow-1"><strong>${subName}</strong> has grade ≥ <strong>${cond.min_grade || 'Pass'}</strong></span>
            <button type="button" class="remove-cond" data-rule-idx="${idx}" data-cond-idx="${ci}">
                <i class="ri-close-line"></i>
            </button>
        </div>`;
    }).join('');

    const colorOpts = ['warning','danger','info','success','primary'].map(c =>
        `<option value="${c}" ${rule.color===c?'selected':''}>${c.charAt(0).toUpperCase()+c.slice(1)}</option>`
    ).join('');

    return `
    <div class="cond-rule-card">
        <span class="rule-num">Rule ${idx+1}</span>
        <button type="button" class="btn btn-sm btn-outline-danger float-end remove-rule-btn" data-idx="${idx}">
            <i class="ri-delete-bin-line"></i>
        </button>

        <div class="row g-3 mt-1">
            <div class="col-md-5">
                <label class="form-label fw-semibold" style="font-size:13px">Label Text <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm rule-label-input"
                       data-idx="${idx}" value="${rule.label || ''}" placeholder="e.g. Borderline — check Maths">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" style="font-size:13px">Description <small class="text-muted">(optional)</small></label>
                <input type="text" class="form-control form-control-sm rule-desc-input"
                       data-idx="${idx}" value="${rule.description || ''}" placeholder="Admin-facing note">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:13px">Badge Colour</label>
                <select class="form-select form-select-sm rule-color-select" data-idx="${idx}">
                    ${colorOpts}
                </select>
            </div>
        </div>

        <div class="mt-3">
            <div style="font-size:13px;font-weight:600;color:#dc2626;margin-bottom:6px">
                <i class="ri-close-circle-line me-1"></i>
                Triggers when student FAILS these compulsory subject(s):
                <small class="text-muted fw-normal ms-1">(click to toggle)</small>
            </div>
            ${compulsorySubjectsForClass.length === 0
                ? '<div class="text-muted small">No compulsory subjects found for this class/term.</div>'
                : `<div>${failChips}</div>`}
        </div>

        <div class="mt-3">
            <div style="font-size:13px;font-weight:600;color:#16a34a;margin-bottom:6px">
                <i class="ri-checkbox-circle-line me-1"></i>
                AND student meets these other conditions:
                <small class="text-muted fw-normal ms-1">(all must be true)</small>
            </div>
            <div class="other-conds-list">${otherCondRows}</div>
            <button type="button" class="btn btn-sm btn-outline-success mt-1 add-other-cond-btn" data-rule-idx="${idx}">
                <i class="ri-add-line me-1"></i>Add Subject Condition
            </button>
        </div>
    </div>`;
}

/* ═══════════════════════════════════════════════════════════════════════════
   Cond-rule actions
═══════════════════════════════════════════════════════════════════════════ */
document.getElementById('addCondRuleBtn').addEventListener('click', () => {
    condRules.push({ label: '', color: 'warning', description: '', failed_subject_ids: [], other_subject_conditions: [] });
    rerenderAllCondRules();
});

function removeCondRule(idx) {
    condRules.splice(idx, 1);
    rerenderAllCondRules();
}

function toggleFailSubject(chip) {
    const ruleIdx   = parseInt(chip.dataset.ruleIdx);
    const subjectId = parseInt(chip.dataset.subjectId);
    const arr       = condRules[ruleIdx].failed_subject_ids;
    const pos       = arr.indexOf(subjectId);
    if (pos >= 0) arr.splice(pos, 1); else arr.push(subjectId);
    chip.classList.toggle('selected', arr.includes(subjectId));
}

function removeOtherCond(ruleIdx, condIdx) {
    condRules[ruleIdx].other_subject_conditions.splice(condIdx, 1);
    rerenderAllCondRules();
}

async function showOtherCondPicker(ruleIdx) {
    const alreadyAdded = (condRules[ruleIdx].other_subject_conditions || []).map(c => c.subject_id);
    const available    = allSubjectsForClass.filter(s => !alreadyAdded.includes(s.id));

    if (available.length === 0) {
        Swal.fire('No subjects available', 'All subjects for this class have been added.', 'info');
        return;
    }

    const subjectOptions = available.map(s => `<option value="${s.id}">${s.subject} (${s.subject_code || ''})</option>`).join('');
    const gradeOptions   = gradeScale.map(g => `<option value="${g}">${g}</option>`).join('');

    const { value: formValues } = await Swal.fire({
        title: 'Add Subject Condition',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <select id="swal-subject" class="form-select">
                        <option value="">-- Select Subject --</option>
                        ${subjectOptions}
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Student must have grade ≥</label>
                    <select id="swal-grade" class="form-select">
                        <option value="">Any pass grade</option>
                        ${gradeOptions}
                    </select>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Add Condition',
        preConfirm: () => {
            const subId = document.getElementById('swal-subject').value;
            if (!subId) { Swal.showValidationMessage('Please select a subject'); return false; }
            return { subject_id: parseInt(subId), min_grade: document.getElementById('swal-grade').value || null };
        }
    });

    if (formValues) {
        condRules[ruleIdx].other_subject_conditions.push(formValues);
        rerenderAllCondRules();
    }
}

/* ═══════════════════════════════════════════════════════════════════════════
   Form submit
═══════════════════════════════════════════════════════════════════════════ */
document.getElementById('settingForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    for (const [i, rule] of condRules.entries()) {
        if (!rule.label.trim()) {
            Swal.fire('Validation Error', `Rule ${i+1} must have a label.`, 'warning');
            return;
        }
    }

    document.getElementById('promotion_rules_input').value = JSON.stringify(condRules);

    const formData = new FormData(this);
    const id       = document.getElementById('setting_id').value;
    let url        = '{{ route("promotion.settings.store") }}';
    if (id) { url = `/promotion-settings/${id}`; formData.append('_method', 'PUT'); }

    Swal.fire({ title: 'Saving…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ icon:'success', title:'Saved!', text:data.message, timer:2000, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Failed.');
            Swal.fire('Error!', msg, 'error');
        }
    } catch(err) {
        Swal.fire('Error!', 'An error occurred.', 'error');
    }
});

/* ═══════════════════════════════════════════════════════════════════════════
   Edit setting
═══════════════════════════════════════════════════════════════════════════ */
document.querySelectorAll('.edit-setting').forEach(btn => {
    btn.addEventListener('click', async function() {
        const d = this.dataset;
        document.getElementById('setting_id').value               = d.id;
        document.getElementById('schoolclass_id').value           = d.schoolclass_id;
        document.getElementById('session_id').value               = d.session_id || '';
        document.getElementById('term_id').value                  = d.term_id    || '';
        document.getElementById('rule_type').value                = d.rule_type;
        document.getElementById('compulsory_fail_action').value   = d.compulsory_fail_action || 'repeat';
        document.getElementById('promotion_pass_average').value   = d.promotion_pass_average;
        document.getElementById('trial_pass_average').value       = d.trial_pass_average;
        document.getElementById('see_principal_average').value    = d.see_principal_average;
        document.getElementById('combined_logic').value           = d.combined_logic || 'and';
        document.getElementById('promoted_label').value           = d.promoted_label      || 'Promoted';
        document.getElementById('trial_label').value              = d.trial_label         || 'Promoted on Trial';
        document.getElementById('see_principal_label').value      = d.see_principal_label || 'Advised to See Principal';
        document.getElementById('repeat_label').value             = d.repeat_label        || 'Advice to Repeat';

        try { condRules = JSON.parse(d.promotion_rules || '[]'); } catch(e) { condRules = []; }

        toggleSections();
        await refreshSubjects();

        new bootstrap.Modal(document.getElementById('addSettingModal')).show();
    });
});

/* ═══════════════════════════════════════════════════════════════════════════
   Delete setting
═══════════════════════════════════════════════════════════════════════════ */
document.querySelectorAll('.delete-setting').forEach(btn => {
    btn.addEventListener('click', async function() {
        const result = await Swal.fire({
            title: 'Confirm Delete',
            text: `Delete promotion rules for ${this.dataset.name}?`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, Delete'
        });
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Deleting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const res  = await fetch(`/promotion-settings/${this.dataset.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon:'success', title:'Deleted!', text:data.message, timer:2000, showConfirmButton:false })
                    .then(() => location.reload());
            } else { Swal.fire('Error!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'An error occurred.', 'error'); }
    });
});

/* ═══════════════════════════════════════════════════════════════════════════
   Init
═══════════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    toggleSections();
    document.getElementById('addSettingModal').addEventListener('hidden.bs.modal', () => {
        document.getElementById('settingForm').reset();
        document.getElementById('setting_id').value = '';
        condRules                  = [];
        allSubjectsForClass        = [];
        compulsorySubjectsForClass = [];
        document.getElementById('addCondRuleBtn').disabled = true;
        rerenderAllCondRules();
        toggleSections();
    });
});
</script>
@endsection
