{{-- resources/views/admin/score-entry/scoresheet.blade.php --}}
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
    --ss-bg: #f8fafc;
    --ss-card: #ffffff;
    --ss-radius: 10px;
    --ss-shadow: 0 1px 4px rgba(0,0,0,.08);
}

.admin-banner {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-left: 4px solid #0284c7;
    border-radius: var(--ss-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
}
.stat-card {
    background: var(--ss-card);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    padding: 14px 18px;
    box-shadow: var(--ss-shadow);
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
.score-input {
    width: 80px;
    text-align: center;
    padding: 4px 8px;
    border: 1.5px solid var(--ss-border);
    border-radius: 6px;
    font-size: 12px;
}
.score-input:focus {
    border-color: var(--ss-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.score-input.is-invalid {
    border-color: var(--ss-danger);
    background: #fef2f2;
}
.score-input.is-saved {
    border-color: var(--ss-success);
    background: #f0fdf4;
}
.table-scoresheet {
    font-size: 12.5px;
}
.table-scoresheet thead th {
    background: var(--ss-primary);
    color: #fff;
    padding: 10px 8px;
    font-weight: 600;
    white-space: nowrap;
    border: none;
}
.table-scoresheet tbody td {
    padding: 8px;
    vertical-align: middle;
    border-bottom: 1px solid var(--ss-border);
}
.row-vetted { background: #f0fdf4 !important; }
.row-not-vetted { background: #fef2f2 !important; }
.row-pending { background: #fffbeb !important; }
.position-badge {
    background: var(--ss-primary);
    color: white;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    min-width: 40px;
    text-align: center;
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
.loading-spinner {
    background: #fff;
    padding: 30px 40px;
    border-radius: 16px;
    text-align: center;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="admin-banner">
        <div class="d-flex align-items-center">
            <i class="ri-shield-user-line fs-3 me-3" style="color: #0284c7;"></i>
            <div>
                <strong class="d-block" style="font-size: 14px;">Admin Mode - Entering Scores on Behalf of Teacher</strong>
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
        $first = $broadsheets->first();
        $total = $broadsheets->count();
        $passed = $broadsheets->filter(fn($b) => ($b->total ?? 0) >= 40)->count();
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
                <div class="stat-value text-success">{{ $passed }}</div>
                <div class="stat-label">Passed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value text-info">{{ $highest }}</div>
                <div class="stat-label">Highest Score</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header" style="background: var(--ss-primary);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-white">
                    <i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }}
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light" id="refreshBtn">
                        <i class="ri-refresh-line me-1"></i>Refresh
                    </button>
                    <button class="btn btn-sm btn-info" id="downloadMarksSheetBtn">
                        <i class="ri-file-pdf-line me-1"></i>Marks Sheet
                    </button>
                    <button class="btn btn-sm btn-success" id="downloadExcelBtn">
                        <i class="ri-download-line me-1"></i>Export Excel
                    </button>
                    <a href="{{ route('admin.score-entry.index', ['termid' => $termId, 'sessionid' => $sessionId]) }}" class="btn btn-sm btn-outline-light">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-scoresheet table-nowrap align-middle mb-0" id="scoresheetTable">
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
                            @foreach($assessments as $assessment)
                                <th class="text-center" style="min-width: 80px;">
                                    {{ $assessment->name }}<br>
                                    <small class="fw-normal opacity-75">({{ $assessment->max_score }})</small>
                                </th>
                            @endforeach
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">BF</th>
                            <th class="text-center">Cum</th>
                            <th class="text-center">Position</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="scoresheetTableBody">
                        @php $i = 0; @endphp
                        @foreach($broadsheets as $broadsheet)
                            @php
                                $rowTotal = 0;
                                $assessmentScores = [];
                                foreach($assessments as $a) {
                                    $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                                    $score = $so ? $so->score : 0;
                                    $rowTotal += $score;
                                    $assessmentScores[$a->id] = $score;
                                }
                                $totalColor = $rowTotal >= 70 ? 'success' : ($rowTotal >= 50 ? 'info' : ($rowTotal >= 40 ? 'warning' : 'danger'));
                                $cum = $broadsheet->cum ?? 0;
                                $cumColor = $cum >= 70 ? 'success' : ($cum >= 50 ? 'info' : ($cum >= 40 ? 'warning' : 'danger'));
                                $vClass = $broadsheet->vettedstatus === '1' ? 'row-vetted' : ($broadsheet->vettedstatus === '0' ? 'row-not-vetted' : 'row-pending');
                            @endphp
                            <tr class="{{ $vClass }}" data-id="{{ $broadsheet->id }}" data-bf="{{ $broadsheet->bf ?? 0 }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}">
                                    </div>
                                </td>
                                <td>{{ ++$i }}</td>
                                <td><span class="text-muted">{{ $broadsheet->admissionno ?? '-' }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <span class="fw-semibold">{{ $broadsheet->lname ?? '' }}, {{ $broadsheet->fname ?? '' }}</span>
                                            @if($broadsheet->mname)
                                                <br><small class="text-muted">{{ $broadsheet->mname }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @foreach($assessments as $assessment)
                                    <td class="text-center">
                                        <input type="number"
                                               class="score-input"
                                               data-field="{{ $assessment->id }}"
                                               data-max="{{ $assessment->max_score }}"
                                               data-id="{{ $broadsheet->id }}"
                                               data-original="{{ $assessmentScores[$assessment->id] }}"
                                               value="{{ $assessmentScores[$assessment->id] }}"
                                               min="0" max="{{ $assessment->max_score }}" step="0.5">
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    <span class="badge bg-{{ $totalColor }}-subtle text-{{ $totalColor }} total-badge">
                                        {{ number_format($rowTotal, 1) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="grade-badge badge bg-secondary">{{ $broadsheet->grade ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="bf-badge">{{ number_format($broadsheet->bf ?? 0, 1) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $cumColor }}-subtle text-{{ $cumColor }} cum-badge">
                                        {{ number_format($cum, 1) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="position-badge">
                                        {{ $broadsheet->position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($broadsheet->vettedstatus === '1')
                                        <span class="badge bg-success">Vetted</span>
                                    @elseif($broadsheet->vettedstatus === '0')
                                        <span class="badge bg-danger">Not Vetted</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top" style="background: #f8fafc;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
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
                    <div>
                        <button class="btn btn-success btn-sm px-4" id="bulkSaveBtn">
                            <i class="ri-save-line me-1"></i>Save All Scores
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ri-inbox-line ri-3x text-muted mb-3"></i>
                <h5>No Students Found</h5>
                <p class="text-muted">No students are registered for this subject in the selected term.</p>
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
        <p class="mt-2 mb-0">Saving scores...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    const CSRF = '{{ csrf_token() }}';
    const adminRoutes = {
        singleUpdate: '{{ route("admin.score-entry.single-update") }}',
        bulkUpdate: '{{ route("admin.score-entry.bulk-update") }}',
        destroy: '{{ route("admin.score-entry.destroy") }}',
        results: '{{ route("admin.score-entry.results") }}',
        downloadMarksSheet: '{{ route("admin.score-entry.download-marks-sheet") }}',
        export: '{{ route("admin.score-entry.export") }}',
    };

    const context = {
        term_id: {{ $termId }},
        session_id: {{ $sessionId }},
        subjectclass_id: {{ $subjectclassId }},
        schoolclass_id: {{ $schoolclass->id ?? 0 }},
        staff_id: {{ $teacherId }},
    };

    function validateInput(input) {
        const max = parseFloat(input.data('max')) || 0;
        const val = parseFloat(input.val()) || 0;
        if (val > max) {
            input.addClass('is-invalid');
            return false;
        }
        input.removeClass('is-invalid');
        return true;
    }

    function updateRowGrades(row) {
        let totalRaw = 0;
        row.find('.score-input').each(function() {
            totalRaw += parseFloat($(this).val()) || 0;
        });

        const totalBadge = row.find('.total-badge');
        totalBadge.text(totalRaw.toFixed(1));

        const totalColor = totalRaw >= 70 ? 'success' : (totalRaw >= 50 ? 'info' : (totalRaw >= 40 ? 'warning' : 'danger'));
        totalBadge.removeClass('bg-success-subtle text-success bg-info-subtle text-info bg-warning-subtle text-warning bg-danger-subtle text-danger')
            .addClass(`bg-${totalColor}-subtle text-${totalColor}`);

        const bf = parseFloat(row.data('bf')) || 0;
        const termId = context.term_id;
        const cum = (termId == 1 || bf === 0) ? totalRaw : (totalRaw + bf) / 2;
        const cumBadge = row.find('.cum-badge');
        cumBadge.text(cum.toFixed(1));

        const cumColor = cum >= 70 ? 'success' : (cum >= 50 ? 'info' : (cum >= 40 ? 'warning' : 'danger'));
        cumBadge.removeClass('bg-success-subtle text-success bg-info-subtle text-info bg-warning-subtle text-warning bg-danger-subtle text-danger')
            .addClass(`bg-${cumColor}-subtle text-${cumColor}`);
    }

    function saveScore(input) {
        if (!validateInput(input)) return;

        const row = input.closest('tr');
        const broadsheetId = input.data('id');
        const assessmentId = input.data('field');
        const score = parseFloat(input.val()) || 0;

        $.ajax({
            url: adminRoutes.singleUpdate,
            method: 'POST',
            data: {
                broadsheet_id: broadsheetId,
                assessment_id: assessmentId,
                score: score,
                is_sub: false,
                term_id: context.term_id,
                session_id: context.session_id,
                subjectclass_id: context.subjectclass_id,
                schoolclass_id: context.schoolclass_id,
                staff_id: context.staff_id,
                _token: CSRF
            },
            success: function(response) {
                if (response.success) {
                    input.addClass('is-saved');
                    setTimeout(() => input.removeClass('is-saved'), 1000);
                    input.data('original', score);

                    const data = response.data;
                    row.find('.bf-badge').text(data.bf.toFixed(1));
                    row.find('.grade-badge').text(data.grade);

                    const cum = data.cum;
                    const cumBadge = row.find('.cum-badge');
                    cumBadge.text(cum.toFixed(1));
                    const cumColor = cum >= 70 ? 'success' : (cum >= 50 ? 'info' : (cum >= 40 ? 'warning' : 'danger'));
                    cumBadge.removeClass('bg-success-subtle text-success bg-info-subtle text-info bg-warning-subtle text-warning bg-danger-subtle text-danger')
                        .addClass(`bg-${cumColor}-subtle text-${cumColor}`);

                    const position = data.subject_position_class;
                    if (position) {
                        row.find('.position-badge').text(ordinal(position));
                    }
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

    function ordinal(n) {
        if (!n) return '-';
        const s = ['th', 'st', 'nd', 'rd'];
        const v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }

    function bulkSave() {
        const invalidInputs = $('.score-input.is-invalid');
        if (invalidInputs.length) {
            toastr.warning(`Please fix ${invalidInputs.length} invalid score(s) before saving.`);
            return;
        }

        const scores = [];
        $('#scoresheetTableBody tr').each(function() {
            const row = $(this);
            const assessments = {};
            row.find('.score-input').each(function() {
                assessments[$(this).data('field')] = parseFloat($(this).val()) || 0;
            });
            if (Object.keys(assessments).length) {
                scores.push({ id: row.data('id'), assessments: assessments });
            }
        });

        if (!scores.length) return;

        $('#loadingOverlay').addClass('active');

        $.ajax({
            url: adminRoutes.bulkUpdate,
            method: 'POST',
            data: JSON.stringify({
                scores: scores,
                term_id: context.term_id,
                session_id: context.session_id,
                subjectclass_id: context.subjectclass_id,
                staff_id: context.staff_id,
                schoolclass_id: context.schoolclass_id,
                is_sub: false,
                _token: CSRF
            }),
            contentType: 'application/json',
            success: function(response) {
                $('#loadingOverlay').removeClass('active');
                if (response.success) {
                    toastr.success(response.message);

                    if (response.data && response.data.broadsheets) {
                        response.data.broadsheets.forEach(bs => {
                            const row = $(`#scoresheetTableBody tr[data-id="${bs.id}"]`);
                            if (row.length) {
                                row.find('.total-badge').text(bs.total.toFixed(1));
                                row.find('.grade-badge').text(bs.grade);
                                row.find('.bf-badge').text(bs.bf.toFixed(1));
                                row.find('.cum-badge').text(bs.cum.toFixed(1));
                                row.find('.position-badge').text(ordinal(bs.position));

                                row.find('.score-input').each(function() {
                                    $(this).addClass('is-saved');
                                    setTimeout(() => $(this).removeClass('is-saved'), 1000);
                                });
                            }
                        });
                    }
                } else {
                    toastr.error(response.message || 'Bulk save failed');
                }
            },
            error: function() {
                $('#loadingOverlay').removeClass('active');
                toastr.error('Network error. Please try again.');
            }
        });
    }

    $('.score-input').on('input', function() {
        validateInput($(this));
        updateRowGrades($(this).closest('tr'));
    });

    $('.score-input').on('blur', function() {
        const input = $(this);
        const original = parseFloat(input.data('original')) || 0;
        const current = parseFloat(input.val()) || 0;
        if (Math.abs(current - original) > 0.001 && validateInput(input)) {
            saveScore(input);
        }
    });

    $('.score-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).blur();
        }
    });

    $('#checkAll').on('change', function() {
        $('.score-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#selectAllBtn').on('click', function() {
        $('#checkAll').prop('checked', true).trigger('change');
    });

    $('#clearAllBtn').on('click', function() {
        $('#checkAll').prop('checked', false).trigger('change');
    });

    $('#deleteSelectedBtn').on('click', function() {
        const selected = $('.score-checkbox:checked');
        if (!selected.length) {
            toastr.warning('Please select scores to delete');
            return;
        }

        if (confirm(`Delete ${selected.length} score(s)? This cannot be undone.`)) {
            const ids = selected.map(function() { return $(this).data('id'); }).get();
            let deleted = 0;

            ids.forEach(id => {
                $.ajax({
                    url: adminRoutes.destroy,
                    method: 'DELETE',
                    data: { id: id, type: 'terminal', _token: CSRF },
                    async: false,
                    success: function(response) {
                        if (response.success) {
                            $(`tr[data-id="${id}"]`).remove();
                            deleted++;
                        }
                    }
                });
            });

            toastr.success(`${deleted} score(s) deleted`);
            if (deleted === ids.length) {
                location.reload();
            }
        }
    });

    $('#bulkSaveBtn').on('click', bulkSave);

    $('#refreshBtn').on('click', function() {
        location.reload();
    });

    $('#downloadMarksSheetBtn').on('click', function() {
        const url = `${adminRoutes.downloadMarksSheet}?subjectclass_id=${context.subjectclass_id}&staff_id=${context.staff_id}&term_id=${context.term_id}&session_id=${context.session_id}&schoolclass_id=${context.schoolclass_id}&type=terminal`;
        window.open(url, '_blank');
    });

    $('#downloadExcelBtn').on('click', function() {
        const url = `${adminRoutes.export}?subjectclass_id=${context.subjectclass_id}&staff_id=${context.staff_id}&term_id=${context.term_id}&session_id=${context.session_id}&schoolclass_id=${context.schoolclass_id}`;
        window.open(url, '_blank');
    });

    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            bulkSave();
        }
    });
});
</script>
@endsection
