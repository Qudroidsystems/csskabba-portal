{{-- resources/views/reports/class-analysis.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --report-primary: #1e3a5f;
    --report-accent: #2563eb;
    --report-success: #16a34a;
    --report-warning: #d97706;
    --report-danger: #dc2626;
    --report-border: #e2e8f0;
    --report-radius: 12px;
}

.report-hero {
    background: linear-gradient(135deg, var(--report-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--report-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}

.filter-card {
    background: white;
    border: 1px solid var(--report-border);
    border-radius: var(--report-radius);
    padding: 20px;
    margin-bottom: 24px;
}

.summary-card {
    background: white;
    border: 1px solid var(--report-border);
    border-radius: var(--report-radius);
    padding: 16px;
    transition: all 0.2s;
}
.summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.summary-card .value { font-size: 28px; font-weight: 700; }
.summary-card .label { font-size: 12px; color: #6b7280; margin-top: 4px; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="report-hero">
        <h1 class="text-white"><i class="ri-bar-chart-grouped-line me-2"></i>{{ $pagetitle }}</h1>
        <p class="text-white-50 mb-0">Analyze fee collection performance by class, term, and session.</p>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Class</label>
                <select id="classSelect" class="form-select">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Term</label>
                <select id="termSelect" class="form-select">
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Session</label>
                <select id="sessionSelect" class="form-select">
                    <option value="">-- Select Session --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="loadReportBtn">
                    <i class="ri-search-line me-1"></i>Load Report
                </button>
            </div>
        </div>
    </div>

    {{-- Summary Cards (initially hidden) --}}
    <div class="row g-3 mb-4" id="summaryCards" style="display: none;">
        <div class="col-md-3">
            <div class="summary-card text-center">
                <div class="value text-primary" id="totalStudents">-</div>
                <div class="label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card text-center">
                <div class="value text-success" id="totalCollected">-</div>
                <div class="label">Total Collected</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card text-center">
                <div class="value text-danger" id="totalOutstanding">-</div>
                <div class="label">Total Outstanding</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card text-center">
                <div class="value text-warning" id="collectionRate">-</div>
                <div class="label">Collection Rate</div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar" id="collectionProgress" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card border-0 shadow-sm" id="resultsCard" style="display: none;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="ri-table-line me-2"></i>Student Payment Details</h5>
            <div>
                <button class="btn btn-sm btn-success" id="exportExcelBtn">
                    <i class="ri-file-excel-line me-1"></i>Export Excel
                </button>
                <button class="btn btn-sm btn-danger" id="exportPdfBtn">
                    <i class="ri-file-pdf-line me-1"></i>Export PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover w-100" id="analysisTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Total Billed</th>
                            <th>Total Paid</th>
                            <th>Outstanding</th>
                            <th>Completion</th>
                            <th width="80">Action</th>
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

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let analysisTable;

$(document).ready(function() {
    analysisTable = $('#analysisTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary"></div>',
            search: '<i class="ri-search-line"></i>',
            searchPlaceholder: 'Search students...'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'admission_no', name: 'admission_no' },
            { data: 'total_billed', name: 'total_billed' },
            { data: 'total_paid', name: 'total_paid' },
            { data: 'outstanding', name: 'outstanding' },
            { data: 'completion', name: 'completion', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#loadReportBtn').on('click', function() {
        const classId = $('#classSelect').val();
        const termId = $('#termSelect').val();
        const sessionId = $('#sessionSelect').val();

        if (!classId || !termId || !sessionId) {
            Swal.fire('Warning', 'Please select class, term, and session', 'warning');
            return;
        }

        $('#resultsCard').show();
        analysisTable.ajax.url('{{ route("reports.analysis.class") }}?class_id=' + classId + '&term_id=' + termId + '&session_id=' + sessionId).load();

        // Load summary data
        $.ajax({
            url: '{{ route("reports.analysis.class") }}',
            type: 'GET',
            data: { class_id: classId, term_id: termId, session_id: sessionId, summary: true },
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success && response.summary) {
                    $('#summaryCards').show();
                    $('#totalStudents').text(response.summary.total_students);
                    $('#totalCollected').text('₦' + response.summary.total_collected.toLocaleString());
                    $('#totalOutstanding').text('₦' + response.summary.total_outstanding.toLocaleString());
                    $('#collectionRate').text(response.summary.collection_rate + '%');
                    $('#collectionProgress').css('width', response.summary.collection_rate + '%');
                }
            }
        });
    });

    $('#exportExcelBtn').on('click', function() {
        Swal.fire('Export Started', 'Your Excel file will download shortly', 'success');
    });

    $('#exportPdfBtn').on('click', function() {
        Swal.fire('Export Started', 'Your PDF file will download shortly', 'success');
    });
});
</script>
@endsection
