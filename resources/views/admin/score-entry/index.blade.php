{{-- resources/views/admin/score-entry/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
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

.btn-hero:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    transform: translateY(-2px);
}

.btn-hero-primary {
    background: #ffc107;
    border-color: #ffc107;
    color: #1e3a5f;
}

.btn-hero-primary:hover {
    background: #ffca2c;
    color: #1e3a5f;
}

.filter-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* Enhanced Stats Cards */
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

.stat-card-enhanced:hover {
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
}

.trend-up { color: #10b981; }
.trend-down { color: #ef4444; }
.trend-neutral { color: #6b7280; }

.stat-footer {
    background: #f8fafc;
    padding: 12px 20px;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
    color: #64748b;
}

/* Teachers Grid */
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

.teacher-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -12px rgba(0,0,0,0.15);
}

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
}

.teacher-name {
    font-weight: 700;
    color: #1e293b;
    font-size: 18px;
    margin: 0 0 6px;
}

.teacher-stats {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #64748b;
}

.teacher-card-body {
    padding: 16px 20px;
    max-height: 480px;
    overflow-y: auto;
}

.subject-item {
    padding: 14px 0;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.2s;
}

.subject-item:last-child {
    border-bottom: none;
}

.subject-item:hover {
    background: #f8fafc;
    margin: 0 -20px;
    padding: 14px 20px;
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

.subject-class {
    font-size: 12px;
    color: #64748b;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.subject-badges {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
}

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
.badge-mock { background: #fef3c7; color: #b45309; }
.badge-locked { background: #fee2e2; color: #dc2626; }
.badge-open { background: #dbeafe; color: #1d4ed8; }

.btn-score-group {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}

.btn-score {
    flex: 1;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
}

.btn-terminal-score {
    background: #10b981;
    color: #fff;
}

.btn-terminal-score:hover {
    background: #059669;
    transform: translateY(-1px);
}

.btn-mock-score {
    background: #fef3c7;
    color: #b45309;
}

.btn-mock-score:hover {
    background: #fde68a;
    transform: translateY(-1px);
}

.lock-status-icon {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 20px;
    background: #f3f4f6;
}

.lock-status-icon.locked {
    background: #fee2e2;
    color: #dc2626;
}

.search-bar {
    margin-bottom: 24px;
}

.search-input-wrapper {
    position: relative;
    max-width: 350px;
}

.search-input-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-input {
    padding-left: 40px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    height: 44px;
    width: 100%;
}

.search-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.empty-state i {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 16px;
}

.empty-state h5 {
    color: #64748b;
    margin-bottom: 8px;
}

/* Scrollbar */
.teacher-card-body::-webkit-scrollbar {
    width: 6px;
}

.teacher-card-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.teacher-card-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.teacher-card-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@media (max-width: 768px) {
    .teachers-grid {
        grid-template-columns: 1fr;
    }

    .stats-dashboard {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .stat-main-value {
        font-size: 28px;
    }

    .admin-hero {
        padding: 20px;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="admin-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="mb-2"><i class="ri-admin-line me-2"></i>Admin Score Entry</h1>
                <p class="mb-0">View all subject teachers and their assigned classes. Enter or edit scores on behalf of teachers.</p>
                <p class="mt-2 mb-0"><i class="ri-shield-check-line me-1"></i> <strong>Lock Management:</strong> Lock individual scoresheets, apply global locks, or disable teacher editing entirely.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="{{ route('admin.score-entry.lock-management') }}" class="btn-hero btn-hero-primary">
                    <i class="ri-shield-lock-line"></i> Lock Manager
                </a>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.score-entry.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Academic Session</label>
                <select name="sessionid" class="form-select" required>
                    <option value="">— Select Session —</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Term</label>
                <select name="termid" class="form-select" required>
                    <option value="">— Select Term —</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTermId == $term->id ? 'selected' : '' }}>{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ri-filter-3-line me-1"></i>Load</button>
            </div>
        </form>
    </div>

    @if($teacherSubjects->isNotEmpty())
    @php
        $totalTeachers = $teacherSubjects->groupBy('teacher_id')->count();
        $totalSubjects = $teacherSubjects->count();
        $totalWithScores = $teacherSubjects->where('has_terminal_scores', true)->count();
        $totalMockScores = $teacherSubjects->where('has_mock_scores', true)->count();
        $totalLocked = $teacherSubjects->where('teacher_editing_enabled', false)->count();
        $completionRate = $totalSubjects > 0 ? round(($totalWithScores / $totalSubjects) * 100) : 0;
    @endphp

    <div class="stats-dashboard">
        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Teachers</h3>
                <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                    <i class="ri-user-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value">{{ $totalTeachers }}</div>
                <div class="stat-trend">
                    <i class="ri-group-line"></i>
                    <span>Active teachers this term</span>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-calendar-line me-1"></i> Current term assignments
            </div>
        </div>

        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Subjects</h3>
                <div class="stat-icon" style="background: #dcfce7; color: #10b981;">
                    <i class="ri-book-open-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value">{{ $totalSubjects }}</div>
                <div class="stat-trend">
                    <span>{{ $totalWithScores }} have scores entered</span>
                </div>
            </div>
            <div class="stat-footer">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: {{ $completionRate }}%"></div>
                </div>
                <small class="mt-2 d-block">{{ $completionRate }}% completion rate</small>
            </div>
        </div>

        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Scores Entered</h3>
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                    <i class="ri-file-list-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-success">{{ $totalWithScores }}</div>
                <div class="stat-trend">
                    <i class="ri-checkbox-circle-line text-success"></i>
                    <span>Terminal scores recorded</span>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Mock: {{ $totalMockScores }}</small>
                </div>
            </div>
            <div class="stat-footer">
                <i class="ri-time-line me-1"></i> Last updated: {{ now()->format('d M Y') }}
            </div>
        </div>

        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Lock Status</h3>
                <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                    <i class="ri-lock-line"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-warning">{{ $totalLocked }}</div>
                <div class="stat-trend">
                    <i class="ri-shield-warning-line"></i>
                    <span>Subjects with editing disabled</span>
                </div>
            </div>
            <div class="stat-footer">
                <a href="{{ route('admin.score-entry.lock-management') }}" class="text-decoration-none">
                    <i class="ri-settings-4-line me-1"></i> Manage locks →
                </a>
            </div>
        </div>
    </div>

    <div class="search-bar">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="search-input-wrapper">
                <i class="ri-search-line"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search teacher, subject or class...">
            </div>
            <div>
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="ri-shield-check-line me-1"></i>
                    {{ $totalTeachers }} Teachers · {{ $totalSubjects }} Subjects
                </span>
            </div>
        </div>
    </div>

    <div id="teachersGrid">
        <div class="teachers-grid">
            @foreach($teacherSubjects->groupBy('teacher_id') as $teacherId => $subjects)
                @php
                    $teacherName = $subjects->first()->teacher_name;
                    $initials = strtoupper(substr($teacherName, 0, 2));
                    $teacherLockedCount = $subjects->where('teacher_editing_enabled', false)->count();
                @endphp
                <div class="teacher-card" data-search="{{ strtolower($teacherName) }}">
                    <div class="teacher-card-header">
                        <div class="teacher-avatar">{{ $initials }}</div>
                        <div class="teacher-info">
                            <div class="teacher-name">{{ $teacherName }}</div>
                            <div class="teacher-stats">
                                <span><i class="ri-book-line"></i> {{ $subjects->count() }} subjects</span>
                                <span><i class="ri-check-line"></i> {{ $subjects->where('has_terminal_scores', true)->count() }} entered</span>
                                @if($teacherLockedCount > 0)
                                    <span class="text-danger"><i class="ri-lock-line"></i> {{ $teacherLockedCount }} locked</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="teacher-card-body">
                        @foreach($subjects as $subject)
                            <div class="subject-item">
                                <div class="subject-name">
                                    <span>{{ $subject->subject_name }} <span class="subject-code">({{ $subject->subject_code }})</span></span>
                                    @if(!$subject->teacher_editing_enabled)
                                        <span class="lock-status-icon locked"><i class="ri-lock-line"></i> Edit Disabled</span>
                                    @endif
                                </div>
                                <div class="subject-class">
                                    <i class="ri-group-line"></i> {{ $subject->class_name }}
                                </div>
                                <div class="subject-badges">
                                    @if($subject->has_terminal_scores)
                                        <span class="badge-terminal"><i class="ri-check-line"></i> Terminal</span>
                                    @else
                                        <span class="badge-open"><i class="ri-add-line"></i> No Scores</span>
                                    @endif
                                    @if($subject->has_mock_scores)
                                        <span class="badge-mock"><i class="ri-flask-line"></i> Mock</span>
                                    @endif
                                </div>
                                <div class="btn-score-group">
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'terminal']) }}"
                                       class="btn-score btn-terminal-score">
                                        <i class="ri-file-edit-line"></i> Terminal
                                    </a>
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'mock']) }}"
                                       class="btn-score btn-mock-score">
                                        <i class="ri-flask-line"></i> Mock
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @elseif($selectedTermId && $selectedSessionId)
        <div class="empty-state">
            <i class="ri-user-unfollow-line"></i>
            <h5>No Teacher Assignments Found</h5>
            <p class="text-muted">No teachers have been assigned to subjects for the selected term and session.</p>
        </div>
    @else
        <div class="empty-state">
            <i class="ri-filter-line"></i>
            <h5>Select Session and Term</h5>
            <p class="text-muted">Please select an academic session and term to view teacher assignments.</p>
        </div>
    @endif

</div>
</div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    let term = this.value.toLowerCase();
    let visibleCount = 0;

    document.querySelectorAll('.teacher-card').forEach(card => {
        let text = (card.dataset.search || '') + ' ' + card.innerText.toLowerCase();
        let isVisible = text.includes(term);
        card.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });

    // Optional: Show/hide empty state message
    const grid = document.querySelector('.teachers-grid');
    const emptyMsg = document.getElementById('searchEmptyMsg');

    if (visibleCount === 0 && term) {
        if (!emptyMsg) {
            const msg = document.createElement('div');
            msg.id = 'searchEmptyMsg';
            msg.className = 'empty-state';
            msg.innerHTML = '<i class="ri-search-line"></i><h5>No matching results</h5><p class="text-muted">Try a different search term.</p>';
            grid?.parentNode?.appendChild(msg);
            grid?.style.setProperty('display', 'none');
        }
    } else {
        const existingMsg = document.getElementById('searchEmptyMsg');
        if (existingMsg) existingMsg.remove();
        if (grid) grid.style.display = '';
    }
});
</script>
@endsection
