{{-- resources/views/lms/enrollments/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <x-cb.hero :title="'Learners'" icon="ri-group-fill" :subtitle="$course->title"
        :back="route('lms.courses.show', $course)" back-label="Course">
        <x-slot name="actions">
            <form method="POST" action="{{ route('lms.enrollments.sync', $course) }}" class="d-inline" onsubmit="return confirm('Enrol all {{ $eligible }} students of this class?')">@csrf
                <button class="action-btn btn-go"><i class="ri-refresh-line"></i>Auto-enrol class ({{ $eligible }})</button>
            </form>
            <button class="action-btn btn-primary-cb" data-bs-toggle="modal" data-bs-target="#addModal"><i class="ri-user-add-line"></i>Add students</button>
        </x-slot>
    </x-cb.hero>

    @if(session('success'))<div class="cb-banner info"><i class="ri-checkbox-circle-line"></i><div>{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="cb-banner warning"><i class="ri-error-warning-line"></i><div>{{ session('error') }}</div></div>@endif

    <x-cb.card title="Enrolled" icon="ri-group-line" :count="$rows->total()" :flush="true">
        <div class="p-3">
            <form method="GET" class="row g-2"><div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name / admission no"></div><div class="col-md-2"><button class="action-btn btn-open w-100 justify-content-center"><i class="ri-search-line"></i></button></div></form>
        </div>
        @if($rows->isEmpty())
            <div class="empty-state"><i class="ri-group-line"></i><h6>No learners</h6><p>Auto-enrol the class or add students manually.</p></div>
        @else
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Student</th><th>Admission</th><th>Source</th><th style="width:180px">Progress</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ trim(($r->firstname ?? '').' '.($r->lastname ?? '')) }}</td>
                        <td class="small">{{ $r->admissionNo ?? '—' }}</td>
                        <td><span class="status-pill {{ $r->source==='auto' ? 'st-info' : 'st-muted' }}">{{ ucfirst($r->source) }}</span></td>
                        <td>
                            <div class="progress" style="height:8px"><div class="progress-bar" style="width: {{ (int)$r->progress_percent }}%"></div></div>
                            <div class="small text-muted">{{ (int)$r->progress_percent }}%</div>
                        </td>
                        <td><span class="status-pill {{ $r->status==='completed' ? 'st-paid' : ($r->status==='dropped' ? 'st-danger' : 'st-pending') }}">{{ ucfirst($r->status) }}</span></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('lms.enrollments.destroy', [$course, $r->student_id]) }}" onsubmit="return confirm('Remove this learner and their progress?')">@csrf @method('DELETE')<button class="action-btn btn-open" title="Remove"><i class="ri-user-unfollow-line"></i></button></form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </x-cb.card>
    @if($rows->hasPages())<div class="mt-3">{{ $rows->links() }}</div>@endif
</div></div></div>

{{-- Add students modal --}}
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" action="{{ route('lms.enrollments.store', $course) }}">@csrf
        <div class="modal-header"><h5 class="modal-title">Add students</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="d-flex gap-2 mb-2">
                <input id="candSearch" class="form-control" placeholder="Search students not yet enrolled">
                <button type="button" class="action-btn btn-open" onclick="lmsLoadCandidates()"><i class="ri-search-line"></i></button>
            </div>
            <div id="candList" class="border rounded p-2" style="max-height:340px;overflow:auto">
                <div class="text-muted small">Search to list students of this class who are not yet enrolled.</div>
            </div>
        </div>
        <div class="modal-footer"><button class="action-btn btn-primary-cb"><i class="ri-user-add-line"></i>Enrol selected</button></div>
    </form>
</div></div></div>

<script>
function lmsLoadCandidates(){
    var q = document.getElementById('candSearch').value;
    var url = "{{ route('lms.enrollments.candidates', $course) }}" + "?q=" + encodeURIComponent(q);
    fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(res){
        var box = document.getElementById('candList');
        if(!res.data || !res.data.length){ box.innerHTML='<div class="text-muted small">No matching students.</div>'; return; }
        box.innerHTML = res.data.map(function(s){
            return '<div class="form-check"><input class="form-check-input" type="checkbox" name="student_ids[]" value="'+s.id+'" id="cand'+s.id+'"><label class="form-check-label" for="cand'+s.id+'">'+s.name+' <span class="text-muted small">'+(s.admissionNo||'')+'</span></label></div>';
        }).join('');
    });
}
document.getElementById('candSearch').addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); lmsLoadCandidates(); }});
</script>
@endsection
