@extends('layouts.master')
@section('content')
<?php use Spatie\Permission\Models\Role; ?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Student Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <style>
                /* ====== MODERN CARD UI STYLES ====== */
                .dashboard-stats-card {
                    border: none;
                    border-radius: 16px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    transition: all 0.3s ease;
                    margin-bottom: 24px;
                    position: relative;
                    overflow: hidden;
                }

                .dashboard-stats-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
                }

                .dashboard-stats-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
                }

                .dashboard-stats-card .card-body {
                    padding: 24px;
                    position: relative;
                    z-index: 1;
                }

                .dashboard-stats-card .stats-icon {
                    width: 64px;
                    height: 64px;
                    border-radius: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-bottom: 20px;
                    font-size: 28px;
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                    color: white;
                }

                .dashboard-stats-card .stats-content {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                }

                .dashboard-stats-card .stats-label {
                    font-size: 14px;
                    font-weight: 500;
                    color: rgba(255, 255, 255, 0.9);
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .dashboard-stats-card .stats-value {
                    font-size: 32px;
                    font-weight: 700;
                    color: white;
                    line-height: 1;
                }

                .stats-primary {
                    --gradient-start: #4361ee;
                    --gradient-end: #3a0ca3;
                    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
                }

                .stats-success {
                    --gradient-start: #10b981;
                    --gradient-end: #047857;
                    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
                }

                .stats-warning {
                    --gradient-start: #f59e0b;
                    --gradient-end: #b45309;
                    background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
                }

                .stats-info {
                    --gradient-start: #0ea5e9;
                    --gradient-end: #0369a1;
                    background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
                }

                .stats-purple {
                    --gradient-start: #8b5cf6;
                    --gradient-end: #7c3aed;
                    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                }

                .stats-pink {
                    --gradient-start: #ec4899;
                    --gradient-end: #be185d;
                    background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
                }

                .stats-teal {
                    --gradient-start: #14b8a6;
                    --gradient-end: #0d9488;
                    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
                }

                /* ====== PROFESSIONAL STUDENT CARD STYLES ====== */
                .student-profile-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 16px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                    background: white;
                    height: 100%;
                    position: relative;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .student-profile-card:hover {
                    border-color: #3b82f6;
                    box-shadow: 0 8px 30px rgba(59, 130, 246, 0.15);
                    transform: translateY(-4px);
                }

                .student-profile-card .card-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 20px;
                    position: relative;
                    min-height: 120px;
                }

                .student-profile-card .avatar-container {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    width: 80px;
                    height: 80px;
                    border-radius: 16px;
                    overflow: hidden;
                    border: 4px solid white;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    background: white;
                }

                .student-profile-card .avatar {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .student-profile-card .avatar-initials {
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: 700;
                    color: #667eea;
                    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
                }

                .student-profile-card .header-content {
                    padding-right: 100px;
                }

                .student-profile-card .student-name {
                    font-size: 20px;
                    font-weight: 700;
                    color: white;
                    margin-bottom: 4px;
                    line-height: 1.2;
                }

                .student-profile-card .student-admission {
                    font-size: 13px;
                    color: rgba(255, 255, 255, 0.9);
                    background: rgba(255, 255, 255, 0.1);
                    padding: 4px 12px;
                    border-radius: 20px;
                    display: inline-block;
                    backdrop-filter: blur(10px);
                }

                .student-profile-card .card-body {
                    padding: 20px;
                }

                .student-profile-card .student-info-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px;
                    margin-bottom: 20px;
                }

                .student-profile-card .info-item {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }

                .student-profile-card .info-label {
                    font-size: 11px;
                    font-weight: 600;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .student-profile-card .info-value {
                    font-size: 14px;
                    font-weight: 600;
                    color: #374151;
                }

                .student-profile-card .status-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    margin-bottom: 16px;
                }

                .student-profile-card .status-active {
                    background-color: #d1fae5;
                    color: #065f46;
                    border: 1px solid #a7f3d0;
                }

                .student-profile-card .status-inactive {
                    background-color: #fee2e2;
                    color: #991b1b;
                    border: 1px solid #fecaca;
                }

                .student-profile-card .status-new {
                    background-color: #dbeafe;
                    color: #1e40af;
                    border: 1px solid #bfdbfe;
                }

                .student-profile-card .status-old {
                    background-color: #fef3c7;
                    color: #92400e;
                    border: 1px solid #fde68a;
                }

                .student-profile-card .action-buttons {
                    display: flex;
                    gap: 8px;
                    padding-top: 16px;
                    border-top: 1px solid #e5e7eb;
                }

                .student-profile-card .action-btn {
                    flex: 1;
                    padding: 10px;
                    border-radius: 12px;
                    border: none;
                    font-size: 13px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                }

                .student-profile-card .view-btn {
                    background-color: #3b82f6;
                    color: white;
                }

                .student-profile-card .edit-btn {
                    background-color: #f3f4f6;
                    color: #374151;
                    border: 1px solid #e5e7eb;
                }

                .student-profile-card .delete-btn {
                    background-color: #fef2f2;
                    color: #dc2626;
                    border: 1px solid #fee2e2;
                }

                .student-profile-card .checkbox-container {
                    position: absolute;
                    top: 16px;
                    left: 16px;
                    z-index: 2;
                }

                .btn-soft-info {
                    color: #0dcaf0;
                    background-color: rgba(13, 202, 240, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-info:hover {
                    color: #fff;
                    background-color: #0dcaf0;
                    transform: translateY(-2px);
                }

                .btn-soft-warning {
                    color: #ffc107;
                    background-color: rgba(255, 193, 7, 0.1);
                    border-color: transparent;
                }

                .btn-soft-warning:hover {
                    color: #fff;
                    background-color: #ffc107;
                    transform: translateY(-2px);
                }

                .btn-soft-danger {
                    color: #dc3545;
                    background-color: rgba(220, 53, 69, 0.1);
                    border-color: transparent;
                }

                .btn-soft-danger:hover {
                    color: #fff;
                    background-color: #dc3545;
                    transform: translateY(-2px);
                }

                /* Table styles */
                .data-table-container {
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .data-table thead {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }

                .data-table thead th {
                    border: none;
                    color: white;
                    font-weight: 600;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    padding: 16px 12px;
                }

                .data-table tbody tr {
                    transition: all 0.2s ease;
                    border-bottom: 1px solid #e5e7eb;
                }

                .data-table tbody tr:hover {
                    background-color: #f9fafb;
                }

                /* Filter bar */
                .filter-bar {
                    background: white;
                    padding: 20px;
                    border-radius: 16px;
                    margin-bottom: 24px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .search-box {
                    position: relative;
                }

                .search-box input {
                    padding-left: 44px;
                    padding-right: 40px;
                    border-radius: 12px;
                    border: 1px solid #e5e7eb;
                    height: 48px;
                    font-size: 14px;
                }

                .search-box .search-icon {
                    position: absolute;
                    left: 16px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #9ca3af;
                    font-size: 18px;
                }

                .search-box .clear-search {
                    position: absolute;
                    right: 8px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: transparent;
                    border: none;
                    color: #6b7280;
                    font-size: 16px;
                    padding: 4px 8px;
                    cursor: pointer;
                    display: none;
                    z-index: 10;
                }

                /* Pagination */
                .pagination-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    background: white;
                    border-top: 1px solid #e5e7eb;
                }

                .pagination .page-link {
                    border: none;
                    color: #374151;
                    margin: 0 4px;
                    border-radius: 10px;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 600;
                }

                .pagination .page-item.active .page-link {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }

                /* Empty state */
                .empty-state {
                    padding: 60px 20px;
                    text-align: center;
                }

                .empty-state-icon {
                    font-size: 64px;
                    color: #d1d5db;
                    margin-bottom: 20px;
                }

                /* Loading state */
                .loading-state {
                    padding: 60px 20px;
                    text-align: center;
                }

                .spinner-ring {
                    width: 60px;
                    height: 60px;
                    border: 4px solid #f3f4f6;
                    border-top-color: #667eea;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                /* Modal styles - FIXED FOR SCROLLING */
                .modal-dialog {
                    max-height: 90vh;
                }

                .modal-content {
                    max-height: 90vh;
                    display: flex;
                    flex-direction: column;
                }

                .modal-body {
                    flex: 1;
                    overflow-y: auto;
                    padding: 1.5rem;
                }

                #editStudentForm .modal-body,
                #addStudentForm .modal-body {
                    max-height: calc(90vh - 130px);
                    overflow-y: auto;
                }

                .modal-header-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 24px 32px;
                    border: none;
                }

                .modal-header-gradient .modal-title {
                    font-size: 20px;
                    font-weight: 700;
                }

                .modal-header-gradient .btn-close {
                    filter: brightness(0) invert(1);
                    opacity: 0.8;
                }

                .progress-steps {
                    display: flex;
                    justify-content: space-between;
                    position: relative;
                    margin-bottom: 30px;
                }

                .progress-steps::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    height: 2px;
                    background: #e9ecef;
                    transform: translateY(-50%);
                    z-index: 1;
                }

                .progress-steps .step {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: #e9ecef;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    color: #6b7280;
                    position: relative;
                    z-index: 2;
                    border: 2px solid #e9ecef;
                }

                .progress-steps .step.active {
                    background: #405189;
                    color: white;
                    border-color: #405189;
                }

                .btn-primary-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 12px;
                    font-weight: 600;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .pagination-container {
                        flex-direction: column;
                        gap: 15px;
                    }

                    .btn-group-toggle .btn {
                        padding: 5px 10px;
                        font-size: 12px;
                    }
                }
            </style>

            <!-- Dashboard Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-primary">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-users"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Total Students</span>
                                <span class="stats-value">{{ $total_population }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-success">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-user-graduate"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Active Students</span>
                                <span class="stats-value">{{ $student_status_counts['Active'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-warning">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-user-plus"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">New Admissions</span>
                                <span class="stats-value">{{ $status_counts['New Student'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-purple">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Staff Count</span>
                                <span class="stats-value">{{ $staff_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gender and Religion Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-info">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-mars"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Male Students</span>
                                <span class="stats-value">{{ $gender_counts['Male'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-pink">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-venus"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Female Students</span>
                                <span class="stats-value">{{ $gender_counts['Female'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-teal">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-cross"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Christians</span>
                                <span class="stats-value">{{ $religion_counts['Christianity'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-warning">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-moon"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Muslims</span>
                                <span class="stats-value">{{ $religion_counts['Islam'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Validation Error!</strong>
                    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="data-table-container">
                <!-- Card Header -->
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for="checkAll"></label>
                        </div>
                        <h5 class="mb-0 fw-bold">Student Records</h5>
                        <span class="badge bg-primary bg-gradient rounded-pill" id="totalStudents">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-toggle" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="tableViewBtn">
                                <i class="fas fa-table me-2"></i>Table
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="cardViewBtn">
                                <i class="fas fa-th-large me-2"></i>Cards
                            </button>
                        </div>
                        @can('Delete student')
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="bulkActionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                <i class="fas fa-cog me-2"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" id="deleteMultipleBtn">
                                    <i class="fas fa-trash me-2"></i>Delete Selected
                                </a></li>
                                <li><a class="dropdown-item text-primary" href="javascript:void(0);" id="updateCurrentTermBtn">
                                    <i class="fas fa-calendar-alt me-2"></i>Update Current Term
                                </a></li>
                            </ul>
                        </div>
                        @endcan
                        @can('Create student')
                        <button type="button" class="btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-user-plus me-2"></i>Add Student
                        </button>
                        @endcan
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#printStudentReportModal">
                            <i class="fas fa-file-export me-2"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control" id="search-input" placeholder="Search...">
                                <button type="button" class="clear-search" id="clear-search"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="schoolclass-filter">
                                <option value="all">All Classes</option>
                                @foreach($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="term-filter">
                                <option value="all">All Terms</option>
                                @foreach($schoolterms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="session-filter">
                                <option value="all">All Sessions</option>
                                @foreach($schoolsessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary w-100" id="filterBtn">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetFiltersBtn">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div id="tableView" class="view-container">
                    <div class="table-responsive">
                        <table class="table data-table" id="studentTable">
                            <thead>
                                <tr>
                                    <th width="50"><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAllTable"></div></th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Gender</th>
                                    <th>Registered</th>
                                    <th width="250">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="view-container d-none p-4">
                    <div class="row" id="studentsCardsContainer"></div>
                </div>

                <!-- Empty/Loading States -->
                <div id="emptyState" class="empty-state d-none">
                    <div class="empty-state-icon"><i class="fas fa-users-slash"></i></div>
                    <h5 class="empty-state-title">No Students Found</h5>
                    <button class="btn btn-primary-gradient" id="resetFromEmptyBtn"><i class="fas fa-redo me-2"></i>Reset Filters</button>
                </div>

                <div id="loadingState" class="loading-state">
                    <div class="spinner-ring"></div>
                    <p class="mt-3 text-muted">Loading students...</p>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div><span class="text-muted">Showing <span class="fw-bold" id="showingCount">0</span> to <span class="fw-bold" id="toCount">0</span> of <span class="fw-bold" id="totalCount">0</span> students</span></div>
                    <nav>
                        <ul class="pagination mb-0" id="pagination">
                            <li class="page-item" id="prevPageLi"><a class="page-link" href="javascript:void(0);" id="prevPage"><i class="fas fa-chevron-left"></i></a></li>
                            <li class="page-item" id="nextPageLi"><a class="page-link" href="javascript:void(0);" id="nextPage"><i class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Student Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="progress-steps mb-4">
                        <div class="step active">1</div>
                        <div class="step">2</div>
                        <div class="step">3</div>
                        <div class="step">4</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Academic Details -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="admissionMode" id="admissionAuto" value="auto" required onchange="toggleAdmissionInput()">
                                                <label class="form-check-label" for="admissionAuto">Auto Generate</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="admissionMode" id="admissionManual" value="manual" required onchange="toggleAdmissionInput()">
                                                <label class="form-check-label" for="admissionManual">Manual Entry</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Admission Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-control" id="admissionYear" name="admissionYear" required onchange="updateAdmissionNumber()">
                                                @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                @endfor
                                            </select>
                                            <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Admission Date <span class="text-danger">*</span></label>
                                        <input type="date" id="admissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Class <span class="text-danger">*</span></label>
                                        <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                            <option value="">Select Class</option>
                                            @foreach($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label">Term <span class="text-danger">*</span></label>
                                                <select id="termid" name="termid" class="form-control" required>
                                                    <option value="">Select Term</option>
                                                    @foreach($schoolterms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label">Session <span class="text-danger">*</span></label>
                                                <select id="sessionid" name="sessionid" class="form-control" required>
                                                    <option value="">Select Session</option>
                                                    @foreach($schoolsessions as $session)
                                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="statusOld" value="1" required><label class="form-check-label" for="statusOld">Old Student</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="statusNew" value="2" required><label class="form-check-label" for="statusNew">New Student</label></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Activity Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="statusActive" value="Active" required><label class="form-check-label" for="statusActive">Active</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="statusInactive" value="Inactive" required><label class="form-check-label" for="statusInactive">Inactive</label></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Student Category <span class="text-danger">*</span></label>
                                        <select id="student_category" name="student_category" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <option value="Day">Day Student</option>
                                            <option value="Boarding">Boarding Student</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Personal Details -->
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 text-center">
                                        <div class="border border-2 border-dashed border-primary rounded p-3">
                                            <img id="addStudentAvatar" src="https://via.placeholder.com/120x120/667eea/ffffff?text=Photo" alt="Avatar Preview" class="rounded-circle mb-2" style="width:100px;height:100px;object-fit:cover;border:4px solid #667eea;"/>
                                            <div>
                                                <label for="avatar" class="btn btn-outline-primary btn-sm"><i class="fas fa-camera me-1"></i>Choose Photo</label>
                                                <input type="file" id="avatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-3">
                                            <label class="form-label">Title</label>
                                            <select id="title" name="title" class="form-control"><option value="">—</option><option value="Master">Master</option><option value="Miss">Miss</option></select>
                                        </div>
                                        <div class="col-9">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last name" required>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First name" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Other Names</label>
                                            <input type="text" id="othername" name="othername" class="form-control" placeholder="Middle name">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required><label class="form-check-label" for="genderMale">Male</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required><label class="form-check-label" for="genderFemale">Female</label></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-7">
                                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value,'addAgeInput')">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label">Age</label>
                                            <input type="number" id="addAgeInput" name="age" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                        <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" id="phone_number" name="phone_number" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" id="email" name="email" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Future Ambition <span class="text-danger">*</span></label>
                                        <textarea id="future_ambition" name="future_ambition" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                        <textarea id="permanent_address" name="permanent_address" class="form-control" rows="2" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Additional Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><label class="form-label">Nationality <span class="text-danger">*</span></label><input type="text" id="nationality" name="nationality" class="form-control" required></div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">State <span class="text-danger">*</span></label><select id="addState" name="state" class="form-control" required><option value="">Select State</option></select></div>
                                        <div class="col-6 mb-3"><label class="form-label">LGA <span class="text-danger">*</span></label><select id="addLocal" name="local" class="form-control" required disabled><option value="">Select LGA</option></select></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">City</label><input type="text" id="city" name="city" class="form-control"></div>
                                        <div class="col-6 mb-3"><label class="form-label">Religion <span class="text-danger">*</span></label><select id="religion" name="religion" class="form-control" required><option value="">Select</option><option value="Christianity">Christianity</option><option value="Islam">Islam</option><option value="Others">Others</option></select></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">Blood Group</label><select id="blood_group" name="blood_group" class="form-control"><option value="">Select</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select></div>
                                        <div class="col-6 mb-3"><label class="form-label">Mother Tongue</label><input type="text" id="mother_tongue" name="mother_tongue" class="form-control"></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">NIN Number</label><input type="text" id="nin_number" name="nin_number" class="form-control" maxlength="11"></div>
                                        <div class="col-6 mb-3"><label class="form-label">School House <span class="text-danger">*</span></label><select id="school_house" name="schoolhouseid" class="form-control" required><option value="">Select House</option>@foreach($schoolhouses as $h)<option value="{{ $h->id }}">{{ $h->house }}</option>@endforeach</select></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Parent Details -->
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent / Guardian Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><label class="form-label">Father's Name</label><input type="text" id="father_name" name="father_name" class="form-control"></div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">Father's Phone</label><input type="text" id="father_phone" name="father_phone" class="form-control"></div>
                                        <div class="col-6 mb-3"><label class="form-label">Father's Occupation</label><input type="text" id="father_occupation" name="father_occupation" class="form-control"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Father's City</label><input type="text" id="father_city" name="father_city" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Mother's Name</label><input type="text" id="mother_name" name="mother_name" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Mother's Phone</label><input type="text" id="mother_phone" name="mother_phone" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Parent Email</label><input type="email" id="parent_email" name="parent_email" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Parent Address</label><textarea id="parent_address" name="parent_address" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><label class="form-label">Last School Attended</label><input type="text" id="last_school" name="last_school" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Last Class Attended</label><input type="text" id="last_class" name="last_class" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Reason for Leaving</label><textarea id="reason_for_leaving" name="reason_for_leaving" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="add-btn"><i class="fas fa-save me-1"></i>Register Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST"
                  action="{{ route('student.update', ':id') }}"
                  data-base-action="{{ route('student.update', ':id') }}">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <input type="hidden" id="editStudentId" name="id">
                    <div class="progress-steps mb-4">
                        <div class="step active">1</div><div class="step">2</div><div class="step">3</div><div class="step">4</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionAuto" value="auto" required onchange="toggleAdmissionInput('edit')"><label class="form-check-label" for="editAdmissionAuto">Auto</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionManual" value="manual" required onchange="toggleAdmissionInput('edit')"><label class="form-check-label" for="editAdmissionManual">Manual</label></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Admission Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-control" id="editAdmissionYear" name="admissionYear" required onchange="updateAdmissionNumber('edit')">
                                                @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                @endfor
                                            </select>
                                            <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                        </div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Admission Date <span class="text-danger">*</span></label><input type="date" id="editAdmissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}"></div>
                                    <div class="mb-3"><label class="form-label">Class <span class="text-danger">*</span></label><select id="editSchoolclassid" name="schoolclassid" class="form-control" required><option value="">Select Class</option>@foreach($schoolclasses as $class)<option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>@endforeach</select></div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">Term <span class="text-danger">*</span></label><select id="editTermid" name="termid" class="form-control" required><option value="">Select Term</option>@foreach($schoolterms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select></div>
                                        <div class="col-6 mb-3"><label class="form-label">Session <span class="text-danger">*</span></label><select id="editSessionid" name="sessionid" class="form-control" required><option value="">Select Session</option>@foreach($schoolsessions as $session)<option value="{{ $session->id }}">{{ $session->name }}</option>@endforeach</select></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="editStatusOld" value="1" required><label class="form-check-label" for="editStatusOld">Old</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="editStatusNew" value="2" required><label class="form-check-label" for="editStatusNew">New</label></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Activity Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="editStatusActive" value="Active" required><label class="form-check-label" for="editStatusActive">Active</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="editStatusInactive" value="Inactive" required><label class="form-check-label" for="editStatusInactive">Inactive</label></div>
                                        </div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Category <span class="text-danger">*</span></label><select id="editStudentCategory" name="student_category" class="form-control" required><option value="">Select</option><option value="Day">Day</option><option value="Boarding">Boarding</option></select></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 text-center">
                                        <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" class="rounded-circle mb-2" style="width:100px;height:100px;object-fit:cover;border:4px solid #0dcaf0;cursor:pointer;" onclick="document.getElementById('editAvatar').click()"/>
                                        <div><label for="editAvatar" class="btn btn-outline-info btn-sm"><i class="fas fa-camera me-1"></i>Choose Photo</label><input type="file" id="editAvatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this,'editStudentAvatar')"></div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-3"><label class="form-label">Title</label><select id="editTitle" name="title" class="form-control"><option value="">—</option><option value="Master">Master</option><option value="Miss">Miss</option></select></div>
                                        <div class="col-9"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" id="editLastname" name="lastname" class="form-control" required></div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" id="editFirstname" name="firstname" class="form-control" required></div>
                                        <div class="col-6"><label class="form-label">Other Names</label><input type="text" id="editOthername" name="othername" class="form-control"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="Male" required><label class="form-check-label" for="editGenderMale">Male</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="Female" required><label class="form-check-label" for="editGenderFemale">Female</label></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-7"><label class="form-label">Date of Birth <span class="text-danger">*</span></label><input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value,'editAgeInput')"></div>
                                        <div class="col-5"><label class="form-label">Age</label><input type="number" id="editAgeInput" name="age" class="form-control" readonly></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Place of Birth <span class="text-danger">*</span></label><input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Phone Number</label><input type="text" id="editPhoneNumber" name="phone_number" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Email</label><input type="email" id="editEmail" name="email" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Future Ambition <span class="text-danger">*</span></label><textarea id="editFutureAmbition" name="future_ambition" class="form-control" rows="2" required></textarea></div>
                                    <div class="mb-3"><label class="form-label">Permanent Address <span class="text-danger">*</span></label><textarea id="editPermanentAddress" name="permanent_address" class="form-control" rows="2" required></textarea></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><label class="form-label">Nationality <span class="text-danger">*</span></label><input type="text" id="editNationality" name="nationality" class="form-control" required></div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">State <span class="text-danger">*</span></label><select id="editState" name="state" class="form-control" required><option value="">Select State</option></select></div>
                                        <div class="col-6 mb-3"><label class="form-label">LGA <span class="text-danger">*</span></label><select id="editLocal" name="local" class="form-control" required disabled><option value="">Select LGA</option></select></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">City</label><input type="text" id="editCity" name="city" class="form-control"></div>
                                        <div class="col-6 mb-3"><label class="form-label">Religion <span class="text-danger">*</span></label><select id="editReligion" name="religion" class="form-control" required><option value="">Select</option><option value="Christianity">Christianity</option><option value="Islam">Islam</option><option value="Others">Others</option></select></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">Blood Group</label><select id="editBloodGroup" name="blood_group" class="form-control"><option value="">Select</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select></div>
                                        <div class="col-6 mb-3"><label class="form-label">Mother Tongue</label><input type="text" id="editMotherTongue" name="mother_tongue" class="form-control"></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">NIN Number</label><input type="text" id="editNinNumber" name="nin_number" class="form-control" maxlength="11"></div>
                                        <div class="col-6 mb-3"><label class="form-label">School House <span class="text-danger">*</span></label><select id="editSchoolHouse" name="schoolhouseid" class="form-control" required><option value="">Select House</option>@foreach($schoolhouses as $h)<option value="{{ $h->id }}">{{ $h->house }}</option>@endforeach</select></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent / Guardian</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><label class="form-label">Father's Name</label><input type="text" id="editFatherName" name="father_name" class="form-control"></div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3"><label class="form-label">Father's Phone</label><input type="text" id="editFatherPhone" name="father_phone" class="form-control"></div>
                                        <div class="col-6 mb-3"><label class="form-label">Father's Occupation</label><input type="text" id="editFatherOccupation" name="father_occupation" class="form-control"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Father's City</label><input type="text" id="editFatherCity" name="father_city" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Mother's Name</label><input type="text" id="editMotherName" name="mother_name" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Mother's Phone</label><input type="text" id="editMotherPhone" name="mother_phone" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Parent Email</label><input type="email" id="editParentEmail" name="parent_email" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Parent Address</label><textarea id="editParentAddress" name="parent_address" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><label class="form-label">Last School</label><input type="text" id="editLastSchool" name="last_school" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Last Class</label><input type="text" id="editLastClass" name="last_class" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Reason for Leaving</label><textarea id="editReasonForLeaving" name="reason_for_leaving" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="edit-btn"><i class="fas fa-save me-1"></i>Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i>Student Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center py-5">
                    <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
                    <h5>Student Profile View</h5>
                    <p class="text-muted">Student details will be displayed here</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Current Term Modal -->
<div id="updateCurrentTermModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Register/Update Term</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="updateCurrentTermForm">
                    @csrf
                    <div class="alert alert-info border-0 rounded-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Registering term for <strong><span id="selectedStudentsCount">0</span></strong> selected student(s).
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                        <select class="form-control rounded-3" name="schoolclassId" required>
                            <option value="">Select Class</option>
                            @foreach($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                        <select class="form-control rounded-3" name="termId" required>
                            <option value="">Select Term</option>
                            @foreach($schoolterms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select class="form-control rounded-3" name="sessionId" required>
                            <option value="">Select Session</option>
                            @foreach($schoolsessions as $session)
                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUpdateCurrentTerm">Register Term</button>
            </div>
        </div>
    </div>
</div>

<!-- Print/Export Report Modal -->
<div id="printStudentReportModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title"><i class="fas fa-file-export me-2"></i>Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="printReportForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id">
                                <option value="">All Classes</option>
                                @foreach($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All</option>
                                <option value="1">Old Students</option>
                                <option value="2">New Students</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Term</label>
                            <select class="form-select" name="term_id">
                                <option value="">All Terms</option>
                                @foreach($schoolterms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Session</label>
                            <select class="form-select" name="session_id">
                                <option value="">All Sessions</option>
                                @foreach($schoolsessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_pdf" value="pdf" checked>
                                <label class="form-check-label" for="format_pdf">PDF</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_excel" value="excel">
                                <label class="form-check-label" for="format_excel">Excel</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="generateReportBtn">Generate & Download</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
(function() {
    'use strict';

    // Nigerian States and LGAs - COMPLETE DATABASE
    const NIGERIAN_STATES = [
        { name: "Abia", lgas: ["Aba North","Aba South","Arochukwu","Bende","Ikwuano","Isiala Ngwa North","Isiala Ngwa South","Isuikwuato","Obi Ngwa","Ohafia","Osisioma","Ugwunagbo","Ukwa East","Ukwa West","Umuahia North","Umuahia South","Umu Nneochi"] },
        { name: "Adamawa", lgas: ["Demsa","Fufure","Ganye","Gayuk","Gombi","Grie","Hong","Jada","Lamurde","Madagali","Maiha","Mayo Belwa","Michika","Mubi North","Mubi South","Numan","Shelleng","Song","Toungo","Yola North","Yola South"] },
        { name: "Akwa Ibom", lgas: ["Abak","Eastern Obolo","Eket","Esit Eket","Essien Udim","Etim Ekpo","Etinan","Ibeno","Ibesikpo Asutan","Ibiono-Ibom","Ika","Ikono","Ikot Abasi","Ikot Ekpene","Ini","Itu","Mbo","Mkpat-Enin","Nsit-Atai","Nsit-Ibom","Nsit-Ubium","Obot Akara","Okobo","Onna","Oron","Oruk Anam","Udung-Uko","Ukanafun","Uruan","Urue-Offong/Oruko","Uyo"] },
        { name: "Anambra", lgas: ["Aguata","Anambra East","Anambra West","Anaocha","Awka North","Awka South","Ayamelum","Dunukofia","Ekwusigo","Idemili North","Idemili South","Ihiala","Njikoka","Nnewi North","Nnewi South","Ogbaru","Onitsha North","Onitsha South","Orumba North","Orumba South","Oyi"] },
        { name: "Bauchi", lgas: ["Alkaleri","Bauchi","Bogoro","Damban","Darazo","Dass","Gamawa","Ganjuwa","Giade","Itas/Gadau","Jama'are","Katagum","Kirfi","Misau","Ningi","Shira","Tafawa Balewa","Toro","Warji","Zaki"] },
        { name: "Bayelsa", lgas: ["Brass","Ekeremor","Kolokuma/Opokuma","Nembe","Ogbia","Sagbama","Southern Ijaw","Yenagoa"] },
        { name: "Benue", lgas: ["Ado","Agatu","Apa","Buruku","Gboko","Guma","Gwer East","Gwer West","Katsina-Ala","Konshisha","Kwande","Logo","Makurdi","Obi","Ogbadibo","Ohimini","Oju","Okpokwu","Oturkpo","Tarka","Ukum","Ushongo","Vandeikya"] },
        { name: "Borno", lgas: ["Abadam","Askira/Uba","Bama","Bayo","Biu","Chibok","Damboa","Dikwa","Gubio","Guzamala","Gwoza","Hawul","Jere","Kaga","Kala/Balge","Konduga","Kukawa","Kwaya Kusar","Mafa","Magumeri","Maiduguri","Marte","Mobbar","Monguno","Ngala","Nganzai","Shani"] },
        { name: "Cross River", lgas: ["Abi","Akamkpa","Akpabuyo","Bakassi","Bekwarra","Biase","Boki","Calabar Municipal","Calabar South","Etung","Ikom","Obanliku","Obubra","Obudu","Odukpani","Ogoja","Yakuur","Yala"] },
        { name: "Delta", lgas: ["Aniocha North","Aniocha South","Bomadi","Burutu","Ethiope East","Ethiope West","Ika North East","Ika South","Isoko North","Isoko South","Ndokwa East","Ndokwa West","Okpe","Oshimili North","Oshimili South","Patani","Sapele","Udu","Ughelli North","Ughelli South","Ukwuani","Uvwie","Warri North","Warri South","Warri South West"] },
        { name: "Ebonyi", lgas: ["Abakaliki","Afikpo North","Afikpo South","Ebonyi","Ezza North","Ezza South","Ikwo","Ishielu","Ivo","Izzi","Ohaozara","Ohaukwu","Onicha"] },
        { name: "Edo", lgas: ["Akoko-Edo","Egor","Esan Central","Esan North-East","Esan South-East","Esan West","Etsako Central","Etsako East","Etsako West","Igueben","Ikpoba Okha","Orhionmwon","Oredo","Ovia North-East","Ovia South-West","Owan East","Owan West","Uhunmwonde"] },
        { name: "Ekiti", lgas: ["Ado Ekiti","Efon","Ekiti East","Ekiti South-West","Ekiti West","Emure","Gbonyin","Ido Osi","Ijero","Ikere","Ilejemeje","Irepodun/Ifelodun","Ise/Orun","Moba","Oye"] },
        { name: "Enugu", lgas: ["Aninri","Awgu","Enugu East","Enugu North","Enugu South","Ezeagu","Igbo Etiti","Igbo Eze North","Igbo Eze South","Isi Uzo","Nkanu East","Nkanu West","Nsukka","Oji River","Udenu","Udi","Uzo Uwani"] },
        { name: "FCT", lgas: ["Abaji","Bwari","Gwagwalada","Kuje","Kwali","Municipal Area Council"] },
        { name: "Gombe", lgas: ["Akko","Balanga","Billiri","Dukku","Funakaye","Gombe","Kaltungo","Kwami","Nafada","Shongom","Yamaltu/Deba"] },
        { name: "Imo", lgas: ["Aboh Mbaise","Ahiazu Mbaise","Ehime Mbano","Ezinihitte","Ideato North","Ideato South","Ihitte/Uboma","Ikeduru","Isiala Mbano","Isu","Mbaitoli","Ngor Okpala","Njaba","Nkwerre","Nwangele","Obowo","Oguta","Ohaji/Egbema","Okigwe","Orlu","Orsu","Oru East","Oru West","Owerri Municipal","Owerri North","Owerri West","Unuimo"] },
        { name: "Jigawa", lgas: ["Auyo","Babura","Biriniwa","Birnin Kudu","Buji","Dutse","Gagarawa","Garki","Gumel","Guri","Gwaram","Gwiwa","Hadejia","Jahun","Kafin Hausa","Kazaure","Kiri Kasama","Kiyawa","Kaugama","Maigatari","Malam Madori","Miga","Ringim","Roni","Sule Tankarkar","Taura","Yankwashi"] },
        { name: "Kaduna", lgas: ["Birnin Gwari","Chikun","Giwa","Igabi","Ikara","Jaba","Jema'a","Kachia","Kaduna North","Kaduna South","Kagarko","Kajuru","Kaura","Kauru","Kubau","Kudan","Lere","Makarfi","Sabon Gari","Sanga","Soba","Zangon Kataf","Zaria"] },
        { name: "Kano", lgas: ["Ajingi","Albasu","Bagwai","Bebeji","Bichi","Bunkure","Dala","Dambatta","Dawakin Kudu","Dawakin Tofa","Doguwa","Fagge","Gabasawa","Garko","Garun Mallam","Gaya","Gezawa","Gwale","Gwarzo","Kabo","Kano Municipal","Karaye","Kibiya","Kiru","Kumbotso","Kunchi","Kura","Madobi","Makoda","Minjibir","Nasarawa","Rano","Rimin Gado","Rogo","Shanono","Sumaila","Takai","Tarauni","Tofa","Tsanyawa","Tudun Wada","Ungogo","Warawa","Wudil"] },
        { name: "Katsina", lgas: ["Bakori","Batagarawa","Batsari","Baure","Bindawa","Charanchi","Dan Musa","Dandume","Danja","Daura","Dutsi","Dutsin Ma","Faskari","Funtua","Ingawa","Jibia","Kafur","Kaita","Kankara","Kankia","Katsina","Kurfi","Kusada","Mai'Adua","Malumfashi","Mani","Mashi","Matazu","Musawa","Rimi","Sabuwa","Safana","Sandamu","Zango"] },
        { name: "Kebbi", lgas: ["Aleiro","Arewa Dandi","Argungu","Augie","Bagudo","Birnin Kebbi","Bunza","Dandi","Fakai","Gwandu","Jega","Kalgo","Koko/Besse","Maiyama","Ngaski","Sakaba","Shanga","Suru","Danko/Wasagu","Yauri","Zuru"] },
        { name: "Kogi", lgas: ["Adavi","Ajaokuta","Ankpa","Bassa","Dekina","Ibaji","Idah","Igalamela Odolu","Ijumu","Kabba/Bunu","Kogi","Lokoja","Mopa Muro","Ofu","Ogori/Magongo","Okehi","Okene","Olamaboro","Omala","Yagba East","Yagba West"] },
        { name: "Kwara", lgas: ["Asa","Baruten","Edu","Ekiti","Ifelodun","Ilorin East","Ilorin South","Ilorin West","Irepodun","Isin","Kaiama","Moro","Offa","Oke Ero","Oyun","Pategi"] },
        { name: "Lagos", lgas: ["Agege","Ajeromi-Ifelodun","Alimosho","Amuwo-Odofin","Apapa","Badagry","Epe","Eti Osa","Ibeju-Lekki","Ifako-Ijaiye","Ikeja","Ikorodu","Kosofe","Lagos Island","Lagos Mainland","Mushin","Ojo","Oshodi-Isolo","Shomolu","Surulere"] },
        { name: "Nasarawa", lgas: ["Akwanga","Awe","Doma","Karu","Keana","Keffi","Kokona","Lafia","Nasarawa","Nasarawa Egon","Obi","Toto","Wamba"] },
        { name: "Niger", lgas: ["Agaie","Agwara","Bida","Borgu","Bosso","Chanchaga","Edati","Gbako","Gurara","Katcha","Kontagora","Lapai","Lavun","Magama","Mariga","Mashegu","Mokwa","Moya","Paikoro","Rafi","Rijau","Shiroro","Suleja","Tafa","Wushishi"] },
        { name: "Ogun", lgas: ["Abeokuta North","Abeokuta South","Ado-Odo/Ota","Egbado North","Egbado South","Ewekoro","Ifo","Ijebu East","Ijebu North","Ijebu North East","Ijebu Ode","Ikenne","Imeko Afon","Ipokia","Obafemi Owode","Odeda","Odogbolu","Ogun Waterside","Remo North","Shagamu"] },
        { name: "Ondo", lgas: ["Akoko North-East","Akoko North-West","Akoko South-East","Akoko South-West","Akure North","Akure South","Ese Odo","Idanre","Ifedore","Ilaje","Ile Oluji/Okeigbo","Irele","Odigbo","Okitipupa","Ondo East","Ondo West","Ose","Owo"] },
        { name: "Osun", lgas: ["Aiyedade","Aiyedire","Atakunmosa East","Atakunmosa West","Boluwaduro","Boripe","Ede North","Ede South","Egbedore","Ejigbo","Ife Central","Ife East","Ife North","Ife South","Ifedayo","Ifelodun","Ila","Ilesa East","Ilesa West","Irepodun","Irewole","Isokan","Iwo","Obokun","Odo Otin","Ola Oluwa","Olorunda","Oriade","Orolu","Osogbo"] },
        { name: "Oyo", lgas: ["Afijio","Akinyele","Atiba","Atisbo","Egbeda","Ibadan North","Ibadan North-East","Ibadan North-West","Ibadan South-East","Ibadan South-West","Ibarapa Central","Ibarapa East","Ibarapa North","Ido","Irepo","Iseyin","Itesiwaju","Iwajowa","Kajola","Lagelu","Ogbomosho North","Ogbomosho South","Ogo Oluwa","Olorunsogo","Oluyole","Ona Ara","Orelope","Ori Ire","Oyo East","Oyo West","Saki East","Saki West","Surulere"] },
        { name: "Plateau", lgas: ["Bokkos","Barkin Ladi","Bassa","Jos East","Jos North","Jos South","Kanam","Kanke","Langtang North","Langtang South","Mangu","Mikang","Pankshin","Qua'an Pan","Riyom","Shendam","Wase"] },
        { name: "Rivers", lgas: ["Abua/Odual","Ahoada East","Ahoada West","Akuku-Toru","Andoni","Asari-Toru","Bonny","Degema","Eleme","Emohua","Etche","Gokana","Ikwerre","Khana","Obio/Akpor","Ogba/Egbema/Ndoni","Ogu/Bolo","Okrika","Omuma","Opobo/Nkoro","Oyigbo","Port Harcourt","Tai"] },
        { name: "Sokoto", lgas: ["Binji","Bodinga","Dange Shuni","Gada","Goronyo","Gudu","Gwadabawa","Illela","Isa","Kebbe","Kware","Rabah","Sabon Birni","Shagari","Silame","Sokoto North","Sokoto South","Tambuwal","Tangaza","Tureta","Wamako","Wurno","Yabo"] },
        { name: "Taraba", lgas: ["Ardo Kola","Bali","Donga","Gashaka","Gassol","Ibi","Jalingo","Karim Lamido","Kumi","Lau","Sardauna","Takum","Ussa","Wukari","Yorro","Zing"] },
        { name: "Yobe", lgas: ["Bade","Bursari","Damaturu","Fika","Fune","Geidam","Gujba","Gulani","Jakusko","Karasuwa","Machina","Nangere","Nguru","Potiskum","Tarmuwa","Yunusari","Yusufari"] },
        { name: "Zamfara", lgas: ["Anka","Bakura","Birnin Magaji/Kiyaw","Bukkuyum","Bungudu","Gummi","Gusau","Kaura Namoda","Maradun","Maru","Shinkafi","Talata Mafara","Chafe","Zurmi"] }
    ];

    // State/LGA Manager
    const StateLGAManager = {
        populateStateDropdown: function(stateSelectId, lgaSelectId) {
            const stateSelect = document.getElementById(stateSelectId);
            const lgaSelect = document.getElementById(lgaSelectId);
            if (!stateSelect || !lgaSelect) return;

            stateSelect.innerHTML = '<option value="">Select State</option>';
            NIGERIAN_STATES.forEach(state => {
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });

            stateSelect.onchange = function() {
                const selectedState = this.value;
                lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                if (selectedState) {
                    const state = NIGERIAN_STATES.find(s => s.name === selectedState);
                    if (state) {
                        lgaSelect.disabled = false;
                        state.lgas.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga;
                            option.textContent = lga;
                            lgaSelect.appendChild(option);
                        });
                    }
                } else {
                    lgaSelect.disabled = true;
                }
            };
        },

        setStateAndLGA: function(stateSelectId, lgaSelectId, stateValue, lgaValue) {
            this.populateStateDropdown(stateSelectId, lgaSelectId);
            if (stateValue) {
                setTimeout(() => {
                    const stateSelect = document.getElementById(stateSelectId);
                    if (stateSelect) {
                        stateSelect.value = stateValue;
                        // Trigger change event
                        const changeEvent = new Event('change');
                        stateSelect.dispatchEvent(changeEvent);
                        setTimeout(() => {
                            const lgaSelect = document.getElementById(lgaSelectId);
                            if (lgaSelect && lgaValue) {
                                lgaSelect.value = lgaValue;
                            }
                        }, 100);
                    }
                }, 100);
            }
        }
    };

    // State Management
    let currentPage = 1;
    let perPage = 25;
    let totalStudents = 0;
    let lastPage = 1;
    let currentView = 'table';
    let filters = { search: '', class: 'all', term: 'all', session: 'all' };

    // Utility Functions
    const Utils = {
        escapeHtml: (text) => {
            if (!text) return '';
            return text.toString().replace(/[&<>"']/g, (m) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[m]);
        },
        formatDate: (dateString) => {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });
            } catch { return 'N/A'; }
        },
        showLoading: () => {
            const loading = document.getElementById('loadingState');
            const table = document.getElementById('tableView');
            const cards = document.getElementById('cardView');
            const empty = document.getElementById('emptyState');
            if (loading) loading.classList.remove('d-none');
            if (table) table.classList.add('d-none');
            if (cards) cards.classList.add('d-none');
            if (empty) empty.classList.add('d-none');
        },
        hideLoading: () => {
            const loading = document.getElementById('loadingState');
            if (loading) loading.classList.add('d-none');
        },
        showError: (msg) => Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'OK' }),
        showSuccess: (msg) => Swal.fire({ title: 'Success', text: msg, icon: 'success', timer: 2000, showConfirmButton: false }),
        showConfirm: async (title, text) => {
            const result = await Swal.fire({ title, text, icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes', cancelButtonText: 'Cancel' });
            return result.isConfirmed;
        }
    };

    // API Service
    const API = {
        async getStudents(page = 1, perPageVal = 25, filterParams = null) {
            const params = new URLSearchParams();
            params.append('page', page);
            params.append('per_page', perPageVal);
            const activeFilters = filterParams || filters;
            if (activeFilters.search) params.append('search', activeFilters.search);
            if (activeFilters.class !== 'all') params.append('class_id', activeFilters.class);
            if (activeFilters.term !== 'all') params.append('term_id', activeFilters.term);
            if (activeFilters.session !== 'all') params.append('session_id', activeFilters.session);
            const response = await axios.get(`/students/optimized?${params.toString()}`);
            return response.data;
        },
        async deleteStudent(id) { return await axios.delete(`/student/${id}/destroy`); },
        async deleteMultiple(ids) { return await axios.post('/students/destroy-multiple', { ids }); },
        async updateBulkTerm(data) { return await axios.post('/student-current-term/bulk-update', data); },
        async generateReport(params) {
            return await axios({ method: 'GET', url: '/students/report', params, responseType: 'blob', timeout: 120000 });
        }
    };

    // Render Manager
    const Render = {
        table: (students) => {
            const tbody = document.getElementById('studentTableBody');
            if (!tbody) return;
            if (!students || students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5">No students found</td></tr>';
                return;
            }
            tbody.innerHTML = students.map(s => {
                const statusBadge = s.student_status === 'Active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                const typeBadge = s.statusId == 2 ? '<span class="badge bg-warning text-dark ms-1">New</span>' : (s.statusId == 1 ? '<span class="badge bg-info ms-1">Old</span>' : '');
                return `
                    <tr data-id="${s.id}">
                        <td><div class="form-check"><input class="form-check-input student-checkbox" type="checkbox" value="${s.id}"></div></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm"><span class="avatar-title rounded-circle bg-primary text-white">${(s.firstname?.charAt(0) || '')}${(s.lastname?.charAt(0) || '')}</span></div>
                                <div><h6 class="mb-0">${Utils.escapeHtml(s.lastname || '')} ${Utils.escapeHtml(s.firstname || '')}</h6><small class="text-muted">${Utils.escapeHtml(s.admissionNo || 'N/A')}</small></div>
                            </div>
                        </td>
                        <td>${Utils.escapeHtml(s.schoolclass || '')} ${Utils.escapeHtml(s.arm || '')}</td>
                        <td>${statusBadge}${typeBadge}</td>
                        <td><i class="fas fa-${s.gender === 'Male' ? 'mars text-primary' : 'venus text-danger'}"></i> ${s.gender || 'N/A'}</td>
                        <td>${Utils.formatDate(s.created_at)}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-soft-info view-student-btn" data-id="${s.id}"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-soft-warning edit-student-btn" data-id="${s.id}"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-soft-danger delete-student-btn" data-id="${s.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            this.updateCheckAll();
        },
        cards: (students) => {
            const container = document.getElementById('studentsCardsContainer');
            if (!container) return;
            if (!students || students.length === 0) {
                container.innerHTML = '<div class="col-12 text-center py-5">No students found</div>';
                return;
            }
            container.innerHTML = students.map(s => {
                const statusClass = s.student_status === 'Active' ? 'status-active' : 'status-inactive';
                const typeClass = s.statusId == 2 ? 'status-new' : (s.statusId == 1 ? 'status-old' : '');
                return `
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="student-profile-card">
                            <div class="checkbox-container"><div class="form-check"><input class="form-check-input student-checkbox" type="checkbox" value="${s.id}"></div></div>
                            <div class="card-header">
                                <div class="header-content"><h5 class="student-name">${Utils.escapeHtml(s.lastname || '')} ${Utils.escapeHtml(s.firstname || '')}</h5><span class="student-admission">${Utils.escapeHtml(s.admissionNo || 'N/A')}</span></div>
                                <div class="avatar-container"><div class="avatar-initials">${(s.firstname?.charAt(0) || '')}${(s.lastname?.charAt(0) || '')}</div></div>
                            </div>
                            <div class="card-body">
                                <div><span class="status-badge ${statusClass}"><i class="fas fa-circle me-1"></i>${s.student_status || 'Inactive'}</span>${s.statusId == 2 ? '<span class="status-badge status-new ms-2"><i class="fas fa-star me-1"></i>New</span>' : (s.statusId == 1 ? '<span class="status-badge status-old ms-2"><i class="fas fa-history me-1"></i>Old</span>' : '')}</div>
                                <div class="student-info-grid">
                                    <div class="info-item"><span class="info-label">Class</span><span class="info-value">${Utils.escapeHtml(s.schoolclass || '')} ${Utils.escapeHtml(s.arm || '')}</span></div>
                                    <div class="info-item"><span class="info-label">Gender</span><span class="info-value">${s.gender || 'N/A'}</span></div>
                                    <div class="info-item"><span class="info-label">Age</span><span class="info-value">${s.age || 'N/A'}</span></div>
                                    <div class="info-item"><span class="info-label">Registered</span><span class="info-value">${Utils.formatDate(s.created_at)}</span></div>
                                </div>
                                <div class="action-buttons">
                                    <button class="action-btn view-btn view-student-btn" data-id="${s.id}"><i class="fas fa-eye"></i> View</button>
                                    <button class="action-btn edit-btn edit-student-btn" data-id="${s.id}"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="action-btn delete-btn delete-student-btn" data-id="${s.id}"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            this.updateCheckAll();
        },
        updateCheckAll: () => {
            const all = document.querySelectorAll('.student-checkbox').length;
            const checked = document.querySelectorAll('.student-checkbox:checked').length;
            ['checkAll', 'checkAllTable'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.checked = all > 0 && all === checked; el.indeterminate = checked > 0 && checked < all; }
            });
            const bulkBtn = document.getElementById('bulkActionsDropdown');
            if (bulkBtn) {
                bulkBtn.disabled = checked === 0;
                bulkBtn.innerHTML = checked > 0 ? `<i class="fas fa-cog me-2"></i>Actions (${checked})` : `<i class="fas fa-cog me-2"></i>Actions`;
            }
        },
        toggleView: (view) => {
            currentView = view;
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');
            if (view === 'table') {
                if (tableView) tableView.classList.remove('d-none');
                if (cardView) cardView.classList.add('d-none');
                if (tableViewBtn) tableViewBtn.classList.add('active');
                if (cardViewBtn) cardViewBtn.classList.remove('active');
            } else {
                if (tableView) tableView.classList.add('d-none');
                if (cardView) cardView.classList.remove('d-none');
                if (tableViewBtn) tableViewBtn.classList.remove('active');
                if (cardViewBtn) cardViewBtn.classList.add('active');
            }
        },
        updatePagination: (pagination) => {
            document.getElementById('showingCount').textContent = pagination.from || 0;
            document.getElementById('toCount').textContent = pagination.to || 0;
            document.getElementById('totalCount').textContent = pagination.total || 0;
            document.getElementById('totalStudents').textContent = pagination.total || 0;
            totalStudents = pagination.total;
            lastPage = pagination.last_page;
            const ul = document.getElementById('pagination');
            if (!ul) return;
            ul.querySelectorAll('.page-item:not(#prevPageLi):not(#nextPageLi)').forEach(el => el.remove());
            if (lastPage > 1) {
                let start = Math.max(1, currentPage - 2);
                let end = Math.min(lastPage, currentPage + 2);
                const addItem = (pageNum) => {
                    const li = document.createElement('li');
                    li.className = `page-item ${pageNum === currentPage ? 'active' : ''}`;
                    li.innerHTML = `<a class="page-link" href="javascript:void(0);" data-page="${pageNum}">${pageNum}</a>`;
                    ul.querySelector('#nextPageLi').before(li);
                };
                if (start > 1) { addItem(1); if (start > 2) { const li = document.createElement('li'); li.className = 'page-item disabled'; li.innerHTML = '<span class="page-link">...</span>'; ul.querySelector('#nextPageLi').before(li); } }
                for (let i = start; i <= end; i++) addItem(i);
                if (end < lastPage) { if (end < lastPage - 1) { const li = document.createElement('li'); li.className = 'page-item disabled'; li.innerHTML = '<span class="page-link">...</span>'; ul.querySelector('#nextPageLi').before(li); } addItem(lastPage); }
            }
            document.getElementById('prevPageLi').classList.toggle('disabled', currentPage <= 1);
            document.getElementById('nextPageLi').classList.toggle('disabled', currentPage >= lastPage);
        }
    };

    // Student Manager
    const StudentManager = {
        async fetchStudents() {
            Utils.showLoading();
            try {
                const response = await API.getStudents(currentPage, perPage, filters);
                if (response.success && response.data) {
                    const pagination = response.data;
                    if (currentView === 'table') Render.table(pagination.data);
                    else Render.cards(pagination.data);
                    Render.updatePagination(pagination);
                } else throw new Error(response.message || 'Failed to load students');
            } catch (error) {
                Utils.showError('Failed to load students. Please try again.');
                document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger">Error loading students</td></tr>';
            } finally { Utils.hideLoading(); }
        },
        async deleteStudent(id) {
            if (!await Utils.showConfirm('Delete Student', 'This action cannot be undone!')) return;
            try { await API.deleteStudent(id); Utils.showSuccess('Student deleted successfully'); await this.fetchStudents(); }
            catch (error) { Utils.showError('Failed to delete student'); }
        },
        async deleteMultiple() {
            const ids = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) { Utils.showError('Please select at least one student'); return; }
            if (!await Utils.showConfirm(`Delete ${ids.length} Students?`, 'This action cannot be undone!')) return;
            try { await API.deleteMultiple(ids); Utils.showSuccess(`${ids.length} student(s) deleted`); await this.fetchStudents(); }
            catch (error) { Utils.showError('Failed to delete students'); }
        }
    };

    // Filter Manager
    const FilterManager = {
        applyFilters: () => {
            filters.search = document.getElementById('search-input')?.value || '';
            filters.class = document.getElementById('schoolclass-filter')?.value || 'all';
            filters.term = document.getElementById('term-filter')?.value || 'all';
            filters.session = document.getElementById('session-filter')?.value || 'all';
            currentPage = 1;
            StudentManager.fetchStudents();
        },
        resetFilters: () => {
            const searchInput = document.getElementById('search-input');
            if (searchInput) searchInput.value = '';
            const classFilter = document.getElementById('schoolclass-filter');
            const termFilter = document.getElementById('term-filter');
            const sessionFilter = document.getElementById('session-filter');
            if (classFilter) classFilter.value = 'all';
            if (termFilter) termFilter.value = 'all';
            if (sessionFilter) sessionFilter.value = 'all';
            const clearBtn = document.getElementById('clear-search');
            if (clearBtn) clearBtn.style.display = 'none';
            filters = { search: '', class: 'all', term: 'all', session: 'all' };
            currentPage = 1;
            StudentManager.fetchStudents();
        }
    };

    // Pagination Handler
    const PaginationHandler = {
        goToPage: (page) => { if (page >= 1 && page <= lastPage) { currentPage = page; StudentManager.fetchStudents(); } }
    };

    // Current Term Manager
    const CurrentTermManager = {
        showModal: () => {
            const ids = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) { Utils.showError('Please select at least one student'); return; }
            document.getElementById('selectedStudentsCount').textContent = ids.length;
            new bootstrap.Modal(document.getElementById('updateCurrentTermModal')).show();
        },
        async updateTerm() {
            const ids = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
            const classId = document.querySelector('[name="schoolclassId"]')?.value;
            const termId = document.querySelector('[name="termId"]')?.value;
            const sessionId = document.querySelector('[name="sessionId"]')?.value;
            if (!classId || !termId || !sessionId) { Utils.showError('Please select class, term, and session'); return; }
            try {
                await API.updateBulkTerm({ student_ids: ids, schoolclassId: classId, termId, sessionId, is_current: true });
                bootstrap.Modal.getInstance(document.getElementById('updateCurrentTermModal'))?.hide();
                Utils.showSuccess('Term updated successfully');
                await StudentManager.fetchStudents();
            } catch (error) { Utils.showError('Failed to update term'); }
        }
    };

    // Report Manager
    const ReportManager = {
        async generateReport() {
            const form = document.getElementById('printReportForm');
            const params = {};
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) if (value) params[key] = value;
            if (!params.format) params.format = 'pdf';
            try {
                Swal.fire({ title: 'Generating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const response = await API.generateReport(params);
                Swal.close();
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = url;
                a.download = `student-report.${params.format === 'excel' ? 'xlsx' : 'pdf'}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                Utils.showSuccess('Report generated successfully');
                bootstrap.Modal.getInstance(document.getElementById('printStudentReportModal'))?.hide();
            } catch (error) {
                Swal.close();
                Utils.showError('Failed to generate report');
            }
        }
    };

    // Event Setup
    function setupEventListeners() {
        document.getElementById('tableViewBtn')?.addEventListener('click', () => Render.toggleView('table'));
        document.getElementById('cardViewBtn')?.addEventListener('click', () => Render.toggleView('card'));
        document.getElementById('filterBtn')?.addEventListener('click', () => FilterManager.applyFilters());
        document.getElementById('resetFiltersBtn')?.addEventListener('click', () => FilterManager.resetFilters());
        document.getElementById('resetFromEmptyBtn')?.addEventListener('click', () => FilterManager.resetFilters());

        const searchInput = document.getElementById('search-input');
        const clearSearch = document.getElementById('clear-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                if (clearSearch) clearSearch.style.display = e.target.value ? 'block' : 'none';
                FilterManager.applyFilters();
            });
        }
        if (clearSearch) {
            clearSearch.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                clearSearch.style.display = 'none';
                FilterManager.applyFilters();
            });
        }

        document.getElementById('deleteMultipleBtn')?.addEventListener('click', () => StudentManager.deleteMultiple());
        document.getElementById('updateCurrentTermBtn')?.addEventListener('click', () => CurrentTermManager.showModal());
        document.getElementById('confirmUpdateCurrentTerm')?.addEventListener('click', () => CurrentTermManager.updateTerm());
        document.getElementById('generateReportBtn')?.addEventListener('click', () => ReportManager.generateReport());

        ['checkAll', 'checkAllTable'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', (e) => {
                document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = e.target.checked);
                Render.updateCheckAll();
            });
        });

        document.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.view-student-btn');
            if (viewBtn) { console.log('View student:', viewBtn.dataset.id); return; }
            const editBtn = e.target.closest('.edit-student-btn');
            if (editBtn) { console.log('Edit student:', editBtn.dataset.id); return; }
            const deleteBtn = e.target.closest('.delete-student-btn');
            if (deleteBtn) { StudentManager.deleteStudent(deleteBtn.dataset.id); return; }
        });

        document.addEventListener('change', (e) => { if (e.target.classList.contains('student-checkbox')) Render.updateCheckAll(); });

        document.getElementById('pagination')?.addEventListener('click', (e) => {
            const link = e.target.closest('.page-link');
            if (link && link.dataset.page) { e.preventDefault(); PaginationHandler.goToPage(parseInt(link.dataset.page)); }
        });
        document.getElementById('prevPage')?.addEventListener('click', (e) => { e.preventDefault(); PaginationHandler.goToPage(currentPage - 1); });
        document.getElementById('nextPage')?.addEventListener('click', (e) => { e.preventDefault(); PaginationHandler.goToPage(currentPage + 1); });
    }

    // Initialize
    function init() {
        StateLGAManager.populateStateDropdown('addState', 'addLocal');
        StateLGAManager.populateStateDropdown('editState', 'editLocal');
        setupEventListeners();
        StudentManager.fetchStudents();
    }

    // Global functions for inline handlers
    window.updateAdmissionNumber = (prefix) => {
        const yearSelect = document.getElementById(`${prefix}admissionYear`);
        const admissionNo = document.getElementById(`${prefix}admissionNo`);
        const mode = document.querySelector(`input[name="admissionMode"]:checked`);
        if (admissionNo && yearSelect) {
            const year = yearSelect.value;
            admissionNo.value = `TCC/${year}/0001`;
            if (mode && mode.value === 'auto') admissionNo.readOnly = true;
        }
    };
    window.toggleAdmissionInput = (prefix) => {
        const mode = document.querySelector(`input[name="admissionMode"]:checked`);
        const input = document.getElementById(`${prefix}admissionNo`);
        if (input && mode) input.readOnly = mode.value === 'auto';
    };
    window.previewImage = (input, targetId = 'addStudentAvatar') => {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => { const img = document.getElementById(targetId); if (img) img.src = e.target.result; };
        reader.readAsDataURL(file);
    };
    window.calculateAge = (dob, ageInputId) => {
        if (!dob) return;
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        const ageInput = document.getElementById(ageInputId);
        if (ageInput) ageInput.value = age;
    };

    init();
})();
</script>
@endsection
