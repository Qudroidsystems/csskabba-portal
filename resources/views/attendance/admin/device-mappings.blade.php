@extends('layouts.master')
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Device PIN Mappings</h4>
                        <div class="page-title-right d-flex gap-2">
                            @if($unmappedCount > 0)
                            <a href="{{ route('device-mappings.unmapped') }}" class="btn btn-warning btn-sm">
                                <i class="ri-alert-line me-1"></i>{{ $unmappedCount }} Unmapped PIN(s)
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bulk import + manual add --}}
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0"><i class="ri-file-upload-line me-2 text-primary"></i>Bulk Import (CSV)</h6></div>
                        <div class="card-body">
                            <p class="text-muted" style="font-size:12px;">Columns: <code>device_pin, person_type, identifier</code>. identifier = admission number for students, staff ID for staff.</p>
                            <form id="bulkImportForm" enctype="multipart/form-data">
                                <div class="mb-2">
                                    <input type="text" name="device_serial" class="form-control form-control-sm" placeholder="Device Serial (e.g. PKD7022588362)" required>
                                </div>
                                <div class="mb-2">
                                    <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv" required>
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">Import</button>
                            </form>
                            <div id="importResult" class="mt-2" style="font-size:12px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0"><i class="ri-user-add-line me-2 text-primary"></i>Add Single Mapping</h6></div>
                        <div class="card-body">
                            <form id="addMappingForm">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="device_serial" class="form-control form-control-sm" placeholder="Device Serial" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="device_pin" class="form-control form-control-sm" placeholder="Device PIN" required>
                                    </div>
                                    <div class="col-6">
                                        <select name="person_type" id="personType" class="form-select form-select-sm" required>
                                            <option value="student">Student</option>
                                            <option value="staff">Staff</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select name="person_id" id="personSelect" class="form-select form-select-sm" required>
                                            <option value="">Type to search…</option>
                                        </select>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-sm mt-2" type="submit">Add Mapping</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center py-3">
                    <h5 class="card-title mb-0 flex-grow-1">Mapped Users <span class="badge bg-dark-subtle text-dark">{{ $mappings->total() }}</span></h5>
                    <form method="GET" class="d-flex gap-2">
                        <select name="type" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
                            <option value="">All types</option>
                            <option value="student" {{ request('type')==='student'?'selected':'' }}>Students</option>
                            <option value="staff" {{ request('type')==='staff'?'selected':'' }}>Staff</option>
                        </select>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search PIN…" style="width:140px;">
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead style="background:#1e3a5f;">
                                <tr>
                                    <th class="text-white">Device</th>
                                    <th class="text-white">PIN</th>
                                    <th class="text-white">Type</th>
                                    <th class="text-white">Person</th>
                                    <th class="text-white">Status</th>
                                    <th class="text-white"></th>
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
