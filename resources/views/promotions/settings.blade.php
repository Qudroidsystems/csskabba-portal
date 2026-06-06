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
    border-radius: var(--pay-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.pay-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}

.pay-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}

.pay-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

.setting-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    height: 100%;
}

.setting-card:hover {
    box-shadow: var(--pay-shadow);
    transform: translateY(-2px);
}

.rule-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.rule-badge.compulsory_only { background: #fef3c7; color: #92400e; }
.rule-badge.average_only { background: #dbeafe; color: #1e40af; }
.rule-badge.both { background: #dcfce7; color: #166534; }

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

.form-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.form-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--pay-primary);
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--pay-border);
}

.info-banner {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-banner i {
    font-size: 20px;
    color: #2563eb;
}

.info-banner .text {
    font-size: 13px;
    color: #1e40af;
}

.info-banner .text strong {
    display: block;
    margin-bottom: 4px;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="pay-hero">
                <h1><i class="ri-settings-4-line me-2"></i>Promotion Settings</h1>
                <p>Configure promotion rules based on compulsory subjects and pass averages.</p>
            </div>

            <div class="info-banner">
                <i class="ri-information-line"></i>
                <div class="text">
                    <strong>How Promotion Rules Work</strong>
                    Set rules to determine student promotion based on compulsory subject performance and overall averages.
                    You can use compulsory subjects only, average scores only, or combine both with AND/OR logic.
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding: 16px 20px">
                            <h5 class="mb-0 fw-semibold" style="color: var(--pay-primary)">
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
                                                <h6 class="mb-1 fw-bold">{{ $setting->schoolclass->schoolclass }} {{ $setting->schoolclass->arm ?? '' }}</h6>
                                                <small class="text-muted">
                                                    @if($setting->session)
                                                        {{ $setting->session->session }}
                                                    @else
                                                        All Sessions
                                                    @endif
                                                    @if($setting->term)
                                                        - {{ $setting->term->term }}
                                                    @else
                                                        - All Terms
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="rule-badge {{ $setting->rule_type }}">
                                                @if($setting->rule_type == 'compulsory_only')
                                                    Compulsory Only
                                                @elseif($setting->rule_type == 'average_only')
                                                    Average Only
                                                @else
                                                    Both Rules
                                                @endif
                                            </span>
                                        </div>

                                        @if($setting->rule_type == 'compulsory_only' || $setting->rule_type == 'both')
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1">
                                                <i class="ri-book-open-line me-1"></i>Compulsory Subjects:
                                            </div>
                                            <div class="fw-semibold">
                                                @if($setting->min_compulsory_pass)
                                                    <span class="badge bg-info me-1">{{ $setting->min_compulsory_pass }}</span>
                                                    Minimum subjects to pass
                                                @else
                                                    <span class="badge bg-secondary me-1">All</span>
                                                    All subjects required
                                                @endif
                                                @if($setting->compulsory_fail_action)
                                                    <div class="mt-1">
                                                        <small>Fail Action:
                                                            <span class="fw-semibold">
                                                                @if($setting->compulsory_fail_action == 'repeat')
                                                                    Advice to Repeat
                                                                @elseif($setting->compulsory_fail_action == 'see_principal')
                                                                    See Principal
                                                                @else
                                                                    Promoted on Trial
                                                                @endif
                                                            </span>
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        @if($setting->rule_type == 'average_only' || $setting->rule_type == 'both')
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1">
                                                <i class="ri-percent-line me-1"></i>Pass Averages:
                                            </div>
                                            <div class="fw-semibold">
                                                @if($setting->promotion_pass_average)
                                                    <div><span class="badge bg-success me-1">{{ $setting->promotion_pass_average }}%</span> Promoted</div>
                                                @endif
                                                @if($setting->trial_pass_average)
                                                    <div><span class="badge bg-warning me-1">{{ $setting->trial_pass_average }}%</span> Trial</div>
                                                @endif
                                                @if($setting->see_principal_average)
                                                    <div><span class="badge bg-info me-1">{{ $setting->see_principal_average }}%</span> See Principal</div>
                                                @endif
                                                @if(!$setting->promotion_pass_average && !$setting->trial_pass_average && !$setting->see_principal_average)
                                                    <span class="text-muted">No thresholds set</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        @if($setting->rule_type == 'both')
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1">
                                                <i class="ri-logic-line me-1"></i>Combined Logic:
                                            </div>
                                            <div class="fw-semibold">
                                                <span class="badge bg-primary">{{ strtoupper($setting->combined_logic) }}</span>
                                                {{ $setting->combined_logic == 'and' ? 'Both conditions must be met' : 'Either condition can be met' }}
                                            </div>
                                        </div>
                                        @endif

                                        <div class="border-top pt-3 mt-2">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Promoted</small>
                                                    <small class="fw-semibold">{{ $setting->promoted_label }}</small>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Trial</small>
                                                    <small class="fw-semibold">{{ $setting->trial_label }}</small>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">See Principal</small>
                                                    <small class="fw-semibold">{{ $setting->see_principal_label }}</small>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Repeat</small>
                                                    <small class="fw-semibold">{{ $setting->repeat_label }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-setting"
                                                    data-id="{{ $setting->id }}"
                                                    data-schoolclass_id="{{ $setting->schoolclass_id }}"
                                                    data-session_id="{{ $setting->session_id }}"
                                                    data-term_id="{{ $setting->term_id }}"
                                                    data-rule_type="{{ $setting->rule_type }}"
                                                    data-min_compulsory_pass="{{ $setting->min_compulsory_pass }}"
                                                    data-compulsory_fail_action="{{ $setting->compulsory_fail_action }}"
                                                    data-promotion_pass_average="{{ $setting->promotion_pass_average }}"
                                                    data-trial_pass_average="{{ $setting->trial_pass_average }}"
                                                    data-see_principal_average="{{ $setting->see_principal_average }}"
                                                    data-combined_logic="{{ $setting->combined_logic }}"
                                                    data-promoted_label="{{ $setting->promoted_label }}"
                                                    data-trial_label="{{ $setting->trial_label }}"
                                                    data-see_principal_label="{{ $setting->see_principal_label }}"
                                                    data-repeat_label="{{ $setting->repeat_label }}">
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
                                        <i class="ri-settings-4-line" style="font-size: 48px; opacity: 0.3;"></i>
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
    </div>
</div>

{{-- Add/Edit Setting Modal --}}
<div class="modal fade" id="addSettingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-settings-4-line me-2"></i>Promotion Rule Settings</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="settingForm">
                @csrf
                <input type="hidden" name="id" id="setting_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" name="schoolclass_id" id="schoolclass_id" required>
                                <option value="">-- Select Class --</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session (Optional)</label>
                            <select class="form-select" name="session_id" id="session_id">
                                <option value="">-- All Sessions --</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->session }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Leave empty to apply to all sessions</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term (Optional)</label>
                            <select class="form-select" name="term_id" id="term_id">
                                <option value="">-- All Terms --</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->term }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Leave empty to apply to all terms</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rule Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="rule_type" id="rule_type" required>
                                <option value="compulsory_only">Compulsory Subjects Only</option>
                                <option value="average_only">Pass Average Only</option>
                                <option value="both">Both Compulsory & Average</option>
                            </select>
                            <div class="form-text">Choose how to evaluate promotion</div>
                        </div>
                    </div>

                    <div id="compulsorySection" class="form-section" style="display: none;">
                        <div class="form-section-title">
                            <i class="ri-book-open-line me-2"></i>Compulsory Subjects Rules
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimum Compulsory Subjects to Pass</label>
                                <input type="number" class="form-control" name="min_compulsory_pass" id="min_compulsory_pass" placeholder="e.g., 3">
                                <div class="form-text">Leave empty to require passing ALL compulsory subjects</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Action if Compulsory Subjects Failed</label>
                                <select class="form-select" name="compulsory_fail_action" id="compulsory_fail_action">
                                    <option value="repeat">Repeat Class</option>
                                    <option value="see_principal">Advised to See Principal</option>
                                    <option value="trial">Promoted on Trial</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="averageSection" class="form-section" style="display: none;">
                        <div class="form-section-title">
                            <i class="ri-percent-line me-2"></i>Pass Average Rules
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Promotion Pass Average (%)</label>
                                <input type="number" step="0.5" class="form-control" name="promotion_pass_average" id="promotion_pass_average" placeholder="e.g., 50">
                                <div class="form-text">Student is fully promoted</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Trial Pass Average (%)</label>
                                <input type="number" step="0.5" class="form-control" name="trial_pass_average" id="trial_pass_average" placeholder="e.g., 45">
                                <div class="form-text">Promoted on trial basis</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">See Principal Average (%)</label>
                                <input type="number" step="0.5" class="form-control" name="see_principal_average" id="see_principal_average" placeholder="e.g., 40">
                                <div class="form-text">Advised to see principal</div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-2">
                            <i class="ri-information-line me-1"></i>
                            <small>Thresholds are evaluated in order: Promotion → Trial → See Principal → Repeat</small>
                        </div>
                    </div>

                    <div id="combinedLogicSection" class="form-section" style="display: none;">
                        <div class="form-section-title">
                            <i class="ri-logic-line me-2"></i>Combined Logic (Both Rules)
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Logic Type</label>
                                <select class="form-select" name="combined_logic" id="combined_logic">
                                    <option value="and">AND - Both conditions must be met for promotion</option>
                                    <option value="or">OR - Either condition can be met for promotion</option>
                                </select>
                                <div class="form-text">
                                    <strong>AND:</strong> Student must pass both compulsory subjects AND meet average requirement<br>
                                    <strong>OR:</strong> Student can pass either compulsory subjects OR meet average requirement
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="ri-price-tag-3-line me-2"></i>Promotion Labels (Customizable Text)
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Promoted Label</label>
                                <input type="text" class="form-control" name="promoted_label" id="promoted_label" placeholder="Promoted">
                                <div class="form-text">Shown for promoted students</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Trial Label</label>
                                <input type="text" class="form-control" name="trial_label" id="trial_label" placeholder="Promoted on Trial">
                                <div class="form-text">Shown for trial promotion</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">See Principal Label</label>
                                <input type="text" class="form-control" name="see_principal_label" id="see_principal_label" placeholder="Advised to See Principal">
                                <div class="form-text">Shown when principal review needed</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Repeat Label</label>
                                <input type="text" class="form-control" name="repeat_label" id="repeat_label" placeholder="Advice to Repeat">
                                <div class="form-text">Shown for repeating students</div>
                            </div>
                        </div>
                    </div>
                </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const ruleType = document.getElementById('rule_type');
    const compulsorySection = document.getElementById('compulsorySection');
    const averageSection = document.getElementById('averageSection');
    const combinedLogicSection = document.getElementById('combinedLogicSection');

    function toggleSections() {
        const value = ruleType.value;

        compulsorySection.style.display = (value === 'compulsory_only' || value === 'both') ? 'block' : 'none';
        averageSection.style.display = (value === 'average_only' || value === 'both') ? 'block' : 'none';
        combinedLogicSection.style.display = value === 'both' ? 'block' : 'none';

        // Update required attributes
        const minPass = document.getElementById('min_compulsory_pass');
        const failAction = document.getElementById('compulsory_fail_action');
        const combinedLogic = document.getElementById('combined_logic');

        if (minPass) minPass.required = (value === 'compulsory_only' || value === 'both');
        if (failAction) failAction.required = (value === 'compulsory_only' || value === 'both');
        if (combinedLogic) combinedLogic.required = value === 'both';
    }

    ruleType.addEventListener('change', toggleSections);
    toggleSections();

    // Handle form submission
    document.getElementById('settingForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = document.getElementById('setting_id').value;
        let url = '{{ route("promotion.settings.store") }}';
        let method = 'POST';

        if (id) {
            url = `/promotion-settings/${id}`;
            formData.append('_method', 'PUT');
        }

        Swal.fire({
            title: 'Saving...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

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
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                let errorMsg = data.message || 'Failed to save settings.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error!', 'An error occurred while saving settings.', 'error');
        }
    });

    // Edit setting
    document.querySelectorAll('.edit-setting').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('addSettingModal'));

            document.getElementById('setting_id').value = this.dataset.id;
            document.getElementById('schoolclass_id').value = this.dataset.schoolclass_id;
            document.getElementById('session_id').value = this.dataset.session_id || '';
            document.getElementById('term_id').value = this.dataset.term_id || '';
            document.getElementById('rule_type').value = this.dataset.rule_type;
            document.getElementById('min_compulsory_pass').value = this.dataset.min_compulsory_pass;
            document.getElementById('compulsory_fail_action').value = this.dataset.compulsory_fail_action || 'repeat';
            document.getElementById('promotion_pass_average').value = this.dataset.promotion_pass_average;
            document.getElementById('trial_pass_average').value = this.dataset.trial_pass_average;
            document.getElementById('see_principal_average').value = this.dataset.see_principal_average;
            document.getElementById('combined_logic').value = this.dataset.combined_logic || 'and';
            document.getElementById('promoted_label').value = this.dataset.promoted_label || 'Promoted';
            document.getElementById('trial_label').value = this.dataset.trial_label || 'Promoted on Trial';
            document.getElementById('see_principal_label').value = this.dataset.see_principal_label || 'Advised to See Principal';
            document.getElementById('repeat_label').value = this.dataset.repeat_label || 'Advice to Repeat';

            toggleSections();
            modal.show();
        });
    });

    // Delete setting
    document.querySelectorAll('.delete-setting').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            const result = await Swal.fire({
                title: 'Confirm Delete',
                text: `Delete promotion rules for ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const response = await fetch(`/promotion-settings/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'An error occurred.', 'error');
                }
            }
        });
    });
});
</script>
@endsection
