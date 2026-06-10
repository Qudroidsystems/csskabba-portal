{{-- resources/views/compulsorysubjectclass/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* ... (keep all existing styles from previous version) ... */
/* Add any additional styles for the new column if needed */
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
            <p>Processing…</p>
        </div>
    </div>

    <div class="pay-hero">
        <h1><i class="ri-book-open-line me-2"></i>Compulsory Subject Class Management</h1>
        <p>Manage subjects that students must pass for promotion to the next class level.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                <div class="stat-value">{{ $compulsorysubjectclasses->count() }}</div>
                <div class="stat-label">Total Assignments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value text-primary">{{ $schoolclasses->count() }}</div>
                <div class="stat-label">Total Classes</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-line"></i></div>
                <div class="stat-value text-success">{{ $sessions->count() }}</div>
                <div class="stat-label">Sessions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-star-line"></i></div>
                <div class="stat-value text-warning">{{ $compulsorysubjectclasses->groupBy('schoolclassid')->count() }}</div>
                <div class="stat-label">Classes with Rules</div>
            </div>
        </div>
    </div>

    <div class="info-banner">
        <i class="ri-information-line"></i>
        <div class="text">
            <strong>About Compulsory Subjects</strong>
            These are core subjects that students MUST pass to be promoted. Set a minimum passing grade per subject and configure the minimum overall average per class below.
        </div>
    </div>

    {{-- Promotion Pass Average Panel --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding:16px 20px">
            <div>
                <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                    <i class="ri-percent-line me-2"></i>Promotion Pass Average — Per Class
                </h5>
                <div class="small text-muted mt-1">Minimum overall % a student must achieve to be promoted. Leave blank to disable the threshold.</div>
            </div>
        </div>
        <div class="card-body">
            @if($schoolclasses->isEmpty())
                <p class="text-muted small">No classes found.</p>
            @else
            <div class="row">
                @foreach ($schoolclasses as $cls)
                    @php
                        $existing = $classPassAverages->get($cls->id);
                        $current  = $existing ? $existing->promotion_pass_average : null;
                    @endphp
                    <div class="col-md-3 mb-3">
                        <div class="pass-avg-card">
                            <div class="pac-label" title="{{ $cls->schoolclass }}{{ $cls->arm ? ' ('.$cls->arm.')' : '' }}">
                                {{ $cls->schoolclass }}{{ $cls->arm ? ' ('.$cls->arm.')' : '' }}
                            </div>
                            <div class="pac-input-row">
                                <input type="number"
                                       class="form-control pac-input"
                                       id="pac_{{ $cls->id }}"
                                       min="0" max="100" step="0.5"
                                       placeholder="e.g. 40"
                                       value="{{ $current !== null ? number_format((float)$current, 1) : '' }}">
                                <span class="pac-unit">%</span>
                                <button type="button"
                                        class="btn btn-primary btn-sm pac-save-btn"
                                        data-classid="{{ $cls->id }}"
                                        data-classname="{{ $cls->schoolclass }}{{ $cls->arm ? ' ('.$cls->arm.')' : '' }}">
                                    <i class="ri-save-line"></i>
                                </button>
                            </div>
                            <div class="pac-status" id="pac_status_{{ $cls->id }}">
                                @if($current !== null)
                                    <span class="pac-badge-set">
                                        <i class="ri-checkbox-circle-line me-1"></i>{{ number_format((float)$current, 1) }}% set
                                    </span>
                                @else
                                    <span class="pac-badge-none">No threshold set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding:16px 20px">
            <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                <i class="ri-list-check me-2"></i>Compulsory Subject Assignments
                <span class="badge bg-primary ms-2">{{ $compulsorysubjectclasses->count() }}</span>
            </h5>
            <div class="d-flex gap-2">
                @can('Create compulsory-subject')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="ri-add-line me-1"></i>Add Compulsory Subject
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by subject, class, term…">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="ri-error-warning-line me-2"></i>{{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @can('Delete compulsory-subject')
            <div class="bulk-action-bar" id="bulkActionBar">
                <span class="bulk-count" id="bulkCount">0 selected</span>
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Delete Selected
                </button>
                <button type="button" class="btn btn-light btn-sm" id="clearSelectionBtn">
                    <i class="ri-close-line me-1"></i>Clear
                </button>
            </div>
            @endcan

            <div class="table-responsive">
                <table class="compulsory-table">
                    <thead>
                        <tr>
                            @can('Delete compulsory-subject')
                            <th width="40">
                                <input type="checkbox" class="select-all-checkbox" id="selectAll" title="Select all">
                            </th>
                            @endcan
                            <th width="45">#</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Arm</th>
                            <th>Term</th>
                            <th>Session</th>
                            <th>Min Grade</th>
                            <th>Promotion Avg</th>
                            <th width="120">Last Updated</th>
                            <th width="90">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @php $i = 0 @endphp
                        @forelse ($compulsorysubjectclasses as $csc)
                        <tr data-id="{{ $csc->cscid }}">
                            @can('Delete compulsory-subject')
                            <td><input type="checkbox" class="row-checkbox" value="{{ $csc->cscid }}"></td>
                            @endcan
                            <td class="sn">{{ ++$i }}</td>
                            <td>
                                <span class="fw-semibold">{{ $csc->subjectname }}</span>
                                <div class="small text-muted">{{ $csc->subjectcode }}</div>
                            </td>
                            <td><span class="fw-semibold">{{ $csc->sclass }}</span></td>
                            <td><span class="badge bg-info">{{ $csc->schoolarm ?? 'N/A' }}</span></td>
                            <td>
                                @if($csc->termname)
                                    <span class="scope-badge"><i class="ri-time-line"></i> {{ $csc->termname }}</span>
                                @else
                                    <span class="badge-all-terms">All Terms</span>
                                @endif
                            </td>
                            <td>
                                @if($csc->sessionname)
                                    <span class="scope-badge"><i class="ri-calendar-line"></i> {{ $csc->sessionname }}</span>
                                @else
                                    <span class="text-muted small">Any</span>
                                @endif
                            </td>
                            <td>
                                @if($csc->min_grade)
                                    <span class="badge-grade"><i class="ri-bar-chart-line"></i> {{ $csc->min_grade }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $classAvg = $classPassAverages->get($csc->schoolclassid);
                                    $passAvg = $classAvg ? $classAvg->promotion_pass_average : null;
                                @endphp
                                @if($passAvg)
                                    <span class="badge bg-success" style="background: #10b981!important;">
                                        <i class="ri-percent-line me-1"></i>{{ number_format((float)$passAvg, 1) }}%
                                    </span>
                                @else
                                    <span class="text-muted small">Not set</span>
                                @endif
                            </td>
                            <td><span class="text-muted small">{{ \Carbon\Carbon::parse($csc->updated_at)->format('d M Y') }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    @can('Update compulsory-subject')
                                    <button type="button"
                                            class="btn-icon btn-subtle-secondary edit-btn"
                                            title="Edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $csc->cscid }}"
                                            data-subject-id="{{ $csc->subjectid }}"
                                            data-class-id="{{ $csc->schoolclassid }}"
                                            data-term-id="{{ $csc->termid ?? '' }}"
                                            data-session-id="{{ $csc->sessionid ?? '' }}"
                                            data-min-grade="{{ $csc->min_grade ?? '' }}">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    @endcan
                                    @can('Delete compulsory-subject')
                                    <button type="button"
                                            class="btn-icon btn-subtle-danger delete-btn"
                                            title="Remove"
                                            data-id="{{ $csc->cscid }}"
                                            data-name="{{ $csc->subjectname }}"
                                            data-class="{{ $csc->sclass }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="11" class="text-center">
                                <div class="empty-state">
                                    <i class="ri-inbox-line"></i>
                                    <p>No compulsory subjects assigned yet.</p>
                                    @can('Create compulsory-subject')
                                    <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                                        <i class="ri-add-line me-1"></i>Add your first compulsory subject
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row align-items-center mt-3">
                <div class="col-sm">
                    <div class="text-muted text-center text-sm-start">
                        Showing <span class="fw-semibold" id="visibleCount">{{ $compulsorysubjectclasses->count() }}</span> assignment(s)
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel"><i class="ri-add-line me-2"></i>Add Compulsory Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="add_classid" class="form-label">Class <span class="text-danger">*</span></label>
                        <select id="add_classid" class="form-select" required>
                            <option value="">— Select Class —</option>
                            @foreach ($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->schoolclass }}{{ $class->arm ? ' ('.$class->arm.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="add_termid" class="form-label">Term</label>
                        <select id="add_termid" class="form-select">
                            <option value="">— All Terms —</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Leave blank to apply to all terms</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="add_sessionid" class="form-label">Session</label>
                        <select id="add_sessionid" class="form-select">
                            <option value="">— Any Session —</option>
                            @foreach ($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label mb-0">Select Subjects <span class="text-danger">*</span></label>
                        <span class="small text-muted" id="add_gradeScaleInfo"></span>
                    </div>
                    <div class="checkbox-group" id="add_subjectList">
                        <div class="checkbox-empty">
                            <i class="ri-arrow-up-line"></i> Select a class above to load its subjects.
                        </div>
                    </div>
                    <div class="form-text mt-2">Per subject, optionally pick the minimum grade the student must achieve.</div>
                </div>

                <div class="alert alert-danger d-none" id="addAlertError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addBtn">
                    <i class="ri-save-line me-1"></i>Add Compulsory Subject(s)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel"><i class="ri-edit-line me-2"></i>Edit Compulsory Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_classid" class="form-label">Class <span class="text-danger">*</span></label>
                        <select id="edit_classid" class="form-select" required>
                            <option value="">— Select Class —</option>
                            @foreach ($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->schoolclass }}{{ $class->arm ? ' ('.$class->arm.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_subjectid" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select id="edit_subjectid" class="form-select" required>
                            <option value="">— Select Subject —</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_termid" class="form-label">Term</label>
                        <select id="edit_termid" class="form-select">
                            <option value="">— All Terms —</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_sessionid" class="form-label">Session</label>
                        <select id="edit_sessionid" class="form-select">
                            <option value="">— Any Session —</option>
                            @foreach ($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_minGrade" class="form-label">Minimum Passing Grade</label>
                    <select id="edit_minGrade" class="form-select">
                        <option value="">— No minimum set —</option>
                    </select>
                    <div class="form-text">Grade scale is determined by the class category.</div>
                </div>
                <div class="alert alert-danger d-none" id="editAlertError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateBtn">
                    <i class="ri-save-line me-1"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Confirm Removal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mx-auto mb-3" style="width:60px;height:60px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center">
                    <i class="ri-delete-bin-line" style="font-size:28px;color:#dc2626"></i>
                </div>
                <h5 class="mb-2">Remove Compulsory Subject?</h5>
                <p class="text-muted mb-0">This subject will no longer be required for promotion.</p>
                <p class="text-muted small mt-2" id="deleteItemInfo"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Yes, Remove
                </button>
            </div>
        </div>
    </div>
</div>

{{-- BULK DELETE MODAL --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Bulk Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mx-auto mb-3" style="width:60px;height:60px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center">
                    <i class="ri-delete-bin-line" style="font-size:28px;color:#dc2626"></i>
                </div>
                <h5 class="mb-2">Delete <span id="bulkDeleteCount">0</span> record(s)?</h5>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Yes, Delete All
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ... (keep all the JavaScript from the previous version) ...
</script>
@endsection
