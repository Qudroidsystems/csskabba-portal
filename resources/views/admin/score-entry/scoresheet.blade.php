{{-- resources/views/admin/score-entry/scoresheet.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --ss-primary: #1e3a5f;
    --ss-accent: #2563eb;
    --ss-success: #16a34a;
    --ss-warning: #d97706;
    --ss-danger: #dc2626;
    --ss-muted: #6b7280;
    --ss-border: #e2e8f0;
    --ss-bg: #f8fafc;
    --ss-card: #ffffff;
    --ss-radius: 10px;
    --ss-shadow: 0 1px 4px rgba(0,0,0,.08);
}

/* Admin Banner */
.admin-banner {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-left: 4px solid #0284c7;
    border-radius: var(--ss-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
    animation: slideIn 0.4s ease;
}
@keyframes slideIn {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Stat Cards */
.stat-card {
    background: var(--ss-card);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    padding: 14px 18px;
    box-shadow: var(--ss-shadow);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.stat-card .stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--ss-primary);
}
.stat-card .stat-label {
    font-size: 11px;
    color: var(--ss-muted);
    margin-top: 2px;
}
.stat-card .stat-icon {
    font-size: 28px;
    opacity: 0.15;
    float: right;
    margin-top: -8px;
}

/* Grade Distribution */
.grade-distribution {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 12px;
}
.grade-pill {
    flex: 1;
    min-width: 60px;
    text-align: center;
    border-radius: 8px;
    padding: 8px 6px;
    font-weight: 700;
    font-size: 12px;
    transition: transform 0.2s ease;
}
.grade-pill:hover {
    transform: scale(1.05);
}
.pass-bar {
    height: 6px;
    border-radius: 3px;
    background: #e2e8f0;
    overflow: hidden;
    margin-top: 8px;
}
.pass-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.4s ease;
}

/* Score Input */
.score-input {
    width: 80px;
    text-align: center;
    padding: 6px 8px;
    border: 1.5px solid var(--ss-border);
    border-radius: 6px;
    font-size: 12px;
    transition: all 0.2s ease;
}
.score-input:focus {
    border-color: var(--ss-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.score-input.is-invalid {
    border-color: var(--ss-danger);
    background: #fef2f2;
}
.score-input.is-saved {
    border-color: var(--ss-success);
    background: #f0fdf4;
    animation: pulse 0.4s ease;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

/* Table Styles */
.table-scoresheet {
    font-size: 12px;
}
.table-scoresheet thead th {
    background: var(--ss-primary);
    color: #fff;
    padding: 10px 8px;
    font-weight: 600;
    white-space: nowrap;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
}
.table-scoresheet tbody td {
    padding: 8px;
    vertical-align: middle;
    border-bottom: 1px solid var(--ss-border);
}
.table-scoresheet tbody tr {
    transition: background-color 0.2s ease;
}
.table-scoresheet tbody tr:hover {
    background-color: rgba(37, 99, 235, 0.05);
}

/* Row Status Classes */
.row-vetted { background: #f0fdf4 !important; }
.row-not-vetted { background: #fef2f2 !important; }
.row-pending { background: #fffbeb !important; }

/* Badge Styles */
.position-badge {
    background: var(--ss-primary);
    color: white;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    min-width: 40px;
    text-align: center;
}
.grade-badge {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.2s ease;
}
.grade-badge.updating {
    opacity: 0.6;
    transform: scale(0.95);
}
.grade-badge.updated {
    animation: gradeFlash 0.4s ease;
}
@keyframes gradeFlash {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); background: #fef3c7; }
}

/* Assessment Groups */
.assessment-group {
    border: 1px solid var(--ss-border);
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}
.assessment-group:hover {
    border-color: var(--ss-accent);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.assessment-group h6 {
    color: var(--ss-primary);
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 12px;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(3px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s ease;
}
.loading-overlay.active {
    visibility: visible;
    opacity: 1;
}
.loading-spinner {
    background: #fff;
    padding: 30px 40px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 20px 35px rgba(0,0,0,0.2);
}
.loading-spinner .spinner-border {
    width: 40px;
    height: 40px;
}

/* Toast Customization */
.toast-container {
    z-index: 10000;
}

/* Responsive */
@media (max-width: 768px) {
    .score-input {
        width: 60px;
        font-size: 11px;
        padding: 4px 6px;
    }
    .stat-card .stat-value {
        font-size: 18px;
    }
    .table-scoresheet {
        font-size: 10px;
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Admin Banner --}}
    <div class="admin-banner">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <i class="ri-shield-user-line fs-2" style="color: #0284c7;"></i>
                <div>
                    <strong class="d-block" style="font-size: 15px;">Admin Score Entry Mode</strong>
                    <small class="text-muted">
                        Entering scores on behalf of: <strong>{{ $teacher->name }}</strong> |
                        Subject: <strong>{{ $subjectClass->subject->subject }}</strong> ({{ $subjectClass->subject->subject_code }}) |
                        Class: <strong>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm->arm ?? '' }}</strong>
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary"><i class="ri-calendar-line me-1"></i>{{ $term->term }}</span>
                <span class="badge bg-info"><i class="ri-calendar-event-line me-1"></i>{{ $session->session }}</span>
            </div>
        </div>
    </div>

    @if($broadsheets->isNotEmpty())
    @php
        $first = $broadsheets->first();
        $total = $broadsheets->count();
        $passed = $broadsheets->filter(fn($b) => ($b->total ?? 0) >= 40)->count();
        $failed = $total - $passed;
        $avg = $total > 0 ? round($broadsheets->avg('total'), 1) : 0;
        $highest = $total > 0 ? round($broadsheets->max('total'), 1) : 0;
        $lowest = $total > 0 ? round($broadsheets->min('total'), 1) : 0;
        $passRate = $total > 0 ? round($passed / $total * 100) : 0;

        // Grade distribution
        $gradeDist = $broadsheets->groupBy('grade')->map->count();
        $gradeColors = [
            'A' => '#16a34a', 'A1' => '#16a34a',
            'B' => '#2563eb', 'B2' => '#2563eb', 'B3' => '#3b82f6',
            'C' => '#7c3aed', 'C4' => '#7c3aed', 'C5' => '#8b5cf6', 'C6' => '#a78bfa',
            'D' => '#d97706', 'D7' => '#d97706', 'E8' => '#f59e0b',
            'F' => '#dc2626', 'F9' => '#dc2626',
        ];
    @endphp

    {{-- Stats Cards Row --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-bar-chart-line"></i></div>
                <div class="stat-value text-warning">{{ $avg }}</div>
                <div class="stat-label">Class Average</div>
                <div class="pass-bar">
                    <div class="pass-bar-fill" style="width: {{ $avg }}%; background: #d97706;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-value text-success">{{ $passed }}</div>
                <div class="stat-label">Passed</div>
                <div class="pass-bar">
                    <div class="pass-bar-fill" style="width: {{ $passRate }}%; background: #16a34a;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-close-circle-line"></i></div>
                <div class="stat-value text-danger">{{ $failed }}</div>
                <div class="stat-label">Failed</div>
                <div class="pass-bar">
                    <div class="pass-bar-fill" style="width: {{ 100 - $passRate }}%; background: #dc2626;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grade Distribution and Assessment Summary Row --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-semibold mb-0"><i class="ri-pie-chart-line me-1"></i>Grade Distribution</h6>
                </div>
                <div class="card-body pt-0">
                    @if($gradeDist->isEmpty())
                        <p class="text-muted text-center py-3">No grades entered yet</p>
                    @else
                        <div class="grade-distribution">
                            @foreach($gradeDist->sortKeys() as $grade => $count)
                                @php
                                    $pct = $total > 0 ? round($count/$total*100) : 0;
                                    $color = $gradeColors[$grade] ?? '#6b7280';
                                @endphp
                                <div class="grade-pill" style="background: {{ $color }}15; color: {{ $color }}; border: 1px solid {{ $color }}30;">
                                    <div style="font-size: 14px; font-weight: 700;">{{ $grade }}</div>
                                    <div style="font-size: 10px;">{{ $count }} ({{ $pct }}%)</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-semibold mb-0"><i class="ri-clipboard-line me-1"></i>Assessments</h6>
                </div>
                <div class="card-body pt-0">
                    @if($assessments->isNotEmpty())
                        <div class="row g-2">
                            @foreach($assessments as $assessment)
                                <div class="col-md-6">
                                    <div class="assessment-group">
                                        <h6>
                                            <i class="ri-edit-line me-1"></i>{{ $assessment->name }}
                                            <span class="badge bg-secondary float-end">{{ $assessment->max_score }}</span>
                                        </h6>
                                        @if($assessment->subAssessments->isNotEmpty())
                                            <small class="text-muted d-block">
                                                {{ $assessment->subAssessments->count() }} sub-assessments
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No assessments defined for this class category</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Scoresheet Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header" style="background: var(--ss-primary);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-white">
                    <i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }}
                    <span class="badge bg-white text-primary ms-2" id="studentCount">{{ $total }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-white border-0"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control border-0" id="searchInput" placeholder="Search student...">
                        <button class="btn btn-light border-0" id="clearSearch"><i class="ri-close-line"></i></button>
                    </div>
                    <button class="btn btn-sm btn-light" id="refreshBtn">
                        <i class="ri-refresh-line me-1"></i>Refresh
                    </button>
                    <button class="btn btn-sm btn-info" id="downloadMarksSheetBtn">
                        <i class="ri-file-pdf-line me-1"></i>Marks Sheet
                    </button>
                    <button class="btn btn-sm btn-success" id="downloadExcelBtn">
                        <i class="ri-download-line me-1"></i>Export Excel
                    </button>
                    <button class="btn btn-sm btn-warning" id="recalculatePositionsBtn">
                        <i class="ri-calculator-line me-1"></i>Recalc Positions
                    </button>
                    <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-sm btn-outline-light">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-scoresheet table-nowrap align-middle mb-0" id="scoresheetTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </div>
                            </th>
                            <th style="width: 50px;">#</th>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            @foreach($assessments as $assessment)
                                <th class="text-center" style="min-width: 85px;">
                                    {{ $assessment->name }}<br>
                                    <small class="fw-normal opacity-75">({{ $assessment->max_score }})</small>
                                </th>
                            @endforeach
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">BF</th>
                            <th class="text-center">Cum</th>
                            <th class="text-center">Class Pos</th>
                            <th class="text-center">Arm Pos</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="scoresheetTableBody">
                        @php $i = 0; @endphp
                        @foreach($broadsheets as $broadsheet)
                            @php
                                $rowTotal = 0;
                                $assessmentScores = [];
                                foreach($assessments as $a) {
                                    $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                                    $score = $so ? $so->score : 0;
                                    $rowTotal += $score;
                                    $assessmentScores[$a->id] = $score;
                                }
                                $totalColor = $rowTotal >= 70 ? 'success' : ($rowTotal >= 50 ? 'info' : ($rowTotal >= 40 ? 'warning' : 'danger'));
                                $cum = $broadsheet->cum ?? 0;
                                $cumColor = $cum >= 70 ? 'success' : ($cum >= 50 ? 'info' : ($cum >= 40 ? 'warning' : 'danger'));
                                $vClass = $broadsheet->vettedstatus === '1' ? 'row-vetted' : ($broadsheet->vettedstatus === '0' ? 'row-not-vetted' : 'row-pending');
                                $avatarUrl = $broadsheet->picture
                                    ? asset('storage/student_avatars/'.basename($broadsheet->picture))
                                    : asset('storage/student_avatars/unnamed.jpg');
                            @endphp
                            <tr class="{{ $vClass }}" data-id="{{ $broadsheet->id }}" data-bf="{{ $broadsheet->bf ?? 0 }}"
                                data-name="{{ strtolower(($broadsheet->lname ?? '').' '.($broadsheet->fname ?? '')) }}"
                                data-admission="{{ strtolower($broadsheet->admissionno ?? '') }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}">
                                    </div>
                                </td>
                                <td>{{ ++$i }}</td>
                                <td><span class="text-muted small">{{ $broadsheet->admissionno ?? '-' }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $avatarUrl }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                        <div>
                                            <span class="fw-semibold">{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}</span>
                                            @if($broadsheet->mname)
                                                <br><small class="text-muted">{{ $broadsheet->mname }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @foreach($assessments as $assessment)
                                    <td class="text-center">
                                        <input type="number"
                                               class="score-input"
                                               data-field="{{ $assessment->id }}"
                                               data-max="{{ $assessment->max_score }}"
                                               data-id="{{ $broadsheet->id }}"
                                               data-original="{{ $assessmentScores[$assessment->id] }}"
                                               value="{{ $assessmentScores[$assessment->id] }}"
                                               min="0" max="{{ $assessment->max_score }}" step="0.5"
                                               style="width: 75px;">
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    <span class="badge bg-{{ $totalColor }}-subtle text-{{ $totalColor }} total-badge" style="font-size: 12px;">
                                        {{ number_format($rowTotal, 1) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="grade-badge" style="background: {{ $gradeColors[$broadsheet->grade] ?? '#6b7280' }}20; color: {{ $gradeColors[$broadsheet->grade] ?? '#6b7280' }}; padding: 4px 8px; border-radius: 20px;">
                                        {{ $broadsheet->grade ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="bf-badge">{{ number_format($broadsheet->bf ?? 0, 1) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $cumColor }}-subtle text-{{ $cumColor }} cum-badge" style="font-size: 12px;">
                                        {{ number_format($cum, 1) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="position-badge">{{ $broadsheet->position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="position-badge" style="background: #0891b2;">{{ $broadsheet->arm_position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position) : '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($broadsheet->vettedstatus === '1')
                                        <span class="badge bg-success"><i class="ri-check-line me-1"></i>Vetted</span>
                                    @elseif($broadsheet->vettedstatus === '0')
                                        <span class="badge bg-danger"><i class="ri-close-line me-1"></i>Not Vetted</span>
                                    @else
                                        <span class="badge bg-warning"><i class="ri-time-line me-1"></i>Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Action Footer --}}
            @if($broadsheets->isNotEmpty())
            <div class="p-3 border-top" style="background: #f8fafc;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                            <i class="ri-check-double-line me-1"></i>Select All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="clearAllBtn">
                            <i class="ri-close-line me-1"></i>Clear
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="deleteSelectedBtn">
                            <i class="ri-delete-bin-line me-1"></i>Delete Selected
                        </button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <small class="text-muted"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small>
                        <button class="btn btn-success btn-sm px-4" id="bulkSaveBtn">
                            <i class="ri-save-line me-1"></i>Save All Scores
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ri-inbox-line ri-3x text-muted mb-3"></i>
                <h5>No Students Found</h5>
                <p class="text-muted">No students are registered for this subject in the selected term.</p>
                <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-primary">
                    <i class="ri-arrow-left-line me-1"></i>Back to Teachers
                </a>
            </div>
        </div>
    @endif

</div>
</div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0" id="loadingMessage">Processing...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    const CSRF = '{{ csrf_token() }}';
    const adminRoutes = {
        singleUpdate: '{{ route("admin.score-entry.single-update") }}',
        bulkUpdate: '{{ route("admin.score-entry.bulk-update") }}',
        destroy: '{{ route("admin.score-entry.destroy") }}',
        results: '{{ route("admin.score-entry.results") }}',
        downloadMarksSheet: '{{ route("admin.score-entry.download-marks-sheet") }}',
        export: '{{ route("admin.score-entry.export") }}',
    };

    const context = {
        term_id: {{ $termId }},
        session_id: {{ $sessionId }},
        subjectclass_id: {{ $subjectclassId }},
        schoolclass_id: {{ $schoolclass->id ?? 0 }},
        staff_id: {{ $teacherId }},
        is_senior: {{ $is_senior ? 'true' : 'false' }},
    };

    // Helper functions
    function ordinal(n) {
        if (!n || isNaN(n)) return '-';
        const s = ['th', 'st', 'nd', 'rd'];
        const v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }

    function clientGrade(score) {
        score = parseFloat(score) || 0;
        if (context.is_senior) {
            if (score >= 75) return 'A1';
            if (score >= 70) return 'B2';
            if (score >= 65) return 'B3';
            if (score >= 60) return 'C4';
            if (score >= 55) return 'C5';
            if (score >= 50) return 'C6';
            if (score >= 45) return 'D7';
            if (score >= 40) return 'E8';
            return 'F9';
        }
        if (score >= 70) return 'A';
        if (score >= 60) return 'B';
        if (score >= 50) return 'C';
        if (score >= 40) return 'D';
        return 'F';
    }

    // Validate input
    function validateInput(input) {
        const max = parseFloat(input.data('max')) || 0;
        const val = parseFloat(input.val()) || 0;
        if (val > max) {
            input.addClass('is-invalid');
            toastr.warning(`${input.data('field')} score cannot exceed ${max}`);
            return false;
        }
        input.removeClass('is-invalid');
        return true;
    }

    // Update row totals and grades in real-time
    function updateRowGrades(row) {
        let totalRaw = 0;
        row.find('.score-input').each(function() {
            totalRaw += parseFloat($(this).val()) || 0;
        });

        // Update total badge
        const totalBadge = row.find('.total-badge');
        totalBadge.text(totalRaw.toFixed(1));
        const totalColor = totalRaw >= 70 ? 'success' : (totalRaw >= 50 ? 'info' : (totalRaw >= 40 ? 'warning' : 'danger'));
        totalBadge.removeClass().addClass(`badge total-badge bg-${totalColor}-subtle text-${totalColor}`);

        // Update grade (client-side preview)
        const gradeSpan = row.find('.grade-badge');
        const previewGrade = clientGrade(totalRaw);
        gradeSpan.text(previewGrade);

        // Update cumulative
        const bf = parseFloat(row.data('bf')) || 0;
        const termId = context.term_id;
        const cum = (termId == 1 || bf === 0) ? totalRaw : (totalRaw + bf) / 2;
        const cumBadge = row.find('.cum-badge');
        cumBadge.text(cum.toFixed(1));
        const cumColor = cum >= 70 ? 'success' : (cum >= 50 ? 'info' : (cum >= 40 ? 'warning' : 'danger'));
        cumBadge.removeClass().addClass(`badge cum-badge bg-${cumColor}-subtle text-${cumColor}`);
    }

    // Single score save
    function saveScore(input) {
        if (!validateInput(input)) return;

        const row = input.closest('tr');
        const broadsheetId = input.data('id');
        const assessmentId = input.data('field');
        const score = parseFloat(input.val()) || 0;

        $.ajax({
            url: adminRoutes.singleUpdate,
            method: 'POST',
            data: {
                broadsheet_id: broadsheetId,
                assessment_id: assessmentId,
                score: score,
                is_sub: false,
                term_id: context.term_id,
                session_id: context.session_id,
                subjectclass_id: context.subjectclass_id,
                schoolclass_id: context.schoolclass_id,
                staff_id: context.staff_id,
                _token: CSRF
            },
            success: function(response) {
                if (response.success) {
                    input.addClass('is-saved');
                    setTimeout(() => input.removeClass('is-saved'), 1000);
                    input.data('original', score);

                    // Update affected fields
                    const data = response.data;
                    row.find('.bf-badge').text(data.bf.toFixed(1));
                    row.find('.grade-badge').text(data.grade);
                    row.find('.position-badge').first().text(ordinal(data.subject_position_class));

                    const cum = data.cum;
                    const cumBadge = row.find('.cum-badge');
                    cumBadge.text(cum.toFixed(1));
                    const cumColor = cum >= 70 ? 'success' : (cum >= 50 ? 'info' : (cum >= 40 ? 'warning' : 'danger'));
                    cumBadge.removeClass().addClass(`badge cum-badge bg-${cumColor}-subtle text-${cumColor}`);

                    toastr.success('Score saved successfully');
                } else {
                    toastr.error(response.message || 'Failed to save score');
                    input.val(input.data('original'));
                }
            },
            error: function() {
                toastr.error('Network error. Please try again.');
                input.val(input.data('original'));
            }
        });
    }

    // Bulk save all scores
    function bulkSave() {
        const invalidInputs = $('.score-input.is-invalid');
        if (invalidInputs.length) {
            toastr.warning(`Please fix ${invalidInputs.length} invalid score(s) before saving.`);
            return;
        }

        const scores = [];
        $('#scoresheetTableBody tr').each(function() {
            const row = $(this);
            const assessments = {};
            row.find('.score-input').each(function() {
                assessments[$(this).data('field')] = parseFloat($(this).val()) || 0;
            });
            if (Object.keys(assessments).length) {
                scores.push({ id: row.data('id'), assessments: assessments });
            }
        });

        if (!scores.length) {
            toastr.warning('No scores to save');
            return;
        }

        $('#loadingOverlay').addClass('active');
        $('#loadingMessage').text('Saving scores...');

        $.ajax({
            url: adminRoutes.bulkUpdate,
            method: 'POST',
            data: JSON.stringify({
                scores: scores,
                term_id: context.term_id,
                session_id: context.session_id,
                subjectclass_id: context.subjectclass_id,
                staff_id: context.staff_id,
                schoolclass_id: context.schoolclass_id,
                is_sub: false,
                _token: CSRF
            }),
            contentType: 'application/json',
            success: function(response) {
                $('#loadingOverlay').removeClass('active');
                if (response.success) {
                    toastr.success(response.message);

                    if (response.data && response.data.broadsheets) {
                        response.data.broadsheets.forEach(bs => {
                            const row = $(`#scoresheetTableBody tr[data-id="${bs.id}"]`);
                            if (row.length) {
                                row.find('.total-badge').text(bs.total.toFixed(1));
                                row.find('.grade-badge').text(bs.grade);
                                row.find('.bf-badge').text(bs.bf.toFixed(1));
                                row.find('.cum-badge').text(bs.cum.toFixed(1));
                                row.find('.position-badge').first().text(ordinal(bs.position));

                                row.find('.score-input').each(function() {
                                    $(this).addClass('is-saved');
                                    setTimeout(() => $(this).removeClass('is-saved'), 1000);
                                });
                            }
                        });
                    }
                    location.reload();
                } else {
                    toastr.error(response.message || 'Bulk save failed');
                }
            },
            error: function() {
                $('#loadingOverlay').removeClass('active');
                toastr.error('Network error. Please try again.');
            }
        });
    }

    // Event Listeners
    $('.score-input').on('input', function() {
        validateInput($(this));
        updateRowGrades($(this).closest('tr'));
    });

    $('.score-input').on('blur', function() {
        const input = $(this);
        const original = parseFloat(input.data('original')) || 0;
        const current = parseFloat(input.val()) || 0;
        if (Math.abs(current - original) > 0.001 && validateInput(input)) {
            saveScore(input);
        }
    });

    $('.score-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).blur();
        }
    });

    // Checkbox handling
    $('#checkAll').on('change', function() {
        $('.score-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#selectAllBtn').on('click', function() {
        $('#checkAll').prop('checked', true).trigger('change');
    });

    $('#clearAllBtn').on('click', function() {
        $('#checkAll').prop('checked', false).trigger('change');
    });

    // Delete selected
    $('#deleteSelectedBtn').on('click', function() {
        const selected = $('.score-checkbox:checked');
        if (!selected.length) {
            toastr.warning('Please select scores to delete');
            return;
        }

        Swal.fire({
            title: 'Delete selected scores?',
            text: `You are about to delete ${selected.length} score(s). This cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (result.isConfirmed) {
                const ids = selected.map(function() { return $(this).data('id'); }).get();
                let deleted = 0;

                $('#loadingOverlay').addClass('active');

                ids.forEach(id => {
                    $.ajax({
                        url: adminRoutes.destroy,
                        method: 'DELETE',
                        data: { id: id, type: 'terminal', _token: CSRF },
                        async: false,
                        success: function(response) {
                            if (response.success) {
                                $(`tr[data-id="${id}"]`).remove();
                                deleted++;
                            }
                        }
                    });
                });

                $('#loadingOverlay').removeClass('active');
                toastr.success(`${deleted} score(s) deleted`);

                if (deleted === ids.length && deleted > 0) {
                    location.reload();
                }
            }
        });
    });

    // Bulk save
    $('#bulkSaveBtn').on('click', bulkSave);

    // Refresh
    $('#refreshBtn').on('click', function() {
        location.reload();
    });

    // Search functionality
    $('#searchInput').on('input', function() {
        const term = $(this).val().toLowerCase();
        let visibleCount = 0;

        $('#scoresheetTableBody tr').each(function() {
            const name = $(this).data('name') || '';
            const admission = $(this).data('admission') || '';
            const matches = term === '' || name.includes(term) || admission.includes(term);
            $(this).toggle(matches);
            if (matches) visibleCount++;
        });

        $('#studentCount').text(visibleCount);
    });

    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        $('#searchInput').trigger('input');
    });

    // Download buttons
    $('#downloadMarksSheetBtn').on('click', function() {
        const url = `${adminRoutes.downloadMarksSheet}?subjectclass_id=${context.subjectclass_id}&staff_id=${context.staff_id}&term_id=${context.term_id}&session_id=${context.session_id}&schoolclass_id=${context.schoolclass_id}&type=terminal`;
        window.open(url, '_blank');
    });

    $('#downloadExcelBtn').on('click', function() {
        const url = `${adminRoutes.export}?subjectclass_id=${context.subjectclass_id}&staff_id=${context.staff_id}&term_id=${context.term_id}&session_id=${context.session_id}&schoolclass_id=${context.schoolclass_id}`;
        window.open(url, '_blank');
    });

    // Recalculate positions
    $('#recalculatePositionsBtn').on('click', function() {
        $('#loadingOverlay').addClass('active');
        $('#loadingMessage').text('Recalculating positions...');

        $.ajax({
            url: '{{ route("update.arm.positions.all") }}',
            method: 'POST',
            data: {
                schoolclass_id: context.schoolclass_id,
                term_id: context.term_id,
                session_id: context.session_id,
                _token: CSRF
            },
            success: function(response) {
                $('#loadingOverlay').removeClass('active');
                if (response.success) {
                    toastr.success('Positions recalculated successfully');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Failed to recalculate positions');
                }
            },
            error: function() {
                $('#loadingOverlay').removeClass('active');
                toastr.error('Network error');
            }
        });
    });

    // Keyboard shortcut
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            bulkSave();
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endsection
