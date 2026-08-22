{{-- resources/views/attendance/admin/school-attendance-report.blade.php --}}
@extends('layouts.master')
@section('content')

<style>
:root {
    --sar2-primary: #1e3a5f;
    --sar2-accent:  #2563eb;
    --sar2-success: #16a34a;
    --sar2-warning: #d97706;
    --sar2-danger:  #dc2626;
    --sar2-muted:   #6b7280;
    --sar2-border:  #e2e8f0;
    --sar2-radius:  10px;
    --sar2-shadow:  0 1px 4px rgba(0,0,0,.08);
}

.sar2-hero {
    background: linear-gradient(135deg, var(--sar2-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--sar2-radius);
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.sar2-hero::before {
    content:'';
    position:absolute;
    top:-60px;
    right:-60px;
    width:220px;
    height:220px;
    background:rgba(255,255,255,.06);
    border-radius:50%;
}
.sar2-hero h4 {
    color:#fff;
    font-weight:700;
    margin:0;
    position:relative;
}
.sar2-hero p {
    color:rgba(255,255,255,.75);
    margin:0;
    font-size:13px;
    position:relative;
}

.sar2-stat-card {
    background:#fff;
    border:1px solid var(--sar2-border);
    border-radius:var(--sar2-radius);
    padding:14px 16px;
    text-align:center;
    transition:transform .15s, box-shadow .15s;
}
.sar2-stat-card:hover {
    transform:translateY(-2px);
    box-shadow:var(--sar2-shadow);
}
.sar2-stat-card .stat-value {
    font-size:24px;
    font-weight:700;
}
.sar2-stat-card .stat-label {
    font-size:11px;
    color:var(--sar2-muted);
    margin-top:2px;
}

.sar2-card {
    background:#fff;
    border:1px solid var(--sar2-border);
    border-radius:var(--sar2-radius);
    box-shadow:var(--sar2-shadow);
    overflow:hidden;
}
.sar2-card .card-header {
    background:#fff;
    border-bottom:1px solid var(--sar2-border);
    padding:16px 20px;
    font-weight:700;
    font-size:14px;
    color:var(--sar2-primary);
}

.sar2-table th {
    background:var(--sar2-primary);
    color:#fff;
    padding:12px 16px;
    font-weight:600;
    font-size:13px;
    border:none;
}
.sar2-table td {
    padding:12px 16px;
    vertical-align:middle;
    border-bottom:1px solid var(--sar2-border);
    font-size:13px;
}
.sar2-table tr:hover td {
    background:#eff6ff;
}

.sar2-form-label {
    font-size:13px;
    font-weight:600;
    color:#374151;
    margin-bottom:6px;
}
.sar2-form-control, .sar2-form-select {
    border:1.5px solid var(--sar2-border);
    border-radius:8px;
    font-size:13px;
    padding:9px 14px;
    transition:border .15s;
}
.sar2-form-control:focus, .sar2-form-select:focus {
    border-color:var(--sar2-accent);
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
    outline:none;
}

.sar2-avatar {
    width:32px;
    height:32px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid var(--sar2-border);
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- ══ HERO ═══════════════════════════════════════════════════════════ --}}
            <div class="sar2-hero d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4><i class="ri-bar-chart-2-line me-2"></i>School Attendance Report</h4>
                    <p>Comprehensive attendance overview across all classes</p>
                </div>
            </div>

            {{-- ══ FILTER ════════════════════════════════════════════════════════ --}}
            <div class="sar2-card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('attendance.school-report') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="sar2-form-label">Term</label>
                                <select name="term_id" class="sar2-form-select">
                                    <option value="">All Terms</option>
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>{{ $t->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="sar2-form-label">Session</label>
                                <select name="session_id" class="sar2-form-select">
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

            @if($termId && $sessionId && $summaries->isNotEmpty())

            {{-- ══ STATS ════════════════════════════════════════════════════════ --}}
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
                    <div class="sar2-stat-card">
                        <div class="stat-value text-primary">{{ $summaries->count() }}</div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="sar2-stat-card">
                        <div class="stat-value text-{{ $avgCol }}">{{ $avgPct }}%</div>
                        <div class="stat-label">School Avg</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="sar2-stat-card">
                        <div class="stat-value text-success">{{ $above80 }}</div>
                        <div class="stat-label">Above 80%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="sar2-stat-card">
                        <div class="stat-value text-danger">{{ $below60 }}</div>
                        <div class="stat-label">Below 60%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="sar2-stat-card">
                        <div class="stat-value text-success">{{ $totP }}</div>
                        <div class="stat-label">Total Present</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="sar2-stat-card">
                        <div class="stat-value text-danger">{{ $totA }}</div>
                        <div class="stat-label">Total Absent</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <div class="sar2-stat-card">
                        <div class="stat-value text-warning">{{ $totS }}</div>
                        <div class="stat-label">Sick Leave</div>
                    </div>
                </div>
            </div>

            {{-- ══ TABLE ════════════════════════════════════════════════════════ --}}
            <div class="sar2-card">
                <div class="card-header d-flex align-items-center">
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
                        <table class="table sar2-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Sick</th>
                                    <th class="text-center">Excused</th>
                                    <th class="text-center">Late</th>
                                    <th>Attendance</th>
                                    <th></th>
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
                                        <img src="{{ $img }}" class="sar2-avatar"
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
                                        Details <i class="ri-arrow-right-line ms-1"></i>
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

            @elseif($termId && $sessionId && $summaries->isEmpty())
            <div class="sar2-card">
                <div class="card-body text-center py-5">
                    <i class="ri-inbox-line ri-3x d-block mb-3 text-muted"></i>
                    <h5 class="text-muted">No Attendance Data Found</h5>
                    <p class="text-muted mb-0">No records exist for the selected term and session.</p>
                </div>
            </div>
            @else
            <div class="sar2-card">
                <div class="card-body text-center py-5">
                    <i class="ri-bar-chart-2-line ri-3x d-block mb-3 text-muted"></i>
                    <h5 class="text-muted">Select Term & Session</h5>
                    <p class="text-muted mb-0">Choose a term and session above to generate the school-wide attendance report.</p>
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