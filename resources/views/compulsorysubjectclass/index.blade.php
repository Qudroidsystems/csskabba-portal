{{-- resources/views/compulsorysubjectclass/index.blade.php --}}
@extends('layouts.master')

@section('content')
{{-- Suppress initialization errors from global scripts --}}
<script>
(function() {
    const requiredElements = ['addIdField','addSubmitButton','editIdField','editCategoryField','editSubmitButton'];
    requiredElements.forEach(function(id) {
        if (!document.getElementById(id)) {
            var el = document.createElement('input');
            el.type = 'hidden'; el.id = id; el.value = '';
            document.body.appendChild(el);
        }
    });
    if (typeof window.initFormFields === 'function')    window.initFormFields    = function() { return true; };
    if (typeof window.initializeSchoolArm === 'function') window.initializeSchoolArm = function() { return true; };
})();
</script>

<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent:  #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-danger:  #dc2626;
    --pay-muted:   #6b7280;
    --pay-border:  #e2e8f0;
    --pay-bg:      #f8fafc;
    --pay-radius:  12px;
    --pay-shadow:  0 2px 8px rgba(0,0,0,.08);
}
/* ── layout helpers ── */
.d-none{display:none!important}
.d-flex{display:flex}.align-items-center{align-items:center}
.justify-content-between{justify-content:space-between}
.justify-content-center{justify-content:center}
.flex-wrap{flex-wrap:wrap}.flex-grow-1{flex-grow:1}
.gap-2{gap:8px}.gap-3{gap:16px}
.mb-0{margin-bottom:0}.mb-1{margin-bottom:4px}.mb-2{margin-bottom:8px}
.mb-3{margin-bottom:16px}.mb-4{margin-bottom:24px}
.mt-1{margin-top:4px}.mt-2{margin-top:8px}.mt-3{margin-top:16px}
.p-3{padding:16px}.py-3{padding-top:16px;padding-bottom:16px}
.text-center{text-align:center}.text-start{text-align:left}
.text-muted{color:var(--pay-muted)}.text-success{color:var(--pay-success)}
.text-warning{color:var(--pay-warning)}.text-danger{color:var(--pay-danger)}
.fw-semibold{font-weight:600}.fw-bold{font-weight:700}
.small{font-size:11px}.w-100{width:100%}
/* ── loading ── */
.loading-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center}
.loading-overlay.active{display:flex}
.loading-spinner{background:#fff;padding:24px 32px;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.18);text-align:center}
.loading-spinner .spinner-border{width:2.5rem;height:2.5rem}
.loading-spinner p{margin:10px 0 0;font-size:14px;font-weight:600;color:var(--pay-primary)}
/* ── hero ── */
.pay-hero{background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 60%,#4f46e5 100%);border-radius:var(--pay-radius);padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden}
.pay-hero::before{content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;background:rgba(255,255,255,.06);border-radius:50%}
.pay-hero h1{font-size:22px;font-weight:700;color:#fff;margin:0 0 6px;position:relative}
.pay-hero p{font-size:13px;color:rgba(255,255,255,.75);margin:0;position:relative}
/* ── stat cards ── */
.stat-card{background:#fff;border:1px solid var(--pay-border);border-radius:var(--pay-radius);padding:18px 20px;transition:transform .15s,box-shadow .15s}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--pay-shadow)}
.stat-card .stat-value{font-size:28px;font-weight:700;color:var(--pay-primary)}
.stat-card .stat-label{font-size:12px;color:var(--pay-muted);margin-top:4px}
.stat-card .stat-icon{font-size:32px;opacity:.12;float:right;margin-top:-8px}
/* ── grid ── */
.row{display:flex;flex-wrap:wrap;margin:-8px}
.col-md-3,.col-md-4,.col-md-6,.col-sm,.col-sm-auto{padding:8px}
.col-md-3{width:25%}.col-md-4{width:33.333%}.col-md-6{width:50%}
.col-sm{flex:1}.col-sm-auto{flex:0 0 auto}
@media(max-width:768px){.col-md-3,.col-md-4,.col-md-6{width:100%}}
/* ── table ── */
.compulsory-table{width:100%;border-collapse:collapse}
.compulsory-table th{background:var(--pay-primary);color:#fff;padding:12px 16px;font-weight:600;font-size:13px;white-space:nowrap;text-align:left}
.compulsory-table td{padding:11px 16px;vertical-align:middle;border-bottom:1px solid var(--pay-border);font-size:13px}
.compulsory-table tr:hover td{background:#f0f9ff}
.compulsory-table .row-selected td{background:#eff6ff!important}
/* ── icon buttons ── */
.btn-icon{width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;transition:all .15s;border:none;cursor:pointer}
.btn-subtle-secondary{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0}
.btn-subtle-secondary:hover{background:#e2e8f0;color:#1e293b;transform:translateY(-1px)}
.btn-subtle-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.btn-subtle-danger:hover{background:#fee2e2;color:#b91c1c;transform:translateY(-1px)}
/* ── search ── */
.search-box{position:relative}
.search-box .form-control{border:1.5px solid var(--pay-border);border-radius:8px;padding:9px 14px;padding-right:36px;font-size:13px;width:100%}
.search-box .form-control:focus{border-color:var(--pay-accent);outline:none;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.search-box .search-icon{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--pay-muted);pointer-events:none}
/* ── modal ── */
.modal-content{border:none;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15)}
.modal-header{background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:20px 28px;border-bottom:none}
.modal-header .modal-title{color:#fff;font-weight:700;font-size:15px}
.modal-header .btn-close{filter:invert(1);background:transparent;opacity:.8}
.modal-header .btn-close:hover{opacity:1}
.modal-body{padding:24px}
.modal-footer{padding:16px 24px 24px;border-top:none}
/* ── form elements ── */
.form-label{font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block}
.form-control,.form-select{border:1.5px solid var(--pay-border);border-radius:8px;font-size:13px;padding:9px 14px;width:100%;box-sizing:border-box}
.form-control:focus,.form-select:focus{border-color:var(--pay-accent);outline:none;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.form-text{font-size:12px;color:var(--pay-muted);margin-top:4px}
/* ── buttons ── */
.btn{padding:8px 20px;font-size:13px;font-weight:500;border-radius:8px;transition:all .15s;cursor:pointer;border:none}
.btn-primary{background:linear-gradient(135deg,#2563eb,#4f46e5);color:white}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(37,99,235,.3)}
.btn-light{background:#f1f5f9;border:1px solid #e2e8f0;color:#475569}
.btn-light:hover{background:#e2e8f0;transform:translateY(-1px)}
.btn-danger{background:#dc2626;color:white}
.btn-danger:hover{background:#b91c1c;transform:translateY(-1px)}
.btn-sm{padding:5px 12px;font-size:12px}
/* ── checkbox group ── */
.checkbox-group{max-height:300px;overflow-y:auto;border:1px solid var(--pay-border);border-radius:8px;padding:12px;background:#f8fafc}
.checkbox-item{display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border-radius:8px;margin-bottom:6px;background:#fff;border:1px solid var(--pay-border);transition:border-color .15s}
.checkbox-item:last-child{margin-bottom:0}
.checkbox-item:hover{border-color:var(--pay-accent);background:#f0f9ff}
.checkbox-item input[type=checkbox]{margin-top:2px;cursor:pointer;width:16px;height:16px;flex-shrink:0}
.checkbox-item .item-info{flex:1}
.checkbox-item .item-info .subject-name{font-size:13px;font-weight:600;color:#1e293b}
.checkbox-item .item-info .subject-meta{font-size:11px;color:var(--pay-muted);margin-top:2px}
.checkbox-item .grade-select{width:90px;flex-shrink:0;font-size:12px;padding:5px 8px;border-radius:6px;border:1px solid var(--pay-border)}
.grade-select:focus{border-color:var(--pay-accent);outline:none;box-shadow:0 0 0 2px rgba(37,99,235,.1)}
.checkbox-loading{text-align:center;padding:24px;color:var(--pay-muted);font-size:13px}
.checkbox-empty{text-align:center;padding:24px;color:var(--pay-muted);font-size:13px}
/* ── alerts ── */
.alert{border:none;border-radius:10px;padding:14px 18px;font-size:13px;margin-bottom:20px}
.alert-danger{background:#fef2f2;color:#991b1b;border-left:3px solid #dc2626}
.alert-success{background:#f0fdf4;color:#166534;border-left:3px solid #16a34a}
.alert-warning{background:#fffbeb;color:#92400e;border-left:3px solid #f59e0b}
.alert-info{background:#eff6ff;color:#1e40af;border-left:3px solid #3b82f6}
/* ── info banner ── */
.info-banner{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.info-banner i{font-size:20px;color:#2563eb}
.info-banner .text{font-size:13px;color:#1e40af}
.info-banner .text strong{display:block;margin-bottom:4px}
/* ── badges ── */
.badge{padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600}
.bg-primary{background:var(--pay-accent)!important;color:white}
.bg-success{background:#16a34a!important;color:white}
.bg-warning{background:#d97706!important;color:white}
.bg-info{background:#2563eb!important;color:white}
.badge-grade{background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700}
.badge-all-terms{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
/* ── card ── */
.card{background:white;border:1px solid var(--pay-border);border-radius:var(--pay-radius);box-shadow:var(--pay-shadow)}
.card-header{border-bottom:1px solid var(--pay-border);background:white;padding:16px 20px}
.card-body{padding:20px}
.table-responsive{overflow-x:auto}
/* ── empty state ── */
.empty-state{text-align:center;padding:52px 24px;color:var(--pay-muted)}
.empty-state i{font-size:3rem;opacity:.25;display:block;margin-bottom:14px}
/* ── bulk action bar ── */
.bulk-action-bar{display:none;align-items:center;gap:12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:10px 16px;margin-bottom:16px}
.bulk-action-bar.visible{display:flex}
.bulk-action-bar .bulk-count{font-size:13px;font-weight:600;color:#92400e}
/* ── select-all checkbox ── */
.select-all-checkbox{width:16px;height:16px;cursor:pointer}
/* ── pagination ── */
.pagination{display:flex;gap:5px;list-style:none;padding:0;margin:0}
.pagination .page-item .page-link{border-radius:8px;padding:6px 12px;font-size:13px;color:var(--pay-primary);border:1px solid var(--pay-border);background:white;text-decoration:none}
.pagination .page-item.active .page-link{background:var(--pay-accent);border-color:var(--pay-accent);color:white}
.pagination .page-item.disabled .page-link{opacity:.5;cursor:not-allowed;pointer-events:none}
/* ── term/session filter badge ── */
.scope-badge{display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:20px;padding:2px 8px;font-size:11px;color:#475569;font-weight:500}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Loading overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
            <p>Processing…</p>
        </div>
    </div>

    {{-- Hero --}}
    <div class="pay-hero">
        <h1><i class="ri-book-open-line me-2"></i>Compulsory Subject Class Management</h1>
        <p>Manage subjects that students must pass for promotion to the next class level.</p>
    </div>

    {{-- Stats --}}
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

    {{-- Info banner --}}
    <div class="info-banner">
        <i class="ri-information-line"></i>
        <div class="text">
            <strong>About Compulsory Subjects</strong>
            These are core subjects that students MUST pass to be promoted. You can now set a minimum passing grade and scope rules to a specific term and session.
        </div>
    </div>

    {{-- Main card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
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

            {{-- Search & filter row --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by subject, class, term…">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
            </div>

            {{-- Flash messages --}}
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

            {{-- Bulk action bar --}}
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

            {{-- Table --}}
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
                            <th width="120">Last Updated</th>
                            <th width="90">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @php $i = 0 @endphp
                        @forelse ($compulsorysubjectclasses as $csc)
                        <tr data-id="{{ $csc->cscid }}">
                            @can('Delete compulsory-subject')
                            <td>
                                <input type="checkbox" class="row-checkbox" value="{{ $csc->cscid }}">
                            </td>
                            @endcan
                            <td class="sn">{{ ++$i }}</td>
                            <td>
                                <span class="fw-semibold">{{ $csc->subjectname }}</span>
                                <div class="small text-muted">{{ $csc->subjectcode }}</div>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $csc->sclass }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $csc->schoolarm ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($csc->termname)
                                    <span class="scope-badge"><i class="ri-time-line"></i>{{ $csc->termname }}</span>
                                @else
                                    <span class="badge-all-terms">All Terms</span>
                                @endif
                            </td>
                            <td>
                                @if($csc->sessionname)
                                    <span class="scope-badge"><i class="ri-calendar-line"></i>{{ $csc->sessionname }}</span>
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
                                <span class="text-muted small">{{ \Carbon\Carbon::parse($csc->updated_at)->format('d M Y') }}</span>
                            </td>
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
                                            data-term-id="{{ $csc->termid }}"
                                            data-session-id="{{ $csc->sessionid }}"
                                            data-min-grade="{{ $csc->min_grade }}">
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
                            <td colspan="10" class="text-center">
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

{{-- ═══════════════════════════════════════════════
     ADD MODAL
═══════════════════════════════════════════════ --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel"><i class="ri-add-line me-2"></i>Add Compulsory Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: Class / Term / Session filters --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="add_classid" class="form-label">Class <span class="text-danger">*</span></label>
                        <select id="add_classid" class="form-select" required>
                            <option value="">— Select Class —</option>
                            @foreach ($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
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

                {{-- Subject list (loaded via AJAX) --}}
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
                    <div class="form-text mt-2">Per subject, optionally set the minimum grade the student must achieve.</div>
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

{{-- ═══════════════════════════════════════════════
     EDIT MODAL
═══════════════════════════════════════════════ --}}
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
                                <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_subjectid" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select id="edit_subjectid" class="form-select" required>
                            <option value="">— Select Subject —</option>
                            {{-- populated via AJAX when class changes --}}
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
                        {{-- populated via AJAX based on class category --}}
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

{{-- ═══════════════════════════════════════════════
     DELETE CONFIRMATION MODAL
═══════════════════════════════════════════════ --}}
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

{{-- ═══════════════════════════════════════════════
     BULK DELETE CONFIRMATION MODAL
═══════════════════════════════════════════════ --}}
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
                <p class="text-muted mb-0">This action cannot be undone. All selected compulsory subject assignments will be permanently removed.</p>
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
$(function () {

    /* ── Route URLs ────────────────────────────────── */
    const URL_STORE         = '{{ route("compulsorysubjectclass.store") }}';
    const URL_BASE          = '{{ url("compulsorysubjectclass") }}';
    const URL_BULK_DESTROY  = '{{ route("compulsorysubjectclass.bulkDestroy") }}';
    const URL_SUBJECTS      = '{{ route("compulsorysubjectclass.subjectsByClass") }}';
    const CSRF              = '{{ csrf_token() }}';

    /* ── State ─────────────────────────────────────── */
    let deleteSingleId  = null;
    let currentGrades   = [];   // grade scale for the currently selected class

    /* ── Helpers ────────────────────────────────────── */
    function showLoading(on) { $('#loadingOverlay').toggleClass('active', on); }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;' }[m]));
    }

    function gradeOptions(grades, selected) {
        let opts = '<option value="">— None —</option>';
        grades.forEach(g => {
            opts += `<option value="${escHtml(g)}" ${selected === g ? 'selected' : ''}>${escHtml(g)}</option>`;
        });
        return opts;
    }

    /* ── Search ─────────────────────────────────────── */
    $('#searchInput').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        let visible = 0;
        $('#tableBody tr[data-id]').each(function () {
            const match = $(this).text().toLowerCase().includes(val);
            $(this).toggle(match);
            if (match) visible++;
        });
        $('#visibleCount').text(visible);
    });

    /* ══════════════════════════════════════════════════
       SELECT / BULK
    ══════════════════════════════════════════════════ */
    function updateBulkBar() {
        const count = $('.row-checkbox:checked').length;
        $('#bulkCount').text(count + ' selected');
        $('#bulkDeleteCount').text(count);
        $('#bulkActionBar').toggleClass('visible', count > 0);
        // highlight rows
        $('.row-checkbox').each(function () {
            $(this).closest('tr').toggleClass('row-selected', $(this).is(':checked'));
        });
    }

    $('#selectAll').on('change', function () {
        $('.row-checkbox:visible').prop('checked', this.checked);
        updateBulkBar();
    });

    $(document).on('change', '.row-checkbox', function () {
        const total   = $('.row-checkbox:visible').length;
        const checked = $('.row-checkbox:visible:checked').length;
        $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
        $('#selectAll').prop('checked', checked === total && total > 0);
        updateBulkBar();
    });

    $('#clearSelectionBtn').on('click', function () {
        $('.row-checkbox, #selectAll').prop('checked', false).prop('indeterminate', false);
        updateBulkBar();
    });

    /* ── Bulk delete open ── */
    $('#bulkDeleteBtn').on('click', function () {
        const count = $('.row-checkbox:checked').length;
        if (!count) return;
        $('#bulkDeleteCount').text(count);
        $('#bulkDeleteModal').modal('show');
    });

    /* ── Bulk delete confirm ── */
    $('#confirmBulkDeleteBtn').on('click', async function () {
        const ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (!ids.length) return;

        showLoading(true);
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting…');

        try {
            const res  = await fetch(URL_BULK_DESTROY, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ ids })
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Bulk delete failed.', 'error');
                $('#bulkDeleteModal').modal('hide');
            }
        } catch (err) {
            Swal.fire('Error', 'An error occurred.', 'error');
            $('#bulkDeleteModal').modal('hide');
        } finally {
            showLoading(false);
            btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i>Yes, Delete All');
        }
    });

    /* ══════════════════════════════════════════════════
       AJAX: LOAD SUBJECTS BY CLASS
    ══════════════════════════════════════════════════ */
    async function loadSubjectsForAdd() {
        const classId   = $('#add_classid').val();
        const termId    = $('#add_termid').val();
        const sessionId = $('#add_sessionid').val();
        const $list     = $('#add_subjectList');

        if (!classId) {
            $list.html('<div class="checkbox-empty"><i class="ri-arrow-up-line"></i> Select a class above to load its subjects.</div>');
            $('#add_gradeScaleInfo').text('');
            currentGrades = [];
            return;
        }

        $list.html('<div class="checkbox-loading"><span class="spinner-border spinner-border-sm me-2"></span>Loading subjects…</div>');
        $('#add_gradeScaleInfo').text('');

        try {
            const params = new URLSearchParams({ classid: classId });
            if (termId)    params.append('termid',    termId);
            if (sessionId) params.append('sessionid', sessionId);

            const res  = await fetch(URL_SUBJECTS + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                $list.html(`<div class="checkbox-empty text-danger">${escHtml(data.message || 'Failed to load subjects.')}</div>`);
                return;
            }

            currentGrades = data.grade_scale || [];

            if (data.category) {
                const type = data.category.is_senior ? 'Senior' : 'Junior';
                $('#add_gradeScaleInfo').text(`${data.category.name} (${type} — grades: ${currentGrades.join(', ')})`);
            }

            if (!data.subjects.length) {
                $list.html('<div class="checkbox-empty">No subjects are assigned to this class for the selected term/session.</div>');
                return;
            }

            let html = '';
            data.subjects.forEach(sub => {
                const checked  = sub.assigned ? 'checked' : '';
                const gradeOpts = gradeOptions(currentGrades, sub.min_grade);
                html += `
                <div class="checkbox-item">
                    <input type="checkbox" class="subject-checkbox" id="add_s_${sub.id}" value="${sub.id}" ${checked}>
                    <div class="item-info">
                        <label for="add_s_${sub.id}" class="subject-name mb-0" style="cursor:pointer">${escHtml(sub.subject)}</label>
                        <div class="subject-meta">
                            <span>${escHtml(sub.subject_code)}</span>
                            <span class="ms-2"><i class="ri-user-line"></i> ${escHtml(sub.teacher)}</span>
                            ${sub.assigned ? '<span class="ms-2 text-warning fw-semibold">Already assigned</span>' : ''}
                        </div>
                    </div>
                    <select class="grade-select" data-subject-id="${sub.id}" title="Minimum grade">
                        ${gradeOpts}
                    </select>
                </div>`;
            });
            $list.html(html);

        } catch (err) {
            $list.html('<div class="checkbox-empty text-danger">Failed to load subjects. Please try again.</div>');
        }
    }

    /* Trigger load when class / term / session changes */
    $('#add_classid, #add_termid, #add_sessionid').on('change', loadSubjectsForAdd);

    /* ══════════════════════════════════════════════════
       ADD — SUBMIT
    ══════════════════════════════════════════════════ */
    $('#addBtn').on('click', async function () {
        const classId   = $('#add_classid').val();
        const termId    = $('#add_termid').val();
        const sessionId = $('#add_sessionid').val();

        const checked = $('.subject-checkbox:checked');
        if (!classId) { Swal.fire('Error', 'Please select a class.', 'error'); return; }
        if (!checked.length) { Swal.fire('Error', 'Please select at least one subject.', 'error'); return; }

        const body = new FormData();
        body.append('_token',       CSRF);
        body.append('schoolclassid', classId);
        if (termId)    body.append('termid',    termId);
        if (sessionId) body.append('sessionid', sessionId);

        checked.each(function () {
            const sid = $(this).val();
            body.append('subjectId[]', sid);
            const grade = $(`.grade-select[data-subject-id="${sid}"]`).val();
            if (grade) body.append(`min_grades[${sid}]`, grade);
        });

        showLoading(true);
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Adding…');

        try {
            const res  = await fetch(URL_STORE, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', title: 'Added!', text: data.message, timer: 2500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Failed.');
                Swal.fire('Error', msg, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'An error occurred. Please try again.', 'error');
        } finally {
            showLoading(false);
            btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Add Compulsory Subject(s)');
        }
    });

    /* ══════════════════════════════════════════════════
       EDIT — open modal
    ══════════════════════════════════════════════════ */
    async function loadEditSubjects(classId, termId, sessionId, selectedSubjectId, selectedGrade) {
        const $sel = $('#edit_subjectid');
        $sel.html('<option value="">Loading…</option>').prop('disabled', true);
        $('#edit_minGrade').html('<option value="">Loading…</option>').prop('disabled', true);

        try {
            const params = new URLSearchParams({ classid: classId });
            if (termId)    params.append('termid',    termId);
            if (sessionId) params.append('sessionid', sessionId);

            const res  = await fetch(URL_SUBJECTS + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();

            if (!res.ok || !data.success) { $sel.html('<option value="">Error loading subjects</option>'); return; }

            // Populate subject dropdown
            let subOpts = '<option value="">— Select Subject —</option>';
            (data.subjects || []).forEach(s => {
                subOpts += `<option value="${s.id}" ${String(s.id) === String(selectedSubjectId) ? 'selected' : ''}>${escHtml(s.subject)} (${escHtml(s.subject_code)})</option>`;
            });
            $sel.html(subOpts).prop('disabled', false);

            // Populate grade scale
            const grades = data.grade_scale || [];
            $('#edit_minGrade').html(gradeOptions(grades, selectedGrade)).prop('disabled', false);

        } catch (err) {
            $sel.html('<option value="">Error</option>');
        }
    }

    $(document).on('click', '.edit-btn', async function () {
        const id        = $(this).data('id');
        const classId   = $(this).data('class-id');
        const subjectId = $(this).data('subject-id');
        const termId    = $(this).data('term-id') || '';
        const sessionId = $(this).data('session-id') || '';
        const minGrade  = $(this).data('min-grade') || '';

        $('#edit_id').val(id);
        $('#edit_classid').val(classId);
        $('#edit_termid').val(termId);
        $('#edit_sessionid').val(sessionId);
        $('#editAlertError').addClass('d-none');

        await loadEditSubjects(classId, termId, sessionId, subjectId, minGrade);
    });

    /* Re-load subjects when class/term/session changes in edit modal */
    $('#edit_classid, #edit_termid, #edit_sessionid').on('change', function () {
        const classId   = $('#edit_classid').val();
        const termId    = $('#edit_termid').val();
        const sessionId = $('#edit_sessionid').val();
        if (classId) loadEditSubjects(classId, termId, sessionId, '', '');
    });

    /* ══════════════════════════════════════════════════
       EDIT — submit
    ══════════════════════════════════════════════════ */
    $('#updateBtn').on('click', async function () {
        const id        = $('#edit_id').val();
        const classId   = $('#edit_classid').val();
        const subjectId = $('#edit_subjectid').val();
        const termId    = $('#edit_termid').val();
        const sessionId = $('#edit_sessionid').val();
        const minGrade  = $('#edit_minGrade').val();

        if (!classId)   { Swal.fire('Error', 'Please select a class.', 'error');   return; }
        if (!subjectId) { Swal.fire('Error', 'Please select a subject.', 'error'); return; }

        const body = new FormData();
        body.append('_token',       CSRF);
        body.append('_method',      'PUT');
        body.append('schoolclassid', classId);
        body.append('subjectId',    subjectId);
        if (termId)    body.append('termid',    termId);
        if (sessionId) body.append('sessionid', sessionId);
        if (minGrade)  body.append('min_grade', minGrade);

        showLoading(true);
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating…');

        try {
            const res  = await fetch(URL_BASE + '/' + id, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', title: 'Updated!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Failed.');
                Swal.fire('Error', msg, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'An error occurred.', 'error');
        } finally {
            showLoading(false);
            btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Update');
        }
    });

    /* ══════════════════════════════════════════════════
       DELETE — single
    ══════════════════════════════════════════════════ */
    $(document).on('click', '.delete-btn', function () {
        deleteSingleId = $(this).data('id');
        const name     = $(this).data('name');
        const cls      = $(this).data('class');
        $('#deleteItemInfo').html(`<strong>${escHtml(name)}</strong> from <strong>${escHtml(cls)}</strong>`);
        $('#deleteModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', async function () {
        if (!deleteSingleId) return;

        showLoading(true);
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting…');

        try {
            const res  = await fetch(URL_BASE + '/' + deleteSingleId, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', title: 'Removed!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Failed to remove.', 'error');
                $('#deleteModal').modal('hide');
            }
        } catch (err) {
            Swal.fire('Error', 'An error occurred.', 'error');
            $('#deleteModal').modal('hide');
        } finally {
            showLoading(false);
            btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i>Yes, Remove');
            deleteSingleId = null;
        }
    });

    /* ── Clean up modals on close ── */
    $('#addModal').on('hidden.bs.modal', function () {
        $('#add_classid, #add_termid, #add_sessionid').val('');
        $('#add_subjectList').html('<div class="checkbox-empty"><i class="ri-arrow-up-line"></i> Select a class above to load its subjects.</div>');
        $('#add_gradeScaleInfo').text('');
        $('#addAlertError').addClass('d-none');
    });

    $('#editModal').on('hidden.bs.modal', function () {
        $('#edit_id, #edit_classid, #edit_termid, #edit_sessionid').val('');
        $('#edit_subjectid, #edit_minGrade').html('<option value=""></option>');
        $('#editAlertError').addClass('d-none');
    });

    $('#deleteModal, #bulkDeleteModal').on('hidden.bs.modal', function () {
        deleteSingleId = null;
    });
});
</script>
@endsection
