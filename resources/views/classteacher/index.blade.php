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

/* ── Modal ─────────────────────────────────────────────── */
#tsModal .modal-content,
#editModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 100%);
    padding: 22px 28px;
    position: relative;
    overflow: hidden;
}
.modal-hero-bar::before {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.modal-hero-bar h5 {
    color: #fff;
    font-weight: 700;
    margin: 0;
    font-size: 16px;
    position: relative;
}
.modal-hero-bar .btn-close {
    position: absolute;
    top: 18px;
    right: 20px;
    filter: invert(1);
}

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
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* ── Checkbox group ────────────────────────────────────── */
.check-group {
    border: 1.5px solid var(--ts-border);
    border-radius: 10px;
    padding: 14px 16px;
    background: var(--ts-bg);
    max-height: 260px;
    overflow-y: auto;
}
.check-group .form-check {
    margin-bottom: 8px;
}
.check-group .form-check:last-child {
    margin-bottom: 0;
}
.check-group .form-check-input:checked {
    background-color: var(--ts-accent);
    border-color: var(--ts-accent);
}
.select-all-bar {
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
    border-radius: 8px;
    padding: 7px 12px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--ts-accent);
}

/* ── Bulk bar ──────────────────────────────────────────── */
.bulk-bar {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 10px 16px;
    display: none;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.bulk-bar.show {
    display: flex;
}

/* ── Radio group ───────────────────────────────────────── */
.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 8px 0;
}
.radio-group .form-check {
    margin: 0;
}
.radio-group .form-check-input:checked {
    background-color: var(--ts-accent);
    border-color: var(--ts-accent);
}

/* ── Avatar styles (matching subjectteacher blade) ────── */
.teacher-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--ts-border);
    cursor: pointer;
    transition: border-color .15s;
}
.teacher-avatar:hover {
    border-color: var(--ts-accent);
}

.edit-info-note {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    color: #2563eb;
    margin-bottom: 16px;
}

/* ── Image preview modal ───────────────────────────────── */
#imageViewModal .modal-content {
    border-radius: 16px;
}
#preview-image {
    width: 160px;
    height: 160px;
    object-fit: cover;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="ts-hero">
        <h1><i class="ri-user-star-line me-2"></i>{{ $pagetitle ?? 'Class Teacher Management' }}</h1>
        <p>Assign class teachers, manage assignments, and track responsibilities across terms and sessions.</p>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-user-settings-line"></i></div>
                <div class="stat-value" id="statTotal">—</div>
                <div class="stat-label">Total Assignments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-user-line"></i></div>
                <div class="stat-value text-primary" id="statTeachers">—</div>
                <div class="stat-label">Unique Teachers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                <div class="stat-value text-success" id="statClasses">—</div>
                <div class="stat-label">Classes Assigned</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-event-line"></i></div>
                <div class="stat-value text-warning" id="statActive">—</div>
                <div class="stat-label">Active Sessions</div>
            </div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold" style="color:var(--ts-primary)">
                    <i class="ri-team-line me-2"></i>Class Teacher Assignments
                    <span class="badge bg-primary ms-2" id="totalBadge">0</span>
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-danger d-none" id="bulkDeleteBtn">
                        <i class="ri-delete-bin-line me-1"></i>Delete Selected
                    </button>
                    @can('Create class-teacher')
                    <button class="btn btn-primary" id="createAssignmentBtn">
                        <i class="ri-add-line me-1"></i>Create Assignment
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">

            {{-- Bulk bar --}}
            <div class="bulk-bar" id="bulkBar">
                <i class="ri-checkbox-circle-line text-warning"></i>
                <span id="bulkCount">0</span> assignment(s) selected
                <button class="btn btn-sm btn-danger ms-auto" id="bulkDeleteBtn2">
                    <i class="ri-delete-bin-line me-1"></i>Delete Selected
                </button>
            </div>

            <div class="table-responsive">
                <table class="table ts-table w-100 mb-0" id="classTeachersTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>#</th>
                            <th>Teacher</th>
                            <th>Class</th>
                            <th>Term</th>
                            <th>Session</th>
                            <th>Last Updated</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- CREATE MODAL --}}
<div class="modal fade" id="tsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5 id="modalTitle"><i class="ri-user-add-line me-2"></i>Create Assignment</h5>
            </div>
            <form id="tsForm">
                @csrf
                <div class="modal-body p-4">

                    <div class="mb-4">
                        <label class="form-label">Class Teacher <span class="text-danger">*</span></label>
                        <select id="staffid" class="form-select" required>
                            <option value="">— Select Teacher —</option>
                            @foreach($subjectteachers as $teacher)
                                <option value="{{ $teacher->userid }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            Classes <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small ms-1">(select one or more)</span>
                        </label>
                        <div class="select-all-bar">
                            <input type="checkbox" class="form-check-input" id="selectAllClasses">
                            <label for="selectAllClasses" class="mb-0" style="cursor:pointer">Select All Classes</label>
                        </div>
                        <div class="check-group" id="classCheckboxes">
                            @foreach($schoolclass->sortBy('schoolclass') as $class)
                                <div class="form-check">
                                    <input class="form-check-input class-cb" type="checkbox"
                                           value="{{ $class->id }}" id="cls_{{ $class->id }}">
                                    <label class="form-check-label" for="cls_{{ $class->id }}">
                                        {{ $class->schoolclass }} ({{ $class->schoolarm }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Term <span class="text-danger">*</span></label>
                        <div class="radio-group" id="termRadios">
                            @foreach($schoolterms as $term)
                                <div class="form-check">
                                    <input class="form-check-input term-rb" type="radio"
                                           name="termid" value="{{ $term->id }}" id="term_{{ $term->id }}">
                                    <label class="form-check-label" for="term_{{ $term->id }}">
                                        {{ $term->term }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Session <span class="text-danger">*</span></label>
                        <div class="radio-group" id="sessionRadios">
                            @foreach($schoolsessions as $session)
                                <div class="form-check">
                                    <input class="form-check-input session-rb" type="radio"
                                           name="sessionid" value="{{ $session->id }}" id="session_{{ $session->id }}">
                                    <label class="form-check-label" for="session_{{ $session->id }}">
                                        {{ $session->session }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="formErrors"></div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="ri-save-line me-1"></i>Save Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-edit-line me-2"></i>Edit Assignment</h5>
            </div>
            <form id="editForm">
                @csrf
                <input type="hidden" id="editAssignmentId">
                <div class="modal-body p-4">

                    <div class="edit-info-note">
                        <i class="ri-information-line me-1"></i>
                        You are editing a teacher's assignments. This will replace all existing classes for this teacher, term, and session.
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Class Teacher <span class="text-danger">*</span></label>
                        <select id="editStaffid" class="form-select" required>
                            <option value="">— Select Teacher —</option>
                            @foreach($subjectteachers as $teacher)
                                <option value="{{ $teacher->userid }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            Classes <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small ms-1">(select one or more)</span>
                        </label>
                        <div class="select-all-bar">
                            <input type="checkbox" class="form-check-input" id="editSelectAllClasses">
                            <label for="editSelectAllClasses" class="mb-0" style="cursor:pointer">Select All Classes</label>
                        </div>
                        <div class="check-group" id="editClassCheckboxes">
                            @foreach($schoolclass->sortBy('schoolclass') as $class)
                                <div class="form-check">
                                    <input class="form-check-input edit-class-cb" type="checkbox"
                                           value="{{ $class->id }}" id="edit_cls_{{ $class->id }}">
                                    <label class="form-check-label" for="edit_cls_{{ $class->id }}">
                                        {{ $class->schoolclass }} ({{ $class->schoolarm }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Term <span class="text-danger">*</span></label>
                        <div class="radio-group" id="editTermRadios">
                            @foreach($schoolterms as $term)
                                <div class="form-check">
                                    <input class="form-check-input edit-term-rb" type="radio"
                                           name="edit_termid" value="{{ $term->id }}" id="edit_term_{{ $term->id }}">
                                    <label class="form-check-label" for="edit_term_{{ $term->id }}">
                                        {{ $term->term }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Session <span class="text-danger">*</span></label>
                        <div class="radio-group" id="editSessionRadios">
                            @foreach($schoolsessions as $session)
                                <div class="form-check">
                                    <input class="form-check-input edit-session-rb" type="radio"
                                           name="edit_sessionid" value="{{ $session->id }}" id="edit_session_{{ $session->id }}">
                                    <label class="form-check-label" for="edit_session_{{ $session->id }}">
                                        {{ $session->session }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="editFormErrors"></div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <i class="ri-save-line me-1"></i>Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemTitle"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="ri-delete-bin-line me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- IMAGE PREVIEW MODAL (exactly like subjectteacher blade) --}}
<div class="modal fade" id="imageViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Staff Photo</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-2 pb-4">
                <img id="preview-image" src="" alt="Staff"
                     class="rounded-circle mb-3"
                     style="width:160px;height:160px;object-fit:cover;border:4px solid var(--ts-border);">
                <p id="preview-staffname" class="fw-semibold mb-0" style="color:var(--ts-primary)"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // ── Route helpers ──────────────────────────────────────────────────
    const ROUTES = {
        data:        '{{ route("classteacher.data") }}',
        stats:       '{{ route("classteacher.stats") }}',
        store:       '{{ route("classteacher.store") }}',
        update:      function(id) { return '{{ url("classteacher") }}/' + id; },
        destroy:     function(id) { return '{{ url("classteacher") }}/' + id; },
        bulkDestroy: '{{ route("classteacher.bulk-destroy") }}',
        assignments: function(staffId, termId, sessionId) {
            return '{{ url("classteacher/assignments") }}/' + staffId + '/' + termId + '/' + sessionId;
        }
    };

    const CSRF = $('meta[name="csrf-token"]').attr('content');
    let table, deleteId = null;

    // ── Image preview function (exactly like subjectteacher blade) ─────
    $(document).on('click', '.staff-image', function () {
        const img    = $(this).data('image');
        const name   = $(this).data('staffname');
        const exists = $(this).data('file-exists') === 'true';
        const defEx  = $(this).data('default-exists') === 'true';
        $('#preview-image').attr('src',
            (exists || (!exists && defEx)) ? img : '/storage/staff_avatars/unnamed.jpg');
        $('#preview-staffname').text(name || 'Unknown');
        $('#imageViewModal').modal('show');
    });

    // ── DataTable ──────────────────────────────────────────────────────
    table = $('#classTeachersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ROUTES.data,
            type: 'GET',
            error: function (xhr) {
                console.error('DataTables AJAX error:', xhr.status, xhr.responseText);
                Swal.fire('Error', 'Failed to load assignments. Please refresh the page.', 'error');
            }
        },
        columns: [
            {
                data: 'id', orderable: false, searchable: false,
                render: function(data) {
                    return '<input type="checkbox" class="form-check-input row-checkbox" value="' + data + '">';
                }
            },
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'teacher_info', orderable: false },
            { data: 'class_info', orderable: false },
            { data: 'term', orderable: false },
            { data: 'session', orderable: false },
            { data: 'formatted_date', orderable: false },
            { data: 'action', orderable: false, searchable: false },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading...',
            search: '',
            searchPlaceholder: 'Search assignments...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_&ndash;_END_ of _TOTAL_ assignments',
            infoEmpty: 'No assignments found',
            zeroRecords: 'No matching assignments',
            emptyTable: 'No class teacher assignments created yet',
        },
        order: [[1, 'desc']],
        pageLength: 15,
        responsive: true,
        drawCallback: function () {
            bindCheckboxes();
            const info = this.api().page.info();
            $('#totalBadge').text(info.recordsTotal);
        },
    });

    // ── Load stats ─────────────────────────────────────────────────────
    function loadStats() {
        $.get(ROUTES.stats, function (data) {
            if (data.stats) {
                $('#statTotal').text(data.stats.total);
                $('#statTeachers').text(data.stats.unique_teachers);
                $('#statClasses').text(data.stats.unique_classes);
                $('#statActive').text(data.stats.active_sessions);
            }
        }).fail(function() {
            console.log('Failed to load stats');
        });
    }
    loadStats();

    // ── Checkboxes ─────────────────────────────────────────────────────
    function bindCheckboxes() {
        $('.row-checkbox').off('change').on('change', updateBulkBar);
    }

    $('#selectAll').on('change', function () {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkBar();
    });

    function updateBulkBar() {
        const count = $('.row-checkbox:checked').length;
        $('#bulkBar').toggleClass('show', count > 0);
        $('#bulkCount').text(count);
        $('#bulkDeleteBtn').toggleClass('d-none', count === 0);
        if (count === 0) $('#selectAll').prop('checked', false);
    }

    // ── Select All Classes toggle ───────────────────────────────────
    $('#selectAllClasses').on('change', function () {
        $('.class-cb').prop('checked', this.checked);
    });

    $('#editSelectAllClasses').on('change', function () {
        $('.edit-class-cb').prop('checked', this.checked);
    });

    function resetForm() {
        $('#staffid').val('');
        $('.class-cb').prop('checked', false);
        $('#selectAllClasses').prop('checked', false);
        $('.term-rb').prop('checked', false);
        $('.session-rb').prop('checked', false);
        $('#formErrors').addClass('d-none').html('');
    }

    function resetEditForm() {
        $('#editStaffid').val('');
        $('.edit-class-cb').prop('checked', false);
        $('#editSelectAllClasses').prop('checked', false);
        $('.edit-term-rb').prop('checked', false);
        $('.edit-session-rb').prop('checked', false);
        $('#editFormErrors').addClass('d-none').html('');
    }

    // ── Create ─────────────────────────────────────────────────────────
    $('#createAssignmentBtn').on('click', function () {
        resetForm();
        $('#modalTitle').html('<i class="ri-user-add-line me-2"></i>Create Assignment');
        $('#saveBtn').html('<i class="ri-save-line me-1"></i>Save Assignment');
        $('#tsModal').modal('show');
    });

    // ── Edit ───────────────────────────────────────────────────────────
    $(document).on('click', '.edit-assignment', function () {
        resetEditForm();

        const id = $(this).data('id');
        const staffid = $(this).data('staffid');
        const termid = $(this).data('termid');
        const sessionid = $(this).data('sessionid');

        $('#editAssignmentId').val(id);
        $('#editStaffid').val(staffid);

        $('#edit_term_' + termid).prop('checked', true);
        $('#edit_session_' + sessionid).prop('checked', true);

        // Load existing class assignments for this teacher/term/session
        $.get(ROUTES.assignments(staffid, termid, sessionid), function(response) {
            if (response.success && response.classIds) {
                response.classIds.forEach(function(classId) {
                    $('#edit_cls_' + classId).prop('checked', true);
                });
            }
        });

        $('#editModal').modal('show');
    });

    // ── Save (create) ───────────────────────────────────────────────────
    $('#tsForm').on('submit', function (e) {
        e.preventDefault();

        const selectedClasses = $('.class-cb:checked').map(function(i, el) { return $(el).val(); }).get();
        const selectedTerm = $('input[name="termid"]:checked').val();
        const selectedSession = $('input[name="sessionid"]:checked').val();

        if (selectedClasses.length === 0) {
            Swal.fire('Error', 'Please select at least one class.', 'error');
            return;
        }

        if (!selectedTerm) {
            Swal.fire('Error', 'Please select a term.', 'error');
            return;
        }

        if (!selectedSession) {
            Swal.fire('Error', 'Please select a session.', 'error');
            return;
        }

        const payload = {
            staffid: $('#staffid').val(),
            schoolclassid: selectedClasses,
            termid: selectedTerm,
            sessionid: selectedSession,
            _token: CSRF,
        };

        $('#saveBtn').prop('disabled', true)
                     .html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        $('#formErrors').addClass('d-none').html('');

        $.ajax({
            url: ROUTES.store,
            type: 'POST',
            data: payload,
            traditional: true,
            success: function(res) {
                if (res.success) {
                    $('#tsModal').modal('hide');
                    table.ajax.reload();
                    loadStats();
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false,
                    });
                } else {
                    showErrors(res.message, res.errors, '#formErrors');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const json = xhr.responseJSON;
                    showErrors(json ? json.message : null, json ? json.errors : null, '#formErrors');
                } else {
                    Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
                }
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false)
                             .html('<i class="ri-save-line me-1"></i>Save Assignment');
            },
        });
    });

    // ── Update ─────────────────────────────────────────────────────────
    $('#editForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#editAssignmentId').val();
        const selectedClasses = $('.edit-class-cb:checked').map(function(i, el) { return $(el).val(); }).get();
        const selectedTerm = $('input[name="edit_termid"]:checked').val();
        const selectedSession = $('input[name="edit_sessionid"]:checked').val();

        if (selectedClasses.length === 0) {
            Swal.fire('Error', 'Please select at least one class.', 'error');
            return;
        }

        if (!selectedTerm) {
            Swal.fire('Error', 'Please select a term.', 'error');
            return;
        }

        if (!selectedSession) {
            Swal.fire('Error', 'Please select a session.', 'error');
            return;
        }

        const payload = {
            staffid: $('#editStaffid').val(),
            schoolclassid: selectedClasses,
            termid: selectedTerm,
            sessionid: selectedSession,
            _token: CSRF,
            _method: 'PUT'
        };

        $('#updateBtn').prop('disabled', true)
                       .html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
        $('#editFormErrors').addClass('d-none').html('');

        $.ajax({
            url: ROUTES.update(id),
            type: 'POST',
            data: payload,
            traditional: true,
            success: function(res) {
                if (res.success) {
                    $('#editModal').modal('hide');
                    table.ajax.reload();
                    loadStats();
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false,
                    });
                } else {
                    showErrors(res.message, res.errors, '#editFormErrors');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const json = xhr.responseJSON;
                    showErrors(json ? json.message : null, json ? json.errors : null, '#editFormErrors');
                } else {
                    Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
                }
            },
            complete: function() {
                $('#updateBtn').prop('disabled', false)
                               .html('<i class="ri-save-line me-1"></i>Update Assignment');
            },
        });
    });

    function showErrors(message, errors, container) {
        let html = '<ul class="mb-0 ps-3">';
        if (errors) {
            $.each(errors, function (k, v) {
                html += '<li>' + (Array.isArray(v) ? v[0] : v) + '</li>';
            });
        } else {
            html += '<li>' + (message || 'Something went wrong.') + '</li>';
        }
        html += '</ul>';
        $(container).removeClass('d-none').html(html);
    }

    // ── Single delete ──────────────────────────────────────────────────
    $(document).on('click', '.delete-assignment', function () {
        deleteId = $(this).data('id');
        $('#deleteItemTitle').text('"' + $(this).data('title') + '"');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function () {
        if (!deleteId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: ROUTES.destroy(deleteId),
            type: 'POST',
            data: { _method: 'DELETE', _token: CSRF },
            success: function(res) {
                if (res.success) {
                    $('#deleteModal').modal('hide');
                    table.ajax.reload();
                    loadStats();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to delete assignment.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false)
                   .html('<i class="ri-delete-bin-line me-1"></i>Delete');
                deleteId = null;
            },
        });
    });

    // ── Bulk delete ────────────────────────────────────────────────────
    function doBulkDelete() {
        const ids = $('.row-checkbox:checked').map(function(i, el) { return $(el).val(); }).get();
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete ' + ids.length + ' assignment(s)?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, delete',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: ROUTES.bulkDestroy,
                type: 'POST',
                data: { ids: ids, _token: CSRF },
                traditional: true,
                success: function(res) {
                    if (res.success) {
                        table.ajax.reload();
                        loadStats();
                        $('#selectAll').prop('checked', false);
                        updateBulkBar();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete assignments.', 'error');
                },
            });
        });
    }

    $('#bulkDeleteBtn, #bulkDeleteBtn2').on('click', doBulkDelete);
});
</script>
@endsection
