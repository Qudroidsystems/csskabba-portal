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
    border-radius:var(--ss-radius); padding:18px 20px;
    transition:transform .15s, box-shadow .15s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.1); }
.stat-card .stat-value { font-size:28px; font-weight:700; color:var(--ss-primary); }
.stat-card .stat-label { font-size:12px; color:var(--ss-muted); margin-top:4px; }

/* Filter bar */
.filter-bar { background:#f8fafc; padding:20px; border-radius:12px; margin-bottom:24px; }
.filter-label { font-weight:600; font-size:13px; margin-bottom:8px; color:var(--ss-primary); }
.filter-label .required { color:#dc2626; }

/* Table */
.report-table th {
    background:var(--ss-primary); color:#fff;
    padding:12px 16px; font-size:13px; white-space:nowrap; font-weight:600;
}
.report-table td { padding:12px 16px; border-bottom:1px solid var(--ss-border); vertical-align:middle; }

/* Row entrance animation */
#debtorsTable tbody tr {
    opacity:0; transform:translateY(14px);
    transition:opacity .38s cubic-bezier(.25,.46,.45,.94),
               transform .38s cubic-bezier(.25,.46,.45,.94),
               background .18s ease;
}
#debtorsTable tbody tr.row-visible { opacity:1; transform:translateY(0); }

/* Row hover */
#debtorsTable tbody tr:hover {
    background:#fff5f5 !important;
    box-shadow:inset 3px 0 0 #dc2626;
    transform:translateY(-1px) !important;
    transition:background .14s, box-shadow .18s, transform .18s cubic-bezier(.34,1.4,.64,1);
    position:relative; z-index:1;
}
#debtorsTable tbody tr:hover .student-avatar-table {
    transform:scale(1.12); box-shadow:0 2px 8px rgba(0,0,0,.15);
}

/* Badges */
.status-partial { background:#fef3c7!important;color:#d97706!important;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;display:inline-block; }
.status-unpaid  { background:#fee2e2!important;color:#dc2626!important;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;display:inline-block; }

/* Progress bar */
.progress-container { width:80px;background:#e2e8f0;border-radius:10px;overflow:hidden;margin:0 auto; }
.progress-fill      { height:5px;border-radius:10px;transition:width .3s; }
.progress-high   { background:#16a34a; }
.progress-medium { background:#d97706; }
.progress-low    { background:#dc2626; }
.progress-text   { font-size:10px;color:#6b7280;margin-top:2px;display:block; }

/* Avatars */
.student-avatar-table {
    width:35px;height:35px;border-radius:50%;object-fit:cover;
    border:2px solid #e2e8f0;cursor:pointer;
    transition:transform .18s,box-shadow .18s;
}
.student-avatar-placeholder {
    width:35px;height:35px;border-radius:50%;
    background:linear-gradient(135deg,#dc2626,#b91c1c);
    color:#fff;display:flex;align-items:center;justify-content:center;
    font-size:12px;font-weight:600;cursor:pointer;
    transition:transform .18s,box-shadow .18s;
}

/* ─────────────────────────────────────────────────────────────────
   TOOLTIP
   • #studentPopover lives directly inside <body>, outside all
     overflow:hidden containers — never clipped.
   • position:fixed → getBoundingClientRect() values plug in directly,
     no scroll offset arithmetic needed.
   • pointer-events:none → tooltip never intercepts mouse events,
     so mouseleave on rows fires every time.
   ──────────────────────────────────────────────────────────────── */
#studentPopover {
    position: fixed;
    z-index: 999999;
    pointer-events: none;
    opacity: 0;
    transform: scale(.92) translateY(6px);
    transition: opacity .22s cubic-bezier(.4,0,.2,1),
                transform .22s cubic-bezier(.4,0,.2,1);
    width: 280px;
    top: -9999px; left: -9999px;
}
#studentPopover.visible { opacity:1; transform:scale(1) translateY(0); }

.popover-card {
    background:#fff;
    border-radius:20px;
    box-shadow:0 0 0 .5px rgba(0,0,0,.1), 0 8px 32px rgba(0,0,0,.2);
    overflow:hidden;
}
.popover-header { background:linear-gradient(135deg,#7f1d1d,#dc2626); padding:16px; }
.popover-avatar-wrapper { display:flex;align-items:center;gap:12px; }
.popover-avatar { width:56px;height:56px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.9); }
.popover-name   { font-size:14px;font-weight:700;color:#fff; }
.popover-adm    { font-size:10px;color:rgba(255,255,255,.75); }
.popover-body   { padding:12px 16px; }
.popover-stats-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px; }
.popover-stat     { background:#f8fafc;border-radius:10px;padding:8px;text-align:center; }
.popover-stat-val { font-size:13px;font-weight:700;color:#7f1d1d;display:block; }
.popover-stat-lbl { font-size:9px;color:#9ca3af;display:block; }
.popover-bill-list { max-height:150px;overflow-y:auto; }
.popover-bill-row  { display:flex;justify-content:space-between;padding:6px 8px;border-bottom:1px solid #e2e8f0;font-size:11px; }
.popover-bill-row .owing { color:#dc2626; font-weight:600; }

/* DataTables chrome */
.dataTables_wrapper .dataTables_filter { margin-bottom:15px; }
.dataTables_wrapper .dataTables_filter input {
    border:1.5px solid var(--ss-border);border-radius:8px;
    padding:8px 14px;margin-left:8px;font-size:13px;width:250px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color:var(--ss-accent);outline:none;box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
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
    #debtorsTable tbody tr, #debtorsTable tbody tr:hover {
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
                <p>Students with outstanding fee balances</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light btn-sm" onclick="exportReport('pdf')"><i class="ri-file-pdf-line"></i> PDF</button>
                <button class="btn btn-light btn-sm" onclick="exportReport('excel')"><i class="ri-file-excel-line"></i> Excel</button>
                <button class="btn btn-light btn-sm" onclick="window.print()"><i class="ri-printer-line"></i> Print</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-value" id="totalDebtors">0</div><div class="stat-label">Total Debtors</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value text-danger" id="totalOutstanding">₦0</div><div class="stat-label">Total Outstanding</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value text-success" id="totalCollected">₦0</div><div class="stat-label">Total Collected</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value text-warning" id="totalSavings">₦0</div><div class="stat-label">Total Savings</div></div></div>
    </div>

    <div class="filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="filter-label">Class</label>
                <select class="form-select" id="classFilter">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Term</label>
                <select class="form-select" id="termFilter">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Session</label>
                <select class="form-select" id="sessionFilter">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" id="applyFilters">
                    <i class="ri-search-line me-1"></i> Search
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
                            <th width="50">#</th>
                            <th width="60">Photo</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Bill</th>
                            <th>Term / Session</th>
                            <th class="text-end">Original (₦)</th>
                            <th class="text-end">Paid (₦)</th>
                            <th class="text-end">Outstanding (₦)</th>
                            <th class="text-end">Savings (₦)</th>
                            <th width="100">Rate</th>
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

{{--
    Tooltip placed directly inside <body>, outside all containers.
    position:fixed in CSS; JS uses getBoundingClientRect() directly.
    pointer-events:none so it never blocks row hover events.
--}}
<div id="studentPopover">
    <div class="popover-card">
        <div class="popover-header">
            <div class="popover-avatar-wrapper">
                <img id="popAvatar" src="" alt="" class="popover-avatar">
                <div>
                    <div class="popover-name" id="popName">—</div>
                    <div class="popover-adm"  id="popAdm">—</div>
                </div>
            </div>
        </div>
        <div class="popover-body">
            <div class="popover-stats-grid">
                <div class="popover-stat"><span class="popover-stat-val" id="popOriginal">—</span><span class="popover-stat-lbl">Original</span></div>
                <div class="popover-stat"><span class="popover-stat-val" id="popPaid">—</span><span class="popover-stat-lbl">Paid</span></div>
                <div class="popover-stat"><span class="popover-stat-val" id="popOwing">—</span><span class="popover-stat-lbl">Owing</span></div>
            </div>
            <div class="popover-bill-list" id="popBillList"></div>
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
let currentFilters = {};
let studentData    = {};   // keyed by a unique row key  – tooltip lookups
let allRowData     = [];   // all rows returned           – stat totals

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

function renderStatus(outstanding) {
    const o = parseFloat(outstanding)||0;
    if (o<=0) return '';
    // All rows in debtors report are either Partial or Unpaid
    return o > 0
        ? '<span class="status-unpaid"><i class="ri-error-warning-line me-1"></i>Owing</span>'
        : '';
}

/* ====================================================================
   STATS – totals from the FULL dataset, not just the visible page
   ==================================================================== */
function updateStats() {
    let outstanding=0, paid=0, savings=0;
    // De-duplicate by student_id for debtor count
    const uniqueStudents = new Set();
    allRowData.forEach(r=>{
        outstanding += parseFloat(r.outstanding)||0;
        paid        += parseFloat(r.amount_paid)||0;
        savings     += parseFloat(r.savings)    ||0;
        if (r.student_id) uniqueStudents.add(r.student_id);
    });
    document.getElementById('totalDebtors').textContent    = uniqueStudents.size || allRowData.length;
    document.getElementById('totalOutstanding').textContent= fmt(outstanding);
    document.getElementById('totalCollected').textContent  = fmt(paid);
    document.getElementById('totalSavings').textContent    = fmt(savings);
}

/* ====================================================================
   ROW ENTRANCE ANIMATION
   ==================================================================== */
function initRowEntrance() {
    const rows=document.querySelectorAll('#debtorsTable tbody tr');
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
   TOOLTIP
   ==================================================================== */
const popEl   = document.getElementById('studentPopover');
let popTimer  = null;
let hideTimer = null;

function fillPopover(rowKey) {
    const s=studentData[rowKey];
    if (!s) return;
    document.getElementById('popName').textContent     = s.name;
    document.getElementById('popAdm').textContent      = 'Adm: '+s.admission;
    document.getElementById('popOriginal').textContent = fmt(s.original);
    document.getElementById('popPaid').textContent     = fmt(s.paid);
    document.getElementById('popOwing').textContent    = fmt(s.outstanding);
    document.getElementById('popAvatar').src           = avatarUrl(s.avatar)||'';

    const list=document.getElementById('popBillList');
    list.innerHTML='';
    if (s.bills && s.bills.length) {
        s.bills.forEach(b=>{
            list.innerHTML+=`<div class="popover-bill-row">
                <span>${esc(b.title)}</span>
                <span class="owing">${fmt(b.outstanding)}</span>
            </div>`;
        });
    } else if (s.bill_title) {
        list.innerHTML=`<div class="popover-bill-row">
            <span>${esc(s.bill_title)}</span>
            <span class="owing">${fmt(s.outstanding)}</span>
        </div>`;
    } else {
        list.innerHTML='<div class="popover-bill-row text-muted">No bill detail</div>';
    }
}

function positionPopover(rect) {
    const pw=280, ph=320;
    const vw=window.innerWidth, vh=window.innerHeight;
    let left=rect.left, top=rect.bottom+8;
    if (left+pw > vw-8) left=vw-pw-8;
    if (left < 8)       left=8;
    if (top+ph > vh-8)  top=rect.top-ph-8;
    if (top < 8)        top=8;
    popEl.style.left=left+'px';
    popEl.style.top =top +'px';
}

function showPopover(row) {
    clearTimeout(hideTimer);
    const key=row.getAttribute('data-row-key');
    if (!key || !studentData[key]) return;
    fillPopover(key);
    positionPopover(row.getBoundingClientRect());
    popEl.classList.add('visible');
}
function hidePopover() {
    hideTimer=setTimeout(()=>popEl.classList.remove('visible'), 180);
}

/* ====================================================================
   ROW EVENT BINDING
   ==================================================================== */
function attachRowEvents() {
    $('#debtorsTable tbody tr').off('mouseenter mouseleave');
    $('#debtorsTable tbody tr').on('mouseenter',function(){
        const row=this;
        clearTimeout(popTimer);
        popTimer=setTimeout(()=>showPopover(row), 260);
    }).on('mouseleave',function(){
        clearTimeout(popTimer);
        hidePopover();
    });
}

/* ====================================================================
   DATATABLE
   ──────────────────────────────────────────────────────────────────────
   serverSide:false because the controller returns all matching rows
   at once (it does not slice by start/length). Pagination, search,
   and sorting all run client-side on the full dataset.
   ==================================================================== */
$(document).ready(function(){

    debtorsTable = $('#debtorsTable').DataTable({
        processing: false,
        serverSide: false,
        deferRender: true,
        ajax: {
            url:  '{{ route("reports.financial.debtors") }}',
            type: 'GET',
            data: function(d){
                d.class_id   = $('#classFilter').val()  || '';
                d.term_id    = $('#termFilter').val()   || '';
                d.session_id = $('#sessionFilter').val()|| '';
            },
            beforeSend: function(){ showLoading(true); },
            complete:   function(){ showLoading(false); },
            dataSrc: function(resp){
                allRowData  = resp.data || [];
                studentData = {};

                // Build tooltip cache keyed by a unique row key
                // (student_id + bill_id handles one-student-multiple-bills)
                allRowData.forEach((item,idx)=>{
                    const key = (item.student_id||idx)+'_'+(item.bill_id||idx);
                    item._rowKey = key;
                    // Aggregate per student for tooltip summary
                    if (!studentData[key]) {
                        studentData[key]={
                            name:       item.student_name,
                            admission:  item.admission_no,
                            avatar:     item.avatar,
                            original:   item.original_amount,
                            paid:       item.amount_paid,
                            outstanding:item.outstanding,
                            bill_title: item.bill_title,
                            bills: item.bills || []
                        };
                    }
                });

                updateStats();
                return allRowData;
            }
        },
        columns: [
            { data:null, orderable:false,
              render:(d,t,r,meta)=>meta.row+1 },
            { data:null, orderable:false,
              render:(d,t,row)=>renderAvatar(row.avatar, row.student_name, row.admission_no) },
            { data:'student_name' },
            { data:'class_name' },
            { data:'bill_title' },
            { data:'term_name' },
            { data:'original_amount', className:'text-end',
              render:d=>`<span class="fw-semibold">${fmt(d)}</span>` },
            { data:'amount_paid', className:'text-end',
              render:d=>`<span class="text-success">${fmt(d)}</span>` },
            { data:'outstanding', className:'text-end',
              render:d=>`<span class="text-danger fw-bold">${fmt(d)}</span>` },
            { data:'savings', className:'text-end',
              render:d=>parseFloat(d||0)>0?`<span class="text-warning">${fmt(d)}</span>`:'<span class="text-muted">—</span>' },
            { data:'collection_rate',
              render:d=>renderProgress(parseFloat(d)||0) },
            { data:null, orderable:false,
              render:(d,t,row)=>{
                // Action button – link to student detail if route exists,
                // otherwise a plain info button
                const sid  = row.student_id   || '';
                const cid  = row.class_id     || '';
                const tid  = row.term_id      || '';
                const sess = row.session_id   || '';
                return `<a href="/reports/analysis/student/${sid}/${cid}/${tid}/${sess}"
                           class="btn btn-sm btn-outline-danger" target="_blank">
                           <i class="ri-eye-line"></i>
                        </a>`;
              }
            }
        ],
        createdRow: function(row, data){
            // Stamp row key so the tooltip can look up studentData
            const key=(data.student_id||'')+'_'+(data.bill_id||'');
            row.setAttribute('data-row-key', data._rowKey || key);
            row.setAttribute('data-student-id', data.student_id||'');
        },
        drawCallback: function(){
            setTimeout(()=>{
                initRowEntrance();
                attachRowEvents();
            }, 60);
        },
        language: {
            emptyTable:   '<div class="text-center py-5 text-muted"><i class="ri-inbox-line" style="font-size:2rem;display:block;margin-bottom:8px"></i>No debtors found for the selected filters.</div>',
            processing:   '',
            search:       'Search:',
            searchPlaceholder:'Filter by name, class, bill…',
            lengthMenu:   'Show _MENU_ entries',
            info:         'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty:    'No records found',
            infoFiltered: '(filtered from _MAX_ total)'
        },
        pageLength: 25,
        searchDelay: 400,
        order: [[8,'desc']]   // sort by outstanding desc by default
    });

    /* ── Apply filters ───────────────────────────────────────────── */
    $('#applyFilters').on('click',function(){
        allRowData  = [];
        studentData = {};
        debtorsTable.ajax.reload();
    });

    // Also reload when any filter changes (matches original behaviour)
    $('#classFilter,#termFilter,#sessionFilter').on('change',function(){
        allRowData  = [];
        studentData = {};
        debtorsTable.ajax.reload();
    });
});

/* ====================================================================
   EXPORT
   ==================================================================== */
function exportReport(format) {
    const classId   = $('#classFilter').val()  || '';
    const termId    = $('#termFilter').val()   || '';
    const sessionId = $('#sessionFilter').val()|| '';
    const url = '{{ url("reports/financial/export") }}/debtors/'+format
        +'?class_id='   + classId
        +'&term_id='    + termId
        +'&session_id=' + sessionId;
    window.open(url,'_blank');
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
