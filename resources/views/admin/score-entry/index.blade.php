{{-- resources/views/admin/score-entry/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* ============================================================
   ADMIN SCORE ENTRY - DASHBOARD STYLE
   Matching the School Command Centre aesthetic
   ============================================================ */
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Outfit:wght@300;400;500;600&display=swap');

:root {
    --c-bg:       #f6f7fb;
    --c-surface:  #ffffff;
    --c-border:   #eaecf4;
    --c-muted:    #94a3b8;
    --c-text:     #1e2a3a;
    --c-sub:      #4a5568;
    --c-indigo:   #4f5fff;
    --c-violet:   #7c3aed;
    --c-sky:      #0ea5e9;
    --c-teal:     #0d9488;
    --c-emerald:  #059669;
    --c-rose:     #f43f5e;
    --c-amber:    #d97706;
    --c-orange:   #ea580c;
    --c-slate:    #475569;
    --r:          14px;
    --r-sm:       8px;
    --sh:         0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    --sh-hover:   0 4px 20px rgba(0,0,0,.10);
    --tr:         .22s cubic-bezier(.4,0,.2,1);
}

.admin-score-container {
    font-family: 'Outfit', sans-serif;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: var(--r);
    padding: 28px 32px;
    margin-bottom: 24px;
    color: white;
}
.hero-title {
    font-family: 'Syne', sans-serif;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
}
.hero-subtitle {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 0;
}
.hero-actions {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-hero {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-hero:hover { background: rgba(255,255,255,0.3); color: white; transform: translateY(-2px); }
.btn-hero-primary { background: #ffc107; border-color: #ffc107; color: #1e3a5f; }
.btn-hero-primary:hover { background: #ffca2c; color: #1e3a5f; }
.btn-hero-success { background: #10b981; border-color: #10b981; color: white; }
.btn-hero-success:hover { background: #059669; color: white; }

/* Stat Cards - Matching dashboard style */
.sc {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 18px 20px;
    box-shadow: var(--sh);
    transition: all var(--tr);
    position: relative;
    overflow: hidden;
    cursor: pointer;
}
.sc::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--sc-color, #4f5fff);
    opacity: 0.04;
    transform: translate(25px, -25px);
}
.sc:hover { transform: translateY(-3px); box-shadow: var(--sh-hover); }
.sc-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.sc-label {
    font-size: 10.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--c-muted);
    margin-bottom: 0;
}
.sc-value {
    font-family: 'Syne', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--c-text);
    line-height: 1.1;
}
.sc-sub {
    font-size: 11.5px;
    color: var(--c-sub);
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 4px;
}
.sc-bar {
    height: 3px;
    border-radius: 3px;
    background: #f1f5f9;
    margin-top: 14px;
    overflow: hidden;
}
.sc-bar-fill {
    height: 100%;
    border-radius: 3px;
    animation: barIn 0.9s ease both;
}
@keyframes barIn { from { width: 0; } to { width: var(--bw); } }

/* Filter Card */
.filter-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--sh);
}
.filter-label-custom {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--c-muted);
    margin-bottom: 6px;
}

/* Tables */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.data-table thead th {
    padding: 10px 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--c-muted);
    border-bottom: 1px solid var(--c-border);
    background: #fafbfe;
}
.data-table tbody tr { transition: background var(--tr); }
.data-table tbody tr:hover { background: #f8fafc; }
.data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f8fafc;
    color: var(--c-sub);
    vertical-align: middle;
}
.data-table tbody tr:last-child td { border-bottom: none; }

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.status-badge.complete { background: #dcfce7; color: #15803d; }
.status-badge.good { background: #dbeafe; color: #1d4ed8; }
.status-badge.partial { background: #fef3c7; color: #b45309; }
.status-badge.low { background: #fee2e2; color: #dc2626; }

/* Progress Bar */
.progress-bar-custom {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}
.progress-fill.high { background: #10b981; }
.progress-fill.medium { background: #f59e0b; }
.progress-fill.low { background: #ef4444; }

/* Teacher Grid */
.teachers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
    gap: 24px;
}
.teacher-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    overflow: hidden;
    transition: all var(--tr);
}
.teacher-card:hover { transform: translateY(-4px); box-shadow: var(--sh-hover); }
.teacher-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 20px;
    border-bottom: 1px solid var(--c-border);
    display: flex;
    align-items: center;
    gap: 16px;
}
.teacher-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 22px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    flex-shrink: 0;
}
.teacher-name {
    font-weight: 700;
    color: var(--c-text);
    font-size: 18px;
    margin: 0 0 6px;
}
.teacher-stats {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: var(--c-muted);
    flex-wrap: wrap;
}
.teacher-card-body { padding: 0 20px; max-height: 540px; overflow-y: auto; }

/* Subject Items */
.subject-item {
    padding: 14px 0;
    border-bottom: 1px solid var(--c-border);
    cursor: pointer;
    transition: background 0.15s;
}
.subject-item:last-child { border-bottom: none; }
.subject-item:hover { background: #f8fafc; margin: 0 -20px; padding: 14px 20px; }
.subject-item.is-selected { background: #eff6ff !important; margin: 0 -20px; padding: 14px 20px; }
.subject-name {
    font-weight: 600;
    font-size: 15px;
    color: var(--c-text);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.subject-code {
    font-size: 11px;
    color: var(--c-muted);
    font-family: monospace;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 12px;
}
.subject-class {
    font-size: 12px;
    color: var(--c-muted);
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.badge-terminal, .badge-mock, .badge-open {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.badge-terminal { background: #dcfce7; color: #15803d; }
.badge-mock { background: #fef3c7; color: #b45309; }
.badge-open { background: #dbeafe; color: #1d4ed8; }
.btn-score-group { display: flex; gap: 10px; margin-top: 12px; }
.btn-score {
    flex: 1;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.btn-terminal-score { background: #10b981; color: #fff; }
.btn-terminal-score:hover { background: #059669; transform: translateY(-1px); }
.btn-mock-score { background: #fef3c7; color: #b45309; }
.btn-mock-score:hover { background: #fde68a; transform: translateY(-1px); }

/* Bulk Export Toolbar */
#bulkExportToolbar {
    position: sticky;
    top: 10px;
    z-index: 200;
    background: #1e3a5f;
    color: white;
    border-radius: 12px;
    padding: 14px 20px;
    display: none;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}
#bulkExportToolbar.visible { display: flex; }
.btn-toolbar {
    border: 1.5px solid rgba(255,255,255,0.4);
    color: white;
    background: transparent;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-toolbar:hover { background: rgba(255,255,255,0.15); color: white; }
.btn-toolbar.green { background: #10b981; border-color: #10b981; }
.btn-toolbar.green:hover { background: #059669; }

/* Search Bar */
.search-input-wrapper {
    position: relative;
    max-width: 350px;
}
.search-input {
    padding-left: 40px;
    border-radius: 12px;
    border: 1px solid var(--c-border);
    height: 44px;
    width: 100%;
    font-family: 'Outfit', sans-serif;
}
.search-input:focus { outline: none; border-color: var(--c-indigo); box-shadow: 0 0 0 3px rgba(79,95,255,0.1); }

/* Responsive */
@media (max-width: 768px) {
    .teachers-grid { grid-template-columns: 1fr; }
    .sc-value { font-size: 20px; }
    .hero-section { padding: 20px; }
}
</style>

<div class="main-content cmd">
<div class="page-content">
<div class="container-fluid">

    {{-- Header with breadcrumb --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="mb-0 fw-bold cmd-heading" style="color:var(--c-text);font-size:21px; font-family:'Syne',sans-serif;">Admin Score Entry</h4>
                    <span class="live-dot mt-1 d-inline-block">Manage teacher scoresheets</span>
                </div>
                <ol class="breadcrumb m-0 bg-transparent" style="font-size:12px;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:var(--c-muted);">Dashboard</a></li>
                    <li class="breadcrumb-item active">Score Entry</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Hero Section --}}
    <div class="hero-section">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <p class="mb-1 opacity-75">Admin Panel</p>
                <h1 class="hero-title"><i class="ri-admin-line me-2"></i>Score Entry Management</h1>
                <p class="hero-subtitle">View all subject teachers and their assigned classes. Enter or edit scores on behalf of teachers.</p>
                <div class="hero-actions">
                    <a href="{{ route('admin.score-entry.student-result-manager') }}" class="btn-hero btn-hero-success">
                        <i class="ri-user-settings-line"></i> Student Result Manager
                    </a>
                    <a href="{{ route('admin.score-entry.lock-management') }}" class="btn-hero btn-hero-primary">
                        <i class="ri-shield-lock-line"></i> Lock Manager
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.score-entry.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="filter-label-custom">Academic Session</label>
                <select name="sessionid" class="form-select" required>
                    <option value="">— Select Session —</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>
                            {{ $session->session }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="filter-label-custom">Term</label>
                <select name="termid" class="form-select" required>
                    <option value="">— Select Term —</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTermId == $term->id ? 'selected' : '' }}>
                            {{ $term->term }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100" style="background: var(--c-indigo); border: none;">
                    <i class="ri-filter-3-line me-1"></i>Load
                </button>
            </div>
        </form>
    </div>

    {{-- Dashboard Stats Cards --}}
    @if($teacherSubjects->isNotEmpty())
    @php
        $totalTeachers    = $teacherSubjects->groupBy('teacher_id')->count();
        $totalSubjects    = $teacherSubjects->count();
        $totalWithScores  = $teacherSubjects->where('has_terminal_scores', true)->count();
        $totalMockScores  = $teacherSubjects->where('has_mock_scores', true)->count();
        $completionRate   = $totalSubjects > 0 ? round(($totalWithScores / $totalSubjects) * 100) : 0;
        $entryCompletion  = $totalSubjects > 0 ? round($teacherSubjects->avg('entry_percentage')) : 0;
        $totalClasses     = $teacherSubjects->groupBy('schoolclass_id')->count();
        $editingDisabled  = $teacherSubjects->where('teacher_editing_enabled', false)->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #4f5fff;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Overall Completion</p>
                        <div class="sc-value">{{ $completionRate }}%</div>
                        <div class="sc-sub mt-1">
                            <span class="badge-up"><i class="ri-check-line"></i> {{ $totalWithScores }} / {{ $totalSubjects }} subjects</span>
                        </div>
                    </div>
                    <div class="sc-icon bg-indigo fg-indigo"><i class="ri-bar-chart-2-line"></i></div>
                </div>
                <div class="sc-bar mt-3">
                    <div class="sc-bar-fill" style="width: {{ $completionRate }}%; background: linear-gradient(90deg, #4f5fff, #7c3aed);"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #059669;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Teachers</p>
                        <div class="sc-value">{{ $totalTeachers }}</div>
                        <div class="sc-sub mt-1"><i class="ri-user-line"></i> Active this term</div>
                    </div>
                    <div class="sc-icon bg-emerald fg-emerald"><i class="ri-user-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: 100%; background: #059669;"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #0ea5e9;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Subjects</p>
                        <div class="sc-value">{{ $totalSubjects }}</div>
                        <div class="sc-sub mt-1"><i class="ri-book-open-line"></i> Across {{ $totalClasses }} classes</div>
                    </div>
                    <div class="sc-icon bg-sky fg-sky"><i class="ri-book-open-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: 100%; background: #0ea5e9;"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #d97706;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Entry Completion</p>
                        <div class="sc-value">{{ $entryCompletion }}%</div>
                        <div class="sc-sub mt-1"><i class="ri-database-2-line"></i> {{ number_format($dashboardStats['total_actual_entries'] ?? 0) }} / {{ number_format($dashboardStats['total_expected_entries'] ?? 0) }} entries</div>
                    </div>
                    <div class="sc-icon bg-amber fg-amber"><i class="ri-database-2-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: {{ $entryCompletion }}%; background: linear-gradient(90deg, #d97706, #ef4444);"></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #7c3aed;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Mock Scoresheets</p>
                        <div class="sc-value">{{ $totalMockScores }}</div>
                        <div class="sc-sub mt-1"><i class="ri-flask-line"></i> {{ $totalSubjects - $totalMockScores }} pending</div>
                    </div>
                    <div class="sc-icon bg-violet fg-violet"><i class="ri-flask-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: {{ $totalSubjects > 0 ? round(($totalMockScores / $totalSubjects) * 100) : 0 }}%; background: linear-gradient(90deg, #7c3aed, #c026d3);"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #f43f5e;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Editing Disabled</p>
                        <div class="sc-value">{{ $editingDisabled }}</div>
                        <div class="sc-sub mt-1"><i class="ri-lock-line"></i> Subjects locked</div>
                    </div>
                    <div class="sc-icon bg-rose fg-rose"><i class="ri-lock-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: {{ $totalSubjects > 0 ? round(($editingDisabled / $totalSubjects) * 100) : 0 }}%; background: #f43f5e;"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #0d9488;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Active Classes</p>
                        <div class="sc-value">{{ $totalClasses }}</div>
                        <div class="sc-sub mt-1"><i class="ri-group-line"></i> With subject assignments</div>
                    </div>
                    <div class="sc-icon bg-teal fg-teal"><i class="ri-group-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: 100%; background: #0d9488;"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="sc" style="--sc-color: #dc2626;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="sc-label mb-0">Pending Entry</p>
                        <div class="sc-value">{{ $totalSubjects - $totalWithScores }}</div>
                        <div class="sc-sub mt-1"><i class="ri-time-line"></i> Subjects need attention</div>
                    </div>
                    <div class="sc-icon bg-rose fg-rose"><i class="ri-time-line"></i></div>
                </div>
                <div class="sc-bar mt-3"><div class="sc-bar-fill" style="width: {{ $totalSubjects > 0 ? round((($totalSubjects - $totalWithScores) / $totalSubjects) * 100) : 0 }}%; background: #dc2626;"></div></div>
            </div>
        </div>
    </div>

    {{-- Bulk Export Toolbar --}}
    <div id="bulkExportToolbar">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            <i class="ri-checkbox-circle-line fs-5"></i>
            <span class="fw-semibold"><span id="toolbarSelectedCount">0</span> scoresheets selected</span>
        </div>
        <button type="button" class="btn-toolbar" onclick="adminBulkExport.deselectAll()"><i class="ri-close-line"></i> Clear</button>
        <button type="button" class="btn-toolbar" onclick="adminBulkExport.selectOnlyWithScores()"><i class="ri-filter-line"></i> With scores only</button>
        <button type="button" class="btn-toolbar green" id="btnBulkExport" onclick="adminBulkExport.export()"><i class="ri-download-2-line"></i> Export ZIP</button>
    </div>

    {{-- Search and Filters --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="search-input-wrapper">
            <i class="ri-search-line" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--c-muted);"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Search teacher, subject or class…">
        </div>
        <div class="d-flex align-items-center gap-3">
            <select id="statusFilter" class="form-select" style="width: auto;">
                <option value="all">All Status</option>
                <option value="complete">Complete (100%)</option>
                <option value="high">High Progress (75-99%)</option>
                <option value="partial">Partial (50-74%)</option>
                <option value="low">Low (Below 50%)</option>
            </select>
            <div class="d-flex align-items-center gap-2 px-3 py-2 bg-white rounded border">
                <input type="checkbox" id="selectAllCheckbox" onchange="adminBulkExport.toggleAll(this.checked)">
                <label for="selectAllCheckbox" class="mb-0 small">Select all visible</label>
                <span class="text-muted small ms-2" id="totalSubjectCount">{{ $teacherSubjects->count() }} scoresheets</span>
            </div>
        </div>
    </div>

    {{-- Teachers Grid --}}
    <div class="teachers-grid" id="teachersGrid">
        @foreach($teacherSubjects->groupBy('teacher_id') as $teacherId => $subjects)
            @php
                $teacherName      = $subjects->first()->teacher_name;
                $initials         = strtoupper(substr($teacherName, 0, 2));
                $teacherCompleted = $subjects->where('has_terminal_scores', true)->count();
                $teacherTotal     = $subjects->count();
                $teacherPercent   = $teacherTotal > 0 ? round(($teacherCompleted / $teacherTotal) * 100) : 0;
                $teacherEntryAvg  = round($subjects->avg('entry_percentage'));
            @endphp
            <div class="teacher-card" data-status="{{ $teacherPercent == 100 ? 'complete' : ($teacherPercent >= 75 ? 'high' : ($teacherPercent >= 50 ? 'partial' : 'low')) }}">
                <div class="teacher-card-header">
                    <div class="teacher-avatar">{{ $initials }}</div>
                    <div>
                        <div class="teacher-name">{{ $teacherName }}</div>
                        <div class="teacher-stats">
                            <span><i class="ri-book-line"></i> {{ $teacherTotal }} subjects</span>
                            <span><i class="ri-check-line"></i> {{ $teacherCompleted }} entered ({{ $teacherPercent }}%)</span>
                            <span><i class="ri-database-line"></i> {{ $teacherEntryAvg }}% entries</span>
                        </div>
                        <div class="progress-bar-custom mt-2" style="width: 150px;">
                            <div class="progress-fill {{ $teacherPercent >= 75 ? 'high' : ($teacherPercent >= 50 ? 'medium' : 'low') }}" style="width: {{ $teacherPercent }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="teacher-card-body">
                    @foreach($subjects as $subject)
                    <div class="subject-item"
                         data-subjectclass-id="{{ $subject->subjectclass_id }}"
                         data-teacher-id="{{ $subject->teacher_id }}"
                         data-schoolclass-id="{{ $subject->schoolclass_id }}"
                         data-term-id="{{ $subject->termid }}"
                         data-session-id="{{ $subject->sessionid }}"
                         data-has-scores="{{ $subject->has_terminal_scores ? '1' : '0' }}"
                         onclick="adminBulkExport.toggleRow(this)">
                        <div class="d-flex gap-3">
                            <input type="checkbox" class="subject-checkbox bulk-export-check mt-1" onclick="event.stopPropagation(); adminBulkExport.onCheckboxClick(this)">
                            <div class="flex-grow-1">
                                <div class="subject-name">
                                    {{ $subject->subject_name }}
                                    <span class="subject-code">{{ $subject->subject_code }}</span>
                                </div>
                                <div class="subject-class">
                                    <i class="ri-group-line"></i> {{ $subject->class_name }}
                                    · {{ $subject->student_count }} students
                                </div>
                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                    @if($subject->has_terminal_scores)
                                        <span class="badge-terminal"><i class="ri-check-line"></i> Terminal ({{ $subject->terminal_entries_count }}/{{ $subject->student_count }})</span>
                                    @else
                                        <span class="badge-open"><i class="ri-add-line"></i> No Terminal Scores</span>
                                    @endif
                                    @if($subject->has_mock_scores)
                                        <span class="badge-mock"><i class="ri-flask-line"></i> Mock ({{ $subject->mock_entries_count }}/{{ $subject->student_count }})</span>
                                    @endif
                                </div>
                                <div class="btn-score-group" onclick="event.stopPropagation()">
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'terminal']) }}" class="btn-score btn-terminal-score">
                                        <i class="ri-file-edit-line"></i> Terminal
                                    </a>
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'mock']) }}" class="btn-score btn-mock-score">
                                        <i class="ri-flask-line"></i> Mock
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @elseif($selectedTermId && $selectedSessionId)
        <div class="text-center py-5 bg-white rounded-3 border">
            <i class="ri-user-unfollow-line fs-1 text-muted"></i>
            <h5 class="mt-3">No Teacher Assignments Found</h5>
            <p class="text-muted">No teachers have been assigned to subjects for the selected term and session.</p>
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-3 border">
            <i class="ri-filter-line fs-1 text-muted"></i>
            <h5 class="mt-3">Select Session and Term</h5>
            <p class="text-muted">Please select an academic session and term to view teacher assignments.</p>
        </div>
    @endif

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ===================================================
   BULK EXPORT MODULE
   =================================================== */
const adminBulkExport = (() => {
    const EXPORT_URL = '{{ route("admin.score-entry.bulk-export") }}';
    const CSRF = '{{ csrf_token() }}';

    function allCheckboxes() { return [...document.querySelectorAll('.bulk-export-check')]; }
    function visibleCheckboxes() { return allCheckboxes().filter(cb => { const card = cb.closest('.teacher-card'); return card && card.style.display !== 'none'; }); }
    function checkedBoxes() { return allCheckboxes().filter(cb => cb.checked); }

    function updateToolbar() {
        const checked = checkedBoxes();
        const n = checked.length;
        const toolbar = document.getElementById('bulkExportToolbar');
        const badge = document.getElementById('toolbarSelectedCount');
        const selAll = document.getElementById('selectAllCheckbox');
        if (toolbar) toolbar.classList.toggle('visible', n > 0);
        if (badge) badge.textContent = n;
        if (selAll) {
            const visible = visibleCheckboxes();
            const visChecked = visible.filter(cb => cb.checked).length;
            selAll.checked = visible.length > 0 && visChecked === visible.length;
            selAll.indeterminate = visChecked > 0 && visChecked < visible.length;
        }
    }

    function toggleRow(row) { const cb = row.querySelector('.bulk-export-check'); if (cb) { cb.checked = !cb.checked; row.classList.toggle('is-selected', cb.checked); updateToolbar(); } }
    function onCheckboxClick(cb) { cb.closest('.subject-item').classList.toggle('is-selected', cb.checked); updateToolbar(); }
    function toggleAll(checked) { visibleCheckboxes().forEach(cb => { cb.checked = checked; cb.closest('.subject-item').classList.toggle('is-selected', checked); }); updateToolbar(); }
    function deselectAll() { allCheckboxes().forEach(cb => { cb.checked = false; cb.closest('.subject-item').classList.remove('is-selected'); }); updateToolbar(); }
    function selectOnlyWithScores() { allCheckboxes().forEach(cb => { const row = cb.closest('.subject-item'); if (row && row.dataset.hasScores !== '1') { cb.checked = false; row.classList.remove('is-selected'); } }); updateToolbar(); }

    function export_() {
        const selected = checkedBoxes();
        if (selected.length === 0) { Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one scoresheet to export.', confirmButtonColor: '#2563eb' }); return; }
        const btn = document.getElementById('btnBulkExport');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Preparing…'; }
        const subjects = selected.map(cb => { const row = cb.closest('.subject-item'); return { subjectclass_id: row.dataset.subjectclassId, teacher_id: row.dataset.teacherId, schoolclass_id: row.dataset.schoolclassId, term_id: row.dataset.termId, session_id: row.dataset.sessionId }; });
        const form = document.createElement('form'); form.method = 'POST'; form.action = EXPORT_URL; form.style.display = 'none';
        const addInput = (name, value) => { const el = document.createElement('input'); el.type = 'hidden'; el.name = name; el.value = value; form.appendChild(el); };
        addInput('_token', CSRF);
        subjects.forEach((s, i) => { Object.entries(s).forEach(([key, val]) => addInput(`subjects[${i}][${key}]`, val)); });
        document.body.appendChild(form); form.submit();
        setTimeout(() => { if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ri-download-2-line"></i> Export ZIP'; } document.body.removeChild(form); }, 4000);
    }

    return { toggleRow, onCheckboxClick, toggleAll, deselectAll, selectOnlyWithScores, export: export_ };
})();

/* ===================================================
   SEARCH AND FILTER
   =================================================== */
(function() {
    const input = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    if (!input) return;
    function filterCards() {
        const searchTerm = input.value.toLowerCase().trim();
        const statusValue = statusFilter ? statusFilter.value : 'all';
        let visible = 0;
        document.querySelectorAll('.teacher-card').forEach(card => {
            const text = (card.innerText.toLowerCase());
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || card.dataset.status === statusValue;
            const show = matchesSearch && matchesStatus;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const total = document.querySelectorAll('.teacher-card').length;
        const countSpan = document.getElementById('totalSubjectCount');
        if (countSpan) countSpan.textContent = (searchTerm || statusValue !== 'all') ? `${visible} of ${total} visible` : '{{ $teacherSubjects->count() }} scoresheets';
    }
    input.addEventListener('input', filterCards);
    if (statusFilter) statusFilter.addEventListener('change', filterCards);
})();
</script>
@endsection
