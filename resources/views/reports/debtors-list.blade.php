{{-- resources/views/reports/debtors-list.blade.php --}}
@extends('layouts.master')

@section('content')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
/* ── Design tokens ─────────────────────────────────────── */
:root {
    --d-bg:       #f0f4f8;
    --d-surface:  #ffffff;
    --d-navy:     #0f2744;
    --d-navy-mid: #1e3a5f;
    --d-blue:     #2563eb;
    --d-blue-lt:  #dbeafe;
    --d-red:      #dc2626;
    --d-red-lt:   #fee2e2;
    --d-green:    #16a34a;
    --d-green-lt: #dcfce7;
    --d-amber:    #d97706;
    --d-amber-lt: #fef3c7;
    --d-border:   #e2e8f0;
    --d-muted:    #64748b;
    --d-radius:   14px;
    --d-radius-sm:8px;
    --d-shadow:   0 1px 3px rgba(15,39,68,.06), 0 4px 16px rgba(15,39,68,.06);
    --d-shadow-lg:0 8px 32px rgba(15,39,68,.12);
    --ff-body:    'DM Sans', sans-serif;
    --ff-mono:    'DM Mono', monospace;
}

* { font-family: var(--ff-body); }

/* ── Page shell ─────────────────────────────────────────── */
.dbl-page { background: var(--d-bg); min-height: 100vh; }

/* ── Page header ────────────────────────────────────────── */
.dbl-header {
    background: var(--d-surface);
    border-bottom: 1px solid var(--d-border);
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.dbl-header-left h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--d-navy);
    margin: 0;
    letter-spacing: -.3px;
}
.dbl-header-left p {
    font-size: 13px;
    color: var(--d-muted);
    margin: 2px 0 0;
}
.dbl-breadcrumb { font-size: 12px; color: var(--d-muted); margin-top: 4px; }
.dbl-breadcrumb a { color: var(--d-blue); text-decoration: none; }
.dbl-breadcrumb span { margin: 0 5px; }

/* ── KPI strip ──────────────────────────────────────────── */
.kpi-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 20px 28px;
}
@media (max-width: 900px) { .kpi-strip { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 540px) { .kpi-strip { grid-template-columns: 1fr; } }

.kpi-card {
    background: var(--d-surface);
    border: 1px solid var(--d-border);
    border-radius: var(--d-radius);
    padding: 18px 20px;
    box-shadow: var(--d-shadow);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow: var(--d-shadow-lg); transform: translateY(-1px); }
.kpi-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.kpi-icon.blue   { background: var(--d-blue-lt);  color: var(--d-blue); }
.kpi-icon.red    { background: var(--d-red-lt);   color: var(--d-red); }
.kpi-icon.green  { background: var(--d-green-lt); color: var(--d-green); }
.kpi-icon.amber  { background: var(--d-amber-lt); color: var(--d-amber); }
.kpi-label { font-size: 12px; color: var(--d-muted); font-weight: 500; }
.kpi-value { font-size: 22px; font-weight: 700; color: var(--d-navy); letter-spacing: -.5px; line-height: 1.1; }
.kpi-value.mono  { font-family: var(--ff-mono); font-size: 18px; }
.kpi-sub   { font-size: 11px; color: var(--d-muted); margin-top: 2px; }

/* ── Main card ──────────────────────────────────────────── */
.dbl-card {
    background: var(--d-surface);
    border: 1px solid var(--d-border);
    border-radius: var(--d-radius);
    box-shadow: var(--d-shadow);
    margin: 0 28px 28px;
    overflow: hidden;
}

/* ── Filter bar ─────────────────────────────────────────── */
.filter-bar {
    padding: 18px 22px;
    border-bottom: 1px solid var(--d-border);
    background: #f8fafc;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 160px; }
.filter-group label { font-size: 11px; font-weight: 600; color: var(--d-muted); text-transform: uppercase; letter-spacing: .5px; }
.filter-group .form-control,
.filter-group .form-select {
    font-size: 13px;
    border-color: var(--d-border);
    border-radius: var(--d-radius-sm);
    padding: 7px 10px;
    height: 36px;
    background: white;
    color: var(--d-navy);
}
.filter-group .form-control:focus,
.filter-group .form-select:focus {
    border-color: var(--d-blue);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.btn-filter {
    height: 36px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: var(--d-radius-sm);
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap;
}
.btn-filter.primary { background: var(--d-blue); color: white; border: none; }
.btn-filter.primary:hover { background: #1d4ed8; }
.btn-filter.ghost   { background: white; color: var(--d-navy-mid); border: 1px solid var(--d-border); }
.btn-filter.ghost:hover { background: var(--d-blue-lt); border-color: var(--d-blue); color: var(--d-blue); }

/* ── Export bar ─────────────────────────────────────────── */
.export-bar {
    padding: 10px 22px;
    border-bottom: 1px solid var(--d-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.export-bar .results-label { font-size: 13px; color: var(--d-muted); }
.export-bar .results-label strong { color: var(--d-navy); }
.btn-export {
    font-size: 12px; font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
    display: inline-flex; align-items: center; gap: 5px;
    cursor: pointer; border: 1px solid var(--d-border);
    background: white; color: var(--d-navy-mid);
    transition: all .15s;
}
.btn-export:hover { background: var(--d-navy); color: white; border-color: var(--d-navy); }

/* ── DataTable overrides ─────────────────────────────────── */
#debtorsTable_wrapper .dataTables_filter { display: none; }
#debtorsTable_wrapper .dataTables_length select {
    border-radius: 6px;
    border-color: var(--d-border);
    font-size: 13px;
    padding: 3px 6px;
}
#debtorsTable { width: 100% !important; }

#debtorsTable thead tr th {
    background: #f8fafc;
    color: var(--d-muted);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 11px 14px;
    border-bottom: 2px solid var(--d-border);
    white-space: nowrap;
}
#debtorsTable tbody tr { transition: background .12s; }
#debtorsTable tbody tr:hover { background: #f8fbff !important; }
#debtorsTable tbody td {
    padding: 12px 14px;
    font-size: 13.5px;
    color: var(--d-navy);
    border-bottom: 1px solid var(--d-border);
    vertical-align: middle;
}

/* Student name cell */
.student-cell { display: flex; align-items: center; gap: 10px; }
.student-initials {
    width: 34px; height: 34px; border-radius: 8px;
    background: linear-gradient(135deg, var(--d-blue) 0%, #7c3aed 100%);
    color: white; font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.student-name { font-weight: 600; color: var(--d-navy); font-size: 13px; }
.student-adm  { font-size: 11px; color: var(--d-muted); font-family: var(--ff-mono); }

/* Amount cells */
.amt { font-family: var(--ff-mono); font-size: 13px; }
.amt-outstanding { color: var(--d-red);   font-weight: 600; }
.amt-paid        { color: var(--d-green); }
.amt-savings     { color: var(--d-amber); }
.amt-original    { color: var(--d-muted); }

/* Progress bar */
.coll-wrap { display: flex; align-items: center; gap: 8px; min-width: 100px; }
.coll-bar  { flex: 1; height: 5px; background: var(--d-border); border-radius: 3px; overflow: hidden; }
.coll-fill { height: 100%; border-radius: 3px; background: var(--d-green); transition: width .4s ease; }
.coll-fill.warn  { background: var(--d-amber); }
.coll-fill.alert { background: var(--d-red); }
.coll-pct  { font-size: 11px; font-family: var(--ff-mono); color: var(--d-muted); white-space: nowrap; }

/* Badge */
.class-badge {
    display: inline-flex; align-items: center;
    font-size: 11px; font-weight: 600;
    padding: 3px 8px; border-radius: 4px;
    background: var(--d-blue-lt); color: var(--d-blue);
    white-space: nowrap;
}

/* Totals footer */
#debtorsTable tfoot td {
    background: var(--d-navy);
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 12px 14px;
    border-top: 2px solid var(--d-navy-mid);
}
.tfoot-label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.tfoot-val   { font-family: var(--ff-mono); font-size: 14px; }
.tfoot-val.red   { color: #fca5a5; }
.tfoot-val.green { color: #86efac; }
.tfoot-val.amber { color: #fde68a; }

/* Loading overlay */
#tableLoading {
    position: absolute; inset: 0;
    background: rgba(255,255,255,.75);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    z-index: 10; gap: 10px;
    border-radius: var(--d-radius);
}
.loading-spinner {
    width: 36px; height: 36px;
    border: 3px solid var(--d-border);
    border-top-color: var(--d-blue);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty state */
.empty-state { padding: 60px 20px; text-align: center; }
.empty-state i { font-size: 48px; color: var(--d-border); }
.empty-state p { color: var(--d-muted); margin-top: 8px; }

/* Pagination */
#debtorsTable_paginate .paginate_button {
    font-size: 13px !important;
    border-radius: 6px !important;
    margin: 0 2px !important;
}
#debtorsTable_paginate .paginate_button.current {
    background: var(--d-blue) !important;
    border-color: var(--d-blue) !important;
    color: white !important;
}

/* Animate rows in */
@keyframes rowIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.row-anim { animation: rowIn .2s ease forwards; }
</style>
@endpush

<div class="dbl-page">

    {{-- Header --}}
    <div class="dbl-header">
        <div class="dbl-header-left">
            <h2><i class="ri-file-list-3-line me-2" style="color:var(--d-blue)"></i>Student Debtors Report</h2>
            <p>Students with outstanding fee balances</p>
            <div class="dbl-breadcrumb">
                <a href="#">Dashboard</a><span>/</span>
                <a href="#">Reports</a><span>/</span>
                <span style="color:var(--d-navy)">Debtors List</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn-export" onclick="exportReport('pdf')">
                <i class="ri-file-pdf-line"></i> PDF
            </button>
            <button class="btn-export" onclick="exportReport('excel')">
                <i class="ri-file-excel-line"></i> Excel
            </button>
            <button class="btn-export" onclick="window.print()">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="ri-user-line"></i></div>
            <div>
                <div class="kpi-label">Total Debtors</div>
                <div class="kpi-value" id="kpiDebtors">—</div>
                <div class="kpi-sub">students with balance</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="ri-money-dollar-circle-line"></i></div>
            <div>
                <div class="kpi-label">Total Outstanding</div>
                <div class="kpi-value mono" id="kpiOutstanding">—</div>
                <div class="kpi-sub">cumulative owed</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="ri-checkbox-circle-line"></i></div>
            <div>
                <div class="kpi-label">Total Collected</div>
                <div class="kpi-value mono" id="kpiCollected">—</div>
                <div class="kpi-sub">from debtors</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon amber"><i class="ri-gift-line"></i></div>
            <div>
                <div class="kpi-label">Total Savings</div>
                <div class="kpi-value mono" id="kpiSavings">—</div>
                <div class="kpi-sub">scholarships + discounts</div>
            </div>
        </div>
    </div>

    {{-- Main card --}}
    <div class="dbl-card">

        {{-- Filters --}}
        <div class="filter-bar">
            <div class="filter-group" style="max-width:220px;">
                <label>Class</label>
                <select class="form-select" id="classFilter">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group" style="max-width:180px;">
                <label>Term</label>
                <select class="form-select" id="termFilter">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group" style="max-width:180px;">
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

        {{-- Export / results bar --}}
        <div class="export-bar">
            <span class="results-label">
                Showing <strong id="resultCount">—</strong> records
            </span>
            <div class="d-flex gap-2">
                <div id="debtorsTable_length" class="dataTables_length"></div>
            </div>
        </div>

        {{-- Table --}}
        <div style="position:relative;">
            <div id="tableLoading">
                <div class="loading-spinner"></div>
                <span style="font-size:13px;color:var(--d-muted)">Loading debtors…</span>
            </div>

            <div class="table-responsive">
                <table id="debtorsTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Bill</th>
                            <th>Term / Session</th>
                            <th class="text-end">Original (₦)</th>
                            <th class="text-end">Paid (₦)</th>
                            <th class="text-end">Outstanding (₦)</th>
                            <th class="text-end">Savings (₦)</th>
                            <th style="width:60px">Rate</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <span class="tfoot-label">Page Totals</span>
                            </td>
                            <td class="text-end">
                                <div class="tfoot-label">Original</div>
                                <div class="tfoot-val" id="footOriginal">₦0</div>
                            </td>
                            <td class="text-end">
                                <div class="tfoot-label">Collected</div>
                                <div class="tfoot-val green" id="footPaid">₦0</div>
                            </td>
                            <td class="text-end">
                                <div class="tfoot-label">Outstanding</div>
                                <div class="tfoot-val red" id="footOutstanding">₦0</div>
                            </td>
                            <td class="text-end">
                                <div class="tfoot-label">Savings</div>
                                <div class="tfoot-val amber" id="footSavings">₦0</div>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>{{-- /.dbl-card --}}

</div>{{-- /.dbl-page --}}

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
/* ─── helpers ──────────────────────────────────────────── */
function fmt(n) {
    const num = parseFloat(String(n).replace(/,/g, '')) || 0;
    return num.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function naira(n) { return '₦' + fmt(n); }

function initials(name) {
    return (name || '').split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase();
}

function collBar(pct) {
    const cls = pct >= 70 ? '' : pct >= 40 ? ' warn' : ' alert';
    return `<div class="coll-wrap">
        <div class="coll-bar"><div class="coll-fill${cls}" style="width:${pct}%"></div></div>
        <span class="coll-pct">${pct}%</span>
    </div>`;
}

/* ─── running totals ────────────────────────────────────── */
let runOrig = 0, runPaid = 0, runOwed = 0, runSave = 0;

function resetTotals() { runOrig = runPaid = runOwed = runSave = 0; }

function updateFooter() {
    document.getElementById('footOriginal').textContent    = naira(runOrig);
    document.getElementById('footPaid').textContent        = naira(runPaid);
    document.getElementById('footOutstanding').textContent = naira(runOwed);
    document.getElementById('footSavings').textContent     = naira(runSave);
}

function updateKPIs(json) {
    let totalOwed = 0, totalPaid = 0, totalSave = 0;
    (json.data || []).forEach(r => {
        totalOwed += parseFloat(r.outstanding) || 0;
        totalPaid += parseFloat(r.amount_paid) || 0;
        totalSave += parseFloat(r.savings)     || 0;
    });
    document.getElementById('kpiDebtors').textContent     = (json.recordsFiltered || 0).toLocaleString();
    document.getElementById('kpiOutstanding').textContent  = naira(totalOwed);
    document.getElementById('kpiCollected').textContent    = naira(totalPaid);
    document.getElementById('kpiSavings').textContent      = naira(totalSave);
}

/* ─── DataTable init ────────────────────────────────────── */
$(function () {

    var dt = $('#debtorsTable').DataTable({
        processing: false,   // we handle our own loader
        serverSide: true,
        order: [[7, 'desc']], // sort by outstanding desc
        ajax: {
            url:  '{{ route("reports.financial.debtors") }}',
            type: 'GET',
            data: function (d) {
                d.class_id        = $('#classFilter').val();
                d.term_id         = $('#termFilter').val();
                d.session_id      = $('#sessionFilter').val();
                d.min_outstanding = $('#minOutstanding').val();
                d.search_value    = $('#searchInput').val();
            },
            beforeSend: function () {
                document.getElementById('tableLoading').style.display = 'flex';
                resetTotals();
            },
            dataSrc: function (json) {
                document.getElementById('tableLoading').style.display = 'none';
                document.getElementById('resultCount').textContent =
                    (json.recordsFiltered || 0).toLocaleString();
                updateKPIs(json);
                return json.data;
            },
            error: function () {
                document.getElementById('tableLoading').style.display = 'none';
            }
        },
        columns: [
            { data: 'DT_RowIndex',      orderable: false, searchable: false,
              render: (d) => `<span style="color:var(--d-muted);font-size:12px">${d}</span>` },
            { data: 'student_name',     name: 'student_name',
              render: function(d, t, r) {
                const ini = initials(d);
                return `<div class="student-cell">
                    <div class="student-initials">${ini}</div>
                    <div>
                        <div class="student-name">${d}</div>
                        <div class="student-adm">${r.admission_no || ''}</div>
                    </div>
                </div>`;
              }},
            { data: 'class_name',       name: 'class_name',
              render: d => d ? `<span class="class-badge">${d}</span>` : '—' },
            { data: 'bill_title',       name: 'bill_title',
              render: d => `<span style="font-size:13px;color:var(--d-navy)">${d || '—'}</span>` },
            { data: 'term_name',        name: 'term_name',
              render: (d, t, r) => `<div style="font-size:12px;line-height:1.5">
                <div style="color:var(--d-navy);font-weight:500">${r.term_name || '—'}</div>
                <div style="color:var(--d-muted)">${r.session_name || ''}</div></div>` },
            { data: 'original_amount',  name: 'original_amount', className: 'text-end',
              render: d => `<span class="amt amt-original">${naira(d)}</span>` },
            { data: 'amount_paid',      name: 'amount_paid',     className: 'text-end',
              render: d => `<span class="amt amt-paid">${naira(d)}</span>` },
            { data: 'outstanding',      name: 'outstanding',     className: 'text-end',
              render: d => `<span class="amt amt-outstanding">${naira(d)}</span>` },
            { data: 'savings',          name: 'savings',         className: 'text-end',
              render: d => `<span class="amt amt-savings">${naira(d)}</span>` },
            { data: 'collection_rate',  name: 'collection_rate', orderable: false,
              render: d => collBar(parseFloat(d) || 0) },
            { data: 'action',           name: 'action',          orderable: false, searchable: false,
              className: 'text-center' },
        ],
        rowCallback: function (row, data) {
            // accumulate page totals
            runOrig += parseFloat(String(data.original_amount).replace(/,/g, '')) || 0;
            runPaid += parseFloat(String(data.amount_paid).replace(/,/g, ''))     || 0;
            runOwed += parseFloat(String(data.outstanding).replace(/,/g, ''))     || 0;
            runSave += parseFloat(String(data.savings).replace(/,/g, ''))         || 0;
            // fade rows in
            row.classList.add('row-anim');
        },
        drawCallback: function () {
            updateFooter();
        },
        language: {
            emptyTable: `<div class="empty-state">
                <i class="ri-inbox-line"></i>
                <p>No debtors found matching your filters.</p>
            </div>`,
            zeroRecords: `<div class="empty-state">
                <i class="ri-search-line"></i>
                <p>No results for the selected filters.</p>
            </div>`,
            info:       '',
            infoEmpty:  '',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
        },
        dom: '<"export-bar-length"l>rtp',
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
    });

    /* move length menu into our bar */
    $('#debtorsTable_length').appendTo('.export-bar .d-flex');

    /* ── filter buttons ── */
    $('#applyFilters').on('click', function () { dt.ajax.reload(); });

    $('#searchInput').on('keydown', function (e) {
        if (e.key === 'Enter') dt.ajax.reload();
    });

    $('#resetFilters').on('click', function () {
        $('#classFilter, #termFilter, #sessionFilter').val('');
        $('#minOutstanding, #searchInput').val('');
        dt.ajax.reload();
    });

});

/* ── export ── */
function exportReport(fmt) {
    const params = new URLSearchParams({
        format:      fmt,
        class_id:    document.getElementById('classFilter').value,
        term_id:     document.getElementById('termFilter').value,
        session_id:  document.getElementById('sessionFilter').value,
    });
    window.open('{{ route("reports.financial.export", ["debtors", "pdf"]) }}?' + params.toString(), '_blank');
}
</script>
@endpush

@endsection
