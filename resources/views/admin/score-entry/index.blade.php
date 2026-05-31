{{-- resources/views/admin/score-entry/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* =========================================================
   HERO
   ========================================================= */
.admin-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: white;
}
.admin-hero-actions {
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
    font-size: 14px;
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

/* =========================================================
   FILTER CARD
   ========================================================= */
.filter-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* =========================================================
   STATS CARDS
   ========================================================= */
.stats-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}
.stat-card-enhanced {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.stat-card-enhanced:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
.stat-card-header {
    padding: 16px 20px 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.stat-card-header h3 {
    font-size: 14px;
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
.stat-card-body { padding: 8px 20px 20px 20px; }
.stat-main-value { font-size: 36px; font-weight: 800; color: #1e293b; line-height: 1.2; margin-bottom: 4px; }
.stat-trend { font-size: 12px; display: flex; align-items: center; gap: 6px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; }
.stat-footer { background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }

/* =========================================================
   BULK EXPORT TOOLBAR (sticky)
   ========================================================= */
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
    transition: all 0.2s;
}
#bulkExportToolbar.visible { display: flex; }
.toolbar-count-text {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.toolbar-count-badge {
    background: rgba(255,255,255,0.22);
    border-radius: 20px;
    padding: 2px 14px;
    font-size: 14px;
    font-weight: 700;
}
.btn-toolbar {
    border: 1.5px solid rgba(255,255,255,0.4);
    color: white;
    background: transparent;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s;
    text-decoration: none;
}
.btn-toolbar:hover { background: rgba(255,255,255,0.15); color: white; }
.btn-toolbar.green { background: #10b981; border-color: #10b981; }
.btn-toolbar.green:hover { background: #059669; }
.btn-toolbar.muted { font-size: 12px; opacity: 0.75; }
.btn-toolbar:disabled { opacity: 0.5; cursor: not-allowed; }

/* =========================================================
   SELECT-ALL BAR
   ========================================================= */
.select-all-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
}
.select-all-bar label { font-size: 13px; color: #64748b; cursor: pointer; margin: 0; }
.select-all-bar .page-count { font-size: 12px; color: #94a3b8; }

/* =========================================================
   SEARCH BAR
   ========================================================= */
.search-bar { margin-bottom: 24px; }
.search-input-wrapper { position: relative; max-width: 350px; }
.search-input-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-input {
    padding-left: 40px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    height: 44px;
    width: 100%;
}
.search-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

/* =========================================================
   TEACHERS GRID
   ========================================================= */
.teachers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
    gap: 24px;
}
.teacher-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
}
.teacher-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -12px rgba(0,0,0,0.15); }
.teacher-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
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
.teacher-name { font-weight: 700; color: #1e293b; font-size: 18px; margin: 0 0 6px; }
.teacher-stats { display: flex; gap: 16px; font-size: 12px; color: #64748b; flex-wrap: wrap; }
.teacher-card-body { padding: 0 20px; max-height: 540px; overflow-y: auto; }

/* =========================================================
   SUBJECT ITEMS
   ========================================================= */
.subject-item {
    padding: 14px 0;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    transition: background 0.15s;
    user-select: none;
}
.subject-item:last-child { border-bottom: none; }
.subject-item:hover { background: #f8fafc; margin: 0 -20px; padding: 14px 20px; }
.subject-item.is-selected { background: #eff6ff !important; margin: 0 -20px; padding: 14px 20px; }

.subject-check-wrap { display: flex; align-items: flex-start; gap: 12px; }
.subject-checkbox {
    margin-top: 3px;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    accent-color: #2563eb;
    cursor: pointer;
}
.subject-name {
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.subject-code {
    font-size: 11px;
    color: #64748b;
    font-family: monospace;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 12px;
}
.subject-class { font-size: 12px; color: #64748b; margin-top: 6px; display: flex; align-items: center; gap: 6px; }
.subject-badges { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
.badge-terminal, .badge-mock, .badge-locked, .badge-open {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.badge-terminal { background: #dcfce7; color: #15803d; }
.badge-mock     { background: #fef3c7; color: #b45309; }
.badge-locked   { background: #fee2e2; color: #dc2626; }
.badge-open     { background: #dbeafe; color: #1d4ed8; }
.badge-progress { background: #e0e7ff; color: #4338ca; }

.lock-status-icon { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 20px; background: #f3f4f6; }
.lock-status-icon.locked { background: #fee2e2; color: #dc2626; }

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

/* Entry progress bar */
.entry-progress {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.progress-bar-custom {
    flex: 1;
    height: 4px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}
.progress-fill.high { background: #10b981; }
.progress-fill.medium { background: #f59e0b; }
.progress-fill.low { background: #ef4444; }

/* =========================================================
   TEACHER PERFORMANCE TABLE
   ========================================================= */
.teacher-performance-table {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 24px;
}
.table-header {
    background: #f8fafc;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}
.table-header h5 {
    margin: 0;
    font-weight: 600;
    color: #1e293b;
}
.table-responsive-custom {
    overflow-x: auto;
}
.teacher-table {
    width: 100%;
    border-collapse: collapse;
}
.teacher-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    background: #fafbfe;
    border-bottom: 1px solid #e2e8f0;
}
.teacher-table td {
    padding: 14px 16px;
    font-size: 13px;
    border-bottom: 1px solid #f0f2f5;
    vertical-align: middle;
}
.teacher-table tr:hover {
    background: #f8fafc;
}
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

/* =========================================================
   CLASS PERFORMANCE TABLE
   ========================================================= */
.class-performance-table {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 24px;
}
.class-table {
    width: 100%;
    border-collapse: collapse;
}
.class-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    background: #fafbfe;
    border-bottom: 1px solid #e2e8f0;
}
.class-table td {
    padding: 14px 16px;
    font-size: 13px;
    border-bottom: 1px solid #f0f2f5;
    vertical-align: middle;
}

/* =========================================================
   EMPTY STATE
   ========================================================= */
.empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; }
.empty-state i { font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block; }
.empty-state h5 { color: #64748b; margin-bottom: 8px; }

/* =========================================================
   SCROLLBAR
   ========================================================= */
.teacher-card-body::-webkit-scrollbar { width: 6px; }
.teacher-card-body::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
.teacher-card-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
.teacher-card-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 768px) {
    .teachers-grid { grid-template-columns: 1fr; }
    .stats-dashboard { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .stat-main-value { font-size: 28px; }
    .admin-hero { padding: 20px; }
    .select-all-bar { flex-wrap: wrap; }
    .teacher-table th, .teacher-table td,
    .class-table th, .class-table td {
        padding: 8px 12px;
        font-size: 11px;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- ===================================================
         HERO
         =================================================== --}}
    <div class="admin-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="mb-2"><i class="ri-admin-line me-2"></i>Admin Score Entry</h1>
                <p class="mb-0">View all subject teachers and their assigned classes. Enter or edit scores on behalf of teachers.</p>
                <p class="mt-2 mb-0"><i class="ri-shield-check-line me-1"></i> <strong>Lock Management:</strong> Lock individual scoresheets, apply global locks, or disable teacher editing entirely.</p>
                <p class="mt-1 mb-0"><i class="ri-user-settings-line me-1"></i> <strong>Student Result Manager:</strong> Enter results per student across all subjects in one place.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="{{ route('admin.score-entry.student-result-manager') }}" class="btn-hero btn-hero-success">
                    <i class="ri-user-settings-line"></i> Student Result Manager
                </a>
                <a href="{{ route('admin.score-entry.lock-management') }}" class="btn-hero btn-hero-primary">
                    <i class="ri-shield-lock-line"></i> Lock Manager
                </a>
            </div>
        </div>
    </div>

    {{-- ===================================================
         FILTER FORM
         =================================================== --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.score-entry.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Academic Session</label>
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
                <label class="form-label fw-semibold">Term</label>
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
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ri-filter-3-line me-1"></i>Load
                </button>
            </div>
        </form>
    </div>

    {{-- ===================================================
         MAIN CONTENT — only shown when subjects are loaded
         =================================================== --}}
    @if($teacherSubjects->isNotEmpty())
    @php
        $totalTeachers    = $teacherSubjects->groupBy('teacher_id')->count();
        $totalSubjects    = $teacherSubjects->count();
        $totalWithScores  = $teacherSubjects->where('has_terminal_scores', true)->count();
        $totalMockScores  = $teacherSubjects->where('has_mock_scores', true)->count();
        $totalLocked      = $teacherSubjects->where('teacher_editing_enabled', false)->count();
        $completionRate   = $totalSubjects > 0 ? round(($totalWithScores / $totalSubjects) * 100) : 0;
        $entryCompletion  = $teacherSubjects->sum('entry_percentage') / $totalSubjects;
    @endphp

    {{-- ===================================================
         ENHANCED DASHBOARD STATS SECTION
         =================================================== --}}
    <div class="row g-3 mb-4">
        {{-- Overall Progress Card --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-white mb-2"><i class="ri-bar-chart-2-line me-2"></i>Score Entry Dashboard</h4>
                            <p class="text-white-50 mb-0">
                                {{ $teacherSubjects->first()->term_name ?? 'N/A' }} ·
                                {{ $teacherSubjects->first()->session_name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="display-4 text-white fw-bold">{{ $dashboardStats['completion_rate'] ?? $completionRate }}%</div>
                            <div class="text-white-50">Overall Completion Rate</div>
                        </div>
                    </div>
                    <div class="row mt-4 g-3">
                        <div class="col-md-3">
                            <div class="bg-white bg-opacity-20 rounded p-3 text-center">
                                <div class="text-white-50 small">Teachers</div>
                                <div class="text-white h3 mb-0">{{ $dashboardStats['total_teachers'] ?? $totalTeachers }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white bg-opacity-20 rounded p-3 text-center">
                                <div class="text-white-50 small">Subjects</div>
                                <div class="text-white h3 mb-0">{{ $dashboardStats['total_subjects'] ?? $totalSubjects }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white bg-opacity-20 rounded p-3 text-center">
                                <div class="text-white-50 small">Classes</div>
                                <div class="text-white h3 mb-0">{{ $dashboardStats['total_classes'] ?? $teacherSubjects->groupBy('schoolclass_id')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white bg-opacity-20 rounded p-3 text-center">
                                <div class="text-white-50 small">Entry Completion</div>
                                <div class="text-white h3 mb-0">{{ round($entryCompletion) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bars --}}
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0"><i class="ri-file-list-line me-2 text-primary"></i>Scoresheet Progress</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Terminal Scoresheets</span>
                            <span><strong>{{ $dashboardStats['completed_scoresheets'] ?? $totalWithScores }}</strong> / {{ $dashboardStats['total_subjects'] ?? $totalSubjects }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $dashboardStats['completion_rate'] ?? $completionRate }}%"></div>
                        </div>
                        <small class="text-muted">{{ $dashboardStats['completion_rate'] ?? $completionRate }}% Complete</small>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Mock Scoresheets</span>
                            <span><strong>{{ $dashboardStats['mock_completed'] ?? $totalMockScores }}</strong> / {{ $dashboardStats['total_subjects'] ?? $totalSubjects }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: {{ $dashboardStats['total_subjects'] > 0 ? round((($dashboardStats['mock_completed'] ?? $totalMockScores) / ($dashboardStats['total_subjects'] ?? $totalSubjects)) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Student Entry Completion</span>
                            <span><strong>{{ number_format($dashboardStats['total_actual_entries'] ?? 0) }}</strong> / {{ number_format($dashboardStats['total_expected_entries'] ?? 0) }} entries</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: {{ $dashboardStats['entry_completion_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0"><i class="ri-pie-chart-line me-2 text-primary"></i>At a Glance</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <i class="ri-checkbox-circle-line text-success fs-2"></i>
                                <div class="mt-2">
                                    <span class="h4 mb-0">{{ $dashboardStats['completed_scoresheets'] ?? $totalWithScores }}</span>
                                    <span class="text-muted">/ {{ $dashboardStats['total_subjects'] ?? $totalSubjects }}</span>
                                </div>
                                <div class="small text-muted">Completed Terminal</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <i class="ri-time-line text-warning fs-2"></i>
                                <div class="mt-2">
                                    <span class="h4 mb-0">{{ ($dashboardStats['total_subjects'] ?? $totalSubjects) - ($dashboardStats['completed_scoresheets'] ?? $totalWithScores) }}</span>
                                    <span class="text-muted">/ {{ $dashboardStats['total_subjects'] ?? $totalSubjects }}</span>
                                </div>
                                <div class="small text-muted">Pending Terminal</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <i class="ri-flask-line text-info fs-2"></i>
                                <div class="mt-2">
                                    <span class="h4 mb-0">{{ $dashboardStats['mock_completed'] ?? $totalMockScores }}</span>
                                    <span class="text-muted">/ {{ $dashboardStats['total_subjects'] ?? $totalSubjects }}</span>
                                </div>
                                <div class="small text-muted">Mock Completed</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <i class="ri-lock-line text-danger fs-2"></i>
                                <div class="mt-2">
                                    <span class="h4 mb-0">{{ $totalLocked }}</span>
                                    <span class="text-muted">/ {{ $totalSubjects }}</span>
                                </div>
                                <div class="small text-muted">Editing Disabled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Teacher Performance Table --}}
    @if(!empty($dashboardStats['teacher_stats']))
    <div class="teacher-performance-table">
        <div class="table-header">
            <h5><i class="ri-user-star-line me-2 text-primary"></i>Teacher Performance Overview</h5>
            <small class="text-muted">Scoresheet completion and entry progress by teacher</small>
        </div>
        <div class="table-responsive-custom">
            <table class="teacher-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Teacher</th>
                        <th>Classes</th>
                        <th>Subjects</th>
                        <th>Terminal</th>
                        <th>Mock</th>
                        <th>Entry Progress</th>
                        <th>Completion</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dashboardStats['teacher_stats'] as $index => $teacher)
                    @php
                        $entryPercent = $teacher['expected_entries'] > 0 ? round(($teacher['actual_entries'] / $teacher['expected_entries']) * 100) : 0;
                        $statusClass = $teacher['completion_rate'] == 100 ? 'complete' : ($teacher['completion_rate'] >= 75 ? 'good' : ($teacher['completion_rate'] >= 50 ? 'partial' : 'low'));
                        $statusText = $teacher['completion_rate'] == 100 ? 'Complete' : ($teacher['completion_rate'] >= 75 ? 'Good Progress' : ($teacher['completion_rate'] >= 50 ? 'Partial' : 'Low Progress'));
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $teacher['teacher_name'] }}</strong>
                            <br>
                            <small class="text-muted">ID: {{ $teacher['teacher_id'] }}</small>
                        </td>
                        <td>
                            @foreach(array_slice($teacher['classes'], 0, 2) as $class)
                                <span class="badge bg-light text-dark me-1">{{ $class }}</span>
                            @endforeach
                            @if(count($teacher['classes']) > 2)
                                <span class="badge bg-light text-dark">+{{ count($teacher['classes']) - 2 }}</span>
                            @endif
                        </td>
                        <td>{{ $teacher['subjects_count'] }}</td>
                        <td class="text-success">{{ $teacher['completed_terminal'] }} / {{ $teacher['subjects_count'] }}</td>
                        <td class="text-warning">{{ $teacher['completed_mock'] }} / {{ $teacher['subjects_count'] }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; width: 100px;">
                                    <div class="progress-bar bg-info" style="width: {{ $entryPercent }}%"></div>
                                </div>
                                <small>{{ number_format($teacher['actual_entries']) }}/{{ number_format($teacher['expected_entries']) }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                    <div class="progress-bar bg-success" style="width: {{ $teacher['completion_rate'] }}%"></div>
                                </div>
                                <span class="fw-bold">{{ $teacher['completion_rate'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                <i class="ri-{{ $teacher['completion_rate'] == 100 ? 'check-line' : ($teacher['completion_rate'] >= 75 ? 'arrow-up-line' : ($teacher['completion_rate'] >= 50 ? 'time-line' : 'alert-line')) }}"></i>
                                {{ $statusText }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Class Performance Table --}}
    @if(!empty($dashboardStats['class_stats']))
    <div class="class-performance-table">
        <div class="table-header">
            <h5><i class="ri-group-line me-2 text-primary"></i>Class Performance Overview</h5>
            <small class="text-muted">Scoresheet completion by class</small>
        </div>
        <div class="table-responsive-custom">
            <table class="class-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Class</th>
                        <th>Students</th>
                        <th>Subjects</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>Completion Rate</th>
                        <th>Entry Rate</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dashboardStats['class_stats'] as $index => $class)
                    @php
                        $statusClass = $class['completion_rate'] == 100 ? 'complete' : ($class['completion_rate'] >= 75 ? 'good' : ($class['completion_rate'] >= 50 ? 'partial' : 'low'));
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $class['class_name'] }}</strong></td>
                        <td>{{ number_format($class['student_count']) }}</td>
                        <td>{{ $class['total_subjects'] }}</td>
                        <td class="text-success">{{ $class['completed_subjects'] }}</td>
                        <td class="text-warning">{{ $class['pending_subjects'] }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; width: 100px;">
                                    <div class="progress-bar bg-{{ $class['completion_rate'] >= 75 ? 'success' : ($class['completion_rate'] >= 50 ? 'warning' : 'danger') }}"
                                         style="width: {{ $class['completion_rate'] }}%">
                                    </div>
                                </div>
                                <span class="fw-bold">{{ $class['completion_rate'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; width: 80px;">
                                    <div class="progress-bar bg-info" style="width: {{ $class['entry_completion_rate'] ?? 0 }}%"></div>
                                </div>
                                <span class="fw-bold">{{ $class['entry_completion_rate'] ?? 0 }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                <i class="ri-{{ $class['completion_rate'] == 100 ? 'check-line' : ($class['completion_rate'] >= 75 ? 'arrow-up-line' : ($class['completion_rate'] >= 50 ? 'time-line' : 'alert-line')) }}"></i>
                                {{ $class['completion_rate'] == 100 ? 'Complete' : ($class['completion_rate'] >= 75 ? 'Good' : ($class['completion_rate'] >= 50 ? 'Partial' : 'Poor')) }}
                            </span>
                                                </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="showClassDetails({{ $class['class_id'] }}, {{ json_encode($class) }})">
                                <i class="ri-eye-line"></i> View Details
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================================================
         STICKY BULK EXPORT TOOLBAR
         =================================================== --}}
    <div id="bulkExportToolbar">
        <div class="toolbar-count-text">
            <i class="ri-checkbox-circle-line" style="font-size:20px;"></i>
            <span class="toolbar-count-badge" id="toolbarSelectedCount">0</span>
            <span id="toolbarLabel">scoresheets selected</span>
        </div>

        <button type="button" class="btn-toolbar muted" onclick="adminBulkExport.deselectAll()">
            <i class="ri-close-line"></i> Clear selection
        </button>

        <button type="button"
                class="btn-toolbar"
                style="background:rgba(255,255,255,0.15);"
                onclick="adminBulkExport.selectOnlyWithScores()"
                title="Keep only scoresheets that have terminal scores">
            <i class="ri-filter-line"></i> With scores only
        </button>

        <button type="button" class="btn-toolbar green" id="btnBulkExport" onclick="adminBulkExport.export()">
            <i class="ri-download-2-line"></i> Export ZIP (Selected)
        </button>

        <button type="button" class="btn-toolbar" onclick="adminBulkExport.exportAllWithScores()" style="background:#2563eb;">
            <i class="ri-download-cloud-line"></i> Export All With Scores
        </button>
    </div>

    {{-- ===================================================
         SEARCH + SELECT-ALL BAR
         =================================================== --}}
    <div class="search-bar">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="search-input-wrapper">
                <i class="ri-search-line"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search teacher, subject or class…">
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- Filter by status dropdown --}}
                <select id="statusFilter" class="form-select" style="width: auto;">
                    <option value="all">All Status</option>
                    <option value="complete">Complete (100%)</option>
                    <option value="high">High Progress (75-99%)</option>
                    <option value="partial">Partial (50-74%)</option>
                    <option value="low">Low (Below 50%)</option>
                    <option value="no_scores">No Scores Entered</option>
                </select>

                {{-- Select-all control --}}
                <div class="select-all-bar">
                    <input type="checkbox" id="selectAllCheckbox"
                           onchange="adminBulkExport.toggleAll(this.checked)" />
                    <label for="selectAllCheckbox">Select all visible</label>
                    <span class="page-count ms-2" id="totalSubjectCount">
                        {{ $teacherSubjects->count() }} scoresheets
                    </span>
                </div>

                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="ri-shield-check-line me-1"></i>
                    {{ $totalTeachers }} Teachers · {{ $totalSubjects }} Subjects
                </span>
            </div>
        </div>
    </div>

    {{-- ===================================================
         TEACHERS GRID
         =================================================== --}}
    <div id="teachersGridWrapper">
        <div class="teachers-grid" id="teachersGrid">
            @foreach($teacherSubjects->groupBy('teacher_id') as $teacherId => $subjects)
                @php
                    $teacherName      = $subjects->first()->teacher_name;
                    $initials         = strtoupper(substr($teacherName, 0, 2));
                    $teacherLocked    = $subjects->where('teacher_editing_enabled', false)->count();
                    $teacherCompleted = $subjects->where('has_terminal_scores', true)->count();
                    $teacherTotal     = $subjects->count();
                    $teacherPercent   = $teacherTotal > 0 ? round(($teacherCompleted / $teacherTotal) * 100) : 0;
                    $teacherEntryPercent = $subjects->avg('entry_percentage');
                @endphp

                <div class="teacher-card"
                     data-search="{{ strtolower($teacherName) }} {{ $subjects->pluck('subject_name')->implode(' ') }} {{ $subjects->pluck('class_name')->implode(' ') }}"
                     data-status="{{ $teacherPercent == 100 ? 'complete' : ($teacherPercent >= 75 ? 'high' : ($teacherPercent >= 50 ? 'partial' : 'low')) }}"
                     style="align-self: start;">

                    {{-- Card header --}}
                    <div class="teacher-card-header">
                        <div class="teacher-avatar">{{ $initials }}</div>
                        <div class="teacher-info" style="min-width:0;">
                            <div class="teacher-name">{{ $teacherName }}</div>
                            <div class="teacher-stats">
                                <span><i class="ri-book-line"></i> {{ $teacherTotal }} subjects</span>
                                <span><i class="ri-check-line"></i> {{ $teacherCompleted }} entered</span>
                                <span><i class="ri-percent-line"></i> {{ $teacherPercent }}% complete</span>
                                @if($teacherLocked > 0)
                                    <span class="text-danger"><i class="ri-lock-line"></i> {{ $teacherLocked }} locked</span>
                                @endif
                            </div>
                            <div class="entry-progress">
                                <span style="font-size: 11px; color: #64748b;">Entry progress:</span>
                                <div class="progress-bar-custom">
                                    <div class="progress-fill {{ $teacherEntryPercent >= 75 ? 'high' : ($teacherEntryPercent >= 50 ? 'medium' : 'low') }}"
                                         style="width: {{ round($teacherEntryPercent) }}%"></div>
                                </div>
                                <span style="font-size: 11px;">{{ round($teacherEntryPercent) }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Subject rows --}}
                    <div class="teacher-card-body">
                        @foreach($subjects as $subject)
                        <div class="subject-item"
                             data-subjectclass-id="{{ $subject->subjectclass_id }}"
                             data-teacher-id="{{ $subject->teacher_id }}"
                             data-schoolclass-id="{{ $subject->schoolclass_id }}"
                             data-term-id="{{ $subject->termid }}"
                             data-session-id="{{ $subject->sessionid }}"
                             data-has-scores="{{ $subject->has_terminal_scores ? '1' : '0' }}"
                             data-entry-percent="{{ $subject->entry_percentage }}"
                             onclick="adminBulkExport.toggleRow(this)">

                            <div class="subject-check-wrap">
                                {{-- Checkbox — stopPropagation so row click doesn't double-fire --}}
                                <input type="checkbox"
                                       class="subject-checkbox bulk-export-check"
                                       onclick="event.stopPropagation(); adminBulkExport.onCheckboxClick(this)"
                                       aria-label="Select {{ $subject->subject_name }} for export" />

                                <div style="flex:1; min-width:0;">
                                    <div class="subject-name">
                                        <span>
                                            {{ $subject->subject_name }}
                                            <span class="subject-code">({{ $subject->subject_code }})</span>
                                        </span>
                                        <div class="d-flex gap-2">
                                            @if(!$subject->teacher_editing_enabled)
                                                <span class="lock-status-icon locked">
                                                    <i class="ri-lock-line"></i> Edit Disabled
                                                </span>
                                            @endif
                                            @if($subject->entry_percentage < 100)
                                                <span class="badge-progress">
                                                    <i class="ri-time-line"></i> {{ $subject->entry_percentage }}% entries
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="subject-class">
                                        <i class="ri-group-line"></i> {{ $subject->class_name }}
                                        @if($subject->class_categories)
                                            · <span class="text-muted">{{ $subject->class_categories }}</span>
                                        @endif
                                        · <span class="text-muted">{{ $subject->student_count }} students</span>
                                    </div>

                                    <div class="subject-badges">
                                        @if($subject->has_terminal_scores)
                                            <span class="badge-terminal"><i class="ri-check-line"></i> Terminal ({{ $subject->terminal_entries_count }}/{{ $subject->student_count }} entries)</span>
                                        @else
                                            <span class="badge-open"><i class="ri-add-line"></i> No Scores - {{ $subject->student_count }} students pending</span>
                                        @endif
                                        @if($subject->has_mock_scores)
                                            <span class="badge-mock"><i class="ri-flask-line"></i> Mock ({{ $subject->mock_entries_count }}/{{ $subject->student_count }})</span>
                                        @endif
                                    </div>

                                    {{-- Entry progress bar for this subject --}}
                                    @if($subject->student_count > 0)
                                    <div class="entry-progress mt-2">
                                        <span style="font-size: 10px; color: #64748b;">Entry completion:</span>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill {{ $subject->entry_percentage >= 75 ? 'high' : ($subject->entry_percentage >= 50 ? 'medium' : 'low') }}"
                                                 style="width: {{ $subject->entry_percentage }}%"></div>
                                        </div>
                                        <span style="font-size: 10px; font-weight: 500;">
                                            {{ $subject->terminal_entries_count }}/{{ $subject->student_count }}
                                            ({{ $subject->entry_percentage }}%)
                                        </span>
                                    </div>
                                    @endif

                                    {{-- Action buttons — stop propagation so clicks don't select the row --}}
                                    <div class="btn-score-group" onclick="event.stopPropagation()">
                                        <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'terminal']) }}"
                                           class="btn-score btn-terminal-score">
                                            <i class="ri-file-edit-line"></i> Terminal
                                            @if(!$subject->has_terminal_scores)
                                                <span class="badge bg-white text-dark ms-1">New</span>
                                            @endif
                                        </a>
                                        <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'mock']) }}"
                                           class="btn-score btn-mock-score">
                                            <i class="ri-flask-line"></i> Mock
                                            @if(!$subject->has_mock_scores)
                                                <span class="badge bg-white text-dark ms-1">New</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>{{-- /.teacher-card-body --}}
                </div>{{-- /.teacher-card --}}
            @endforeach
        </div>{{-- /.teachers-grid --}}
    </div>{{-- /#teachersGridWrapper --}}

    @elseif($selectedTermId && $selectedSessionId)
        <div class="empty-state">
            <i class="ri-user-unfollow-line"></i>
            <h5>No Teacher Assignments Found</h5>
            <p class="text-muted">No teachers have been assigned to subjects for the selected term and session.</p>
            <div class="mt-3">
                <a href="{{ route('admin.score-entry.student-result-manager') }}" class="btn btn-success">
                    <i class="ri-user-settings-line"></i> Go to Student Result Manager
                </a>
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="ri-filter-line"></i>
            <h5>Select Session and Term</h5>
            <p class="text-muted">Please select an academic session and term to view teacher assignments.</p>
        </div>
    @endif

</div>{{-- /.container-fluid --}}
</div>{{-- /.page-content --}}
</div>{{-- /.main-content --}}

{{-- ===================================================
     SweetAlert2 for modals
     =================================================== --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- ===================================================
     JAVASCRIPT
     =================================================== --}}
<script>
/* -------------------------------------------------------
   Bulk Export Module
   ------------------------------------------------------- */
const adminBulkExport = (() => {
    const EXPORT_URL = '{{ route("admin.score-entry.bulk-export") }}';
    const CSRF       = '{{ csrf_token() }}';

    /* ------ helpers ------ */
    function allCheckboxes() {
        return [...document.querySelectorAll('.bulk-export-check')];
    }
    function visibleCheckboxes() {
        return allCheckboxes().filter(cb => {
            const card = cb.closest('.teacher-card');
            return card && card.style.display !== 'none';
        });
    }
    function checkedBoxes() {
        return allCheckboxes().filter(cb => cb.checked);
    }

    /* ------ toolbar sync ------ */
    function updateToolbar() {
        const checked = checkedBoxes();
        const n       = checked.length;
        const visible = visibleCheckboxes();
        const toolbar = document.getElementById('bulkExportToolbar');
        const badge   = document.getElementById('toolbarSelectedCount');
        const label   = document.getElementById('toolbarLabel');
        const selAll  = document.getElementById('selectAllCheckbox');

        if (toolbar) {
            toolbar.classList.toggle('visible', n > 0);
        }
        if (badge) badge.textContent = n;
        if (label) label.textContent = n === 1 ? 'scoresheet selected' : 'scoresheets selected';

        const visChecked = visible.filter(cb => cb.checked).length;
        if (selAll) {
            selAll.checked       = visible.length > 0 && visChecked === visible.length;
            selAll.indeterminate = visChecked > 0 && visChecked < visible.length;
        }
    }

    /* ------ public API ------ */
    function toggleRow(row) {
        const cb = row.querySelector('.bulk-export-check');
        if (!cb) return;
        cb.checked = !cb.checked;
        row.classList.toggle('is-selected', cb.checked);
        updateToolbar();
    }

    function onCheckboxClick(cb) {
        cb.closest('.subject-item').classList.toggle('is-selected', cb.checked);
        updateToolbar();
    }

    function toggleAll(checked) {
        visibleCheckboxes().forEach(cb => {
            cb.checked = checked;
            cb.closest('.subject-item').classList.toggle('is-selected', checked);
        });
        updateToolbar();
    }

    function deselectAll() {
        allCheckboxes().forEach(cb => {
            cb.checked = false;
            cb.closest('.subject-item').classList.remove('is-selected');
        });
        const selAll = document.getElementById('selectAllCheckbox');
        if (selAll) {
            selAll.checked       = false;
            selAll.indeterminate = false;
        }
        updateToolbar();
    }

    function selectOnlyWithScores() {
        allCheckboxes().forEach(cb => {
            const row = cb.closest('.subject-item');
            if (row && row.dataset.hasScores !== '1') {
                cb.checked = false;
                row.classList.remove('is-selected');
            }
        });
        updateToolbar();
    }

    function export_() {
        const selected = checkedBoxes();

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one scoresheet to export.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        const btn = document.getElementById('btnBulkExport');
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Preparing…';
        }

        const subjects = selected.map(cb => {
            const row = cb.closest('.subject-item');
            return {
                subjectclass_id : row.dataset.subjectclassId,
                teacher_id      : row.dataset.teacherId,
                schoolclass_id  : row.dataset.schoolclassId,
                term_id         : row.dataset.termId,
                session_id      : row.dataset.sessionId,
            };
        });

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = EXPORT_URL;
        form.style.display = 'none';

        const addInput = (name, value) => {
            const el  = document.createElement('input');
            el.type   = 'hidden';
            el.name   = name;
            el.value  = value;
            form.appendChild(el);
        };

        addInput('_token', CSRF);

        subjects.forEach((s, i) => {
            Object.entries(s).forEach(([key, val]) => {
                addInput(`subjects[${i}][${key}]`, val);
            });
        });

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            if (btn) {
                btn.disabled  = false;
                btn.innerHTML = '<i class="ri-download-2-line"></i> Export ZIP';
            }
            document.body.removeChild(form);
        }, 4000);
    }

    function exportAllWithScores() {
        // Select all visible items that have scores
        const visible = visibleCheckboxes();
        const withScores = visible.filter(cb => {
            const row = cb.closest('.subject-item');
            return row && row.dataset.hasScores === '1';
        });

        if (withScores.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Scoresheets',
                text: 'No scoresheets with scores found to export.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        // Check them
        withScores.forEach(cb => {
            cb.checked = true;
            cb.closest('.subject-item').classList.add('is-selected');
        });
        updateToolbar();

        // Export
        export_();
    }

    return {
        toggleRow,
        onCheckboxClick,
        toggleAll,
        deselectAll,
        selectOnlyWithScores,
        export: export_,
        exportAllWithScores
    };
})();

/* -------------------------------------------------------
   Search / filter
   ------------------------------------------------------- */
(function () {
    const input     = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const countSpan = document.getElementById('totalSubjectCount');
    const grid      = document.getElementById('teachersGrid');
    if (!input) return;

    function filterCards() {
        const searchTerm = input.value.toLowerCase().trim();
        const statusValue = statusFilter ? statusFilter.value : 'all';
        let visible = 0;

        document.querySelectorAll('.teacher-card').forEach(card => {
            const text = (card.dataset.search || '') + ' ' + card.innerText.toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || card.dataset.status === statusValue;
            const show = matchesSearch && matchesStatus;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        /* Update count label */
        const total = document.querySelectorAll('.teacher-card').length;
        if (countSpan) {
            countSpan.textContent = searchTerm || statusValue !== 'all'
                ? `${visible} of ${total} cards visible`
                : `{{ $teacherSubjects->count() }} scoresheets`;
        }

        /* Empty-state message */
        let emptyMsg = document.getElementById('searchEmptyMsg');
        if (visible === 0 && (searchTerm || statusValue !== 'all')) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.id        = 'searchEmptyMsg';
                emptyMsg.className = 'empty-state mt-3';
                emptyMsg.innerHTML = '<i class="ri-search-line"></i>'
                    + '<h5>No matching results</h5>'
                    + '<p class="text-muted">Try a different search term or status filter.</p>';
                grid?.parentNode?.appendChild(emptyMsg);
            }
            if (grid) grid.style.display = 'none';
        } else {
            emptyMsg?.remove();
            if (grid) grid.style.display = '';
        }

        /* Keep toolbar in sync with visibility changes */
        const selAll = document.getElementById('selectAllCheckbox');
        if (selAll) {
            const vis     = [...document.querySelectorAll('.teacher-card')]
                .filter(c => c.style.display !== 'none');
            const visCbs  = vis.flatMap(c => [...c.querySelectorAll('.bulk-export-check')]);
            const checked = visCbs.filter(cb => cb.checked).length;
            selAll.checked       = visCbs.length > 0 && checked === visCbs.length;
            selAll.indeterminate = checked > 0 && checked < visCbs.length;
        }
    }

    input.addEventListener('input', filterCards);
    if (statusFilter) {
        statusFilter.addEventListener('change', filterCards);
    }
})();

/* -------------------------------------------------------
   Show class details modal
   ------------------------------------------------------- */
function showClassDetails(classId, classData) {
    let subjectList = '';
    if (classData.subjects && classData.subjects.length > 0) {
        subjectList = '<ul class="list-group mt-2">';
        classData.subjects.forEach(sub => {
            subjectList += `<li class="list-group-item">${sub}</li>`;
        });
        subjectList += '</ul>';
    } else {
        subjectList = '<p class="text-muted">No subjects available</p>';
    }

    Swal.fire({
        title: `${classData.class_name} - Class Details`,
        html: `
            <div class="text-start">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Students</div>
                            <div class="h5 mb-0">${classData.student_count.toLocaleString()}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Subjects</div>
                            <div class="h5 mb-0">${classData.total_subjects}</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Completed</div>
                            <div class="h5 mb-0 text-success">${classData.completed_subjects}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Pending</div>
                            <div class="h5 mb-0 text-warning">${classData.pending_subjects}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Completion Rate</span>
                        <span class="fw-bold">${classData.completion_rate}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-${classData.completion_rate >= 75 ? 'success' : (classData.completion_rate >= 50 ? 'warning' : 'danger')}"
                             style="width: ${classData.completion_rate}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Entry Completion Rate</span>
                        <span class="fw-bold">${classData.entry_completion_rate || 0}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: ${classData.entry_completion_rate || 0}%"></div>
                    </div>
                </div>
                <hr>
                <h6>Subjects Offered:</h6>
                ${subjectList}
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close',
        confirmButtonColor: '#2563eb',
        width: '600px'
    });
}

/* -------------------------------------------------------
   Dashboard refresh on filter change
   ------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function() {
    // Add teacher filter dropdown if not present
    const filterContainer = document.querySelector('.select-all-bar')?.parentNode;
    if (filterContainer && !document.getElementById('statusFilter')) {
        const statusFilterHtml = `
            <select id="statusFilter" class="form-select" style="width: auto;">
                <option value="all">All Status</option>
                <option value="complete">Complete (100%)</option>
                <option value="high">High Progress (75-99%)</option>
                <option value="partial">Partial (50-74%)</option>
                <option value="low">Low (Below 50%)</option>
                <option value="no_scores">No Scores Entered</option>
            </select>
        `;
        filterContainer.insertAdjacentHTML('afterbegin', statusFilterHtml);

        const newFilter = document.getElementById('statusFilter');
        if (newFilter) {
            newFilter.addEventListener('change', function() {
                const value = this.value;
                const teacherCards = document.querySelectorAll('.teacher-card');

                teacherCards.forEach(card => {
                    const percent = parseInt(card.dataset.completion || '0');
                    if (value === 'all') {
                        card.style.display = '';
                    } else if (value === 'complete' && percent === 100) {
                        card.style.display = '';
                    } else if (value === 'high' && percent >= 75 && percent < 100) {
                        card.style.display = '';
                    } else if (value === 'partial' && percent >= 50 && percent < 75) {
                        card.style.display = '';
                    } else if (value === 'low' && percent < 50 && percent > 0) {
                        card.style.display = '';
                    } else if (value === 'no_scores' && percent === 0) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    }

    // Store completion percentage on teacher cards
    document.querySelectorAll('.teacher-card').forEach(card => {
        const percentText = card.querySelector('.teacher-stats span:last-child')?.textContent || '0';
        const percent = parseInt(percentText) || 0;
        card.dataset.completion = percent;
    });
});
</script>
@endsection
