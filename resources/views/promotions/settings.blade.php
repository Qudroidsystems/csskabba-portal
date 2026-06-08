{{-- resources/views/promotions/settings.blade.php --}}
@extends('layouts.master')
@section('content')
<style>
:root {
    --ps-primary:#1e3a5f; --ps-accent:#2563eb; --ps-success:#16a34a;
    --ps-warning:#d97706; --ps-danger:#dc2626; --ps-info:#0891b2;
    --ps-muted:#6b7280; --ps-border:#e2e8f0; --ps-bg:#f8fafc;
    --ps-radius:12px; --ps-shadow:0 2px 8px rgba(0,0,0,.08);
}
.ps-hero { background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 60%,#4f46e5 100%);
    border-radius:var(--ps-radius); padding:28px 32px; margin-bottom:24px; position:relative; overflow:hidden; }
.ps-hero::before { content:''; position:absolute; top:-60px; right:-60px; width:220px; height:220px;
    background:rgba(255,255,255,.06); border-radius:50%; }
.ps-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.ps-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

.setting-card { background:#fff; border:1px solid var(--ps-border); border-radius:var(--ps-radius);
    padding:20px; margin-bottom:20px; transition:all .3s; height:100%; }
.setting-card:hover   { box-shadow:var(--ps-shadow); transform:translateY(-2px); }
.setting-card.has-rules  { border-left:4px solid var(--ps-success); }
.setting-card.inactive   { border-left:4px solid var(--ps-muted); opacity:.75; }

.active-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px;
    border-radius:20px; font-size:11px; font-weight:700; }
.active-badge.is-active   { background:#dcfce7; color:#166534; }
.active-badge.is-inactive { background:#f3f4f6; color:#6b7280; }

/* Modal */
.modal-content { border-radius:16px; overflow:hidden; }
.modal-header  { background:linear-gradient(135deg,#1e3a5f,#2563eb); padding:20px 28px; border-bottom:none; }
.modal-header .modal-title { color:#fff; font-weight:700; }
.modal-header .btn-close   { filter:invert(1); }
.modal-body   { padding:1.5rem; max-height:78vh; overflow-y:auto; }
.modal-footer { border-top:1px solid var(--ps-border); padding:1rem 1.5rem; }

.form-section { background:var(--ps-bg); border-radius:12px; padding:20px; margin-bottom:18px; }
.form-section-title { font-size:14px; font-weight:700; color:var(--ps-primary);
    margin-bottom:14px; padding-bottom:10px; border-bottom:2px solid var(--ps-border);
    display:flex; align-items:center; justify-content:space-between; }

.info-banner { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;
    padding:12px 16px; margin-bottom:14px; display:flex; gap:12px; }
.info-banner i { font-size:18px; color:#2563eb; flex-shrink:0; margin-top:2px; }
.info-banner .text { font-size:12px; color:#1e40af; line-height:1.5; }

/* Rule card */
.rule-card { background:#fff; border:2px solid var(--ps-border); border-radius:12px;
    margin-bottom:18px; overflow:hidden; transition:all .2s; }
.rule-card:hover { border-color:var(--ps-accent); box-shadow:0 4px 12px rgba(0,0,0,.1); }
.rule-card-header { background:linear-gradient(90deg,#f8fafc,#fff); border-bottom:1px solid var(--ps-border);
    padding:12px 18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.rule-num-badge { background:var(--ps-primary); color:#fff; font-size:11px; font-weight:700;
    padding:3px 12px; border-radius:20px; white-space:nowrap; }
.rule-name-input { flex:1; min-width:180px; font-size:13px; }
.rule-card-body  { padding:18px; }

/* Label pills */
.label-selector { display:flex; gap:8px; flex-wrap:wrap; }
.label-pill { display:inline-flex; align-items:center; gap:5px; padding:6px 14px;
    border-radius:30px; font-size:12px; font-weight:600; border:2px solid transparent;
    cursor:pointer; transition:all .2s; }
.label-pill:hover { transform:translateY(-1px); }
.label-pill.active { box-shadow:0 0 0 3px rgba(0,0,0,.12); transform:scale(1.03); }
.label-pill.lp-promoted  { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
.label-pill.lp-promoted.active   { background:#16a34a; color:#fff; }
.label-pill.lp-trial     { background:#fef9c3; color:#854d0e; border-color:#fde68a; }
.label-pill.lp-trial.active      { background:#ca8a04; color:#fff; }
.label-pill.lp-principal { background:#e0f2fe; color:#075985; border-color:#bae6fd; }
.label-pill.lp-principal.active  { background:#0284c7; color:#fff; }
.label-pill.lp-repeat    { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
.label-pill.lp-repeat.active     { background:#dc2626; color:#fff; }

/* Section tabs inside rule */
.rule-section { border:1px solid var(--ps-border); border-radius:10px; margin-bottom:14px; overflow:hidden; }
.rule-section-header { background:linear-gradient(90deg,#f1f5f9,#f8fafc); padding:10px 16px;
    font-size:13px; font-weight:700; color:var(--ps-primary);
    display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--ps-border); }
.rule-section-body { padding:14px; }

/* Compulsory subject rows */
.comp-subj-row { display:grid; grid-template-columns:1fr 130px; gap:10px; align-items:center;
    padding:8px 12px; border-bottom:1px solid #f1f5f9; }
.comp-subj-row:last-child { border-bottom:none; }
.comp-subj-row .subj-name { font-size:13px; font-weight:500; }
.comp-subj-row .subj-code { font-size:11px; color:var(--ps-muted); font-family:monospace; }
.comp-subj-row .default-badge { font-size:10px; color:var(--ps-warning); }

/* Grade condition rows */
.cond-row { display:flex; align-items:center; gap:8px; flex-wrap:wrap;
    padding:8px 12px; border-bottom:1px solid #f1f5f9; }
.cond-row:last-child { border-bottom:none; }
.grade-pill { display:inline-flex; align-items:center; justify-content:center;
    width:36px; height:36px; border-radius:8px; font-size:14px; font-weight:800; flex-shrink:0; }
.gp-A,.gp-A1 { background:#dcfce7; color:#166534; }
.gp-B,.gp-B2,.gp-B3 { background:#dbeafe; color:#1e40af; }
.gp-C,.gp-C4,.gp-C5,.gp-C6 { background:#fef9c3; color:#854d0e; }
.gp-D,.gp-D7 { background:#ffedd5; color:#9a3412; }
.gp-E,.gp-E8 { background:#f3e8ff; color:#6b21a8; }
.gp-F,.gp-F9 { background:#fee2e2; color:#991b1b; }
.cond-text { font-size:12px; color:var(--ps-muted); white-space:nowrap; }
.scope-tag { font-size:10px; font-weight:600; padding:2px 8px; border-radius:10px;
    background:#e0f2fe; color:#075985; }

/* Average box */
.avg-box { background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:10px; padding:14px; margin-top:12px; }

/* Grade select */
.grade-sel { border:1.5px solid var(--ps-border); border-radius:8px; padding:5px 8px;
    font-size:12px; font-weight:600; background:#fff; width:100%; }
.grade-sel:focus { border-color:var(--ps-accent); outline:none; box-shadow:0 0 0 2px rgba(37,99,235,.1); }

.loading-spinner { display:inline-block; width:14px; height:14px; border:2px solid #e2e8f0;
    border-radius:50%; border-top-color:#2563eb; animation:spin .6s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

.no-rules-ph { text-align:center; padding:36px 20px; color:var(--ps-muted);
    background:var(--ps-bg); border-radius:12px; border:2px dashed var(--ps-border); }

.chip { display:inline-flex; align-items:center; gap:3px; padding:2px 8px;
    border-radius:12px; font-size:10px; font-weight:600; }
.chip-blue { background:#dbeafe; color:#1e40af; }
.chip-green { background:#dcfce7; color:#166534; }
.chip-amber { background:#fef9c3; color:#854d0e; }
.chip-red   { background:#fee2e2; color:#991b1b; }

.template-badge { background:#ede9fe; color:#5b21b6; border-radius:8px;
    padding:2px 10px; font-size:10px; font-weight:700; }
</style>

<div class="main-content"><div class="page-content"><div class="container-fluid">

<div class="ps-hero">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="ri-settings-4-line me-2"></i>Promotion Settings</h1>
            <p>Define flexible grade-count based promotion rules per class. Rules are evaluated by priority — first match wins.</p>
        </div>
        <a href="{{ route('promotion.templates.index') }}" class="btn btn-light btn-sm">
            <i class="ri-file-copy-line me-1"></i>Rule Templates
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap"
         style="padding:16px 20px;background:#fff;border-bottom:2px solid var(--ps-border);">
        <h5 class="mb-0 fw-semibold" style="color:var(--ps-primary);">
            <i class="ri-list-check me-2"></i>Promotion Rules
            <span class="badge bg-primary ms-2">{{ $settings->count() }}</span>
            <span class="badge bg-success ms-1">{{ $settings->where('is_active',true)->count() }} Active</span>
            <span class="badge bg-secondary ms-1">{{ $settings->where('is_active',false)->count() }} Inactive</span>
        </h5>
        <button type="button" class="btn btn-primary" id="openAddBtn">
            <i class="ri-add-line me-1"></i>Add New Setting
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse ($settings as $setting)
            @php
                $armData  = $setting->schoolclass?->arm ? DB::table('schoolarm')->where('id',$setting->schoolclass->arm)->first() : null;
                $armName  = $armData?->arm ?? '';
                $fullName = trim($setting->schoolclass?->schoolclass . ' ' . $armName);
                $isActive = (bool)$setting->is_active;
                $rules    = $setting->promotion_rules ?? [];
                $logicLabel = match($setting->rule_logic ?? 'grade_count') {
                    'average_only' => '📈 Average Only',
                    'both'         => '🎯 Grades + Average',
                    default        => '📊 Grade Count',
                };
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="setting-card {{ !empty($rules) ? 'has-rules' : '' }} {{ !$isActive ? 'inactive' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-active-switch" type="checkbox" role="switch"
                                   id="sw{{ $setting->id }}" data-id="{{ $setting->id }}" {{ $isActive ? 'checked' : '' }}>
                            <label class="form-check-label" for="sw{{ $setting->id }}">
                                <span class="active-badge {{ $isActive ? 'is-active' : 'is-inactive' }}" id="ab{{ $setting->id }}">
                                    <i class="{{ $isActive ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}"></i>
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </div>
                        @if($setting->template)
                            <span class="template-badge"><i class="ri-file-copy-line me-1"></i>{{ $setting->template->name }}</span>
                        @endif
                    </div>

                    <h6 class="fw-bold mb-0">{{ $fullName }}</h6>
                    <small class="text-muted">
                        {{ $setting->session?->session ?? 'All Sessions' }} &mdash; {{ $setting->term?->term ?? 'All Terms' }}
                    </small>
                    <div class="mt-1 mb-3">
                        <span class="badge bg-info">{{ $logicLabel }}</span>
                        @if($setting->promotion_pass_average)
                            <span class="badge bg-secondary ms-1">≥{{ $setting->promotion_pass_average }}% avg</span>
                        @endif
                    </div>

                    @if(!empty($rules))
                    <div style="max-height:200px;overflow-y:auto;">
                        @foreach($rules as $i => $rule)
                        @php
                            $stCls = match($rule['status_label'] ?? 'repeat') {
                                'promoted'=>'success','trial'=>'warning','see_principal'=>'info',default=>'danger'
                            };
                            $compSubjCount = count($rule['compulsory_section']['subjects'] ?? []);
                            $compCondCount = count($rule['compulsory_section']['count_conditions'] ?? []);
                            $otherCondCount= count($rule['other_section']['count_conditions'] ?? []);
                        @endphp
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-semibold small">
                                    <span class="badge bg-light text-dark me-1" style="font-size:10px;">{{ $i+1 }}</span>
                                    {{ $rule['rule_name'] ?? 'Unnamed' }}
                                </span>
                                <span class="badge bg-{{ $stCls }} px-2" style="font-size:10px;">
                                    {{ ucfirst(str_replace('_',' ',$rule['status_label'] ?? '')) }}
                                </span>
                            </div>
                            <div class="mt-1" style="font-size:10px;">
                                @if($compSubjCount) <span class="chip chip-blue me-1">{{ $compSubjCount }} comp subj</span> @endif
                                @if($compCondCount) <span class="chip chip-green me-1">{{ $compCondCount }} comp cond</span> @endif
                                @if($otherCondCount)<span class="chip chip-amber me-1">{{ $otherCondCount }} other cond</span>@endif
                                @if(!empty($rule['average_condition']['enabled']))
                                    <span class="chip chip-red">Avg ≥{{ $rule['average_condition']['min_average'] }}%</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-warning py-2 px-3 mb-0 small"><i class="ri-alert-line me-1"></i>No rules yet.</div>
                    @endif

                    <div class="border-top pt-3 mt-3">
                        <div class="row g-1" style="font-size:11px;">
                            <div class="col-6"><span class="text-muted">Promoted:</span> {{ $setting->promoted_label }}</div>
                            <div class="col-6"><span class="text-muted">Trial:</span> {{ $setting->trial_label }}</div>
                            <div class="col-6"><span class="text-muted">Principal:</span> {{ $setting->see_principal_label }}</div>
                            <div class="col-6"><span class="text-muted">Repeat:</span> {{ $setting->repeat_label }}</div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary edit-setting flex-fill"
                            data-id="{{ $setting->id }}"
                            data-schoolclass_id="{{ $setting->schoolclass_id }}"
                            data-session_id="{{ $setting->session_id ?? '' }}"
                            data-term_id="{{ $setting->term_id ?? '' }}"
                            data-promoted_label="{{ $setting->promoted_label }}"
                            data-trial_label="{{ $setting->trial_label }}"
                            data-see_principal_label="{{ $setting->see_principal_label }}"
                            data-repeat_label="{{ $setting->repeat_label }}"
                            data-rule_logic="{{ $setting->rule_logic ?? 'grade_count' }}"
                            data-promotion_pass_average="{{ $setting->promotion_pass_average ?? '' }}"
                            data-is_active="{{ $isActive ? '1' : '0' }}"
                            data-template_id="{{ $setting->template_id ?? '' }}"
                            data-promotion_rules="{{ json_encode($rules) }}">
                            <i class="ri-pencil-line"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-setting"
                            data-id="{{ $setting->id }}" data-name="{{ $fullName }}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="ri-settings-4-line" style="font-size:48px;opacity:.3;"></i>
                <p class="mt-3 text-muted">No promotion rules configured yet.</p>
                <button class="btn btn-primary" id="openAddBtn2">Create first rule</button>
            </div>
            @endforelse
        </div>
    </div>
</div>

</div></div></div>

{{-- ===================================================================
     MODAL
     =================================================================== --}}
<div class="modal fade" id="settingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-settings-4-line me-2"></i>Promotion Rule Settings</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="settingForm" hidden>
        @csrf
        <input type="hidden" name="id"              id="setting_id">
        <input type="hidden" name="promotion_rules" id="promotion_rules_input">
        <input type="hidden" name="template_id"     id="template_id_input">
      </form>

      <div class="modal-body">

        {{-- Class & Scope --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-book-2-line me-2"></i>Class &amp; Scope</span></div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
              <select class="form-select" id="schoolclass_id" required>
                <option value="">-- Select Class --</option>
                @foreach ($schoolclasses as $class)
                <option value="{{ $class->id }}">{{ trim($class->schoolclass . ' ' . ($class->arm_name ?? '')) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Session <small class="text-muted">(optional)</small></label>
              <select class="form-select" id="session_id">
                <option value="">-- All Sessions --</option>
                @foreach ($sessions as $s)
                <option value="{{ $s->id }}">{{ $s->session }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Term <small class="text-muted">(optional)</small></label>
              <select class="form-select" id="term_id">
                <option value="">-- All Terms --</option>
                @foreach ($terms as $t)
                <option value="{{ $t->id }}">{{ $t->term }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div id="subjectLoadStatus" class="mt-3" style="display:none;">
            <div class="d-flex align-items-center gap-2 text-muted">
              <div class="loading-spinner"></div><small>Loading class info...</small>
            </div>
          </div>
          <div id="subjectSummary" class="mt-2" style="display:none;"></div>
        </div>

        {{-- Template loader --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-file-copy-line me-2"></i>Load from Template <small class="text-muted fw-normal">(optional)</small></span></div>
          <div class="d-flex gap-2 align-items-end flex-wrap">
            <div style="flex:1;min-width:200px;">
              <label class="form-label fw-semibold small mb-1">Select Template</label>
              <select class="form-select" id="templateSelect">
                <option value="">-- None --</option>
                @foreach($templates as $tpl)
                <option value="{{ $tpl->id }}" data-scale="{{ $tpl->grade_scale }}">
                  {{ $tpl->name }} ({{ $tpl->grade_scale }})
                </option>
                @endforeach
              </select>
            </div>
            <button type="button" class="btn btn-outline-primary" id="loadTemplateBtn" disabled>
              <i class="ri-download-line me-1"></i>Load Template
            </button>
            <div id="templateStatus" class="small text-muted align-self-center"></div>
          </div>
        </div>

        {{-- Rule Status --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-toggle-line me-2"></i>Rule Status</span></div>
          <div class="d-flex align-items-center gap-3">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="modal_is_active" checked>
              <label class="form-check-label fw-semibold" for="modal_is_active">Active</label>
            </div>
            <span id="modalActiveBadge" class="active-badge is-active">
              <i class="ri-checkbox-circle-line"></i> Active
            </span>
          </div>
        </div>

        {{-- Evaluation Mode --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-git-branch-line me-2"></i>Evaluation Mode</span></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mode</label>
              <select class="form-select" id="rule_logic">
                <option value="grade_count">📊 Grade Count Rules Only</option>
                <option value="average_only">📈 Minimum Average Only</option>
                <option value="both">🎯 Grade Count AND/OR Average</option>
              </select>
            </div>
            <div class="col-md-6" id="globalAvgSection" style="display:none;">
              <label class="form-label fw-semibold">Global Minimum Average (%)</label>
              <div class="d-flex gap-2 align-items-center">
                <input type="range" class="form-range flex-fill" id="avg_slider" min="0" max="100" step="1" value="50">
                <input type="number" class="form-control" id="promotion_pass_average" style="width:80px;" min="0" max="100">
              </div>
            </div>
          </div>
        </div>

        {{-- Rules --}}
        <div class="form-section">
          <div class="form-section-title">
            <span><i class="ri-price-tag-3-line me-2"></i>Promotion Rules
              <small class="text-muted fw-normal ms-2" id="ruleScopeInfo"></small>
            </span>
            <button type="button" class="btn btn-sm btn-primary" id="addRuleBtn" disabled>
              <i class="ri-add-line me-1"></i>Add Rule
            </button>
          </div>
          <div class="info-banner">
            <i class="ri-lightbulb-line"></i>
            <div class="text">
              <strong>Rule priority order</strong>
              Rules are checked top-to-bottom by priority number. The <strong>first rule where all conditions pass</strong>
              determines the student's outcome. If no rule matches → Advice to Repeat.
              Each rule has three sections: <strong>Compulsory subjects</strong> (per-subject min grade + count conditions),
              <strong>Other subjects</strong> (grade count conditions), and an optional <strong>Average</strong> threshold.
            </div>
          </div>
          <div id="rulesContainer">
            <div class="no-rules-ph" id="noRulesMsg">
              <i class="ri-clipboard-line d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
              Select a class, then click <strong>Add Rule</strong>.
            </div>
          </div>
        </div>

        {{-- Labels --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-price-tag-line me-2"></i>Status Labels</span></div>
          <div class="row g-3">
            <div class="col-md-3"><label class="form-label fw-semibold small">Promoted</label>
              <input type="text" class="form-control form-control-sm" id="promoted_label" value="Promoted"></div>
            <div class="col-md-3"><label class="form-label fw-semibold small">Trial</label>
              <input type="text" class="form-control form-control-sm" id="trial_label" value="Promoted on Trial"></div>
            <div class="col-md-3"><label class="form-label fw-semibold small">See Principal</label>
              <input type="text" class="form-control form-control-sm" id="see_principal_label" value="Advised to See Principal"></div>
            <div class="col-md-3"><label class="form-label fw-semibold small">Repeat</label>
              <input type="text" class="form-control form-control-sm" id="repeat_label" value="Advice to Repeat"></div>
          </div>
        </div>

      </div>{{-- /modal-body --}}

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveSettingBtn">
          <i class="ri-save-line me-1"></i>Save Settings
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── State ──────────────────────────────────────────────────────────────────────
let promotionRules    = [];
let gradeScale        = ['A1','B2','B3','C4','C5','C6','D7','E8','F9'];
let isSenior          = true;
let totalSubjects     = 0;
let compulsoryCount   = 0;
let otherCount        = 0;
let classPassAvg      = null;
let compulsorySubjects= []; // [{subject_id, subject_name, subject_code, default_min_grade}]

const GROUPED_SENIOR  = {A:['A1'],B:['B2','B3'],C:['C4','C5','C6'],D:['D7'],E:['E8'],F:['F9']};
const GROUPED_JUNIOR  = {A:['A'],B:['B'],C:['C'],D:['D'],F:['F']};
const STATUS_LABELS   = [
    {key:'promoted',      label:'Promoted',               cls:'lp-promoted',  icon:'ri-checkbox-circle-line'},
    {key:'trial',         label:'Promoted on Trial',      cls:'lp-trial',     icon:'ri-time-line'},
    {key:'see_principal', label:'Advised to See Principal',cls:'lp-principal', icon:'ri-user-star-line'},
    {key:'repeat',        label:'Advice to Repeat',       cls:'lp-repeat',    icon:'ri-repeat-line'},
];

function getGroupedGrades() { return isSenior ? Object.keys(GROUPED_SENIOR) : Object.keys(GROUPED_JUNIOR); }
function getAllGrades()       { return gradeScale; }

// ── Open / close modal ─────────────────────────────────────────────────────────
function openModal() { new bootstrap.Modal(document.getElementById('settingModal')).show(); }
document.getElementById('openAddBtn')?.addEventListener('click',  openModal);
document.getElementById('openAddBtn2')?.addEventListener('click', openModal);
document.getElementById('settingModal').addEventListener('hidden.bs.modal', resetModal);

// ── Active badge ───────────────────────────────────────────────────────────────
document.getElementById('modal_is_active').addEventListener('change', function () {
    const b = document.getElementById('modalActiveBadge');
    b.className = this.checked ? 'active-badge is-active' : 'active-badge is-inactive';
    b.innerHTML = this.checked
        ? '<i class="ri-checkbox-circle-line"></i> Active'
        : '<i class="ri-close-circle-line"></i> Inactive';
});

// ── Eval mode toggle ───────────────────────────────────────────────────────────
document.getElementById('rule_logic').addEventListener('change', function () {
    document.getElementById('globalAvgSection').style.display =
        ['average_only','both'].includes(this.value) ? 'block' : 'none';
});
document.getElementById('avg_slider').addEventListener('input', e =>
    document.getElementById('promotion_pass_average').value = e.target.value);
document.getElementById('promotion_pass_average').addEventListener('input', e =>
    document.getElementById('avg_slider').value = e.target.value);

// ── Card-level toggle ──────────────────────────────────────────────────────────
document.addEventListener('change', async function (e) {
    if (!e.target.classList.contains('toggle-active-switch')) return;
    const toggle = e.target, sid = toggle.dataset.id, isActive = toggle.checked;
    const badge  = document.getElementById('ab' + sid);
    const card   = toggle.closest('.setting-card');
    try {
        const res  = await fetch(`/promotion-settings/${sid}/toggle-active`, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                     'Accept':'application/json','Content-Type':'application/json'},
            body: JSON.stringify({is_active:isActive})
        });
        const data = await res.json();
        if (data.success) {
            badge.className = isActive ? 'active-badge is-active' : 'active-badge is-inactive';
            badge.innerHTML = isActive ? '<i class="ri-checkbox-circle-line"></i> Active'
                                       : '<i class="ri-close-circle-line"></i> Inactive';
            card?.classList.toggle('inactive', !isActive);
        } else { toggle.checked = !isActive; Swal.fire('Error', data.message, 'error'); }
    } catch { toggle.checked = !isActive; Swal.fire('Error','Network error.','error'); }
});

// ── Template loader ────────────────────────────────────────────────────────────
document.getElementById('templateSelect').addEventListener('change', function () {
    document.getElementById('loadTemplateBtn').disabled = !this.value;
    document.getElementById('template_id_input').value = this.value;
});

document.getElementById('loadTemplateBtn').addEventListener('click', async function () {
    const tplId   = document.getElementById('templateSelect').value;
    const classId = document.getElementById('schoolclass_id').value;
    if (!tplId)   { Swal.fire('','Select a template first.','info'); return; }
    if (!classId) { Swal.fire('','Select a class first.','info'); return; }
    const termId    = document.getElementById('term_id').value;
    const sessionId = document.getElementById('session_id').value;
    const status    = document.getElementById('templateStatus');
    status.textContent = 'Loading…';
    try {
        let url = `/promotion-templates/${tplId}/load-for-class?classid=${classId}`;
        if (termId)    url += `&termid=${termId}`;
        if (sessionId) url += `&sessionid=${sessionId}`;
        const res  = await fetch(url);
        const data = await res.json();
        if (data.success) {
            promotionRules = data.merged_rules ?? [];
            status.textContent = `✓ ${data.template.name} loaded (${promotionRules.length} rules)`;
            status.style.color = '#16a34a';
            rerenderRules();
        } else {
            status.textContent = '✗ ' + data.message;
            status.style.color = '#dc2626';
        }
    } catch (err) {
        status.textContent = '✗ Error: ' + err.message;
        status.style.color = '#dc2626';
    }
});

// ── Load class info ────────────────────────────────────────────────────────────
async function refreshClassInfo() {
    const classId   = document.getElementById('schoolclass_id').value;
    const termId    = document.getElementById('term_id').value;
    const sessionId = document.getElementById('session_id').value;
    const addBtn    = document.getElementById('addRuleBtn');
    const loadEl    = document.getElementById('subjectLoadStatus');
    const summaryEl = document.getElementById('subjectSummary');
    const scopeInfo = document.getElementById('ruleScopeInfo');

    addBtn.disabled = true;
    summaryEl.style.display = 'none';
    if (!classId) { rerenderRules(); return; }

    loadEl.style.display = 'block';
    try {
        let url = `/promotion-settings/class-promotion-data?classid=${classId}`;
        if (termId)    url += `&termid=${termId}`;
        if (sessionId) url += `&sessionid=${sessionId}`;
        const res  = await fetch(url);
        const data = await res.json();
        loadEl.style.display = 'none';

        if (data.success) {
            isSenior          = data.is_senior;
            totalSubjects     = data.total_subjects   ?? 0;
            compulsoryCount   = data.compulsory_count ?? 0;
            otherCount        = data.other_count      ?? 0;
            classPassAvg      = data.pass_average;
            gradeScale        = data.grade_scale;
            compulsorySubjects= data.compulsory_subjects ?? [];

            if (classPassAvg) {
                document.getElementById('promotion_pass_average').value = classPassAvg;
                document.getElementById('avg_slider').value             = classPassAvg;
            }

            scopeInfo.textContent = `${totalSubjects} total | ${compulsoryCount} compulsory | ${otherCount} other`;
            summaryEl.innerHTML   = `
                <div class="alert alert-success py-2 mb-1">
                    <i class="ri-checkbox-circle-line me-1"></i>
                    <strong>${totalSubjects}</strong> total subjects &nbsp;|&nbsp;
                    <strong>${compulsoryCount}</strong> compulsory &nbsp;|&nbsp;
                    <strong>${otherCount}</strong> other &nbsp;|&nbsp;
                    Grade scale: <strong>${isSenior ? 'Senior (A1–F9)' : 'Junior (A–F)'}</strong>
                </div>`;
            summaryEl.style.display = 'block';
            addBtn.disabled = false;

            // Re-merge compulsory subjects into existing rules if editing
            if (promotionRules.length > 0) {
                promotionRules = promotionRules.map(rule => mergeCompulsorySubjectsIntoRule(rule));
            }
            rerenderRules();
        }
    } catch (err) {
        loadEl.style.display = 'none';
        summaryEl.innerHTML = `<div class="alert alert-danger py-2 mb-0">Error: ${err.message}</div>`;
        summaryEl.style.display = 'block';
    }
}

['schoolclass_id','session_id','term_id'].forEach(id =>
    document.getElementById(id)?.addEventListener('change', refreshClassInfo));

// ── Merge class compulsory subjects into a rule ────────────────────────────────
function mergeCompulsorySubjectsIntoRule(rule) {
    const existing = (rule.compulsory_section?.subjects ?? [])
        .reduce((m, s) => { m[s.subject_id] = s; return m; }, {});
    const merged = compulsorySubjects.map(cs => ({
        subject_id:         cs.subject_id,
        subject_name:       cs.subject_name,
        subject_code:       cs.subject_code,
        default_min_grade:  cs.default_min_grade,
        min_grade:          existing[cs.subject_id]?.min_grade ?? cs.default_min_grade ?? '',
        override:           !!existing[cs.subject_id]?.min_grade,
    }));
    return { ...rule, compulsory_section: { ...(rule.compulsory_section ?? {}), subjects: merged } };
}

// ── Add rule ───────────────────────────────────────────────────────────────────
document.getElementById('addRuleBtn').addEventListener('click', () => {
    const newRule = {
        rule_name:      '',
        status_label:   'promoted',
        priority:       promotionRules.length + 1,
        grade_grouping: 'grouped',
        compulsory_section: {
            subjects:         compulsorySubjects.map(cs => ({
                subject_id:        cs.subject_id,
                subject_name:      cs.subject_name,
                subject_code:      cs.subject_code,
                default_min_grade: cs.default_min_grade,
                min_grade:         cs.default_min_grade ?? '',
                override:          false,
            })),
            count_conditions: [],
        },
        other_section: { count_conditions: [] },
        average_condition: { enabled: false, min_average: classPassAvg ?? 50, logic: 'AND' },
    };
    promotionRules.push(newRule);
    rerenderRules();
    setTimeout(() => {
        const cards = document.querySelectorAll('.rule-card');
        cards[cards.length-1]?.scrollIntoView({behavior:'smooth',block:'nearest'});
    }, 100);
});

// ── Render all rules ───────────────────────────────────────────────────────────
function rerenderRules() {
    const container = document.getElementById('rulesContainer');
    const noMsg     = document.getElementById('noRulesMsg');
    if (!promotionRules.length) {
        container.innerHTML = '';
        container.appendChild(noMsg);
        noMsg.style.display = 'block';
        return;
    }
    noMsg.style.display = 'none';
    container.innerHTML  = '';
    promotionRules.forEach((rule, idx) => {
        const div = document.createElement('div');
        div.className = 'rule-card';
        div.innerHTML = buildRuleHTML(rule, idx);
        container.appendChild(div);
    });
    attachListeners(container);
}

// ── Build rule HTML ────────────────────────────────────────────────────────────
function buildRuleHTML(rule, idx) {
    const selSt     = STATUS_LABELS.find(s => s.key === rule.status_label) || STATUS_LABELS[0];
    const grouping  = rule.grade_grouping ?? 'grouped';
    const availGrades = grouping === 'grouped' ? getGroupedGrades() : getAllGrades();

    const labelPills = STATUS_LABELS.map(sl => {
        const active = rule.status_label === sl.key ? 'active' : '';
        return `<span class="label-pill ${sl.cls} ${active}" data-idx="${idx}" data-status="${sl.key}">
                    <i class="${sl.icon} me-1"></i>${sl.label}</span>`;
    }).join('');

    const groupingOpts = [
        ['grouped', isSenior ? 'Grouped (A=A1, B=B2+B3…)' : 'Grouped (A, B, C…)'],
        ['exact',   isSenior ? 'Exact (A1, B2, B3 separately)' : 'Exact (A, B, C separately)'],
    ].map(([v,l]) => `<option value="${v}" ${grouping===v?'selected':''}>${l}</option>`).join('');

    // Compulsory subjects per-subject min grade rows
    const compSubjRows = (rule.compulsory_section?.subjects ?? []).map((subj, si) => {
        const gradeOpts = ['', ...gradeScale].map(g =>
            `<option value="${g}" ${subj.min_grade === g ? 'selected' : ''}>
                ${g === '' ? '— Any (pass/fail default)' : g}
             </option>`).join('');
        const defTag = subj.default_min_grade
            ? `<span class="default-badge">default: ${subj.default_min_grade}</span>` : '';
        return `<div class="comp-subj-row">
            <div>
                <div class="subj-name">${escH(subj.subject_name)}
                    ${subj.subject_code ? `<span class="subj-code">(${escH(subj.subject_code)})</span>` : ''}
                </div>
                ${defTag}
            </div>
            <select class="grade-sel comp-subj-grade-sel" data-idx="${idx}" data-si="${si}">${gradeOpts}</select>
        </div>`;
    }).join('') || '<div class="text-muted small p-3"><i class="ri-info-line me-1"></i>No compulsory subjects for this class.</div>';

    // Compulsory count conditions
    const compCondRows = buildCondRows(rule.compulsory_section?.count_conditions ?? [], idx, 'comp', availGrades);
    const gradeAddOptsComp = availGrades.map(g => `<option>${g}</option>`).join('');

    // Other count conditions
    const otherCondRows = buildCondRows(rule.other_section?.count_conditions ?? [], idx, 'other', availGrades);
    const gradeAddOptsOther = availGrades.map(g => `<option>${g}</option>`).join('');

    // Average section
    const avg     = rule.average_condition ?? {enabled:false, min_average: classPassAvg ?? 50, logic:'AND'};
    const avgCheck = avg.enabled ? 'checked' : '';

    return `
    <div class="rule-card-header">
        <span class="rule-num-badge">Rule ${idx+1}</span>
        <span class="badge bg-${selSt.key==='promoted'?'success':selSt.key==='trial'?'warning':selSt.key==='see_principal'?'info':'danger'}">
            <i class="${selSt.icon} me-1"></i>${selSt.label}
        </span>
        <input type="text" class="form-control form-control-sm rule-name-input" data-idx="${idx}"
               value="${escH(rule.rule_name)}" placeholder="Rule name (e.g. Top Performer)">
        <div class="d-flex gap-1 align-items-center ms-auto">
            <span class="text-muted small me-1">Priority:</span>
            <input type="number" class="form-control form-control-sm priority-input" data-idx="${idx}"
                   value="${rule.priority ?? idx+1}" min="1" style="width:60px;">
            <button class="btn btn-sm btn-outline-secondary move-up-btn"   data-idx="${idx}"><i class="ri-arrow-up-line"></i></button>
            <button class="btn btn-sm btn-outline-secondary move-down-btn" data-idx="${idx}"><i class="ri-arrow-down-line"></i></button>
            <button class="btn btn-sm btn-outline-danger   remove-rule-btn" data-idx="${idx}"><i class="ri-delete-bin-line"></i></button>
        </div>
    </div>
    <div class="rule-card-body">

        <div class="mb-3">
            <label class="fw-semibold small d-block mb-2"><i class="ri-award-line me-1"></i>Promotion Outcome</label>
            <div class="label-selector">${labelPills}</div>
        </div>

        <div class="mb-3">
            <label class="fw-semibold small"><i class="ri-git-branch-line me-1"></i>Grade Grouping</label>
            <select class="form-select form-select-sm grouping-sel mt-1" data-idx="${idx}" style="max-width:300px;">${groupingOpts}</select>
        </div>

        {{-- Section 1: Compulsory --}}
        <div class="rule-section mb-3">
            <div class="rule-section-header">
                <span><i class="ri-star-fill text-warning me-2"></i>Section 1 — Compulsory Subjects
                    <small class="text-muted fw-normal ms-2">(${rule.compulsory_section?.subjects?.length ?? 0} subjects)</small>
                </span>
            </div>
            <div class="rule-section-body">
                <div class="mb-2">
                    <label class="form-label fw-semibold small mb-1">Per-subject minimum grade
                        <small class="text-muted fw-normal">(overrides default from Compulsory Subject setup)</small>
                    </label>
                    <div id="compSubjRows_${idx}">${compSubjRows}</div>
                </div>
                <hr class="my-2">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold small mb-0">
                            <i class="ri-bar-chart-line me-1"></i>Grade Count Conditions across Compulsory Subjects
                        </label>
                        <div class="d-flex gap-1">
                            <select class="form-select form-select-sm" id="addCompGrade_${idx}" style="width:80px;">${gradeAddOptsComp}</select>
                            <button class="btn btn-xs btn-outline-primary add-comp-cond-btn" data-idx="${idx}"
                                    style="padding:3px 10px;font-size:12px;">
                                <i class="ri-add-line"></i> Add
                            </button>
                        </div>
                    </div>
                    <div id="compCondRows_${idx}">${compCondRows || '<div class="text-muted small ps-1">No conditions — add above.</div>'}</div>
                </div>
            </div>
        </div>

        {{-- Section 2: Other subjects --}}
        <div class="rule-section mb-3">
            <div class="rule-section-header">
                <span><i class="ri-book-open-line text-primary me-2"></i>Section 2 — Other / All Subjects Grade Count
                    <small class="text-muted fw-normal ms-2">(${otherCount} other subjects)</small>
                </span>
            </div>
            <div class="rule-section-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label fw-semibold small mb-0">Grade Count Conditions</label>
                    <div class="d-flex gap-1">
                        <select class="form-select form-select-sm" id="addOtherGrade_${idx}" style="width:80px;">${gradeAddOptsOther}</select>
                        <button class="btn btn-xs btn-outline-primary add-other-cond-btn" data-idx="${idx}"
                                style="padding:3px 10px;font-size:12px;">
                            <i class="ri-add-line"></i> Add
                        </button>
                    </div>
                </div>
                <div id="otherCondRows_${idx}">${otherCondRows || '<div class="text-muted small ps-1">No conditions — add above.</div>'}</div>
            </div>
        </div>

        {{-- Section 3: Average --}}
        <div class="avg-box">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input avg-toggle-cb" type="checkbox" role="switch"
                           id="avgCb_${idx}" data-idx="${idx}" ${avgCheck}>
                    <label class="form-check-label fw-semibold small" for="avgCb_${idx}">
                        <i class="ri-percent-line text-info me-1"></i>Section 3 — Minimum Average Condition
                    </label>
                </div>
            </div>
            <div id="avgFields_${idx}" style="${avg.enabled ? '' : 'opacity:.4;pointer-events:none;'}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Min Average (%)</label>
                        <input type="number" class="form-control form-control-sm avg-min-inp" data-idx="${idx}"
                               min="0" max="100" step="0.5" value="${avg.min_average ?? (classPassAvg ?? 50)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Logic with Sections 1+2</label>
                        <select class="form-select form-select-sm avg-logic-sel" data-idx="${idx}">
                            <option value="AND" ${avg.logic==='AND'?'selected':''}>AND (all sections must pass)</option>
                            <option value="OR"  ${avg.logic==='OR'?'selected':''}>OR (average alone qualifies)</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <small class="text-muted">AND = strictest. OR = most flexible.</small>
                    </div>
                </div>
            </div>
        </div>

    </div>`;
}

// ── Build condition rows HTML ──────────────────────────────────────────────────
function buildCondRows(conds, ruleIdx, section, availGrades) {
    if (!conds.length) return '';
    const scopeOpts = [
        ['all',             '📚 All Subjects'],
        ['compulsory_only', '⭐ Compulsory Only'],
        ['other_only',      '📖 Other Only'],
    ];
    return conds.map((cond, ci) => {
        const ops = ['>=','<=','=','>','<'];
        const opOpts = ops.map(op => `<option value="${op}" ${cond.operator===op?'selected':''}>${op}</option>`).join('');
        const gradeOpts = availGrades.map(g => `<option value="${g}" ${cond.grade===g?'selected':''}>${g}</option>`).join('');
        const scopeSelOpts = scopeOpts.map(([v,l]) => `<option value="${v}" ${cond.scope===v?'selected':''}>${l}</option>`).join('');
        const pill = `gp-${cond.grade || 'F'}`;
        return `
        <div class="cond-row" data-rule="${ruleIdx}" data-sec="${section}" data-ci="${ci}">
            <span class="grade-pill ${pill}" id="gp_${ruleIdx}_${section}_${ci}">${escH(cond.grade||'')}</span>
            <select class="form-select form-select-sm cond-grade-sel" style="width:85px;"
                    data-rule="${ruleIdx}" data-sec="${section}" data-ci="${ci}">${gradeOpts}</select>
            <span class="cond-text">count</span>
            <select class="form-select form-select-sm cond-op-sel" style="width:65px;"
                    data-rule="${ruleIdx}" data-sec="${section}" data-ci="${ci}">${opOpts}</select>
            <input type="number" class="form-control form-control-sm cond-count-inp" style="width:65px;"
                   min="0" max="99" value="${cond.count ?? 0}"
                   data-rule="${ruleIdx}" data-sec="${section}" data-ci="${ci}">
            <span class="cond-text">subj. — scope:</span>
            <select class="form-select form-select-sm cond-scope-sel" style="width:150px;"
                    data-rule="${ruleIdx}" data-sec="${section}" data-ci="${ci}">${scopeSelOpts}</select>
            <button class="btn btn-sm btn-outline-danger remove-cond-btn ms-auto"
                    data-rule="${ruleIdx}" data-sec="${section}" data-ci="${ci}">
                <i class="ri-close-line"></i>
            </button>
        </div>`;
    }).join('');
}

// ── Attach listeners ───────────────────────────────────────────────────────────
function attachListeners(container) {
    // Rule name
    container.querySelectorAll('.rule-name-input').forEach(el =>
        el.addEventListener('input', e => promotionRules[+e.target.dataset.idx].rule_name = e.target.value));

    // Priority
    container.querySelectorAll('.priority-input').forEach(el =>
        el.addEventListener('input', e => promotionRules[+e.target.dataset.idx].priority = +e.target.value));

    // Status pills
    container.querySelectorAll('.label-pill').forEach(pill =>
        pill.addEventListener('click', () => {
            const idx = +pill.dataset.idx, stat = pill.dataset.status;
            promotionRules[idx].status_label = stat;
            pill.closest('.label-selector').querySelectorAll('.label-pill').forEach(p =>
                p.classList.toggle('active', p.dataset.status === stat));
        }));

    // Grade grouping — reset conditions when changed
    container.querySelectorAll('.grouping-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const idx = +e.target.dataset.idx;
            promotionRules[idx].grade_grouping = e.target.value;
            promotionRules[idx].compulsory_section.count_conditions = [];
            promotionRules[idx].other_section.count_conditions      = [];
            rerenderRules();
        }));

    // Compulsory per-subject min grade
    container.querySelectorAll('.comp-subj-grade-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const idx = +e.target.dataset.idx, si = +e.target.dataset.si;
            promotionRules[idx].compulsory_section.subjects[si].min_grade = e.target.value;
            promotionRules[idx].compulsory_section.subjects[si].override  = !!e.target.value;
        }));

    // Add compulsory count condition
    container.querySelectorAll('.add-comp-cond-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx   = +btn.dataset.idx;
            const grade = document.getElementById(`addCompGrade_${idx}`)?.value;
            if (!grade) return;
            if (!promotionRules[idx].compulsory_section.count_conditions)
                promotionRules[idx].compulsory_section.count_conditions = [];
            promotionRules[idx].compulsory_section.count_conditions.push(
                {grade, operator:'>=', count:1, scope:'compulsory_only'});
            rerenderRules();
        }));

    // Add other count condition
    container.querySelectorAll('.add-other-cond-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx   = +btn.dataset.idx;
            const grade = document.getElementById(`addOtherGrade_${idx}`)?.value;
            if (!grade) return;
            if (!promotionRules[idx].other_section.count_conditions)
                promotionRules[idx].other_section.count_conditions = [];
            promotionRules[idx].other_section.count_conditions.push(
                {grade, operator:'>=', count:1, scope:'other_only'});
            rerenderRules();
        }));

    // Condition grade / operator / count / scope
    container.querySelectorAll('.cond-grade-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const {rule:ri, sec, ci} = e.target.dataset;
            const conds = sec === 'comp'
                ? promotionRules[+ri].compulsory_section.count_conditions
                : promotionRules[+ri].other_section.count_conditions;
            conds[+ci].grade = e.target.value;
            const pill = document.getElementById(`gp_${ri}_${sec}_${ci}`);
            if (pill) { pill.textContent = e.target.value; pill.className = `grade-pill gp-${e.target.value}`; }
        }));
    container.querySelectorAll('.cond-op-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const {rule:ri, sec, ci} = e.target.dataset;
            const conds = sec === 'comp'
                ? promotionRules[+ri].compulsory_section.count_conditions
                : promotionRules[+ri].other_section.count_conditions;
            conds[+ci].operator = e.target.value;
        }));
    container.querySelectorAll('.cond-count-inp').forEach(inp =>
        inp.addEventListener('input', e => {
            const {rule:ri, sec, ci} = e.target.dataset;
            const conds = sec === 'comp'
                ? promotionRules[+ri].compulsory_section.count_conditions
                : promotionRules[+ri].other_section.count_conditions;
            conds[+ci].count = +e.target.value;
        }));
    container.querySelectorAll('.cond-scope-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const {rule:ri, sec, ci} = e.target.dataset;
            const conds = sec === 'comp'
                ? promotionRules[+ri].compulsory_section.count_conditions
                : promotionRules[+ri].other_section.count_conditions;
            conds[+ci].scope = e.target.value;
        }));

    // Remove condition
    container.querySelectorAll('.remove-cond-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const {rule:ri, sec, ci} = btn.dataset;
            if (sec === 'comp')
                promotionRules[+ri].compulsory_section.count_conditions.splice(+ci, 1);
            else
                promotionRules[+ri].other_section.count_conditions.splice(+ci, 1);
            rerenderRules();
        }));

    // Average toggle
    container.querySelectorAll('.avg-toggle-cb').forEach(cb =>
        cb.addEventListener('change', e => {
            const idx = +e.target.dataset.idx;
            if (!promotionRules[idx].average_condition) promotionRules[idx].average_condition = {};
            promotionRules[idx].average_condition.enabled = e.target.checked;
            const f = document.getElementById(`avgFields_${idx}`);
            if (f) { f.style.opacity = e.target.checked ? '1' : '0.4';
                     f.style.pointerEvents = e.target.checked ? 'auto' : 'none'; }
        }));
    container.querySelectorAll('.avg-min-inp').forEach(inp =>
        inp.addEventListener('input', e => {
            const idx = +e.target.dataset.idx;
            if (!promotionRules[idx].average_condition) promotionRules[idx].average_condition = {};
            promotionRules[idx].average_condition.min_average = +e.target.value;
        }));
    container.querySelectorAll('.avg-logic-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const idx = +e.target.dataset.idx;
            if (!promotionRules[idx].average_condition) promotionRules[idx].average_condition = {};
            promotionRules[idx].average_condition.logic = e.target.value;
        }));

    // Move up / down
    container.querySelectorAll('.move-up-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx > 0) { [promotionRules[idx-1], promotionRules[idx]] = [promotionRules[idx], promotionRules[idx-1]]; rerenderRules(); }
        }));
    container.querySelectorAll('.move-down-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx < promotionRules.length-1) { [promotionRules[idx], promotionRules[idx+1]] = [promotionRules[idx+1], promotionRules[idx]]; rerenderRules(); }
        }));
    container.querySelectorAll('.remove-rule-btn').forEach(btn =>
        btn.addEventListener('click', () => { promotionRules.splice(+btn.dataset.idx, 1); rerenderRules(); }));
}

// ── Save ───────────────────────────────────────────────────────────────────────
document.getElementById('saveSettingBtn').addEventListener('click', async function () {
    const classId = document.getElementById('schoolclass_id').value;
    if (!classId) { Swal.fire('Validation','Please select a class.','warning'); return; }

    for (const [i, rule] of promotionRules.entries()) {
        if (!rule.rule_name?.trim()) {
            Swal.fire('Validation', `Rule ${i+1} needs a name.`, 'warning'); return;
        }
        const hasCompSubjGrades = (rule.compulsory_section?.subjects ?? []).some(s => s.min_grade);
        const hasCompConds  = (rule.compulsory_section?.count_conditions ?? []).length > 0;
        const hasOtherConds = (rule.other_section?.count_conditions ?? []).length > 0;
        const hasAvg        = rule.average_condition?.enabled;
        if (!hasCompSubjGrades && !hasCompConds && !hasOtherConds && !hasAvg) {
            Swal.fire('Validation', `Rule ${i+1} has no conditions set. Add at least one.`, 'warning'); return;
        }
    }

    document.getElementById('promotion_rules_input').value = JSON.stringify(promotionRules);

    const fd = new FormData(document.getElementById('settingForm'));
    fd.set('schoolclass_id',       classId);
    fd.set('session_id',           document.getElementById('session_id').value || '');
    fd.set('term_id',              document.getElementById('term_id').value    || '');
    fd.set('promoted_label',       document.getElementById('promoted_label').value);
    fd.set('trial_label',          document.getElementById('trial_label').value);
    fd.set('see_principal_label',  document.getElementById('see_principal_label').value);
    fd.set('repeat_label',         document.getElementById('repeat_label').value);
    fd.set('rule_logic',           document.getElementById('rule_logic').value || 'grade_count');
    fd.set('promotion_pass_average', document.getElementById('promotion_pass_average').value || '');
    fd.set('is_active',            document.getElementById('modal_is_active').checked ? '1' : '0');
    fd.set('template_id',          document.getElementById('template_id_input').value || '');

    const id = document.getElementById('setting_id').value;
    let url  = '/promotion-settings';
    if (id) { url = `/promotion-settings/${id}`; fd.append('_method','PUT'); }

    Swal.fire({title:'Saving…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    try {
        const res  = await fetch(url, {method:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                     'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: fd});
        const data = await res.json();
        if (data.success) {
            Swal.fire({icon:'success',title:'Saved!',text:data.message,timer:1500,showConfirmButton:false})
                .then(() => location.reload());
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Failed.');
            Swal.fire('Error', msg, 'error');
        }
    } catch { Swal.fire('Error','An error occurred.','error'); }
});

// ── Edit ───────────────────────────────────────────────────────────────────────
function bindEditButtons() {
    document.querySelectorAll('.edit-setting').forEach(btn => {
        btn.removeEventListener('click', handleEditClick);
        btn.addEventListener('click', handleEditClick);
    });
}
async function handleEditClick(e) {
    const d = e.currentTarget.dataset;
    resetModal();
    document.getElementById('setting_id').value             = d.id;
    document.getElementById('schoolclass_id').value         = d.schoolclass_id;
    document.getElementById('session_id').value             = d.session_id    || '';
    document.getElementById('term_id').value                = d.term_id       || '';
    document.getElementById('promoted_label').value         = d.promoted_label;
    document.getElementById('trial_label').value            = d.trial_label;
    document.getElementById('see_principal_label').value    = d.see_principal_label;
    document.getElementById('repeat_label').value           = d.repeat_label;
    document.getElementById('rule_logic').value             = d.rule_logic    || 'grade_count';
    document.getElementById('promotion_pass_average').value = d.promotion_pass_average || '';
    document.getElementById('avg_slider').value             = d.promotion_pass_average || 50;
    document.getElementById('template_id_input').value      = d.template_id   || '';
    if (d.template_id) document.getElementById('templateSelect').value = d.template_id;

    const isActive = d.is_active === '1';
    document.getElementById('modal_is_active').checked = isActive;
    const badge = document.getElementById('modalActiveBadge');
    badge.className = isActive ? 'active-badge is-active' : 'active-badge is-inactive';
    badge.innerHTML = isActive ? '<i class="ri-checkbox-circle-line"></i> Active'
                               : '<i class="ri-close-circle-line"></i> Inactive';

    document.getElementById('rule_logic').dispatchEvent(new Event('change'));

    try { promotionRules = JSON.parse(d.promotion_rules || '[]'); }
    catch { promotionRules = []; }

    openModal();
    await refreshClassInfo();
}

// ── Delete ─────────────────────────────────────────────────────────────────────
function bindDeleteButtons() {
    document.querySelectorAll('.delete-setting').forEach(btn => {
        btn.removeEventListener('click', handleDeleteClick);
        btn.addEventListener('click', handleDeleteClick);
    });
}
async function handleDeleteClick(e) {
    const btn = e.currentTarget;
    const result = await Swal.fire({
        title:'Confirm Delete', icon:'warning',
        html:`Delete rules for <strong>${escH(btn.dataset.name)}</strong>?`,
        showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Yes, Delete',
    });
    if (!result.isConfirmed) return;
    Swal.fire({title:'Deleting…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    try {
        const res  = await fetch(`/promotion-settings/${btn.dataset.id}`, {
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                     'Accept':'application/json','Content-Type':'application/json'}});
        const data = await res.json();
        if (data.success) {
            Swal.fire({icon:'success',title:'Deleted!',text:data.message,timer:1500,showConfirmButton:false})
                .then(() => location.reload());
        } else Swal.fire('Error', data.message || 'Failed.', 'error');
    } catch { Swal.fire('Error','Network error.','error'); }
}

// ── Reset modal ────────────────────────────────────────────────────────────────
function resetModal() {
    ['setting_id','session_id','term_id','template_id_input','promotion_pass_average'].forEach(id =>
        document.getElementById(id) && (document.getElementById(id).value = ''));
    document.getElementById('schoolclass_id').value      = '';
    document.getElementById('promoted_label').value      = 'Promoted';
    document.getElementById('trial_label').value         = 'Promoted on Trial';
    document.getElementById('see_principal_label').value = 'Advised to See Principal';
    document.getElementById('repeat_label').value        = 'Advice to Repeat';
    document.getElementById('rule_logic').value          = 'grade_count';
    document.getElementById('avg_slider').value          = 50;
    document.getElementById('modal_is_active').checked   = true;
    document.getElementById('globalAvgSection').style.display = 'none';
    document.getElementById('subjectSummary').style.display   = 'none';
    document.getElementById('addRuleBtn').disabled            = true;
    document.getElementById('templateSelect').value           = '';
    document.getElementById('loadTemplateBtn').disabled       = true;
    document.getElementById('templateStatus').textContent     = '';
    document.getElementById('ruleScopeInfo').textContent      = '';
    const badge = document.getElementById('modalActiveBadge');
    badge.className = 'active-badge is-active';
    badge.innerHTML = '<i class="ri-checkbox-circle-line"></i> Active';
    promotionRules = []; gradeScale = ['A1','B2','B3','C4','C5','C6','D7','E8','F9'];
    isSenior = true; totalSubjects = 0; compulsoryCount = 0; otherCount = 0;
    classPassAvg = null; compulsorySubjects = [];
    rerenderRules();
}

function escH(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('DOMContentLoaded', () => { bindEditButtons(); bindDeleteButtons(); });
</script>
@endsection
