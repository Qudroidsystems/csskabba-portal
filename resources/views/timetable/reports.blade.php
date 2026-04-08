{{-- resources/views/timetable/reports.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="ri-bar-chart-2-line me-2"></i>{{ $pagetitle }}
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Timetable Reports</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Report Generation Form --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Generate Report</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Report Type</label>
                                    <select class="form-select" id="reportType">
                                        <option value="teacher_workload">Teacher Workload Report</option>
                                        <option value="room_utilization">Room Utilization Report</option>
                                        <option value="class_schedule">Class Schedule Summary</option>
                                        <option value="conflict_analysis">Conflict Analysis Report</option>
                                        <option value="subject_distribution">Subject Distribution Report</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Session</label>
                                    <select class="form-select" id="reportSessionId">
                                        <option value="">Current Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Term</label>
                                    <select class="form-select" id="reportTermId">
                                        <option value="">All Terms</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Format</label>
                                    <select class="form-select" id="reportFormat">
                                        <option value="json">JSON (Preview)</option>
                                        <option value="csv">CSV (Download)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button class="btn btn-primary" onclick="generateReport()">
                                        <i class="ri-file-chart-line me-2"></i>Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Report Preview --}}
            <div class="row mt-3" id="reportPreview" style="display: none;">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Report Preview</h5>
                            <button class="btn btn-sm btn-success" id="downloadReportBtn" onclick="downloadReport()">
                                <i class="ri-download-line me-1"></i>Download CSV
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" id="reportTableContainer">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2">Loading report...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Saved Reports --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Saved Reports</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Report Name</th>
                                            <th>Type</th>
                                            <th>Session</th>
                                            <th>Term</th>
                                            <th>Generated By</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reports as $report)
                                        <tr>
                                            <td class="fw-medium">{{ $report->report_name }}</td>
                                            <td>
                                                @php
                                                    $typeNames = [
                                                        'teacher_workload' => 'Teacher Workload',
                                                        'room_utilization' => 'Room Utilization',
                                                        'class_schedule' => 'Class Schedule',
                                                        'conflict_analysis' => 'Conflict Analysis',
                                                        'subject_distribution' => 'Subject Distribution'
                                                    ];
                                                @endphp
                                                {{ $typeNames[$report->report_type] ?? $report->report_type }}
                                            </td>
                                            <td>{{ $report->session->session ?? 'N/A' }}</td>
                                            <td>{{ $report->term->term ?? 'All' }}</td>
                                            <td>{{ $report->generator->name ?? 'N/A' }}</td>
                                            <td>{{ $report->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewSavedReport({{ $report->id }})" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="downloadSavedReport({{ $report->id }})" title="Download">
                                                    <i class="ri-download-line"></i>
                                                </button>
                                                @can('Delete timetable reports')
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReport({{ $report->id }})" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                {{ $reports->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    let currentReportData = null;
    let currentReportId = null;

    // Define routes using url() for routes with parameters
    const REPORT_ROUTES = {
        generate: '{{ route("timetable.reports.generate") }}',
        download: '{{ url("/timetable-reports/download") }}',
        show: '{{ url("/timetable-reports") }}',
        destroy: '{{ url("/timetable-reports") }}'
    };

    function generateReport() {
        const reportType = document.getElementById('reportType').value;
        const sessionId = document.getElementById('reportSessionId').value;
        const termId = document.getElementById('reportTermId').value;
        const format = document.getElementById('reportFormat').value;

        Swal.fire({
            title: 'Generating Report...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(REPORT_ROUTES.generate, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                report_type: reportType,
                session_id: sessionId || null,
                term_id: termId || null,
                format: format
            })
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();

            if (data.success) {
                currentReportData = data.data;
                currentReportId = data.report?.id;

                if (format === 'csv') {
                    // FIXED: Use url() helper instead of route()
                    window.location.href = REPORT_ROUTES.download + '/' + data.report.id;
                } else {
                    displayReportPreview(data.data, reportType);
                    document.getElementById('reportPreview').style.display = 'block';
                }

                Swal.fire('Success', 'Report generated successfully', 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error', 'Failed to generate report', 'error');
        });
    }

    function displayReportPreview(data, reportType) {
        const container = document.getElementById('reportTableContainer');

        if (!data || !data.length) {
            container.innerHTML = '<div class="text-center text-muted py-5">No data available for this report</div>';
            return;
        }

        const headers = Object.keys(data[0]);

        let html = '<table class="table table-bordered table-hover">';
        html += '<thead class="table-light"><tr>';
        headers.forEach(header => {
            html += `<th>${header.replace(/_/g, ' ').toUpperCase()}</th>`;
        });
        html += '</tr></thead><tbody>';

        data.forEach(row => {
            html += '<tr>';
            headers.forEach(header => {
                let value = row[header];
                if (typeof value === 'object') {
                    value = JSON.stringify(value);
                }
                html += `<td>${value !== null && value !== undefined ? value : '—'}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function downloadReport() {
        if (currentReportId) {
            // FIXED: Use url() helper instead of route()
            window.location.href = REPORT_ROUTES.download + '/' + currentReportId;
        } else {
            generateReport();
        }
    }

    function viewSavedReport(id) {
        fetch(REPORT_ROUTES.show + '/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayReportPreview(data.report.data, data.report.report_type);
                    document.getElementById('reportPreview').style.display = 'block';
                    Swal.fire('Success', 'Report loaded successfully', 'success');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    }

    function downloadSavedReport(id) {
        // FIXED: Use url() helper instead of route()
        window.location.href = REPORT_ROUTES.download + '/' + id;
    }

    function deleteReport(id) {
        Swal.fire({
            title: 'Delete Report?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(REPORT_ROUTES.destroy + '/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>

@endsection
