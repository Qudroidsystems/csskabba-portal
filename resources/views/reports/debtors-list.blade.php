{{-- resources/views/reports/debtors-list.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.report-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
.report-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: white;
    padding: 15px 20px;
}
.filter-bar {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 15px 20px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Student Debtors List</h4>
            <p class="text-muted">Students with outstanding fee balances</p>
        </div>
    </div>

    <div class="report-card">
        <div class="filter-bar">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-0">Class</label>
                    <select class="form-select" id="classFilter">
                        <option value="">All Classes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0">Min Outstanding</label>
                    <input type="number" class="form-control" id="minOutstanding" placeholder="Min amount">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name or admission">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" id="searchBtn">
                        <i class="ri-search-line me-1"></i>Search
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="debtorsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th>Class</th>
                        <th>Bill Title</th>
                        <th class="text-end">Original (₦)</th>
                        <th class="text-end">Paid (₦)</th>
                        <th class="text-end">Outstanding (₦)</th>
                        <th class="text-end">Savings (₦)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-active">
                    <tr>
                        <td colspan="5" class="fw-bold text-end">Totals:</td>
                        <td class="text-end fw-bold" id="totalOriginal">₦0</td>
                        <td class="text-end fw-bold" id="totalPaid">₦0</td>
                        <td class="text-end fw-bold text-danger" id="totalOutstanding">₦0</td>
                        <td class="text-end fw-bold text-success" id="totalSavings">₦0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
</div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#debtorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("reports.financial.debtors") }}',
            type: 'GET',
            data: function(d) {
                d.class_id = $('#classFilter').val();
                d.min_outstanding = $('#minOutstanding').val();
                d.search_value = $('#searchInput').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'admission_no', name: 'admission_no' },
            { data: 'class_name', name: 'class_name' },
            { data: 'bill_title', name: 'bill_title' },
            { data: 'original_amount', name: 'original_amount', className: 'text-end' },
            { data: 'amount_paid', name: 'amount_paid', className: 'text-end' },
            { data: 'outstanding', name: 'outstanding', className: 'text-end' },
            { data: 'savings', name: 'savings', className: 'text-end' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        drawCallback: function(settings) {
            var api = this.api();
            var totalOriginal = api.column(5, {page:'current'}).data().sum();
            var totalPaid = api.column(6, {page:'current'}).data().sum();
            var totalOutstanding = api.column(7, {page:'current'}).data().sum();
            var totalSavings = api.column(8, {page:'current'}).data().sum();

            $('#totalOriginal').text('₦' + totalOriginal.toLocaleString());
            $('#totalPaid').text('₦' + totalPaid.toLocaleString());
            $('#totalOutstanding').text('₦' + totalOutstanding.toLocaleString());
            $('#totalSavings').text('₦' + totalSavings.toLocaleString());
        }
    });

    $('#searchBtn').on('click', function() {
        table.ajax.reload();
    });
});
</script>
@endsection
