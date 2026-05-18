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

/* Hero */
.cb-hero { background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%); border-radius: var(--cb-radius); padding: 32px 36px; margin-bottom: 28px; position: relative; overflow: hidden; }
.cb-hero::before { content: ''; position: absolute; top: -80px; right: -80px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%); border-radius: 50%; }
.cb-hero h1 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #fff; margin: 0 0 8px; }
.cb-hero p { font-size: 13px; color: rgba(255,255,255,.72); margin: 0; }
.cb-hero .meta-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
.cb-meta-pill { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; color: #fff; display: inline-flex; align-items: center; gap: 5px; }

/* Stat cards */
.cb-stat { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); padding: 20px 22px; position: relative; overflow: hidden; transition: transform .15s, box-shadow .15s; }
.cb-stat:hover { transform: translateY(-2px); box-shadow: var(--cb-shadow); }
.cb-stat .stat-accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--cb-radius) var(--cb-radius) 0 0; }
.cb-stat .stat-value { font-size: 30px; font-weight: 700; color: var(--cb-navy); line-height: 1; margin-top: 8px; }
.cb-stat .stat-label { font-size: 12px; color: var(--cb-muted); margin-top: 5px; font-weight: 500; }
.cb-stat .stat-ico { font-size: 36px; opacity: .08; position: absolute; right: 16px; top: 50%; transform: translateY(-50%); }

/* Column Toggle */
.col-toggle-panel { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); padding: 18px 22px; margin-bottom: 22px; box-shadow: var(--cb-shadow); }
.col-toggle-panel h6 { font-size: 13px; font-weight: 700; color: var(--cb-navy); margin: 0 0 14px; display: flex; align-items: center; gap: 7px; }
.toggle-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.toggle-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--cb-border); background: var(--cb-surface); color: var(--cb-muted); transition: all .18s ease; user-select: none; }
.toggle-chip:hover { border-color: var(--cb-teal); color: var(--cb-teal); }
.toggle-chip.active { background: var(--cb-teal); border-color: var(--cb-teal); color: #fff; box-shadow: 0 2px 8px rgba(13,148,136,.3); }

/* Main card */
.cb-card { background: var(--cb-white); border: 1px solid var(--cb-border); border-radius: var(--cb-radius); box-shadow: var(--cb-shadow); overflow: hidden; }
.cb-card-header { padding: 18px 24px; border-bottom: 1px solid var(--cb-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(to right, #f8fafc, #f0fdf9); }
.cb-card-header h5 { font-size: 15px; font-weight: 700; color: var(--cb-navy); margin: 0; display: flex; align-items: center; gap: 8px; }

/* Table */
.cb-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.cb-table thead th { background: var(--cb-navy); color: #fff; padding: 11px 14px; font-weight: 600; font-size: 11.5px; white-space: nowrap; text-align: center; border-right: 1px solid rgba(255,255,255,.08); }
.cb-table thead th.col-name-hdr { text-align: left; }
.cb-table tbody td { padding: 10px 14px; vertical-align: middle; border-bottom: 1px solid var(--cb-border); text-align: center; }
.cb-table tbody td.td-name { text-align: left; }
.cb-table tbody tr:hover td { background: #f0fdf9; }

/* Row status */
.cb-table tbody tr.row-has-comment td.td-name { border-left: 3px solid var(--cb-teal); }
.cb-table tbody tr.row-no-comment  td.td-name { border-left: 3px solid var(--cb-border); }

.score-dual { display: flex; flex-direction: column; gap: 2px; min-width: 80px; }
.score-row { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 2px 5px; border-radius: 5px; font-size: 11px; font-weight: 700; }
.score-row-term { background: rgba(14,165,233,.08); border-left: 2.5px solid #0ea5e9; }
.score-row-cum  { background: rgba(15,35,66,.06); border-left: 2.5px solid var(--cb-navy); }
.score-lbl { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; opacity: .7; }

.grade-badge { display: inline-block; padding: 1px 5px; border-radius: 8px; font-size: 9px; font-weight: 700; }
.g-a,.g-a1 { background: #dcfce7; color: #15803d; }
.g-b,.g-b2,.g-b3 { background: #dbeafe; color: #1d4ed8; }
.g-c,.g-c4,.g-c5,.g-c6 { background: #fef9c3; color: #a16207; }
.g-d,.g-d7 { background: #ffedd5; color: #c2410c; }
.g-e,.g-e8 { background: #ffe4e6; color: #be123c; }
.g-f,.g-f9 { background: #fee2e2; color: #b91c1c; }

.analytics-cell { min-width: 130px; font-size: 11px; line-height: 1.4; }
.analytics-row { display: flex; justify-content: space-between; align-items: center; padding: 2px 0; gap: 6px; }
.analytics-lbl { color: var(--cb-muted); font-size: 10px; font-weight: 500; }
.analytics-val { font-weight: 700; color: var(--cb-navy); font-size: 11.5px; }
.pct-bar-wrap { background: #e2e8f0; border-radius: 4px; height: 5px; margin-top: 3px; overflow: hidden; }
.pct-bar { height: 100%; border-radius: 4px; }

.cb-input { border: 1.5px solid var(--cb-border); border-radius: 8px; padding: 8px 10px; font-size: 13px; width: 100%; transition: border .15s; background: var(--cb-surface); }
.cb-input:focus { border-color: var(--cb-teal); outline: none; box-shadow: 0 0 0 3px rgba(13,148,136,.12); background: #fff; }
.absence-input { width: 72px !important; text-align: center; }

.student-name-cell { display: flex; align-items: center; gap: 9px; }
.cb-avatar { width: 38px; height: 38px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid var(--cb-border); cursor: pointer; transition: all .15s; }
.cb-avatar:hover { border-color: var(--cb-teal); transform: scale(1.05); }
.cb-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cb-avatar-initials { background: linear-gradient(135deg, var(--cb-teal), var(--cb-sky)); color: #fff; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; }

/* Enhanced Comment Features */
.comment-type-pill {
    font-size: 10px; padding: 1px 6px; border-radius: 12px; margin: 2px 1px;
    font-weight: 600; display: inline-flex; align-items: center; gap: 3px;
}
.ct-teacher   { background: #e0f2fe; color: #0369a1; }
.ct-guidance  { background: #f3e8ff; color: #6b21a8; }
.ct-activities{ background: #fef3c7; color: #854d0e; }

#cbCommentModal .modal-content { border-radius: 20px; overflow: hidden; }
#cbCommentModal .modal-header { background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal)); color: #fff; }
.student-modal-header { display: flex; align-items: center; gap: 14px; }
.past-comment-item {
    border-left: 4px solid var(--cb-teal); background: #f8fafc; padding: 12px;
    margin-bottom: 10px; border-radius: 8px; cursor: pointer; transition: all .2s;
}
.past-comment-item:hover { background: #f0fdf9; transform: translateX(6px); }
.past-comment-meta { font-size: 10.5px; color: var(--cb-muted); margin-bottom: 4px; }
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
        <span class="toggle-chip active" data-colkey="absence"><i class="ri-calendar-close-line"></i> Absences</span>
    </div>
</div>

@php $cbAnalyticsJson = json_encode($studentAnalytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); @endphp

<!-- Comment Modal -->
<div class="modal fade" id="cbCommentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="student-modal-header w-100">
                    <div id="modalStudentAvatar" class="cb-avatar" style="width:56px;height:56px;font-size:20px;"></div>
                    <div style="flex:1">
                        <h5 class="mb-1 text-white" id="modalStudentName"></h5>
                        <div class="text-white-50 small" id="modalStudentMeta"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="performance-strip mb-3" id="modalPerformance"></div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0" id="modalCommentType"></h6>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnLoadPastComments">
                        <i class="ri-history-line"></i> Past Comments
                    </button>
                </div>

                <textarea id="modalTextarea" class="cb-input" rows="8" style="width:100%; resize:vertical; font-size:14px; line-height:1.6;"></textarea>

                <div class="mt-2 mb-3">
                    <button type="button" class="btn btn-sm btn-light me-1" onclick="formatText('bold')">𝐁 Bold</button>
                    <button type="button" class="btn btn-sm btn-light me-1" onclick="formatText('italic')">𝑖 Italic</button>
                    <button type="button" class="btn btn-sm btn-light" onclick="formatText('bullet')">• Bullet</button>
                </div>

                <div id="pastCommentsPanel" style="display:none; max-height:320px; overflow-y:auto; border:1px solid var(--cb-border); border-radius:12px; padding:12px;">
                    <h6 class="small text-muted mb-2">Past Comments (Click to load into current field)</h6>
                    <div id="pastCommentsList"></div>
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
        <h5><i class="ri-table-alt-line" style="color:var(--cb-teal)"></i> Student Performance &amp; Comments <span class="badge bg-teal">{{ $students->count() }} Students</span></h5>
        <div class="cb-search" style="max-width:260px;">
            <i class="ri-search-line"></i>
            <input type="text" id="searchInput" placeholder="Search students…">
        </div>
    </div>

    <form id="commentsForm">
        @csrf
        <input type="hidden" name="_method" value="PATCH">

        <!-- Canonical Fields -->
        @foreach ($students as $student)
            @php
                $sid = $student->id;
                $profile = $personalityProfiles->where('studentid', $sid)->first();
            @endphp
            <input type="hidden" class="canonical-teacher"    id="c_teacher_{{ $sid }}"    name="teacher_comments[{{ $sid }}]"            value="{{ $profile?->classteachercomment ?? '' }}">
            <input type="hidden" class="canonical-guidance"   id="c_guidance_{{ $sid }}"   name="guidance_comments[{{ $sid }}]"           value="{{ $profile?->guidancescomment ?? '' }}">
            <input type="hidden" class="canonical-activities" id="c_activities_{{ $sid }}" name="remarks_on_other_activities[{{ $sid }}]"  value="{{ $profile?->remark_on_other_activities ?? '' }}">
            <input type="hidden" class="canonical-absence"    id="c_absence_{{ $sid }}"    name="no_of_times_school_absent[{{ $sid }}]"   value="{{ $profile?->no_of_times_school_absent ?? '' }}">
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
                        @endphp
                        <tr class="cb-student-row" data-student-id="{{ $sid }}" data-student-name="{{ $fullName }}" data-searchkey="{{ strtolower($fullName . ' ' . ($student->admissionNo ?? '')) }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="td-name">
                                <div class="student-name-cell">
                                    @if($imgUrl)
                                        <div class="cb-avatar cb-avatar-trigger" data-img="{{ $imgUrl }}" data-name="{{ $fullName }}" data-adm="{{ $student->admissionNo }}" data-class="{{ $schoolclass?->schoolclass . ' ' . $schoolclass?->arm }}" data-gender="{{ $student->gender }}">
                                            <img src="{{ $imgUrl }}" alt="{{ $fullName }}">
                                        </div>
                                    @else
                                        <div class="cb-avatar cb-avatar-initials cb-avatar-trigger" data-name="{{ $fullName }}" data-adm="{{ $student->admissionNo }}" data-class="{{ $schoolclass?->schoolclass . ' ' . $schoolclass?->arm }}" data-initials="{{ $initials }}">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="student-name-text">{{ $fullName }}</div>
                                        <div class="student-adm">{{ $student->admissionNo }} · {{ $student->gender ?? '' }}</div>
                                        <span class="comment-status-dot" id="status-{{ $sid }}"></span>
                                    </div>
                                </div>
                            </td>

                            @foreach ($subjects as $subject)
                                @php
                                    $tScore = $termScoreMap[$sid][$subject->subject] ?? 0;
                                    $cScore = $cumScoreMap[$sid][$subject->subject] ?? 0;
                                    $tGrade = $cGrade = '-';
                                    if ($isSenior) {
                                        if ($tScore >= 75) $tGrade='A1'; elseif ($tScore >= 70) $tGrade='B2';
                                        elseif ($tScore >= 65) $tGrade='B3'; elseif ($tScore >= 60) $tGrade='C4';
                                        elseif ($tScore >= 55) $tGrade='C5'; elseif ($tScore >= 50) $tGrade='C6';
                                        elseif ($tScore >= 45) $tGrade='D7'; elseif ($tScore >= 40) $tGrade='E8';
                                        elseif ($tScore > 0) $tGrade='F9';
                                        if ($cScore >= 75) $cGrade='A1'; elseif ($cScore >= 70) $cGrade='B2';
                                        elseif ($cScore >= 65) $cGrade='B3'; elseif ($cScore >= 60) $cGrade='C4';
                                        elseif ($cScore >= 55) $cGrade='C5'; elseif ($cScore >= 50) $cGrade='C6';
                                        elseif ($cScore >= 45) $cGrade='D7'; elseif ($cScore >= 40) $cGrade='E8';
                                        elseif ($cScore > 0) $cGrade='F9';
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

                            <td class="cbcol-teacher">
                                <input type="text" class="cb-input desk-teacher" data-sid="{{ $sid }}" data-field="teacher" value="{{ $profile?->classteachercomment ?? '' }}" placeholder="Click to edit...">
                            </td>
                            <td class="cbcol-guidance">
                                <input type="text" class="cb-input desk-guidance" data-sid="{{ $sid }}" data-field="guidance" value="{{ $profile?->guidancescomment ?? '' }}" placeholder="Click to edit...">
                            </td>
                            <td class="cbcol-activities">
                                <input type="text" class="cb-input desk-activities" data-sid="{{ $sid }}" data-field="activities" value="{{ $profile?->remark_on_other_activities ?? '' }}" placeholder="Click to edit...">
                            </td>
                            <td class="cbcol-absence">
                                <input type="number" class="cb-input absence-input desk-absence" data-sid="{{ $sid }}" data-field="absence" value="{{ $profile?->no_of_times_school_absent ?? '' }}" min="0">
                            </td>
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

    var SA = {!! $cbAnalyticsJson !!};
    var SAVE_URL = '{{ route("classbroadsheet.updateComments", [$schoolclassid, $sessionid, $termid]) }}';
    var CSRF = '{{ csrf_token() }}';
    var currentSid = null;
    var currentField = null;
    var commentModal = null;

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    function toast(msg, type = 'info') {
        alert(msg); // Replace with your toast system if needed
    }

    var FIELD_MAP = {
        teacher: 'c_teacher_',
        guidance: 'c_guidance_',
        activities: 'c_activities_',
        absence: 'c_absence_'
    };

    function getCanonical(sid, field) {
        var el = document.getElementById(FIELD_MAP[field] + sid);
        return el ? el.value : '';
    }

    function setCanonical(sid, field, value) {
        var el = document.getElementById(FIELD_MAP[field] + sid);
        if (el) el.value = value;
    }

    function refreshCommentStatus() {
        document.querySelectorAll('.cb-student-row').forEach(function(row) {
            var sid = row.getAttribute('data-student-id');
            if (!sid) return;

            var t = getCanonical(sid, 'teacher').trim();
            var g = getCanonical(sid, 'guidance').trim();
            var a = getCanonical(sid, 'activities').trim();

            var statusEl = document.getElementById('status-' + sid);
            if (statusEl) {
                var html = '';
                if (t) html += '<span class="comment-type-pill ct-teacher">T</span>';
                if (g) html += '<span class="comment-type-pill ct-guidance">G</span>';
                if (a) html += '<span class="comment-type-pill ct-activities">A</span>';
                statusEl.innerHTML = html || '○ No comment';
            }
        });
    }

    function openCommentModal(sid, field) {
        currentSid = sid;
        currentField = field;

        var row = document.querySelector(`[data-student-id="${sid}"]`);
        var name = row.getAttribute('data-student-name');
        var an = SA[sid] || {};

        document.getElementById('modalStudentName').textContent = name;
        document.getElementById('modalStudentMeta').textContent = row.querySelector('.student-adm').textContent || '';

        // Avatar
        var avatarEl = document.getElementById('modalStudentAvatar');
        var img = row.querySelector('img');
        if (img) {
            avatarEl.innerHTML = `<img src="${img.src}" style="width:100%;height:100%;object-fit:cover;">`;
        } else {
            avatarEl.textContent = row.querySelector('.cb-avatar-initials')?.textContent || 'ST';
        }

        document.getElementById('modalPerformance').innerHTML = `
            Term Avg: <strong>${an.term_average || 0}</strong> |
            Cum Avg: <strong>${an.cum_average || 0}</strong> |
            Cum %: <strong>${an.cum_percentage || 0}%</strong>
        `;

        const labels = { teacher: "Teacher's Comment", guidance: "Counselor's Comment", activities: "Remark on Activities" };
        document.getElementById('modalCommentType').textContent = labels[field] || field;

        document.getElementById('modalTextarea').value = getCanonical(sid, field);
        document.getElementById('pastCommentsPanel').style.display = 'none';

        commentModal.show();
    }

    window.formatText = function(type) {
        var ta = document.getElementById('modalTextarea');
        if (type === 'bold') ta.value += ' **Bold Text** ';
        else if (type === 'italic') ta.value += ' *Italic Text* ';
        else if (type === 'bullet') ta.value += '\n• ';
        ta.focus();
    };

    async function loadPastComments() {
        if (!currentSid) return;
        const list = document.getElementById('pastCommentsList');
        list.innerHTML = '<p class="text-center">Loading past comments...</p>';

        try {
            const res = await fetch(`/classbroadsheet/past-comments/${currentSid}`);
            const data = await res.json();

            if (data.success && data.data.length) {
                list.innerHTML = data.data.map(p => `
                    <div class="past-comment-item" onclick="loadPastIntoCurrent('${esc(p.classteachercomment || p.guidancescomment || p.remark_on_other_activities || '')}')">
                        <div class="past-comment-meta">${p.session} • ${p.term} • ${p.class}</div>
                        <div style="font-size:13.5px;">${esc(p.classteachercomment || p.guidancescomment || p.remark_on_other_activities || '—')}</div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<p class="text-muted text-center">No past comments found for this student.</p>';
            }
            document.getElementById('pastCommentsPanel').style.display = 'block';
        } catch(e) {
            list.innerHTML = '<p class="text-danger text-center">Failed to load past comments.</p>';
        }
    }

    window.loadPastIntoCurrent = function(text) {
        document.getElementById('modalTextarea').value = text;
    };

    function saveFromModal() {
        if (!currentSid || !currentField) return;
        const value = document.getElementById('modalTextarea').value.trim();
        setCanonical(currentSid, currentField, value);

        document.querySelectorAll(`[data-sid="${currentSid}"][data-field="${currentField}"]`).forEach(function(inp) {
            inp.value = value;
        });

        refreshCommentStatus();
        commentModal.hide();
        toast('Comment saved successfully!', 'success');
    }

    document.addEventListener('DOMContentLoaded', function () {
        commentModal = new bootstrap.Modal(document.getElementById('cbCommentModal'));

        // Open modal when clicking comment fields
        document.querySelectorAll('.desk-teacher, .desk-guidance, .desk-activities').forEach(function(inp) {
            inp.addEventListener('focus', function() {
                openCommentModal(this.getAttribute('data-sid'), this.getAttribute('data-field'));
            });
        });

        document.getElementById('modalSaveBtn').addEventListener('click', saveFromModal);
        document.getElementById('btnLoadPastComments').addEventListener('click', loadPastComments);

        refreshCommentStatus();
    });
})();
</script>

@endsection
