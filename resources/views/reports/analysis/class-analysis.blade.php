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
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
}
.filter-label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 8px;
    color: #1e3a5f;
}
.filter-label .required {
    color: #dc2626;
}
.stats-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}
.stats-value {
    font-size: 28px;
    font-weight: 700;
    color: #1e3a5f;
}
.stats-label {
    font-size: 12px;
    color: #64748b;
    margin-top: 5px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold">Class Financial Analysis</h4>
                    <p class="text-muted">Analyze fee collection by class, term, and session</p>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-outline-secondary me-2">
                        <i class="ri-printer-line"></i> Print
                    </button>
                    <button onclick="exportReport('excel')" class="btn btn-success me-2">
                        <i class="ri-file-excel-line"></i> Excel
                    </button>
                    <button onclick="exportReport('pdf')" class="btn btn-danger">
                        <i class="ri-file-pdf-line"></i> PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <div class="filter-label">Class <span class="required">*</span></div>
                <select class="form-select" id="class_id">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="filter-label">Term <span class="required">*</span></div>
                <select class="form-select" id="term_id">
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="filter-label">Session <span class="required">*</span></div>
                <select class="form-select" id="session_id">
                    <option value="">-- Select Session --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button class="btn btn-primary" id="loadReportBtn">
                    <i class="ri-search-line me-1"></i> Load Report
                </button>
                <button class="btn btn-secondary ms-2" id="resetBtn">
                    <i class="ri-refresh-line me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4" id="statsRow" style="display: none;">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-value" id="totalStudents">0</div>
                <div class="stats-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-value text-success" id="totalBilled">₦0</div>
                <div class="stats-label">Total Billed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-value text-info" id="totalPaid">₦0</div>
                <div class="stats-label">Total Paid</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-value text-warning" id="collectionRate">0%</div>
                <div class="stats-label">Collection Rate</div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="report-card" id="tableCard" style="display: none;">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-table-line me-2"></i>Student Payment Details</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="analysisTable" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th class="text-end">Total Billed (₦)</th>
                        <th class="text-end">Total Paid (₦)</th>
                        <th class="text-end">Outstanding (₦)</th>
                        <th width="100">Completion</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <td colspan="8" class="py-5 text-muted">
                            <i class="ri-inbox-line d-block mb-2 fs-1"></i>
                            Select class, term, and session then click Load Report
                        </td>
                    </tr>
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
var analysisTable;
var currentFilters = {};

$(document).ready(function() {
    initializeDataTable();

    $('#loadReportBtn').on('click', function() {
        var classId = $('#class_id').val();
        var termId = $('#term_id').val();
        var sessionId = $('#session_id').val();

        if (!classId || !termId || !sessionId) {
            Swal.fire('Warning', 'Please select class, term, and session', 'warning');
            return;
        }

        currentFilters = {
            class_id: classId,
            term_id: termId,
            session_id: sessionId
        };

        loadReportData();
    });

    $('#resetBtn').on('click', function() {
        $('#class_id').val('');
        $('#term_id').val('');
        $('#session_id').val('');
        $('#statsRow, #tableCard').hide();
        if (analysisTable) {
            analysisTable.clear().draw();
        }
    });
});

function initializeDataTable() {
    analysisTable = $('#analysisTable').DataTable({
        processing: true,
        serverSide: false, // We'll manually load data via AJAX
        pageLength: 25,
        searching: true,
        ordering: true,
        order: [[5, 'desc']],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'student_name' },
            { data: 'admission_no' },
            { data: 'total_billed', className: 'text-end' },
            { data: 'total_paid', className: 'text-end' },
            { data: 'outstanding', className: 'text-end' },
            { data: 'completion', orderable: false },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            emptyTable: '<div class="text-center py-5 text-muted">No data available. Please select filters and click Load Report.</div>',
            processing: '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>'
        }
    });
}

function loadReportData() {
    // Show loading
    analysisTable.clear().draw();
    $('#statsRow, #tableCard').hide();

    $.ajax({
        url: '{{ route("reports.analysis.class") }}',
        type: 'GET',
        data: {
            class_id: currentFilters.class_id,
            term_id: currentFilters.term_id,
            session_id: currentFilters.session_id,
            _: Date.now() // Prevent caching
        },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                // Add row index and action button to each row
                var dataWithIndex = response.data.map(function(item, index) {
                    return {
                        ...item,
                        DT_RowIndex: index + 1,
                        action: '<a href="/reports/analysis/student/' + item.student_id + '/' + currentFilters.class_id + '/' + currentFilters.term_id + '/' + currentFilters.session_id + '" class="btn btn-sm btn-info" target="_blank"><i class="ri-eye-line"></i> View</a>'
                    };
                });

                analysisTable.clear();
                analysisTable.rows.add(dataWithIndex);
                analysisTable.draw();

                calculateAndDisplayStats(response.data);
                $('#statsRow, #tableCard').show();
            } else {
                analysisTable.clear().draw();
                Swal.fire('Info', 'No data found for the selected filters', 'info');
                $('#statsRow, #tableCard').show();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            var errorMsg = 'Failed to load data. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            Swal.fire('Error', errorMsg, 'error');
            analysisTable.clear().draw();
        }
    });
}

function calculateAndDisplayStats(data) {
    var totalStudents = data.length;
    var totalBilled = 0;
    var totalPaid = 0;

    data.forEach(function(row) {
        totalBilled += parseFloat(row.total_billed) || 0;
        totalPaid += parseFloat(row.total_paid) || 0;
    });

    var collectionRate = totalBilled > 0 ? ((totalPaid / totalBilled) * 100).toFixed(1) : 0;

    $('#totalStudents').text(totalStudents);
    $('#totalBilled').text('₦' + totalBilled.toLocaleString());
    $('#totalPaid').text('₦' + totalPaid.toLocaleString());
    $('#collectionRate').text(collectionRate + '%');
}

function exportReport(format) {
    if (!currentFilters.class_id || !currentFilters.term_id || !currentFilters.session_id) {
        Swal.fire('Warning', 'Please load report data first', 'warning');
        return;
    }

    var url = '{{ route("reports.analysis.class.export") }}?class_id=' + currentFilters.class_id + '&term_id=' + currentFilters.term_id + '&session_id=' + currentFilters.session_id + '&format=' + format;
    window.open(url, '_blank');
}
</script>
@endsection
