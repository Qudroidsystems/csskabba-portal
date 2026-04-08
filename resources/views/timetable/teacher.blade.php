{{-- resources/views/timetable/teacher.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="ri-calendar-todo-line me-2"></i>{{ $pagetitle }}
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">My Timetable</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Session & Term Filter --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Session</label>
                                    <select class="form-select" id="sessionFilter" onchange="filterTimetable()">
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}" {{ $sessionId == $session->id ? 'selected' : '' }}>
                                                {{ $session->session }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Term</label>
                                    <select class="form-select" id="termFilter" onchange="filterTimetable()">
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>
                                                {{ $term->term }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100" onclick="filterTimetable()">
                                        <i class="ri-refresh-line me-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Teacher Profile Card --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-4">
                                <div class="flex-shrink-0">
                                    @if($teacherPicture)
                                        <img src="{{ $teacherPicture }}" class="rounded-circle border border-3 border-white"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                                             style="width: 80px; height: 80px;">
                                            <i class="ri-user-line ri-3x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-1">{{ Auth::user()->name }}</h3>
                                    <p class="mb-0 opacity-75">
                                        <i class="ri-mail-line me-2"></i>{{ Auth::user()->email }}
                                    </p>
                                </div>
                                <div>
                                    <button class="btn btn-light" onclick="window.print()">
                                        <i class="ri-printer-line me-2"></i>Print Timetable
                                    </button>
                                    <button class="btn btn-outline-light ms-2" data-bs-toggle="modal" data-bs-target="#requestSubstituteModal">
                                        <i class="ri-user-star-line me-2"></i>Request Substitute
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Classes Alert --}}
            @if(count($upcomingSlots) > 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="ri-information-line me-2"></i>
                        <strong>Upcoming Classes:</strong>
                        @foreach($upcomingSlots as $slot)
                            <span class="badge bg-primary me-2">
                                {{ $slot['day'] }} • {{ $slot['period'] }} • {{ $slot['subject'] }} ({{ $slot['class'] }})
                                @if($slot['room']) <i class="ri-door-line ms-1"></i> {{ $slot['room'] }} @endif
                            </span>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Weekly Summary Cards --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-bar-chart-2-line me-2"></i>Weekly Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($weeklySummary as $day => $summary)
                                <div class="col-md-2">
                                    <div class="card text-center border">
                                        <div class="card-body py-3">
                                            <h6 class="mb-2">{{ $day }}</h6>
                                            <h3 class="mb-2 text-primary">{{ $summary['count'] }}</h3>
                                            <small class="text-muted">classes</small>
                                            <div class="mt-2">
                                                @foreach(array_slice($summary['subjects'], 0, 2) as $subject)
                                                    <span class="badge bg-light text-dark me-1">{{ Str::limit($subject, 10) }}</span>
                                                @endforeach
                                                @if(count($summary['subjects']) > 2)
                                                    <span class="badge bg-light">+{{ count($summary['subjects']) - 2 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timetable Grid --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-table-line me-2"></i>My Timetable
                            </h5>
                            <div class="text-muted">
                                <small><i class="ri-information-line me-1"></i> Hover over any class to see details</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered teacher-timetable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 120px">Period / Time</th>
                                            @foreach($days as $day)
                                                <th class="text-center">{{ $day }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allPeriods as $period)
                                        <tr data-period-id="{{ $period->id }}">
                                            <td class="fw-semibold" style="background: #f8f9fa;">
                                                <div>{{ $period->name }}</div>
                                                <small class="text-muted">{{ $period->start_time }} - {{ $period->end_time }}</small>
                                                @if($period->is_break)
                                                    <span class="badge bg-warning ms-1">Break</span>
                                                @endif
                                            </td>
                                            @foreach($days as $day)
                                                @php
                                                    $slot = $slots[$day] ?? collect();
                                                    $currentSlot = $slot->firstWhere('period_id', $period->id);
                                                    $hasClass = $currentSlot && !$currentSlot->is_free && $currentSlot->subject;
                                                @endphp
                                                <td class="timetable-cell text-center {{ $period->is_break ? 'bg-light' : '' }}"
                                                    @if($hasClass)
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        title="<div class='text-start'><strong>{{ $currentSlot->subject->subject ?? '' }}</strong><br>
                                                        <small>Class: {{ $currentSlot->setting->schoolclass->schoolclass ?? '' }}</small><br>
                                                        @if($currentSlot->room)<small>Room: {{ $currentSlot->room }}</small><br>@endif
                                                        @if($currentSlot->notes)<small>Note: {{ Str::limit($currentSlot->notes, 50) }}</small>@endif</div>"
                                                    @endif
                                                >
                                                    @if($hasClass)
                                                        <div class="py-2">
                                                            <span class="fw-semibold d-block">{{ $currentSlot->subject->subject ?? 'N/A' }}</span>
                                                            <small class="text-muted">{{ $currentSlot->setting->schoolclass->schoolclass ?? '' }}</small>
                                                            @if($currentSlot->room)
                                                                <small class="text-muted d-block">
                                                                    <i class="ri-door-line"></i> {{ $currentSlot->room }}
                                                                </small>
                                                            @endif
                                                            @if($currentSlot->is_double)
                                                                <span class="badge bg-primary mt-1">Double</span>
                                                            @endif
                                                        </div>
                                                    @elseif($period->is_break)
                                                        <span class="text-muted">
                                                            <i class="ri-coffee-line"></i> Break
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-4 flex-wrap">
                                <div><i class="ri-checkbox-blank-circle-fill text-success me-1"></i> Regular Class</div>
                                <div><i class="ri-checkbox-blank-circle-fill text-primary me-1"></i> Double Period</div>
                                <div><i class="ri-checkbox-blank-circle-fill text-muted me-1"></i> Free Period</div>
                                <div><i class="ri-coffee-line me-1"></i> Break Time</div>
                                <div><i class="ri-door-line me-1"></i> Room/Venue</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Request Substitute Modal --}}
<div class="modal fade" id="requestSubstituteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">
                    <i class="ri-user-star-line me-2"></i>Request Substitute Teacher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Class/Slot</label>
                    <select class="form-select" id="substituteSlotId">
                        <option value="">-- Select a class --</option>
                        @foreach($slots as $day => $daySlots)
                            @foreach($daySlots as $slot)
                                @if($slot->subject && !$slot->is_free)
                                    <option value="{{ $slot->id }}">
                                        {{ $slot->day }} - {{ $slot->period->name }}: {{ $slot->subject->subject }} ({{ $slot->setting->schoolclass->schoolclass }})
                                    </option>
                                @endif
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Substitute Teacher</label>
                    <select class="form-select" id="substituteTeacherId">
                        <option value="">-- Select substitute --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assignment Date</label>
                    <input type="date" class="form-control" id="substituteDate" min="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea class="form-control" id="substituteReason" rows="3" placeholder="Why do you need a substitute?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="requestSubstitute()">
                    <i class="ri-send-plane-line me-2"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    function filterTimetable() {
        const sessionId = document.getElementById('sessionFilter').value;
        const termId = document.getElementById('termFilter').value;

        let url = '{{ route("timetable.teacher") }}';
        const params = new URLSearchParams();
        if (sessionId) params.append('session_id', sessionId);
        if (termId) params.append('term_id', termId);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.location.href = url;
    }

    // Load substitute teachers when slot is selected
    document.getElementById('substituteSlotId')?.addEventListener('change', function() {
        const slotId = this.value;
        if (!slotId) return;

        // Fetch available substitute teachers for this slot
        fetch(`/api/timetable/available-substitutes?slot_id=${slotId}`)
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('substituteTeacherId');
                select.innerHTML = '<option value="">-- Select substitute --</option>';
                data.teachers.forEach(teacher => {
                    select.innerHTML += `<option value="${teacher.id}">${teacher.name}</option>`;
                });
            });
    });

    function requestSubstitute() {
        const slotId = document.getElementById('substituteSlotId').value;
        const substituteTeacherId = document.getElementById('substituteTeacherId').value;
        const assignmentDate = document.getElementById('substituteDate').value;
        const reason = document.getElementById('substituteReason').value;

        if (!slotId || !substituteTeacherId || !assignmentDate || !reason) {
            Swal.fire('Error', 'Please fill all fields', 'error');
            return;
        }

        fetch('{{ route("timetable.request-substitute") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                slot_id: slotId,
                substitute_teacher_id: substituteTeacherId,
                assignment_date: assignmentDate,
                reason: reason
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', 'Substitute request submitted successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('requestSubstituteModal')).hide();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Failed to submit request', 'error');
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>

<style>
    .teacher-timetable td {
        vertical-align: middle;
        transition: all 0.2s ease;
    }
    .teacher-timetable .timetable-cell {
        cursor: default;
    }
    .teacher-timetable .timetable-cell:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    @media print {
        .page-title-box, .card-header, .alert, .btn, .breadcrumb, .navbar, .sidebar {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        body {
            background: white !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
    }
</style>
@endsection
