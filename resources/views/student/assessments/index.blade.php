@extends('layouts.master')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <style>
            :root {
                --navy: #0f1c35;
                --navy-mid: #1a2f55;
                --gold: #c9a84c;
                --cream: #f9f7f2;
                --paper: #ffffff;
                --border: #e3e7f0;
                --radius: 12px;
                --radius-sm: 8px;
            }

            .assessment-portal {
                font-family: 'Segoe UI', Roboto, sans-serif;
                background: var(--cream);
                border-radius: var(--radius);
                overflow: hidden;
            }

            .ap-hero {
                background: var(--navy);
                padding: 36px 32px 28px;
                position: relative;
            }

            .ap-hero-title {
                font-size: 28px;
                font-weight: 700;
                color: #fff;
                margin: 0;
            }

            .ap-hero-sub {
                color: rgba(255,255,255,.55);
                font-size: 13.5px;
                margin-top: 5px;
            }

            .ap-filter-bar {
                background: var(--paper);
                padding: 16px 32px;
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
                border-bottom: 1px solid var(--border);
            }

            .ap-filter-select {
                padding: 8px 12px;
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                min-width: 160px;
            }

            .ap-filter-btn, .ap-print-btn {
                padding: 8px 22px;
                border-radius: var(--radius-sm);
                cursor: pointer;
                border: none;
            }

            .ap-filter-btn {
                background: var(--gold);
                color: var(--navy);
                font-weight: 600;
            }

            .ap-print-btn {
                background: var(--navy-mid);
                color: #fff;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 7px;
            }

            .ap-body {
                padding: 32px 24px;
            }

            .ap-identity-card {
                background: var(--paper);
                border-radius: var(--radius);
                padding: 24px 28px;
                display: flex;
                align-items: center;
                gap: 24px;
                margin-bottom: 24px;
                border: 1px solid var(--border);
            }

            .ap-avatar {
                width: 64px; height: 64px;
                border-radius: 50%;
                background: var(--navy);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--gold);
                font-size: 24px;
                font-weight: bold;
            }

            .ap-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

            .ap-identity-name {
                font-size: 19px;
                font-weight: 700;
                color: var(--navy);
                margin: 0;
            }

            .ap-stats-strip {
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 14px;
                margin-bottom: 24px;
            }

            @media (max-width: 900px) { .ap-stats-strip { grid-template-columns: repeat(3, 1fr); } }

            .ap-stat-card {
                background: var(--paper);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 18px 16px;
                text-align: center;
            }

            .ap-stat-value {
                font-size: 26px;
                font-weight: 700;
                color: var(--navy);
            }

            .ap-stat-label {
                font-size: 10.5px;
                text-transform: uppercase;
                color: #7b85a3;
            }

            .ap-accordion { display: flex; flex-direction: column; gap: 12px; }

            .ap-accordion-item {
                background: var(--paper);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                overflow: hidden;
            }

            .ap-accordion-trigger {
                width: 100%;
                display: flex;
                justify-content: space-between;
                padding: 18px 22px;
                background: none;
                border: none;
                cursor: pointer;
            }

            .ap-subject-name {
                font-size: 15px;
                font-weight: 700;
                color: var(--navy);
            }

            .ap-grade-pill {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 700;
            }

            .ap-panel {
                display: none;
                border-top: 1px solid var(--border);
                padding: 22px;
                background: #fdf6e3;
            }

            .ap-accordion-item.is-open .ap-panel { display: block; }

            .grade-A1, .grade-A { background: #d4edda; color: #0e6b46; }
            .grade-B2, .grade-B { background: #cce5ff; color: #1565c0; }
            .grade-C4, .grade-C { background: #fff3cd; color: #8a6000; }
            .grade-D7, .grade-D { background: #ffe5cc; color: #7a4200; }
            .grade-F9, .grade-F { background: #f8d7da; color: #c0392b; }

            .ap-empty {
                text-align: center;
                padding: 60px;
                background: var(--paper);
                border-radius: var(--radius);
            }
            </style>

            <div class="assessment-portal">
                <div class="ap-hero">
                    <h1 class="ap-hero-title">My Assessment Report</h1>
                    <p class="ap-hero-sub">View your subject scores and assessment breakdowns</p>
                    @if(isset($term) && isset($session))
                        <span style="color: var(--gold); font-size: 12px;">{{ $term->term ?? '' }} · {{ $session->session ?? '' }}</span>
                    @endif
                </div>

                <form method="GET" action="{{ route('assessments') }}">
                    <div class="ap-filter-bar">
                        <select name="term_id" class="ap-filter-select">
                            <option value="">All Terms</option>
                            @foreach($terms as $t)
                                <option value="{{ $t->id }}" {{ $userSelectedTermId == $t->id ? 'selected' : '' }}>{{ $t->term }}</option>
                            @endforeach
                        </select>

                        <select name="session_id" class="ap-filter-select">
                            <option value="">All Sessions</option>
                            @foreach($sessions as $s)
                                <option value="{{ $s->id }}" {{ $selectedSessionId == $s->id ? 'selected' : '' }}>{{ $s->session }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="ap-filter-btn">Apply Filter</button>

                        @if(isset($subjectsWithAssessments) && $subjectsWithAssessments->isNotEmpty())
                        <button type="button" class="ap-print-btn" id="showPrintModalBtn" style="margin-left: auto;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 6 2 18 2 18 9"/>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                <rect x="6" y="14" width="12" height="8"/>
                            </svg>
                            Print / Save PDF
                        </button>
                        @endif
                    </div>
                </form>

                <div class="ap-body">
                    @if(session('error'))
                        <div class="alert alert-warning">{{ session('error') }}</div>
                    @endif

                    @if(!isset($subjectsWithAssessments) || $subjectsWithAssessments->isEmpty())
                        <div class="ap-empty">
                            <h3>No Assessments Found</h3>
                            <p>No assessments available for the selected term and session.</p>
                        </div>
                    @else

                    <div class="ap-identity-card">
                        <div class="ap-avatar">
                            @if(!empty($studentPicture))
                                <img src="{{ asset('storage/student_avatars/' . $studentPicture) }}" alt="Student">
                            @else
                                {{ strtoupper(substr($student->firstname, 0, 1)) }}{{ strtoupper(substr($student->lastname, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <p class="ap-identity-name">{{ $student->firstname }} {{ $student->lastname }}</p>
                            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <span>Adm No: {{ $student->admissionNo }}</span>
                                @isset($class)<span>Class: {{ $class->schoolclass }}</span>@endisset
                                @isset($term)<span>Term: {{ $term->term }}</span>@endisset
                                @isset($session)<span>Session: {{ $session->session }}</span>@endisset
                            </div>
                        </div>
                    </div>

                    <div class="ap-stats-strip">
                        <div class="ap-stat-card"><div class="ap-stat-value">{{ $overallProgress['total_subjects'] }}</div><div class="ap-stat-label">Subjects</div></div>
                        <div class="ap-stat-card"><div class="ap-stat-value">{{ number_format($overallProgress['average_cum'], 1) }}</div><div class="ap-stat-label">Avg Score</div></div>
                        <div class="ap-stat-card"><div class="ap-stat-value">{{ number_format($overallProgress['gpa'], 2) }}</div><div class="ap-stat-label">GPA</div></div>
                        <div class="ap-stat-card"><div class="ap-stat-value">{{ number_format($overallProgress['cgpa'], 2) }}</div><div class="ap-stat-label">CGPA</div></div>
                        <div class="ap-stat-card"><div class="ap-stat-value"><span class="ap-grade-pill grade-A1">{{ $overallProgress['gpa_grade'] ?? '-' }}</span></div><div class="ap-stat-label">Grade</div></div>
                        <div class="ap-stat-card"><div class="ap-stat-value">{{ number_format($overallProgress['total_grade_points'], 1) }}</div><div class="ap-stat-label">Total GP</div></div>
                    </div>

                    <div class="ap-accordion" id="apAccordion">
                        @foreach($subjectsWithAssessments as $idx => $subject)
                        @php
                            $grade = $subject['grade'] ?? '-';
                            $gradeClass = match(true) {
                                str_starts_with($grade, 'A') => 'grade-A1',
                                str_starts_with($grade, 'B') => 'grade-B2',
                                str_starts_with($grade, 'C') => 'grade-C4',
                                str_starts_with($grade, 'D') => 'grade-D7',
                                default => 'grade-F9',
                            };
                        @endphp
                        <div class="ap-accordion-item {{ $idx === 0 ? 'is-open' : '' }}" id="item-{{ $idx }}">
                            <button class="ap-accordion-trigger" onclick="toggleItem({{ $idx }})">
                                <div>
                                    <p class="ap-subject-name">{{ $subject['subject_name'] }}</p>
                                    <small>{{ $subject['subject_code'] ?? '' }}</small>
                                </div>
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <span class="ap-grade-pill {{ $gradeClass }}">{{ $grade }}</span>
                                    <span style="font-weight: 500;">{{ number_format($subject['cum'], 1) }}</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>
                            </button>
                            <div class="ap-panel">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
                                    <div style="background: white; padding: 10px; border-radius: 8px;"><strong>Total:</strong> {{ number_format($subject['total'], 1) }}</div>
                                    <div style="background: white; padding: 10px; border-radius: 8px;"><strong>Cumulative:</strong> {{ number_format($subject['cum'], 1) }}</div>
                                    <div style="background: white; padding: 10px; border-radius: 8px;"><strong>Grade:</strong> {{ $grade }}</div>
                                    <div style="background: white; padding: 10px; border-radius: 8px;"><strong>Position:</strong> {{ $subject['position'] }}</div>
                                </div>
                                @if(isset($subject['assessments']) && $subject['assessments']->isNotEmpty())
                                <h4 style="font-size: 12px; margin-bottom: 10px;">Assessment Breakdown</h4>
                                @foreach($subject['assessments'] as $assessment)
                                <div style="background: white; padding: 10px; border-radius: 8px; margin-bottom: 8px;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>{{ $assessment['name'] }}</span>
                                        <span>{{ number_format($assessment['score'], 1) }} / {{ $assessment['max_score'] }} ({{ $assessment['percentage'] }}%)</span>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Print Column Selection Modal -->
            <div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Columns for PDF Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">Select the columns you want to include in your PDF report.</div>

                            <div class="card mb-3">
                                <div class="card-header">Student Information</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox" value="picture" checked> Student Picture</label></div>
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox" value="admission_no" checked> Admission Number</label></div>
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox" value="gender"> Gender</label></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between">
                                    <span>Assessments</span>
                                    <label><input type="checkbox" id="selectAllAssessments"> Select All</label>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="assessmentsCheckboxes">
                                        @foreach($allAssessments ?? [] as $assessment)
                                        <div class="col-md-4">
                                            <label><input type="checkbox" class="col-checkbox assessment-cb" value="{{ $assessment->id }}" checked> {{ $assessment->name }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header">Scores & Metrics</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3"><label><input type="checkbox" class="col-checkbox" value="total" checked> Total</label></div>
                                        <div class="col-md-3"><label><input type="checkbox" class="col-checkbox" value="cum" checked> Cumulative</label></div>
                                        <div class="col-md-3"><label><input type="checkbox" class="col-checkbox" value="grade" checked> Grade</label></div>
                                        <div class="col-md-3"><label><input type="checkbox" class="col-checkbox" value="subject_gpa"> Subject GPA</label></div>
                                        <div class="col-md-3"><label><input type="checkbox" class="col-checkbox" value="position" checked> Position</label></div>
                                        <div class="col-md-3"><label><input type="checkbox" class="col-checkbox" value="remark"> Remark</label></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between">
                                    <span>Summary Sections</span>
                                    <label><input type="checkbox" id="selectAllSummaries"> Select All</label>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox summary-cb" value="totals_summary" checked> Totals Summary</label></div>
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox summary-cb" value="gpa_summary" checked> GPA Summary</label></div>
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox summary-cb" value="cgpa_summary" checked> CGPA Summary</label></div>
                                        <div class="col-md-4"><label><input type="checkbox" class="col-checkbox" value="remarks" checked> Remarks Section</label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="generatePdfBtn">Generate PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleItem(idx) {
        document.getElementById('item-' + idx).classList.toggle('is-open');
    }

    document.getElementById('showPrintModalBtn')?.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('printModal')).show();
    });

    document.getElementById('selectAllAssessments')?.addEventListener('change', function() {
        document.querySelectorAll('.assessment-cb').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllSummaries')?.addEventListener('change', function() {
        document.querySelectorAll('.summary-cb').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('generatePdfBtn')?.addEventListener('click', function() {
        const selectedColumns = [];
        document.querySelectorAll('.col-checkbox:checked').forEach(cb => selectedColumns.push(cb.value));

        if (selectedColumns.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Columns Selected', text: 'Please select at least one column.' });
            return;
        }

        const termId = document.querySelector('select[name="term_id"]').value;
        const sessionId = document.querySelector('select[name="session_id"]').value;

        if (!termId || !sessionId) {
            Swal.fire({ icon: 'warning', title: 'Missing Selection', text: 'Please select both Term and Session.' });
            return;
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
        modal.hide();

        Swal.fire({ title: 'Generating PDF', html: 'Please wait...', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const params = new URLSearchParams();
        params.append('session_id', sessionId);
        params.append('term_id', termId);
        selectedColumns.forEach(col => params.append('selected_columns[]', col));

        window.open("{{ route('assessments.print') }}?" + params.toString(), '_blank');
        setTimeout(() => Swal.close(), 1500);
    });
</script>

@endsection
