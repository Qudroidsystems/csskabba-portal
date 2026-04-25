{{-- resources/views/reports/collection-summary.blade.php --}}
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
            <h4 class="fw-bold">School Fee Collection Summary</h4>
            <p class="text-muted">Summary of fee collection across classes, terms, and sessions</p>
        </div>
    </div>

    <div class="report-card">
        <div class="filter-bar">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-0">Class</label>
                    <select class="form-select" id="classFilter">
                        <option value="">All Classes</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0">Term</label>
                    <select class="form-select" id="termFilter">
                        <option value="">All Terms</option>
                        @foreach($terms ?? [] as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0">Session</label>
                    <select class="form-select" id="sessionFilter">
                        <option value="">All Sessions</option>
                        @foreach($sessions ?? [] as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" id="searchBtn">
                        <i class="ri-search-line me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="collectionTable">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th class="text-end">Students</th>
                        <th class="text-end">Total Expected (₦)</th>
                        <th class="text-end">Total Collected (₦)</th>
                        <th class="text-end">Outstanding (₦)</th>
                        <th class="text-end">Collection Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#collectionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("reports.financial.collection-summary") }}',
            type: 'GET',
            data: function(d) {
                d.class_id = $('#classFilter').val();
                d.term_id = $('#termFilter').val();
                d.session_id = $('#sessionFilter').val();
            }
        },
        columns: [
            { data: 'class', name: 'class' },
            { data: 'term', name: 'term' },
            { data: 'session', name: 'session' },
            { data: 'student_count', name: 'student_count', className: 'text-end' },
            { data: 'total_expected', name: 'total_expected', className: 'text-end' },
            { data: 'total_collected', name: 'total_collected', className: 'text-end' },
            { data: 'total_outstanding', name: 'total_outstanding', className: 'text-end' },
            { data: 'collection_rate', name: 'collection_rate', className: 'text-end' }
        ],
        order: [[0, 'asc']]
    });

    $('#searchBtn').on('click', function() {
        table.ajax.reload();
    });
});
</script>
@endsection
