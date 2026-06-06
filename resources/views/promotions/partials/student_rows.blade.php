{{-- resources/views/promotions/partials/student_rows.blade.php --}}

@forelse ($allstudents as $student)
    <tr>
        {{-- Col 1: Checkbox --}}
        <td>
            <input type="checkbox" class="row-checkbox select-all-checkbox" value="{{ $student->stid }}">
        </td>

        {{-- Col 2: Admission No --}}
        <td class="fw-medium">{{ $student->admissionno }}</td>

        {{-- Col 3: Student Name (photo + full name) --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                @if ($student->picture)
                    <img src="{{ asset('storage/student_avatars/' . $student->picture) }}"
                         alt="Student Picture"
                         width="36" height="36"
                         class="rounded-circle flex-shrink-0"
                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                @else
                    <span class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center flex-shrink-0"
                          style="width:36px;height:36px;font-size:13px;color:#fff;">
                        {{ strtoupper(substr($student->firstname, 0, 1)) }}
                    </span>
                @endif
                <span>{{ $student->lastname }}, {{ $student->firstname }} {{ $student->othername ?? '' }}</span>
            </div>
        </td>

        {{-- Col 4: Class --}}
        <td>{{ $student->schoolclass }}</td>

        {{-- Col 5: Arm --}}
        <td>{{ $student->schoolarm ?? '-' }}</td>

        {{-- Col 6: Session --}}
        <td>{{ $student->session }}</td>

        {{-- Col 7: Overall Avg --}}
        <td>
            @if(isset($student->overall_average) && $student->overall_average !== null)
                <span class="fw-semibold">{{ number_format($student->overall_average, 1) }}%</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        {{-- Col 8: Recommendation / Status --}}
        <td>
            @php $status = strtolower($student->promotion_status ?? ''); @endphp
            @if($status === 'promoted')
                <span class="promotion-badge-promoted"><i class="ri-arrow-up-circle-line"></i> Promoted</span>
            @elseif($status === 'trial')
                <span class="promotion-badge-trial"><i class="ri-time-line"></i> On Trial</span>
            @elseif($status === 'see_principal')
                <span class="promotion-badge-see_principal"><i class="ri-eye-line"></i> See Principal</span>
            @elseif(in_array($status, ['repeat', 'repeated']))
                <span class="promotion-badge-repeated"><i class="ri-repeat-line"></i> Repeated</span>
            @else
                <span class="promotion-badge-pending"><i class="ri-question-line"></i> Pending</span>
            @endif
        </td>

        {{-- Col 9: Actions --}}
        <td>
            <div class="d-flex gap-1">
                <button type="button"
                        class="btn btn-icon btn-subtle-primary"
                        title="Manage Promotion"
                        onclick="openPromotionModal(
                            '{{ $student->stid }}',
                            '{{ $student->admissionno }}',
                            '{{ addslashes($student->firstname) }}',
                            '{{ addslashes($student->lastname) }}',
                            '{{ addslashes($student->othername ?? '') }}',
                            '{{ $student->picture }}',
                            '{{ addslashes($student->schoolclass) }}',
                            '{{ addslashes($student->schoolarm ?? '') }}',
                            '{{ $student->session }}',
                            '{{ $student->termid }}'
                        )">
                    <i class="ri-edit-line"></i>
                </button>
                <button type="button"
                        class="btn btn-icon btn-subtle-danger"
                        title="Remove from Class"
                        onclick="removeStudent(
                            '{{ $student->stid }}',
                            {{ $student->schoolclassID }},
                            {{ $student->sessionid }},
                            {{ $student->termid }},
                            '{{ $student->admissionno }}',
                            '{{ addslashes($student->firstname) }}',
                            '{{ addslashes($student->lastname) }}'
                        )">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5">
            <div class="empty-state">
                <i class="ri-inbox-line"></i>
                <p class="mb-0">No students found</p>
            </div>
        </td>
    </tr>
@endforelse
