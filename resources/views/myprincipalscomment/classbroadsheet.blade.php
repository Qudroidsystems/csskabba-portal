@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --principal-primary: #1e3a5f;
    --principal-accent:  #2563eb;
    --principal-success: #16a34a;
    --principal-warning: #d97706;
    --principal-danger:  #dc2626;
    --principal-muted:   #6b7280;
    --principal-border:  #e2e8f0;
    --principal-bg:      #f8fafc;
    --principal-radius:  12px;
    --principal-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* Hero Section */
.principal-hero {
    background: linear-gradient(135deg, var(--principal-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--principal-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.principal-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.principal-hero::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -30px;
    width: 260px;
    height: 260px;
    background: rgba(255,255,255,.03);
    border-radius: 50%;
}
.principal-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.principal-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

/* Stat Cards */
.stat-card {
    background: #fff;
    border: 1px solid var(--principal-border);
    border-radius: var(--principal-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--principal-shadow);
}
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--principal-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: var(--principal-muted);
    margin-top: 4px;
}
.stat-card .stat-icon {
    font-size: 32px;
    opacity: .12;
    float: right;
    margin-top: -8px;
}

/* Badges */
.principal-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.principal-badge-senior {
    background: #fef3c7;
    color: #d97706;
}
.principal-badge-junior {
    background: #dbeafe;
    color: #2563eb;
}
.principal-badge-cumulative {
    background: linear-gradient(135deg, #17a2b8, #0d6efd);
    color: #fff;
}
.principal-badge-term {
    background: #6c757d;
    color: #fff;
}

/* Subject Score Card */
.subject-score-card {
    background: var(--principal-bg);
    border-radius: 10px;
    padding: 8px 6px;
    text-align: center;
    transition: all .2s ease;
    min-width: 100px;
    border: 1px solid var(--principal-border);
}
.subject-score-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: var(--principal-shadow);
}
.term-score {
    font-size: 1.2rem;
    font-weight: 700;
}
.cumulative-score {
    font-size: 0.9rem;
    font-weight: 600;
}
.term-label {
    font-size: .6rem;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--principal-muted);
    letter-spacing: .5px;
}
.cumulative-label {
    font-size: .6rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #17a2b8;
    letter-spacing: .5px;
}

/* Grade badges */
.grade-badge-sm {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 700;
    margin-left: 5px;
}
.grade-a, .grade-a1 { background: #28a745; color: #fff; }
.grade-b, .grade-b2, .grade-b3 { background: #17a2b8; color: #fff; }
.grade-c, .grade-c4, .grade-c5, .grade-c6 { background: #6c757d; color: #fff; }
.grade-d, .grade-d7 { background: #ffc107; color: #212529; }
.grade-e, .grade-e8 { background: #fd7e14; color: #fff; }
.grade-f, .grade-f9 { background: #dc3545; color: #fff; }

.highlight-red { color: #dc3545 !important; font-weight: 600; }
.highlight-orange { color: #fd7e14 !important; font-weight: 600; }
.highlight-green { color: #28a745 !important; font-weight: 600; }

/* Table Styling */
.principal-table th {
    background: var(--principal-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.principal-table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--principal-border);
    font-size: 13px;
}
.principal-table tr:hover td {
    background: #eff6ff;
}

/* Comment dropdown */
.form-select.teacher-comment-dropdown {
    width: 100%;
    min-width: 200px;
    cursor: pointer;
    background-color: var(--principal-bg);
    border: 1.5px solid var(--principal-border);
    border-radius: 8px;
    transition: all .2s ease;
    font-size: .85rem;
}
.form-select.teacher-comment-dropdown:focus {
    background-color: #fff;
    border-color: var(--principal-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.comment-cell {
    position: relative;
}
.comment-info-icon {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: var(--principal-accent);
    z-index: 2;
    transition: color .2s ease;
    background: #fff;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
}
.comment-info-icon:hover {
    color: #0056b3;
    background: #e9ecef;
}

/* Intelligent comment */
.intelligent-comment-section {
    border-left: 3px solid #28a745;
    background-color: #f0fdf4 !important;
    margin-bottom: 10px;
    border-radius: 8px;
    padding: 8px;
}
.intelligent-comment-preview {
    font-size: .8rem;
    line-height: 1.4;
    white-space: pre-wrap;
    background: #fff;
    border: 1px solid var(--principal-border);
    border-radius: 6px;
    padding: 8px;
    margin-top: 5px;
}
.intelligent-comment-text {
    color: #155724;
    font-weight: 500;
}

/* Saved comment preview */
.saved-comment-preview {
    background-color: #f0fdf4 !important;
    border-left: 3px solid #28a745;
    border-radius: 8px;
    padding: 8px;
    font-size: .8rem;
    line-height: 1.4;
    white-space: pre-wrap;
    max-height: 100px;
    overflow-y: auto;
}

/* Grades tooltip */
.grades-tooltip {
    position: fixed;
    background: #fff;
    border: 2px solid var(--principal-accent);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    width: 480px;
    max-height: 550px;
    overflow: hidden;
    z-index: 10050;
    opacity: 0;
    visibility: hidden;
    transition: all .3s cubic-bezier(.4,0,.2,1);
    pointer-events: none;
}
.grades-tooltip.show {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    animation: tooltipFadeIn .3s ease-out;
}
@keyframes tooltipFadeIn {
    from {
        opacity: 0;
        transform: translate(-50%,-48%) scale(.95);
    }
    to {
        opacity: 1;
        transform: translate(-50%,-50%) scale(1);
    }
}
.grades-tooltip.position-bottom {
    bottom: 15%;
    left: 50%;
    transform: translateX(-50%);
}
.grades-tooltip .tooltip-header {
    background: linear-gradient(135deg, var(--principal-primary) 0%, var(--principal-accent) 60%, #4f46e5 100%);
    color: #fff;
    padding: 16px 50px 16px 20px;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 14px 14px 0 0;
    margin: -2px -2px 15px -2px;
    position: relative;
    display: flex;
    align-items: center;
}
.grades-tooltip .tooltip-close {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    color: #fff;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
}
.grades-tooltip .tooltip-body {
    padding: 0 15px 15px 15px;
    max-height: 420px;
    overflow-y: auto;
}

/* Auto-save toast */
.auto-save-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 99999;
    min-width: 280px;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}

/* Mobile cards */
.mobile-cards {
    display: none;
}
.student-card {
    background: #fff;
    border: 1px solid var(--principal-border);
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: var(--principal-shadow);
    overflow: hidden;
}
.student-header {
    background: var(--principal-bg);
    padding: 12px 15px;
    border-bottom: 1px solid var(--principal-border);
}
.student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.avatar-sm {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border-radius: 50%;
}
.student-details h6 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}
.student-meta {
    font-size: .8rem;
    color: var(--principal-muted);
}
.student-body {
    padding: 15px;
}
.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}
.subject-item {
    text-align: center;
    padding: 10px 6px;
    background: var(--principal-bg);
    border-radius: 10px;
    border: 1px solid var(--principal-border);
}
.subject-name {
    font-size: .7rem;
    font-weight: 700;
    color: #495057;
    margin-bottom: 6px;
    line-height: 1.2;
}
.performance-summary {
    background: linear-gradient(135deg, var(--principal-primary) 0%, var(--principal-accent) 100%);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    color: #fff;
}
.summary-title {
    font-weight: 600;
    font-size: .85rem;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    text-align: center;
}
.summary-item {
    padding: 8px;
    background: rgba(255,255,255,.15);
    border-radius: 8px;
    backdrop-filter: blur(5px);
}
.summary-label {
    font-size: .65rem;
    opacity: .9;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.summary-value {
    font-size: 1.2rem;
    font-weight: 700;
}

/* Responsive */
@media (min-width: 1200px) {
    .desktop-table {
        display: block !important;
    }
    .mobile-cards {
        display: none !important;
    }
}
@media (max-width: 1199.98px) {
    .desktop-table {
        display: none !important;
    }
    .mobile-cards {
        display: block !important;
    }
}

.spin-icon {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Hero Section --}}
            <div class="principal-hero">
                <h1>
                    <i class="ri-chat-quote-line me-2"></i>
                    Principal's Comment & Class Broadsheet
                </h1>
                <p>
                    <i class="ri-school-line me-1"></i>
                    {{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }} |
                    {{ $schoolterm }} {{ $schoolsession }}
                </p>
            </div>

            {{-- Stat Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-group-line"></i></div>
                        <div class="stat-value">{{ $students->count() }}</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                        <div class="stat-value text-primary">{{ count($subjects) }}</div>
                        <div class="stat-label">Subjects Offered</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-bar-chart-line"></i></div>
                        <div class="stat-value text-success">{{ $classAnalytics['average'] }}</div>
                        <div class="stat-label">Class Average</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-award-line"></i></div>
                        <div class="stat-value text-warning">
                            @php
                                $topStudent = $students->sortByDesc(fn($s) => $studentAnalytics[$s->id]['average'] ?? 0)->first();
                            @endphp
                            @if($topStudent)
                                {{ $topStudent->firstname }} {{ substr($topStudent->lastname, 0, 1) }}.
                            @else
                                —
                            @endif
                        </div>
                        <div class="stat-label">Top Performer</div>
                    </div>
                </div>
            </div>

            {{-- Flash messages --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @if ($students->isNotEmpty())
                <form id="commentsForm"
                      action="{{ route('myprincipalscomment.updateComments', [$schoolclassid, $sessionid, $termid]) }}"
                      method="POST">
                    @csrf

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-0 fw-semibold" style="color: var(--principal-primary)">
                                    <i class="ri-bar-chart-2-line me-2"></i>
                                    Student Performance Dashboard
                                    @if($isSenior)
                                        <span class="principal-badge principal-badge-senior ms-2">Senior Class (A1–F9)</span>
                                    @else
                                        <span class="principal-badge principal-badge-junior ms-2">Junior Class (A–F)</span>
                                    @endif
                                </h5>
                                <div class="mt-2 mt-sm-0">
                                    <span class="principal-badge principal-badge-cumulative me-1">
                                        <i class="ri-bar-chart-line"></i> Cumulative Score
                                    </span>
                                    <span class="principal-badge principal-badge-term">
                                        <i class="ri-calendar-line"></i> Term Score
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- Search --}}
                            <div class="search-box mb-4">
                                <input type="text" class="form-control" id="searchInput"
                                       placeholder="🔍 Search students by name or admission number…"
                                       style="border: 1.5px solid var(--principal-border); border-radius: 8px;">
                                <i class="ri-search-line search-icon"></i>
                            </div>

                            {{-- ========================================================
                                 DESKTOP TABLE
                            ======================================================== --}}
                            <div class="desktop-table">
                                <div class="table-responsive">
                                    <table class="table principal-table w-100 mb-0">
                                        <thead>
                                            <tr>
                                                <th width="40">#</th>
                                                <th width="100">Admission No</th>
                                                <th width="180">Student Name</th>
                                                <th width="70">Gender</th>
                                                <th class="text-center">
                                                    <i class="ri-book-open-line me-2"></i>
                                                    Subjects Performance (Term | Cumulative)
                                                </th>
                                                <th width="300">Principal's Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $index => $student)
                                                @php
                                                    $sid = $student->id;
                                                    $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                    $imgPath = asset('storage/student_avatars/' . $picture);
                                                    $currentComment = $profiles[$sid] ?? '';
                                                    $currentCommentPlain = strip_tags($currentComment);
                                                    $intelligentComment = $intelligentComments[$sid] ?? '';
                                                    $hasWeakAdvice = !empty($studentGradeAnalysis[$sid]['weak_subjects'] ?? []);
                                                    $analytics = $studentAnalytics[$sid] ?? [];
                                                @endphp
                                                <tr data-student-id="{{ $sid }}" class="student-row">
                                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                                    <td>{{ $student->admissionNo }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $imgPath }}" class="avatar-sm me-2" alt="">
                                                            <div>
                                                                <strong>{{ $student->lastname }} {{ $student->fname }}</strong>
                                                                @if($currentComment)
                                                                    <small class="d-block text-success mt-1">
                                                                        <i class="ri-check-double-line"></i> Comment saved
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <i class="ri-{{ $student->gender === 'Male' ? 'male' : 'female' }}-line"></i>
                                                            {{ $student->gender ?? 'N/A' }}
                                                        </span>
                                                    </td>

                                                    {{-- Subject scores --}}
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach ($subjects as $subject)
                                                                @php
                                                                    $termTotal = $termScoreMap[$sid][$subject] ?? 0;
                                                                    $cumTotal = $cumScoreMap[$sid][$subject] ?? 0;
                                                                    $termClass = $termTotal < 40 ? 'highlight-red' : ($termTotal < 50 ? 'highlight-orange' : ($termTotal >= 70 ? 'highlight-green' : ''));
                                                                    $cumClass = $cumTotal < 40 ? 'highlight-red' : ($cumTotal < 50 ? 'highlight-orange' : ($cumTotal >= 70 ? 'highlight-green' : ''));
                                                                @endphp
                                                                <div class="subject-score-card" style="min-width: 80px;">
                                                                    <div class="small fw-bold">{{ $subject }}</div>
                                                                    <div class="term-score {{ $termClass }}">{{ $termTotal ?: '—' }}</div>
                                                                    <div class="cumulative-score {{ $cumClass }}">{{ $cumTotal ?: '—' }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>

                                                    {{-- Comment cell --}}
                                                    <td class="comment-cell">
                                                        @if($intelligentComment)
                                                            <div class="intelligent-comment-section mb-2">
                                                                <small class="text-muted d-block mb-1">
                                                                    <i class="ri-lightbulb-line text-success"></i>
                                                                    <strong>AI Suggested Comment</strong>
                                                                    @if($hasWeakAdvice)
                                                                        <span class="badge bg-warning ms-1">Includes Advice</span>
                                                                    @endif
                                                                </small>
                                                                <div class="intelligent-comment-preview">
                                                                    <div class="intelligent-comment-text small">
                                                                        {!! nl2br(e(\Illuminate\Support\Str::limit($intelligentComment, 120))) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($currentComment)
                                                            <div class="mb-2">
                                                                <small class="text-success d-block mb-1">
                                                                    <i class="ri-chat-check-line"></i> <strong>Saved Comment</strong>
                                                                </small>
                                                                <div class="saved-comment-preview">
                                                                    <small class="text-secondary">
                                                                        {!! nl2br(e(\Illuminate\Support\Str::limit($currentCommentPlain, 100))) !!}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                name="teacher_comments[{{ $sid }}]"
                                                                data-student-id="{{ $sid }}"
                                                                data-original-value="{{ $currentCommentPlain }}">
                                                            <option value="">-- Select a Comment --</option>
                                                            @foreach ($standardPersonalizedComments[$sid] ?? [] as $opt)
                                                                @php $plain = strip_tags($opt); @endphp
                                                                <option value="{{ $plain }}"
                                                                    {{ $currentCommentPlain === $plain ? 'selected' : '' }}>
                                                                    {{ \Illuminate\Support\Str::limit($plain, 70) }}
                                                                </option>
                                                            @endforeach
                                                            @php
                                                                $intPlain = strip_tags($intelligentComments[$sid] ?? '');
                                                                $stdPlains = array_map('strip_tags', $standardPersonalizedComments[$sid] ?? []);
                                                                $showAI = $intPlain && !in_array($intPlain, $stdPlains);
                                                            @endphp
                                                            @if($showAI)
                                                                <option value="{{ $intPlain }}"
                                                                        style="background-color: #e8f5e8 !important; font-weight: 600 !important; color: #155724 !important;"
                                                                    {{ $currentCommentPlain === $intPlain ? 'selected' : '' }}>
                                                                    💡 Use AI Generated Comment
                                                                </option>
                                                            @endif
                                                        </select>

                                                        <button type="button"
                                                                class="comment-info-icon grades-trigger"
                                                                data-student-id="{{ $sid }}"
                                                                data-student-name="{{ $student->lastname }} {{ $student->fname }}">
                                                            <i class="ri-eye-line"></i>
                                                        </button>

                                                        {{-- Grades tooltip --}}
                                                        <div class="grades-tooltip position-bottom" id="tooltip-{{ $sid }}">
                                                            <div class="tooltip-header">
                                                                <i class="ri-bar-chart-line me-2"></i>
                                                                <span id="tooltip-title-{{ $sid }}">Performance Details</span>
                                                                <button type="button" class="tooltip-close">
                                                                    <i class="ri-close-line"></i>
                                                                </button>
                                                            </div>
                                                            <div class="tooltip-body">
                                                                <div class="row mb-3">
                                                                    <div class="col-6">
                                                                        <div class="stat-card">
                                                                            <small class="text-muted">📊 Term Total</small>
                                                                            <h4 class="mb-0 {{ ($analytics['term_total'] ?? 0) < 50 ? 'text-danger' : 'text-success' }}">
                                                                                {{ $analytics['term_total'] ?? 0 }}
                                                                            </h4>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="stat-card" style="border:2px solid #17a2b8;">
                                                                            <small class="text-info">📈 Cumulative Total</small>
                                                                            <h4 class="mb-0 text-info">{{ $analytics['total_score'] ?? 0 }}</h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <div class="col-4">
                                                                        <div class="stat-card">
                                                                            <small>Term Avg</small>
                                                                            <strong>{{ $analytics['term_average'] ?? 0 }}</strong>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="stat-card">
                                                                            <small>Cum Avg</small>
                                                                            <strong>{{ $analytics['average'] ?? 0 }}</strong>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="stat-card">
                                                                            <small>Position</small>
                                                                            <strong class="text-primary">{{ $analytics['position_text'] ?? '—' }}</strong>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ========================================================
                                 MOBILE CARDS
                            ======================================================== --}}
                            <div class="mobile-cards">
                                @foreach ($students as $student)
                                    @php
                                        $sid = $student->id;
                                        $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                        $imgPath = asset('storage/student_avatars/' . $picture);
                                        $currentComment = $profiles[$sid] ?? '';
                                        $currentCommentPlain = strip_tags($currentComment);
                                        $intelligentComment = $intelligentComments[$sid] ?? '';
                                        $analytics = $studentAnalytics[$sid] ?? [];
                                        $myAvg = $analytics['average'] ?? 0;
                                        $myTermAvg = $analytics['term_average'] ?? 0;
                                    @endphp
                                    <div class="student-card" data-student-id="{{ $sid }}">
                                        <div class="student-header">
                                            <div class="student-info">
                                                <img src="{{ $imgPath }}" class="avatar-sm" alt="">
                                                <div class="student-details">
                                                    <h6>
                                                        {{ $student->lastname }} {{ $student->fname }}
                                                        @if($currentComment)
                                                            <span class="badge bg-success ms-2">✓</span>
                                                        @endif
                                                    </h6>
                                                    <div class="student-meta">
                                                        <i class="ri-id-card-line"></i> {{ $student->admissionNo }} |
                                                        <i class="ri-user-line"></i> {{ $student->gender ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="student-body">
                                            <div class="performance-summary">
                                                <div class="summary-title"><i class="ri-bar-chart-line"></i> Performance Summary</div>
                                                <div class="summary-grid">
                                                    <div class="summary-item">
                                                        <div class="summary-label">Term Average</div>
                                                        <div class="summary-value">{{ $myTermAvg }}</div>
                                                    </div>
                                                    <div class="summary-item">
                                                        <div class="summary-label">Cumulative Avg</div>
                                                        <div class="summary-value">{{ $myAvg }}</div>
                                                    </div>
                                                    <div class="summary-item">
                                                        <div class="summary-label">Position</div>
                                                        <div class="summary-value">{{ $analytics['position_text'] ?? '—' }}</div>
                                                    </div>
                                                    <div class="summary-item">
                                                        <div class="summary-label">Cum Total</div>
                                                        <div class="summary-value">{{ $analytics['total_score'] ?? 0 }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="subjects-grid">
                                                @foreach ($subjects as $subject)
                                                    @php
                                                        $termTotal = $termScoreMap[$sid][$subject] ?? 0;
                                                        $cumTotal = $cumScoreMap[$sid][$subject] ?? 0;
                                                    @endphp
                                                    <div class="subject-item">
                                                        <div class="subject-name">{{ $subject }}</div>
                                                        <div class="small">
                                                            <span class="badge bg-secondary">Term</span>
                                                            <strong class="{{ $termTotal < 50 ? 'text-danger' : 'text-success' }}">{{ $termTotal ?: '—' }}</strong>
                                                        </div>
                                                        <div class="small mt-1">
                                                            <span class="badge bg-primary">Cum</span>
                                                            <strong class="{{ $cumTotal < 50 ? 'text-danger' : 'text-success' }}">{{ $cumTotal ?: '—' }}</strong>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($intelligentComment)
                                                <div class="intelligent-comment-section mb-2">
                                                    <small><i class="ri-lightbulb-line text-success"></i> <strong>AI Suggestion</strong></small>
                                                    <div class="small mt-1 text-muted">
                                                        {{ \Illuminate\Support\Str::limit($intelligentComment, 100) }}
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="comment-section-mobile">
                                                <label class="form-label mb-2">
                                                    <i class="ri-chat-3-line"></i> Principal's Comment
                                                </label>
                                                <select class="form-select auto-save-comment"
                                                        name="teacher_comments[{{ $sid }}]"
                                                        data-student-id="{{ $sid }}"
                                                        data-original-value="{{ $currentCommentPlain }}">
                                                    <option value="">-- Select Comment --</option>
                                                    @foreach ($standardPersonalizedComments[$sid] ?? [] as $opt)
                                                        @php $plain = strip_tags($opt); @endphp
                                                        <option value="{{ $plain }}"
                                                            {{ $currentCommentPlain === $plain ? 'selected' : '' }}>
                                                            {{ \Illuminate\Support\Str::limit($plain, 50) }}
                                                        </option>
                                                    @endforeach
                                                    @php
                                                        $intPlain = strip_tags($intelligentComments[$sid] ?? '');
                                                        $stdPlains = array_map('strip_tags', $standardPersonalizedComments[$sid] ?? []);
                                                    @endphp
                                                    @if($intPlain && !in_array($intPlain, $stdPlains))
                                                        <option value="{{ $intPlain }}"
                                                                style="background-color: #e8f5e8 !important; font-weight: 600 !important;"
                                                            {{ $currentCommentPlain === $intPlain ? 'selected' : '' }}>
                                                            💡 Use AI Generated Comment
                                                        </option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Save all button --}}
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-save-all" id="saveAllBtn"
                                            style="padding: 10px 24px; font-weight: 600; border-radius: 8px;">
                                        <i class="ri-save-line me-1"></i> Save All Comments
                                    </button>
                                    <span id="savingIndicator" class="ms-2 text-muted" style="display:none;">
                                        <i class="ri-loader-4-line spin-icon me-1"></i> Saving…
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                   trigger="loop"
                                   colors="primary:#121331,secondary:#08a88a"
                                   style="width:120px;height:120px">
                        </lord-icon>
                        <h5 class="mt-4 text-muted">No Students Enrolled</h5>
                        <p class="text-muted mb-0">No students are enrolled in this class for the selected session and term.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.studentGradesData = @json($studentGrades);
let activeTooltip = null;

function showToast(message, type = 'info') {
    document.querySelector('.auto-save-toast')?.remove();
    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show`;
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'information'}-fill me-2 fs-5"></i>
            <span>${escapeHtml(message)}</span>
            <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
        </div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function closeAllTooltips() {
    document.querySelectorAll('.grades-tooltip.show').forEach(t => t.classList.remove('show'));
    activeTooltip = null;
}

function showTooltip(tooltipId, studentId, studentName) {
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    closeAllTooltips();

    const titleEl = document.getElementById(`tooltip-title-${studentId}`);
    if (titleEl) titleEl.textContent = `${studentName}'s Performance`;

    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

// Auto-save functionality
document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function() {
        const studentId = this.dataset.studentId;
        const comment = this.value.trim();
        const original = this.dataset.originalValue || '';
        if (comment === original) return;

        this.style.backgroundColor = '#fff3cd';
        this.disabled = true;

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append(`teacher_comments[${studentId}]`, comment);

        fetch('{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}', {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Save failed');
            document.querySelectorAll(`.auto-save-comment[data-student-id="${studentId}"]`).forEach(s => {
                s.value = comment;
                s.dataset.originalValue = comment;
            });
            this.style.backgroundColor = '#d1e7dd';
            showToast('Comment saved!', 'success');
            setTimeout(() => { this.style.backgroundColor = ''; }, 1500);
        })
        .catch(err => {
            console.error('Auto-save error:', err);
            this.value = original;
            this.style.backgroundColor = '#f8d7da';
            showToast('Error: ' + err.message, 'danger');
            setTimeout(() => { this.style.backgroundColor = ''; }, 2000);
        })
        .finally(() => {
            this.disabled = false;
        });
    });
});

// Bulk save
const commentsForm = document.getElementById('commentsForm');
if (commentsForm) {
    commentsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveAllBtn');
        const ind = document.getElementById('savingIndicator');
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line spin-icon me-1"></i> Saving All Comments…';
        ind.style.display = 'inline-block';

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');

        document.querySelectorAll('.auto-save-comment').forEach(sel => {
            const val = sel.value.trim();
            if (val) fd.append(`teacher_comments[${sel.dataset.studentId}]`, val);
        });

        fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Save failed');
            document.querySelectorAll('.auto-save-comment').forEach(sel => {
                const val = sel.value.trim();
                if (val) sel.dataset.originalValue = val;
            });
            showToast(data.message || 'All comments saved!', 'success');
        })
        .catch(err => {
            console.error('Bulk save error:', err);
            showToast('Error: ' + err.message, 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = orig;
            ind.style.display = 'none';
        });
    });
}

// Tooltip triggers
if (window.innerWidth > 1199) {
    document.querySelectorAll('.grades-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const sid = this.dataset.studentId;
            const name = this.dataset.studentName;
            const tid = `tooltip-${sid}`;
            activeTooltip === tid ? closeAllTooltips() : showTooltip(tid, sid, name);
        });
    });

    document.querySelectorAll('.tooltip-close').forEach(btn => {
        btn.addEventListener('click', closeAllTooltips);
    });

    document.addEventListener('click', e => {
        if (!activeTooltip) return;
        const activeEl = document.getElementById(activeTooltip);
        if (activeEl && !activeEl.contains(e.target)) {
            closeAllTooltips();
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeAllTooltips();
    });
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function() {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.desktop-table tbody tr').forEach(row => {
        row.style.display = (!term || row.textContent.toLowerCase().includes(term)) ? '' : 'none';
    });
    document.querySelectorAll('.mobile-cards .student-card').forEach(card => {
        card.style.display = (!term || card.textContent.toLowerCase().includes(term)) ? '' : 'none';
    });
});

// Initialize original values
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.auto-save-comment').forEach(s => {
        s.dataset.originalValue = s.value;
    });
});
</script>
@endsection
