@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
    .report-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .report-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        color: white;
        padding: 15px 20px;
    }
    .filter-bar {
        padding: 15px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .stat-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        height: 100%;
    }
    .collection-rate {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .rate-high { background: #dcfce7; color: #16a34a; }
    .rate-medium { background: #fef3c7; color: #d97706; }
    .rate-low { background: #fee2e2; color: #dc2626; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">School Fee Collection Summary</h4>
            <p class="text-muted">Analysis of fee collection performance by class</p>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-filter-line me-2"></i>Filters</h5>
        </div>
        <div class="filter-bar">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select id="classFilter" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Term</label>
                    <select id="termFilter" class="form-select">
                        <option value="">All Terms</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Session</label>
                    <select id="sessionFilter" class="form-select">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button id="applyFilters" class="btn btn-primary w-100">
                        <i class="ri-search-line"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="statsRow" style="display: none;">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-primary mb-2"><i class="ri-group-line fs-1"></i></div>
                <h3 id="totalStudents">0</h3>
                <small class="text-muted">Total Students</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-success mb-2"><i class="ri-money-dollar-circle-line fs-1"></i></div>
                <h3 id="totalCollected">₦0</h3>
                <small class="text-muted">Total Collected</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-warning mb-2"><i class="ri-alert-line fs-1"></i></div>
                <h3 id="totalOutstanding">₦0</h3>
                <small class="text-muted">Total Outstanding</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-info mb-2"><i class="ri-percent-line fs-1"></i></div>
                <h3 id="overallRate">0%</h3>
                <small class="text-muted">Overall Collection Rate</small>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-table-line me-2"></i>Collection Details</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="collectionTable" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Students</th>
                        <th class="text-end">Expected (₦)</th>
                        <th class="text-end">Collected (₦)</th>
                        <th class="text-end">Outstanding (₦)</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
var collectionTable;

function loadCollectionData() {
    $.ajax({
        url: '{{ route("reports.financial.collection-summary") }}',
        type: 'GET',
        data: {
            ajax: true,
            class_id: $('#classFilter').val(),
            term_id: $('#termFilter').val(),
            session_id: $('#sessionFilter').val()
        },
        success: function(response) {
            if (collectionTable) {
                collectionTable.clear().destroy();
            }

            collectionTable = $('#collectionTable').DataTable({
                data: response.data,
                columns: [
                    { data: 'class' },
                    { data: 'term' },
                    { data: 'session' },
                    { data: 'student_count' },
                    {
                        data: 'total_expected',
                        className: 'text-end',
                        render: function(data) { return '₦' + parseFloat(data).toLocaleString(); }
                    },
                    {
                        data: 'total_collected',
                        className: 'text-end',
                        render: function(data) { return '₦' + parseFloat(data).toLocaleString(); }
                    },
                    {
                        data: 'total_outstanding',
                        className: 'text-end',
                        render: function(data) { return '₦' + parseFloat(data).toLocaleString(); }
                    },
                    {
                        data: 'collection_rate',
                        render: function(data) {
                            var rate = parseFloat(data);
                            var rateClass = rate >= 70 ? 'rate-high' : (rate >= 40 ? 'rate-medium' : 'rate-low');
                            return '<span class="collection-rate ' + rateClass + '">' + data + '</span>';
                        }
                    }
                ],
                pageLength: 25,
                language: {
                    emptyTable: '<div class="text-center py-4 text-muted">No collection data available</div>'
                }
            });

            calculateStats(response.data);
            $('#statsRow').show();
        },
        error: function() {
            Swal.fire('Error', 'Failed to load collection data', 'error');
        }
    });
}

function calculateStats(data) {
    var totalStudents = 0;
    var totalCollected = 0;
    var totalExpected = 0;

    data.forEach(function(item) {
        totalStudents += parseInt(item.student_count) || 0;
        totalCollected += parseFloat(item.total_collected) || 0;
        totalExpected += parseFloat(item.total_expected) || 0;
    });

    var totalOutstanding = totalExpected - totalCollected;
    var overallRate = totalExpected > 0 ? ((totalCollected / totalExpected) * 100).toFixed(1) : 0;

    $('#totalStudents').text(totalStudents.toLocaleString());
    $('#totalCollected').text('₦' + totalCollected.toLocaleString());
    $('#totalOutstanding').text('₦' + totalOutstanding.toLocaleString());
    $('#overallRate').text(overallRate + '%');
}

$('#applyFilters').on('click', function() {
    loadCollectionData();
});

$(document).ready(function() {
    loadCollectionData();
});
</script>
@endsection
