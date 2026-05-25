@extends('layouts.master')

@section('content')
<style>
/* ══════════════════════════════════════════════════════════════════════
   LOCK MANAGEMENT — Design System
   ══════════════════════════════════════════════════════════════════════ */
:root {
    --lm-primary:   #1e3a5f;
    --lm-accent:    #2563eb;
    --lm-success:   #16a34a;
    --lm-warning:   #d97706;
    --lm-danger:    #dc2626;
    --lm-muted:     #6b7280;
    --lm-border:    #e2e8f0;
    --lm-bg:        #f8fafc;
    --lm-card:      #ffffff;
    --lm-radius:    12px;
    --lm-shadow:    0 2px 12px rgba(0,0,0,.07);
}

/* ── Reset & base ─────────────────────────────────────────────────── */
.lm-wrap * { box-sizing: border-box; }

/* ── Animations ───────────────────────────────────────────────────── */
.spin { animation: lm-spin .8s linear infinite; }
@keyframes lm-spin  { to { transform: rotate(360deg); } }
@keyframes lm-fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
@keyframes lm-slideRight { from { transform:translateX(100%); opacity:0; } to { transform:none; opacity:1; } }
@keyframes lm-pop { 0%{transform:scale(1)} 40%{transform:scale(1.18)} 100%{transform:scale(1)} }

/* ── Hero banner ──────────────────────────────────────────────────── */
.lm-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--lm-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    animation: lm-fadeIn .5s ease both;
}
.lm-hero::before, .lm-hero::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
}
.lm-hero::before { width: 260px; height: 260px; top: -80px; right: -60px; }
.lm-hero::after  { width: 140px; height: 140px; bottom: -50px; right: 120px; }
.lm-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.lm-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }
.lm-hero .hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
    color: #fff; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600;
    margin-top: 12px; backdrop-filter: blur(4px); position: relative;
}

/* ── Stat cards ───────────────────────────────────────────────────── */
.stat-card {
    background: var(--lm-card);
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    transition: transform .18s cubic-bezier(.34,1.4,.64,1), box-shadow .18s ease;
    animation: lm-fadeIn .5s ease both;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.stat-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: var(--lm-radius) var(--lm-radius) 0 0;
}
.stat-card.c-total::before   { background: var(--lm-accent); }
.stat-card.c-open::before    { background: var(--lm-success); }
.stat-card.c-indiv::before   { background: var(--lm-warning); }
.stat-card.c-global::before  { background: var(--lm-danger); }
.stat-card.c-disabled::before{ background: var(--lm-muted); }
.stat-value { font-size: 28px; font-weight: 800; color: var(--lm-primary); line-height: 1; }
.stat-label { font-size: 11px; font-weight: 600; color: var(--lm-muted); margin-top: 6px; text-transform: uppercase; letter-spacing: .4px; }
.stat-icon  {
    position: absolute; top: 14px; right: 16px;
    font-size: 34px; opacity: .08; line-height: 1;
    transition: opacity .2s ease, transform .2s ease;
}
.stat-card:hover .stat-icon { opacity: .16; transform: scale(1.1) rotate(-5deg); }

/* ── Filter card ──────────────────────────────────────────────────── */
.filter-card {
    background: var(--lm-card);
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    padding: 20px 24px;
    margin-bottom: 20px;
    box-shadow: var(--lm-shadow);
    animation: lm-fadeIn .5s .1s ease both;
}
.filter-group label {
    font-size: 11px; font-weight: 700; margin-bottom: 6px; color: #475569;
    text-transform: uppercase; letter-spacing: .4px; display: block;
}
.filter-group input,
.filter-group select {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #cbd5e1; border-radius: 8px;
    font-size: 13px; transition: border-color .2s, box-shadow .2s;
    background: #fff; color: #1e293b;
}
.filter-group input:focus,
.filter-group select:focus {
    outline: none; border-color: var(--lm-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
}

/* ── Action bar ───────────────────────────────────────────────────── */
.action-bar {
    background: var(--lm-card);
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
    box-shadow: var(--lm-shadow);
    animation: lm-fadeIn .5s .15s ease both;
}
.selected-info {
    font-size: 13px; color: var(--lm-muted);
    margin-right: auto; background: #f1f5f9;
    padding: 6px 16px; border-radius: 20px;
    transition: background .2s, color .2s;
}
.selected-info.has-selection { background: #dbeafe; color: #1d4ed8; }
.selected-info strong { font-weight: 700; }

/* ── Buttons ──────────────────────────────────────────────────────── */
.btn {
    padding: 8px 18px; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all .18s cubic-bezier(.34,1.4,.64,1);
    display: inline-flex; align-items: center; gap: 7px;
    text-decoration: none; white-space: nowrap;
}
.btn-sm { padding: 7px 14px; font-size: 12px; }
.btn-primary   { background: var(--lm-accent); color: #fff; }
.btn-primary:hover   { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,.35); color: #fff; }
.btn-success   { background: #10b981; color: #fff; }
.btn-success:hover   { background: #059669; box-shadow: 0 4px 12px rgba(16,185,129,.35); }
.btn-warning   { background: #f59e0b; color: #fff; }
.btn-warning:hover   { background: #d97706; box-shadow: 0 4px 12px rgba(245,158,11,.35); }
.btn-danger    { background: #ef4444; color: #fff; }
.btn-danger:hover    { background: #dc2626; box-shadow: 0 4px 12px rgba(239,68,68,.35); }
.btn-secondary { background: #64748b; color: #fff; }
.btn-secondary:hover { background: #475569; box-shadow: 0 4px 12px rgba(100,116,139,.3); }
.btn-outline   { background: transparent; border: 1.5px solid #cbd5e1; color: #475569; }
.btn-outline:hover   { background: #f8fafc; border-color: #94a3b8; color: #1e293b; }
.btn:disabled  { opacity: .45; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

/* ── Status badges ────────────────────────────────────────────────── */
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
}
.status-open       { background: #d1fae5; color: #065f46; }
.status-individual { background: #fef3c7; color: #92400e; }
.status-global     { background: #fee2e2; color: #991b1b; }
.status-disabled   { background: #e5e7eb; color: #374151; }

/* ── Table card ───────────────────────────────────────────────────── */
.table-card {
    background: var(--lm-card);
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    overflow: hidden;
    box-shadow: var(--lm-shadow);
    animation: lm-fadeIn .5s .2s ease both;
    width: 100%; /* fix width alignment */
}
.table-card .card-header {
    background: var(--lm-primary);
    padding: 16px 22px;
    display: flex; align-items: center; justify-content: space-between;
}
.table-card .card-header h5 {
    color: #fff; margin: 0; font-size: 15px; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
}
.table-card .card-header .badge {
    background: rgba(255,255,255,.2); color: #fff;
    border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 700;
}

/* ── Table itself ─────────────────────────────────────────────────── */
#scoresheetsTable { font-size: 12.5px; width: 100%; border-collapse: collapse; }
#scoresheetsTable thead tr { background: var(--lm-primary); }
#scoresheetsTable thead th {
    color: #fff; padding: 11px 14px; font-weight: 600;
    font-size: 12px; white-space: nowrap; border: none;
}
#scoresheetsTable thead th:first-child { border-radius: 0; }

/* ── Row animations (mirrors scoresheet blade) ────────────────────── */
#scoresheetsTableBody tr[data-id] {
    opacity: 0; transform: translateY(12px);
    transition: opacity .36s cubic-bezier(.25,.46,.45,.94),
                transform .36s cubic-bezier(.25,.46,.45,.94),
                background .15s ease;
    will-change: opacity, transform;
}
#scoresheetsTableBody tr[data-id].row-visible { opacity: 1; transform: translateY(0); }

#scoresheetsTableBody tr[data-id] td {
    padding: 11px 14px; vertical-align: middle;
    border-bottom: 1px solid var(--lm-border);
    transition: background .14s ease;
}

/* hover — left accent stripe like scoresheet */
#scoresheetsTableBody tr[data-id]:hover td {
    background: #f0f6ff !important;
}
#scoresheetsTableBody tr[data-id]:hover td:first-child {
    box-shadow: inset 3px 0 0 var(--lm-accent);
}
#scoresheetsTableBody tr[data-id]:hover { transform: translateY(-1px) !important; }

/* row-status tinting */
#scoresheetsTableBody tr.rs-open     td { background: transparent; }
#scoresheetsTableBody tr.rs-indiv    td { background: #fffbeb; }
#scoresheetsTableBody tr.rs-global   td { background: #fef2f2; }
#scoresheetsTableBody tr.rs-disabled td { background: #f9fafb; }

#scoresheetsTableBody tr.rs-open:hover     td { background: #f0f6ff !important; }
#scoresheetsTableBody tr.rs-indiv:hover    td { background: #fef9ec !important; }
#scoresheetsTableBody tr.rs-global:hover   td { background: #fff0f0 !important; }
#scoresheetsTableBody tr.rs-disabled:hover td { background: #f1f5f9 !important; }

/* checkbox fade-in on hover */
#scoresheetsTableBody tr[data-id] .row-checkbox {
    opacity: .35; transform: scale(.85);
    transition: opacity .18s ease, transform .18s cubic-bezier(.34,1.4,.64,1);
}
#scoresheetsTableBody tr[data-id]:hover .row-checkbox,
#scoresheetsTableBody tr[data-id] .row-checkbox:checked { opacity: 1; transform: scale(1); }

/* ── Icon action buttons ──────────────────────────────────────────── */
.row-actions { display: flex; gap: 6px; flex-wrap: nowrap; }
.icon-btn {
    width: 30px; height: 30px; border-radius: 7px;
    background: transparent; border: 1.5px solid var(--lm-border);
    cursor: pointer; font-size: 14px;
    transition: all .18s cubic-bezier(.34,1.4,.64,1);
    display: inline-flex; align-items: center; justify-content: center;
    color: #64748b;
}
.icon-btn:hover { transform: scale(1.12); }
.icon-btn.ib-lock:hover    { background: #fef9ec; border-color: #fbbf24; color: #d97706; box-shadow: 0 2px 8px rgba(217,119,6,.2); }
.icon-btn.ib-unlock:hover  { background: #ecfdf5; border-color: #6ee7b7; color: #059669; box-shadow: 0 2px 8px rgba(16,185,129,.2); }
.icon-btn.ib-disable:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; box-shadow: 0 2px 8px rgba(239,68,68,.2); }
.icon-btn.ib-enable:hover  { background: #eff6ff; border-color: #93c5fd; color: #2563eb; box-shadow: 0 2px 8px rgba(37,99,235,.2); }
.icon-btn.ib-info:hover    { background: #f5f3ff; border-color: #c4b5fd; color: #7c3aed; box-shadow: 0 2px 8px rgba(124,58,237,.2); }

/* tooltip on icon buttons */
.icon-btn[title] { position: relative; }

/* ── Teacher / subject cell ───────────────────────────────────────── */
.teacher-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--lm-accent), #4f46e5);
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px; font-weight: 700; flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.8);
    box-shadow: 0 2px 6px rgba(37,99,235,.25);
    transition: transform .2s cubic-bezier(.34,1.4,.64,1);
}
#scoresheetsTableBody tr[data-id]:hover .teacher-avatar { transform: scale(1.1); }

.student-count-bar {
    height: 4px; border-radius: 2px; background: #e2e8f0; margin-top: 5px; overflow: hidden;
}
.student-count-bar-fill {
    height: 100%; border-radius: 2px; background: #f59e0b;
    transition: width .4s ease;
}

/* ── Empty / loading states ───────────────────────────────────────── */
.state-row td {
    padding: 52px 16px !important;
    text-align: center; color: var(--lm-muted);
}
.state-icon { font-size: 2.8rem; display: block; margin-bottom: 10px; opacity: .4; }

/* ── Toast ────────────────────────────────────────────────────────── */
.toast-container {
    position: fixed; bottom: 24px; right: 24px; z-index: 1100;
    display: flex; flex-direction: column; gap: 10px;
}
.lm-toast {
    background: #fff; border-radius: 12px; padding: 12px 18px;
    box-shadow: 0 10px 28px rgba(0,0,0,.12); display: flex; align-items: center; gap: 12px;
    animation: lm-slideRight .3s ease; border-left: 4px solid;
    min-width: 260px; max-width: 420px; font-size: 13px;
}
.lm-toast.t-success { border-left-color: #10b981; }
.lm-toast.t-error   { border-left-color: #ef4444; }
.lm-toast.t-info    { border-left-color: #3b82f6; }
.lm-toast.t-warning { border-left-color: #f59e0b; }
.lm-toast .t-icon   { font-size: 18px; flex-shrink: 0; }
.lm-toast .t-msg    { flex: 1; color: #1e293b; }
.lm-toast .t-close  { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 16px; padding: 0; line-height: 1; }
.lm-toast .t-close:hover { color: #475569; }

/* ── Reduced motion ───────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    #scoresheetsTableBody tr[data-id] { transition: background .15s ease !important; transform: none !important; opacity: 1 !important; }
    #scoresheetsTableBody tr[data-id]:hover { transform: none !important; }
}

/* ── Responsive ───────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .lm-hero { padding: 20px; }
    .lm-hero h1 { font-size: 17px; }
    .stat-value { font-size: 22px; }
    .action-bar { flex-direction: column; align-items: stretch; }
    .selected-info { margin-right: 0; text-align: center; }
    .table-card { overflow-x: auto; }
    .filter-card { padding: 16px; }
}

/* ── Pulse badge for count ────────────────────────────────────────── */
@keyframes lm-pulse { 0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.4)} 50%{box-shadow:0 0 0 6px rgba(37,99,235,0)} }
.count-badge-pulse { animation: lm-pulse 2s ease infinite; }
</style>

<div class="main-content lm-wrap">
<div class="page-content">
<div class="container-fluid">

    {{-- ══ HERO ═══════════════════════════════════════════════════════ --}}
    <div class="lm-hero">
        <h1><i class="ri-shield-lock-line me-2"></i>Scoresheet Lock Management</h1>
        <p>Manage locks, disable teacher editing, and control access to scoresheets across all subjects.</p>
        <div class="hero-badge">
            <i class="ri-database-2-line"></i>
            <span id="heroCount">Loading…</span>
        </div>
    </div>

    {{-- ══ STATS ═══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-6" style="animation-delay:.05s">
            <div class="stat-card c-total">
                <div class="stat-icon"><i class="ri-file-list-line"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Total Scoresheets</div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6" style="animation-delay:.1s">
            <div class="stat-card c-open">
                <div class="stat-icon"><i class="ri-lock-unlock-line"></i></div>
                <div class="stat-value text-success" id="statOpen">0</div>
                <div class="stat-label">Open</div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6" style="animation-delay:.15s">
            <div class="stat-card c-indiv">
                <div class="stat-icon"><i class="ri-lock-line"></i></div>
                <div class="stat-value text-warning" id="statIndividual">0</div>
                <div class="stat-label">Individually Locked</div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6" style="animation-delay:.2s">
            <div class="stat-card c-global">
                <div class="stat-icon"><i class="ri-global-line"></i></div>
                <div class="stat-value text-danger" id="statGlobal">0</div>
                <div class="stat-label">Globally Locked</div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6" style="animation-delay:.25s">
            <div class="stat-card c-disabled">
                <div class="stat-icon"><i class="ri-ban-line"></i></div>
                <div class="stat-value text-secondary" id="statDisabled">0</div>
                <div class="stat-label">Editing Disabled</div>
            </div>
        </div>
    </div>

    {{-- ══ FILTERS ══════════════════════════════════════════════════════ --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <div class="filter-group">
                    <label><i class="ri-search-line me-1"></i>Search</label>
                    <input type="text" id="searchInput" placeholder="Teacher, subject or code…">
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-calendar-line me-1"></i>Term</label>
                    <select id="termFilter"><option value="">All Terms</option></select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-calendar-event-line me-1"></i>Session</label>
                    <select id="sessionFilter"><option value="">All Sessions</option></select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-group-line me-1"></i>Class</label>
                    <select id="classFilter"><option value="">All Classes</option></select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-shield-line me-1"></i>Status</label>
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="individual">Individually Locked</option>
                        <option value="global">Globally Locked</option>
                        <option value="disabled">Editing Disabled</option>
                    </select>
                </div>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100" id="applyFiltersBtn" style="height:40px;">
                    <i class="ri-filter-3-line"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══ ACTION BAR ══════════════════════════════════════════════════ --}}
    <div class="action-bar">
        <div class="selected-info" id="selInfo">
            <i class="ri-checkbox-line me-1"></i>
            <strong id="selectedCount">0</strong> scoresheet(s) selected
        </div>
        <button class="btn btn-warning  btn-sm" id="bulkLockBtn"    disabled><i class="ri-lock-line"></i>Lock</button>
        <button class="btn btn-success  btn-sm" id="bulkUnlockBtn"  disabled><i class="ri-lock-unlock-line"></i>Unlock</button>
        <button class="btn btn-danger   btn-sm" id="bulkDisableBtn" disabled><i class="ri-ban-line"></i>Disable</button>
        <button class="btn btn-secondary btn-sm" id="bulkEnableBtn" disabled><i class="ri-check-line"></i>Enable</button>
        <button class="btn btn-outline  btn-sm" id="refreshBtn"><i class="ri-refresh-line"></i>Refresh</button>
        <a href="{{ route('admin.score-entry.index') }}" class="btn btn-outline btn-sm">
            <i class="ri-arrow-left-line"></i>Back
        </a>
    </div>

    {{-- ══ TABLE CARD ══════════════════════════════════════════════════ --}}
    <div class="table-card">
        <div class="card-header">
            <h5>
                <i class="ri-list-check"></i>Scoresheets
            </h5>
            <span class="badge count-badge-pulse" id="recordCount">0</span>
        </div>
        <div class="table-responsive">
            <table id="scoresheetsTable" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="width:42px;">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                        </th>
                        <th>Teacher &amp; Subject</th>
                        <th>Class</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Lock Status</th>
                        <th style="width:170px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="scoresheetsTableBody">
                    <tr class="state-row">
                        <td colspan="7">
                            <i class="ri-loader-4-line spin state-icon"></i>
                            Loading scoresheets…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- footer bar --}}
        <div class="p-3 border-top" style="background:#f8fafc;font-size:12px;color:var(--lm-muted);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span><i class="ri-information-line me-1 text-info"></i>
                    Click row actions to lock / unlock individual scoresheets. Use the action bar for bulk operations.
                </span>
                <span id="footerCount" class="fw-semibold" style="color:var(--lm-primary);">0 records</span>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ══ TOASTS ═══════════════════════════════════════════════════════════ --}}
<div id="toastContainer" class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ── Routes & CSRF ─────────────────────────────────────────────────── */
const ROUTES = {
    scoresheetsList      : '{{ route("admin.score-entry.scoresheets-list") }}',
    bulkLockManagement   : '{{ route("admin.score-entry.bulk-lock-management") }}',
    lockBatch            : '{{ route("admin.score-entry.lock-batch") }}',
    unlockBatch          : '{{ route("admin.score-entry.unlock-batch") }}',
    disableTeacherEditing: '{{ route("admin.score-entry.disable-teacher-editing") }}',
    enableTeacherEditing : '{{ route("admin.score-entry.enable-teacher-editing") }}',
};
const CSRF = '{{ csrf_token() }}';

/* ── State ─────────────────────────────────────────────────────────── */
let scoresheetsData = [];
let selectedIds     = new Set();
let filtersLoaded   = false;

/* ══════════════════════════════════════════════════════════════════════
   BOOT
   ══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    loadData(true);

    document.getElementById('applyFiltersBtn').addEventListener('click', () => loadData(false));
    document.getElementById('refreshBtn')     .addEventListener('click', () => { rotateRefresh(); loadData(false); });
    document.getElementById('bulkLockBtn')    .addEventListener('click', () => bulkAction('lock_individual'));
    document.getElementById('bulkUnlockBtn')  .addEventListener('click', () => bulkAction('unlock_individual'));
    document.getElementById('bulkDisableBtn') .addEventListener('click', () => bulkAction('disable_editing'));
    document.getElementById('bulkEnableBtn')  .addEventListener('click', () => bulkAction('enable_editing'));
    document.getElementById('selectAllCheckbox').addEventListener('change', toggleSelectAll);

    document.getElementById('searchInput').addEventListener('input', applyClientSearch);
});

function rotateRefresh() {
    const btn = document.getElementById('refreshBtn');
    const ico = btn.querySelector('i');
    if (!ico) return;
    ico.classList.add('spin');
    setTimeout(() => ico.classList.remove('spin'), 900);
}

/* ══════════════════════════════════════════════════════════════════════
   DATA LOADING
   ══════════════════════════════════════════════════════════════════════ */
async function loadData(populateFilters) {
    showTableLoading();

    const params = new URLSearchParams({
        search    : document.getElementById('searchInput').value,
        term_id   : document.getElementById('termFilter').value,
        session_id: document.getElementById('sessionFilter').value,
        class_id  : document.getElementById('classFilter').value,
        status    : document.getElementById('statusFilter').value,
    });

    try {
        const res    = await fetch(`${ROUTES.scoresheetsList}?${params}`);
        const result = await res.json();

        if (!result.success) {
            showTableError(result.message || 'Failed to load scoresheets.');
            showToast(result.message || 'Failed to load scoresheets.', 'error');
            return;
        }

        scoresheetsData = result.data || [];

        if (populateFilters && result.filters && !filtersLoaded) {
            populateFilterDropdowns(result.filters);
            filtersLoaded = true;
        }

        renderTable();
        updateStats();

    } catch (err) {
        console.error('loadData error:', err);
        showTableError('Network error. Please check your connection.');
        showToast('Network error loading scoresheets.', 'error');
    }
}

function populateFilterDropdowns(filters) {
    const termSel    = document.getElementById('termFilter');
    const sessionSel = document.getElementById('sessionFilter');
    const classSel   = document.getElementById('classFilter');

    (filters.terms    || []).forEach(t => termSel.insertAdjacentHTML('beforeend',
        `<option value="${t.id}">${escHtml(t.term)}</option>`));
    (filters.sessions || []).forEach(s => sessionSel.insertAdjacentHTML('beforeend',
        `<option value="${s.id}">${escHtml(s.session)}</option>`));
    (filters.classes  || []).forEach(c => {
        const arm = c.arm?.arm || '';
        classSel.insertAdjacentHTML('beforeend',
            `<option value="${c.id}">${escHtml(c.schoolclass)} ${escHtml(arm)}</option>`);
    });
}

/* ══════════════════════════════════════════════════════════════════════
   CLIENT-SIDE SEARCH (instant)
   ══════════════════════════════════════════════════════════════════════ */
function applyClientSearch() {
    const q = document.getElementById('searchInput').value.trim().toLowerCase();
    let vis = 0;
    document.querySelectorAll('#scoresheetsTableBody tr[data-id]').forEach(row => {
        const txt = (row.dataset.search || '').toLowerCase();
        const show = !q || txt.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) vis++;
    });
    document.getElementById('recordCount').textContent = vis;
    document.getElementById('footerCount').textContent = vis + ' record' + (vis !== 1 ? 's' : '');
    document.getElementById('heroCount').textContent   = vis + ' scoresheet' + (vis !== 1 ? 's' : '') + ' loaded';
}

/* ══════════════════════════════════════════════════════════════════════
   TABLE STATES
   ══════════════════════════════════════════════════════════════════════ */
function showTableLoading() {
    document.getElementById('scoresheetsTableBody').innerHTML = `
        <tr class="state-row"><td colspan="7">
            <i class="ri-loader-4-line spin state-icon"></i>
            Loading scoresheets…
        </td></tr>`;
}
function showTableError(msg) {
    document.getElementById('scoresheetsTableBody').innerHTML = `
        <tr class="state-row"><td colspan="7">
            <i class="ri-error-warning-line state-icon" style="color:#ef4444;opacity:1;"></i>
            ${escHtml(msg)}
        </td></tr>`;
}

/* ══════════════════════════════════════════════════════════════════════
   RENDER TABLE
   ══════════════════════════════════════════════════════════════════════ */
function renderTable() {
    const tbody = document.getElementById('scoresheetsTableBody');
    const count = scoresheetsData.length;

    document.getElementById('recordCount').textContent = count;
    document.getElementById('footerCount').textContent = count + ' record' + (count !== 1 ? 's' : '');
    document.getElementById('heroCount').textContent   = count + ' scoresheet' + (count !== 1 ? 's' : '') + ' loaded';

    if (!count) {
        tbody.innerHTML = `
            <tr class="state-row"><td colspan="7">
                <i class="ri-inbox-line state-icon"></i>
                No scoresheets found matching your criteria.
            </td></tr>`;
        return;
    }

    tbody.innerHTML = '';
    scoresheetsData.forEach(sheet => {
        const status     = getStatus(sheet);
        const isSelected = selectedIds.has(sheet.subjectclass_id);
        const initials   = getInitials(sheet.teacher_name);
        const lockPct    = sheet.total_students > 0
            ? Math.round((sheet.individually_locked_count / sheet.total_students) * 100) : 0;

        // search string for instant filter
        const searchStr = [sheet.teacher_name, sheet.subject_name, sheet.subject_code,
                           sheet.class_name, sheet.term_name, sheet.session_name].join(' ').toLowerCase();

        const tr = document.createElement('tr');
        tr.dataset.id     = sheet.subjectclass_id;
        tr.dataset.search = searchStr;
        tr.className = `rs-${status.key}`;
        if (isSelected) tr.style.background = '#eff6ff';

        tr.innerHTML = `
            <td style="width:42px;">
                <input type="checkbox" class="form-check-input row-checkbox"
                    data-id="${sheet.subjectclass_id}" ${isSelected ? 'checked' : ''}>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="teacher-avatar">${escHtml(initials)}</div>
                    <div style="min-width:0;">
                        <div class="fw-semibold" style="font-size:13px;color:#1e293b;">${escHtml(sheet.teacher_name)}</div>
                        <div class="text-muted" style="font-size:11.5px;">
                            ${escHtml(sheet.subject_name)}
                            <span style="background:#f1f5f9;border-radius:4px;padding:1px 6px;margin-left:4px;font-weight:600;">
                                ${escHtml(sheet.subject_code)}
                            </span>
                        </div>
                        ${sheet.individually_locked_count > 0 ? `
                        <div style="margin-top:5px;">
                            <div style="font-size:10.5px;color:var(--lm-muted);margin-bottom:3px;">
                                <i class="ri-lock-line"></i> ${sheet.individually_locked_count} / ${sheet.total_students} students locked
                            </div>
                            <div class="student-count-bar">
                                <div class="student-count-bar-fill" style="width:${lockPct}%;"></div>
                            </div>
                        </div>` : ''}
                    </div>
                </div>
            </td>
            <td>
                <span style="background:#f0f4ff;color:#3b4fa0;border-radius:6px;padding:4px 10px;font-size:12px;font-weight:600;">
                    ${escHtml(sheet.class_name)}
                </span>
            </td>
            <td style="color:#475569;font-size:12.5px;">${escHtml(sheet.term_name    || '—')}</td>
            <td style="color:#475569;font-size:12.5px;">${escHtml(sheet.session_name || '—')}</td>
            <td>
                <span class="status-badge ${status.cls}">
                    <i class="${status.icon}"></i>${status.text}
                </span>
                ${sheet.global_lock_reason ? `
                <div class="text-muted mt-1" style="font-size:11px;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                     title="${escHtml(sheet.global_lock_reason)}">
                    <i class="ri-chat-quote-line me-1"></i>${escHtml(sheet.global_lock_reason.substring(0,50))}${sheet.global_lock_reason.length > 50 ? '…' : ''}
                </div>` : ''}
            </td>
            <td>
                <div class="row-actions">
                    <button class="icon-btn ib-lock"    onclick="quickAction(${sheet.subjectclass_id},'lock_individual')"    title="Lock individual scores"><i class="ri-lock-line"></i></button>
                    <button class="icon-btn ib-unlock"  onclick="quickAction(${sheet.subjectclass_id},'unlock_individual')"  title="Unlock individual scores"><i class="ri-lock-unlock-line"></i></button>
                    <button class="icon-btn ib-disable" onclick="quickAction(${sheet.subjectclass_id},'disable_editing')"    title="Disable teacher editing"><i class="ri-ban-line"></i></button>
                    <button class="icon-btn ib-enable"  onclick="quickAction(${sheet.subjectclass_id},'enable_editing')"     title="Enable teacher editing"><i class="ri-check-line"></i></button>
                    <button class="icon-btn ib-info"    onclick="showAuditInfo(${sheet.subjectclass_id})"                    title="View lock info"><i class="ri-history-line"></i></button>
                </div>
            </td>`;

        tbody.appendChild(tr);
    });

    // Checkbox delegation
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const id = parseInt(this.dataset.id);
            const row = this.closest('tr');
            if (this.checked) {
                selectedIds.add(id);
                row.style.background = '#eff6ff';
            } else {
                selectedIds.delete(id);
                row.style.background = '';
            }
            updateSelectedCount();
            updateSelectAllState();
        });
    });

    // Staggered entrance (mirrors scoresheet blade)
    initRowEntrance();
}

/* ── Staggered row entrance ───────────────────────────────────────── */
function initRowEntrance() {
    const rows = Array.from(document.querySelectorAll('#scoresheetsTableBody tr[data-id]'));
    if (!rows.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        rows.forEach(r => r.classList.add('row-visible')); return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const row   = entry.target;
            const index = rows.indexOf(row);
            setTimeout(() => row.classList.add('row-visible'), Math.min(index * 40, 15 * 40) + 50);
            observer.unobserve(row);
        });
    }, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });

    rows.forEach(r => observer.observe(r));
}

/* ══════════════════════════════════════════════════════════════════════
   STATUS HELPER
   ══════════════════════════════════════════════════════════════════════ */
function getStatus(sheet) {
    if (!sheet.teacher_editing_enabled) {
        return { key:'disabled',  cls:'status-disabled',  text:'Editing Disabled', icon:'ri-ban-line' };
    }
    if (sheet.global_lock_active) {
        return { key:'global',    cls:'status-global',    text:'Globally Locked',  icon:'ri-global-line' };
    }
    const locked = parseInt(sheet.individually_locked_count || 0);
    const total  = parseInt(sheet.total_students || 0);
    if (locked > 0) {
        return {
            key: 'indiv', cls: 'status-individual',
            text: locked === total ? 'Fully Locked' : `Partial (${locked}/${total})`,
            icon: 'ri-lock-line',
        };
    }
    return { key:'open', cls:'status-open', text:'Open', icon:'ri-lock-unlock-line' };
}

/* ── Avatar initials ──────────────────────────────────────────────── */
function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
}

/* ══════════════════════════════════════════════════════════════════════
   STATS
   ══════════════════════════════════════════════════════════════════════ */
function updateStats() {
    let open = 0, individual = 0, global = 0, disabled = 0;
    scoresheetsData.forEach(sheet => {
        if (!sheet.teacher_editing_enabled)           disabled++;
        else if (sheet.global_lock_active)            global++;
        else if (sheet.individually_locked_count > 0) individual++;
        else                                          open++;
    });
    animateCount('statTotal',      scoresheetsData.length);
    animateCount('statOpen',       open);
    animateCount('statIndividual', individual);
    animateCount('statGlobal',     global);
    animateCount('statDisabled',   disabled);
}

function animateCount(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    const start = parseInt(el.textContent) || 0;
    if (start === target) return;
    const duration = 600, steps = 24, step = (target - start) / steps;
    let current = start, count = 0;
    const iv = setInterval(() => {
        count++;
        current = count >= steps ? target : Math.round(current + step);
        el.textContent = current;
        if (count >= steps) clearInterval(iv);
    }, duration / steps);
}

/* ══════════════════════════════════════════════════════════════════════
   SELECTION
   ══════════════════════════════════════════════════════════════════════ */
function updateSelectedCount() {
    const n   = selectedIds.size;
    const el  = document.getElementById('selectedCount');
    const inf = document.getElementById('selInfo');
    if (el) el.textContent = n;
    if (inf) inf.classList.toggle('has-selection', n > 0);
    const has = n > 0;
    ['bulkLockBtn','bulkUnlockBtn','bulkDisableBtn','bulkEnableBtn']
        .forEach(id => document.getElementById(id).disabled = !has);
}

function updateSelectAllState() {
    const allIds = scoresheetsData.map(s => s.subjectclass_id);
    const cb     = document.getElementById('selectAllCheckbox');
    const selAll = allIds.length > 0 && allIds.every(id => selectedIds.has(id));
    cb.checked       = selAll;
    cb.indeterminate = !selAll && allIds.some(id => selectedIds.has(id));
}

function toggleSelectAll(e) {
    const checked = e.target.checked;
    scoresheetsData.forEach(sheet => {
        if (checked) selectedIds.add(sheet.subjectclass_id);
        else         selectedIds.delete(sheet.subjectclass_id);
    });
    renderTable();
    updateSelectedCount();
}

/* ══════════════════════════════════════════════════════════════════════
   BULK ACTION
   ══════════════════════════════════════════════════════════════════════ */
const ACTION_META = {
    lock_individual  : { title:'Lock Selected Scoresheets',   msg:'This will lock all student scoresheets in the selected subjects.',   needsReason:true,  color:'#f59e0b' },
    unlock_individual: { title:'Unlock Selected Scoresheets', msg:'This will unlock all student scoresheets in the selected subjects.', needsReason:false, color:'#10b981' },
    disable_editing  : { title:'Disable Teacher Editing',     msg:'Teachers will not be able to edit ANY scores in these subjects.',    needsReason:true,  color:'#ef4444' },
    enable_editing   : { title:'Enable Teacher Editing',      msg:'Teachers will regain editing abilities for these subjects.',         needsReason:false, color:'#10b981' },
};

async function bulkAction(action) {
    const ids = Array.from(selectedIds);
    if (!ids.length) return;
    const m = ACTION_META[action];

    const { value: reason, isDismissed } = await Swal.fire({
        title: m.title, text: m.msg, icon: 'question',
        input: m.needsReason ? 'textarea' : undefined,
        inputPlaceholder: 'Enter reason (optional)…',
        showCancelButton: true,
        confirmButtonColor: m.color,
        confirmButtonText: 'Confirm',
    });
    if (isDismissed) return;

    await sendBulkRequest(ROUTES.bulkLockManagement, {
        action, subjectclass_ids: ids, reason: reason || null,
        term_id   : document.getElementById('termFilter').value    || null,
        session_id: document.getElementById('sessionFilter').value || null,
    });
}

/* ══════════════════════════════════════════════════════════════════════
   QUICK SINGLE ACTION
   ══════════════════════════════════════════════════════════════════════ */
window.quickAction = async function (id, action) {
    const m = ACTION_META[action];

    const { value: reason, isDismissed } = await Swal.fire({
        title: m.title, text: m.msg, icon: 'question',
        input: m.needsReason ? 'textarea' : undefined,
        inputPlaceholder: 'Enter reason (optional)…',
        showCancelButton: true,
        confirmButtonColor: m.color,
        confirmButtonText: 'Confirm',
    });
    if (isDismissed) return;

    const endpointMap = {
        lock_individual  : { url: ROUTES.lockBatch,             body: { subjectclass_ids:[id], reason, lock_type:'individual' } },
        unlock_individual: { url: ROUTES.unlockBatch,           body: { subjectclass_ids:[id], unlock_type:'individual' } },
        disable_editing  : { url: ROUTES.disableTeacherEditing, body: { subjectclass_ids:[id], reason } },
        enable_editing   : { url: ROUTES.enableTeacherEditing,  body: { subjectclass_ids:[id] } },
    };
    const ep = endpointMap[action];
    await postJSON(ep.url, ep.body);
};

/* ══════════════════════════════════════════════════════════════════════
   AUDIT INFO POPUP
   ══════════════════════════════════════════════════════════════════════ */
window.showAuditInfo = function (id) {
    const sheet = scoresheetsData.find(s => s.subjectclass_id === id);
    if (!sheet) return;
    const status   = getStatus(sheet);
    const initials = getInitials(sheet.teacher_name);

    Swal.fire({
        title: 'Lock Details',
        icon: 'info',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Close',
        customClass: { popup: 'swal-lock-popup' },
        html: `
        <div style="text-align:left;font-size:13px;line-height:1.9;padding:4px 0;">

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:14px 16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0;">
                    ${escHtml(initials)}
                </div>
                <div>
                    <div style="font-weight:700;color:#1e293b;font-size:14px;">${escHtml(sheet.teacher_name)}</div>
                    <div style="color:#64748b;font-size:12px;">${escHtml(sheet.subject_name)} · ${escHtml(sheet.class_name)}</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;margin-bottom:3px;">Status</div>
                    <div style="font-weight:700;color:#1e293b;">${status.text}</div>
                </div>
                <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;margin-bottom:3px;">Individually Locked</div>
                    <div style="font-weight:700;color:#1e293b;">${sheet.individually_locked_count} / ${sheet.total_students} students</div>
                </div>
                <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;margin-bottom:3px;">Global Lock</div>
                    <div style="font-weight:700;color:${sheet.global_lock_active ? '#dc2626' : '#16a34a'};">
                        ${sheet.global_lock_active ? '🔴 Active' : '🟢 Inactive'}
                    </div>
                </div>
                <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;margin-bottom:3px;">Teacher Editing</div>
                    <div style="font-weight:700;color:${sheet.teacher_editing_enabled ? '#16a34a' : '#dc2626'};">
                        ${sheet.teacher_editing_enabled ? '✅ Enabled' : '🚫 Disabled'}
                    </div>
                </div>
            </div>

            ${sheet.global_lock_reason ? `
            <div style="padding:10px 14px;background:#fffbeb;border-radius:8px;border:1px solid #fde68a;margin-bottom:10px;">
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#92400e;font-weight:700;margin-bottom:3px;">Lock Reason</div>
                <div style="color:#78350f;">${escHtml(sheet.global_lock_reason)}</div>
            </div>` : ''}

            ${sheet.global_lock_by ? `
            <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;margin-bottom:3px;">Locked By</div>
                <div style="color:#1e293b;">${escHtml(sheet.global_lock_by)} <span style="color:#94a3b8;">at</span> ${escHtml(sheet.global_lock_at || '')}</div>
            </div>` : ''}
        </div>`,
    });
};

/* ══════════════════════════════════════════════════════════════════════
   HTTP HELPERS
   ══════════════════════════════════════════════════════════════════════ */
async function sendBulkRequest(url, body) {
    try {
        const res    = await fetch(url, { method:'POST', headers: jsonHeaders(), body: JSON.stringify(body) });
        const result = await res.json();
        if (result.success) {
            showToast(result.message, 'success');
            selectedIds.clear();
            loadData(false);
        } else {
            showToast(result.message || 'Action failed.', 'error');
        }
    } catch (e) {
        console.error('sendBulkRequest error:', e);
        showToast('Network error performing action.', 'error');
    }
}

async function postJSON(url, body) {
    try {
        const res    = await fetch(url, { method:'POST', headers: jsonHeaders(), body: JSON.stringify(body) });
        const result = await res.json();
        if (result.success) {
            showToast(result.message, 'success');
            loadData(false);
        } else {
            showToast(result.message || 'Action failed.', 'error');
        }
    } catch (e) {
        console.error('postJSON error:', e);
        showToast('Network error performing action.', 'error');
    }
}

function jsonHeaders() {
    return { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF };
}

/* ══════════════════════════════════════════════════════════════════════
   TOAST
   ══════════════════════════════════════════════════════════════════════ */
function showToast(message, type = 'info') {
    const icons = {
        success : '<i class="ri-checkbox-circle-fill" style="color:#10b981;"></i>',
        error   : '<i class="ri-error-warning-fill"   style="color:#ef4444;"></i>',
        info    : '<i class="ri-information-fill"      style="color:#3b82f6;"></i>',
        warning : '<i class="ri-alert-fill"            style="color:#f59e0b;"></i>',
    };
    const toast = document.createElement('div');
    toast.className = `lm-toast t-${type}`;
    toast.innerHTML = `
        <span class="t-icon">${icons[type] || icons.info}</span>
        <span class="t-msg">${escHtml(message)}</span>
        <button class="t-close" onclick="this.closest('.lm-toast').remove()">
            <i class="ri-close-line"></i>
        </button>`;
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(() => toast.remove(), 320); }, 5000);
}

/* ── Utility ────────────────────────────────────────────────────────── */
function escHtml(text) {
    if (text === null || text === undefined) return '';
    const d = document.createElement('div'); d.textContent = String(text); return d.innerHTML;
}
</script>
@endsection
