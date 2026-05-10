@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
/* ── Design System ────────────────────────────────────────────────── */
:root {
    --ss-primary:   #1e3a5f;
    --ss-accent:    #2563eb;
    --ss-success:   #16a34a;
    --ss-warning:   #d97706;
    --ss-danger:    #dc2626;
    --ss-muted:     #6b7280;
    --ss-border:    #e2e8f0;
    --ss-bg:        #f8fafc;
    --ss-card:      #ffffff;
    --ss-radius:    10px;
    --ss-shadow:    0 1px 4px rgba(0,0,0,.08);
}

/* Apple-style Loading Overlay */
#loadingOverlay {
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
#loadingOverlay.active {
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
    background: linear-gradient(135deg, var(--ss-primary) 0%, var(--ss-accent) 60%, #4f46e5 100%);
    border-radius: var(--ss-radius);
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
    background: var(--ss-card);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,.1);
}
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--ss-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: var(--ss-muted);
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
    color: var(--ss-primary);
}
.filter-label .required {
    color: #dc2626;
}

/* ── Report Table Styles ─────────────────────────────────────────── */
.report-table th {
    background: var(--ss-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 13px;
    white-space: nowrap;
    font-weight: 600;
}
.report-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--ss-border);
    vertical-align: middle;
}

/* Row Entrance Animation */
#analysisTable tbody tr {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                background 0.18s ease;
    will-change: opacity, transform;
}
#analysisTable tbody tr.row-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Row Hover Effects */
#analysisTable tbody tr:hover {
    background: #f0f6ff !important;
    box-shadow: inset 3px 0 0 #2563eb;
    transform: translateY(-1px) !important;
    transition: background 0.14s ease, box-shadow 0.18s ease, transform 0.18s cubic-bezier(0.34, 1.4, 0.64, 1);
    position: relative;
    z-index: 1;
}
#analysisTable tbody tr:hover .student-avatar-table {
    transform: scale(1.12);
    transition: transform 0.22s cubic-bezier(0.34, 1.4, 0.64, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
#analysisTable tbody tr:hover .badge {
    transform: scale(1.06);
    transition: transform 0.18s cubic-bezier(0.34, 1.4, 0.64, 1);
}

/* Status Badges */
.status-paid {
    background: #dcfce7 !important;
    color: #16a34a !important;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}
.status-partial {
    background: #fef3c7 !important;
    color: #d97706 !important;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}
.status-unpaid {
    background: #fee2e2 !important;
    color: #dc2626 !important;
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
    transition: width 0.3s ease;
}
.progress-high { background: #16a34a; }
.progress-medium { background: #d97706; }
.progress-low { background: #dc2626; }
.progress-text {
    font-size: 10px;
    color: #6b7280;
    margin-top: 2px;
    display: block;
}

/* Student Avatar */
.student-avatar-table {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
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
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

/* ── Popover/Tooltip Styles ──────────────────────────────────────── */
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
    -webkit-backdrop-filter: blur(20px);
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

/* DataTables Customization */
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 15px;
}
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--ss-border);
    border-radius: 8px;
    padding: 8px 14px;
    margin-left: 8px;
    font-size: 13px;
    width: 250px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--ss-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid var(--ss-border);
    border-radius: 8px;
    padding: 6px 10px;
    margin: 0 6px;
    font-size: 13px;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background: var(--ss-accent) !important;
    border-color: var(--ss-accent) !important;
    color: #fff !important;
}
.dataTables_wrapper .dataTables_info {
    font-size: 13px;
    color: var(--ss-muted);
}

/* Search highlight */
.highlight {
    background-color: #fef3c7;
    color: #92400e;
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

@media (prefers-reduced-motion: reduce) {
    #analysisTable tbody tr,
    #analysisTable tbody tr:hover {
        transition: background 0.15s ease !important;
        transform: none !important;
        opacity: 1 !important;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <!-- Apple-style Loading Overlay -->
    <div id="loadingOverlay">
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
                <label class="filter-label">Class</label>
                <select class="form-select" id="class_id">
                    <option value="">-- All Classes --</option>
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

<!-- Popover / Tooltip -->
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

// Helper Functions
function formatMoney(n) { return '₦' + (parseFloat(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 })); }

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
    return String(str).replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[m]);
}

function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) overlay.classList.add('active');
    else overlay.classList.remove('active');
}

// Row entrance animation
function initRowEntrance() {
    const rows = document.querySelectorAll('#analysisTable tbody tr');
    if (!rows.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        rows.forEach(r => r.classList.add('row-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const row = entry.target;
            const index = Array.from(rows).indexOf(row);
            const delay = Math.min(index * 38, 15 * 38) + 60;
            setTimeout(() => row.classList.add('row-visible'), delay);
            observer.unobserve(row);
        });
    }, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });

    rows.forEach(row => observer.observe(row));
}

// Rendering Functions
function renderAvatar(avatar, name, admission) {
    const avatarUrl = avatar ? getAvatarUrl(avatar) : null;
    const initials = getInitials(name);
    const escapedName = escapeHtml(name);
    const escapedAdmission = escapeHtml(admission);

    if (avatarUrl) {
        return `<img src="${avatarUrl}" class="student-avatar-table" data-name="${escapedName}" data-admission="${escapedAdmission}" data-avatar="${avatarUrl}">`;
    }
    return `<div class="student-avatar-placeholder" data-name="${escapedName}" data-admission="${escapedAdmission}" data-avatar="">${initials}</div>`;
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

    document.getElementById('totalStudents').textContent = response.recordsTotal || 0;
    document.getElementById('totalBilled').textContent = formatMoney(totalBilled);
    document.getElementById('totalPaid').textContent = formatMoney(totalPaid);
    document.getElementById('collectionRate').textContent = collectionRate + '%';
}

// Popover Functions
const popover = document.getElementById('studentPopover');
let popoverTimer = null;
let hideTimer = null;

function fillPopover(studentId) {
    const s = studentData[studentId];
    if (!s) return;

    document.getElementById('popName').textContent = s.name;
    document.getElementById('popAdm').textContent = 'Adm: ' + s.admission;
    document.getElementById('popBilled').textContent = formatMoney(s.billed);
    document.getElementById('popPaid').textContent = formatMoney(s.paid);
    document.getElementById('popOutstanding').textContent = formatMoney(s.outstanding);

    const avatarUrl = s.avatar ? getAvatarUrl(s.avatar) : null;
    if (avatarUrl) document.getElementById('popAvatar').src = avatarUrl;
    else document.getElementById('popAvatar').src = '';

    const list = document.getElementById('popBillList');
    list.innerHTML = '';
    if (s.bills && s.bills.length) {
        s.bills.forEach(bill => {
            list.innerHTML += `<div class="popover-subject-row"><span>${escapeHtml(bill.title)}</span><span class="fw-bold">${formatMoney(bill.balance)}</span></div>`;
        });
    } else {
        list.innerHTML = '<div class="popover-subject-row text-muted">No bills available</div>';
    }
}

function positionPopover(e) {
    const vw = window.innerWidth, vh = window.innerHeight;
    const pw = 280, ph = 340;
    let left = e.clientX + 16;
    let top = e.clientY - 20;
    const arrow = document.getElementById('popoverArrow');

    if (left + pw > vw) left = e.clientX - pw + 4;
    if (top + ph > vh) {
        top = e.clientY - ph + 20;
        arrow.className = 'popover-arrow arrow-bottom';
    } else {
        arrow.className = 'popover-arrow arrow-top';
    }

    left = Math.max(8, Math.min(left, vw - pw - 8));
    top = Math.max(8, Math.min(top, vh - ph - 8));
    popover.style.left = left + 'px';
    popover.style.top = top + 'px';
}

function showPopover(row, e) {
    clearTimeout(hideTimer);
    const studentId = row.getAttribute('data-student-id');
    if (!studentId || !studentData[studentId]) return;

    fillPopover(studentId);
    positionPopover(e);
    popover.classList.add('visible');
}

function hidePopover() {
    hideTimer = setTimeout(() => {
        popover.classList.remove('visible');
    }, 180);
}

// DataTable Setup - with client-side search to avoid reloading on each keystroke
$(document).ready(function() {
    analysisTable = $('#analysisTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("reports.analysis.class-data") }}',
            type: 'GET',
            data: function(d) {
                d.class_id = currentFilters.class_id || '';
                d.term_id = currentFilters.term_id;
                d.session_id = currentFilters.session_id;
            },
            beforeSend: function() { showLoading(true); },
            complete: function() {
                showLoading(false);
                setTimeout(initRowEntrance, 100);
            },
            dataSrc: function(response) {
                if (response.data && response.data.length) {
                    response.data.forEach(item => {
                        studentData[item.student_id] = {
                            name: item.student_name,
                            admission: item.admission_no,
                            avatar: item.avatar,
                            billed: item.total_billed,
                            paid: item.total_paid,
                            outstanding: item.outstanding,
                            hasScholarship: item.has_scholarship,
                            hasDiscount: item.has_discount,
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
                render: function(data, type, row, meta) { return meta.row + 1; }
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
                    return '<a href="/reports/analysis/student/' + row.student_id + '/' + (currentFilters.class_id || '') + '/' + currentFilters.term_id + '/' + currentFilters.session_id + '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-eye-line"></i></a>';
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            const response = api.ajax.json();
            if (response) updateStats(response);

            // Attach hover events for popover
            $('#analysisTable tbody tr').off('mouseenter mouseleave mousemove');
            $('#analysisTable tbody tr').on('mouseenter', function(e) {
                clearTimeout(popoverTimer);
                popoverTimer = setTimeout(() => showPopover(this, e), 280);
            }).on('mousemove', function(e) {
                if (popover.classList.contains('visible')) {
                    positionPopover(e);
                }
            }).on('mouseleave', function() {
                clearTimeout(popoverTimer);
                hidePopover();
            });
        },
        language: {
            emptyTable: '<div class="text-center py-5 text-muted">No data available. Please select filters and click Load Report.</div>',
            processing: '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading...</p></div>',
            search: 'Search:',
            searchPlaceholder: 'Type to search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ students',
            infoEmpty: 'Showing 0 to 0 of 0 students',
            infoFiltered: '(filtered from _MAX_ total students)'
        },
        pageLength: 25,
        order: [[8, 'desc']],
        // Disable server-side search - use client-side search instead
        searchDelay: 500
    });

    // Custom client-side search highlighting
    $('#analysisTable_filter input').off('keyup').on('keyup', function() {
        const searchTerm = $(this).val();
        if (searchTerm.length > 0) {
            highlightSearchTerm(searchTerm);
        }
    });

    function highlightSearchTerm(term) {
        $('#analysisTable tbody tr').each(function() {
            const text = $(this).text();
            if (text.toLowerCase().indexOf(term.toLowerCase()) !== -1) {
                $(this).addClass('highlight');
            } else {
                $(this).removeClass('highlight');
            }
        });
    }

    $('#loadReportBtn').on('click', function() {
        const classId = $('#class_id').val();
        const termId = $('#term_id').val();
        const sessionId = $('#session_id').val();

        if (!termId || !sessionId) {
            Swal.fire('Warning', 'Please select term and session', 'warning');
            return;
        }

        currentFilters = {
            class_id: classId || null,
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
    if (!currentFilters.term_id) {
        Swal.fire('Warning', 'Please load report data first', 'warning');
        return;
    }
    const url = '{{ route("reports.analysis.export") }}?class_id=' + (currentFilters.class_id || '') + '&term_id=' + currentFilters.term_id + '&session_id=' + currentFilters.session_id + '&format=' + format;
    window.open(url, '_blank');
}

// Image zoom on avatar click
$(document).on('click', '.student-avatar-table, .student-avatar-placeholder', function() {
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
