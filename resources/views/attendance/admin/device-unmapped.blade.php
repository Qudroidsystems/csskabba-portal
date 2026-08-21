{{-- resources/views/attendance/admin/device-unmapped.blade.php --}}
@extends('layouts.master')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
<style>
.select2-container .select2-selection--single {
    border:1.5px solid #e2e8f0 !important; border-radius:7px !important; min-height:31px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height:29px; font-size:12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height:29px; }
</style>

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
                    <td style="min-width:380px;">
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm assign-type" style="width:100px;">
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                            </select>
                            <select class="assign-person" style="width:240px;"></select>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<script>
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

function personTemplate(item) {
    if (!item.id) return item.text;
    const photo = item.photo
        ? `<img src="${item.photo}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;margin-right:6px;">`
        : `<div style="width:26px;height:26px;border-radius:50%;background:#e2e8f0;display:inline-flex;align-items:center;justify-content:center;margin-right:6px;font-size:10px;color:#64748b;">${item.text.slice(0,2).toUpperCase()}</div>`;
    const meta = item.meta ? Object.entries(item.meta).map(([k, v]) => `${k}: ${v}`).join(' · ') : '';
    return $(`
        <div style="display:flex;align-items:center;padding:1px 0;">
            ${photo}
            <div>
                <div style="font-weight:600;font-size:12px;">${item.text}</div>
                <div style="font-size:10px;color:#6b7280;">${item.subtitle || ''}${meta ? ' · ' + meta : ''}</div>
            </div>
        </div>
    `);
}

// Each row gets its own independent Select2 instance, scoped to that row's
// type toggle — this is what fixes both the "only 20 ever load" issue
// (now a live AJAX search) and the "staff shows nothing" issue (each
// instance re-queries fresh instead of relying on a stale one-time fetch).
document.querySelectorAll('tbody tr').forEach(row => {
    const typeSel   = row.querySelector('.assign-type');
    const personSel = row.querySelector('.assign-person');
    if (!typeSel || !personSel) return;

    $(personSel).select2({
        ajax: {
            url: "{{ route('device-mappings.search') }}",
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term || '', type: $(typeSel).val(), page: params.page || 1 }),
            processResults: data => ({ results: data.results, pagination: data.pagination }),
            cache: true,
        },
        minimumInputLength: 0,
        placeholder: 'Search…',
        templateResult: personTemplate,
        templateSelection: item => item.text || item.id,
        width: '240px',
        dropdownAutoWidth: true,
    });

    $(typeSel).on('change', () => $(personSel).val(null).trigger('change'));
});

document.querySelectorAll('.assign-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const row      = this.closest('tr');
        const personId = $(row.querySelector('.assign-person')).val();
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
