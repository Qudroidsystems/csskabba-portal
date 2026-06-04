{{-- resources/views/classcategories/index.blade.php --}}
@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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

.category-table th {
    background: var(--pay-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
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

.form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
    margin-top: 0;
}
.form-check-input:checked {
    background-color: var(--pay-accent);
    border-color: var(--pay-accent);
}

.dataTables_wrapper .dataTables_filter input,
.search-box .form-control {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_filter input:focus,
.search-box .form-control:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.search-box {
    position: relative;
}
.search-box .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--pay-muted);
    pointer-events: none;
}
.search-box .form-control {
    padding-right: 36px;
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
    overflow: hidden;
}
.modal-hero-bar::before {
    content: '';
    position: absolute; top: -25px; right: -25px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.modal-hero-bar h5 {
    color: #fff;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
    position: relative;
}
.modal-hero-bar .btn-close {
    position: absolute;
    top: 16px;
    right: 20px;
    filter: invert(1);
}
.modal-body {
    padding: 24px;
}
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.form-control, .form-select {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 14px;
    transition: border .15s;
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
}
.btn {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: all .15s;
}
.btn-primary {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    border: none;
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
    color: var(--pay-accent);
}
.btn-outline-primary:hover {
    background: var(--pay-accent);
    border-color: var(--pay-accent);
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
}
.badge-junior {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
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

.pagination {
    gap: 5px;
}
.pagination .page-link {
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    color: var(--pay-primary);
    border: 1px solid var(--pay-border);
}
.pagination .page-item.active .page-link {
    background: var(--pay-accent);
    border-color: var(--pay-accent);
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
                    <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="ri-add-line me-1"></i>Create Category
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search categories or assessments...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table category-table w-100 mb-0" id="categoriesTable">
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
                    <tbody>
                        @php $i = ($classcategories->currentPage() - 1) * $classcategories->perPage() + 1; @endphp
                        @forelse ($classcategories as $sc)
                            @php
                                $assessment = $sc->assessments->first();
                                $subAssessments = $assessment ? $assessment->subAssessments : collect();
                            @endphp
                            <tr>
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
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    {{ $subAssessments->count() }} Sub-assessment(s)
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">No Assessment</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sc->is_senior ? 'badge-senior' : 'badge-junior' }}">
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
                                                    class="btn btn-subtle-secondary btn-icon edit-category-btn"
                                                    data-id="{{ $sc->id }}"
                                                    data-category="{{ $sc->category }}"
                                                    data-is_senior="{{ $sc->is_senior ? 1 : 0 }}"
                                                    data-assessment-name="{{ $assessment->name ?? '' }}"
                                                    data-sub-assessments='@json($subAssessments)'>
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endcan
                                        @can('Delete class-category')
                                            <button type="button"
                                                    class="btn btn-subtle-danger btn-icon delete-category-btn"
                                                    data-id="{{ $sc->id }}"
                                                    data-name="{{ $sc->category }}">
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
                                            <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                                <i class="ri-add-line me-1"></i>Create your first category
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                <div class="row align-items-center">
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
</div>

{{-- ADD CATEGORY MODAL --}}
<div id="addCategoryModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-add-line me-2"></i>Create New Class Category</h5>
            </div>
            <form id="addCategoryForm">
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
                                    <span class="badge badge-junior">Junior</span>
                                    <small class="text-muted d-block">(A, B, C, D, F)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_senior" id="senior" value="1">
                                <label class="form-check-label" for="senior">
                                    <span class="badge badge-senior">Senior</span>
                                    <small class="text-muted d-block">(A1, B2, B3, C4, C5, C6, D7, E8, F9)</small>
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
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-sub-btn">
                            <i class="ri-add-line me-1"></i>Add Sub Assessment
                        </button>
                        <div class="form-text text-muted mt-2">At least one sub-assessment with a valid max score is required.</div>
                    </div>

                    <div class="alert alert-danger d-none" id="addAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addBtn">
                        <i class="ri-save-line me-1"></i>Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT CATEGORY MODAL --}}
<div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-edit-line me-2"></i>Edit Class Category</h5>
            </div>
            <form id="editCategoryForm">
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
                                    <span class="badge badge-junior">Junior</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_senior" id="edit_senior" value="1">
                                <label class="form-check-label" for="edit_senior">
                                    <span class="badge badge-senior">Senior</span>
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
                        <button type="button" class="btn btn-outline-primary btn-sm" id="edit-sub-btn">
                            <i class="ri-add-line me-1"></i>Add Sub Assessment
                        </button>
                    </div>

                    <div class="alert alert-danger d-none" id="editAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <i class="ri-save-line me-1"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#categoriesTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: '',
            searchPlaceholder: 'Search categories...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ categories',
            infoEmpty: 'No categories found',
            zeroRecords: 'No matching categories',
        },
        columnDefs: [
            { orderable: false, targets: [5] },
            { orderable: true, targets: [0, 1, 2, 3, 4] }
        ],
        dom: 'rtip',
    });

    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    let addSubIndex = 0;
    let editSubIndex = 0;
    let deleteCategoryId = null;

    function showLoading(show) {
        $('#loadingOverlay').toggleClass('active', show);
    }

    function addSubAssessment(containerId, subData = null, isEdit = false) {
        const container = document.getElementById(containerId);
        const currentIndex = isEdit ? editSubIndex++ : addSubIndex++;
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
                        <button type="button" class="btn btn-outline-danger w-100" onclick="$(this).closest('.sub-assessment-row').remove();">
                            <i class="ri-delete-bin-line"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        $(container).append(subHtml);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m];
        });
    }

    // Add initial sub assessment
    addSubAssessment('add-sub-container', null, false);

    $('#add-sub-btn').click(function() {
        addSubAssessment('add-sub-container', null, false);
    });

    $('#edit-sub-btn').click(function() {
        addSubAssessment('edit-sub-container', null, true);
    });

    // Add Category Form Submit
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();

        const category = $('#category').val().trim();
        const isSenior = $('input[name="is_senior"]:checked').val();
        const assessmentName = $('#assessment_name').val().trim();

        if (!category) {
            Swal.fire('Error', 'Please enter a category name.', 'error');
            return;
        }
        if (!assessmentName) {
            Swal.fire('Error', 'Please enter an assessment name.', 'error');
            return;
        }

        const subRows = $('#add-sub-container .sub-assessment-row');
        const subAssessments = [];
        let validSubs = 0;

        subRows.each(function() {
            const maxScore = parseFloat($(this).find('input[name*="[max_score]"]').val());
            if (!isNaN(maxScore) && maxScore >= 0) {
                validSubs++;
                subAssessments.push({
                    name: $(this).find('input[name*="[name]"]').val() || null,
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
            is_senior: isSenior,
            assessments: [{
                name: assessmentName,
                sub_assessments: subAssessments
            }]
        };

        showLoading(true);
        $('#addBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');

        $.ajax({
            url: '{{ route("classcategories.store") }}',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to create category.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                showLoading(false);
                $('#addBtn').prop('disabled', false).html('<i class="ri-save-line me-1"></i>Create Category');
            }
        });
    });

    // Edit Category Button
    $(document).on('click', '.edit-category-btn', function() {
        const id = $(this).data('id');
        const category = $(this).data('category');
        const isSenior = $(this).data('is_senior');
        const assessmentName = $(this).data('assessment-name');
        const subAssessments = $(this).data('sub-assessments');

        $('#edit_id').val(id);
        $('#edit_category').val(category);
        $('#edit_assessment_name').val(assessmentName);

        if (isSenior == 1) {
            $('#edit_senior').prop('checked', true);
        } else {
            $('#edit_junior').prop('checked', true);
        }

        $('#edit-sub-container').empty();
        editSubIndex = 0;

        if (subAssessments && subAssessments.length > 0) {
            subAssessments.forEach(function(sub) {
                addSubAssessment('edit-sub-container', sub, true);
            });
        } else {
            addSubAssessment('edit-sub-container', null, true);
        }

        $('#editModal').modal('show');
    });

    // Edit Category Form Submit
    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();

        const id = $('#edit_id').val();
        const category = $('#edit_category').val().trim();
        const isSenior = $('input[name="is_senior"]:checked').val();
        const assessmentName = $('#edit_assessment_name').val().trim();

        const subRows = $('#edit-sub-container .sub-assessment-row');
        const subAssessments = [];
        let validSubs = 0;

        subRows.each(function() {
            const maxScore = parseFloat($(this).find('input[name*="[max_score]"]').val());
            if (!isNaN(maxScore) && maxScore >= 0) {
                validSubs++;
                subAssessments.push({
                    name: $(this).find('input[name*="[name]"]').val() || null,
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
            is_senior: isSenior,
            assessments: [{
                name: assessmentName,
                sub_assessments: subAssessments
            }]
        };

        showLoading(true);
        $('#updateBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        $.ajax({
            url: '{{ route("classcategories.updateclasscategory") }}',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update category.', 'error');
            },
            complete: function() {
                showLoading(false);
                $('#updateBtn').prop('disabled', false).html('<i class="ri-save-line me-1"></i>Update Category');
            }
        });
    });

    // Delete Category Button
    $(document).on('click', '.delete-category-btn', function() {
        deleteCategoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        $('#deleteItemName').html(`<strong>${escapeHtml(categoryName)}</strong> will be permanently deleted.`);
        $('#deleteRecordModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!deleteCategoryId) return;

        showLoading(true);
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');

        $.ajax({
            url: '{{ route("classcategories.destroy", "") }}/' + deleteCategoryId,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete category.', 'error');
                $('#deleteRecordModal').modal('hide');
            },
            complete: function() {
                showLoading(false);
                btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i>Yes, Delete');
                deleteCategoryId = null;
            }
        });
    });
});
</script>
@endsection
