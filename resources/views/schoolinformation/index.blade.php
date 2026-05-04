@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
    :root {
        --school-primary: #1e3a5f;
        --school-accent: #2563eb;
        --school-success: #16a34a;
        --school-warning: #d97706;
        --school-danger: #dc2626;
        --school-muted: #6b7280;
        --school-border: #e2e8f0;
        --school-bg: #f8fafc;
        --school-radius: 12px;
        --school-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    /* Hero Banner */
    .school-hero {
        background: linear-gradient(135deg, var(--school-primary) 0%, #2563eb 60%, #4f46e5 100%);
        border-radius: var(--school-radius);
        padding: 28px 32px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .school-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .school-hero::after {
        content: '';
        position: absolute;
        bottom: -80px;
        left: -30px;
        width: 260px;
        height: 260px;
        background: rgba(255,255,255,.03);
        border-radius: 50%;
    }
    .school-hero h1 {
        font-size: 22px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 6px;
        position: relative;
    }
    .school-hero p {
        font-size: 13px;
        color: rgba(255,255,255,.75);
        margin: 0;
        position: relative;
    }

    /* Phone Input Group */
    .phone-input-group {
        background: #f8fafc;
        border: 1.5px solid var(--school-border);
        border-radius: 8px;
        padding: 12px;
    }
    .phone-input-item {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
    }
    .phone-input-item:last-child {
        margin-bottom: 0;
    }
    .phone-input-item .form-control {
        flex: 1;
    }
    .remove-phone-btn {
        background: none;
        border: none;
        color: var(--school-danger);
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .remove-phone-btn:hover {
        background: #fee2e2;
    }
    .add-phone-btn {
        margin-top: 10px;
        width: 100%;
    }

    /* Stat Cards */
    .stat-card {
        background: #fff;
        border: 1px solid var(--school-border);
        border-radius: var(--school-radius);
        padding: 18px 20px;
        transition: transform .15s, box-shadow .15s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--school-shadow);
    }
    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--school-primary);
    }
    .stat-card .stat-label {
        font-size: 12px;
        color: var(--school-muted);
        margin-top: 4px;
    }
    .stat-card .stat-icon {
        font-size: 32px;
        opacity: .12;
        float: right;
        margin-top: -8px;
    }

    /* Table Styles - Original */
    .school-table th {
        background: var(--school-primary);
        color: #fff;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
    }
    .school-table td {
        padding: 12px 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--school-border);
        font-size: 13px;
    }
    .school-table tr:hover td {
        background: #eff6ff;
    }

    /* Phone Badges */
    .phone-badge {
        display: inline-block;
        background: #f3f4f6;
        padding: 4px 10px;
        border-radius: 20px;
        margin: 2px;
        font-size: 12px;
    }

    /* Modal Styles */
    #schoolModal .modal-content, #deleteModal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.15);
    }
    .modal-hero-bar {
        background: linear-gradient(135deg, var(--school-primary) 0%, #2563eb 100%);
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

    /* Form Styles */
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border: 1.5px solid var(--school-border);
        border-radius: 8px;
        font-size: 13px;
        padding: 9px 14px;
        transition: border .15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--school-accent);
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }

    /* Bulk Bar */
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

    /* Original Button Styles */
    .btn-subtle-primary {
        color: #2563eb;
        background-color: rgba(37,99,235,.1);
        border: none;
    }
    .btn-subtle-primary:hover {
        background-color: rgba(37,99,235,.2);
        color: #1d4ed8;
    }
    .btn-subtle-secondary {
        color: #6b7280;
        background-color: rgba(107,114,128,.1);
        border: none;
    }
    .btn-subtle-secondary:hover {
        background-color: rgba(107,114,128,.2);
        color: #4b5563;
    }
    .btn-subtle-danger {
        color: #dc2626;
        background-color: rgba(220,38,38,.1);
        border: none;
    }
    .btn-subtle-danger:hover {
        background-color: rgba(220,38,38,.2);
        color: #b91c1c;
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    /* Cropper Preview */
    .cropper-preview img {
        max-width: 100px;
        max-height: 100px;
        border-radius: 8px;
        border: 1px solid var(--school-border);
    }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero Banner --}}
    <div class="school-hero">
        <h1><i class="ri-school-line me-2"></i>{{ $pagetitle ?? 'School Information Management' }}</h1>
        <p>Manage school information, logos, stamps, and operational dates</p>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-building-line"></i></div>
                <div class="stat-value">{{ $data->total() }}</div>
                <div class="stat-label">Total Schools</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-value text-success">{{ $status_counts['Active'] ?? 0 }}</div>
                <div class="stat-label">Active Schools</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-close-circle-line"></i></div>
                <div class="stat-value text-secondary">{{ $status_counts['Inactive'] ?? 0 }}</div>
                <div class="stat-label">Inactive Schools</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-calendar-line"></i></div>
                <div class="stat-value" id="openedCount">—</div>
                <div class="stat-label">Total Times Opened</div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-semibold" style="color:var(--school-primary)">
                        <i class="ri-bar-chart-2-line me-2"></i>Schools by Status
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="schoolsByStatusChart" data-status='@json($status_counts)' height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Schools List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
            <div class="flex-grow-1">
                <h5 class="mb-0 fw-semibold" style="color:var(--school-primary)">
                    <i class="ri-list-check me-2"></i>Schools
                    <span class="badge bg-primary ms-2">{{ $data->total() }}</span>
                </h5>
            </div>
            <div class="flex-shrink-0">
                <div class="d-flex flex-wrap align-items-start gap-2">
                    <button class="btn btn-subtle-danger d-none" id="remove-actions">
                        <i class="ri-delete-bin-2-line"></i>
                    </button>
                    @can('Create schoolinformation')
                        <button type="button" class="btn btn-primary add-btn" onclick="openAddModal()">
                            <i class="ri-add-line me-1"></i> Add School
                        </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="row g-3 mb-4">
                <div class="col-xxl-3">
                    <div class="search-box">
                        <input type="text" class="form-control search" id="searchInput" placeholder="Search schools...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <select class="form-control" id="idStatus">
                        <option value="all">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <select class="form-control" id="idEmail">
                        <option value="all">All Emails</option>
                        @foreach ($data as $school)
                            <option value="{{ $school->school_email }}">{{ $school->school_email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xxl-1 col-sm-6">
                    <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                        <i class="ri-filter-line me-1"></i> Filter
                    </button>
                </div>
            </div>

            {{-- Bulk Action Bar --}}
            <div class="bulk-bar" id="bulkBar">
                <i class="ri-checkbox-circle-line text-warning"></i>
                <span id="bulkCount">0</span> school(s) selected
                <button class="btn btn-sm btn-danger ms-auto" id="bulkDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i> Delete Selected
                </button>
            </div>

            <div class="table-responsive">
                <table class="table school-table align-middle table-nowrap mb-0">
                    <thead class="table-active">
                        <tr>
                            <th width="40"><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"></div></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone(s)</th>
                            <th>Status</th>
                            <th>Times Opened</th>
                            <th>Date Opened</th>
                            <th>Date Closed</th>
                            <th>Next Term</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $school)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input row-checkbox" type="checkbox" value="{{ $school->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($school->getLogoUrlAttribute())
                                            <img src="{{ $school->getLogoUrlAttribute() }}" alt="logo" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="ri-building-line text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.school-info.show', $school->id) }}" class="text-reset fw-medium">{{ $school->school_name }}</a>
                                        </div>
                                    </div>
                                </div>
                                <td>{{ $school->school_email }}</div>
                                <td>
                                    @php
                                        $phones = is_array($school->school_phones) ? $school->school_phones : json_decode($school->school_phones ?? '[]', true);
                                    @endphp
                                    @if(!empty($phones))
                                        @foreach($phones as $phone)
                                            <span class="phone-badge">{{ $phone }}</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </div>
                                <td>
                                    <span class="badge bg-{{ $school->is_active ? 'success' : 'secondary' }}">
                                        {{ $school->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <td>{{ $school->no_of_times_school_opened }}</div>
                                <td>{{ $school->date_school_opened ? $school->date_school_opened->format('Y-m-d') : '-' }}</div>
                                <td>{{ $school->date_school_closed ? $school->date_school_closed->format('Y-m-d') : '-' }}</div>
                                <td>{{ $school->date_next_term_begins ? $school->date_next_term_begins->format('Y-m-d') : '-' }}</div>
                                <td>{{ $school->created_at->format('Y-m-d') }}</div>
                                <td>
                                    <ul class="d-flex gap-2 list-unstyled mb-0">
                                        @can('View schoolinformation')
                                            <li>
                                                <a href="{{ route('admin.school-info.show', $school->id) }}" class="btn btn-subtle-primary btn-icon btn-sm" title="View">
                                                    <i class="ph-eye"></i>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('Update schoolinformation')
                                            <li>
                                                <button type="button" class="btn btn-subtle-secondary btn-icon btn-sm edit-school-btn" data-id="{{ $school->id }}" data-name="{{ $school->school_name }}" title="Edit">
                                                    <i class="ph-pencil"></i>
                                                </button>
                                            </li>
                                        @endcan
                                        @can('Delete schoolinformation')
                                            <li>
                                                <button type="button" class="btn btn-subtle-danger btn-icon btn-sm delete-school-btn" data-id="{{ $school->id }}" data-name="{{ $school->school_name }}" title="Delete">
                                                    <i class="ph-trash"></i>
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">No schools found. Click "Add School" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="row mt-4 align-items-center">
                <div class="col-sm">
                    <div class="text-muted text-center text-sm-start">
                        Showing <span class="fw-semibold">{{ $data->count() }}</span> of <span class="fw-semibold">{{ $data->total() }}</span> Results
                    </div>
                </div>
                <div class="col-sm-auto mt-3 mt-sm-0">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

{{-- ADD/EDIT SCHOOL MODAL --}}
<div class="modal fade" id="schoolModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalTitle" class="modal-title">Add School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="schoolForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="schoolId" name="id">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="formErrors"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" id="school_name" name="school_name" class="form-control" placeholder="Enter school name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="school_email" name="school_email" class="form-control" placeholder="Enter school email" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea id="school_address" name="school_address" class="form-control" placeholder="Enter school address" rows="2" required></textarea>
                    </div>

                    {{-- Multiple Phone Numbers --}}
                    <div class="mb-3">
                        <label class="form-label">Phone Numbers <span class="text-danger">*</span></label>
                        <div class="phone-input-group">
                            <div id="phoneInputsList">
                                <div class="phone-input-item">
                                    <input type="text" class="form-control phone-input" name="school_phones[]" placeholder="e.g., +234 123 456 7890" required>
                                    <button type="button" class="remove-phone-btn" style="display: none;">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-phone-btn" onclick="addPhoneInput()">
                                <i class="ri-add-line me-1"></i>Add Another Phone Number
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" id="school_website" name="school_website" class="form-control" placeholder="https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Motto</label>
                                <input type="text" id="school_motto" name="school_motto" class="form-control" placeholder="Enter school motto">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Times Opened <span class="text-danger">*</span></label>
                                <input type="number" id="no_of_times_school_opened" name="no_of_times_school_opened" class="form-control" placeholder="0" min="0" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date School Opened</label>
                                <input type="date" id="date_school_opened" name="date_school_opened" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date School Closed</label>
                                <input type="date" id="date_school_closed" name="date_school_closed" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Next Term Begins</label>
                                <input type="date" id="date_next_term_begins" name="date_next_term_begins" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                                    <label class="form-check-label" for="is_active">Set as Active School</label>
                                    <small class="text-muted d-block mt-1">Only one school can be active at a time</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Logo Uploads Section --}}
                    <hr>
                    <h6 class="fw-semibold mb-3">School Assets</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">School Logo</h6>
                                </div>
                                <div class="card-body">
                                    <input type="file" id="school_logo" name="school_logo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <small class="text-muted">Recommended: 300x300px</small>
                                    <div id="school-logo-cropper-container" class="d-none mt-2">
                                        <img id="school-logo-cropper" style="max-width: 100%; max-height: 200px;">
                                        <div class="mt-2">
                                            <input type="number" id="school-crop-width" class="form-control form-control-sm d-inline-block w-25" placeholder="W" value="300">
                                            <input type="number" id="school-crop-height" class="form-control form-control-sm d-inline-block w-25" placeholder="H" value="300">
                                            <button type="button" id="school-crop-btn" class="btn btn-primary btn-sm">Crop</button>
                                            <button type="button" id="school-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                        </div>
                                    </div>
                                    <div id="school-logo-preview" class="text-center mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">App Logo</h6>
                                </div>
                                <div class="card-body">
                                    <input type="file" id="app_logo" name="app_logo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <small class="text-muted">Recommended: 200x200px</small>
                                    <div id="app-logo-cropper-container" class="d-none mt-2">
                                        <img id="app-logo-cropper" style="max-width: 100%; max-height: 200px;">
                                        <div class="mt-2">
                                            <input type="number" id="app-crop-width" class="form-control form-control-sm d-inline-block w-25" placeholder="W" value="200">
                                            <input type="number" id="app-crop-height" class="form-control form-control-sm d-inline-block w-25" placeholder="H" value="200">
                                            <button type="button" id="app-crop-btn" class="btn btn-primary btn-sm">Crop</button>
                                            <button type="button" id="app-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                        </div>
                                    </div>
                                    <div id="app-logo-preview" class="text-center mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">School Stamp</h6>
                                </div>
                                <div class="card-body">
                                    <input type="file" id="school_stamp" name="school_stamp" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <small class="text-muted">For official documents</small>
                                    <div id="school-stamp-cropper-container" class="d-none mt-2">
                                        <img id="school-stamp-cropper" style="max-width: 100%; max-height: 200px;">
                                        <div class="mt-2">
                                            <input type="number" id="stamp-crop-width" class="form-control form-control-sm d-inline-block w-25" placeholder="W" value="200">
                                            <input type="number" id="stamp-crop-height" class="form-control form-control-sm d-inline-block w-25" placeholder="H" value="200">
                                            <button type="button" id="stamp-crop-btn" class="btn btn-primary btn-sm">Crop</button>
                                            <button type="button" id="stamp-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                        </div>
                                    </div>
                                    <div id="school-stamp-preview" class="text-center mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save School</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-md-5">
                <div class="text-center">
                    <div class="text-danger">
                        <i class="bi bi-trash display-4"></i>
                    </div>
                    <div class="mt-4">
                        <h3 class="mb-2">Are you sure?</h3>
                        <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove <strong id="deleteItemName"></strong>?</p>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="confirmDeleteBtn">Yes, Delete It!</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables
let schoolLogoCropper = null, appLogoCropper = null, schoolStampCropper = null;
let croppedSchoolLogoBlob = null, croppedAppLogoBlob = null, croppedSchoolStampBlob = null;
let currentDeleteId = null;
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

document.addEventListener('DOMContentLoaded', function() {
    initStatusChart();
    initEventListeners();
    initAddModalCroppers();
    updateOpenedCount();
    initPhoneInputs();
});

function initStatusChart() {
    const ctx = document.getElementById("schoolsByStatusChart");
    if (!ctx) return;
    const statusData = JSON.parse(ctx.getAttribute('data-status') || '{"Active":0,"Inactive":0}');
    new Chart(ctx.getContext("2d"), {
        type: "bar",
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                label: "Number of Schools",
                data: Object.values(statusData),
                backgroundColor: ["#16a34a", "#6b7280"],
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
    });
}

function updateOpenedCount() {
    let total = 0;
    document.querySelectorAll('table tbody tr td:nth-child(6)').forEach(cell => {
        let val = parseInt(cell.innerText);
        if (!isNaN(val)) total += val;
    });
    document.getElementById('openedCount').textContent = total;
}

function initPhoneInputs() {
    const removeBtns = document.querySelectorAll('.remove-phone-btn');
    removeBtns.forEach(btn => btn.style.display = removeBtns.length > 1 ? 'inline-flex' : 'none');
}

function addPhoneInput(value = '') {
    const container = document.getElementById('phoneInputsList');
    const div = document.createElement('div');
    div.className = 'phone-input-item';
    div.innerHTML = `
        <input type="text" class="form-control phone-input" name="school_phones[]" placeholder="e.g., +234 123 456 7890" value="${escapeHtml(value)}" required>
        <button type="button" class="remove-phone-btn" onclick="removePhoneInput(this)">
            <i class="ri-delete-bin-line"></i>
        </button>
    `;
    container.appendChild(div);
    const btns = document.querySelectorAll('.remove-phone-btn');
    btns.forEach(btn => btn.style.display = btns.length > 1 ? 'inline-flex' : 'none');
}

function removePhoneInput(btn) {
    if (document.querySelectorAll('.phone-input-item').length <= 1) return;
    btn.closest('.phone-input-item').remove();
    const btns = document.querySelectorAll('.remove-phone-btn');
    btns.forEach(btn => btn.style.display = btns.length > 1 ? 'inline-flex' : 'none');
}

function getPhonesArray() {
    const phones = [];
    document.querySelectorAll('.phone-input').forEach(input => {
        if (input.value.trim()) phones.push(input.value.trim());
    });
    return phones;
}

function setPhonesArray(phones) {
    const container = document.getElementById('phoneInputsList');
    container.innerHTML = '';
    if (!phones || phones.length === 0) addPhoneInput('');
    else phones.forEach(phone => addPhoneInput(phone));
}

function escapeHtml(str) { return str ? String(str).replace(/[&<>]/g, function(m) { return m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;'; }) : ''; }

// Cropper functions
function initAddModalCroppers() {
    const schoolLogo = document.getElementById('school_logo');
    if (schoolLogo) {
        schoolLogo.addEventListener('change', (e) => handleImageUpload(e, 'school-logo-cropper', 'school-logo-cropper-container', 'school-crop-width', 'school-crop-height', 'school-logo-preview', 'schoolLogoCropper'));
        document.getElementById('school-crop-btn')?.addEventListener('click', () => handleCropImage('school-logo-preview', 'schoolLogoCropper', 'school-crop-width', 'school-crop-height', 'croppedSchoolLogoBlob'));
        document.getElementById('school-reset-crop-btn')?.addEventListener('click', () => resetCropper('schoolLogoCropper', 'school-logo-cropper-container', 'school-logo-preview', 'croppedSchoolLogoBlob'));
    }
    const appLogo = document.getElementById('app_logo');
    if (appLogo) {
        appLogo.addEventListener('change', (e) => handleImageUpload(e, 'app-logo-cropper', 'app-logo-cropper-container', 'app-crop-width', 'app-crop-height', 'app-logo-preview', 'appLogoCropper'));
        document.getElementById('app-crop-btn')?.addEventListener('click', () => handleCropImage('app-logo-preview', 'appLogoCropper', 'app-crop-width', 'app-crop-height', 'croppedAppLogoBlob'));
        document.getElementById('app-reset-crop-btn')?.addEventListener('click', () => resetCropper('appLogoCropper', 'app-logo-cropper-container', 'app-logo-preview', 'croppedAppLogoBlob'));
    }
    const stamp = document.getElementById('school_stamp');
    if (stamp) {
        stamp.addEventListener('change', (e) => handleImageUpload(e, 'school-stamp-cropper', 'school-stamp-cropper-container', 'stamp-crop-width', 'stamp-crop-height', 'school-stamp-preview', 'schoolStampCropper'));
        document.getElementById('stamp-crop-btn')?.addEventListener('click', () => handleCropImage('school-stamp-preview', 'schoolStampCropper', 'stamp-crop-width', 'stamp-crop-height', 'croppedSchoolStampBlob'));
        document.getElementById('stamp-reset-crop-btn')?.addEventListener('click', () => resetCropper('schoolStampCropper', 'school-stamp-cropper-container', 'school-stamp-preview', 'croppedSchoolStampBlob'));
    }
}

function handleImageUpload(e, cropperId, containerId, widthId, heightId, previewId, cropperVar) {
    const file = e.target.files[0];
    if (!file) return;
    if (!file.type.match('image.*')) { showAlert('error', 'Invalid File', 'Please select an image'); return; }
    if (file.size > 5 * 1024 * 1024) { showAlert('error', 'File Too Large', 'Image must be less than 5MB'); return; }
    const container = document.getElementById(containerId);
    const cropperImg = document.getElementById(cropperId);
    const reader = new FileReader();
    reader.onload = function(e) {
        container.classList.remove('d-none');
        cropperImg.src = e.target.result;
        if (window[cropperVar]) window[cropperVar].destroy();
        const w = document.getElementById(widthId)?.value || 200;
        const h = document.getElementById(heightId)?.value || 200;
        window[cropperVar] = new Cropper(cropperImg, { aspectRatio: w / h, viewMode: 1, autoCropArea: 1 });
    };
    reader.readAsDataURL(file);
}

function handleCropImage(previewId, cropperVar, widthId, heightId, blobVar) {
    const cropper = window[cropperVar];
    if (!cropper) { showAlert('warning', 'No Image', 'Select an image first'); return; }
    const w = parseInt(document.getElementById(widthId)?.value) || 200;
    const h = parseInt(document.getElementById(heightId)?.value) || 200;
    const canvas = cropper.getCroppedCanvas({ width: w, height: h });
    canvas.toBlob(function(blob) {
        window[blobVar] = blob;
        const preview = document.getElementById(previewId);
        if (preview) preview.innerHTML = `<img src="${canvas.toDataURL()}" class="img-thumbnail" style="max-width: 80px;">`;
        showAlert('success', 'Cropped!', 'Image cropped successfully');
    }, 'image/png');
}

function resetCropper(cropperVar, containerId, previewId, blobVar) {
    if (window[cropperVar]) { window[cropperVar].destroy(); window[cropperVar] = null; }
    window[blobVar] = null;
    document.getElementById(containerId)?.classList.add('d-none');
    if (previewId) document.getElementById(previewId).innerHTML = '';
}

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').innerHTML = 'Add School';
    document.getElementById('schoolForm').reset();
    document.getElementById('schoolId').value = '';
    document.getElementById('formErrors').classList.add('d-none');
    setPhonesArray(['']);
    ['school-logo-preview', 'app-logo-preview', 'school-stamp-preview'].forEach(id => {
        if (document.getElementById(id)) document.getElementById(id).innerHTML = '';
    });
    ['schoolLogoCropper', 'appLogoCropper', 'schoolStampCropper'].forEach(name => {
        if (window[name]) { window[name].destroy(); window[name] = null; }
    });
    ['school-logo-cropper-container', 'app-logo-cropper-container', 'school-stamp-cropper-container'].forEach(id => {
        if (document.getElementById(id)) document.getElementById(id).classList.add('d-none');
    });
    new bootstrap.Modal(document.getElementById('schoolModal')).show();
}

function openEditModal(id) {
    document.getElementById('modalTitle').innerHTML = 'Edit School';
    document.getElementById('schoolId').value = id;
    document.getElementById('formErrors').classList.add('d-none');

    const saveBtn = document.getElementById('saveBtn');
    const originalHtml = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

    fetch(`/school-info/${id}/edit-json`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const s = data.school;
            document.getElementById('school_name').value = s.school_name || '';
            document.getElementById('school_email').value = s.school_email || '';
            document.getElementById('school_address').value = s.school_address || '';
            document.getElementById('school_website').value = s.school_website || '';
            document.getElementById('school_motto').value = s.school_motto || '';
            document.getElementById('no_of_times_school_opened').value = s.no_of_times_school_opened || 0;
            document.getElementById('date_school_opened').value = s.date_school_opened || '';
            document.getElementById('date_school_closed').value = s.date_school_closed || '';
            document.getElementById('date_next_term_begins').value = s.date_next_term_begins || '';
            document.getElementById('is_active').checked = s.is_active || false;
            setPhonesArray(s.school_phones || []);
            if (s.logo_url) document.getElementById('school-logo-preview').innerHTML = `<img src="${s.logo_url}" class="img-thumbnail" style="max-width: 80px;">`;
            if (s.app_logo_url) document.getElementById('app-logo-preview').innerHTML = `<img src="${s.app_logo_url}" class="img-thumbnail" style="max-width: 80px;">`;
            if (s.stamp_url) document.getElementById('school-stamp-preview').innerHTML = `<img src="${s.stamp_url}" class="img-thumbnail" style="max-width: 80px;">`;
            new bootstrap.Modal(document.getElementById('schoolModal')).show();
        } else {
            showAlert('error', 'Error', data.message || 'Failed to load');
        }
    })
    .catch(error => showAlert('error', 'Error', 'Network error'))
    .finally(() => { saveBtn.disabled = false; saveBtn.innerHTML = originalHtml; });
}

function openDeleteModal(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteItemName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteRecordModal')).show();
}

// Form submission
document.getElementById('schoolForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    const schoolId = document.getElementById('schoolId').value;

    formData.append('school_name', document.getElementById('school_name').value);
    formData.append('school_address', document.getElementById('school_address').value);
    getPhonesArray().forEach(p => formData.append('school_phones[]', p));
    formData.append('school_email', document.getElementById('school_email').value);
    formData.append('school_website', document.getElementById('school_website').value || '');
    formData.append('school_motto', document.getElementById('school_motto').value || '');
    formData.append('no_of_times_school_opened', document.getElementById('no_of_times_school_opened').value);
    formData.append('date_school_opened', document.getElementById('date_school_opened').value || '');
    formData.append('date_school_closed', document.getElementById('date_school_closed').value || '');
    formData.append('date_next_term_begins', document.getElementById('date_next_term_begins').value || '');
    formData.append('is_active', document.getElementById('is_active').checked ? 1 : 0);
    formData.append('_token', CSRF_TOKEN);
    if (croppedSchoolLogoBlob) formData.append('school_logo', croppedSchoolLogoBlob, 'logo.png');
    if (croppedAppLogoBlob) formData.append('app_logo', croppedAppLogoBlob, 'app_logo.png');
    if (croppedSchoolStampBlob) formData.append('school_stamp', croppedSchoolStampBlob, 'stamp.png');
    if (schoolId) formData.append('_method', 'PUT');

    const url = schoolId ? `/school-info/${schoolId}` : '/school-info';
    const btn = document.getElementById('saveBtn');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    fetch(url, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Success!', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            let html = '<ul>';
            if (data.errors) for (let k in data.errors) data.errors[k].forEach(e => html += `<li>${e}</li>`);
            else html += `<li>${data.message || 'Error'}</li>`;
            html += '</ul>';
            document.getElementById('formErrors').innerHTML = html;
            document.getElementById('formErrors').classList.remove('d-none');
        }
    })
    .catch(error => showAlert('error', 'Error', 'Network error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = original; });
});

// Delete confirmation
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!currentDeleteId) return;
    const btn = this;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

    fetch(`/school-info/${currentDeleteId}`, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Deleted!', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('error', 'Error', data.message || 'Delete failed');
        }
    })
    .catch(error => showAlert('error', 'Error', 'Network error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = original;
        bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal'))?.hide();
        currentDeleteId = null;
    });
});

// Event listeners
function initEventListeners() {
    document.querySelectorAll('.edit-school-btn').forEach(btn => {
        btn.addEventListener('click', function() { openEditModal(this.getAttribute('data-id')); });
    });
    document.querySelectorAll('.delete-school-btn').forEach(btn => {
        btn.addEventListener('click', function() { openDeleteModal(this.getAttribute('data-id'), this.getAttribute('data-name')); });
    });
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.addEventListener('change', updateBulkBar));
    document.getElementById('bulkDeleteBtn')?.addEventListener('click', bulkDelete);
}

function updateBulkBar() {
    const count = document.querySelectorAll('.row-checkbox:checked').length;
    const bar = document.getElementById('bulkBar');
    const actions = document.getElementById('remove-actions');
    if (count > 0) {
        bar.classList.add('show');
        if (actions) actions.classList.remove('d-none');
        document.getElementById('bulkCount').textContent = count;
    } else {
        bar.classList.remove('show');
        if (actions) actions.classList.add('d-none');
    }
}

function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    if (ids.length === 0) return;
    Swal.fire({
        title: `Delete ${ids.length} school(s)?`,
        text: 'This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/school-info/bulk-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Deleted!', data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert('error', 'Error', data.message || 'Delete failed');
                }
            });
        }
    });
}

function filterData() {
    const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const status = document.getElementById('idStatus')?.value || 'all';
    const email = document.getElementById('idEmail')?.value || 'all';
    document.querySelectorAll('table tbody tr').forEach(row => {
        const name = row.cells[1]?.innerText.toLowerCase() || '';
        const emailText = row.cells[2]?.innerText.toLowerCase() || '';
        const statusText = row.cells[4]?.innerText || '';
        const rowEmail = row.cells[2]?.innerText || '';
        const match = (name.includes(search) || emailText.includes(search)) && (status === 'all' || statusText === status) && (email === 'all' || rowEmail === email);
        row.style.display = match ? '' : 'none';
    });
}

function showAlert(icon, title, text) {
    Swal.fire({ icon, title, text, timer: icon === 'success' ? 2000 : undefined, showConfirmButton: icon !== 'success' });
}

// Global exports
window.filterData = filterData;
window.addPhoneInput = addPhoneInput;
window.removePhoneInput = removePhoneInput;
window.openAddModal = openAddModal;
</script>
@endsection
