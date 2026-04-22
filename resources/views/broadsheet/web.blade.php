@extends('layouts.master')


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

    .broadsheet-table tbody tr:nth-child(odd)  { background: #ffffff; }
    .broadsheet-table tbody tr:nth-child(even) { background: #f0f4fa; }

    .broadsheet-table tbody tr:hover {
        background-color: #e8f0fe !important;
    }

    .broadsheet-table tbody td {
        padding: 5px 4px;
        border: 0.5px solid #c5d3e8;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 11px;
    }

    .broadsheet-table tbody td.student-info-cell {
        text-align: left;
        padding-left: 8px;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: inherit;
        z-index: 5;
        min-width: 160px;
    }

    .broadsheet-table tbody td.sn-cell {
        color: #6b7280;
        font-size: 10px;
        width: 30px;
    }

    .broadsheet-table tbody td.adm-cell {
        font-size: 10px;
        color: #4a6e8a;
        min-width: 70px;
    }

    .broadsheet-table tbody td.sub-boundary {
        border-left: 1.5px solid #2563eb66;
    }

    .gpa-cell {
        background: #eff6ff !important;
        color: #1e3a8a;
        font-weight: bold;
        border-left: 1.5px solid #3b82f6 !important;
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
    .stats-row td.stats-label {
        text-align: left;
        padding-left: 6px;
        font-size: 10px;
    }
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
    }
    .meta-cell {
        flex: 1;
        padding: 8px 12px;
        border-right: 1px solid #c5d3e8;
    }
    .meta-cell:last-child { border-right: none; }
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
    }

    /* Pass/Fail summary table */
    .sum-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        border: 1px solid #c5d3e8;
    }
    .sum-table thead tr th {
        background: #1e3a5f;
        color: white;
        padding: 7px 10px;
        text-align: left;
        border: 0.5px solid #2563eb55;
    }
    .sum-table tbody tr:nth-child(even) { background: #f0f4fa; }
    .sum-table tbody td {
        padding: 6px 10px;
        border: 0.5px solid #c5d3e8;
    }
    .pass-good { background: #dcfce7; color: #166534; font-weight: bold; padding: 2px 8px; border-radius: 3px; }
    .pass-bad  { background: #fee2e2; color: #991b1b; font-weight: bold; padding: 2px 8px; border-radius: 3px; }

    /* Toolbar */
    .toolbar-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 12px 16px;
        margin-bottom: 14px;
    }

    /* School header */
    .school-header-bar {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        border-radius: 10px;
        padding: 18px 24px;
        margin-bottom: 18px;
        color: white;
    }

    /* Signature row */
    .signature-row {
        display: flex;
        justify-content: space-around;
        margin-top: 30px;
        padding-top: 10px;
    }
    .sig-cell {
        text-align: center;
        width: 22%;
    }
    .sig-line {
        border-top: 1px solid #374151;
        padding-top: 5px;
        font-size: 12px;
        color: #374151;
        margin-top: 28px;
    }

    @media print {
        .toolbar-card, .no-print { display: none !important; }
        .broadsheet-table { font-size: 8px; }
    }
</style>


@section('content')
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

            {{-- Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- School Header --}}
            <div class="school-header-bar">
                <div class="d-flex align-items-center">
                    @if(!empty($school_logo_base64))
                        <img src="{{ $school_logo_base64 }}" alt="Logo"
                             style="width:65px;height:65px;object-fit:contain;border-radius:50%;border:2px solid white;margin-right:16px;">
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
            <div style="background:#1e3a5f;color:white;text-align:center;padding:10px;font-size:15px;font-weight:bold;letter-spacing:1.5px;border-radius:6px;margin-bottom:14px;">
                CLASS ACADEMIC BROADSHEET
            </div>

            {{-- Meta Grid --}}
            <div class="meta-grid">
                <div class="meta-cell">
                    <span class="meta-label">Class</span>
                    <span class="meta-value">{{ $schoolclass->schoolclass ?? '-' }} {{ $schoolclass->arm_name ?? '' }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Academic Session</span>
                    <span class="meta-value">{{ $schoolsession->session ?? '-' }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Term</span>
                    <span class="meta-value">{{ $schoolterm->term ?? '-' }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">No. of Students</span>
                    <span class="meta-value">{{ $totalStudents }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">No. of Subjects</span>
                    <span class="meta-value">{{ count($subjects) }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Generated</span>
                    <span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span>
                </div>
            </div>

            {{-- Grade Key --}}
            <div class="grade-key">
                <strong style="color:#1e3a5f;margin-right:8px;font-size:12px;">GRADING SCALE:</strong>
                @php
                $gradeKey = [
                    'A1' => ['75-100', '#16a34a'], 'B2' => ['70-74', '#1d4ed8'], 'B3' => ['65-69', '#2563eb'],
                    'C4' => ['60-64', '#d97706'],  'C5' => ['55-59', '#b45309'], 'C6' => ['50-54', '#92400e'],
                    'D7' => ['45-49', '#ea580c'],  'E8' => ['40-44', '#c2410c'], 'F9' => ['0-39',  '#dc2626'],
                ];
                @endphp
                @foreach($gradeKey as $grade => $info)
                    <span class="badge me-1" style="background:{{ $info[1] }};font-size:11px;">{{ $grade }} ({{ $info[0] }})</span>
                @endforeach
                <span class="text-muted ms-2" style="font-size:11px;">
                    <strong>BF</strong>=Brought Forward &nbsp;
                    <strong>CUM</strong>=Cumulative &nbsp;
                    <strong>POS</strong>=Position
                </span>
            </div>

            {{-- Toolbar --}}
            <div class="toolbar-card no-print">
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
                            <option value="top10">⭐ Top 10 Students (by GPA)</option>
                            <option value="failures">⚠️ Students with Failures (F9)</option>
                            <option value="below_avg">📉 Below Class Average</option>
                            <option value="missing_scores">❌ Missing Scores</option>
                            <option disabled>──────────</option>
                            @foreach($studentRows as $student)
                                <option value="student_{{ $student['id'] }}">
                                    👤 {{ $student['lastname'] }}, {{ $student['firstname'] }} ({{ $student['admissionno'] }})
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

            {{-- Main Table --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div style="overflow-x:auto;">
                        @php
                            $selected = $selectedColumns ?? [];
                            $showAdmNo    = empty($selected) || in_array('admission_no', $selected);
                            $showGender   = in_array('gender', $selected);
                            $showTotal    = empty($selected) || in_array('total', $selected);
                            $showBF       = in_array('bf', $selected);
                            $showCum      = empty($selected) || in_array('cum', $selected);
                            $showGrade    = empty($selected) || in_array('grade', $selected);
                            $showPosition = empty($selected) || in_array('position', $selected);
                            $showAvg      = in_array('class_average', $selected);
                            $showRemark   = in_array('remark', $selected);
                            $showGPA      = in_array('gpa', $selected);
                            $showCGPA     = in_array('cgpa', $selected);
                            $showGPAGrade = in_array('gpa_grade', $selected);
                            $showNumSub   = in_array('num_subjects', $selected);
                            $showTotalGP  = in_array('total_grade_points', $selected);

                            $activeAssessments = $assessments->filter(fn($a) =>
                                empty($selected) || in_array('assessment_' . $a->id, $selected)
                            );

                            $gradeColors = [
                                'A1'=>'grade-a1','B2'=>'grade-b2','B3'=>'grade-b3',
                                'C4'=>'grade-c4','C5'=>'grade-c5','C6'=>'grade-c6',
                                'D7'=>'grade-d7','E8'=>'grade-e8','F9'=>'grade-f9','-'=>'',
                            ];

                            $subColspan = $activeAssessments->count();
                            if ($showTotal)    $subColspan++;
                            if ($showBF)       $subColspan++;
                            if ($showCum)      $subColspan++;
                            if ($showGrade)    $subColspan++;
                            if ($showPosition) $subColspan++;
                            if ($showAvg)      $subColspan++;
                            if ($showRemark)   $subColspan++;

                            $frozenCols = 1 + ($showAdmNo?1:0) + 1 + ($showGender?1:0);
                            $gpaColspan = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0);
                        @endphp

                        <table class="broadsheet-table" id="broadsheetTable">
                            <thead>
                                {{-- Subject header row --}}
                                <tr class="subject-header">
                                    <th class="student-col" rowspan="2" style="width:35px;">#</th>
                                    @if($showAdmNo)
                                        <th class="student-col" rowspan="2" style="min-width:70px;">Adm. No</th>
                                    @endif
                                    <th class="student-col" rowspan="2" style="min-width:160px;text-align:left;padding-left:8px;">Student Name</th>
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

                                    @if($gpaColspan > 0)
                                        <th colspan="{{ $gpaColspan }}" style="background:#0a1e38;border-left:2px solid #3b82f6;font-size:10px;">
                                            GPA METRICS
                                        </th>
                                    @endif
                                </tr>

                                {{-- Assessment sub-header row --}}
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
                                    @if($showGPA)      <th style="background:#0a1e38;color:#93c5fd;min-width:36px;border-left:2px solid #3b82f6;">GPA</th>      @endif
                                    @if($showCGPA)     <th style="background:#0a1e38;color:#86efac;min-width:36px;">CGPA</th>    @endif
                                    @if($showGPAGrade) <th style="background:#0a1e38;color:#fcd34d;min-width:30px;">GGrd</th>   @endif
                                    @if($showNumSub)   <th style="background:#0a1e38;color:#a8d4ef;min-width:30px;">NS</th>     @endif
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
                                    @endphp
                                    <tr data-student-id="{{ $stu['id'] }}"
                                        data-student-name="{{ strtolower($stu['lastname'] . ' ' . $stu['firstname']) }}"
                                        data-admission="{{ strtolower($stu['admissionno']) }}"
                                        data-gpa="{{ $stu['gpa'] }}"
                                        data-has-failure="{{ $hasFailure ? 'true' : 'false' }}"
                                        data-class-avg="{{ $stu['class_average'] ?? 0 }}">

                                        <td class="sn-cell">{{ $idx + 1 }}</td>
                                        @if($showAdmNo)
                                            <td class="adm-cell">{{ $stu['admissionno'] }}</td>
                                        @endif
                                        <td class="student-info-cell">
                                            <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
                                        </td>
                                        @if($showGender)
                                            <td style="font-size:10px;">{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
                                        @endif

                                        @foreach($subjects as $subId => $subInfo)
                                            @php $sd = $stu['subjects'][$subId] ?? []; $g = $sd['grade'] ?? '-'; $gc = $gradeColors[$g] ?? ''; @endphp
                                            @foreach($activeAssessments as $aIdx => $a)
                                                @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
                                                <td class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}">
                                                    {{ $as > 0 ? number_format($as, 1) : '—' }}
                                                </td>
                                            @endforeach
                                            @if($showTotal)    <td class="{{ $gc }}">{{ ($sd['total']??0)>0 ? number_format($sd['total'],1) : '—' }}</td> @endif
                                            @if($showBF)       <td>{{ ($sd['bf']??0)>0 ? number_format($sd['bf'],1) : '—' }}</td> @endif
                                            @if($showCum)      <td class="{{ $gc }}" style="font-weight:bold;">{{ ($sd['cum']??0)>0 ? number_format($sd['cum'],1) : '—' }}</td> @endif
                                            @if($showGrade)    <td class="{{ $gc }}" style="font-weight:bold;">{{ $g }}</td> @endif
                                            @if($showPosition) <td style="font-size:10px;">{{ $sd['position'] ?? '—' }}</td> @endif
                                            @if($showAvg)      <td style="font-size:10px;color:#6b7280;">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td> @endif
                                            @if($showRemark)   <td style="font-size:10px;white-space:nowrap;">{{ $sd['remark'] ?? '—' }}</td> @endif
                                        @endforeach

                                        @if($showGPA)      <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>      @endif
                                        @if($showCGPA)     <td class="gpa-cell" style="background:#f0fdf4!important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td> @endif
                                        @if($showGPAGrade) @php $ggc = $gradeColors[$stu['gpa_grade']??'-']??''; @endphp
                                                           <td class="gpa-cell {{ $ggc }}" style="font-weight:bold;">{{ $stu['gpa_grade']??'—' }}</td> @endif
                                        @if($showNumSub)   <td>{{ $stu['num_subjects']??'—' }}</td> @endif
                                        @if($showTotalGP)  <td>{{ number_format($stu['total_grade_points'],1) }}</td> @endif
                                    </tr>
                                @endforeach

                                {{-- Stats rows --}}
                                @php
                                    $statRows = [
                                        ['CLASS AVG', 'avg'],
                                        ['HIGHEST',   'highest'],
                                        ['LOWEST',    'lowest'],
                                    ];
                                    $statStyles = [
                                        'avg'     => '',
                                        'highest' => 'stats-hi',
                                        'lowest'  => 'stats-lo',
                                    ];
                                @endphp
                                @foreach($statRows as [$label, $key])
                                    <tr class="stats-row {{ $statStyles[$key] }}">
                                        <td class="stats-label" colspan="{{ $frozenCols }}">{{ $label }}</td>
                                        @foreach($subjects as $subId => $subInfo)
                                            @php $st = $subjectStats[$subId] ?? []; @endphp
                                            @foreach($activeAssessments as $a) <td>—</td> @endforeach
                                            @if($showTotal)    <td>{{ $st[$key] ?? '—' }}</td> @endif
                                            @if($showBF)       <td>—</td> @endif
                                            @if($showCum)      <td>—</td> @endif
                                            @if($showGrade)    <td>—</td> @endif
                                            @if($showPosition) <td>—</td> @endif
                                            @if($showAvg)      <td>{{ $key==='avg' ? ($st['avg']??'—') : '—' }}</td> @endif
                                            @if($showRemark)   <td>—</td> @endif
                                        @endforeach
                                        @if($showGPA)      <td>—</td> @endif
                                        @if($showCGPA)     <td>—</td> @endif
                                        @if($showGPAGrade) <td>—</td> @endif
                                        @if($showNumSub)   <td>—</td> @endif
                                        @if($showTotalGP)  <td>—</td> @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pass/Fail Summary --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background:#1e3a5f;">
                    <h6 class="mb-0 text-white fw-bold">
                        <i class="ri-bar-chart-2-line me-2"></i>Subject Performance Summary
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="sum-table">
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
                                    <tr>
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
                                            <span class="{{ $pr >= 50 ? 'pass-good' : 'pass-bad' }}">{{ $pr }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="card shadow-sm mb-4 no-print">
                <div class="card-body">
                    <div class="signature-row">
                        <div class="sig-cell"><div class="sig-line">Class Teacher</div></div>
                        <div class="sig-cell"><div class="sig-line">Head of Department</div></div>
                        <div class="sig-cell"><div class="sig-line">Vice Principal</div></div>
                        <div class="sig-cell"><div class="sig-line">Principal</div></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const tableRows = document.querySelectorAll('#broadsheetTable tbody tr:not(.stats-row)');

// Search
document.getElementById('searchStudent').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    let count = 0;
    tableRows.forEach(row => {
        const name = row.getAttribute('data-student-name') || '';
        const adm  = row.getAttribute('data-admission') || '';
        const show = !q || name.includes(q) || adm.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });
    if (q) showToast(`Found ${count} student(s)`, 'info');
});

// Locate
document.getElementById('locateStudent').addEventListener('change', function() {
    const val = this.value;
    if (!val) return;

    tableRows.forEach(r => {
        r.style.backgroundColor = '';
        r.style.outline = '';
    });

    if (val === 'top5')           highlightTop(5);
    else if (val === 'top10')     highlightTop(10);
    else if (val === 'failures')  highlightFailures();
    else if (val === 'below_avg') highlightBelowAvg();
    else if (val === 'missing_scores') highlightMissing();
    else if (val.startsWith('student_')) {
        const id  = val.replace('student_', '');
        const row = document.querySelector(`tr[data-student-id="${id}"]`);
        if (row) { row.style.outline = '3px solid #f59e0b'; row.scrollIntoView({ behavior:'smooth', block:'center' }); }
    }

    setTimeout(() => { this.value = ''; }, 100);
});

function highlightTop(n) {
    const rows = Array.from(tableRows).filter(r => r.style.display !== 'none');
    rows.sort((a,b) => parseFloat(b.dataset.gpa||0) - parseFloat(a.dataset.gpa||0));
    rows.slice(0, n).forEach(r => { r.style.backgroundColor = '#fef9c3'; r.style.outline = '2px solid #d97706'; });
    showToast(`Top ${n} students highlighted`, 'success');
}

function highlightFailures() {
    let c = 0;
    tableRows.forEach(r => {
        if (r.dataset.hasFailure === 'true') { r.style.backgroundColor = '#fee2e2'; r.style.outline = '2px solid #dc2626'; c++; }
    });
    showToast(`${c} student(s) with F9 highlighted`, 'warning');
}

function highlightBelowAvg() {
    let c = 0;
    tableRows.forEach(r => {
        const gpa = parseFloat(r.dataset.gpa||0);
        const avg = parseFloat(r.dataset.classAvg||0);
        if (gpa < avg && gpa > 0) { r.style.backgroundColor = '#fff7ed'; r.style.outline = '2px solid #f97316'; c++; }
    });
    showToast(`${c} student(s) below class average`, 'info');
}

function highlightMissing() {
    let c = 0;
    tableRows.forEach(row => {
        const hasMissing = Array.from(row.querySelectorAll('td')).some(td =>
            td.textContent.trim() === '—' && !td.classList.contains('student-info-cell') && !td.classList.contains('sn-cell')
        );
        if (hasMissing) { row.style.backgroundColor = '#f0fdf4'; row.style.outline = '2px solid #16a34a'; c++; }
    });
    showToast(`${c} student(s) with missing scores`, 'warning');
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showToast(msg, type = 'info') {
    const colors = { success:'#16a34a', warning:'#d97706', info:'#2563eb', danger:'#dc2626' };
    const id = 'toast_' + Date.now();
    document.body.insertAdjacentHTML('beforeend',
        `<div id="${id}" style="position:fixed;bottom:20px;right:20px;z-index:9999;background:${colors[type]||colors.info};color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;min-width:220px;animation:fadeIn .3s ease;">${msg}</div>`
    );
    setTimeout(() => document.getElementById(id)?.remove(), 3000);
}
</script>

<style>
@keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
</style>
@endsection
