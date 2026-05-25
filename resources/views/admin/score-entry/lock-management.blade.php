@extends('layouts.master')

@section('content')
<style>
:root {
    --color-text-primary: #1a1a2e;
    --color-text-secondary: #6c757d;
    --color-background-primary: #ffffff;
    --color-background-secondary: #f8f9fa;
    --color-border-primary: #e9ecef;
    --color-border-secondary: #dee2e6;
    --border-radius-sm: 6px;
    --border-radius-md: 8px;
    --border-radius-lg: 12px;
}

* { box-sizing: border-box; }

.lm-container {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.lm-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-border-primary);
}

.lm-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.lm-subtitle {
    color: var(--color-text-secondary);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--color-background-primary);
    border: 1px solid var(--color-border-primary);
    border-radius: var(--border-radius-md);
    padding: 1rem;
    transition: all 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-bar {
    background: var(--color-background-secondary);
    border: 1px solid var(--color-border-primary);
    border-radius: var(--border-radius-md);
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--color-text-secondary);
    text-transform: uppercase;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--color-border-primary);
    border-radius: var(--border-radius-sm);
    font-size: 0.875rem;
}

.action-bar {
    background: var(--color-background-primary);
    border: 1px solid var(--color-border-primary);
    border-radius: var(--border-radius-md);
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.selected-info {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-right: auto;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: var(--border-radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary { background: #4a90e2; color: white; }
.btn-primary:hover { background: #357abd; }

.btn-success { background: #28a745; color: white; }
.btn-success:hover { background: #218838; }

.btn-warning { background: #ffc107; color: #212529; }
.btn-warning:hover { background: #e0a800; }

.btn-danger { background: #dc3545; color: white; }
.btn-danger:hover { background: #c82333; }

.btn-secondary { background: #6c757d; color: white; }
.btn-secondary:hover { background: #5a6268; }

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.scoresheet-table {
    background: var(--color-background-primary);
    border: 1px solid var(--color-border-primary);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
}

.table-header {
    background: var(--color-background-secondary);
    padding: 0.75rem 1rem;
    display: grid;
    grid-template-columns: 40px 2fr 1.5fr 1.5fr 1.5fr 1.5fr 1fr;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--color-text-secondary);
    border-bottom: 1px solid var(--color-border-primary);
}

.scoresheet-row {
    padding: 0.75rem 1rem;
    display: grid;
    grid-template-columns: 40px 2fr 1.5fr 1.5fr 1.5fr 1.5fr 1fr;
    gap: 0.75rem;
    align-items: center;
    border-bottom: 1px solid var(--color-border-primary);
    transition: background 0.2s;
    cursor: pointer;
}

.scoresheet-row:hover {
    background: var(--color-background-secondary);
}

.scoresheet-row.selected {
    background: #e3f2fd;
    border-left: 3px solid #4a90e2;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-open { background: #d4edda; color: #155724; }
.status-individual { background: #fff3cd; color: #856404; }
.status-global { background: #f8d7da; color: #721c24; }
.status-disabled { background: #e2e3e5; color: #383d41; }

.row-actions {
    display: flex;
    gap: 0.5rem;
}

.icon-btn {
    padding: 0.25rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    transition: opacity 0.2s;
}

.icon-btn:hover { opacity: 0.7; }

.loading {
    text-align: center;
    padding: 2rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--color-text-secondary);
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
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    margin-bottom: 1rem;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-body {
    margin-bottom: 1.5rem;
}

.modal-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--color-border-primary);
    border-radius: var(--border-radius-sm);
    font-size: 0.875rem;
    resize: vertical;
}

.toast-container {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    z-index: 1100;
}

.toast {
    background: white;
    border-left: 4px solid;
    border-radius: var(--border-radius-sm);
    padding: 0.75rem 1rem;
    margin-top: 0.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-success { border-left-color: #28a745; }
.toast-error { border-left-color: #dc3545; }
.toast-info { border-left-color: #17a2b8; }

.spin {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .table-header, .scoresheet-row {
        grid-template-columns: 40px 1fr;
        gap: 0.5rem;
    }

    .hide-mobile {
        display: none;
    }
}
</style>

<div class="lm-container">
    <div class="lm-header">
        <div class="lm-title">
            <i class="ri-shield-lock-line"></i>
            Scoresheet Lock Management
        </div>
        <div class="lm-subtitle">
            Manage locks, disable teacher editing, and control access to scoresheets across all subjects
        </div>
    </div>

    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-value" id="statTotal">0</div>
            <div class="stat-label">Total Scoresheets</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-success" id="statOpen">0</div>
            <div class="stat-label">Open</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-warning" id="statIndividual">0</div>
            <div class="stat-label">Individually Locked</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-danger" id="statGlobal">0</div>
            <div class="stat-label">Globally Locked</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-secondary" id="statDisabled">0</div>
            <div class="stat-label">Editing Disabled</div>
        </div>
    </div>

    <div class="filter-bar">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" id="searchInput" placeholder="Teacher, subject, code...">
        </div>
        <div class="filter-group">
            <label>Term</label>
            <select id="termFilter">
                <option value="">All Terms</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Session</label>
            <select id="sessionFilter">
                <option value="">All Sessions</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Class</label>
            <select id="classFilter">
                <option value="">All Classes</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="individual">Individually Locked</option>
                <option value="global">Globally Locked</option>
                <option value="disabled">Editing Disabled</option>
            </select>
        </div>
        <div class="filter-group">
            <button class="btn btn-primary" id="applyFiltersBtn">
                <i class="ri-filter-line"></i> Apply Filters
            </button>
        </div>
    </div>

    <div class="action-bar">
        <div class="selected-info">
            <strong id="selectedCount">0</strong> scoresheet(s) selected
        </div>
        <button class="btn btn-warning" id="bulkLockBtn" disabled>
            <i class="ri-lock-line"></i> Lock Selected
        </button>
        <button class="btn btn-success" id="bulkUnlockBtn" disabled>
            <i class="ri-lock-unlock-line"></i> Unlock Selected
        </button>
        <button class="btn btn-danger" id="bulkDisableBtn" disabled>
            <i class="ri-ban-line"></i> Disable Editing
        </button>
        <button class="btn btn-secondary" id="bulkEnableBtn" disabled>
            <i class="ri-check-line"></i> Enable Editing
        </button>
        <button class="btn btn-primary" id="refreshBtn">
            <i class="ri-refresh-line"></i> Refresh
        </button>
    </div>

    <div class="scoresheet-table">
        <div class="table-header">
            <div><input type="checkbox" id="selectAllCheckbox"></div>
            <div>Teacher & Subject</div>
            <div class="hide-mobile">Class</div>
            <div class="hide-mobile">Term</div>
            <div class="hide-mobile">Session</div>
            <div>Status</div>
            <div>Actions</div>
        </div>
        <div id="scoresheetsList">
            <div class="loading">
                <i class="ri-loader-4-line spin"></i> Loading scoresheets...
            </div>
        </div>
    </div>
</div>

<div id="modalContainer"></div>
<div id="toastContainer" class="toast-container"></div>

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
            renderScoresheets();
            updateStats();
        } else {
            showToast(result.message || 'Failed to load scoresheets', 'error');
        }
    } catch (error) {
        console.error('Error loading scoresheets:', error);
        showToast('Network error loading scoresheets', 'error');
    }
}

function renderScoresheets() {
    const container = document.getElementById('scoresheetsList');

    if (!scoresheetsData.length) {
        container.innerHTML = '<div class="empty-state"><i class="ri-inbox-line"></i><p>No scoresheets found matching your criteria</p></div>';
        return;
    }

    container.innerHTML = '';

    scoresheetsData.forEach(sheet => {
        const status = getStatus(sheet);
        const row = document.createElement('div');
        row.className = `scoresheet-row ${selectedIds.has(sheet.subjectclass_id) ? 'selected' : ''}`;
        row.dataset.id = sheet.subjectclass_id;

        row.innerHTML = `
            <div><input type="checkbox" class="row-checkbox" data-id="${sheet.subjectclass_id}" ${selectedIds.has(sheet.subjectclass_id) ? 'checked' : ''}></div>
            <div>
                <div><strong>${escapeHtml(sheet.teacher_name)}</strong></div>
                <div><small class="text-muted">${escapeHtml(sheet.subject_name)} (${sheet.subject_code})</small></div>
            </div>
            <div class="hide-mobile">${escapeHtml(sheet.class_name)}</div>
            <div class="hide-mobile">${sheet.term_name || '-'}</div>
            <div class="hide-mobile">${sheet.session_name || '-'}</div>
            <div>
                <span class="status-badge ${status.class}">
                    <i class="${status.icon}"></i> ${status.text}
                </span>
                ${sheet.individually_locked_count > 0 ? `<div><small>${sheet.individually_locked_count}/${sheet.total_students} locked</small></div>` : ''}
                ${sheet.global_lock_reason ? `<div><small title="${escapeHtml(sheet.global_lock_reason)}">Reason: ${escapeHtml(sheet.global_lock_reason.substring(0, 30))}${sheet.global_lock_reason.length > 30 ? '...' : ''}</small></div>` : ''}
            </div>
            <div class="row-actions">
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
            </div>
        `;

        container.appendChild(row);
    });

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', (e) => {
            const id = parseInt(e.target.dataset.id);
            if (e.target.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
            updateSelectedCount();
            updateRowSelection(id);
            updateSelectAllCheckbox();
        });
    });
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
        return { class: 'status-individual', text: `Partially Locked (${sheet.individually_locked_count}/${sheet.total_students})`, icon: 'ri-lock-line' };
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

function updateRowSelection(id) {
    const row = document.querySelector(`.scoresheet-row[data-id="${id}"]`);
    if (row) {
        if (selectedIds.has(id)) {
            row.classList.add('selected');
        } else {
            row.classList.remove('selected');
        }
    }
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
    renderScoresheets();
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

    const subs = {
        lock_individual: 'This will lock all student scoresheets in the selected subjects.',
        unlock_individual: 'This will unlock all student scoresheets in the selected subjects.',
        disable_editing: 'Teachers will not be able to edit ANY scores in these subjects.',
        enable_editing: 'Teachers will regain editing abilities for these subjects.'
    };

    showModal(titles[action], subs[action], needsReason, async (reason) => {
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
                    reason: reason,
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
    });
}

async function quickAction(id, action) {
    const needsReason = ['lock_individual', 'disable_editing'].includes(action);
    const titles = {
        lock_individual: 'Lock Scoresheet',
        unlock_individual: 'Unlock Scoresheet',
        disable_editing: 'Disable Teacher Editing',
        enable_editing: 'Enable Teacher Editing'
    };

    const subs = {
        lock_individual: 'This will lock all scores for this subject.',
        unlock_individual: 'This will unlock all scores for this subject.',
        disable_editing: 'The teacher will not be able to edit scores.',
        enable_editing: 'The teacher will regain editing abilities.'
    };

    showModal(titles[action], subs[action], needsReason, async (reason) => {
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
    });
}

function showAuditHistory(subjectclassId) {
    const sheet = scoresheetsData.find(s => s.subjectclass_id === subjectclassId);
    if (!sheet) return;

    const modalHtml = `
        <div class="modal-overlay" onclick="closeModal(event)">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <div class="modal-title">Audit History</div>
                </div>
                <div class="modal-body">
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
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modalContainer').innerHTML = modalHtml;
}

function showModal(title, message, needsReason, callback) {
    const modalHtml = `
        <div class="modal-overlay" onclick="closeModal(event)">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <div class="modal-title">${escapeHtml(title)}</div>
                </div>
                <div class="modal-body">
                    <p>${escapeHtml(message)}</p>
                    ${needsReason ? `
                        <label style="display: block; margin-top: 1rem; margin-bottom: 0.5rem;">Reason (optional):</label>
                        <textarea id="modalReason" rows="3" placeholder="Enter reason for this action..."></textarea>
                    ` : ''}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="confirmModalAction(${needsReason}, callback)">Confirm</button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modalContainer').innerHTML = modalHtml;
    window.modalCallback = callback;
}

function confirmModalAction(needsReason, callback) {
    const reason = needsReason ? document.getElementById('modalReason')?.value : null;
    closeModal();
    if (window.modalCallback) {
        window.modalCallback(reason);
    }
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget && event.target !== document.querySelector('.modal-content')) {
        return;
    }
    document.getElementById('modalContainer').innerHTML = '';
    window.modalCallback = null;
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="ri-${type === 'success' ? 'checkbox-circle' : type === 'error' ? 'error-warning' : 'information'}-line"></i>
        <span>${escapeHtml(message)}</span>
    `;
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
