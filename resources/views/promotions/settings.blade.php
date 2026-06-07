{{-- resources/views/promotions/settings.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --ps-primary:  #1e3a5f;
    --ps-accent:   #2563eb;
    --ps-success:  #16a34a;
    --ps-warning:  #d97706;
    --ps-danger:   #dc2626;
    --ps-info:     #0891b2;
    --ps-muted:    #6b7280;
    --ps-border:   #e2e8f0;
    --ps-bg:       #f8fafc;
    --ps-radius:   12px;
    --ps-shadow:   0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ── */
.ps-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--ps-radius); padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.ps-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.ps-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.ps-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* ── Setting card ── */
.setting-card {
    background:#fff; border:1px solid var(--ps-border);
    border-radius:var(--ps-radius); padding:20px; margin-bottom:20px;
    transition:all .3s ease; height:100%;
}
.setting-card:hover { box-shadow:var(--ps-shadow); transform:translateY(-2px); }

/* ── Modal ── */
.modal-content {
    border-radius:16px; overflow:visible;
    display:flex; flex-direction:column;
    max-height:calc(100vh - 56px);
}
.modal-header {
    background:linear-gradient(135deg,#1e3a5f,#2563eb);
    padding:20px 28px; border-bottom:none;
    border-radius:16px 16px 0 0; flex-shrink:0;
}
.modal-header .modal-title { color:#fff; font-weight:700; }
.modal-header .btn-close   { filter:invert(1); }
.modal-footer { flex-shrink:0; border-radius:0 0 16px 16px; }
.modal-body   { overflow-y:auto; flex:1 1 auto; padding:1.5rem; }

.form-section {
    background:var(--ps-bg); border-radius:12px;
    padding:16px; margin-bottom:20px;
}
.form-section-title {
    font-size:14px; font-weight:700; color:var(--ps-primary);
    margin-bottom:16px; padding-bottom:8px;
    border-bottom:2px solid var(--ps-border);
    display:flex; align-items:center; justify-content:space-between;
}
.info-banner {
    background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;
    padding:12px 16px; margin-bottom:16px;
    display:flex; align-items:flex-start; gap:10px;
}
.info-banner i { font-size:18px; color:#2563eb; margin-top:1px; flex-shrink:0; }
.info-banner .text { font-size:13px; color:#1e40af; }
.info-banner .text strong { display:block; margin-bottom:2px; }

/* ── Rule card ── */
.rule-card {
    background:#fff; border:2px solid var(--ps-border);
    border-radius:12px; margin-bottom:16px; overflow:hidden;
    transition:border-color .2s;
}
.rule-card:hover { border-color:#bfdbfe; }
.rule-card-header {
    background:linear-gradient(90deg,#f0f7ff,#f8fafc);
    border-bottom:1px solid var(--ps-border);
    padding:12px 16px; display:flex; align-items:center; gap:10px;
}
.rule-num-badge {
    background:var(--ps-primary); color:#fff;
    font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px;
    white-space:nowrap;
}
.rule-card-body { padding:16px; }

/* status label pill selector */
.label-selector { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:0; }
.label-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600;
    border:2px solid transparent; cursor:pointer; transition:all .15s;
    white-space:nowrap;
}
.label-pill:hover        { transform:translateY(-1px); box-shadow:0 2px 8px rgba(0,0,0,.12); }
.label-pill.active       { box-shadow:0 0 0 3px rgba(0,0,0,.15); }
.label-pill.lp-promoted  { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
.label-pill.lp-promoted.active  { background:#16a34a; color:#fff; border-color:#15803d; }
.label-pill.lp-trial     { background:#fef9c3; color:#854d0e; border-color:#fde68a; }
.label-pill.lp-trial.active     { background:#ca8a04; color:#fff; border-color:#a16207; }
.label-pill.lp-principal { background:#e0f2fe; color:#075985; border-color:#bae6fd; }
.label-pill.lp-principal.active { background:#0284c7; color:#fff; border-color:#0369a1; }
.label-pill.lp-repeat    { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
.label-pill.lp-repeat.active    { background:#dc2626; color:#fff; border-color:#b91c1c; }

/* subject condition table */
.subj-table { width:100%; border-collapse:collapse; font-size:13px; }
.subj-table thead tr { background:#1e3a5f; color:#fff; }
.subj-table th, .subj-table td { padding:9px 12px; border-bottom:1px solid var(--ps-border); }
.subj-table td { vertical-align:middle; }
.subj-table tbody tr:hover td { background:#f0f9ff; }
.subj-table .badge-compulsory {
    background:#fef3c7; color:#92400e;
    padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;
}

/* grade select */
.grade-sel {
    border:1.5px solid var(--ps-border); border-radius:6px;
    padding:4px 8px; font-size:12px; color:var(--ps-primary);
    background:#fff; cursor:pointer; min-width:80px;
}
.grade-sel:focus { border-color:var(--ps-accent); outline:none; }

/* no-rules placeholder */
.no-rules-placeholder {
    text-align:center; padding:28px 20px; color:var(--ps-muted);
    background:var(--ps-bg); border-radius:10px;
    border:2px dashed var(--ps-border); font-size:13px;
}

/* loading spinner */
.spin { animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>

<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="ps-hero">
        <h1><i class="ri-settings-4-line me-2"></i>Promotion Settings</h1>
        <p>Define grade-based promotion rules per class. Each rule maps subject performance to a promotion status label.</p>
      </div>

      {{-- existing settings cards --}}
      <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding:16px 20px">
          <h5 class="mb-0 fw-semibold" style="color:var(--ps-primary)">
            <i class="ri-list-check me-2"></i>Promotion Rules
            <span class="badge bg-primary ms-2">{{ $settings->count() }}</span>
          </h5>
          <button type="button" class="btn btn-primary" id="openAddBtn">
            <i class="ri-add-line me-1"></i>Add New Rule
          </button>
        </div>
        <div class="card-body">
          <div class="row">
            @forelse ($settings as $setting)
            <div class="col-md-6 col-lg-4">
              <div class="setting-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h6 class="mb-0 fw-bold">
                      {{ $setting->schoolclass->schoolclass }} {{ $setting->schoolclass->arm ?? '' }}
                    </h6>
                    <small class="text-muted">
                      {{ $setting->session?->session ?? 'All Sessions' }}
                      &mdash; {{ $setting->term?->term ?? 'All Terms' }}
                    </small>
                  </div>
                </div>

                @if(!empty($setting->promotion_rules))
                  <div class="mt-2">
                    <div class="small text-muted mb-1">
                      <i class="ri-price-tag-3-line me-1"></i>
                      {{ count($setting->promotion_rules) }} rule(s) configured
                    </div>
                    @foreach($setting->promotion_rules as $rule)
                    @php
                      $lmap = ['promoted'=>'bg-success','trial'=>'bg-warning','see_principal'=>'bg-info','repeat'=>'bg-danger'];
                      $badge = $lmap[$rule['status_label'] ?? ''] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $badge }} me-1 mb-1">{{ $rule['rule_name'] ?? 'Rule '.($loop->index+1) }}</span>
                    @endforeach
                  </div>
                @else
                  <div class="text-muted small mt-2">No rules defined yet.</div>
                @endif

                {{-- Labels section --}}
                <div class="border-top pt-2 mt-2">
                  <div class="row g-1" style="font-size:12px">
                    <div class="col-6"><span class="text-muted">Promoted:</span> {{ $setting->promoted_label }}</div>
                    <div class="col-6"><span class="text-muted">Trial:</span> {{ $setting->trial_label }}</div>
                    <div class="col-6"><span class="text-muted">Principal:</span> {{ $setting->see_principal_label }}</div>
                    <div class="col-6"><span class="text-muted">Repeat:</span> {{ $setting->repeat_label }}</div>
                  </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-primary edit-setting"
                    data-id="{{ $setting->id }}"
                    data-schoolclass_id="{{ $setting->schoolclass_id }}"
                    data-session_id="{{ $setting->session_id }}"
                    data-term_id="{{ $setting->term_id }}"
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
                <button class="btn btn-primary" id="openAddBtn2">Create your first promotion rule</button>
              </div>
            </div>
            @endforelse
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     Add / Edit Modal
══════════════════════════════════════════ --}}
<div class="modal fade" id="settingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-settings-4-line me-2"></i>Promotion Rule Settings</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      {{-- hidden carrier form --}}
      <form id="settingForm" hidden>
        @csrf
        <input type="hidden" name="id"              id="setting_id">
        <input type="hidden" name="promotion_rules" id="promotion_rules_input">
      </form>

      <div class="modal-body">

        {{-- ── 1. Class / Session / Term ── --}}
        <div class="form-section">
          <div class="form-section-title">
            <span><i class="ri-book-2-line me-2"></i>Class &amp; Scope</span>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Class <span class="text-danger">*</span></label>
              <select class="form-select" id="schoolclass_id" required>
                <option value="">-- Select Class --</option>
                @foreach ($schoolclasses as $class)
                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Session <small class="text-muted">(optional)</small></label>
              <select class="form-select" id="session_id">
                <option value="">-- All Sessions --</option>
                @foreach ($sessions as $s)
                <option value="{{ $s->id }}">{{ $s->session }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Term <small class="text-muted">(optional)</small></label>
              <select class="form-select" id="term_id">
                <option value="">-- All Terms --</option>
                @foreach ($terms as $t)
                <option value="{{ $t->id }}">{{ $t->term }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div id="subjectLoadStatus" class="mt-2" style="display:none">
            <small class="text-muted">
              <i class="ri-loader-4-line spin me-1"></i>Loading subjects…
            </small>
          </div>
          <div id="subjectSummary" class="mt-2" style="display:none">
            <small class="text-success fw-semibold" id="subjectSummaryText"></small>
          </div>
        </div>

        {{-- ── 2. Promotion Rules ── --}}
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
              <strong>How rules work</strong>
              Each rule specifies a minimum grade for every subject (compulsory and non-compulsory) assigned to this
              class. When a student's grades meet all the specified minimums, the selected
              <em>Promotion Status Label</em> is applied. Rules are evaluated top-to-bottom; the first match wins.
              Leave a subject's grade as <em>Any</em> to ignore that subject in the rule.
            </div>
          </div>

          <div id="rulesContainer">
            <div class="no-rules-placeholder" id="noRulesMsg">
              <i class="ri-clipboard-line d-block mb-2" style="font-size:2rem;opacity:.3"></i>
              Select a class above, then click <strong>Add Rule</strong> to define your first promotion condition.
            </div>
          </div>
        </div>

        {{-- ── 3. Status Labels ── --}}
        <div class="form-section">
          <div class="form-section-title">
            <span><i class="ri-price-tag-line me-2"></i>Promotion Status Labels</span>
          </div>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Promoted</label>
              <input type="text" class="form-control" id="promoted_label" placeholder="Promoted">
            </div>
            <div class="col-md-3">
              <label class="form-label">Trial</label>
              <input type="text" class="form-control" id="trial_label" placeholder="Promoted on Trial">
            </div>
            <div class="col-md-3">
              <label class="form-label">See Principal</label>
              <input type="text" class="form-control" id="see_principal_label" placeholder="Advised to See Principal">
            </div>
            <div class="col-md-3">
              <label class="form-label">Repeat</label>
              <input type="text" class="form-control" id="repeat_label" placeholder="Advice to Repeat">
            </div>
          </div>
        </div>

      </div>{{-- /.modal-body --}}

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
/* ═══════════════════════════════════════════════════════════════
   State
═══════════════════════════════════════════════════════════════ */
let compulsorySubjects  = [];   // [{id, subject, subject_code, min_grade}]
let otherSubjects       = [];   // [{id, subject, subject_code}]
let promotionRules      = [];   // see structure below
let gradeScale          = [];   // ['A1','B2',...] or ['A','B','C','D','F']

// Rule structure:
// {
//   rule_name: '',
//   status_label: 'promoted'|'trial'|'see_principal'|'repeat',
//   subject_conditions: [
//     { subject_id, subject_name, is_compulsory, min_grade }   // min_grade='' means "Any"
//   ]
// }

const STATUS_LABELS = [
    { key:'promoted',      label:'Promoted',              cls:'lp-promoted'  },
    { key:'trial',         label:'Promoted on Trial',     cls:'lp-trial'     },
    { key:'see_principal', label:'Advised to See Principal', cls:'lp-principal' },
    { key:'repeat',        label:'Advice to Repeat',      cls:'lp-repeat'    },
];

/* ═══════════════════════════════════════════════════════════════
   Modal open helpers
═══════════════════════════════════════════════════════════════ */
function openModal() {
    new bootstrap.Modal(document.getElementById('settingModal')).show();
}
document.getElementById('openAddBtn')?.addEventListener('click', openModal);
document.getElementById('openAddBtn2')?.addEventListener('click', openModal);

/* ═══════════════════════════════════════════════════════════════
   Subject fetch
═══════════════════════════════════════════════════════════════ */
async function refreshSubjects() {
    const classId   = document.getElementById('schoolclass_id').value;
    const termId    = document.getElementById('term_id').value;
    const sessionId = document.getElementById('session_id').value;
    const addBtn    = document.getElementById('addRuleBtn');
    const loadStatus = document.getElementById('subjectLoadStatus');
    const summary    = document.getElementById('subjectSummary');

    addBtn.disabled = true;
    summary.style.display = 'none';

    if (!classId) {
        compulsorySubjects = []; otherSubjects = []; gradeScale = [];
        rerenderRules();
        return;
    }

    loadStatus.style.display = 'block';

    try {
        const [subRes, compRes] = await Promise.all([
            fetch(`/promotion-settings/subjects-by-class?classid=${classId}&termid=${termId}&sessionid=${sessionId}`),
            fetch(`/promotion-settings/compulsory-by-class?classid=${classId}&termid=${termId}&sessionid=${sessionId}`)
        ]);

        const subData  = subRes.ok  ? await subRes.json().catch(() => ({}))  : {};
        const compData = compRes.ok ? await compRes.json().catch(() => ({})) : {};

        const allSubs  = subData.success  ? subData.subjects  : [];
        const compSubs = compData.success ? compData.subjects : [];

        // normalise compulsory set
        const compIds = new Set(compSubs.map(s => s.id));

        compulsorySubjects = compSubs.map(s => ({
            id: s.id, subject: s.subject, subject_code: s.subject_code, min_grade: s.min_grade
        }));

        otherSubjects = allSubs.filter(s => !compIds.has(s.id)).map(s => ({
            id: s.id, subject: s.subject, subject_code: s.subject_code
        }));

        // detect grade scale from compulsory min_grades; fallback to all subjects
        const sampleGrade = compSubs.find(s => s.min_grade)?.min_grade || '';
        gradeScale = /[0-9]/.test(sampleGrade)
            ? ['A1','B2','B3','C4','C5','C6','D7','E8','F9']
            : ['A','B','C','D','F'];

        addBtn.disabled = false;
        loadStatus.style.display = 'none';
        summary.style.display = 'block';
        document.getElementById('subjectSummaryText').textContent =
            `✓ ${compulsorySubjects.length} compulsory subject(s), ${otherSubjects.length} other subject(s) loaded.`;

        // re-sync existing rules' subject_conditions to match current subject list
        promotionRules.forEach(rule => syncRuleSubjects(rule));
        rerenderRules();

    } catch(e) {
        loadStatus.style.display = 'none';
        compulsorySubjects = []; otherSubjects = []; gradeScale = [];
        addBtn.disabled = false;
        rerenderRules();
    }
}

/**
 * Make sure a rule's subject_conditions list is in sync with
 * the currently loaded compulsory + other subjects.
 */
function syncRuleSubjects(rule) {
    const existing = new Map((rule.subject_conditions || []).map(c => [c.subject_id, c]));

    rule.subject_conditions = [
        ...compulsorySubjects.map(s => existing.get(s.id) ?? {
            subject_id: s.id, subject_name: s.subject, is_compulsory: true, min_grade: ''
        }),
        ...otherSubjects.map(s => existing.get(s.id) ?? {
            subject_id: s.id, subject_name: s.subject, is_compulsory: false, min_grade: ''
        }),
    ];
}

['schoolclass_id','session_id','term_id'].forEach(id =>
    document.getElementById(id).addEventListener('change', refreshSubjects)
);

/* ═══════════════════════════════════════════════════════════════
   Add rule
═══════════════════════════════════════════════════════════════ */
document.getElementById('addRuleBtn').addEventListener('click', () => {
    const newRule = {
        rule_name: '',
        status_label: 'promoted',
        subject_conditions: [
            ...compulsorySubjects.map(s => ({
                subject_id: s.id, subject_name: s.subject, is_compulsory: true, min_grade: ''
            })),
            ...otherSubjects.map(s => ({
                subject_id: s.id, subject_name: s.subject, is_compulsory: false, min_grade: ''
            })),
        ]
    };
    promotionRules.push(newRule);
    rerenderRules();

    // scroll to the new card
    setTimeout(() => {
        const cards = document.querySelectorAll('.rule-card');
        if (cards.length) cards[cards.length-1].scrollIntoView({ behavior:'smooth', block:'nearest' });
    }, 60);
});

/* ═══════════════════════════════════════════════════════════════
   Render all rules
═══════════════════════════════════════════════════════════════ */
function rerenderRules() {
    const container = document.getElementById('rulesContainer');
    const noMsg     = document.getElementById('noRulesMsg');

    if (promotionRules.length === 0) {
        container.innerHTML = '';
        container.appendChild(noMsg);
        noMsg.style.display = 'block';
        return;
    }
    noMsg.style.display = 'none';

    container.innerHTML = promotionRules.map((rule, idx) => buildRuleCard(rule, idx)).join('');

    // Attach events
    container.querySelectorAll('.rule-name-input').forEach(inp => {
        inp.addEventListener('input', e => {
            promotionRules[+e.target.dataset.idx].rule_name = e.target.value;
        });
    });

    container.querySelectorAll('.label-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            const idx  = +pill.dataset.idx;
            const stat = pill.dataset.status;
            promotionRules[idx].status_label = stat;
            // update siblings
            pill.closest('.label-selector').querySelectorAll('.label-pill').forEach(p => {
                p.classList.toggle('active', p.dataset.status === stat);
            });
        });
    });

    container.querySelectorAll('.grade-sel').forEach(sel => {
        sel.addEventListener('change', e => {
            const rIdx = +e.target.dataset.ruleIdx;
            const sIdx = +e.target.dataset.subjIdx;
            promotionRules[rIdx].subject_conditions[sIdx].min_grade = e.target.value;
        });
    });

    container.querySelectorAll('.remove-rule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            promotionRules.splice(+btn.dataset.idx, 1);
            rerenderRules();
        });
    });

    // move up / down
    container.querySelectorAll('.move-up-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx === 0) return;
            [promotionRules[idx-1], promotionRules[idx]] = [promotionRules[idx], promotionRules[idx-1]];
            rerenderRules();
        });
    });
    container.querySelectorAll('.move-down-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx >= promotionRules.length-1) return;
            [promotionRules[idx], promotionRules[idx+1]] = [promotionRules[idx+1], promotionRules[idx]];
            rerenderRules();
        });
    });
}

/* ═══════════════════════════════════════════════════════════════
   Build one rule card
═══════════════════════════════════════════════════════════════ */
function buildRuleCard(rule, idx) {
    const labelPills = STATUS_LABELS.map(sl => {
        const active = rule.status_label === sl.key ? 'active' : '';
        return `<span class="label-pill ${sl.cls} ${active}"
                      data-idx="${idx}" data-status="${sl.key}">
                    ${sl.label}
                </span>`;
    }).join('');

    const gradeOptions = ['', ...gradeScale].map(g =>
        `<option value="${g}">${g === '' ? 'Any' : g}</option>`
    ).join('');

    const tableRows = rule.subject_conditions.map((cond, sIdx) => {
        // default from compulsory table if min_grade blank
        const compInfo = cond.is_compulsory
            ? compulsorySubjects.find(s => s.id === cond.subject_id)
            : null;

        const defaultGradeNote = (cond.is_compulsory && compInfo?.min_grade)
            ? `<small class="text-muted ms-1">(def: ${compInfo.min_grade})</small>`
            : '';

        // build select with current value selected
        const selOpts = ['', ...gradeScale].map(g =>
            `<option value="${g}" ${cond.min_grade === g ? 'selected' : ''}>${g === '' ? 'Any' : g}</option>`
        ).join('');

        return `
        <tr>
          <td>
            ${cond.is_compulsory
                ? `<span class="badge-compulsory me-1">Compulsory</span>`
                : ''}
            <strong>${cond.subject_name}</strong>
            ${defaultGradeNote}
          </td>
          <td class="text-center">
            <select class="grade-sel" data-rule-idx="${idx}" data-subj-idx="${sIdx}">
              ${selOpts}
            </select>
          </td>
        </tr>`;
    }).join('');

    const noSubjRow = rule.subject_conditions.length === 0
        ? `<tr><td colspan="2" class="text-center text-muted py-3">
              No subjects loaded. Please select a class/term above.
           </td></tr>`
        : '';

    const totalRules = promotionRules.length;

    return `
    <div class="rule-card">
      <div class="rule-card-header">
        <span class="rule-num-badge">Rule ${idx+1}</span>
        <input type="text" class="form-control form-control-sm rule-name-input"
               data-idx="${idx}"
               value="${escHtml(rule.rule_name)}"
               placeholder="Rule name (e.g. All A's — Top Performer)"
               style="max-width:320px">
        <div class="ms-auto d-flex gap-1">
          <button type="button" class="btn btn-sm btn-outline-secondary move-up-btn py-0 px-2"
                  data-idx="${idx}" title="Move up" ${idx===0?'disabled':''}>
            <i class="ri-arrow-up-s-line"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary move-down-btn py-0 px-2"
                  data-idx="${idx}" title="Move down" ${idx===totalRules-1?'disabled':''}>
            <i class="ri-arrow-down-s-line"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger remove-rule-btn py-0 px-2"
                  data-idx="${idx}" title="Remove rule">
            <i class="ri-delete-bin-line"></i>
          </button>
        </div>
      </div>

      <div class="rule-card-body">
        {{-- Status label --}}
        <div class="mb-3">
          <div class="fw-semibold mb-2" style="font-size:13px;color:var(--ps-primary)">
            <i class="ri-award-line me-1"></i>
            Promotion Status Label — <em>applied when this rule matches</em>
          </div>
          <div class="label-selector">${labelPills}</div>
        </div>

        {{-- Subject grade conditions --}}
        <div>
          <div class="fw-semibold mb-2" style="font-size:13px;color:var(--ps-primary)">
            <i class="ri-book-open-line me-1"></i>
            Minimum Grade Required per Subject
            <small class="text-muted fw-normal ms-1">
              — leave as <em>Any</em> to skip that subject in this rule
            </small>
          </div>
          <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
            <table class="subj-table">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th class="text-center" style="width:130px">Minimum Grade</th>
                </tr>
              </thead>
              <tbody>
                ${tableRows}
                ${noSubjRow}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>`;
}

/* ═══════════════════════════════════════════════════════════════
   Save
═══════════════════════════════════════════════════════════════ */
document.getElementById('saveSettingBtn').addEventListener('click', async function() {
    const classId = document.getElementById('schoolclass_id').value;
    if (!classId) { Swal.fire('Validation', 'Please select a class.', 'warning'); return; }

    // validate rule names
    for (const [i, rule] of promotionRules.entries()) {
        if (!rule.rule_name.trim()) {
            Swal.fire('Validation', `Rule ${i+1} needs a name.`, 'warning'); return;
        }
    }

    document.getElementById('promotion_rules_input').value = JSON.stringify(promotionRules);

    const formData = new FormData(document.getElementById('settingForm'));
    formData.set('schoolclass_id',      classId);
    formData.set('session_id',          document.getElementById('session_id').value);
    formData.set('term_id',             document.getElementById('term_id').value);
    formData.set('promoted_label',      document.getElementById('promoted_label').value || 'Promoted');
    formData.set('trial_label',         document.getElementById('trial_label').value    || 'Promoted on Trial');
    formData.set('see_principal_label', document.getElementById('see_principal_label').value || 'Advised to See Principal');
    formData.set('repeat_label',        document.getElementById('repeat_label').value   || 'Advice to Repeat');

    const id  = document.getElementById('setting_id').value;
    let url   = '{{ route("promotion.settings.store") }}';
    if (id) { url = `/promotion-settings/${id}`; formData.append('_method','PUT'); }

    Swal.fire({ title:'Saving…', allowOutsideClick:false, didOpen:() => Swal.showLoading() });

    try {
        const res  = await fetch(url, {
            method:'POST',
            headers:{
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':'application/json'
            },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon:'success', title:'Saved!', text:data.message, timer:2000, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Failed.');
            Swal.fire('Error', msg, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'An error occurred.', 'error');
    }
});

/* ═══════════════════════════════════════════════════════════════
   Edit setting
═══════════════════════════════════════════════════════════════ */
document.querySelectorAll('.edit-setting').forEach(btn => {
    btn.addEventListener('click', async function() {
        const d = this.dataset;
        resetModal();

        document.getElementById('setting_id').value       = d.id;
        document.getElementById('schoolclass_id').value   = d.schoolclass_id;
        document.getElementById('session_id').value       = d.session_id || '';
        document.getElementById('term_id').value          = d.term_id    || '';
        document.getElementById('promoted_label').value      = d.promoted_label      || '';
        document.getElementById('trial_label').value         = d.trial_label         || '';
        document.getElementById('see_principal_label').value = d.see_principal_label || '';
        document.getElementById('repeat_label').value        = d.repeat_label        || '';

        try { promotionRules = JSON.parse(d.promotion_rules || '[]'); }
        catch(e) { promotionRules = []; }

        openModal();
        await refreshSubjects();   // will re-sync rule subjects with loaded data
    });
});

/* ═══════════════════════════════════════════════════════════════
   Delete setting
═══════════════════════════════════════════════════════════════ */
document.querySelectorAll('.delete-setting').forEach(btn => {
    btn.addEventListener('click', async function() {
        const result = await Swal.fire({
            title:'Confirm Delete', icon:'warning', showCancelButton:true,
            text:`Delete promotion rules for ${this.dataset.name}?`,
            confirmButtonColor:'#dc3545', confirmButtonText:'Yes, Delete'
        });
        if (!result.isConfirmed) return;

        Swal.fire({ title:'Deleting…', allowOutsideClick:false, didOpen:() => Swal.showLoading() });
        try {
            const res  = await fetch(`/promotion-settings/${this.dataset.id}`, {
                method:'DELETE',
                headers:{
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon:'success', title:'Deleted!', text:data.message, timer:2000, showConfirmButton:false })
                    .then(() => location.reload());
            } else { Swal.fire('Error', data.message, 'error'); }
        } catch(e) { Swal.fire('Error', 'An error occurred.', 'error'); }
    });
});

/* ═══════════════════════════════════════════════════════════════
   Reset modal on close
═══════════════════════════════════════════════════════════════ */
function resetModal() {
    document.getElementById('settingForm').reset();
    document.getElementById('setting_id').value       = '';
    document.getElementById('schoolclass_id').value   = '';
    document.getElementById('session_id').value       = '';
    document.getElementById('term_id').value          = '';
    document.getElementById('promoted_label').value      = '';
    document.getElementById('trial_label').value         = '';
    document.getElementById('see_principal_label').value = '';
    document.getElementById('repeat_label').value        = '';
    promotionRules      = [];
    compulsorySubjects  = [];
    otherSubjects       = [];
    gradeScale          = [];
    document.getElementById('addRuleBtn').disabled = true;
    document.getElementById('subjectSummary').style.display  = 'none';
    document.getElementById('subjectLoadStatus').style.display = 'none';
    rerenderRules();
}

document.getElementById('settingModal').addEventListener('hidden.bs.modal', resetModal);

/* ═══════════════════════════════════════════════════════════════
   Utility
═══════════════════════════════════════════════════════════════ */
function escHtml(str) {
    return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endsection
