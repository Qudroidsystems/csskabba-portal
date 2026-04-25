{{-- resources/views/reports/class-analysis.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Class Analysis Report</h4>
            <p class="text-muted">Analyze fee collection by class, term, and session</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select class="form-select" id="classSelect">
                        <option value="">-- Select Class --</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Term</label>
                    <select class="form-select" id="termSelect">
                        <option value="">-- Select Term --</option>
                        @foreach($terms ?? [] as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Session</label>
                    <select class="form-select" id="sessionSelect">
                        <option value="">-- Select Session --</option>
                        @foreach($sessions ?? [] as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" id="loadReportBtn">
                        <i class="ri-search-line me-1"></i>Load Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4" id="reportCard" style="display: none;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Student Payment Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="analysisTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th class="text-end">Total Billed (₦)</th>
                            <th class="text-end">Total Paid (₦)</th>
                            <th class="text-end">Outstanding (₦)</th>
                            <th>Completion</th>
                            <th>Action</th>
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
<script>
$(document).ready(function() {
    var table = $('#analysisTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'admission_no', name: 'admission_no' },
            { data: 'total_billed', name: 'total_billed', className: 'text-end' },
            { data: 'total_paid', name: 'total_paid', className: 'text-end' },
            { data: 'outstanding', name: 'outstanding', className: 'text-end' },
            { data: 'completion', name: 'completion', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#loadReportBtn').on('click', function() {
        var classId = $('#classSelect').val();
        var termId = $('#termSelect').val();
        var sessionId = $('#sessionSelect').val();

        if (!classId || !termId || !sessionId) {
            Swal.fire('Warning', 'Please select class, term, and session', 'warning');
            return;
        }

        $('#reportCard').show();
        table.ajax.url('{{ route("reports.analysis.class") }}?class_id=' + classId + '&term_id=' + termId + '&session_id=' + sessionId).load();
    });
});
</script>
@endsection
