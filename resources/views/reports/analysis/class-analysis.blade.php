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

/* Loading Overlay - Apple Style */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}
.loading-overlay.active {
    display: flex;
}
.loading-content {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 30px 40px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 15px;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.loading-text {
    font-size: 14px;
    color: #1e3a5f;
    font-weight: 500;
}

.report-hero {
    background: linear-gradient(135deg, var(--report-primary) 0%, var(--report-accent) 60%, #4f46e5 100%);
    border-radius: var(--report-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.report-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.report-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.report-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

.stat-card {
    background: #fff;
    border: 1px solid var(--report-border);
    border-radius: var(--report-radius);
    padding: 18px 20px;
    transition: transform .15s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--report-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
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
    color: var(--report-primary);
}
.filter-label .required {
    color: #dc2626;
}

.report-table th {
    background: var(--report-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 13px;
    white-space: nowrap;
}
.report-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--report-border);
    vertical-align: middle;
}
.report-table tr:hover td {
    background: #f0f9ff;
}

/* Status Badges */
.status-paid {
    background: #dcfce7;
    color: #16a34a;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}
.status-partial {
    background: #fef3c7;
    color: #d97706;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}
.status-unpaid {
    background: #fee2e2;
    color: #dc2626;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

/* Benefit Badges */
.benefit-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}
.benefit-scholarship {
    background: #fef3c7;
    color: #d97706;
}
.benefit-discount {
    background: #ede9fe;
    color: #6d28d9;
}

/* Progress Bar */
.progress-container {
    width: 80px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 0 auto;
}
.progress-fill {
    height: 5px;
    border-radius: 10px;
}
.progress-high { background: #16a34a; width: 100%; }
.progress-medium { background: #d97706; width: 100%; }
.progress-low { background: #dc2626; width: 100%; }
.progress-text {
    font-size: 10px;
    color: #6b7280;
    margin-top: 2px;
    display: block;
}

/* Student Avatar */
.student-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    cursor: pointer;
}
.student-avatar-placeholder {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

/* Popover Styles */
#studentPopover {
    position: fixed;
    z-index: 99999;
    pointer-events: none;
    opacity: 0;
    transform: scale(0.92) translateY(6px);
    transition: opacity 0.22s cubic-bezier(.4,0,.2,1),
                transform 0.22s cubic-bezier(.4,0,.2,1);
}
#studentPopover.visible {
    opacity: 1;
    transform: scale(1) translateY(0);
    pointer-events: none;
}
.popover-card {
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 0 0 0.5px rgba(0,0,0,0.08), 0 8px 32px rgba(0,0,0,0.14);
    width: 280px;
    overflow: hidden;
}
.popover-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    padding: 16px;
    position: relative;
}
.popover-avatar-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}
.popover-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.9);
}
.popover-name {
    font-size: 14px;
    font-weight: 700;
    color: white;
}
.popover-adm {
    font-size: 10px;
    color: rgba(255,255,255,0.75);
}
.popover-body {
    padding: 12px 16px;
}
.popover-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}
.popover-stat {
    background: #f8fafc;
    border-radius: 10px;
    padding: 8px;
    text-align: center;
}
.popover-stat-val {
    font-size: 14px;
    font-weight: 700;
    color: #1e3a5f;
}
.popover-stat-lbl {
    font-size: 9px;
    color: #9ca3af;
}
.popover-subject-list {
    max-height: 150px;
    overflow-y: auto;
}
.popover-subject-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 8px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 11px;
}
.popover-arrow {
    position: absolute;
    width: 12px;
    height: 12px;
    background: rgba(255,255,255,0.96);
    transform: rotate(45deg);
    border-radius: 2px;
}
.popover-arrow.arrow-top { top: -5px; left: 50%; transform: translateX(-50%) rotate(45deg); }
.popover-arrow.arrow-bottom { bottom: -5px; left: 50%; transform: translateX(-50%) rotate(45deg); }

/* DataTables */
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--report-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background: var(--report-accent) !important;
    border-color: var(--report-accent) !important;
    color: #fff !important;
}

/* Image Zoom Modal */
.image-zoom-modal .modal-content {
    background: transparent;
    border: none;
}
.zoomed-image {
    max-width: 90vw;
    max-height: 75vh;
    border-radius: 16px;
    border: 4px solid white;
    cursor: pointer;
}
.btn-close-zoom {
    position: absolute;
    top: 20px;
    right: 30px;
    background: rgba(0,0,0,.7);
    border: none;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    color: white;
    font-size: 18px;
    cursor: pointer;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <!-- Apple-style Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading payment data...</div>
        </div>
    </div>

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

    <div class="row g-3 mb-4" id="statsRow">
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

    <div class="card border-0 shadow-sm">
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
                            <th>Gender</th>
                            <th>Benefits</th>
                            <th class="text-end">Total Billed (₦)</th>
                            <th class="text-end">Total Paid (₦)</th>
                            <th class="text-end">Outstanding (₦)</th>
                            <th width="100">Progress</th>
                            <th width="100">Status</th>
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

<!-- Popover -->
<div id="studentPopover">
    <div class="popover-card">
        <div class="popover-arrow" id="popoverArrow"></div>
        <div class="popover-header">
            <div class="popover-avatar-wrapper">
                <img id="popAvatar" src="" alt="" class="popover-avatar">
                <div>
                    <div class="popover-name" id="popName">—</div>
                    <div class="popover-adm" id="popAdm">—</div>
                </div>
            </div>
        </div>
        <div class="popover-body">
            <div class="popover-stats-grid">
                <div class="popover-stat"><span class="popover-stat-val" id="popBilled">—</span><span class="popover-stat-lbl">Billed</span></div>
                <div class="popover-stat"><span class="popover-stat-val" id="popPaid">—</span><span class="popover-stat-lbl">Paid</span></div>
                <div class="popover-stat"><span class="popover-stat-val" id="popOutstanding">—</span><span class="popover-stat-lbl">Owing</span></div>
            </div>
            <div class="popover-subject-list" id="popBillList"></div>
        </div>
    </div>
</div>

<!-- Image Zoom Modal -->
<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <button class="btn-close-zoom" data-bs-dismiss="modal">×</button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" class="zoomed-image">
                <div class="zoomed-image-name" id="zoomedImageName" style="color:white; margin-top:18px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let analysisTable;
let currentFilters = {};
let studentData = {};

function showLoading(show) {
    if (show) {
        $('#loadingOverlay').addClass('active');
    } else {
        $('#loadingOverlay').removeClass('active');
    }
}

function formatMoney(n) {
    return '₦' + (parseFloat(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 }));
}

function getInitials(name) {
    if (!name) return 'ST';
    return name.split(' ').slice(0,2).map(w => w[0] || '').join('').toUpperCase();
}

function getAvatarUrl(picture) {
    if (!picture || picture === 'unnamed.jpg') return null;
    return '/storage/images/student_avatars/' + picture;
}

function getProgressClass(percentage) {
    if (percentage >= 70) return 'progress-high';
    if (percentage >= 40) return 'progress-medium';
    return 'progress-low';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m];
    });
}

function renderAvatar(avatar, name, admission) {
    const avatarUrl = avatar ? getAvatarUrl(avatar) : null;
    const initials = getInitials(name);
    const escapedName = escapeHtml(name);
    const escapedAdmission = escapeHtml(admission);

    if (avatarUrl) {
        return `<img src="${avatarUrl}" class="student-avatar" data-name="${escapedName}" data-admission="${escapedAdmission}">`;
    }
    return `<div class="student-avatar-placeholder" data-name="${escapedName}" data-admission="${escapedAdmission}">${initials}</div>`;
}

function renderBenefits(hasScholarship, hasDiscount) {
    let html = '';
    if (hasScholarship) html += '<span class="benefit-badge benefit-scholarship me-1">🎓 Scholarship</span> ';
    if (hasDiscount) html += '<span class="benefit-badge benefit-discount">🏷️ Discount</span> ';
    if (!hasScholarship && !hasDiscount) html = '<span class="text-muted">—</span>';
    return html;
}

function renderStatus(status) {
    if (status === 'Fully Paid') {
        return '<span class="status-paid"><i class="ri-checkbox-circle-line me-1"></i>Fully Paid</span>';
    } else if (status === 'Partial') {
        return '<span class="status-partial"><i class="ri-time-line me-1"></i>Partial</span>';
    } else {
        return '<span class="status-unpaid"><i class="ri-error-warning-line me-1"></i>Unpaid</span>';
    }
}

function renderProgress(completion) {
    const progressClass = getProgressClass(completion);
    return `<div class="progress-container"><div class="progress-fill ${progressClass}" style="width: ${completion}%"></div></div><span class="progress-text">${completion}%</span>`;
}

function updateStats(response) {
    let totalBilled = 0, totalPaid = 0, totalOutstanding = 0;

    if (response.data && response.data.length) {
        response.data.forEach(row => {
            totalBilled += parseFloat(row.total_billed) || 0;
            totalPaid += parseFloat(row.total_paid) || 0;
            totalOutstanding += parseFloat(row.outstanding) || 0;
        });
    }

    const collectionRate = totalBilled > 0 ? ((totalPaid / totalBilled) * 100).toFixed(1) : 0;

    $('#totalStudents').text(response.recordsTotal || 0);
    $('#totalBilled').text(formatMoney(totalBilled));
    $('#totalPaid').text(formatMoney(totalPaid));
    $('#collectionRate').text(collectionRate + '%');
}

// Popover Functions
function fillPopover(studentId) {
    const s = studentData[studentId];
    if (!s) return;

    $('#popName').text(s.name);
    $('#popAdm').text('Adm: ' + s.admission);
    $('#popBilled').text(formatMoney(s.billed));
    $('#popPaid').text(formatMoney(s.paid));
    $('#popOutstanding').text(formatMoney(s.outstanding));

    if (s.avatar) $('#popAvatar').attr('src', s.avatar);
    else $('#popAvatar').attr('src', '');

    const list = $('#popBillList');
    list.empty();
    if (s.bills && s.bills.length) {
        s.bills.forEach(bill => {
            list.append(`<div class="popover-subject-row"><span>${escapeHtml(bill.title)}</span><span class="fw-bold">${formatMoney(bill.balance)}</span></div>`);
        });
    } else {
        list.append('<div class="popover-subject-row text-muted">No bills available</div>');
    }
}

function positionPopover(e) {
    const vw = window.innerWidth, vh = window.innerHeight;
    const pw = 280, ph = 320;
    let left = e.clientX + 16, top = e.clientY - 20;
    const arrow = document.getElementById('popoverArrow');

    if (left + pw > vw) left = e.clientX - pw + 4;
    if (top + ph > vh) { top = e.clientY - ph + 20; arrow.className = 'popover-arrow arrow-bottom'; }
    else { arrow.className = 'popover-arrow arrow-top'; }

    left = Math.max(8, Math.min(left, vw - pw - 8));
    top = Math.max(8, Math.min(top, vh - ph - 8));
    popover.style.left = left + 'px';
    popover.style.top = top + 'px';
}

function showPopover(row, e) {
    clearTimeout(hideTimer);
    const studentId = row.attr('data-student-id');
    if (!studentId || !studentData[studentId]) return;
    fillPopover(studentId);
    positionPopover(e);
    popover.classList.add('visible');
}

function hidePopover() {
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => popover.classList.remove('visible'), 180);
}

let popoverTimer, hideTimer;
const popover = document.getElementById('studentPopover');

function attachPopoverEvents() {
    $('#analysisTable tbody tr').off('mouseenter mouseleave mousemove').on('mouseenter', function(e) {
        clearTimeout(popoverTimer);
        const $row = $(this);
        popoverTimer = setTimeout(() => showPopover($row, e), 280);
    }).on('mousemove', function(e) {
        if (popover.classList.contains('visible')) positionPopover(e);
    }).on('mouseleave', function() {
        clearTimeout(popoverTimer);
        hidePopover();
    });
}

$(document).ready(function() {
    analysisTable = $('#analysisTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("reports.analysis.class-data") }}',
            type: 'GET',
            data: function(d) {
                d.class_id = currentFilters.class_id;
                d.term_id = currentFilters.term_id;
                d.session_id = currentFilters.session_id;
            },
            beforeSend: function() {
                showLoading(true);
            },
            complete: function() {
                showLoading(false);
            },
            dataSrc: function(response) {
                // Store student data for popover
                if (response.data && response.data.length) {
                    response.data.forEach(item => {
                        studentData[item.student_id] = {
                            name: item.student_name,
                            admission: item.admission_no,
                            avatar: item.avatar ? getAvatarUrl(item.avatar) : null,
                            billed: item.total_billed,
                            paid: item.total_paid,
                            outstanding: item.outstanding,
                            bills: item.bills || []
                        };
                    });
                }
                return response.data;
            }
        },
        columns: [
            {
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return renderAvatar(row.avatar, row.student_name, row.admission_no);
                }
            },
            { data: 'student_name' },
            { data: 'admission_no' },
            { data: 'gender' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return renderBenefits(row.has_scholarship, row.has_discount);
                }
            },
            {
                data: 'total_billed',
                className: 'text-end',
                render: function(data) { return formatMoney(data); }
            },
            {
                data: 'total_paid',
                className: 'text-end',
                render: function(data) { return formatMoney(data); }
            },
            {
                data: 'outstanding',
                className: 'text-end',
                render: function(data) { return formatMoney(data); }
            },
            {
                data: 'completion',
                render: function(data) { return renderProgress(data); }
            },
            {
                data: 'status',
                render: function(data) { return renderStatus(data); }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return '<a href="/reports/analysis/student/' + row.student_id + '/' + currentFilters.class_id + '/' + currentFilters.term_id + '/' + currentFilters.session_id + '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-eye-line"></i></a>';
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            const response = api.ajax.json();
            if (response) {
                updateStats(response);
            }
            attachPopoverEvents();
        },
        language: {
            emptyTable: '<div class="text-center py-5 text-muted">No data available. Please select filters and click Load Report.</div>',
            processing: '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading...</p></div>'
        },
        pageLength: 25,
        order: [[8, 'desc']]
    });

    $('#loadReportBtn').on('click', function() {
        const classId = $('#class_id').val();
        const termId = $('#term_id').val();
        const sessionId = $('#session_id').val();

        if (!classId || !termId || !sessionId) {
            Swal.fire('Warning', 'Please select class, term, and session', 'warning');
            return;
        }

        currentFilters = {
            class_id: classId,
            term_id: termId,
            session_id: sessionId
        };

        analysisTable.ajax.reload();
    });

    $('#resetBtn').on('click', function() {
        $('#class_id, #term_id, #session_id').val('');
        currentFilters = {};
        studentData = {};
        analysisTable.ajax.reload();
    });
});

function exportReport(format) {
    if (!currentFilters.class_id) {
        Swal.fire('Warning', 'Please load report data first', 'warning');
        return;
    }
    const url = '{{ route("reports.analysis.export") }}?class_id=' + currentFilters.class_id + '&term_id=' + currentFilters.term_id + '&session_id=' + currentFilters.session_id + '&format=' + format;
    window.open(url, '_blank');
}

// Image zoom on avatar click
$(document).on('click', '.student-avatar, .student-avatar-placeholder', function() {
    const imageUrl = $(this).is('img') ? $(this).attr('src') : null;
    const name = $(this).data('name');
    const admission = $(this).data('admission');

    $('#zoomedImageName').text(name + ' (' + admission + ')');
    if (imageUrl) {
        $('#zoomedImage').attr('src', imageUrl);
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
        ctx.fillRect(0, 0, 400, 400);
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 160px "DM Sans", Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(initials.substring(0, 2), 200, 200);
        $('#zoomedImage').attr('src', canvas.toDataURL());
    }
    $('#imageZoomModal').modal('show');
});

$(document).on('click', '.zoomed-image', function() {
    $('#imageZoomModal').modal('hide');
});
</script>
@endsection
