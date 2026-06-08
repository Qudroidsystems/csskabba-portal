{{-- resources/views/promotions/templates.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --tp-primary: #1e3a5f;
    --tp-accent: #2563eb;
    --tp-success: #16a34a;
    --tp-warning: #d97706;
    --tp-danger: #dc2626;
    --tp-info: #0891b2;
    --tp-muted: #6b7280;
    --tp-border: #e2e8f0;
    --tp-bg: #f8fafc;
    --tp-radius: 12px;
    --tp-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.tp-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--tp-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.tp-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.tp-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.tp-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

.template-card {
    background: #fff;
    border: 1px solid var(--tp-border);
    border-radius: var(--tp-radius);
    padding: 20px;
    margin-bottom: 20px;
    transition: all .3s ease;
    height: 100%;
}
.template-card:hover {
    box-shadow: var(--tp-shadow);
    transform: translateY(-2px);
}

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
    max-height: 80vh;
    overflow-y: auto;
}
.modal-footer {
    border-top: 1px solid var(--tp-border);
    padding: 1rem 1.5rem;
}

.form-section {
    background: var(--tp-bg);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.form-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--tp-primary);
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--tp-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

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

.rule-preview {
    background: #f8fafc;
    border: 1px solid var(--tp-border);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
}
.rule-preview-header {
    font-weight: 700;
    font-size: 13px;
    margin-bottom: 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--tp-border);
}
.rule-preview-badge {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.rule-preview-badge.promoted { background: #dcfce7; color: #166534; }
.rule-preview-badge.trial { background: #fef9c3; color: #854d0e; }
.rule-preview-badge.principal { background: #e0f2fe; color: #075985; }
.rule-preview-badge.repeat { background: #fee2e2; color: #991b1b; }

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all .15s;
    border: none;
    cursor: pointer;
}
.btn-subtle-primary {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}
.btn-subtle-primary:hover {
    background: #dbeafe;
    color: #1d4ed8;
    transform: translateY(-1px);
}
.btn-subtle-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.btn-subtle-danger:hover {
    background: #fee2e2;
    color: #b91c1c;
    transform: translateY(-1px);
}

.empty-state {
    text-align: center;
    padding: 52px 24px;
    color: var(--tp-muted);
}
.empty-state i {
    font-size: 3rem;
    opacity: .25;
    display: block;
    margin-bottom: 14px;
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
            <div class="tp-hero">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="ri-file-copy-line me-2"></i>Promotion Rule Templates</h1>
                        <p>Create and manage reusable promotion rule templates that can be applied across different classes.</p>
                    </div>
                    <div>
                        <a href="{{ route('promotion.settings.index') }}" class="btn btn-light me-2">
                            <i class="ri-settings-4-line me-1"></i>Promotion Settings
                        </a>
                        <button type="button" class="btn btn-primary" id="createTemplateBtn">
                            <i class="ri-add-line me-1"></i>New Template
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                @forelse ($templates as $template)
                <div class="col-md-6 col-lg-4">
                    <div class="template-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1 fw-bold">{{ $template->name }}</h5>
                                <span class="badge {{ $template->grade_scale === 'senior' ? 'bg-primary' : 'bg-success' }}">
                                    <i class="ri-{{ $template->grade_scale === 'senior' ? 'graduation-cap' : 'school' }}-line me-1"></i>
                                    {{ ucfirst($template->grade_scale) }} Scale
                                </span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item edit-template"
                                                data-id="{{ $template->id }}"
                                                data-name="{{ $template->name }}"
                                                data-description="{{ $template->description }}"
                                                data-grade_scale="{{ $template->grade_scale }}"
                                                data-rules="{{ json_encode($template->rules) }}">
                                            <i class="ri-pencil-line me-2"></i>Edit Template
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item view-usage"
                                                data-id="{{ $template->id }}"
                                                data-name="{{ $template->name }}">
                                            <i class="ri-eye-line me-2"></i>View Usage
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger delete-template"
                                                data-id="{{ $template->id }}"
                                                data-name="{{ $template->name }}">
                                            <i class="ri-delete-bin-line me-2"></i>Delete Template
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @if($template->description)
                            <p class="text-muted small mb-3">{{ $template->description }}</p>
                        @endif

                        <div class="mb-3">
                            <div class="small text-muted mb-2">
                                <i class="ri-price-tag-3-line me-1"></i>
                                Rules in this template:
                            </div>
                            @php
                                $rules = $template->rules ?? [];
                                $ruleCount = count($rules);
                            @endphp
                            @if($ruleCount > 0)
                                <div class="mb-2">
                                    @foreach($rules as $rule)
                                        @php
                                            $statusBadge = match($rule['status_label'] ?? 'repeat') {
                                                'promoted' => 'promoted',
                                                'trial' => 'trial',
                                                'see_principal' => 'principal',
                                                default => 'repeat'
                                            };
                                        @endphp
                                        <span class="rule-preview-badge {{ $statusBadge }} mb-1">
                                            <i class="ri-{{ match($statusBadge) {
                                                'promoted' => 'checkbox-circle-line',
                                                'trial' => 'time-line',
                                                'principal' => 'user-star-line',
                                                default => 'repeat-line'
                                            } }} me-1"></i>
                                            {{ $rule['rule_name'] ?? 'Unnamed Rule' }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small">No rules defined in this template.</div>
                            @endif
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="ri-time-line me-1"></i>
                                    Created: {{ $template->created_at->format('M d, Y') }}
                                </small>
                                @if($template->settings_count)
                                    <span class="badge bg-secondary">
                                        <i class="ri-group-line me-1"></i>
                                        Used by {{ $template->settings_count }} class(es)
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill edit-template"
                                    data-id="{{ $template->id }}"
                                    data-name="{{ $template->name }}"
                                    data-description="{{ $template->description }}"
                                    data-grade_scale="{{ $template->grade_scale }}"
                                    data-rules="{{ json_encode($template->rules) }}">
                                <i class="ri-pencil-line me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-primary flex-fill use-template"
                                    data-id="{{ $template->id }}"
                                    data-name="{{ $template->name }}">
                                <i class="ri-download-line me-1"></i>Use Template
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="empty-state">
                        <i class="ri-file-copy-line"></i>
                        <p class="mt-3 text-muted">No promotion rule templates created yet.</p>
                        <button class="btn btn-primary" id="createEmptyTemplateBtn">
                            <i class="ri-add-line me-1"></i>Create Your First Template
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Create/Edit Template Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-file-copy-line me-2"></i><span id="templateModalTitle">Create Template</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="templateForm">
                @csrf
                <input type="hidden" name="id" id="template_id">
                <div class="modal-body">
                    <div class="form-section">
                        <div class="form-section-title">
                            <span><i class="ri-information-line me-2"></i>Basic Information</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="template_name" required
                                   placeholder="e.g., 'Standard Junior Secondary', 'Advanced Senior Secondary'">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="description" id="template_description" rows="3"
                                      placeholder="Describe when to use this template..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Grade Scale <span class="text-danger">*</span></label>
                            <select class="form-select" name="grade_scale" id="template_grade_scale" required>
                                <option value="senior">Senior Scale (A1, B2, B3, C4, C5, C6, D7, E8, F9)</option>
                                <option value="junior">Junior Scale (A, B, C, D, F)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <span><i class="ri-price-tag-3-line me-2"></i>Promotion Rules</span>
                            <button type="button" class="btn btn-sm btn-primary" id="addRuleToTemplateBtn">
                                <i class="ri-add-line me-1"></i>Add Rule
                            </button>
                        </div>
                        <div class="info-banner">
                            <i class="ri-lightbulb-line"></i>
                            <div class="text">
                                <strong>Template Rules</strong>
                                Define reusable promotion rules. These rules can be applied to multiple classes.
                                Each rule specifies the outcome based on subject performance.
                            </div>
                        </div>
                        <div id="templateRulesContainer">
                            <div class="no-rules-ph text-center py-4 text-muted" id="noTemplateRulesMsg">
                                <i class="ri-clipboard-line" style="font-size: 2rem; opacity: .3;"></i>
                                <p class="mt-2 mb-0">No rules added yet.</p>
                                <small>Click "Add Rule" to create promotion rules for this template.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTemplateBtn">
                        <i class="ri-save-line me-1"></i>Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Use Template Modal --}}
<div class="modal fade" id="useTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-download-line me-2"></i>Apply Template to Class</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-1"></i>
                    This will load the template rules into a promotion setting for the selected class.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Template</label>
                    <input type="text" class="form-control" id="useTemplateName" readonly>
                    <input type="hidden" id="useTemplateId">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Class <span class="text-danger">*</span></label>
                    <select class="form-select" id="useTemplateClass" required>
                        <option value="">-- Select Class --</option>
                        @foreach ($schoolclasses ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Session <small class="text-muted">(optional)</small></label>
                    <select class="form-select" id="useTemplateSession">
                        <option value="">-- All Sessions --</option>
                        @foreach ($sessions ?? [] as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Term <small class="text-muted">(optional)</small></label>
                    <select class="form-select" id="useTemplateTerm">
                        <option value="">-- All Terms --</option>
                        @foreach ($terms ?? [] as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUseTemplateBtn">
                    <i class="ri-download-line me-1"></i>Apply Template
                </button>
            </div>
        </div>
    </div>
</div>

{{-- View Usage Modal --}}
<div class="modal fade" id="usageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-eye-line me-2"></i>Template Usage</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="usageTemplateName"></h6>
                <p class="text-muted small">This template is used by the following promotion settings:</p>
                <div id="usageList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let templateRules = [];
let currentGradeScale = 'senior';

// Grade scales
const GRADE_SCALES = {
    senior: ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'],
    junior: ['A', 'B', 'C', 'D', 'F']
};

const STATUS_LABELS = [
    { key: 'promoted', label: 'Promoted', icon: 'ri-checkbox-circle-line', color: 'success' },
    { key: 'trial', label: 'Promoted on Trial', icon: 'ri-time-line', color: 'warning' },
    { key: 'see_principal', label: 'See Principal', icon: 'ri-user-star-line', color: 'info' },
    { key: 'repeat', label: 'Repeat', icon: 'ri-repeat-line', color: 'danger' }
];

// ── Template Rules Management ──────────────────────────────────────────────────
function renderTemplateRules() {
    const container = document.getElementById('templateRulesContainer');
    const noMsg = document.getElementById('noTemplateRulesMsg');

    if (templateRules.length === 0) {
        if (noMsg) noMsg.style.display = 'block';
        return;
    }
    if (noMsg) noMsg.style.display = 'none';

    container.innerHTML = templateRules.map((rule, idx) => buildTemplateRuleHTML(rule, idx)).join('');
    attachTemplateRuleListeners();
}

function buildTemplateRuleHTML(rule, idx) {
    const selectedStatus = STATUS_LABELS.find(s => s.key === rule.status_label) || STATUS_LABELS[3];
    const gradeOptions = GRADE_SCALES[currentGradeScale].map(g =>
        `<option value="${g}" ${rule.min_grade === g ? 'selected' : ''}>${g}</option>`
    ).join('');

    return `
    <div class="rule-preview" data-rule-idx="${idx}">
        <div class="rule-preview-header d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-secondary me-2">Rule ${idx + 1}</span>
                <span class="rule-preview-badge ${rule.status_label}">
                    <i class="${selectedStatus.icon} me-1"></i>${selectedStatus.label}
                </span>
                <input type="text" class="form-control form-control-sm d-inline-block ms-2"
                       style="width: 200px; display: inline-block;"
                       placeholder="Rule name" value="${escapeHtml(rule.rule_name || '')}"
                       data-rule-idx="${idx}" data-field="rule_name">
            </div>
            <button class="btn btn-sm btn-outline-danger remove-template-rule" data-idx="${idx}">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <label class="small fw-semibold">Minimum Grade</label>
                <select class="form-select form-select-sm rule-min-grade" data-rule-idx="${idx}">
                    <option value="">Any</option>
                    ${gradeOptions}
                </select>
            </div>
            <div class="col-md-6">
                <label class="small fw-semibold">Status Label</label>
                <select class="form-select form-select-sm rule-status" data-rule-idx="${idx}">
                    ${STATUS_LABELS.map(sl =>
                        `<option value="${sl.key}" ${rule.status_label === sl.key ? 'selected' : ''}>${sl.label}</option>`
                    ).join('')}
                </select>
            </div>
        </div>
        <div class="small text-muted mt-2">
            <i class="ri-information-line"></i>
            Rule applies to all compulsory subjects. Student must meet or exceed the minimum grade.
        </div>
    </div>`;
}

function attachTemplateRuleListeners() {
    document.querySelectorAll('.remove-template-rule').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.idx);
            templateRules.splice(idx, 1);
            renderTemplateRules();
        });
    });

    document.querySelectorAll('[data-field="rule_name"]').forEach(inp => {
        inp.addEventListener('input', (e) => {
            const idx = parseInt(e.target.dataset.ruleIdx);
            templateRules[idx].rule_name = e.target.value;
        });
    });

    document.querySelectorAll('.rule-min-grade').forEach(sel => {
        sel.addEventListener('change', (e) => {
            const idx = parseInt(e.target.dataset.ruleIdx);
            templateRules[idx].min_grade = e.target.value;
        });
    });

    document.querySelectorAll('.rule-status').forEach(sel => {
        sel.addEventListener('change', (e) => {
            const idx = parseInt(e.target.dataset.ruleIdx);
            templateRules[idx].status_label = e.target.value;
            renderTemplateRules();
        });
    });
}

document.getElementById('addRuleToTemplateBtn')?.addEventListener('click', () => {
    templateRules.push({
        rule_name: '',
        status_label: 'promoted',
        min_grade: ''
    });
    renderTemplateRules();
});

// ── Grade scale change ─────────────────────────────────────────────────────────
document.getElementById('template_grade_scale')?.addEventListener('change', (e) => {
    currentGradeScale = e.target.value;
    renderTemplateRules();
});

// ── Create/Edit Template ───────────────────────────────────────────────────────
document.getElementById('createTemplateBtn')?.addEventListener('click', () => {
    document.getElementById('templateModalTitle').textContent = 'Create Template';
    document.getElementById('template_id').value = '';
    document.getElementById('template_name').value = '';
    document.getElementById('template_description').value = '';
    document.getElementById('template_grade_scale').value = 'senior';
    templateRules = [];
    currentGradeScale = 'senior';
    renderTemplateRules();
    new bootstrap.Modal(document.getElementById('templateModal')).show();
});

document.querySelectorAll('.edit-template').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const data = e.currentTarget.dataset;
        document.getElementById('templateModalTitle').textContent = 'Edit Template';
        document.getElementById('template_id').value = data.id;
        document.getElementById('template_name').value = data.name;
        document.getElementById('template_description').value = data.description || '';
        document.getElementById('template_grade_scale').value = data.grade_scale;
        currentGradeScale = data.grade_scale;
        try {
            templateRules = JSON.parse(data.rules || '[]');
        } catch {
            templateRules = [];
        }
        renderTemplateRules();
        new bootstrap.Modal(document.getElementById('templateModal')).show();
    });
});

document.getElementById('templateForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = document.getElementById('template_name').value.trim();
    if (!name) {
        Swal.fire('Error', 'Please enter a template name', 'error');
        return;
    }

    if (templateRules.length === 0) {
        Swal.fire('Error', 'Please add at least one rule to the template', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', document.getElementById('template_description').value);
    formData.append('grade_scale', document.getElementById('template_grade_scale').value);
    formData.append('rules', JSON.stringify(templateRules));

    const id = document.getElementById('template_id').value;
    let url = '/promotion-templates';
    let method = 'POST';
    if (id) {
        url = `/promotion-templates/${id}`;
        formData.append('_method', 'PUT');
    }

    Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Saved!', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Failed to save', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Network error', 'error');
    }
});

// ── Use Template ────────────────────────────────────────────────────────────────
document.querySelectorAll('.use-template').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const data = e.currentTarget.dataset;
        document.getElementById('useTemplateId').value = data.id;
        document.getElementById('useTemplateName').value = data.name;
        new bootstrap.Modal(document.getElementById('useTemplateModal')).show();
    });
});

document.getElementById('confirmUseTemplateBtn')?.addEventListener('click', async () => {
    const templateId = document.getElementById('useTemplateId').value;
    const classId = document.getElementById('useTemplateClass').value;
    const sessionId = document.getElementById('useTemplateSession').value;
    const termId = document.getElementById('useTemplateTerm').value;

    if (!classId) {
        Swal.fire('Error', 'Please select a class', 'error');
        return;
    }

    Swal.fire({ title: 'Applying template...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        let url = `/promotion-templates/${templateId}/load-for-class?classid=${classId}`;
        if (sessionId) url += `&sessionid=${sessionId}`;
        if (termId) url += `&termid=${termId}`;

        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('useTemplateModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Template Applied!',
                text: `${data.template.name} applied with ${data.merged_rules?.length || 0} rules. You can now save the promotion setting.`,
                confirmButtonText: 'Go to Settings'
            }).then(() => {
                window.location.href = '/promotion-settings';
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to apply template', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Network error', 'error');
    }
});

// ── View Usage ─────────────────────────────────────────────────────────────────
document.querySelectorAll('.view-usage').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.id;
        const name = e.currentTarget.dataset.name;

        document.getElementById('usageTemplateName').innerHTML = `<i class="ri-file-copy-line me-1"></i>${escapeHtml(name)}`;
        document.getElementById('usageList').innerHTML = '<div class="text-center text-muted">Loading...</div>';

        new bootstrap.Modal(document.getElementById('usageModal')).show();

        try {
            const response = await fetch(`/promotion-templates/${id}/usage`);
            const data = await response.json();

            if (data.success && data.usage && data.usage.length > 0) {
                let html = '<ul class="list-group">';
                data.usage.forEach(setting => {
                    html += `<li class="list-group-item">
                        <div class="fw-semibold">${escapeHtml(setting.class_name)}</div>
                        <small class="text-muted">
                            ${setting.session_name || 'All Sessions'}
                            ${setting.term_name ? `- ${setting.term_name}` : '- All Terms'}
                        </small>
                    </li>`;
                });
                html += '</ul>';
                document.getElementById('usageList').innerHTML = html;
            } else {
                document.getElementById('usageList').innerHTML = '<p class="text-muted text-center">This template is not used by any promotion setting.</p>';
            }
        } catch (error) {
            document.getElementById('usageList').innerHTML = '<p class="text-danger text-center">Error loading usage data.</p>';
        }
    });
});

// ── Delete Template ────────────────────────────────────────────────────────────
document.querySelectorAll('.delete-template').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.id;
        const name = e.currentTarget.dataset.name;

        const result = await Swal.fire({
            title: 'Confirm Delete',
            html: `Delete template <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await fetch(`/promotion-templates/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Failed to delete', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Network error', 'error');
        }
    });
});

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.getElementById('createEmptyTemplateBtn')?.addEventListener('click', () => {
    document.getElementById('createTemplateBtn').click();
});
</script>
@endsection
