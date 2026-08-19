@extends('layouts.master')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Unmapped Device PINs</h4>
        <a href="{{ route('device-mappings.index') }}" class="btn btn-outline-secondary btn-sm">Back to Mappings</a>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body p-0">
            <table class="table table-nowrap align-middle mb-0">
                <thead style="background:#1e3a5f;">
                    <tr>
                        <th class="text-white">Device</th>
                        <th class="text-white">PIN</th>
                        <th class="text-white">Punches</th>
                        <th class="text-white">Last Seen</th>
                        <th class="text-white">Assign To</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($unmapped as $row)
                <tr>
                    <td class="text-muted">{{ $row->device_serial }}</td>
                    <td class="fw-semibold">{{ $row->device_pin }}</td>
                    <td><span class="badge bg-warning-subtle text-warning">{{ $row->punch_count }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($row->last_seen)->diffForHumans() }}</td>
                    <td style="min-width:340px;">
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm assign-type" style="width:100px;">
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                            </select>
                            <select class="form-select form-select-sm assign-person" style="width:220px;"></select>
                            <button class="btn btn-primary btn-sm assign-btn"
                                    data-device="{{ $row->device_serial }}" data-pin="{{ $row->device_pin }}">
                                Assign
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No unmapped PINs. All punches are matched.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div></div></div>

<script>
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

document.querySelectorAll('tbody tr').forEach(row => {
    const typeSel = row.querySelector('.assign-type');
    const personSel = row.querySelector('.assign-person');
    if (!typeSel) return;
    const loadOpts = async () => {
        const res = await fetch(`{{ route('device-mappings.search') }}?type=${typeSel.value}&q=`);
        const data = await res.json();
        personSel.innerHTML = '<option value="">Select…</option>' + data.map(o => `<option value="${o.id}">${o.text}</option>`).join('');
    };
    typeSel.addEventListener('change', loadOpts);
    loadOpts();
});

document.querySelectorAll('.assign-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const personId = row.querySelector('.assign-person').value;
        const personType = row.querySelector('.assign-type').value;
        if (!personId) { alert('Select a person first.'); return; }

        fetch('{{ route('device-mappings.quick-assign') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                device_serial: this.dataset.device,
                device_pin: this.dataset.pin,
                person_type: personType,
                person_id: personId,
            }),
        }).then(r => r.json()).then(d => {
            if (d.success) row.remove();
            else alert(d.message);
        });
    });
});
</script>
@endsection
