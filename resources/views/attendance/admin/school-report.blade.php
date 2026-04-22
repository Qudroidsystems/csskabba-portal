@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">School Attendance Report</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('attendance.settings') }}">Attendance</a></li>
                                <li class="breadcrumb-item active">School Report</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('attendance.school-report') }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Term</label>
                                        <select name="term_id" class="form-select">
                                            <option value="">All Terms</option>
                                            @foreach($terms as $t)
                                                <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>{{ $t->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Session</label>
                                        <select name="session_id" class="form-select">
                                            <option value="">All Sessions</option>
                                            @foreach($sessions as $s)
                                                <option value="{{ $s->id }}" {{ $sessionId == $s->id ? 'selected' : '' }}>{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="ri-search-line me-1"></i>Generate Report
                                        </button>
                                    </div>
                                    @if($termId && $sessionId)
                                    <div class="col-md-2">
                                        <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100">
                                            <i class="ri-printer-line me-1"></i>Print
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if($termId && $sessionId && $summaries->isNotEmpty())

            {{-- Stats --}}
            @php
                $avgPct  = round($summaries->avg('attendance_percentage'), 1);
                $totP    = $summaries->sum('days_present');
                $totA    = $summaries->sum('days_absent');
                $totS    = $summaries->sum('days_sick_leave');
                $above80 = $summaries->where('attendance_percentage', '>=', 80)->count();
                $below60 = $summaries->where('attendance_percentage', '<',  60)->count();
                $avgCol  = $avgPct >= 80 ? 'success' : ($avgPct >= 60 ? 'warning' : 'danger');
            @endphp
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-primary">{{ $summaries->count() }}</div>
                        <div class="text-muted" style="font-size:11px;">Students</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-{{ $avgCol }}">{{ $avgPct }}%</div>
                        <div class="text-muted" style="font-size:11px;">School Avg</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-success">{{ $above80 }}</div>
                        <div class="text-muted" style="font-size:11px;">Above 80%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-danger">{{ $below60 }}</div>
                        <div class="text-muted" style="font-size:11px;">Below 60%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-success">{{ $totP }}</div>
                        <div class="text-muted" style="font-size:11px;">Total Present</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-danger">{{ $totA }}</div>
                        <div class="text-muted" style="font-size:11px;">Total Absent</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="card border text-center mb-0 py-3">
                        <div class="fw-bold fs-3 text-warning">{{ $totS }}</div>
                        <div class="text-muted" style="font-size:11px;">Sick Leave</div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center py-3">
                            <h5 class="card-title mb-0 flex-grow-1">
                                <i class="ri-group-line me-2 text-primary"></i>All Students
                                <span class="badge bg-dark-subtle text-dark ms-1">{{ $summaries->count() }}</span>
                            </h5>
                            <div class="input-group input-group-sm" style="width:240px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" class="form-control" id="srch" placeholder="Search student or class…">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead style="background:#1e3a5f;">
                                        <tr>
                                            <th class="text-white">#</th>
                                            <th class="text-white">Student</th>
                                            <th class="text-white">Class</th>
                                            <th class="text-white text-center">Present</th>
                                            <th class="text-white text-center">Absent</th>
                                            <th class="text-white text-center">Sick</th>
                                            <th class="text-white text-center">Excused</th>
                                            <th class="text-white text-center">Late</th>
                                            <th class="text-white">Attendance</th>
                                            <th class="text-white"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="rptRows">
                                    @foreach($summaries as $i => $s)
                                    @php
                                        $pct = (float) $s->attendance_percentage;
                                        $col = $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                                        $img = ($s->student && $s->student->picture)
                                            ? asset('storage/student_avatars/'.basename($s->student->picture))
                                            : asset('storage/student_avatars/unnamed.jpg');
                                        $sName = strtolower(($s->student->lname ?? '').' '.($s->student->fname ?? ''));
                                        $cName = strtolower($s->schoolclass?->schoolclass ?? '');
                                    @endphp
                                    <tr data-search="{{ $sName }} {{ $cName }}">
                                        <td class="text-muted fw-medium">{{ $i+1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $img }}" class="rounded-circle"
                                                     style="width:32px;height:32px;object-fit:cover;border:2px solid #e2e8f0;"
                                                     onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                                                <div>
                                                    <div class="fw-semibold" style="font-size:13px;">{{ $s->student?->lname }} {{ $s->student?->fname }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ $s->student?->admissionNo }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">{{ $s->schoolclass?->schoolclass }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success-subtle text-success fw-semibold">{{ $s->days_present }}</span></td>
                                        <td class="text-center"><span class="badge bg-danger-subtle text-danger fw-semibold">{{ $s->days_absent }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning-subtle text-warning fw-semibold">{{ $s->days_sick_leave }}</span></td>
                                        <td class="text-center"><span class="badge bg-info-subtle text-info fw-semibold">{{ $s->days_excused }}</span></td>
                                        <td class="text-center"><span class="badge bg-secondary-subtle text-secondary fw-semibold">{{ $s->days_late }}</span></td>
                                        <td style="min-width:160px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px;">
                                                    <div class="progress-bar bg-{{ $col }}" style="width:{{ $pct }}%"></div>
                                                </div>
                                                <span class="fw-bold text-{{ $col }}" style="font-size:12px;min-width:38px;">{{ $pct }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @can('View attendance-student-report')
                                            <a href="{{ route('attendance.student-report', [$s->student_id, $s->schoolclass_id, $termId, $sessionId]) }}"
                                               class="btn btn-outline-primary btn-sm" style="font-size:11px;">
                                                Details →
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @elseif($termId && $sessionId && $summaries->isEmpty())
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-inbox-line ri-3x d-block mb-3 text-muted"></i>
                            <h5 class="text-muted">No Attendance Data Found</h5>
                            <p class="text-muted mb-0">No records exist for the selected term and session.</p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-bar-chart-2-line ri-3x d-block mb-3 text-muted"></i>
                            <h5 class="text-muted">Select Term & Session</h5>
                            <p class="text-muted mb-0">Choose a term and session above to generate the school-wide attendance report.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
document.getElementById('srch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#rptRows tr').forEach(r => {
        r.style.display = (!q || r.dataset.search.includes(q)) ? '' : 'none';
    });
});
</script>
@endsection
