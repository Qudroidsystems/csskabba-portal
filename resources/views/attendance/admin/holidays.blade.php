@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Holidays & Breaks</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('attendance.settings') }}">Attendance Settings</a></li>
                                <li class="breadcrumb-item active">Holidays</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @can('Create attendance-holidays')
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-add-circle-line me-2 text-primary"></i>Add Holiday / Break
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="holidayForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                        <select name="term_id" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach($terms as $t)
                                                <option value="{{ $t->id }}">{{ $t->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                        <select name="session_id" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach($sessions as $s)
                                                <option value="{{ $s->id }}">{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="holiday_date" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">End Date <small class="text-muted fw-normal">(optional)</small></label>
                                        <input type="date" name="holiday_end_date" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="holiday_name" class="form-control" placeholder="e.g. Mid-Term Break" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select name="holiday_type" class="form-select" required>
                                            <option value="public">Public Holiday</option>
                                            <option value="midterm">Mid-Term Break</option>
                                            <option value="school_event">School Event</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Notes <small class="text-muted fw-normal">(optional)</small></label>
                                        <input type="text" name="notes" class="form-control" placeholder="Any additional notes…">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line me-1"></i>Save Holiday
                                    </button>
                                    <a href="{{ route('attendance.settings') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="ri-arrow-left-line me-1"></i>Back to Settings
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">
                                <i class="ri-rest-time-line me-2 text-primary"></i>Holidays List
                                <span class="badge bg-dark-subtle text-dark ms-1">{{ $holidays->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead style="background:#1e3a5f;">
                                        <tr>
                                            <th class="text-white">#</th>
                                            <th class="text-white">Name</th>
                                            <th class="text-white">Type</th>
                                            <th class="text-white">Start</th>
                                            <th class="text-white">End</th>
                                            <th class="text-white">Term</th>
                                            <th class="text-white">Session</th>
                                            <th class="text-white">Notes</th>
                                            @can('Delete attendance-holidays')<th class="text-white"></th>@endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($holidays as $i => $h)
                                    @php
                                        $typeColors = ['public'=>'primary','midterm'=>'info','school_event'=>'success','other'=>'secondary'];
                                        $tc = $typeColors[$h->holiday_type] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td class="text-muted">{{ $i+1 }}</td>
                                        <td><strong>{{ $h->holiday_name }}</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $tc }}-subtle text-{{ $tc }}">
                                                {{ ucfirst(str_replace('_', ' ', $h->holiday_type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $h->holiday_date->format('d M Y') }}</td>
                                        <td>{{ $h->holiday_end_date ? $h->holiday_end_date->format('d M Y') : '—' }}</td>
                                        <td>{{ $h->term?->term }}</td>
                                        <td>{{ $h->session?->session }}</td>
                                        <td class="text-muted" style="font-size:12px;">{{ $h->notes ?? '—' }}</td>
                                        @can('Delete attendance-holidays')
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteHoliday({{ $h->id }})">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                        @endcan
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <i class="ri-inbox-line ri-2x d-block mb-2 text-muted"></i>
                                            <p class="text-muted mb-0">No holidays added yet.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

function showToast(msg, type = 'success') {
    const colors = { success:'#16a34a', danger:'#dc2626' };
    const id = 'toast_' + Date.now();
    document.body.insertAdjacentHTML('beforeend',
        `<div id="${id}" style="position:fixed;bottom:20px;right:20px;z-index:9999;background:${colors[type]};color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;min-width:220px;">${msg}</div>`
    );
    setTimeout(() => document.getElementById(id)?.remove(), 3500);
}

@can('Create attendance-holidays')
document.getElementById('holidayForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const r = await fetch('{{ route('attendance.holidays.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: new FormData(this) });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'danger');
    if (d.success) setTimeout(() => location.reload(), 1000);
});
@endcan

@can('Delete attendance-holidays')
async function deleteHoliday(id) {
    if (!confirm('Delete this holiday?')) return;
    const r = await fetch(`/attendance/holidays/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken()} });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'danger');
    if (d.success) setTimeout(() => location.reload(), 800);
}
@endcan
</script>
@endsection
