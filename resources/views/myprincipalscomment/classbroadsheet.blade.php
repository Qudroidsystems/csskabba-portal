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

/* ── Scoring Mode Toggle ── */
.scoring-mode-bar {
    background: #fff;
    border: 1px solid var(--principal-border);
    border-radius: var(--principal-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: var(--principal-shadow);
}
.scoring-mode-bar .mode-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--principal-primary);
    white-space: nowrap;
}
.scoring-mode-toggle {
    display: flex;
    background: var(--principal-bg);
    border: 1.5px solid var(--principal-border);
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}
.scoring-mode-toggle .mode-btn {
    padding: 7px 18px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    background: transparent;
    color: var(--principal-muted);
    cursor: pointer;
    transition: all .2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.scoring-mode-toggle .mode-btn:hover {
    background: #e9ecef;
    color: var(--principal-primary);
}
.scoring-mode-toggle .mode-btn.active {
    background: var(--principal-primary);
    color: #fff;
}
.scoring-mode-toggle .mode-btn.active-term {
    background: #0891b2;
    color: #fff;
}
.mode-hint {
    font-size: 12px;
    color: var(--principal-muted);
    background: var(--principal-bg);
    border: 1px dashed var(--principal-border);
    border-radius: 6px;
    padding: 5px 10px;
}
.mode-hint strong {
    color: var(--principal-primary);
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
.principal-badge-mode-cum {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.principal-badge-mode-term {
    background: linear-gradient(135deg, #0891b2, #06b6d4);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

/* Clickable Avatar Styles */
.avatar-clickable {
    cursor: pointer;
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.avatar-clickable:hover {
    transform: scale(1.1);
    opacity: 0.9;
}
.student-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--principal-border);
    background: #f0f0f0;
}
.avatar-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    color: white;
    border: 2px solid var(--principal-border);
    cursor: pointer;
    transition: transform 0.2s ease;
}
.avatar-placeholder:hover {
    transform: scale(1.1);
}

/* Image Zoom Modal */
.image-zoom-modal .modal-content {
    background: transparent;
    border: none;
    box-shadow: none;
}
.image-zoom-modal .modal-dialog {
    max-width: 90vw;
    margin: 1.75rem auto;
}
.image-zoom-modal .modal-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 20px;
}
.zoomed-image {
    max-width: 90vw;
    max-height: 75vh;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    border: 4px solid white;
    cursor: pointer;
    animation: zoomIn 0.3s ease;
    object-fit: contain;
}
@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.8); }
    to   { opacity: 1; transform: scale(1);   }
}
.image-zoom-modal .btn-close {
    position: absolute;
    top: 20px;
    right: 30px;
    background-color: rgba(0,0,0,0.7);
    border-radius: 50%;
    padding: 12px;
    opacity: 1;
    z-index: 1060;
    filter: brightness(0) invert(1);
}
.image-zoom-modal .btn-close:hover {
    background-color: rgba(0,0,0,0.9);
    transform: scale(1.1);
}
.zoomed-image-name {
    color: white;
    margin-top: 20px;
    font-size: 18px;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    background: rgba(0,0,0,0.5);
    padding: 8px 20px;
    border-radius: 40px;
    display: inline-block;
}
.zoomed-image-details {
    color: rgba(255,255,255,0.8);
    margin-top: 8px;
    font-size: 14px;
    text-align: center;
}

/* ── Dual-Score Subject Card ── */
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
.subject-score-card .score-subject-name {
    font-size: .65rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 6px;
    line-height: 1.2;
}
/* Term row */
.score-row-term {
    background: rgba(8, 145, 178, .07);
    border-radius: 6px;
    padding: 4px 5px;
    margin-bottom: 4px;
    border-left: 3px solid #0891b2;
}
.score-row-term .score-type-label {
    font-size: .55rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #0891b2;
    margin-bottom: 1px;
}
/* Cumulative row */
.score-row-cum {
    background: rgba(30, 58, 95, .07);
    border-radius: 6px;
    padding: 4px 5px;
    border-left: 3px solid var(--principal-primary);
}
.score-row-cum .score-type-label {
    font-size: .55rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--principal-primary);
    margin-bottom: 1px;
}
.score-value {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
}
.score-grade-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 10px;
    font-size: .6rem;
    font-weight: 700;
    margin-left: 3px;
    vertical-align: middle;
}

/* Active mode highlight on card */
.subject-score-card.mode-cum .score-row-cum {
    background: rgba(30, 58, 95, .13);
    box-shadow: 0 1px 4px rgba(30,58,95,.1);
}
.subject-score-card.mode-term .score-row-term {
    background: rgba(8, 145, 178, .14);
    box-shadow: 0 1px 4px rgba(8,145,178,.12);
}

/* Grade colour system */
.grade-a, .grade-a1 { background: #16a34a; color: #fff; }
.grade-b, .grade-b2, .grade-b3 { background: #0891b2; color: #fff; }
.grade-c, .grade-c4, .grade-c5, .grade-c6 { background: #6b7280; color: #fff; }
.grade-d, .grade-d7 { background: #d97706; color: #fff; }
.grade-e, .grade-e8 { background: #ea580c; color: #fff; }
.grade-f, .grade-f9 { background: #dc2626; color: #fff; }

.highlight-red    { color: #dc2626 !important; font-weight: 600; }
.highlight-orange { color: #ea580c !important; font-weight: 600; }
.highlight-green  { color: #16a34a !important; font-weight: 600; }

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
    width: 520px;
    max-height: 580px;
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
    from { opacity: 0; transform: translate(-50%,-48%) scale(.95); }
    to   { opacity: 1; transform: translate(-50%,-50%) scale(1);   }
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
    max-height: 450px;
    overflow-y: auto;
}

/* Tooltip grade table column headers */
.tooltip-grade-header {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.tooltip-col-term  { color: #0891b2; }
.tooltip-col-cum   { color: var(--principal-primary); }
.tooltip-col-tgrade  { color: #0891b2; }
.tooltip-col-cgrade  { color: var(--principal-primary); }

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
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.2s ease;
    border: 2px solid var(--principal-border);
}
.avatar-sm:hover {
    transform: scale(1.1);
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
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
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
    .desktop-table  { display: block !important; }
    .mobile-cards   { display: none  !important; }
}
@media (max-width: 1199.98px) {
    .desktop-table  { display: none  !important; }
    .mobile-cards   { display: block !important; }
}

.spin-icon {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
/* Enhanced Auto-save Feedback */
.class-arm-badge {
    font-size: 10px;
    padding: 2px 7px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 12px;
    font-weight: 600;
    margin-left: 6px;
}

.comment-saved-badge {
    font-size: 11px;
    color: #16a34a;
}

.auto-save-comment {
    transition: all 0.3s ease;
}
.class-arm-badge {
    font-size: 10px;
    padding: 2px 7px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 12px;
    font-weight: 600;
    margin-left: 6px;
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

            {{-- ── Scoring Mode Toggle Bar ── --}}
            <div class="scoring-mode-bar">
                <span class="mode-label">
                    <i class="ri-bar-chart-grouped-line me-1"></i>
                    Grading & Comment Mode:
                </span>
                <div class="scoring-mode-toggle">
                    <a href="{{ request()->fullUrlWithQuery(['scoring_mode' => 'cumulative']) }}"
                       class="mode-btn {{ $scoringMode === 'cumulative' ? 'active' : '' }}">
                        <i class="ri-bar-chart-line"></i> Cumulative Score
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['scoring_mode' => 'term']) }}"
                       class="mode-btn {{ $scoringMode === 'term' ? 'active-term' : '' }}">
                        <i class="ri-calendar-check-line"></i> Term Score
                    </a>
                </div>
                <span class="mode-hint">
                    @if($scoringMode === 'cumulative')
                        <i class="ri-information-line text-primary"></i>
                        Grades &amp; comments are based on <strong>Cumulative scores</strong>
                    @else
                        <i class="ri-information-line text-info"></i>
                        Grades &amp; comments are based on <strong>Term scores only</strong>
                    @endif
                </span>
                {{-- Current mode badge --}}
                @if($scoringMode === 'cumulative')
                    <span class="principal-badge-mode-cum ms-auto">
                        <i class="ri-bar-chart-line me-1"></i> Cumulative Mode Active
                    </span>
                @else
                    <span class="principal-badge-mode-term ms-auto">
                        <i class="ri-calendar-check-line me-1"></i> Term Mode Active
                    </span>
                @endif
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
                        <div class="stat-value {{ $scoringMode === 'term' ? 'text-info' : 'text-success' }}">
                            {{ $classAnalytics['average'] }}
                        </div>
                        <div class="stat-label">
                            Class Average
                            <span class="badge {{ $scoringMode === 'term' ? 'bg-info' : 'bg-primary' }} ms-1" style="font-size:.65rem;">
                                {{ $scoringMode === 'term' ? 'Term' : 'Cumulative' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-award-line"></i></div>
                        <div class="stat-value text-warning">
                            @php
                                $topStudent = $students->sortByDesc(fn($s) =>
                                    $scoringMode === 'term'
                                        ? ($studentAnalytics[$s->id]['term_average'] ?? 0)
                                        : ($studentAnalytics[$s->id]['average'] ?? 0)
                                )->first();
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
                                <div class="mt-2 mt-sm-0 d-flex gap-1 flex-wrap">
                                    <span class="principal-badge" style="background:#e0f2fe;color:#0891b2;">
                                        <i class="ri-calendar-check-line me-1"></i> Term Score / Grade
                                    </span>
                                    <span class="principal-badge principal-badge-cumulative">
                                        <i class="ri-bar-chart-line me-1"></i> Cumulative Score / Grade
                                    </span>
                                    @if($scoringMode === 'cumulative')
                                        <span class="principal-badge" style="background:#dcfce7;color:#16a34a;">
                                            <i class="ri-checkbox-circle-line me-1"></i> Grading: Cumulative
                                        </span>
                                    @else
                                        <span class="principal-badge" style="background:#cffafe;color:#0891b2;">
                                            <i class="ri-checkbox-circle-line me-1"></i> Grading: Term
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- Search --}}
                            <div class="search-box mb-4">
                                <input type="text" class="form-control" id="searchInput"
                                       placeholder="🔍 Search students by name or admission number…"
                                       style="border: 1.5px solid var(--principal-border); border-radius: 8px;">
                            </div>

                            {{-- ============================================================
                                 DESKTOP TABLE
                            ============================================================ --}}
                            <div class="desktop-table">
                                <div class="table-responsive">
                                    <table class="table principal-table w-100 mb-0">
                                        <thead>
                                            <tr>
                                                <th width="40">#</th>
                                                <th width="60">Photo</th>
                                                <th width="100">Admission No</th>
                                                <th width="180">Student Name</th>
                                                <th width="70">Gender</th>
                                                <th class="text-center">
                                                    <i class="ri-book-open-line me-1"></i>
                                                    Subjects Performance
                                                    <small class="d-block fw-normal opacity-75" style="font-size:.65rem;margin-top:2px;">
                                                        <span style="color:#90cdf4;">■ Term Score/Grade</span> &nbsp;
                                                        <span style="color:#bfdbfe;">■ Cumulative Score/Grade</span>
                                                    </small>
                                                </th>
                                                <th width="300">Principal's Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $index => $student)
                                                @php
                                                    $sid = $student->id;
                                                    $avatarUrl = null;
                                                    if(isset($student->picture) && $student->picture && $student->picture != 'unnamed.jpg' && $student->picture != '') {
                                                        $avatarUrl = asset('storage/student_avatars/' . $student->picture);
                                                    }
                                                    $fullName = trim($student->lastname . ' ' . $student->fname);
                                                    $otherName = $student->othername ?? '';
                                                    $fullNameWithOther = trim($fullName . ($otherName ? ' (' . $otherName . ')' : ''));
                                                    $initials = strtoupper(substr($student->fname, 0, 1) . substr($student->lastname, 0, 1));
                                                    if(empty($initials)) $initials = 'ST';

                                                    $currentComment = $profiles[$sid] ?? '';
                                                    $currentCommentPlain = strip_tags($currentComment);
                                                    $intelligentComment = $intelligentComments[$sid] ?? '';
                                                    $hasWeakAdvice = !empty($studentGradeAnalysis[$sid]['weak_subjects'] ?? []);
                                                    $analytics = $studentAnalytics[$sid] ?? [];
                                                @endphp
                                                <tr data-student-id="{{ $sid }}" class="student-row">
                                                    <td class="fw-bold">{{ $index + 1 }}</td>

                                                    {{-- Photo --}}
                                                    <td class="text-center">
                                                        @if($avatarUrl)
                                                            <img src="{{ $avatarUrl }}"
                                                                 alt="{{ $fullNameWithOther }}"
                                                                 class="student-avatar avatar-clickable"
                                                                 data-bs-toggle="modal"
                                                                 data-bs-target="#imageZoomModal"
                                                                 data-image="{{ $avatarUrl }}"
                                                                 data-name="{{ $fullNameWithOther }}"
                                                                 data-admission="{{ $student->admissionNo ?? 'N/A' }}"
                                                                 data-class="{{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }}"
                                                                 data-gender="{{ $student->gender ?? 'N/A' }}">
                                                        @else
                                                            <div class="avatar-placeholder avatar-clickable"
                                                                 data-bs-toggle="modal"
                                                                 data-bs-target="#imageZoomModal"
                                                                 data-name="{{ $fullNameWithOther }}"
                                                                 data-admission="{{ $student->admissionNo ?? 'N/A' }}"
                                                                 data-class="{{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }}"
                                                                 data-gender="{{ $student->gender ?? 'N/A' }}"
                                                                 data-initials="{{ $initials }}">
                                                                {{ $initials }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td>{{ $student->admissionNo }}</td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $fullNameWithOther }}</strong>
                                                            <span class="class-arm-badge">{{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }}</span>

                                                            @if($currentComment)
                                                                <small class="comment-saved-badge d-block mt-1">
                                                                    <i class="ri-check-double-line"></i> Comment Saved
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                     <td>
                                                        <span class="badge bg-light text-dark">
                                                            <i class="ri-{{ $student->gender === 'Male' ? 'male' : 'female' }}-line"></i>
                                                            {{ $student->gender ?? 'N/A' }}
                                                        </span>
                                                    </td>

                                                    {{-- ── Subject Scores — dual rows ── --}}
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach ($subjects as $subject)
                                                                @php
                                                                    $termTotal = $termScoreMap[$sid][$subject] ?? 0;
                                                                    $cumTotal  = $cumScoreMap[$sid][$subject]  ?? 0;

                                                                    // Term grade
                                                                    [$termGrade, $termGradeLetter] = [null, null];
                                                                    if ($termTotal > 0) {
                                                                        if ($isSenior) {
                                                                            if      ($termTotal >= 75) { $termGrade = 'A1'; $termGradeLetter = 'a1'; }
                                                                            elseif  ($termTotal >= 70) { $termGrade = 'B2'; $termGradeLetter = 'b2'; }
                                                                            elseif  ($termTotal >= 65) { $termGrade = 'B3'; $termGradeLetter = 'b3'; }
                                                                            elseif  ($termTotal >= 60) { $termGrade = 'C4'; $termGradeLetter = 'c4'; }
                                                                            elseif  ($termTotal >= 55) { $termGrade = 'C5'; $termGradeLetter = 'c5'; }
                                                                            elseif  ($termTotal >= 50) { $termGrade = 'C6'; $termGradeLetter = 'c6'; }
                                                                            elseif  ($termTotal >= 45) { $termGrade = 'D7'; $termGradeLetter = 'd7'; }
                                                                            elseif  ($termTotal >= 40) { $termGrade = 'E8'; $termGradeLetter = 'e8'; }
                                                                            else                       { $termGrade = 'F9'; $termGradeLetter = 'f9'; }
                                                                        } else {
                                                                            if      ($termTotal >= 70) { $termGrade = 'A'; $termGradeLetter = 'a'; }
                                                                            elseif  ($termTotal >= 60) { $termGrade = 'B'; $termGradeLetter = 'b'; }
                                                                            elseif  ($termTotal >= 50) { $termGrade = 'C'; $termGradeLetter = 'c'; }
                                                                            elseif  ($termTotal >= 40) { $termGrade = 'D'; $termGradeLetter = 'd'; }
                                                                            else                       { $termGrade = 'F'; $termGradeLetter = 'f'; }
                                                                        }
                                                                    }

                                                                    // Cumulative grade
                                                                    [$cumGrade, $cumGradeLetter] = [null, null];
                                                                    if ($cumTotal > 0) {
                                                                        if ($isSenior) {
                                                                            if      ($cumTotal >= 75) { $cumGrade = 'A1'; $cumGradeLetter = 'a1'; }
                                                                            elseif  ($cumTotal >= 70) { $cumGrade = 'B2'; $cumGradeLetter = 'b2'; }
                                                                            elseif  ($cumTotal >= 65) { $cumGrade = 'B3'; $cumGradeLetter = 'b3'; }
                                                                            elseif  ($cumTotal >= 60) { $cumGrade = 'C4'; $cumGradeLetter = 'c4'; }
                                                                            elseif  ($cumTotal >= 55) { $cumGrade = 'C5'; $cumGradeLetter = 'c5'; }
                                                                            elseif  ($cumTotal >= 50) { $cumGrade = 'C6'; $cumGradeLetter = 'c6'; }
                                                                            elseif  ($cumTotal >= 45) { $cumGrade = 'D7'; $cumGradeLetter = 'd7'; }
                                                                            elseif  ($cumTotal >= 40) { $cumGrade = 'E8'; $cumGradeLetter = 'e8'; }
                                                                            else                      { $cumGrade = 'F9'; $cumGradeLetter = 'f9'; }
                                                                        } else {
                                                                            if      ($cumTotal >= 70) { $cumGrade = 'A'; $cumGradeLetter = 'a'; }
                                                                            elseif  ($cumTotal >= 60) { $cumGrade = 'B'; $cumGradeLetter = 'b'; }
                                                                            elseif  ($cumTotal >= 50) { $cumGrade = 'C'; $cumGradeLetter = 'c'; }
                                                                            elseif  ($cumTotal >= 40) { $cumGrade = 'D'; $cumGradeLetter = 'd'; }
                                                                            else                      { $cumGrade = 'F'; $cumGradeLetter = 'f'; }
                                                                        }
                                                                    }

                                                                    $termColorClass = $termTotal < 40 ? 'highlight-red' : ($termTotal < 50 ? 'highlight-orange' : ($termTotal >= 70 ? 'highlight-green' : ''));
                                                                    $cumColorClass  = $cumTotal  < 40 ? 'highlight-red' : ($cumTotal  < 50 ? 'highlight-orange' : ($cumTotal  >= 70 ? 'highlight-green' : ''));
                                                                @endphp
                                                                <div class="subject-score-card {{ $scoringMode === 'term' ? 'mode-term' : 'mode-cum' }}">
                                                                    <div class="score-subject-name">{{ $subject }}</div>

                                                                    {{-- Term row --}}
                                                                    <div class="score-row-term">
                                                                        <div class="score-type-label">
                                                                            <i class="ri-calendar-check-line"></i> Term
                                                                            @if($scoringMode === 'term')
                                                                                <span style="color:#ea580c;font-size:.55rem;">★</span>
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <span class="score-value {{ $termColorClass }}">{{ $termTotal ?: '—' }}</span>
                                                                            @if($termGrade)
                                                                                <span class="score-grade-badge grade-{{ $termGradeLetter }}">{{ $termGrade }}</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    {{-- Cumulative row --}}
                                                                    <div class="score-row-cum">
                                                                        <div class="score-type-label">
                                                                            <i class="ri-bar-chart-line"></i> Cum
                                                                            @if($scoringMode === 'cumulative')
                                                                                <span style="color:#ea580c;font-size:.55rem;">★</span>
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <span class="score-value {{ $cumColorClass }}">{{ $cumTotal ?: '—' }}</span>
                                                                            @if($cumGrade)
                                                                                <span class="score-grade-badge grade-{{ $cumGradeLetter }}">{{ $cumGrade }}</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
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
                                                                    <span class="badge {{ $scoringMode === 'term' ? 'bg-info' : 'bg-primary' }} ms-1" style="font-size:.6rem;">
                                                                        Based on {{ $scoringMode === 'term' ? 'Term' : 'Cumulative' }}
                                                                    </span>
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
                                                                $intPlain  = strip_tags($intelligentComments[$sid] ?? '');
                                                                $stdPlains = array_map('strip_tags', $standardPersonalizedComments[$sid] ?? []);
                                                                $showAI    = $intPlain && !in_array($intPlain, $stdPlains);
                                                            @endphp
                                                            @if($showAI)
                                                                <option value="{{ $intPlain }}"
                                                                        style="background-color:#e8f5e8!important;font-weight:600!important;color:#155724!important;"
                                                                    {{ $currentCommentPlain === $intPlain ? 'selected' : '' }}>
                                                                    💡 Use AI Generated Comment
                                                                </option>
                                                            @endif
                                                        </select>

                                                        <button type="button"
                                                                class="comment-info-icon grades-trigger"
                                                                data-student-id="{{ $sid }}"
                                                                data-student-name="{{ $fullNameWithOther }}">
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
                                                                {{-- Summary stats --}}
                                                                <div class="row mb-2 g-2">
                                                                    <div class="col-6">
                                                                        <div class="stat-card" style="border:2px solid #0891b2;padding:10px;">
                                                                            <small class="text-info fw-bold">
                                                                                <i class="ri-calendar-check-line"></i> Term Total
                                                                            </small>
                                                                            <h4 class="mb-0 {{ ($analytics['term_total'] ?? 0) < 50 ? 'text-danger' : 'text-info' }}">
                                                                                {{ $analytics['term_total'] ?? 0 }}
                                                                            </h4>
                                                                            <small class="text-muted">Avg: {{ $analytics['term_average'] ?? 0 }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="stat-card" style="border:2px solid var(--principal-primary);padding:10px;">
                                                                            <small style="color:var(--principal-primary);" class="fw-bold">
                                                                                <i class="ri-bar-chart-line"></i> Cumulative Total
                                                                            </small>
                                                                            <h4 class="mb-0 {{ ($analytics['total_score'] ?? 0) < 50 ? 'text-danger' : '' }}" style="color:var(--principal-primary);">
                                                                                {{ $analytics['total_score'] ?? 0 }}
                                                                            </h4>
                                                                            <small class="text-muted">Avg: {{ $analytics['average'] ?? 0 }}</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-2 g-2">
                                                                    <div class="col-4">
                                                                        <div class="stat-card" style="padding:8px;">
                                                                            <small>Position</small>
                                                                            <strong class="d-block text-primary">{{ $analytics['position_text'] ?? '—' }}</strong>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-8">
                                                                        <div class="stat-card" style="padding:8px;">
                                                                            <small>Active Mode</small>
                                                                            <strong class="d-block {{ $scoringMode === 'term' ? 'text-info' : '' }}" style="{{ $scoringMode === 'cumulative' ? 'color:var(--principal-primary);' : '' }}">
                                                                                {{ $scoringMode === 'term' ? '📅 Term Score' : '📈 Cumulative Score' }}
                                                                            </strong>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="text-center mb-3 p-2 bg-light rounded">
                                                                    <small class="text-muted">
                                                                        Class Average ({{ $scoringMode === 'term' ? 'Term' : 'Cumulative' }}):
                                                                        <strong>{{ $classAnalytics['average'] }}</strong>
                                                                    </small>
                                                                    @php $diff = ($analytics['average'] ?? 0) - $classAnalytics['average']; @endphp
                                                                    @if($diff > 0.5)
                                                                        <span class="text-success ms-2"><i class="ri-arrow-up-line"></i> +{{ round($diff,1) }}</span>
                                                                    @elseif($diff < -0.5)
                                                                        <span class="text-danger ms-2"><i class="ri-arrow-down-line"></i> {{ round($diff,1) }}</span>
                                                                    @endif
                                                                </div>

                                                                {{-- Grade table: all 4 columns --}}
                                                                <table class="table table-sm table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="tooltip-grade-header">Subject</th>
                                                                            <th class="tooltip-grade-header tooltip-col-term text-center">
                                                                                <i class="ri-calendar-check-line"></i> Term Score
                                                                            </th>
                                                                            <th class="tooltip-grade-header tooltip-col-tgrade text-center">Term Grade</th>
                                                                            <th class="tooltip-grade-header tooltip-col-cum text-center">
                                                                                <i class="ri-bar-chart-line"></i> Cum Score
                                                                            </th>
                                                                            <th class="tooltip-grade-header tooltip-col-cgrade text-center">Cum Grade</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="grades-body-{{ $sid }}"></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ============================================================
                                 MOBILE CARDS
                            ============================================================ --}}
                            <div class="mobile-cards">
                                @foreach ($students as $student)
                                    @php
                                        $sid = $student->id;
                                        $avatarUrl = null;
                                        if(isset($student->picture) && $student->picture && $student->picture != 'unnamed.jpg' && $student->picture != '') {
                                            $avatarUrl = asset('storage/student_avatars/' . $student->picture);
                                        }
                                        $fullName = trim($student->lastname . ' ' . $student->fname);
                                        $otherName = $student->othername ?? '';
                                        $fullNameWithOther = trim($fullName . ($otherName ? ' (' . $otherName . ')' : ''));
                                        $initials = strtoupper(substr($student->fname, 0, 1) . substr($student->lastname, 0, 1));
                                        if(empty($initials)) $initials = 'ST';

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
                                                @if($avatarUrl)
                                                    <img src="{{ $avatarUrl }}"
                                                         alt="{{ $fullNameWithOther }}"
                                                         class="avatar-sm avatar-clickable"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#imageZoomModal"
                                                         data-image="{{ $avatarUrl }}"
                                                         data-name="{{ $fullNameWithOther }}"
                                                         data-admission="{{ $student->admissionNo ?? 'N/A' }}"
                                                         data-class="{{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }}"
                                                         data-gender="{{ $student->gender ?? 'N/A' }}">
                                                @else
                                                    <div class="avatar-placeholder avatar-clickable"
                                                         style="width: 45px; height: 45px; font-size: 16px;"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#imageZoomModal"
                                                         data-name="{{ $fullNameWithOther }}"
                                                         data-admission="{{ $student->admissionNo ?? 'N/A' }}"
                                                         data-class="{{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }}"
                                                         data-gender="{{ $student->gender ?? 'N/A' }}"
                                                         data-initials="{{ $initials }}">
                                                        {{ $initials }}
                                                    </div>
                                                @endif
                                                <div class="student-details">
                                                    <h6>
                                                        {{ $fullNameWithOther }}
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
                                                <div class="summary-title">
                                                    <i class="ri-bar-chart-line"></i>
                                                    Performance Summary
                                                    <span class="badge {{ $scoringMode === 'term' ? 'bg-info' : 'bg-light text-dark' }} ms-auto" style="font-size:.65rem;">
                                                        {{ $scoringMode === 'term' ? '📅 Term Mode' : '📈 Cumulative Mode' }}
                                                    </span>
                                                </div>
                                                <div class="summary-grid">
                                                    <div class="summary-item">
                                                        <div class="summary-label">Term Avg</div>
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

                                            {{-- Mobile subject grid — dual rows --}}
                                            <div class="subjects-grid">
                                                @foreach ($subjects as $subject)
                                                    @php
                                                        $termTotal = $termScoreMap[$sid][$subject] ?? 0;
                                                        $cumTotal  = $cumScoreMap[$sid][$subject]  ?? 0;

                                                        [$mTermGrade, $mTermGL] = [null, null];
                                                        if ($termTotal > 0) {
                                                            if ($isSenior) {
                                                                if      ($termTotal >= 75) { $mTermGrade = 'A1'; $mTermGL = 'a1'; }
                                                                elseif  ($termTotal >= 70) { $mTermGrade = 'B2'; $mTermGL = 'b2'; }
                                                                elseif  ($termTotal >= 65) { $mTermGrade = 'B3'; $mTermGL = 'b3'; }
                                                                elseif  ($termTotal >= 60) { $mTermGrade = 'C4'; $mTermGL = 'c4'; }
                                                                elseif  ($termTotal >= 55) { $mTermGrade = 'C5'; $mTermGL = 'c5'; }
                                                                elseif  ($termTotal >= 50) { $mTermGrade = 'C6'; $mTermGL = 'c6'; }
                                                                elseif  ($termTotal >= 45) { $mTermGrade = 'D7'; $mTermGL = 'd7'; }
                                                                elseif  ($termTotal >= 40) { $mTermGrade = 'E8'; $mTermGL = 'e8'; }
                                                                else                       { $mTermGrade = 'F9'; $mTermGL = 'f9'; }
                                                            } else {
                                                                if      ($termTotal >= 70) { $mTermGrade = 'A'; $mTermGL = 'a'; }
                                                                elseif  ($termTotal >= 60) { $mTermGrade = 'B'; $mTermGL = 'b'; }
                                                                elseif  ($termTotal >= 50) { $mTermGrade = 'C'; $mTermGL = 'c'; }
                                                                elseif  ($termTotal >= 40) { $mTermGrade = 'D'; $mTermGL = 'd'; }
                                                                else                       { $mTermGrade = 'F'; $mTermGL = 'f'; }
                                                            }
                                                        }

                                                        [$mCumGrade, $mCumGL] = [null, null];
                                                        if ($cumTotal > 0) {
                                                            if ($isSenior) {
                                                                if      ($cumTotal >= 75) { $mCumGrade = 'A1'; $mCumGL = 'a1'; }
                                                                elseif  ($cumTotal >= 70) { $mCumGrade = 'B2'; $mCumGL = 'b2'; }
                                                                elseif  ($cumTotal >= 65) { $mCumGrade = 'B3'; $mCumGL = 'b3'; }
                                                                elseif  ($cumTotal >= 60) { $mCumGrade = 'C4'; $mCumGL = 'c4'; }
                                                                elseif  ($cumTotal >= 55) { $mCumGrade = 'C5'; $mCumGL = 'c5'; }
                                                                elseif  ($cumTotal >= 50) { $mCumGrade = 'C6'; $mCumGL = 'c6'; }
                                                                elseif  ($cumTotal >= 45) { $mCumGrade = 'D7'; $mCumGL = 'd7'; }
                                                                elseif  ($cumTotal >= 40) { $mCumGrade = 'E8'; $mCumGL = 'e8'; }
                                                                else                      { $mCumGrade = 'F9'; $mCumGL = 'f9'; }
                                                            } else {
                                                                if      ($cumTotal >= 70) { $mCumGrade = 'A'; $mCumGL = 'a'; }
                                                                elseif  ($cumTotal >= 60) { $mCumGrade = 'B'; $mCumGL = 'b'; }
                                                                elseif  ($cumTotal >= 50) { $mCumGrade = 'C'; $mCumGL = 'c'; }
                                                                elseif  ($cumTotal >= 40) { $mCumGrade = 'D'; $mCumGL = 'd'; }
                                                                else                      { $mCumGrade = 'F'; $mCumGL = 'f'; }
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="subject-item {{ $scoringMode === 'term' ? 'mode-term' : 'mode-cum' }}">
                                                        <div class="subject-name">{{ $subject }}</div>
                                                        {{-- Term --}}
                                                        <div class="score-row-term mb-1" style="border-radius:5px;padding:3px 4px;">
                                                            <div style="font-size:.55rem;font-weight:700;color:#0891b2;text-transform:uppercase;">
                                                                Term @if($scoringMode==='term')<span style="color:#ea580c;">★</span>@endif
                                                            </div>
                                                            <span class="fw-bold {{ $termTotal < 50 ? 'text-danger' : 'text-success' }}" style="font-size:.85rem;">
                                                                {{ $termTotal ?: '—' }}
                                                            </span>
                                                            @if($mTermGrade)
                                                                <span class="score-grade-badge grade-{{ $mTermGL }}" style="font-size:.55rem;">{{ $mTermGrade }}</span>
                                                            @endif
                                                        </div>
                                                        {{-- Cumulative --}}
                                                        <div class="score-row-cum" style="border-radius:5px;padding:3px 4px;">
                                                            <div style="font-size:.55rem;font-weight:700;color:var(--principal-primary);text-transform:uppercase;">
                                                                Cum @if($scoringMode==='cumulative')<span style="color:#ea580c;">★</span>@endif
                                                            </div>
                                                            <span class="fw-bold {{ $cumTotal < 50 ? 'text-danger' : 'text-success' }}" style="font-size:.85rem;">
                                                                {{ $cumTotal ?: '—' }}
                                                            </span>
                                                            @if($mCumGrade)
                                                                <span class="score-grade-badge grade-{{ $mCumGL }}" style="font-size:.55rem;">{{ $mCumGrade }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($intelligentComment)
                                                <div class="intelligent-comment-section mb-2">
                                                    <small>
                                                        <i class="ri-lightbulb-line text-success"></i>
                                                        <strong>AI Suggestion</strong>
                                                        <span class="badge {{ $scoringMode === 'term' ? 'bg-info' : 'bg-primary' }} ms-1" style="font-size:.6rem;">
                                                            {{ $scoringMode === 'term' ? 'Term' : 'Cumulative' }}
                                                        </span>
                                                    </small>
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
                                                        $intPlain  = strip_tags($intelligentComments[$sid] ?? '');
                                                        $stdPlains = array_map('strip_tags', $standardPersonalizedComments[$sid] ?? []);
                                                    @endphp
                                                    @if($intPlain && !in_array($intPlain, $stdPlains))
                                                        <option value="{{ $intPlain }}"
                                                                style="background-color:#e8f5e8!important;font-weight:600!important;"
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

{{-- IMAGE ZOOM MODAL --}}
<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" alt="Student Photo" class="zoomed-image">
                <div class="zoomed-image-name" id="zoomedImageName"></div>
                <div class="zoomed-image-details" id="zoomedImageDetails"></div>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.studentGradesData = @json($studentGrades);
let activeTooltip = null;

function showToast(message, type = 'info') {
    document.querySelectorAll('.auto-save-toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show shadow`;
    toast.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:99999; min-width:360px;';

    toast.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : type === 'danger' ? 'error-warning' : 'information'}-fill me-2 fs-4 mt-1"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>`;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4500);
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

function getGradeClass(grade) {
    const g = (grade || '').toLowerCase().replace(/\s+/g, '');
    if (g === 'a1') return 'grade-a1';
    if (g === 'b2') return 'grade-b2';
    if (g === 'b3') return 'grade-b3';
    if (g === 'c4') return 'grade-c4';
    if (g === 'c5') return 'grade-c5';
    if (g === 'c6') return 'grade-c6';
    if (g === 'd7') return 'grade-d7';
    if (g === 'e8') return 'grade-e8';
    if (g === 'f9') return 'grade-f9';
    if (g === 'a')  return 'grade-a';
    if (g === 'b')  return 'grade-b';
    if (g === 'c')  return 'grade-c';
    if (g === 'd')  return 'grade-d';
    if (g === 'f')  return 'grade-f';
    return 'bg-secondary text-white';
}

function showTooltip(tooltipId, studentId, studentName) {
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    closeAllTooltips();

    const titleEl = document.getElementById(`tooltip-title-${studentId}`);
    if (titleEl) titleEl.textContent = `${studentName}'s Performance`;

    const grades = window.studentGradesData[studentId] || [];
    const tbody = document.getElementById(`grades-body-${studentId}`);
    if (tbody) {
        tbody.innerHTML = '';
        if (!grades.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No grades available</td></tr>';
        } else {
            grades.forEach(g => {
                const termColor = (g.term_score < 50) ? 'text-danger' : 'text-success';
                const cumColor  = (g.cum_score  < 50) ? 'text-danger' : 'text-success';
                const tGradeClass = getGradeClass(g.term_grade);
                const cGradeClass = getGradeClass(g.cum_grade);
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${escapeHtml(g.subject)}</strong></td>
                    <td class="text-center fw-bold ${termColor}" style="color:#0891b2!important;">${g.term_score || '—'}</td>
                    <td class="text-center">
                        ${g.term_grade ? `<span class="score-grade-badge ${tGradeClass}">${escapeHtml(g.term_grade)}</span>` : '<span class="text-muted">—</span>'}
                    </td>
                    <td class="text-center fw-bold ${cumColor}">${g.cum_score || '—'}</td>
                    <td class="text-center">
                        ${g.cum_grade ? `<span class="score-grade-badge ${cGradeClass}">${escapeHtml(g.cum_grade)}</span>` : '<span class="text-muted">—</span>'}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    }
    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

function setupImageZoom() {
    $('.avatar-clickable').off('click').on('click', function(e) {
        e.stopPropagation();
        const imageUrl   = $(this).data('image');
        const studentName = $(this).data('name');
        const admissionNo = $(this).data('admission') || 'N/A';
        const studentClass = $(this).data('class') || 'N/A';
        const gender      = $(this).data('gender') || 'N/A';
        const initials    = $(this).data('initials');

        $('#zoomedImageName').text(studentName || 'Student Photo');
        $('#zoomedImageDetails').html(`
            <i class="ri-id-card-line me-1"></i> ${admissionNo} &nbsp;|&nbsp;
            <i class="ri-school-line me-1"></i> ${studentClass} &nbsp;|&nbsp;
            <i class="ri-${gender === 'Male' ? 'male' : 'female'}-line me-1"></i> ${gender}
        `);

        if (imageUrl && imageUrl !== '' && imageUrl !== 'null' && imageUrl !== 'undefined') {
            $('#zoomedImage').attr('src', imageUrl).show();
        } else {
            const canvas = document.createElement('canvas');
            canvas.width = 400; canvas.height = 400;
            const ctx = canvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 400, 400);
            gradient.addColorStop(0, '#667eea');
            gradient.addColorStop(1, '#764ba2');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 400, 400);
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 160px "Segoe UI", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            const displayInitials = (initials && initials !== 'null') ? initials.substring(0, 2) : 'ST';
            ctx.fillText(displayInitials, 200, 200);
            $('#zoomedImage').attr('src', canvas.toDataURL()).show();
        }
    });
}

// ====================== AUTO-SAVE WITH CLASS-ARM ======================
document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function() {
        const studentId   = this.dataset.studentId;
        const comment     = this.value.trim();
        const original    = this.dataset.originalValue || '';

        if (comment === original) return;

        const row = this.closest('tr') || this.closest('.student-card');
        let studentName = 'Student';
        if (row) {
            const nameCell = row.querySelector('td:nth-child(4) strong') || row.querySelector('.student-details h6');
            if (nameCell) studentName = nameCell.textContent.trim();
        }

        // Improved Class + Arm Logic
        let classArm = '{{ $schoolclass->schoolclass ?? "" }}';
        @if(isset($schoolclass->arm_name) && $schoolclass->arm_name)
            classArm += ' {{ $schoolclass->arm_name }}';
        @elseif(isset($schoolclass->arm) && $schoolclass->arm)
            classArm += ' {{ $schoolclass->arm->arm ?? "" }}';
        @endif
        classArm = classArm.trim();

        // Visual feedback
        this.style.borderColor = '#f59e0b';
        this.style.backgroundColor = '#fffbeb';
        this.disabled = true;

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append(`teacher_comments[${studentId}]`, comment);

        fetch('{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}', {
            method: 'POST',
            body: fd,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.dataset.originalValue = comment;
                this.style.borderColor = '#16a34a';
                this.style.backgroundColor = '#d1fae5';

                showToast(`✅ Comment saved for <strong>${studentName}</strong><br><small class="text-muted">${classArm}</small>`, 'success');

                if (row) {
                    let badge = row.querySelector('.comment-saved-badge');
                    if (!badge) {
                        badge = document.createElement('small');
                        badge.className = 'comment-saved-badge d-block mt-1';
                        badge.innerHTML = `<i class="ri-check-double-line"></i> Saved`;
                        const nameDiv = row.querySelector('td:nth-child(4) div') || row.querySelector('.student-details');
                        if (nameDiv) nameDiv.appendChild(badge);
                    }
                }
            } else {
                throw new Error(data.message || 'Save failed');
            }
        })
        .catch(err => {
            console.error('Auto-save failed:', err);
            this.value = original;
            this.style.borderColor = '#dc2626';
            this.style.backgroundColor = '#fee2e2';
            showToast(`❌ Failed to save for ${studentName}`, 'danger');
        })
        .finally(() => {
            this.disabled = false;
            setTimeout(() => {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
            }, 1800);
        });
    });
});

// ====================== BULK SAVE ======================
const commentsForm = document.getElementById('commentsForm');
if (commentsForm) {
    commentsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn  = document.getElementById('saveAllBtn');
        const ind  = document.getElementById('savingIndicator');
        const orig = btn.innerHTML;

        // Improved Class + Arm for bulk save
        let classArm = '{{ $schoolclass->schoolclass ?? "" }}';
        @if(isset($schoolclass->arm_name) && $schoolclass->arm_name)
            classArm += ' {{ $schoolclass->arm_name }}';
        @elseif(isset($schoolclass->arm) && $schoolclass->arm)
            classArm += ' {{ $schoolclass->arm->arm ?? "" }}';
        @endif
        classArm = classArm.trim();

        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line spin-icon me-1"></i> Saving All...';
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
            showToast(`✅ All comments saved successfully!<br><small class="text-muted">${classArm}</small>`, 'success');

            document.querySelectorAll('.auto-save-comment').forEach(sel => {
                if (sel.value.trim()) sel.dataset.originalValue = sel.value.trim();
            });
        })
        .catch(err => {
            console.error('Bulk save error:', err);
            showToast('❌ Error saving comments: ' + err.message, 'danger');
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
            const sid  = this.dataset.studentId;
            const name = this.dataset.studentName;
            const tid  = `tooltip-${sid}`;
            activeTooltip === tid ? closeAllTooltips() : showTooltip(tid, sid, name);
        });
    });

    document.querySelectorAll('.tooltip-close').forEach(btn => btn.addEventListener('click', closeAllTooltips));

    document.addEventListener('click', e => {
        if (activeTooltip) {
            const activeEl = document.getElementById(activeTooltip);
            if (activeEl && !activeEl.contains(e.target)) closeAllTooltips();
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeAllTooltips();
    });
}

// Search
document.getElementById('searchInput')?.addEventListener('input', function() {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.desktop-table tbody tr').forEach(row => {
        row.style.display = (!term || row.textContent.toLowerCase().includes(term)) ? '' : 'none';
    });
    document.querySelectorAll('.mobile-cards .student-card').forEach(card => {
        card.style.display = (!term || card.textContent.toLowerCase().includes(term)) ? '' : 'none';
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.auto-save-comment').forEach(s => {
        s.dataset.originalValue = s.value;
    });
    setupImageZoom();

    $(document).on('click', '.zoomed-image', function() {
        $('#imageZoomModal').modal('hide');
    });
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') $('#imageZoomModal').modal('hide');
    });
});
</script>

@endsection
