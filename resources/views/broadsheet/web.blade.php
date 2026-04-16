@extends('layouts.master')

@section('styles')
<style>
    /* Grade colors - matching PDF */
    .grade-a1 { background-color: #dcfce7 !important; color: #166534; font-weight: bold; }
    .grade-b2 { background-color: #dbeafe !important; color: #1e40af; }
    .grade-b3 { background-color: #e0eeff !important; color: #1e40af; }
    .grade-c4 { background-color: #fef9c3 !important; color: #854d0e; }
    .grade-c5 { background-color: #fef3c7 !important; color: #92400e; }
    .grade-c6 { background-color: #fde68a !important; color: #78350f; }
    .grade-d7 { background-color: #ffedd5 !important; color: #9a3412; }
    .grade-e8 { background-color: #fed7aa !important; color: #9a3412; }
    .grade-f9 { background-color: #fee2e2 !important; color: #991b1b; font-weight: bold; }

    /* Animation keyframes */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    /* Main content wrapper with proper spacing */
    .broadsheet-wrapper {
        margin-left: 0;
        padding: 20px 25px;
        animation: fadeInUp 0.5s ease-out;
    }

    /* School header animation */
    .school-header {
        background: linear-gradient(135deg, var(--bs-pri) 0%, var(--bs-acc) 100%);
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 25px;
        color: white;
        animation: slideInRight 0.6s ease-out;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .school-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    /* Toolbar styling */
    .toolbar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 15px 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        animation: fadeInUp 0.5s ease-out 0.1s both;
    }

    .toolbar-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }

    /* Locate dropdown styling */
    .locate-select {
        border: 2px solid var(--bs-border);
        border-radius: 8px;
        padding: 8px 15px;
        font-size: 13px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .locate-select:focus {
        border-color: var(--bs-acc);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        outline: none;
    }

    /* Stats cards animation */
    .stat-card {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 10px;
        padding: 12px 15px;
        text-align: center;
        border: 1px solid var(--bs-border);
        transition: all 0.3s ease;
        animation: fadeInUp 0.5s ease-out both;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    .stat-card:nth-child(5) { animation-delay: 0.5s; }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: var(--bs-pri);
        display: block;
    }

    /* Table container with horizontal scroll */
    .table-container {
        overflow-x: auto;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        animation: fadeInUp 0.6s ease-out 0.2s both;
    }

    /* Table styling matching PDF */
    .broadsheet-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
        font-size: 12px;
        background: white;
    }

    .broadsheet-table thead tr.subject-header th {
        background: #1e3a5f;
        color: white;
        text-align: center;
        padding: 10px 5px;
        border: 0.5px solid #2563eb55;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .broadsheet-table thead tr.assessment-header th {
        background: #1a3d6a;
        color: #a8d4ef;
        text-align: center;
        padding: 6px 3px;
        border: 0.5px solid #2563eb33;
        font-size: 11px;
    }

    .broadsheet-table tbody tr {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .broadsheet-table tbody tr:hover {
        background-color: #f0f7ff !important;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .broadsheet-table tbody tr.highlight-row {
        animation: pulse 0.5s ease;
        background-color: #fef3c7 !important;
        border-left: 3px solid var(--bs-warn);
    }

    .broadsheet-table tbody td {
        padding: 8px 5px;
        border: 0.5px solid #c5d3e8;
        text-align: center;
        vertical-align: middle;
    }

    .broadsheet-table tbody td.student-info-cell {
        text-align: left;
        padding-left: 10px;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: inherit;
        z-index: 5;
    }

    /* Loading overlay animation */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeInUp 0.3s ease;
    }

    .loading-overlay.active {
        display: flex;
    }

    .loading-content {
        background: white;
        border-radius: 15px;
        padding: 30px 40px;
        text-align: center;
        animation: pulse 1s infinite;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--bs-border);
        border-top-color: var(--bs-acc);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Alert animations */
    .alert {
        animation: slideInRight 0.4s ease-out;
    }

    /* Button hover effects */
    .btn-export {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .btn-export:active {
        transform: translateY(0);
    }

    /* Search input animation */
    .search-input {
        transition: all 0.3s ease;
        border: 2px solid var(--bs-border);
    }

    .search-input:focus {
        border-color: var(--bs-acc);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        transform: scale(1.02);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .broadsheet-wrapper {
            padding: 15px;
        }

        .stat-value {
            font-size: 18px;
        }

        .broadsheet-table {
            font-size: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="broadsheet-wrapper">

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- School Header --}}
    <div class="school-header">
        <div class="d-flex align-items-center">
            @if(!empty($school_logo_base64))
                <div class="me-3">
                    <img src="{{ $school_logo_base64 }}" alt="Logo" style="width: 70px; height: 70px; object-fit: contain; border-radius: 50%; border: 2px solid white;">
                </div>
            @endif
            <div class="flex-grow-1 text-center">
                <h2 class="mb-1 fw-bold">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</h2>
                @if(!empty($schoolInfo->school_address))
                    <p class="mb-1 opacity-75">{{ $schoolInfo->school_address }}</p>
                @endif
                @if(!empty($schoolInfo->school_motto))
                    <p class="mb-0 fst-italic">"{{ $schoolInfo->school_motto }}"</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Title --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold" style="color: var(--bs-pri);">CLASS ACADEMIC BROADSHEET</h3>
        <div class="d-flex justify-content-center gap-3 mt-2">
            <span class="badge bg-primary">{{ $schoolclass->schoolclass ?? '-' }} {{ $schoolclass->arm_name ?? '' }}</span>
            <span class="badge bg-success">{{ $schoolsession->session ?? '-' }}</span>
            <span class="badge bg-warning">{{ $schoolterm->term ?? '-' }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="ri-group-line" style="font-size: 28px; color: var(--bs-acc);"></i>
                <span class="stat-value">{{ $totalStudents }}</span>
                <small class="text-muted">Total Students</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="ri-book-open-line" style="font-size: 28px; color: var(--bs-success);"></i>
                <span class="stat-value">{{ count($subjects) }}</span>
                <small class="text-muted">Subjects</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="ri-clipboard-line" style="font-size: 28px; color: var(--bs-warn);"></i>
                <span class="stat-value">{{ $assessments->count() }}</span>
                <small class="text-muted">Assessments</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="ri-calendar-line" style="font-size: 28px; color: var(--bs-danger);"></i>
                <span class="stat-value" style="font-size: 14px;">{{ $generatedAt }}</span>
                <small class="text-muted">Generated</small>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="toolbar-card">
        <div class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                    <input type="text" class="form-control search-input" id="searchStudent"
                           placeholder="Search by name or admission number...">
                </div>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <select class="form-select locate-select" id="locateStudent">
                    <option value="">🔍 Locate Student...</option>
                    <option value="top5">🏆 Top 5 Students (by GPA)</option>
                    <option value="top10">⭐ Top 10 Students (by GPA)</option>
                    <option value="failures">⚠️ Students with Failures (F9)</option>
                    <option value="below_avg">📉 Below Class Average</option>
                    <option value="missing_scores">❌ Missing Assessment Scores</option>
                    <option disabled>──────────</option>
                    @foreach($studentRows as $student)
                        <option value="student_{{ $student['id'] }}">👤 {{ $student['lastname'] }}, {{ $student['firstname'] }} ({{ $student['admissionno'] }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-sm btn-outline-primary me-2" onclick="window.print();">
                    <i class="ri-printer-line"></i> Print
                </button>
                <button class="btn btn-sm btn-primary" onclick="scrollToTop();">
                    <i class="ri-arrow-up-line"></i> Top
                </button>
            </div>
        </div>
    </div>

    {{-- Grade Key --}}
    <div class="toolbar-card mb-3">
        <div class="row align-items-center">
            <div class="col-md-2 fw-bold">GRADING SCALE:</div>
            <div class="col-md-10">
                @php
                $gradeKey = [
                    'A1' => ['75-100', '#16a34a'], 'B2' => ['70-74', '#1d4ed8'], 'B3' => ['65-69', '#2563eb'],
                    'C4' => ['60-64', '#d97706'], 'C5' => ['55-59', '#b45309'], 'C6' => ['50-54', '#92400e'],
                    'D7' => ['45-49', '#ea580c'], 'E8' => ['40-44', '#c2410c'], 'F9' => ['0-39', '#dc2626'],
                ];
                @endphp
                @foreach($gradeKey as $grade => $info)
                    <span class="badge me-2 mb-1" style="background: {{ $info[1] }};">{{ $grade }} ({{ $info[0] }})</span>
                @endforeach
                <span class="text-muted ms-2">
                    <small><strong>BF</strong>=Brought Forward | <strong>CUM</strong>=Cumulative | <strong>POS</strong>=Position</small>
                </span>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="table-container">
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
        @endphp

        <table class="broadsheet-table" id="broadsheetTable">
            <thead>
                {{-- Subject headers --}}
                <tr class="subject-header">
                    <th rowspan="2" style="width: 40px;">#</th>
                    @if($showAdmNo)
                        <th rowspan="2" style="min-width: 80px;">Admission No</th>
                    @endif
                    <th rowspan="2" style="min-width: 180px; text-align: left;">Student Name</th>
                    @if($showGender)
                        <th rowspan="2" style="width: 50px;">Gender</th>
                    @endif
                    @foreach($subjects as $subId => $subInfo)
                        <th colspan="{{ $activeAssessments->count() + ($showTotal?1:0) + ($showBF?1:0) + ($showCum?1:0) + ($showGrade?1:0) + ($showPosition?1:0) + ($showAvg?1:0) + ($showRemark?1:0) }}">
                            {{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))
                                <br><small>({{ $subInfo['subject_code'] }})</small>
                            @endif
                        </th>
                    @endforeach
                    @php $gpaCount = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0); @endphp
                    @if($gpaCount > 0)
                        <th colspan="{{ $gpaCount }}" style="background: #0a1e38;">GPA METRICS</th>
                    @endif
                </tr>

                {{-- Assessment sub-headers --}}
                <tr class="assessment-header">
                    @foreach($subjects as $subId => $subInfo)
                        @foreach($activeAssessments as $aIdx => $a)
                            <th style="min-width: 55px;">
                                {{ $a->name }}<br>
                                <small>/{{ $a->max_score }}</small>
                            </th>
                        @endforeach
                        @if($showTotal) <th>Total</th> @endif
                        @if($showBF) <th>BF</th> @endif
                        @if($showCum) <th>Cum</th> @endif
                        @if($showGrade) <th>Grade</th> @endif
                        @if($showPosition) <th>Pos</th> @endif
                        @if($showAvg) <th>Avg</th> @endif
                        @if($showRemark) <th>Remark</th> @endif
                    @endforeach
                    @if($showGPA) <th>GPA</th> @endif
                    @if($showCGPA) <th>CGPA</th> @endif
                    @if($showGPAGrade) <th>GGrd</th> @endif
                    @if($showNumSub) <th>#Sub</th> @endif
                    @if($showTotalGP) <th>TGP</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($studentRows as $idx => $stu)
                    @php
                        $classAvgAll = $stu['class_average'] ?? 0;
                        $hasFailure = false;
                        foreach($stu['subjects'] as $subData) {
                            if(($subData['grade'] ?? '') === 'F9') { $hasFailure = true; break; }
                        }
                    @endphp
                    <tr data-student-id="{{ $stu['id'] }}"
                        data-student-name="{{ $stu['lastname'] }}, {{ $stu['firstname'] }}"
                        data-admission="{{ $stu['admissionno'] }}"
                        data-gpa="{{ $stu['gpa'] }}"
                        data-has-failure="{{ $hasFailure ? 'true' : 'false' }}"
                        data-class-avg="{{ $classAvgAll }}">

                        <td>{{ $idx + 1 }}</td>
                        @if($showAdmNo)
                            <td>{{ $stu['admissionno'] }}</td>
                        @endif
                        <td class="student-info-cell">
                            <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
                        </td>
                        @if($showGender)
                            <td>{{ substr($stu['gender'] ?? '', 0, 1) }}</td>
                        @endif

                        @foreach($subjects as $subId => $subInfo)
                            @php
                                $sd = $stu['subjects'][$subId] ?? [];
                                $grade = $sd['grade'] ?? '-';
                                $gradeClass = $gradeColors[$grade] ?? '';
                            @endphp
                            @foreach($activeAssessments as $a)
                                @php $score = $sd['assessments'][$a->id] ?? 0; @endphp
                                <td>{{ $score > 0 ? number_format($score, 1) : '—' }}</td>
                            @endforeach
                            @if($showTotal) <td class="{{ $gradeClass }}">{{ ($sd['total']??0) > 0 ? number_format($sd['total'],1) : '—' }}</td> @endif
                            @if($showBF) <td>{{ ($sd['bf']??0) > 0 ? number_format($sd['bf'],1) : '—' }}</td> @endif
                            @if($showCum) <td class="{{ $gradeClass }} fw-bold">{{ ($sd['cum']??0) > 0 ? number_format($sd['cum'],1) : '—' }}</td> @endif
                            @if($showGrade) <td class="{{ $gradeClass }} fw-bold">{{ $grade }}</td> @endif
                            @if($showPosition) <td>{{ $sd['position'] ?? '—' }}</td> @endif
                            @if($showAvg) <td>{{ $subjectStats[$subId]['avg'] ?? '—' }}</td> @endif
                            @if($showRemark) <td>{{ $sd['remark'] ?? '—' }}</td> @endif
                        @endforeach

                        @if($showGPA) <td class="grade-{{ $stu['gpa_grade'] ?? 'f9' }}">{{ number_format($stu['gpa'], 2) }}</td> @endif
                        @if($showCGPA) <td>{{ number_format($stu['cgpa'], 2) }}</td> @endif
                        @if($showGPAGrade) <td class="grade-{{ $stu['gpa_grade'] ?? 'f9' }} fw-bold">{{ $stu['gpa_grade'] ?? '—' }}</td> @endif
                        @if($showNumSub) <td>{{ $stu['num_subjects'] ?? '—' }}</td> @endif
                        @if($showTotalGP) <td>{{ number_format($stu['total_grade_points'], 1) }}</td> @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h5>Processing...</h5>
            <p class="text-muted mb-0">Please wait while we load the data</p>
        </div>
    </div>
</div>

<script>
// Search functionality with animation
const searchInput = document.getElementById('searchStudent');
const tableRows = document.querySelectorAll('#broadsheetTable tbody tr');

searchInput.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    let visibleCount = 0;

    tableRows.forEach(row => {
        const name = row.getAttribute('data-student-name')?.toLowerCase() || '';
        const admission = row.getAttribute('data-admission')?.toLowerCase() || '';
        const matches = name.includes(searchTerm) || admission.includes(searchTerm);

        if (matches) {
            row.style.display = '';
            visibleCount++;
            // Add highlight animation
            row.style.animation = 'none';
            setTimeout(() => {
                row.style.animation = 'pulse 0.3s ease';
            }, 10);
        } else {
            row.style.display = 'none';
        }
    });

    // Show result count
    if (searchTerm.length > 0) {
        showTemporaryMessage(`Found ${visibleCount} student(s)`, 'info');
    }
});

// Locate functionality
const locateSelect = document.getElementById('locateStudent');
locateSelect.addEventListener('change', function(e) {
    const value = e.target.value;
    if (!value) return;

    // Remove previous highlights
    tableRows.forEach(row => {
        row.classList.remove('highlight-row');
        row.style.opacity = '1';
    });

    if (value === 'top5') {
        highlightTopStudents(5);
    } else if (value === 'top10') {
        highlightTopStudents(10);
    } else if (value === 'failures') {
        highlightFailures();
    } else if (value === 'below_avg') {
        highlightBelowAverage();
    } else if (value === 'missing_scores') {
        highlightMissingScores();
    } else if (value.startsWith('student_')) {
        const studentId = value.replace('student_', '');
        highlightStudent(studentId);
    }

    // Reset select
    setTimeout(() => {
        locateSelect.value = '';
    }, 100);
});

function highlightTopStudents(count) {
    const rows = Array.from(tableRows).filter(row => row.style.display !== 'none');
    const sortedRows = [...rows].sort((a, b) => {
        const gpaA = parseFloat(a.getAttribute('data-gpa')) || 0;
        const gpaB = parseFloat(b.getAttribute('data-gpa')) || 0;
        return gpaB - gpaA;
    });

    sortedRows.slice(0, count).forEach(row => {
        row.classList.add('highlight-row');
        scrollToRow(row);
    });

    showTemporaryMessage(`Highlighted top ${count} students`, 'success');
}

function highlightFailures() {
    let failureCount = 0;
    tableRows.forEach(row => {
        if (row.getAttribute('data-has-failure') === 'true') {
            row.classList.add('highlight-row');
            failureCount++;
        }
    });
    showTemporaryMessage(`${failureCount} student(s) have at least one failure (F9)`, 'warning');
}

function highlightBelowAverage() {
    let belowAvgCount = 0;
    tableRows.forEach(row => {
        const gpa = parseFloat(row.getAttribute('data-gpa')) || 0;
        const classAvg = parseFloat(row.getAttribute('data-class-avg')) || 0;
        if (gpa < classAvg && gpa > 0) {
            row.classList.add('highlight-row');
            belowAvgCount++;
        }
    });
    showTemporaryMessage(`${belowAvgCount} student(s) below class average`, 'info');
}

function highlightMissingScores() {
    let missingCount = 0;
    tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let hasMissing = false;
        cells.forEach(cell => {
            if (cell.textContent.trim() === '—' && !cell.classList.contains('student-info-cell')) {
                hasMissing = true;
            }
        });
        if (hasMissing) {
            row.classList.add('highlight-row');
            missingCount++;
        }
    });
    showTemporaryMessage(`${missingCount} student(s) have missing assessment scores`, 'warning');
}

function highlightStudent(studentId) {
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (row) {
        row.classList.add('highlight-row');
        scrollToRow(row);
        showTemporaryMessage(`Located student: ${row.getAttribute('data-student-name')}`, 'success');
    }
}

function scrollToRow(row) {
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showTemporaryMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'info' ? 'info' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.animation = 'slideInRight 0.3s ease';
    alertDiv.innerHTML = `
        <i class="ri-information-line me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// Add loading animation on page load
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.add('active');
    setTimeout(() => {
        overlay.classList.remove('active');
    }, 500);
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>
@endsection
