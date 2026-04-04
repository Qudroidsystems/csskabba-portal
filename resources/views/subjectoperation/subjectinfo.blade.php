{{-- resources/views/subjectoperation/subjectinfo.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="ri-book-open-line me-2"></i>
                            Subject Information for {{ $studentdata->first()->firstname ?? '' }} {{ $studentdata->first()->lastname ?? '' }}
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('subjectoperation.index') }}">Subjects</a></li>
                                <li class="breadcrumb-item active">Subject Information</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Student Profile Card --}}
            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            @php
                                $avatarPath = !empty($studentpic->first()->avatar)
                                    ? asset('storage/student_avatars/' . $studentpic->first()->avatar)
                                    : asset('storage/student_avatars/unnamed.jpg');
                            @endphp
                            <img src="{{ $avatarPath }}"
                                 class="rounded-circle img-thumbnail mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                            <h4 class="mb-1">{{ $studentdata->first()->firstname ?? '' }} {{ $studentdata->first()->lastname ?? '' }}</h4>
                            <p class="text-muted">
                                <i class="ri-id-card-line me-1"></i>
                                Admission No: <strong>{{ $studentdata->first()->admissionno ?? 'N/A' }}</strong>
                            </p>
                            <p class="text-muted">
                                <i class="ri-gender-line me-1"></i>
                                Gender: <strong>{{ $studentdata->first()->gender ?? 'N/A' }}</strong>
                            </p>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <div class="border rounded-3 p-2">
                                        <h5 class="text-success mb-0">{{ $regcount ?? 0 }}</h5>
                                        <small class="text-muted">Registered</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2">
                                        <h5 class="text-danger mb-0">{{ $noregcount ?? 0 }}</h5>
                                        <small class="text-muted">Unregistered</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="card-title text-white mb-0">
                                <i class="ri-graduation-cap-line me-2"></i>
                                Subjects for
                                @if($classname->isNotEmpty())
                                    {{ $classname->first()->schoolclass ?? '' }} {{ $classname->first()->arm ?? '' }}
                                @else
                                    Unknown Class
                                @endif
                                <span class="badge bg-light text-dark ms-2">
                                    Term: {{ $subjectclass->isNotEmpty() ? $subjectclass->first()->term : 'N/A' }}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="ri-book-line me-1"></i> Subject</th>
                                            <th><i class="ri-user-star-line me-1"></i> Teacher</th>
                                            <th><i class="ri-checkbox-circle-line me-1"></i> Status</th>
                                            <th><i class="ri-calendar-line me-1"></i> Term</th>
                                            <th><i class="ri-calendar-event-line me-1"></i> Session</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($subjectclass as $sc)
                                            <tr class="subject-class-row"
                                                data-subjectclassid="{{ $sc->subjectclassid ?? '' }}"
                                                data-staffid="{{ $sc->staffid ?? '' }}">
                                                <td class="fw-medium">
                                                    <i class="ri-book-2-line text-primary me-2"></i>
                                                    {{ $sc->subject ?? 'N/A' }}
                                                    <br>
                                                    <small class="text-muted">{{ $sc->subjectcode ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        @php
                                                            $teacherPic = !empty($sc->picture)
                                                                ? asset('storage/staff_avatars/' . $sc->picture)
                                                                : asset('storage/staff_avatars/default.png');
                                                        @endphp
                                                        <img src="{{ $teacherPic }}"
                                                             class="rounded-circle"
                                                             style="width: 35px; height: 35px; object-fit: cover;"
                                                             onerror="this.src='{{ asset('storage/staff_avatars/default.png') }}'">
                                                        <div>
                                                            <div class="fw-medium">{{ $sc->title ?? '' }} {{ $sc->name ?? '' }}</div>
                                                            <small class="text-muted">Teacher</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = isset($subjectRegistrations[$sc->subjectid][$sc->staffid]['status']['status'])
                                                            ? $subjectRegistrations[$sc->subjectid][$sc->staffid]['status']['status']
                                                            : 'Not Registered';
                                                    @endphp
                                                    @if($status === 'Registered')
                                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                                            <i class="ri-check-line me-1"></i> {{ $status }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                                            <i class="ri-close-line me-1"></i> {{ $status }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info">{{ $sc->term ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary">{{ $sc->session ?? 'N/A' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="ri-information-line ri-2x mb-2 d-block"></i>
                                                    No subjects found for this class, term, and session.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Summary Statistics --}}
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary bg-opacity-10 border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-primary mb-0">{{ $totalreg ?? 0 }}</h3>
                                            <small class="text-muted">Total Subjects</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success bg-opacity-10 border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-success mb-0">{{ $regcount ?? 0 }}</h3>
                                            <small class="text-muted">Registered Subjects</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-danger bg-opacity-10 border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-danger mb-0">{{ $noregcount ?? 0 }}</h3>
                                            <small class="text-muted">Unregistered Subjects</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            @php
                                $percentage = $totalreg > 0 ? ($regcount / $totalreg) * 100 : 0;
                            @endphp
                            <div class="mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Registration Progress</small>
                                    <small>{{ number_format($percentage, 1) }}%</small>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $percentage }}%;"
                                         role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    // Add any additional JavaScript for the subject info page if needed
    document.addEventListener('DOMContentLoaded', function() {
        // You can add row click handlers or other interactions here
        const rows = document.querySelectorAll('.subject-class-row');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                // Optional: Add functionality when clicking on a subject row
                console.log('Subject clicked:', this.dataset.subjectclassid);
            });
        });
    });
</script>

