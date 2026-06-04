{{-- resources/views/arm/index.blade.php --}}
@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent:  #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-danger:  #dc2626;
    --pay-purple:  #7c3aed;
    --pay-muted:   #6b7280;
    --pay-border:  #e2e8f0;
    --pay-bg:      #f8fafc;
    --pay-radius:  12px;
    --pay-shadow:  0 2px 8px rgba(0,0,0,.08);
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
.loading-spinner p { margin: 10px 0 0; font-size: 14px; font-weight: 600; color: var(--pay-primary); }

/* Hero Section */
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
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pay-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

/* Stat Cards */
.stat-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--pay-shadow); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--pay-primary); }
.stat-card .stat-label { font-size: 12px; color: var(--pay-muted); margin-top: 4px; }
.stat-card .stat-icon  { font-size: 32px; opacity: .12; float: right; margin-top: -8px; }

/* Table Styles */
.arm-table th {
    background: var(--pay-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.arm-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--pay-border);
    font-size: 13px;
}
.arm-table tr:hover td { background: #f0f9ff; }

/* Action Buttons */
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all .15s;
}
.btn-subtle-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.btn-subtle-secondary:hover {
    background: #e2e8f0;
    color: #1e293b;
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

/* Checkbox Styles */
.form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
    margin-top: 0;
}
.form-check-input:checked {
    background-color: var(--pay-accent);
    border-color: var(--pay-accent);
}

/* DataTables Overrides */
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    padding: 5px 24px 5px 10px;
    font-size: 13px;
}

/* Modal Styles */
#addArmModal .modal-content,
#editModal .modal-content,
#deleteRecordModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px;
    position: relative;
    overflow: hidden;
}
.modal-hero-bar::before {
    content: '';
    position: absolute; top: -25px; right: -25px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.modal-hero-bar h5 {
    color: #fff;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
    position: relative;
}
.modal-hero-bar .btn-close {
    position: absolute;
    top: 16px;
    right: 20px;
    filter: invert(1);
}
.modal-body {
    padding: 24px;
}
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.form-control, .form-select {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 14px;
    transition: border .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
.modal-footer {
    padding: 16px 24px 24px;
    border-top: none;
}
.btn {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: all .15s;
}
.btn-primary {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    border: none;
}
.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,.3);
}
.btn-light {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
}
.btn-light:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 52px 24px;
    color: var(--pay-muted);
}
.empty-state i {
    font-size: 3rem;
    opacity: .25;
    display: block;
    margin-bottom: 14px;
}
.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Alert Styles */
.alert {
    border: none;
    border-radius: 10px;
    padding: 14px 18px;
    font-size: 13px;
}
.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border-left: 3px solid #dc2626;
}
.alert-success {
    background: #f0fdf4;
    color: #166534;
    border-left: 3px solid #16a34a;
}

/* Badge */
.badge {
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
}
.bg-dark-subtle {
    background: #f1f5f9;
    color: #1e293b;
}

/* Search Box */
.search-box {
    position: relative;
}
.search-box .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--pay-muted);
    pointer-events: none;
}
.search-box .form-control {
    padding-right: 36px;
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

    <div class="pay-hero">
        <h1><i class="ri-building-line me-2"></i>School Arm Management</h1>
        <p>Manage school arms/classes divisions for organizing student classes.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value">{{ $all_arms->total() }}</div>
                <div class="stat-label">Total Arms</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-bar-chart-line"></i></div>
                <div class="stat-value text-primary">{{ $all_arms->count() }}</div>
                <div class="stat-label">Showing Now</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-line"></i></div>
                <div class="stat-value text-success">{{ $all_arms->where('updated_at', '>=', now()->subDays(30))->count() }}</div>
                <div class="stat-label">Last 30 Days</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-pencil-line"></i></div>
                <div class="stat-value text-warning">{{ $all_arms->where('updated_at', '>=', now()->subDays(7))->count() }}</div>
                <div class="stat-label">Recent Updates</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                <i class="ri-list-check me-2"></i>School Arms List
                <span class="badge bg-primary ms-2">{{ $all_arms->total() }}</span>
            </h5>
            <div class="d-flex gap-2">
                @can('Create school-arm')
                    <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addArmModal">
                        <i class="ri-add-line me-1"></i>Create Arm
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search arms...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-8 text-end">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for="checkAll">
                                Select All
                            </label>
                        </div>
                        <button class="btn btn-danger btn-sm d-none" id="remove-actions" onclick="deleteMultiple()">
                            <i class="ri-delete-bin-line me-1"></i>Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    <i class="ri-error-warning-line me-2"></i>{{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table arm-table w-100 mb-0" id="armsTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAllTable">
                                </div>
                            </th>
                            <th width="60">#</th>
                            <th>Arm Name</th>
                            <th>Description / Remark</th>
                            <th width="120">Last Updated</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0 @endphp
                        @forelse ($all_arms as $arm)
                            <tr data-id="{{ $arm->id }}" data-url="{{ route('schoolarm.deletearm') }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input chk_child" type="checkbox" value="{{ $arm->id }}">
                                    </div>
                                </td>
                                <td class="sn">{{ ++$i }}</td>
                                <td class="arm-name">
                                    <span class="fw-semibold">{{ $arm->arm }}</span>
                                </td>
                                <td class="arm-description">{{ $arm->description ?? '—' }}</td>
                                <td class="arm-updated">
                                    <span class="text-muted small">{{ $arm->updated_at->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('Update school-arm')
                                            <button type="button"
                                                    class="btn btn-subtle-secondary btn-icon edit-arm-btn"
                                                    data-id="{{ $arm->id }}"
                                                    data-arm="{{ $arm->arm }}"
                                                    data-description="{{ $arm->description }}">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endcan
                                        @can('Delete school-arm')
                                            <button type="button"
                                                    class="btn btn-subtle-danger btn-icon delete-arm-btn"
                                                    data-id="{{ $arm->id }}"
                                                    data-name="{{ $arm->arm }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="ri-inbox-line"></i>
                                        <p>No school arms found.</p>
                                        @can('Create school-arm')
                                            <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addArmModal">
                                                <i class="ri-add-line me-1"></i>Create your first arm
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <div class="text-muted text-center text-sm-start">
                            Showing <span class="fw-semibold">{{ $all_arms->count() }}</span> of <span class="fw-semibold">{{ $all_arms->total() }}</span> arms
                        </div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                            {{ $all_arms->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ADD ARM MODAL --}}
<div id="addArmModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-add-line me-2"></i>Create New School Arm</h5>
            </div>
            <form id="addArmForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="arm" class="form-label">Arm Name <span class="text-danger">*</span></label>
                        <input type="text" name="arm" id="arm" class="form-control" placeholder="e.g., A, B, C, or Science, Arts" required>
                        <div class="invalid-feedback" id="armError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description / Remark</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Enter a brief description of this arm" rows="3"></textarea>
                        <div class="invalid-feedback" id="descriptionError"></div>
                    </div>
                    <div class="alert alert-danger d-none" id="addAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addBtn">
                        <i class="ri-save-line me-1"></i>Create Arm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT ARM MODAL --}}
<div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-edit-line me-2"></i>Edit School Arm</h5>
            </div>
            <form id="editArmForm">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_arm" class="form-label">Arm Name <span class="text-danger">*</span></label>
                        <input type="text" name="arm" id="edit_arm" class="form-control" required>
                        <div class="invalid-feedback" id="editArmError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description / Remark</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="editDescriptionError"></div>
                    </div>
                    <div class="alert alert-danger d-none" id="editAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <i class="ri-save-line me-1"></i>Update Arm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-3">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-delete-bin-line" style="font-size: 28px; color: #dc2626;"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">You won't be able to revert this action!</p>
                    <p class="text-muted small mt-2" id="deleteItemName"></p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable for search/sort functionality
    var table = $('#armsTable').DataTable({
        pageLength: 10,
        order: [[1, 'asc']],
        language: {
            search: '',
            searchPlaceholder: 'Search arms...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ arms',
            infoEmpty: 'No arms found',
            zeroRecords: 'No matching arms',
        },
        columnDefs: [
            { orderable: false, targets: [0, 5] },
            { orderable: true, targets: [1, 2, 3, 4] }
        ],
        dom: 'rtip',
    });

    // Move custom search to work with DataTable
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // CheckAll functionality
    $('#checkAll, #checkAllTable').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.chk_child').prop('checked', isChecked);
        $('#remove-actions').toggleClass('d-none', !isChecked);
    });

    $(document).on('change', '.chk_child', function() {
        var anyChecked = $('.chk_child:checked').length > 0;
        $('#remove-actions').toggleClass('d-none', !anyChecked);
        var allChecked = $('.chk_child:checked').length === $('.chk_child').length;
        $('#checkAll, #checkAllTable').prop('checked', allChecked);
    });

    // ── ADD ARM ──────────────────────────────────────────────────────────
    $('#addArmForm').on('submit', function(e) {
        e.preventDefault();

        // Reset validation
        $('#arm, #description').removeClass('is-invalid');
        $('#armError, #descriptionError').text('');
        $('#addAlertError').addClass('d-none');

        const submitBtn = $('#addBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');

        $.ajax({
            url: '{{ route("schoolarm.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    if (errors.arm) {
                        $('#arm').addClass('is-invalid');
                        $('#armError').text(errors.arm[0]);
                    }
                    if (errors.description) {
                        $('#description').addClass('is-invalid');
                        $('#descriptionError').text(errors.description[0]);
                    }
                    if (xhr.responseJSON.message && !errors) {
                        $('#addAlertError').removeClass('d-none').text(xhr.responseJSON.message);
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    $('#addAlertError').removeClass('d-none').text(xhr.responseJSON.message);
                } else {
                    $('#addAlertError').removeClass('d-none').text('An error occurred. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ── EDIT ARM ──────────────────────────────────────────────────────────
    let editArmId = null;

    $(document).on('click', '.edit-arm-btn', function() {
        editArmId = $(this).data('id');
        const armName = $(this).data('arm');
        const description = $(this).data('description');

        $('#edit_id').val(editArmId);
        $('#edit_arm').val(armName);
        $('#edit_description').val(description || '');

        // Reset validation
        $('#edit_arm, #edit_description').removeClass('is-invalid');
        $('#editArmError, #editDescriptionError').text('');
        $('#editAlertError').addClass('d-none');

        $('#editModal').modal('show');
    });

    $('#editArmForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#updateBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        $.ajax({
            url: '{{ route("schoolarm.updatearm") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    if (errors.arm) {
                        $('#edit_arm').addClass('is-invalid');
                        $('#editArmError').text(errors.arm[0]);
                    }
                    if (errors.description) {
                        $('#edit_description').addClass('is-invalid');
                        $('#editDescriptionError').text(errors.description[0]);
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    $('#editAlertError').removeClass('d-none').text(xhr.responseJSON.message);
                } else {
                    $('#editAlertError').removeClass('d-none').text('An error occurred. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ── DELETE ARM (Single) ──────────────────────────────────────────────
    let deleteArmId = null;
    let deleteArmName = null;

    $(document).on('click', '.delete-arm-btn', function() {
        deleteArmId = $(this).data('id');
        deleteArmName = $(this).data('name');
        $('#deleteItemName').html(`<strong>${deleteArmName}</strong> will be permanently deleted.`);
        $('#deleteRecordModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!deleteArmId) return;

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');

        $.ajax({
            url: '{{ route("schoolarm.deletearm") }}',
            method: 'POST',
            data: {
                armid: deleteArmId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to delete arm.'
                });
                $('#deleteRecordModal').modal('hide');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
                deleteArmId = null;
            }
        });
    });
});

// ── BULK DELETE ──────────────────────────────────────────────────────────
function deleteMultiple() {
    const selectedIds = [];
    $('.chk_child:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one arm to delete.'
        });
        return;
    }

    Swal.fire({
        title: 'Delete Multiple Arms?',
        text: `You are about to delete ${selectedIds.length} arm(s). This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#loadingOverlay').addClass('active');

            // Process deletions sequentially
            let completed = 0;
            let errors = 0;

            selectedIds.forEach(id => {
                $.ajax({
                    url: '{{ route("schoolarm.deletearm") }}',
                    method: 'POST',
                    data: {
                        armid: id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        completed++;
                        if (response.success) {
                            // Remove row from table
                            $(`tr[data-id="${id}"]`).remove();
                        } else {
                            errors++;
                        }
                    },
                    error: function() {
                        errors++;
                        completed++;
                    },
                    complete: function() {
                        if (completed === selectedIds.length) {
                            $('#loadingOverlay').removeClass('active');
                            if (errors === 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: `${completed} arm(s) deleted successfully.`,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Partial Success',
                                    text: `${completed - errors} arm(s) deleted, ${errors} failed.`
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        }
                    }
                });
            });
        }
    });
}
</script>
@endsection
