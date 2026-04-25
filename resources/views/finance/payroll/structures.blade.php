{{-- resources/views/finance/payroll/structures.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold">Staff Salary Structures</h4>
                    <p class="text-muted">Manage staff salary structures and allowances</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStructureModal">
                    <i class="ri-add-line me-1"></i>Add Structure
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Salary Structures</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover w-100" id="structuresTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th>Staff ID</th>
                            <th>Basic Salary</th>
                            <th>Total Earnings</th>
                            <th>Effective Period</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- Create Structure Modal --}}
<div class="modal fade" id="createStructureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-add-circle-line me-2"></i>Create Salary Structure</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createStructureForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Staff <span class="text-danger">*</span></label>
                            <select name="staff_id" class="form-select" required>
                                <option value="">Select Staff</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}">{{ $s->user->name ?? 'N/A' }} ({{ $s->employmentid ?? 'No ID' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Effective From <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Effective To</label>
                            <input type="date" name="effective_to" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Basic Salary <span class="text-danger">*</span></label>
                            <input type="number" name="basic_salary" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Housing Allowance</label>
                            <input type="number" name="housing_allowance" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Transport Allowance</label>
                            <input type="number" name="transport_allowance" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Meal Allowance</label>
                            <input type="number" name="meal_allowance" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Medical Allowance</label>
                            <input type="number" name="medical_allowance" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Utility Allowance</label>
                            <input type="number" name="utility_allowance" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Other Allowances</label>
                            <input type="number" name="other_allowances" class="form-control" step="0.01">
                        </div>
                    </div>
                    <div class="alert alert-danger d-none mt-3" id="structureErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

$(document).ready(function() {
    $('#structuresTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("payroll.structures") }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'staff_name', name: 'staff_name' },
            { data: 'staff_id', name: 'staff_id' },
            { data: 'basic_salary', name: 'basic_salary' },
            { data: 'total_earnings', name: 'total_earnings' },
            { data: 'effective_period', name: 'effective_period' },
            { data: 'is_active', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#createStructureForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: '{{ route("payroll.structures.store") }}',
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success').then(() => location.reload());
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var html = '<ul>';
                    $.each(errors, function(k, v) { html += '<li>' + v + '</li>'; });
                    html += '</ul>';
                    $('#structureErrors').removeClass('d-none').html(html);
                }
            }
        });
    });
});
</script>
@endsection
