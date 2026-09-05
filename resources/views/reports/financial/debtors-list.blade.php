@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
/* ── Design System ──────────────────────────────────────────────── */
:root {
    --ss-primary: #1e3a5f;
    --ss-accent:  #2563eb;
    --ss-success: #16a34a;
    --ss-warning: #d97706;
    --ss-danger:  #dc2626;
    --ss-muted:   #6b7280;
    --ss-border:  #e2e8f0;
    --ss-bg:      #f8fafc;
    --ss-card:    #ffffff;
    --ss-radius:  10px;
}

/* Loading Overlay */
#loadingOverlay {
    position: fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,.5);
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    z-index: 99999; display:none; justify-content:center; align-items:center;
}
#loadingOverlay.active { display:flex; }
.loading-content {
    background:rgba(255,255,255,.96); border-radius:20px;
    padding:30px 40px; text-align:center;
    box-shadow:0 20px 40px rgba(0,0,0,.2);
}
.loading-spinner {
    width:40px; height:40px;
    border:3px solid #e2e8f0; border-top-color:#2563eb;
    border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 15px;
}
@keyframes spin { to { transform:rotate(360deg); } }
.loading-text { font-size:14px; color:#1e3a5f; font-weight:500; }

/* Hero */
.report-hero {
    background:linear-gradient(135deg,var(--ss-primary) 0%,var(--ss-accent) 60%,#4f46e5 100%);
    border-radius:var(--ss-radius); padding:28px 32px; margin-bottom:24px;
    position:relative; overflow:hidden;
}
.report-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.report-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.report-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* Stat cards */
.stat-card {
    background:var(--ss-card); border:1px solid var(--ss-border);
    border-radius:var(--ss-radius); padding:16px 18px;
    transition:transform .15s, box-shadow .15s; height:100%;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.1); }
.stat-card .stat-value { font-size:24px; font-weight:700; color:var(--ss-primary); }
.stat-card .stat-label { font-size:11.5px; color:var(--ss-muted); margin-top:4px; }

/* Filter bar */
.filter-bar { background:#f8fafc; padding:20px; border-radius:12px; margin-bottom:24px; }
.filter-label { font-weight:600; font-size:13px; margin-bottom:8px; color:var(--ss-primary); }

/* Table */
.report-table th {
    background:var(--ss-primary); color:#fff;
    padding:12px 14px; font-size:12.5px; white-space:nowrap; font-weight:600;
}
.report-table td { padding:11px 14px; border-bottom:1px solid var(--ss-border); vertical-align:middle; }

/* Row entrance animation */
#debtorsTable tbody tr.student-row {
    opacity:0; transform:translateY(14px);
    transition:opacity .38s cubic-bezier(.25,.46,.45,.94),
               transform .38s cubic-bezier(.25,.46,.45,.94),
               background .18s ease;
}
#debtorsTable tbody tr.student-row.row-visible { opacity:1; transform:translateY(0); }

/* Row hover */
#debtorsTable tbody tr.student-row:hover {
    background:#fff5f5 !important;
    box-shadow:inset 3px 0 0 #dc2626;
}

/* Adjustment badges (scholarship / discount) */
.adj-badges { display:flex; flex-wrap:wrap; gap:4px; max-width:190px; }
.adj-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 9px; border-radius:20px; font-size:10.5px; font-weight:600;
    white-space:nowrap;
}
.adj-badge.scholarship { background:linear-gradient(135deg,#fde68a,#fcd34d); color:#92400e; }
.adj-badge.discount    { background:#dbeafe; color:#1e40af; }
.adj-badge.none        { background:#f1f5f9; color:#94a3b8; }

/* Bills toggle */
.bills-toggle {
    cursor:pointer; user-select:none;
    display:inline-flex; align-items:center; gap:6px;
    background:#f1f5f9; border:1px solid var(--ss-border); border-radius:20px;
    padding:5px 12px; font-size:12px; font-weight:600; color:var(--ss-primary);
    transition:background .15s;
}
.bills-toggle:hover { background:#e2e8f0; }
.bills-toggle .chevron { transition:transform .2s; font-size:13px; }
.bills-toggle.open .chevron { transform:rotate(180deg); }
tr.student-row.shown { background:#f8fafc; }

/* Child (drawer) row */
.child-row-wrap { padding:14px 20px 14px 74px; background:#f8fafc; }
.child-row-wrap .child-title { font-size:11px; font-weight:700; color:var(--ss-primary); text-transform:uppercase; letter-spacing:.3px; margin-bottom:8px; }
table.child-bill-table { width:100%; font-size:12.5px; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; border:1px solid var(--ss-border); }
table.child-bill-table th { text-align:left; color:var(--ss-muted); font-weight:600; padding:7px 12px; border-bottom:1px solid var(--ss-border); background:#fff; font-size:11px; text-transform:uppercase; }
table.child-bill-table td { padding:8px 12px; border-bottom:1px solid #eef2f7; }
table.child-bill-table tr:last-child td { border-bottom:none; }

/* Progress bar */
.progress-container { width:80px;background:#e2e8f0;border-radius:10px;overflow:hidden;margin:0 auto; }
.progress-fill      { height:5px;border-radius:10px;transition:width .3s; }
.progress-high   { background:#16a34a; }
.progress-medium { background:#d97706; }
.progress-low    { background:#dc2626; }
.progress-text   { font-size:10px;color:#6b7280;margin-top:2px;display:block; }

/* Avatars */
.student-avatar-table {
    width:36px;height:36px;border-radius:50%;object-fit:cover;
    border:2px solid #e2e8f0;cursor:pointer;
    transition:transform .18s,box-shadow .18s;
}
.student-avatar-placeholder {
    width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,#dc2626,#b91c1c);
    color:#fff;display:flex;align-items:center;justify-content:center;
    font-size:12px;font-weight:600;cursor:pointer;
    transition:transform .18s,box-shadow .18s;
}
#debtorsTable tbody tr:hover .student-avatar-table,
#debtorsTable tbody tr:hover .student-avatar-placeholder {
    transform:scale(1.1); box-shadow:0 2px 8px rgba(0,0,0,.15);
}

/* DataTables chrome */
.dataTables_wrapper .dataTables_length select {
    border:1.5px solid var(--ss-border);border-radius:8px;
    padding:6px 10px;margin:0 6px;font-size:13px;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background:var(--ss-accent)!important;border-color:var(--ss-accent)!important;color:#fff!important;
}
.dataTables_wrapper .dataTables_info { font-size:13px;color:var(--ss-muted); }

/* Image zoom modal */
.image-zoom-modal .modal-content { background:transparent;border:none; }
.zoomed-image { max-width:90vw;max-height:75vh;border-radius:16px;border:4px solid #fff;cursor:pointer; }
.btn-close-zoom {
    position:absolute;top:20px;right:30px;
    background:rgba(0,0,0,.7);border:none;border-radius:50%;
    width:38px;height:38px;color:#fff;font-size:18px;cursor:pointer;
}

@media (prefers-reduced-motion:reduce) {
    #debtorsTable tbody tr.student-row {
        transition:background .15s!important; transform:none!important; opacity:1!important;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading debtors data...</div>
        </div>
    </div>

    <div class="report-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="ri-file-list-3-line me-2"></i>Student Debtors Report</h1>
                <p>Students with outstanding fee balances — one row per student, grouped across every bill they owe</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light btn-sm" onclick="exportReport('csv')"><i class="ri-file-excel-line"></i> Excel/CSV</button>
                <button class="btn btn-light btn-sm" onclick="exportReport('pdf')"><i class="ri-printer-line"></i> Print / PDF</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg"><div class="stat-card"><div class="stat-value" id="totalDebtors">0</div><div class="stat-label">Total Debtors</div></div></div>
        <div class="col-6 col-lg"><div class="stat-card"><div class="stat-value text-danger" id="totalOutstanding">₦0</div><div class="stat-label">Total Outstanding</div></div></div>
        <div class="col-6 col-lg"><div class="stat-card"><div class="stat-value text-success" id="totalCollected">₦0</div><div class="stat-label">Total Collected</div></div></div>
        <div class="col-6 col-lg"><div class="stat-card"><div class="stat-value text-warning" id="totalSavings">₦0</div><div class="stat-label">Scholarship + Discount Savings</div></div></div>
        <div class="col-6 col-lg"><div class="stat-card"><div class="stat-value" style="color:#7c3aed" id="totalWithAid">0</div><div class="stat-label">Debtors on Scholarship/Discount</div></div></div>
    </div>

    <div class="filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="filter-label">Search <span class="text-muted fw-normal">(Name or Admission No.)</span></label>
                <input type="text" class="form-control" id="searchFilter" placeholder="e.g. John, or CSK/2024/001">
            </div>
            <div class="col-md-3">
                <label class="filter-label">Class</label>
                <select class="form-select" id="classFilter">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label">Term</label>
                <select class="form-select" id="termFilter">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label">Session</label>
                <select class="form-select" id="sessionFilter">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" id="applyFilters">
                    <i class="ri-search-line me-1"></i> Search
                </button>
                <button class="btn btn-outline-secondary" id="resetFilters" title="Reset filters">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold"><i class="ri-table-line me-2"></i>Debtors List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table report-table w-100" id="debtorsTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th width="55">Photo</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Term / Session</th>
                            <th>Scholarship / Discount</th>
                            <th class="text-center">Bills</th>
                            <th class="text-end">Original (₦)</th>
                            <th class="text-end">Paid (₦)</th>
                            <th class="text-end">Outstanding (₦)</th>
                            <th class="text-end">Savings (₦)</th>
                            <th width="90">Rate</th>
                            <th width="70">Action</th>
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

<!-- Image Zoom Modal -->
<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <button class="btn-close-zoom" data-bs-dismiss="modal">×</button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" class="zoomed-image">
                <div id="zoomedImageName" style="color:#fff;margin-top:18px;font-size:14px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ====================================================================
   STATE
   ==================================================================== */
let debtorsTable;
let allRowData = [];   // the currently-loaded, already-filtered dataset

/* ====================================================================
   HELPERS
   ==================================================================== */
function fmt(n) {
    return '₦' + parseFloat(n||0).toLocaleString('en-NG',{minimumFractionDigits:2});
}
function initials(name) {
    if (!name) return 'ST';
    return name.split(' ').slice(0,2).map(w=>w[0]||'').join('').toUpperCase();
}
function avatarUrl(pic) {
    if (!pic || pic==='unnamed.jpg') return null;
    return '/storage/images/student_avatars/'+pic;
}
function progressClass(p) {
    return p>=70?'progress-high':p>=40?'progress-medium':'progress-low';
}
function esc(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}
function showLoading(on) {
    document.getElementById('loadingOverlay').classList.toggle('active',on);
}

/* ====================================================================
   RENDERS
   ==================================================================== */
function renderAvatar(pic, name, adm) {
    const url=avatarUrl(pic), ini=initials(name);
    const n=esc(name), a=esc(adm);
    if (url) return `<img src="${url}" class="student-avatar-table" data-name="${n}" data-admission="${a}" data-avatar="${url}" alt="${n}">`;
    return `<div class="student-avatar-placeholder" data-name="${n}" data-admission="${a}" data-avatar="">${ini}</div>`;
}

function renderProgress(p) {
    const cls=progressClass(p);
    return `<div class="progress-container"><div class="progress-fill ${cls}" style="width:${p}%"></div></div><span class="progress-text">${p}%</span>`;
}

function renderAdjustments(row) {
    let html = '';
    if (row.scholarship) {
        html += `<span class="adj-badge scholarship" title="${esc(row.scholarship.title)}"><i class="ri-award-fill"></i>${esc(row.scholarship.title)}</span>`;
    }
    (row.discounts || []).forEach(d => {
        html += `<span class="adj-badge discount" title="${esc(d.title)}"><i class="ri-price-tag-3-fill"></i>${esc(d.title)}</span>`;
    });
    if (!html) html = '<span class="adj-badge none">—</span>';
    return `<div class="adj-badges">${html}</div>`;
}

function renderBillsToggle(row) {
    const count = row.bill_count || (row.bills ? row.bills.length : 0);
    return `<span class="bills-toggle"><i class="ri-file-list-3-line"></i>${count} bill${count===1?'':'s'} <i class="ri-arrow-down-s-line chevron"></i></span>`;
}

function renderChildBills(row) {
    if (!row.bills || !row.bills.length) {
        return '<div class="child-row-wrap text-muted">No bill breakdown available.</div>';
    }
    const rowsHtml = row.bills.map(b => `
        <tr>
            <td>${esc(b.title || 'Bill')}</td>
            <td class="text-end">${fmt(b.original_amount)}</td>
            <td class="text-end text-success">${fmt(b.amount_paid)}</td>
            <td class="text-end text-danger fw-semibold">${fmt(b.outstanding)}</td>
            <td class="text-end">${parseFloat(b.savings||0) > 0 ? fmt(b.savings) : '—'}</td>
        </tr>`).join('');

    return `
        <div class="child-row-wrap">
            <div class="child-title">Bills owing for ${esc(row.student_name)}</div>
            <table class="child-bill-table">
                <thead>
                    <tr><th>Bill</th><th class="text-end">Original</th><th class="text-end">Paid</th><th class="text-end">Outstanding</th><th class="text-end">Savings</th></tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        </div>`;
}

/* ====================================================================
   STATS – totals from the currently loaded (already filtered) dataset
   ==================================================================== */
function updateStats() {
    let outstanding=0, paid=0, savings=0, withAid=0;
    allRowData.forEach(r=>{
        outstanding += parseFloat(r.outstanding)||0;
        paid        += parseFloat(r.amount_paid)||0;
        savings     += parseFloat(r.savings)    ||0;
        if (r.scholarship || (r.discounts && r.discounts.length)) withAid++;
    });
    document.getElementById('totalDebtors').textContent     = allRowData.length;
    document.getElementById('totalOutstanding').textContent = fmt(outstanding);
    document.getElementById('totalCollected').textContent   = fmt(paid);
    document.getElementById('totalSavings').textContent     = fmt(savings);
    document.getElementById('totalWithAid').textContent     = withAid;
}

/* ====================================================================
   ROW ENTRANCE ANIMATION
   ==================================================================== */
function initRowEntrance() {
    const rows=document.querySelectorAll('#debtorsTable tbody tr.student-row');
    if (!rows.length) return;
    rows.forEach(r=>r.classList.remove('row-visible'));
    if (window.matchMedia('(prefers-reduced-motion:reduce)').matches) {
        rows.forEach(r=>r.classList.add('row-visible')); return;
    }
    const obs=new IntersectionObserver(entries=>{
        entries.forEach(e=>{
            if (!e.isIntersecting) return;
            const row=e.target;
            const idx=Array.from(rows).indexOf(row);
            setTimeout(()=>row.classList.add('row-visible'), Math.min(idx*38,15*38)+60);
            obs.unobserve(row);
        });
    },{threshold:.05,rootMargin:'0px 0px -20px 0px'});
    rows.forEach(r=>obs.observe(r));
}

/* ====================================================================
   DATATABLE
   ──────────────────────────────────────────────────────────────────────
   serverSide:false because the controller returns the full grouped
   dataset for the applied filters at once. Pagination and sorting run
   client-side; filtering (name/admission/class/term/session) is done
   server-side via the filter bar so the stat cards stay accurate.
   ==================================================================== */
$(document).ready(function(){

    debtorsTable = $('#debtorsTable').DataTable({
        processing: false,
        serverSide: false,
        searching: false,   // custom Search field above replaces the default box
        deferRender: true,
        ajax: {
            url:  '{{ route("reports.financial.debtors") }}',
            type: 'GET',
            data: function(d){
                d.class_id   = $('#classFilter').val()  || '';
                d.term_id    = $('#termFilter').val()   || '';
                d.session_id = $('#sessionFilter').val()|| '';
                d.search     = $('#searchFilter').val() || '';
            },
            beforeSend: function(){ showLoading(true); },
            complete:   function(){ showLoading(false); },
            dataSrc: function(resp){
                allRowData = resp.data || [];
                updateStats();
                return allRowData;
            }
        },
        columns: [
            { data:null, orderable:false, render:(d,t,r,meta)=>meta.row+1 },
            { data:null, orderable:false, render:(d,t,row)=>renderAvatar(row.avatar, row.student_name, row.admission_no) },
            { data:null, render:(d,t,row)=>`<div class="fw-semibold" style="color:var(--ss-primary)">${esc(row.student_name)}</div><div class="text-muted small">Adm: ${esc(row.admission_no)}</div>` },
            { data:'class_name' },
            { data:null, render:(d,t,row)=>`${esc(row.term_name)}<div class="text-muted small">${esc(row.session_name)}</div>` },
            { data:null, orderable:false, render:(d,t,row)=>renderAdjustments(row) },
            { data:null, orderable:false, className:'text-center', render:(d,t,row)=>renderBillsToggle(row) },
            { data:'original_amount', className:'text-end', render:d=>`<span class="fw-semibold">${fmt(d)}</span>` },
            { data:'amount_paid', className:'text-end', render:d=>`<span class="text-success">${fmt(d)}</span>` },
            { data:'outstanding', className:'text-end', render:d=>`<span class="text-danger fw-bold">${fmt(d)}</span>` },
            { data:'savings', className:'text-end', render:d=>parseFloat(d||0)>0?`<span class="text-warning">${fmt(d)}</span>`:'<span class="text-muted">—</span>' },
            { data:'collection_rate', render:d=>renderProgress(parseFloat(d)||0) },
            { data:null, orderable:false,
              render:(d,t,row)=>`<a href="/reports/analysis/student/${row.student_id}/${row.class_id}/${row.term_id}/${row.session_id}"
                                     class="btn btn-sm btn-outline-danger" target="_blank" title="View student report">
                                     <i class="ri-eye-line"></i>
                                  </a>`
            }
        ],
        createdRow: function(row, data){
            $(row).addClass('student-row');
            row.setAttribute('data-student-id', data.student_id || '');
        },
        drawCallback: function(){
            setTimeout(()=>{ initRowEntrance(); }, 60);
        },
        language: {
            emptyTable:   '<div class="text-center py-5 text-muted"><i class="ri-inbox-line" style="font-size:2rem;display:block;margin-bottom:8px"></i>No debtors found for the selected filters.</div>',
            processing:   '',
            lengthMenu:   'Show _MENU_ entries',
            info:         'Showing _START_ to _END_ of _TOTAL_ debtors',
            infoEmpty:    'No records found',
            infoFiltered: ''
        },
        pageLength: 25,
        order: [[9,'desc']]   // sort by outstanding desc by default
    });

    /* ── Expand/collapse bills drawer ──────────────────────────────── */
    $('#debtorsTable tbody').on('click', '.bills-toggle', function () {
        const tr  = $(this).closest('tr');
        const row = debtorsTable.row(tr);
        const btn = $(this);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            btn.removeClass('open');
        } else {
            row.child(renderChildBills(row.data())).show();
            tr.addClass('shown');
            btn.addClass('open');
        }
    });

    /* ── Filters ─────────────────────────────────────────────────── */
    function reloadTable() {
        debtorsTable.ajax.reload();
    }

    $('#applyFilters').on('click', reloadTable);
    $('#classFilter,#termFilter,#sessionFilter').on('change', reloadTable);

    let searchDebounce;
    $('#searchFilter').on('keyup', function (e) {
        clearTimeout(searchDebounce);
        if (e.key === 'Enter') { reloadTable(); return; }
        searchDebounce = setTimeout(reloadTable, 450);
    });

    $('#resetFilters').on('click', function () {
        $('#searchFilter').val('');
        $('#classFilter,#termFilter,#sessionFilter').val('');
        reloadTable();
    });
});

/* ====================================================================
   EXPORT / PRINT — reuses whatever filters are currently applied so the
   PDF/CSV always matches exactly what's on screen.
   ==================================================================== */
function exportReport(format) {
    const params = new URLSearchParams({
        class_id:   $('#classFilter').val()   || '',
        term_id:    $('#termFilter').val()    || '',
        session_id: $('#sessionFilter').val() || '',
        search:     $('#searchFilter').val()  || '',
    });
    const url = '{{ url("reports/financial/export") }}/debtors/' + format + '?' + params.toString();
    window.open(url, '_blank');
}

/* ====================================================================
   AVATAR CLICK → IMAGE ZOOM MODAL
   ==================================================================== */
$(document).on('click','.student-avatar-table,.student-avatar-placeholder',function(){
    const isImg  = $(this).is('img');
    const imgUrl = isImg ? $(this).attr('src') : null;
    const name   = $(this).data('name');
    const adm    = $(this).data('admission');

    $('#zoomedImageName').text(name+' ('+adm+')');

    if (imgUrl) {
        $('#zoomedImage').attr('src', imgUrl);
    } else {
        const ini=initials(name);
        const cv=document.createElement('canvas');
        cv.width=cv.height=400;
        const ctx=cv.getContext('2d');
        const g=ctx.createLinearGradient(0,0,400,400);
        g.addColorStop(0,'#dc2626'); g.addColorStop(1,'#7f1d1d');
        ctx.fillStyle=g; ctx.fillRect(0,0,400,400);
        ctx.fillStyle='#fff'; ctx.font='bold 160px Arial,sans-serif';
        ctx.textAlign='center'; ctx.textBaseline='middle';
        ctx.fillText(ini.substring(0,2),200,200);
        $('#zoomedImage').attr('src',cv.toDataURL());
    }
    $('#imageZoomModal').modal('show');
});
$(document).on('click','#zoomedImage',()=>$('#imageZoomModal').modal('hide'));
</script>
@endsection