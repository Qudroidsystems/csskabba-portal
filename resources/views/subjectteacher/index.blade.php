{{-- resources/views/subjectteacher/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --st-primary:  #1e3a5f;
    --st-accent:   #2563eb;
    --st-success:  #16a34a;
    --st-warning:  #d97706;
    --st-danger:   #dc2626;
    --st-muted:    #6b7280;
    --st-border:   #e2e8f0;
    --st-bg:       #f8fafc;
    --st-radius:   12px;
    --st-shadow:   0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ────────────────────────────────────────────────── */
.st-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #0891b2 60%, #0d9488 100%);
    border-radius: var(--st-radius);
    padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.st-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.st-hero::after {
    content:''; position:absolute; bottom:-80px; left:-30px;
    width:260px; height:260px; background:rgba(255,255,255,.03); border-radius:50%;
}
.st-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.st-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* ── Stat cards ──────────────────────────────────────────── */
.stat-card {
    background:#fff; border:1px solid var(--st-border);
    border-radius:var(--st-radius); padding:18px 20px;
    transition:transform .15s, box-shadow .15s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--st-shadow); }
.stat-card .stat-value { font-size:28px; font-weight:700; color:var(--st-primary); }
.stat-card .stat-label { font-size:12px; color:var(--st-muted); margin-top:4px; }
.stat-card .stat-icon  { font-size:32px; opacity:.12; float:right; margin-top:-8px; }

/* ── Table ───────────────────────────────────────────────── */
.st-table th {
    background:var(--st-primary); color:#fff;
    padding:12px 16px; font-weight:600; font-size:13px;
    white-space:nowrap;
}
.st-table td {
    padding:11px 16px; vertical-align:middle;
    border-bottom:1px solid var(--st-border); font-size:13px;
}
.st-table tr:hover td { background:#f0f9ff; }

/* ── Term badges ─────────────────────────────────────────── */
.term-badge {
    display:inline-flex; align-items:center;
    padding:3px 9px; border-radius:20px;
    font-size:11px; font-weight:600; margin:2px 2px;
}
.term-first  { background:#dcfce7; color:#16a34a; }
.term-second { background:#dbeafe; color:#2563eb; }
.term-third  { background:#fee2e2; color:#dc2626; }
.term-other  { background:#f3f4f6; color:#6b7280; }

/* ── Avatar ──────────────────────────────────────────────── */
.teacher-avatar {
    width:36px; height:36px; border-radius:50%;
    object-fit:cover; border:2px solid var(--st-border);
    cursor:pointer; transition:border-color .15s;
}
.teacher-avatar:hover { border-color:var(--st-accent); }

/* ── DataTables overrides ────────────────────────────────── */
.dataTables_wrapper .dataTables_filter input {
    border:1.5px solid var(--st-border); border-radius:8px;
    padding:7px 14px; margin-left:8px; font-size:13px;
    transition:border .15s;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color:var(--st-accent); outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border:1.5px solid var(--st-border); border-radius:8px;
    padding:6px 10px; margin:0 6px; font-size:13px;
}
.dataTables_wrapper .dataTables_info  { font-size:13px; color:var(--st-muted); }
.dataTables_wrapper .paginate_button  {
    border-radius:6px !important; font-size:13px !important;
    padding:4px 10px !important;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background:var(--st-accent) !important;
    border-color:var(--st-accent) !important; color:#fff !important;
}

/* ── Modals ──────────────────────────────────────────────── */
.st-modal .modal-content {
    border:none; border-radius:16px;
    overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background:linear-gradient(135deg, #1e3a5f 0%, #0891b2 100%);
    padding:22px 28px; position:relative; overflow:hidden;
}
.modal-hero-bar::before {
    content:''; position:absolute; top:-30px; right:-30px;
    width:120px; height:120px; background:rgba(255,255,255,.07); border-radius:50%;
}
.modal-hero-bar h5 { color:#fff; font-weight:700; margin:0; font-size:16px; position:relative; }
.modal-hero-bar .btn-close { position:absolute; top:18px; right:20px; filter:invert(1); }

.form-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-control, .form-select {
    border:1.5px solid var(--st-border); border-radius:8px;
    font-size:13px; padding:9px 14px; transition:border .15s;
}
.form-control:focus, .form-select:focus {
    border-color:var(--st-accent);
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}

/* ── Checkbox / radio scroll area ────────────────────────── */
.checkbox-scroll {
    max-height:200px; overflow-y:auto;
    border:1.5px solid var(--st-border); border-radius:8px;
    padding:10px 14px; background:#fafbfc;
}
.checkbox-scroll .form-check { padding:5px 0; border-bottom:1px solid #f0f0f0; }
.checkbox-scroll .form-check:last-child { border-bottom:none; }
.checkbox-scroll .form-check-label { font-size:13px; cursor:pointer; }
.checkbox-scroll .form-check-input:checked {
    background-color:var(--st-accent); border-color:var(--st-accent);
}

/* ── Term / Session inline group ─────────────────────────── */
.inline-check-group {
    display:flex; flex-wrap:wrap; gap:8px;
    padding:10px 14px;
    border:1.5px solid var(--st-border); border-radius:8px;
    background:#fafbfc;
}
.inline-check-group .form-check { margin:0; }
.inline-check-group .form-check-label { font-size:13px; cursor:pointer; }
.inline-check-group .form-check-input:checked {
    background-color:var(--st-accent); border-color:var(--st-accent);
}

/* ── Bulk bar ────────────────────────────────────────────── */
.bulk-bar {
    background:#fff3cd; border:1px solid #ffc107;
    border-radius:8px; padding:10px 16px;
    display:none; align-items:center; gap:12px; margin-bottom:12px;
}
.bulk-bar.show { display:flex; }

/* ── Full-page loader overlay ────────────────────────────── */
#st-page-loader {
    position:fixed; inset:0; z-index:9999;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(3px);
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    opacity:0; visibility:hidden;
    transition:opacity .22s, visibility .22s;
}
#st-page-loader.active { opacity:1; visibility:visible; }

.st-loader-card {
    background:#fff; border-radius:16px;
    padding:32px 40px; text-align:center;
    box-shadow:0 24px 64px rgba(0,0,0,.22);
    min-width:220px;
}
.st-loader-spinner {
    width:52px; height:52px; margin:0 auto 16px;
    border:4px solid #e2e8f0;
    border-top-color:var(--st-accent);
    border-radius:50%;
    animation:st-spin .75s linear infinite;
}
@keyframes st-spin { to { transform:rotate(360deg); } }

.st-loader-label {
    font-size:14px; font-weight:600;
    color:var(--st-primary); margin-bottom:12px;
}
.st-progress-wrap {
    width:160px; height:5px;
    background:#e2e8f0; border-radius:99px; overflow:hidden;
    margin:0 auto;
}
.st-progress-bar {
    height:100%; width:0%;
    background:linear-gradient(90deg, var(--st-accent), #0d9488);
    border-radius:99px;
    transition:width .35s ease;
}

/* ── Modal body loading overlay ──────────────────────────── */
.modal-body-loader {
    position:absolute; inset:0; z-index:10;
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(2px);
    display:flex; align-items:center; justify-content:center;
    border-radius:0 0 16px 16px;
    opacity:0; visibility:hidden;
    transition:opacity .18s, visibility .18s;
}
.modal-body-loader.active { opacity:1; visibility:visible; }
.modal-body-loader .inner { display:flex; flex-direction:column; align-items:center; gap:10px; }
.modal-body-loader .mbl-spinner {
    width:36px; height:36px;
    border:3px solid #e2e8f0;
    border-top-color:var(--st-accent);
    border-radius:50%;
    animation:st-spin .7s linear infinite;
}
.modal-body-loader .mbl-text { font-size:13px; font-weight:600; color:var(--st-primary); }

/* ── Toast notifications ─────────────────────────────────── */
#st-toast-stack {
    position:fixed; bottom:24px; right:24px;
    z-index:10000; display:flex;
    flex-direction:column-reverse; gap:10px;
    pointer-events:none;
}
.st-toast {
    pointer-events:all;
    background:#fff; border-radius:10px;
    box-shadow:0 8px 28px rgba(0,0,0,.14);
    padding:14px 18px; min-width:280px; max-width:360px;
    display:flex; align-items:flex-start; gap:12px;
    border-left:4px solid var(--st-accent);
    transform:translateX(120%);
    transition:transform .3s cubic-bezier(.34,1.56,.64,1);
}
.st-toast.show { transform:translateX(0); }
.st-toast.st-toast-success { border-left-color:var(--st-success); }
.st-toast.st-toast-error   { border-left-color:var(--st-danger);  }
.st-toast.st-toast-warning { border-left-color:var(--st-warning); }
.st-toast .st-toast-icon { font-size:20px; line-height:1; flex-shrink:0; margin-top:1px; }
.st-toast-success .st-toast-icon { color:var(--st-success); }
.st-toast-error   .st-toast-icon { color:var(--st-danger);  }
.st-toast-warning .st-toast-icon { color:var(--st-warning); }
.st-toast .st-toast-body { flex:1; }
.st-toast .st-toast-title { font-size:13px; font-weight:700; color:#111827; margin-bottom:2px; }
.st-toast .st-toast-msg   { font-size:12px; color:var(--st-muted); line-height:1.4; }
.st-toast .st-toast-close {
    background:none; border:none; cursor:pointer;
    color:var(--st-muted); font-size:16px; line-height:1;
    padding:0; flex-shrink:0;
}

/* ── Button loading state ────────────────────────────────── */
.btn-loading { position:relative; pointer-events:none; opacity:.85; }
.btn-loading .btn-text { visibility:hidden; }
.btn-loading::after {
    content:''; position:absolute; inset:0;
    margin:auto; width:16px; height:16px;
    border:2px solid rgba(255,255,255,.4);
    border-top-color:#fff; border-radius:50%;
    animation:st-spin .65s linear infinite;
}
.btn-loading.btn-outline-secondary::after,
.btn-loading.btn-outline-danger::after { border-top-color:currentColor; }
.btn-loading.btn-light::after { border-top-color:#374151; }

/* ── Subject search inside modal ─────────────────────────── */
.modal-search-input {
    border:1.5px solid var(--st-border); border-radius:8px;
    padding:7px 12px; font-size:12px; width:100%;
    margin-bottom:8px; transition:border .15s;
}
.modal-search-input:focus {
    border-color:var(--st-accent); outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

{{-- ═══ Full-page loader overlay ═══ --}}
<div id="st-page-loader">
    <div class="st-loader-card">
        <div class="st-loader-spinner"></div>
        <div class="st-loader-label" id="st-loader-label">Processing…</div>
        <div class="st-progress-wrap">
            <div class="st-progress-bar" id="st-progress-bar"></div>
        </div>
    </div>
</div>

{{-- ═══ Toast stack ═══ --}}
<div id="st-toast-stack"></div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="st-hero">
        <h1><i class="ri-user-star-line me-2"></i>Subject Teacher Management</h1>
        <p>Assign teachers to subjects across terms and sessions.</p>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-links-line"></i></div>
                <div class="stat-value" id="statTotal">{{ $subjectteacher->count() }}</div>
                <div class="stat-label">Total Assignments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-user-line"></i></div>
                <div class="stat-value text-primary">{{ $subjectteacher->pluck('userid')->unique()->count() }}</div>
                <div class="stat-label">Unique Teachers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-flask-line"></i></div>
                <div class="stat-value text-success">{{ $subjectteacher->pluck('subjectid')->unique()->count() }}</div>
                <div class="stat-label">Subjects Covered</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-line"></i></div>
                <div class="stat-value text-warning">{{ $subjectteacher->pluck('sessionid')->unique()->count() }}</div>
                <div class="stat-label">Sessions Active</div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('danger'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Table card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold" style="color:var(--st-primary)">
                    <i class="ri-list-check me-2"></i>Subject Teacher Assignments
                    <span class="badge bg-primary ms-2" id="totalBadge">{{ $subjectteacher->count() }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-danger d-none" id="bulkDeleteBtn">
                        <i class="ri-delete-bin-line me-1"></i>Delete Selected
                    </button>
                    @can('Create subject-teacher')
                    <button class="btn btn-primary" id="createSubjectTeacherBtn">
                        <i class="ri-add-line me-1"></i>Create Subject Teacher
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
                <table class="table st-table w-100 mb-0" id="subjectTeacherTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>#</th>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Term(s)</th>
                            <th>Session</th>
                            <th>Updated</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 0;
                            // Group by teacher+subject+session to avoid duplicate rows for multi-term
                            $grouped = $subjectteacher->groupBy(function($row) {
                                return $row->userid . '_' . $row->subjectid . '_' . $row->sessionid;
                            });
                        @endphp
                        @forelse ($grouped as $key => $rows)
                            @php
                                $first = $rows->first();
                                $picture   = $first->avatar ?? 'unnamed.jpg';
                                $imagePath = asset('storage/staff_avatars/' . $picture);
                                $fileExists = file_exists(storage_path('app/public/staff_avatars/' . $picture));
                                $defaultExists = file_exists(storage_path('app/public/staff_avatars/unnamed.jpg'));

                                // Collect all term names for this group
                                $termNames = \App\Models\SubjectTeacher::where('staffid', $first->userid)
                                    ->where('subjectid', $first->subjectid)
                                    ->where('sessionid', $first->sessionid)
                                    ->join('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                                    ->pluck('schoolterm.term', 'subjectteacher.termid')
                                    ->toArray();
                            @endphp
                            <tr data-id="{{ $first->id }}"
                                data-destroy-url="{{ route('subjectteacher.destroy', $first->id) }}"
                                data-staffid="{{ $first->userid }}"
                                data-subjectid="{{ $first->subjectid }}"
                                data-sessionid="{{ $first->sessionid }}">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $first->id }}">
                                </td>
                                <td>{{ ++$i }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $imagePath }}"
                                             alt="{{ $first->staffname }}"
                                             class="teacher-avatar staff-image"
                                             data-bs-toggle="modal"
                                             data-bs-target="#imageViewModal"
                                             data-image="{{ $imagePath }}"
                                             data-staffname="{{ $first->staffname }}"
                                             data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                             data-default-exists="{{ $defaultExists ? 'true' : 'false' }}"
                                             onerror="this.src='{{ asset('storage/staff_avatars/unnamed.jpg') }}'">
                                        <span class="fw-semibold text-dark">{{ $first->staffname }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $first->subjectname }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2" style="border-radius:6px;font-size:12px;">
                                        {{ $first->subjectcode }}
                                    </span>
                                </td>
                                <td>
                                    @foreach ($termNames as $termId => $termName)
                                        @php
                                            $tClass = match(true) {
                                                str_contains($termName, 'First')  => 'term-first',
                                                str_contains($termName, 'Second') => 'term-second',
                                                str_contains($termName, 'Third')  => 'term-third',
                                                default => 'term-other'
                                            };
                                        @endphp
                                        <span class="term-badge {{ $tClass }}">{{ $termName }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $first->sessionname }}</td>
                                <td>
                                    <small class="text-muted">{{ $first->updated_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @can('Update subject-teacher')
                                        <button class="btn btn-sm btn-outline-secondary edit-st-btn" title="Edit"
                                            data-id="{{ $first->id }}"
                                            data-staffid="{{ $first->userid }}"
                                            data-subjectid="{{ $first->subjectid }}"
                                            data-sessionid="{{ $first->sessionid }}"
                                            data-termids="{{ implode(',', array_keys($termNames)) }}">
                                            <i class="ph-pencil"></i>
                                        </button>
                                        @endcan
                                        @can('Delete subject-teacher')
                                        <button class="btn btn-sm btn-outline-danger delete-st-btn" title="Delete"
                                            data-id="{{ $first->id }}"
                                            data-teacher="{{ $first->staffname }}"
                                            data-subject="{{ $first->subjectname }}"
                                            data-destroy-url="{{ route('subjectteacher.destroy', $first->id) }}">
                                            <i class="ph-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No subject teacher assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ═══════════════════════ ADD MODAL ═══════════════════════ --}}
<div class="modal fade st-modal" id="addSubjectTeacherModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-add-circle-line me-2"></i>Add Subject Teacher</h5>
            </div>
            <form id="add-subjectteacher-form" autocomplete="off">
                @csrf
                <div class="modal-body-loader" id="add-modal-loader">
                    <div class="inner">
                        <div class="mbl-spinner"></div>
                        <div class="mbl-text" id="add-modal-loader-text">Saving…</div>
                    </div>
                </div>
                <div class="modal-body p-4" style="position:relative">

                    {{-- Teacher --}}
                    <div class="mb-3">
                        <label class="form-label">Teacher <span class="text-danger">*</span></label>
                        <select name="staffid" id="add-staffid" class="form-select" required>
                            <option value="">— Select Teacher —</option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->userid }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subjects --}}
                    <div class="mb-3">
                        <label class="form-label">Subject(s) <span class="text-danger">*</span></label>
                        <input type="text" id="add-subject-search" class="modal-search-input"
                               placeholder="🔍  Filter subjects…">
                        <div class="checkbox-scroll" id="add-subject-list">
                            @foreach ($subjects->sortBy('subject') as $subject)
                                <div class="form-check subject-item">
                                    <input class="form-check-input add-subject-checkbox"
                                           type="checkbox"
                                           name="subjectids[]"
                                           id="add-subj-{{ $subject->id }}"
                                           value="{{ $subject->id }}"
                                           data-label="{{ $subject->subject }}">
                                    <label class="form-check-label" for="add-subj-{{ $subject->id }}">
                                        <strong>{{ $subject->subject }}</strong>
                                        <small class="text-muted">({{ $subject->subject_code }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <span id="add-subject-count">0</span> subject(s) selected
                        </small>
                    </div>

                    {{-- Terms --}}
                    <div class="mb-3">
                        <label class="form-label">Term(s) <span class="text-danger">*</span></label>
                        <div class="inline-check-group">
                            @foreach ($terms as $term)
                                @php
                                    $tColor = match(true) {
                                        str_contains($term->term, 'First')  => 'term-first',
                                        str_contains($term->term, 'Second') => 'term-second',
                                        str_contains($term->term, 'Third')  => 'term-third',
                                        default => 'term-other'
                                    };
                                @endphp
                                <div class="form-check">
                                    <input class="form-check-input add-term-checkbox"
                                           type="checkbox"
                                           name="termid[]"
                                           id="add-term-{{ $term->id }}"
                                           value="{{ $term->id }}">
                                    <label class="form-check-label" for="add-term-{{ $term->id }}">
                                        <span class="term-badge {{ $tColor }}">{{ $term->term }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Session --}}
                    <div class="mb-3">
                        <label class="form-label">Session <span class="text-danger">*</span></label>
                        <div class="inline-check-group">
                            @foreach ($schoolsessions as $session)
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="sessionid"
                                           id="add-session-{{ $session->id }}"
                                           value="{{ $session->id }}"
                                           required>
                                    <label class="form-check-label" for="add-session-{{ $session->id }}">
                                        {{ $session->session }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="add-error-msg"></div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="add-btn" disabled>
                        <i class="ri-save-line me-1"></i>Add Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════ EDIT MODAL ══════════════════════ --}}
<div class="modal fade st-modal" id="editModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-edit-line me-2"></i>Edit Subject Teacher</h5>
            </div>
            <form id="edit-subjectteacher-form" autocomplete="off">
                @csrf
                <input type="hidden" id="edit-id">
                <div class="modal-body-loader" id="edit-modal-loader">
                    <div class="inner">
                        <div class="mbl-spinner"></div>
                        <div class="mbl-text" id="edit-modal-loader-text">Updating…</div>
                    </div>
                </div>
                <div class="modal-body p-4" style="position:relative">

                    {{-- Teacher --}}
                    <div class="mb-3">
                        <label class="form-label">Teacher <span class="text-danger">*</span></label>
                        <select name="staffid" id="edit-staffid" class="form-select" required>
                            <option value="">— Select Teacher —</option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->userid }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subjects --}}
                    <div class="mb-3">
                        <label class="form-label">Subject(s) <span class="text-danger">*</span></label>
                        <input type="text" id="edit-subject-search" class="modal-search-input"
                               placeholder="🔍  Filter subjects…">
                        <div class="checkbox-scroll" id="edit-subject-list">
                            @foreach ($subjects->sortBy('subject') as $subject)
                                <div class="form-check subject-item">
                                    <input class="form-check-input edit-subject-checkbox"
                                           type="checkbox"
                                           name="subjectids[]"
                                           id="edit-subj-{{ $subject->id }}"
                                           value="{{ $subject->id }}"
                                           data-label="{{ $subject->subject }}">
                                    <label class="form-check-label" for="edit-subj-{{ $subject->id }}">
                                        <strong>{{ $subject->subject }}</strong>
                                        <small class="text-muted">({{ $subject->subject_code }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div class="mb-3">
                        <label class="form-label">Term(s) <span class="text-danger">*</span></label>
                        <div class="inline-check-group">
                            @foreach ($terms as $term)
                                @php
                                    $tColor = match(true) {
                                        str_contains($term->term, 'First')  => 'term-first',
                                        str_contains($term->term, 'Second') => 'term-second',
                                        str_contains($term->term, 'Third')  => 'term-third',
                                        default => 'term-other'
                                    };
                                @endphp
                                <div class="form-check">
                                    <input class="form-check-input edit-term-checkbox"
                                           type="checkbox"
                                           name="termid[]"
                                           id="edit-term-{{ $term->id }}"
                                           value="{{ $term->id }}">
                                    <label class="form-check-label" for="edit-term-{{ $term->id }}">
                                        <span class="term-badge {{ $tColor }}">{{ $term->term }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Session --}}
                    <div class="mb-3">
                        <label class="form-label">Session <span class="text-danger">*</span></label>
                        <div class="inline-check-group">
                            @foreach ($schoolsessions as $session)
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="sessionid"
                                           id="edit-session-{{ $session->id }}"
                                           value="{{ $session->id }}"
                                           required>
                                    <label class="form-check-label" for="edit-session-{{ $session->id }}">
                                        {{ $session->session }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="edit-error-msg"></div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="update-btn">
                        <i class="ri-save-line me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════ DELETE MODAL ════════════════════ --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Remove <strong id="delete-subject-name"></strong> from
                   <strong id="delete-teacher-name"></strong>'s assignments?</p>
                <p class="text-muted small mb-0">This may affect related class assignments.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="ri-delete-bin-line me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════ IMAGE PREVIEW MODAL ═════════════ --}}
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
                     style="width:160px;height:160px;object-fit:cover;border:4px solid var(--st-border);">
                <p id="preview-staffname" class="fw-semibold mb-0" style="color:var(--st-primary)"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    const CSRF = $('meta[name="csrf-token"]').attr('content');
    let deleteUrl = null;

    // =========================================================================
    // LOADING HELPERS
    // =========================================================================

    const PageLoader = {
        _prog: 0, _timer: null,
        show(label = 'Processing…') {
            $('#st-loader-label').text(label);
            $('#st-progress-bar').css('width', '0%');
            $('#st-page-loader').addClass('active');
            this._prog = 0; this._tick();
        },
        _tick() {
            PageLoader._timer = setInterval(() => {
                if (PageLoader._prog < 85) {
                    PageLoader._prog += Math.random() * 8;
                    $('#st-progress-bar').css('width', Math.min(PageLoader._prog, 85) + '%');
                }
            }, 220);
        },
        hide() {
            clearInterval(this._timer);
            $('#st-progress-bar').css('width', '100%');
            setTimeout(() => $('#st-page-loader').removeClass('active'), 350);
        },
    };

    function showModalLoader(id, text = 'Processing…') {
        $(`#${id}-modal-loader-text`).text(text);
        $(`#${id}-modal-loader`).addClass('active');
    }
    function hideModalLoader(id) { $(`#${id}-modal-loader`).removeClass('active'); }

    function btnLoad(selector, loadingText = '') {
        const $btn = $(selector);
        $btn.data('original-html', $btn.html()).prop('disabled', true).addClass('btn-loading');
        if (loadingText) $btn.html(`<span class="btn-text">${loadingText}</span>`);
        return $btn;
    }
    function btnReset(selector) {
        const $btn = $(selector);
        const orig = $btn.data('original-html');
        if (orig) $btn.html(orig);
        $btn.prop('disabled', false).removeClass('btn-loading');
    }

    function toast(type, title, msg, duration = 4000) {
        const icons = { success:'ri-checkbox-circle-fill', error:'ri-close-circle-fill',
                        warning:'ri-alert-fill', info:'ri-information-fill' };
        const id  = 'toast-' + Date.now();
        const $el = $(`
            <div class="st-toast st-toast-${type}" id="${id}">
                <span class="st-toast-icon"><i class="${icons[type] || icons.info}"></i></span>
                <div class="st-toast-body">
                    <div class="st-toast-title">${title}</div>
                    ${msg ? `<div class="st-toast-msg">${msg}</div>` : ''}
                </div>
                <button class="st-toast-close" onclick="$('#${id}').remove()">×</button>
            </div>`);
        $('#st-toast-stack').append($el);
        setTimeout(() => $el.addClass('show'), 20);
        if (duration > 0) {
            setTimeout(() => { $el.removeClass('show'); setTimeout(() => $el.remove(), 350); }, duration);
        }
    }

    function showError(selector, msg) {
        $(selector).removeClass('d-none').html(`<i class="ri-error-warning-line me-1"></i>${msg}`);
    }

    // =========================================================================
    // DATATABLE
    // =========================================================================

    const table = $('#subjectTeacherTable').DataTable({
        dom: "<'row align-items-center mb-3'<'col-sm-6'l><'col-sm-6 text-end'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row align-items-center mt-3'<'col-sm-5'i><'col-sm-7 text-end'p>>",
        language: {
            search: '', searchPlaceholder: 'Search assignments…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No assignments found', zeroRecords: 'No matching assignments',
            emptyTable: 'No subject teacher assignments yet',
        },
        order: [[1, 'asc']], pageLength: 15, responsive: true,
        drawCallback: function () {
            bindCheckboxes();
            $('#totalBadge').text(this.api().page.info().recordsTotal);
        },
    });

    // =========================================================================
    // CHECKBOXES & BULK BAR
    // =========================================================================

    function bindCheckboxes() {
        $('.row-checkbox').off('change').on('change', updateBulkBar);
    }
    $('#selectAll').on('change', function () {
        $('.row-checkbox').prop('checked', this.checked); updateBulkBar();
    });
    function updateBulkBar() {
        const count = $('.row-checkbox:checked').length;
        $('#bulkBar').toggleClass('show', count > 0);
        $('#bulkCount').text(count);
        $('#bulkDeleteBtn').toggleClass('d-none', count === 0);
        if (count === 0) $('#selectAll').prop('checked', false);
    }

    // =========================================================================
    // SUBJECT SEARCH FILTER (inside modals)
    // =========================================================================

    $('#add-subject-search').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#add-subject-list .subject-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    $('#edit-subject-search').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#edit-subject-list .subject-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    // =========================================================================
    // ADD MODAL — guard button
    // =========================================================================

    function updateAddBtn() {
        const ok = $('#add-staffid').val() !== '' &&
                   $('.add-subject-checkbox:checked').length > 0 &&
                   $('.add-term-checkbox:checked').length > 0 &&
                   $('input[name="sessionid"]:checked').length > 0;
        $('#add-btn').prop('disabled', !ok);
    }

    $('#add-staffid, #addSubjectTeacherModal input').on('change', updateAddBtn);
    $('#add-subject-list').on('change', '.add-subject-checkbox', function () {
        $('#add-subject-count').text($('.add-subject-checkbox:checked').length);
        updateAddBtn();
    });

    // ── Open ADD ─────────────────────────────────────────────────
    $('#createSubjectTeacherBtn').on('click', function () {
        $('#add-staffid').val('');
        $('.add-subject-checkbox, .add-term-checkbox').prop('checked', false);
        $('input[name="sessionid"]').prop('checked', false);
        $('#add-subject-count').text(0);
        $('#add-btn').prop('disabled', true);
        $('#add-error-msg').addClass('d-none').html('');
        $('#add-subject-search').val('');
        $('#add-subject-list .subject-item').show();
        hideModalLoader('add');
        new bootstrap.Modal(document.getElementById('addSubjectTeacherModal')).show();
    });

    // =========================================================================
    // EDIT MODAL
    // =========================================================================

    $(document).on('click', '.edit-st-btn', function () {
        const id        = $(this).data('id');
        const staffid   = $(this).data('staffid');
        const subjectid = $(this).data('subjectid');
        const sessionid = $(this).data('sessionid');
        const termids   = String($(this).data('termids')).split(',').map(s => s.trim());

        $('#edit-id').val(id);
        $('#edit-staffid').val(staffid);

        // Reset all checkboxes/radios then restore saved values
        $('.edit-subject-checkbox').prop('checked', false);
        $('.edit-term-checkbox').prop('checked', false);
        $('input[name="sessionid"]').prop('checked', false);

        $(`#edit-subj-${subjectid}`).prop('checked', true);
        termids.forEach(tid => $(`#edit-term-${tid}`).prop('checked', true));
        $(`#edit-session-${sessionid}`).prop('checked', true);

        $('#edit-error-msg').addClass('d-none').html('');
        $('#edit-subject-search').val('');
        $('#edit-subject-list .subject-item').show();
        hideModalLoader('edit');
        btnReset('#update-btn');

        new bootstrap.Modal(document.getElementById('editModal')).show();
    });

    // =========================================================================
    // DELETE MODAL
    // =========================================================================

    $(document).on('click', '.delete-st-btn', function () {
        deleteUrl = $(this).data('destroy-url');
        $('#delete-subject-name').text($(this).data('subject'));
        $('#delete-teacher-name').text($(this).data('teacher'));
        btnReset('#confirm-delete-btn');
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });

    // =========================================================================
    // IMAGE PREVIEW
    // =========================================================================

    $(document).on('click', '.staff-image', function () {
        const img    = $(this).data('image');
        const name   = $(this).data('staffname');
        const exists = $(this).data('file-exists') === 'true';
        const defEx  = $(this).data('default-exists') === 'true';
        $('#preview-image').attr('src',
            (exists || (!exists && defEx)) ? img : '/storage/staff_avatars/unnamed.jpg');
        $('#preview-staffname').text(name || 'Unknown');
    });

    // =========================================================================
    // SUBMIT: ADD
    // =========================================================================

    $('#add-subjectteacher-form').on('submit', function (e) {
        e.preventDefault();

        const staffid    = $('#add-staffid').val();
        const subjectids = $('.add-subject-checkbox:checked').map((i, el) => el.value).get();
        const termids    = $('.add-term-checkbox:checked').map((i, el) => el.value).get();
        const sessionid  = $('input[name="sessionid"]:checked').val();

        if (!staffid) { showError('#add-error-msg', 'Please select a teacher.'); return; }
        if (!subjectids.length) { showError('#add-error-msg', 'Please select at least one subject.'); return; }
        if (!termids.length) { showError('#add-error-msg', 'Please select at least one term.'); return; }
        if (!sessionid) { showError('#add-error-msg', 'Please select a session.'); return; }

        btnLoad('#add-btn', 'Adding…');
        showModalLoader('add', `Adding ${subjectids.length * termids.length} assignment(s)…`);
        $('#add-error-msg').addClass('d-none').html('');

        $.ajax({
            url:  '{{ route("subjectteacher.store") }}',
            type: 'POST',
            data: {
                staffid,
                'subjectids[]': subjectids,
                'termid[]':     termids,
                sessionid,
                _token: CSRF,
            },
            traditional: true,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },

            success(res) {
                if (res.success) {
                    $('#addSubjectTeacherModal').modal('hide');
                    toast('success', 'Added!', res.message);
                    setTimeout(() => {
                        PageLoader.show('Refreshing data…');
                        setTimeout(() => location.reload(), 400);
                    }, 600);
                } else {
                    hideModalLoader('add'); btnReset('#add-btn'); updateAddBtn();
                    showError('#add-error-msg', res.message || 'Could not add assignment.');
                }
            },

            error(xhr) {
                hideModalLoader('add'); btnReset('#add-btn'); updateAddBtn();
                const msg = xhr.responseJSON?.message
                    || Object.values(xhr.responseJSON?.errors || {}).flat().join(', ')
                    || 'An error occurred.';
                showError('#add-error-msg', msg);
                toast('error', 'Failed', msg);
            },
        });
    });

    // =========================================================================
    // SUBMIT: EDIT
    // =========================================================================

    $('#edit-subjectteacher-form').on('submit', function (e) {
        e.preventDefault();

        const id         = $('#edit-id').val();
        const staffid    = $('#edit-staffid').val();
        const subjectids = $('.edit-subject-checkbox:checked').map((i, el) => el.value).get();
        const termids    = $('.edit-term-checkbox:checked').map((i, el) => el.value).get();
        const sessionid  = $('input[name="sessionid"]:checked').val();

        if (!staffid) { showError('#edit-error-msg', 'Please select a teacher.'); return; }
        if (!subjectids.length) { showError('#edit-error-msg', 'Please select at least one subject.'); return; }
        if (!termids.length) { showError('#edit-error-msg', 'Please select at least one term.'); return; }
        if (!sessionid) { showError('#edit-error-msg', 'Please select a session.'); return; }

        btnLoad('#update-btn', 'Updating…');
        showModalLoader('edit', 'Saving changes…');
        $('#edit-error-msg').addClass('d-none').html('');

        $.ajax({
            url:  `{{ url('subjectteacher') }}/${id}`,
            type: 'POST',
            data: {
                staffid,
                'subjectids[]': subjectids,
                'termid[]':     termids,
                sessionid,
                _token: CSRF,
                _method: 'PUT',
            },
            traditional: true,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },

            success(res) {
                if (res.success) {
                    $('#editModal').modal('hide');
                    toast('success', 'Updated!', res.message);
                    setTimeout(() => {
                        PageLoader.show('Refreshing data…');
                        setTimeout(() => location.reload(), 400);
                    }, 600);
                } else {
                    hideModalLoader('edit'); btnReset('#update-btn');
                    showError('#edit-error-msg', res.message || 'Could not update.');
                }
            },

            error(xhr) {
                hideModalLoader('edit'); btnReset('#update-btn');
                const msg = xhr.responseJSON?.message
                    || Object.values(xhr.responseJSON?.errors || {}).flat().join(', ')
                    || 'An error occurred.';
                showError('#edit-error-msg', msg);
                toast('error', 'Failed', msg);
            },
        });
    });

    // =========================================================================
    // DELETE: SINGLE
    // =========================================================================

    $('#confirm-delete-btn').on('click', function () {
        if (!deleteUrl) return;
        btnLoad('#confirm-delete-btn', 'Deleting…');

        $.ajax({
            url:  deleteUrl, type: 'POST',
            data: { _method: 'DELETE', _token: CSRF },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },

            success(res) {
                $('#deleteModal').modal('hide');
                if (res.success) {
                    toast('success', 'Deleted!', res.message);
                    PageLoader.show('Removing record…');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toast('error', 'Cannot Delete', res.message);
                    Swal.fire({ icon:'error', title:'Cannot Delete',
                        text: res.message, confirmButtonColor:'#2563eb' });
                }
            },

            error(xhr) {
                $('#deleteModal').modal('hide');
                const msg = xhr.responseJSON?.message || 'Failed to delete.';
                toast('error', 'Error', msg);
                Swal.fire('Error!', msg, 'error');
            },

            complete() { btnReset('#confirm-delete-btn'); deleteUrl = null; },
        });
    });

    // =========================================================================
    // DELETE: BULK
    // =========================================================================

    function doBulkDelete() {
        const ids = $('.row-checkbox:checked').map((i, el) => el.value).get();
        if (!ids.length) return;

        Swal.fire({
            title: `Delete ${ids.length} assignment(s)?`,
            text:  'This will remove the selected subject teacher assignments.',
            icon:  'warning',
            showCancelButton:    true,
            confirmButtonColor:  '#dc2626',
            confirmButtonText:   'Yes, delete all',
            cancelButtonText:    'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => Promise.allSettled(
                ids.map(id => $.ajax({
                    url:  `{{ url('subjectteacher') }}/${id}`,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: CSRF },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                }))
            ),
            allowOutsideClick: () => !Swal.isLoading(),
        }).then(result => {
            if (!result.isConfirmed) return;
            const successList = result.value.filter(r => r.status === 'fulfilled' && r.value?.success);
            const failedList  = result.value.filter(r => r.status === 'rejected' || !r.value?.success);

            if (failedList.length === 0) {
                toast('success', 'Deleted!', `${ids.length} assignment(s) removed.`);
            } else if (successList.length > 0) {
                toast('warning', 'Partial Success',
                    `${successList.length} deleted. ${failedList.length} failed.`);
            } else {
                toast('error', 'Failed', 'Could not delete selected assignments.');
            }

            PageLoader.show('Refreshing…');
            setTimeout(() => location.reload(), 600);
        });
    }

    $('#bulkDeleteBtn, #bulkDeleteBtn2').on('click', doBulkDelete);

    bindCheckboxes();
});
</script>
@endsection
