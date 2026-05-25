{{-- resources/views/admin/score-entry/mock-scoresheet.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --ss-primary: #1e3a5f;
    --ss-accent: #2563eb;
    --ss-success: #16a34a;
    --ss-warning: #d97706;
    --ss-danger: #dc2626;
    --ss-muted: #6b7280;
    --ss-border: #e2e8f0;
    --ss-radius: 10px;
}

.admin-banner-mock {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #d97706;
    border-radius: var(--ss-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
}
.stat-card {
    background: #fff;
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    padding: 14px 18px;
}
.stat-card .stat-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--ss-primary);
}
.stat-card .stat-label {
    font-size: 11px;
    color: var(--ss-muted);
    margin-top: 2px;
}
.mock-score-input {
    width: 100px;
    text-align: center;
    padding: 6px 10px;
    border: 1.5px solid var(--ss-border);
    border-radius: 6px;
    font-size: 13px;
}
.mock-score-input:focus {
    border-color: var(--ss-warning);
    outline: none;
    box-shadow: 0 0 0 3px rgba(217,119,6,.1);
}
.mock-score-input.is-saved {
    border-color: var(--ss-success);
    background: #f0fdf4;
}
.table-mock thead th {
    background: var(--ss-primary);
    color: #fff;
    padding: 12px;
    font-weight: 600;
}
.table-mock tbody td {
    padding: 10px;
    vertical-align: middle;
    border-bottom: 1px solid var(--ss-border);
}
.position-badge-mock {
    background: #d97706;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    min-width: 50px;
    text-align: center;
}
.grade-badge-mock {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s;
}
.loading-overlay.active {
    visibility: visible;
    opacity: 1;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="admin-banner-mock">
        <div class="d-flex align-items-center">
            <i class="ri-flask-line fs-3 me-3" style="color: #d97706;"></i>
            <div>
                <strong class="d-block" style="font-size: 14px;">Admin Mode - Mock Exam Score Entry</strong>
                <small class="text-muted">
                    Teacher: <strong>{{ $teacher->name }}</strong> |
                    Subject: <strong>{{ $subjectClass->subject->subject }}</strong> ({{ $subjectClass->subject->subject_code }}) |
                    Class: <strong>{{ $schoolclass->schoolclass }} {{ $schoolclass->arm->arm ?? '' }}</strong> |
                    Term: <strong>{{ $term->term }}</strong> |
                    Session: <strong>{{ $session->session }}</strong>
                </small>
            </div>
        </div>
    </div>

    @if($broadsheets->isNotEmpty())
    @php
        $total = $broadsheets->count();
        $avg = $total > 0 ? round($broadsheets->avg('total'), 1) : 0;
        $highest = $total > 0 ? round($broadsheets->max('total'), 1) : 0;
        $lowest = $total > 0 ? round($broadsheets->min('total'), 1) : 0;
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-primary">{{ $total }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-warning">{{ $avg }}</div>
                <div class="stat-label">Class Average</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-success">{{ $highest }}</div>
                <div class="stat-label">Highest Score</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-info">{{ $lowest }}</div>
                <div class="stat-label">Lowest Score</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header" style="background: var(--ss-primary);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-white">
                    <i class="ri-flask-line me-2"></i>{{ $pagetitle }}
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light" id="refreshBtn">
                        <i class="ri-refresh-line me-1"></i>Refresh
                    </button>
                    <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-sm btn-outline-light">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-mock table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </div>
                            </th>
                            <th>#</th>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th class="text-center" style="min-width: 120px;">Exam Score (100)</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Position</th>
                            <th class="text-center">Remark</th>
                        </tr>
                    </thead>
                    <tbody id="mockScoresheetBody">
                        @php $i = 0; @endphp
                        @foreach($broadsheets as $broadsheet)
                            @php
                                $examScore = $broadsheet->exam ?? 0;
                                $total = $broadsheet->total ?? 0;
                                $grade = $broadsheet->grade ?? '-';
                                $position = $broadsheet->position ?? '-';
                                $remark = $broadsheet->remark ?? '-';

                                $gradeClass = match($grade) {
                                    'A', 'A1' => 'success',
                                    'B', 'B2', 'B3' => 'primary',
                                    'C', 'C4', 'C5', 'C6' => 'info',
                                    'D', 'D7', 'E8' => 'warning',
                                    default => 'danger'
                                };
                            @endphp
                            <tr data-id="{{ $broadsheet->id }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input mock-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}">
                                    </div>
                                </td>
                                <td>{{ ++$i }}</td>
                                <td>{{ $broadsheet->admissionno ?? '-' }}</td>
                                <td>
                                    <div>
                                        <span class="fw-semibold">{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}</span>
                                        @if($broadsheet->mname)
                                            <br><small class="text-muted">{{ $broadsheet->mname }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="number"
                                           class="mock-score-input"
                                           data-id="{{ $broadsheet->id }}"
                                           data-original="{{ $examScore }}"
                                           value="{{ $examScore }}"
                                           min="0" max="100" step="0.5">
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary total-badge">{{ number_format($total, 1) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gradeClass }} grade-badge">{{ $grade }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="position-badge-mock">{{ $position }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="remark-badge">{{ $remark }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                        <i class="ri-check-double-line me-1"></i>Select All
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="clearAllBtn">
                        <i class="ri-close-line me-1"></i>Clear
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="deleteSelectedBtn">
                        <i class="ri-delete-bin-line me-1"></i>Delete Selected
                    </button>
                </div>
                <button class="btn btn-success px-4" id="bulkSaveBtn">
                    <i class="ri-save-line me-1"></i>Save All Mock Scores
                </button>
            </div>
        </div>
    </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ri-inbox-line ri-3x text-muted mb-3"></i>
                <h5>No Mock Scores Found</h5>
                <p class="text-muted">No mock exam scores have been entered for this subject.</p>
                <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-primary">
                    <i class="ri-arrow-left-line me-1"></i>Back to Teachers
                </a>
            </div>
        </div>
    @endif

</div>
</div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0">Saving mock scores...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    const CSRF = '{{ csrf_token() }}';

    function saveMockScore(input) {
        const id = input.data('id');
        const exam = parseFloat(input.val()) || 0;

        $.ajax({
            url: '{{ route("admin.score-entry.mock-single-update") }}',
            method: 'POST',
            data: {
                broadsheet_id: id,
                exam: exam,
                _token: CSRF
            },
            success: function(response) {
                if (response.success) {
                    const row = input.closest('tr');
                    row.find('.total-badge').text(response.data.total.toFixed(1));
                    row.find('.grade-badge').text(response.data.grade);
                    row.find('.position-badge-mock').text(response.data.position);
                    row.find('.remark-badge').text(response.data.remark);

                    input.data('original', exam);
                    input.addClass('is-saved');
                    setTimeout(() => input.removeClass('is-saved'), 1000);

                    const grade = response.data.grade;
                    const gradeClass = grade === 'A' || grade === 'A1' ? 'success' :
                                      (grade === 'B' || grade === 'B2' || grade === 'B3' ? 'primary' :
                                      (grade === 'C' || grade === 'C4' || grade === 'C5' || grade === 'C6' ? 'info' :
                                      (grade === 'D' || grade === 'D7' || grade === 'E8' ? 'warning' : 'danger')));
                    row.find('.grade-badge').removeClass().addClass(`badge bg-${gradeClass} grade-badge`);
                } else {
                    toastr.error(response.message || 'Failed to save score');
                    input.val(input.data('original'));
                }
            },
            error: function() {
                toastr.error('Network error. Please try again.');
                input.val(input.data('original'));
            }
        });
    }

    $('.mock-score-input').on('blur', function() {
        const input = $(this);
        const original = parseFloat(input.data('original')) || 0;
        const current = parseFloat(input.val()) || 0;
        if (Math.abs(current - original) > 0.001) {
            saveMockScore(input);
        }
    });

    $('.mock-score-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).blur();
        }
    });

    $('#checkAll').on('change', function() {
        $('.mock-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#selectAllBtn').on('click', function() {
        $('#checkAll').prop('checked', true).trigger('change');
    });

    $('#clearAllBtn').on('click', function() {
        $('#checkAll').prop('checked', false).trigger('change');
    });

    $('#deleteSelectedBtn').on('click', function() {
        const selected = $('.mock-checkbox:checked');
        if (!selected.length) {
            toastr.warning('Please select scores to delete');
            return;
        }

        if (confirm(`Delete ${selected.length} mock score(s)? This cannot be undone.`)) {
            const ids = selected.map(function() { return $(this).data('id'); }).get();
            let deleted = 0;

            ids.forEach(id => {
                $.ajax({
                    url: '{{ route("admin.score-entry.destroy") }}',
                    method: 'DELETE',
                    data: { id: id, type: 'mock', _token: CSRF },
                    async: false,
                    success: function(response) {
                        if (response.success) {
                            $(`tr[data-id="${id}"]`).remove();
                            deleted++;
                        }
                    }
                });
            });

            toastr.success(`${deleted} mock score(s) deleted`);
            if (deleted === ids.length && deleted > 0) {
                location.reload();
            }
        }
    });

    $('#bulkSaveBtn').on('click', function() {
        const scores = [];
        $('.mock-score-input').each(function() {
            scores.push({
                id: $(this).data('id'),
                exam: parseFloat($(this).val()) || 0
            });
        });

        if (!scores.length) return;

        $('#loadingOverlay').addClass('active');

        $.ajax({
            url: '{{ route("admin.score-entry.mock-bulk-update") }}',
            method: 'POST',
            data: {
                scores: scores,
                term_id: {{ $termId }},
                session_id: {{ $sessionId }},
                subjectclass_id: {{ $subjectclassId }},
                staff_id: {{ $teacherId }},
                schoolclass_id: {{ $schoolclass->id ?? 0 }},
                _token: CSRF
            },
            success: function(response) {
                $('#loadingOverlay').removeClass('active');
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message || 'Failed to save scores');
                }
            },
            error: function() {
                $('#loadingOverlay').removeClass('active');
                toastr.error('Network error. Please try again.');
            }
        });
    });

    $('#refreshBtn').on('click', function() {
        location.reload();
    });
});
</script>
@endsection
