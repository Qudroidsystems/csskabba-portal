{{-- resources/views/lms/gradebook.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <x-cb.hero :title="'Gradebook'" icon="ri-bar-chart-box-fill" :subtitle="$course->title"
        :back="route('lms.courses.show', $course)" back-label="Course">
        <x-slot name="actions">
            <a href="{{ route('lms.gradebook.export', $course) }}" class="action-btn btn-go"><i class="ri-download-line"></i>Export CSV</a>
        </x-slot>
    </x-cb.hero>

    <x-cb.card title="Learners" icon="ri-group-line" :count="count($rows)" :flush="true">
        @if(empty($rows))
            <div class="empty-state"><i class="ri-bar-chart-box-line"></i><h6>No learners</h6><p>Enrol students to see grades.</p></div>
        @else
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Student</th><th>Admission</th><th style="width:200px">Progress</th><th class="text-end">Quiz avg</th><th class="text-end">Assignment avg</th><th class="text-end">Overall</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r['name'] }}</td>
                        <td class="small">{{ $r['admissionNo'] ?? '—' }}</td>
                        <td>
                            <div class="progress" style="height:8px"><div class="progress-bar" style="width: {{ $r['progress'] }}%"></div></div>
                            <div class="small text-muted">{{ $r['progress'] }}%</div>
                        </td>
                        <td class="text-end">{{ $r['quiz_avg'] !== null ? $r['quiz_avg'].'%' : '—' }}</td>
                        <td class="text-end">{{ $r['assignment_avg'] !== null ? $r['assignment_avg'].'%' : '—' }}</td>
                        <td class="text-end fw-semibold">{{ $r['overall'] !== null ? $r['overall'].'%' : '—' }}</td>
                        <td><span class="status-pill {{ $r['status']==='completed' ? 'st-paid' : 'st-pending' }}">{{ ucfirst($r['status']) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </x-cb.card>
</div></div></div>
@endsection
