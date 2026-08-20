{{-- resources/views/device-mappings/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --bill-primary: #1e3a5f;
    --bill-accent:  #2563eb;
    --bill-success: #16a34a;
    --bill-warning: #d97706;
    --bill-danger:  #dc2626;
    --bill-muted:   #6b7280;
    --bill-border:  #e2e8f0;
    --bill-bg:      #f8fafc;
    --bill-radius:  12px;
    --bill-shadow:  0 2px 8px rgba(0,0,0,.08);
}

.bill-hero {
    background: linear-gradient(135deg, var(--bill-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--bill-radius);
    padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.bill-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.bill-hero::after {
    content:''; position:absolute; bottom:-80px; left:-30px;
    width:260px; height:260px; background:rgba(255,255,255,.03); border-radius:50%;
}
.bill-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.bill-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }
.bill-hero .btn-light { position:relative; }

.stat-card {
    background:#fff; border:1px solid var(--bill-border);
    border-radius:var(--bill-radius); padding:18px 20px;
    transition:transform .15s, box-shadow .15s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--bill-shadow); }
.stat-card .stat-value { font-size:28px; font-weight:700; color:var(--bill-primary); }
.stat-card .stat-label { font-size:12px; color:var(--bill-muted); margin-top:4px; }
.stat-card .stat-icon  { font-size:32px; opacity:.12; float:right; margin-top:-8px; }

.filter-card {
    background:#fff; border:1px solid var(--bill-border);
    border-radius:var(--bill-radius); padding:20px 24px;
    margin-bottom:24px; box-shadow:var(--bill-shadow);
}

.bill-table th {
    background:var(--bill-primary); color:#fff;
    padding:12px 16px; font-weight:600; font-size:13px;
    white-space:nowrap; border:none;
}
.bill-table td {
    padding:12px 16px; vertical-align:middle;
    border-bottom:1px solid var(--bill-border); font-size:13px;
}
.bill-table tr:hover td { background:#eff6ff; }

.form-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-control, .form-select {
    border:1.5px solid var(--bill-border); border-radius:8px;
    font-size:13px; padding:9px 14px; transition:border .15s;
}
.form-control:focus, .form-select:focus {
    border-color:var(--bill-accent);
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
    outline:none;
}
.form-control-sm, .form-select-sm { padding:6px 10px; border-radius:7px; }

.bill-card {
    background:#fff; border:1px solid var(--bill-border);
    border-radius:var(--bill-radius); box-shadow:var(--bill-shadow);
    overflow:hidden;
}
.bill-card .card-header {
    background:#fff; border-bottom:1px solid var(--bill-border);
    padding:16px 20px; font-weight:700; font-size:14px; color:var(--bill-primary);
}
.bill-card .card-body { padding:20px; }

#importResult .text-success { color: var(--bill-success) !important; font-weight:600; }
#importResult .text-danger  { color: var(--bill-danger) !important; font-weight:600; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="bill-hero d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1><i class="ri-fingerprint-line me-2"></i>Device PIN Mappings</h1>
            <p>Link biometric device PINs to students and staff, import in bulk, or resolve unmapped punches.</p>
        </div>
        @if($unmappedCount > 0)
        <a href="{{ route('device-mappings.unmapped') }}" class="btn btn-light btn-sm fw-semibold">
            <i class="ri-alert-line me-1"></i>{{ $unmappedCount }} Unmapped PIN(s)
        </a>
        @endif
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-links-line"></i></div>
                <div class="stat-value">{{ $mappings->total() }}</div>
                <div class="stat-label">Total Mappings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-graduation-cap-line"></i></div>
                <div class="stat-value text-primary">{{ $studentCount ?? '—' }}</div>
                <div class="stat-label">Student Mappings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-briefcase-line"></i></div>
                <div class="stat-value text-info">{{ $staffCount ?? '—' }}</div>
                <div class="stat-label">Staff Mappings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-alert-line"></i></div>
                <div class="stat-value text-warning">{{ $unmappedCount }}</div>
                <div class="stat-label">Unmapped PINs</div>
            </div>
        </div>
    </div>

    {{-- Bulk import + manual add --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="bill-card h-100">
                <div class="card-header"><i class="ri-file-upload-line me-2"></i>Bulk Import (CSV)</div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:12px;">Columns: <code>device_pin, person_type, identifier</code>. identifier = admission number for students, staff ID for staff.</p>
                    <form id="bulkImportForm" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="form-label">Device Serial</label>
                            <input type="text" name="device_serial" class="form-control form-control-sm" placeholder="e.g. PKD7022588362" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">CSV File</label>
                            <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv" required>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="ri-upload-2-line me-1"></i>Import</button>
                    </form>
                    <div id="importResult" class="mt-2" style="font-size:12px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bill-card h-100">
                <div class="card-header"><i class="ri-user-add-line me-2"></i>Add Single Mapping</div>
                <div class="card-body">
                    <form id="addMappingForm">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Device Serial</label>
                                <input type="text" name="device_serial" class="form-control form-control-sm" placeholder="Device Serial" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Device PIN</label>
                                <input type="number" name="device_pin" class="form-control form-control-sm" placeholder="Device PIN" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Person Type</label>
                                <select name="person_type" id="personType" class="form-select form-select-sm" required>
                                    <option value="student">Student</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Person</label>
                                <select name="person_id" id="personSelect" class="form-select form-select-sm" required>
                                    <option value="">Type to search…</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-success btn-sm mt-3" type="submit"><i class="ri-add-line me-1"></i>Add Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label"><i class="ri-filter-3-line me-1"></i>Type</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All types</option>
                    <option value="student" {{ request('type')==='student'?'selected':'' }}>Students</option>
                    <option value="staff" {{ request('type')==='staff'?'selected':'' }}>Staff</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="ri-search-line me-1"></i>Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search PIN…">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="ri-search-line me-1"></i>Search</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bill-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="ri-list-check-2 me-2"></i>Mapped Users</span>
            <span class="badge bg-primary">{{ $mappings->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table bill-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>PIN</th>
                            <th>Type</th>
                            <th>Person</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($mappings as $m)
                        <tr>
                            <td class="text-muted" style="font-size:12px;">{{ $m->device_serial }}</td>
                            <td class="fw-semibold">{{ $m->device_pin }}</td>
                            <td><span class="badge bg-{{ $m->person_type==='student'?'primary':'info' }}-subtle text-{{ $m->person_type==='student'?'primary':'info' }}">{{ ucfirst($m->person_type) }}</span></td>
                            <td>{{ $m->display_name }}</td>
                            <td>
                                <span class="badge bg-{{ $m->active?'success':'secondary' }}-subtle text-{{ $m->active?'success':'secondary' }}">
                                    {{ $m->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteMapping({{ $m->id }})"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No mappings yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $mappings->links() }}</div>
        </div>
    </div>

</div>
</div>
</div>

<script>
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

const personSelect = document.getElementById('personSelect');
const personType = document.getElementById('personType');

async function loadOptions(q) {
    const res = await fetch(`{{ route('device-mappings.search') }}?type=${personType.value}&q=${encodeURIComponent(q)}`);
    const data = await res.json();
    personSelect.innerHTML = '<option value="">Select…</option>' + data.map(o => `<option value="${o.id}">${o.text}</option>`).join('');
}
personType.addEventListener('change', () => loadOptions(''));
document.addEventListener('DOMContentLoaded', () => loadOptions(''));

document.getElementById('addMappingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('{{ route('device-mappings.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        body: fd,
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
});

document.getElementById('bulkImportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const resultEl = document.getElementById('importResult');
    resultEl.innerHTML = 'Importing…';
    fetch('{{ route('device-mappings.bulk-import') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        body: fd,
    }).then(r => r.json()).then(d => {
        resultEl.innerHTML = `<span class="text-${d.success?'success':'danger'}">${d.message}</span>`;
        if (d.errors && d.errors.length) resultEl.innerHTML += '<br>' + d.errors.join('<br>');
        if (d.success) setTimeout(() => location.reload(), 1500);
    });
});

function deleteMapping(id) {
    if (!confirm('Remove this mapping?')) return;
    fetch(`/attendance/device-mappings/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>
@endsection