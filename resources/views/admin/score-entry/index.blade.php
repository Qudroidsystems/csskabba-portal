{{-- resources/views/admin/score-entry/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-accent: #2563eb;
    --admin-success: #16a34a;
    --admin-warning: #d97706;
    --admin-danger: #dc2626;
    --admin-muted: #6b7280;
    --admin-border: #e2e8f0;
    --admin-bg: #f8fafc;
    --admin-radius: 12px;
    --admin-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.admin-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: var(--admin-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.admin-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.admin-hero::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -30px;
    width: 260px;
    height: 260px;
    background: rgba(255,255,255,.03);
    border-radius: 50%;
}
.admin-hero h1 {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.admin-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

.filter-card {
    background: #fff;
    border-radius: var(--admin-radius);
    border: 1px solid var(--admin-border);
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--admin-shadow);
}
.filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}
.filter-select {
    border: 1.5px solid var(--admin-border);
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 13px;
    width: 100%;
    transition: border .15s;
    background: #fff;
}
.filter-select:focus {
    border-color: var(--admin-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.stat-card {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--admin-shadow);
}
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--admin-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: var(--admin-muted);
    margin-top: 4px;
}
.stat-card .stat-icon {
    font-size: 32px;
    opacity: .12;
    float: right;
    margin-top: -8px;
}

.teachers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 24px;
}
.teacher-card {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    overflow: hidden;
    transition: transform .18s, box-shadow .18s;
}
.teacher-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,.1);
}
.teacher-card-header {
    background: linear-gradient(135deg, #f1f5f9 0%, #e9eef3 100%);
    padding: 18px 20px;
    border-bottom: 1px solid var(--admin-border);
    display: flex;
    align-items: center;
    gap: 14px;
}
.teacher-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--admin-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 20px;
}
.teacher-info {
    flex: 1;
}
.teacher-name {
    font-weight: 700;
    color: var(--admin-primary);
    font-size: 16px;
    margin: 0 0 4px;
}
.teacher-stats {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: var(--admin-muted);
}
.teacher-stats span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.teacher-card-body {
    padding: 16px 20px;
    max-height: 400px;
    overflow-y: auto;
}
.subject-item {
    padding: 12px 0;
    border-bottom: 1px solid var(--admin-border);
}
.subject-item:last-child {
    border-bottom: none;
}
.subject-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
}
.subject-code {
    font-size: 11px;
    color: var(--admin-muted);
    font-family: monospace;
}
.subject-class {
    font-size: 11px;
    color: var(--admin-muted);
    margin-top: 4px;
}
.subject-category {
    font-size: 10px;
    color: #6b7280;
    margin-top: 2px;
}
.subject-badges {
    display: flex;
    gap: 8px;
    margin: 8px 0 10px;
}
.badge-terminal, .badge-mock {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.badge-terminal {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.badge-mock {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.btn-score-group {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}
.btn-score {
    flex: 1;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all .15s;
}
.btn-terminal-score {
    background: var(--admin-success);
    color: #fff;
}
.btn-terminal-score:hover {
    background: #15803d;
    color: #fff;
    text-decoration: none;
}
.btn-mock-score {
    background: #fef3c7;
    color: #b45309;
}
.btn-mock-score:hover {
    background: #fde68a;
    color: #b45309;
    text-decoration: none;
}

.admin-table {
    width: 100%;
    margin-bottom: 0;
}
.admin-table th {
    background: var(--admin-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.admin-table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--admin-border);
    font-size: 13px;
}
.admin-table tbody tr:hover td {
    background: #eff6ff;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: var(--admin-radius);
    border: 1px solid var(--admin-border);
}
.empty-state i {
    font-size: 64px;
    color: #cbd5e1;
    margin-bottom: 16px;
    display: block;
}
.empty-state h5 {
    font-size: 18px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 8px;
}
.empty-state p {
    font-size: 13px;
    color: var(--admin-muted);
}

.search-input {
    border: 1.5px solid var(--admin-border);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    min-width: 240px;
    transition: border .15s;
}
.search-input:focus {
    border-color: var(--admin-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.view-toggle {
    display: flex;
    gap: 8px;
}
.view-toggle-btn {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid var(--admin-border);
    background: #fff;
    transition: all .15s;
}
.view-toggle-btn.active {
    background: var(--admin-accent);
    border-color: var(--admin-accent);
    color: #fff;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s;
}
.loading-overlay.active {
    visibility: visible;
    opacity: 1;
}
.loading-spinner {
    background: #fff;
    padding: 30px 40px;
    border-radius: 16px;
    text-align: center;
}
.loading-spinner .spinner-border {
    width: 40px;
    height: 40px;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="admin-hero">
        <h1><i class="ri-admin-line me-2"></i>Admin Score Entry</h1>
        <p>View all subject teachers and their assigned classes. Enter or edit scores on behalf of teachers.</p>
    </div>

    <div class="filter-card">
        <form id="filterForm" method="GET" action="{{ route('admin.score-entry.index') }}" autocomplete="off">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="filter-label"><i class="ri-calendar-line me-1"></i>Academic Session</label>
                    <select name="sessionid" id="sessionid" class="filter-select" required>
                        <option value="">— Select Session —</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>
                                {{ $session->session }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="filter-label"><i class="ri-survey-line me-1"></i>Term</label>
                    <select name="termid" id="termid" class="filter-select" required>
                        <option value="">— Select Term —</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ $selectedTermId == $term->id ? 'selected' : '' }}>
                                {{ $term->term }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" id="filterBtn">
                        <i class="ri-filter-3-line me-1"></i>Load Teachers
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($teacherSubjects->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-user-line"></i></div>
                <div class="stat-value">{{ $teacherSubjects->groupBy('teacher_id')->count() }}</div>
                <div class="stat-label">Total Teachers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                <div class="stat-value">{{ $teacherSubjects->count() }}</div>
                <div class="stat-label">Subject Assignments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-value text-success">{{ $teacherSubjects->where('has_terminal_scores', true)->count() }}</div>
                <div class="stat-label">With Terminal Scores</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-flask-line"></i></div>
                <div class="stat-value text-warning">{{ $teacherSubjects->where('has_mock_scores', true)->count() }}</div>
                <div class="stat-label">With Mock Scores</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="view-toggle">
            <button type="button" class="view-toggle-btn active" id="gridViewBtn">
                <i class="ri-grid-line me-1"></i>Grid View
            </button>
            <button type="button" class="view-toggle-btn" id="tableViewBtn">
                <i class="ri-table-line me-1"></i>Table View
            </button>
        </div>
        <div>
            <input type="text" id="searchInput" class="search-input" placeholder="Search teacher, subject or class…">
        </div>
    </div>

    <div id="gridView">
        <div id="teachersGrid">
            <div class="teachers-grid">
                @foreach($teacherSubjects->groupBy('teacher_id') as $teacherId => $subjects)
                    @php
                        $teacherName = $subjects->first()->teacher_name;
                        $initials = collect(explode(' ', $teacherName))->map(function($word) {
                            return strtoupper(substr($word, 0, 1));
                        })->take(2)->implode('');
                    @endphp
                    <div class="teacher-card" data-search="{{ strtolower($teacherName) }}">
                        <div class="teacher-card-header">
                            <div class="teacher-avatar">{{ $initials }}</div>
                            <div class="teacher-info">
                                <div class="teacher-name">{{ $teacherName }}</div>
                                <div class="teacher-stats">
                                    <span><i class="ri-book-line"></i> {{ $subjects->count() }} subjects</span>
                                    <span><i class="ri-check-line"></i> {{ $subjects->where('has_terminal_scores', true)->count() }} terminal</span>
                                    <span><i class="ri-flask-line"></i> {{ $subjects->where('has_mock_scores', true)->count() }} mock</span>
                                </div>
                            </div>
                        </div>
                        <div class="teacher-card-body">
                            @foreach($subjects as $subject)
                                <div class="subject-item">
                                    <div>
                                        <div class="subject-name">
                                            {{ $subject->subject_name }}
                                            <span class="subject-code">({{ $subject->subject_code }})</span>
                                        </div>
                                        <div class="subject-class">
                                            <i class="ri-group-line me-1"></i>{{ $subject->class_name }}
                                        </div>
                                        @if($subject->class_categories && $subject->class_categories !== 'N/A')
                                            <div class="subject-category">
                                                <i class="ri-folder-line me-1"></i>{{ $subject->class_categories }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="subject-badges">
                                        @if($subject->has_terminal_scores)
                                            <span class="badge-terminal"><i class="ri-check-line me-1"></i>Terminal Entered</span>
                                        @endif
                                        @if($subject->has_mock_scores)
                                            <span class="badge-mock"><i class="ri-flask-line me-1"></i>Mock Entered</span>
                                        @endif
                                    </div>
                                    <div class="btn-score-group">
                                        <a href="{{ route('admin.score-entry.scoresheet', [
                                            $subject->subjectclass_id,
                                            $subject->teacher_id,
                                            $subject->termid,
                                            $subject->sessionid,
                                            'terminal'
                                        ]) }}" class="btn-score btn-terminal-score">
                                            <i class="ri-file-list-line me-1"></i>
                                            {{ $subject->has_terminal_scores ? 'Edit Terminal' : 'Enter Terminal' }}
                                        </a>
                                        <a href="{{ route('admin.score-entry.scoresheet', [
                                            $subject->subjectclass_id,
                                            $subject->teacher_id,
                                            $subject->termid,
                                            $subject->sessionid,
                                            'mock'
                                        ]) }}" class="btn-score btn-mock-score">
                                            <i class="ri-flask-line me-1"></i>
                                            {{ $subject->has_mock_scores ? 'Edit Mock' : 'Enter Mock' }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="tableView" style="display:none;">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th>Subject Code</th>
                                <th>Class</th>
                                <th>Category</th>
                                <th>Term</th>
                                <th>Terminal Status</th>
                                <th>Mock Status</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacherSubjects as $index => $subject)
                                <tr data-search="{{ strtolower($subject->teacher_name . ' ' . $subject->subject_name . ' ' . $subject->class_name) }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $subject->teacher_name }}</td>
                                    <td>{{ $subject->subject_name }}</td>
                                    <td><code>{{ $subject->subject_code }}</code></td>
                                    <td>{{ $subject->class_name }}</td>
                                    <td>{{ $subject->class_categories ?? 'N/A' }}</td>
                                    <td>{{ $subject->term_name }}</td>
                                    <td>
                                        @if($subject->has_terminal_scores)
                                            <span class="badge bg-success"><i class="ri-check-line me-1"></i>Entered</span>
                                        @else
                                            <span class="badge bg-danger"><i class="ri-time-line me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subject->has_mock_scores)
                                            <span class="badge bg-success"><i class="ri-check-line me-1"></i>Entered</span>
                                        @else
                                            <span class="badge bg-danger"><i class="ri-time-line me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.score-entry.scoresheet', [
                                                $subject->subjectclass_id,
                                                $subject->teacher_id,
                                                $subject->termid,
                                                $subject->sessionid,
                                                'terminal'
                                            ]) }}" class="btn btn-sm btn-success" title="Terminal Scores">
                                                <i class="ri-file-list-line"></i> Terminal
                                            </a>
                                            <a href="{{ route('admin.score-entry.scoresheet', [
                                                $subject->subjectclass_id,
                                                $subject->teacher_id,
                                                $subject->termid,
                                                $subject->sessionid,
                                                'mock'
                                            ]) }}" class="btn btn-sm btn-warning" title="Mock Scores">
                                                <i class="ri-flask-line"></i> Mock
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @elseif($selectedTermId && $selectedSessionId)
        <div class="empty-state">
            <i class="ri-user-unfollow-line"></i>
            <h5>No Teacher Assignments Found</h5>
            <p>No subject teachers are assigned for the selected session and term.</p>
            <p class="mt-2 text-muted">Please check that subject teachers have been assigned in the system.</p>
        </div>
    @else
        <div class="empty-state">
            <i class="ri-filter-line"></i>
            <h5>Select Session and Term</h5>
            <p>Please select a session and term above to view teacher subject assignments.</p>
        </div>
    @endif

</div>
</div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0">Loading...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentView = localStorage.getItem('adminScoreEntryView') || 'grid';

    function setView(view) {
        currentView = view;
        localStorage.setItem('adminScoreEntryView', view);

        if (view === 'grid') {
            $('#gridView').show();
            $('#tableView').hide();
            $('#gridViewBtn').addClass('active');
            $('#tableViewBtn').removeClass('active');
        } else {
            $('#gridView').hide();
            $('#tableView').show();
            $('#tableViewBtn').addClass('active');
            $('#gridViewBtn').removeClass('active');
        }
    }

    $('#gridViewBtn').on('click', () => setView('grid'));
    $('#tableViewBtn').on('click', () => setView('table'));

    setView(currentView);

    // Search functionality
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();

        if (currentView === 'grid') {
            $('.teacher-card').each(function() {
                const cardText = ($(this).data('search') || '').toLowerCase();
                let subjectMatch = false;

                $(this).find('.subject-item').each(function() {
                    const subjectText = $(this).find('.subject-name').text().toLowerCase();
                    const classText = $(this).find('.subject-class').text().toLowerCase();
                    const categoryText = $(this).find('.subject-category').text().toLowerCase();

                    if (subjectText.includes(searchTerm) || classText.includes(searchTerm) || categoryText.includes(searchTerm)) {
                        subjectMatch = true;
                        return false;
                    }
                });

                if (cardText.includes(searchTerm) || subjectMatch || !searchTerm) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            $('#tableView tbody tr').each(function() {
                const rowText = ($(this).data('search') || $(this).text()).toLowerCase();
                if (rowText.includes(searchTerm) || !searchTerm) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    $('#filterForm').on('submit', function() {
        $('#loadingOverlay').addClass('active');
    });

    $('#sessionid, #termid').on('change', function() {
        if ($('#sessionid').val() && $('#termid').val()) {
            $('#filterForm').submit();
        }
    });

    @if(session('success'))
        toastr.success('{{ session("success") }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session("error") }}');
    @endif
});
</script>
@endsection
