@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --class-primary:  #1e3a5f;
    --class-accent:   #2563eb;
    --class-success:  #16a34a;
    --class-warning:  #d97706;
    --class-danger:   #dc2626;
    --class-muted:    #6b7280;
    --class-border:   #e2e8f0;
    --class-bg:       #f8fafc;
    --class-radius:   12px;
    --class-shadow:   0 2px 8px rgba(0,0,0,.08);
}

.class-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--class-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.class-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.class-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.class-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

.stat-card {
    background: #fff;
    border: 1px solid var(--class-border);
    border-radius: var(--class-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--class-shadow); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--class-primary); }
.stat-card .stat-label { font-size: 12px; color: var(--class-muted); margin-top: 4px; }
.stat-card .stat-icon  { font-size: 32px; opacity: .12; float: right; margin-top: -8px; }

.class-table th {
    background: var(--class-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.class-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--class-border);
    font-size: 13px;
}
.class-table tr:hover td { background: #f0f9ff; }

.badge-class-item {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
    padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--class-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--class-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* Modal Styles */
.class-modal .modal-content {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,.2);
}
.class-modal-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    padding: 24px 28px;
    position: relative;
    overflow: hidden;
}
.class-modal-hero::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.class-modal-hero h5 { color: #fff; font-weight: 700; font-size: 16px; margin: 0; position: relative; }
.class-modal-hero p  { color: rgba(255,255,255,.72); font-size: 12px; margin: 5px 0 0; position: relative; }
.class-modal-hero .btn-close { position: absolute; top: 18px; right: 20px; filter: invert(1); opacity: .8; }

.class-label {
    font-size: 12px; font-weight: 700;
    color: #374151; margin-bottom: 7px;
    display: flex; align-items: center; gap: 6px;
    text-transform: uppercase; letter-spacing: .04em;
}
.class-label i { color: var(--class-accent); font-size: 14px; }

.class-input, .class-select {
    width: 100%;
    border: 1.5px solid var(--class-border);
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    transition: border .15s, box-shadow .15s;
}
.class-input:focus, .class-select:focus {
    border-color: var(--class-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.class-btn {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff; border: none;
    border-radius: 10px;
    padding: 11px 20px;
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
    transition: opacity .15s, transform .1s;
}
.class-btn:hover { opacity: .91; transform: translateY(-1px); }
.class-btn:active { transform: translateY(0); }

.btn-icon-sm {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}
.btn-icon-sm i { font-size: 16px; }
.btn-icon-sm:hover { transform: scale(1.05); }

.btn-subtle-primary {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
    border: none;
}
.btn-subtle-primary:hover {
    background: rgba(37, 99, 235, 0.2);
    color: #1d4ed8;
}
.btn-subtle-danger {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
    border: none;
}
.btn-subtle-danger:hover {
    background: rgba(220, 38, 38, 0.2);
    color: #b91c1c;
}

.avatar-placeholder {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
    transition: transform 0.2s ease;
}
.avatar-placeholder:hover {
    transform: scale(1.05);
}

.pagination {
    margin-bottom: 0;
}
.pagination .page-link {
    border-radius: 8px;
    margin: 0 3px;
    color: #1e3a5f;
    border: 1px solid #e2e8f0;
}
.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    border-color: #2563eb;
    color: white;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 8px;
}
.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8fafc;
    border: 1px solid var(--class-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.checkbox-item:hover {
    background: #f0f9ff;
    border-color: var(--class-accent);
}
.checkbox-item input {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: var(--class-accent);
}
.checkbox-item label {
    margin: 0;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    color: #374151;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="class-hero">
        <h1><i class="ri-school-line me-2"></i>School Class Management</h1>
        <p>Manage school classes, assign arms and categories efficiently.</p>
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
                <div class="stat-icon"><i class="ri-building-line"></i></div>
                <div class="stat-value text-success">{{ $arms->count() }}</div>
                <div class="stat-label">Available Arms</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-price-tag-3-line"></i></div>
                <div class="stat-value text-warning">{{ $classcategories->count() }}</div>
                <div class="stat-label">Class Categories</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-check-line"></i></div>
                <div class="stat-value text-primary">{{ $all_classes->where('updated_at', '>=', now()->subDays(30))->count() }}</div>
                <div class="stat-label">Recently Updated</div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="ri-error-warning-line me-1"></i>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-1"></i> {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="mb-0 fw-semibold" style="color:var(--class-primary)">
                <i class="ri-list-check me-2"></i>All Classes
                <span class="badge bg-primary ms-2">{{ $all_classes->total() }}</span>
            </h5>
            <div class="d-flex gap-2">
                @can('Create school-class')
                    <button type="button" class="class-btn" data-bs-toggle="modal" data-bs-target="#addClassModal">
                        <i class="ri-add-line"></i> Create Class
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table class-table w-100 mb-0" id="classesTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Class Name</th>
                            <th>Arm</th>
                            <th>Categories</th>
                            <th>Description</th>
                            <th width="120">Last Updated</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($all_classes as $i => $class)
                        <tr data-id="{{ $class->id }}">
                            <td class="text-center fw-semibold">{{ $all_classes->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($class->schoolclass, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $class->schoolclass }}</div>
                                        <div class="text-muted small">ID: {{ $class->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-class-item">
                                    <i class="ri-building-line"></i> {{ $class->arm_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @php
                                        $categories = explode(', ', $class->classcategory ?? '');
                                        $categoryIds = explode(',', $class->classcategoryids ?? '');
                                    @endphp
                                    @foreach($categories as $catIndex => $category)
                                        @if(!empty($category))
                                            <span class="badge bg-light text-dark" style="font-size: 10px; padding: 3px 8px;">
                                                {{ $category }}
                                            </span>
                                        @endif
                                    @endforeach
                                    @if(empty($categories) || (count($categories) == 1 && empty($categories[0])))
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($class->description ?? '—', 50) }}</span>
                            </td>
                            <td>
                                <div class="small">
                                    <div>{{ \Carbon\Carbon::parse($class->updated_at)->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($class->updated_at)->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @can('Update school-class')
                                        <button type="button"
                                                class="btn btn-subtle-primary btn-icon-sm edit-class-btn"
                                                data-id="{{ $class->id }}"
                                                data-class="{{ $class->schoolclass }}"
                                                data-arm-id="{{ $class->arm_id }}"
                                                data-category-ids="{{ $class->classcategoryids }}"
                                                data-description="{{ $class->description }}"
                                                title="Edit Class">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                    @endcan
                                    @can('Delete school-class')
                                        <button type="button"
                                                class="btn btn-subtle-danger btn-icon-sm delete-class-btn"
                                                data-id="{{ $class->id }}"
                                                data-class="{{ $class->schoolclass }}"
                                                title="Delete Class">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-inbox-line d-block mb-2" style="font-size: 2rem; opacity: 0.4;"></i>
                                No school classes found. Click "Create Class" to add one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row mt-4 align-items-center">
                <div class="col-sm">
                    <div class="text-muted">
                        Showing {{ $all_classes->firstItem() ?? 0 }} to {{ $all_classes->lastItem() ?? 0 }} of {{ $all_classes->total() ?? 0 }} results
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
<div class="modal fade class-modal" id="addClassModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content">
            <div class="class-modal-hero">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-add-circle-line me-2"></i>Create New Class</h5>
                <p>Add a new school class with arms and categories</p>
            </div>
            <div class="p-4">
                <form id="addClassForm">
                    @csrf
                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-school-line"></i>Class Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="schoolclass" id="className" class="class-input" placeholder="e.g., JSS 1, SS 2, Primary 3" required>
                    </div>

                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-building-line"></i>Select Arms
                            <span class="text-danger">*</span>
                            <small class="text-muted d-block">(You can select multiple arms)</small>
                        </label>
                        <div class="checkbox-group" id="armsCheckboxGroup">
                            @foreach($arms as $arm)
                            <label class="checkbox-item">
                                <input type="checkbox" name="arm_ids[]" value="{{ $arm->id }}" class="arm-checkbox">
                                <span>{{ $arm->arm }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div id="armsError" class="text-danger small mt-1 d-none">Please select at least one arm</div>
                    </div>

                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-price-tag-3-line"></i>Select Categories
                            <span class="text-danger">*</span>
                            <small class="text-muted d-block">(You can select multiple categories)</small>
                        </label>
                        <div class="checkbox-group" id="categoriesCheckboxGroup">
                            @foreach($classcategories as $category)
                            <label class="checkbox-item">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="category-checkbox">
                                <span>{{ $category->category }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div id="categoriesError" class="text-danger small mt-1 d-none">Please select at least one category</div>
                    </div>

                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-file-text-line"></i>Description / Remark
                        </label>
                        <textarea name="description" id="classDescription" class="class-input" rows="3" placeholder="Enter description or additional information..."></textarea>
                    </div>

                    <div id="addClassError" class="alert alert-danger d-none"></div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="class-btn" id="submitAddBtn">
                            <i class="ri-save-line"></i> Create Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT CLASS MODAL --}}
<div class="modal fade class-modal" id="editClassModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content">
            <div class="class-modal-hero">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-edit-circle-line me-2"></i>Edit Class</h5>
                <p>Update school class information</p>
            </div>
            <div class="p-4">
                <form id="editClassForm">
                    @csrf
                    <input type="hidden" name="id" id="editClassId">
                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-school-line"></i>Class Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="schoolclass" id="editClassName" class="class-input" required>
                    </div>

                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-building-line"></i>Select Arm
                            <span class="text-danger">*</span>
                        </label>
                        <select name="arm_id" id="editArmId" class="class-select" required>
                            <option value="">-- Select Arm --</option>
                            @foreach($arms as $arm)
                            <option value="{{ $arm->id }}">{{ $arm->arm }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-price-tag-3-line"></i>Select Categories
                            <span class="text-danger">*</span>
                            <small class="text-muted d-block">(You can select multiple categories)</small>
                        </label>
                        <div class="checkbox-group" id="editCategoriesCheckboxGroup">
                            @foreach($classcategories as $category)
                            <label class="checkbox-item">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="edit-category-checkbox">
                                <span>{{ $category->category }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div id="editCategoriesError" class="text-danger small mt-1 d-none">Please select at least one category</div>
                    </div>

                    <div class="mb-4">
                        <label class="class-label">
                            <i class="ri-file-text-line"></i>Description / Remark
                        </label>
                        <textarea name="description" id="editClassDescription" class="class-input" rows="3"></textarea>
                    </div>

                    <div id="editClassError" class="alert alert-danger d-none"></div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="class-btn" id="submitEditBtn">
                            <i class="ri-save-line"></i> Update Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="deleteClassModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="class-modal-hero" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-delete-bin-line me-2"></i>Delete Class</h5>
                <p>This action cannot be undone</p>
            </div>
            <div class="p-4 text-center">
                <i class="ri-alert-line" style="font-size: 48px; color: #dc2626; opacity: 0.5; margin-bottom: 16px; display: inline-block;"></i>
                <h5 class="mb-2">Are you absolutely sure?</h5>
                <p class="text-muted mb-4">You are about to delete class: <strong id="deleteClassName"></strong><br>This action cannot be reversed.</p>
                <input type="hidden" id="deleteClassId">
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="ri-delete-bin-line me-1"></i> Yes, Delete
                    </button>
                </div>
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
    if ($('#classesTable tbody tr').length > 0 && !$('#classesTable tbody tr td').hasClass('text-center')) {
        $('#classesTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            language: {
                search: '',
                searchPlaceholder: 'Search classes...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ classes',
                infoEmpty: 'No classes found',
                zeroRecords: 'No matching classes found',
            },
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            paging: false,
            searching: true,
        });
    }

    // ADD CLASS
    $('#addClassForm').on('submit', function(e) {
        e.preventDefault();

        // Validate at least one arm is selected
        const selectedArms = $('input[name="arm_ids[]"]:checked').length;
        if (selectedArms === 0) {
            $('#armsError').removeClass('d-none');
            return;
        } else {
            $('#armsError').addClass('d-none');
        }

        // Validate at least one category is selected
        const selectedCategories = $('input[name="category_ids[]"]:checked').length;
        if (selectedCategories === 0) {
            $('#categoriesError').removeClass('d-none');
            return;
        } else {
            $('#categoriesError').addClass('d-none');
        }

        const submitBtn = $('#submitAddBtn');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: '{{ route("schoolclass.store") }}',
            method: 'POST',
            data: {
                schoolclass: $('#className').val(),
                arm_id: $('input[name="arm_ids[]"]:checked').map(function() { return $(this).val(); }).get(),
                classcategoryid: $('input[name="category_ids[]"]:checked').map(function() { return $(this).val(); }).get(),
                description: $('#classDescription').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    $('#addClassError').removeClass('d-none').text(response.message || 'An error occurred');
                    submitBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join(', ');
                }
                $('#addClassError').removeClass('d-none').text(errorMsg);
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // EDIT CLASS - Open Modal
    $('.edit-class-btn').on('click', function() {
        const id = $(this).data('id');
        const className = $(this).data('class');
        const armId = $(this).data('arm-id');
        const categoryIds = $(this).data('category-ids') ? String($(this).data('category-ids')).split(',') : [];
        const description = $(this).data('description');

        $('#editClassId').val(id);
        $('#editClassName').val(className);
        $('#editArmId').val(armId);
        $('#editClassDescription').val(description || '');
        $('#editClassError').addClass('d-none');

        // Reset all category checkboxes
        $('.edit-category-checkbox').prop('checked', false);

        // Check the categories that belong to this class
        categoryIds.forEach(function(catId) {
            $(`.edit-category-checkbox[value="${catId}"]`).prop('checked', true);
        });

        $('#editClassModal').modal('show');
    });

    // EDIT CLASS - Submit
    $('#editClassForm').on('submit', function(e) {
        e.preventDefault();

        // Validate at least one category is selected
        const selectedCategories = $('.edit-category-checkbox:checked').length;
        if (selectedCategories === 0) {
            $('#editCategoriesError').removeClass('d-none');
            return;
        } else {
            $('#editCategoriesError').addClass('d-none');
        }

        const submitBtn = $('#submitEditBtn');
        const originalHtml = submitBtn.html();
        const classId = $('#editClassId').val();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
            url: '{{ route("schoolclass.update", "") }}/' + classId,
            method: 'POST',
            data: {
                schoolclass: $('#editClassName').val(),
                arm_id: $('#editArmId').val(),
                classcategoryid: $('.edit-category-checkbox:checked').map(function() { return $(this).val(); }).get(),
                description: $('#editClassDescription').val(),
                _token: '{{ csrf_token() }}',
                _method: 'POST'
            },
            success: function(response) {
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    $('#editClassError').removeClass('d-none').text(response.message || 'An error occurred');
                    submitBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join(', ');
                }
                $('#editClassError').removeClass('d-none').text(errorMsg);
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // DELETE CLASS - Open Modal
    $('.delete-class-btn').on('click', function() {
        const id = $(this).data('id');
        const className = $(this).data('class');

        $('#deleteClassId').val(id);
        $('#deleteClassName').text(className);
        $('#deleteClassModal').modal('show');
    });

    // DELETE CLASS - Confirm
    $('#confirmDeleteBtn').on('click', function() {
        const id = $('#deleteClassId').val();
        const btn = $(this);
        const originalHtml = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: '{{ route("schoolclass.deleteschoolclass") }}',
            method: 'POST',
            data: {
                schoolclassid: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message || 'Class deleted successfully',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to delete class',
                        confirmButtonColor: '#2563eb'
                    });
                    btn.prop('disabled', false).html(originalHtml);
                    $('#deleteClassModal').modal('hide');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred while deleting the class';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                    confirmButtonColor: '#2563eb'
                });
                btn.prop('disabled', false).html(originalHtml);
                $('#deleteClassModal').modal('hide');
            }
        });
    });

    // Reset forms when modals are closed
    $('#addClassModal').on('hidden.bs.modal', function() {
        $('#addClassForm')[0].reset();
        $('#addClassError').addClass('d-none');
        $('#armsError').addClass('d-none');
        $('#categoriesError').addClass('d-none');
        $('input[name="arm_ids[]"]').prop('checked', false);
        $('input[name="category_ids[]"]').prop('checked', false);
    });

    $('#editClassModal').on('hidden.bs.modal', function() {
        $('#editClassError').addClass('d-none');
        $('#editCategoriesError').addClass('d-none');
    });
});
</script>

@endsection
