@extends('layouts.master')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Staff Attendance – {{ $staff->full_name }}</h4>
        <a href="{{ route('staff-attendance.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line me-1"></i>Back to Report
        </a>
    </div>

    <div class="row g-3 mt-1 mb-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-0" style="border-left:4px solid #2563eb !important;">
                <div class="card-body py-3">
                    <h5 class="fw-bold mb-1">{{ $staff->full_name }}</h5>
                    <div class="text-muted mb-2" style="font-size:13px;">{{ $staff->employmentid }} · {{ $staff->department ?? '—' }}</div>
                    <form method="GET" class="d-flex gap-2 align-items-end">
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" style="width:150px;">
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" style="width:150px;">
                        <button class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="row g-2">
                @foreach([
                    ['Present', $present, 'success'],
                    ['Late', $late, 'secondary'],
                    ['Excused', $excused, 'info'],
                    ['Absent', $absent, 'danger'],
                ] as [$label, $val, $color])
                <div class="col-3">
                    <div class="card border text-center mb-0 py-2">
                        <div class="fw-bold fs-5 text-{{ $color }}">{{ $val }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $label }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center py-3">
            <h5 class="card-title mb-0 flex-grow-1"><i class="ri-calendar-line me-2 text-primary"></i>Daily Log</h5>
            <span class="fw-bold text-{{ $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger') }}">{{ $pct }}% attendance</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-nowrap align-middle mb-0">
                    <thead style="background:#1e3a5f;">
                        <tr>
                            <th class="text-white">Date</th>
                            <th class="text-white">Status</th>
                            <th class="text-white">Time In</th>
                            <th class="text-white">Time Out</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($calendar as $day)
                        @php
                            $sc = ['present'=>'success','late'=>'secondary','excused'=>'info','absent'=>'danger','outage'=>'dark'];
                            $c  = $sc[$day['status']] ?? 'secondary';
                        @endphp
                        <tr>
                            <td><strong>{{ $day['label'] }}</strong></td>
                            <td><span class="badge bg-{{ $c }}-subtle text-{{ $c }} fw-semibold">{{ ucfirst($day['status']) }}</span></td>
                            <td class="text-muted">{{ $day['time_in'] ?? '—' }}</td>
                            <td class="text-muted">{{ $day['time_out'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No working days in this range.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div></div></div>
@endsection
