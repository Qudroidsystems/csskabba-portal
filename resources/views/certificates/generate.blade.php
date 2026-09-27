{{-- resources/views/certificates/generate.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <x-cb.hero title="Generate Certificate" icon="ri-award-line" subtitle="Pick a template and a student (or a whole class), then print." :back="route('certificates.index')" back-label="Certificates" />

    @if($errors->any())<div class="cb-banner warning"><i class="ri-error-warning-line"></i><div>{{ $errors->first() }}</div></div>@endif

    <x-cb.card title="Details" icon="ri-file-add-line">
        <form method="POST" action="{{ route('certificates.issue') }}" class="row g-3">@csrf
            <div class="col-md-6">
                <label class="form-label small">Template *</label>
                <select name="template_id" class="form-select" required>
                    <option value="">Choose…</option>
                    @foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->name }}{{ $t->requires_approval ? ' (needs approval)' : '' }}</option>@endforeach
                </select>
                @if($templates->isEmpty())<div class="small text-danger mt-1">No active templates. Create one first.</div>@endif
            </div>
            <div class="col-md-6">
                <label class="form-label small">Certificate title</label>
                <input name="title" class="form-control" maxlength="180" placeholder="e.g. Certificate of Completion">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Session</label>
                <select name="session_id" id="genSession" class="form-select"><option value="">—</option>@foreach($sessions as $s)<option value="{{ $s->id }}">{{ $s->session }}</option>@endforeach</select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Class</label>
                <select name="class_id" id="genClass" class="form-select"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Student</label>
                <select name="student_id" id="genStudent" class="form-select"><option value="">— choose class first —</option></select>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="whole_class" id="wholeClass" value="1">
                    <label class="form-check-label" for="wholeClass">Create for the <strong>whole class</strong> (drafts for every student in the selected class/session)</label>
                </div>
            </div>
            <div class="col-12">
                <button class="action-btn btn-primary-cb" @disabled($templates->isEmpty())><i class="ri-magic-line"></i>Generate</button>
            </div>
            <div class="small text-muted">Re-generating an existing certificate keeps its original serial &amp; QR (a reprint). Each print is counted and audited.</div>
        </form>
    </x-cb.card>
</div></div></div>

<script>
(function () {
    const cls = document.getElementById('genClass'), ses = document.getElementById('genSession'), stu = document.getElementById('genStudent');
    function load() {
        if (!cls.value) { stu.innerHTML = '<option value="">— choose class first —</option>'; return; }
        stu.innerHTML = '<option value="">Loading…</option>';
        const url = '{{ route('certificates.students') }}?class_id=' + cls.value + '&session_id=' + (ses.value || '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(j => {
            stu.innerHTML = '<option value="">— select a student —</option>';
            (j.data || []).forEach(function (s) {
                const o = document.createElement('option'); o.value = s.id;
                o.textContent = s.name + (s.admissionNo ? ' · ' + s.admissionNo : '');
                stu.appendChild(o);
            });
        }).catch(() => { stu.innerHTML = '<option value="">Could not load students</option>'; });
    }
    cls.addEventListener('change', load); ses.addEventListener('change', load);
})();
</script>
@endsection
