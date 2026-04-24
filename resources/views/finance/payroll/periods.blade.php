{{-- resources/views/finance/payroll/periods.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --payroll-primary: #1e3a5f;
    --payroll-accent: #2563eb;
    --payroll-success: #16a34a;
    --payroll-warning: #d97706;
    --payroll-danger: #dc2626;
    --payroll-border: #e2e8f0;
    --payroll-radius: 12px;
}

.payroll-hero {
    background: linear-gradient(135deg, var(--payroll-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--payroll-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border: 1px solid var(--payroll-border);
    border-radius: var(--payroll-radius);
    padding: 18px 20px;
    transition: all 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--payroll-primary); }
.stat-card .stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="payroll-hero">
        <h1 class="text-white"><i class="ri-money-dollar-circle-line me-2"></i>{{ $pagetitle }}</h1>
        <p class="text-white-50 mb-0">Manage payroll periods, process salaries, and generate payslips for staff.</p>
    </div>

    @if(isset($currentPeriod))
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <i class="ri-information-line me-2"></i>
                <strong>Current Payroll Period:</strong> {{ $currentPeriod->period_name }}
                ({{ $currentPeriod->start_date->format('d M Y') }} - {{ $currentPeriod->end_date->format('d M Y') }})
            </div>
            <button class="btn btn-sm btn-primary" id="processCurrentPayrollBtn" data-id="{{ $currentPeriod->id }}">
                <i class="ri-play-line me-1"></i>Process Now
            </button>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="ri-calendar-line me-2"></i>Payroll Periods</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
                <i class="ri-add-line me-1"></i>Create Period
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover w-100" id="payrollTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Period</th>
                            <th>Total Gross</th>
                            <th>Total Net Pay</th>
                            <th>Staff Count</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
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

{{-- Create Period Modal --}}
<div class="modal fade" id="createPeriodModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-add-circle-line me-2"></i>Create Payroll Period</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createPeriodForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Month <span class="text-danger">*</span></label>
                        <select name="month" class="form-select" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                        <select name="year" class="form-select" required>
                            @for($y = date('Y')-2; $y <= date('Y')+2; $y++)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-28') }}" required>
                        <small class="text-muted">When salaries will be paid</small>
                    </div>
                    <div class="alert alert-danger d-none" id="periodErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createPeriodBtn">Create Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Processing Modal --}}
<div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5 id="processingTitle">Processing Payroll...</h5>
                <p id="processingMessage" class="text-muted mb-0">Please wait while we process the payroll</p>
                <div class="progress mt-3" style="height: 5px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let payrollTable;

$(document).ready(function() {
    payrollTable = $('#payrollTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("payroll.periods") }}',
            type: 'GET',
            data: function(d) {
                d._token = CSRF_TOKEN;
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'period_info', name: 'period_name' },
            { data: 'total_gross', name: 'total_gross_pay' },
            { data: 'total_net', name: 'total_net_pay' },
            { data: null, name: 'staff_count', orderable: false,
                render: function(data) { return data.staff_count || '-'; }
            },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            processing: '<div class="spinner-border text-primary"></div>',
            search: '<i class="ri-search-line"></i>',
            searchPlaceholder: 'Search periods...'
        }
    });

    // Create Period Form
    $('#createPeriodForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $('#createPeriodBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: '{{ route("payroll.periods.store") }}',
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#createPeriodModal').modal('hide');
                    payrollTable.ajax.reload();
                    $('#createPeriodForm')[0].reset();
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
                    $('#periodErrors').removeClass('d-none').html(errorHtml);
                } else {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to create period', 'error');
                }
            },
            complete: function() {
                $('#createPeriodBtn').prop('disabled', false).html('Create Period');
            }
        });
    });

    // Process Payroll
    $(document).on('click', '.process-payroll, #processCurrentPayrollBtn', function() {
        const periodId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('processingModal'));
        modal.show();

        $.ajax({
            url: `/payroll/periods/${periodId}/process`,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    $('#processingTitle').text('Processing Complete!');
                    $('#processingMessage').text(response.message);
                    setTimeout(() => {
                        modal.hide();
                        payrollTable.ajax.reload();
                        Swal.fire('Success!', response.message, 'success');
                    }, 1500);
                }
            },
            error: function(xhr) {
                modal.hide();
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to process payroll', 'error');
            }
        });
    });

    // Approve Payroll
    $(document).on('click', '.approve-payroll', function() {
        const periodId = $(this).data('id');

        Swal.fire({
            title: 'Approve Payroll?',
            text: 'This will create accounting entries and lock the payroll period.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, approve'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/payroll/periods/${periodId}/approve`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function(response) {
                        Swal.fire('Approved!', response.message, 'success');
                        payrollTable.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to approve', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
