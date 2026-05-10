@extends('layouts.master')

@section('content')
<style>
    /* Grade colors for payment status */
    .status-paid { background-color: #dcfce7 !important; color: #166534; font-weight: bold; }
    .status-partial { background-color: #fef9c3 !important; color: #854d0e; }
    .status-unpaid { background-color: #fee2e2 !important; color: #991b1b; font-weight: bold; }

    /* Main table styles */
    .analysis-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        background: white;
        border: 1.5px solid #1e3a5f;
    }

    .analysis-table thead tr th {
        background: #1e3a5f;
        color: white;
        text-align: center;
        padding: 10px 8px;
        border: 0.5px solid #2563eb55;
        font-weight: bold;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .analysis-table thead tr th.student-col {
        background: #0f2040;
        text-align: left;
        padding-left: 12px;
    }

    .analysis-table tbody tr:nth-child(odd) { background: #ffffff; }
    .analysis-table tbody tr:nth-child(even) { background: #f0f4fa; }
    .analysis-table tbody tr:hover { background-color: #e8f0fe !important; cursor: pointer; }

    .analysis-table tbody td {
        padding: 8px 6px;
        border: 0.5px solid #c5d3e8;
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
    }

    .analysis-table tbody td.student-info-cell {
        text-align: left;
        padding-left: 12px;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: inherit;
        z-index: 5;
        min-width: 200px;
    }

    /* Student avatar */
    .student-avatar-table {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .student-avatar-table:hover {
        transform: scale(1.1);
        border-color: #2563eb;
    }

    /* Progress bar */
    .progress-bar-container {
        width: 100px;
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin: 0 auto;
    }
    .progress-bar-fill {
        height: 6px;
        border-radius: 10px;
        transition: width 0.3s ease;
    }
    .progress-high { background: linear-gradient(90deg, #16a34a, #22c55e); }
    .progress-medium { background: linear-gradient(90deg, #d97706, #f59e0b); }
    .progress-low { background: linear-gradient(90deg, #dc2626, #ef4444); }

    /* Stats cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-value { font-size: 24px; font-weight: 700; color: #1e3a5f; }
    .stat-label { font-size: 11px; color: #64748b; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Meta grid */
    .meta-grid {
        display: flex;
        border: 1px solid #c5d3e8;
        background: #f0f4fa;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .meta-cell { flex: 1; padding: 10px 15px; border-right: 1px solid #c5d3e8; }
    .meta-cell:last-child { border-right: none; }
    .meta-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; display: block; }
    .meta-value { font-size: 14px; font-weight: bold; color: #1e3a5f; }

    /* School header */
    .school-header-bar {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        border-radius: 10px;
        padding: 18px 24px;
        margin-bottom: 18px;
        color: white;
    }

    /* Filter bar */
    .filter-bar {
        background: #f8fafc;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .filter-label {
        font-weight: 600;
        font-size: 12px;
        margin-bottom: 6px;
        color: #1e3a5f;
    }

    /* Popover styles */
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
    .popover-name { font-size: 14px; font-weight: 700; color: white; }
    .popover-adm { font-size: 10px; color: rgba(255,255,255,0.75); }
    .popover-body { padding: 12px 16px; }
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
    .popover-stat-val { font-size: 14px; font-weight: 700; color: #1e3a5f; }
    .popover-stat-lbl { font-size: 9px; color: #9ca3af; }
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

    @media print {
        .no-print { display: none !important; }
        #studentPopover { display: none !important; }
        .analysis-table { font-size: 9px; }
    }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- School Header --}}
    <div class="school-header-bar">
        <div class="d-flex align-items-center">
            @if($schoolInfo && $schoolInfo->getLogoUrlAttribute())
                <img src="{{ $schoolInfo->getLogoUrlAttribute() }}" alt="Logo"
                     style="width:60px;height:60px;object-fit:contain;border-radius:50%;border:2px solid white;margin-right:15px;">
            @endif
            <div class="flex-grow-1 text-center">
                <h4 class="mb-1 fw-bold text-uppercase">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</h4>
                @if($schoolInfo && $schoolInfo->school_address)
                    <p class="mb-1 opacity-75" style="font-size:12px;">{{ $schoolInfo->school_address }}</p>
                @endif
                @if($schoolInfo && $schoolInfo->school_motto)
                    <p class="mb-0 fst-italic opacity-75" style="font-size:11px;">"{{ $schoolInfo->school_motto }}"</p>
                @endif
            </div>
            @if($schoolInfo && $schoolInfo->getStampUrlAttribute())
                <img src="{{ $schoolInfo->getStampUrlAttribute() }}" alt="Stamp" style="width:50px;height:50px;">
            @endif
        </div>
    </div>

    {{-- Title Strip --}}
    <div style="background:#1e3a5f;color:white;text-align:center;padding:10px;font-size:14px;font-weight:bold;border-radius:6px;margin-bottom:15px;">
        CLASS FINANCIAL ANALYSIS REPORT
    </div>

    {{-- Meta Grid --}}
    <div class="meta-grid">
        <div class="meta-cell"><span class="meta-label">Class</span><span class="meta-value" id="selectedClass">—</span></div>
        <div class="meta-cell"><span class="meta-label">Term</span><span class="meta-value" id="selectedTerm">—</span></div>
        <div class="meta-cell"><span class="meta-label">Session</span><span class="meta-value" id="selectedSession">—</span></div>
        <div class="meta-cell"><span class="meta-label">Total Students</span><span class="meta-value" id="totalStudents">0</span></div>
        <div class="meta-cell"><span class="meta-label">Collection Rate</span><span class="meta-value" id="totalRate">0%</span></div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid" id="statsRow" style="display: none;">
        <div class="stat-card"><div class="stat-value text-success" id="totalBilled">₦0</div><div class="stat-label">Total Billed</div></div>
        <div class="stat-card"><div class="stat-value text-info" id="totalPaid">₦0</div><div class="stat-label">Total Paid</div></div>
        <div class="stat-card"><div class="stat-value text-danger" id="totalOutstanding">₦0</div><div class="stat-label">Outstanding</div></div>
        <div class="stat-card"><div class="stat-value text-warning" id="totalSavings">₦0</div><div class="stat-label">Total Savings</div></div>
        <div class="stat-card"><div class="stat-value" id="scholarshipCount">0</div><div class="stat-label">Scholarship Students</div></div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar no-print">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="filter-label">Class <span class="text-danger">*</span></label>
                <select class="form-select" id="class_id">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Term <span class="text-danger">*</span></label>
                <select class="form-select" id="term_id">
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Session <span class="text-danger">*</span></label>
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
                <div class="float-end action-buttons">
                    <button class="btn btn-outline-success btn-sm" onclick="exportReport('excel')"><i class="ri-file-excel-line"></i> Excel</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportReport('pdf')"><i class="ri-file-pdf-line"></i> PDF</button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="ri-printer-line"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card shadow-sm mb-4" id="tableCard" style="display: none;">
        <div class="card-header" style="background:#1e3a5f;">
            <h6 class="mb-0 text-white fw-bold"><i class="ri-table-line me-2"></i>Student Payment Details</h6>
        </div>
        <div class="card-body p-0">
            <div style="overflow-x: auto; max-height: 70vh; overflow-y: auto;">
                <table class="analysis-table" id="analysisTable">
                    <thead>
                        <tr>
                            <th class="student-col" width="40">#</th>
                            <th class="student-col" width="50">Photo</th>
                            <th class="student-col" width="200">Student Name</th>
                            <th width="100">Admission No</th>
                            <th width="60">Gender</th>
                            <th width="80">Benefits</th>
                            <th width="110">Total Billed (₦)</th>
                            <th width="110">Total Paid (₦)</th>
                            <th width="110">Outstanding (₦)</th>
                            <th width="100">Completion</th>
                            <th width="80">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr><td colspan="11" class="text-center py-5 text-muted">Select class, term, and session to view data</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- Popover --}}
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentFilters = {};
let studentData = {};

function formatMoney(n) { return '₦' + (parseFloat(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 })); }
function formatNumber(n) { return parseFloat(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 })); }
function getInitials(name) { if (!name) return 'ST'; return name.split(' ').slice(0,2).map(w=>w[0]||'').join('').toUpperCase(); }

function getAvatarUrl(picture) {
    if (!picture || picture === 'unnamed.jpg') return null;
    return '/storage/images/student_avatars/' + picture;
}

function getProgressClass(percentage) {
    if (percentage >= 70) return 'progress-high';
    if (percentage >= 40) return 'progress-medium';
    return 'progress-low';
}

function renderTable(data) {
    let html = '';
    studentData = {};

    data.forEach((item, idx) => {
        const statusClass = item.status === 'Fully Paid' ? 'status-paid' : (item.status === 'Partial' ? 'status-partial' : 'status-unpaid');
        const progressClass = getProgressClass(item.completion);
        const avatarUrl = item.avatar ? getAvatarUrl(item.avatar) : null;

        studentData[item.student_id] = {
            name: item.student_name,
            admission: item.admission_no,
            avatar: avatarUrl,
            billed: item.total_billed,
            paid: item.total_paid,
            outstanding: item.outstanding,
            status: item.status,
            bills: item.bills || []
        };

        html += `<tr data-student-id="${item.student_id}" data-student-name="${item.student_name.toLowerCase()}" data-admission="${item.admission_no.toLowerCase()}">
            <td class="text-center">${idx + 1}</td>
            <td class="text-center">${renderAvatar(item)}</td>
            <td class="student-info-cell"><strong>${escapeHtml(item.student_name)}</strong></td>
            <td>${escapeHtml(item.admission_no)}</td>
            <td>${escapeHtml(item.gender || 'N/A')}</td>
            <td>${renderBenefits(item)}</td>
            <td class="text-end">${formatMoney(item.total_billed)}</td>
            <td class="text-end text-success">${formatMoney(item.total_paid)}</td>
            <td class="text-end text-danger">${formatMoney(item.outstanding)}</td>
            <td>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill ${progressClass}" style="width: ${item.completion}%"></div>
                </div>
                <small>${item.completion}%</small>
            </td>
            <td><span class="badge ${statusClass} px-2 py-1">${item.status}</span></td>
        </tr>`;
    });

    $('#tableBody').html(html);
    attachPopoverEvents();
}

function renderAvatar(item) {
    const avatarUrl = item.avatar ? getAvatarUrl(item.avatar) : null;
    const initials = getInitials(item.student_name);

    if (avatarUrl) {
        return `<img src="${avatarUrl}" class="student-avatar-table" data-name="${escapeHtml(item.student_name)}" data-admission="${escapeHtml(item.admission_no)}">`;
    }
    return `<div class="student-avatar-table" style="background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; border-radius: 50%; width: 32px; height: 32px; margin: 0 auto; cursor: pointer;">${initials}</div>`;
}

function renderBenefits(item) {
    let html = '';
    if (item.has_scholarship) html += '<span class="badge bg-warning me-1" style="background:#fef3c7!important;color:#d97706;"><i class="ri-award-line"></i> Scholarship</span> ';
    if (item.has_discount) html += '<span class="badge bg-info me-1" style="background:#ede9fe!important;color:#6d28d9;"><i class="ri-price-tag-3-line"></i> Discount</span> ';
    if (!item.has_scholarship && !item.has_discount) html = '<span class="text-muted">—</span>';
    return html;
}

function updateStats(data) {
    let totalBilled = 0, totalPaid = 0, totalOutstanding = 0, totalSavings = 0, scholarshipCount = 0;
    data.forEach(item => {
        totalBilled += parseFloat(item.total_billed) || 0;
        totalPaid += parseFloat(item.total_paid) || 0;
        totalOutstanding += parseFloat(item.outstanding) || 0;
        totalSavings += parseFloat(item.total_savings) || 0;
        if (item.has_scholarship) scholarshipCount++;
    });
    const rate = totalBilled > 0 ? ((totalPaid / totalBilled) * 100).toFixed(1) : 0;

    $('#totalBilled').text(formatMoney(totalBilled));
    $('#totalPaid').text(formatMoney(totalPaid));
    $('#totalOutstanding').text(formatMoney(totalOutstanding));
    $('#totalSavings').text(formatMoney(totalSavings));
    $('#scholarshipCount').text(scholarshipCount);
    $('#totalStudents').text(data.length);
    $('#totalRate').text(rate + '%');
    $('#selectedClass').text($('#class_id option:selected').text());
    $('#selectedTerm').text($('#term_id option:selected').text());
    $('#selectedSession').text($('#session_id option:selected').text());
}

function loadReportData() {
    const classId = $('#class_id').val();
    const termId = $('#term_id').val();
    const sessionId = $('#session_id').val();

    if (!classId || !termId || !sessionId) {
        Swal.fire('Warning', 'Please select class, term, and session', 'warning');
        return;
    }

    currentFilters = { class_id: classId, term_id: termId, session_id: sessionId };

    $('#tableBody').html('<tr><td colspan="11" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading data...</p></td></tr>');
    $('#statsRow, #tableCard').hide();

    $.ajax({
        url: '{{ route("reports.analysis.class-data") }}',
        type: 'GET',
        data: currentFilters,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                renderTable(response.data);
                updateStats(response.data);
                $('#statsRow, #tableCard').show();
            } else {
                $('#tableBody').html('<tr><td colspan="11" class="text-center py-5 text-muted">No data found for the selected filters</td></tr>');
                Swal.fire('Info', 'No data found for the selected filters', 'info');
                $('#statsRow, #tableCard').show();
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.responseText);
            $('#tableBody').html('<tr><td colspan="11" class="text-center py-5 text-danger">Failed to load data. Please try again.</td></tr>');
            Swal.fire('Error', 'Failed to load data', 'error');
        }
    });
}

// Popover functionality
let popoverTimer, hideTimer;
const popover = document.getElementById('studentPopover');

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
            list.append(`<div class="popover-subject-row"><span>${escapeHtml(bill.title)}</span><span class="fw-bold">₦${formatNumber(bill.balance)}</span></div>`);
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
    const studentId = row.dataset.studentId;
    if (!studentId || !studentData[studentId]) return;
    fillPopover(studentId);
    positionPopover(e);
    popover.classList.add('visible');
}

function hidePopover() {
    hideTimer = setTimeout(() => popover.classList.remove('visible'), 180);
}

function attachPopoverEvents() {
    $('#tableBody tr').off('mouseenter mouseleave mousemove').on('mouseenter', function(e) {
        clearTimeout(popoverTimer);
        popoverTimer = setTimeout(() => showPopover(this, e), 280);
    }).on('mousemove', function(e) {
        if (popover.classList.contains('visible')) positionPopover(e);
    }).on('mouseleave', function() {
        clearTimeout(popoverTimer);
        hidePopover();
    });
}

function exportReport(format) {
    if (!currentFilters.class_id) {
        Swal.fire('Warning', 'Please load report data first', 'warning');
        return;
    }
    window.open('{{ route("reports.analysis.export") }}?class_id=' + currentFilters.class_id + '&term_id=' + currentFilters.term_id + '&session_id=' + currentFilters.session_id + '&format=' + format, '_blank');
}

function escapeHtml(str) { if (!str) return ''; return String(str).replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[m]); }

$(document).ready(function() {
    $('#loadReportBtn').on('click', loadReportData);
    $('#resetBtn').on('click', function() { $('#class_id, #term_id, #session_id').val(''); $('#statsRow, #tableCard').hide(); $('#tableBody').html('<tr><td colspan="11" class="text-center py-5 text-muted">Select class, term, and session to view data</td></tr>'); });
});
</script>
@endsection
