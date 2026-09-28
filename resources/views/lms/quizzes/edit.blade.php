{{-- resources/views/lms/quizzes/edit.blade.php — quiz settings + question builder --}}
@extends('layouts.master')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <x-cb.hero :title="$quiz->title" icon="ri-questionnaire-fill"
        :subtitle="'Quiz builder · '.$quiz->questions->count().' questions · '.$quiz->totalPoints().' pts'"
        :back="route('lms.courses.show', $course)" back-label="Course">
        <x-slot name="actions">
            <a href="{{ route('lms.quizzes.results', [$course, $quiz]) }}" class="action-btn btn-go"><i class="ri-bar-chart-line"></i>Results</a>
        </x-slot>
    </x-cb.hero>

    @if(session('success'))<div class="cb-banner info"><i class="ri-checkbox-circle-line"></i><div>{{ session('success') }}</div></div>@endif
    @if($errors->any())<div class="cb-banner warning"><i class="ri-error-warning-line"></i><div>{{ $errors->first() }}</div></div>@endif

    <div class="row g-3">
        <div class="col-lg-4">
            <x-cb.card title="Settings" icon="ri-settings-3-line">
                <form method="POST" action="{{ route('lms.quizzes.update', [$course, $quiz]) }}">@csrf @method('PUT')
                    <label class="form-label small">Title *</label><input name="title" value="{{ $quiz->title }}" class="form-control mb-2" required>
                    <label class="form-label small">Description</label><textarea name="description" rows="2" class="form-control mb-2">{{ $quiz->description }}</textarea>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label small">Pass %</label><input type="number" min="0" max="100" name="pass_mark" value="{{ $quiz->pass_mark }}" class="form-control"></div>
                        <div class="col-6"><label class="form-label small">Max attempts</label><input type="number" min="0" name="max_attempts" value="{{ $quiz->max_attempts }}" class="form-control"></div>
                        <div class="col-6"><label class="form-label small">Time limit (min)</label><input type="number" min="0" name="time_limit_minutes" value="{{ $quiz->time_limit_minutes }}" class="form-control"></div>
                        <div class="col-6"><label class="form-label small">Lesson</label>
                            <select name="lesson_id" class="form-select"><option value="">—</option>@foreach($course->lessons as $l)<option value="{{ $l->id }}" @selected($quiz->lesson_id==$l->id)>{{ $l->title }}</option>@endforeach</select></div>
                    </div>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check form-switch"><input type="hidden" name="shuffle" value="0"><input class="form-check-input" type="checkbox" name="shuffle" value="1" id="qs" @checked($quiz->shuffle)><label class="form-check-label small" for="qs">Shuffle</label></div>
                        <div class="form-check form-switch"><input type="hidden" name="is_published" value="0"><input class="form-check-input" type="checkbox" name="is_published" value="1" id="qp" @checked($quiz->is_published)><label class="form-check-label small" for="qp">Published</label></div>
                    </div>
                    <button class="action-btn btn-primary-cb w-100 justify-content-center mt-3"><i class="ri-save-line"></i>Save settings</button>
                </form>
            </x-cb.card>
        </div>

        <div class="col-lg-8">
            <x-cb.card title="Questions" icon="ri-list-ordered" :count="$quiz->questions->count()">
                @forelse($quiz->questions as $i => $qn)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <div><span class="badge bg-secondary">Q{{ $i+1 }}</span> <strong>{{ $qn->question }}</strong>
                                <span class="text-muted small">· {{ ucfirst($qn->type) }} · {{ $qn->points }} pt</span></div>
                            <div class="d-flex gap-1">
                                <button class="action-btn btn-open" title="Edit" onclick="lmsEditQuestion(this)"
                                    data-id="{{ $qn->id }}"
                                    data-question="{{ e($qn->question) }}"
                                    data-type="{{ $qn->type }}"
                                    data-points="{{ $qn->points }}"
                                    data-options="{{ e(json_encode($qn->options ?? [])) }}"
                                    data-correct="{{ e(json_encode($qn->correct ?? [])) }}"><i class="ri-edit-line"></i></button>
                                <form method="POST" action="{{ route('lms.questions.destroy', [$course, $quiz, $qn]) }}" onsubmit="return confirm('Remove question?')">@csrf @method('DELETE')<button class="action-btn btn-open"><i class="ri-delete-bin-line"></i></button></form>
                            </div>
                        </div>
                        <ul class="mb-0 mt-1 small">
                            @foreach(($qn->options ?? []) as $oi => $opt)
                                <li class="{{ in_array($oi, $qn->correct ?? []) ? 'text-success fw-semibold' : '' }}">{{ $opt }} @if(in_array($oi, $qn->correct ?? []))<i class="ri-check-line"></i>@endif</li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <div class="empty-state"><i class="ri-list-ordered"></i><p>No questions yet.</p></div>
                @endforelse

                <hr>
                <h6 class="mb-2"><i class="ri-add-line"></i> <span id="qFormTitle">Add question</span></h6>
                <form method="POST" action="{{ route('lms.questions.store', [$course, $quiz]) }}" id="qForm"
                      data-store="{{ route('lms.questions.store', [$course, $quiz]) }}"
                      data-update="{{ route('lms.questions.update', [$course, $quiz, 0]) }}"
                      onsubmit="return lmsRenumber()">@csrf
                    <input type="hidden" name="_method" id="qMethod" value="POST">
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-8"><label class="form-label small">Question *</label><textarea name="question" rows="2" class="form-control" required></textarea></div>
                        <div class="col-md-2"><label class="form-label small">Type</label>
                            <select name="type" id="qType" class="form-select" onchange="lmsQType()"><option value="single">Single</option><option value="multiple">Multiple</option><option value="boolean">True/False</option></select></div>
                        <div class="col-md-2"><label class="form-label small">Points</label><input type="number" min="1" name="points" value="1" class="form-control"></div>
                    </div>
                    <div id="optWrap">
                        <label class="form-label small">Options (tick the correct one/s)</label>
                        <div id="optRows"></div>
                        <button type="button" class="action-btn btn-open mt-1" onclick="lmsAddOpt()"><i class="ri-add-line"></i>Add option</button>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="action-btn btn-primary-cb" id="qSubmit"><i class="ri-add-circle-line"></i>Add question</button>
                        <button type="button" class="action-btn btn-open d-none" id="qCancel" onclick="lmsResetQuestionForm()"><i class="ri-close-line"></i>Cancel edit</button>
                    </div>
                </form>
            </x-cb.card>
        </div>
    </div>
</div></div></div>

<script>
function lmsEsc(v){ return String(v==null?'':v).replace(/"/g,'&quot;'); }
function lmsOptRow(val, checked){
    var wrap = document.getElementById('optRows');
    var row = document.createElement('div');
    row.className = 'input-group input-group-sm mb-1 optrow';
    row.innerHTML = '<span class="input-group-text"><input type="checkbox" class="optcheck"'+(checked?' checked':'')+'></span>'
        + '<input type="text" class="form-control opttext" placeholder="Option text" value="'+lmsEsc(val)+'">'
        + '<button type="button" class="btn btn-outline-danger" onclick="this.closest(\'.optrow\').remove()"><i class="ri-close-line"></i></button>';
    wrap.appendChild(row);
}
function lmsResetQuestionForm(){
    var f=document.getElementById('qForm');
    f.action=f.getAttribute('data-store');
    document.getElementById('qMethod').value='POST';
    document.getElementById('qFormTitle').textContent='Add question';
    document.getElementById('qSubmit').innerHTML='<i class="ri-add-circle-line"></i>Add question';
    document.getElementById('qCancel').classList.add('d-none');
    f.querySelector('[name=question]').value='';
    document.getElementById('qType').value='single';
    f.querySelector('[name=points]').value='1';
    document.getElementById('optRows').innerHTML='';
    lmsOptRow(''); lmsOptRow('');
}
function lmsEditQuestion(btn){
    var g=function(a){return btn.getAttribute(a);};
    var f=document.getElementById('qForm');
    f.action=f.getAttribute('data-update').replace(/0$/, g('data-id'));
    document.getElementById('qMethod').value='PUT';
    document.getElementById('qFormTitle').textContent='Edit question';
    document.getElementById('qSubmit').innerHTML='<i class="ri-save-line"></i>Save question';
    document.getElementById('qCancel').classList.remove('d-none');
    f.querySelector('[name=question]').value=g('data-question')||'';
    document.getElementById('qType').value=g('data-type')||'single';
    f.querySelector('[name=points]').value=g('data-points')||'1';
    var opts=[], corr=[];
    try{opts=JSON.parse(g('data-options')||'[]');}catch(e){}
    try{corr=JSON.parse(g('data-correct')||'[]');}catch(e){}
    var wrap=document.getElementById('optRows'); wrap.innerHTML='';
    opts.forEach(function(o,i){ lmsOptRow(o, corr.indexOf(i)!==-1); });
    if(!opts.length){ lmsOptRow(''); lmsOptRow(''); }
    f.scrollIntoView({behavior:'smooth', block:'center'});
}
function lmsAddOpt(){ lmsQType(); if(document.getElementById('qType').value!=='boolean') lmsOptRow(''); }
function lmsQType(){
    var t = document.getElementById('qType').value;
    var wrap = document.getElementById('optRows');
    if(t==='boolean'){
        wrap.innerHTML='';
        lmsOptRow('True'); lmsOptRow('False');
        // make checks act as single-choice
    }
}
// Renumber options[] and correct[] contiguously on submit.
function lmsRenumber(){
    var rows = document.querySelectorAll('#optRows .optrow');
    if(rows.length < 2){ alert('Add at least two options.'); return false; }
    var form = document.getElementById('qForm');
    // clear old generated hidden inputs
    form.querySelectorAll('.gen').forEach(function(e){ e.remove(); });
    var correctCount = 0;
    rows.forEach(function(row, i){
        var text = row.querySelector('.opttext').value;
        var hi = document.createElement('input'); hi.type='hidden'; hi.name='options['+i+']'; hi.value=text; hi.className='gen'; form.appendChild(hi);
        if(row.querySelector('.optcheck').checked){
            var hc = document.createElement('input'); hc.type='hidden'; hc.name='correct[]'; hc.value=i; hc.className='gen'; form.appendChild(hc);
            correctCount++;
        }
    });
    if(correctCount<1){ alert('Tick at least one correct answer.'); return false; }
    return true;
}
document.addEventListener('DOMContentLoaded', function(){ lmsOptRow(''); lmsOptRow(''); });
</script>
@endsection
