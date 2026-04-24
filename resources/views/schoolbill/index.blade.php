{{-- resources/views/schoolbill/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --bill-primary: #1e3a5f;
    --bill-accent: #2563eb;
    --bill-success: #16a34a;
    --bill-warning: #d97706;
    --bill-danger: #dc2626;
    --bill-border: #e2e8f0;
    --bill-radius: 12px;
}

.bill-hero {
    background: linear-gradient(135deg, var(--bill-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--bill-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.bill-hero h1 { font-size: 22px; font-weight: 700; color: white; margin: 0 0 6px; }
.bill-hero p { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; }

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    margin-bottom: 1rem;
}
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--bill-border);
    border-radius: 8px;
    padding: 8px 12px;
    margin-left: 8px;
}
.dataTables_wrapper .dataTables_length select {
    border: 1px solid var(--bill-border);
    border-radius: 8px;
    padding: 6px 10px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="bill-hero">
        <h1><i class="ri-file-list-line me-2"></i>{{ $pagetitle }}</h1>
        <p>Create, manage, and assign school bills for fee collection across different student categories.</p>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold" style="color: var(--bill-primary);">
                            <i class="ri-list-check me-2"></i>All School Bills
                        </h5>
                        @can('Create school-bills')
                            <button type="button" class="btn btn-primary" id="createBillBtn">
                                <i class="ri-add-line me-1"></i>Create School Bill
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover w-100" id="billsTable">
                            <thead>
                                <tr>
                                    <th width="50"><input type="checkbox" id="selectAll"></th>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Bill Amount</th>
                                    <th>Remark</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

{{-- Create/Edit Modal --}}
<div class="modal fade" id="billModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Create School Bill</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="billForm">
                @csrf
                <input type="hidden" name="id" id="billId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bill Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Enter bill title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bill Amount (₦) <span class="text-danger">*</span></label>
                        <input type="number" name="bill_amount" id="billAmount" class="form-control" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Remark/Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter bill description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student Status <span class="text-danger">*</span></label>
                        <select name="statusId" id="statusId" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="1">Old Student Bill</option>
                            <option value="2">New Student Bill</option>
                        </select>
                    </div>
                    <div class="alert alert-danger d-none" id="formErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemTitle"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    let table;

    // Initialize DataTable
    function initDataTable() {
        table = $('#billsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('schoolbill.index') }}",
                type: 'GET',
                data: function(d) {
                    d._token = CSRF_TOKEN;
                }
            },
            columns: [
                { data: 'id', orderable: false, searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="row-checkbox" value="' + data + '">';
                    }
                },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title', name: 'title' },
                { data: 'formatted_amount', name: 'bill_amount' },
                { data: 'description', name: 'description' },
                { data: 'status_name', name: 'statusId' },
                { data: 'formatted_date', name: 'updated_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                processing: '<div class="spinner-border text-primary"></div>',
                search: '<i class="ri-search-line"></i>',
                searchPlaceholder: 'Search bills...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'No records available',
                zeroRecords: 'No matching records found'
            },
            order: [[1, 'desc']],
            pageLength: 15,
            responsive: true
        });
    }

    initDataTable();

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        $('.row-checkbox').trigger('change');
    });

    // Create Bill Button
    $('#createBillBtn').on('click', function() {
        $('#billForm')[0].reset();
        $('#billId').val('');
        $('#modalTitle').text('Create School Bill');
        $('#formErrors').addClass('d-none').empty();
        $('#billModal').modal('show');
    });

    // Edit Bill
    $(document).on('click', '.edit-bill', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        const amount = $(this).data('amount');
        const description = $(this).data('description');
        const status = $(this).data('status');

        $('#billId').val(id);
        $('#title').val(title);
        $('#billAmount').val(amount);
        $('#description').val(description);
        $('#statusId').val(status);
        $('#modalTitle').text('Edit School Bill');
        $('#formErrors').addClass('d-none').empty();
        $('#billModal').modal('show');
    });

    // Delete Bill
    let deleteId = null;
    $(document).on('click', '.delete-bill', function() {
        deleteId = $(this).data('id');
        $('#deleteItemTitle').text($(this).data('title'));
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (!deleteId) return;

        $.ajax({
            url: '/schoolbill/' + deleteId,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Deleted!', response.message, 'success');
                    table.ajax.reload();
                    $('#deleteModal').modal('hide');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to delete bill', 'error');
            }
        });
    });

    // Form Submission
    $('#billForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#billId').val();
        const url = id ? '/schoolbill/' + id : '/schoolbill';
        const method = id ? 'PUT' : 'POST';

        const formData = {
            title: $('#title').val(),
            bill_amount: $('#billAmount').val(),
            description: $('#description').val(),
            statusId: $('#statusId').val(),
            _token: CSRF_TOKEN,
            _method: method
        };

        $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#billModal').modal('hide');
                    table.ajax.reload();
                    $('#billForm')[0].reset();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#formErrors').removeClass('d-none').html(errorHtml);
                } else {
                    Swal.fire('Error!', 'Something went wrong', 'error');
                }
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false).html('Save Bill');
            }
        });
    });

    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire('Warning', 'Please select at least one bill to delete', 'warning');
            return;
        }

        Swal.fire({
            title: 'Delete Selected Bills?',
            text: `You are about to delete ${selectedIds.length} bill(s).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, delete them'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('schoolbill.bulk-destroy') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    data: { ids: selectedIds },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                            $('#selectAll').prop('checked', false);
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete bills', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
