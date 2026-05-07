{{-- resources/views/reports/debtors-list.blade.php --}}
@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
/* ── Design tokens ─────────────────────────────────────── */
:root {
    --d-bg:        #f0f4f8;
    --d-surface:   #ffffff;
    --d-navy:      #0f2744;
    --d-navy-mid:  #1e3a5f;
    --d-blue:      #2563eb;
    --d-blue-lt:   #dbeafe;
    --d-red:       #dc2626;
    --d-red-lt:    #fee2e2;
    --d-green:     #16a34a;
    --d-green-lt:  #dcfce7;
    --d-amber:     #d97706;
    --d-amber-lt:  #fef3c7;
    --d-border:    #e2e8f0;
    --d-muted:     #64748b;
    --d-radius:    12px;
    --d-radius-sm: 8px;
    --d-shadow:    0 1px 3px rgba(15,39,68,.06), 0 4px 16px rgba(15,39,68,.06);
    --d-shadow-lg: 0 8px 32px rgba(15,39,68,.12);
    --ff-body:     'DM Sans', sans-serif;
    --ff-mono:     'DM Mono', monospace;
}
* { font-family: var(--ff-body); }

/* ── Hero ──────────────────────────────────────────────── */
.dbl-hero {
    background: linear-gradient(135deg, var(--d-navy-mid) 0%, var(--d-blue) 60%, #4f46e5 100%);
    border-radius: var(--d-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.dbl-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.dbl-hero::after {
    content:''; position:absolute; bottom:-80px; left:-30px;
    width:260px; height:260px; background:rgba(255,255,255,.03); border-radius:50%;
}
.dbl-hero h1 {
    font-size:22px; font-weight:700; color:#fff;
    margin:0 0 6px; position:relative;
}
.dbl-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

.btn-hero-export {
    font-size:12px; font-weight:600; padding:7px 14px; border-radius:8px;
    display:inline-flex; align-items:center; gap:5px;
    border:1px solid rgba(255,255,255,.35);
    background:rgba(255,255,255,.12); color:#fff;
    transition:all .15s; text-decoration:none; cursor:pointer;
    position: relative;
}
.btn-hero-export:hover { background:rgba(255,255,255,.25); color:#fff; }

/* ── KPI cards ─────────────────────────────────────────── */
.kpi-card {
    background:#fff; border:1px solid var(--d-border);
    border-radius:var(--d-radius); padding:18px 20px;
    box-shadow:var(--d-shadow);
    display:flex; align-items:center; gap:14px;
    transition:box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow:var(--d-shadow-lg); transform:translateY(-1px); }
.kpi-icon {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.kpi-icon.blue  { background:var(--d-blue-lt);  color:var(--d-blue); }
.kpi-icon.red   { background:var(--d-red-lt);   color:var(--d-red); }
.kpi-icon.green { background:var(--d-green-lt); color:var(--d-green); }
.kpi-icon.amber { background:var(--d-amber-lt); color:var(--d-amber); }
.kpi-label { font-size:12px; color:var(--d-muted); font-weight:500; }
.kpi-value { font-size:22px; font-weight:700; color:var(--d-navy); letter-spacing:-.5px; line-height:1.1; }
.kpi-value.mono { font-family:var(--ff-mono); font-size:18px; }
.kpi-sub   { font-size:11px; color:var(--d-muted); margin-top:2px; }

/* ── Main card ─────────────────────────────────────────── */
.dbl-card {
    background:var(--d-surface);
    border:1px solid var(--d-border);
    border-radius:var(--d-radius);
    box-shadow:var(--d-shadow);
    overflow:hidden;
    margin-bottom:24px;
}

/* ── Filter bar ─────────────────────────────────────────── */
.filter-bar {
    padding:16px 20px;
    border-bottom:1px solid var(--d-border);
    background:#f8fafc;
    display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;
}
.filter-group { display:flex; flex-direction:column; gap:4px; flex:1; min-width:130px; }
.filter-group label {
    font-size:11px; font-weight:600; color:var(--d-muted);
    text-transform:uppercase; letter-spacing:.5px;
}
.filter-group .form-control,
.filter-group .form-select {
    font-size:13px; border-color:var(--d-border);
    border-radius:var(--d-radius-sm);
    padding:7px 10px; height:36px;
    background:white; color:var(--d-navy);
}
.filter-group .form-control:focus,
.filter-group .form-select:focus {
    border-color:var(--d-blue);
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.btn-filter {
    height:36px; padding:0 16px; font-size:13px; font-weight:600;
    border-radius:var(--d-radius-sm);
    display:flex; align-items:center; gap:6px; white-space:nowrap; cursor:pointer;
}
.btn-filter.primary { background:var(--d-blue); color:white; border:none; }
.btn-filter.primary:hover { background:#1d4ed8; }
.btn-filter.ghost { background:white; color:var(--d-navy-mid); border:1px solid var(--d-border); }
.btn-filter.ghost:hover { background:var(--d-blue-lt); border-color:var(--d-blue); color:var(--d-blue); }

/* ── Info bar ───────────────────────────────────────────── */
.info-bar {
    padding:10px 20px;
    border-bottom:1px solid var(--d-border);
    display:flex; justify-content:space-between; align-items:center;
    flex-wrap:wrap; gap:8px; background:#fff;
}
.info-bar .results-label { font-size:13px; color:var(--d-muted); }
.info-bar .results-label strong { color:var(--d-navy); }

/* ── DataTable overrides ─────────────────────────────────── */
.dataTables_wrapper .dataTables_filter { display:none; }
.dataTables_wrapper .dataTables_length select {
    border-radius:6px; border:1.5px solid var(--d-border);
    font-size:13px; padding:4px 8px; margin:0 4px;
}
.dataTables_wrapper .dataTables_info { font-size:12px; color:var(--d-muted); }

#debtorsTable thead tr th {
    background:#f8fafc; color:var(--d-muted);
    font-size:11px; font-weight:700;
    text-transform:uppercase; letter-spacing:.5px;
    padding:11px 14px;
    border-bottom:2px solid var(--d-border);
    white-space:nowrap;
}
#debtorsTable tbody tr { transition:background .12s; }
#debtorsTable tbody tr:hover { background:#f8fbff !important; }
#debtorsTable tbody td {
    padding:12px 14px; font-size:13.5px;
    color:var(--d-navy); border-bottom:1px solid var(--d-border);
    vertical-align:middle;
}

/* ── Student cell with avatar ────────────────────────────── */
.student-cell { display:flex; align-items:center; gap:10px; }

.student-avatar-wrap {
    position: relative;
    flex-shrink: 0;
    cursor: pointer;
}
.student-avatar-img {
    width: 38px; height: 38px;
    border-radius: 9px;
    object-fit: cover;
    border: 2px solid var(--d-border);
    background: #f0f0f0;
    transition: transform .2s, box-shadow .2s;
    display: block;
}
.student-avatar-img:hover {
    transform: scale(1.12);
    box-shadow: 0 4px 14px rgba(37,99,235,.25);
    border-color: var(--d-blue);
}
.student-initials {
    width: 38px; height: 38px;
    border-radius: 9px;
    background: linear-gradient(135deg, var(--d-blue) 0%, #7c3aed 100%);
    color: white; font-size: 13px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    border: 2px solid transparent;
}
.student-initials:hover {
    transform: scale(1.12);
    box-shadow: 0 4px 14px rgba(124,58,237,.3);
}
.student-name { font-weight:600; color:var(--d-navy); font-size:13px; }
.student-adm  { font-size:11px; color:var(--d-muted); font-family:var(--ff-mono); }

/* Amount cells */
.amt { font-family:var(--ff-mono); font-size:13px; }
.amt-outstanding { color:var(--d-red);   font-weight:600; }
.amt-paid        { color:var(--d-green); }
.amt-savings     { color:var(--d-amber); }
.amt-original    { color:var(--d-muted); }

/* Collection rate bar */
.coll-wrap { display:flex; align-items:center; gap:6px; min-width:90px; }
.coll-bar  { flex:1; height:5px; background:var(--d-border); border-radius:3px; overflow:hidden; }
.coll-fill { height:100%; border-radius:3px; background:var(--d-green); }
.coll-fill.warn  { background:var(--d-amber); }
.coll-fill.alert { background:var(--d-red); }
.coll-pct  { font-size:11px; font-family:var(--ff-mono); color:var(--d-muted); white-space:nowrap; }

/* Class badge */
.class-badge {
    display:inline-flex; align-items:center;
    font-size:11px; font-weight:600; padding:3px 8px; border-radius:4px;
    background:var(--d-blue-lt); color:var(--d-blue); white-space:nowrap;
}

/* Totals footer */
#debtorsTable tfoot td {
    background:var(--d-navy); color:white;
    font-size:13px; font-weight:600;
    padding:12px 14px;
    border-top:2px solid var(--d-navy-mid);
}
.tfoot-label { font-size:10px; text-transform:uppercase; letter-spacing:.5px; opacity:.7; }
.tfoot-val   { font-family:var(--ff-mono); font-size:14px; }
.tfoot-val.red   { color:#fca5a5; }
.tfoot-val.green { color:#86efac; }
.tfoot-val.amber { color:#fde68a; }

/* Pagination */
.dataTables_wrapper .paginate_button {
    font-size:13px !important; border-radius:6px !important; margin:0 2px !important;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background:var(--d-blue) !important;
    border-color:var(--d-blue) !important; color:white !important;
}

/* Row animation */
@keyframes rowIn {
    from { opacity:0; transform:translateY(4px); }
    to   { opacity:1; transform:translateY(0); }
}
.row-anim { animation:rowIn .2s ease forwards; }

/* ── Image Zoom Modal ───────────────────────────────────── */
.image-zoom-modal .modal-content {
    background: transparent;
    border: none;
    box-shadow: none;
}
.image-zoom-modal .modal-dialog {
    max-width: 90vw;
    margin: 1.75rem auto;
}
.image-zoom-modal .modal-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 20px;
}
.zoomed-image {
    max-width: 90vw;
    max-height: 75vh;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,.35);
    border: 4px solid white;
    cursor: pointer;
    animation: zoomIn .25s ease;
    object-fit: contain;
}
@keyframes zoomIn {
    from { opacity:0; transform:scale(.8); }
    to   { opacity:1; transform:scale(1); }
}
.image-zoom-modal .btn-close-zoom {
    position: absolute;
    top: 20px; right: 30px;
    background: rgba(0,0,0,.7);
    border: none;
    border-radius: 50%;
    width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px;
    cursor: pointer;
    z-index: 1060;
    transition: background .15s, transform .15s;
}
.image-zoom-modal .btn-close-zoom:hover {
    background: rgba(0,0,0,.9);
    transform: scale(1.1);
}
.zoomed-image-name {
    color: white;
    margin-top: 18px;
    font-size: 17px; font-weight: 600;
    text-shadow: 0 2px 4px rgba(0,0,0,.3);
    background: rgba(0,0,0,.5);
    padding: 7px 20px;
    border-radius: 40px;
    display: inline-block;
}
.zoomed-image-details {
    color: rgba(255,255,255,.8);
    margin-top: 8px;
    font-size: 13px;
    text-align: center;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero banner --}}
    <div class="dbl-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="ri-file-list-3-line me-2"></i>Student Debtors Report</h1>
                <p>Students with outstanding fee balances — sorted by highest debt</p>
            </div>
            <div class="d-flex gap-2 flex-wrap" style="position:relative;z-index:1">
                <button class="btn-hero-export" onclick="exportReport('pdf')">
                    <i class="ri-file-pdf-line"></i> PDF
                </button>
                <button class="btn-hero-export" onclick="exportReport('excel')">
                    <i class="ri-file-excel-line"></i> Excel
                </button>
                <button class="btn-hero-export" onclick="window.print()">
                    <i class="ri-printer-line"></i> Print
                </button>
            </div>
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="ri-user-line"></i></div>
                <div>
                    <div class="kpi-label">Total Debtors</div>
                    <div class="kpi-value" id="kpiDebtors">—</div>
                    <div class="kpi-sub">students with balance</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon red"><i class="ri-money-dollar-circle-line"></i></div>
                <div>
                    <div class="kpi-label">Total Outstanding</div>
                    <div class="kpi-value mono" id="kpiOutstanding">—</div>
                    <div class="kpi-sub">cumulative owed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon green"><i class="ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="kpi-label">Total Collected</div>
                    <div class="kpi-value mono" id="kpiCollected">—</div>
                    <div class="kpi-sub">from debtors</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon amber"><i class="ri-gift-line"></i></div>
                <div>
                    <div class="kpi-label">Total Savings</div>
                    <div class="kpi-value mono" id="kpiSavings">—</div>
                    <div class="kpi-sub">scholarships + discounts</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main table card --}}
    <div class="dbl-card">

        {{-- Filters --}}
        <div class="filter-bar">
            <div class="filter-group" style="max-width:200px;">
                <label>Class</label>
                <select class="form-select" id="classFilter">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group" style="max-width:160px;">
                <label>Term</label>
                <select class="form-select" id="termFilter">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group" style="max-width:160px;">
                <label>Session</label>
                <select class="form-select" id="sessionFilter">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group" style="max-width:160px;">
                <label>Min Outstanding (₦)</label>
                <input type="number" class="form-control" id="minOutstanding" placeholder="e.g. 5000">
            </div>
            <div class="filter-group" style="max-width:200px;">
                <label>Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Name or admission no…">
            </div>
            <div class="d-flex gap-2 align-self-end flex-shrink-0">
                <button class="btn-filter primary" id="applyFilters">
                    <i class="ri-search-line"></i> Search
                </button>
                <button class="btn-filter ghost" id="resetFilters">
                    <i class="ri-refresh-line"></i> Reset
                </button>
            </div>
        </div>

        {{-- Info / length bar --}}
        <div class="info-bar">
            <span class="results-label">
                Showing <strong id="resultCount">—</strong> records
            </span>
            <div id="dtLengthSlot"></div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="debtorsTable" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th style="width:50px">Photo</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Bill</th>
                        <th>Term / Session</th>
                        <th class="text-end">Original (₦)</th>
                        <th class="text-end">Paid (₦)</th>
                        <th class="text-end">Outstanding (₦)</th>
                        <th class="text-end">Savings (₦)</th>
                        <th style="width:100px">Rate</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">
                            <span class="tfoot-label">Page Totals</span>
                        </td>
                        <td class="text-end">
                            <div class="tfoot-label">Original</div>
                            <div class="tfoot-val" id="footOriginal">₦0.00</div>
                        </td>
                        <td class="text-end">
                            <div class="tfoot-label">Collected</div>
                            <div class="tfoot-val green" id="footPaid">₦0.00</div>
                        </td>
                        <td class="text-end">
                            <div class="tfoot-label">Outstanding</div>
                            <div class="tfoot-val red" id="footOutstanding">₦0.00</div>
                        </td>
                        <td class="text-end">
                            <div class="tfoot-label">Savings</div>
                            <div class="tfoot-val amber" id="footSavings">₦0.00</div>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination row --}}
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top flex-wrap gap-2">
            <div id="dtInfo" style="font-size:12px; color:var(--d-muted);"></div>
            <div id="dtPaginate"></div>
        </div>

    </div>{{-- /.dbl-card --}}

</div>
</div>
</div>

{{-- ── Image Zoom Modal ─────────────────────────────────── --}}
<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button class="btn-close-zoom" data-bs-dismiss="modal" aria-label="Close">
                <i class="ri-close-line"></i>
            </button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" alt="Student Photo" class="zoomed-image">
                <div class="zoomed-image-name" id="zoomedImageName"></div>
                <div class="zoomed-image-details" id="zoomedImageDetails"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ── helpers ──────────────────────────────────────────── */
function fmt(n) {
    const num = parseFloat(String(n).replace(/,/g, '')) || 0;
    return num.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function naira(n) { return '₦' + fmt(n); }

function getInitials(name) {
    return (name || '').split(' ').slice(0, 2).map(w => (w[0] || '')).join('').toUpperCase();
}

function collBar(pct) {
    const p   = parseFloat(pct) || 0;
    const cls = p >= 70 ? '' : p >= 40 ? ' warn' : ' alert';
    return `<div class="coll-wrap">
        <div class="coll-bar"><div class="coll-fill${cls}" style="width:${p}%"></div></div>
        <span class="coll-pct">${p}%</span>
    </div>`;
}

/* ── Image zoom ──────────────────────────────────────── */
function showZoomModal(imageUrl, name, details) {
    document.getElementById('zoomedImageName').textContent = name || '';
    document.getElementById('zoomedImageDetails').innerHTML = details || '';

    const imgEl = document.getElementById('zoomedImage');

    if (imageUrl && imageUrl !== '' && imageUrl !== 'null') {
        imgEl.src = imageUrl;
        imgEl.style.display = 'block';
    } else {
        // Generate canvas with initials
        const initials = getInitials(name);
        const canvas   = document.createElement('canvas');
        canvas.width   = 400;
        canvas.height  = 400;
        const ctx      = canvas.getContext('2d');
        const grad     = ctx.createLinearGradient(0, 0, 400, 400);
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
        ctx.fillText(initials || '??', 200, 200);
        imgEl.src = canvas.toDataURL();
        imgEl.style.display = 'block';
    }

    new bootstrap.Modal(document.getElementById('imageZoomModal')).show();
}

/* ── page-level accumulators ──────────────────────────── */
let runOrig = 0, runPaid = 0, runOwed = 0, runSave = 0;
function resetTotals() { runOrig = runPaid = runOwed = runSave = 0; }

function updateFooter() {
    document.getElementById('footOriginal').textContent    = naira(runOrig);
    document.getElementById('footPaid').textContent        = naira(runPaid);
    document.getElementById('footOutstanding').textContent = naira(runOwed);
    document.getElementById('footSavings').textContent     = naira(runSave);
}

function updateKPIs(recordsFiltered, data) {
    let totalOwed = 0, totalPaid = 0, totalSave = 0;
    (data || []).forEach(r => {
        totalOwed += parseFloat(String(r.outstanding).replace(/,/g,''))  || 0;
        totalPaid += parseFloat(String(r.amount_paid).replace(/,/g,''))  || 0;
        totalSave += parseFloat(String(r.savings).replace(/,/g,''))      || 0;
    });
    document.getElementById('kpiDebtors').textContent     = (recordsFiltered || 0).toLocaleString();
    document.getElementById('kpiOutstanding').textContent = naira(totalOwed);
    document.getElementById('kpiCollected').textContent   = naira(totalPaid);
    document.getElementById('kpiSavings').textContent     = naira(totalSave);
}

/* ── DataTable ────────────────────────────────────────── */
$(function () {

    var dt = $('#debtorsTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[8, 'desc']],
        ajax: {
            url: '{{ route("reports.financial.debtors") }}',
            type: 'GET',
            data: function (d) {
                d.class_id        = $('#classFilter').val();
                d.term_id         = $('#termFilter').val();
                d.session_id      = $('#sessionFilter').val();
                d.min_outstanding = $('#minOutstanding').val();
                d.search_value    = $('#searchInput').val();
            },
            dataSrc: function (json) {
                document.getElementById('resultCount').textContent =
                    (json.recordsFiltered || 0).toLocaleString();
                updateKPIs(json.recordsFiltered, json.data);
                resetTotals();
                return json.data;
            },
            error: function () {
                Swal.fire('Error', 'Failed to load debtors data. Please try again.', 'error');
            }
        },
        columns: [
            /* 0 # */
            { data: 'DT_RowIndex', orderable: false, searchable: false,
              render: d => `<span style="color:var(--d-muted);font-size:12px">${d}</span>` },

            /* 1 Photo */
            { data: 'student_avatar', orderable: false, searchable: false,
              render: function(d, t, r) {
                const name    = r.student_name || '';
                const initials = getInitials(name);
                const admNo   = r.admission_no || '';
                const cls     = r.class_name   || '';
                const details = `<i class="ri-honour-line me-1"></i>${admNo}&nbsp;|&nbsp;<i class="ri-building-line me-1"></i>${cls}`;

                if (d && d !== '' && d !== 'null') {
                    return `<img src="${d}" class="student-avatar-img"
                                 style="cursor:pointer"
                                 onclick='showZoomModal("${d.replace(/'/g,"\\'")}","${name.replace(/"/g,"&quot;")}","${details.replace(/"/g,"&quot;")}")'
                                 onerror="this.onerror=null;
                                          var el=document.createElement('div');
                                          el.className='student-initials';
                                          el.textContent='${initials}';
                                          el.onclick=function(){ showZoomModal('','${name.replace(/'/g,"\\'")}','${details.replace(/'/g,"\\'")}'); };
                                          this.parentNode.replaceChild(el,this);"
                            >`;
                }
                return `<div class="student-initials"
                              onclick='showZoomModal("","${name.replace(/"/g,"&quot;")}","${details.replace(/"/g,"&quot;")}")'>${initials}</div>`;
              }},

            /* 2 Student name */
            { data: 'student_name', name: 'student_name',
              render: function (d, t, r) {
                return `<div>
                    <div class="student-name">${d}</div>
                    <div class="student-adm">${r.admission_no || ''}</div>
                </div>`;
              }},

            /* 3 Class */
            { data: 'class_name', name: 'class_name',
              render: d => d ? `<span class="class-badge">${d}</span>` : '—' },

            /* 4 Bill */
            { data: 'bill_title', name: 'bill_title',
              render: d => `<span style="font-size:13px">${d || '—'}</span>` },

            /* 5 Term / Session */
            { data: 'term_name', name: 'term_name',
              render: (d, t, r) => `<div style="font-size:12px;line-height:1.6">
                <div style="font-weight:500">${r.term_name || '—'}</div>
                <div style="color:var(--d-muted)">${r.session_name || ''}</div>
              </div>` },

            /* 6 Original */
            { data: 'original_amount', name: 'original_amount', className: 'text-end',
              render: d => `<span class="amt amt-original">${naira(d)}</span>` },

            /* 7 Paid */
            { data: 'amount_paid', name: 'amount_paid', className: 'text-end',
              render: d => `<span class="amt amt-paid">${naira(d)}</span>` },

            /* 8 Outstanding */
            { data: 'outstanding', name: 'outstanding', className: 'text-end',
              render: d => `<span class="amt amt-outstanding">${naira(d)}</span>` },

            /* 9 Savings */
            { data: 'savings', name: 'savings', className: 'text-end',
              render: d => `<span class="amt amt-savings">${naira(d)}</span>` },

            /* 10 Rate */
            { data: 'collection_rate', name: 'collection_rate', orderable: false,
              render: d => collBar(d) },

            /* 11 Action */
            { data: 'action', name: 'action', orderable: false, searchable: false,
              className: 'text-center' },
        ],
        rowCallback: function (row, data) {
            runOrig += parseFloat(String(data.original_amount).replace(/,/g,'')) || 0;
            runPaid += parseFloat(String(data.amount_paid).replace(/,/g,''))     || 0;
            runOwed += parseFloat(String(data.outstanding).replace(/,/g,''))     || 0;
            runSave += parseFloat(String(data.savings).replace(/,/g,''))         || 0;
            row.classList.add('row-anim');
        },
        drawCallback: function () {
            updateFooter();
            $('#debtorsTable_info').appendTo('#dtInfo');
            $('#debtorsTable_paginate').appendTo('#dtPaginate');
            $('#debtorsTable_length').appendTo('#dtLengthSlot');
        },
        language: {
            processing: '<div style="padding:20px;color:var(--d-muted);font-size:13px">'
                      + '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading…</div>',
            emptyTable:  '<div style="padding:50px 20px;text-align:center;color:var(--d-muted)">'
                      + '<i class="ri-inbox-line" style="font-size:36px;display:block;margin-bottom:8px"></i>'
                      + 'No debtors found matching your filters.</div>',
            zeroRecords: '<div style="padding:50px 20px;text-align:center;color:var(--d-muted)">'
                      + '<i class="ri-search-line" style="font-size:36px;display:block;margin-bottom:8px"></i>'
                      + 'No results for the selected filters.</div>',
            search: '', searchPlaceholder: '',
            info: 'Showing _START_–_END_ of _TOTAL_ records',
            infoEmpty: 'No records',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
        },
        dom: 'rtp',
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
    });

    /* filter controls */
    $('#applyFilters').on('click', function () { resetTotals(); dt.ajax.reload(); });

    $('#searchInput').on('keydown', function (e) {
        if (e.key === 'Enter') { resetTotals(); dt.ajax.reload(); }
    });

    $('#resetFilters').on('click', function () {
        $('#classFilter, #termFilter, #sessionFilter').val('');
        $('#minOutstanding, #searchInput').val('');
        resetTotals();
        dt.ajax.reload();
    });

    /* zoom modal: click image to close */
    $(document).on('click', '.zoomed-image', function () {
        $('#imageZoomModal').modal('hide');
    });
});

/* ── export ── */
function exportReport(fmt) {
    const params = new URLSearchParams({
        format:     fmt,
        class_id:   document.getElementById('classFilter').value,
        term_id:    document.getElementById('termFilter').value,
        session_id: document.getElementById('sessionFilter').value,
    });
    window.open('{{ route("reports.financial.export", ["debtors", "pdf"]) }}?' + params, '_blank');
}
</script>
@endsection
