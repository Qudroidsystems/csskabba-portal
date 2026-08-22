{{-- resources/views/attendance/staff/staff-attendance-report.blade.php --}}
@extends('layouts.master')
@section('content')
<style>
:root {
    --sar-primary: #1e3a5f;
    --sar-accent:  #2563eb;
    --sar-success: #16a34a;
    --sar-warning: #d97706;
    --sar-danger:  #dc2626;
    --sar-muted:   #6b7280;
    --sar-border:  #e2e8f0;
    --sar-radius:  10px;
    --sar-shadow:  0 1px 4px rgba(0,0,0,.08);
}

.sar-hero {
    background: linear-gradient(135deg, var(--sar-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--sar-radius);
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.sar-hero::before {
    content:'';
    position:absolute;
    top:-60px;
    right:-60px;
    width:220px;
    height:220px;
    background:rgba(255,255,255,.06);
    border-radius:50%;
}
.sar-hero h4 {
    color:#fff;
    font-weight:700;
    margin:0;
    position:relative;
}
.sar-hero p {
    color:rgba(255,255,255,.75);
    margin:0;
    font-size:13px;
    position:relative;
}

.sar-stat-card {
    background:#fff;
    border:1px solid var(--sar-border);
    border-radius:var(--sar-radius);
    padding:16px 18px;
    text-align:center;
    transition:transform .15s, box-shadow .15s;
}
.sar-stat-card:hover {
    transform:translateY(-2px);
    box-shadow:var(--sar-shadow);
}
.sar-stat-card .stat-value {
    font-size:24px;
    font-weight:700;
}
.sar-stat-card .stat-label {
    font-size:11px;
    color:var(--sar-muted);
    margin-top:2px;
}

.sar-card {
    background:#fff;
    border:1px solid var(--sar-border);
    border-radius:var(--sar-radius);
    box-shadow:var(--sar-shadow);
    overflow:hidden;
}
.sar-card .card-header {
    background:#fff;
    border-bottom:1px solid var(--sar-border);
    padding:16px 20px;
    font-weight:700;
    font-size:14px;
    color:var(--sar-primary);
}

.sar-table th {
    background:var(--sar-primary);
    color:#fff;
    padding:12px 16px;
    font-weight:600;
    font-size:13px;
    border:none;
}
.sar-table td {
    padding:12px 16px;
    vertical-align:middle;
    border-bottom:1px solid var(--sar-border);
    font-size:13px;
}
.sar-table tr:hover td {
    background:#eff6ff;
}

.sar-form-label {
    font-size:13px;
    font-weight:600;
    color:#374151;
    margin-bottom:6px;
}
.sar-form-control {
    border:1.5px solid var(--sar-border);
    border-radius:8px;
    font-size:13px;
    padding:9px 14px;
    transition:border .15s;
}
.sar-form-control:focus {
    border-color:var(--sar-accent);
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
    outline:none;
}
.sar-form-control-sm {
    padding:6px 10px;
    border-radius:7px;
}

.sar-badge-outage {
    background: #fef2f2;
    color: #dc2626;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.sar-toast {
    position:fixed;
    bottom:20px;
    right:20px;
    z-index:99999;
    padding:14px 20px;
    border-radius:10px;
    background:#fff;
    box-shadow:0 4px 20px rgba(0,0,0,.12);
    font-weight:600;
    font-size:13px;
    animation: sarToastIn .3s ease;
}
@keyframes sarToastIn {
    from { opacity:0; transform:translateY(20px); }
    to { opacity:1; transform:translateY(0); }
}
</style>

<div class="main-content"><div class="page-content"><div class="container-fluid">

    {{-- ══ HERO ═══════════════════════════════════════════════════════════ --}}
    <div class="sar-hero d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="ri-briefcase-line me-2"></i>Staff Attendance Report</h4>
            <p>Device-driven attendance tracking — no manual entries</p>
        </div>
        <span class="badge bg-light text-dark">Device-driven</span>
    </div>

    {{-- ══ FILTER ════════════════════════════════════════════════════════ --}}
    <div class="sar-card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="d-flex align-items-end gap-3 flex-wrap">
                <div>
                    <label class="sar-form-label mb-1" style="font-size:11px;text-transform:uppercase;">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="sar-form-control sar-form-control-sm" style="width:160px;">
                </div>
                <div>
                    <label class="sar-form-label mb-1" style="font-size:11px;text-transform:uppercase;">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="sar-form-control sar-form-control-sm" style="width:160px;">
                </div>
                <button class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                <div class="ms-auto d-flex gap-4">
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

    {{-- ══ DEVICE OUTAGE MANAGER ════════════════════════════════════════ --}}
    <div class="sar-card mb-3">
        <div class="card-header">
            <i class="ri-signal-wifi-off-line me-2 text-danger"></i>Device Outage Dates
        </div>
        <div class="card-body">
            <p class="text-muted" style="font-size:12px;">Mark a date as a device outage to exclude it from every staff member's working-day count for this range, so nobody is wrongly marked absent.</p>
            <form id="outageForm" class="d-flex gap-2 align-items-end mb-3">
                <div>
                    <label class="sar-form-label mb-1" style="font-size:11px;">Date</label>
                    <input type="date" name="outage_date" class="sar-form-control sar-form-control-sm" required style="width:160px;">
                </div>
                <div class="flex-grow-1">
                    <label class="sar-form-label mb-1" style="font-size:11px;">Reason</label>
                    <input type="text" name="reason" class="sar-form-control sar-form-control-sm" placeholder="e.g. Power outage, network down…">
                </div>
                @can('Create device-outages')
                <button class="btn btn-outline-danger btn-sm"><i class="ri-add-line me-1"></i>Mark Outage</button>
                @endcan
            </form>

            @if($outages->isNotEmpty())
            <div class="d-flex flex-wrap gap-2">
                @foreach($outages as $o)
                <span class="sar-badge-outage">
                    {{ $o->outage_date->format('d M Y') }}{{ $o->reason ? ' — '.$o->reason : '' }}
                    @can('Delete device-outages')
                    <button onclick="removeOutage({{ $o->id }})" class="btn-close btn-close-sm" style="font-size:8px;"></button>
                    @endcan
                </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ══ TABLE ════════════════════════════════════════════════════════ --}}
    <div class="sar-card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1"><i class="ri-briefcase-line me-2 text-primary"></i>Staff</h5>
            <div class="input-group input-group-sm" style="width:220px;">
                <span class="input-group-text"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control" id="srch" placeholder="Search staff…">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table sar-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Department</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Excused</th>
                            <th class="text-center">Absent*</th>
                            <th>Attendance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="rows">
                    @forelse($rows as $i => $r)
                        @php
                            $col = $r->attendance_percentage >= 80 ? 'success' : ($r->attendance_percentage >= 60 ? 'warning' : 'danger');
                        @endphp
                        <tr data-name="{{ strtolower($r->full_name . ' ' . $r->employmentid) }}">
                            <td class="text-muted">{{ $i+1 }}</td>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;">{{ $r->full_name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $r->employmentid }}</div>
                            </td>
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
                                   class="btn btn-outline-primary btn-sm" style="font-size:11px;">
                                    Details <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted">
                            <i class="ri-inbox-line ri-2x d-block mb-2"></i>No staff records found.
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 text-muted" style="font-size:11px;">*Absent is inferred — no device punch recorded for that working day.</div>
        </div>
    </div>

</div></div></div>

<script>
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function sarToast(msg, type = 'success') {
    const colors = { success:'#16a34a', danger:'#dc2626', warning:'#d97706', info:'#2563eb' };
    const id = 'sar_toast_' + Date.now();
    document.body.insertAdjacentHTML('beforeend',
        `<div id="${id}" class="sar-toast" style="background:${colors[type] || colors.success};color:#fff;min-width:220px;border-radius:10px;padding:14px 20px;box-shadow:0 4px 20px rgba(0,0,0,.12);font-weight:600;font-size:13px;animation:sarToastIn .3s ease;">
            ${msg}
            <button onclick="document.getElementById('${id}').remove()" style="background:none;border:none;color:#fff;float:right;margin-left:12px;font-size:16px;cursor:pointer;">×</button>
        </div>`
    );
    setTimeout(() => document.getElementById(id)?.remove(), 4500);
}

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
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd,
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            sarToast(d.message || 'Outage marked.', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(d.message || 'Failed to mark outage.');
        }
    })
    .catch(e => console.error(e));
});

function removeOutage(id) {
    if (!confirm('Remove this outage flag? Absences for that date will be recalculated normally.')) return;
    fetch(`{{ url('attendance/staff/outage') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            sarToast(d.message || 'Outage removed.', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(d.message || 'Failed to remove outage.');
        }
    })
    .catch(e => console.error(e));
}
</script>
@endsection