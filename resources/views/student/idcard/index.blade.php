@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="sm-hero">
                <h1><i class="ri-id-card-line me-2"></i>Student ID Card Generator</h1>
                <p>Generate professional, secure student ID cards with QR code and holographic design.</p>
            </div>

            <div class="sm-panel">
                <div class="sm-panel-header">
                    <div class="sm-panel-title">
                        <i class="fas fa-id-card-alt"></i> Select Students
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn-pg" id="previewSelectedBtn">
                            <i class="fas fa-eye me-1"></i> Preview Selected
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="sm-filter">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <input type="text" id="search-input" class="form-control rounded-3" placeholder="Search by name or admission number...">
                        </div>
                        <div class="col-md-3">
                            <select id="class-filter" class="form-select rounded-3">
                                <option value="">All Classes</option>
                                @foreach($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="loadStudentsBtn">Load Students</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <input type="radio" class="btn-check" name="orientation" id="portrait" value="portrait" checked>
                                <label class="btn btn-outline-primary" for="portrait">Portrait</label>
                                <input type="radio" class="btn-check" name="orientation" id="landscape" value="landscape">
                                <label class="btn btn-outline-primary" for="landscape">Landscape</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Grid -->
                <div class="p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <input type="checkbox" id="selectAll" class="form-check-input me-2">
                            <label for="selectAll" class="fw-semibold">Select All</label>
                        </div>
                        <span id="selectedCount" class="text-muted">0 students selected</span>
                    </div>

                    <div class="row g-3" id="studentsGrid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header modal-hdr">
                <h5 class="modal-title">ID Card Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="previewBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn-pg" id="downloadFromPreview">Download PDF</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let selectedIds = new Set();

    function updateSelectedCount() {
        $('#selectedCount').text(`${selectedIds.size} students selected`);
    }

    // Load Students
    $('#loadStudentsBtn').on('click', function() {
        const search = $('#search-input').val();
        const classId = $('#class-filter').val();

        $.get('/student-id-cards/load-students', { search, class_id: classId }, function(res) {
            if (res.success) {
                renderStudents(res.data.data);
            }
        });
    });

    function renderStudents(students) {
        let html = '';
        students.forEach(student => {
            const checked = selectedIds.has(student.id.toString()) ? 'checked' : '';
            const fullname = `${student.firstname} ${student.lastname}`.trim();
            const photo = student.picture ? `/storage/images/student_avatars/${student.picture}` : '{{ asset("theme/layouts/assets/media/avatars/blank.png") }}';

            html += `
            <div class="col-md-3 col-lg-2">
                <div class="stu-card">
                    <div class="form-check position-absolute top-0 start-0 m-2">
                        <input type="checkbox" class="student-check form-check-input" value="${student.id}" ${checked}>
                    </div>
                    <div class="text-center pt-4">
                        <img src="${photo}" class="rounded-circle" style="width:85px;height:85px;object-fit:cover;border:3px solid #2563eb;" alt="">
                    </div>
                    <div class="p-3 text-center">
                        <h6 class="mb-1">${fullname}</h6>
                        <small class="text-muted">${student.admissionNo}</small><br>
                        <small>${student.schoolclass ?? ''} ${student.arm ?? ''}</small>
                    </div>
                </div>
            </div>`;
        });
        $('#studentsGrid').html(html);
    }

    // Selection
    $(document).on('change', '.student-check', function() {
        if (this.checked) selectedIds.add(this.value);
        else selectedIds.delete(this.value);
        updateSelectedCount();
    });

    $('#selectAll').on('change', function() {
        $('.student-check').prop('checked', this.checked);
        if (this.checked) {
            $('.student-check').each(function() { selectedIds.add(this.value); });
        } else {
            selectedIds.clear();
        }
        updateSelectedCount();
    });

    // Preview
    $('#previewSelectedBtn').on('click', function() {
        if (selectedIds.size === 0) {
            alert('Please select at least one student');
            return;
        }

        const orientation = $('input[name="orientation"]:checked').val();

        $.post('/student-id-cards/preview', {
            student_ids: Array.from(selectedIds),
            orientation: orientation
        }, function(res) {
            if (res.success) {
                $('#previewBody').html(res.html);
                new bootstrap.Modal('#previewModal').show();
            }
        });
    });

    $('#downloadFromPreview').on('click', function() {
        const orientation = $('input[name="orientation"]:checked').val();
        window.location.href = `/student-id-cards/download?orientation=${orientation}&student_ids[]=` + Array.from(selectedIds).join('&student_ids[]=');
    });
});
</script>
@endsection
