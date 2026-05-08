@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@include('components.report-styles')

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero Banner --}}
    <div class="report-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="ri-bar-chart-line me-2"></i>Class Analysis Report</h1>
                <p>Analyze fee collection by class, term, and session</p>
            </div>
            <div class="d-flex gap-2 flex-wrap" style="position:relative;z-index:1">
                <button class="btn btn-light btn-sm" onclick="exportReport('excel')">
                    <i class="ri-file-excel-line"></i> Excel
                </button>
                <button class="btn btn-light btn-sm" onclick="exportReport('pdf')">
                    <i class="ri-file-pdf-line"></i> PDF
                </button>
                <button class="btn btn-light btn-sm" onclick="window.print()">
                    <i class="ri-printer-line"></i> Print
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4" id="statsRow" style="display: none;">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value" id="totalStudents">0</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-money-dollar-circle-line"></i></div>
                <div class="stat-value text-success" id="totalBilled">₦0</div>
                <div class="stat-label">Total Billed</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-wallet-line"></i></div>
                <div class="stat-value text-info" id="totalPaid">₦0</div>
                <div class="stat-label">Total Paid</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-percent-line"></i></div>
                <div class="stat-value text-warning" id="collectionRate">0%</div>
                <div class="stat-label">Collection Rate</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="filter-label">Class <span class="required">*</span></label>
                <select class="form-select" id="class_id">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Term <span class="required">*</span></label>
                <select class="form-select" id="term_id">
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Session <span class="required">*</span></label>
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

    {{-- Progress Summary Card --}}
    <div class="card border-0 shadow-sm mb-4" id="summaryCard" style="display: none;">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold"><i class="ri-bar-chart-line me-2"></i>Collection Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="text-muted small">Collection Progress</label>
                    <div class="completion-progress mt-1">
                        <div class="progress">
                            <div id="collectionProgressBar" class="progress-bar high" style="width: 0%"></div>
                        </div>
                        <span id="progressText">0%</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold text-primary fs-4" id="summaryStudents">0</div>
                            <small class="text-muted">Total Students</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success fs-4" id="summaryFullyPaid">0</div>
                            <small class="text-muted">Fully Paid</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning fs-4" id="summaryPartial">0</div>
                            <small class="text-muted">Partial</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm" id="tableCard" style="display: none;">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold"><i class="ri-table-line me-2"></i>Student Payment Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table report-table w-100" id="analysisTable">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th width="60">Photo</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th class="text-end">Total Billed (₦)</th>
                            <th class="text-end">Total Paid (₦)</th>
                            <th class="text-end">Outstanding (₦)</th>
                            <th width="120">Completion</th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="9" class="text-center py-5 text-muted">Select class, term, and session to view data</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- Image Zoom Modal --}}
<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <button class="btn-close-zoom" data-bs-dismiss="modal"><i class="ri-close-line"></i></button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" alt="Student Photo" class="zoomed-image">
                <div class="zoomed-image-name" id="zoomedImageName"></div>
                <div class="zoomed-image-details" id="zoomedImageDetails"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 });
}
function naira(n) { return '₦' + fmt(n); }

function getInitials(name) {
    if (!name) return 'ST';
    return name.split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase();
}

function getAvatarUrl(picture) {
    if (!picture || picture === 'unnamed.jpg' || picture === '') return null;
    return '/storage/images/student_avatars/' + picture;
}

function showZoomModal(imageUrl, name, admission, studentClass) {
    $('#zoomedImageName').text(name || 'Student Photo');
    $('#zoomedImageDetails').html(`<i class="ri-honour-line me-1"></i>${admission || 'N/A'} &nbsp;|&nbsp; <i class="ri-building-line me-1"></i>${studentClass || 'N/A'}`);

    if (imageUrl && imageUrl !== '' && imageUrl !== 'null') {
        $('#zoomedImage').attr('src', imageUrl).show();
    } else {
        const initials = getInitials(name);
        const canvas = document.createElement('canvas');
        canvas.width = 400;
        canvas.height = 400;
        const ctx = canvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 400, 400);
        grad.addColorStop(0, '#2563eb');
        grad.addColorStop(1, '#7c3aed');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(200, 200, 200, 0, 2 * Math.PI);
        ctx.fill();
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 150px "DM Sans", Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(initials, 200, 200);
        $('#zoomedImage').attr('src', canvas.toDataURL()).show();
    }

    new bootstrap.Modal(document.getElementById('imageZoomModal')).show();
}

let analysisTable;
let currentFilters = {};

$(document).ready(function() {
    initializeDataTable();

    $('#loadReportBtn').on('click', function() {
        const classId = $('#class_id').val();
        const termId = $('#term_id').val();
        const sessionId = $('#session_id').val();

        if (!classId || !termId || !sessionId) {
            Swal.fire('Warning', 'Please select class, term, and session', 'warning');
            return;
        }

        currentFilters = { class_id: classId, term_id: termId, session_id: sessionId };
        loadReportData();
    });

    $('#resetBtn').on('click', function() {
        $('#class_id, #term_id, #session_id').val('');
        $('#statsRow, #summaryCard, #tableCard').hide();
        if (analysisTable) analysisTable.clear().draw();
    });
});

function initializeDataTable() {
    analysisTable = $('#analysisTable').DataTable({
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'avatar', orderable: false, searchable: false, width: '60px' },
            { data: 'student_name' },
            { data: 'admission_no' },
            { data: 'total_billed', className: 'text-end' },
            { data: 'total_paid', className: 'text-end' },
            { data: 'outstanding', className: 'text-end' },
            { data: 'completion', orderable: false },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        pageLength: 25,
        language: {
            emptyTable: '<div class="text-center py-5 text-muted">No data available</div>',
            processing: '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading...</p></div>'
        }
    });
}

function loadReportData() {
    analysisTable.clear().draw();
    $('#statsRow, #summaryCard, #tableCard').hide();

    $.ajax({
        url: '{{ route("reports.analysis.class") }}',
        type: 'GET',
        data: { ...currentFilters, _: Date.now() },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                const dataWithIndex = response.data.map((item, index) => ({
                    DT_RowIndex: index + 1,
                    student_name: item.student_name,
                    admission_no: item.admission_no,
                    total_billed: '₦' + fmt(item.total_billed),
                    total_paid: '₦' + fmt(item.total_paid),
                    outstanding: '₦' + fmt(item.outstanding),
                    completion: `<div class="completion-progress">
                        <div class="progress"><div class="progress-bar ${getProgressClass(item.completion)}" style="width: ${item.completion}%"></div></div>
                        <span>${item.completion}%</span>
                    </div>`,
                    avatar: renderAvatar(item),
                    action: `<a href="/reports/analysis/student/${item.student_id}/${currentFilters.class_id}/${currentFilters.term_id}/${currentFilters.session_id}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-eye-line"></i></a>`
                }));

                analysisTable.clear();
                analysisTable.rows.add(dataWithIndex);
                analysisTable.draw();

                updateStats(response.data);
                $('#statsRow, #summaryCard, #tableCard').show();
            } else {
                analysisTable.clear().draw();
                Swal.fire('Info', 'No data found', 'info');
                $('#statsRow, #summaryCard, #tableCard').show();
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load data', 'error');
        }
    });
}

function renderAvatar(item) {
    const initials = getInitials(item.student_name);
    const avatarUrl = item.avatar ? getAvatarUrl(item.avatar) : null;

    if (avatarUrl) {
        return `<img src="${avatarUrl}" class="student-avatar" data-name="${escapeHtml(item.student_name)}" data-admission="${escapeHtml(item.admission_no)}" data-class="${escapeHtml(item.class_name || '')}" style="cursor:pointer;">`;
    }
    return `<div class="student-avatar-placeholder" data-name="${escapeHtml(item.student_name)}" data-admission="${escapeHtml(item.admission_no)}" data-class="${escapeHtml(item.class_name || '')}">${initials}</div>`;
}

function getProgressClass(percentage) {
    if (percentage >= 70) return 'high';
    if (percentage >= 40) return 'medium';
    return 'low';
}

function updateStats(data) {
    let totalBilled = 0, totalPaid = 0, fullyPaid = 0, partial = 0;

    data.forEach(item => {
        totalBilled += parseFloat(item.total_billed) || 0;
        totalPaid += parseFloat(item.total_paid) || 0;
        if (parseFloat(item.outstanding) <= 0) fullyPaid++;
        else if (parseFloat(item.total_paid) > 0) partial++;
    });

    const rate = totalBilled > 0 ? ((totalPaid / totalBilled) * 100).toFixed(1) : 0;

    $('#totalStudents').text(data.length);
    $('#totalBilled').text(naira(totalBilled));
    $('#totalPaid').text(naira(totalPaid));
    $('#collectionRate').text(rate + '%');
    $('#summaryStudents').text(data.length);
    $('#summaryFullyPaid').text(fullyPaid);
    $('#summaryPartial').text(partial);
    $('#collectionProgressBar').css('width', rate + '%');
    $('#progressText').text(rate + '%');
}

$(document).on('click', '.student-avatar, .student-avatar-placeholder', function() {
    const name = $(this).data('name');
    const admission = $(this).data('admission');
    const studentClass = $(this).data('class');
    const imageUrl = $(this).is('img') ? $(this).attr('src') : null;
    showZoomModal(imageUrl, name, admission, studentClass);
});

$(document).on('click', '.zoomed-image', function() {
    bootstrap.Modal.getInstance(document.getElementById('imageZoomModal'))?.hide();
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[m]);
}

function exportReport(format) {
    if (!currentFilters.class_id) {
        Swal.fire('Warning', 'Please load report data first', 'warning');
        return;
    }
    window.open('{{ route("reports.analysis.class.export") }}?class_id=' + currentFilters.class_id + '&term_id=' + currentFilters.term_id + '&session_id=' + currentFilters.session_id + '&format=' + format, '_blank');
}
</script>
@endsection
