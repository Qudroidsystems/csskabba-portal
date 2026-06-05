@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
:root {
    --term-primary: #1e3a5f;
    --term-accent:  #2563eb;
    --term-success: #16a34a;
    --term-warning: #d97706;
    --term-danger:  #dc2626;
    --term-muted:   #6b7280;
    --term-border:  #e2e8f0;
    --term-bg:      #f8fafc;
    --term-radius:  12px;
    --term-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* Loading overlay */
.loading-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.loading-overlay.active { display: flex; }
.loading-spinner {
    background: white; padding: 24px 32px; border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18); text-align: center;
}
.loading-spinner .spinner-border { width: 2.5rem; height: 2.5rem; }
.loading-spinner p { margin: 10px 0 0; font-size: 14px; font-weight: 600; color: var(--term-primary); }

/* Hero section */
.term-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--term-radius); padding: 24px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.term-hero::before {
    content: ''; position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px; background: rgba(255,255,255,.06); border-radius: 50%;
}
.term-hero h1 { font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 4px; position: relative; }
.term-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

/* Info banner */
.info-banner {
    border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;
    display: flex; align-items: flex-start; gap: 12px; font-size: 13px;
}
.info-banner.success {
    background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;
}
.info-banner.warning {
    background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
}
.info-banner i { font-size: 20px; flex-shrink: 0; margin-top: 1px; }

/* Table styles */
.term-table th {
    background: var(--term-bg); color: var(--term-primary);
    padding: 12px 16px; font-size: 12px; font-weight: 700;
    border-bottom: 2px solid var(--term-border);
}
.term-table td {
    padding: 12px 16px; vertical-align: middle;
    font-size: 13px; border-bottom: 1px solid var(--term-border);
}
.term-table tr:hover td { background: #f0f9ff; }

/* Toggle switches */
.form-check-input.term-toggle,
.form-check-input.promo-toggle {
    width: 3em; height: 1.5em; cursor: pointer; transition: all .3s ease;
}
.form-check-input.promo-toggle:checked {
    background-color: #198754; border-color: #198754;
}
.form-check-input.term-toggle:checked {
    background-color: #198754; border-color: #198754;
}
.status-label.active   { color: #198754; font-weight: 500; }
.status-label.inactive { color: #dc3545; font-weight: 500; }

/* Badge styles */
.promo-badge-active {
    background: #f0fdf4; color: #166534;
    border: 1px solid #bbf7d0; padding: 4px 12px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
}
.promo-badge-inactive {
    background: #f1f5f9; color: #64748b;
    border: 1px solid #e2e8f0; padding: 4px 12px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
}

/* Modal styles */
#addTermModal .modal-content,
#editModal .modal-content {
    border: none; border-radius: 16px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px; position: relative; overflow: hidden;
}
.modal-hero-bar::before {
    content: ''; position: absolute; top: -25px; right: -25px;
    width: 100px; height: 100px; background: rgba(255,255,255,.07); border-radius: 50%;
}
.modal-hero-bar h5 { color: #fff; font-weight: 700; margin: 0; font-size: 15px; position: relative; }
.modal-hero-bar .btn-close { position: absolute; top: 16px; right: 20px; filter: invert(1); }

.form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-control, .form-select {
    border: 1.5px solid var(--term-border); border-radius: 8px;
    font-size: 13px; padding: 9px 14px; transition: border .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--term-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* Empty state */
.empty-state { text-align: center; padding: 52px 24px; color: var(--term-muted); }
.empty-state i { font-size: 3rem; opacity: .25; display: block; margin-bottom: 14px; }
.empty-state p { margin: 0; font-size: 14px; }

/* Alert animations */
.alert {
    border-radius: 10px;
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Global loading overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
            <p>Processing…</p>
        </div>
    </div>

    {{-- Alert messages container --}}
    <div id="alertContainer"></div>

    <div class="term-hero">
        <h1><i class="ri-calendar-line me-2"></i>Term Management</h1>
        <p>Create, manage, and configure academic terms with promotional settings</p>
    </div>

    {{-- Promotional term info banner --}}
    @php $promotionalTerm = $terms->firstWhere('is_promotional', true); @endphp
    @if ($promotionalTerm)
        <div class="info-banner success">
            <i class="ri-award-line"></i>
            <div>
                <strong>Promotional Term Active:</strong>
                <span class="fw-semibold">{{ $promotionalTerm->term }}</span>
                <span class="text-muted ms-2">— Student promotion is evaluated at the end of this term.</span>
            </div>
        </div>
    @else
        <div class="info-banner warning">
            <i class="ri-alert-line"></i>
            <div>
                <strong>No promotional term set.</strong>
                <span class="text-muted ms-1">Students will show <strong>Awaiting Final Term</strong> on their reports.</span>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <div class="flex-grow-1">
                <div class="search-box" style="max-width: 300px;">
                    <input type="text" class="form-control search-terms" placeholder="Search terms..." id="searchInput">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
            <div class="flex-shrink-0">
                @can('Create term')
                    <button type="button" class="btn btn-primary add-btn"
                            data-bs-toggle="modal" data-bs-target="#addTermModal">
                        <i class="ri-add-line align-baseline me-1"></i> Create Term
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table term-table mb-0" id="termsTable">
                    <thead>
                        <tr>
                            <th class="w-50px">#</th>
                            <th>Term Name</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Promotional Term</th>
                            <th>Date Updated</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="termsTableBody">
                        @forelse ($terms as $index => $term)
                            <tr id="term-row-{{ $term->id }}" data-term-name="{{ strtolower($term->term) }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold" style="color: var(--term-primary)">{{ $term->term }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input type="checkbox"
                                               class="form-check-input term-toggle"
                                               data-id="{{ $term->id }}"
                                               id="status-switch-{{ $term->id }}"
                                               {{ $term->status ? 'checked' : '' }}>
                                        <label class="form-check-label ms-1 status-label {{ $term->status ? 'active' : 'inactive' }}"
                                               for="status-switch-{{ $term->id }}">
                                            {{ $term->status ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                        <input type="checkbox"
                                               class="form-check-input promo-toggle"
                                               data-id="{{ $term->id }}"
                                               id="promo-switch-{{ $term->id }}"
                                               {{ $term->is_promotional ? 'checked' : '' }}>
                                        <span class="promo-badge-{{ $term->is_promotional ? 'active' : 'inactive' }}" id="promo-badge-{{ $term->id }}">
                                            <i class="ri-award-line me-1"></i>
                                            {{ $term->is_promotional ? 'Promotional' : 'Not Promotional' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $term->updated_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @can('Update term')
                                            <button class="btn btn-sm btn-outline-primary edit-term-btn"
                                                    data-id="{{ $term->id }}"
                                                    data-term="{{ $term->term }}"
                                                    data-status="{{ $term->status ? 1 : 0 }}"
                                                    data-promotional="{{ $term->is_promotional ? 1 : 0 }}">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endcan
                                        @can('Delete term')
                                            <button class="btn btn-sm btn-outline-danger delete-term-btn"
                                                    data-id="{{ $term->id }}"
                                                    data-term="{{ $term->term }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="ri-inbox-line"></i>
                                        <p>No terms found. Click "Create Term" to add your first academic term.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($terms->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-end">
                    {{ $terms->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ── ADD TERM MODAL ── --}}
    <div id="addTermModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-hero-bar">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    <h5><i class="ri-add-circle-line me-2"></i>Create New Term</h5>
                </div>
                <form id="addTermForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="term_name" class="form-label">Term Name <span class="text-danger">*</span></label>
                            <input type="text" name="term" id="term_name" class="form-control"
                                   placeholder="e.g., First Term, Second Term, Third Term" required>
                            <div class="invalid-feedback" id="termNameError"></div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="add-status-switch" name="status" checked>
                                <label class="form-check-label" for="add-status-switch">
                                    <i class="ri-checkbox-circle-line text-success me-1"></i>Active
                                </label>
                            </div>
                            <div class="form-text text-muted">Inactive terms won't be available for selection.</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="add-promo-switch" name="is_promotional">
                                <label class="form-check-label" for="add-promo-switch">
                                    <i class="ri-award-line text-warning me-1"></i>Promotional Term
                                </label>
                            </div>
                            <div class="form-text text-muted">If enabled, all other terms will lose their promotional status.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="addTermBtn">
                            <i class="ri-save-line me-1"></i>Create Term
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── EDIT TERM MODAL ── --}}
    <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-hero-bar">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    <h5><i class="ri-edit-line me-2"></i>Edit Term</h5>
                </div>
                <form id="editTermForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_term" class="form-label">Term Name <span class="text-danger">*</span></label>
                            <input type="text" name="term" id="edit_term" class="form-control" required>
                            <div class="invalid-feedback" id="editTermNameError"></div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="edit-status-switch" name="status">
                                <label class="form-check-label" for="edit-status-switch">
                                    <i class="ri-checkbox-circle-line text-success me-1"></i>Active
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="edit-promo-switch" name="is_promotional">
                                <label class="form-check-label" for="edit-promo-switch">
                                    <i class="ri-award-line text-warning me-1"></i>Promotional Term
                                </label>
                            </div>
                            <div class="form-text text-muted">If enabled, all other terms will lose their promotional status.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateTermBtn">
                            <i class="ri-save-line me-1"></i>Update Term
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
            <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Are you sure you want to delete <strong id="deleteTermName"></strong>?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="ri-delete-bin-line me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let deleteId = null;

    // Helper to show loading
    function showLoading(show) {
        document.getElementById('loadingOverlay').classList.toggle('active', show);
    }

    // Helper to show alert message (Bootstrap style)
    function showAlert(type, title, message, autoClose = true) {
        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert_' + Date.now();

        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="${type === 'success' ? 'ri-checkbox-circle-line' : (type === 'danger' ? 'ri-error-warning-line' : 'ri-information-line')} me-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        alertContainer.insertAdjacentHTML('beforeend', alertHtml);

        if (autoClose) {
            setTimeout(() => {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    alertElement.classList.remove('show');
                    setTimeout(() => alertElement.remove(), 150);
                }
            }, 3000);
        }

        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Search functionality ──
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#termsTableBody tr');

        rows.forEach(row => {
            const termName = row.getAttribute('data-term-name') || '';
            if (termName.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // ── Status Toggle ──
    $(document).on('change', '.term-toggle', async function() {
        const id = $(this).data('id');
        const isActive = $(this).is(':checked');
        const $toggle = $(this);
        const $label = $(`label[for="status-switch-${id}"]`);

        $toggle.prop('disabled', true);
        showLoading(true);

        try {
            const response = await fetch(`/term/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: isActive ? 1 : 0 }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Update label
                if (isActive) {
                    $label.text('Active').removeClass('inactive').addClass('active');
                } else {
                    $label.text('Inactive').removeClass('active').addClass('inactive');
                }
                showAlert('success', 'Success!', data.message);
            } else {
                $toggle.prop('checked', !isActive);
                showAlert('danger', 'Error!', data.message || 'Failed to update status');
            }
        } catch (error) {
            $toggle.prop('checked', !isActive);
            showAlert('danger', 'Error!', 'An error occurred while updating status');
        } finally {
            $toggle.prop('disabled', false);
            showLoading(false);
        }
    });

    // ── Promotional Toggle ──
    $(document).on('change', '.promo-toggle', async function() {
        const id = $(this).data('id');
        const isPromo = $(this).is(':checked');
        const $toggle = $(this);
        const $badge = $(`#promo-badge-${id}`);

        $toggle.prop('disabled', true);
        showLoading(true);

        try {
            const response = await fetch(`/term/${id}/promotional`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ is_promotional: isPromo ? 1 : 0 }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Reset ALL other toggles and badges
                if (isPromo) {
                    $('.promo-toggle').not($toggle).each(function() {
                        const otherId = $(this).data('id');
                        $(this).prop('checked', false);
                        $(`#promo-badge-${otherId}`)
                            .removeClass('promo-badge-active')
                            .addClass('promo-badge-inactive')
                            .html('<i class="ri-award-line me-1"></i>Not Promotional');
                    });
                }

                // Update current badge
                if (isPromo) {
                    $badge
                        .removeClass('promo-badge-inactive')
                        .addClass('promo-badge-active')
                        .html('<i class="ri-award-line me-1"></i>Promotional');
                } else {
                    $badge
                        .removeClass('promo-badge-active')
                        .addClass('promo-badge-inactive')
                        .html('<i class="ri-award-line me-1"></i>Not Promotional');
                }

                showAlert('success', 'Success!', data.message);

                // Reload to update banner after 1 second
                if (isPromo) {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                $toggle.prop('checked', !isPromo);
                showAlert('danger', 'Error!', data.message || 'Failed to update promotional status');
            }
        } catch (error) {
            $toggle.prop('checked', !isPromo);
            showAlert('danger', 'Error!', 'An error occurred while updating promotional status');
        } finally {
            $toggle.prop('disabled', false);
            showLoading(false);
        }
    });

    // ── Edit button: pre-fill modal ──
    $(document).on('click', '.edit-term-btn', function() {
        const id = $(this).data('id');
        const term = $(this).data('term');
        const status = $(this).data('status');
        const promotional = $(this).data('promotional');

        $('#edit_id').val(id);
        $('#edit_term').val(term);
        $('#edit-status-switch').prop('checked', status == 1);
        $('#edit-promo-switch').prop('checked', promotional == 1);
        $('#editTermNameError').addClass('d-none').text('');
        $('#editModal').modal('show');
    });

    // ── Add form submit ──
    $('#addTermForm').on('submit', async function(e) {
        e.preventDefault();

        const termName = $('#term_name').val().trim();
        if (!termName) {
            $('#termNameError').text('Term name is required').show();
            return;
        }

        const btn = $('#addTermBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');
        showLoading(true);

        try {
            const response = await fetch('{{ route("term.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    term: termName,
                    status: $('#add-status-switch').is(':checked') ? 1 : 0,
                    is_promotional: $('#add-promo-switch').is(':checked') ? 1 : 0,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                $('#addTermModal').modal('hide');
                showAlert('success', 'Success!', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                if (data.errors && data.errors.term) {
                    $('#termNameError').text(data.errors.term[0]).show();
                } else {
                    showAlert('danger', 'Error!', data.message || 'Failed to create term');
                }
            }
        } catch (error) {
            showAlert('danger', 'Error!', 'An error occurred while creating the term');
        } finally {
            btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Create Term');
            showLoading(false);
        }
    });

    // ── Edit form submit ──
    $('#editTermForm').on('submit', async function(e) {
        e.preventDefault();

        const id = $('#edit_id').val();
        const termName = $('#edit_term').val().trim();

        if (!termName) {
            $('#editTermNameError').text('Term name is required').show();
            return;
        }

        const btn = $('#updateTermBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
        showLoading(true);

        try {
            const response = await fetch(`/term/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    _method: 'PUT',
                    term: termName,
                    status: $('#edit-status-switch').is(':checked') ? 1 : 0,
                    is_promotional: $('#edit-promo-switch').is(':checked') ? 1 : 0,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                $('#editModal').modal('hide');
                showAlert('success', 'Success!', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                if (data.errors && data.errors.term) {
                    $('#editTermNameError').text(data.errors.term[0]).show();
                } else {
                    showAlert('danger', 'Error!', data.message || 'Failed to update term');
                }
            }
        } catch (error) {
            showAlert('danger', 'Error!', 'An error occurred while updating the term');
        } finally {
            btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Update Term');
            showLoading(false);
        }
    });

    // ── Delete button click ──
    $(document).on('click', '.delete-term-btn', function() {
        deleteId = $(this).data('id');
        const termName = $(this).data('term');
        $('#deleteTermName').text(termName);
        $('#confirmDeleteModal').modal('show');
    });

    // ── Confirm Delete ──
    $('#confirmDeleteBtn').on('click', async function() {
        if (!deleteId) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');
        showLoading(true);

        try {
            const response = await fetch(`/term/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (response.ok && data.success) {
                $('#confirmDeleteModal').modal('hide');
                showAlert('success', 'Success!', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', 'Error!', data.message || 'Failed to delete term');
            }
        } catch (error) {
            showAlert('danger', 'Error!', 'An error occurred while deleting the term');
        } finally {
            btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i>Delete');
            showLoading(false);
            deleteId = null;
        }
    });

    // Clear validation errors when modals are closed
    $('#addTermModal').on('hidden.bs.modal', function() {
        $('#termNameError').addClass('d-none').text('');
        $('#term_name').val('');
        $('#add-status-switch').prop('checked', true);
        $('#add-promo-switch').prop('checked', false);
    });

    $('#editModal').on('hidden.bs.modal', function() {
        $('#editTermNameError').addClass('d-none').text('');
    });
});
</script>
@endsection
