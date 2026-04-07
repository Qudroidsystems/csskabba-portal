@extends('layouts.master')

@section('content')
<style>
/* ── Scoresheet Design System ────────────────────────────────────── */
:root {
    --ss-primary:   #1e3a5f;
    --ss-accent:    #2563eb;
    --ss-success:   #16a34a;
    --ss-warning:   #d97706;
    --ss-danger:    #dc2626;
    --ss-muted:     #6b7280;
    --ss-border:    #e2e8f0;
    --ss-bg:        #f8fafc;
    --ss-card:      #ffffff;
    --ss-radius:    10px;
    --ss-shadow:    0 1px 4px rgba(0,0,0,.08);
}

/* ── Score input ─────────────────────────────────────────────────── */
.score-input {
    width: 72px; min-width: 72px;
    height: 36px; padding: 4px 6px;
    border: 1.5px solid var(--ss-border); border-radius: 6px;
    font-size: 13px; text-align: center;
    background: #fff; transition: border-color .15s, box-shadow .15s;
}
.score-input:focus { outline: none; border-color: var(--ss-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.score-input.is-invalid { border-color: var(--ss-danger) !important; background: #fef2f2; }
.score-input.is-saved  { border-color: var(--ss-success); background: #f0fdf4; }

/* ── Table ───────────────────────────────────────────────────────── */
#scoresheetTable { font-size: 12.5px; }
#scoresheetTable thead tr { background: var(--ss-primary); color: #fff; }
#scoresheetTable thead th { padding: 10px 8px; font-weight: 600; white-space: nowrap; border: none; }
#scoresheetTable tbody tr { transition: background .12s; }
#scoresheetTable tbody tr:hover { background: #eff6ff !important; }
#scoresheetTable tbody td { padding: 6px 8px; vertical-align: middle; border-bottom: 1px solid var(--ss-border); }

/* ── Vetted row colours ──────────────────────────────────────────── */
.row-vetted     { background: #f0fdf4 !important; }
.row-not-vetted { background: #fef2f2 !important; }
.row-pending    { background: #fffbeb !important; }

/* ── Stats / analysis cards ─────────────────────────────────────── */
.stat-card {
    background: var(--ss-card); border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius); padding: 14px 18px;
    box-shadow: var(--ss-shadow); transition: transform .15s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value { font-size: 22px; font-weight: 700; color: var(--ss-primary); }
.stat-card .stat-label { font-size: 11px; color: var(--ss-muted); margin-top: 2px; }
.stat-card .stat-icon  { font-size: 28px; opacity: .15; float: right; margin-top: -6px; }

/* ── Grade badge strip ───────────────────────────────────────────── */
.grade-strip { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.grade-pill  { flex: 1; min-width: 80px; text-align: center; border-radius: 8px;
               padding: 8px 6px; font-weight: 700; font-size: 13px; }

/* ── Assessment info button ─────────────────────────────────────── */
.assessment-btn { font-size: 12px; }

/* ── Progress bar (mini) ─────────────────────────────────────────── */
.pass-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; margin-top: 6px; }
.pass-bar-fill { height: 100%; border-radius: 4px; transition: width .4s; }

/* ── Column visibility modal ─────────────────────────────────────── */
.col-group { border: 1px solid var(--ss-border); border-radius: 8px; padding: 10px 14px; margin-bottom: 10px; }
.col-group h6 { color: var(--ss-primary); font-weight: 600; margin-bottom: 8px; }

/* ── Mobile ─────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .score-input { width: 64px; min-width: 64px; height: 42px; font-size: 1rem; }
    .stat-card   { padding: 10px 12px; }
    .stat-card .stat-value { font-size: 18px; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong>
            <ul class="mb-0 mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @foreach(['success','status','warning','error'] as $bag)
        @if(session($bag))
            <div class="alert alert-{{ $bag === 'status' ? 'success' : ($bag === 'error' ? 'danger' : $bag) }} alert-dismissible fade show">
                {{ session($bag) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- INFO + ASSESSMENTS HEADER                                      --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if ($broadsheets->isNotEmpty())

    {{-- Subject info strip --}}
    <div class="row g-3 mb-3">
        @php
            $first  = $broadsheets->first();
            $total  = $broadsheets->count();
            $passed = $broadsheets->filter(fn($b) => ($b->cum ?? 0) >= 40)->count();
            $failed = $total - $passed;
            $avg    = $total > 0 ? round($broadsheets->avg('cum'), 1) : 0;
            $highest= $total > 0 ? round($broadsheets->max('cum'), 1) : 0;
            $lowest = $total > 0 ? round($broadsheets->min('cum'), 1) : 0;
            $passRate= $total > 0 ? round($passed / $total * 100) : 0;

            // Grade distribution
            $gradeDist = $broadsheets->groupBy('grade')->map->count();
            $gradeColors = ['A'=>'#16a34a','A1'=>'#16a34a','B'=>'#2563eb','B2'=>'#2563eb','B3'=>'#3b82f6',
                            'C'=>'#7c3aed','C4'=>'#7c3aed','C5'=>'#8b5cf6','C6'=>'#a78bfa',
                            'D'=>'#d97706','D7'=>'#d97706','E8'=>'#f59e0b',
                            'F'=>'#dc2626','F9'=>'#dc2626'];
        @endphp

        {{-- Subject / Class card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid var(--ss-primary) !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 bg-primary rounded-3 p-2" style="background:var(--ss-primary) !important;">
                            <i class="ri-book-2-line text-white fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold" style="color:var(--ss-primary);">{{ $first->subject }} <small class="text-muted fw-normal">({{ $first->subject_code }})</small></h5>
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">
                                    <i class="ri-school-line me-1"></i>{{ $first->schoolclass }} {{ $first->arm }}
                                </span>
                                <span class="badge bg-info-subtle text-info fs-6 px-3 py-2">
                                    <i class="ri-calendar-line me-1"></i>{{ $first->term }} | {{ $first->session }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="col-lg-6">
            <div class="row g-2 h-100">
                <div class="col-4"><div class="stat-card text-center h-100">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value text-primary">{{ $total }}</div>
                    <div class="stat-label">Total Students</div>
                </div></div>
                <div class="col-4"><div class="stat-card text-center h-100">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value" style="color:var(--ss-warning);">{{ $avg }}</div>
                    <div class="stat-label">Class Average</div>
                </div></div>
                <div class="col-4"><div class="stat-card text-center h-100">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value" style="color:var(--ss-success);">{{ $passRate }}%</div>
                    <div class="stat-label">Pass Rate</div>
                    <div class="pass-bar"><div class="pass-bar-fill" style="width:{{ $passRate }}%;background:var(--ss-success);"></div></div>
                </div></div>
            </div>
        </div>
    </div>

    {{-- Result analysis row --}}
    <div class="row g-3 mb-3">
        {{-- Pass/Fail + High/Low --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0" style="color:var(--ss-primary)"><i class="ri-bar-chart-2-line me-1"></i>Score Summary</h6>
                </div>
                <div class="card-body pt-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background:#f0fdf4;">
                                <div class="fw-bold fs-5" style="color:var(--ss-success);">{{ $passed }}</div>
                                <div class="text-muted" style="font-size:11px;">Passed</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background:#fef2f2;">
                                <div class="fw-bold fs-5" style="color:var(--ss-danger);">{{ $failed }}</div>
                                <div class="text-muted" style="font-size:11px;">Failed</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background:#eff6ff;">
                                <div class="fw-bold fs-5" style="color:var(--ss-accent);">{{ $highest }}</div>
                                <div class="text-muted" style="font-size:11px;">Highest</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background:#fffbeb;">
                                <div class="fw-bold fs-5" style="color:var(--ss-warning);">{{ $lowest }}</div>
                                <div class="text-muted" style="font-size:11px;">Lowest</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grade distribution --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0" style="color:var(--ss-primary)"><i class="ri-pie-chart-line me-1"></i>Grade Distribution</h6>
                </div>
                <div class="card-body pt-2">
                    @if($gradeDist->isEmpty())
                        <p class="text-muted small text-center mt-3">No grades yet.</p>
                    @else
                        <div class="grade-strip">
                            @foreach($gradeDist->sortKeysDesc() as $grade => $count)
                                @php $pct = $total > 0 ? round($count / $total * 100) : 0; $col = $gradeColors[$grade] ?? '#6b7280'; @endphp
                                <div class="grade-pill" style="background:{{ $col }}18; color:{{ $col }}; border:1px solid {{ $col }}40;">
                                    <div style="font-size:16px;">{{ $grade }}</div>
                                    <div style="font-size:11px;font-weight:600;">{{ $count }} <span style="opacity:.7;">({{ $pct }}%)</span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Assessments navigation --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0" style="color:var(--ss-primary)"><i class="ri-clipboard-line me-1"></i>Assessments</h6>
                </div>
                <div class="card-body pt-2">
                    @if($assessments->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($assessments as $assessment)
                                <a href="{{ route('assessment.scoresheet', [
                                    'schoolclassid' => session('schoolclass_id'),
                                    'subjectclassid'=> session('subjectclass_id'),
                                    'staffid'       => session('staff_id'),
                                    'termid'        => session('term_id'),
                                    'sessionid'     => session('session_id'),
                                    'assessmentid'  => $assessment->id
                                ]) }}" class="d-flex align-items-center justify-content-between p-2 rounded-3 assessment-btn text-decoration-none"
                                   style="background:#eff6ff;border:1px solid #bfdbfe;color:var(--ss-accent);">
                                    <span><i class="ri-edit-line me-1"></i>{{ $assessment->name }}</span>
                                    <span class="badge" style="background:var(--ss-accent);">{{ $assessment->max_score }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center mt-3"><i class="ri-information-line me-1"></i>No assessments defined.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @endif {{-- broadsheets not empty --}}

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MAIN SCORESHEET CARD                                           --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
    <div class="col-12">
    <div class="card border-0 shadow-sm">

        {{-- Card header --}}
        <div class="card-header d-flex align-items-center flex-wrap gap-2 py-3" style="background:var(--ss-primary);">
            <div class="flex-grow-1">
                <h5 class="mb-0 text-white fw-semibold">
                    <i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }}
                    @if ($broadsheets->isNotEmpty())
                        <span class="badge bg-white text-primary ms-2" id="scoreCount">{{ $broadsheets->count() }}</span>
                    @endif
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Search --}}
                <div class="input-group input-group-sm" style="width:240px;">
                    <span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span>
                    <input type="text" class="form-control border-0 ps-1" id="searchInput" placeholder="Search admission / name…" {{ $broadsheets->isEmpty() ? 'disabled' : '' }}>
                    <button class="btn btn-light border-0" id="clearSearch"><i class="ri-close-line"></i></button>
                </div>
                @if ($broadsheets->isNotEmpty())
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal">
                        <i class="ri-eye-line me-1"></i>Columns
                    </button>
                    {{-- These IDs are required by subjectscoresheet.init.js --}}
                    <button type="button" class="btn btn-sm btn-warning" id="downloadMarksSheet">
                        <i class="ri-file-pdf-line me-1"></i>Marks Sheet
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="downloadExcel">
                        <i class="ri-download-line me-1"></i>Export Excel
                    </button>
                    <button class="btn btn-sm btn-info" id="importBtn" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="ri-upload-line me-1"></i>Import
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body p-0">

            {{-- No data alert --}}
            <div class="alert alert-info text-center m-3 mb-0" id="noDataAlert" style="display:{{ $broadsheets->isEmpty() ? 'block' : 'none' }};">
                <i class="ri-information-line me-2"></i>No scores available. Import scores or register students for this subject.
            </div>

            {{-- Download progress (required by subjectscoresheet.init.js) --}}
            <div id="downloadProgressContainer" style="display:none;" class="px-3 pt-3">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#fefce8;">
                    <div class="spinner-border spinner-border-sm text-warning"></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1" style="font-size:13px;">Downloading…</div>
                        <div class="progress" style="height:5px;"><div class="progress-bar progress-bar-animated bg-warning" id="downloadProgressBar" style="width:0%"></div></div>
                    </div>
                </div>
            </div>

            {{-- Save progress bar --}}
            <div id="progressContainer" style="display:none;" class="px-3 pt-3">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#eff6ff;">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1" style="font-size:13px;">Saving scores…</div>
                        <div class="progress" style="height:5px;"><div class="progress-bar progress-bar-animated bg-primary" id="saveProgressBar" style="width:0%"></div></div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0" id="scoresheetTable">
                <thead>
                    <tr>
                        <th class="col-checkbox" style="width:44px;">
                            <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="checkAll"></div>
                        </th>
                        <th class="col-sn">SN</th>
                        <th class="col-admissionno">Adm. No</th>
                        <th class="col-name">Student Name</th>
                        @forelse ($assessments as $assessment)
                            <th class="col-assessment-{{ $assessment->id }} text-center">
                                {{ $assessment->name }}<br>
                                <small class="fw-normal opacity-75">({{ $assessment->max_score }})</small>
                            </th>
                        @empty
                            <th colspan="4" class="col-no-assessments text-center text-white opacity-75">No Assessments Defined</th>
                        @endforelse
                        <th class="col-total text-center">Total</th>
                        <th class="col-bf text-center">BF</th>
                        <th class="col-cum text-center">Cum</th>
                        <th class="col-gpa text-center">GPA</th>
                        <th class="col-cgpa text-center">CGPA</th>
                        <th class="col-grade text-center">Grade</th>
                        <th class="col-position text-center">Pos</th>
                        <th class="col-vetted text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="scoresheetTableBody">
                    @php $i = 0; @endphp
                    @forelse ($broadsheets as $broadsheet)
                        @php
                            $rowTotal = 0;
                            foreach ($assessments as $a) {
                                $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                                $rowTotal += $so ? $so->score : 0;
                            }
                            $vClass = match(true) {
                                $broadsheet->vettedstatus === '1' => 'row-vetted',
                                $broadsheet->vettedstatus === '0' => 'row-not-vetted',
                                default => 'row-pending'
                            };
                        @endphp
                        <tr class="{{ $vClass }}" data-id="{{ $broadsheet->id }}">
                            <td class="col-checkbox">
                                <div class="form-check mb-0">
                                    <input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}">
                                </div>
                            </td>
                            <td class="col-sn sn fw-medium">{{ ++$i }}</td>
                            <td class="col-admissionno admissionno" data-admissionno="{{ $broadsheet->admissionno }}">
                                <span class="text-muted small">{{ $broadsheet->admissionno ?? '-' }}</span>
                            </td>
                            <td class="col-name name" data-name="{{ strtolower(($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '')) }}">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $broadsheet->picture ? asset('storage/student_avatars/' . basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                         class="rounded-circle" style="width:34px;height:34px;object-fit:cover;border:2px solid var(--ss-border);"
                                         data-bs-toggle="modal" data-bs-target="#imageViewModal"
                                         data-image="{{ $broadsheet->picture ? asset('storage/student_avatars/' . basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';"
                                         style="cursor:pointer;">
                                    <div>
                                        <span class="fw-semibold d-block" style="font-size:12.5px;">{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}</span>
                                        @if($broadsheet->mname)<span class="text-muted small">{{ $broadsheet->mname }}</span>@endif
                                    </div>
                                </div>
                            </td>

                            {{-- Assessment score inputs --}}
                            @forelse ($assessments as $assessment)
                                @php
                                    $scoreObj   = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                    $scoreValue = $scoreObj ? $scoreObj->score : 0;
                                @endphp
                                <td class="col-assessment-{{ $assessment->id }} assessment-col text-center">
                                    <input type="number"
                                           class="score-input"
                                           data-field="{{ $assessment->id }}"
                                           data-max="{{ $assessment->max_score }}"
                                           data-id="{{ $broadsheet->id }}"
                                           data-original="{{ $scoreValue }}"
                                           value="{{ $scoreValue }}"
                                           min="0" max="{{ $assessment->max_score }}" step="0.1">
                                </td>
                            @empty
                                <td colspan="4" class="col-no-assessments text-center text-muted">-</td>
                            @endforelse

                            <td class="col-total text-center">
                                <span class="badge bg-primary" data-total="{{ $rowTotal }}">{{ number_format($rowTotal, 1) }}</span>
                            </td>
                            <td class="col-bf text-center">
                                <span class="badge bg-secondary-subtle text-secondary">{{ number_format($broadsheet->bf ?? 0, 1) }}</span>
                            </td>
                            <td class="col-cum text-center">
                                @php $cum = $broadsheet->cum ?? 0; $cumColor = $cum >= 70 ? 'success' : ($cum >= 50 ? 'info' : ($cum >= 40 ? 'warning' : 'danger')); @endphp
                                <span class="badge bg-{{ $cumColor }}-subtle text-{{ $cumColor }} fw-bold" style="font-size:12px;">{{ number_format($cum, 1) }}</span>
                            </td>
                            <td class="col-gpa text-center">
                                <span class="badge bg-warning-subtle text-warning fw-semibold">{{ number_format($broadsheet->gpa ?? 0, 2) }}</span>
                            </td>
                            <td class="col-cgpa text-center">
                                <span class="badge bg-dark-subtle text-dark">{{ number_format($broadsheet->cgpa ?? 0, 2) }}</span>
                            </td>
                            <td class="col-grade text-center fw-bold" style="font-size:13px;color:{{ $gradeColors[$broadsheet->grade ?? 'F'] ?? '#6b7280' }};">
                                {{ $broadsheet->grade ?? '-' }}
                            </td>
                            <td class="col-position text-center">
                                <span class="badge" style="background:var(--ss-primary);">
                                    {{ $broadsheet->position ? $broadsheet->position . \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}
                                </span>
                            </td>
                            <td class="col-vetted text-center">
                                @if($broadsheet->vettedstatus === '1')
                                    <span class="badge bg-success-subtle text-success"><i class="ri-check-line me-1"></i>Vetted</span>
                                @elseif($broadsheet->vettedstatus === '0')
                                    <span class="badge bg-danger-subtle text-danger"><i class="ri-close-line me-1"></i>Not Vetted</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning"><i class="ri-time-line me-1"></i>Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="noDataRow">
                            <td colspan="{{ ($assessments->count() ?: 4) + 13 }}" class="text-center py-4 text-muted">
                                <i class="ri-inbox-line ri-2x d-block mb-2"></i>No scores available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            {{-- Bulk action toolbar --}}
            @if ($broadsheets->isNotEmpty())
            <div class="p-3 border-top" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-primary" id="selectAllScores"><i class="ri-check-double-line me-1"></i>Select All</button>
                        <button class="btn btn-sm btn-outline-secondary" id="clearAllScores"><i class="ri-close-line me-1"></i>Clear</button>
                        <button class="btn btn-sm btn-outline-danger" id="deleteSelectedScoresBtn"><i class="ri-delete-bin-line me-1"></i>Delete Selected</button>
                        <a href="{{ route('myresultroom.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>Back
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small>
                        <button class="btn btn-success btn-sm px-4" id="bulkUpdateScores">
                            <i class="ri-save-line me-1"></i>Save All Scores
                        </button>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- card-body --}}
    </div>
    </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MODALS                                                         --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}

    {{-- Column visibility --}}
    @if ($broadsheets->isNotEmpty())
    <div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="background:var(--ss-primary);">
                    <h5 class="modal-title text-white"><i class="ri-eye-line me-2"></i>Column Visibility</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="col-group">
                                <h6>Student Info</h6>
                                @foreach([['col-checkbox','Select'],['col-sn','SN'],['col-admissionno','Adm. No'],['col-name','Name']] as [$cls,$lbl])
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-{{ $cls }}" data-col="{{ $cls }}" checked><label class="form-check-label" for="chk-{{ $cls }}">{{ $lbl }}</label></div>
                                @endforeach
                            </div>
                        </div>
                        @if($assessments->isNotEmpty())
                        <div class="col-md-4">
                            <div class="col-group">
                                <h6>Assessments</h6>
                                @foreach($assessments as $a)
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-col-assessment-{{ $a->id }}" data-col="col-assessment-{{ $a->id }}" checked><label class="form-check-label" for="chk-col-assessment-{{ $a->id }}">{{ $a->name }}</label></div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <div class="col-group">
                                <h6>Scores & Metrics</h6>
                                @foreach([['col-total','Total'],['col-bf','BF'],['col-cum','Cum'],['col-gpa','GPA'],['col-cgpa','CGPA'],['col-grade','Grade'],['col-position','Position'],['col-vetted','Status']] as [$cls,$lbl])
                                <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-{{ $cls }}" data-col="{{ $cls }}" checked><label class="form-check-label" for="chk-{{ $cls }}">{{ $lbl }}</label></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Import modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="background:var(--ss-primary);">
                    <h5 class="modal-title text-white"><i class="ri-upload-line me-2"></i>Import Scores</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info"><i class="ri-information-line me-2"></i>
                        Upload the Excel file exported from this scoresheet. Assessment columns are matched automatically by header name.
                    </div>
                    {{-- importForm, importSubmit, importLoader, uploadProgressBar are all required by subjectscoresheet.init.js --}}
                    <form action="{{ route('subjectscoresheet.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <input type="hidden" name="schoolclass_id" value="{{ session('schoolclass_id') }}">
                        <input type="hidden" name="subjectclass_id" value="{{ session('subjectclass_id') }}">
                        <input type="hidden" name="staff_id" value="{{ session('staff_id') }}">
                        <input type="hidden" name="term_id" value="{{ session('term_id') }}">
                        <input type="hidden" name="session_id" value="{{ session('session_id') }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Excel File (.xlsx)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        {{-- Upload progress (required by init.js) --}}
                        <div id="importLoader" style="display:none;" class="mb-3">
                            <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f0fdf4;">
                                <div class="spinner-border spinner-border-sm text-success"></div>
                                <div class="flex-grow-1">
                                    <div style="font-size:12px;margin-bottom:3px;">Uploading…</div>
                                    <div class="progress" style="height:5px;">
                                        <div class="progress-bar progress-bar-animated bg-success" id="uploadProgressBar" style="width:0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="importSubmit">
                                <i class="ri-upload-line me-1"></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Image view modal --}}
    <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header"><h5 class="modal-title">Student Photo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body text-center p-4">
                    <img id="enlargedImage" src="" alt="Student" class="img-fluid rounded-3" style="max-height:400px;">
                </div>
            </div>
        </div>
    </div>

</div>{{-- container-fluid --}}
</div>{{-- page-content --}}
</div>{{-- main-content --}}

<script>
// ── CSRF meta ─────────────────────────────────────────────────────────────────
if (!document.querySelector('meta[name="csrf-token"]')) {
    const m = document.createElement('meta'); m.name = 'csrf-token';
    m.content = '{{ csrf_token() }}'; document.head.appendChild(m);
}

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ROUTES used by inline JS below
const ROUTES = {
    singleUpdate: '{{ route("subjectscoresheet.single-update") }}',
    bulkUpdate  : '{{ route("subjectscoresheet.bulk-update") }}',
    destroy     : '{{ route("subjectscoresheet.destroy", ["id" => "__ID__"]) }}',
};

// window.routes is used by subjectscoresheet.init.js — keep both in sync
window.routes = {
    singleUpdate     : '{{ route("subjectscoresheet.single-update") }}',
    bulkUpdate       : '{{ route("subjectscoresheet.bulk-update") }}',
    destroy          : '{{ route("subjectscoresheet.destroy", ["id" => "__ID__"]) }}',
    results          : '{{ route("subjectscoresheet.results") }}',
    export           : '{{ route("subjectscoresheet.export") }}',
    import           : '{{ route("subjectscoresheet.import") }}',
    downloadMarksSheet: '{{ route("scoresheet.download-marks-sheet") }}',
    gradePreview     : '{{ route("subjectscoresheet.grade-preview") }}',
};

window.term_id         = {{ session('term_id')         ?? 'null' }};
window.session_id      = {{ session('session_id')      ?? 'null' }};
window.subjectclass_id = {{ session('subjectclass_id') ?? 'null' }};
window.schoolclass_id  = {{ session('schoolclass_id')  ?? 'null' }};
window.staff_id        = {{ session('staff_id')        ?? 'null' }};
window.is_senior       = {{ $is_senior ?? false ? 'true' : 'false' }};
window.broadsheets     = @json($broadsheets ?? []);

document.addEventListener('DOMContentLoaded', function () {

    // ── Tooltips ──────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // ── Image modal ───────────────────────────────────────────────────────────
    document.getElementById('imageViewModal')?.addEventListener('show.bs.modal', function(e) {
        const src = e.relatedTarget?.dataset?.image || e.relatedTarget?.getAttribute('data-image');
        document.getElementById('enlargedImage').src = src || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
    });
    // Also handle click on img tags (not data-bs-toggle targets)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('student-image') || e.target.closest('[data-image]')) {
            const el  = e.target.classList.contains('student-image') ? e.target : e.target.closest('[data-image]');
            const src = el.getAttribute('data-image');
            if (src) document.getElementById('enlargedImage').src = src;
        }
    });

    // ── Column visibility ─────────────────────────────────────────────────────
    document.querySelectorAll('.col-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            const cls = this.dataset.col;
            document.querySelectorAll(`th.${cls}, td.${cls}`).forEach(el => {
                el.style.display = this.checked ? '' : 'none';
            });
        });
    });

    // ── Search ────────────────────────────────────────────────────────────────
    let tableRows = () => document.querySelectorAll('#scoresheetTableBody tr[data-id]');
    const noDataAlert = document.getElementById('noDataAlert');
    const scoreCount  = document.getElementById('scoreCount');

    function applySearch() {
        const q = (document.getElementById('searchInput')?.value ?? '').trim().toLowerCase();
        let vis = 0;
        tableRows().forEach(row => {
            const adm  = (row.querySelector('.admissionno')?.dataset?.admissionno ?? '').toLowerCase();
            const name = (row.querySelector('.name')?.dataset?.name ?? '').toLowerCase();
            const show = !q || adm.includes(q) || name.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        if (scoreCount) scoreCount.textContent = vis;
        if (noDataAlert) noDataAlert.style.display = vis === 0 ? 'block' : 'none';
    }

    document.getElementById('searchInput')?.addEventListener('input', applySearch);
    document.getElementById('clearSearch')?.addEventListener('click', function() {
        if (document.getElementById('searchInput')) document.getElementById('searchInput').value = '';
        applySearch();
    });

    // ── Check-all ─────────────────────────────────────────────────────────────
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = this.checked);
    });
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('score-checkbox')) {
            const all    = document.querySelectorAll('.score-checkbox');
            const checked= document.querySelectorAll('.score-checkbox:checked');
            const ca     = document.getElementById('checkAll');
            if (ca) { ca.checked = checked.length === all.length && all.length > 0; ca.indeterminate = checked.length > 0 && checked.length < all.length; }
        }
    });

    document.getElementById('selectAllScores')?.addEventListener('click', () => {
        document.getElementById('checkAll').checked = true;
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('clearAllScores')?.addEventListener('click', () => {
        document.getElementById('checkAll').checked = false;
        document.querySelectorAll('.score-checkbox').forEach(cb => cb.checked = false);
    });

    // ── Score input helpers ───────────────────────────────────────────────────

    function updateRowTotal(row) {
        let sum = 0;
        row.querySelectorAll('.score-input').forEach(inp => sum += parseFloat(inp.value) || 0);
        const span = row.querySelector('.col-total span');
        if (span) { span.textContent = fmtNum(sum, 1); span.dataset.total = sum; }
    }

    function validateInput(input) {
        const max = parseFloat(input.dataset.max) || 0;
        const val = parseFloat(input.value) || 0;
        if (val > max) { input.classList.add('is-invalid'); return false; }
        input.classList.remove('is-invalid'); return true;
    }

    // ── FIX: highlight existing value on focus ────────────────────────────────
    document.querySelectorAll('.score-input').forEach(input => {
        // Select all text when user clicks/tabs into the field
        input.addEventListener('focus', function() { this.select(); });

        input.addEventListener('input', function() {
            validateInput(this);
            updateRowTotal(this.closest('tr'));
        });

        input.addEventListener('blur', function() {
            validateInput(this);
            updateRowTotal(this.closest('tr'));
            const orig = parseFloat(this.dataset.original) || 0;
            const curr = parseFloat(this.value) || 0;
            if (Math.abs(curr - orig) > 0.001) {
                this.dataset.original = this.value;
                saveIndividualScore(this);
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const row = this.closest('tr');
                if (!document.querySelectorAll('.score-input.is-invalid').length) {
                    saveIndividualScore(this);
                }
                // Move to next score input
                const inputs = Array.from(document.querySelectorAll('.score-input'));
                const idx    = inputs.indexOf(this);
                if (idx < inputs.length - 1) inputs[idx + 1].focus();
            }
        });
    });

    // ── Individual save ───────────────────────────────────────────────────────
    function saveIndividualScore(input) {
        const rowId   = input.dataset.id;
        const fieldId = parseInt(input.dataset.field);
        const score   = parseFloat(input.value) || 0;
        const row     = input.closest('tr');
        const totSpan = row.querySelector('.col-total span');
        const total   = totSpan ? parseFloat(totSpan.dataset.total) || 0 : 0;

        // Visual indicator: saving
        input.classList.add('is-saved');
        setTimeout(() => input.classList.remove('is-saved'), 2000);

        fetch(ROUTES.singleUpdate, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body   : JSON.stringify({
                broadsheet_id: rowId, assessment_id: fieldId, score, is_sub: false,
                raw_total: total, term_id: window.term_id, session_id: window.session_id,
                subjectclass_id: window.subjectclass_id, schoolclass_id: window.schoolclass_id, staff_id: window.staff_id
            })
        })
        .then(r => r.json())   // always parse — don't rely on r.ok before parsing
        .then(data => {
            if (!data.success) {
                // Server returned an error payload — show it but don't crash
                showToast(data.message || 'Could not save score.', 'warning');
                return;
            }
            // Update displayed values
            const d = data.data;
            const update = (cls, val, fmt) => { const el = row.querySelector(`.${cls} span`); if (el && val != null) el.textContent = fmtNum(val, fmt ?? 1); };
            update('col-cum',  d.cum,  1);
            update('col-gpa',  d.gpa,  2);
            update('col-cgpa', d.cgpa, 2);
            update('col-bf',   d.bf,   1);
            if (row.querySelector('.col-grade')) row.querySelector('.col-grade').textContent = d.grade ?? '';
        })
        .catch(err => {
            // Network-level error (offline, timeout) — show minimal toast, don't alert
            console.warn('saveIndividualScore network error:', err.message);
            showToast('Network issue — score may not have saved. Check your connection.', 'danger');
        });
    }

    // ── Bulk save ─────────────────────────────────────────────────────────────
    document.getElementById('bulkUpdateScores')?.addEventListener('click', function() {
        const invalid = document.querySelectorAll('.score-input.is-invalid').length;
        if (invalid) {
            Swal.fire({ icon: 'warning', title: 'Invalid Scores', text: `${invalid} score(s) exceed the maximum. Please correct them.` });
            return;
        }
        document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => updateRowTotal(row));

        const scores = [];
        document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
            const assessments = {};
            row.querySelectorAll('.score-input').forEach(inp => { assessments[inp.dataset.field] = parseFloat(inp.value) || 0; });
            const totSpan = row.querySelector('.col-total span');
            scores.push({ id: row.dataset.id, assessments, raw_total: totSpan ? parseFloat(totSpan.dataset.total) || 0 : 0 });
        });

        if (!scores.length) return;

        const prog = document.getElementById('progressContainer');
        const bar  = document.getElementById('saveProgressBar');
        if (prog) prog.style.display = 'block';
        let w = 0; const iv = setInterval(() => { w = Math.min(w + 8, 90); if (bar) bar.style.width = w + '%'; }, 150);

        fetch(ROUTES.bulkUpdate, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body   : JSON.stringify({ scores, term_id: window.term_id, session_id: window.session_id,
                subjectclass_id: window.subjectclass_id, staff_id: window.staff_id, schoolclass_id: window.schoolclass_id, is_sub: false })
        })
        .then(r => r.json())
        .then(data => {
            clearInterval(iv); if (prog) prog.style.display = 'none';
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Saved!', text: data.message, timer: 2000, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not save scores.' });
            }
        })
        .catch(err => {
            clearInterval(iv); if (prog) prog.style.display = 'none';
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Please check your connection and try again.' });
        });
    });

    // ── Delete selected ───────────────────────────────────────────────────────
    document.getElementById('deleteSelectedScoresBtn')?.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb => cb.dataset.id);
        if (!ids.length) { Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Select rows to delete.' }); return; }

        Swal.fire({ title: 'Delete selected scores?', text: 'This cannot be undone.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete' })
        .then(r => {
            if (!r.isConfirmed) return;
            Promise.all(ids.map(id =>
                fetch(ROUTES.destroy.replace('__ID__', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } }).then(r => r.json())
            )).then(results => {
                let deleted = 0;
                results.forEach((res, i) => { if (res.success) { document.querySelector(`tr[data-id="${ids[i]}"]`)?.remove(); deleted++; } });
                Swal.fire({ icon: 'success', title: `${deleted} deleted`, timer: 1500, showConfirmButton: false });
                if (!document.querySelectorAll('#scoresheetTableBody tr[data-id]').length) location.reload();
            });
        });
    });

    // ── Keyboard shortcut Ctrl+S ──────────────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('bulkUpdateScores')?.click();
        }
    });
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmtNum(n, d) { return parseFloat(n).toFixed(d ?? 1); }

function showToast(msg, type = 'info') {
    // Minimal non-blocking toast (Bootstrap toast)
    const id = 'toast_' + Date.now();
    const colors = { success: '#16a34a', warning: '#d97706', danger: '#dc2626', info: '#2563eb' };
    const html = `<div id="${id}" class="toast align-items-center border-0 text-white show" role="alert"
        style="position:fixed;bottom:20px;right:20px;z-index:9999;background:${colors[type]};min-width:260px;">
        <div class="d-flex p-3"><div class="me-auto">${msg}</div>
        <button type="button" class="btn-close btn-close-white ms-2" onclick="this.closest('.toast').remove()"></button></div></div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    setTimeout(() => document.getElementById(id)?.remove(), 4500);
}

// Include SweetAlert2 if needed
if (typeof Swal === 'undefined') {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    document.head.appendChild(s);
}
</script>
@endsection
