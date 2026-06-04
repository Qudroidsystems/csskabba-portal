{{-- resources/views/classcategories/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent:  #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-danger:  #dc2626;
    --pay-purple:  #7c3aed;
    --pay-muted:   #6b7280;
    --pay-border:  #e2e8f0;
    --pay-bg:      #f8fafc;
    --pay-radius:  12px;
    --pay-shadow:  0 2px 8px rgba(0,0,0,.08);
}

.loading-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.loading-overlay.active { display: flex; }
.loading-spinner {
    background: white; padding: 24px 32px; border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18); text-align: center;
}
.loading-spinner .spinner-border { width: 2.5rem; height: 2.5rem; }
.loading-spinner p { margin: 10px 0 0; font-size: 14px; font-weight: 600; color: var(--pay-primary); }

.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.pay-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pay-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

.stat-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--pay-shadow); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--pay-primary); }
.stat-card .stat-label { font-size: 12px; color: var(--pay-muted); margin-top: 4px; }
.stat-card .stat-icon  { font-size: 32px; opacity: .12; float: right; margin-top: -8px; }

.category-table {
    width: 100%;
    border-collapse: collapse;
}
.category-table th {
    background: var(--pay-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    text-align: left;
}
.category-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--pay-border);
    font-size: 13px;
}
.category-table tr:hover td { background: #f0f9ff; }

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all .15s;
    border: none;
    cursor: pointer;
}
.btn-subtle-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.btn-subtle-secondary:hover {
    background: #e2e8f0;
    color: #1e293b;
    transform: translateY(-1px);
}
.btn-subtle-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.btn-subtle-danger:hover {
    background: #fee2e2;
    color: #b91c1c;
    transform: translateY(-1px);
}

.search-box {
    position: relative;
}
.search-box .form-control {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    padding: 9px 14px;
    padding-right: 36px;
    font-size: 13px;
    width: 100%;
}
.search-box .form-control:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.search-box .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--pay-muted);
    pointer-events: none;
}

.modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px;
    position: relative;
}
.modal-hero-bar h5 {
    color: #fff;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
}
.modal-hero-bar .btn-close {
    position: absolute;
    top: 16px;
    right: 20px;
    filter: invert(1);
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
}
.modal-body {
    padding: 24px;
}
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}
.form-control, .form-select {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 14px;
    width: 100%;
    box-sizing: border-box;
}
.form-control:focus, .form-select:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
.modal-footer {
    padding: 16px 24px 24px;
    border-top: none;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.btn {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: all .15s;
    cursor: pointer;
    border: none;
}
.btn-primary {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
}
.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,.3);
}
.btn-light {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
}
.btn-light:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}
.btn-outline-primary {
    border: 1.5px solid var(--pay-accent);
    background: transparent;
    color: var(--pay-accent);
}
.btn-outline-primary:hover {
    background: var(--pay-accent);
    color: white;
}
.btn-outline-danger {
    border: 1.5px solid #dc2626;
    background: transparent;
    color: #dc2626;
}
.btn-outline-danger:hover {
    background: #dc2626;
    color: white;
}

.sub-assessment-row {
    background: #f8fafc;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 1px solid var(--pay-border);
}

.badge-senior {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}
.badge-junior {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

.empty-state {
    text-align: center;
    padding: 52px 24px;
    color: var(--pay-muted);
}
.empty-state i {
    font-size: 3rem;
    opacity: .25;
    display: block;
    margin-bottom: 14px;
}

.alert {
    border: none;
    border-radius: 10px;
    padding: 14px 18px;
    font-size: 13px;
    margin-bottom: 20px;
}
.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border-left: 3px solid #dc2626;
}
.alert-success {
    background: #f0fdf4;
    color: #166534;
    border-left: 3px solid #16a34a;
}
.alert-warning {
    background: #fffbeb;
    color: #92400e;
    border-left: 3px solid #f59e0b;
}

.pagination {
    display: flex;
    gap: 5px;
    list-style: none;
    padding: 0;
    margin: 0;
}
.pagination .page-item .page-link {
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    color: var(--pay-primary);
    border: 1px solid var(--pay-border);
    background: white;
    text-decoration: none;
}
.pagination .page-item.active .page-link {
    background: var(--pay-accent);
    border-color: var(--pay-accent);
    color: white;
}
.pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.d-none {
    display: none;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -8px;
}
.col-md-3, .col-md-4, .col-sm, .col-sm-auto {
    padding: 8px;
}
.col-md-3 { width: 25%; }
.col-md-4 { width: 33.333%; }
.col-sm { flex: 1; }
.col-sm-auto { flex: 0 0 auto; }

.gap-2 { gap: 8px; }
.gap-3 { gap: 16px; }
.gap-4 { gap: 24px; }
.mb-0 { margin-bottom: 0; }
.mb-1 { margin-bottom: 4px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 16px; }
.mb-4 { margin-bottom: 24px; }
.mt-1 { margin-top: 4px; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 16px; }
.p-3 { padding: 16px; }
.py-3 { padding-top: 16px; padding-bottom: 16px; }
.px-4 { padding-left: 24px; padding-right: 24px; }
.pb-4 { padding-bottom: 24px; }
.pt-0 { padding-top: 0; }
.text-center { text-align: center; }
.text-start { text-align: left; }
.text-end { text-align: right; }
.text-muted { color: var(--pay-muted); }
.text-success { color: var(--pay-success); }
.text-warning { color: var(--pay-warning); }
.text-danger { color: var(--pay-danger); }
.fw-semibold { font-weight: 600; }
.fw-bold { font-weight: 700; }
.small { font-size: 11px; }

.card {
    background: white;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    box-shadow: var(--pay-shadow);
}
.card-header {
    border-bottom: 1px solid var(--pay-border);
    background: white;
}
.card-body {
    padding: 20px;
}

.table-responsive {
    overflow-x: auto;
}

.d-flex {
    display: flex;
}
.align-items-center {
    align-items: center;
}
.justify-content-between {
    justify-content: space-between;
}
.justify-content-center {
    justify-content: center;
}
.flex-wrap {
    flex-wrap: wrap;
}
.flex-grow-1 {
    flex-grow: 1;
}
.flex-shrink-0 {
    flex-shrink: 0;
}

.badge {
    background: #f1f5f9;
    color: #475569;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.bg-primary {
    background: var(--pay-accent);
    color: white;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
            <p>Processing…</p>
        </div>
    </div>

    <div class="pay-hero">
        <h1><i class="ri-bookmark-line me-2"></i>Class Category Management</h1>
        <p>Manage class categories and their assessment configurations for grading systems.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-bookmark-line"></i></div>
                <div class="stat-value">{{ $classcategories->total() }}</div>
                <div class="stat-label">Total Categories</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-bar-chart-line"></i></div>
                <div class="stat-value text-primary">{{ $classcategories->count() }}</div>
                <div class="stat-label">Showing Now</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-school-line"></i></div>
                <div class="stat-value text-success">
                    {{ $classcategories->where('is_senior', true)->count() }}
                </div>
                <div class="stat-label">Senior Categories</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-graduation-cap-line"></i></div>
                <div class="stat-value text-warning">
                    {{ $classcategories->where('is_senior', false)->count() }}
                </div>
                <div class="stat-label">Junior Categories</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                <i class="ri-list-check me-2"></i>Class Categories List
                <span class="badge bg-primary ms-2">{{ $classcategories->total() }}</span>
            </h5>
            <div class="d-flex gap-2">
                @can('Create class-category')
                    <button type="button" class="btn btn-primary" onclick="openAddModal()">
                        <i class="ri-add-line me-1"></i>Create Category
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search categories or assessments...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="float: right; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Category Name</th>
                            <th>Assessment</th>
                            <th>Grade Type</th>
                            <th width="120">Last Updated</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @php $i = ($classcategories->currentPage() - 1) * $classcategories->perPage() + 1; @endphp
                        @forelse ($classcategories as $sc)
                            @php
                                $assessment = $sc->assessments->first();
                                $subAssessments = $assessment ? $assessment->subAssessments : collect();
                            @endphp
                            <tr data-id="{{ $sc->id }}">
                                <td class="sn">{{ $i++ }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $sc->category }}</span>
                                    <small class="text-muted d-block">ID: {{ $sc->id }}</small>
                                </td>
                                <td>
                                    @if($assessment)
                                        <div class="fw-semibold">{{ $assessment->name }}</div>
                                        <div class="small text-muted">Max Score: {{ number_format($assessment->max_score, 2) }}</div>
                                        @if($subAssessments->count() > 0)
                                            <div class="mt-1">
                                                <span class="badge">
                                                    {{ $subAssessments->count() }} Sub-assessment(s)
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">No Assessment</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $sc->is_senior ? 'badge-senior' : 'badge-junior' }}">
                                        {{ $sc->is_senior ? 'Senior' : 'Junior' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $sc->updated_at->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('Update class-category')
                                            <button type="button"
                                                    class="btn-icon btn-subtle-secondary"
                                                    onclick="openEditModal({{ $sc->id }}, '{{ addslashes($sc->category) }}', {{ $sc->is_senior ? 1 : 0 }}, '{{ addslashes($assessment->name ?? '') }}', {{ json_encode($subAssessments) }})">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endcan
                                        @can('Delete class-category')
                                            <button type="button"
                                                    class="btn-icon btn-subtle-danger"
                                                    onclick="openDeleteModal({{ $sc->id }}, '{{ addslashes($sc->category) }}')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="ri-inbox-line"></i>
                                        <p>No class categories found.</p>
                                        @can('Create class-category')
                                            <button class="btn btn-primary btn-sm mt-3" onclick="openAddModal()">
                                                <i class="ri-add-line me-1"></i>Create your first category
                                            </button>
                                        @endcan
                                    </div>
                                </tr>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row align-items-center mt-3">
                <div class="col-sm">
                    <div class="text-muted text-center text-sm-start">
                        Showing <span class="fw-semibold">{{ $classcategories->count() }}</span> of <span class="fw-semibold">{{ $classcategories->total() }}</span> categories
                    </div>
                </div>
                <div class="col-sm-auto mt-3 mt-sm-0">
                    {{ $classcategories->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ADD CATEGORY MODAL --}}
<div id="addCategoryModal" class="modal fade" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; align-items: center; justify-content: center;">
    <div class="modal-dialog" style="max-width: 700px; width: 90%; margin: auto;">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" onclick="closeAddModal()">&times;</button>
                <h5><i class="ri-add-line me-2"></i>Create New Class Category</h5>
            </div>
            <form id="addCategoryForm" onsubmit="submitAddForm(event)">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category" id="category" class="form-control" placeholder="e.g., Science, Arts, Commercial" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_senior" id="junior" value="0" checked>
                                <label class="form-check-label" for="junior">
                                    <span class="badge-junior">Junior (A, B, C, D, F)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_senior" id="senior" value="1">
                                <label class="form-check-label" for="senior">
                                    <span class="badge-senior">Senior (A1, B2, B3, C4, C5, C6, D7, E8, F9)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="assessment_name" class="form-label">Assessment Name <span class="text-danger">*</span></label>
                        <input type="text" name="assessments[0][name]" id="assessment_name" class="form-control" placeholder="e.g., First Term Examination" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sub Assessments <span class="text-danger">*</span></label>
                        <div id="add-sub-container" class="mb-2"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubAssessmentField('add')">
                            <i class="ri-add-line me-1"></i>Add Sub Assessment
                        </button>
                        <div class="form-text text-muted mt-2">At least one sub-assessment with a valid max score is required.</div>
                    </div>

                    <div class="alert alert-danger d-none" id="addAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addBtn">
                        <i class="ri-save-line me-1"></i>Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT CATEGORY MODAL --}}
<div id="editModal" class="modal fade" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; align-items: center; justify-content: center;">
    <div class="modal-dialog" style="max-width: 700px; width: 90%; margin: auto;">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" onclick="closeEditModal()">&times;</button>
                <h5><i class="ri-edit-line me-2"></i>Edit Class Category</h5>
            </div>
            <form id="editCategoryForm" onsubmit="submitEditForm(event)">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category" id="edit_category" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_senior" id="edit_junior" value="0">
                                <label class="form-check-label" for="edit_junior">
                                    <span class="badge-junior">Junior</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_senior" id="edit_senior" value="1">
                                <label class="form-check-label" for="edit_senior">
                                    <span class="badge-senior">Senior</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_assessment_name" class="form-label">Assessment Name <span class="text-danger">*</span></label>
                        <input type="text" name="assessments[0][name]" id="edit_assessment_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sub Assessments <span class="text-danger">*</span></label>
                        <div id="edit-sub-container" class="mb-2"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubAssessmentField('edit')">
                            <i class="ri-add-line me-1"></i>Add Sub Assessment
                        </button>
                    </div>

                    <div class="alert alert-danger d-none" id="editAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <i class="ri-save-line me-1"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div id="deleteRecordModal" class="modal fade" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; align-items: center; justify-content: center;">
    <div class="modal-dialog" style="max-width: 400px; width: 90%; margin: auto;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end; padding: 16px;">
                <button type="button" class="btn-close" onclick="closeDeleteModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-3">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-delete-bin-line" style="font-size: 28px; color: #dc2626;"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">You won't be able to revert this action!</p>
                    <p class="text-muted small mt-2" id="deleteItemName"></p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-light" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDelete()">
                    <i class="ri-delete-bin-line me-1"></i>Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables
let addSubIndex = 0;
let editSubIndex = 0;
let deleteCategoryId = null;
let deleteCategoryName = null;

// Route URLs
const storeUrl = '{{ route("classcategories.store") }}';
const updateUrl = '{{ route("classcategories.updateclasscategory") }}';
const deleteUrlBase = '{{ url("classcategories") }}';

function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.add('active');
    } else {
        overlay.classList.remove('active');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m];
    });
}

// Modal functions
function openAddModal() {
    document.getElementById('addCategoryModal').style.display = 'flex';
    addSubIndex = 0;
    document.getElementById('add-sub-container').innerHTML = '';
    addSubAssessmentField('add');
    document.getElementById('category').value = '';
    document.getElementById('assessment_name').value = '';
    document.getElementById('junior').checked = true;
    document.getElementById('addAlertError').classList.add('d-none');
}

function closeAddModal() {
    document.getElementById('addCategoryModal').style.display = 'none';
}

function openEditModal(id, category, isSenior, assessmentName, subAssessments) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_assessment_name').value = assessmentName;

    if (isSenior == 1) {
        document.getElementById('edit_senior').checked = true;
    } else {
        document.getElementById('edit_junior').checked = true;
    }

    document.getElementById('edit-sub-container').innerHTML = '';
    editSubIndex = 0;

    if (subAssessments && subAssessments.length > 0) {
        subAssessments.forEach(function(sub) {
            addSubAssessmentField('edit', sub);
        });
    } else {
        addSubAssessmentField('edit', null);
    }

    document.getElementById('editAlertError').classList.add('d-none');
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openDeleteModal(id, name) {
    deleteCategoryId = id;
    deleteCategoryName = name;
    document.getElementById('deleteItemName').innerHTML = `<strong>${escapeHtml(name)}</strong> will be permanently deleted.`;
    document.getElementById('deleteRecordModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteRecordModal').style.display = 'none';
    deleteCategoryId = null;
    deleteCategoryName = null;
}

// Sub Assessment functions
function addSubAssessmentField(type, subData = null) {
    const containerId = type === 'add' ? 'add-sub-container' : 'edit-sub-container';
    const container = document.getElementById(containerId);
    const currentIndex = type === 'add' ? addSubIndex++ : editSubIndex++;

    const subHtml = `
        <div class="sub-assessment-row" data-index="${currentIndex}">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="assessments[0][sub_assessments][${currentIndex}][name]"
                           class="form-control" placeholder="Sub Assessment Name"
                           value="${subData && subData.name ? escapeHtml(subData.name) : ''}">
                </div>
                <div class="col-md-4">
                    <input type="number" name="assessments[0][sub_assessments][${currentIndex}][max_score]"
                           class="form-control" placeholder="Max Score" min="0" step="0.01"
                           value="${subData && subData.max_score ? subData.max_score : ''}" required>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.sub-assessment-row').remove();">
                        <i class="ri-delete-bin-line"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', subHtml);
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.indexOf(value) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Add Category Form Submit
async function submitAddForm(event) {
    event.preventDefault();

    const category = document.getElementById('category').value.trim();
    const isSenior = document.querySelector('input[name="is_senior"]:checked').value;
    const assessmentName = document.getElementById('assessment_name').value.trim();

    if (!category) {
        Swal.fire('Error', 'Please enter a category name.', 'error');
        return;
    }
    if (!assessmentName) {
        Swal.fire('Error', 'Please enter an assessment name.', 'error');
        return;
    }

    const subRows = document.querySelectorAll('#add-sub-container .sub-assessment-row');
    const subAssessments = [];
    let validSubs = 0;

    subRows.forEach(row => {
        const maxScoreInput = row.querySelector('input[name*="[max_score]"]');
        const maxScore = parseFloat(maxScoreInput.value);
        if (!isNaN(maxScore) && maxScore >= 0) {
            validSubs++;
            subAssessments.push({
                name: row.querySelector('input[name*="[name]"]').value || null,
                max_score: maxScore
            });
        }
    });

    if (validSubs === 0) {
        Swal.fire('Error', 'Please add at least one valid sub-assessment with a max score.', 'error');
        return;
    }

    const formData = {
        category: category,
        is_senior: parseInt(isSenior),
        assessments: [{
            name: assessmentName,
            sub_assessments: subAssessments
        }]
    };

    showLoading(true);
    const submitBtn = document.getElementById('addBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';

    try {
        const response = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to create category.', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'An error occurred. Please try again.', 'error');
    } finally {
        showLoading(false);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Edit Category Form Submit
async function submitEditForm(event) {
    event.preventDefault();

    const id = document.getElementById('edit_id').value;
    const category = document.getElementById('edit_category').value.trim();
    const isSenior = document.querySelector('input[name="is_senior"]:checked').value;
    const assessmentName = document.getElementById('edit_assessment_name').value.trim();

    const subRows = document.querySelectorAll('#edit-sub-container .sub-assessment-row');
    const subAssessments = [];
    let validSubs = 0;

    subRows.forEach(row => {
        const maxScoreInput = row.querySelector('input[name*="[max_score]"]');
        const maxScore = parseFloat(maxScoreInput.value);
        if (!isNaN(maxScore) && maxScore >= 0) {
            validSubs++;
            subAssessments.push({
                name: row.querySelector('input[name*="[name]"]').value || null,
                max_score: maxScore
            });
        }
    });

    if (validSubs === 0) {
        Swal.fire('Error', 'Please add at least one valid sub-assessment with a max score.', 'error');
        return;
    }

    const formData = {
        id: id,
        category: category,
        is_senior: parseInt(isSenior),
        assessments: [{
            name: assessmentName,
            sub_assessments: subAssessments
        }]
    };

    showLoading(true);
    const submitBtn = document.getElementById('updateBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';

    try {
        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to update category.', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'An error occurred. Please try again.', 'error');
    } finally {
        showLoading(false);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Confirm Delete
async function confirmDelete() {
    if (!deleteCategoryId) return;

    showLoading(true);
    const btn = document.getElementById('confirmDeleteBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

    try {
        const response = await fetch(deleteUrlBase + '/' + deleteCategoryId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to delete category.', 'error');
            closeDeleteModal();
        }
    } catch (error) {
        Swal.fire('Error', 'An error occurred. Please try again.', 'error');
        closeDeleteModal();
    } finally {
        showLoading(false);
        btn.disabled = false;
        btn.innerHTML = originalText;
        deleteCategoryId = null;
    }
}

// Initialize with one sub-assessment field on page load
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking outside
    window.onclick = function(event) {
        const addModal = document.getElementById('addCategoryModal');
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteRecordModal');

        if (event.target === addModal) {
            closeAddModal();
        }
        if (event.target === editModal) {
            closeEditModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    };

    // Add initial sub assessment fields for add modal (will be added when modal opens)
    // The first sub assessment will be added when openAddModal() is called
});

// Override openAddModal to add initial sub assessment
const originalOpenAddModal = openAddModal;
window.openAddModal = function() {
    originalOpenAddModal();
    // Ensure at least one sub assessment field
    if (document.querySelectorAll('#add-sub-container .sub-assessment-row').length === 0) {
        addSubAssessmentField('add');
    }
};
</script>
@endsection
