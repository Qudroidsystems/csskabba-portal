@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Student Attendance Report</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('attendance.my-classes') }}">Attendance</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('attendance.class-summary', [$classId, $termId, $sessionId]) }}">Class Summary</a></li>
                                <li class="breadcrumb-item active">Student Report</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if($student)
            @php
                $img = $student->picture
                    ? asset('storage/student_avatars/'.basename($student->picture))
                    : asset('storage/student_avatars/unnamed.jpg');
            @endphp
            <div class="row g-3 mb-3">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-0" style="border-left:4px solid #2563eb !important;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $img }}" class="rounded-circle"
                                     style="width:60px;height:60px;object-fit:cover;border:3px solid #bfdbfe;"
                                     onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $student->lname }} {{ $student->fname }} {{ $student->mname }}</h5>
                                    <div class="text-muted mb-2" style="font-size:13px;">Adm: {{ $student->admissionno }}</div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-success-subtle text-success">{{ $class?->schoolclass }} {{ $class?->arms?->arm }}</span>
                                        <span class="badge bg-primary-subtle text-primary">{{ $term?->term }}</span>
                                        <span class="badge bg-info-subtle text-info">{{ $session?->session }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-printer-line me-1"></i>Print
                    </button>
                    <a href="{{ route('attendance.class-summary', [$classId, $termId, $sessionId]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i>Back to Summary
                    </a>
                </div>
            </div>
            @endif

            @php
                $pct      = $summary ? (float) $summary->attendance_percentage : 0;
                $pctColor = $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                $totalDays= $setting?->totalSchoolDays() ?? ($summary?->total_school_days ?? 0);
            @endphp

            <div class="row g-2 mb-3">
                @foreach([
                    ['School Days', $totalDays, 'secondary'],
                    ['Present', $summary?->days_present ?? 0, 'success'],
                    ['Absent', $summary?->days_absent ?? 0, 'danger'],
                    ['Sick Leave', $summary?->days_sick_leave ?? 0, 'warning'],
                    ['Excused', $summary?->days_excused ?? 0, 'info'],
                    ['Late', $summary?->days_late ?? 0, 'secondary'],
                ] as [$label, $val, $color])
                <div class="col-4 col-md-2">
                    <div class="card border text-center mb-0 py-2">
                        <div class="fw-bold fs-4 text-{{ $color }}">{{ $val }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $label }}</div>
                    </div>
                </div>
                @endforeach
                <div class="col-4 col-md-2">
                    <div class="card border text-center mb-0 py-2">
                        <div class="fw-bold fs-4 text-{{ $pctColor }}">{{ $pct }}%</div>
                        <div class="text-muted" style="font-size:11px;">Attendance</div>
                    </div>
                </div>
            </div>

            @if($summary && $totalDays > 0)
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="ri-bar-chart-2-line me-2 text-primary"></i>Breakdown</h6>
                    @php
                        $prP = round(($summary->days_present    / $totalDays) * 100, 1);
                        $abP = round(($summary->days_absent     / $totalDays) * 100, 1);
                        $skP = round(($summary->days_sick_leave / $totalDays) * 100, 1);
                        $exP = round(($summary->days_excused    / $totalDays) * 100, 1);
                        $ltP = round(($summary->days_late       / $totalDays) * 100, 1);
                    @endphp
                    <div class="progress mb-3" style="height:12px;border-radius:99px;">
                        <div class="progress-bar bg-success" style="width:{{ $prP }}%" title="Present"></div>
                        <div class="progress-bar" style="width:{{ $ltP }}%;background:#fb923c;" title="Late"></div>
                        <div class="progress-bar bg-warning" style="width:{{ $skP }}%" title="Sick"></div>
                        <div class="progress-bar bg-info" style="width:{{ $exP }}%" title="Excused"></div>
                        <div class="progress-bar bg-danger" style="width:{{ $abP }}%" title="Absent"></div>
                    </div>
                    <div class="d-flex gap-3 flex-wrap" style="font-size:12px;">
                        <span><span class="badge bg-success me-1">&nbsp;</span>Present ({{ $prP }}%)</span>
                        <span><span class="badge me-1" style="background:#fb923c;">&nbsp;</span>Late ({{ $ltP }}%)</span>
                        <span><span class="badge bg-warning me-1">&nbsp;</span>Sick ({{ $skP }}%)</span>
                        <span><span class="badge bg-info me-1">&nbsp;</span>Excused ({{ $exP }}%)</span>
                        <span><span class="badge bg-danger me-1">&nbsp;</span>Absent ({{ $abP }}%)</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center py-3">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-calendar-line me-2 text-primary"></i>Daily Attendance Log
                    </h5>
                    <span class="badge bg-dark-subtle text-dark">{{ $records->count() }} records</span>
                </div>
                <div class="card-body p-0">
                    @if($records->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead style="background:#1e3a5f;">
                                <tr>
                                    <th class="text-white">#</th>
                                    <th class="text-white">Date</th>
                                    <th class="text-white">Day</th>
                                    <th class="text-white">Period</th>
                                    <th class="text-white">Status</th>
                                    <th class="text-white">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($records as $i => $rec)
                            @php
                                $sc = ['present'=>'success','absent'=>'danger','sick_leave'=>'warning','excused'=>'info','late'=>'secondary'];
                                $si = ['present'=>'ri-check-line','absent'=>'ri-close-line','sick_leave'=>'ri-heart-pulse-line','excused'=>'ri-file-text-line','late'=>'ri-time-line'];
                                $c  = $sc[$rec->status] ?? 'secondary';
                                $ic = $si[$rec->status] ?? 'ri-question-line';
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $i+1 }}</td>
                                <td><strong>{{ $rec->attendance_date->format('d M Y') }}</strong></td>
                                <td class="text-muted">{{ $rec->attendance_date->format('l') }}</td>
                                <td>
                                    <span class="badge {{ $rec->period === 'morning' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info' }}">
                                        {{ ucfirst($rec->period) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $c }}-subtle text-{{ $c }} fw-semibold">
                                        <i class="{{ $ic }} me-1"></i>
                                        {{ \App\Models\StudentAttendance::statusLabel($rec->status) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:12px;">{{ $rec->notes ?? '—' }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="ri-calendar-line ri-2x d-block mb-2 text-muted"></i>
                        <p class="text-muted mb-0">No attendance records found for this student.</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
