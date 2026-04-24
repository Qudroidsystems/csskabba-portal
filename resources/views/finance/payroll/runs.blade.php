{{-- resources/views/finance/payroll/runs.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.payroll-runs-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="payroll-runs-hero">
        <h1 class="text-white"><i class="ri-team-line me-2"></i>{{ $pagetitle }}</h1>
        <p class="text-white-50 mb-0">View and manage individual staff payroll records.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-primary">{{ $summary['total_staff'] }}</div><div>Total Staff</div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-success">₦{{ number_format($summary['total_gross'], 2) }}</div><div>Total Gross Pay</div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-danger">₦{{ number_format($summary['total_deductions'], 2) }}</div><div>Total Deductions</div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-warning">₦{{ number_format($summary['total_net'], 2) }}</div><div>Total Net Pay</div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold"><i class="ri-table-line me-2"></i>Staff Payroll List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover w-100" id="payrollRunsTable">
                    <thead><tr><th>#</th><th>Staff Name</th><th>Staff ID</th><th>Gross Pay</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th width="100">Action</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#payrollRunsTable').DataTable({ processing: true, serverSide: true, ajax: { url: '{{ route("payroll.runs", $period->id) }}', type: 'GET' }, columns: [{ data: 'DT_RowIndex' }, { data: 'staff_name' }, { data: 'staff_id' }, { data: 'gross_pay' }, { data: 'deductions' }, { data: 'net_pay' }, { data: 'status_badge' }, { data: 'action' }] });
});
</script>
@endsection
