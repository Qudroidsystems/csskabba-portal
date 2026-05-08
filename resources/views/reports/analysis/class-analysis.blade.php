@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --report-primary: #1e3a5f;
    --report-accent: #2563eb;
    --report-success: #16a34a;
    --report-warning: #d97706;
    --report-border: #e2e8f0;
    --report-radius: 12px;
}

.report-hero {
    background: linear-gradient(135deg, var(--report-primary) 0%, var(--report-accent) 60%, #4f46e5 100%);
    border-radius: var(--report-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}
.report-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0; }
.report-hero p { font-size: 13px; color: rgba(255,255,255,.75); margin: 5px 0 0; }

.stat-card {
    background: #fff;
    border: 1px solid var(--report-border);
    border-radius: var(--report-radius);
    padding: 18px 20px;
    transition: transform .15s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--report-primary); }
.stat-card .stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }

.filter-bar {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
}
.filter-label { font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--report-primary); }
.filter-label .required { color: #dc2626; }

.report-table th {
    background: var(--report-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 13px;
}
.report-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--report-border);
}

.completion-progress {
    display: flex;
    align-items: center;
    gap: 8px;
}
.completion-progress .progress {
    flex: 1;
    height: 6px;
    border-radius: 10px;
    background: #e2e8f0;
    overflow: hidden;
}
.completion-progress .progress-bar.high { background: linear-gradient(90deg, #16a34a, #22c55e); }
.completion-progress .progress-bar.medium { background: linear-gradient(90deg, #d97706, #f59e0b); }
.completion-progress .progress-bar.low { background: linear-gradient(90deg, #dc2626, #ef4444); }
.completion-progress span { font-size: 11px; font-weight: 600; min-width: 45px; }

.student-avatar, .student-avatar-placeholder {
    cursor: pointer;
    transition: transform 0.2s;
}
.student-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.student-avatar-placeholder {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.image-zoom-modal .modal-content { background: transparent; border: none; }
.zoomed-image { max-width: 90vw; max-height: 75vh; border-radius: 16px; border: 4px solid white; cursor: pointer; }
.btn-close-zoom {
    position: absolute; top: 20px; right: 30px;
    background: rgba(0,0,0,.7); border: none; border-radius: 50%;
    width: 38px; height: 38px; display: flex;
    align-items: center; justify-content: center;
    color: white; cursor: pointer;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="report-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="ri-bar-chart-line me-2"></i>Class Analysis Report</h1>
                <p>Analyze fee collection by class, term, and session</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light btn-sm" onclick="exportReport('excel')"><i class="ri-file-excel-line"></i> Excel</button>
                <button class="btn btn-light btn-sm" onclick="exportReport('pdf')"><i class="ri-file-pdf-line"></i> PDF</button>
                <button class="btn btn-light btn-sm" onclick="window.print()"><i class="ri-printer-line"></i> Print</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="statsRow" style="display: none;">
        <div class="col-md-3"><div class="stat-card"><div class="stat-value" id="totalStudents">0</div><div class="stat-label">Total Students</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value text-success" id="totalBilled">₦0</div><div class="stat-label">Total Billed</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value text-info" id="totalPaid">₦0</div><div class="stat-label">Total Paid</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value text-warning" id="collectionRate">0%</div><div class="stat-label">Collection Rate</div></div></div>
    </div>

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
                <button class="btn btn-primary" id="loadReportBtn"><i class="ri-search-line me-1"></i> Load Report</button>
                <button class="btn btn-secondary ms-2" id="resetBtn"><i class="ri-refresh-line me-1"></i> Reset</button>
            </div>
        </div>
    </div>

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
                    <tbody id="tableBody">
                        <tr class="text-center">
                            <td colspan="9" class="py-5 text-muted">Select class, term, and session to view data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <button class="btn-close-zoom" data-bs-dismiss="modal"><i class="ri-close-line"></i></button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" class="zoomed-image">
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
    return name.split(' ').slice(0,2).map(w => w[0] || '').join('').toUpperCase();
}
function getAvatarUrl(picture) {
    if (!picture || picture === 'unnamed.jpg' || picture === '') return null;
    return '/storage/images/student_avatars/' + picture;
}
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m];
    });
}

function showZoomModal(imageUrl, name, admission, studentClass) {
    $('#zoomedImageName').text(name || 'Student Photo');
    $('#zoomedImageDetails').html('<i class="ri-honour-line me-1"></i>' + (admission || 'N/A') + ' &nbsp;|&nbsp; <i class="ri-building-line me-1"></i>' + (studentClass || 'N/A'));

    if (imageUrl && imageUrl !== '' && imageUrl !== 'null') {
        $('#zoomedImage').attr('src', imageUrl).show();
    } else {
        var initials = getInitials(name);
        var canvas = document.createElement('canvas');
        canvas.width = 400;
        canvas.height = 400;
        var ctx = canvas.getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 400, 400);
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

var analysisTable;
var currentFilters = {};

$(document).ready(function() {
    // Initialize DataTable with EXACTLY 9 columns matching the header
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
            { data: 'action', orderable: false, searchable: false, className: 'text-center', width: '80px' }
        ],
        pageLength: 25,
        language: {
            emptyTable: '<div class="text-center py-5 text-muted">No data available</div>',
            processing: '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading...</p></div>'
        }
    });

    $('#loadReportBtn').on('click', function() {
        var classId = $('#class_id').val();
        var termId = $('#term_id').val();
        var sessionId = $('#session_id').val();

        if (!classId || !termId || !sessionId) {
            Swal.fire('Warning', 'Please select class, term, and session', 'warning');
            return;
        }

        currentFilters = { class_id: classId, term_id: termId, session_id: sessionId };
        loadReportData();
    });

    $('#resetBtn').on('click', function() {
        $('#class_id, #term_id, #session_id').val('');
        $('#statsRow, #tableCard').hide();
        analysisTable.clear().draw();
        $('#tableBody').html('<tr class="text-center"><td colspan="9" class="py-5 text-muted">Select class, term, and session to view data</td></tr>');
    });
});

function loadReportData() {
    analysisTable.clear().draw();
    $('#statsRow, #tableCard').hide();

    $.ajax({
        url: '{{ route("reports.analysis.class-data") }}',
        type: 'GET',
        data: currentFilters,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                var dataWithIndex = [];
                for (var i = 0; i < response.data.length; i++) {
                    var item = response.data[i];
                    dataWithIndex.push({
                        DT_RowIndex: i + 1,
                        student_name: item.student_name,
                        admission_no: item.admission_no,
                        total_billed: '₦' + fmt(item.total_billed),
                        total_paid: '₦' + fmt(item.total_paid),
                        outstanding: '₦' + fmt(item.outstanding),
                        completion: '<div class="completion-progress"><div class="progress"><div class="progress-bar ' + (item.completion >= 70 ? 'high' : (item.completion >= 40 ? 'medium' : 'low')) + '" style="width: ' + item.completion + '%"></div></div><span>' + item.completion + '%</span></div>',
                        avatar: renderAvatar(item),
                        action: '<a href="/reports/analysis/student/' + item.student_id + '/' + currentFilters.class_id + '/' + currentFilters.term_id + '/' + currentFilters.session_id + '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-eye-line"></i></a>'
                    });
                }
                analysisTable.clear();
                analysisTable.rows.add(dataWithIndex);
                analysisTable.draw();
                updateStats(response.data);
                $('#statsRow, #tableCard').show();
            } else {
                analysisTable.clear().draw();
                Swal.fire('Info', 'No data found for the selected filters', 'info');
                $('#statsRow, #tableCard').show();
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.responseText);
            Swal.fire('Error', 'Failed to load data. Please try again.', 'error');
            analysisTable.clear().draw();
        }
    });
}

function renderAvatar(item) {
    var initials = getInitials(item.student_name);
    var avatarUrl = item.avatar ? getAvatarUrl(item.avatar) : null;
    var name = escapeHtml(item.student_name);
    var admission = escapeHtml(item.admission_no);
    var className = escapeHtml(item.class_name || '');

    if (avatarUrl) {
        return '<img src="' + avatarUrl + '" class="student-avatar" data-name="' + name + '" data-admission="' + admission + '" data-class="' + className + '" style="cursor:pointer;">';
    }
    return '<div class="student-avatar-placeholder" data-name="' + name + '" data-admission="' + admission + '" data-class="' + className + '">' + initials + '</div>';
}

function updateStats(data) {
    var totalBilled = 0;
    var totalPaid = 0;

    for (var i = 0; i < data.length; i++) {
        var item = data[i];
        totalBilled += parseFloat(item.total_billed) || 0;
        totalPaid += parseFloat(item.total_paid) || 0;
    }

    var rate = totalBilled > 0 ? ((totalPaid / totalBilled) * 100).toFixed(1) : 0;

    $('#totalStudents').text(data.length);
    $('#totalBilled').text(naira(totalBilled));
    $('#totalPaid').text(naira(totalPaid));
    $('#collectionRate').text(rate + '%');
}

$(document).on('click', '.student-avatar, .student-avatar-placeholder', function() {
    var imageUrl = $(this).is('img') ? $(this).attr('src') : null;
    var name = $(this).data('name');
    var admission = $(this).data('admission');
    var studentClass = $(this).data('class');
    showZoomModal(imageUrl, name, admission, studentClass);
});

$(document).on('click', '.zoomed-image', function() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('imageZoomModal'));
    if (modal) modal.hide();
});

function exportReport(format) {
    if (!currentFilters.class_id) {
        Swal.fire('Warning', 'Please load report data first', 'warning');
        return;
    }
    var url = '{{ route("reports.analysis.export") }}?class_id=' + currentFilters.class_id + '&term_id=' + currentFilters.term_id + '&session_id=' + currentFilters.session_id + '&format=' + format;
    window.open(url, '_blank');
}
</script>
@endsection
