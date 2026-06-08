{{-- resources/views/promotions/settings.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --ps-primary: #1e3a5f;
    --ps-accent:  #2563eb;
    --ps-success: #16a34a;
    --ps-warning: #d97706;
    --ps-danger:  #dc2626;
    --ps-info:    #0891b2;
    --ps-muted:   #6b7280;
    --ps-border:  #e2e8f0;
    --ps-bg:      #f8fafc;
    --ps-radius:  12px;
    --ps-shadow:  0 2px 8px rgba(0,0,0,.08);
}

.ps-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--ps-radius); padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.ps-hero::before {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px; background: rgba(255,255,255,.06); border-radius: 50%;
}
.ps-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.ps-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

.setting-card {
    background: #fff; border: 1px solid var(--ps-border);
    border-radius: var(--ps-radius); padding: 20px; margin-bottom: 20px;
    transition: all .3s; height: 100%;
}
.setting-card:hover    { box-shadow: var(--ps-shadow); transform: translateY(-2px); }
.setting-card.has-rules { border-left: 4px solid var(--ps-success); }
.setting-card.inactive  { border-left: 4px solid var(--ps-muted); opacity: .75; }

.toggle-active { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.toggle-active .form-check-input { width: 2.5em; height: 1.3em; cursor: pointer; }
.active-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
}
.active-badge.is-active   { background: #dcfce7; color: #166534; }
.active-badge.is-inactive { background: #f3f4f6; color: #6b7280; }

/* ── Modal ── */
.modal-content { border-radius: 16px; overflow: hidden; }
.modal-header  {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px; border-bottom: none;
}
.modal-header .modal-title { color: #fff; font-weight: 700; }
.modal-header .btn-close   { filter: invert(1); }
.modal-body   { padding: 1.5rem; max-height: 75vh; overflow-y: auto; }
.modal-footer { border-top: 1px solid var(--ps-border); padding: 1rem 1.5rem; }

.form-section { background: var(--ps-bg); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
.form-section-title {
    font-size: 15px; font-weight: 700; color: var(--ps-primary);
    margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--ps-border);
    display: flex; align-items: center; justify-content: space-between;
}

.info-banner {
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;
}
.info-banner i     { font-size: 20px; color: #2563eb; margin-top: 2px; }
.info-banner .text { font-size: 13px; color: #1e40af; line-height: 1.5; }
.info-banner .text strong { display: block; margin-bottom: 4px; }

/* ── Rule card ── */
.rule-card {
    background: #fff; border: 2px solid var(--ps-border);
    border-radius: 12px; margin-bottom: 20px; overflow: hidden; transition: all .2s;
}
.rule-card:hover { border-color: var(--ps-accent); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.rule-card-header {
    background: linear-gradient(90deg, #f8fafc, #fff);
    border-bottom: 1px solid var(--ps-border);
    padding: 14px 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.rule-num-badge {
    background: var(--ps-primary); color: #fff;
    font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px;
    white-space: nowrap;
}
.rule-name-input { flex: 1; min-width: 200px; font-size: 14px; }
.rule-card-body  { padding: 20px; }

/* ── Status label pills ── */
.label-selector { display: flex; gap: 8px; flex-wrap: wrap; }
.label-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 30px; font-size: 12px; font-weight: 600;
    border: 2px solid transparent; cursor: pointer; transition: all .2s;
}
.label-pill:hover  { transform: translateY(-1px); }
.label-pill.active { box-shadow: 0 0 0 3px rgba(0,0,0,.15); transform: scale(1.02); }
.label-pill.lp-promoted  { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.label-pill.lp-promoted.active  { background: #16a34a; color: #fff; }
.label-pill.lp-trial     { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
.label-pill.lp-trial.active     { background: #ca8a04; color: #fff; }
.label-pill.lp-principal { background: #e0f2fe; color: #075985; border-color: #bae6fd; }
.label-pill.lp-principal.active { background: #0284c7; color: #fff; }
.label-pill.lp-repeat    { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.label-pill.lp-repeat.active    { background: #dc2626; color: #fff; }

/* ── Options row ── */
.options-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;
}
@media(max-width:600px){ .options-row { grid-template-columns: 1fr; } }
.option-box {
    background: var(--ps-bg); border: 2px solid var(--ps-border);
    border-radius: 10px; padding: 14px;
}
.option-box label { font-size: 12px; font-weight: 700; color: var(--ps-primary);
                    display: block; margin-bottom: 8px; }
.option-box select { font-size: 13px; }

/* ── Grade condition rows ── */
.grade-conditions { display: flex; flex-direction: column; gap: 10px; }
.grade-condition-row {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    background: #fff; border: 1px solid var(--ps-border);
    border-radius: 10px; padding: 10px 14px;
}
.grade-condition-row .grade-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; border-radius: 10px; font-size: 16px;
    font-weight: 800; flex-shrink: 0;
}
.grade-badge-A { background: #dcfce7; color: #166534; }
.grade-badge-B { background: #dbeafe; color: #1e40af; }
.grade-badge-C { background: #fef9c3; color: #854d0e; }
.grade-badge-D { background: #ffedd5; color: #9a3412; }
.grade-badge-E { background: #f3e8ff; color: #6b21a8; }
.grade-badge-F { background: #fee2e2; color: #991b1b; }
.grade-badge-A1 { background: #dcfce7; color: #166534; }
.grade-badge-B2,.grade-badge-B3 { background: #dbeafe; color: #1e40af; }
.grade-badge-C4,.grade-badge-C5,.grade-badge-C6 { background: #fef9c3; color: #854d0e; }
.grade-badge-D7 { background: #ffedd5; color: #9a3412; }
.grade-badge-E8 { background: #f3e8ff; color: #6b21a8; }
.grade-badge-F9 { background: #fee2e2; color: #991b1b; }

.grade-condition-row select, .grade-condition-row input[type=number] {
    border: 1.5px solid var(--ps-border); border-radius: 8px;
    padding: 6px 10px; font-size: 13px; font-weight: 600;
}
.grade-condition-row select:focus, .grade-condition-row input:focus {
    border-color: var(--ps-accent); outline: none;
    box-shadow: 0 0 0 2px rgba(37,99,235,.1);
}
.grade-condition-row .op-select { width: 70px; }
.grade-condition-row .count-input { width: 70px; }
.cond-text { font-size: 13px; color: var(--ps-muted); white-space: nowrap; }

/* ── Average condition ── */
.avg-condition-box {
    background: #f0f9ff; border: 2px solid #bae6fd;
    border-radius: 10px; padding: 16px; margin-top: 16px;
}
.avg-condition-box.disabled { opacity: .5; pointer-events: none; }
.avg-toggle { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.avg-toggle label { font-weight: 700; font-size: 13px; color: var(--ps-primary); margin: 0; cursor: pointer; }

.no-rules-placeholder {
    text-align: center; padding: 40px 20px; color: var(--ps-muted);
    background: var(--ps-bg); border-radius: 12px; border: 2px dashed var(--ps-border);
}
.loading-spinner {
    display: inline-block; width: 16px; height: 16px;
    border: 2px solid #e2e8f0; border-radius: 50%;
    border-top-color: #2563eb; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Subject count chips ── */
.chip { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
        border-radius: 20px; font-size: 11px; font-weight: 600; }
.chip-blue   { background: #dbeafe; color: #1e40af; }
.chip-green  { background: #dcfce7; color: #166534; }
.chip-orange { background: #ffedd5; color: #9a3412; }
</style>

<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="ps-hero">
        <h1><i class="ri-settings-4-line me-2"></i>Promotion Settings</h1>
        <p>Define grade-count based promotion rules per class. Rules are evaluated top-to-bottom — the first match wins.</p>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap"
             style="padding:16px 20px;background:#fff;border-bottom:2px solid var(--ps-border);">
          <h5 class="mb-0 fw-semibold" style="color:var(--ps-primary);">
            <i class="ri-list-check me-2"></i>Promotion Rules
            <span class="badge bg-primary ms-2">{{ $settings->count() }}</span>
            <span class="badge bg-success ms-1">{{ $settings->where('is_active', true)->count() }} Active</span>
            <span class="badge bg-secondary ms-1">{{ $settings->where('is_active', false)->count() }} Inactive</span>
          </h5>
          <button type="button" class="btn btn-primary" id="openAddBtn">
            <i class="ri-add-line me-1"></i>Add New Rule
          </button>
        </div>

        <div class="card-body">
          <div class="row">
            @forelse ($settings as $setting)
            @php
                $armDisplay = '';
                if ($setting->schoolclass && $setting->schoolclass->arm) {
                    $armData = DB::table('schoolarm')->where('id', $setting->schoolclass->arm)->first();
                    $armDisplay = $armData ? $armData->arm : '';
                }
                $fullClassName = trim($setting->schoolclass->schoolclass . ' ' . $armDisplay);
                $isActive = (bool) $setting->is_active;
                $ruleLogicLabel = match($setting->rule_logic ?? 'grade_count') {
                    'average_only' => '📊 Average Only',
                    'both'         => '🎯 Grades + Average',
                    default        => '📊 Grade Count',
                };
            @endphp
            <div class="col-md-6 col-lg-4">
              <div class="setting-card {{ !empty($setting->promotion_rules) ? 'has-rules' : '' }} {{ !$isActive ? 'inactive' : '' }}">

                <div class="toggle-active">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input toggle-active-switch" type="checkbox" role="switch"
                           id="activeSwitch{{ $setting->id }}" data-id="{{ $setting->id }}"
                           {{ $isActive ? 'checked' : '' }}>
                    <label class="form-check-label" for="activeSwitch{{ $setting->id }}">
                      <span class="active-badge {{ $isActive ? 'is-active' : 'is-inactive' }}" id="activeBadge{{ $setting->id }}">
                        <i class="{{ $isActive ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}"></i>
                        {{ $isActive ? 'Active' : 'Inactive' }}
                      </span>
                    </label>
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h6 class="mb-0 fw-bold">{{ $fullClassName }}</h6>
                    <small class="text-muted">
                      {{ $setting->session?->session ?? 'All Sessions' }}
                      &mdash; {{ $setting->term?->term ?? 'All Terms' }}
                    </small>
                    <div class="mt-1">
                      <span class="badge bg-info">{{ $ruleLogicLabel }}</span>
                      @if($setting->promotion_pass_average)
                        <span class="badge bg-secondary ms-1">≥{{ $setting->promotion_pass_average }}% avg</span>
                      @endif
                    </div>
                  </div>
                </div>

                @if(!empty($setting->promotion_rules) && is_array($setting->promotion_rules))
                <div class="mt-2">
                  <div class="small text-muted mb-2">
                    <i class="ri-price-tag-3-line me-1"></i>{{ count($setting->promotion_rules) }} rule(s)
                  </div>
                  <div style="max-height:180px;overflow-y:auto;">
                    @foreach($setting->promotion_rules as $rule)
                    @php
                      $stMap = [
                        'promoted'=>['bg'=>'success','icon'=>'ri-checkbox-circle-line'],
                        'trial'=>['bg'=>'warning','icon'=>'ri-time-line'],
                        'see_principal'=>['bg'=>'info','icon'=>'ri-user-star-line'],
                        'repeat'=>['bg'=>'danger','icon'=>'ri-repeat-line'],
                      ];
                      $st = $stMap[$rule['status_label'] ?? 'repeat'] ?? $stMap['repeat'];
                      $scopeLabel = match($rule['subject_scope'] ?? 'all') {
                        'compulsory_only' => 'Compulsory',
                        'other_only'      => 'Other',
                        default           => 'All subjects',
                      };
                    @endphp
                    <div class="border-bottom pb-2 mb-2">
                      <div class="d-flex justify-content-between align-items-start">
                        <span class="fw-semibold small">{{ $rule['rule_name'] ?? 'Unnamed' }}</span>
                        <span class="badge bg-{{ $st['bg'] }} px-2">
                          <i class="{{ $st['icon'] }} me-1"></i>{{ ucfirst(str_replace('_',' ',$rule['status_label'] ?? '')) }}
                        </span>
                      </div>
                      <div class="text-muted" style="font-size:11px;margin-top:4px;">
                        <span class="chip chip-blue me-1">{{ $scopeLabel }}</span>
                        @foreach($rule['grade_conditions'] ?? [] as $cond)
                          <span class="chip chip-green me-1">
                            {{ $cond['operator'] }}{{ $cond['count'] }} {{ $cond['grade'] }}
                          </span>
                        @endforeach
                        @if(!empty($rule['average_condition']['enabled']))
                          <span class="chip chip-orange">Avg {{ $rule['average_condition']['logic'] }}: ≥{{ $rule['average_condition']['min_average'] }}%</span>
                        @endif
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
                @else
                <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                  <i class="ri-alert-line me-1"></i>No rules defined yet.
                </div>
                @endif

                <div class="border-top pt-3 mt-3">
                  <div class="row g-1 small">
                    <div class="col-6"><span class="text-muted">Promoted:</span> {{ $setting->promoted_label }}</div>
                    <div class="col-6"><span class="text-muted">Trial:</span> {{ $setting->trial_label }}</div>
                    <div class="col-6"><span class="text-muted">Principal:</span> {{ $setting->see_principal_label }}</div>
                    <div class="col-6"><span class="text-muted">Repeat:</span> {{ $setting->repeat_label }}</div>
                  </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-primary edit-setting flex-fill"
                    data-id="{{ $setting->id }}"
                    data-schoolclass_id="{{ $setting->schoolclass_id }}"
                    data-session_id="{{ $setting->session_id }}"
                    data-term_id="{{ $setting->term_id }}"
                    data-promoted_label="{{ $setting->promoted_label }}"
                    data-trial_label="{{ $setting->trial_label }}"
                    data-see_principal_label="{{ $setting->see_principal_label }}"
                    data-repeat_label="{{ $setting->repeat_label }}"
                    data-rule_logic="{{ $setting->rule_logic ?? 'grade_count' }}"
                    data-promotion_pass_average="{{ $setting->promotion_pass_average ?? '' }}"
                    data-is_active="{{ $isActive ? '1' : '0' }}"
                    data-promotion_rules="{{ json_encode($setting->promotion_rules ?? []) }}">
                    <i class="ri-pencil-line"></i> Edit
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-danger delete-setting"
                    data-id="{{ $setting->id }}" data-name="{{ $fullClassName }}">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>

              </div>
            </div>
            @empty
            <div class="col-12">
              <div class="text-center py-5">
                <i class="ri-settings-4-line" style="font-size:48px;opacity:.3;"></i>
                <p class="mt-3 text-muted">No promotion rules configured yet.</p>
                <button class="btn btn-primary" id="openAddBtn2">Create your first rule</button>
              </div>
            </div>
            @endforelse
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- ================================================================
     MODAL
     ================================================================ --}}
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

        {{-- Rule Status --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-toggle-line me-2"></i>Rule Status</span></div>
          <div class="d-flex align-items-center gap-3">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="modal_is_active" checked>
              <label class="form-check-label fw-semibold" for="modal_is_active">
                Active (applied automatically during evaluation)
              </label>
            </div>
            <span id="modalActiveBadge" class="active-badge is-active">
              <i class="ri-checkbox-circle-line"></i> Active
            </span>
          </div>
          <small class="text-muted mt-2 d-block">
            Inactive rules are saved but <strong>not</strong> applied when evaluating student promotions.
          </small>
        </div>

        {{-- Evaluation Logic --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-git-branch-line me-2"></i>Evaluation Mode</span></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">How are rules evaluated?</label>
              <select class="form-select" id="rule_logic">
                <option value="grade_count">📊 Grade Count Rules Only</option>
                <option value="average_only">📈 Minimum Average Score Only</option>
                <option value="both">🎯 Grade Count Rules AND/OR Average</option>
              </select>
              <small class="text-muted">Choose what drives the promotion decision</small>
            </div>
            <div class="col-md-6" id="globalAverageSection" style="display:none;">
              <label class="form-label fw-semibold">
                <i class="ri-percent-line me-1"></i>Global Minimum Average (%)
              </label>
              <div class="d-flex gap-2 align-items-center">
                <input type="range" class="form-range flex-fill" id="avg_score_slider" min="0" max="100" step="1" value="50">
                <input type="number" class="form-control" id="promotion_pass_average"
                       style="width:80px;" min="0" max="100" step="0.01" placeholder="50">
              </div>
              <small class="text-muted" id="avgHelpText">Student must achieve at least this average to be promoted</small>
            </div>
          </div>
        </div>

        {{-- Promotion Rules --}}
        <div class="form-section">
          <div class="form-section-title">
            <span><i class="ri-price-tag-3-line me-2"></i>Promotion Rules</span>
            <button type="button" class="btn btn-sm btn-primary" id="addRuleBtn" disabled>
              <i class="ri-add-line me-1"></i>Add Rule
            </button>
          </div>

          <div class="info-banner">
            <i class="ri-lightbulb-line"></i>
            <div class="text">
              <strong>How grade-count rules work</strong>
              Each rule specifies how many of each grade a student must have across a chosen scope
              (all subjects, compulsory only, or other subjects only). Rules are checked
              top-to-bottom — the <strong>first rule where all conditions are met</strong> determines
              the student's promotion status. If no rule matches, the student is advised to repeat.
              <br><small class="text-warning mt-1">⭐ Tip: Put your best-outcome rule first (e.g. "Promoted") and worst last (e.g. "Repeat").</small>
            </div>
          </div>

          <div id="rulesContainer">
            <div class="no-rules-placeholder" id="noRulesMsg">
              <i class="ri-clipboard-line d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
              Select a class above, then click <strong>Add Rule</strong>.
            </div>
          </div>
        </div>

        {{-- Status Labels --}}
        <div class="form-section">
          <div class="form-section-title"><span><i class="ri-price-tag-line me-2"></i>Promotion Status Labels</span></div>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Promoted</label>
              <input type="text" class="form-control" id="promoted_label" value="Promoted">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Trial</label>
              <input type="text" class="form-control" id="trial_label" value="Promoted on Trial">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">See Principal</label>
              <input type="text" class="form-control" id="see_principal_label" value="Advised to See Principal">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Repeat</label>
              <input type="text" class="form-control" id="repeat_label" value="Advice to Repeat">
            </div>
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
let promotionRules  = [];
let gradeScale      = ['A1','B2','B3','C4','C5','C6','D7','E8','F9'];
let isSenior        = true;
let totalSubjects   = 0;
let compulsoryCount = 0;
let classPassAvg    = null;

const STATUS_LABELS = [
    { key:'promoted',      label:'Promoted',               cls:'lp-promoted',  icon:'ri-checkbox-circle-line' },
    { key:'trial',         label:'Promoted on Trial',      cls:'lp-trial',     icon:'ri-time-line'            },
    { key:'see_principal', label:'Advised to See Principal',cls:'lp-principal', icon:'ri-user-star-line'       },
    { key:'repeat',        label:'Advice to Repeat',       cls:'lp-repeat',    icon:'ri-repeat-line'          },
];

// Grouped scale labels
const GROUPED_SENIOR = { A:['A1'], B:['B2','B3'], C:['C4','C5','C6'], D:['D7'], E:['E8'], F:['F9'] };
const GROUPED_JUNIOR = { A:['A'],  B:['B'],       C:['C'],            D:['D'],             F:['F']  };
function getGroupedScale() {
    return isSenior ? Object.keys(GROUPED_SENIOR) : Object.keys(GROUPED_JUNIOR);
}

// ── Open modal ─────────────────────────────────────────────────────────────────
function openModal() { new bootstrap.Modal(document.getElementById('settingModal')).show(); }
document.getElementById('openAddBtn')?.addEventListener('click',  openModal);
document.getElementById('openAddBtn2')?.addEventListener('click', openModal);

// ── Active badge sync ──────────────────────────────────────────────────────────
document.getElementById('modal_is_active').addEventListener('change', function () {
    const badge = document.getElementById('modalActiveBadge');
    badge.className = this.checked ? 'active-badge is-active' : 'active-badge is-inactive';
    badge.innerHTML = this.checked
        ? '<i class="ri-checkbox-circle-line"></i> Active'
        : '<i class="ri-close-circle-line"></i> Inactive';
});

// ── Evaluation mode toggle ─────────────────────────────────────────────────────
document.getElementById('rule_logic').addEventListener('change', function () {
    const show = ['average_only','both'].includes(this.value);
    document.getElementById('globalAverageSection').style.display = show ? 'block' : 'none';
});

// ── Slider sync ────────────────────────────────────────────────────────────────
document.getElementById('avg_score_slider').addEventListener('input', e => {
    document.getElementById('promotion_pass_average').value = e.target.value;
});
document.getElementById('promotion_pass_average').addEventListener('input', e => {
    document.getElementById('avg_score_slider').value = e.target.value;
});

// ── Card-level toggle (AJAX) ───────────────────────────────────────────────────
document.addEventListener('change', async function (e) {
    if (!e.target.classList.contains('toggle-active-switch')) return;
    const toggle    = e.target;
    const sid       = toggle.dataset.id;
    const isActive  = toggle.checked;
    const badge     = document.getElementById('activeBadge' + sid);
    const card      = toggle.closest('.setting-card');

    try {
        const res  = await fetch(`/promotion-settings/${sid}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
                'Content-Type':'application/json',
            },
            body: JSON.stringify({ is_active: isActive }),
        });
        const data = await res.json();
        if (data.success) {
            badge.className = isActive ? 'active-badge is-active' : 'active-badge is-inactive';
            badge.innerHTML = isActive
                ? '<i class="ri-checkbox-circle-line"></i> Active'
                : '<i class="ri-close-circle-line"></i> Inactive';
            card?.classList.toggle('inactive', !isActive);
            syncHeaderCounts();
        } else {
            toggle.checked = !isActive;
            Swal.fire('Error', data.message || 'Failed.', 'error');
        }
    } catch {
        toggle.checked = !isActive;
        Swal.fire('Error', 'Network error.', 'error');
    }
});

function syncHeaderCounts() {
    const all      = document.querySelectorAll('.toggle-active-switch');
    let active = 0, inactive = 0;
    all.forEach(t => t.checked ? active++ : inactive++);
    document.querySelectorAll('.card-header .badge').forEach(b => {
        if (b.classList.contains('bg-success') && b.textContent.includes('Active'))
            b.textContent = active + ' Active';
        if (b.classList.contains('bg-secondary') && b.textContent.includes('Inactive'))
            b.textContent = inactive + ' Inactive';
    });
}

// ── Load class info ────────────────────────────────────────────────────────────
async function refreshClassInfo() {
    const classId   = document.getElementById('schoolclass_id').value;
    const termId    = document.getElementById('term_id').value;
    const sessionId = document.getElementById('session_id').value;
    const addBtn    = document.getElementById('addRuleBtn');
    const loadEl    = document.getElementById('subjectLoadStatus');
    const summaryEl = document.getElementById('subjectSummary');

    addBtn.disabled = true;
    summaryEl.style.display = 'none';

    if (!classId) { rerenderRules(); return; }

    loadEl.style.display = 'block';
    try {
        let url = `/promotion-settings/class-promotion-data?classid=${classId}`;
        if (termId    && termId    !== '') url += `&termid=${termId}`;
        if (sessionId && sessionId !== '') url += `&sessionid=${sessionId}`;

        const res  = await fetch(url);
        const data = await res.json();
        loadEl.style.display = 'none';

        if (data.success) {
            isSenior        = data.is_senior;
            totalSubjects   = data.total_subjects   ?? 0;
            compulsoryCount = data.compulsory_count ?? 0;
            classPassAvg    = data.pass_average;

            gradeScale = data.grade_scale ?? (isSenior
                ? ['A1','B2','B3','C4','C5','C6','D7','E8','F9']
                : ['A','B','C','D','F']);

            if (classPassAvg) {
                document.getElementById('promotion_pass_average').value = classPassAvg;
                document.getElementById('avg_score_slider').value       = classPassAvg;
                document.getElementById('avgHelpText').textContent =
                    `Class default: ${classPassAvg}% minimum average required.`;
            }

            summaryEl.innerHTML = `
                <div class="alert alert-success py-2 mb-0">
                    <i class="ri-checkbox-circle-line me-1"></i>
                    <strong>${totalSubjects}</strong> total subjects &nbsp;|&nbsp;
                    <strong>${compulsoryCount}</strong> compulsory &nbsp;|&nbsp;
                    Grade scale: <strong>${isSenior ? 'Senior (A1–F9)' : 'Junior (A–F)'}</strong>
                </div>`;
            summaryEl.style.display = 'block';

            addBtn.disabled = false;
            promotionRules.forEach(() => {}); // just trigger re-render
            rerenderRules();
        } else {
            summaryEl.innerHTML = `<div class="alert alert-warning py-2 mb-0">${data.message}</div>`;
            summaryEl.style.display = 'block';
        }
    } catch (err) {
        loadEl.style.display = 'none';
        summaryEl.innerHTML = `<div class="alert alert-danger py-2 mb-0">Error: ${err.message}</div>`;
        summaryEl.style.display = 'block';
    }
}

['schoolclass_id','session_id','term_id'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', refreshClassInfo);
});

// ── Add rule ───────────────────────────────────────────────────────────────────
document.getElementById('addRuleBtn').addEventListener('click', () => {
    promotionRules.push({
        rule_name:        '',
        status_label:     'promoted',
        subject_scope:    'all',
        grade_grouping:   'grouped',
        grade_conditions: [],
        average_condition: { enabled: false, min_average: classPassAvg ?? 50, logic: 'AND' },
    });
    rerenderRules();
    // Scroll to new rule
    setTimeout(() => {
        const cards = document.querySelectorAll('.rule-card');
        cards[cards.length - 1]?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
    container.innerHTML = '';
    promotionRules.forEach((rule, idx) => {
        const div = document.createElement('div');
        div.className = 'rule-card';
        div.innerHTML = buildRuleHTML(rule, idx);
        container.appendChild(div);
    });
    attachListeners(container);
}

// ── Build rule card HTML ───────────────────────────────────────────────────────
function buildRuleHTML(rule, idx) {
    const selStatus = STATUS_LABELS.find(s => s.key === rule.status_label) || STATUS_LABELS[0];
    const labelPills = STATUS_LABELS.map(sl => {
        const active = rule.status_label === sl.key ? 'active' : '';
        return `<span class="label-pill ${sl.cls} ${active}" data-idx="${idx}" data-status="${sl.key}">
                    <i class="${sl.icon} me-1"></i>${sl.label}
                </span>`;
    }).join('');

    // Scope options
    const scopeOptions = [
        ['all',             '📚 All Subjects'],
        ['compulsory_only', '⭐ Compulsory Subjects Only'],
        ['other_only',      '📖 Other Subjects Only'],
    ].map(([v, l]) => `<option value="${v}" ${rule.subject_scope === v ? 'selected' : ''}>${l}</option>`).join('');

    // Grade grouping options
    const groupingOptions = [
        ['grouped', isSenior ? '📊 Grouped (A = A1, B = B2+B3 …)' : '📊 Grouped (A, B, C …)'],
        ['exact',   isSenior ? '🔬 Exact (A1, B2, B3 separately)' : '🔬 Exact (A, B, C separately)'],
    ].map(([v, l]) => `<option value="${v}" ${rule.grade_grouping === v ? 'selected' : ''}>${l}</option>`).join('');

    // Grade conditions
    const condRows = (rule.grade_conditions || []).map((cond, ci) =>
        buildCondRow(idx, ci, cond, rule.grade_grouping)
    ).join('');

    // Available grades for the add-condition dropdown
    const availableGrades = rule.grade_grouping === 'grouped'
        ? getGroupedScale()
        : gradeScale;
    const gradeOptions = availableGrades.map(g =>
        `<option value="${g}">${g}</option>`
    ).join('');

    // Average condition
    const avg = rule.average_condition ?? { enabled: false, min_average: classPassAvg ?? 50, logic: 'AND' };
    const avgEnabled = avg.enabled ? 'checked' : '';
    const avgDisabledClass = avg.enabled ? '' : 'disabled';
    const avgAndSelected = avg.logic === 'AND' ? 'selected' : '';
    const avgOrSelected  = avg.logic === 'OR'  ? 'selected' : '';

    return `
    <div class="rule-card-header">
        <span class="rule-num-badge">Rule ${idx + 1}</span>
        <span class="badge bg-${selStatus.key === 'promoted' ? 'success' : selStatus.key === 'trial' ? 'warning' : selStatus.key === 'see_principal' ? 'info' : 'danger'} rule-status-badge-${idx}">
            <i class="${selStatus.icon} me-1"></i>${selStatus.label}
        </span>
        <input type="text" class="form-control form-control-sm rule-name-input" data-idx="${idx}"
               value="${escHtml(rule.rule_name)}" placeholder="Rule name (e.g. Top Performer)">
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary move-up-btn"   data-idx="${idx}" title="Move Up"><i class="ri-arrow-up-line"></i></button>
            <button class="btn btn-sm btn-outline-secondary move-down-btn" data-idx="${idx}" title="Move Down"><i class="ri-arrow-down-line"></i></button>
            <button class="btn btn-sm btn-outline-danger remove-rule-btn"  data-idx="${idx}" title="Remove"><i class="ri-delete-bin-line"></i></button>
        </div>
    </div>
    <div class="rule-card-body">

        {{-- Status label --}}
        <div class="mb-4">
            <label class="fw-semibold mb-2 d-block"><i class="ri-award-line me-1"></i>Promotion Outcome</label>
            <div class="label-selector">${labelPills}</div>
        </div>

        {{-- Options row --}}
        <div class="options-row">
            <div class="option-box">
                <label><i class="ri-filter-line me-1"></i>Subject Scope</label>
                <select class="form-select form-select-sm scope-sel" data-idx="${idx}">${scopeOptions}</select>
                <div class="mt-2" style="font-size:11px;color:var(--ps-muted);">
                    ${compulsoryCount} compulsory &nbsp;|&nbsp; ${totalSubjects - compulsoryCount} other &nbsp;|&nbsp; ${totalSubjects} total
                </div>
            </div>
            <div class="option-box">
                <label><i class="ri-group-line me-1"></i>Grade Grouping</label>
                <select class="form-select form-select-sm grouping-sel" data-idx="${idx}">${groupingOptions}</select>
                <div class="mt-2" style="font-size:11px;color:var(--ps-muted);">
                    ${rule.grade_grouping === 'grouped'
                        ? (isSenior ? 'Groups: A·B·C·D·E·F' : 'Groups: A·B·C·D·F')
                        : 'Individual grade codes'}
                </div>
            </div>
        </div>

        {{-- Grade conditions --}}
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="fw-semibold"><i class="ri-bar-chart-line me-1"></i>Grade Count Conditions</label>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm add-grade-select" data-idx="${idx}" style="width:90px;">
                        ${gradeOptions}
                    </select>
                    <button class="btn btn-sm btn-outline-primary add-cond-btn" data-idx="${idx}">
                        <i class="ri-add-line me-1"></i>Add
                    </button>
                </div>
            </div>
            <div class="grade-conditions" id="condRows_${idx}">
                ${condRows || '<div class="text-muted small py-2 ps-1"><i class="ri-info-line me-1"></i>No conditions yet — click Add to set grade requirements.</div>'}
            </div>
        </div>

        {{-- Average condition --}}
        <div class="avg-condition-box" id="avgBox_${idx}">
            <div class="avg-toggle">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input avg-toggle-cb" type="checkbox" role="switch"
                           id="avgCb_${idx}" data-idx="${idx}" ${avgEnabled}>
                    <label class="form-check-label fw-semibold" for="avgCb_${idx}">
                        <i class="ri-percent-line me-1 text-info"></i>Add Minimum Average Condition
                    </label>
                </div>
            </div>
            <div id="avgFields_${idx}" class="${avgDisabledClass}" style="${avg.enabled ? '' : 'pointer-events:none;opacity:.5;'}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Minimum Average (%)</label>
                        <input type="number" class="form-control form-control-sm avg-min-input" data-idx="${idx}"
                               min="0" max="100" step="0.5" value="${avg.min_average ?? (classPassAvg ?? 50)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Logic with Grade Rules</label>
                        <select class="form-select form-select-sm avg-logic-sel" data-idx="${idx}">
                            <option value="AND" ${avgAndSelected}>AND (both must pass)</option>
                            <option value="OR"  ${avgOrSelected}>OR (either is enough)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mt-1">
                            <strong>AND</strong>: grades + average both required<br>
                            <strong>OR</strong>: passing either qualifies student
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>`;
}

function buildCondRow(ruleIdx, condIdx, cond, grouping) {
    const ops = ['>=','<=','=','>','<'];
    const opOpts = ops.map(op =>
        `<option value="${op}" ${cond.operator === op ? 'selected' : ''}>${op}</option>`
    ).join('');

    const available = grouping === 'grouped' ? getGroupedScale() : gradeScale;
    const gradeOpts = available.map(g =>
        `<option value="${g}" ${cond.grade === g ? 'selected' : ''}>${g}</option>`
    ).join('');

    const badgeCls = `grade-badge grade-badge-${cond.grade || 'F'}`;

    return `
    <div class="grade-condition-row" data-rule="${ruleIdx}" data-cond="${condIdx}">
        <span class="${badgeCls}" id="gradeBadge_${ruleIdx}_${condIdx}">${escHtml(cond.grade || '')}</span>
        <select class="form-select form-select-sm cond-grade-sel" data-rule="${ruleIdx}" data-cond="${condIdx}"
                style="width:90px;">${gradeOpts}</select>
        <span class="cond-text">count is</span>
        <select class="form-select form-select-sm op-select cond-op-sel" data-rule="${ruleIdx}" data-cond="${condIdx}">${opOpts}</select>
        <input type="number" class="form-control form-control-sm count-input cond-count-inp"
               data-rule="${ruleIdx}" data-cond="${condIdx}"
               min="0" max="50" value="${cond.count ?? 0}">
        <span class="cond-text">subject(s)</span>
        <button class="btn btn-sm btn-outline-danger ms-auto remove-cond-btn"
                data-rule="${ruleIdx}" data-cond="${condIdx}" title="Remove condition">
            <i class="ri-close-line"></i>
        </button>
    </div>`;
}

// ── Attach event listeners ─────────────────────────────────────────────────────
function attachListeners(container) {
    // Rule name
    container.querySelectorAll('.rule-name-input').forEach(el =>
        el.addEventListener('input', e => {
            promotionRules[+e.target.dataset.idx].rule_name = e.target.value;
        })
    );

    // Status label pills
    container.querySelectorAll('.label-pill').forEach(pill =>
        pill.addEventListener('click', () => {
            const idx  = +pill.dataset.idx;
            const stat = pill.dataset.status;
            promotionRules[idx].status_label = stat;
            // Update badge in header
            const selStatus = STATUS_LABELS.find(s => s.key === stat);
            const badge = container.querySelector(`.rule-status-badge-${idx}`);
            if (badge && selStatus) {
                badge.className = `badge bg-${stat === 'promoted' ? 'success' : stat === 'trial' ? 'warning' : stat === 'see_principal' ? 'info' : 'danger'} rule-status-badge-${idx}`;
                badge.innerHTML = `<i class="${selStatus.icon} me-1"></i>${selStatus.label}`;
            }
            pill.closest('.label-selector').querySelectorAll('.label-pill').forEach(p => {
                p.classList.toggle('active', p.dataset.status === stat);
            });
        })
    );

    // Scope
    container.querySelectorAll('.scope-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            promotionRules[+e.target.dataset.idx].subject_scope = e.target.value;
        })
    );

    // Grade grouping — re-render rule to update grade options
    container.querySelectorAll('.grouping-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const idx = +e.target.dataset.idx;
            promotionRules[idx].grade_grouping = e.target.value;
            // Reset conditions that use incompatible grades
            promotionRules[idx].grade_conditions = [];
            rerenderRules();
        })
    );

    // Add condition
    container.querySelectorAll('.add-cond-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx   = +btn.dataset.idx;
            const grade = container.querySelector(`.add-grade-select[data-idx="${idx}"]`)?.value;
            if (!grade) return;
            promotionRules[idx].grade_conditions.push({ grade, operator: '>=', count: 1 });
            rerenderRules();
        })
    );

    // Condition grade change
    container.querySelectorAll('.cond-grade-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const ri = +e.target.dataset.rule;
            const ci = +e.target.dataset.cond;
            promotionRules[ri].grade_conditions[ci].grade = e.target.value;
            // Update badge colour
            const badge = container.querySelector(`#gradeBadge_${ri}_${ci}`);
            if (badge) {
                badge.textContent = e.target.value;
                badge.className   = `grade-badge grade-badge-${e.target.value}`;
            }
        })
    );

    // Condition operator
    container.querySelectorAll('.cond-op-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            promotionRules[+e.target.dataset.rule].grade_conditions[+e.target.dataset.cond].operator = e.target.value;
        })
    );

    // Condition count
    container.querySelectorAll('.cond-count-inp').forEach(inp =>
        inp.addEventListener('input', e => {
            promotionRules[+e.target.dataset.rule].grade_conditions[+e.target.dataset.cond].count = +e.target.value;
        })
    );

    // Remove condition
    container.querySelectorAll('.remove-cond-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const ri = +btn.dataset.rule;
            const ci = +btn.dataset.cond;
            promotionRules[ri].grade_conditions.splice(ci, 1);
            rerenderRules();
        })
    );

    // Average toggle
    container.querySelectorAll('.avg-toggle-cb').forEach(cb =>
        cb.addEventListener('change', e => {
            const idx = +e.target.dataset.idx;
            promotionRules[idx].average_condition = promotionRules[idx].average_condition ?? {};
            promotionRules[idx].average_condition.enabled = e.target.checked;
            const fields = document.getElementById(`avgFields_${idx}`);
            if (fields) {
                fields.style.pointerEvents = e.target.checked ? 'auto' : 'none';
                fields.style.opacity       = e.target.checked ? '1' : '0.5';
            }
        })
    );

    // Average min input
    container.querySelectorAll('.avg-min-input').forEach(inp =>
        inp.addEventListener('input', e => {
            const idx = +e.target.dataset.idx;
            if (!promotionRules[idx].average_condition) promotionRules[idx].average_condition = {};
            promotionRules[idx].average_condition.min_average = +e.target.value;
        })
    );

    // Average logic
    container.querySelectorAll('.avg-logic-sel').forEach(sel =>
        sel.addEventListener('change', e => {
            const idx = +e.target.dataset.idx;
            if (!promotionRules[idx].average_condition) promotionRules[idx].average_condition = {};
            promotionRules[idx].average_condition.logic = e.target.value;
        })
    );

    // Move up / down
    container.querySelectorAll('.move-up-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx > 0) {
                [promotionRules[idx - 1], promotionRules[idx]] = [promotionRules[idx], promotionRules[idx - 1]];
                rerenderRules();
            }
        })
    );
    container.querySelectorAll('.move-down-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx < promotionRules.length - 1) {
                [promotionRules[idx], promotionRules[idx + 1]] = [promotionRules[idx + 1], promotionRules[idx]];
                rerenderRules();
            }
        })
    );

    // Remove rule
    container.querySelectorAll('.remove-rule-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            promotionRules.splice(+btn.dataset.idx, 1);
            rerenderRules();
        })
    );
}

// ── Save ───────────────────────────────────────────────────────────────────────
document.getElementById('saveSettingBtn').addEventListener('click', async function () {
    const classId = document.getElementById('schoolclass_id').value;
    if (!classId) { Swal.fire('Validation', 'Please select a class.', 'warning'); return; }

    for (const [i, rule] of promotionRules.entries()) {
        if (!rule.rule_name?.trim()) {
            Swal.fire('Validation', `Rule ${i + 1} needs a name.`, 'warning'); return;
        }
        if (!rule.grade_conditions?.length && rule.average_condition?.enabled !== true) {
            Swal.fire('Validation', `Rule ${i + 1} has no grade conditions and no average condition.`, 'warning'); return;
        }
    }

    document.getElementById('promotion_rules_input').value = JSON.stringify(promotionRules);

    const fd = new FormData(document.getElementById('settingForm'));
    fd.set('schoolclass_id',       classId);
    fd.set('session_id',           document.getElementById('session_id').value || '');
    fd.set('term_id',              document.getElementById('term_id').value    || '');
    fd.set('promoted_label',       document.getElementById('promoted_label').value       || 'Promoted');
    fd.set('trial_label',          document.getElementById('trial_label').value          || 'Promoted on Trial');
    fd.set('see_principal_label',  document.getElementById('see_principal_label').value  || 'Advised to See Principal');
    fd.set('repeat_label',         document.getElementById('repeat_label').value         || 'Advice to Repeat');
    fd.set('rule_logic',           document.getElementById('rule_logic').value           || 'grade_count');
    fd.set('promotion_pass_average', document.getElementById('promotion_pass_average').value || '');
    fd.set('is_active',            document.getElementById('modal_is_active').checked ? '1' : '0');

    const id  = document.getElementById('setting_id').value;
    let   url = '/promotion-settings';
    if (id) { url = `/promotion-settings/${id}`; fd.append('_method', 'PUT'); }

    Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Saved!', text: data.message,
                timer: 1500, showConfirmButton: false }).then(() => location.reload());
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Failed to save.');
            Swal.fire('Error', msg, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'An error occurred.', 'error');
    }
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
    document.getElementById('avg_score_slider').value       = d.promotion_pass_average || 50;

    const isActive = d.is_active === '1';
    document.getElementById('modal_is_active').checked = isActive;
    const badge = document.getElementById('modalActiveBadge');
    badge.className = isActive ? 'active-badge is-active' : 'active-badge is-inactive';
    badge.innerHTML = isActive
        ? '<i class="ri-checkbox-circle-line"></i> Active'
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
    const btn  = e.currentTarget;
    const sid  = btn.dataset.id;
    const name = btn.dataset.name;

    const result = await Swal.fire({
        title: 'Confirm Delete',
        html: `Delete promotion rules for <strong>${escHtml(name)}</strong>?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, Delete',
    });
    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const res  = await fetch(`/promotion-settings/${sid}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json', 'Content-Type': 'application/json',
            },
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message,
                timer: 1500, showConfirmButton: false }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Failed.', 'error');
        }
    } catch {
        Swal.fire('Error', 'Network error.', 'error');
    }
}

// ── Reset modal ────────────────────────────────────────────────────────────────
function resetModal() {
    document.getElementById('setting_id').value             = '';
    document.getElementById('schoolclass_id').value         = '';
    document.getElementById('session_id').value             = '';
    document.getElementById('term_id').value                = '';
    document.getElementById('promoted_label').value         = 'Promoted';
    document.getElementById('trial_label').value            = 'Promoted on Trial';
    document.getElementById('see_principal_label').value    = 'Advised to See Principal';
    document.getElementById('repeat_label').value           = 'Advice to Repeat';
    document.getElementById('rule_logic').value             = 'grade_count';
    document.getElementById('promotion_pass_average').value = '';
    document.getElementById('avg_score_slider').value       = 50;
    document.getElementById('modal_is_active').checked      = true;
    document.getElementById('globalAverageSection').style.display = 'none';

    const badge = document.getElementById('modalActiveBadge');
    badge.className = 'active-badge is-active';
    badge.innerHTML = '<i class="ri-checkbox-circle-line"></i> Active';

    document.getElementById('subjectSummary').style.display = 'none';
    document.getElementById('addRuleBtn').disabled = true;

    promotionRules  = [];
    gradeScale      = ['A1','B2','B3','C4','C5','C6','D7','E8','F9'];
    isSenior        = true;
    totalSubjects   = 0;
    compulsoryCount = 0;
    classPassAvg    = null;

    rerenderRules();
}

document.getElementById('settingModal').addEventListener('hidden.bs.modal', resetModal);

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('DOMContentLoaded', () => {
    bindEditButtons();
    bindDeleteButtons();
});
</script>
@endsection
