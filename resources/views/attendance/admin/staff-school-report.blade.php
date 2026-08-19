@extends('layouts.master')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Staff Attendance Report</h4>
        <span class="badge bg-info-subtle text-info">Device-driven — no manual entries</span>
    </div>

    <div class="card shadow-sm mt-3 mb-3">
        <div class="card-body py-3">
            <form method="GET" class="d-flex align-items-end gap-3 flex-wrap">
                <div>
                    <label class="form-label fw-semibold mb-1" style="font-size:11px;text-transform:uppercase;">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" style="width:160px;">
                </div>
                <div>
                    <label class="form-label fw-semibold mb-1" style="font-size:11px;text-transform:uppercase;">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" style="width:160px;">
                </div>
                <button class="btn btn-primary btn-sm">Filter</button>
                <div class="ms-auto d-flex gap-3">
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-secondary">{{ $workingDays }}</div>
                        <div class="text-muted" style="font-size:11px;">Working Days</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-{{ $avgPct >= 80 ? 'success' : ($avgPct >= 60 ? 'warning' : 'danger') }}">{{ $avgPct }}%</div>
                        <div class="text-muted" style="font-size:11px;">Staff Avg</div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Device outage manager --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header py-3"><h6 class="mb-0"><i class="ri-signal-wifi-off-line me-2 text-danger"></i>Device Outage Dates</h6></div>
        <div class="card-body">
            <p class="text-muted" style="font-size:12px;">Mark a date as a device outage to exclude it from every staff member's working-day count for this range, so nobody is wrongly marked absent.</p>
            <form id="outageForm" class="d-flex gap-2 align-items-end mb-3">
                <div>
                    <label class="form-label mb-1" style="font-size:11px;">Date</label>
                    <input type="date" name="outage_date" class="form-control form-control-sm" required style="width:160px;">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label mb-1" style="font-size:11px;">Reason</label>
                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Power outage, network down…">
                </div>
                @can('Create device-outages')
                <button class="btn btn-outline-danger btn-sm">Mark Outage</button>
                @endcan
            </form>

            @if($outages->isNotEmpty())
            <div class="d-flex flex-wrap gap-2">
                @foreach($outages as $o)
                <span class="badge bg-danger-subtle text-danger d-flex align-items-center gap-1" style="font-size:12px;padding:6px 10px;">
                    {{ $o->outage_date->format('d M Y') }}{{ $o->reason ? ' — '.$o->reason : '' }}
                    @can('Delete device-outages')
                    <button onclick="removeOutage({{ $o->id }})" class="btn-close btn-close-sm ms-1" style="font-size:9px;"></button>
                    @endcan
                </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center py-3">
            <h5 class="card-title mb-0 flex-grow-1"><i class="ri-briefcase-line me-2 text-primary"></i>Staff</h5>
            <div class="input-group input-group-sm" style="width:220px;">
                <span class="input-group-text"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control" id="srch" placeholder="Search staff…">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-nowrap align-middle mb-0">
                    <thead style="background:#1e3a5f;">
                        <tr>
                            <th class="text-white">#</th>
                            <th class="text-white">Staff</th>
                            <th class="text-white">Department</th>
                            <th class="text-white text-center">Present</th>
                            <th class="text-white text-center">Late</th>
                            <th class="text-white text-center">Excused</th>
                            <th class="text-white text-center">Absent*</th>
                            <th class="text-white">Attendance</th>
                            <th class="text-white"></th>
                        </tr>
                    </thead>
                    <tbody id="rows">
                    @forelse($rows as $i => $r)
                        @php
                            $col = $r->attendance_percentage >= 80 ? 'success' : ($r->attendance_percentage >= 60 ? 'warning' : 'danger');
                        @endphp
                        <tr data-name="{{ strtolower($r->full_name . ' ' . $r->employmentid) }}">
                            <td class="text-muted">{{ $i+1 }}</td>
                            <td class="fw-semibold" style="font-size:13px;">{{ $r->full_name }}<div class="text-muted" style="font-size:11px;">{{ $r->employmentid }}</div></td>
                            <td class="text-muted" style="font-size:12px;">{{ $r->department ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $r->days_present }}</span></td>
                            <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">{{ $r->days_late }}</span></td>
                            <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $r->days_excused }}</span></td>
                            <td class="text-center"><span class="badge bg-danger-subtle text-danger">{{ $r->days_absent }}</span></td>
                            <td style="min-width:150px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar bg-{{ $col }}" style="width:{{ $r->attendance_percentage }}%"></div>
                                    </div>
                                    <span class="fw-bold text-{{ $col }}" style="font-size:12px;min-width:38px;">{{ $r->attendance_percentage }}%</span>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('staff-attendance.report', $r->staff_id) }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}"
                                   class="btn btn-outline-primary btn-sm" style="font-size:11px;">Details →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">No staff records found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 text-muted" style="font-size:11px;">*Absent is inferred — no device punch recorded for that working day.</div>
        </div>
    </div>

</div></div></div>

<script>
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

document.getElementById('srch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#rows tr[data-name]').forEach(r => {
        r.style.display = (!q || r.dataset.name.includes(q)) ? '' : 'none';
    });
});

document.getElementById('outageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('{{ route('staff-attendance.outage.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        body: fd,
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
});

function removeOutage(id) {
    if (!confirm('Remove this outage flag? Absences for that date will be recalculated normally.')) return;
    fetch(`{{ url('attendance/staff/outage') }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>
@endsection
