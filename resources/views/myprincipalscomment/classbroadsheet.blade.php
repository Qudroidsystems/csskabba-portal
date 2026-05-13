@extends('layouts.master')

@section('content')
<style>
    .highlight-red    { color: #dc3545 !important; font-weight: 600; }
    .highlight-orange { color: #fd7e14 !important; font-weight: 600; }
    .highlight-green  { color: #28a745 !important; font-weight: 600; }
    .avatar-sm        { width: 32px; height: 32px; object-fit: cover; border-radius: 50%; }
    .table-centered th, .table-centered td { text-align: center; vertical-align: middle; }

    /* Subject Score Card */
    .subject-score-card {
        background: #f8f9fa; border-radius: 10px; padding: 8px 6px;
        text-align: center; transition: all .2s ease;
        min-width: 100px; border: 1px solid #e9ecef;
    }
    .subject-score-card:hover { background:#e9ecef; transform:translateY(-2px); box-shadow:0 2px 8px rgba(0,0,0,.1); }
    .term-score       { font-size: 1.2rem; font-weight: 700; }
    .cumulative-score { font-size: 0.9rem; font-weight: 600; }
    .term-label       { font-size:.6rem; text-transform:uppercase; font-weight:700; color:#6c757d; letter-spacing:.5px; }
    .cumulative-label { font-size:.6rem; text-transform:uppercase; font-weight:700; color:#17a2b8; letter-spacing:.5px; }

    /* Grade badges */
    .grade-badge-sm { display:inline-block; padding:2px 8px; border-radius:20px; font-size:.7rem; font-weight:700; margin-left:5px; }
    .grade-a,.grade-a1            { background:#28a745; color:#fff; }
    .grade-b,.grade-b2,.grade-b3  { background:#17a2b8; color:#fff; }
    .grade-c,.grade-c4,.grade-c5,.grade-c6 { background:#6c757d; color:#fff; }
    .grade-d,.grade-d7            { background:#ffc107; color:#212529; }
    .grade-e,.grade-e8            { background:#fd7e14; color:#fff; }
    .grade-f,.grade-f9            { background:#dc3545; color:#fff; }

    /* Table headers */
    .subject-header { font-size:.85rem; font-weight:700; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; }
    .score-header   { font-size:.7rem; font-weight:600; background:#f1f3f5; }

    /* Comment dropdown */
    .form-select.teacher-comment-dropdown {
        width:100%; min-width:200px; cursor:pointer;
        background-color:#f8f9fa; border:1px solid #ced4da;
        transition:all .2s ease; font-size:.85rem;
    }
    .form-select.teacher-comment-dropdown:focus {
        background-color:#fff; border-color:#80bdff;
        box-shadow:0 0 0 .2rem rgba(0,123,255,.25);
    }

    .comment-cell { position:relative; }
    .comment-info-icon {
        position:absolute; right:5px; top:50%; transform:translateY(-50%);
        font-size:1rem; color:#0d6efd; z-index:2; transition:color .2s ease;
        background:#fff; border-radius:50%; width:28px; height:28px;
        display:flex; align-items:center; justify-content:center;
    }
    .comment-info-icon:hover { color:#0056b3; background:#e9ecef; }

    .auto-save-toast {
        position:fixed; bottom:20px; right:20px; z-index:99999;
        min-width:280px; box-shadow:0 4px 12px rgba(0,0,0,.15);
    }

    /* Intelligent comment */
    .intelligent-comment-section {
        border-left:3px solid #28a745; background-color:#f8fff8 !important;
        margin-bottom:10px; border-radius:6px; padding:8px;
    }
    .intelligent-comment-preview {
        font-size:.8rem; line-height:1.4; white-space:pre-wrap;
        background:#fff; border:1px solid #dee2e6; border-radius:6px; padding:8px; margin-top:5px;
    }
    .intelligent-comment-text   { color:#155724; font-weight:500; }
    .intelligent-comment-badge  { font-size:.7rem; padding:2px 6px; margin-left:6px; }
    .intelligent-option {
        background-color:#e8f5e8 !important; border-top:2px solid #28a745 !important;
        font-weight:600 !important; color:#155724 !important; margin-top:5px;
    }

    /* Saved comment preview */
    .saved-comment-preview {
        background-color:#f8fff8 !important; border-left:3px solid #28a745;
        border-radius:6px; padding:8px; font-size:.8rem; line-height:1.4;
        white-space:pre-wrap; max-height:100px; overflow-y:auto;
    }

    /* Grades tooltip */
    .grades-tooltip {
        position:fixed; background:#fff; border:2px solid #667eea; border-radius:16px;
        box-shadow:0 20px 60px rgba(0,0,0,.3); width:480px; max-height:550px;
        overflow:hidden; z-index:10050; opacity:0; visibility:hidden;
        transition:all .3s cubic-bezier(.4,0,.2,1); pointer-events:none;
    }
    .grades-tooltip.show { opacity:1; visibility:visible; pointer-events:auto; animation:tooltipFadeIn .3s ease-out; }
    @keyframes tooltipFadeIn {
        from { opacity:0; transform:translate(-50%,-48%) scale(.95); }
        to   { opacity:1; transform:translate(-50%,-50%) scale(1); }
    }
    .grades-tooltip.position-bottom { bottom:15%; left:50%; transform:translateX(-50%); }
    .grades-tooltip .tooltip-header {
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff;
        padding:16px 50px 16px 20px; font-weight:700; font-size:1.1rem;
        border-radius:14px 14px 0 0; margin:-2px -2px 15px -2px;
        position:relative; display:flex; align-items:center;
    }
    .grades-tooltip .tooltip-close {
        position:absolute; right:15px; top:50%; transform:translateY(-50%);
        width:30px; height:30px; border-radius:50%;
        background:rgba(255,255,255,.2); color:#fff; font-size:1.2rem;
        display:flex; align-items:center; justify-content:center;
        cursor:pointer; border:none;
    }
    .grades-tooltip .tooltip-body { padding:0 15px 15px 15px; max-height:420px; overflow-y:auto; }
    .grades-tooltip table        { width:100%; border-collapse:separate; border-spacing:0 6px; }
    .grades-tooltip th { color:#6c757d; font-weight:600; font-size:.8rem; padding:10px 6px; border-bottom:2px solid #e9ecef; }
    .grades-tooltip td { padding:10px 6px; background:#f8f9fa; border-radius:8px; font-size:.85rem; }
    .grade-badge { font-weight:700; padding:4px 12px; border-radius:20px; font-size:.8rem; min-width:45px; text-align:center; display:inline-block; }

    /* Student cards (mobile) */
    .student-card  { background:#fff; border:1px solid #dee2e6; border-radius:12px; margin-bottom:1.5rem; box-shadow:0 2px 8px rgba(0,0,0,.08); overflow:hidden; }
    .student-header { background:#f8f9fa; padding:12px 15px; border-bottom:1px solid #dee2e6; }
    .student-info   { display:flex; align-items:center; gap:12px; }
    .student-details h6 { margin:0; font-size:1rem; font-weight:600; }
    .student-meta   { font-size:.8rem; color:#6c757d; }
    .student-body   { padding:15px; }
    .subjects-grid  { display:grid; grid-template-columns:repeat(auto-fill,minmax(100px,1fr)); gap:10px; margin-bottom:20px; }
    .subject-item   { text-align:center; padding:10px 6px; background:#f8f9fa; border-radius:10px; border:1px solid #e9ecef; }
    .subject-name   { font-size:.7rem; font-weight:700; color:#495057; margin-bottom:6px; line-height:1.2; }

    .performance-summary { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-radius:12px; padding:15px; margin-bottom:15px; color:#fff; }
    .summary-title { font-weight:600; font-size:.85rem; margin-bottom:12px; display:flex; align-items:center; gap:6px; }
    .summary-grid  { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; text-align:center; }
    .summary-item  { padding:8px; background:rgba(255,255,255,.15); border-radius:8px; backdrop-filter:blur(5px); }
    .summary-label { font-size:.65rem; opacity:.9; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px; }
    .summary-value { font-size:1.2rem; font-weight:700; }

    .comment-label-mobile { font-weight:600; margin-bottom:8px; font-size:.9rem; color:#495057; display:flex; align-items:center; gap:8px; }
    .btn-save-all { padding:10px 24px; font-weight:600; border-radius:8px; }

    .cumulative-badge { background:linear-gradient(135deg,#17a2b8,#0d6efd); color:#fff; font-size:.7rem; padding:3px 10px; border-radius:20px; margin-left:8px; }
    .term-badge       { background:#6c757d; color:#fff; font-size:.7rem; padding:3px 10px; border-radius:20px; margin-left:5px; }

    .split-score { display:flex; flex-direction:column; align-items:center; gap:5px; }
    .term-score-box { background:#fff; padding:4px 8px; border-radius:8px; width:100%; box-shadow:0 1px 3px rgba(0,0,0,.1); }
    .cum-score-box  { background:#e8f4f8; padding:4px 8px; border-radius:8px; width:100%; }
    .score-divider  { font-size:.7rem; color:#adb5bd; }

    .stat-card { background:#fff; border-radius:10px; padding:10px; text-align:center; box-shadow:0 2px 4px rgba(0,0,0,.05); }

    @media (min-width: 1200px) {
        .desktop-table { display:block !important; }
        .mobile-cards  { display:none  !important; }
        .comment-info-icon { display:flex !important; }
    }
    @media (max-width: 1199.98px) {
        .desktop-table { display:none  !important; }
        .mobile-cards  { display:block !important; }
        .comment-info-icon { display:none !important; }
    }

    .spin-icon { animation:spin 1s linear infinite; }
    @keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page title --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('myprincipalscomment.index') }}">My Assignments</a></li>
                                <li class="breadcrumb-item active">Broadsheet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flash messages --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($students->isNotEmpty())
                <form id="commentsForm"
                      action="{{ route('myprincipalscomment.updateComments', [$schoolclassid, $sessionid, $termid]) }}"
                      method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                {{-- Card header --}}
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <h5 class="card-title mb-0">
                                            <i class="ri-bar-chart-2-line me-2"></i>
                                            <strong>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }}</strong>
                                            — {{ $schoolterm }} {{ $schoolsession }}
                                            @if($isSenior)
                                                <span class="badge bg-warning ms-2">Senior Class (A1–F9)</span>
                                            @else
                                                <span class="badge bg-info ms-2">Junior Class (A–F)</span>
                                            @endif
                                        </h5>
                                        <div class="mt-2 mt-sm-0">
                                            <span class="badge bg-primary cumulative-badge">
                                                <i class="ri-bar-chart-line"></i> Cumulative Score
                                            </span>
                                            <span class="badge bg-secondary term-badge">
                                                <i class="ri-calendar-line"></i> Term Score
                                            </span>
                                        </div>
                                    </div>

                                    <div class="alert alert-light mt-3 mb-0 border">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <i class="ri-bar-chart-line me-2 text-primary"></i>
                                                <strong>Class Cumulative Average:</strong>
                                                <span class="badge bg-primary">{{ $classAnalytics['average'] }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <i class="ri-group-line me-2 text-info"></i>
                                                <strong>Total Students:</strong>
                                                <span class="badge bg-info">{{ $classAnalytics['total_students'] }}</span>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                @if(in_array($schoolterm, ['2nd Term', 'Second Term']))
                                                    <span class="text-success"><i class="ri-information-line"></i> Cumulative = Average of 1st &amp; 2nd Terms</span>
                                                @elseif(in_array($schoolterm, ['3rd Term', 'Third Term']))
                                                    <span class="text-success"><i class="ri-information-line"></i> Cumulative = Average of 1st, 2nd &amp; 3rd Terms</span>
                                                @else
                                                    <span class="text-success"><i class="ri-information-line"></i> Cumulative = Term Score</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>{{-- /card-header --}}

                                <div class="card-body">
                                    {{-- Search --}}
                                    <div class="search-box mb-4">
                                        <input type="text" class="form-control" id="searchInput"
                                               placeholder="🔍 Search students by name or admission number…">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>

                                    {{-- ========================================================
                                         DESKTOP TABLE
                                    ======================================================== --}}
                                    <div class="desktop-table">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle">
                                                <thead>
                                                    <tr class="subject-header">
                                                        <th rowspan="2" style="vertical-align:middle;width:40px;">#</th>
                                                        <th rowspan="2" style="vertical-align:middle;width:100px;">Admission No</th>
                                                        <th rowspan="2" style="vertical-align:middle;width:180px;">Student Name</th>
                                                        <th rowspan="2" style="vertical-align:middle;width:70px;">Gender</th>
                                                        <th colspan="{{ count($subjects) }}" class="text-center"
                                                            style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;">
                                                            <i class="ri-book-open-line me-2"></i>
                                                            Subjects Performance (Term Score | Cumulative Score)
                                                        </th>
                                                        <th rowspan="2" style="vertical-align:middle;min-width:280px;">Principal's Comment</th>
                                                    </tr>
                                                    <tr class="score-header">
                                                        @foreach ($subjects as $subject)
                                                            <th class="text-center" style="min-width:110px;background:#f8f9fa;">
                                                                <div>{{ $subject }}</div>
                                                                <div class="small mt-1">
                                                                    <span class="badge bg-secondary">Term</span>
                                                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                                                    <span class="badge bg-primary">Cum</span>
                                                                </div>
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($students as $index => $student)
                                                        @php
                                                            $sid     = $student->id;
                                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                            $imgPath = asset('storage/student_avatars/' . $picture);

                                                            $currentComment      = $profiles[$sid] ?? '';
                                                            $currentCommentPlain = strip_tags($currentComment);
                                                            $intelligentComment  = $intelligentComments[$sid] ?? '';
                                                            $hasWeakAdvice       = !empty($studentGradeAnalysis[$sid]['weak_subjects'] ?? []);
                                                            $analytics           = $studentAnalytics[$sid] ?? [];
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

                                                            {{-- Subject score cells --}}
                                                            @foreach ($subjects as $subject)
                                                                @php
                                                                    $termTotal = $termScoreMap[$sid][$subject] ?? 0;
                                                                    $cumTotal  = $cumScoreMap[$sid][$subject]  ?? 0;

                                                                    $termClass = $termTotal < 40 ? 'highlight-red'
                                                                               : ($termTotal < 50 ? 'highlight-orange'
                                                                               : ($termTotal >= 70 ? 'highlight-green' : ''));

                                                                    $cumClass  = $cumTotal < 40 ? 'highlight-red'
                                                                               : ($cumTotal < 50 ? 'highlight-orange'
                                                                               : ($cumTotal >= 70 ? 'highlight-green' : ''));

                                                                    $cumGrade = '';
                                                                    if ($cumTotal > 0) {
                                                                        if ($isSenior) {
                                                                            if      ($cumTotal >= 75) $cumGrade = 'A1';
                                                                            elseif  ($cumTotal >= 70) $cumGrade = 'B2';
                                                                            elseif  ($cumTotal >= 65) $cumGrade = 'B3';
                                                                            elseif  ($cumTotal >= 60) $cumGrade = 'C4';
                                                                            elseif  ($cumTotal >= 55) $cumGrade = 'C5';
                                                                            elseif  ($cumTotal >= 50) $cumGrade = 'C6';
                                                                            elseif  ($cumTotal >= 45) $cumGrade = 'D7';
                                                                            elseif  ($cumTotal >= 40) $cumGrade = 'E8';
                                                                            else                      $cumGrade = 'F9';
                                                                        } else {
                                                                            if      ($cumTotal >= 70) $cumGrade = 'A';
                                                                            elseif  ($cumTotal >= 60) $cumGrade = 'B';
                                                                            elseif  ($cumTotal >= 50) $cumGrade = 'C';
                                                                            elseif  ($cumTotal >= 40) $cumGrade = 'D';
                                                                            else                      $cumGrade = 'F';
                                                                        }
                                                                    }
                                                                @endphp
                                                                <td class="p-2">
                                                                    <div class="subject-score-card">
                                                                        <div class="split-score">
                                                                            <div class="term-score-box">
                                                                                <span class="term-label">TERM</span>
                                                                                <div class="term-score {{ $termClass }}">
                                                                                    {{ $termTotal ?: '—' }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="score-divider">▼</div>
                                                                            <div class="cum-score-box">
                                                                                <span class="cumulative-label">CUMULATIVE</span>
                                                                                <div class="cumulative-score {{ $cumClass }}">
                                                                                    {{ $cumTotal ?: '—' }}
                                                                                    @if($cumGrade)
                                                                                        <span class="grade-badge-sm grade-{{ strtolower($cumGrade) }}">
                                                                                            {{ $cumGrade }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            @endforeach

                                                            {{-- Comment cell --}}
                                                            <td class="comment-cell position-relative" style="min-width:280px;">

                                                                @if($intelligentComment)
                                                                    <div class="intelligent-comment-section mb-2">
                                                                        <small class="text-muted d-block mb-1">
                                                                            <i class="ri-lightbulb-line text-success"></i>
                                                                            <strong>AI Suggested Comment</strong>
                                                                            @if($hasWeakAdvice)
                                                                                <span class="badge bg-warning intelligent-comment-badge">Includes Advice</span>
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

                                                                <select class="form-select form-select-sm teacher-comment-dropdown auto-save-comment"
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
                                                                        $intPlain   = strip_tags($intelligentComments[$sid] ?? '');
                                                                        $stdPlains  = array_map('strip_tags', $standardPersonalizedComments[$sid] ?? []);
                                                                        $showAI     = $intPlain && !in_array($intPlain, $stdPlains);
                                                                    @endphp
                                                                    @if($showAI)
                                                                        <option value="{{ $intPlain }}"
                                                                                class="intelligent-option"
                                                                            {{ $currentCommentPlain === $intPlain ? 'selected' : '' }}>
                                                                            💡 Use AI Generated Comment
                                                                        </option>
                                                                    @endif
                                                                </select>

                                                                <button type="button"
                                                                        class="comment-info-icon grades-trigger btn btn-link p-0"
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
                                                                        <div class="text-center mb-3 p-2 bg-light rounded">
                                                                            <small class="text-muted">
                                                                                Class Average: <strong>{{ $classAnalytics['average'] }}</strong>
                                                                            </small>
                                                                            @php $diff = ($analytics['average'] ?? 0) - $classAnalytics['average']; @endphp
                                                                            @if($diff > 0.5)
                                                                                <span class="text-success ms-2"><i class="ri-arrow-up-line"></i> +{{ round($diff,1) }}</span>
                                                                            @elseif($diff < -0.5)
                                                                                <span class="text-danger ms-2"><i class="ri-arrow-down-line"></i> {{ round($diff,1) }}</span>
                                                                            @endif
                                                                        </div>
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Subject</th>
                                                                                    <th>Term</th>
                                                                                    <th>Cumulative</th>
                                                                                    <th>Grade</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="grades-body-{{ $sid }}"></tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>{{-- /grades-tooltip --}}

                                                            </td>{{-- /comment-cell --}}
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>{{-- /desktop-table --}}

                                    {{-- ========================================================
                                         MOBILE CARDS
                                    ======================================================== --}}
                                    <div class="mobile-cards">
                                        @foreach ($students as $student)
                                            @php
                                                $sid     = $student->id;
                                                $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                $imgPath = asset('storage/student_avatars/' . $picture);

                                                $currentComment      = $profiles[$sid] ?? '';
                                                $currentCommentPlain = strip_tags($currentComment);
                                                $intelligentComment  = $intelligentComments[$sid] ?? '';
                                                $analytics           = $studentAnalytics[$sid] ?? [];
                                                $myAvg     = $analytics['average'] ?? 0;
                                                $myTermAvg = $analytics['term_average'] ?? 0;
                                                $diff      = $myAvg - $classAnalytics['average'];
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
                                                        <div class="text-center mt-2 small">
                                                            @if($diff > 0.5)
                                                                <span class="text-success"><i class="ri-arrow-up-line"></i> +{{ round(abs($diff),1) }} above average</span>
                                                            @elseif($diff < -0.5)
                                                                <span class="text-warning"><i class="ri-arrow-down-line"></i> {{ round(abs($diff),1) }} below average</span>
                                                            @else
                                                                <span class="text-white-50">At class average</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="subjects-grid">
                                                        @foreach ($subjects as $subject)
                                                            @php
                                                                $termTotal = $termScoreMap[$sid][$subject] ?? 0;
                                                                $cumTotal  = $cumScoreMap[$sid][$subject]  ?? 0;
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
                                                        <label class="comment-label-mobile">
                                                            <i class="ri-chat-3-line"></i> Principal's Comment
                                                        </label>
                                                        <select class="form-select form-select-sm auto-save-comment"
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
                                                                $intPlain  = strip_tags($intelligentComments[$sid] ?? '');
                                                                $stdPlains = array_map('strip_tags', $standardPersonalizedComments[$sid] ?? []);
                                                            @endphp
                                                            @if($intPlain && !in_array($intPlain, $stdPlains))
                                                                <option value="{{ $intPlain }}"
                                                                    {{ $currentCommentPlain === $intPlain ? 'selected' : '' }}>
                                                                    💡 Use AI Generated Comment
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>{{-- /mobile-cards --}}

                                    {{-- Save all --}}
                                    <div class="row mt-4">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary btn-save-all" id="saveAllBtn">
                                                <i class="ri-save-line me-1"></i> Save All Comments
                                            </button>
                                            <span id="savingIndicator" class="ms-2 text-muted" style="display:none;">
                                                <i class="ri-loader-4-line spin-icon me-1"></i> Saving…
                                            </span>
                                        </div>
                                    </div>

                                </div>{{-- /card-body --}}
                            </div>{{-- /card --}}
                        </div>
                    </div>
                </form>
            @else
                <div class="alert alert-info text-center">
                    <i class="ri-information-line me-2"></i>
                    No students enrolled in this class for the selected session and term.
                </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script>
window.studentGradesData = @json($studentGrades);
let activeTooltip = null;

/* ── Utilities ──────────────────────────────────────────────────────────── */
function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text ?? '';
    return d.innerHTML;
}

function showToast(message, type = 'info') {
    document.querySelector('.auto-save-toast')?.remove();
    const icon  = type === 'success' ? 'checkbox-circle' : 'information';
    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show`;
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-${icon}-fill me-2 fs-5"></i>
            <span>${escapeHtml(message)}</span>
            <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
        </div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

/* ── Tooltip helpers ─────────────────────────────────────────────────────── */
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

    const grades = window.studentGradesData[studentId] || [];
    const tbody  = document.getElementById(`grades-body-${studentId}`);
    if (tbody) {
        tbody.innerHTML = '';
        if (!grades.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No grades available</td></tr>';
        } else {
            grades.forEach(g => {
                const letterMap = { A: 'grade-a', B: 'grade-b', C: 'grade-c', D: 'grade-d', E: 'grade-e', F: 'grade-f' };
                const gc  = letterMap[g.grade_letter] || 'grade-f';
                const tc  = (g.term_score < 50) ? 'text-danger' : 'text-success';
                const cc  = (g.score < 50)      ? 'text-danger' : 'text-success';
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${escapeHtml(g.subject)}</strong></td>
                    <td class="text-center fw-bold ${tc}">${g.term_score || '—'}</td>
                    <td class="text-center fw-bold ${cc}">${g.score || '—'}</td>
                    <td class="text-center"><span class="grade-badge ${gc}">${escapeHtml(g.grade)}</span></td>`;
                tbody.appendChild(row);
            });
        }
    }

    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

/* ── Update saved-comment UI (no page reload) ────────────────────────────── */
function updateCommentUI(studentId, comment) {
    document.querySelectorAll(`[data-student-id="${studentId}"]`).forEach(container => {
        // Desktop: "Comment saved" badge in name cell
        const nameDiv = container.querySelector('td:nth-child(3) div > div');
        if (nameDiv) {
            let badge = nameDiv.querySelector('.text-success');
            if (comment && !badge) {
                const s = document.createElement('small');
                s.className = 'd-block text-success mt-1';
                s.innerHTML = '<i class="ri-check-double-line"></i> Comment saved';
                nameDiv.appendChild(s);
            } else if (!comment && badge) {
                badge.remove();
            }
        }

        // Desktop: saved-comment preview box
        const commentCell = container.querySelector('.comment-cell');
        if (commentCell) {
            // The wrapper is the .mb-2 div that is NOT the intelligent-comment-section
            let previewWrapper = null;
            commentCell.querySelectorAll('.mb-2').forEach(el => {
                if (!el.classList.contains('intelligent-comment-section')) previewWrapper = el;
            });

            if (comment) {
                const previewText = comment.length > 100 ? comment.substring(0, 100) + '…' : comment;
                if (previewWrapper) {
                    const box = previewWrapper.querySelector('.saved-comment-preview small');
                    if (box) box.textContent = previewText;
                } else {
                    const newWrap = document.createElement('div');
                    newWrap.className = 'mb-2';
                    newWrap.innerHTML = `
                        <small class="text-success d-block mb-1">
                            <i class="ri-chat-check-line"></i> <strong>Saved Comment</strong>
                        </small>
                        <div class="saved-comment-preview">
                            <small class="text-secondary">${escapeHtml(previewText)}</small>
                        </div>`;
                    const sel = commentCell.querySelector('select');
                    if (sel) commentCell.insertBefore(newWrap, sel);
                }
            } else if (previewWrapper) {
                previewWrapper.remove();
            }
        }

        // Mobile: checkmark badge
        const mobileH6 = container.querySelector('.student-details h6');
        if (mobileH6) {
            let mb = mobileH6.querySelector('.badge.bg-success');
            if (comment && !mb) {
                const b = document.createElement('span');
                b.className = 'badge bg-success ms-2';
                b.textContent = '✓';
                mobileH6.appendChild(b);
            } else if (!comment && mb) {
                mb.remove();
            }
        }
    });
}

/* ── Auto-save on dropdown change ────────────────────────────────────────── */
document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function () {
        const studentId = this.dataset.studentId;
        const comment   = this.value.trim();
        const original  = this.dataset.originalValue || '';
        if (comment === original) return;

        this.style.backgroundColor = '#fff3cd';
        this.disabled = true;
        const self = this;

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
            // Sync all dropdowns (desktop + mobile) for this student
            document.querySelectorAll(`.auto-save-comment[data-student-id="${studentId}"]`).forEach(s => {
                s.value = comment;
                s.dataset.originalValue = comment;
            });
            updateCommentUI(studentId, comment);
            self.style.backgroundColor = '#d1e7dd';
            self.disabled = false;
            showToast('Comment saved!', 'success');
            setTimeout(() => { self.style.backgroundColor = ''; }, 1500);
        })
        .catch(err => {
            console.error('Auto-save error:', err);
            self.value = original;
            self.style.backgroundColor = '#f8d7da';
            self.disabled = false;
            showToast('Error: ' + err.message, 'danger');
            setTimeout(() => { self.style.backgroundColor = ''; }, 2000);
        });
    });
});

/* ── Bulk save ───────────────────────────────────────────────────────────── */
const commentsForm = document.getElementById('commentsForm');
if (commentsForm) {
    commentsForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn  = document.getElementById('saveAllBtn');
        const ind  = document.getElementById('savingIndicator');
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line spin-icon me-1"></i> Saving All Comments…';
        ind.style.display = 'inline-block';

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');

        document.querySelectorAll('.auto-save-comment').forEach(sel => {
            if (sel.offsetParent === null) return; // skip hidden duplicates
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
                if (sel.offsetParent === null) return;
                const val = sel.value.trim();
                if (val) {
                    sel.dataset.originalValue = val;
                    updateCommentUI(sel.dataset.studentId, val);
                }
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

/* ── Tooltip triggers (desktop only) ────────────────────────────────────── */
if (window.innerWidth > 1199) {
    document.querySelectorAll('.grades-trigger').forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const sid  = this.dataset.studentId;
            const name = this.dataset.studentName;
            const tid  = `tooltip-${sid}`;
            activeTooltip === tid ? closeAllTooltips() : showTooltip(tid, sid, name);
        });
    });

    document.querySelectorAll('.tooltip-close').forEach(btn => {
        btn.addEventListener('click', closeAllTooltips);
    });

    document.addEventListener('click', e => {
        if (!activeTooltip) return;
        const activeEl  = document.getElementById(activeTooltip);
        const activeSid = activeTooltip.replace('tooltip-', '');
        const trigger   = document.querySelector(`.grades-trigger[data-student-id="${activeSid}"]`);
        if (activeEl && !activeEl.contains(e.target) && (!trigger || !trigger.contains(e.target))) {
            closeAllTooltips();
        }
    });

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllTooltips(); });
}

/* ── Search ──────────────────────────────────────────────────────────────── */
document.getElementById('searchInput')?.addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.desktop-table tbody tr').forEach(row => {
        row.style.display = (!term || row.textContent.toLowerCase().includes(term)) ? '' : 'none';
    });
    document.querySelectorAll('.mobile-cards .student-card').forEach(card => {
        card.style.display = (!term || card.textContent.toLowerCase().includes(term)) ? '' : 'none';
    });
});

/* ── Init original-value trackers ───────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.auto-save-comment').forEach(s => {
        s.dataset.originalValue = s.value;
    });
});
</script>
@endpush
@endsection
