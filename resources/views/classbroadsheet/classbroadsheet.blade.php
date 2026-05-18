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
body { font-family: 'DM Sans', sans-serif; }

/* Original Styles */
.cb-hero { background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%); border-radius: var(--cb-radius); padding: 32px 36px; margin-bottom: 28px; position: relative; overflow: hidden; }
.cb-hero::before { content: ''; position: absolute; top: -80px; right: -80px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%); border-radius: 50%; }
.cb-hero h1 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #fff; margin: 0 0 8px; }
.cb-hero p { font-size: 13px; color: rgba(255,255,255,.72); margin: 0; }
.cb-hero .meta-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
.cb-meta-pill { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; color: #fff; display: inline-flex; align-items: center; gap: 5px; }

.cb-stat { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); padding: 20px 22px; position: relative; overflow: hidden; transition: transform .15s, box-shadow .15s; }
.cb-stat:hover { transform: translateY(-2px); box-shadow: var(--cb-shadow); }
.cb-stat .stat-accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--cb-radius) var(--cb-radius) 0 0; }
.cb-stat .stat-value { font-size: 30px; font-weight: 700; color: var(--cb-navy); line-height: 1; margin-top: 8px; }
.cb-stat .stat-label { font-size: 12px; color: var(--cb-muted); margin-top: 5px; font-weight: 500; }
.cb-stat .stat-ico { font-size: 36px; opacity: .08; position: absolute; right: 16px; top: 50%; transform: translateY(-50%); }

.col-toggle-panel { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); padding: 18px 22px; margin-bottom: 22px; box-shadow: var(--cb-shadow); }
.col-toggle-panel h6 { font-size: 13px; font-weight: 700; color: var(--cb-navy); margin: 0 0 14px; display: flex; align-items: center; gap: 7px; }
.toggle-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.toggle-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--cb-border); background: var(--cb-surface); color: var(--cb-muted); transition: all .18s ease; user-select: none; }
.toggle-chip:hover { border-color: var(--cb-teal); color: var(--cb-teal); }
.toggle-chip.active { background: var(--cb-teal); border-color: var(--cb-teal); color: #fff; box-shadow: 0 2px 8px rgba(13,148,136,.3); }

.cb-card { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); box-shadow: var(--cb-shadow); overflow: hidden; }
.cb-card-header { padding: 18px 24px; border-bottom: 1px solid var(--cb-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(to right, #f8fafc, #f0fdf9); }
.cb-card-header h5 { font-size: 15px; font-weight: 700; color: var(--cb-navy); margin: 0; display: flex; align-items: center; gap: 8px; }

.cb-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.cb-table thead th { background: var(--cb-navy); color: #fff; padding: 11px 14px; font-weight: 600; font-size: 11.5px; white-space: nowrap; text-align: center; border-right: 1px solid rgba(255,255,255,.08); }
.cb-table thead th.col-name-hdr { text-align: left; }
.cb-table tbody td { padding: 10px 14px; vertical-align: middle; border-bottom: 1px solid var(--cb-border); text-align: center; }
.cb-table tbody td.td-name { text-align: left; }
.cb-table tbody tr:hover td { background: #f0fdf9; }

/* Enhanced Comment Status Styling */
.comment-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}
.comment-status-badge.has-comment {
    background: #dcfce7;
    color: #15803d;
}
.comment-status-badge.no-comment {
    background: #fee2e2;
    color: #b91c1c;
}

.comment-type-pill {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    margin: 2px 2px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ct-teacher   { background: #e0f2fe; color: #0369a1; }
.ct-guidance  { background: #f3e8ff; color: #6b21a8; }
.ct-activities{ background: #fef3c7; color: #854d0e; }
.ct-principal { background: #fed7aa; color: #9a3412; }

/* Modal Styling */
#cbCommentModal .modal-content { border-radius: 20px; overflow: hidden; }
#cbCommentModal .modal-header { background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal)); color: #fff; }
.past-comment-item {
    border-left: 4px solid var(--cb-teal);
    background: #f8fafc;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.past-comment-item:hover {
    background: #f0fdf9;
    transform: translateX(4px);
}
.past-comment-meta {
    font-size: 11px;
    font-weight: 600;
    color: var(--cb-teal);
    margin-bottom: 6px;
}
.past-comment-text {
    font-size: 12px;
    color: #334155;
    line-height: 1.4;
}
.past-comment-type {
    display: inline-block;
    font-size: 9px;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: 8px;
}

.count-badge {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    border-radius: 20px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 6px;
}

/* Text formatting toolbar */
.text-format-toolbar {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 6px;
    margin-bottom: 10px;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.format-btn {
    padding: 4px 10px;
    border: 1px solid #cbd5e1;
    background: white;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}
.format-btn:hover {
    background: var(--cb-teal);
    color: white;
    border-color: var(--cb-teal);
}

/* Student Avatar in Modal */
.modal-student-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,0.3);
}
.modal-student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.modal-student-avatar .initials {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--cb-teal), var(--cb-sky));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
}

.performance-strip-modal {
    background: #f8fafc;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 12px;
}
.perf-item {
    text-align: center;
}
.perf-label {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
}
.perf-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--cb-navy);
}

/* Toast Notifications */
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

.save-bar {
    position: sticky;
    bottom: 0;
    background: white;
    border-top: 2px solid var(--cb-teal);
    padding: 12px 20px;
    text-align: right;
    z-index: 100;
}
.btn-save-all {
    background: var(--cb-teal);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-save-all:hover {
    background: #0f766e;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13,148,136,0.3);
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

<!-- Hero -->
<div class="cb-hero">
    <h1><i class="ri-clipboard-line me-2"></i>Class Broadsheet</h1>
    <p>Review student performance, assign comments, and track attendance for your class.</p>
    <div class="meta-pills">
        <span class="cb-meta-pill"><i class="ri-building-line"></i>{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-line"></i>{{ $schoolterm }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-event-line"></i>{{ $schoolsession }}</span>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
            <div class="stat-ico"><i class="ri-group-line"></i></div>
            <div class="stat-value">{{ $students->count() }}</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-book-open-line"></i></div>
            <div class="stat-value text-info">{{ $subjects->count() }}</div>
            <div class="stat-label">Subjects</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-green),#4ade80);"></div>
            <div class="stat-ico"><i class="ri-percent-line"></i></div>
            <div class="stat-value text-success" id="statPassRate">—</div>
            <div class="stat-label">Avg Cum %</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-amber),#fcd34d);"></div>
            <div class="stat-ico"><i class="ri-award-line"></i></div>
            <div class="stat-value text-warning" id="statTop">—</div>
            <div class="stat-label">Top Performer</div>
        </div>
    </div>
</div>

<!-- Column Toggle -->
<div class="col-toggle-panel">
    <h6><i class="ri-layout-column-line" style="color:var(--cb-teal)"></i> Show / Hide Columns</h6>
    <div class="toggle-chips">
        <span class="toggle-chip active" data-colkey="scores"><i class="ri-bar-chart-line"></i> Subject Scores</span>
        <span class="toggle-chip active" data-colkey="summary"><i class="ri-pie-chart-line"></i> Summary</span>
        <span class="toggle-chip active" data-colkey="teacher"><i class="ri-chat-3-line"></i> Teacher's Comment</span>
        <span class="toggle-chip active" data-colkey="guidance"><i class="ri-mental-health-line"></i> Counselor's Comment</span>
        <span class="toggle-chip active" data-colkey="activities"><i class="ri-football-line"></i> Remark on Activities</span>
        <span class="toggle-chip active" data-colkey="principal"><i class="ri-government-line"></i> Principal's Comment</span>
        <span class="toggle-chip active" data-colkey="absence"><i class="ri-calendar-close-line"></i> Absences</span>
    </div>
</div>

@php $cbAnalyticsJson = json_encode($studentAnalytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); @endphp

<!-- ENHANCED COMMENT MODAL -->
<div class="modal fade" id="cbCommentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div id="modalStudentAvatar" class="modal-student-avatar"></div>
                    <div>
                        <h5 class="mb-1 text-white" id="modalStudentName"></h5>
                        <div class="text-white-50 small" id="modalStudentMeta"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Performance Summary -->
                <div class="performance-strip-modal" id="modalPerformance"></div>

                <!-- Comment Type Indicator -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0" id="modalCommentType"></h6>
                        <small class="text-muted" id="modalCommentHint">Click on past comments to load them</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnLoadPastComments">
                        <i class="ri-history-line"></i> View Past Comments
                        <span id="pastCommentCount" class="count-badge">0</span>
                    </button>
                </div>

                <!-- Text Formatting Toolbar -->
                <div class="text-format-toolbar">
                    <button type="button" class="format-btn" onclick="formatText('bold')"><b>Bold</b></button>
                    <button type="button" class="format-btn" onclick="formatText('italic')"><i>Italic</i></button>
                    <button type="button" class="format-btn" onclick="formatText('bullet')">• Bullet List</button>
                    <button type="button" class="format-btn" onclick="formatText('number')">1. Number List</button>
                    <button type="button" class="format-btn" onclick="formatText('quote')">" Quote</button>
                </div>

                <!-- Text Area -->
                <textarea id="modalTextarea" class="cb-input" rows="6" style="width:100%; resize:vertical; font-size:14px; line-height:1.6;"></textarea>

                <!-- Past Comments Panel -->
                <div id="pastCommentsPanel" style="display:none; margin-top: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="small fw-bold mb-0"><i class="ri-history-line"></i> Past Comments from Previous Terms</h6>
                        <button type="button" class="btn-close btn-sm" onclick="document.getElementById('pastCommentsPanel').style.display='none'"></button>
                    </div>
                    <div id="pastCommentsList" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="modalSaveBtn"><i class="ri-save-line"></i> Save Comment</button>
            </div>
        </div>
    </div>
</div>

<div class="cb-card">
    <div class="cb-card-header">
        <h5><i class="ri-table-alt-line" style="color:var(--cb-teal)"></i> Student Performance &amp; Comments</h5>
        <div class="cb-search" style="max-width:260px;">
            <i class="ri-search-line"></i>
            <input type="text" id="searchInput" placeholder="Search students…" style="border:1px solid #e2e8f0; border-radius:20px; padding:6px 12px 6px 32px;">
        </div>
    </div>

    <form id="commentsForm">
        @csrf
        <input type="hidden" name="_method" value="PATCH">

        @foreach ($students as $student)
            @php $sid = $student->id; $profile = $personalityProfiles->where('studentid', $sid)->first(); @endphp
            <input type="hidden" id="c_teacher_{{ $sid }}" name="teacher_comments[{{ $sid }}]" value="{{ $profile?->classteachercomment ?? '' }}">
            <input type="hidden" id="c_guidance_{{ $sid }}" name="guidance_comments[{{ $sid }}]" value="{{ $profile?->guidancescomment ?? '' }}">
            <input type="hidden" id="c_activities_{{ $sid }}" name="remarks_on_other_activities[{{ $sid }}]" value="{{ $profile?->remark_on_other_activities ?? '' }}">
            <input type="hidden" id="c_principal_{{ $sid }}" name="principals_comments[{{ $sid }}]" value="{{ $profile?->principalscomment ?? '' }}">
            <input type="hidden" id="c_absence_{{ $sid }}" name="no_of_times_school_absent[{{ $sid }}]" value="{{ $profile?->no_of_times_school_absent ?? '' }}">
        @endforeach

        <!-- DESKTOP TABLE -->
        <div class="desktop-only" style="overflow-x:auto;">
            <table class="cb-table">
                <thead>
                    <tr>
                        <th style="width:34px;">#</th>
                        <th class="col-name-hdr" style="min-width:220px;">Student</th>
                        @foreach ($subjects as $subject)
                            <th class="cbcol-scores" style="min-width:86px;">{{ $subject->subject }}</th>
                        @endforeach
                        <th class="cbcol-summary" style="min-width:140px;">Summary</th>
                        <th class="cbcol-teacher" style="min-width:220px;">Teacher's Comment</th>
                        <th class="cbcol-guidance" style="min-width:180px;">Counselor's Comment</th>
                        <th class="cbcol-activities" style="min-width:180px;">Remark on Activities</th>
                        <th class="cbcol-principal" style="min-width:180px;">Principal's Comment</th>
                        <th class="cbcol-absence" style="min-width:80px;">Absent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $index => $student)
                        @php
                            $sid = $student->id;
                            $initials = strtoupper(substr($student->fname ?? '', 0, 1) . substr($student->lastname ?? '', 0, 1)) ?: 'ST';
                            $hasPic = !empty($student->picture) && $student->picture !== 'unnamed.jpg';
                            $imgUrl = $hasPic ? asset('storage/student_avatars/' . basename($student->picture)) : null;
                            $fullName = trim(($student->lastname ?? '') . ' ' . ($student->fname ?? '') . ' ' . ($student->othername ?? ''));
                            $profile = $personalityProfiles->where('studentid', $sid)->first();
                            $an = $studentAnalytics[$sid] ?? [];

                            // Check which comments exist
                            $hasTeacherComment = $profile?->classteachercomment && trim($profile->classteachercomment) !== '';
                            $hasGuidanceComment = $profile?->guidancescomment && trim($profile->guidancescomment) !== '';
                            $hasActivitiesComment = $profile?->remark_on_other_activities && trim($profile->remark_on_other_activities) !== '';
                            $hasPrincipalComment = $profile?->principalscomment && trim($profile->principalscomment) !== '';
                            $hasAnyComment = $hasTeacherComment || $hasGuidanceComment || $hasActivitiesComment || $hasPrincipalComment;

                            $commentTypes = [];
                            if ($hasTeacherComment) $commentTypes[] = 'Teacher';
                            if ($hasGuidanceComment) $commentTypes[] = 'Guidance';
                            if ($hasActivitiesComment) $commentTypes[] = 'Activities';
                            if ($hasPrincipalComment) $commentTypes[] = 'Principal';
                        @endphp
                        <tr class="cb-student-row" data-student-id="{{ $sid }}" data-student-name="{{ $fullName }}" data-searchkey="{{ strtolower($fullName . ' ' . $student->admissionNo) }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="td-name">
                                <div class="student-name-cell">
                                    @if($imgUrl)
                                        <div class="cb-avatar cb-avatar-trigger" style="cursor:pointer;" data-img="{{ $imgUrl }}" data-name="{{ $fullName }}" data-adm="{{ $student->admissionNo }}" data-class="{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : '' }}" data-gender="{{ $student->gender ?? '' }}">
                                            <img src="{{ $imgUrl }}" alt="{{ $fullName }}">
                                        </div>
                                    @else
                                        <div class="cb-avatar cb-avatar-initials cb-avatar-trigger" style="cursor:pointer;" data-name="{{ $fullName }}" data-initials="{{ $initials }}">{{ $initials }}</div>
                                    @endif
                                    <div>
                                        <div class="student-name-text">{{ $fullName }}</div>
                                        <div class="student-adm">{{ $student->admissionNo }} · {{ $student->gender ?? '' }}</div>
                                        <div class="comment-types-container" id="comment-types-{{ $sid }}">
                                            @if($hasAnyComment)
                                                @foreach($commentTypes as $type)
                                                    <span class="comment-type-pill ct-{{ strtolower($type) }}"><i class="ri-chat-3-line"></i> {{ $type }}</span>
                                                @endforeach
                                            @else
                                                <span class="comment-status-badge no-comment">No comments yet</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            @foreach ($subjects as $subject)
                                @php
                                    $tScore = $termScoreMap[$sid][$subject->subject] ?? 0;
                                    $cScore = $cumScoreMap[$sid][$subject->subject] ?? 0;
                                    $tGrade = $cGrade = '-';
                                    if ($isSenior) {
                                        if ($tScore >= 75) $tGrade='A1'; elseif ($tScore >= 70) $tGrade='B2'; elseif ($tScore >= 65) $tGrade='B3';
                                        elseif ($tScore >= 60) $tGrade='C4'; elseif ($tScore >= 55) $tGrade='C5'; elseif ($tScore >= 50) $tGrade='C6';
                                        elseif ($tScore >= 45) $tGrade='D7'; elseif ($tScore >= 40) $tGrade='E8'; elseif ($tScore > 0) $tGrade='F9';
                                        if ($cScore >= 75) $cGrade='A1'; elseif ($cScore >= 70) $cGrade='B2'; elseif ($cScore >= 65) $cGrade='B3';
                                        elseif ($cScore >= 60) $cGrade='C4'; elseif ($cScore >= 55) $cGrade='C5'; elseif ($cScore >= 50) $cGrade='C6';
                                        elseif ($cScore >= 45) $cGrade='D7'; elseif ($cScore >= 40) $cGrade='E8'; elseif ($cScore > 0) $cGrade='F9';
                                    } else {
                                        if ($tScore >= 70) $tGrade='A'; elseif ($tScore >= 60) $tGrade='B';
                                        elseif ($tScore >= 50) $tGrade='C'; elseif ($tScore >= 40) $tGrade='D';
                                        elseif ($tScore > 0) $tGrade='F';
                                        if ($cScore >= 70) $cGrade='A'; elseif ($cScore >= 60) $cGrade='B';
                                        elseif ($cScore >= 50) $cGrade='C'; elseif ($cScore >= 40) $cGrade='D';
                                        elseif ($cScore > 0) $cGrade='F';
                                    }
                                @endphp
                                <td class="cbcol-scores">
                                    <div class="score-dual">
                                        <div class="score-row score-row-term">
                                            <span class="score-lbl">T</span>
                                            <span>{{ $tScore ?: '—' }}</span>
                                            @if($tGrade !== '-')<span class="grade-badge g-{{ strtolower($tGrade) }}">{{ $tGrade }}</span>@endif
                                        </div>
                                        <div class="score-row score-row-cum">
                                            <span class="score-lbl">C</span>
                                            <span>{{ $cScore ?: '—' }}</span>
                                            @if($cGrade !== '-')<span class="grade-badge g-{{ strtolower($cGrade) }}">{{ $cGrade }}</span>@endif
                                        </div>
                                    </div>
                                </td>
                            @endforeach

                            <td class="cbcol-summary analytics-cell">
                                <div class="analytics-row"><span class="analytics-lbl">Term Avg</span><span class="analytics-val">{{ $an['term_average']??0 }}</span></div>
                                <div class="analytics-row"><span class="analytics-lbl">Cum Avg</span><span class="analytics-val">{{ $an['cum_average']??0 }}</span></div>
                                <div class="analytics-row"><span class="analytics-lbl">Cum %</span><span class="analytics-val">{{ $an['cum_percentage']??0 }}%</span></div>
                            </td>

                            <td class="cbcol-teacher"><input type="text" class="cb-input desk-teacher" data-sid="{{ $sid }}" data-field="teacher" value="{{ $profile?->classteachercomment ?? '' }}" placeholder="Click to edit..."></td>
                            <td class="cbcol-guidance"><input type="text" class="cb-input desk-guidance" data-sid="{{ $sid }}" data-field="guidance" value="{{ $profile?->guidancescomment ?? '' }}" placeholder="Click to edit..."></td>
                            <td class="cbcol-activities"><input type="text" class="cb-input desk-activities" data-sid="{{ $sid }}" data-field="activities" value="{{ $profile?->remark_on_other_activities ?? '' }}" placeholder="Click to edit..."></td>
                            <td class="cbcol-principal"><input type="text" class="cb-input desk-principal" data-sid="{{ $sid }}" data-field="principal" value="{{ $profile?->principalscomment ?? '' }}" placeholder="Click to edit..."></td>
                            <td class="cbcol-absence"><input type="number" class="cb-input absence-input desk-absence" data-sid="{{ $sid }}" data-field="absence" value="{{ $profile?->no_of_times_school_absent ?? '' }}" min="0"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Save Bar -->
        <div class="save-bar">
            <button type="button" id="saveBtn" class="btn-save-all">
                <i class="ri-save-3-line"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';

    /* ───────────────────────────────────────────────
       CONFIG
    ─────────────────────────────────────────────── */
    var SA       = {!! $cbAnalyticsJson !!};
    var SAVE_URL = '{{ route("classbroadsheet.updateComments", [$schoolclassid, $sessionid, $termid]) }}';
    var CSRF     = '{{ csrf_token() }}';

    var currentSid = null;
    var currentField = null;
    var commentModal = null;
    var pastCommentsData = [];

    /* ───────────────────────────────────────────────
       HELPERS
    ─────────────────────────────────────────────── */
    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function toast(msg, type) {
        document.querySelectorAll('.cb-toast').forEach(function (t) { t.remove(); });
        var icons = { success: 'checkbox-circle-fill', error: 'error-warning-fill', info: 'information-fill' };
        var el = document.createElement('div');
        el.className = 'cb-toast cb-toast-' + (type || 'info');
        el.innerHTML = '<i class="ri-' + (icons[type] || icons.info) + '" style="font-size:18px;flex-shrink:0;"></i> ' + esc(msg);
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 5000);
    }

    /* ───────────────────────────────────────────────
       CANONICAL DATA LAYER
    ─────────────────────────────────────────────── */
    var FIELD_MAP = {
        teacher:    'c_teacher_',
        guidance:   'c_guidance_',
        activities: 'c_activities_',
        principal:  'c_principal_',
        absence:    'c_absence_',
    };

    function getCanonical(sid, field) {
        var el = document.getElementById(FIELD_MAP[field] + sid);
        return el ? el.value : '';
    }

    function setCanonical(sid, field, value) {
        var el = document.getElementById(FIELD_MAP[field] + sid);
        if (el) el.value = value;
    }

    /* ───────────────────────────────────────────────
       COMMENT STATUS DISPLAY
    ─────────────────────────────────────────────── */
    function updateCommentTypesDisplay(sid) {
        var container = document.getElementById('comment-types-' + sid);
        if (!container) return;

        var hasTeacher = getCanonical(sid, 'teacher').trim() !== '';
        var hasGuidance = getCanonical(sid, 'guidance').trim() !== '';
        var hasActivities = getCanonical(sid, 'activities').trim() !== '';
        var hasPrincipal = getCanonical(sid, 'principal').trim() !== '';
        var hasAny = hasTeacher || hasGuidance || hasActivities || hasPrincipal;

        var types = [];
        if (hasTeacher) types.push('Teacher');
        if (hasGuidance) types.push('Guidance');
        if (hasActivities) types.push('Activities');
        if (hasPrincipal) types.push('Principal');

        if (hasAny) {
            container.innerHTML = types.map(function(type) {
                var icon = 'ri-chat-3-line';
                if (type === 'Teacher') icon = 'ri-chat-quote-line';
                if (type === 'Guidance') icon = 'ri-mental-health-line';
                if (type === 'Activities') icon = 'ri-football-line';
                if (type === 'Principal') icon = 'ri-government-line';
                return '<span class="comment-type-pill ct-' + type.toLowerCase() + '"><i class="' + icon + '"></i> ' + type + '</span>';
            }).join('');
        } else {
            container.innerHTML = '<span class="comment-status-badge no-comment">No comments yet</span>';
        }
    }

    /* ───────────────────────────────────────────────
       AUTO-SAVE FUNCTIONALITY
    ─────────────────────────────────────────────── */
    var debounceTimers = {};
    var AUTOSAVE_DELAY = 1200;

    function autoSaveStudent(sid) {
        var fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('_method', 'PATCH');
        fd.append('teacher_comments[' + sid + ']', getCanonical(sid, 'teacher'));
        fd.append('guidance_comments[' + sid + ']', getCanonical(sid, 'guidance'));
        fd.append('remarks_on_other_activities[' + sid + ']', getCanonical(sid, 'activities'));
        fd.append('principals_comments[' + sid + ']', getCanonical(sid, 'principal'));
        fd.append('no_of_times_school_absent[' + sid + ']', getCanonical(sid, 'absence'));

        fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF
            },
            body: fd,
        })
        .then(function(res) {
            return res.json().then(function(data) {
                if (!res.ok) throw new Error(data.message || 'Error');
                return data;
            });
        })
        .then(function(data) {
            if (data.success) {
                updateCommentTypesDisplay(sid);
                toast('Comment saved', 'success');
            } else {
                toast('Failed to save', 'error');
            }
        })
        .catch(function(err) {
            console.error(err);
            toast('Error saving comment', 'error');
        });
    }

    function scheduleAutosave(sid) {
        if (debounceTimers[sid]) clearTimeout(debounceTimers[sid]);
        debounceTimers[sid] = setTimeout(function() { autoSaveStudent(sid); }, AUTOSAVE_DELAY);
    }

    /* ───────────────────────────────────────────────
       SYNC HANDLER
    ─────────────────────────────────────────────── */
    function onInputChange(e) {
        var inp = e.target;
        var sid = inp.getAttribute('data-sid');
        var field = inp.getAttribute('data-field');
        if (!sid || !field) return;

        var val = inp.value;
        setCanonical(sid, field, val);

        // Mirror to twin
        var twinClass = inp.classList.contains('desk-' + field) ? 'mob-' + field : 'desk-' + field;
        document.querySelectorAll('.' + twinClass + '[data-sid="' + sid + '"]').forEach(function(twin) {
            if (twin !== inp) twin.value = val;
        });

        updateCommentTypesDisplay(sid);
        scheduleAutosave(sid);
    }

    /* ───────────────────────────────────────────────
       ENHANCED COMMENT MODAL
    ─────────────────────────────────────────────── */
    function openCommentModal(sid, field) {
        currentSid = sid;
        currentField = field;

        var row = document.querySelector('[data-student-id="' + sid + '"]');
        var name = row.getAttribute('data-student-name');
        var an = SA[sid] || {};

        document.getElementById('modalStudentName').textContent = name;

        var admElement = row.querySelector('.student-adm');
        document.getElementById('modalStudentMeta').textContent = admElement ? admElement.textContent : '';

        // Setup avatar
        var avatarEl = document.getElementById('modalStudentAvatar');
        var img = row.querySelector('img');
        if (img) {
            avatarEl.innerHTML = '<img src="' + img.src + '" alt="' + esc(name) + '">';
        } else {
            var initialsDiv = row.querySelector('.cb-avatar-initials');
            var initials = initialsDiv ? initialsDiv.textContent : 'ST';
            avatarEl.innerHTML = '<div class="initials">' + esc(initials) + '</div>';
        }

        // Performance summary with specific counts
        document.getElementById('modalPerformance').innerHTML = `
            <div class="perf-item"><div class="perf-label">Term Average</div><div class="perf-value">${an.term_average || 0}</div></div>
            <div class="perf-item"><div class="perf-label">Cumulative Average</div><div class="perf-value">${an.cum_average || 0}</div></div>
            <div class="perf-item"><div class="perf-label">Cumulative %</div><div class="perf-value">${an.cum_percentage || 0}%</div></div>
            <div class="perf-item"><div class="perf-label">Subjects</div><div class="perf-value">${an.subject_count || 0}</div></div>
        `;

        var labels = {
            teacher: "Teacher's Comment",
            guidance: "Counselor's Comment",
            activities: "Remark on Activities",
            principal: "Principal's Comment"
        };
        document.getElementById('modalCommentType').textContent = labels[field] || field;
        document.getElementById('modalCommentHint').textContent = 'Click on any past comment below to load it into this field';

        document.getElementById('modalTextarea').value = getCanonical(sid, field);
        document.getElementById('pastCommentsPanel').style.display = 'none';

        commentModal.show();
    }

    window.formatText = function(type) {
        var ta = document.getElementById('modalTextarea');
        var start = ta.selectionStart;
        var end = ta.selectionEnd;
        var text = ta.value;
        var selectedText = text.substring(start, end);
        var formatted = '';

        switch(type) {
            case 'bold':
                formatted = '**' + (selectedText || 'bold text') + '**';
                break;
            case 'italic':
                formatted = '*' + (selectedText || 'italic text') + '*';
                break;
            case 'bullet':
                formatted = (selectedText ? selectedText.split('\n').map(function(line) { return '• ' + line; }).join('\n') : '• ');
                break;
            case 'number':
                formatted = (selectedText ? selectedText.split('\n').map(function(line, i) { return (i+1) + '. ' + line; }).join('\n') : '1. ');
                break;
            case 'quote':
                formatted = '> ' + (selectedText || 'quote text');
                break;
            default:
                formatted = selectedText;
        }

        ta.value = text.substring(0, start) + formatted + text.substring(end);
        ta.focus();
        ta.setSelectionRange(start + formatted.length, start + formatted.length);
    };

    async function loadPastComments() {
        if (!currentSid) return;
        const listEl = document.getElementById('pastCommentsList');
        listEl.innerHTML = '<div class="text-center py-3"><i class="ri-loader-4-line ri-spin"></i> Loading past comments...</div>';

        try {
            const res = await fetch('/classbroadsheet/past-comments/' + currentSid);
            const data = await res.json();

            if (data.success && data.data.length) {
                // Update count badge
                document.getElementById('pastCommentCount').textContent = data.data.length;
                pastCommentsData = data.data;

                // Display comment counts summary
                var countsHtml = '<div class="mb-3 p-2 bg-light rounded"><small><strong>Comment History:</strong> ';
                countsHtml += '<span class="badge bg-info">Teacher: ' + data.counts.classteacher + '</span> ';
                countsHtml += '<span class="badge bg-secondary">Guidance: ' + data.counts.guidance + '</span> ';
                countsHtml += '<span class="badge bg-warning">Activities: ' + data.counts.activities + '</span> ';
                countsHtml += '<span class="badge bg-danger">Principal: ' + data.counts.principal + '</span>';
                countsHtml += ' | Total records: ' + data.counts.total + '</small></div>';

                listEl.innerHTML = countsHtml + data.data.map(function(p) {
                    // Find which comment exists
                    var commentText = '';
                    var commentType = '';
                    if (p.classteachercomment && p.classteachercomment.trim()) {
                        commentText = p.classteachercomment;
                        commentType = 'Teacher';
                    } else if (p.guidancescomment && p.guidancescomment.trim()) {
                        commentText = p.guidancescomment;
                        commentType = 'Guidance';
                    } else if (p.remark_on_other_activities && p.remark_on_other_activities.trim()) {
                        commentText = p.remark_on_other_activities;
                        commentType = 'Activities';
                    } else if (p.principalscomment && p.principalscomment.trim()) {
                        commentText = p.principalscomment;
                        commentType = 'Principal';
                    } else {
                        commentText = 'No specific comment type available';
                        commentType = 'Unknown';
                    }

                    return `
                        <div class="past-comment-item" onclick="loadPastIntoCurrent('${esc(commentText)}')">
                            <div class="past-comment-meta">
                                <i class="ri-calendar-line"></i> ${p.session} · ${p.term}
                                <span class="past-comment-type ct-${commentType.toLowerCase()}">${commentType}</span>
                                <span class="ms-2"><i class="ri-book-line"></i> ${p.class}</span>
                                ${p.no_of_times_school_absent ? '<span class="ms-2"><i class="ri-calendar-close-line"></i> Absent: ' + p.no_of_times_school_absent + 'x</span>' : ''}
                            </div>
                            <div class="past-comment-text">${esc(commentText.length > 200 ? commentText.substring(0, 200) + '...' : commentText)}</div>
                            <div class="text-muted small mt-1"><i class="ri-time-line"></i> ${p.date}</div>
                        </div>
                    `;
                }).join('');
            } else {
                listEl.innerHTML = '<div class="text-center py-3 text-muted"><i class="ri-inbox-line"></i><br>No past comments found for this student.</div>';
                document.getElementById('pastCommentCount').textContent = '0';
            }
            document.getElementById('pastCommentsPanel').style.display = 'block';
        } catch(e) {
            console.error(e);
            listEl.innerHTML = '<div class="text-center py-3 text-danger"><i class="ri-error-warning-line"></i><br>Failed to load past comments.</div>';
        }
    }

    window.loadPastIntoCurrent = function(text) {
        document.getElementById('modalTextarea').value = text;
        toast('Past comment loaded! You can edit it before saving.', 'info');
    };

    function saveFromModal() {
        const value = document.getElementById('modalTextarea').value.trim();
        setCanonical(currentSid, currentField, value);

        document.querySelectorAll('[data-sid="' + currentSid + '"][data-field="' + currentField + '"]').forEach(function(inp) {
            inp.value = value;
        });

        updateCommentTypesDisplay(currentSid);
        commentModal.hide();
        scheduleAutosave(currentSid);
        toast(currentField.charAt(0).toUpperCase() + currentField.slice(1) + ' comment saved!', 'success');
    }

    /* ───────────────────────────────────────────────
       SAVE ALL FUNCTIONALITY
    ─────────────────────────────────────────────── */
    function doSaveAll() {
        var fd = new FormData(document.getElementById('commentsForm'));
        fd.append('_token', CSRF);
        fd.append('_method', 'PATCH');

        var saveBtn = document.getElementById('saveBtn');
        var originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Saving...';
        saveBtn.disabled = true;

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: fd,
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                toast(data.message, 'success');
                // Refresh all comment displays
                document.querySelectorAll('.cb-student-row').forEach(function(row) {
                    var sid = row.getAttribute('data-student-id');
                    if (sid) updateCommentTypesDisplay(sid);
                });
            } else {
                toast(data.message || 'Save failed', 'error');
            }
        })
        .catch(function(err) {
            console.error(err);
            toast('Network error. Please try again.', 'error');
        })
        .finally(function() {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }

    /* ───────────────────────────────────────────────
       DOM READY
    ─────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        commentModal = new bootstrap.Modal(document.getElementById('cbCommentModal'));

        // Open modal on focus for all comment fields
        var commentSelectors = ['.desk-teacher', '.desk-guidance', '.desk-activities', '.desk-principal'];
        commentSelectors.forEach(function(selector) {
            document.querySelectorAll(selector).forEach(function(inp) {
                inp.addEventListener('focus', function() {
                    openCommentModal(this.dataset.sid, this.dataset.field);
                });
            });
        });

        document.getElementById('modalSaveBtn').addEventListener('click', saveFromModal);
        document.getElementById('btnLoadPastComments').addEventListener('click', loadPastComments);

        // Column Toggle
        document.querySelectorAll('.toggle-chip').forEach(function(chip) {
            chip.addEventListener('click', function() {
                var key = this.getAttribute('data-colkey');
                var show = this.classList.toggle('active') ? '' : 'none';
                document.querySelectorAll('.cbcol-' + key).forEach(function(el) {
                    el.style.display = show;
                });
            });
        });

        // Search functionality
        var searchEl = document.getElementById('searchInput');
        if (searchEl) {
            searchEl.addEventListener('input', function() {
                var q = this.value.toLowerCase().trim();
                document.querySelectorAll('.cb-student-row').forEach(function(row) {
                    var key = (row.getAttribute('data-searchkey') || '').toLowerCase();
                    row.style.display = (!q || key.includes(q)) ? '' : 'none';
                });
            });
        }

        // Attach input listeners for auto-save
        var fieldSelectors = ['.desk-teacher', '.desk-guidance', '.desk-activities', '.desk-principal', '.desk-absence'];
        fieldSelectors.forEach(function(sel) {
            document.querySelectorAll(sel).forEach(function(inp) {
                inp.addEventListener('input', onInputChange);
            });
        });

        // Save all button
        document.getElementById('saveBtn').addEventListener('click', doSaveAll);

        // Calculate and display average cumulative percentage and top performer
        var totalCumPct = 0;
        var studentCount = 0;
        var topPerformer = { name: '', pct: 0 };

        document.querySelectorAll('.cb-student-row').forEach(function(row) {
            var sid = row.getAttribute('data-student-id');
            var an = SA[sid];
            if (an && an.cum_percentage) {
                totalCumPct += an.cum_percentage;
                studentCount++;
                if (an.cum_percentage > topPerformer.pct) {
                    topPerformer.pct = an.cum_percentage;
                    topPerformer.name = row.getAttribute('data-student-name');
                }
            }
        });

        if (studentCount > 0) {
            var avgPct = (totalCumPct / studentCount).toFixed(1);
            document.getElementById('statPassRate').textContent = avgPct + '%';
        }

        if (topPerformer.name) {
            document.getElementById('statTop').innerHTML = '<i class="ri-award-line"></i> ' + esc(topPerformer.name.split(' ').slice(0,2).join(' '));
        }
    });
})();
</script>

@endsection
