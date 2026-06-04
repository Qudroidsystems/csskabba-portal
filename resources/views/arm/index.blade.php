@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --arm-primary:  #1e3a5f;
    --arm-accent:   #2563eb;
    --arm-success:  #16a34a;
    --arm-warning:  #d97706;
    --arm-danger:   #dc2626;
    --arm-muted:    #6b7280;
    --arm-border:   #e2e8f0;
    --arm-bg:       #f8fafc;
    --arm-radius:   12px;
    --arm-shadow:   0 2px 8px rgba(0,0,0,.08);
}

.arm-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--arm-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.arm-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.arm-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.arm-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

.stat-card {
    background: #fff;
    border: 1px solid var(--arm-border);
    border-radius: var(--arm-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--arm-shadow); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--arm-primary); }
.stat-card .stat-label { font-size: 12px; color: var(--arm-muted); margin-top: 4px; }
.stat-card .stat-icon  { font-size: 32px; opacity: .12; float: right; margin-top: -8px; }

.arm-table th {
    background: var(--arm-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.arm-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--arm-border);
    font-size: 13px;
}
.arm-table tr:hover td { background: #f0f9ff; }

.badge-arm {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
    padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--arm-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--arm-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* Modal Styles */
.arm-modal .modal-content {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,.2);
}
.arm-modal-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    padding: 24px 28px;
    position: relative;
    overflow: hidden;
}
.arm-modal-hero::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.arm-modal-hero h5 { color: #fff; font-weight: 700; font-size: 16px; margin: 0; position: relative; }
.arm-modal-hero p  { color: rgba(255,255,255,.72); font-size: 12px; margin: 5px 0 0; position: relative; }
.arm-modal-hero .btn-close { position: absolute; top: 18px; right: 20px; filter: invert(1); opacity: .8; }

.arm-label {
    font-size: 12px; font-weight: 700;
    color: #374151; margin-bottom: 7px;
    display: flex; align-items: center; gap: 6px;
    text-transform: uppercase; letter-spacing: .04em;
}
.arm-label i { color: var(--arm-accent); font-size: 14px; }

.arm-input {
    width: 100%;
    border: 1.5px solid var(--arm-border);
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    transition: border .15s, box-shadow .15s;
}
.arm-input:focus {
    border-color: var(--arm-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.arm-btn {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff; border: none;
    border-radius: 10px;
    padding: 11px 20px;
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
    transition: opacity .15s, transform .1s;
}
.arm-btn:hover { opacity: .91; transform: translateY(-1px); }
.arm-btn:active { transform: translateY(0); }

.btn-icon-sm {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}
.btn-icon-sm i { font-size: 16px; }
.btn-icon-sm:hover { transform: scale(1.05); }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="arm-hero">
        <h1><i class="ri-group-line me-2"></i>School Arm Management</h1>
        <p>Manage school classes, arms, and sections efficiently.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-building-line"></i></div>
                <div class="stat-value">{{ $all_arms->count() }}</div>
                <div class="stat-label">Total Arms</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-check-line"></i></div>
                <div class="stat-value text-success">{{ $all_arms->where('updated_at', '>=', now()->subDays(30))->count() }}</div>
                <div class="stat-label">Recently Updated (30 days)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-history-line"></i></div>
                <div class="stat-value text-warning">{{ $all_arms->where('description', '!=', '')->count() }}</div>
                <div class="stat-label">With Description</div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="ri-error-warning-line me-1"></i>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-1"></i> {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="mb-0 fw-semibold" style="color:var(--arm-primary)">
                <i class="ri-list-check me-2"></i>All School Arms
                <span class="badge bg-primary ms-2">{{ $all_arms->count() }}</span>
            </h5>
            <div class="d-flex gap-2">
                @can('Create school-arm')
                    <button type="button" class="arm-btn" data-bs-toggle="modal" data-bs-target="#addArmModal">
                        <i class="ri-add-line"></i> Create Arm
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table arm-table w-100 mb-0" id="armsTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Arm Name</th>
                            <th>Description / Remark</th>
                            <th width="120">Last Updated</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($all_arms as $i => $arm)
                        <tr data-id="{{ $arm->id }}" data-url="{{ route('schoolarm.deletearm', ['armid' => $arm->id]) }}">
                            <td class="text-center fw-semibold">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder" style="width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                                        {{ strtoupper(substr($arm->arm, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $arm->arm }}</div>
                                        <div class="text-muted small">ID: {{ $arm->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $arm->description ?: '—' }}</span>
                            </td>
                            <td>
                                <div class="small">
                                    <div>{{ $arm->updated_at->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $arm->updated_at->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @can('Update school-arm')
                                        <button type="button"
                                                class="btn btn-subtle-primary btn-icon-sm edit-arm-btn"
                                                data-id="{{ $arm->id }}"
                                                data-arm="{{ $arm->arm }}"
                                                data-remark="{{ $arm->description }}"
                                                title="Edit Arm">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                    @endcan
                                    @can('Delete school-arm')
                                        <button type="button"
                                                class="btn btn-subtle-danger btn-icon-sm delete-arm-btn"
                                                data-id="{{ $arm->id }}"
                                                data-arm="{{ $arm->arm }}"
                                                title="Delete Arm">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="ri-inbox-line d-block mb-2" style="font-size: 2rem; opacity: 0.4;"></i>
                                No school arms found. Click "Create Arm" to add one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($data, 'links'))
            <div class="row mt-4 align-items-center">
                <div class="col-sm">
                    <div class="text-muted">
                        Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} results
                    </div>
                </div>
                <div class="col-sm-auto mt-3 mt-sm-0">
                    {{ $data->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
</div>
</div>

{{-- ADD ARM MODAL --}}
<div class="modal fade arm-modal" id="addArmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content">
            <div class="arm-modal-hero">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-add-circle-line me-2"></i>Create New Arm</h5>
                <p>Add a new school arm/class section</p>
            </div>
            <div class="p-4">
                <form id="addArmForm">
                    @csrf
                    <div class="mb-4">
                        <label class="arm-label">
                            <i class="ri-building-line"></i>Arm Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="arm" id="armName" class="arm-input" placeholder="e.g., Arts, Science, Commercial" required>
                    </div>
                    <div class="mb-4">
                        <label class="arm-label">
                            <i class="ri-file-text-line"></i>Description / Remark
                        </label>
                        <textarea name="remark" id="armRemark" class="arm-input" rows="3" placeholder="Enter description or additional information..."></textarea>
                    </div>
                    <div id="addArmError" class="alert alert-danger d-none"></div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="arm-btn" id="submitAddBtn">
                            <i class="ri-save-line"></i> Create Arm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT ARM MODAL --}}
<div class="modal fade arm-modal" id="editArmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content">
            <div class="arm-modal-hero">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-edit-circle-line me-2"></i>Edit Arm</h5>
                <p>Update school arm information</p>
            </div>
            <div class="p-4">
                <form id="editArmForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="editArmId">
                    <div class="mb-4">
                        <label class="arm-label">
                            <i class="ri-building-line"></i>Arm Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="arm" id="editArmName" class="arm-input" placeholder="e.g., Arts, Science, Commercial" required>
                    </div>
                    <div class="mb-4">
                        <label class="arm-label">
                            <i class="ri-file-text-line"></i>Description / Remark
                        </label>
                        <textarea name="remark" id="editArmRemark" class="arm-input" rows="3" placeholder="Enter description or additional information..."></textarea>
                    </div>
                    <div id="editArmError" class="alert alert-danger d-none"></div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="arm-btn" id="submitEditBtn">
                            <i class="ri-save-line"></i> Update Arm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="deleteArmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="arm-modal-hero" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-delete-bin-line me-2"></i>Delete Arm</h5>
                <p>This action cannot be undone</p>
            </div>
            <div class="p-4 text-center">
                <i class="ri-alert-line" style="font-size: 48px; color: #dc2626; opacity: 0.5; margin-bottom: 16px; display: inline-block;"></i>
                <h5 class="mb-2">Are you absolutely sure?</h5>
                <p class="text-muted mb-4">You are about to delete arm: <strong id="deleteArmName"></strong><br>This action cannot be reversed.</p>
                <input type="hidden" id="deleteArmId">
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="ri-delete-bin-line me-1"></i> Yes, Delete
                    </button>
                </div>
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
    // Initialize DataTable
    if ($('#armsTable tbody tr').length > 1 || ($('#armsTable tbody tr').length === 1 && !$('#armsTable tbody tr td').hasClass('text-center'))) {
        $('#armsTable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: '',
                searchPlaceholder: 'Search arms...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ arms',
                infoEmpty: 'No arms found',
                zeroRecords: 'No matching arms found',
            },
            columnDefs: [
                { orderable: false, targets: [4] }
            ],
        });
    }

    // ADD ARM
    $('#addArmForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#submitAddBtn');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: '{{ route("schoolarm.store") }}',
            method: 'POST',
            data: {
                arm: $('#armName').val(),
                remark: $('#armRemark').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Arm created successfully',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    $('#addArmError').removeClass('d-none').text(response.message || 'An error occurred');
                    submitBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                $('#addArmError').removeClass('d-none').text(errorMsg);
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // EDIT ARM - Open Modal
    $('.edit-arm-btn').on('click', function() {
        const id = $(this).data('id');
        const arm = $(this).data('arm');
        const remark = $(this).data('remark');

        $('#editArmId').val(id);
        $('#editArmName').val(arm);
        $('#editArmRemark').val(remark || '');
        $('#editArmError').addClass('d-none');

        $('#editArmModal').modal('show');
    });

    // EDIT ARM - Submit
    $('#editArmForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#submitEditBtn');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
            url: '{{ route("schoolarm.update") }}',
            method: 'POST',
            data: {
                id: $('#editArmId').val(),
                arm: $('#editArmName').val(),
                remark: $('#editArmRemark').val(),
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Arm updated successfully',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    $('#editArmError').removeClass('d-none').text(response.message || 'An error occurred');
                    submitBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                $('#editArmError').removeClass('d-none').text(errorMsg);
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // DELETE ARM - Open Modal
    $('.delete-arm-btn').on('click', function() {
        const id = $(this).data('id');
        const arm = $(this).data('arm');

        $('#deleteArmId').val(id);
        $('#deleteArmName').text(arm);
        $('#deleteArmModal').modal('show');
    });

    // DELETE ARM - Confirm
    $('#confirmDeleteBtn').on('click', function() {
        const id = $('#deleteArmId').val();
        const btn = $(this);
        const originalHtml = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: '{{ route("schoolarm.deletearm", "") }}/' + id,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Arm deleted successfully',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to delete arm',
                        confirmButtonColor: '#2563eb'
                    });
                    btn.prop('disabled', false).html(originalHtml);
                    $('#deleteArmModal').modal('hide');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while deleting the arm',
                    confirmButtonColor: '#2563eb'
                });
                btn.prop('disabled', false).html(originalHtml);
                $('#deleteArmModal').modal('hide');
            }
        });
    });

    // Reset forms when modals are closed
    $('#addArmModal').on('hidden.bs.modal', function() {
        $('#addArmForm')[0].reset();
        $('#addArmError').addClass('d-none');
    });

    $('#editArmModal').on('hidden.bs.modal', function() {
        $('#editArmError').addClass('d-none');
    });
});
</script>

@push('styles')
<style>
.btn-subtle-primary {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
    border: none;
}
.btn-subtle-primary:hover {
    background: rgba(37, 99, 235, 0.2);
    color: #1d4ed8;
}
.btn-subtle-danger {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
    border: none;
}
.btn-subtle-danger:hover {
    background: rgba(220, 38, 38, 0.2);
    color: #b91c1c;
}
.avatar-placeholder {
    transition: transform 0.2s ease;
}
.avatar-placeholder:hover {
    transform: scale(1.05);
}
.pagination {
    margin-bottom: 0;
}
.pagination .page-link {
    border-radius: 8px;
    margin: 0 3px;
    color: #1e3a5f;
    border: 1px solid #e2e8f0;
}
.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    border-color: #2563eb;
    color: white;
}
</style>
@endpush

@endsection
