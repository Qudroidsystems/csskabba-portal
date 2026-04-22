@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Attendance Settings</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Attendance</a></li>
                                <li class="breadcrumb-item active">Settings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-term" role="tab">
                                        <i class="ri-calendar-line me-1"></i>Term Calendar
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-holiday" role="tab">
                                        <i class="ri-rest-time-line me-1"></i>Holidays & Breaks
                                    </a>
                                </li>
                                @can('View attendance-school-report')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('attendance.school-report') }}">
                                        <i class="ri-bar-chart-2-line me-1"></i>School Report
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">

                                {{-- ── Term Calendar Tab ── --}}
                                <div class="tab-pane active" id="tab-term" role="tabpanel">

                                    @can('Create attendance-settings')
                                    <div class="card border mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold">
                                                <i class="ri-add-circle-line me-2 text-primary"></i>Add / Update Term Calendar
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <form id="settingForm">
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
                                                        <label class="form-label fw-semibold">Resumption Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="resumption_date" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Vacation Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="vacation_date" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Periods to Track</label>
                                                        <div class="d-flex gap-4 mt-1">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="track_morning" id="trackMorning" checked>
                                                                <label class="form-check-label" for="trackMorning">🌅 Morning Attendance</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="track_afternoon" id="trackAfternoon">
                                                                <label class="form-check-label" for="trackAfternoon">🌇 Afternoon Attendance</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ri-save-line me-1"></i>Save Setting
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endcan

                                    @can('View attendance-settings')
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold">
                                                <i class="ri-list-check me-2 text-primary"></i>Existing Term Settings
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-nowrap align-middle mb-0">
                                                    <thead style="background:#1e3a5f;">
                                                        <tr>
                                                            <th class="text-white">Term</th>
                                                            <th class="text-white">Session</th>
                                                            <th class="text-white">Resumption</th>
                                                            <th class="text-white">Vacation</th>
                                                            <th class="text-white text-center">Morning</th>
                                                            <th class="text-white text-center">Afternoon</th>
                                                            <th class="text-white text-center">School Days</th>
                                                            @can('Delete attendance-settings')<th class="text-white"></th>@endcan
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($settings as $s)
                                                    <tr>
                                                        <td><strong>{{ $s->term?->term }}</strong></td>
                                                        <td>{{ $s->session?->session }}</td>
                                                        <td>{{ $s->resumption_date->format('d M Y') }}</td>
                                                        <td>{{ $s->vacation_date->format('d M Y') }}</td>
                                                        <td class="text-center">
                                                            @if($s->track_morning)
                                                                <span class="badge bg-success-subtle text-success"><i class="ri-check-line"></i></span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($s->track_afternoon)
                                                                <span class="badge bg-success-subtle text-success"><i class="ri-check-line"></i></span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary-subtle text-primary fw-bold fs-6">{{ $s->totalSchoolDays() }}</span>
                                                        </td>
                                                        @can('Delete attendance-settings')
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSetting({{ $s->id }})">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </td>
                                                        @endcan
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4 text-muted">
                                                            <i class="ri-inbox-line ri-2x d-block mb-1"></i>No settings configured yet.
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @endcan
                                </div>

                                {{-- ── Holiday Tab ── --}}
                                <div class="tab-pane" id="tab-holiday" role="tabpanel">

                                    @can('Create attendance-holidays')
                                    <div class="card border mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold">
                                                <i class="ri-add-circle-line me-2 text-primary"></i>Add Holiday / Break
                                            </h6>
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
                                                        <label class="form-label fw-semibold">End Date <small class="text-muted">(optional)</small></label>
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
                                                        <label class="form-label fw-semibold">Notes <small class="text-muted">(optional)</small></label>
                                                        <input type="text" name="notes" class="form-control" placeholder="Any additional notes…">
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ri-save-line me-1"></i>Save Holiday
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endcan

                                    @can('View attendance-holidays')
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold">
                                                <i class="ri-list-check me-2 text-primary"></i>Holidays List
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-nowrap align-middle mb-0">
                                                    <thead style="background:#1e3a5f;">
                                                        <tr>
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
                                                    @forelse($holidays as $h)
                                                    <tr>
                                                        <td><strong>{{ $h->holiday_name }}</strong></td>
                                                        <td>
                                                            @php
                                                                $typeColors = ['public'=>'primary','midterm'=>'info','school_event'=>'success','other'=>'secondary'];
                                                                $tc = $typeColors[$h->holiday_type] ?? 'secondary';
                                                            @endphp
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
                                                        <td colspan="8" class="text-center py-4 text-muted">
                                                            <i class="ri-inbox-line ri-2x d-block mb-1"></i>No holidays added yet.
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @endcan
                                </div>

                            </div>{{-- tab-content --}}
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
    const colors = { success:'#16a34a', danger:'#dc2626', warning:'#d97706' };
    const id = 'toast_' + Date.now();
    document.body.insertAdjacentHTML('beforeend',
        `<div id="${id}" style="position:fixed;bottom:20px;right:20px;z-index:9999;background:${colors[type]||colors.success};color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;min-width:220px;">${msg}</div>`
    );
    setTimeout(() => document.getElementById(id)?.remove(), 3500);
}

@can('Create attendance-settings')
document.getElementById('settingForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.set('track_morning',   document.getElementById('trackMorning').checked   ? '1' : '0');
    fd.set('track_afternoon', document.getElementById('trackAfternoon').checked ? '1' : '0');
    const r = await fetch('{{ route('attendance.settings.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body:fd });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'danger');
    if (d.success) setTimeout(() => location.reload(), 1000);
});
@endcan

@can('Create attendance-holidays')
document.getElementById('holidayForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const r = await fetch('{{ route('attendance.holidays.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: new FormData(this) });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'danger');
    if (d.success) setTimeout(() => location.reload(), 1000);
});
@endcan

@can('Delete attendance-settings')
async function deleteSetting(id) {
    if (!confirm('Delete this setting? This cannot be undone.')) return;
    const r = await fetch(`/attendance/settings/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken()} });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'danger');
    if (d.success) setTimeout(() => location.reload(), 800);
}
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
