{{-- resources/views/promotions/index.blade.php --}}
@extends('layouts.master')

@section('content')
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
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}

.pay-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}

.pay-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

.stat-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--pay-shadow);
}

.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--pay-primary);
}

.stat-card .stat-label {
    font-size: 12px;
    color: var(--pay-muted);
    margin-top: 4px;
}

.stat-card .stat-icon {
    font-size: 32px;
    opacity: .12;
    float: right;
    margin-top: -8px;
}

.info-banner {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-banner i {
    font-size: 20px;
    color: #2563eb;
}

.info-banner .text {
    font-size: 13px;
    color: #1e40af;
}

.info-banner .text strong {
    display: block;
    margin-bottom: 4px;
}

.info-banner .text a {
    color: #1e40af;
    font-weight: 600;
    text-decoration: underline;
}

.promotion-badge-promoted {
    background: #10b981;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.promotion-badge-trial {
    background: #f59e0b;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.promotion-badge-see_principal {
    background: #3b82f6;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.promotion-badge-repeated {
    background: #ef4444;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.promotion-badge-pending {
    background: #6b7280;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.bulk-action-bar {
    display: none;
    align-items: center;
    gap: 12px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 16px;
}

.bulk-action-bar.visible { display: flex; }
.bulk-action-bar .bulk-count { font-size: 13px; font-weight: 600; color: #92400e; }

.select-all-checkbox { width: 16px; height: 16px; cursor: pointer; }

.modal-content {
    border-radius: 16px;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px;
    border-bottom: none;
}

.modal-header .modal-title {
    color: #fff;
    font-weight: 700;
}

.modal-header .btn-close {
    filter: invert(1);
    background: transparent;
    opacity: .8;
}

.form-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.form-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--pay-primary);
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--pay-border);
}

.recommendation-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
}

.recommendation-card.promoted { border-left: 4px solid #10b981; }
.recommendation-card.trial { border-left: 4px solid #f59e0b; }
.recommendation-card.see_principal { border-left: 4px solid #3b82f6; }
.recommendation-card.repeated { border-left: 4px solid #ef4444; }

.recommendation-card .label { font-size: 12px; color: var(--pay-muted); margin-bottom: 4px; }
.recommendation-card .value { font-size: 16px; font-weight: 700; }

.form-check-card .form-check-input { display: none; }

.promotion-card, .trial-card, .principal-card, .repeat-card {
    transition: all 0.3s ease;
    background-color: #fff;
}

.promotion-card:hover { border-color: #198754 !important; box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.1); }
.trial-card:hover { border-color: #ffc107 !important; box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.1); }
.principal-card:hover { border-color: #0dcaf0 !important; box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.1); }
.repeat-card:hover { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.1); }

#promotionCheckbox:checked ~ label .promotion-card { border-color: #198754 !important; background-color: #d1e7dd !important; }
#trialCheckbox:checked ~ label .trial-card { border-color: #ffc107 !important; background-color: #fff3cd !important; }
#seePrincipalCheckbox:checked ~ label .principal-card { border-color: #0dcaf0 !important; background-color: #cff4fc !important; }
#repeatCheckbox:checked ~ label .repeat-card { border-color: #dc3545 !important; background-color: #f8d7da !important; }

.animate-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.avatar-sm { height: 3rem; width: 3rem; }
.avatar-title {
    align-items: center;
    display: flex;
    height: 100%;
    justify-content: center;
    width: 100%;
}

.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }

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

.btn-subtle-primary {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.btn-subtle-primary:hover {
    background: #dbeafe;
    color: #1d4ed8;
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

.compulsory-table {
    width: 100%;
    border-collapse: collapse;
}

.compulsory-table th {
    background: var(--pay-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    text-align: left;
}

.compulsory-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--pay-border);
    font-size: 13px;
}

.compulsory-table tr:hover td {
    background: #f0f9ff;
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

/* Modal Enhancements */
.student-avatar-lg {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    background: #f8f9fa;
}

.status-badge-lg {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
}

.rule-badge {
    background: #1e3a5f;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.subject-pass { background-color: #d1fae5; }
.subject-fail { background-color: #fee2e2; }
.subject-not-sat { background-color: #fed7aa; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Hero Section -->
            <div class="pay-hero">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="ri-user-star-line me-2"></i>Student Promotion Management</h1>
                        <p>Manage student promotion, repetition, and class assignments based on academic performance.</p>
                    </div>
                    <div>
                        <a href="{{ route('promotion.settings.index') }}" class="btn btn-light">
                            <i class="ri-settings-4-line me-1"></i>Promotion Settings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-user-line"></i></div>
                        <div class="stat-value" id="totalStudents">0</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-arrow-up-circle-line"></i></div>
                        <div class="stat-value text-success" id="promotedCount">0</div>
                        <div class="stat-label">Promoted</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-time-line"></i></div>
                        <div class="stat-value text-warning" id="trialCount">0</div>
                        <div class="stat-label">On Trial</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ri-repeat-line"></i></div>
                        <div class="stat-value text-danger" id="repeatCount">0</div>
                        <div class="stat-label">To Repeat</div>
                    </div>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="info-banner">
                <i class="ri-information-line"></i>
                <div class="text">
                    <strong>Promotion Rules</strong>
                    Promotion decisions are based on compulsory subject performance and overall averages.
                    Configure rules in <a href="{{ route('promotion.settings.index') }}">Promotion Settings</a>.
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Select Class</label>
                            <select class="form-select" id="idclass" name="schoolclassid">
                                <option value="ALL">-- Select Class --</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Select Session</label>
                            <select class="form-select" id="idsession" name="sessionid">
                                <option value="ALL">-- Select Session --</option>
                                @foreach ($schoolsessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->session }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Select Term</label>
                            <select class="form-select" id="idterm" name="termid">
                                <option value="3">Third Term (Promotional)</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->term }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search Student</label>
                            <div class="search-box">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search by name or admission number...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="padding: 16px 20px">
                    <h5 class="mb-0 fw-semibold" style="color: var(--pay-primary)">
                        <i class="ri-group-line me-2"></i>Students
                        <span class="badge bg-primary ms-2" id="studentcount">{{ $allstudents->total() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Bulk Action Bar -->
                    <div class="bulk-action-bar" id="bulkActionBar">
                        <span class="bulk-count" id="bulkCount">0 selected</span>
                        <button type="button" class="btn btn-primary btn-sm" id="bulkPromoteActionBtn">
                            <i class="ri-group-line me-1"></i>Bulk Promote Selected
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="clearSelectionBtn">
                            <i class="ri-close-line me-1"></i>Clear
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="compulsory-table">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="select-all-checkbox" id="selectAll">
                                    </th>
                                    <th>Admission No</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Arm</th>
                                    <th>Session</th>
                                    <th>Overall Avg</th>
                                    <th>Recommendation</th>
                                    <th width="90">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                @include('promotions.partials.student_rows')
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                        {{ $allstudents->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Promotion Modal -->
<div class="modal fade" id="promotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white"><i class="ri-user-star-line me-2"></i>Student Promotion Management</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="promotionForm">
                @csrf
                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">

                    <!-- Student Profile Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img id="modalStudentImage"
                                         src="{{ asset('storage/student_avatars/unnamed.jpg') }}"
                                         alt="Student Picture"
                                         class="student-avatar-lg rounded-circle">
                                    <div class="mt-2">
                                        <span class="badge bg-primary" id="modalStudentGender"></span>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h4 class="mb-2 text-primary" id="modalStudentName"></h4>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="ri-book-2-line text-primary fs-4 me-3"></i>
                                                <div>
                                                    <small class="text-muted d-block">Current Class</small>
                                                    <strong id="modalCurrentClass" class="fs-5"></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="ri-team-line text-primary fs-4 me-3"></i>
                                                <div>
                                                    <small class="text-muted d-block">Arm</small>
                                                    <strong id="modalCurrentArm" class="fs-5"></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="ri-calendar-line text-primary fs-4 me-3"></i>
                                                <div>
                                                    <small class="text-muted d-block">Session</small>
                                                    <strong id="modalCurrentSession" class="fs-5"></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="ri-percent-line text-primary fs-4 me-3"></i>
                                                <div>
                                                    <small class="text-muted d-block">Overall Average</small>
                                                    <strong id="modalOverallAverage" class="fs-5"></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Recommendation Card -->
                    <div class="card border-0 shadow-sm mb-4" id="recommendationCard" style="display: none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ri-robot-line fs-4 text-primary"></i>
                                <h6 class="mb-0 fw-bold text-primary">System Recommendation</h6>
                            </div>
                            <div id="recommendationContent"></div>
                        </div>
                    </div>

                    <!-- Compulsory Subjects Card -->
                    <div class="card border-0 shadow-sm mb-4" id="compulsoryCard" style="display: none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ri-book-open-line fs-4 text-warning"></i>
                                <h6 class="mb-0 fw-bold">Compulsory Subjects Performance</h6>
                            </div>
                            <div id="compulsoryContent"></div>
                        </div>
                    </div>

                    <!-- Promotion Arrow -->
                    <div class="text-center my-4">
                        <i class="ri-arrow-down-line text-primary fs-1 animate-bounce"></i>
                    </div>

                    <!-- New Assignment Card -->
                    <div class="card border-2 border-primary shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="ri-refresh-line me-2"></i>New Assignment
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">New Class <span class="text-danger">*</span></label>
                                    <select class="form-select" name="new_schoolclassid" id="newClassSelect" required>
                                        <option value="">-- Select Class --</option>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">New Session <span class="text-danger">*</span></label>
                                    <select class="form-select" name="new_sessionid" id="newSessionSelect" required>
                                        <option value="">-- Select Session --</option>
                                        @foreach ($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">New Term <span class="text-danger">*</span></label>
                                    <select class="form-select" name="new_termid" id="newTermSelect" required>
                                        <option value="">-- Select Term --</option>
                                        <option value="1">First Term</option>
                                        <option value="2">Second Term</option>
                                        <option value="3">Third Term</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Promotion Decision Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <i class="ri-checkbox-circle-line me-2 text-primary"></i>Promotion Decision <span class="text-danger">*</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input" type="checkbox" name="promotion" id="promotionCheckbox">
                                        <label class="form-check-label w-100" for="promotionCheckbox">
                                            <div class="d-flex align-items-center p-3 border rounded cursor-pointer promotion-card">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title bg-success-subtle text-success rounded-circle fs-2">
                                                            <i class="ri-arrow-up-circle-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">Promote</h6>
                                                    <p class="text-muted mb-0 small">Move to next class level</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input" type="checkbox" name="trial" id="trialCheckbox">
                                        <label class="form-check-label w-100" for="trialCheckbox">
                                            <div class="d-flex align-items-center p-3 border rounded cursor-pointer trial-card">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-2">
                                                            <i class="ri-time-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">Promote on Trial</h6>
                                                    <p class="text-muted mb-0 small">Conditional promotion</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input" type="checkbox" name="see_principal" id="seePrincipalCheckbox">
                                        <label class="form-check-label w-100" for="seePrincipalCheckbox">
                                            <div class="d-flex align-items-center p-3 border rounded cursor-pointer principal-card">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title bg-info-subtle text-info rounded-circle fs-2">
                                                            <i class="ri-eye-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">See Principal</h6>
                                                    <p class="text-muted mb-0 small">Principal review needed</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input" type="checkbox" name="repeat" id="repeatCheckbox">
                                        <label class="form-check-label w-100" for="repeatCheckbox">
                                            <div class="d-flex align-items-center p-3 border rounded cursor-pointer repeat-card">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-2">
                                                            <i class="ri-repeat-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">Repeat Class</h6>
                                                    <p class="text-muted mb-0 small">Student repeats current level</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitPromotion()">
                        <i class="ri-save-line me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Promotion Modal -->
<div class="modal fade" id="bulkPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white"><i class="ri-group-line me-2"></i>Bulk Promotion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You have selected <strong id="bulkSelectedCount">0</strong> students.</p>
                <div class="mb-3">
                    <label class="form-label">Promotion Type</label>
                    <select class="form-select" id="bulkPromotionType">
                        <option value="promoted">Promote Students</option>
                        <option value="trial">Promote on Trial</option>
                        <option value="see_principal">Advised to See Principal</option>
                        <option value="repeat">Advice to Repeat</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Class</label>
                    <select class="form-select" id="bulkNewClass">
                        <option value="">-- Select Class --</option>
                        @foreach ($schoolclasses as $class)
                            <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Session</label>
                    <select class="form-select" id="bulkNewSession">
                        <option value="">-- Select Session --</option>
                        @foreach ($schoolsessions as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Term</label>
                    <select class="form-select" id="bulkNewTerm">
                        <option value="1">First Term</option>
                        <option value="2">Second Term</option>
                        <option value="3">Third Term</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkPromoteBtn">Process Bulk Promotion</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentStudentId = null;
    let currentSchoolclassId = null;
    let currentSessionId = null;
    let currentTermId = null;

    function filterData() {
        const classValue = document.getElementById("idclass").value;
        const sessionValue = document.getElementById("idsession").value;
        const termValue = document.getElementById("idterm").value;
        const searchValue = document.getElementById("searchInput").value.trim();

        if (classValue === 'ALL' || sessionValue === 'ALL') {
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="9" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
            updateStats([]);
            return;
        }

        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading...</td></tr>';

        axios.get('{{ route("promotions.index") }}', {
            params: {
                search: searchValue,
                schoolclassid: classValue,
                sessionid: sessionValue,
                termid: termValue
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody;
            document.getElementById('pagination-container').innerHTML = response.data.pagination;
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.data.tableBody;
            const rows = tempDiv.querySelectorAll('tr');
            const students = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 8) {
                    const recommendationCell = cells[7];
                    const recommendationText = recommendationCell?.innerText?.trim() || 'pending';
                    students.push({ recommendation: recommendationText });
                }
            });
            updateStats(students);

            setupPaginationLinks();
            setupCheckboxHandlers();
        }).catch(function (error) {
            console.error('AJAX Error:', error);
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({ icon: "error", title: "Error", text: error.response?.data?.message || "Failed to fetch student data." });
        });
    }

    function updateStats(students) {
        const total = students.length;
        let promoted = 0, trial = 0, repeat = 0;

        students.forEach(s => {
            const text = s.recommendation.toLowerCase();
            if (text.includes('promoted') && !text.includes('trial')) promoted++;
            else if (text.includes('trial')) trial++;
            else if (text.includes('repeat')) repeat++;
        });

        document.getElementById('totalStudents').innerText = total;
        document.getElementById('promotedCount').innerText = promoted;
        document.getElementById('trialCount').innerText = trial;
        document.getElementById('repeatCount').innerText = repeat;
    }

    function setupPaginationLinks() {
        const paginationLinks = document.querySelectorAll('#pagination-container a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                url.searchParams.set('schoolclassid', document.getElementById("idclass").value);
                url.searchParams.set('sessionid', document.getElementById("idsession").value);
                url.searchParams.set('termid', document.getElementById("idterm").value);
                loadPage(url.toString());
            });
        });
    }

    function loadPage(url) {
        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading...</tr></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody;
            document.getElementById('pagination-container').innerHTML = response.data.pagination;
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.data.tableBody;
            const rows = tempDiv.querySelectorAll('tr');
            const students = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 8) {
                    students.push({ recommendation: cells[7]?.innerText?.trim() || 'pending' });
                }
            });
            updateStats(students);

            setupPaginationLinks();
            setupCheckboxHandlers();
        }).catch(function (error) {
            console.error('Page load error:', error);
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        });
    }

    function setupCheckboxHandlers() {
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
                updateBulkBar();
            });
        }

        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });
    }

    function updateBulkBar() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkBar = document.getElementById('bulkActionBar');

        if (selectedCount > 0) {
            bulkBar.classList.add('visible');
            document.getElementById('bulkCount').innerText = selectedCount + ' selected';
        } else {
            bulkBar.classList.remove('visible');
        }
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        if (document.getElementById('selectAll')) {
            document.getElementById('selectAll').checked = false;
        }
        updateBulkBar();
    }

    document.getElementById('clearSelectionBtn')?.addEventListener('click', clearSelection);
    document.getElementById('bulkPromoteActionBtn')?.addEventListener('click', () => {
        const selected = document.querySelectorAll('.row-checkbox:checked');
        document.getElementById('bulkSelectedCount').innerText = selected.length;
        new bootstrap.Modal(document.getElementById('bulkPromotionModal')).show();
    });

    document.getElementById('confirmBulkPromoteBtn')?.addEventListener('click', async () => {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;

        const promotionType = document.getElementById('bulkPromotionType').value;
        const newClass = document.getElementById('bulkNewClass').value;
        const newSession = document.getElementById('bulkNewSession').value;
        const newTerm = document.getElementById('bulkNewTerm').value;

        if (!newClass || !newSession) {
            Swal.fire('Error', 'Please select new class and session', 'error');
            return;
        }

        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await axios.post('{{ route("promotions.bulk.promote") }}', {
                student_ids: selectedIds,
                new_schoolclassid: newClass,
                new_sessionid: newSession,
                new_termid: newTerm,
                promotion_type: promotionType,
                _token: document.querySelector('meta[name="csrf-token"]').content
            });

            if (response.data.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: response.data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Bulk promotion failed', 'error');
        }
    });

    async function openPromotionModal(studentId, admissionNo, firstName, lastName, otherName, picture, gender, schoolclass, schoolarm, session, termid) {
        currentStudentId = studentId;
        currentSchoolclassId = document.getElementById("idclass").value;
        currentSessionId = document.getElementById("idsession").value;
        currentTermId = termid || document.getElementById("idterm").value;

        document.getElementById('modalStudentName').innerHTML = `<i class="ri-id-card-line me-2"></i>${admissionNo} - ${firstName} ${lastName} ${otherName || ''}`;
        document.getElementById('modalStudentGender').innerHTML = `<i class="ri-gender-${gender === 'Male' ? 'male' : 'female'}-line me-1"></i>${gender || 'N/A'}`;
        document.getElementById('modalCurrentClass').innerText = schoolclass;
        document.getElementById('modalCurrentArm').innerText = schoolarm || 'N/A';
        document.getElementById('modalCurrentSession').innerText = session;

        const imgElement = document.getElementById('modalStudentImage');
        if (picture && picture !== 'null' && picture !== '') {
            imgElement.src = `/storage/${picture}`;
        } else {
            imgElement.src = gender === 'Male' ? '{{ asset("storage/student_avatars/male-default.png") }}' : '{{ asset("storage/student_avatars/female-default.png") }}';
        }
        imgElement.onerror = function() {
            this.src = '{{ asset("storage/student_avatars/unnamed.jpg") }}';
        };

        document.getElementById('promotionForm').reset();
        ['newClassSelect', 'newSessionSelect', 'newTermSelect'].forEach(id => {
            document.getElementById(id).value = '';
        });
        ['promotionCheckbox', 'trialCheckbox', 'seePrincipalCheckbox', 'repeatCheckbox'].forEach(id => {
            document.getElementById(id).checked = false;
        });
        document.getElementById('recommendationCard').style.display = 'none';
        document.getElementById('compulsoryCard').style.display = 'none';

        Swal.fire({ title: 'Loading student data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await axios.get(`/promotions/student-details/${studentId}/${currentSchoolclassId}/${currentSessionId}/${currentTermId}`);
            Swal.close();

            if (response.data.success) {
                const result = response.data.promotion_result;
                const avg = response.data.overall_average;

                const avgElement = document.getElementById('modalOverallAverage');
                const avgValue = avg !== null ? `${avg}%` : 'N/A';
                const avgColor = avg !== null ? (avg >= 50 ? 'text-success' : (avg >= 40 ? 'text-warning' : 'text-danger')) : 'text-muted';
                avgElement.innerHTML = `<span class="${avgColor} fs-5">${avgValue}</span>`;

                const recCard = document.getElementById('recommendationCard');
                const recContent = document.getElementById('recommendationContent');

                if (result && result.status !== 'awaiting') {
                    recCard.style.display = 'block';
                    const statusClass = result.status;
                    const statusLabel = result.status_label || result.status;

                    let html = `<div class="recommendation-card ${statusClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="label text-muted mb-2">System Recommendation</div>
                                <div class="value">
                                    <span class="status-badge-lg ${result.status === 'promoted' ? 'bg-success' : (result.status === 'trial' ? 'bg-warning' : (result.status === 'see_principal' ? 'bg-info' : 'bg-danger'))} text-white">
                                        <i class="${result.status === 'promoted' ? 'ri-checkbox-circle-line' : (result.status === 'trial' ? 'ri-time-line' : (result.status === 'see_principal' ? 'ri-eye-line' : 'ri-repeat-line'))} me-2"></i>
                                        ${statusLabel}
                                    </span>
                                </div>
                            </div>`;

                    if (result.required_average !== null) {
                        const metAverage = result.actual_average >= result.required_average;
                        html += `<div class="text-end">
                            <div class="rule-badge">
                                <i class="ri-percent-line"></i> Required: ${result.required_average}%
                            </div>
                            <div class="small mt-1 ${metAverage ? 'text-success' : 'text-danger'}">
                                ${metAverage ? '✓' : '✗'} Actual: ${result.actual_average ?? avg ?? 'N/A'}%
                            </div>
                        </div>`;
                    }
                    html += `</div>`;

                    if (result.compulsory_count > 0) {
                        const allPassed = result.passed_compulsory === result.compulsory_count;
                        html += `<div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <span><i class="ri-book-open-line me-1"></i>Compulsory Subjects:</span>
                                <span class="${allPassed ? 'text-success fw-bold' : 'text-danger fw-bold'}">
                                    ${result.passed_compulsory}/${result.compulsory_count} passed
                                </span>
                            </div>`;

                        if (result.failed_compulsory && result.failed_compulsory.length > 0) {
                            html += `<div class="mt-2 small text-danger">
                                <i class="ri-close-circle-line me-1"></i>Failed subjects: `;
                            result.failed_compulsory.forEach(f => {
                                html += `<span class="badge bg-danger me-1">${f.subject || `Subject #${f.subject_id}`}</span>`;
                            });
                            html += `</div>`;
                        }
                        html += `</div>`;
                    }

                    if (result.matched_labels && result.matched_labels.length > 0) {
                        html += `<div class="mt-3 pt-3 border-top">
                            <div class="small text-muted mb-2">
                                <i class="ri-price-tag-3-line me-1"></i>
                                <strong>Applied Rules / Labels</strong>
                            </div>`;
                        result.matched_labels.forEach(ml => {
                            const colorMap = { danger:'#dc2626', success:'#16a34a', warning:'#d97706', info:'#2563eb', primary:'#1e3a5f' };
                            html += `<span class="badge me-2 mb-1" style="background:${colorMap[ml.color] || '#6c757d'}; font-size:12px; padding:5px 12px">
                                <i class="ri-price-tag-3-line me-1"></i>${ml.label}
                            </span>`;
                            if (ml.description) {
                                html += `<div class="small text-muted mt-1 ms-1 mb-2">${ml.description}</div>`;
                            }
                        });
                        html += `</div>`;
                    }

                    html += `</div>`;
                    recContent.innerHTML = html;
                } else {
                    recCard.style.display = 'none';
                }

                const compSubjects = response.data.compulsory_subjects;
                if (compSubjects && compSubjects.length > 0) {
                    const compCard = document.getElementById('compulsoryCard');
                    const compContent = document.getElementById('compulsoryContent');

                    const passCount = compSubjects.filter(s => s.pass_status === 'pass').length;
                    const failCount = compSubjects.filter(s => s.pass_status === 'fail').length;
                    const notSatCount = compSubjects.filter(s => s.pass_status === 'not_sat').length;

                    let html = `<div class="d-flex gap-2 mb-3 flex-wrap">
                        <span class="badge bg-success" style="font-size:13px; padding:6px 12px">
                            <i class="ri-checkbox-circle-line me-1"></i>${passCount} Passed
                        </span>
                        <span class="badge bg-danger" style="font-size:13px; padding:6px 12px">
                            <i class="ri-close-circle-line me-1"></i>${failCount} Failed
                        </span>`;
                    if (notSatCount > 0) {
                        html += `<span class="badge bg-secondary" style="font-size:13px; padding:6px 12px">
                            <i class="ri-minus-line me-1"></i>${notSatCount} Not Sat
                        </span>`;
                    }
                    html += `</div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-center">Min Grade Required</th>
                                    <th class="text-center">Grade Obtained</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    compSubjects.forEach(cs => {
                        let statusBadge, rowClass;
                        if (cs.pass_status === 'pass') {
                            statusBadge = `<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Pass</span>`;
                            rowClass = 'subject-pass';
                        } else if (cs.pass_status === 'fail') {
                            statusBadge = `<span class="badge bg-danger"><i class="ri-close-circle-line me-1"></i>Fail</span>`;
                            rowClass = 'subject-fail';
                        } else {
                            statusBadge = `<span class="badge bg-secondary"><i class="ri-minus-line me-1"></i>Not Sat</span>`;
                            rowClass = 'subject-not-sat';
                        }

                        html += `<tr class="${rowClass}">
                            <td><strong>${cs.subject}</strong><br><small class="text-muted">${cs.subject_code || ''}</small></td>
                            <td class="text-center">${cs.min_grade}</td>
                            <td class="text-center"><strong>${cs.student_grade || '—'}</strong></td>
                            <td class="text-center">${cs.student_total !== null ? cs.student_total : '—'}</td>
                            <td class="text-center">${statusBadge}</td>
                        </tr>`;
                    });

                    html += `</tbody>
                        </table>
                    </div>`;
                    compContent.innerHTML = html;
                    compCard.style.display = 'block';
                } else {
                    document.getElementById('compulsoryCard').style.display = 'none';
                }
            }
        } catch (error) {
            Swal.close();
            console.error('Error fetching student details:', error);
            Swal.fire('Error', 'Failed to load student details', 'error');
        }

        new bootstrap.Modal(document.getElementById('promotionModal')).show();
    }

    function removeStudent(studentId, schoolclassId, sessionId, termId, admissionNo, firstName, lastName) {
        const fullName = `${admissionNo} - ${firstName} ${lastName}`;

        Swal.fire({
            title: 'Confirm Removal',
            text: `Remove ${fullName} from this class?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Remove',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const deleteUrl = `/promotions/${studentId}`;
                const deleteData = new FormData();
                deleteData.append('_method', 'DELETE');
                deleteData.append('schoolclassid', schoolclassId);
                deleteData.append('sessionid', sessionId);
                deleteData.append('termid', termId);

                axios.post(deleteUrl, deleteData, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(function (response) {
                    if (response.data.success) {
                        Swal.fire({ icon: 'success', title: 'Removed!', text: response.data.message, timer: 2000, showConfirmButton: false });
                        filterData();
                    } else {
                        Swal.fire('Error!', response.data.message || 'Failed to remove.', 'error');
                    }
                }).catch(function (error) {
                    Swal.fire('Error!', error.response?.data?.message || 'Failed to remove student.', 'error');
                });
            }
        });
    }

    function submitPromotion() {
        if (!currentStudentId) {
            Swal.fire('Error!', 'Student ID not found.', 'error');
            return;
        }

        const newClassSelect = document.getElementById('newClassSelect');
        const newSessionSelect = document.getElementById('newSessionSelect');
        const newTermSelect = document.getElementById('newTermSelect');
        const promotionCheckbox = document.getElementById('promotionCheckbox');
        const trialCheckbox = document.getElementById('trialCheckbox');
        const seePrincipalCheckbox = document.getElementById('seePrincipalCheckbox');
        const repeatCheckbox = document.getElementById('repeatCheckbox');

        if (!newClassSelect.value) { Swal.fire('Error!', 'Please select a new class.', 'error'); return; }
        if (!newSessionSelect.value) { Swal.fire('Error!', 'Please select a new session.', 'error'); return; }
        if (!newTermSelect.value) { Swal.fire('Error!', 'Please select a new term.', 'error'); return; }

        const selectedCount = [promotionCheckbox, trialCheckbox, seePrincipalCheckbox, repeatCheckbox].filter(cb => cb.checked).length;
        if (selectedCount !== 1) {
            Swal.fire('Error!', 'Please select exactly one promotion decision.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('new_schoolclassid', newClassSelect.value);
        formData.append('new_sessionid', newSessionSelect.value);
        formData.append('new_termid', newTermSelect.value);
        formData.append('promotion', promotionCheckbox.checked ? '1' : '0');
        formData.append('trial', trialCheckbox.checked ? '1' : '0');
        formData.append('see_principal', seePrincipalCheckbox.checked ? '1' : '0');
        formData.append('repeat', repeatCheckbox.checked ? '1' : '0');

        const updateUrl = `/promotions/${currentStudentId}`;

        Swal.fire({
            title: 'Confirm Update',
            text: 'Update this student\'s promotion?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                axios.post(updateUrl, formData, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(function (response) {
                    if (response.data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('promotionModal')).hide();
                        Swal.fire({ icon: 'success', title: 'Success!', text: response.data.message, timer: 2000, showConfirmButton: false });
                        filterData();
                    } else {
                        Swal.fire('Error!', response.data.message || 'Failed to update.', 'error');
                    }
                }).catch(function (error) {
                    Swal.fire('Error!', error.response?.data?.message || 'Failed to update promotion.', 'error');
                });
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("idclass").addEventListener("change", filterData);
        document.getElementById("idsession").addEventListener("change", filterData);
        document.getElementById("idterm").addEventListener("change", filterData);

        let searchTimeout;
        document.getElementById("searchInput").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterData, 500);
        });

        setupCheckboxHandlers();

        const rows = document.querySelectorAll('#studentTableBody tr');
        const students = [];
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 8) {
                students.push({ recommendation: cells[7]?.innerText?.trim() || 'pending' });
            }
        });
        updateStats(students);
    });
</script>
@endsection
