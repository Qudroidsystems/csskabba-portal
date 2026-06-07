@extends('layouts.master')

@section('content')
<style>
:root {
    --ps-primary: #1e3a5f;
    --ps-accent: #2563eb;
    --ps-success: #16a34a;
    --ps-warning: #d97706;
    --ps-danger: #dc2626;
    --ps-info: #0891b2;
    --ps-muted: #6b7280;
    --ps-border: #e2e8f0;
    --ps-bg: #f8fafc;
    --ps-radius: 12px;
    --ps-shadow: 0 2px 8px rgba(0,0,0,.08);
}

/* Hero Section */
.ps-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--ps-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.ps-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.ps-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.ps-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

/* Setting Card */
.setting-card {
    background: #fff;
    border: 1px solid var(--ps-border);
    border-radius: var(--ps-radius);
    padding: 20px;
    margin-bottom: 20px;
    transition: all .3s ease;
    height: 100%;
    position: relative;
}
.setting-card:hover {
    box-shadow: var(--ps-shadow);
    transform: translateY(-2px);
}
.setting-card.has-rules {
    border-left: 4px solid var(--ps-success);
}

/* Modal Improvements */
.modal-content {
    border-radius: 16px;
    overflow: hidden;
}
.modal-header {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px;
    border-bottom: none;
}
.modal-header .modal-title {
    color: #fff;
    font-weight: 700;
}
.modal-header .btn-close {
    filter: invert(1);
}
.modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}
.modal-footer {
    border-top: 1px solid var(--ps-border);
    padding: 1rem 1.5rem;
}

/* Form Sections */
.form-section {
    background: var(--ps-bg);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}
.form-section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--ps-primary);
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--ps-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Info Banner */
.info-banner {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.info-banner i {
    font-size: 20px;
    color: #2563eb;
    margin-top: 2px;
}
.info-banner .text {
    font-size: 13px;
    color: #1e40af;
    line-height: 1.5;
}
.info-banner .text strong {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

/* Rule Card */
.rule-card {
    background: #fff;
    border: 2px solid var(--ps-border);
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all .2s;
}
.rule-card:hover {
    border-color: var(--ps-accent);
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.rule-card-header {
    background: linear-gradient(90deg, #f8fafc, #fff);
    border-bottom: 1px solid var(--ps-border);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.rule-num-badge {
    background: var(--ps-primary);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
}
.rule-status-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}
.rule-status-badge.promoted { background: #16a34a; color: #fff; }
.rule-status-badge.trial { background: #ca8a04; color: #fff; }
.rule-status-badge.principal { background: #0284c7; color: #fff; }
.rule-status-badge.repeat { background: #dc2626; color: #fff; }
.rule-name-input {
    flex: 1;
    max-width: 350px;
    font-size: 14px;
}
.rule-card-body {
    padding: 20px;
}

/* Status Label Pills */
.label-selector {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.label-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 600;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all .2s;
}
.label-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.label-pill.active {
    box-shadow: 0 0 0 3px rgba(0,0,0,.15);
    transform: scale(1.02);
}
.label-pill.lp-promoted {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
}
.label-pill.lp-promoted.active {
    background: #16a34a;
    color: #fff;
    border-color: #15803d;
}
.label-pill.lp-trial {
    background: #fef9c3;
    color: #854d0e;
    border-color: #fde68a;
}
.label-pill.lp-trial.active {
    background: #ca8a04;
    color: #fff;
    border-color: #a16207;
}
.label-pill.lp-principal {
    background: #e0f2fe;
    color: #075985;
    border-color: #bae6fd;
}
.label-pill.lp-principal.active {
    background: #0284c7;
    color: #fff;
    border-color: #0369a1;
}
.label-pill.lp-repeat {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}
.label-pill.lp-repeat.active {
    background: #dc2626;
    color: #fff;
    border-color: #b91c1c;
}

/* Improved Subject Sections */
.subjects-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.subject-group {
    border: 1px solid var(--ps-border);
    border-radius: 10px;
    overflow: hidden;
}
.subject-group-header {
    background: linear-gradient(90deg, #f0f7ff, #f8fafc);
    padding: 10px 15px;
    border-bottom: 1px solid var(--ps-border);
    font-weight: 700;
    font-size: 14px;
}
.subject-group-header.compulsory {
    background: linear-gradient(90deg, #fef3c7, #fffbeb);
    color: #92400e;
}
.subject-group-header.other {
    background: linear-gradient(90deg, #f0fdf4, #f0fdf4);
    color: #166534;
}
.subject-group-header i {
    margin-right: 8px;
}
.subject-group-header .badge {
    margin-left: 10px;
    font-size: 11px;
}
.subjects-container {
    max-height: 300px;
    overflow-y: auto;
}
.subj-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.subj-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #fff;
}
.subj-table thead tr {
    background: #f1f5f9;
    border-bottom: 2px solid var(--ps-border);
}
.subj-table th {
    padding: 10px 12px;
    font-weight: 600;
    font-size: 12px;
    color: var(--ps-primary);
}
.subj-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.subj-table tbody tr:hover {
    background: #f8fafc;
}
.subject-name-cell {
    font-weight: 500;
}
.subject-code {
    font-size: 11px;
    color: var(--ps-muted);
    font-family: monospace;
    margin-left: 8px;
}
.badge-compulsory {
    background: #fef3c7;
    color: #92400e;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    display: inline-block;
    margin-right: 8px;
}
.badge-optional {
    background: #e0e7ff;
    color: #3730a3;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    display: inline-block;
    margin-right: 8px;
}
.grade-sel {
    border: 1.5px solid var(--ps-border);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 12px;
    background: #fff;
    cursor: pointer;
    width: 100px;
    font-weight: 500;
}
.grade-sel:focus {
    border-color: var(--ps-accent);
    outline: none;
    box-shadow: 0 0 0 2px rgba(37,99,235,.1);
}
.grade-sel.has-value {
    border-color: var(--ps-success);
    background: #f0fdf4;
}

/* Empty State */
.no-rules-placeholder {
    text-align: center;
    padding: 40px 20px;
    color: var(--ps-muted);
    background: var(--ps-bg);
    border-radius: 12px;
    border: 2px dashed var(--ps-border);
}
.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    border-top-color: #2563eb;
    animation: spin .6s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="ps-hero">
                <h1><i class="ri-settings-4-line me-2"></i>Promotion Settings</h1>
                <p>Define grade-based promotion rules per class. Each rule maps subject performance to a promotion status label.</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding: 16px 20px; background: #fff; border-bottom: 2px solid var(--ps-border);">
                    <h5 class="mb-0 fw-semibold" style="color: var(--ps-primary);">
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
                            <div class="setting-card {{ !empty($setting->promotion_rules) ? 'has-rules' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-0 fw-bold">
                                            {{ $setting->schoolclass->schoolclass }}
                                            {{ $setting->schoolclass->arm ?? '' }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $setting->session?->session ?? 'All Sessions' }}
                                            &mdash; {{ $setting->term?->term ?? 'All Terms' }}
                                        </small>
                                    </div>
                                    @if(!empty($setting->promotion_rules))
                                    <span class="badge bg-success">Active</span>
                                    @endif
                                </div>

                                @if(!empty($setting->promotion_rules) && is_array($setting->promotion_rules))
                                <div class="mt-2">
                                    <div class="small text-muted mb-2">
                                        <i class="ri-price-tag-3-line me-1"></i>
                                        {{ count($setting->promotion_rules) }} rule(s) configured
                                    </div>
                                    <div class="rules-list" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($setting->promotion_rules as $rule)
                                        @php
                                            $statusMap = [
                                                'promoted' => ['class' => 'success', 'icon' => 'ri-checkbox-circle-line', 'label' => $setting->promoted_label],
                                                'trial' => ['class' => 'warning', 'icon' => 'ri-time-line', 'label' => $setting->trial_label],
                                                'see_principal' => ['class' => 'info', 'icon' => 'ri-user-star-line', 'label' => $setting->see_principal_label],
                                                'repeat' => ['class' => 'danger', 'icon' => 'ri-repeat-line', 'label' => $setting->repeat_label]
                                            ];
                                            $status = $statusMap[$rule['status_label'] ?? 'repeat'] ?? $statusMap['repeat'];
                                        @endphp
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="fw-semibold small">{{ $rule['rule_name'] ?? 'Unnamed Rule' }}</div>
                                                <span class="badge bg-{{ $status['class'] }} px-2 py-1">
                                                    <i class="{{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
                                                </span>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="ri-book-open-line me-1"></i>
                                                {{ count($rule['subject_conditions'] ?? []) }} subject(s)
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                                    <i class="ri-alert-line me-1"></i> No rules defined yet.
                                </div>
                                @endif

                                <div class="border-top pt-3 mt-3">
                                    <div class="row g-2 small">
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
                                        data-promotion_rules="{{ json_encode($setting->promotion_rules ?? []) }}">
                                        <i class="ri-pencil-line"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-setting"
                                        data-id="{{ $setting->id }}"
                                        data-name="{{ $setting->schoolclass->schoolclass }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="ri-settings-4-line" style="font-size: 48px; opacity: .3;"></i>
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

<!-- Modal -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-settings-4-line me-2"></i>Promotion Rule Settings</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="settingForm" hidden>
                @csrf
                <input type="hidden" name="id" id="setting_id">
                <input type="hidden" name="promotion_rules" id="promotion_rules_input">
            </form>

            <div class="modal-body">
                <!-- Class Selection -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span><i class="ri-book-2-line me-2"></i>Class &amp; Scope</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="schoolclass_id" required>
                                <option value="">-- Select Class --</option>
                                @foreach ($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
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
                    <div id="subjectLoadStatus" class="mt-3" style="display: none;">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <div class="loading-spinner"></div>
                            <small>Loading subjects...</small>
                        </div>
                    </div>
                    <div id="subjectSummary" class="mt-2" style="display: none;"></div>
                </div>

                <!-- Promotion Rules -->
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
                            Rules are evaluated in order from top to bottom. The first rule that matches all subject
                            requirements determines the student's promotion status. Leave a subject's grade as
                            <strong>"Any"</strong> to skip that subject in the rule.
                        </div>
                    </div>

                    <div id="rulesContainer">
                        <div class="no-rules-placeholder" id="noRulesMsg">
                            <i class="ri-clipboard-line d-block mb-2" style="font-size: 2rem; opacity: .3;"></i>
                            Select a class above, then click <strong>Add Rule</strong> to define your first promotion condition.
                        </div>
                    </div>
                </div>

                <!-- Status Labels -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span><i class="ri-price-tag-line me-2"></i>Promotion Status Labels</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Promoted</label>
                            <input type="text" class="form-control" id="promoted_label" placeholder="Promoted" value="Promoted">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Trial</label>
                            <input type="text" class="form-control" id="trial_label" placeholder="Promoted on Trial" value="Promoted on Trial">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">See Principal</label>
                            <input type="text" class="form-control" id="see_principal_label" placeholder="Advised to See Principal" value="Advised to See Principal">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Repeat</label>
                            <input type="text" class="form-control" id="repeat_label" placeholder="Advice to Repeat" value="Advice to Repeat">
                        </div>
                    </div>
                </div>
            </div>

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
let compulsorySubjects = [];
let otherSubjects = [];
let promotionRules = [];
let gradeScale = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];

const STATUS_LABELS = [
    { key: 'promoted', label: 'Promoted', cls: 'lp-promoted', icon: 'ri-checkbox-circle-line' },
    { key: 'trial', label: 'Promoted on Trial', cls: 'lp-trial', icon: 'ri-time-line' },
    { key: 'see_principal', label: 'Advised to See Principal', cls: 'lp-principal', icon: 'ri-user-star-line' },
    { key: 'repeat', label: 'Advice to Repeat', cls: 'lp-repeat', icon: 'ri-repeat-line' },
];

function openModal() {
    new bootstrap.Modal(document.getElementById('settingModal')).show();
}

document.getElementById('openAddBtn')?.addEventListener('click', openModal);
document.getElementById('openAddBtn2')?.addEventListener('click', openModal);

async function refreshSubjects() {
    const classId = document.getElementById('schoolclass_id').value;
    const termId = document.getElementById('term_id').value;
    const sessionId = document.getElementById('session_id').value;
    const addBtn = document.getElementById('addRuleBtn');
    const loadStatus = document.getElementById('subjectLoadStatus');
    const summary = document.getElementById('subjectSummary');

    addBtn.disabled = true;
    summary.style.display = 'none';
    compulsorySubjects = [];
    otherSubjects = [];

    if (!classId) {
        rerenderRules();
        addBtn.disabled = true;
        summary.innerHTML = '<div class="alert alert-warning py-2 mb-0"><i class="ri-alert-line me-1"></i> Please select a class first.</div>';
        summary.style.display = 'block';
        return;
    }

    loadStatus.style.display = 'block';

    try {
        let subUrl = `/promotion-settings/subjects-by-class?classid=${classId}`;
        let compUrl = `/promotion-settings/compulsory-by-class?classid=${classId}`;

        if (termId && termId !== '') {
            subUrl += `&termid=${termId}`;
            compUrl += `&termid=${termId}`;
        }
        if (sessionId && sessionId !== '') {
            subUrl += `&sessionid=${sessionId}`;
            compUrl += `&sessionid=${sessionId}`;
        }

        const [subRes, compRes] = await Promise.all([fetch(subUrl), fetch(compUrl)]);
        const subData = await subRes.json();
        const compData = await compRes.json();

        const allSubs = subData.success ? subData.subjects : [];
        const compSubs = compData.success ? compData.subjects : [];

        const compIds = new Set(compSubs.map(s => s.id));

        compulsorySubjects = compSubs.map(s => ({
            id: s.id,
            subject: s.subject,
            subject_code: s.subject_code,
            min_grade: s.min_grade || ''
        }));

        otherSubjects = allSubs.filter(s => !compIds.has(s.id)).map(s => ({
            id: s.id,
            subject: s.subject,
            subject_code: s.subject_code
        }));

        if (compSubs.length > 0 && compSubs[0].min_grade) {
            const sampleGrade = compSubs[0].min_grade;
            gradeScale = /[0-9]/.test(sampleGrade)
                ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9']
                : ['A', 'B', 'C', 'D', 'F'];
        }

        addBtn.disabled = false;
        loadStatus.style.display = 'none';

        if (allSubs.length > 0) {
            summary.innerHTML = `
                <div class="alert alert-success py-2 mb-0">
                    <i class="ri-checkbox-circle-line me-1"></i>
                    <strong>📚 ${compulsorySubjects.length}</strong> Compulsory subject(s) &nbsp;|&nbsp;
                    <strong>📖 ${otherSubjects.length}</strong> Other subject(s) loaded
                </div>`;
        } else {
            summary.innerHTML = `
                <div class="alert alert-warning py-2 mb-0">
                    <i class="ri-alert-line me-1"></i>
                    No subjects found. Please ensure subjects are assigned to this class.
                </div>`;
        }
        summary.style.display = 'block';

        promotionRules.forEach(rule => syncRuleSubjects(rule));
        rerenderRules();

    } catch (e) {
        console.error('Error loading subjects:', e);
        loadStatus.style.display = 'none';
        summary.innerHTML = `<div class="alert alert-danger py-2 mb-0">Error loading subjects: ${e.message}</div>`;
        summary.style.display = 'block';
        addBtn.disabled = true;
    }
}

function syncRuleSubjects(rule) {
    const existing = new Map((rule.subject_conditions || []).map(c => [c.subject_id, c]));
    rule.subject_conditions = [
        ...compulsorySubjects.map(s => existing.get(s.id) ?? {
            subject_id: s.id,
            subject_name: s.subject,
            is_compulsory: true,
            min_grade: ''
        }),
        ...otherSubjects.map(s => existing.get(s.id) ?? {
            subject_id: s.id,
            subject_name: s.subject,
            is_compulsory: false,
            min_grade: ''
        }),
    ];
}

['schoolclass_id', 'session_id', 'term_id'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', refreshSubjects);
});

document.getElementById('addRuleBtn').addEventListener('click', () => {
    if (compulsorySubjects.length === 0 && otherSubjects.length === 0) {
        Swal.fire('No Subjects', 'Please select a class that has subjects assigned.', 'warning');
        return;
    }

    const newRule = {
        rule_name: '',
        status_label: 'promoted',
        subject_conditions: [
            ...compulsorySubjects.map(s => ({
                subject_id: s.id,
                subject_name: s.subject,
                subject_code: s.subject_code,
                is_compulsory: true,
                min_grade: ''
            })),
            ...otherSubjects.map(s => ({
                subject_id: s.id,
                subject_name: s.subject,
                subject_code: s.subject_code,
                is_compulsory: false,
                min_grade: ''
            })),
        ]
    };
    promotionRules.push(newRule);
    rerenderRules();
});

function rerenderRules() {
    const container = document.getElementById('rulesContainer');
    const noMsg = document.getElementById('noRulesMsg');

    if (promotionRules.length === 0) {
        container.innerHTML = '';
        container.appendChild(noMsg);
        noMsg.style.display = 'block';
        return;
    }
    noMsg.style.display = 'none';

    container.innerHTML = promotionRules.map((rule, idx) => buildRuleCard(rule, idx)).join('');

    // Attach event listeners
    container.querySelectorAll('.rule-name-input').forEach(inp => {
        inp.addEventListener('input', e => {
            promotionRules[+e.target.dataset.idx].rule_name = e.target.value;
        });
    });

    container.querySelectorAll('.label-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            const idx = +pill.dataset.idx;
            const stat = pill.dataset.status;
            promotionRules[idx].status_label = stat;
            // Update UI
            const ruleCard = pill.closest('.rule-card');
            const statusBadge = ruleCard.querySelector('.rule-status-badge');
            const selectedLabel = STATUS_LABELS.find(s => s.key === stat);
            if (statusBadge && selectedLabel) {
                statusBadge.className = `rule-status-badge ${stat}`;
                statusBadge.innerHTML = `<i class="${selectedLabel.icon} me-1"></i>${selectedLabel.label}`;
            }
            // Update active state on pills
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
            // Highlight if value is selected
            if (e.target.value) {
                e.target.classList.add('has-value');
            } else {
                e.target.classList.remove('has-value');
            }
        });
        // Initialize highlight
        if (sel.value) {
            sel.classList.add('has-value');
        }
    });

    container.querySelectorAll('.remove-rule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            promotionRules.splice(+btn.dataset.idx, 1);
            rerenderRules();
        });
    });

    container.querySelectorAll('.move-up-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx > 0) {
                [promotionRules[idx-1], promotionRules[idx]] = [promotionRules[idx], promotionRules[idx-1]];
                rerenderRules();
            }
        });
    });

    container.querySelectorAll('.move-down-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = +btn.dataset.idx;
            if (idx < promotionRules.length - 1) {
                [promotionRules[idx], promotionRules[idx+1]] = [promotionRules[idx+1], promotionRules[idx]];
                rerenderRules();
            }
        });
    });
}

function buildRuleCard(rule, idx) {
    const selectedStatus = STATUS_LABELS.find(s => s.key === rule.status_label) || STATUS_LABELS[0];

    const labelPills = STATUS_LABELS.map(sl => {
        const active = rule.status_label === sl.key ? 'active' : '';
        return `<span class="label-pill ${sl.cls} ${active}" data-idx="${idx}" data-status="${sl.key}">
                    <i class="${sl.icon} me-1"></i>${sl.label}
                </span>`;
    }).join('');

    // Build compulsory subjects table
    const compulsoryConditions = rule.subject_conditions.filter(c => c.is_compulsory);
    const otherConditions = rule.subject_conditions.filter(c => !c.is_compulsory);

    const buildSubjectRows = (conditions, isCompulsory) => {
        if (conditions.length === 0) return '';
        return conditions.map((cond, sIdx) => `
            <tr>
                <td class="subject-name-cell">
                    ${isCompulsory ? '<span class="badge-compulsory"><i class="ri-star-fill me-1"></i>Compulsory</span>' : '<span class="badge-optional"><i class="ri-checkbox-line me-1"></i>Optional</span>'}
                    <strong>${escapeHtml(cond.subject_name)}</strong>
                    ${cond.subject_code ? `<span class="subject-code">(${escapeHtml(cond.subject_code)})</span>` : ''}
                 </td>
                <td style="width: 130px;">
                    <select class="grade-sel form-select form-select-sm" data-rule-idx="${idx}" data-subj-idx="${sIdx}">
                        <option value="">📌 Any</option>
                        ${gradeScale.map(g => `<option value="${g}" ${cond.min_grade === g ? 'selected' : ''}>${g}</option>`).join('')}
                    </select>
                 </td>
            </tr>
        `).join('');
    };

    const compulsoryRows = buildSubjectRows(compulsoryConditions, true);
    const otherRows = buildSubjectRows(otherConditions, false);

    return `
    <div class="rule-card">
        <div class="rule-card-header">
            <span class="rule-num-badge"><i class="ri-number-1 me-1"></i>Rule ${idx + 1}</span>
            <span class="rule-status-badge ${rule.status_label}">
                <i class="${selectedStatus.icon} me-1"></i>${selectedStatus.label}
            </span>
            <input type="text" class="form-control form-control-sm rule-name-input"
                   data-idx="${idx}" value="${escapeHtml(rule.rule_name)}"
                   placeholder="Enter rule name (e.g., 'All A's - Top Performer')">
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary move-up-btn" data-idx="${idx}" title="Move Up">
                    <i class="ri-arrow-up-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary move-down-btn" data-idx="${idx}" title="Move Down">
                    <i class="ri-arrow-down-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-rule-btn" data-idx="${idx}" title="Remove Rule">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        <div class="rule-card-body">
            <div class="mb-3">
                <label class="fw-semibold mb-2">
                    <i class="ri-award-line me-1"></i>Promotion Status Label
                </label>
                <div class="label-selector">${labelPills}</div>
            </div>
            <div>
                <label class="fw-semibold mb-2">
                    <i class="ri-graduation-cap-line me-1"></i>Minimum Grade Requirements
                </label>
                <div class="subjects-layout">
                    ${compulsoryRows ? `
                    <div class="subject-group">
                        <div class="subject-group-header compulsory">
                            <i class="ri-star-fill"></i> Compulsory Subjects
                            <span class="badge bg-warning text-dark">${compulsoryConditions.length} subjects</span>
                            <small class="text-muted ms-2">- Must meet these requirements</small>
                        </div>
                        <div class="subjects-container">
                            <table class="subj-table">
                                <thead>
                                    <tr><th>Subject</th><th style="width: 130px;">Minimum Grade</th></tr>
                                </thead>
                                <tbody>${compulsoryRows}</tbody>
                            </table>
                        </div>
                    </div>
                    ` : ''}

                    ${otherRows ? `
                    <div class="subject-group">
                        <div class="subject-group-header other">
                            <i class="ri-book-open-line"></i> Other Subjects
                            <span class="badge bg-success">${otherConditions.length} subjects</span>
                            <small class="text-muted ms-2">- Optional requirements</small>
                        </div>
                        <div class="subjects-container">
                            <table class="subj-table">
                                <thead>
                                    <tr><th>Subject</th><th style="width: 130px;">Minimum Grade</th></tr>
                                </thead>
                                <tbody>${otherRows}</tbody>
                            </table>
                        </div>
                    </div>
                    ` : ''}
                </div>
                <small class="text-muted mt-2 d-block">
                    <i class="ri-information-line"></i>
                    Leave as <strong>"Any"</strong> to exclude this subject from the rule evaluation.
                    ${compulsorySubjects.length > 0 ? '<span class="text-warning ms-2"><i class="ri-alert-line"></i> Compulsory subjects require passing grades!</span>' : ''}
                </small>
            </div>
        </div>
    </div>`;
}

document.getElementById('saveSettingBtn').addEventListener('click', async function() {
    const classId = document.getElementById('schoolclass_id').value;
    if (!classId) {
        Swal.fire('Validation Error', 'Please select a class.', 'warning');
        return;
    }

    for (const [i, rule] of promotionRules.entries()) {
        if (!rule.rule_name || !rule.rule_name.trim()) {
            Swal.fire('Validation Error', `Rule ${i + 1} must have a name.`, 'warning');
            return;
        }
    }

    document.getElementById('promotion_rules_input').value = JSON.stringify(promotionRules);

    const formData = new FormData(document.getElementById('settingForm'));
    formData.set('schoolclass_id', classId);
    formData.set('session_id', document.getElementById('session_id').value || '');
    formData.set('term_id', document.getElementById('term_id').value || '');
    formData.set('promoted_label', document.getElementById('promoted_label').value || 'Promoted');
    formData.set('trial_label', document.getElementById('trial_label').value || 'Promoted on Trial');
    formData.set('see_principal_label', document.getElementById('see_principal_label').value || 'Advised to See Principal');
    formData.set('repeat_label', document.getElementById('repeat_label').value || 'Advice to Repeat');

    const id = document.getElementById('setting_id').value;
    let url = '/promotion-settings';

    if (id) {
        url = `/promotion-settings/${id}`;
        formData.append('_method', 'PUT');
    }

    Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            const errorMsg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Failed to save.');
            Swal.fire('Error', errorMsg, 'error');
        }
    } catch (error) {
        console.error('Save error:', error);
        Swal.fire('Error', 'An error occurred while saving.', 'error');
    }
});

// Edit and Delete handlers...
document.querySelectorAll('.edit-setting').forEach(btn => {
    btn.addEventListener('click', async function() {
        const data = this.dataset;
        resetModal();

        document.getElementById('setting_id').value = data.id;
        document.getElementById('schoolclass_id').value = data.schoolclass_id;
        document.getElementById('session_id').value = data.session_id || '';
        document.getElementById('term_id').value = data.term_id || '';
        document.getElementById('promoted_label').value = data.promoted_label || 'Promoted';
        document.getElementById('trial_label').value = data.trial_label || 'Promoted on Trial';
        document.getElementById('see_principal_label').value = data.see_principal_label || 'Advised to See Principal';
        document.getElementById('repeat_label').value = data.repeat_label || 'Advice to Repeat';

        try {
            promotionRules = JSON.parse(data.promotion_rules || '[]');
        } catch (e) {
            promotionRules = [];
        }

        openModal();
        await refreshSubjects();
    });
});

document.querySelectorAll('.delete-setting').forEach(btn => {
    btn.addEventListener('click', async function() {
        const result = await Swal.fire({
            title: 'Confirm Delete',
            text: `Delete promotion rules for ${this.dataset.name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, Delete'
        });

        if (result.isConfirmed) {
            Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch(`/promotion-settings/${this.dataset.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Failed to delete.', 'error');
            }
        }
    });
});

function resetModal() {
    document.getElementById('setting_id').value = '';
    document.getElementById('schoolclass_id').value = '';
    document.getElementById('session_id').value = '';
    document.getElementById('term_id').value = '';
    document.getElementById('promoted_label').value = 'Promoted';
    document.getElementById('trial_label').value = 'Promoted on Trial';
    document.getElementById('see_principal_label').value = 'Advised to See Principal';
    document.getElementById('repeat_label').value = 'Advice to Repeat';
    promotionRules = [];
    compulsorySubjects = [];
    otherSubjects = [];
    rerenderRules();
    document.getElementById('addRuleBtn').disabled = true;
}

document.getElementById('settingModal').addEventListener('hidden.bs.modal', resetModal);

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection
