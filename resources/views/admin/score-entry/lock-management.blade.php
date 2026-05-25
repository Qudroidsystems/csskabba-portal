@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
.selected-info strong {
    color: #1e293b;
    font-weight: 700;
}

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
}
.btn-sm { padding: 6px 14px; font-size: 12px; }
.btn-primary { background: #2563eb; color: white; }
.btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
.btn-success { background: #10b981; color: white; }
.btn-success:hover { background: #059669; transform: translateY(-1px); }
.btn-warning { background: #f59e0b; color: white; }
.btn-warning:hover { background: #d97706; transform: translateY(-1px); }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
.btn-secondary { background: #64748b; color: white; }
.btn-secondary:hover { background: #475569; transform: translateY(-1px); }
.btn-outline { background: transparent; border: 1px solid #cbd5e1; color: #475569; }
.btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.lm-table th {
    background: var(--lm-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.lm-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--lm-border);
    font-size: 13px;
}
.lm-table tr:hover td { background: #f0f9ff; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-open { background: #d1fae5; color: #065f46; }
.status-individual { background: #fed7aa; color: #92400e; }
.status-global { background: #fecaca; color: #991b1b; }
.status-disabled { background: #e2e3e5; color: #383d41; }

.row-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.icon-btn {
    width: 32px;
    height: 32px;
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

.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--lm-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--lm-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(2px);
}
.modal-container {
    background: white;
    border-radius: 16px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}
.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--lm-border);
    background: #f8fafc;
}
.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}
.modal-body {
    padding: 24px;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--lm-border);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    background: #f8fafc;
}
.modal-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    resize: vertical;
    font-family: inherit;
    margin-top: 12px;
}
.modal-textarea:focus {
    outline: none;
    border-color: var(--lm-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.toast-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1100;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.toast {
    background: white;
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideInRight 0.3s ease;
    border-left: 4px solid;
}
.toast-success { border-left-color: #10b981; }
.toast-error { border-left-color: #ef4444; }
.toast-info { border-left-color: #3b82f6; }

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

@media (max-width: 768px) {
    .lm-hero { padding: 20px; }
    .lm-hero h1 { font-size: 18px; }
    .stat-card .stat-value { font-size: 22px; }
    .filter-group { margin-bottom: 10px; }
    .action-bar { flex-direction: column; align-items: stretch; }
    .selected-info { margin-right: 0; text-align: center; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="lm-hero">
        <h1><i class="ri-shield-lock-line me-2"></i>Scoresheet Lock Management</h1>
        <p>Manage locks, disable teacher editing, and control access to scoresheets across all subjects</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-file-list-line"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Total Scoresheets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-lock-unlock-line"></i></div>
                <div class="stat-value text-success" id="statOpen">0</div>
                <div class="stat-label">Open</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-lock-line"></i></div>
                <div class="stat-value text-warning" id="statIndividual">0</div>
                <div class="stat-label">Individually Locked</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-global-line"></i></div>
                <div class="stat-value text-danger" id="statGlobal">0</div>
                <div class="stat-label">Globally Locked</div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <div class="filter-group">
                    <label><i class="ri-search-line me-1"></i> Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Teacher, subject or code...">
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-calendar-line me-1"></i> Term</label>
                    <select id="termFilter" class="form-select">
                        <option value="">All Terms</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-calendar-event-line me-1"></i> Session</label>
                    <select id="sessionFilter" class="form-select">
                        <option value="">All Sessions</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-group-line me-1"></i> Class</label>
                    <select id="classFilter" class="form-select">
                        <option value="">All Classes</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="filter-group">
                    <label><i class="ri-shield-line me-1"></i> Status</label>
                    <select id="statusFilter" class="form-select">
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

    <div class="action-bar">
        <div class="selected-info">
            <i class="ri-checkbox-line me-1"></i>
            <strong id="selectedCount">0</strong> scoresheet(s) selected
        </div>
        <button class="btn btn-warning btn-sm" id="bulkLockBtn" disabled>
            <i class="ri-lock-line"></i> Lock Selected
        </button>
        <button class="btn btn-success btn-sm" id="bulkUnlockBtn" disabled>
            <i class="ri-lock-unlock-line"></i> Unlock Selected
        </button>
        <button class="btn btn-danger btn-sm" id="bulkDisableBtn" disabled>
            <i class="ri-ban-line"></i> Disable Editing
        </button>
        <button class="btn btn-secondary btn-sm" id="bulkEnableBtn" disabled>
            <i class="ri-check-line"></i> Enable Editing
        </button>
        <button class="btn btn-outline btn-sm" id="refreshBtn">
            <i class="ri-refresh-line"></i> Refresh
        </button>
        <a href="{{ route('admin.score-entry.index') }}" class="btn btn-outline btn-sm">
            <i class="ri-arrow-left-line"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold" style="color:var(--lm-primary)">
                <i class="ri-list-check me-2"></i>Scoresheets
                <span class="badge bg-primary ms-2" id="recordCount">0</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table lm-table w-100 mb-0" id="scoresheetsTable">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
                            <th>Teacher & Subject</th>
                            <th>Class</th>
                            <th>Term</th>
                            <th>Session</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scoresheetsTableBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-loader-4-line spin d-block mb-2" style="font-size:2rem"></i>
                                Loading scoresheets...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<div id="modalContainer"></div>
<div id="toastContainer" class="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const routes = {
    scoresheetsList: '{{ route("admin.score-entry.scoresheets-list") }}',
    bulkLockManagement: '{{ route("admin.score-entry.bulk-lock-management") }}',
    lockBatch: '{{ route("admin.score-entry.lock-batch") }}',
    unlockBatch: '{{ route("admin.score-entry.unlock-batch") }}',
    disableTeacherEditing: '{{ route("admin.score-entry.disable-teacher-editing") }}',
    enableTeacherEditing: '{{ route("admin.score-entry.enable-teacher-editing") }}',
};

let scoresheetsData = [];
let selectedIds = new Set();
let dataTable = null;

document.addEventListener('DOMContentLoaded', function() {
    loadFilters();
    loadScoresheets();

    document.getElementById('applyFiltersBtn').addEventListener('click', loadScoresheets);
    document.getElementById('refreshBtn').addEventListener('click', loadScoresheets);
    document.getElementById('bulkLockBtn').addEventListener('click', () => bulkAction('lock_individual'));
    document.getElementById('bulkUnlockBtn').addEventListener('click', () => bulkAction('unlock_individual'));
    document.getElementById('bulkDisableBtn').addEventListener('click', () => bulkAction('disable_editing'));
    document.getElementById('bulkEnableBtn').addEventListener('click', () => bulkAction('enable_editing'));
    document.getElementById('selectAllCheckbox').addEventListener('change', toggleSelectAll);
});

async function loadFilters() {
    try {
        const response = await fetch(routes.scoresheetsList);
        const result = await response.json();

        if (result.success && result.filters) {
            const termSelect = document.getElementById('termFilter');
            const sessionSelect = document.getElementById('sessionFilter');
            const classSelect = document.getElementById('classFilter');

            termSelect.innerHTML = '<option value="">All Terms</option>';
            result.filters.terms.forEach(term => {
                termSelect.innerHTML += `<option value="${term.id}">${escapeHtml(term.term)}</option>`;
            });

            sessionSelect.innerHTML = '<option value="">All Sessions</option>';
            result.filters.sessions.forEach(session => {
                sessionSelect.innerHTML += `<option value="${session.id}">${escapeHtml(session.session)}</option>`;
            });

            classSelect.innerHTML = '<option value="">All Classes</option>';
            result.filters.classes.forEach(cls => {
                const armName = cls.arm?.arm || '';
                classSelect.innerHTML += `<option value="${cls.id}">${escapeHtml(cls.schoolclass)} ${escapeHtml(armName)}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading filters:', error);
        showToast('Failed to load filters', 'error');
    }
}

async function loadScoresheets() {
    const params = new URLSearchParams({
        search: document.getElementById('searchInput').value,
        term_id: document.getElementById('termFilter').value,
        session_id: document.getElementById('sessionFilter').value,
        class_id: document.getElementById('classFilter').value,
        status: document.getElementById('statusFilter').value
    });

    try {
        const response = await fetch(`${routes.scoresheetsList}?${params}`);
        const result = await response.json();

        if (result.success) {
            scoresheetsData = result.data;
            renderDataTable();
            updateStats();
        } else {
            showToast(result.message || 'Failed to load scoresheets', 'error');
        }
    } catch (error) {
        console.error('Error loading scoresheets:', error);
        showToast('Network error loading scoresheets', 'error');
    }
}

function renderDataTable() {
    const tbody = document.getElementById('scoresheetsTableBody');

    if (!scoresheetsData.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="ri-inbox-line d-block mb-2" style="font-size:2rem;opacity:.4"></i>No scoresheets found matching your criteria</td></tr>';
        document.getElementById('recordCount').textContent = '0';
        if (dataTable) {
            dataTable.destroy();
            dataTable = null;
        }
        return;
    }

    tbody.innerHTML = '';
    scoresheetsData.forEach(sheet => {
        const status = getStatus(sheet);
        const row = document.createElement('tr');
        row.dataset.id = sheet.subjectclass_id;
        row.className = selectedIds.has(sheet.subjectclass_id) ? 'table-active' : '';

        row.innerHTML = `
            <td><input type="checkbox" class="form-check-input row-checkbox" data-id="${sheet.subjectclass_id}" ${selectedIds.has(sheet.subjectclass_id) ? 'checked' : ''}></td>
            <td>
                <div class="fw-semibold">${escapeHtml(sheet.teacher_name)}</div>
                <div class="text-muted small">${escapeHtml(sheet.subject_name)} (${sheet.subject_code})</div>
                ${sheet.individually_locked_count > 0 ? `<div class="text-muted small mt-1">${sheet.individually_locked_count}/${sheet.total_students} students locked</div>` : ''}
            </td>
            <td>${escapeHtml(sheet.class_name)}</td>
            <td>${sheet.term_name || '-'}</td>
            <td>${sheet.session_name || '-'}</td>
            <td>
                <span class="status-badge ${status.class}">
                    <i class="${status.icon}"></i> ${status.text}
                </span>
                ${sheet.global_lock_reason ? `<div class="text-muted small mt-1" title="${escapeHtml(sheet.global_lock_reason)}">${escapeHtml(sheet.global_lock_reason.substring(0, 40))}${sheet.global_lock_reason.length > 40 ? '...' : ''}</div>` : ''}
            </td>
            <td class="row-actions">
                <button class="icon-btn" onclick="quickAction(${sheet.subjectclass_id}, 'lock_individual')" title="Lock Individual">
                    <i class="ri-lock-line"></i>
                </button>
                <button class="icon-btn" onclick="quickAction(${sheet.subjectclass_id}, 'unlock_individual')" title="Unlock Individual">
                    <i class="ri-lock-unlock-line"></i>
                </button>
                <button class="icon-btn" onclick="quickAction(${sheet.subjectclass_id}, 'disable_editing')" title="Disable Editing">
                    <i class="ri-ban-line"></i>
                </button>
                <button class="icon-btn" onclick="quickAction(${sheet.subjectclass_id}, 'enable_editing')" title="Enable Editing">
                    <i class="ri-check-line"></i>
                </button>
                <button class="icon-btn" onclick="showAuditHistory(${sheet.subjectclass_id})" title="Audit History">
                    <i class="ri-history-line"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('recordCount').textContent = scoresheetsData.length;

    // Reinitialize DataTable
    if (dataTable) {
        dataTable.destroy();
    }

    dataTable = $('#scoresheetsTable').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ scoresheets',
            infoEmpty: 'No scoresheets found',
            zeroRecords: 'No matching scoresheets',
        },
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ],
        drawCallback: function() {
            // Re-attach checkbox events after redraw
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.removeEventListener('change', handleCheckboxChange);
                cb.addEventListener('change', handleCheckboxChange);
            });
        }
    });

    // Attach checkbox events
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.removeEventListener('change', handleCheckboxChange);
        cb.addEventListener('change', handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const id = parseInt(e.target.dataset.id);
    if (e.target.checked) {
        selectedIds.add(id);
    } else {
        selectedIds.delete(id);
    }
    updateSelectedCount();
    updateSelectAllCheckbox();
}

function getStatus(sheet) {
    if (!sheet.teacher_editing_enabled) {
        return { class: 'status-disabled', text: 'Editing Disabled', icon: 'ri-ban-line' };
    }
    if (sheet.global_lock_active) {
        return { class: 'status-global', text: 'Globally Locked', icon: 'ri-global-line' };
    }
    if (sheet.individually_locked_count > 0) {
        if (sheet.individually_locked_count === sheet.total_students) {
            return { class: 'status-individual', text: 'Fully Locked', icon: 'ri-lock-line' };
        }
        return { class: 'status-individual', text: `Partially Locked`, icon: 'ri-lock-line' };
    }
    return { class: 'status-open', text: 'Open', icon: 'ri-lock-unlock-line' };
}

function updateStats() {
    let total = scoresheetsData.length;
    let open = 0, individual = 0, global = 0, disabled = 0;

    scoresheetsData.forEach(sheet => {
        if (!sheet.teacher_editing_enabled) {
            disabled++;
        } else if (sheet.global_lock_active) {
            global++;
        } else if (sheet.individually_locked_count > 0) {
            individual++;
        } else {
            open++;
        }
    });

    document.getElementById('statTotal').textContent = total;
    document.getElementById('statOpen').textContent = open;
    document.getElementById('statIndividual').textContent = individual;
    document.getElementById('statGlobal').textContent = global;
    document.getElementById('statDisabled').textContent = disabled;
}

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedIds.size;
    const hasSelection = selectedIds.size > 0;
    document.getElementById('bulkLockBtn').disabled = !hasSelection;
    document.getElementById('bulkUnlockBtn').disabled = !hasSelection;
    document.getElementById('bulkDisableBtn').disabled = !hasSelection;
    document.getElementById('bulkEnableBtn').disabled = !hasSelection;
}

function updateSelectAllCheckbox() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const allIds = scoresheetsData.map(s => s.subjectclass_id);
    const selectedAll = allIds.length > 0 && allIds.every(id => selectedIds.has(id));
    selectAll.checked = selectedAll;
    selectAll.indeterminate = !selectedAll && allIds.some(id => selectedIds.has(id));
}

function toggleSelectAll(e) {
    const checked = e.target.checked;
    scoresheetsData.forEach(sheet => {
        if (checked) {
            selectedIds.add(sheet.subjectclass_id);
        } else {
            selectedIds.delete(sheet.subjectclass_id);
        }
    });
    renderDataTable();
    updateSelectedCount();
}

async function bulkAction(action) {
    const ids = Array.from(selectedIds);
    if (!ids.length) return;

    const needsReason = ['lock_individual', 'disable_editing'].includes(action);
    const titles = {
        lock_individual: 'Lock Selected Scoresheets',
        unlock_individual: 'Unlock Selected Scoresheets',
        disable_editing: 'Disable Teacher Editing',
        enable_editing: 'Enable Teacher Editing'
    };
    const messages = {
        lock_individual: 'This will lock all student scoresheets in the selected subjects.',
        unlock_individual: 'This will unlock all student scoresheets in the selected subjects.',
        disable_editing: 'Teachers will not be able to edit ANY scores in these subjects.',
        enable_editing: 'Teachers will regain editing abilities for these subjects.'
    };

    const { value: reason } = await Swal.fire({
        title: titles[action],
        text: messages[action],
        icon: 'question',
        input: needsReason ? 'textarea' : null,
        inputPlaceholder: needsReason ? 'Enter reason (optional)...' : null,
        showCancelButton: true,
        confirmButtonColor: action === 'disable_editing' ? '#dc2626' : '#2563eb',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel'
    });

    if (reason === undefined) return;

    try {
        const response = await fetch(routes.bulkLockManagement, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                action: action,
                subjectclass_ids: ids,
                reason: reason || null,
                term_id: document.getElementById('termFilter').value,
                session_id: document.getElementById('sessionFilter').value
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            selectedIds.clear();
            await loadScoresheets();
        } else {
            showToast(result.message || 'Action failed', 'error');
        }
    } catch (error) {
        console.error('Bulk action error:', error);
        showToast('Network error performing action', 'error');
    }
}

window.quickAction = async function(id, action) {
    const needsReason = ['lock_individual', 'disable_editing'].includes(action);
    const titles = {
        lock_individual: 'Lock Scoresheet',
        unlock_individual: 'Unlock Scoresheet',
        disable_editing: 'Disable Teacher Editing',
        enable_editing: 'Enable Teacher Editing'
    };
    const messages = {
        lock_individual: 'This will lock all scores for this subject.',
        unlock_individual: 'This will unlock all scores for this subject.',
        disable_editing: 'The teacher will not be able to edit scores.',
        enable_editing: 'The teacher will regain editing abilities.'
    };

    const { value: reason } = await Swal.fire({
        title: titles[action],
        text: messages[action],
        icon: 'question',
        input: needsReason ? 'textarea' : null,
        inputPlaceholder: needsReason ? 'Enter reason (optional)...' : null,
        showCancelButton: true,
        confirmButtonColor: action === 'disable_editing' ? '#dc2626' : '#2563eb',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel'
    });

    if (reason === undefined) return;

    let endpoint = '';
    let body = {};

    switch(action) {
        case 'lock_individual':
            endpoint = routes.lockBatch;
            body = { subjectclass_ids: [id], reason: reason, lock_type: 'individual' };
            break;
        case 'unlock_individual':
            endpoint = routes.unlockBatch;
            body = { subjectclass_ids: [id], unlock_type: 'individual' };
            break;
        case 'disable_editing':
            endpoint = routes.disableTeacherEditing;
            body = { subjectclass_ids: [id], reason: reason };
            break;
        case 'enable_editing':
            endpoint = routes.enableTeacherEditing;
            body = { subjectclass_ids: [id] };
            break;
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(body)
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            await loadScoresheets();
        } else {
            showToast(result.message || 'Action failed', 'error');
        }
    } catch (error) {
        console.error('Quick action error:', error);
        showToast('Network error performing action', 'error');
    }
};

window.showAuditHistory = function(subjectclassId) {
    const sheet = scoresheetsData.find(s => s.subjectclass_id === subjectclassId);
    if (!sheet) return;

    Swal.fire({
        title: 'Audit History',
        html: `
            <div style="text-align: left;">
                <p><strong>${escapeHtml(sheet.subject_name)}</strong> - ${escapeHtml(sheet.class_name)}</p>
                <p><strong>Teacher:</strong> ${escapeHtml(sheet.teacher_name)}</p>
                <p><strong>Current Status:</strong> ${getStatus(sheet).text}</p>
                <hr>
                <p><strong>Global Lock:</strong> ${sheet.global_lock_active ? 'Active' : 'Inactive'}</p>
                ${sheet.global_lock_reason ? `<p><strong>Lock Reason:</strong> ${escapeHtml(sheet.global_lock_reason)}</p>` : ''}
                ${sheet.global_lock_by ? `<p><strong>Locked By:</strong> ${escapeHtml(sheet.global_lock_by)} at ${sheet.global_lock_at}</p>` : ''}
                <p><strong>Individually Locked:</strong> ${sheet.individually_locked_count} out of ${sheet.total_students} students</p>
                <p><strong>Teacher Editing:</strong> ${sheet.teacher_editing_enabled ? 'Enabled' : 'Disabled'}</p>
            </div>
        `,
        icon: 'info',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Close'
    });
};

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = {
        success: 'ri-checkbox-circle-fill',
        error: 'ri-error-warning-fill',
        info: 'ri-information-fill'
    }[type] || 'ri-information-fill';

    toast.innerHTML = `<i class="${icon}"></i><span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
