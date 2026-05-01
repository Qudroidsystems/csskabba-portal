<div class="p-4" style="background: #f8fafc;">
    <div class="d-flex flex-wrap gap-4 justify-content-center">
        @foreach($students as $student)
            @include('student.idcard.card', ['student' => $student, 'orientation' => $orientation ?? 'portrait'])
        @endforeach
    </div>
</div>
