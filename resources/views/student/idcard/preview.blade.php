<div class="p-5 bg-light">
    <div class="d-flex flex-wrap gap-4 justify-content-center">
        @foreach($students as $student)
            @include('student.idcard.card', [
                'student'     => $student,
                'orientation' => $orientation ?? 'portrait',
                'schoolInfo'  => $schoolInfo ?? null
            ])
        @endforeach
    </div>
</div>
