{{-- resources/views/lms/learn/quiz-result.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <x-cb.hero :title="'Quiz result'" icon="ri-award-fill" :subtitle="$quiz->title"
        :back="route('lms.learn.show', $course)" back-label="Course" />

    <x-cb.card>
        <div class="text-center py-3">
            <div class="display-4 fw-bold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">{{ $attempt->percent }}%</div>
            <div class="mb-2">{{ rtrim(rtrim(number_format($attempt->score,2),'0'),'.') }} / {{ rtrim(rtrim(number_format($attempt->max_score,2),'0'),'.') }} points</div>
            <span class="status-pill {{ $attempt->passed ? 'st-paid' : 'st-danger' }}">{{ $attempt->passed ? 'Passed' : 'Not passed' }}</span>
        </div>
    </x-cb.card>

    <x-cb.card title="Review" icon="ri-file-list-3-line">
        @php $answers = $attempt->answers ?? []; @endphp
        @foreach($questions as $i => $qn)
            @php $sel = array_map('intval', (array)($answers[$qn->id] ?? [])); $right = $qn->isCorrect($sel); @endphp
            <div class="border rounded p-2 mb-2">
                <div class="fw-semibold mb-1">
                    <i class="ri-{{ $right ? 'checkbox-circle-fill text-success' : 'close-circle-fill text-danger' }}"></i>
                    Q{{ $i+1 }}. {{ $qn->question }}
                </div>
                <ul class="mb-0 small">
                    @foreach(($qn->options ?? []) as $oi => $opt)
                        @php $isKey = in_array($oi, $qn->correct ?? []); $isSel = in_array($oi, $sel); @endphp
                        <li class="{{ $isKey ? 'text-success fw-semibold' : ($isSel ? 'text-danger' : '') }}">
                            {{ $opt }}
                            @if($isKey)<i class="ri-check-line"></i>@endif
                            @if($isSel && !$isKey)<i class="ri-close-line"></i> (your answer)@endif
                            @if($isSel && $isKey)(your answer)@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </x-cb.card>

    <a href="{{ route('lms.learn.show', $course) }}" class="action-btn btn-primary-cb"><i class="ri-arrow-left-line"></i>Back to course</a>
</div></div></div>
@endsection
