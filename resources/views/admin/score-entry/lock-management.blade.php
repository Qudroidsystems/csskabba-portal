@extends('layouts.master')

@section('content')
<style>
:root {
    --lm-primary:  #1e3a5f;
    --lm-accent:   #2563eb;
    --lm-success:  #16a34a;
    --lm-warning:  #d97706;
    --lm-danger:   #dc2626;
    --lm-muted:    #6b7280;
    --lm-border:   #e2e8f0;
    --lm-bg:       #f8fafc;
    --lm-radius:   12px;
    --lm-shadow:   0 2px 8px rgba(0,0,0,.08);
}

.lm-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--lm-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.lm-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.lm-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.lm-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

.stat-card {
    background: #fff;
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--lm-shadow); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--lm-primary); }
.stat-card .stat-label { font-size: 12px; color: var(--lm-muted); margin-top: 4px; }
.stat-card .stat-icon  { font-size: 32px; opacity: .12; float: right; margin-top: -8px; }

.filter-card {
    background: #fff;
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    padding: 20px 24px;
    margin-bottom: 24px;
}
.filter-group label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: block;
}
.filter-group input,
.filter-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    transition: all 0.2s;
}
.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--lm-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.action-bar {
    background: #fff;
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}
.selected-info {
    font-size: 13px;
    color: var(--lm-muted);
    margin-right: auto;
    background: #f1f5f9;
    padding: 6px 14px;
    border-radius: 20px;
}
.selected-info strong { color: #1e293b; font-weight: 700; }

.btn {
    padding: 8px 18px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-sm { padding: 6px 14px; font-size: 12px; }
.btn-primary   { background: #2563eb; color: white; }
.btn-primary:hover   { background: #1d4ed8; transform: translateY(-1px); color: white; }
.btn-success   { background: #10b981; color: white; }
.btn-success:hover   { background: #059669; transform: translateY(-1px); }
.btn-warning   { background: #f59e0b; color: white; }
.btn-warning:hover   { background: #d97706; transform: translateY(-1px); }
.btn-danger    { background: #ef4444; color: white; }
.btn-danger:hover    { background: #dc2626; transform: translateY(-1px); }
.btn-secondary { background: #64748b; color: white; }
.btn-secondary:hover { background: #475569; transform: translateY(-1px); }
.btn-outline   { background: transparent; border: 1px solid #cbd5e1; color: #475569; }
.btn-outline:hover   { background: #f8fafc; border-color: #94a3b8; color: #475569; }
.btn:disabled  { opacity: 0.5; cursor: not-allowed; transform: none !important; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-open       { background: #d1fae5; color: #065f46; }
.status-individual { background: #fed7aa; color: #92400e; }
.status-global     { background: #fecaca; color: #991b1b; }
.status-disabled   { background: #e2e3e5; color: #383d41; }

.row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.icon-btn {
    width: 32px; height: 32px;
    border-radius: 6px;
    background: transparent;
    border: 1px solid var(--lm-border);
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.icon-btn:hover { background: #f1f5f9; transform: scale(1.05); }

.table-card {
    background: #fff;
    border: 1px solid var(--lm-border);
    border-radius: var(--lm-radius);
    overflow: hidden;
}
.table-card .card-header {
    background: #fff;
    border-bottom: 1px solid var(--lm-border);
    padding: 16px 20px;
}
.table-card th {
    background: var(--lm-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.table-card td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--lm-border);
    font-size: 13px;
}
.table-card tr:hover td { background: #f0f9ff; }

.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.toast-container {
    position: fixed; bottom: 24px; right: 24px;
    z-index: 1100;
    display: flex; flex-direction: column; gap: 10px;
}
.toast {
    background: white;
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    display: flex; align-items: center; gap: 12px;
    animation: slideInRight 0.3s ease;
    border-left: 4px solid;
    min-width: 260px;
    max-width: 420px;
}
.toast-success { border-left-color: #10b981; }
.toast-error   { border-left-color: #ef4444; }
.toast-info    { border-left-color: #3b82f6; }

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}

@media (max-width: 768px) {
    .lm-hero { padding: 20px; }
    .lm-hero h1 { font-size: 18px; }
    .stat-card .stat-value { font-size: 22px; }
    .action-bar { flex-direction: column; align-items: stretch; }
    .selected-info { margin-right: 0; text-align: center; }
    .table-card { overflow-x: auto; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="lm-hero">
        <h1><i class="ri-shield-lock-line me-2"></i>Scoresheet Lock Management</h1>
        <p>Manage locks, disable teacher editing, and control access to scoresheets across all subjects.</p>
    </div>

    {{-- Stats — 4 cards (statTotal, statOpen, statIndividual, statGlobal, statDisabled) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-file-list-line"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Total Scoresheets</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-lock-unlock-line"></i></div>
                <div class="stat-value text-success" id="statOpen">0</div>
                <div class="stat-label">Open</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-lock-line"></i></div>
                <div class="stat-value text-warning" id="statIndividual">0</div>
                <div class="stat-label">Individually Locked</div>
            </div>
        </div>
        {{-- This card was missing — it was referenced in JS as statGlobal --}}
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-global-line"></i></div>
                <div class="stat-value text-danger" id="statGlobal">0</div>
                <div class="stat-label">Globally Locked</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-ban-line"></i></div>
                <div class="stat-value text-secondary" id="statDisabled">0</div>
                <div class="stat-label">Editing Disabled</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <div class="filter-group">
                    <label><i class="ri-search-line me-1"></i>Search</label>
                    <input type="text" id="searchInput" placeholder="Teacher, subject or code...">
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-calendar-line me-1"></i>Term</label>
                    <select id="termFilter">
                        <option value="">All Terms</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-calendar-event-line me-1"></i>Session</label>
                    <select id="sessionFilter">
                        <option value="">All Sessions</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-group-line me-1"></i>Class</label>
                    <select id="classFilter">
                        <option value="">All Classes</option>
                    </select>
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
                <button class="btn btn-primary w-100" id="applyFiltersBtn">
                    <i class="ri-filter-3-line"></i> Apply
                </button>
            </div>
        </div>
    </div>

    {{-- Action bar --}}
    <div class="action-bar">
        <div class="selected-info">
            <i class="ri-checkbox-line me-1"></i>
            <strong id="selectedCount">0</strong> scoresheet(s) selected
        </div>
        <button class="btn btn-warning  btn-sm" id="bulkLockBtn"    disabled><i class="ri-lock-line"></i>         Lock Selected</button>
        <button class="btn btn-success  btn-sm" id="bulkUnlockBtn"  disabled><i class="ri-lock-unlock-line"></i>  Unlock Selected</button>
        <button class="btn btn-danger   btn-sm" id="bulkDisableBtn" disabled><i class="ri-ban-line"></i>          Disable Editing</button>
        <button class="btn btn-secondary btn-sm" id="bulkEnableBtn" disabled><i class="ri-check-line"></i>        Enable Editing</button>
        <button class="btn btn-outline   btn-sm" id="refreshBtn">  <i class="ri-refresh-line"></i>               Refresh</button>
        <a href="{{ route('admin.score-entry.index') }}" class="btn btn-outline btn-sm">
            <i class="ri-arrow-left-line"></i> Back
        </a>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-semibold" style="color:var(--lm-primary)">
                <i class="ri-list-check me-2"></i>Scoresheets
            </h5>
            <span class="badge bg-primary" id="recordCount">0</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" id="scoresheetsTable">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
                        <th>Teacher &amp; Subject</th>
                        <th>Class</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Status</th>
                        <th width="190">Actions</th>
                    </tr>
                </thead>
                <tbody id="scoresheetsTableBody">
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="ri-loader-4-line spin d-block mb-2" style="font-size:2rem"></i>
                            Loading scoresheets…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>

<div id="toastContainer" class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ROUTES = {
    scoresheetsList:      '{{ route("admin.score-entry.scoresheets-list") }}',
    bulkLockManagement:   '{{ route("admin.score-entry.bulk-lock-management") }}',
    lockBatch:            '{{ route("admin.score-entry.lock-batch") }}',
    unlockBatch:          '{{ route("admin.score-entry.unlock-batch") }}',
    disableTeacherEditing:'{{ route("admin.score-entry.disable-teacher-editing") }}',
    enableTeacherEditing: '{{ route("admin.score-entry.enable-teacher-editing") }}',
};
const CSRF = '{{ csrf_token() }}';

let scoresheetsData = [];
let selectedIds     = new Set();
let filtersLoaded   = false;

// ── Boot ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadData(true);   // first call: also populate filter dropdowns

    document.getElementById('applyFiltersBtn').addEventListener('click', () => loadData(false));
    document.getElementById('refreshBtn').addEventListener('click',       () => loadData(false));
    document.getElementById('bulkLockBtn').addEventListener('click',    () => bulkAction('lock_individual'));
    document.getElementById('bulkUnlockBtn').addEventListener('click',  () => bulkAction('unlock_individual'));
    document.getElementById('bulkDisableBtn').addEventListener('click', () => bulkAction('disable_editing'));
    document.getElementById('bulkEnableBtn').addEventListener('click',  () => bulkAction('enable_editing'));
    document.getElementById('selectAllCheckbox').addEventListener('change', toggleSelectAll);
});

// ── Data loading ─────────────────────────────────────────────
async function loadData(populateFilters) {
    showTableLoading();

    const params = new URLSearchParams({
        search:     document.getElementById('searchInput').value,
        term_id:    document.getElementById('termFilter').value,
        session_id: document.getElementById('sessionFilter').value,
        class_id:   document.getElementById('classFilter').value,
        status:     document.getElementById('statusFilter').value,
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
        document.getElementById('recordCount').textContent = scoresheetsData.length;

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

    (filters.terms || []).forEach(t => {
        termSel.insertAdjacentHTML('beforeend',
            `<option value="${t.id}">${escHtml(t.term)}</option>`);
    });
    (filters.sessions || []).forEach(s => {
        sessionSel.insertAdjacentHTML('beforeend',
            `<option value="${s.id}">${escHtml(s.session)}</option>`);
    });
    (filters.classes || []).forEach(c => {
        const arm = c.arm?.arm || '';
        classSel.insertAdjacentHTML('beforeend',
            `<option value="${c.id}">${escHtml(c.schoolclass)} ${escHtml(arm)}</option>`);
    });
}

// ── Table rendering ───────────────────────────────────────────
function showTableLoading() {
    document.getElementById('scoresheetsTableBody').innerHTML = `
        <tr><td colspan="7" class="text-center text-muted py-5">
            <i class="ri-loader-4-line spin d-block mb-2" style="font-size:2rem"></i>
            Loading scoresheets…
        </td></tr>`;
}

function showTableError(msg) {
    document.getElementById('scoresheetsTableBody').innerHTML = `
        <tr><td colspan="7" class="text-center text-muted py-5">
            <i class="ri-error-warning-line d-block mb-2" style="font-size:2rem;color:#ef4444"></i>
            ${escHtml(msg)}
        </td></tr>`;
}

function renderTable() {
    const tbody = document.getElementById('scoresheetsTableBody');

    if (!scoresheetsData.length) {
        tbody.innerHTML = `
            <tr><td colspan="7" class="text-center text-muted py-5">
                <i class="ri-inbox-line d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                No scoresheets found matching your criteria.
            </td></tr>`;
        return;
    }

    tbody.innerHTML = '';
    scoresheetsData.forEach(sheet => {
        const status = getStatus(sheet);
        const isSelected = selectedIds.has(sheet.subjectclass_id);

        const tr = document.createElement('tr');
        if (isSelected) tr.classList.add('table-active');
        tr.dataset.id = sheet.subjectclass_id;

        tr.innerHTML = `
            <td>
                <input type="checkbox" class="form-check-input row-checkbox"
                    data-id="${sheet.subjectclass_id}" ${isSelected ? 'checked' : ''}>
            </td>
            <td>
                <div class="fw-semibold">${escHtml(sheet.teacher_name)}</div>
                <div class="text-muted small">${escHtml(sheet.subject_name)} (${escHtml(sheet.subject_code)})</div>
                ${sheet.individually_locked_count > 0
                    ? `<div class="text-muted small mt-1">
                            📌 ${sheet.individually_locked_count} / ${sheet.total_students} students locked
                       </div>`
                    : ''}
            </td>
            <td>${escHtml(sheet.class_name)}</td>
            <td>${escHtml(sheet.term_name    || '—')}</td>
            <td>${escHtml(sheet.session_name || '—')}</td>
            <td>
                <span class="status-badge ${status.cls}">
                    <i class="${status.icon}"></i> ${status.text}
                </span>
                ${sheet.global_lock_reason
                    ? `<div class="text-muted small mt-1"
                            title="${escHtml(sheet.global_lock_reason)}">
                            ${escHtml(sheet.global_lock_reason.substring(0, 40))}${sheet.global_lock_reason.length > 40 ? '…' : ''}
                       </div>`
                    : ''}
            </td>
            <td>
                <div class="row-actions">
                    <button class="icon-btn"
                        onclick="quickAction(${sheet.subjectclass_id}, 'lock_individual')"
                        title="Lock individual scores">
                        <i class="ri-lock-line"></i>
                    </button>
                    <button class="icon-btn"
                        onclick="quickAction(${sheet.subjectclass_id}, 'unlock_individual')"
                        title="Unlock individual scores">
                        <i class="ri-lock-unlock-line"></i>
                    </button>
                    <button class="icon-btn"
                        onclick="quickAction(${sheet.subjectclass_id}, 'disable_editing')"
                        title="Disable teacher editing">
                        <i class="ri-ban-line"></i>
                    </button>
                    <button class="icon-btn"
                        onclick="quickAction(${sheet.subjectclass_id}, 'enable_editing')"
                        title="Enable teacher editing">
                        <i class="ri-check-line"></i>
                    </button>
                    <button class="icon-btn"
                        onclick="showAuditInfo(${sheet.subjectclass_id})"
                        title="View info">
                        <i class="ri-history-line"></i>
                    </button>
                </div>
            </td>`;

        tbody.appendChild(tr);
    });

    // Checkbox event delegation
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const id = parseInt(this.dataset.id);
            if (this.checked) {
                selectedIds.add(id);
                this.closest('tr').classList.add('table-active');
            } else {
                selectedIds.delete(id);
                this.closest('tr').classList.remove('table-active');
            }
            updateSelectedCount();
            updateSelectAllState();
        });
    });
}

// ── Status helper ─────────────────────────────────────────────
function getStatus(sheet) {
    if (!sheet.teacher_editing_enabled) {
        return { cls: 'status-disabled',  text: 'Editing Disabled',  icon: 'ri-ban-line' };
    }
    if (sheet.global_lock_active) {
        return { cls: 'status-global',    text: 'Globally Locked',   icon: 'ri-global-line' };
    }
    const locked = parseInt(sheet.individually_locked_count || 0);
    const total  = parseInt(sheet.total_students || 0);
    if (locked > 0) {
        return {
            cls:  'status-individual',
            text: locked === total ? 'Fully Locked' : 'Partially Locked',
            icon: 'ri-lock-line',
        };
    }
    return { cls: 'status-open', text: 'Open', icon: 'ri-lock-unlock-line' };
}

// ── Stats ─────────────────────────────────────────────────────
function updateStats() {
    let open = 0, individual = 0, global = 0, disabled = 0;

    scoresheetsData.forEach(sheet => {
        if (!sheet.teacher_editing_enabled)   { disabled++; }
        else if (sheet.global_lock_active)    { global++; }
        else if (sheet.individually_locked_count > 0) { individual++; }
        else { open++; }
    });

    document.getElementById('statTotal').textContent      = scoresheetsData.length;
    document.getElementById('statOpen').textContent       = open;
    document.getElementById('statIndividual').textContent = individual;
    document.getElementById('statGlobal').textContent     = global;    // ← was missing element
    document.getElementById('statDisabled').textContent   = disabled;
}

// ── Selection helpers ─────────────────────────────────────────
function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedIds.size;
    const has = selectedIds.size > 0;
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

// ── Bulk action ───────────────────────────────────────────────
async function bulkAction(action) {
    const ids = Array.from(selectedIds);
    if (!ids.length) return;

    const META = {
        lock_individual:  { title: 'Lock Selected Scoresheets',     msg: 'This will lock all student scoresheets in the selected subjects.',          needsReason: true,  confirmColor: '#f59e0b' },
        unlock_individual:{ title: 'Unlock Selected Scoresheets',   msg: 'This will unlock all student scoresheets in the selected subjects.',        needsReason: false, confirmColor: '#10b981' },
        disable_editing:  { title: 'Disable Teacher Editing',       msg: 'Teachers will not be able to edit ANY scores in these subjects.',           needsReason: true,  confirmColor: '#ef4444' },
        enable_editing:   { title: 'Enable Teacher Editing',        msg: 'Teachers will regain editing abilities for these subjects.',                needsReason: false, confirmColor: '#10b981' },
    };
    const m = META[action];

    const { value: reason, isDismissed } = await Swal.fire({
        title: m.title, text: m.msg, icon: 'question',
        input: m.needsReason ? 'textarea' : undefined,
        inputPlaceholder: 'Enter reason (optional)…',
        showCancelButton: true,
        confirmButtonColor: m.confirmColor,
        confirmButtonText: 'Confirm',
    });
    if (isDismissed) return;

    await sendBulkRequest(ROUTES.bulkLockManagement, {
        action,
        subjectclass_ids: ids,
        reason:     reason || null,
        term_id:    document.getElementById('termFilter').value    || null,
        session_id: document.getElementById('sessionFilter').value || null,
    });
}

// ── Quick single-row action ───────────────────────────────────
window.quickAction = async function (id, action) {
    const META = {
        lock_individual:  { title: 'Lock Scoresheet',         msg: 'This will lock all scores for this subject.',           needsReason: true,  confirmColor: '#f59e0b' },
        unlock_individual:{ title: 'Unlock Scoresheet',       msg: 'This will unlock all scores for this subject.',         needsReason: false, confirmColor: '#10b981' },
        disable_editing:  { title: 'Disable Teacher Editing', msg: 'The teacher will not be able to edit scores.',          needsReason: true,  confirmColor: '#ef4444' },
        enable_editing:   { title: 'Enable Teacher Editing',  msg: 'The teacher will regain editing abilities.',            needsReason: false, confirmColor: '#10b981' },
    };
    const m = META[action];

    const { value: reason, isDismissed } = await Swal.fire({
        title: m.title, text: m.msg, icon: 'question',
        input: m.needsReason ? 'textarea' : undefined,
        inputPlaceholder: 'Enter reason (optional)…',
        showCancelButton: true,
        confirmButtonColor: m.confirmColor,
        confirmButtonText: 'Confirm',
    });
    if (isDismissed) return;

    const endpointMap = {
        lock_individual:   { url: ROUTES.lockBatch,             body: { subjectclass_ids: [id], reason, lock_type: 'individual' } },
        unlock_individual: { url: ROUTES.unlockBatch,           body: { subjectclass_ids: [id], unlock_type: 'individual' } },
        disable_editing:   { url: ROUTES.disableTeacherEditing, body: { subjectclass_ids: [id], reason } },
        enable_editing:    { url: ROUTES.enableTeacherEditing,  body: { subjectclass_ids: [id] } },
    };
    const ep = endpointMap[action];
    await postJSON(ep.url, ep.body);
};

// ── Audit info popup ──────────────────────────────────────────
window.showAuditInfo = function (id) {
    const sheet = scoresheetsData.find(s => s.subjectclass_id === id);
    if (!sheet) return;

    const status = getStatus(sheet);
    Swal.fire({
        title: 'Lock Info',
        icon: 'info',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Close',
        html: `
            <div style="text-align:left;font-size:13px;line-height:1.8">
                <p><strong>${escHtml(sheet.subject_name)}</strong> — ${escHtml(sheet.class_name)}</p>
                <p><strong>Teacher:</strong> ${escHtml(sheet.teacher_name)}</p>
                <p><strong>Status:</strong> <span style="font-weight:600">${status.text}</span></p>
                <hr>
                <p><strong>Global lock:</strong> ${sheet.global_lock_active ? '<span style="color:#dc2626">Active</span>' : 'Inactive'}</p>
                ${sheet.global_lock_reason ? `<p><strong>Reason:</strong> ${escHtml(sheet.global_lock_reason)}</p>` : ''}
                ${sheet.global_lock_by     ? `<p><strong>Locked by:</strong> ${escHtml(sheet.global_lock_by)} at ${escHtml(sheet.global_lock_at || '')}</p>` : ''}
                <p><strong>Individually locked:</strong> ${sheet.individually_locked_count} / ${sheet.total_students} students</p>
                <p><strong>Teacher editing:</strong> ${sheet.teacher_editing_enabled ? '<span style="color:#16a34a">Enabled</span>' : '<span style="color:#dc2626">Disabled</span>'}</p>
            </div>`,
    });
};

// ── HTTP helpers ──────────────────────────────────────────────
async function sendBulkRequest(url, body) {
    try {
        const res    = await fetch(url, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify(body) });
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
        const res    = await fetch(url, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify(body) });
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
    return { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF };
}

// ── Toast ─────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    const icons = { success: 'ri-checkbox-circle-fill', error: 'ri-error-warning-fill', info: 'ri-information-fill' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class="${icons[type] || icons.info}" style="font-size:18px"></i>
                       <span style="font-size:13px">${escHtml(message)}</span>`;
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

function escHtml(text) {
    if (text === null || text === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(text);
    return d.innerHTML;
}
</script>
@endsection
