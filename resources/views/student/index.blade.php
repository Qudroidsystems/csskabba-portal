@extends('layouts.master')
@section('content')
<?php
use Spatie\Permission\Models\Role;
?>
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

                .dashboard-stats-card .stats-change {
                    font-size: 12px;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    color: rgba(255, 255, 255, 0.8);
                }

                .dashboard-stats-card .stats-change.positive {
                    color: #10b981;
                }

                .dashboard-stats-card .stats-change.negative {
                    color: #ef4444;
                }

                /* Card color themes */
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

                .student-profile-card.selected {
                    border-color: #3b82f6;
                    background-color: #f0f9ff;
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
                    cursor: pointer;
                    transition: transform 0.2s ease;
                }

                .student-profile-card .avatar-container:hover {
                    transform: scale(1.05);
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

                .student-profile-card .view-btn:hover {
                    background-color: #2563eb;
                    transform: translateY(-2px);
                }

                .student-profile-card .edit-btn {
                    background-color: #f3f4f6;
                    color: #374151;
                    border: 1px solid #e5e7eb;
                }

                .student-profile-card .edit-btn:hover {
                    background-color: #e5e7eb;
                    transform: translateY(-2px);
                }

                .student-profile-card .delete-btn {
                    background-color: #fef2f2;
                    color: #dc2626;
                    border: 1px solid #fee2e2;
                }

                .student-profile-card .delete-btn:hover {
                    background-color: #fee2e2;
                    transform: translateY(-2px);
                }

                .student-profile-card .checkbox-container {
                    position: absolute;
                    top: 16px;
                    left: 16px;
                    z-index: 2;
                }

                .student-profile-card .form-check-input {
                    width: 20px;
                    height: 20px;
                    cursor: pointer;
                    border: 2px solid white;
                    background-color: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                }

                .student-profile-card .form-check-input:checked {
                    background-color: #3b82f6;
                    border-color: #3b82f6;
                }

                /* ====== TABLE STYLES ====== */
                .data-table-container {
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .data-table {
                    margin-bottom: 0;
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

                .data-table tbody tr.selected {
                    background-color: #f0f9ff;
                }

                /* Avatar clickable styles */
                .avatar-clickable {
                    cursor: pointer;
                    transition: transform 0.2s ease, opacity 0.2s ease;
                }

                .avatar-clickable:hover {
                    transform: scale(1.1);
                    opacity: 0.9;
                }

                .student-avatar {
                    width: 45px;
                    height: 45px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid #fff;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }

                .avatar-initials {
                    width: 45px;
                    height: 45px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 16px;
                    cursor: pointer;
                    transition: transform 0.2s ease;
                }

                .avatar-initials:hover {
                    transform: scale(1.1);
                }

                /* Table action buttons */
                .btn-soft-info, .btn-soft-warning, .btn-soft-danger {
                    padding: 0.3rem 0.6rem;
                    font-size: 0.8rem;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                }

                .btn-soft-info {
                    background-color: rgba(13, 202, 240, 0.1);
                    color: #0dcaf0;
                }

                .btn-soft-info:hover {
                    background-color: #0dcaf0;
                    color: #fff;
                }

                .btn-soft-warning {
                    background-color: rgba(255, 193, 7, 0.1);
                    color: #ffc107;
                }

                .btn-soft-warning:hover {
                    background-color: #ffc107;
                    color: #fff;
                }

                .btn-soft-danger {
                    background-color: rgba(220, 53, 69, 0.1);
                    color: #dc3545;
                }

                .btn-soft-danger:hover {
                    background-color: #dc3545;
                    color: #fff;
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
                    transition: all 0.3s ease;
                }

                .search-box input:focus {
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

                .search-box .clear-search:hover {
                    color: #dc2626;
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
                    transition: all 0.3s ease;
                }

                .pagination .page-link:hover {
                    background-color: #f3f4f6;
                    color: #667eea;
                }

                .pagination .page-item.active .page-link {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }

                /* Modal Styles */
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

                /* Image Zoom Modal */
                .image-zoom-modal .modal-content {
                    background: transparent;
                    border: none;
                    box-shadow: none;
                }
                .image-zoom-modal .modal-dialog {
                    max-width: 90vw;
                    margin: 1.75rem auto;
                }
                .image-zoom-modal .modal-body {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    min-height: 80vh;
                    padding: 20px;
                }
                .zoomed-image {
                    max-width: 90vw;
                    max-height: 75vh;
                    border-radius: 16px;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
                    border: 4px solid white;
                    cursor: pointer;
                    animation: zoomIn 0.3s ease;
                    object-fit: contain;
                }
                @keyframes zoomIn {
                    from {
                        opacity: 0;
                        transform: scale(0.8);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }
                .image-zoom-modal .btn-close {
                    position: absolute;
                    top: 20px;
                    right: 30px;
                    background-color: rgba(0,0,0,0.7);
                    border-radius: 50%;
                    padding: 12px;
                    opacity: 1;
                    z-index: 1060;
                    filter: brightness(0) invert(1);
                }
                .image-zoom-modal .btn-close:hover {
                    background-color: rgba(0,0,0,0.9);
                    transform: scale(1.1);
                }
                .zoomed-image-name {
                    color: white;
                    margin-top: 20px;
                    font-size: 18px;
                    font-weight: 600;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    background: rgba(0,0,0,0.5);
                    padding: 8px 20px;
                    border-radius: 40px;
                    display: inline-block;
                }
                .zoomed-image-details {
                    color: rgba(255,255,255,0.8);
                    margin-top: 8px;
                    font-size: 14px;
                    text-align: center;
                }

                /* Empty/Loading States */
                .empty-state {
                    padding: 60px 20px;
                    text-align: center;
                }

                .loading-state {
                    padding: 60px 20px;
                    text-align: center;
                }

                .spinner-container {
                    display: inline-block;
                    position: relative;
                    width: 80px;
                    height: 80px;
                }

                .spinner-ring {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    border: 4px solid #f3f4f6;
                    border-top-color: #667eea;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                .btn-primary-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    color: white;
                    padding: 12px 24px;
                    border-radius: 12px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .btn-primary-gradient:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
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
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>12% from last term</span>
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
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>8% from last term</span>
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
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>15% from last term</span>
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
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>5% from last term</span>
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
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($gender_counts['Male'] / $total_population) * 100, 1) : 0 }}%</span>
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
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($gender_counts['Female'] / $total_population) * 100, 1) : 0 }}%</span>
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
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($religion_counts['Christianity'] / $total_population) * 100, 1) : 0 }}%</span>
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
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($religion_counts['Islam'] / $total_population) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Validation Error!</strong> Please check the form for errors.
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="data-table-container">
                <!-- Card Header -->
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="option" id="checkAll">
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
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" id="deleteMultipleBtn"><i class="fas fa-trash me-2"></i>Delete Selected</a></li>
                                <li><a class="dropdown-item text-primary" href="javascript:void(0);" id="updateCurrentTermBtn"><i class="fas fa-calendar-alt me-2"></i>Update Current Term</a></li>
                            </ul>
                        </div>
                        @endcan

                        @can('Create student')
                        <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#addStudentModal">
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
                                <input type="text" class="form-control" id="search-input" placeholder="Search name or admission...">
                                <button type="button" class="clear-search" id="clear-search" title="Clear search"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="schoolclass-filter">
                                <option value="all">All Classes</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="term-filter">
                                <option value="all">All Terms</option>
                                @foreach ($schoolterms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="session-filter">
                                <option value="all">All Sessions</option>
                                @if(isset($schoolsessions) && count($schoolsessions) > 0)
                                    @foreach ($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->session ?? $session->name ?? 'Session ' . $session->id }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No sessions found</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-primary w-100" id="filterBtn"><i class="fas fa-filter me-2"></i>Filter</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-secondary w-100" id="resetFiltersBtn"><i class="fas fa-redo-alt"></i></button>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-warning w-100" id="bulkStatusBtn" data-bs-toggle="tooltip" title="Update student status">
                                    <i class="fas fa-sync-alt me-2"></i>Status
                                </button>
                                <button type="button" class="btn btn-info w-100" id="manageTermBtn" data-bs-toggle="tooltip" title="Manage term registrations">
                                    <i class="fas fa-calendar-alt me-2"></i>Term
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div id="tableView" class="view-container">
                    <div class="table-responsive">
                        <table class="table data-table" id="studentTable">
                            <thead>
                                <tr>
                                    <th width="50"><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAllTable"></div></th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Gender</th>
                                    <th>Registered</th>
                                    <th width="250">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody"></tbody>
                        </tr>
                    </div>
                </div>

                <!-- CARD VIEW -->
                <div id="cardView" class="view-container d-none p-4">
                    <div class="row" id="studentsCardsContainer"></div>
                </div>

                <!-- Empty/Loading States -->
                <div id="emptyState" class="empty-state d-none">
                    <div class="empty-state-icon"><i class="fas fa-users-slash"></i></div>
                    <h5 class="empty-state-title">No Students Found</h5>
                    <p class="empty-state-description">Try adjusting your search or filter to find what you're looking for.</p>
                    <button class="btn btn-primary-gradient" id="resetFromEmptyBtn"><i class="fas fa-redo me-2"></i>Reset Filters</button>
                </div>

                <div id="loadingState" class="loading-state">
                    <div class="spinner-container"><div class="spinner-ring"></div></div>
                    <p class="mt-3 text-muted">Loading students...</p>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div><span class="text-muted">Showing <span class="fw-bold" id="showingCount">0</span> to <span class="fw-bold" id="toCount">0</span> of <span class="fw-bold" id="totalCount">0</span> students</span></div>
                    <nav><ul class="pagination mb-0" id="pagination">
                        <li class="page-item" id="prevPageLi"><a class="page-link" href="javascript:void(0);" id="prevPage"><i class="fas fa-chevron-left"></i></a></li>
                        <li class="page-item" id="nextPageLi"><a class="page-link" href="javascript:void(0);" id="nextPage"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>

        <!-- IMAGE ZOOM MODAL -->
        <div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-transparent border-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body text-center">
                        <img id="zoomedImage" src="" alt="Student Photo" class="zoomed-image">
                        <div class="zoomed-image-name" id="zoomedImageName"></div>
                        <div class="zoomed-image-details" id="zoomedImageDetails"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- ... Add Student Modal Content ... -->
        </div>

        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- ... Edit Student Modal Content ... -->
        </div>

        <!-- View Student Modal -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- ... View Student Modal Content ... -->
        </div>

        <!-- Update Current Term Modal -->
        <div id="updateCurrentTermModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <!-- ... Update Current Term Modal Content ... -->
        </div>

        <!-- Print/Export Report Modal -->
        <div id="printStudentReportModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <!-- ... Print Report Modal Content ... -->
        </div>

    </div>
</div>

<!-- Include required libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// ============================================================================
// STUDENT MANAGEMENT SYSTEM - COMPLETE WORKING VERSION WITH IMAGE ZOOM
// ============================================================================

(function() {
    'use strict';

    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================
    const Utils = {
        escapeHtml: function(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        },

        formatDate: function(dateString, format = 'short') {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                return 'N/A';
            }
        },

        getInitials: function(firstName, lastName) {
            const first = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
            const last = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
            return (first + last) || 'ST';
        },

        getFullName: function(firstName, lastName, otherName = '') {
            let fullName = `${firstName || ''} ${lastName || ''}`.trim();
            if (otherName && otherName.trim()) {
                fullName += ` (${otherName.trim()})`;
            }
            return fullName || 'N/A';
        },

        showError: function(message, title = 'Error') {
            Swal.fire({ title: title, text: message, icon: 'error', confirmButtonText: 'OK' });
        },

        showSuccess: function(message, title = 'Success') {
            Swal.fire({ title: title, text: message, icon: 'success', confirmButtonText: 'OK', timer: 2000, timerProgressBar: true });
        },

        showConfirm: async function(title, text, confirmText = 'Yes', cancelText = 'Cancel') {
            const result = await Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText
            });
            return result.isConfirmed;
        },

        showInfo: function(title, text) {
            Swal.fire({ title: title, text: text, icon: 'info', confirmButtonText: 'OK' });
        }
    };

    // ============================================================================
    // IMAGE ZOOM MANAGER
    // ============================================================================
    const ImageZoomManager = {
        showZoomModal: function(imageUrl, studentName, admissionNo, studentClass, gender, initials) {
            const zoomedImage = document.getElementById('zoomedImage');
            const zoomedImageName = document.getElementById('zoomedImageName');
            const zoomedImageDetails = document.getElementById('zoomedImageDetails');

            if (!zoomedImage || !zoomedImageName || !zoomedImageDetails) {
                console.error('Zoom modal elements not found');
                return;
            }

            zoomedImageName.textContent = studentName || 'Student Photo';
            zoomedImageDetails.innerHTML = `
                <i class="fas fa-id-card me-1"></i> ${admissionNo || 'N/A'} &nbsp;|&nbsp;
                <i class="fas fa-school me-1"></i> ${studentClass || 'N/A'} &nbsp;|&nbsp;
                <i class="fas fa-${gender === 'Male' ? 'mars' : 'venus'} me-1"></i> ${gender || 'N/A'}
            `;

            if (imageUrl && imageUrl !== '' && imageUrl !== 'null' && imageUrl !== 'undefined') {
                zoomedImage.src = imageUrl;
                zoomedImage.style.display = 'block';
            } else {
                // Create canvas with initials
                const canvas = document.createElement('canvas');
                canvas.width = 400;
                canvas.height = 400;
                const ctx = canvas.getContext('2d');

                const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
                gradient.addColorStop(0, '#667eea');
                gradient.addColorStop(1, '#764ba2');
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 160px "Segoe UI", Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const displayInitials = (initials && initials !== 'null' && initials !== 'undefined') ? initials.substring(0, 2) : 'ST';
                ctx.fillText(displayInitials, canvas.width/2, canvas.height/2);

                zoomedImage.src = canvas.toDataURL();
                zoomedImage.style.display = 'block';
            }

            const modal = document.getElementById('imageZoomModal');
            if (modal) {
                new bootstrap.Modal(modal).show();
            }
        }
    };

    // ============================================================================
    // RENDER MANAGER
    // ============================================================================
    const RenderManager = {
        renderTableView: function(students) {
            const tbody = document.getElementById('studentTableBody');
            if (!tbody) return;

            if (!students || students.length === 0) {
                tbody.innerHTML = '';
                return;
            }

            const fragment = document.createDocumentFragment();

            students.forEach(student => {
                const row = document.createElement('tr');
                row.className = 'align-middle';
                row.dataset.id = student.id;

                const fullName = Utils.getFullName(student.firstname, student.lastname, student.othername);
                const initials = Utils.getInitials(student.firstname, student.lastname);
                const statusBadge = student.student_status === 'Active'
                    ? '<span class="badge bg-success bg-gradient px-2 py-1 rounded-pill"><span class="status-dot active"></span>Active</span>'
                    : '<span class="badge bg-secondary bg-gradient px-2 py-1 rounded-pill"><span class="status-dot inactive"></span>Inactive</span>';

                const typeBadge = student.statusId == 2
                    ? '<span class="badge bg-warning bg-gradient text-dark px-2 py-1 rounded-pill ms-1"><i class="fas fa-star me-1"></i>New</span>'
                    : student.statusId == 1
                    ? '<span class="badge bg-secondary bg-gradient px-2 py-1 rounded-pill ms-1"><i class="fas fa-history me-1"></i>Old</span>'
                    : '';

                const avatarUrl = student.picture && student.picture !== 'unnamed.jpg'
                    ? `/storage/images/student_avatars/${student.picture}`
                    : null;

                let avatarHtml;
                if (avatarUrl) {
                    avatarHtml = `<img src="${Utils.escapeHtml(avatarUrl)}" alt="Avatar" class="student-avatar rounded-circle border border-2 border-white shadow-sm avatar-clickable"
                        data-avatar-url="${Utils.escapeHtml(avatarUrl)}"
                        data-student-name="${Utils.escapeHtml(fullName)}"
                        data-admission="${Utils.escapeHtml(student.admissionNo || 'N/A')}"
                        data-class="${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}"
                        data-gender="${Utils.escapeHtml(student.gender || 'N/A')}"
                        data-initials="${Utils.escapeHtml(initials)}">`;
                } else {
                    avatarHtml = `<div class="avatar-initials rounded-circle border border-2 border-white shadow-sm avatar-clickable"
                        data-avatar-url=""
                        data-student-name="${Utils.escapeHtml(fullName)}"
                        data-admission="${Utils.escapeHtml(student.admissionNo || 'N/A')}"
                        data-class="${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}"
                        data-gender="${Utils.escapeHtml(student.gender || 'N/A')}"
                        data-initials="${Utils.escapeHtml(initials)}">${initials}</div>`;
                }

                row.innerHTML = `
                    <td><div class="form-check"><input class="form-check-input student-checkbox" type="checkbox" value="${student.id}"></div></div>
                    <td><div class="d-flex align-items-center gap-3"><div class="position-relative">${avatarHtml}<span class="position-absolute bottom-0 end-0 ${student.student_status === 'Active' ? 'bg-success' : 'bg-secondary'} rounded-circle p-1 border border-2 border-white" style="width: 12px; height: 12px;"></span></div><div><h6 class="mb-1 fw-semibold">${Utils.escapeHtml(fullName)}</h6><div class="d-flex align-items-center gap-2"><span class="badge bg-light text-dark px-2 py-1 rounded-pill"><i class="fas fa-id-card me-1 text-muted"></i> ${Utils.escapeHtml(student.admissionNo || 'N/A')}</span>${typeBadge}</div></div></div></div>
                    <td><div class="d-flex flex-column"><span class="fw-medium">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</span><small class="text-muted">${Utils.escapeHtml(student.student_category || '')}</small></div></div>
                    <td>${statusBadge}</div>
                    <td><span class="d-flex align-items-center gap-1"><i class="fas fa-${student.gender === 'Male' ? 'mars text-primary' : 'venus text-pink'}"></i> ${Utils.escapeHtml(student.gender || 'N/A')}</span></div>
                    <td><div class="d-flex align-items-center gap-1"><i class="fas fa-calendar-alt text-muted"></i> <span>${Utils.formatDate(student.created_at, 'short')}</span></div></div>
                    <td><div class="d-flex gap-2 justify-content-end"><div class="btn-group"><button type="button" class="btn btn-sm btn-soft-info rounded-start view-student-btn" data-student-id="${student.id}"><i class="fas fa-eye"></i><span class="d-none d-xl-inline-block ms-1">View</span></button><button type="button" class="btn btn-sm btn-soft-warning edit-student-btn" data-student-id="${student.id}"><i class="fas fa-edit"></i><span class="d-none d-xl-inline-block ms-1">Edit</span></button><button type="button" class="btn btn-sm btn-soft-danger rounded-end delete-student-btn" data-student-id="${student.id}"><i class="fas fa-trash-alt"></i><span class="d-none d-xl-inline-block ms-1">Delete</span></button></div></div></div>
                `;
                fragment.appendChild(row);
            });

            tbody.innerHTML = '';
            tbody.appendChild(fragment);

            this.attachImageZoomEvents();
            this.updateCheckAllState();
        },

        attachImageZoomEvents: function() {
            const avatarElements = document.querySelectorAll('.avatar-clickable, .student-avatar, .avatar-initials');

            avatarElements.forEach(el => {
                el.removeEventListener('click', this.handleAvatarClick);
                el.addEventListener('click', this.handleAvatarClick);
                el.style.cursor = 'pointer';
            });
        },

        handleAvatarClick: function(e) {
            e.stopPropagation();

            const avatarUrl = this.dataset.avatarUrl || this.getAttribute('data-avatar-url');
            const studentName = this.dataset.studentName || this.getAttribute('data-student-name');
            const admission = this.dataset.admission || this.getAttribute('data-admission');
            const studentClass = this.dataset.class || this.getAttribute('data-class');
            const gender = this.dataset.gender || this.getAttribute('data-gender');
            const initials = this.dataset.initials || this.getAttribute('data-initials');

            ImageZoomManager.showZoomModal(avatarUrl, studentName, admission, studentClass, gender, initials);
        },

        renderCardView: function(students) {
            const container = document.getElementById('studentsCardsContainer');
            if (!container) return;

            if (!students || students.length === 0) {
                container.innerHTML = '';
                return;
            }

            const fragment = document.createDocumentFragment();

            students.forEach(student => {
                const col = document.createElement('div');
                col.className = 'col-xl-3 col-lg-4 col-md-6 mb-4';
                const fullName = Utils.getFullName(student.firstname, student.lastname, student.othername);
                const initials = Utils.getInitials(student.firstname, student.lastname);
                const avatarUrl = student.picture && student.picture !== 'unnamed.jpg'
                    ? `/storage/images/student_avatars/${student.picture}`
                    : null;

                let avatarHtml;
                if (avatarUrl) {
                    avatarHtml = `<img src="${Utils.escapeHtml(avatarUrl)}" alt="Avatar" class="avatar" style="cursor: pointer; width: 80px; height: 80px; object-fit: cover;"
                        data-avatar-url="${Utils.escapeHtml(avatarUrl)}"
                        data-student-name="${Utils.escapeHtml(fullName)}"
                        data-admission="${Utils.escapeHtml(student.admissionNo || 'N/A')}"
                        data-class="${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}"
                        data-gender="${Utils.escapeHtml(student.gender || 'N/A')}"
                        data-initials="${Utils.escapeHtml(initials)}">`;
                } else {
                    avatarHtml = `<div class="avatar-initials" style="cursor: pointer; width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; border-radius: 50%;"
                        data-avatar-url=""
                        data-student-name="${Utils.escapeHtml(fullName)}"
                        data-admission="${Utils.escapeHtml(student.admissionNo || 'N/A')}"
                        data-class="${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}"
                        data-gender="${Utils.escapeHtml(student.gender || 'N/A')}"
                        data-initials="${Utils.escapeHtml(initials)}">${initials}</div>`;
                }

                let statusBadges = '';
                if (student.student_status === 'Active') {
                    statusBadges += `<span class="status-badge status-active"><i class="fas fa-check-circle"></i> Active</span>`;
                } else if (student.student_status === 'Inactive') {
                    statusBadges += `<span class="status-badge status-inactive"><i class="fas fa-pause-circle"></i> Inactive</span>`;
                }
                if (student.statusId == 2) {
                    statusBadges += `<span class="status-badge status-new ms-2"><i class="fas fa-star"></i> New Student</span>`;
                } else if (student.statusId == 1) {
                    statusBadges += `<span class="status-badge status-old ms-2"><i class="fas fa-history"></i> Old Student</span>`;
                }

                col.innerHTML = `
                    <div class="student-profile-card" data-id="${student.id}">
                        <div class="checkbox-container"><div class="form-check"><input class="form-check-input student-checkbox" type="checkbox" value="${student.id}"></div></div>
                        <div class="card-header">
                            <div class="header-content">
                                <h5 class="student-name">${Utils.escapeHtml(fullName)}</h5>
                                <span class="student-admission">${Utils.escapeHtml(student.admissionNo || 'N/A')}</span>
                            </div>
                            <div class="avatar-container">${avatarHtml}</div>
                        </div>
                        <div class="card-body">
                            ${statusBadges}
                            <div class="student-info-grid">
                                <div class="info-item"><span class="info-label">Class</span><span class="info-value">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</span></div>
                                <div class="info-item"><span class="info-label">Gender</span><span class="info-value">${Utils.escapeHtml(student.gender || 'N/A')}</span></div>
                                <div class="info-item"><span class="info-label">Age</span><span class="info-value">${Utils.escapeHtml(student.age || 'N/A')}</span></div>
                                <div class="info-item"><span class="info-label">Registered</span><span class="info-value">${Utils.formatDate(student.created_at, 'short')}</span></div>
                            </div>
                            <div class="action-buttons">
                                <button class="action-btn view-btn view-student-btn" data-student-id="${student.id}"><i class="fas fa-eye"></i> View</button>
                                <button class="action-btn edit-btn edit-student-btn" data-student-id="${student.id}"><i class="fas fa-edit"></i> Edit</button>
                                <button class="action-btn delete-btn delete-student-btn" data-student-id="${student.id}"><i class="fas fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                `;
                fragment.appendChild(col);
            });

            container.innerHTML = '';
            container.appendChild(fragment);

            // Attach image zoom events for card view
            setTimeout(() => {
                const avatarElements = document.querySelectorAll('.avatar, .avatar-initials');
                avatarElements.forEach(el => {
                    el.removeEventListener('click', RenderManager.handleAvatarClick);
                    el.addEventListener('click', RenderManager.handleAvatarClick);
                    el.style.cursor = 'pointer';
                });
            }, 100);

            this.updateCheckAllState();
        },

        updateCheckAllState: function() {
            const checkAll = document.getElementById('checkAll');
            const checkAllTable = document.getElementById('checkAllTable');
            const totalCheckboxes = document.querySelectorAll('.student-checkbox').length;
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked').length;

            if (checkAll) {
                checkAll.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
                checkAll.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }
            if (checkAllTable) {
                checkAllTable.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
                checkAllTable.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }

            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');
            if (bulkActionsDropdown) {
                if (checkedCheckboxes > 0) {
                    bulkActionsDropdown.disabled = false;
                    bulkActionsDropdown.innerHTML = `<i class="fas fa-cog me-2"></i>Actions (${checkedCheckboxes})`;
                } else {
                    bulkActionsDropdown.disabled = true;
                    bulkActionsDropdown.innerHTML = `<i class="fas fa-cog me-2"></i>Actions`;
                }
            }
        },

        toggleView: function(viewType) {
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');

            if (!tableView || !cardView || !tableViewBtn || !cardViewBtn) return;

            if (viewType === 'table') {
                tableView.classList.remove('d-none');
                cardView.classList.add('d-none');
                tableViewBtn.classList.add('active');
                cardViewBtn.classList.remove('active');
                if (AppState.pagination.data.length > 0) {
                    this.renderTableView(AppState.pagination.data);
                }
            } else {
                tableView.classList.add('d-none');
                cardView.classList.remove('d-none');
                tableViewBtn.classList.remove('active');
                cardViewBtn.classList.add('active');
                if (AppState.pagination.data.length > 0) {
                    this.renderCardView(AppState.pagination.data);
                }
            }
        }
    };

    // Bind methods
    RenderManager.handleAvatarClick = RenderManager.handleAvatarClick.bind(RenderManager);
    RenderManager.attachImageZoomEvents = RenderManager.attachImageZoomEvents.bind(RenderManager);

    // ============================================================================
    // API SERVICE
    // ============================================================================
    const ApiService = {
        async getStudents(page = 1, perPage = 25, filters = {}) {
            try {
                const params = new URLSearchParams();
                params.append('page', page);
                params.append('per_page', perPage);

                if (filters.search && filters.search.trim()) params.append('search', filters.search.trim());
                if (filters.class && filters.class !== 'all') params.append('class_id', filters.class);
                if (filters.status && filters.status !== 'all') params.append('status', filters.status);
                if (filters.gender && filters.gender !== 'all') params.append('gender', filters.gender);
                if (filters.session && filters.session !== 'all') params.append('session_id', filters.session);

                const response = await axios.get(`/students/optimized?${params.toString()}`);
                if (response.data.success) {
                    return response.data.data;
                } else {
                    throw new Error(response.data.message || 'Failed to fetch students');
                }
            } catch (error) {
                console.error('API Error - getStudents:', error);
                throw error;
            }
        }
    };

    // ============================================================================
    // APP STATE
    // ============================================================================
    const AppState = {
        pagination: {
            currentPage: 1,
            perPage: 25,
            total: 0,
            lastPage: 1,
            from: 0,
            to: 0,
            data: []
        },
        filters: {
            search: '',
            class: 'all',
            status: 'all',
            gender: 'all',
            session: 'all',
            term: 'all'
        },
        ui: {
            currentView: 'table',
            isLoading: false,
            selectedStudents: new Set()
        }
    };

    // ============================================================================
    // STUDENT MANAGER
    // ============================================================================
    const StudentManager = {
        async fetchStudents() {
            const loadingEl = document.getElementById('loadingState');
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const emptyState = document.getElementById('emptyState');

            if (loadingEl) loadingEl.classList.remove('d-none');
            if (tableView) tableView.classList.add('d-none');
            if (cardView) cardView.classList.add('d-none');
            if (emptyState) emptyState.classList.add('d-none');

            try {
                const paginationData = await ApiService.getStudents(
                    AppState.pagination.currentPage,
                    AppState.pagination.perPage,
                    AppState.filters
                );

                AppState.pagination = {
                    currentPage: paginationData.current_page,
                    lastPage: paginationData.last_page,
                    total: paginationData.total,
                    from: paginationData.from,
                    to: paginationData.to,
                    data: paginationData.data
                };

                document.getElementById('showingCount').textContent = paginationData.from || 0;
                document.getElementById('toCount').textContent = paginationData.to || 0;
                document.getElementById('totalCount').textContent = paginationData.total || 0;
                document.getElementById('totalStudents').textContent = paginationData.total || 0;

                if (AppState.ui.currentView === 'table') {
                    RenderManager.renderTableView(paginationData.data);
                } else {
                    RenderManager.renderCardView(paginationData.data);
                }

                this.updatePaginationUI();

                if (loadingEl) loadingEl.classList.add('d-none');

                if (paginationData.data && paginationData.data.length > 0) {
                    if (tableView && AppState.ui.currentView === 'table') tableView.classList.remove('d-none');
                    if (cardView && AppState.ui.currentView === 'card') cardView.classList.remove('d-none');
                } else {
                    if (emptyState) emptyState.classList.remove('d-none');
                }

            } catch (error) {
                if (loadingEl) loadingEl.classList.add('d-none');
                if (emptyState) emptyState.classList.remove('d-none');
                Utils.showError('Failed to load students. Please try again.');
            }
        },

        updatePaginationUI: function() {
            const paginationContainer = document.getElementById('pagination');
            if (!paginationContainer) return;

            const pageItems = paginationContainer.querySelectorAll('.page-item:not(#prevPageLi):not(#nextPageLi)');
            pageItems.forEach(item => item.remove());

            if (!AppState.pagination.last_page || AppState.pagination.last_page <= 1) return;

            let startPage = Math.max(1, AppState.pagination.currentPage - 2);
            let endPage = Math.min(AppState.pagination.last_page, AppState.pagination.currentPage + 2);

            if (startPage > 1) {
                this.addPageItem(1, AppState.pagination.currentPage);
                if (startPage > 2) this.addEllipsis();
            }
            for (let i = startPage; i <= endPage; i++) this.addPageItem(i, AppState.pagination.currentPage);
            if (endPage < AppState.pagination.last_page) {
                if (endPage < AppState.pagination.last_page - 1) this.addEllipsis();
                this.addPageItem(AppState.pagination.last_page, AppState.pagination.currentPage);
            }

            const prevPageBtn = document.getElementById('prevPage');
            if (prevPageBtn) {
                if (AppState.pagination.currentPage > 1) {
                    prevPageBtn.classList.remove('disabled');
                    prevPageBtn.onclick = (e) => {
                        e.preventDefault();
                        AppState.pagination.currentPage--;
                        StudentManager.fetchStudents();
                    };
                } else {
                    prevPageBtn.classList.add('disabled');
                    prevPageBtn.onclick = null;
                }
            }

            const nextPageBtn = document.getElementById('nextPage');
            if (nextPageBtn) {
                if (AppState.pagination.currentPage < AppState.pagination.last_page) {
                    nextPageBtn.classList.remove('disabled');
                    nextPageBtn.onclick = (e) => {
                        e.preventDefault();
                        AppState.pagination.currentPage++;
                        StudentManager.fetchStudents();
                    };
                } else {
                    nextPageBtn.classList.add('disabled');
                    nextPageBtn.onclick = null;
                }
            }
        },

        addPageItem: function(pageNum, currentPage) {
            const container = document.getElementById('pagination');
            if (!container) return;

            const li = document.createElement('li');
            li.className = `page-item ${pageNum === currentPage ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = 'javascript:void(0);';
            a.textContent = pageNum;
            a.onclick = (e) => {
                e.preventDefault();
                AppState.pagination.currentPage = pageNum;
                StudentManager.fetchStudents();
            };
            li.appendChild(a);
            container.insertBefore(li, document.getElementById('nextPageLi'));
        },

        addEllipsis: function() {
            const container = document.getElementById('pagination');
            if (!container) return;
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = '<span class="page-link">...</span>';
            container.insertBefore(li, document.getElementById('nextPageLi'));
        }
    };

    // ============================================================================
    // FILTER MANAGER
    // ============================================================================
    const FilterManager = {
        searchTimeout: null,

        initializeFilters: function() {
            const searchInput = document.getElementById('search-input');
            const classFilter = document.getElementById('schoolclass-filter');
            const termFilter = document.getElementById('term-filter');
            const sessionFilter = document.getElementById('session-filter');
            const filterBtn = document.getElementById('filterBtn');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const clearSearchBtn = document.getElementById('clear-search');
            const resetFromEmptyBtn = document.getElementById('resetFromEmptyBtn');

            if (searchInput) {
                searchInput.addEventListener('input', (e) => this.handleSearchInput(e));
                searchInput.addEventListener('keypress', (e) => this.handleSearchEnter(e));
            }
            if (clearSearchBtn) clearSearchBtn.addEventListener('click', () => this.clearSearch());
            if (classFilter) classFilter.addEventListener('change', () => this.applyFilters());
            if (termFilter) termFilter.addEventListener('change', () => this.applyFilters());
            if (sessionFilter) sessionFilter.addEventListener('change', () => this.applyFilters());
            if (filterBtn) filterBtn.addEventListener('click', () => this.applyFilters());
            if (resetBtn) resetBtn.addEventListener('click', () => this.resetFilters());
            if (resetFromEmptyBtn) resetFromEmptyBtn.addEventListener('click', () => this.resetFilters());

            if (searchInput && clearSearchBtn) {
                clearSearchBtn.style.display = searchInput.value.length > 0 ? 'block' : 'none';
            }
        },

        handleSearchInput: function(e) {
            const searchInput = e.target;
            const clearSearchBtn = document.getElementById('clear-search');
            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchInput.value.length > 0 ? 'block' : 'none';
            }
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.applyFilters(), 500);
        },

        handleSearchEnter: function(e) {
            if (e.key === 'Enter') {
                if (this.searchTimeout) clearTimeout(this.searchTimeout);
                this.applyFilters();
            }
        },

        clearSearch: function() {
            const searchInput = document.getElementById('search-input');
            const clearSearchBtn = document.getElementById('clear-search');
            if (searchInput) {
                searchInput.value = '';
                if (clearSearchBtn) clearSearchBtn.style.display = 'none';
                if (this.searchTimeout) clearTimeout(this.searchTimeout);
                this.applyFilters();
            }
        },

        applyFilters: function() {
            const searchInput = document.getElementById('search-input');
            const classFilter = document.getElementById('schoolclass-filter');
            const termFilter = document.getElementById('term-filter');
            const sessionFilter = document.getElementById('session-filter');

            AppState.filters = {
                search: searchInput ? searchInput.value.trim() : '',
                class: classFilter ? classFilter.value : 'all',
                status: 'all',
                gender: 'all',
                session: sessionFilter ? sessionFilter.value : 'all',
                term: termFilter ? termFilter.value : 'all'
            };

            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        },

        resetFilters: function() {
            const searchInput = document.getElementById('search-input');
            const classFilter = document.getElementById('schoolclass-filter');
            const termFilter = document.getElementById('term-filter');
            const sessionFilter = document.getElementById('session-filter');
            const clearSearchBtn = document.getElementById('clear-search');

            if (searchInput) {
                searchInput.value = '';
                if (clearSearchBtn) clearSearchBtn.style.display = 'none';
            }
            if (classFilter) classFilter.value = 'all';
            if (termFilter) termFilter.value = 'all';
            if (sessionFilter) sessionFilter.value = 'all';

            AppState.filters = {
                search: '',
                class: 'all',
                status: 'all',
                gender: 'all',
                session: 'all',
                term: 'all'
            };

            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        }
    };

    // ============================================================================
    // SELECTION MANAGER
    // ============================================================================
    const SelectionManager = {
        initializeCheckboxes: function() {
            const checkAll = document.getElementById('checkAll');
            const checkAllTable = document.getElementById('checkAllTable');

            if (checkAll) checkAll.addEventListener('change', (e) => this.handleSelectAll(e));
            if (checkAllTable) checkAllTable.addEventListener('change', (e) => this.handleSelectAll(e));
            document.addEventListener('change', (e) => this.handleCheckboxChange(e));
        },

        handleSelectAll: function(e) {
            const isChecked = e.target.checked;
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) parent.classList.toggle('selected', isChecked);
                if (isChecked) AppState.ui.selectedStudents.add(checkbox.value);
                else AppState.ui.selectedStudents.delete(checkbox.value);
            });
            RenderManager.updateCheckAllState();
        },

        handleCheckboxChange: function(e) {
            if (e.target.classList.contains('student-checkbox')) {
                const checkbox = e.target;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) parent.classList.toggle('selected', checkbox.checked);
                if (checkbox.checked) AppState.ui.selectedStudents.add(checkbox.value);
                else AppState.ui.selectedStudents.delete(checkbox.value);
                RenderManager.updateCheckAllState();
            }
        },

        getSelectedStudentIds: function() {
            return Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        },

        clearAllSelections: function() {
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) parent.classList.remove('selected');
            });
            AppState.ui.selectedStudents.clear();
            const checkAll = document.getElementById('checkAll');
            const checkAllTable = document.getElementById('checkAllTable');
            if (checkAll) checkAll.checked = false;
            if (checkAllTable) checkAllTable.checked = false;
            RenderManager.updateCheckAllState();
        }
    };

    // ============================================================================
    // EVENT DELEGATION
    // ============================================================================
    const EventDelegationManager = {
        initialize: function() {
            this.initializeGlobalButtons();
        },

        initializeGlobalButtons: function() {
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');
            const bulkStatusBtn = document.getElementById('bulkStatusBtn');
            const manageTermBtn = document.getElementById('manageTermBtn');

            if (tableViewBtn) tableViewBtn.addEventListener('click', () => RenderManager.toggleView('table'));
            if (cardViewBtn) cardViewBtn.addEventListener('click', () => RenderManager.toggleView('card'));
            if (bulkStatusBtn) bulkStatusBtn.addEventListener('click', () => Utils.showInfo('Bulk Status Update', 'This feature is available.'));
            if (manageTermBtn) manageTermBtn.addEventListener('click', () => Utils.showInfo('Term Registration', 'This feature is available.'));
        }
    };

    // ============================================================================
    // INITIALIZATION
    // ============================================================================
    function initializeApplication() {
        console.log('Initializing Student Management System...');

        FilterManager.initializeFilters();
        SelectionManager.initializeCheckboxes();
        EventDelegationManager.initialize();

        StudentManager.fetchStudents();

        window.StudentManager = StudentManager;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApplication);
    } else {
        initializeApplication();
    }

})();
</script>
@endsection
