@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Grade colors */
    .grade-a1 { background-color: #dcfce7 !important; color: #166534; font-weight: bold; }
    .grade-b2 { background-color: #dbeafe !important; color: #1e40af; }
    .grade-b3 { background-color: #e0eeff !important; color: #1e40af; }
    .grade-c4 { background-color: #fef9c3 !important; color: #854d0e; }
    .grade-c5 { background-color: #fef3c7 !important; color: #92400e; }
    .grade-c6 { background-color: #fde68a !important; color: #78350f; }
    .grade-d7 { background-color: #ffedd5 !important; color: #9a3412; }
    .grade-e8 { background-color: #fed7aa !important; color: #9a3412; }
    .grade-f9 { background-color: #fee2e2 !important; color: #991b1b; font-weight: bold; }

    /* ── Animations ── */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes countUp {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes progressFill {
        from { width: 0; }
    }
    @keyframes rowSlide {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    @keyframes glowPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
    }
    @keyframes floatUp {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-fade-up { animation: fadeInUp 0.5s ease both; }
    .animate-fade-left { animation: fadeInLeft 0.5s ease both; }
    .animate-fade-right { animation: fadeInRight 0.5s ease both; }
    .animate-scale { animation: scaleIn 0.4s ease both; }
    .animate-count { animation: countUp 0.6s ease both; }

    .broadsheet-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        background: white;
        border: 1.5px solid #1e3a5f;
    }

    .broadsheet-table thead tr.subject-header th {
        background: #1e3a5f;
        color: white;
        text-align: center;
        padding: 7px 4px;
        border: 0.5px solid #2563eb55;
        font-weight: bold;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .broadsheet-table thead tr.subject-header th.student-col {
        background: #0f2040;
        text-align: left;
        padding-left: 6px;
    }

    .broadsheet-table thead tr.subject-header th.subj-name-hdr {
        background: #163562;
        border-left: 1.5px solid #2563eb;
        font-size: 10px;
        white-space: normal;
        word-break: break-word;
        min-width: 60px;
    }

    .broadsheet-table thead tr.assessment-header th {
        background: #1a3d6a;
        color: #a8d4ef;
        text-align: center;
        padding: 5px 3px;
        border: 0.5px solid #2563eb33;
        font-size: 10px;
        white-space: nowrap;
    }

    .broadsheet-table thead tr.assessment-header th.sub-boundary {
        border-left: 1.5px solid #2563eb;
    }

    .broadsheet-table tbody tr {
        animation: rowSlide 0.4s ease both;
        transition: all 0.25s ease;
    }
    .broadsheet-table tbody tr:nth-child(odd)  { background: #ffffff; }
    .broadsheet-table tbody tr:nth-child(even) { background: #f0f4fa; }
    .broadsheet-table tbody tr:hover {
        background-color: #e8f0fe !important;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .broadsheet-table tbody td {
        padding: 5px 4px;
        border: 0.5px solid #c5d3e8;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 11px;
        transition: all 0.2s ease;
    }

    .broadsheet-table tbody td.student-info-cell {
        text-align: left;
        padding-left: 8px;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: inherit;
        z-index: 5;
        min-width: 200px;
    }

    .score-cell {
        transition: all 0.2s ease;
    }
    .score-cell:hover {
        transform: scale(1.05);
        filter: brightness(0.95);
    }

    /* Position Badge */
    .pos-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 800;
        border: 2px solid;
        transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        cursor: pointer;
    }
    .pos-badge:hover {
        transform: scale(1.18) rotate(-5deg);
        animation: glowPulse 0.8s ease infinite;
    }
    .pos-1 { background: linear-gradient(135deg, #fef9c3, #fde68a); border-color: #f59e0b; color: #92400e; }
    .pos-2 { background: linear-gradient(135deg, #f1f5f9, #e2e8f0); border-color: #94a3b8; color: #475569; }
    .pos-3 { background: linear-gradient(135deg, #ffedd5, #fed7aa); border-color: #f97316; color: #9a3412; }
    .pos-other { background: #f0f4fa; border-color: #c5d3e8; color: #6b7280; }

    /* Progress Bar */
    .progress-bar-wrap {
        background: #e2e8f0;
        border-radius: 4px;
        height: 4px;
        overflow: hidden;
        margin-top: 3px;
    }
    .progress-bar {
        height: 100%;
        border-radius: 4px;
        background: #f43f5e;
        transition: background-color 0.8s ease;
        animation: progressFill 0.8s ease both;
    }

    /* Performance Summary Button */
    .perf-summary-btn {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        border: none;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .perf-summary-btn:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, #2563eb, #1e3a5f);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
    }

    /* Score colors */
    .score-red { color: #dc2626 !important; font-weight: bold; }
    .score-amber { color: #d97706 !important; font-weight: bold; }
    .score-green { color: #16a34a !important; font-weight: bold; }

    .gpa-cell {
        background: #eff6ff !important;
        color: #1e3a8a;
        font-weight: bold;
        border-left: 1.5px solid #3b82f6 !important;
        transition: all 0.2s ease;
    }
    .gpa-cell:hover {
        background: #dbeafe !important;
        transform: scale(1.02);
    }

    /* Stats rows */
    .stats-row td {
        background: #1e3a5f !important;
        color: white;
        font-weight: bold;
        padding: 5px 4px;
        text-align: center;
        border: 0.5px solid #163785;
        white-space: nowrap;
        font-size: 11px;
    }
    .stats-row td.stats-label { text-align: left; padding-left: 6px; font-size: 10px; }
    .stats-hi td { background: #0a2240 !important; }
    .stats-lo td { background: #111c2a !important; }

    /* Meta grid */
    .meta-grid {
        display: flex;
        border: 1px solid #c5d3e8;
        background: #f0f4fa;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 12px;
        animation: fadeInUp 0.5s ease;
    }
    .meta-cell { flex: 1; padding: 8px 12px; border-right: 1px solid #c5d3e8; transition: all 0.2s ease; }
    .meta-cell:last-child { border-right: none; }
    .meta-cell:hover { background: #e8f0fe; transform: translateY(-2px); }
    .meta-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; display: block; }
    .meta-value { font-size: 13px; font-weight: bold; color: #1e3a5f; }

    /* Grade key */
    .grade-key {
        display: flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        padding: 6px 12px;
        background: #fafafa;
        border-radius: 6px;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 6px;
        animation: fadeInLeft 0.5s ease;
    }
    .grade-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .grade-item:hover {
        transform: translateY(-2px) scale(1.05);
    }

    /* School header */
    .school-header-bar {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        border-radius: 10px;
        padding: 18px 24px;
        margin-bottom: 18px;
        color: white;
        animation: fadeInUp 0.6s ease;
    }

    /* Toast notification */
    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99999;
        background: #1e3a5f;
        color: white;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        min-width: 220px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        animation: slideInRight 0.3s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Stat cards */
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        animation: scaleIn 0.4s ease both;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1e3a5f;
        animation: countUp 0.6s ease both;
    }
    .stat-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Performance Modal */
    .perf-modal .modal-content {
        border-radius: 16px;
        overflow: hidden;
        animation: scaleIn 0.3s ease;
    }
    .perf-modal .modal-header {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        color: white;
        border: none;
        padding: 20px 24px;
    }
    .perf-modal .modal-body {
        padding: 24px;
    }
    .perf-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 16px;
    }
    .perf-stat-item {
        background: linear-gradient(135deg, #f8fafc, #f0fdf9);
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    .perf-stat-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #2563eb;
    }
    .perf-stat-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .perf-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1e3a5f;
    }
    .perf-subjects-list {
        max-height: 300px;
        overflow-y: auto;
        margin-top: 16px;
    }
    .perf-subject-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .perf-subject-item:hover {
        background: #f0fdf9;
        transform: translateX(5px);
    }
    .perf-subject-name {
        font-weight: 600;
        color: #1e3a5f;
    }
    .perf-subject-scores {
        display: flex;
        gap: 12px;
    }
    .perf-subject-score {
        font-size: 11px;
    }
    .progress-bar-lg {
        background: #e2e8f0;
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
        margin-top: 8px;
    }
    .progress-bar-lg .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.8s ease;
    }

    .student-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        transition: transform 0.2s ease;
    }
    .student-avatar:hover {
        transform: scale(1.1);
    }
    .spin { animation: spin 0.8s linear infinite; }

    @media print {
        .no-print { display: none !important; }
        .perf-modal { display: none !important; }
        .broadsheet-table { font-size: 8px; }
        .stat-card, .meta-cell, .grade-item { animation: none !important; }
        .broadsheet-table tbody tr { animation: none !important; }
        .progress-bar { animation: none !important; }
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Title --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Class Broadsheet</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Results</a></li>
                                <li class="breadcrumb-item active">Class Broadsheet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Cards Row --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card animate-scale" style="animation-delay: 0.05s">
                        <div class="stat-value" id="statTotalStudents">{{ $totalStudents }}</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card animate-scale" style="animation-delay: 0.1s">
                        <div class="stat-value" id="statTotalSubjects">{{ count($subjects) }}</div>
                        <div class="stat-label">Subjects</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card animate-scale" style="animation-delay: 0.15s">
                        <div class="stat-value" id="statAvgPercentage">0%</div>
                        <div class="stat-label">Avg % (Cumulative)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card animate-scale" style="animation-delay: 0.2s">
                        <div class="stat-value" id="statTopPerformer">—</div>
                        <div class="stat-label">Top Performer</div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show animate-fade-up">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- School Header --}}
            <div class="school-header-bar">
                <div class="d-flex align-items-center">
                    @if(!empty($school_logo_base64))
                        <img src="{{ $school_logo_base64 }}" alt="Logo"
                             style="width:65px;height:65px;object-fit:contain;border-radius:50%;border:2px solid white;margin-right:16px;animation: floatUp 3s ease-in-out infinite;">
                    @endif
                    <div class="flex-grow-1 text-center">
                        <h4 class="mb-1 fw-bold text-uppercase">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</h4>
                        @if(!empty($schoolInfo->school_address))
                            <p class="mb-1 opacity-75" style="font-size:13px;">{{ $schoolInfo->school_address }}</p>
                        @endif
                        @if(!empty($schoolInfo->school_motto))
                            <p class="mb-0 fst-italic opacity-75" style="font-size:12px;">"{{ $schoolInfo->school_motto }}"</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Title Strip --}}
            <div class="animate-fade-up" style="background:#1e3a5f;color:white;text-align:center;padding:10px;font-size:15px;font-weight:bold;letter-spacing:1.5px;border-radius:6px;margin-bottom:14px;">
                CLASS ACADEMIC BROADSHEET
                @if(!empty($is_combined))
                    <span style="font-size:11px;opacity:.75;font-weight:400;margin-left:8px;">— Combined Arms</span>
                @endif
            </div>

            {{-- Meta Grid --}}
            <div class="meta-grid">
                <div class="meta-cell animate-fade-left" style="animation-delay: 0.05s">
                    <span class="meta-label">Class</span>
                    <span class="meta-value">{{ $schoolclass->schoolclass ?? '-' }} {{ $schoolclass->arm_name ?? '' }}</span>
                </div>
                <div class="meta-cell animate-fade-left" style="animation-delay: 0.1s">
                    <span class="meta-label">Academic Session</span>
                    <span class="meta-value">{{ $schoolsession->session ?? '-' }}</span>
                </div>
                <div class="meta-cell animate-fade-left" style="animation-delay: 0.15s">
                    <span class="meta-label">Term</span>
                    <span class="meta-value">{{ $schoolterm->term ?? '-' }}</span>
                </div>
                <div class="meta-cell animate-fade-left" style="animation-delay: 0.2s">
                    <span class="meta-label">Generated</span>
                    <span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span>
                </div>
            </div>

            {{-- Grade Key --}}
            <div class="grade-key">
                <strong style="color:#1e3a5f;margin-right:8px;font-size:12px;">GRADING SCALE:</strong>
                @php
                $gradeKey = [
                    'A1'=>['75-100','#16a34a'],'B2'=>['70-74','#1d4ed8'],'B3'=>['65-69','#2563eb'],
                    'C4'=>['60-64','#d97706'],'C5'=>['55-59','#b45309'],'C6'=>['50-54','#92400e'],
                    'D7'=>['45-49','#ea580c'],'E8'=>['40-44','#c2410c'],'F9'=>['0-39','#dc2626'],
                ];
                @endphp
                @foreach($gradeKey as $grade => $info)
                    <span class="grade-item animate-scale" style="animation-delay: {{ $loop->index * 0.03 }}s">
                        <span class="badge me-1" style="background:{{ $info[1] }};font-size:11px;">{{ $grade }} ({{ $info[0] }})</span>
                    </span>
                @endforeach
                <span class="text-muted ms-2" style="font-size:11px;">
                    <strong>BF</strong>=Brought Forward &nbsp;
                    <strong>CUM</strong>=Cumulative &nbsp;
                    <strong>POS</strong>=Position
                </span>
            </div>

            {{-- Toolbar --}}
            <div class="card shadow-sm mb-3 no-print animate-fade-up">
                <div class="card-body py-2">
                    <div class="row align-items-center g-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" class="form-control" id="searchStudent"
                                       placeholder="Search by name or admission number…">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="locateStudent">
                                <option value="">🔍 Locate Student…</option>
                                <option value="top5">🏆 Top 5 Students (by GPA)</option>
                                <option value="top10">⭐ Top 10 Students</option>
                                <option value="failures">⚠️ Students with F9</option>
                                <option value="below_avg">📉 Below Class Average</option>
                                <option value="missing_scores">❌ Missing Scores</option>
                                <option disabled>──────────</option>
                                @foreach($studentRows as $student)
                                    <option value="student_{{ $student['id'] }}">
                                        👤 {{ $student['lastname'] }}, {{ $student['firstname'] }}
                                        ({{ $student['admissionno'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-sm btn-outline-secondary me-1" onclick="window.print()">
                                <i class="ri-printer-line me-1"></i>Print
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="scrollToTop()">
                                <i class="ri-arrow-up-line me-1"></i>Top
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="card shadow-sm mb-4 animate-fade-up">
                <div class="card-body p-0">
                    <div style="overflow-x:auto;">
                        @php
                            $selected = $selectedColumns ?? [];
                            $showAdmNo = empty($selected) || in_array('admission_no', $selected);
                            $showGender = in_array('gender', $selected);
                            $showTotal = empty($selected) || in_array('total', $selected);
                            $showBF = in_array('bf', $selected);
                            $showCum = empty($selected) || in_array('cum', $selected);
                            $showGrade = empty($selected) || in_array('grade', $selected);
                            $showPosition = empty($selected) || in_array('position', $selected);
                            $showAvg = in_array('class_average', $selected);
                            $showRemark = in_array('remark', $selected);
                            $showGPA = in_array('gpa', $selected);
                            $showCGPA = in_array('cgpa', $selected);
                            $showGPAGrade = in_array('gpa_grade', $selected);
                            $showNumSub = in_array('num_subjects', $selected);
                            $showTotalGP = in_array('total_grade_points', $selected);

                            $activeAssessments = $assessments->filter(fn($a) =>
                                empty($selected) || in_array('assessment_' . $a->id, $selected)
                            );

                            $gradeColors = [
                                'A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3',
                                'C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6',
                                'D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9','-'=>'',
                            ];

                            // Calculate positions
                            $positionMap = [];
                            $sortedByCum = collect($studentRows)->sortByDesc('total_cum')->values();
                            $prevPct = null;
                            $prevPos = 0;
                            $counter = 0;
                            foreach($sortedByCum as $stu) {
                                $counter++;
                                if($prevPct !== null && $stu['total_cum'] == $prevPct) {
                                    $positionMap[$stu['id']] = $prevPos;
                                } else {
                                    $positionMap[$stu['id']] = $counter;
                                    $prevPos = $counter;
                                }
                                $prevPct = $stu['total_cum'];
                            }

                            $subColspan = $activeAssessments->count();
                            if($showTotal) $subColspan++;
                            if($showBF) $subColspan++;
                            if($showCum) $subColspan++;
                            if($showGrade) $subColspan++;
                            if($showPosition) $subColspan++;
                            if($showAvg) $subColspan++;
                            if($showRemark) $subColspan++;

                            $frozenCols = 1 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0);
                            $gpaColspan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);
                        @endphp

                        <table class="broadsheet-table" id="broadsheetTable">
                            <thead>
                                <tr class="subject-header">
                                    <th class="student-col" rowspan="2" style="width:40px;">#</th>
                                    <th class="student-col" rowspan="2" style="width:50px;">Pos</th>
                                    @if($showAdmNo)
                                        <th class="student-col" rowspan="2" style="min-width:70px;">Adm. No</th>
                                    @endif
                                    <th class="student-col" rowspan="2" style="min-width:180px;text-align:left;padding-left:8px;">Student Name</th>
                                    @if($showGender)
                                        <th class="student-col" rowspan="2" style="width:40px;">Sex</th>
                                    @endif
                                    @foreach($subjects as $subId => $subInfo)
                                        <th class="subj-name-hdr" colspan="{{ max(1, $subColspan) }}">
                                            {{ $subInfo['subject_name'] }}
                                            @if(!empty($subInfo['subject_code']))
                                                <br><small style="opacity:.75;font-size:9px;">({{ $subInfo['subject_code'] }})</small>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th class="subj-name-hdr" style="min-width:100px;">Actions</th>
                                    @if($gpaColspan > 0)
                                        <th colspan="{{ $gpaColspan }}" style="background:#0a1e38;border-left:2px solid #3b82f6;font-size:10px;">GPA METRICS</th>
                                    @endif
                                  </tr>
                                <tr class="assessment-header">
                                    @foreach($subjects as $subId => $subInfo)
                                        @foreach($activeAssessments as $aIdx => $a)
                                            <th class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}" style="min-width:40px;">
                                                {{ $a->name }}<br>
                                                <span style="font-size:9px;opacity:.75;">/{{ $a->max_score }}</span>
                                            </th>
                                        @endforeach
                                        @if($showTotal)    <th style="min-width:36px;">Total</th>  @endif
                                        @if($showBF)       <th style="min-width:30px;">BF</th>     @endif
                                        @if($showCum)      <th style="min-width:36px;">Cum</th>    @endif
                                        @if($showGrade)    <th style="min-width:30px;">Grd</th>    @endif
                                        @if($showPosition) <th style="min-width:30px;">Pos</th>    @endif
                                        @if($showAvg)      <th style="min-width:32px;">Avg</th>    @endif
                                        @if($showRemark)   <th style="min-width:45px;">Rmk</th>    @endif
                                    @endforeach
                                    <th style="min-width:100px;background:#163562;">Quick View</th>
                                    @if($showGPA)      <th style="background:#0a1e38;color:#93c5fd;min-width:36px;border-left:2px solid #3b82f6;">GPA</th>   @endif
                                    @if($showCGPA)     <th style="background:#0a1e38;color:#86efac;min-width:36px;">CGPA</th>  @endif
                                    @if($showGPAGrade) <th style="background:#0a1e38;color:#fcd34d;min-width:30px;">GGrd</th>  @endif
                                    @if($showNumSub)   <th style="background:#0a1e38;color:#a8d4ef;min-width:30px;">NS</th>    @endif
                                    @if($showTotalGP)  <th style="background:#0a1e38;color:#a8d4ef;min-width:36px;">TGP</th>   @endif
                                  </tr>
                            </thead>
                            <tbody>
                                @foreach($studentRows as $idx => $stu)
                                    @php
                                        $hasFailure = false;
                                        foreach($stu['subjects'] as $sd) {
                                            if(($sd['grade'] ?? '') === 'F9') { $hasFailure = true; break; }
                                        }
                                        $imgSrc = $stu['picture']
                                            ? asset('storage/student_avatars/' . basename($stu['picture']))
                                            : asset('storage/student_avatars/unnamed.jpg');
                                        $pos = $positionMap[$stu['id']] ?? 0;
                                        $posClass = $pos === 1 ? 'pos-1' : ($pos === 2 ? 'pos-2' : ($pos === 3 ? 'pos-3' : 'pos-other'));
                                        $posIcon = $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : $pos));

                                        // Calculate percentages for the student
                                        $totalObtainable = count($subjects) * 100;
                                        $termTotal = $stu['total_cum'];
                                        $termPercentage = $totalObtainable > 0 ? round(($termTotal / $totalObtainable) * 100, 1) : 0;
                                        $termColor = $termPercentage < 40 ? '#dc2626' : ($termPercentage < 70 ? '#f59e0b' : '#22c55e');

                                        // Calculate cumulative (using same as term for now - adjust if you have separate cumulative data)
                                        $cumPercentage = $termPercentage;
                                        $cumColor = $cumPercentage < 40 ? '#dc2626' : ($cumPercentage < 70 ? '#f59e0b' : '#22c55e');
                                    @endphp
                                    <tr data-student-id="{{ $stu['id'] }}"
                                        data-student-name="{{ strtolower($stu['lastname'] . ' ' . $stu['firstname']) }}"
                                        data-admission="{{ strtolower($stu['admissionno']) }}"
                                        data-gpa="{{ $stu['gpa'] }}"
                                        data-has-failure="{{ $hasFailure ? 'true' : 'false' }}"
                                        data-class-avg="{{ $stu['class_average'] ?? 0 }}"
                                        data-term-percentage="{{ $termPercentage }}"
                                        data-student-json='@json($stu)'
                                        data-subjects-json='@json($subjects)'
                                        style="animation-delay: {{ $idx * 0.05 }}s">

                                        <td class="sn-cell">{{ $idx + 1 }}</td>
                                        <td style="text-align:center;">
                                            <div class="pos-badge {{ $posClass }}" data-tooltip="Position {{ $pos }} (Cumulative)">
                                                {{ $posIcon }}
                                            </div>
                                        </td>
                                        @if($showAdmNo)
                                            <td class="adm-cell">{{ $stu['admissionno'] }}</td>
                                        @endif
                                        <td class="student-info-cell">
                                            <div class="student-name-wrapper">
                                                <img src="{{ $imgSrc }}"
                                                     class="student-avatar"
                                                     onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'"
                                                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;margin-right:8px;">
                                                <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
                                            </div>
                                        </td>
                                        @if($showGender)
                                            <td style="font-size:10px;">{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
                                        @endif

                                        @foreach($subjects as $subId => $subInfo)
                                            @php $sd = $stu['subjects'][$subId] ?? []; $g = $sd['grade'] ?? '-'; $gc = $gradeColors[$g] ?? ''; @endphp
                                            @foreach($activeAssessments as $aIdx => $a)
                                                @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
                                                <td class="score-cell {{ $aIdx === 0 ? 'sub-boundary' : '' }}">
                                                    {{ $as > 0 ? number_format($as, 1) : '—' }}
                                                </td>
                                            @endforeach
                                            @if($showTotal)    <td class="score-cell {{ $gc }}">{{ ($sd['total']??0)>0 ? number_format($sd['total'],1) : '—' }}</td> @endif
                                            @if($showBF)       <td class="score-cell">{{ ($sd['bf']??0)>0 ? number_format($sd['bf'],1) : '—' }}</td> @endif
                                            @if($showCum)      <td class="score-cell {{ $gc }}" style="font-weight:bold;">{{ ($sd['cum']??0)>0 ? number_format($sd['cum'],1) : '—' }}</td> @endif
                                            @if($showGrade)    <td class="score-cell {{ $gc }}" style="font-weight:bold;">{{ $g }}</td> @endif
                                            @if($showPosition) <td class="score-cell" style="font-size:10px;">{{ $sd['position'] ?? '—' }}</td> @endif
                                            @if($showAvg)      <td class="score-cell" style="font-size:10px;color:#6b7280;">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td> @endif
                                            @if($showRemark)   <td class="score-cell" style="font-size:10px;white-space:nowrap;">{{ $sd['remark'] ?? '—' }}</td> @endif
                                        @endforeach

                                        {{-- Actions Cell with Eye Button --}}
                                        <td style="text-align:center;">
                                            <button class="perf-summary-btn" onclick="showPerformanceModal({{ $stu['id'] }})">
                                                <i class="ri-eye-line"></i> Summary
                                            </button>
                                        </td>

                                        @if($showGPA)      <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>      @endif
                                        @if($showCGPA)     <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td> @endif
                                        @if($showGPAGrade) @php $ggc = $gradeColors[$stu['gpa_grade']??'-']??''; @endphp
                                                           <td class="gpa-cell {{ $ggc }}" style="font-weight:bold;">{{ $stu['gpa_grade']??'—' }}</td> @endif
                                        @if($showNumSub)    <td>{{ $stu['num_subjects']??'—' }}</td> @endif
                                        @if($showTotalGP)   <td>{{ number_format($stu['total_grade_points'],1) }}</td> @endif
                                    </tr>
                                @endforeach

                                {{-- Stats rows --}}
                                @php
                                    $statRows = [['CLASS AVG','avg'],['HIGHEST','highest'],['LOWEST','lowest']];
                                    $statStyles = ['avg'=>'','highest'=>'stats-hi','lowest'=>'stats-lo'];
                                @endphp
                                @foreach($statRows as [$label, $key])
                                    <tr class="stats-row {{ $statStyles[$key] }}">
                                        <td class="stats-label" colspan="{{ $frozenCols + 2 }}">{{ $label }}</td>
                                        @foreach($subjects as $subId => $subInfo)
                                            @php $st = $subjectStats[$subId] ?? []; @endphp
                                            @foreach($activeAssessments as $a)  <td>—</td> @endforeach
                                            @if($showTotal)     <td>{{ $st[$key] ?? '—' }}</td> @endif
                                            @if($showBF)        <td>—</td> @endif
                                            @if($showCum)       <td>—</td> @endif
                                            @if($showGrade)     <td>—</td> @endif
                                            @if($showPosition)  <td>—</td> @endif
                                            @if($showAvg)       <td>{{ $key==='avg' ? ($st['avg']??'—') : '—' }}</td> @endif
                                            @if($showRemark)    <td>—</td> @endif
                                        @endforeach
                                        <td class="stats-label">—</td>
                                        @if($showGPA)       <td>—</td> @endif
                                        @if($showCGPA)      <td>—</td> @endif
                                        @if($showGPAGrade)  <td>—</td> @endif
                                        @if($showNumSub)    <td>—</td> @endif
                                        @if($showTotalGP)   <td>—</td> @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Subject Performance Summary --}}
            <div class="card shadow-sm mb-4 animate-fade-up">
                <div class="card-header" style="background:#1e3a5f;">
                    <h6 class="mb-0 text-white fw-bold">
                        <i class="ri-bar-chart-2-line me-2"></i>Subject Performance Summary
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:160px;">Subject</th>
                                    <th style="text-align:center;min-width:60px;">Avg</th>
                                    <th style="text-align:center;min-width:70px;">Highest</th>
                                    <th style="text-align:center;min-width:60px;">Lowest</th>
                                    <th style="text-align:center;min-width:60px;">Passed</th>
                                    <th style="text-align:center;min-width:60px;">Failed</th>
                                    <th style="text-align:center;min-width:80px;">Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subId => $subInfo)
                                    @php
                                        $st = $subjectStats[$subId] ?? [];
                                        $p  = $st['passed'] ?? 0;
                                        $f  = $st['failed'] ?? 0;
                                        $t  = $p + $f;
                                        $pr = $t > 0 ? round($p / $t * 100) : 0;
                                    @endphp
                                    <tr style="animation: rowSlide 0.3s ease both; animation-delay: {{ $loop->index * 0.03 }}s;">
                                        <td style="font-weight:600;">
                                            {{ $subInfo['subject_name'] }}
                                            @if(!empty($subInfo['subject_code']))
                                                <span class="text-muted" style="font-size:10px;">({{ $subInfo['subject_code'] }})</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center;font-weight:bold;">{{ $st['avg'] ?? '—' }}</td>
                                        <td style="text-align:center;color:#16a34a;font-weight:bold;">{{ $st['highest'] ?? '—' }}</td>
                                        <td style="text-align:center;color:#dc2626;font-weight:bold;">{{ $st['lowest'] ?? '—' }}</td>
                                        <td style="text-align:center;color:#16a34a;">{{ $p }}</td>
                                        <td style="text-align:center;color:#dc2626;">{{ $f }}</td>
                                        <td style="text-align:center;">
                                            <span class="{{ $pr >= 50 ? 'text-success' : 'text-danger' }} fw-bold">{{ $pr }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="card shadow-sm mb-4 no-print animate-fade-up">
                <div class="card-body">
                    <div class="row">
                        <div class="col-3 text-center">
                            <div class="border-top pt-2 mt-3">Class Teacher</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="border-top pt-2 mt-3">Head of Department</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="border-top pt-2 mt-3">Vice Principal</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="border-top pt-2 mt-3">Principal</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Performance Modal --}}
<div class="modal fade perf-modal" id="performanceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <i class="ri-bar-chart-line" style="font-size: 24px;"></i>
                    <div>
                        <h5 class="mb-0" id="modalStudentName">Student Performance Summary</h5>
                        <small class="opacity-75" id="modalStudentInfo"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="perf-stats-grid" id="perfStatsGrid">
                    <!-- Stats will be populated by JS -->
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold mb-3"><i class="ri-bar-chart-line me-1"></i>Subject Performance Details</h6>
                    <div class="perf-subjects-list" id="perfSubjectsList">
                        <!-- Subjects will be populated by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Store student data for modal
const studentDataMap = {};

@foreach($studentRows as $stu)
    studentDataMap[{{ $stu['id'] }}] = {
        id: {{ $stu['id'] }},
        name: '{{ addslashes($stu['lastname'] . ' ' . $stu['firstname']) }}',
        admissionno: '{{ $stu['admissionno'] }}',
        total_cum: {{ $stu['total_cum'] }},
        num_subjects: {{ count($subjects) }},
        total_obtainable: {{ count($subjects) * 100 }},
        term_percentage: {{ $totalObtainable = count($subjects) * 100; echo $totalObtainable > 0 ? round(($stu['total_cum'] / $totalObtainable) * 100, 1) : 0; }},
        gpa: {{ $stu['gpa'] }},
        gpa_grade: '{{ $stu['gpa_grade'] ?? '-' }}',
        subjects: @json($stu['subjects']),
        subjects_map: @json($subjects)
    };
@endforeach

let performanceModal;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal
    performanceModal = new bootstrap.Modal(document.getElementById('performanceModal'));

    // Animate percentage counters in the table
    document.querySelectorAll('[data-percentage]').forEach(el => {
        const target = parseFloat(el.getAttribute('data-percentage'));
        if (isNaN(target)) return;
        let current = 0;
        const duration = 800;
        const steps = 60;
        const increment = target / steps;
        let step = 0;
        const timer = setInterval(() => {
            step++;
            current += increment;
            if (step >= steps) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toFixed(1) + '%';
        }, duration / steps);
    });

    // Animate progress bars
    document.querySelectorAll('.progress-bar[data-final-color]').forEach(bar => {
        const targetWidth = bar.getAttribute('data-width') || bar.parentElement.parentElement.querySelector('[data-percentage]')?.getAttribute('data-percentage') || 0;
        const color = bar.getAttribute('data-final-color');
        setTimeout(() => {
            bar.style.width = targetWidth + '%';
            setTimeout(() => {
                bar.style.backgroundColor = color;
            }, 500);
        }, 100);
    });

    // Calculate and animate stats
    const students = @json($studentRows);
    if (students.length > 0) {
        let totalCumPct = 0;
        let topGPA = 0;
        let topName = '';
        students.forEach(s => {
            const obtainable = students.length * 100;
            const pct = obtainable > 0 ? (s.total_cum / obtainable) * 100 : 0;
            totalCumPct += pct;
            if (s.gpa > topGPA) {
                topGPA = s.gpa;
                topName = s.lastname + ' ' + s.firstname;
            }
        });
        const avgPct = totalCumPct / students.length;
        animateNumber('statAvgPercentage', avgPct, '%');
        document.getElementById('statTopPerformer').textContent = topName || '—';
    }
    document.getElementById('statTotalStudents').textContent = students.length;
    document.getElementById('statTotalSubjects').textContent = @json(count($subjects));
});

function animateNumber(elementId, target, suffix = '') {
    const el = document.getElementById(elementId);
    if (!el) return;
    let current = 0;
    const duration = 800;
    const steps = 60;
    const increment = target / steps;
    let step = 0;
    const timer = setInterval(() => {
        step++;
        current += increment;
        if (step >= steps) {
            current = target;
            clearInterval(timer);
        }
        el.textContent = current.toFixed(1) + suffix;
    }, duration / steps);
}

// Show performance modal
function showPerformanceModal(studentId) {
    const student = studentDataMap[studentId];
    if (!student) {
        showToast('Student data not found', 'error');
        return;
    }

    // Update modal header
    document.getElementById('modalStudentName').textContent = student.name;
    document.getElementById('modalStudentInfo').innerHTML =
        '<i class="ri-id-card-line me-1"></i>' + student.admissionno +
        ' &nbsp;|&nbsp; <i class="ri-bar-chart-line me-1"></i>GPA: ' + student.gpa.toFixed(2) +
        ' &nbsp;|&nbsp; <i class="ri-award-line me-1"></i>Grade: ' + student.gpa_grade;

    // Build stats grid
    const termPct = student.term_percentage;
    const termColor = termPct < 40 ? 'score-red' : (termPct < 70 ? 'score-amber' : 'score-green');
    const obtainable = student.total_obtainable;
    const obtained = student.total_cum;
    const pctColor = termPct < 50 ? 'score-red' : (termPct < 70 ? 'score-amber' : 'score-green');

    const statsGrid = document.getElementById('perfStatsGrid');
    statsGrid.innerHTML = `
        <div class="perf-stat-item animate-scale" style="animation-delay: 0.05s">
            <div class="perf-stat-label">Obtained (Cumulative)</div>
            <div class="perf-stat-value">${obtained.toFixed(1)}</div>
            <div class="progress-bar-lg mt-2">
                <div class="progress-fill" style="width: ${(obtained/obtainable)*100}%; background: #2563eb;"></div>
            </div>
        </div>
        <div class="perf-stat-item animate-scale" style="animation-delay: 0.1s">
            <div class="perf-stat-label">Total Obtainable</div>
            <div class="perf-stat-value">${obtainable}</div>
        </div>
        <div class="perf-stat-item animate-scale" style="animation-delay: 0.15s">
            <div class="perf-stat-label">% Obtained (Term)</div>
            <div class="perf-stat-value ${termColor}" data-perf-percentage="${termPct}">0%</div>
            <div class="progress-bar-lg mt-2">
                <div class="progress-fill" style="width: 0%; background: ${termPct < 40 ? '#dc2626' : (termPct < 70 ? '#f59e0b' : '#22c55e')};"></div>
            </div>
        </div>
        <div class="perf-stat-item animate-scale" style="animation-delay: 0.2s">
            <div class="perf-stat-label">Class Position</div>
            <div class="perf-stat-value">—</div>
        </div>
    `;

    // Animate the percentage in modal
    setTimeout(() => {
        const pctEl = statsGrid.querySelector('[data-perf-percentage]');
        if (pctEl) {
            const target = parseFloat(pctEl.getAttribute('data-perf-percentage'));
            let current = 0;
            const duration = 800;
            const steps = 60;
            const increment = target / steps;
            let step = 0;
            const timer = setInterval(() => {
                step++;
                current += increment;
                if (step >= steps) {
                    current = target;
                    clearInterval(timer);
                }
                pctEl.textContent = current.toFixed(1) + '%';
            }, duration / steps);
        }

        // Animate progress bars in modal
        statsGrid.querySelectorAll('.progress-fill').forEach((bar, idx) => {
            const targetWidth = idx === 0 ? (obtained/obtainable)*100 : termPct;
            setTimeout(() => {
                bar.style.width = targetWidth + '%';
            }, 100 + idx * 100);
        });
    }, 100);

    // Build subjects list
    const subjectsList = document.getElementById('perfSubjectsList');
    let subjectsHtml = '';

    for (const [subId, subject] of Object.entries(student.subjects_map)) {
        const subData = student.subjects[subId] || {};
        const total = subData.cum || 0;
        const grade = subData.grade || '-';
        const gradeClass = grade === 'A1' ? 'grade-a1' : (grade === 'F9' ? 'grade-f9' : '');
        const totalObtainable = 100;
        const percentage = totalObtainable > 0 ? (total / totalObtainable) * 100 : 0;
        const pctClass = percentage < 40 ? 'score-red' : (percentage < 70 ? 'score-amber' : 'score-green');

        subjectsHtml += `
            <div class="perf-subject-item animate-fade-right" style="animation-delay: ${Object.keys(student.subjects_map).indexOf(subId) * 0.03}s">
                <div class="perf-subject-name">${subject.subject_name}</div>
                <div class="perf-subject-scores">
                    <span class="perf-subject-score">Score: <strong>${total > 0 ? total.toFixed(1) : '—'}</strong></span>
                    <span class="perf-subject-score grade-badge ${gradeClass}" style="padding: 2px 8px; border-radius: 12px;">Grade: ${grade}</span>
                    <span class="perf-subject-score ${pctClass}">${percentage.toFixed(1)}%</span>
                </div>
            </div>
            <div class="progress-bar-lg mb-2" style="margin: 0 12px 8px 12px;">
                <div class="progress-fill" style="width: 0%; background: ${percentage < 40 ? '#dc2626' : (percentage < 70 ? '#f59e0b' : '#22c55e')}; height: 4px;"></div>
            </div>
        `;
    }

    subjectsList.innerHTML = subjectsHtml;

    // Animate subject progress bars
    setTimeout(() => {
        subjectsList.querySelectorAll('.progress-fill').forEach(bar => {
            const parent = bar.closest('.perf-subject-item');
            if (parent) {
                const scoreSpan = parent.querySelector('.perf-subject-score:first-child strong');
                if (scoreSpan) {
                    const score = parseFloat(scoreSpan.textContent);
                    if (!isNaN(score)) {
                        const width = (score / 100) * 100;
                        bar.style.width = width + '%';
                    }
                }
            }
        });
    }, 200);

    performanceModal.show();
}

// Search functionality
const tableRows = document.querySelectorAll('#broadsheetTable tbody tr:not(.stats-row)');
const searchInput = document.getElementById('searchStudent');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        let count = 0;
        tableRows.forEach(row => {
            const name = row.getAttribute('data-student-name') || '';
            const adm = row.getAttribute('data-admission') || '';
            const show = !q || name.includes(q) || adm.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) count++;
        });
        if (q) showToast(`Found ${count} student(s)`, 'info');
    });
}

// Locate functionality
const locateSelect = document.getElementById('locateStudent');
if (locateSelect) {
    locateSelect.addEventListener('change', function() {
        const val = this.value;
        if (!val) return;

        tableRows.forEach(r => {
            r.style.backgroundColor = '';
            r.style.outline = '';
        });

        if (val === 'top5') highlightTop(5);
        else if (val === 'top10') highlightTop(10);
        else if (val === 'failures') highlightFailures();
        else if (val === 'below_avg') highlightBelowAvg();
        else if (val === 'missing_scores') highlightMissing();
        else if (val.startsWith('student_')) {
            const id = val.replace('student_', '');
            const row = document.querySelector(`tr[data-student-id="${id}"]`);
            if (row) {
                row.style.outline = '3px solid #2563eb';
                row.style.backgroundColor = '#e8f0fe';
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                showToast('Located: ' + (row.getAttribute('data-student-name') || ''), 'success');
            }
        }
        setTimeout(() => { locateSelect.value = ''; }, 100);
    });
}

function highlightTop(n) {
    const rows = Array.from(tableRows).filter(r => r.style.display !== 'none');
    rows.sort((a,b) => parseFloat(b.dataset.gpa||0) - parseFloat(a.dataset.gpa||0));
    rows.slice(0,n).forEach(r => {
        r.style.backgroundColor = '#fef9c3';
        r.style.outline = '2px solid #d97706';
    });
    showToast(`Top ${n} students highlighted`, 'success');
}

function highlightFailures() {
    let c = 0;
    tableRows.forEach(r => {
        if (r.dataset.hasFailure === 'true') {
            r.style.backgroundColor = '#fee2e2';
            r.style.outline = '2px solid #dc2626';
            c++;
        }
    });
    showToast(`${c} student(s) with F9 highlighted`, 'warning');
}

function highlightBelowAvg() {
    let c = 0;
    tableRows.forEach(r => {
        const gpa = parseFloat(r.dataset.gpa||0);
        const avg = parseFloat(r.dataset.classAvg||0);
        if (gpa < avg && gpa > 0) {
            r.style.backgroundColor = '#fff7ed';
            r.style.outline = '2px solid #f97316';
            c++;
        }
    });
    showToast(`${c} student(s) below class average`, 'info');
}

function highlightMissing() {
    let c = 0;
    tableRows.forEach(row => {
        const hasMissing = Array.from(row.querySelectorAll('td')).some(td =>
            td.textContent.trim() === '—' &&
            !td.classList.contains('student-info-cell') &&
            !td.classList.contains('sn-cell')
        );
        if (hasMissing) {
            row.style.backgroundColor = '#f0fdf4';
            row.style.outline = '2px solid #16a34a';
            c++;
        }
    });
    showToast(`${c} student(s) with missing scores`, 'warning');
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showToast(msg, type = 'info') {
    const colors = { success: '#16a34a', warning: '#d97706', info: '#2563eb', error: '#dc2626' };
    const div = document.createElement('div');
    div.className = 'toast-notification';
    div.style.background = colors[type] || colors.info;
    div.innerHTML = '<i class="ri-information-line me-2"></i>' + msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

// Make functions global
window.scrollToTop = scrollToTop;
window.showPerformanceModal = showPerformanceModal;
</script>

<style>
.student-name-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}
.perf-subject-item {
    transition: all 0.3s ease;
}
.perf-subject-item:hover {
    background: #f0fdf9;
    transform: translateX(5px);
}
.grade-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}
.progress-fill {
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
</style>
@endsection
