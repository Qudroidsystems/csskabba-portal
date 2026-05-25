@extends('layouts.master')

@section('content')
<style>
/* Lock Management Styles - Consistent with Admin Panel */
.lock-management-wrapper {
    padding: 1.5rem;
}

/* Header Section */
.lock-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: white;
}

.lock-header h2 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 8px;
}

.lock-header p {
    opacity: 0.9;
    margin-bottom: 0;
}

.lock-header-actions {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-back {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    transform: translateY(-2px);
}

/* Stats Cards */
.stats-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.stat-card-header {
    padding: 16px 20px 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stat-card-header h3 {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-card-body {
    padding: 8px 20px 20px 20px;
}

.stat-main-value {
    font-size: 36px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 4px;
}

.stat-trend {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
}

.stat-footer {
    background: #f8fafc;
    padding: 12px 20px;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
    color: #64748b;
}

/* Filter Card */
.filter-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.filter-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 160px;
}

.filter-group label {
    display: block;
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
    font-size: 14px;
    transition: all 0.2s;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

/* Action Bar */
.action-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.selected-info {
    font-size: 14px;
    color: #64748b;
    margin-right: auto;
    background: #f1f5f9;
    padding: 6px 14px;
    border-radius: 20px;
}

.selected-info strong {
    color: #1e293b;
    font-weight: 700;
}

/* Buttons */
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

.btn-sm {
    padding: 6px 14px;
    font-size: 12px;
}

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

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Scoresheet Table */
.scoresheet-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    background: #f8fafc;
    padding: 14px 20px;
    display: grid;
    grid-template-columns: 40px 2fr 1.5fr 1.5fr 1.5fr 1.5fr 120px;
    gap: 16px;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
}

.scoresheet-row {
    padding: 14px 20px;
    display: grid;
    grid-template-columns: 40px 2fr 1.5fr 1.5fr 1.5fr 1.5fr 120px;
    gap: 16px;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s;
}

.scoresheet-row:hover {
    background: #f8fafc;
}

.scoresheet-row.selected {
    background: #eff6ff;
    border-left: 3px solid #2563eb;
    margin-left: -3px;
}

/* Checkbox Styling */
.checkbox-wrapper {
    display: flex;
    align-items: center;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563eb;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-open { background: #d1fae5; color: #065f46; }
.status-individual { background: #fed7aa; color: #92400e; }
.status-global { background: #fecaca; color: #991b1b; }
.status-disabled { background: #e2e3e5; color: #383d41; }

/* Action Buttons in Row */
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
    border: 1px solid #e2e8f0;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.icon-btn:hover {
    background: #f1f5f9;
    transform: scale(1.05);
}

.icon-btn.lock:hover { background: #fed7aa; border-color: #f59e0b; color: #d97706; }
.icon-btn.unlock:hover { background: #d1fae5; border-color: #10b981; color: #059669; }
.icon-btn.disable:hover { background: #fecaca; border-color: #ef4444; color: #dc2626; }
.icon-btn.enable:hover { background: #d1fae5; border-color: #10b981; color: #059669; }
.icon-btn.history:hover { background: #e0e7ff; border-color: #6366f1; color: #4f46e5; }

/* Loading & Empty States */
.loading-state, .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.loading-state i, .empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    color: #cbd5e1;
}

.empty-state h5 {
    font-size: 18px;
    margin-bottom: 8px;
    color: #475569;
}

.spin {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Modal */
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
    border-bottom: 1px solid #e2e8f0;
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
    border-top: 1px solid #e2e8f0;
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
    font-size: 14px;
    resize: vertical;
    font-family: inherit;
    margin-top: 12px;
}

.modal-textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

/* Toast Notifications */
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
.toast-warning { border-left-color: #f59e0b; }

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 1200px) {
    .table-header, .scoresheet-row {
        grid-template-columns: 40px 2fr 1.5fr 1.5fr 1.5fr 1fr 100px;
        gap: 12px;
    }
}

@media (max-width: 992px) {
    .hide-tablet {
        display: none;
    }

    .table-header, .scoresheet-row {
        grid-template-columns: 40px 2fr 1.5fr 1fr 100px;
    }
}

@media (max-width: 768px) {
    .lock-header {
        padding: 20px;
    }

    .lock-header h2 {
        font-size: 20px;
    }

    .stats-dashboard {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .stat-main-value {
        font-size: 28px;
    }

    .table-header, .scoresheet-row {
        grid-template-columns: 40px 1fr;
        gap: 10px;
    }

    .hide-mobile {
        display: none;
    }

    .filter-group {
        min-width: 100%;
    }

    .filter-row {
        flex-direction: column;
    }
}

/* Small text utility */
.text-muted { color: #64748b; }
.text-sm { font-size: 12px; }
.font-semibold { font-weight: 600; }
</style>

<div class="lock-management-wrapper">
    <!-- Header Section -->
    <div class="lock-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h2><i class="ri-shield-lock-line me-2"></i>Scoresheet Lock Management</h2>
                <p>Manage locks, disable teacher editing, and control access to scoresheets across all subjects</p>
            </div>
            <div class="lock-header-actions">
                <a href="{{ route('admin.score-entry.index') }}" class="btn-back">
                    <i class="ri-arrow-left-line"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="stats-dashboard" id="statsGrid">
        <div class="stat-card">
            <div class="stat-card-header">
                <h3>Total Scoresheets</h3>
                <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;">
                    <i class="ri-file-list-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value" id="statTotal">0</div>
                <div class="stat-trend">
                    <i class="ri-database-2-line"></i>
                    <span>Total records in system</span>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-time-line me-1"></i> Across all terms
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <h3>Open</h3>
                <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                    <i class="ri-lock-unlock-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-success" id="statOpen">0</div>
                <div class="stat-trend">
                    <i class="ri-checkbox-circle-line"></i>
                    <span>Fully editable</span>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-edit-line me-1"></i> Teachers can edit
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <h3>Individually Locked</h3>
                <div class="stat-icon" style="background: #fed7aa; color: #f59e0b;">
                    <i class="ri-lock-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-warning" id="statIndividual">0</div>
                <div class="stat-trend">
                    <i class="ri-user-settings-line"></i>
                    <span>Per-student locks</span>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-user-line me-1"></i> Individual student records
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <h3>Globally Locked</h3>
                <div class="stat-icon" style="background: #fecaca; color: #ef4444;">
                    <i class="ri-global-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-danger" id="statGlobal">0</div>
                <div class="stat-trend">
                    <i class="ri-shield-warning-line"></i>
                    <span>Full subject lock</span>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-alert-line me-1"></i> No teacher edits allowed
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <h3>Editing Disabled</h3>
                <div class="stat-icon" style="background: #e2e3e5; color: #6c757d;">
                    <i class="ri-ban-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-secondary" id="statDisabled">0</div>
                <div class="stat-trend">
                    <i class="ri-user-forbid-line"></i>
                    <span>Teacher editing off</span>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-settings-4-line me-1"></i> Disabled by admin
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <div class="filter-row">
            <div class="filter-group">
                <label><i class="ri-search-line me-1"></i> Search</label>
                <input type="text" id="searchInput" placeholder="Teacher, subject or code...">
            </div>
            <div class="filter-group">
                <label><i class="ri-calendar-line me-1"></i> Term</label>
                <select id="termFilter">
                    <option value="">All Terms</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="ri-calendar-event-line me-1"></i> Session</label>
                <select id="sessionFilter">
                    <option value="">All Sessions</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="ri-group-line me-1"></i> Class</label>
                <select id="classFilter">
                    <option value="">All Classes</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="ri-shield-line me-1"></i> Status</label>
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="individual">Individually Locked</option>
                    <option value="global">Globally Locked</option>
                    <option value="disabled">Editing Disabled</option>
                </select>
            </div>
            <div class="filter-group" style="flex: 0.5; min-width: auto;">
                <button class="btn btn-primary w-100" id="applyFiltersBtn">
                    <i class="ri-filter-3-line"></i> Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
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
    </div>

    <!-- Scoresheet Table -->
    <div class="scoresheet-card">
        <div class="table-header">
            <div class="checkbox-wrapper">
                <input type="checkbox" class="checkbox-custom" id="selectAllCheckbox">
            </div>
            <div><i class="ri-user-line me-1"></i> Teacher & Subject</div>
            <div class="hide-tablet"><i class="ri-group-line me-1"></i> Class</div>
            <div class="hide-tablet"><i class="ri-calendar-line me-1"></i> Term</div>
            <div class="hide-tablet"><i class="ri-calendar-event-line me-1"></i> Session</div>
            <div><i class="ri-shield-line me-1"></i> Status</div>
            <div><i class="ri-settings-4-line me-1"></i> Actions</div>
        </div>
        <div id="scoresheetsList">
            <div class="loading-state">
                <i class="ri-loader-4-line spin"></i>
                <p>Loading scoresheets...</p>
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
        container.innerHTML = `
            <div class="empty-state">
                <i class="ri-inbox-line"></i>
                <h5>No scoresheets found</h5>
                <p class="text-muted">Try adjusting your search filters</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    scoresheetsData.forEach(sheet => {
        const status = getStatus(sheet);
        const row = document.createElement('div');
        row.className = `scoresheet-row ${selectedIds.has(sheet.subjectclass_id) ? 'selected' : ''}`;
        row.dataset.id = sheet.subjectclass_id;

        row.innerHTML = `
            <div class="checkbox-wrapper">
                <input type="checkbox" class="checkbox-custom row-checkbox" data-id="${sheet.subjectclass_id}" ${selectedIds.has(sheet.subjectclass_id) ? 'checked' : ''}>
            </div>
            <div>
                <div class="font-semibold">${escapeHtml(sheet.teacher_name)}</div>
                <div class="text-sm text-muted">${escapeHtml(sheet.subject_name)} (${sheet.subject_code})</div>
            </div>
            <div class="hide-tablet">${escapeHtml(sheet.class_name)}</div>
            <div class="hide-tablet">${sheet.term_name || '-'}</div>
            <div class="hide-tablet">${sheet.session_name || '-'}</div>
            <div>
                <span class="status-badge ${status.class}">
                    <i class="${status.icon}"></i> ${status.text}
                </span>
                ${sheet.individually_locked_count > 0 ? `<div class="text-sm text-muted mt-1">${sheet.individually_locked_count}/${sheet.total_students} students locked</div>` : ''}
                ${sheet.global_lock_reason ? `<div class="text-sm text-muted mt-1" title="${escapeHtml(sheet.global_lock_reason)}">${escapeHtml(sheet.global_lock_reason.substring(0, 40))}${sheet.global_lock_reason.length > 40 ? '...' : ''}</div>` : ''}
            </div>
            <div class="row-actions">
                <button class="icon-btn lock" onclick="quickAction(${sheet.subjectclass_id}, 'lock_individual')" title="Lock Individual">
                    <i class="ri-lock-line"></i>
                </button>
                <button class="icon-btn unlock" onclick="quickAction(${sheet.subjectclass_id}, 'unlock_individual')" title="Unlock Individual">
                    <i class="ri-lock-unlock-line"></i>
                </button>
                <button class="icon-btn disable" onclick="quickAction(${sheet.subjectclass_id}, 'disable_editing')" title="Disable Editing">
                    <i class="ri-ban-line"></i>
                </button>
                <button class="icon-btn enable" onclick="quickAction(${sheet.subjectclass_id}, 'enable_editing')" title="Enable Editing">
                    <i class="ri-check-line"></i>
                </button>
                <button class="icon-btn history" onclick="showAuditHistory(${sheet.subjectclass_id})" title="Audit History">
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
            <div class="modal-container" onclick="event.stopPropagation()">
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
                    <button class="btn btn-secondary btn-sm" onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modalContainer').innerHTML = modalHtml;
}

function showModal(title, message, needsReason, callback) {
    const modalHtml = `
        <div class="modal-overlay" onclick="closeModal(event)">
            <div class="modal-container" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <div class="modal-title">${escapeHtml(title)}</div>
                </div>
                <div class="modal-body">
                    <p>${escapeHtml(message)}</p>
                    ${needsReason ? `
                        <label class="text-sm font-semibold text-muted">Reason (optional):</label>
                        <textarea id="modalReason" class="modal-textarea" rows="3" placeholder="Enter reason for this action..."></textarea>
                    ` : ''}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline btn-sm" onclick="closeModal()">Cancel</button>
                    <button class="btn btn-primary btn-sm" onclick="confirmModalAction(${needsReason})">Confirm</button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modalContainer').innerHTML = modalHtml;
    window.modalCallback = callback;
}

function confirmModalAction(needsReason) {
    const reason = needsReason ? document.getElementById('modalReason')?.value : null;
    closeModal();
    if (window.modalCallback) {
        window.modalCallback(reason);
    }
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget && event.target !== document.querySelector('.modal-container')) {
        return;
    }
    document.getElementById('modalContainer').innerHTML = '';
    window.modalCallback = null;
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = {
        success: 'ri-checkbox-circle-fill',
        error: 'ri-error-warning-fill',
        info: 'ri-information-fill',
        warning: 'ri-alert-fill'
    }[type] || 'ri-information-fill';

    toast.innerHTML = `
        <i class="${icon}"></i>
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
