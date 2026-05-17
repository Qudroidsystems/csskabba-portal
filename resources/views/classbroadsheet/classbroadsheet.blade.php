@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --ts-primary: #1e3a5f;
    --ts-accent:  #2563eb;
    --ts-success: #16a34a;
    --ts-warning: #d97706;
    --ts-danger:  #dc2626;
    --ts-muted:   #6b7280;
    --ts-border:  #e2e8f0;
    --ts-bg:      #f8fafc;
    --ts-radius:  12px;
    --ts-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ──────────────────────────────────────────────── */
.ts-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 60%, #0891b2 100%);
    border-radius: var(--ts-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.ts-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.ts-hero::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -30px;
    width: 260px;
    height: 260px;
    background: rgba(255,255,255,.03);
    border-radius: 50%;
}
.ts-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.ts-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

/* ── Stat cards ────────────────────────────────────────── */
.stat-card {
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--ts-shadow);
}
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--ts-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: var(--ts-muted);
    margin-top: 4px;
}
.stat-card .stat-icon {
    font-size: 32px;
    opacity: .12;
    float: right;
    margin-top: -8px;
}

/* ── Table ─────────────────────────────────────────────── */
.ts-table th {
    background: var(--ts-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.ts-table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--ts-border);
    font-size: 13px;
}
.ts-table tr:hover td {
    background: #f0fdfa;
}

/* ── Badges ────────────────────────────────────────────── */
.ts-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.ts-badge-term {
    background: #dbeafe;
    color: #2563eb;
}
.ts-badge-session {
    background: #ccfbf1;
    color: #0f766e;
}
.ts-badge-class {
    background: #fef3c7;
    color: #d97706;
}
.ts-badge-score {
    background: #dcfce7;
    color: #16a34a;
}
.ts-badge-score-low {
    background: #fee2e2;
    color: #dc2626;
}

/* ── DataTables overrides ──────────────────────────────── */
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--ts-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
    transition: border .15s;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--ts-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid var(--ts-border);
    border-radius: 8px;
    padding: 6px 10px;
    margin: 0 6px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_info {
    font-size: 13px;
    color: var(--ts-muted);
}
.dataTables_wrapper .paginate_button {
    border-radius: 6px !important;
    font-size: 13px !important;
    padding: 4px 10px !important;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background: var(--ts-accent) !important;
    border-color: var(--ts-accent) !important;
    color: #fff !important;
}

/* ── Form inputs ───────────────────────────────────────── */
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.form-control, .form-select {
    border: 1.5px solid var(--ts-border);
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 14px;
    transition: border .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--ts-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* ── Avatar styles ─────────────────────────────────────── */
.student-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--ts-border);
    cursor: pointer;
    transition: border-color .15s;
}
.student-avatar:hover {
    border-color: var(--ts-accent);
}

/* ── Search box ────────────────────────────────────────── */
.search-box {
    position: relative;
    margin-bottom: 20px;
}
.search-box input {
    padding-left: 35px;
    border-radius: 10px;
}
.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ts-muted);
}

/* ── Score styling ─────────────────────────────────────── */
.score-low {
    color: #dc2626 !important;
    font-weight: 700;
}
.score-normal {
    color: #16a34a;
    font-weight: 600;
}

/* ── Signature container ───────────────────────────────── */
.signature-container {
    display: flex;
    align-items: center;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--ts-border);
}

/* ── Mobile cards ──────────────────────────────────────── */
@media (max-width: 991px) {
    .desktop-table {
        display: none;
    }
    .mobile-cards {
        display: block;
    }
}
@media (min-width: 992px) {
    .mobile-cards {
        display: none;
    }
}

.student-card {
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    margin-bottom: 16px;
    overflow: hidden;
    transition: box-shadow .15s;
}
.student-card:hover {
    box-shadow: var(--ts-shadow);
}
.student-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 16px;
    border-bottom: 1px solid var(--ts-border);
}
.student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.student-details h6 {
    margin: 0 0 4px;
    font-size: 14px;
    font-weight: 600;
}
.student-meta {
    font-size: 11px;
    color: var(--ts-muted);
}
.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.subject-item {
    text-align: center;
    padding: 8px;
    background: var(--ts-bg);
    border-radius: 8px;
    border: 1px solid var(--ts-border);
}
.subject-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--ts-muted);
    margin-bottom: 4px;
}
.subject-score {
    font-size: 16px;
    font-weight: 700;
}
.comments-section {
    border-top: 1px solid var(--ts-border);
    padding-top: 16px;
}
.comment-group {
    margin-bottom: 12px;
}
.comment-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--ts-muted);
    margin-bottom: 4px;
}

/* ── Image preview modal ───────────────────────────────── */
#imageViewModal .modal-content {
    border-radius: 16px;
}
#preview-image {
    width: 160px;
    height: 160px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid var(--ts-border);
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="ts-hero">
        <h1><i class="ri-file-list-line me-2"></i>Class Broadsheet</h1>
        <p>View and manage student scores, comments, and performance across all subjects.</p>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-user-line"></i></div>
                <div class="stat-value">{{ $students->count() }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-flask-line"></i></div>
                <div class="stat-value text-primary">{{ $subjects->count() }}</div>
                <div class="stat-label">Subjects Offered</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-award-line"></i></div>
                <div class="stat-value text-success" id="statPassing">—</div>
                <div class="stat-label">Passing Rate</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-medal-line"></i></div>
                <div class="stat-value text-warning" id="statTopStudent">—</div>
                <div class="stat-label">Top Performer</div>
            </div>
        </div>
    </div>

    {{-- Class Info Badges --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <span class="ts-badge ts-badge-class">
                    <i class="ri-building-line me-1"></i>{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}
                </span>
                <span class="ts-badge ts-badge-term">
                    <i class="ri-calendar-line me-1"></i>{{ $schoolterm }}
                </span>
                <span class="ts-badge ts-badge-session">
                    <i class="ri-calendar-event-line me-1"></i>{{ $schoolsession }}
                </span>
            </div>
        </div>
    </div>

    @if ($students->isNotEmpty())
        {{-- Main Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color:var(--ts-primary)">
                        <i class="ri-file-copy-line me-2"></i>Student Performance
                        <span class="badge bg-primary ms-2">{{ $students->count() }} students</span>
                    </h5>
                </div>
            </div>
            <div class="card-body">

                <form id="commentsForm" action="{{ route('classbroadsheet.updateComments', [$schoolclassid, $sessionid, $termid]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Search Box --}}
                    <div class="search-box">
                        <i class="ri-search-line"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by student name, admission number, or comment...">
                    </div>

                    {{-- Desktop Table View --}}
                    <div class="desktop-table">
                        <div class="table-responsive">
                            <table class="table ts-table w-100 mb-0" id="broadsheetTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Admission No</th>
                                        <th>Student Name</th>
                                        <th>Gender</th>
                                        @foreach ($subjects as $subject)
                                            <th>{{ $subject->subject }}</th>
                                        @endforeach
                                        <th>Teacher's Comment</th>
                                        <th>Counselor's Comment</th>
                                        <th>Remark on Activities</th>
                                        <th>Absences</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $key => $student)
                                        @php
                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                            $imagePath = asset('storage/student_avatars/' . $picture);
                                            $profile = $personalityProfiles->where('studentid', $student->id)->first();
                                        @endphp
                                        <tr data-student-id="{{ $student->id }}">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $student->admissionNo }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $imagePath }}"
                                                         alt="{{ $student->lastname }} {{ $student->firstname }}"
                                                         class="student-avatar"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#imageViewModal"
                                                         data-image="{{ $imagePath }}"
                                                         data-studentname="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}"
                                                         data-admissionno="{{ $student->admissionNo }}"
                                                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                                                    <div>
                                                        <a href="{{ route('myclass.studentpersonalityprofile', [$student->id, $schoolclassid, $termid, $sessionid]) }}"
                                                           class="text-reset text-decoration-none fw-semibold">
                                                            {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $student->gender ?? 'N/A' }}</td>
                                            @foreach ($subjects as $subject)
                                                @php
                                                    $score = $scores->where('student_id', $student->id)->where('subject_name', $subject->subject)->first();
                                                    $total = $score ? $score->total : '-';
                                                    $isLow = is_numeric($total) && $total <= 50;
                                                @endphp
                                                <td class="text-center @if($isLow) score-low @else score-normal @endif">
                                                    {{ $total }}
                                                </td>
                                            @endforeach
                                            <td>
                                                <input type="text" class="form-control form-control-sm teacher-comment-input"
                                                       name="teacher_comments[{{ $student->id }}]"
                                                       value="{{ $profile ? $profile->classteachercomment : '' }}"
                                                       placeholder="Enter comment...">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm guidance-comment-input"
                                                       name="guidance_comments[{{ $student->id }}]"
                                                       value="{{ $profile ? $profile->guidancescomment : '' }}"
                                                       placeholder="Enter comment...">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark-input"
                                                       name="remarks_on_other_activities[{{ $student->id }}]"
                                                       value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                                       placeholder="Enter remark...">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm absence-input"
                                                       name="no_of_times_school_absent[{{ $student->id }}]"
                                                       value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                                       min="0" placeholder="0" style="width: 80px;">
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Mobile Card View --}}
                    <div class="mobile-cards">
                        @forelse ($students as $key => $student)
                            @php
                                $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                $imagePath = asset('storage/student_avatars/' . $picture);
                                $profile = $personalityProfiles->where('studentid', $student->id)->first();
                            @endphp
                            <div class="student-card" data-student-id="{{ $student->id }}">
                                <div class="student-header">
                                    <div class="student-info">
                                        <img src="{{ $imagePath }}"
                                             alt="{{ $student->lastname }} {{ $student->firstname }}"
                                             class="student-avatar"
                                             data-bs-toggle="modal"
                                             data-bs-target="#imageViewModal"
                                             data-image="{{ $imagePath }}"
                                             data-studentname="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}"
                                             data-admissionno="{{ $student->admissionNo }}"
                                             onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                                        <div class="student-details">
                                            <h6>
                                                <a href="{{ route('myclass.studentpersonalityprofile', [$student->id, $schoolclassid, $termid, $sessionid]) }}"
                                                   class="text-reset text-decoration-none">
                                                    {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                </a>
                                            </h6>
                                            <div class="student-meta">
                                                <span class="me-2">SN: {{ $key + 1 }}</span>
                                                <span class="me-2">Adm: {{ $student->admissionNo }}</span>
                                                <span>Gender: {{ $student->gender ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="student-body" style="padding: 16px;">
                                    <div class="subjects-grid">
                                        @foreach ($subjects as $subject)
                                            @php
                                                $score = $scores->where('student_id', $student->id)->where('subject_name', $subject->subject)->first();
                                                $total = $score ? $score->total : '-';
                                                $isLow = is_numeric($total) && $total <= 50;
                                            @endphp
                                            <div class="subject-item">
                                                <div class="subject-name">{{ $subject->subject }}</div>
                                                <div class="subject-score @if($isLow) score-low @else score-normal @endif">
                                                    {{ $total }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="comments-section">
                                        <div class="comment-group">
                                            <div class="comment-label">Teacher's Comment</div>
                                            <input type="text" class="form-control form-control-sm teacher-comment-input"
                                                   name="teacher_comments[{{ $student->id }}]"
                                                   value="{{ $profile ? $profile->classteachercomment : '' }}"
                                                   placeholder="Enter comment...">
                                        </div>
                                        <div class="comment-group">
                                            <div class="comment-label">Counselor's Comment</div>
                                            <input type="text" class="form-control form-control-sm guidance-comment-input"
                                                   name="guidance_comments[{{ $student->id }}]"
                                                   value="{{ $profile ? $profile->guidancescomment : '' }}"
                                                   placeholder="Enter comment...">
                                        </div>
                                        <div class="comment-group">
                                            <div class="comment-label">Remark on Activities</div>
                                            <input type="text" class="form-control form-control-sm remark-input"
                                                   name="remarks_on_other_activities[{{ $student->id }}]"
                                                   value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                                   placeholder="Enter remark...">
                                        </div>
                                        <div class="comment-group">
                                            <div class="comment-label">Times Absent</div>
                                            <input type="number" class="form-control form-control-sm absence-input"
                                                   name="no_of_times_school_absent[{{ $student->id }}]"
                                                   value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                                   min="0" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Signature and Save Button --}}
                    <div class="signature-container">
                        <div class="flex-grow-1">
                            <label class="form-label mb-1">Upload Signature (JPG, PNG, or PDF)</label>
                            <input type="file" class="form-control" name="signature" id="signature" accept=".jpg,.jpeg,.png,.pdf" style="width: 250px;">
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="ri-save-line me-1"></i>Save All Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-warning text-center py-4">
            <i class="ri-information-line fs-1 d-block mb-2"></i>
            <h5>No Student Data Found</h5>
            <p class="mb-0">No students are enrolled in this class for the selected term and session.</p>
        </div>
    @endif
</div>
</div>
</div>

{{-- IMAGE PREVIEW MODAL --}}
<div class="modal fade" id="imageViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold" style="color:var(--ts-primary)">
                    <i class="ri-user-star-line me-1"></i> Student Photo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-2 pb-4">
                <img id="preview-image" src="" alt="Student"
                     class="rounded-circle mb-3"
                     style="width:160px;height:160px;object-fit:cover;border:4px solid var(--ts-border);">
                <p id="preview-studentname" class="fw-semibold mb-1" style="color:var(--ts-primary)"></p>
                <p id="preview-admissionno" class="text-muted small mb-0"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // ── DataTable initialization ──────────────────────────────────────
    const table = $('#broadsheetTable').DataTable({
        pageLength: 15,
        responsive: true,
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ students',
            infoEmpty: 'No students found',
            zeroRecords: 'No matching students',
        },
        order: [[2, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 4, 5, 6, 7, 8] }
        ]
    });

    // ── Search functionality for mobile ───────────────────────────────
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        // Desktop DataTable search
        table.search(searchTerm).draw();

        // Mobile cards search
        $('.student-card').each(function() {
            const cardText = $(this).text().toLowerCase();
            if (searchTerm === '' || cardText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ── Image preview modal ──────────────────────────────────────────
    $(document).on('click', '.student-avatar', function () {
        const imgSrc = $(this).data('image');
        const studentName = $(this).data('studentname');
        const admissionNo = $(this).data('admissionno');

        $('#preview-image').attr('src', imgSrc);
        $('#preview-studentname').text(studentName || 'Student');
        $('#preview-admissionno').text('Admission No: ' + (admissionNo || 'N/A'));
        $('#imageViewModal').modal('show');
    });

    // ── Form submission with validation ──────────────────────────────
    $('#commentsForm').on('submit', function(e) {
        const teacherInputs = $('.teacher-comment-input').filter(function() {
            return $(this).val().trim() !== '';
        });
        const guidanceInputs = $('.guidance-comment-input').filter(function() {
            return $(this).val().trim() !== '';
        });
        const remarkInputs = $('.remark-input').filter(function() {
            return $(this).val().trim() !== '';
        });
        const absenceInputs = $('.absence-input').filter(function() {
            return $(this).val().trim() !== '';
        });

        const hasData = teacherInputs.length > 0 || guidanceInputs.length > 0 ||
                        remarkInputs.length > 0 || absenceInputs.length > 0;

        if (!hasData && !$('#signature').val()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No Data to Save',
                text: 'Please enter at least one comment, remark, absence count, or upload a signature before saving.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        // Show loading state
        const saveBtn = $('#saveBtn');
        const originalHtml = saveBtn.html();
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        // Allow form to submit normally
        setTimeout(() => {
            saveBtn.prop('disabled', false).html(originalHtml);
        }, 3000);
    });

    // ── Calculate and display stats ──────────────────────────────────
    function calculateStats() {
        let totalScores = 0;
        let scoreCount = 0;
        let studentAverages = [];

        $('.student-row').each(function() {
            let studentTotal = 0;
            let studentSubjects = 0;

            $(this).find('td.score-normal, td.score-low').each(function() {
                const score = parseInt($(this).text());
                if (!isNaN(score)) {
                    studentTotal += score;
                    studentSubjects++;
                    totalScores += score;
                    scoreCount++;
                }
            });

            if (studentSubjects > 0) {
                studentAverages.push(studentTotal / studentSubjects);
            }
        });

        const passingRate = scoreCount > 0 ? Math.round((totalScores / (scoreCount * 100)) * 100) : 0;
        $('#statPassing').text(passingRate + '%');

        const topAverage = studentAverages.length > 0 ? Math.max(...studentAverages).toFixed(1) : 0;
        $('#statTopStudent').text(topAverage + '%');
    }

    // Calculate stats after table is loaded
    setTimeout(calculateStats, 500);
});
</script>
@endsection
