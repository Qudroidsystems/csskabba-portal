{{-- resources/views/classbroadsheet/classbroadsheet.blade.php --}}

@extends('layouts.app')

@section('title', $pagetitle)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $pagetitle }}</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $schoolclass->schoolclass }} {{ $schoolclass->arm }}</span>
                <span class="badge badge-success">{{ $schoolterm }}</span>
                <span class="badge badge-warning">{{ $schoolsession }}</span>
            </div>
        </div>

        <div class="card-body">
            <form id="commentsForm" method="POST" action="{{ route('classbroadsheet.update.comments', [$schoolclassid, $sessionid, $termid]) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="broadsheetTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Details</th>
                                <th>Performance Summary</th>
                                <th>
                                    Teacher's Comment
                                    <small class="text-muted d-block">(Academic & Behaviour)</small>
                                </th>
                                <th>
                                    Guidance's Comment
                                    <small class="text-muted d-block">(Counseling Notes)</small>
                                </th>
                                <th>
                                    Remarks on Activities
                                    <small class="text-muted d-block">(Sports, Clubs, etc.)</small>
                                </th>
                                <th>
                                    Principal's Comment
                                    <small class="text-muted d-block">(Final Remarks)</small>
                                </th>
                                <th>
                                    Days Absent
                                    <small class="text-muted d-block">(This Term)</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            @php
                                $analytics = $studentAnalytics[$student->id] ?? null;
                                $profile = $personalityProfiles[$student->id] ?? null;
                                $studentName = trim($student->lastname . ' ' . $student->firstname . ' ' . $student->othername);
                                $studentInitials = strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname, 0, 1));
                            @endphp
                            <tr data-student-id="{{ $student->id }}" data-student-name="{{ $studentName }}" data-student-picture="{{ $student->picture ?? '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="student-avatar mr-2" style="cursor: pointer;"
                                             data-toggle="tooltip"
                                             data-html="true"
                                             title="<strong>{{ $studentName }}</strong><br>Admission: {{ $student->admissionNo }}<br>Click to view past comments">
                                            @if($student->picture)
                                                <img src="{{ asset('storage/' . $student->picture) }}" class="rounded-circle" width="40" height="40" alt="Student">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <span class="text-white">{{ $studentInitials }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $studentName }}</strong><br>
                                            <small class="text-muted">Adm: {{ $student->admissionNo }}</small><br>
                                            <small class="text-muted">{{ ucfirst($student->gender) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($analytics)
                                    <div class="performance-summary">
                                        <div><strong>Term Total:</strong> {{ $analytics['term_total'] }}/{{ $analytics['total_obtainable'] }}</div>
                                        <div><strong>Term Avg:</strong> {{ $analytics['term_average'] }}%</div>
                                        <div><strong>Cum Total:</strong> {{ $analytics['cum_total'] }}/{{ $analytics['total_obtainable'] }}</div>
                                        <div><strong>Cum Avg:</strong> {{ $analytics['cum_average'] }}%</div>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-group comment-group">
                                        <label class="comment-label">
                                            <i class="fas fa-chalkboard-teacher"></i> Teacher's Comment
                                        </label>
                                        <textarea
                                            name="teacher_comments[{{ $student->id }}]"
                                            class="form-control teacher-comment comment-textarea"
                                            rows="3"
                                            data-comment-type="teacher"
                                            data-student-id="{{ $student->id }}"
                                            placeholder="Enter teacher's comment...">{{ old('teacher_comments.' . $student->id, $profile->classteachercomment ?? '') }}</textarea>
                                        <small class="text-muted comment-stats">
                                            <span class="char-count">0</span> characters
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group comment-group">
                                        <label class="comment-label">
                                            <i class="fas fa-hand-holding-heart"></i> Guidance's Comment
                                        </label>
                                        <textarea
                                            name="guidance_comments[{{ $student->id }}]"
                                            class="form-control guidance-comment comment-textarea"
                                            rows="3"
                                            data-comment-type="guidance"
                                            data-student-id="{{ $student->id }}"
                                            placeholder="Enter guidance counselor's comment...">{{ old('guidance_comments.' . $student->id, $profile->guidancescomment ?? '') }}</textarea>
                                        <small class="text-muted comment-stats">
                                            <span class="char-count">0</span> characters
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group comment-group">
                                        <label class="comment-label">
                                            <i class="fas fa-futbol"></i> Remarks on Activities
                                        </label>
                                        <textarea
                                            name="remarks_on_other_activities[{{ $student->id }}]"
                                            class="form-control activities-comment comment-textarea"
                                            rows="3"
                                            data-comment-type="activities"
                                            data-student-id="{{ $student->id }}"
                                            placeholder="Enter remarks on extracurricular activities...">{{ old('remarks_on_other_activities.' . $student->id, $profile->remark_on_other_activities ?? '') }}</textarea>
                                        <small class="text-muted comment-stats">
                                            <span class="char-count">0</span> characters
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group comment-group">
                                        <label class="comment-label">
                                            <i class="fas fa-building"></i> Principal's Comment
                                        </label>
                                        <textarea
                                            name="principals_comments[{{ $student->id }}]"
                                            class="form-control principal-comment comment-textarea"
                                            rows="3"
                                            data-comment-type="principal"
                                            data-student-id="{{ $student->id }}"
                                            placeholder="Enter principal's comment...">{{ old('principals_comments.' . $student->id, $profile->principalscomment ?? '') }}</textarea>
                                        <small class="text-muted comment-stats">
                                            <span class="char-count">0</span> characters
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-calendar-times"></i> Days Absent
                                        </label>
                                        <input
                                            type="number"
                                            name="no_of_times_school_absent[{{ $student->id }}]"
                                            class="form-control"
                                            value="{{ old('no_of_times_school_absent.' . $student->id, $profile->no_of_times_school_absent ?? 0) }}"
                                            min="0"
                                            placeholder="0">
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="signature">Upload Signature (Optional)</label>
                            <input type="file" name="signature" id="signature" class="form-control-file" accept="image/*,.pdf">
                            <small class="text-muted">Upload signature for all comments (JPEG, PNG, PDF - max 5MB)</small>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-lg" id="saveCommentsBtn">
                            <i class="fas fa-save"></i> Save All Comments
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Past Comments Modal -->
<div class="modal fade" id="pastCommentsModal" tabindex="-1" role="dialog" aria-labelledby="pastCommentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="pastCommentsModalLabel">
                    <i class="fas fa-history"></i> Past Comments
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="pastCommentsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Loading past comments...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Comment Tooltip Modal -->
<div class="modal fade" id="commentTooltipModal" tabindex="-1" role="dialog" aria-labelledby="commentTooltipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="commentTooltipModalLabel">
                    <i class="fas fa-comment-dots"></i> Comment Information
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commentTooltipContent">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection


<style>
    .comment-group {
        position: relative;
    }

    .comment-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: block;
    }

    .comment-textarea {
        font-size: 0.9rem;
        resize: vertical;
        transition: all 0.3s ease;
    }

    .comment-textarea:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    .comment-stats {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
    }

    .performance-summary {
        font-size: 0.8rem;
        line-height: 1.3;
    }

    .performance-summary div {
        margin-bottom: 2px;
    }

    .student-avatar {
        transition: transform 0.2s ease;
    }

    .student-avatar:hover {
        transform: scale(1.05);
        cursor: pointer;
    }

    .past-comment-item {
        background-color: #f8f9fa;
        border-left: 3px solid #007bff;
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }

    .past-comment-item:hover {
        background-color: #e9ecef;
        transform: translateX(5px);
        cursor: pointer;
    }

    .past-comment-item.selected {
        background-color: #d1ecf1;
        border-left-color: #17a2b8;
    }

    .comment-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.25rem;
    }

    .comment-badge.teacher { background-color: #007bff; color: white; }
    .comment-badge.guidance { background-color: #28a745; color: white; }
    .comment-badge.activities { background-color: #ffc107; color: #333; }
    .comment-badge.principal { background-color: #6c757d; color: white; }

    .tooltip-inner {
        max-width: 350px;
        text-align: left;
    }

    .btn-use-comment {
        margin-top: 0.5rem;
    }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
$(document).ready(function() {
    // Initialize CKEditor for all textareas (optional - can use simple textarea if preferred)
    // Uncomment the following lines if you want rich text editing
    /*
    $('.comment-textarea').each(function() {
        ClassicEditor
            .create(this, {
                toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo'],
                placeholder: $(this).attr('placeholder')
            })
            .catch(error => {
                console.error(error);
            });
    });
    */

    // Character counter for textareas (for non-CKEditor textareas)
    function updateCharCount(textarea) {
        const count = $(textarea).val().length;
        $(textarea).closest('.comment-group').find('.char-count').text(count);
    }

    $('.comment-textarea').each(function() {
        updateCharCount(this);
        $(this).on('input', function() {
            updateCharCount(this);
        });
    });

    // Tooltip on avatar click - show student summary with comment counts
    $('.student-avatar, .student-avatar img, .student-avatar div').click(function(e) {
        e.stopPropagation();
        const $row = $(this).closest('tr');
        const studentId = $row.data('student-id');
        const studentName = $row.data('student-name');

        // Fetch student comment summary
        $.ajax({
            url: `/classbroadsheet/student-summary/${studentId}/${$schoolclassid}/${$sessionid}/${$termid}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    const pictureHtml = data.picture
                        ? `<img src="{{ asset('storage') }}/${data.picture}" class="rounded-circle" width="80" height="80" alt="Student">`
                        : `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <span class="text-white" style="font-size: 2rem;">${data.student_name.charAt(0)}</span>
                           </div>`;

                    $('#commentTooltipContent').html(`
                        <div class="text-center mb-3">
                            ${pictureHtml}
                            <h4 class="mt-2">${data.student_name}</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info text-center">
                                    <h5>Current Term</h5>
                                    <p class="mb-0"><strong>Comments:</strong> ${data.current_comments_count}</p>
                                    <p><strong>Absences:</strong> ${data.current_profile?.no_of_times_school_absent || 0}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary text-center">
                                    <h5>Historical</h5>
                                    <p class="mb-0"><strong>Past Comments:</strong> ${data.total_historical_comments}</p>
                                    <p><strong>Total Comments:</strong> ${data.current_comments_count + data.total_historical_comments}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Current Comments:</h6>
                            ${data.current_profile ? `
                                ${data.current_profile.classteachercomment ? `<div class="alert alert-primary"><strong>Teacher:</strong> ${data.current_profile.classteachercomment}</div>` : ''}
                                ${data.current_profile.guidancescomment ? `<div class="alert alert-success"><strong>Guidance:</strong> ${data.current_profile.guidancescomment}</div>` : ''}
                                ${data.current_profile.remark_on_other_activities ? `<div class="alert alert-warning"><strong>Activities:</strong> ${data.current_profile.remark_on_other_activities}</div>` : ''}
                                ${data.current_profile.principalscomment ? `<div class="alert alert-secondary"><strong>Principal:</strong> ${data.current_profile.principalscomment}</div>` : ''}
                            ` : '<p class="text-muted">No comments for current term</p>'}
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm btn-block" onclick="viewPastComments(${studentId})">
                                <i class="fas fa-history"></i> View Past Comments
                            </button>
                        </div>
                    `);

                    $('#commentTooltipModal').modal('show');
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to load student summary', 'error');
            }
        });
    });

    // Past Comments Modal
    window.viewPastComments = function(studentId) {
        $('#commentTooltipModal').modal('hide');

        $.ajax({
            url: `/classbroadsheet/past-comments/${studentId}`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '<div class="list-group">';

                    response.data.forEach(function(comment, index) {
                        html += `
                            <div class="past-comment-item" data-comment-index="${index}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>${comment.session} - ${comment.term}</strong>
                                    <span class="badge badge-secondary">${comment.class}</span>
                                    <small class="text-muted">${comment.date}</small>
                                </div>
                                ${comment.classteachercomment ? `
                                    <div class="comment-badge teacher">Teacher's Comment</div>
                                    <p class="mt-1 mb-2">${escapeHtml(comment.classteachercomment)}</p>
                                ` : ''}
                                ${comment.guidancescomment ? `
                                    <div class="comment-badge guidance">Guidance's Comment</div>
                                    <p class="mt-1 mb-2">${escapeHtml(comment.guidancescomment)}</p>
                                ` : ''}
                                ${comment.remark_on_other_activities ? `
                                    <div class="comment-badge activities">Activities Remark</div>
                                    <p class="mt-1 mb-2">${escapeHtml(comment.remark_on_other_activities)}</p>
                                ` : ''}
                                ${comment.principalscomment ? `
                                    <div class="comment-badge principal">Principal's Comment</div>
                                    <p class="mt-1 mb-2">${escapeHtml(comment.principalscomment)}</p>
                                ` : ''}
                                <button class="btn btn-sm btn-outline-primary btn-use-comment"
                                        data-comment-data='${JSON.stringify(comment)}'
                                        onclick="usePastComment(this, ${studentId})">
                                    <i class="fas fa-paste"></i> Use This Comment
                                </button>
                            </div>
                        `;
                    });

                    html += '</div>';
                    $('#pastCommentsContent').html(html);
                } else {
                    $('#pastCommentsContent').html(`
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No past comments found for this student.
                        </div>
                    `);
                }

                $('#pastCommentsModal').modal('show');
            },
            error: function() {
                $('#pastCommentsContent').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle"></i> Error loading past comments. Please try again.
                    </div>
                `);
            }
        });
    };

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Global function to use past comment
    window.usePastComment = function(button, studentId) {
        const commentData = JSON.parse($(button).data('comment-data'));
        const $row = $(`tr[data-student-id="${studentId}"]`);

        // Determine which comment type to fill (based on active textarea or current context)
        // For simplicity, we'll show a prompt to select which field to fill
        Swal.fire({
            title: 'Select Comment Field',
            text: 'Which comment field would you like to fill?',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Teacher\'s Comment',
            denyButtonText: 'Guidance\'s Comment',
            cancelButtonText: 'Cancel',
            showCloseButton: true
        }).then((result) => {
            let targetTextarea = null;
            let commentText = '';

            if (result.isConfirmed) {
                targetTextarea = $row.find('.teacher-comment');
                commentText = commentData.classteachercomment;
            } else if (result.isDenied) {
                targetTextarea = $row.find('.guidance-comment');
                commentText = commentData.guidancescomment;
            } else if (result.dismiss === Swal.DismissReason.close) {
                // Show sub-menu for activities and principal
                Swal.fire({
                    title: 'More Options',
                    text: 'Choose additional comment field',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Activities Remark',
                    denyButtonText: 'Principal\'s Comment',
                    cancelButtonText: 'Cancel'
                }).then((subResult) => {
                    if (subResult.isConfirmed) {
                        targetTextarea = $row.find('.activities-comment');
                        commentText = commentData.remark_on_other_activities;
                    } else if (subResult.isDenied) {
                        targetTextarea = $row.find('.principal-comment');
                        commentText = commentData.principalscomment;
                    }

                    if (targetTextarea && commentText) {
                        targetTextarea.val(commentText).trigger('input');
                        Swal.fire('Success!', 'Past comment has been inserted.', 'success');
                    } else if (!commentText) {
                        Swal.fire('Warning', 'No comment available for this field in the selected past comment.', 'warning');
                    }
                });
                return;
            }

            if (targetTextarea && commentText) {
                targetTextarea.val(commentText).trigger('input');
                Swal.fire('Success!', 'Past comment has been inserted.', 'success');
            } else if (!commentText) {
                Swal.fire('Warning', 'No comment available for this field in the selected past comment.', 'warning');
            }
        });
    };

    // Tooltip for comment textareas when focused
    $('.comment-textarea').on('focus', function() {
        const $row = $(this).closest('tr');
        const studentId = $row.data('student-id');
        const studentName = $row.data('student-name');
        const commentType = $(this).data('comment-type');

        // Show tooltip with comment information
        $(this).tooltip({
            title: `Adding ${commentType} comment for ${studentName}. Click the student avatar to view past comments.`,
            placement: 'top',
            trigger: 'focus',
            html: true
        }).tooltip('show');
    });

    // Form submission with AJAX
    $('#commentsForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('_method', 'PATCH');

        Swal.fire({
            title: 'Saving Comments',
            text: 'Please wait while we save all comments...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: response.message,
                        timer: 2000
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while saving comments.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
