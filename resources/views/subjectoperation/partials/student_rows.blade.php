{{--
===========================================================================
  resources/views/subjectoperation/partials/student_rows.blade.php
  Replace the ENTIRE content of this partial with the markup below.
  The CSS lives in the parent index.blade.php <style> block (sop-* classes).
===========================================================================
--}}

@php
    $avatarColors = [
        ['#E6F1FB','#0C447C'],
        ['#EAF3DE','#27500A'],
        ['#FAEEDA','#633806'],
        ['#EEEDFE','#3C3489'],
        ['#FBEAF0','#993556'],
        ['#E1F5EE','#085041'],
    ];
    $rowIndex = 0;
@endphp

@forelse ($students ?? [] as $student)
    @php
        $fullName  = trim($student->lastname . ' ' . $student->firstname . ' ' . ($student->othername ?? ''));
        $words     = array_filter(explode(' ', $fullName));
        $initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
        $colorPair = $avatarColors[($student->id - 1) % count($avatarColors)];
        $isFemale  = $student->gender === 'Female';
        $delay     = $rowIndex * 35;
        $rowIndex++;

        // If your model has a registration status field, use it; default true
        $isRegistered = $student->is_registered ?? true;
    @endphp

    <div class="sop-student-row{{ $student->id ? '' : '' }}"
         style="animation-delay: {{ $delay }}ms;"
         data-id="{{ $student->id }}"
         data-name="{{ $fullName }}"
         data-admno="{{ $student->admissionno ?? '' }}"
         onclick="sopToggleRow({{ $student->id }}, event)">

        {{-- Checkbox --}}
        <div class="sop-chk-wrap">
            <input type="checkbox"
                   class="sop-chk"
                   name="chk_child"
                   data-id="{{ $student->id }}"
                   data-name="{{ $fullName }}"
                   data-initials="{{ $initials }}"
                   data-color-bg="{{ $colorPair[0] }}"
                   data-color-fg="{{ $colorPair[1] }}"
                   onclick="event.stopPropagation()">
        </div>

        {{-- Student info --}}
        <div class="sop-student-info">
            @if($student->picture && file_exists(storage_path('app/public/student_avatars/' . basename($student->picture))))
                <img src="{{ asset('storage/student_avatars/' . basename($student->picture)) }}"
                     class="sop-avatar-img"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                     alt="{{ $fullName }}">
                <div class="sop-avatar" style="background:{{ $colorPair[0] }};color:{{ $colorPair[1] }};display:none;">{{ $initials }}</div>
            @else
                <div class="sop-avatar" style="background:{{ $colorPair[0] }};color:{{ $colorPair[1] }};">{{ $initials }}</div>
            @endif
            <div>
                <div class="sop-name fw-semibold">{{ $fullName }}</div>
                <div class="sop-adm admissionno" data-admissionno="{{ $student->admissionno ?? '' }}">
                    {{ $student->admissionno ?? '—' }}
                </div>
            </div>
        </div>

        {{-- Adm No (hidden on mobile, shown by table head) --}}
        <div class="sop-td d-none d-md-block">{{ $student->admissionno ?? '—' }}</div>

        {{-- Class --}}
        <div class="sop-td d-none d-sm-block">
            {{ ($student->schoolclass ?? '') . ' ' . ($student->schoolarm ?? '') }}
        </div>

        {{-- Gender --}}
        <div>
            <span class="sop-badge {{ $isFemale ? 'sop-badge-f' : 'sop-badge-m' }}">
                {{ $student->gender ?? '—' }}
            </span>
        </div>

        {{-- Action --}}
        <div>
            <span class="sop-badge" style="background:{{ $isRegistered ? '#EAF3DE' : '#FCEBEB' }};color:{{ $isRegistered ? '#27500A' : '#A32D2D' }}">
                {{ $isRegistered ? 'Registered' : 'Unregistered' }}
            </span>
        </div>

        {{-- Hidden id cell for JS back-compat --}}
        <span class="id d-none" data-id="{{ $student->id }}"></span>

    </div>

@empty
    <div class="sop-no-result">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.3-4.3M8 11h6M11 8v6"/>
        </svg>
        <span>No students found. Try adjusting your filters.</span>
    </div>
@endforelse
