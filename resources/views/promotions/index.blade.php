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

.promotion-badge-promoted { background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.promotion-badge-trial { background: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.promotion-badge-see_principal { background: #3b82f6; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.promotion-badge-repeated { background: #ef4444; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.promotion-badge-pending { background: #6b7280; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }

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
                        <div class="stat-icon"><i class="ri-repeat-line"></i></div>
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
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="bulkPromoteBtn" style="display: none;">
                            <i class="ri-group-line me-1"></i>Bulk Promote
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Bulk Action Bar -->
                    <div class="bulk-action-bar" id="bulkActionBar">
                        <span class="bulk-count" id="bulkCount">0 selected</span>
                        <button type="button" class="btn btn-primary btn-sm" id="bulkPromoteActionBtn">
                            <i class="ri-arrow-up-line me-1"></i>Promote Selected
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
                                    <th width="100">Actions</th>
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
                <h5 class="modal-title"><i class="ri-user-star-line me-2"></i>Student Promotion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="promotionForm">
                @csrf
                <div class="modal-body p-4">
                    <!-- Student Information Card -->
                    <div class="card border shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="ri-user-line me-2"></i>Student Information
                            </h6>
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img id="modalStudentImage"
                                         src=""
                                         alt="Student Picture"
                                         class="img-fluid rounded-circle shadow"
                                         style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #f0f0f0;">
                                </div>
                                <div class="col-md-9">
                                    <h5 class="mb-3 text-primary" id="modalStudentName"></h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                <i class="ri-book-2-line text-primary fs-5 me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Class</small>
                                                    <strong id="modalCurrentClass"></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                <i class="ri-team-line text-primary fs-5 me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Arm</small>
                                                    <strong id="modalCurrentArm"></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                <i class="ri-calendar-line text-primary fs-5 me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Session</small>
                                                    <strong id="modalCurrentSession"></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                <i class="ri-percent-line text-primary fs-5 me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Average</small>
                                                    <strong id="modalOverallAverage"></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Recommendation Card -->
                    <div class="card border shadow-sm mb-4" id="recommendationCard" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="ri-question-line me-2"></i>System Recommendation
                            </h6>
                            <div id="recommendationContent"></div>
                        </div>
                    </div>

                    <!-- Compulsory Subjects Card -->
                    <div class="card border shadow-sm mb-4" id="compulsoryCard" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="ri-book-open-line me-2"></i>Compulsory Subjects Performance
                            </h6>
                            <div id="compulsoryContent"></div>
                        </div>
                    </div>

                    <!-- Promotion Arrow -->
                    <div class="text-center mb-4">
                        <i class="ri-arrow-down-line text-primary fs-1 animate-bounce"></i>
                    </div>

                    <!-- New Assignment Card -->
                    <div class="card border-primary shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3">
                                <i class="ri-refresh-line me-2"></i>New Assignment
                            </h6>
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

                    <!-- Promotion Decision -->
                    <div class="card border-0 bg-light mt-4">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="ri-checkbox-circle-line me-2"></i>Promotion Decision <span class="text-danger">*</span>
                            </h6>
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
                <h5 class="modal-title"><i class="ri-group-line me-2"></i>Bulk Promotion</h5>
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

    // Filter data function
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

            // Extract student data from response for stats
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.data.tableBody;
            const rows = tempDiv.querySelectorAll('tr');
            const students = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 8) {
                    students.push({
                        recommendation: cells[7]?.innerText?.trim() || 'pending'
                    });
                }
            });
            updateStats(students);

            setupPaginationLinks();
            setupCheckboxHandlers();
        }).catch(function (error) {
            console.error('AJAX Error:', error);
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        });
    }

    function updateStats(students) {
        const total = students.length;
        const promoted = students.filter(s => s.recommendation?.toLowerCase().includes('promoted') && !s.recommendation?.toLowerCase().includes('trial')).length;
        const trial = students.filter(s => s.recommendation?.toLowerCase().includes('trial')).length;
        const repeat = students.filter(s => s.recommendation?.toLowerCase().includes('repeat')).length;

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
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading...</td></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody;
            document.getElementById('pagination-container').innerHTML = response.data.pagination;
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            // Extract student data for stats
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.data.tableBody;
            const rows = tempDiv.querySelectorAll('tr');
            const students = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 8) {
                    students.push({
                        recommendation: cells[7]?.innerText?.trim() || 'pending'
                    });
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

    document.getElementById('bulkPromoteBtn')?.addEventListener('click', () => {
        const selected = document.querySelectorAll('.row-checkbox:checked');
        document.getElementById('bulkSelectedCount').innerText = selected.length;
        new bootstrap.Modal(document.getElementById('bulkPromotionModal')).show();
    });

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

    async function openPromotionModal(studentId, admissionNo, firstName, lastName, otherName, picture, schoolclass, schoolarm, session, termid) {
        currentStudentId = studentId;
        currentSchoolclassId = document.getElementById("idclass").value;
        currentSessionId = document.getElementById("idsession").value;
        currentTermId = termid || document.getElementById("idterm").value;

        document.getElementById('modalStudentName').innerHTML = `${admissionNo} - ${firstName} ${lastName} ${otherName || ''}`;
        document.getElementById('modalCurrentClass').innerText = schoolclass;
        document.getElementById('modalCurrentArm').innerText = schoolarm || 'N/A';
        document.getElementById('modalCurrentSession').innerText = session;
        document.getElementById('modalStudentImage').src = picture ? `/storage/${picture}` : '{{ asset("storage/student_avatars/unnamed.jpg") }}';

        document.getElementById('promotionForm').reset();
        document.getElementById('newClassSelect').value = '';
        document.getElementById('newSessionSelect').value = '';
        document.getElementById('newTermSelect').value = '';
        document.getElementById('promotionCheckbox').checked = false;
        document.getElementById('trialCheckbox').checked = false;
        document.getElementById('seePrincipalCheckbox').checked = false;
        document.getElementById('repeatCheckbox').checked = false;

        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await axios.get(`/promotions/student-details/${studentId}/${currentSchoolclassId}/${currentSessionId}/${currentTermId}`);

            if (response.data.success) {
                Swal.close();

                const result = response.data.promotion_result;
                const avg = response.data.overall_average;

                document.getElementById('modalOverallAverage').innerHTML = avg !== null ? `${avg}%` : 'N/A';

                const recCard = document.getElementById('recommendationCard');
                const recContent = document.getElementById('recommendationContent');

                if (result && result.status !== 'awaiting') {
                    recCard.style.display = 'block';
                    const statusClass = result.status;
                    const statusLabel = result.status_label || result.status;

                    let html = `
                        <div class="recommendation-card ${statusClass}">
                            <div class="label">System Recommendation</div>
                            <div class="value">
                                <span class="promotion-badge-${statusClass === 'promoted' ? 'promoted' : (statusClass === 'trial' ? 'trial' : (statusClass === 'see_principal' ? 'see_principal' : 'repeated'))}">
                                    ${statusLabel}
                                </span>
                            </div>
                    `;

                    if (result.required_average !== null) {
                        html += `<div class="mt-2"><small>Required Average: ${result.required_average}% | Actual: ${result.actual_average || avg}%</small></div>`;
                    }

                    if (result.compulsory_count > 0) {
                        html += `<div class="mt-2"><small>Compulsory Subjects: ${result.passed_compulsory}/${result.compulsory_count} passed</small></div>`;
                    }

                    html += `</div>`;
                    recContent.innerHTML = html;
                } else {
                    recCard.style.display = 'none';
                }

                if (response.data.compulsory_subjects && response.data.compulsory_subjects.length > 0) {
                    const compCard = document.getElementById('compulsoryCard');
                    const compContent = document.getElementById('compulsoryContent');
                    let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Subject</th><th>Min Grade</th><th>Status</th></tr></thead><tbody>';

                    response.data.compulsory_subjects.forEach(cs => {
                        html += `<tr><td>${cs.subject?.subject || 'N/A'}</td><td>${cs.min_grade || 'Pass'}</td><td><span class="badge bg-warning">Pending Evaluation</span></td></tr>`;
                    });

                    html += '</tbody></table></div>';
                    compContent.innerHTML = html;
                    compCard.style.display = 'block';
                }
            } else {
                Swal.close();
            }
        } catch (error) {
            Swal.close();
            console.error('Error fetching student details:', error);
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

        // Initial stats update if table has data
        const rows = document.querySelectorAll('#studentTableBody tr');
        const students = [];
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 8) {
                students.push({
                    recommendation: cells[7]?.innerText?.trim() || 'pending'
                });
            }
        });
        updateStats(students);
    });
</script>
@endsection
