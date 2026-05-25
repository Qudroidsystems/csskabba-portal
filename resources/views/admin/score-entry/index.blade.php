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
.filter-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px 24px;
    margin-bottom: 24px;
}
.teachers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 24px;
}
.teacher-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    transition: transform .18s;
}
.teacher-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,.1); }
.teacher-card-header {
    background: linear-gradient(135deg, #f1f5f9 0%, #e9eef3 100%);
    padding: 18px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 14px;
}
.teacher-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #1e3a5f;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 20px;
}
.teacher-name { font-weight: 700; color: #1e3a5f; font-size: 16px; margin: 0 0 4px; }
.teacher-stats { display: flex; gap: 12px; font-size: 11px; color: #6b7280; }
.teacher-card-body { padding: 16px 20px; max-height: 450px; overflow-y: auto; }
.subject-item { padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
.subject-item:last-child { border-bottom: none; }
.subject-name { font-weight: 600; font-size: 14px; }
.subject-code { font-size: 11px; color: #6b7280; font-family: monospace; }
.subject-class { font-size: 11px; color: #6b7280; margin-top: 4px; }
.badge-terminal, .badge-mock, .badge-locked { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-terminal { background: #dcfce7; color: #15803d; }
.badge-mock { background: #fef3c7; color: #b45309; }
.badge-locked { background: #fee2e2; color: #dc2626; }
.btn-score-group { display: flex; gap: 10px; margin-top: 10px; }
.btn-score { flex: 1; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; text-align: center; }
.btn-terminal-score { background: #16a34a; color: #fff; }
.btn-mock-score { background: #fef3c7; color: #b45309; }
.lock-status-icon { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 12px; background: #f3f4f6; }
.lock-status-icon.locked { background: #fee2e2; color: #dc2626; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="admin-hero">
        <h1><i class="ri-admin-line me-2"></i>Admin Score Entry</h1>
        <p>View all subject teachers and their assigned classes. Enter or edit scores on behalf of teachers.</p>
        <p class="mt-1"><i class="ri-lock-line me-1"></i> <strong>Lock Management:</strong> Lock individual scoresheets, apply global locks, or disable teacher editing entirely.</p>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.score-entry.index') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Academic Session</label>
                <select name="sessionid" class="form-select" required>
                    <option value="">— Select Session —</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Term</label>
                <select name="termid" class="form-select" required>
                    <option value="">— Select Term —</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTermId == $term->id ? 'selected' : '' }}>{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="ri-filter-3-line me-1"></i>Load</button>
            </div>
        </form>
    </div>

    @if($teacherSubjects->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $teacherSubjects->groupBy('teacher_id')->count() }}</div><div class="stat-label">Teachers</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $teacherSubjects->count() }}</div><div class="stat-label">Subjects</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value text-success">{{ $teacherSubjects->where('has_terminal_scores', true)->count() }}</div><div class="stat-label">With Scores</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value text-warning">{{ $teacherSubjects->where('has_mock_scores', true)->count() }}</div><div class="stat-label">Mock Scores</div></div></div>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <div><input type="text" id="searchInput" class="form-control" placeholder="Search teacher, subject or class..." style="width:300px;"></div>
    </div>

    <div id="teachersGrid">
        <div class="teachers-grid">
            @foreach($teacherSubjects->groupBy('teacher_id') as $teacherId => $subjects)
                @php $teacherName = $subjects->first()->teacher_name; $initials = strtoupper(substr($teacherName, 0, 2)); @endphp
                <div class="teacher-card" data-search="{{ strtolower($teacherName) }}">
                    <div class="teacher-card-header">
                        <div class="teacher-avatar">{{ $initials }}</div>
                        <div class="teacher-info">
                            <div class="teacher-name">{{ $teacherName }}</div>
                            <div class="teacher-stats">
                                <span><i class="ri-book-line"></i> {{ $subjects->count() }} subjects</span>
                                <span><i class="ri-check-line"></i> {{ $subjects->where('has_terminal_scores', true)->count() }} entered</span>
                            </div>
                        </div>
                    </div>
                    <div class="teacher-card-body">
                        @foreach($subjects as $subject)
                            <div class="subject-item">
                                <div class="subject-name">
                                    {{ $subject->subject_name }} <span class="subject-code">({{ $subject->subject_code }})</span>
                                    @if(!$subject->teacher_editing_enabled)
                                        <span class="lock-status-icon locked ms-2"><i class="ri-lock-line"></i> Locked</span>
                                    @endif
                                </div>
                                <div class="subject-class"><i class="ri-group-line me-1"></i>{{ $subject->class_name }}</div>
                                <div class="subject-badges mt-2">
                                    @if($subject->has_terminal_scores)<span class="badge-terminal"><i class="ri-check-line me-1"></i>Terminal</span>@endif
                                    @if($subject->has_mock_scores)<span class="badge-mock"><i class="ri-flask-line me-1"></i>Mock</span>@endif
                                    @if(!$subject->teacher_editing_enabled)<span class="badge-locked"><i class="ri-lock-line me-1"></i>Editing Disabled</span>@endif
                                </div>
                                <div class="btn-score-group">
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'terminal']) }}" class="btn-score btn-terminal-score">Terminal</a>
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'mock']) }}" class="btn-score btn-mock-score">Mock</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @elseif($selectedTermId && $selectedSessionId)
        <div class="text-center py-5"><i class="ri-user-unfollow-line fs-1 text-muted"></i><h5 class="mt-3">No Teacher Assignments Found</h5></div>
    @else
        <div class="text-center py-5"><i class="ri-filter-line fs-1 text-muted"></i><h5 class="mt-3">Select Session and Term</h5></div>
    @endif

</div>
</div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    let term = this.value.toLowerCase();
    document.querySelectorAll('.teacher-card').forEach(card => {
        let text = (card.dataset.search || '') + ' ' + card.innerText.toLowerCase();
        card.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>
@endsection
