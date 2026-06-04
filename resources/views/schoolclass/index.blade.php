{{-- resources/views/schoolclass/index.blade.php --}}
@extends('layouts.master')

@section('content')
{{-- Suppress initialization errors from schoolarm.init.js --}}
<script>
    (function() {
        const requiredElements = [
            'addIdField', 'addSubmitButton',
            'editIdField', 'editCategoryField', 'editSubmitButton'
        ];

        requiredElements.forEach(function(id) {
            if (!document.getElementById(id)) {
                var element = document.createElement('input');
                element.type = 'hidden';
                element.id = id;
                element.value = '';
                document.body.appendChild(element);
            }
        });

        if (typeof window.initFormFields === 'function') {
            window.initFormFields = function() { return true; };
        }
        if (typeof window.initializeSchoolArm === 'function') {
            window.initializeSchoolArm = function() { return true; };
        }
    })();
</script>

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

.class-table {
    width: 100%;
    border-collapse: collapse;
}
.class-table th {
    background: var(--pay-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    text-align: left;
}
.class-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--pay-border);
    font-size: 13px;
}
.class-table tr:hover td { background: #f0f9ff; }

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
.modal-header {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px;
    border-bottom: none;
}
.modal-header .modal-title {
    color: #fff;
    font-weight: 700;
    font-size: 15px;
}
.modal-header .btn-close {
    filter: invert(1);
    background: transparent;
    opacity: 0.8;
}
.modal-header .btn-close:hover {
    opacity: 1;
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
.btn-danger {
    background: #dc2626;
    color: white;
}
.btn-danger:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

.checkbox-group, .radio-group {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid var(--pay-border);
    border-radius: 8px;
    padding: 12px;
    background: #f8fafc;
}
.checkbox-item, .radio-item {
    margin-bottom: 8px;
}
.checkbox-item:last-child, .radio-item:last-child {
    margin-bottom: 0;
}
.checkbox-item label, .radio-item label {
    margin-left: 8px;
    font-size: 13px;
    cursor: pointer;
}
.checkbox-item input, .radio-item input {
    cursor: pointer;
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

.d-none {
    display: none;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -8px;
}
.col-md-3, .col-md-4, .col-md-6, .col-sm, .col-sm-auto {
    padding: 8px;
}
.col-md-3 { width: 25%; }
.col-md-4 { width: 33.333%; }
.col-md-6 { width: 50%; }
.col-sm { flex: 1; }
.col-sm-auto { flex: 0 0 auto; }

.gap-2 { gap: 8px; }
.gap-3 { gap: 16px; }
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
.text-center { text-align: center; }
.text-start { text-align: left; }
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
.bg-success {
    background: #16a34a;
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
        <h1><i class="ri-building-line me-2"></i>School Class Management</h1>
        <p>Manage school classes with their respective arms and categories.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value">{{ $all_classes->total() }}</div>
                <div class="stat-label">Total Classes</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-bar-chart-line"></i></div>
                <div class="stat-value text-primary">{{ $all_classes->count() }}</div>
                <div class="stat-label">Showing Now</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-shield-line"></i></div>
                <div class="stat-value text-success">{{ $arms->count() }}</div>
                <div class="stat-label">Total Arms</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-bookmark-line"></i></div>
                <div class="stat-value text-warning">{{ $classcategories->count() }}</div>
                <div class="stat-label">Total Categories</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                <i class="ri-list-check me-2"></i>School Classes List
                <span class="badge bg-primary ms-2">{{ $all_classes->total() }}</span>
            </h5>
            <div class="d-flex gap-2">
                @can('Create school-class')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                        <i class="ri-add-line me-1"></i>Create Class
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search classes...">
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="ri-error-warning-line me-2"></i>{{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="class-table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>School Class</th>
                            <th>Arm</th>
                            <th>Category</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @php $i = ($all_classes->currentPage() - 1) * $all_classes->perPage() + 1; @endphp
                        @forelse ($all_classes as $class)
                            <tr>
                                <td class="sn">{{ $i++ }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $class->schoolclass }}</span>
                                    <small class="text-muted d-block">ID: {{ $class->id }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $class->arm_name ?? 'N/A' }}</span>
                                    <small class="text-muted d-block">Arm ID: {{ $class->arm_id ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @php
                                        $categoryNames = explode(', ', $class->classcategory ?? '');
                                    @endphp
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($categoryNames as $catName)
                                            @if(!empty($catName))
                                                <span class="badge bg-primary">{{ $catName }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block">Category IDs: {{ $class->classcategoryids ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('Update school-class')
                                            <button type="button"
                                                    class="btn-icon btn-subtle-secondary edit-class-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $class->id }}"
                                                    data-schoolclass="{{ $class->schoolclass }}"
                                                    data-arm-id="{{ $class->arm_id }}"
                                                    data-category-ids="{{ $class->classcategoryids }}">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endcan
                                        @can('Delete school-class')
                                            <button type="button"
                                                    class="btn-icon btn-subtle-danger delete-class-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteRecordModal"
                                                    data-id="{{ $class->id }}"
                                                    data-name="{{ $class->schoolclass }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="empty-state">
                                        <i class="ri-inbox-line"></i>
                                        <p>No school classes found.</p>
                                        @can('Create school-class')
                                            <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                                <i class="ri-add-line me-1"></i>Create your first class
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
                        Showing <span class="fw-semibold">{{ $all_classes->count() }}</span> of <span class="fw-semibold">{{ $all_classes->total() }}</span> classes
                    </div>
                </div>
                <div class="col-sm-auto mt-3 mt-sm-0">
                    {{ $all_classes->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ADD CLASS MODAL --}}
<div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClassModalLabel">
                    <i class="ri-add-line me-2"></i>Create New School Class
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addClassForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="schoolclass" class="form-label">School Class <span class="text-danger">*</span></label>
                        <input type="text" name="schoolclass" id="schoolclass" class="form-control" placeholder="e.g., JSS 1, SSS 1" required>
                        <div class="form-text">Enter the class name</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Arm(s) <span class="text-danger">*</span></label>
                        <div class="checkbox-group" id="arm-checkboxes">
                            @forelse($arms as $arm)
                                <div class="checkbox-item">
                                    <input type="checkbox" name="arm_id[]" id="arm_{{ $arm->id }}" value="{{ $arm->id }}">
                                    <label for="arm_{{ $arm->id }}">{{ $arm->arm }} <small class="text-muted">(ID: {{ $arm->id }})</small></label>
                                </div>
                            @empty
                                <div class="alert alert-warning mb-0">No arms found. Please add arms first.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Category(s) <span class="text-danger">*</span></label>
                        <div class="checkbox-group" id="category-checkboxes">
                            @forelse($classcategories as $category)
                                <div class="checkbox-item">
                                    <input type="checkbox" name="classcategoryid[]" id="category_{{ $category->id }}" value="{{ $category->id }}">
                                    <label for="category_{{ $category->id }}">{{ $category->category }} <small class="text-muted">(ID: {{ $category->id }})</small></label>
                                </div>
                            @empty
                                <div class="alert alert-warning mb-0">No categories found. Please add categories first.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="addAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addBtn">
                        <i class="ri-save-line me-1"></i>Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT CLASS MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="ri-edit-line me-2"></i>Edit School Class
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClassForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_schoolclass" class="form-label">School Class <span class="text-danger">*</span></label>
                        <input type="text" name="schoolclass" id="edit_schoolclass" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Arm <span class="text-danger">*</span></label>
                        <div class="radio-group" id="edit-arm-radios">
                            @forelse($arms as $arm)
                                <div class="radio-item">
                                    <input type="radio" name="arm_id" id="edit_arm_{{ $arm->id }}" value="{{ $arm->id }}">
                                    <label for="edit_arm_{{ $arm->id }}">{{ $arm->arm }} <small class="text-muted">(ID: {{ $arm->id }})</small></label>
                                </div>
                            @empty
                                <div class="alert alert-warning mb-0">No arms found.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Category(s) <span class="text-danger">*</span></label>
                        <div class="checkbox-group" id="edit-category-checkboxes">
                            @forelse($classcategories as $category)
                                <div class="checkbox-item">
                                    <input type="checkbox" name="classcategoryid[]" id="edit_category_{{ $category->id }}" value="{{ $category->id }}">
                                    <label for="edit_category_{{ $category->id }}">{{ $category->category }} <small class="text-muted">(ID: {{ $category->id }})</small></label>
                                </div>
                            @empty
                                <div class="alert alert-warning mb-0">No categories found.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="editAlertError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <i class="ri-save-line me-1"></i>Update Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="deleteRecordModal" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRecordModalLabel">
                    <i class="ri-delete-bin-line me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-delete-bin-line" style="font-size: 28px; color: #dc2626;"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">You won't be able to revert this action!</p>
                    <p class="text-muted small mt-2" id="deleteItemName"></p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let deleteClassId = null;

    // Route URLs
    const storeUrl = '{{ route("schoolclass.store") }}';
    const updateUrlBase = '{{ url("schoolclass") }}';
    const deleteUrlBase = '{{ url("schoolclass") }}';

    function showLoading(show) {
        $('#loadingOverlay').toggleClass('active', show);
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#tableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Edit button click - populate edit form
    $('.edit-class-btn').on('click', function() {
        const id = $(this).data('id');
        const schoolclass = $(this).data('schoolclass');
        const armId = $(this).data('arm-id');
        const categoryIds = $(this).data('category-ids');

        $('#edit_id').val(id);
        $('#edit_schoolclass').val(schoolclass);

        // Set arm radio
        $('input[name="arm_id"]').prop('checked', false);
        $(`input[name="arm_id"][value="${armId}"]`).prop('checked', true);

        // Set category checkboxes
        const categoryIdsArray = categoryIds ? categoryIds.split(',').map(id => id.trim()) : [];
        $('input[name="classcategoryid[]"]').prop('checked', false);
        categoryIdsArray.forEach(catId => {
            $(`#edit_category_${catId}`).prop('checked', true);
        });

        $('#editAlertError').addClass('d-none');
    });

    // Delete button click
    $('.delete-class-btn').on('click', function() {
        deleteClassId = $(this).data('id');
        const className = $(this).data('name');
        $('#deleteItemName').html(`<strong>${escapeHtml(className)}</strong> will be permanently deleted.`);
    });

    // Add Class Form Submit
    $('#addClassForm').on('submit', async function(e) {
        e.preventDefault();

        const schoolclass = $('#schoolclass').val().trim();
        const selectedArms = $('input[name="arm_id[]"]:checked').map(function() { return $(this).val(); }).get();
        const selectedCategories = $('input[name="classcategoryid[]"]:checked').map(function() { return $(this).val(); }).get();

        if (!schoolclass) {
            Swal.fire('Error', 'Please enter a school class name.', 'error');
            return;
        }

        if (selectedArms.length === 0) {
            Swal.fire('Error', 'Please select at least one arm.', 'error');
            return;
        }

        if (selectedCategories.length === 0) {
            Swal.fire('Error', 'Please select at least one category.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('schoolclass', schoolclass);

        selectedArms.forEach(arm => {
            formData.append('arm_id[]', arm);
        });

        selectedCategories.forEach(cat => {
            formData.append('classcategoryid[]', cat);
        });

        showLoading(true);
        const submitBtn = $('#addBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');

        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.message) {
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
                let errorMsg = data.message || 'Failed to create class.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        } catch (error) {
            console.error('Add error:', error);
            Swal.fire('Error', 'An error occurred. Please try again.', 'error');
        } finally {
            showLoading(false);
            submitBtn.prop('disabled', false).html(originalText);
        }
    });

    // Edit Class Form Submit
    $('#editClassForm').on('submit', async function(e) {
        e.preventDefault();

        const id = $('#edit_id').val();
        const schoolclass = $('#edit_schoolclass').val().trim();
        const selectedArm = $('input[name="arm_id"]:checked').val();
        const selectedCategories = $('input[name="classcategoryid[]"]:checked').map(function() { return $(this).val(); }).get();

        if (!schoolclass) {
            Swal.fire('Error', 'Please enter a school class name.', 'error');
            return;
        }

        if (!selectedArm) {
            Swal.fire('Error', 'Please select an arm.', 'error');
            return;
        }

        if (selectedCategories.length === 0) {
            Swal.fire('Error', 'Please select at least one category.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('_method', 'PUT');
        formData.append('schoolclass', schoolclass);
        formData.append('arm_id', selectedArm);

        selectedCategories.forEach(cat => {
            formData.append('classcategoryid[]', cat);
        });

        showLoading(true);
        const submitBtn = $('#updateBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        try {
            const response = await fetch(updateUrlBase + '/' + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.message) {
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
                let errorMsg = data.message || 'Failed to update class.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        } catch (error) {
            console.error('Edit error:', error);
            Swal.fire('Error', 'An error occurred. Please try again.', 'error');
        } finally {
            showLoading(false);
            submitBtn.prop('disabled', false).html(originalText);
        }
    });

    // Confirm Delete
    $('#confirmDeleteBtn').on('click', async function() {
        if (!deleteClassId) return;

        showLoading(true);
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');

        try {
            const response = await fetch(deleteUrlBase + '/' + deleteClassId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.message) {
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
                Swal.fire('Error', data.message || 'Failed to delete class.', 'error');
                $('#deleteRecordModal').modal('hide');
            }
        } catch (error) {
            console.error('Delete error:', error);
            Swal.fire('Error', 'An error occurred. Please try again.', 'error');
            $('#deleteRecordModal').modal('hide');
        } finally {
            showLoading(false);
            btn.prop('disabled', false).html(originalText);
            deleteClassId = null;
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m];
        });
    }
});
</script>
@endsection
