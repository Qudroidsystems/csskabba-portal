@forelse ($allstudents as $student)
    <tr>
        <td class="id" data-id="{{ $student->stid }}">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="chk_child" value="{{ $student->stid }}">
                <label class="form-check-label"></label>
            </div>
        </td>
        <td>{{ $student->admissionno }}</td>
        <td>
            @if ($student->picture)
                <a href="#" data-bs-toggle="modal" data-bs-target="#imageViewModal"
                   data-image="{{ asset('storage/' . $student->picture) }}">
                    <img src="{{ asset('storage/' . $student->picture) }}"
                         alt="{{ $student->firstname }}"
                         width="50" height="50" class="rounded-circle"
                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                </a>
            @else
                <span class="text-muted">No Picture</span>
            @endif
        </td>
        <td>{{ $student->lastname }}</td>
        <td>{{ $student->firstname }}</td>
        <td>{{ $student->othername ?? '-' }}</td>
        <td>{{ $student->gender }}</td>
        <td>{{ $student->schoolclass }}</td>
        <td>{{ $student->schoolarm }}</td>
        <td>{{ $student->session }}</td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center text-muted">
            Select class and session to view students.
        </td>
    </tr>
@endforelse
