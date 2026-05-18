@extends('layouts.master')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
:root {
    --cb-navy:      #0f2342;
    --cb-teal:      #0d9488;
    --cb-sky:       #0ea5e9;
    --cb-amber:     #f59e0b;
    --cb-rose:      #f43f5e;
    --cb-green:     #22c55e;
    --cb-muted:     #64748b;
    --cb-border:    #e2e8f0;
    --cb-surface:   #f8fafc;
    --cb-white:     #ffffff;
    --cb-radius:    14px;
    --cb-shadow:    0 4px 16px rgba(15,35,66,.10);
    --cb-shadow-lg: 0 8px 32px rgba(15,35,66,.14);
}

*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: #f1f5f9; }

/* Hero Section */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.cb-hero::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
    border-radius: 50%;
}
.cb-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 8px;
}
.cb-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.72);
    margin: 0;
}
.cb-hero .meta-pills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.cb-meta-pill {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Stat Cards */
.cb-stat {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.cb-stat:hover {
    transform: translateY(-2px);
    box-shadow: var(--cb-shadow);
}
.cb-stat .stat-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: var(--cb-radius) var(--cb-radius) 0 0;
}
.cb-stat .stat-value {
    font-size: 30px;
    font-weight: 700;
    color: var(--cb-navy);
    line-height: 1;
    margin-top: 8px;
}
.cb-stat .stat-label {
    font-size: 12px;
    color: var(--cb-muted);
    margin-top: 5px;
    font-weight: 500;
}
.cb-stat .stat-ico {
    font-size: 36px;
    opacity: .08;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
}

/* Filter Panel */
.filter-panel {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    padding: 20px 24px;
    margin-bottom: 28px;
    box-shadow: var(--cb-shadow);
}
.filter-panel h6 {
    font-size: 13px;
    font-weight: 700;
    color: var(--cb-navy);
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}
.filter-item label {
    font-size: 11px;
    font-weight: 600;
    color: var(--cb-muted);
    margin-bottom: 6px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.filter-item input, .filter-item select {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--cb-border);
    border-radius: 10px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.15s;
    background: var(--cb-surface);
}
.filter-item input:focus, .filter-item select:focus {
    border-color: var(--cb-teal);
    outline: none;
    box-shadow: 0 0 0 3px rgba(13,148,136,.1);
}
.btn-search {
    background: var(--cb-teal);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-search:hover {
    background: #0f766e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13,148,136,0.3);
}
.btn-reset {
    background: #f1f5f9;
    color: var(--cb-muted);
    border: 1.5px solid var(--cb-border);
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-reset:hover {
    background: #e2e8f0;
    border-color: var(--cb-teal);
    color: var(--cb-teal);
}

/* Main Card */
.cb-card {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    box-shadow: var(--cb-shadow);
    overflow: hidden;
}
.cb-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--cb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    background: linear-gradient(to right, #f8fafc, #f0fdf9);
}
.cb-card-header h5 {
    font-size: 15px;
    font-weight: 700;
    color: var(--cb-navy);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Table Styles */
.cb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.cb-table thead th {
    background: var(--cb-navy);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 11.5px;
    white-space: nowrap;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,.08);
}
.cb-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--cb-border);
}
.cb-table tbody tr:hover td {
    background: #f0fdf9;
}
.cb-table tbody tr:last-child td {
    border-bottom: none;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.btn-icon {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}
.btn-icon i {
    font-size: 13px;
}
.btn-primary-icon {
    background: #e0f2fe;
    color: #0369a1;
}
.btn-primary-icon:hover {
    background: #0ea5e9;
    color: white;
    transform: translateY(-2px);
}
.btn-info-icon {
    background: #dcfce7;
    color: #15803d;
}
.btn-info-icon:hover {
    background: #22c55e;
    color: white;
    transform: translateY(-2px);
}
.btn-warning-icon {
    background: #fef3c7;
    color: #92400e;
}
.btn-warning-icon:hover {
    background: #f59e0b;
    color: white;
    transform: translateY(-2px);
}

/* Dropdown Menu */
.cb-dropdown {
    position: relative;
    display: inline-block;
}
.cb-dropdown-btn {
    background: #f1f5f9;
    border: 1px solid var(--cb-border);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.cb-dropdown-btn:hover {
    background: var(--cb-teal);
    color: white;
    border-color: var(--cb-teal);
}
.cb-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid var(--cb-border);
    border-radius: 10px;
    box-shadow: var(--cb-shadow-lg);
    min-width: 200px;
    z-index: 100;
    display: none;
    margin-top: 5px;
}
.cb-dropdown-menu.show {
    display: block;
    animation: fadeIn 0.15s ease;
}
.cb-dropdown-menu .dropdown-header {
    padding: 8px 16px;
    font-size: 11px;
    font-weight: 700;
    color: var(--cb-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--cb-border);
}
.cb-dropdown-menu .dropdown-item {
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--cb-navy);
    font-size: 12px;
    transition: all 0.15s;
}
.cb-dropdown-menu .dropdown-item:hover {
    background: #f0fdf9;
    color: var(--cb-teal);
}
.cb-dropdown-menu .dropdown-item.text-danger:hover {
    background: #fee2e2;
    color: #dc2626;
}
.cb-dropdown-menu .divider {
    height: 1px;
    background: var(--cb-border);
    margin: 6px 0;
}

/* Pagination */
.pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 20px;
}
.page-item {
    display: inline-flex;
}
.page-link, .page-item {
    padding: 8px 12px;
    border: 1px solid var(--cb-border);
    border-radius: 8px;
    color: var(--cb-navy);
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.15s;
    background: white;
    cursor: pointer;
}
.page-link:hover, .page-item:hover:not(.disabled) {
    background: var(--cb-teal);
    color: white;
    border-color: var(--cb-teal);
}
.page-item.active {
    background: var(--cb-teal);
    color: white;
    border-color: var(--cb-teal);
}
.page-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Badge */
.cb-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.badge-current {
    background: #dcfce7;
    color: #15803d;
}
.badge-past {
    background: #f1f5f9;
    color: #64748b;
}

/* Checkbox */
.cb-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--cb-teal);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 768px) {
    .cb-hero { padding: 24px 20px; }
    .cb-hero h1 { font-size: 22px; }
    .filter-grid { grid-template-columns: 1fr; }
    .cb-table thead { display: none; }
    .cb-table tbody td {
        display: block;
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    .cb-table tbody td:before {
        content: attr(data-label);
        position: absolute;
        left: 16px;
        width: 45%;
        text-align: left;
        font-weight: 600;
        color: var(--cb-navy);
    }
}

/* Toast */
.cb-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    z-index: 9999;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
}
.cb-toast-success { background: #059669; }
.cb-toast-error { background: #dc2626; }
.cb-toast-info { background: #3b82f6; }
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Loading Spinner */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #e2e8f0;
    border-top-color: var(--cb-teal);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

<!-- Hero Section -->
<div class="cb-hero">
    <h1><i class="ri-graduation-cap-line me-2"></i>My Classes</h1>
    <p>Manage your assigned classes, view students, and access broadsheet reports.</p>
    <div class="meta-pills">
        <span class="cb-meta-pill"><i class="ri-user-line"></i>{{ Auth::user()->name }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-line"></i>{{ date('F j, Y') }}</span>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
            <div class="stat-ico"><i class="ri-book-open-line"></i></div>
            <div class="stat-value" id="totalClasses">{{ $myclass->total() }}</div>
            <div class="stat-label">Current Classes</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-group-line"></i></div>
            <div class="stat-value text-info" id="totalStudents">—</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-green),#4ade80);"></div>
            <div class="stat-ico"><i class="ri-file-text-line"></i></div>
            <div class="stat-value text-success" id="totalBroadsheets">—</div>
            <div class="stat-label">Broadsheets</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-amber),#fcd34d);"></div>
            <div class="stat-ico"><i class="ri-time-line"></i></div>
            <div class="stat-value text-warning" id="totalHistory">{{ $myclasshistory->count() }}</div>
            <div class="stat-label">Class History</div>
        </div>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel">
    <h6><i class="ri-filter-line" style="color:var(--cb-teal)"></i> Filter Classes</h6>
    <div class="filter-grid">
        <div class="filter-item">
            <label><i class="ri-search-line"></i> Search</label>
            <input type="text" id="searchInput" placeholder="Class, Arm, Term, Session..." value="{{ request()->query('search') }}">
        </div>
        <div class="filter-item">
            <label><i class="ri-building-line"></i> Class</label>
            <select id="classFilter">
                <option value="ALL">All Classes</option>
                @foreach ($schoolclasses as $class)
                    <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label><i class="ri-calendar-event-line"></i> Session</label>
            <select id="sessionFilter">
                <option value="ALL">All Sessions</option>
                @foreach ($schoolsessions as $session)
                    <option value="{{ $session->id }}">{{ $session->session }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <button class="btn-search" onclick="filterData()"><i class="ri-search-line"></i> Search</button>
            <button class="btn-reset ms-2" onclick="resetFilters()"><i class="ri-refresh-line"></i> Reset</button>
        </div>
    </div>
</div>

<!-- Classes Table Card -->
<div class="cb-card">
    <div class="cb-card-header">
        <h5>
            <i class="ri-table-alt-line" style="color:var(--cb-teal)"></i>
            My Assigned Classes
            <span class="cb-badge badge-current ms-2" id="classCountBadge">{{ $myclass->total() }} Active</span>
        </h5>
        @can('Create my-class')
        <button type="button" class="btn-search" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class="ri-add-line"></i> Create Class Setting
        </button>
        @endcan
    </div>

    <div style="overflow-x: auto;">
        <table class="cb-table" id="classesTable">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" class="cb-checkbox" id="checkAll"></th>
                    <th>#</th>
                    <th>Class</th>
                    <th>Arm</th>
                    <th>Term</th>
                    <th>Session</th>
                    <th>Students</th>
                    <th>Broadsheet</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @php $i = ($myclass->currentPage() - 1) * $myclass->perPage() @endphp
                @forelse ($myclass as $sc)
                <tr data-class-id="{{ $sc->id }}" data-schoolclassid="{{ $sc->schoolclassid }}" data-termid="{{ $sc->termid }}" data-sessionid="{{ $sc->sessionid }}">
                    <td data-label="Select">
                        <input type="checkbox" class="cb-checkbox child-checkbox" value="{{ $sc->id }}">
                    </td>
                    <td data-label="#">{{ ++$i }}</td>
                    <td data-label="Class"><strong>{{ $sc->schoolclass }}</strong></td>
                    <td data-label="Arm"><span class="cb-badge badge-current">{{ $sc->schoolarm }}</span></td>
                    <td data-label="Term">{{ $sc->term }}</td>
                    <td data-label="Session">{{ $sc->session }}</td>
                    <td data-label="Students">
                        <div class="action-buttons">
                            <a href="{{ route('viewstudent', [$sc->schoolclassid, $sc->termid, $sc->sessionid]) }}" class="btn-icon btn-primary-icon">
                                <i class="ri-group-line"></i> View Students
                            </a>
                        </div>
                    </td>
                    <td data-label="Broadsheet">
                        <div class="action-buttons">
                            <div class="cb-dropdown">
                                <button class="cb-dropdown-btn" onclick="toggleDropdown(this)">
                                    <i class="ri-file-text-line"></i> Broadsheet <i class="ri-arrow-down-s-line"></i>
                                </button>
                                <div class="cb-dropdown-menu">
                                    <div class="dropdown-header">Select Term</div>
                                    <a class="dropdown-item" href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, 1]) }}">
                                        <i class="ri-file-copy-line"></i> Term 1
                                    </a>
                                    <a class="dropdown-item" href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, 2]) }}">
                                        <i class="ri-file-copy-line"></i> Term 2
                                    </a>
                                    <a class="dropdown-item" href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, 3]) }}">
                                        <i class="ri-file-copy-line"></i> Term 3
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Actions">
                        <div class="action-buttons">
                            <div class="cb-dropdown">
                                <button class="cb-dropdown-btn" onclick="toggleDropdown(this)">
                                    <i class="ri-settings-3-line"></i> Actions <i class="ri-arrow-down-s-line"></i>
                                </button>
                                <div class="cb-dropdown-menu">
                                    <div class="dropdown-header">Student Reports</div>
                                    <a class="dropdown-item" href="{{ route('viewstudent', [$sc->schoolclassid, 1, $sc->sessionid]) }}">
                                        <i class="ri-user-line"></i> Term 1 Students
                                    </a>
                                    <a class="dropdown-item" href="{{ route('viewstudent', [$sc->schoolclassid, 2, $sc->sessionid]) }}">
                                        <i class="ri-user-line"></i> Term 2 Students
                                    </a>
                                    <a class="dropdown-item" href="{{ route('viewstudent', [$sc->schoolclassid, 3, $sc->sessionid]) }}">
                                        <i class="ri-user-line"></i> Term 3 Students
                                    </a>
                                    <div class="divider"></div>
                                    <div class="dropdown-header">Broadsheets</div>
                                    <a class="dropdown-item" href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, 1]) }}">
                                        <i class="ri-file-text-line"></i> Term 1
                                    </a>
                                    <a class="dropdown-item" href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, 2]) }}">
                                        <i class="ri-file-text-line"></i> Term 2
                                    </a>
                                    <a class="dropdown-item" href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, 3]) }}">
                                        <i class="ri-file-text-line"></i> Term 3
                                    </a>
                                    @can('Update my-class')
                                    <div class="divider"></div>
                                    <a class="dropdown-item edit-item-btn" href="javascript:void(0);" data-id="{{ $sc->id }}">
                                        <i class="ri-edit-line"></i> Edit Setting
                                    </a>
                                    @endcan
                                    @can('Delete my-class')
                                    <a class="dropdown-item text-danger remove-item-btn" href="javascript:void(0);" data-id="{{ $sc->id }}">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        <i class="ri-inbox-line" style="font-size: 48px; color: #cbd5e1;"></i>
                        <p class="text-muted mt-2 mb-0">No classes found</p>
                        <small class="text-muted">Try adjusting your filters</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="padding: 20px 24px; border-top: 1px solid var(--cb-border);">
        <div class="row align-items-center">
            <div class="col-sm">
                <div class="text-muted" style="font-size: 12px;">
                    Showing <span class="fw-semibold">{{ $myclass->count() }}</span> of <span class="fw-semibold">{{ $myclass->total() }}</span> classes
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="pagination-wrap">
                    @if($myclass->onFirstPage())
                        <span class="page-item disabled">Prev</span>
                    @else
                        <a class="page-item" href="{{ $myclass->previousPageUrl() }}">Prev</a>
                    @endif

                    @foreach ($myclass->getUrlRange(1, $myclass->lastPage()) as $page => $url)
                        @if($page == $myclass->currentPage())
                            <span class="page-item active">{{ $page }}</span>
                        @else
                            <a class="page-item" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($myclass->hasMorePages())
                        <a class="page-item" href="{{ $myclass->nextPageUrl() }}">Next</a>
                    @else
                        <span class="page-item disabled">Next</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Class History Section -->
@if($myclasshistory->count() > 0)
<div class="cb-card mt-4">
    <div class="cb-card-header">
        <h5><i class="ri-history-line" style="color:var(--cb-teal)"></i> Class History</h5>
    </div>
    <div style="overflow-x: auto;">
        <table class="cb-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Arm</th>
                    <th>Term</th>
                    <th>Session</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($myclasshistory as $index => $history)
                <tr>
                    <td data-label="#">{{ $index + 1 }}</td>
                    <td data-label="Class"><strong>{{ $history->schoolclass }}</strong></td>
                    <td data-label="Arm"><span class="cb-badge badge-past">{{ $history->schoolarm }}</span></td>
                    <td data-label="Term">{{ $history->term }}</td>
                    <td data-label="Session">{{ $history->session }}</td>
                    <td data-label="Updated">{{ $history->updated_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div></div></div>

<script>
// Toggle Dropdown
function toggleDropdown(btn) {
    event.stopPropagation();
    var dropdown = btn.closest('.cb-dropdown');
    var menu = dropdown.querySelector('.cb-dropdown-menu');

    // Close all other dropdowns
    document.querySelectorAll('.cb-dropdown-menu').forEach(function(m) {
        if (m !== menu) m.classList.remove('show');
    });

    menu.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function() {
    document.querySelectorAll('.cb-dropdown-menu').forEach(function(menu) {
        menu.classList.remove('show');
    });
});

// Select All Checkbox
var checkAll = document.getElementById('checkAll');
if (checkAll) {
    checkAll.addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.child-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = checkAll.checked;
        });
    });
}

// Filter Data
function filterData() {
    var search = document.getElementById('searchInput').value;
    var classFilter = document.getElementById('classFilter').value;
    var sessionFilter = document.getElementById('sessionFilter').value;

    var url = new URL(window.location.href);
    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');
    if (classFilter !== 'ALL') url.searchParams.set('schoolclassid', classFilter);
    else url.searchParams.delete('schoolclassid');
    if (sessionFilter !== 'ALL') url.searchParams.set('sessionid', sessionFilter);
    else url.searchParams.delete('sessionid');
    url.searchParams.set('page', '1');

    window.location.href = url.toString();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('classFilter').value = 'ALL';
    document.getElementById('sessionFilter').value = 'ALL';
    filterData();
}

// Toast notification
function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'cb-toast cb-toast-' + (type || 'success');
    toast.innerHTML = '<i class="ri-' + (type === 'success' ? 'checkbox-circle-fill' : 'error-warning-fill') + '"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}

// Calculate total students (PHP side calculation)
var totalStudents = @json($myclass->sum(function($c) {
    return \App\Models\Studentclass::where('schoolclassid', $c->schoolclassid)
        ->where('sessionid', $c->sessionid)
        ->count();
}));
document.getElementById('totalStudents').textContent = totalStudents;
document.getElementById('totalBroadsheets').textContent = {{ $myclass->count() * 3 }};

// Edit and Delete functionality
@can('Update my-class')
document.querySelectorAll('.edit-item-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        showToast('Edit feature - ID: ' + id, 'info');
        // Implement edit modal population here
    });
});
@endcan

@can('Delete my-class')
document.querySelectorAll('.remove-item-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        if (confirm('Are you sure you want to delete this class setting?')) {
            fetch('{{ url("myclass") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(function(error) {
                showToast('Error deleting class setting', 'error');
            });
        }
    });
});
@endcan
</script>

@endsection
