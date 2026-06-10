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

    /* Additional semantic colors */
    --color-background-success: #dcfce7;
    --color-background-danger:  #fee2e2;
    --color-background-warning: #fef9c3;
    --color-background-info:    #dbeafe;
    --color-background-secondary: #f1f5f9;

    --color-text-success: #15803d;
    --color-text-danger:  #b91c1c;
    --color-text-warning: #92400e;
    --color-text-info:    #1e40af;
    --color-text-secondary: #475569;
    --color-text-primary:   #1e293b;

    --color-border-success: #86efac;
    --color-border-danger:  #fecaca;
    --color-border-warning: #fed7aa;
    --color-border-info:    #bfdbfe;
    --color-border-tertiary: #cbd5e1;
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
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pay-hero h1 {
    font-size: 22px; font-weight: 700; color: #fff;
    margin: 0 0 6px; position: relative;
}
.pay-hero p {
    font-size: 13px; color: rgba(255,255,255,.75);
    margin: 0; position: relative;
}

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

.info-banner {
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 10px; padding: 12px 16px;
    margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
}
.info-banner i { font-size: 20px; color: #2563eb; }
.info-banner .text { font-size: 13px; color: #1e40af; }
.info-banner .text strong { display: block; margin-bottom: 4px; }
.info-banner .text a { color: #1e40af; font-weight: 600; text-decoration: underline; }

.promotion-badge-promoted {
    background: #10b981; color: white;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}
.promotion-badge-trial {
    background: #f59e0b; color: white;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}
.promotion-badge-see_principal {
    background: #3b82f6; color: white;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}
.promotion-badge-repeated {
    background: #ef4444; color: white;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}
.promotion-badge-pending {
    background: #6b7280; color: white;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}

.bulk-action-bar {
    display: none; align-items: center; gap: 12px;
    background: #fff7ed; border: 1px solid #fed7aa;
    border-radius: 10px; padding: 10px 16px; margin-bottom: 16px;
}
.bulk-action-bar.visible { display: flex; }
.bulk-action-bar .bulk-count { font-size: 13px; font-weight: 600; color: #92400e; }
.select-all-checkbox { width: 16px; height: 16px; cursor: pointer; }

.modal-content { border-radius: 16px; overflow: hidden; }
.modal-header {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 20px 28px; border-bottom: none;
}
.modal-header .modal-title { color: #fff; font-weight: 700; }
.modal-header .btn-close { filter: invert(1); background: transparent; opacity: .8; }

.form-section { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
.form-section-title {
    font-size: 14px; font-weight: 700; color: var(--pay-primary);
    margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid var(--pay-border);
}

.recommendation-card { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
.recommendation-card.promoted     { border-left: 4px solid #10b981; }
.recommendation-card.trial        { border-left: 4px solid #f59e0b; }
.recommendation-card.see_principal{ border-left: 4px solid #3b82f6; }
.recommendation-card.repeated     { border-left: 4px solid #ef4444; }
.recommendation-card .label { font-size: 12px; color: var(--pay-muted); margin-bottom: 4px; }
.recommendation-card .value { font-size: 16px; font-weight: 700; }

.form-check-card .form-check-input { display: none; }
.promotion-card, .trial-card, .principal-card, .repeat-card {
    transition: all 0.3s ease; background-color: #fff;
}
.promotion-card:hover { border-color: #198754 !important; box-shadow: 0 0 0 0.2rem rgba(25,135,84,.1); }
.trial-card:hover     { border-color: #ffc107 !important; box-shadow: 0 0 0 0.2rem rgba(255,193,7,.1); }
.principal-card:hover { border-color: #0dcaf0 !important; box-shadow: 0 0 0 0.2rem rgba(13,202,240,.1); }
.repeat-card:hover    { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220,53,69,.1); }

#promotionCheckbox:checked ~ label .promotion-card,
input[name="promotion"]:checked ~ label .promotion-card { border-color: #198754 !important; background-color: #d1e7dd !important; }
#trialCheckbox:checked ~ label .trial-card,
input[name="trial"]:checked ~ label .trial-card { border-color: #ffc107 !important; background-color: #fff3cd !important; }
#seePrincipalCheckbox:checked ~ label .principal-card,
input[name="see_principal"]:checked ~ label .principal-card { border-color: #0dcaf0 !important; background-color: #cff4fc !important; }
#repeatCheckbox:checked ~ label .repeat-card,
input[name="repeat"]:checked ~ label .repeat-card { border-color: #dc3545 !important; background-color: #f8d7da !important; }

.animate-bounce { animation: bounce 2s infinite; }
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-10px); }
}

.avatar-sm { height: 3rem; width: 3rem; }
.avatar-title {
    align-items: center; display: flex;
    height: 100%; justify-content: center; width: 100%;
}
.bg-success-subtle { background-color: rgba(25,135,84,.1)  !important; }
.bg-warning-subtle { background-color: rgba(255,193,7,.1)  !important; }
.bg-info-subtle    { background-color: rgba(13,202,240,.1) !important; }
.bg-danger-subtle  { background-color: rgba(220,53,69,.1)  !important; }

.btn-icon {
    width: 32px; height: 32px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 8px; transition: all .15s; border: none; cursor: pointer;
}
.btn-subtle-primary { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.btn-subtle-primary:hover { background: #dbeafe; color: #1d4ed8; transform: translateY(-1px); }
.btn-subtle-danger  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.btn-subtle-danger:hover  { background: #fee2e2; color: #b91c1c; transform: translateY(-1px); }

.search-box { position: relative; }
.search-box .form-control {
    border: 1.5px solid var(--pay-border); border-radius: 8px;
    padding: 9px 14px; padding-right: 36px; font-size: 13px; width: 100%;
}
.search-box .form-control:focus {
    border-color: var(--pay-accent); outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.search-box .search-icon {
    position: absolute; right: 12px; top: 50%;
    transform: translateY(-50%); color: var(--pay-muted); pointer-events: none;
}

.compulsory-table { width: 100%; border-collapse: collapse; }
.compulsory-table th {
    background: var(--pay-primary); color: #fff;
    padding: 12px 16px; font-weight: 600; font-size: 13px;
    white-space: nowrap; text-align: left;
}
.compulsory-table td {
    padding: 11px 16px; vertical-align: middle;
    border-bottom: 1px solid var(--pay-border); font-size: 13px;
}
.compulsory-table tr:hover td { background: #f0f9ff; }

.empty-state { text-align: center; padding: 52px 24px; color: var(--pay-muted); }
.empty-state i { font-size: 3rem; opacity: .25; display: block; margin-bottom: 14px; }

.student-avatar-lg {
    width: 120px; height: 120px; object-fit: cover;
    border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,.15); background: #f8f9fa;
}
.status-badge-lg {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 30px; font-size: 14px; font-weight: 600;
}
.rule-badge {
    background: #1e3a5f; color: white;
    padding: 4px 12px; border-radius: 20px;
    font-size: 11px; font-weight: 500;
    display: inline-flex; align-items: center; gap: 4px;
}
.subject-pass td:first-child { border-left: 3px solid #10b981; }
.subject-fail td:first-child { border-left: 3px solid #ef4444; }
.subject-not-sat td:first-child { border-left: 3px solid #f59e0b; }
.table-hover tbody tr:hover { background-color: rgba(0,0,0,.02); }
.rule-link { transition: all 0.2s ease; }
.rule-link:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.badge-compulsory {
    background: #fef3c7; color: #92400e;
    padding: 2px 8px; border-radius: 12px;
    font-size: 9px; font-weight: 600; display: inline-block;
}

/* ── All-Subjects table ──────────────────────────────────────────────────── */
.subjects-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.subjects-table thead th {
    background: var(--pay-primary); color: #fff;
    padding: 10px 14px; font-weight: 600; white-space: nowrap;
    position: sticky; top: 0; z-index: 1;
}
.subjects-table tbody td {
    padding: 10px 14px; vertical-align: middle;
    border-bottom: 1px solid var(--pay-border);
}
.subjects-table tbody tr:hover td { background: #f0f9ff; }
.subjects-table tr.row-pass    td:first-child { border-left: 4px solid #10b981; }
.subjects-table tr.row-fail    td:first-child { border-left: 4px solid #ef4444; }
.subjects-table tr.row-notsat  td:first-child { border-left: 4px solid #f59e0b; }
.subjects-table tr.row-optional td:first-child { border-left: 4px solid #94a3b8; }
.subjects-table tr.row-section td {
    background: #f1f5f9; padding: 6px 14px;
    font-size: 11px; font-weight: 700; color: #475569;
    letter-spacing: .05em; text-transform: uppercase;
    border-bottom: 1px solid var(--pay-border);
}

.score-bar-wrap {
    background: #e2e8f0; border-radius: 4px;
    height: 6px; width: 60px;
    display: inline-block; vertical-align: middle; margin-left: 6px;
}
.score-bar-fill { height: 100%; border-radius: 4px; }

.subject-summary-strip {
    display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
    background: #f8fafc; border: 1px solid var(--pay-border);
    border-radius: 10px; padding: 12px 16px; margin-bottom: 14px;
}
.summary-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.pill-pass     { background: #dcfce7; color: #15803d; }
.pill-fail     { background: #fee2e2; color: #b91c1c; }
.pill-notsat   { background: #fef9c3; color: #92400e; }
.pill-optional { background: #e0f2fe; color: #0369a1; }
.pill-total    { background: #ede9fe; color: #6d28d9; }
.pill-credit   { background: #fce7f3; color: #9d174d; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Hero --}}
            <div class="pay-hero">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="ri-user-star-line me-2"></i>Student Promotion Management</h1>
                        <p>Manage student promotion, repetition, and class assignments based on academic performance.</p>
                    </div>
                    <div>
                        <a href="{{ route('promotion-settings.index') }}" class="btn btn-light">
                            <i class="ri-settings-4-line me-1"></i>Promotion Settings
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
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
                        <div class="stat-label">Recommended Promoted</div>
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

            {{-- Info Banner --}}
            <div class="info-banner">
                <i class="ri-information-line"></i>
                <div class="text">
                    <strong>Promotion Rules</strong>
                    Promotion decisions are based on compulsory subject performance and overall averages.
                    Only <strong>active</strong> rules are applied automatically.
                    Configure rules in <a href="{{ route('promotion-settings.index') }}">Promotion Settings</a>.
                </div>
            </div>

            {{-- Warning Banner for Missing Promotion Settings --}}
            @php
                $selectedClassId = request()->input('schoolclassid');
                $hasPromotionSettings = false;
                if ($selectedClassId && $selectedClassId !== 'ALL') {
                    $hasPromotionSettings = \App\Models\PromotionSetting::where('schoolclass_id', $selectedClassId)
                        ->where('is_active', true)
                        ->exists();
                }
            @endphp

            @if(request()->filled('schoolclassid') && request()->input('schoolclassid') !== 'ALL' && !$hasPromotionSettings)
            <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert" style="border-left: 4px solid #d97706;">
                <div class="d-flex align-items-center">
                    <i class="ri-alert-line fs-4 me-3"></i>
                    <div>
                        <strong class="d-block mb-1">⚠️ No Promotion Rules Configured!</strong>
                        <span>No active promotion settings found for this class. Please
                        <a href="{{ route('promotion-settings.index') }}" class="alert-link fw-bold">configure promotion rules</a>
                        to enable automatic recommendations. Until then, all students will show "Awaiting Decision".</span>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            </div>
            @endif

            {{-- Filters --}}
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
                                <input type="text" class="form-control" id="searchInput"
                                       placeholder="Search by name or admission number...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Students Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap"
                     style="padding:16px 20px">
                    <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                        <i class="ri-group-line me-2"></i>Students
                        <span class="badge bg-primary ms-2" id="studentcount">{{ $allstudents->total() }}</span>
                    </h5>
                </div>
                <div class="card-body">

                    {{-- Bulk Action Bar --}}
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
                                    <th>Promotion Status</th>
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

{{-- ============================================================
     Promotion Modal
     ============================================================ --}}
<div class="modal fade" id="promotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">
                    <i class="ri-user-star-line me-2"></i>Student Promotion Management
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="promotionForm">
                @csrf
                <div class="modal-body p-4" style="max-height:80vh;overflow-y:auto;">

                    {{-- Student Profile --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img id="modalStudentImage"
                                         src="{{ asset('storage/student_avatars/unnamed.jpg') }}"
                                         alt="Student Picture"
                                         class="student-avatar-lg rounded-circle"
                                         style="object-fit: cover; width: 120px; height: 120px;">
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

                    {{-- System Recommendation --}}
                    <div class="card border-0 shadow-sm mb-4" id="recommendationCard" style="display:none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ri-robot-line fs-4 text-primary"></i>
                                <h6 class="mb-0 fw-bold text-primary">System Recommendation</h6>
                            </div>
                            <div id="recommendationContent"></div>
                        </div>
                    </div>

                    {{-- Compulsory Subjects Summary --}}
                    <div class="card border-0 shadow-sm mb-4" id="compulsoryCard" style="display:none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ri-star-fill fs-4 text-warning"></i>
                                <h6 class="mb-0 fw-bold">Compulsory Subjects Performance</h6>
                            </div>
                            <div id="compulsoryContent"></div>
                        </div>
                    </div>

                    {{-- All Subjects Table --}}
                    <div class="card border-0 shadow-sm mb-4" id="allSubjectsCard" style="display:none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ri-book-open-line fs-4 text-primary"></i>
                                <h6 class="mb-0 fw-bold">All Subjects Performance</h6>
                            </div>
                            <div id="allSubjectsContent"></div>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <div class="text-center my-4">
                        <i class="ri-arrow-down-line text-primary fs-1 animate-bounce"></i>
                    </div>

                    {{-- New Assignment --}}
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

                    {{-- Promotion Decision --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <i class="ri-checkbox-circle-line me-2 text-primary"></i>Promotion Decision
                            <span class="text-danger">*</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input" type="checkbox" name="promotion" id="promotionCheckbox" value="promoted">
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
                                        <input class="form-check-input" type="checkbox" name="trial" id="trialCheckbox" value="trial">
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
                                        <input class="form-check-input" type="checkbox" name="see_principal" id="seePrincipalCheckbox" value="see_principal">
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
                                        <input class="form-check-input" type="checkbox" name="repeat" id="repeatCheckbox" value="repeat">
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

{{-- ============================================================
     Bulk Promotion Modal
     ============================================================ --}}
<div class="modal fade" id="bulkPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">
                    <i class="ri-group-line me-2"></i>Bulk Promotion
                </h5>
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
                <button type="button" class="btn btn-primary" id="confirmBulkPromoteBtn">
                    Process Bulk Promotion
                </button>
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
let currentStudentData = null;

// Grade order mapping for comparisons
const gradeOrder = {
    'A1': 8, 'A': 4,
    'B2': 7, 'B3': 6, 'B': 3,
    'C4': 5, 'C5': 4, 'C6': 3, 'C': 2,
    'D7': 2, 'D': 1,
    'E8': 1,
    'F9': 0, 'F': 0,
};

// Helper function to normalize image path
function normalizeImagePath(picture, gender) {
    if (!picture || picture === 'null' || picture === 'undefined' || picture === '') {
        return gender === 'Male'
            ? '/storage/student_avatars/male-default.png'
            : '/storage/student_avatars/female-default.png';
    }

    // Remove any leading slashes and 'storage/' prefixes to normalize
    let cleanPath = picture.replace(/^\/+/, '').replace(/^storage\//, '');

    // If it's already a full URL, return as is
    if (cleanPath.startsWith('http://') || cleanPath.startsWith('https://')) {
        return cleanPath;
    }

    // Ensure proper storage path
    return '/storage/' + cleanPath;
}

// Helper function to format rule description with proper grammar
function formatRuleDescription(description) {
    if (!description) return '';

    // Fix common grammar issues
    let formatted = description
        .replace(/subj\b/g, 'subjects')
        .replace(/subj\.\b/g, 'subjects')
        .replace(/(\d+)\s+C\b/g, (match, num) => {
            return `${num} ${num == 1 ? 'C' : 'Cs'}`;
        })
        .replace(/;\s*>=/g, '; ≥')
        .replace(/;\s*(\d+)\s+(\w+)/g, (match, num, word) => {
            return `; ${num} ${word}${num == 1 ? '' : 's'}`;
        });

    // Ensure proper capitalization of first letter
    if (formatted.length > 0) {
        formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1);
    }

    return formatted;
}

// ── Filter & load ──────────────────────────────────────────────────────────────
function filterData() {
    const classValue = document.getElementById("idclass").value;
    const sessionValue = document.getElementById("idsession").value;
    const termValue = document.getElementById("idterm").value;
    const searchValue = document.getElementById("searchInput").value.trim();

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="10" class="text-center">Select class and session to view students.</td></tr>';
        document.getElementById('pagination-container').innerHTML = '';
        document.getElementById('studentcount').innerText = '0';
        updateStats();
        return;
    }

    const tableBody = document.getElementById('studentTableBody');
    tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...<\/td><\/tr>';

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
    }).then(function(response) {
        document.getElementById('studentTableBody').innerHTML = response.data.tableBody;
        document.getElementById('pagination-container').innerHTML = response.data.pagination;
        document.getElementById('studentcount').innerText = response.data.studentCount || '0';
        updateStats();
        setupPaginationLinks();
        setupCheckboxHandlers();
    }).catch(function(error) {
        console.error('AJAX Error:', error);
        tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data. Please try again.<\/td><\/tr>';
        Swal.fire({ icon: "error", title: "Error", text: error.response?.data?.message || "Failed to fetch student data." });
    });
}

function updateStats() {
    const rows = document.querySelectorAll('#studentTableBody tr');
    let total = 0, promoted = 0, trial = 0, repeat = 0;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 8) return;
        total++;
        const recCell = cells[7];
        if (recCell) {
            const recStatus = recCell.getAttribute('data-rec-status') || '';
            if (recStatus === 'promoted') promoted++;
            else if (recStatus === 'trial') trial++;
            else if (recStatus === 'repeated' || recStatus === 'repeat') repeat++;
        }
    });

    document.getElementById('totalStudents').innerText = total;
    document.getElementById('promotedCount').innerText = promoted;
    document.getElementById('trialCount').innerText = trial;
    document.getElementById('repeatCount').innerText = repeat;
}

function setupPaginationLinks() {
    document.querySelectorAll('#pagination-container a').forEach(link => {
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
    tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...<\/td><\/tr>';

    axios.get(url, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function(response) {
        document.getElementById('studentTableBody').innerHTML = response.data.tableBody;
        document.getElementById('pagination-container').innerHTML = response.data.pagination;
        document.getElementById('studentcount').innerText = response.data.studentCount || '0';
        updateStats();
        setupPaginationLinks();
        setupCheckboxHandlers();
    }).catch(function(error) {
        console.error('Page load error:', error);
        tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data.<\/td><\/tr>';
    });
}

function setupCheckboxHandlers() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.row-checkbox').forEach(cb => { cb.checked = this.checked; });
            updateBulkBar();
        });
    }
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });
}

function updateBulkBar() {
    const count = document.querySelectorAll('.row-checkbox:checked').length;
    const bulkBar = document.getElementById('bulkActionBar');
    if (count > 0) {
        bulkBar.classList.add('visible');
        document.getElementById('bulkCount').innerText = count + ' selected';
    } else {
        bulkBar.classList.remove('visible');
    }
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    if (document.getElementById('selectAll')) document.getElementById('selectAll').checked = false;
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
    if (!selectedIds.length) return;

    const promotionType = document.getElementById('bulkPromotionType').value;
    const newClass      = document.getElementById('bulkNewClass').value;
    const newSession    = document.getElementById('bulkNewSession').value;
    const newTerm       = document.getElementById('bulkNewTerm').value;

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

function gradePassFail(studentGrade, minGrade) {
    if (!studentGrade) return false;
    const sg = studentGrade.toString().toUpperCase().trim();
    if (minGrade) {
        const mg = minGrade.toString().toUpperCase().trim();
        return (gradeOrder[sg] ?? -1) >= (gradeOrder[mg] ?? 0);
    }
    return !['F', 'F9'].includes(sg);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ── Open promotion modal ───────────────────────────────────────────────────────
async function openPromotionModal(studentId, admissionNo, firstName, lastName, otherName, picture, gender, schoolclass, schoolarm, session, termid) {
    currentStudentId      = studentId;
    currentSchoolclassId  = document.getElementById("idclass").value;
    currentSessionId      = document.getElementById("idsession").value;
    currentTermId         = termid || document.getElementById("idterm").value;

    document.getElementById('modalStudentName').innerHTML =
        `<i class="ri-id-card-line me-2"></i>${admissionNo} - ${firstName} ${lastName} ${otherName || ''}`;
    document.getElementById('modalStudentGender').innerHTML =
        `<i class="ri-gender-${gender === 'Male' ? 'male' : 'female'}-line me-1"></i>${gender || 'N/A'}`;
    document.getElementById('modalCurrentClass').innerText   = schoolclass;
    document.getElementById('modalCurrentArm').innerText     = schoolarm || 'N/A';
    document.getElementById('modalCurrentSession').innerText = session;

    // FIXED: Better image path handling with cache busting
    const imgEl = document.getElementById('modalStudentImage');
    const imagePath = normalizeImagePath(picture, gender);

    // Add timestamp to force refresh and avoid cache issues
    const finalPath = imagePath + '?t=' + new Date().getTime();
    imgEl.src = finalPath;

    // Handle image load errors with multiple fallbacks
    imgEl.onerror = function() {
        console.error('Failed to load image:', finalPath);
        // First fallback: try without cache busting
        this.src = imagePath;

        this.onerror = function() {
            console.error('Still failed, trying gender default');
            // Second fallback: gender-specific default
            const fallbackPath = gender === 'Male'
                ? '/storage/student_avatars/male-default.png'
                : '/storage/student_avatars/female-default.png';
            this.src = fallbackPath + '?t=' + new Date().getTime();

            this.onerror = function() {
                console.error('Using ultimate fallback');
                // Ultimate fallback
                this.src = '/storage/student_avatars/unnamed.jpg';
            };
        };
    };

    // Reset form
    document.getElementById('promotionForm').reset();
    ['newClassSelect', 'newSessionSelect', 'newTermSelect'].forEach(id => {
        document.getElementById(id).value = '';
    });
    ['promotionCheckbox', 'trialCheckbox', 'seePrincipalCheckbox', 'repeatCheckbox'].forEach(id => {
        document.getElementById(id).checked = false;
    });
    document.getElementById('recommendationCard').style.display  = 'none';
    document.getElementById('compulsoryCard').style.display      = 'none';
    document.getElementById('allSubjectsCard').style.display     = 'none';
    document.getElementById('allSubjectsContent').innerHTML      = '';
    document.getElementById('compulsoryContent').innerHTML       = '';
    document.getElementById('recommendationContent').innerHTML   = '';

    Swal.fire({ title: 'Loading student data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const response = await axios.get(
            `/promotions/student-details/${studentId}/${currentSchoolclassId}/${currentSessionId}/${currentTermId}`
        );
        Swal.close();

        if (response.data.success) {
            currentStudentData = response.data;
            const result        = response.data.promotion_result;
            const avg           = response.data.overall_average;
            const allSubjects   = response.data.all_subjects   || [];
            const compulsoryData = response.data.compulsory_subjects || [];
            const stats         = response.data.statistics     || {};

            // Overall average
            const avgEl    = document.getElementById('modalOverallAverage');
            const avgValue = avg !== null ? `${avg}%` : 'N/A';
            const avgClass = avg !== null
                ? (avg >= 50 ? 'text-success' : avg >= 40 ? 'text-warning' : 'text-danger')
                : 'text-muted';
            avgEl.innerHTML = `<span class="${avgClass} fs-5">${avgValue}</span>`;

            // ── Recommendation card with fixed grammar ──────────────────────────
            if (result && result.status !== 'awaiting') {
                const recCard    = document.getElementById('recommendationCard');
                const recContent = document.getElementById('recommendationContent');
                recCard.style.display = 'block';

                const statusColors = {
                    promoted:      { bg: '#10b981', icon: 'ri-checkbox-circle-line' },
                    trial:         { bg: '#f59e0b', icon: 'ri-time-line'            },
                    see_principal: { bg: '#3b82f6', icon: 'ri-eye-line'             },
                    repeated:      { bg: '#ef4444', icon: 'ri-repeat-line'          },
                };
                const sc = statusColors[result.status] || { bg: '#6b7280', icon: 'ri-question-line' };

                let html = `<div class="recommendation-card ${result.status}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="label text-muted mb-2">System Recommendation</div>
                            <span class="status-badge-lg text-white" style="background:${sc.bg};">
                                <i class="${sc.icon} me-1"></i>${result.status_label || result.status}
                            </span>
                        </div>`;

                if (result.required_average !== null) {
                    const metAvg = result.actual_average >= result.required_average;
                    html += `<div class="text-end">
                        <div class="rule-badge"><i class="ri-percent-line me-1"></i>Required: ${result.required_average}%</div>
                        <div class="small mt-1 ${metAvg ? 'text-success' : 'text-danger'}">
                            ${metAvg ? '✓' : '✗'} Actual: ${result.actual_average ?? avg ?? 'N/A'}%
                        </div>
                    </div>`;
                }
                html += `</div>`;

                if (result.applied_rule) {
                    // Format the description with proper grammar
                    const formattedDesc = formatRuleDescription(result.applied_rule.description);
                    html += `<div class="mt-3 pt-3 border-top d-flex align-items-center flex-wrap gap-2">
                        <i class="ri-price-tag-3-line text-primary"></i>
                        <strong>Matched rule:</strong>
                        <span class="badge bg-primary">${escapeHtml(result.applied_rule.name)}</span>
                        ${formattedDesc
                            ? `<span class="small text-muted ms-2">— ${escapeHtml(formattedDesc)}</span>`
                            : ''}
                    </div>`;
                }

                if (result.compulsory_count > 0) {
                    const allPassed = result.passed_compulsory === result.compulsory_count;
                    html += `<div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <span><i class="ri-book-open-line me-1"></i>Compulsory subjects:</span>
                            <span class="${allPassed ? 'text-success' : 'text-danger'} fw-bold">
                                ${result.passed_compulsory}/${result.compulsory_count} passed
                            </span>
                        </div>`;
                    if (result.failed_compulsory?.length > 0) {
                        html += `<div class="mt-2 small text-danger">
                            <i class="ri-close-circle-line me-1"></i>Failed: `;
                        result.failed_compulsory.forEach(f => {
                            html += `<span class="badge bg-danger me-1">${f.subject || `Subject #${f.subject_id}`}</span>`;
                        });
                        html += `</div>`;
                    }
                    html += `</div>`;
                }
                html += `</div>`;
                recContent.innerHTML = html;
            }

            // ── ALL SUBJECTS TABLE (improved with fixed grammar) ─────────────────────────
            if (allSubjects && allSubjects.length > 0) {
                document.getElementById('allSubjectsCard').style.display = 'block';

                const failGradeSet = new Set(['F', 'F9', 'E8']);

                // Detect senior vs junior grading from actual grade values
                const allGrades = allSubjects.map(s => (s.grade || '').toUpperCase());
                const hasSeniorGrade = allGrades.some(g => /^[A-E][1-9]$|^F9$/.test(g));

                const seniorGradeOrder = { F9:0, E8:1, D7:2, C6:3, C5:4, C4:5, B3:6, B2:7, A1:8 };
                const juniorGradeOrder = { F:0, D:1, C:2, B:3, A:4 };

                function gradeRank(g) {
                    g = (g || '').toUpperCase();
                    return hasSeniorGrade ? (seniorGradeOrder[g] ?? -1) : (juniorGradeOrder[g] ?? -1);
                }
                function isCreditGrade(g) {
                    g = (g || '').toUpperCase();
                    return hasSeniorGrade
                        ? ['A1','B2','B3','C4','C5','C6'].includes(g)
                        : ['A','B','C'].includes(g);
                }
                function isPassGrade(g) {
                    g = (g || '').toUpperCase();
                    return hasSeniorGrade ? !['F9','E8'].includes(g) && g !== '' : g !== 'F' && g !== '';
                }
                function gradeClr(g) {
                    if (!g || g === '—') return 'var(--color-text-secondary)';
                    const u = g.toUpperCase();
                    if (['A1','A'].includes(u))                   return '#15803d';
                    if (['B2','B3','B'].includes(u))              return '#1d4ed8';
                    if (['C4','C5','C6','C'].includes(u))         return '#0369a1';
                    if (['D7','D'].includes(u))                   return '#d97706';
                    return '#b91c1c';
                }

                const compulsoryList = allSubjects.filter(s => s.is_compulsory);
                const optionalList   = allSubjects.filter(s => !s.is_compulsory);

                const compCreditCount  = compulsoryList.filter(s => s.grade && isCreditGrade(s.grade)).length;
                const otherCreditCount = optionalList.filter(s => s.grade && isCreditGrade(s.grade)).length;
                const allCreditCount   = allSubjects.filter(s => s.grade && isCreditGrade(s.grade)).length;

                const compPassCount   = compulsoryList.filter(s => s.pass_status === 'pass').length;
                const compFailCount   = compulsoryList.filter(s => s.pass_status === 'fail').length;
                const compNotSatCount = compulsoryList.filter(s => s.pass_status === 'not_sat').length;

                const optPassCount = optionalList.filter(s => {
                    if (s.pass_status === 'optional_pass') return true;
                    if (s.pass_status === 'optional_fail' || s.pass_status === 'optional_not_sat') return false;
                    return s.grade && !failGradeSet.has(s.grade.toUpperCase());
                }).length;
                const optFailCount = optionalList.filter(s => {
                    if (s.pass_status === 'optional_fail') return true;
                    if (s.pass_status === 'optional_pass' || s.pass_status === 'optional_not_sat') return false;
                    return s.grade && failGradeSet.has(s.grade.toUpperCase());
                }).length;

                const appliedRuleName = result?.applied_rule?.name || null;
                const appliedRuleDesc = result?.applied_rule?.description ? formatRuleDescription(result.applied_rule.description) : null;

                // ── tag() helper ──────────────────────────────────────────────────────
                function tag(label, color, rimIcon, tooltip) {
                    const cfg = {
                        green: ['var(--color-background-success)','var(--color-text-success)','var(--color-border-success)'],
                        red:   ['var(--color-background-danger)','var(--color-text-danger)','var(--color-border-danger)'],
                        blue:  ['var(--color-background-info)','var(--color-text-info)','var(--color-border-info)'],
                        amber: ['var(--color-background-warning)','var(--color-text-warning)','var(--color-border-warning)'],
                        gray:  ['var(--color-background-secondary)','var(--color-text-secondary)','var(--color-border-tertiary)'],
                    }[color] || ['var(--color-background-secondary)','var(--color-text-secondary)','var(--color-border-tertiary)'];
                    return `<span title="${escapeHtml(tooltip || label)}" style="display:inline-flex;align-items:center;gap:4px;background:${cfg[0]};color:${cfg[1]};border:0.5px solid ${cfg[2]};font-size:11px;font-weight:500;padding:3px 8px;border-radius:6px;white-space:nowrap;cursor:default;"><i class="ri-${rimIcon}" style="font-size:12px;"></i>${escapeHtml(label)}</span>`;
                }

                // ── per-subject rule context tag ──────────────────────────────────────
                function ruleTag(subject) {
                    const grade = (subject.grade || '').toUpperCase();
                    const ps    = subject.pass_status;
                    if (subject.is_compulsory) {
                        if (ps === 'not_sat')
                            return tag('Not sat — compulsory subject', 'amber', 'alert-line', 'Absent. Compulsory subjects must be sat.');
                        const min = subject.required_min_grade;
                        if (min && min !== '—') {
                            return ps === 'pass'
                                ? tag(`Meets min grade (≥ ${min})`, 'green', 'checkbox-circle-line', `${grade} ≥ required ${min} ✓`)
                                : tag(`Below min grade (needs ≥ ${min})`, 'red', 'close-circle-line', `${grade} < required ${min} ✗`);
                        }
                        return ps === 'pass'
                            ? tag('Compulsory — passed', 'green', 'checkbox-circle-line', 'Meets pass threshold')
                            : tag('Compulsory — failed', 'red', 'close-circle-line', 'Does not meet pass threshold');
                    }
                    if (!grade || grade === '—')
                        return tag('Not attempted', 'gray', 'subtract-line', 'No score — not counted in any condition');
                    if (isCreditGrade(grade))
                        return tag('Credit — counted in grade tally', 'blue', 'add-circle-line', `${grade} is a credit — contributes to grade count conditions`);
                    if (isPassGrade(grade) && !isCreditGrade(grade))
                        return tag('Pass — not a credit', 'amber', 'record-circle-line', `${grade} is a pass but not credit — does not count toward credit conditions`);
                    return tag('Fail — not counted', 'red', 'close-circle-line', `${grade} is a failing grade — does not contribute to any condition`);
                }

                // ── sub-note line under the tag ───────────────────────────────────────
                function subNote(subject) {
                    const grade = (subject.grade || '').toUpperCase();
                    const ps    = subject.pass_status;
                    const min   = subject.required_min_grade;
                    if (subject.is_compulsory) {
                        if (ps === 'fail' && grade !== '—' && min && min !== '—')
                            return `<span style="font-size:10.5px;color:var(--color-text-danger);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>Has ${grade}, rule needs ≥ ${min} — blocks every rule requiring this subject</span>`;
                        if (ps === 'pass' && min && min !== '—')
                            return `<span style="font-size:10.5px;color:var(--color-text-success);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>Satisfies compulsory min grade condition</span>`;
                        if (ps === 'not_sat')
                            return `<span style="font-size:10.5px;color:var(--color-text-warning);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>No score recorded — compulsory subject must be sat to qualify</span>`;
                        return '';
                    }
                    if (!grade || grade === '—')
                        return `<span style="font-size:10.5px;color:var(--color-text-secondary);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>Not counted in any condition</span>`;
                    if (isCreditGrade(grade))
                        return `<span style="font-size:10.5px;color:var(--color-text-secondary);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>Counted as 1 credit — contributes to grade count conditions</span>`;
                    if (isPassGrade(grade) && !isCreditGrade(grade))
                        return `<span style="font-size:10.5px;color:var(--color-text-secondary);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>Pass but below credit threshold — not counted in credit conditions</span>`;
                    return `<span style="font-size:10.5px;color:var(--color-text-secondary);margin-top:2px;display:block;"><i class="ri-arrow-right-line me-1"></i>Fail grade — not counted in any condition</span>`;
                }

                // ── score bar ─────────────────────────────────────────────────────────
                function scoreBar2(score, barBg) {
                    if (score === null || score === undefined)
                        return `<span style="color:var(--color-text-secondary);font-size:12px;">—</span>`;
                    const pct = Math.min(100, Math.round((parseFloat(score) / 100) * 100));
                    return `<span style="display:inline-flex;align-items:center;gap:6px;">
                        <strong style="min-width:26px;text-align:right;">${score}</strong>
                        <span style="background:var(--color-border-tertiary);border-radius:3px;height:5px;width:52px;display:inline-block;vertical-align:middle;overflow:hidden;">
                            <span style="display:block;height:100%;width:${pct}%;background:${barBg};border-radius:3px;"></span>
                        </span>
                    </span>`;
                }

                // ── shared pill helper ────────────────────────────────────────────────
                function pill(label, color, icon) {
                    return `<span style="background:var(--color-background-${color});color:var(--color-text-${color});border:0.5px solid var(--color-border-${color});font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><i class="ri-${icon}"></i>${label}</span>`;
                }

                // ── BUILD ─────────────────────────────────────────────────────────────
                let html = '';

                // Rule match / no-match note with fixed grammar
                if (appliedRuleName) {
                    const stBg  = { promoted:'success', trial:'warning', see_principal:'info', repeated:'danger' }[result?.status] || 'secondary';
                    html += `<div style="background:var(--color-background-${stBg});border:0.5px solid var(--color-border-${stBg});border-radius:8px;padding:11px 14px;margin-bottom:13px;font-size:12.5px;color:var(--color-text-${stBg});">
                        <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
                            <i class="ri-price-tag-3-line" style="font-size:15px;"></i>
                            <strong>Matched rule: ${escapeHtml(appliedRuleName)}</strong>
                            ${appliedRuleDesc ? `<span style="opacity:.75;font-size:11.5px;">— ${escapeHtml(appliedRuleDesc)}</span>` : ''}
                        </div>
                        <div style="margin-top:7px;font-size:11.5px;opacity:.85;">
                            <i class="ri-information-line" style="margin-right:4px;"></i>
                            Credit grades (${hasSeniorGrade ? 'C4–A1' : 'C–A'}) are tallied per scope to satisfy count conditions. Each subject's contribution is shown below.
                        </div>
                    </div>`;
                } else {
                    html += `<div style="background:var(--color-background-danger);border:0.5px solid var(--color-border-danger);border-radius:8px;padding:11px 14px;margin-bottom:13px;font-size:12.5px;color:var(--color-text-danger);">
                        <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
                            <i class="ri-close-circle-line" style="font-size:15px;"></i>
                            <strong>No rule matched.</strong> Student did not satisfy any promotion rule — result is Advice to Repeat.
                        </div>
                        <div style="margin-top:7px;font-size:11.5px;opacity:.85;">
                            <i class="ri-information-line" style="margin-right:4px;"></i>
                            Check each compulsory subject's min grade and whether the credit counts below reach the rule thresholds.
                        </div>
                    </div>`;
                }

                // Credit tally cards
                html += `<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
                    ${[
                        { label:'Credits in compulsory', val:compCreditCount,  total:compulsoryList.length, clr:'var(--color-text-success)' },
                        { label:'Credits in optional',   val:otherCreditCount, total:optionalList.length,   clr:'var(--color-text-info)'    },
                        { label:'Total credits',          val:allCreditCount,   total:allSubjects.length,    clr:'var(--color-text-primary)' },
                    ].map(it => `<div style="background:var(--color-background-secondary);border:0.5px solid var(--color-border-tertiary);border-radius:8px;padding:7px 13px;display:inline-flex;align-items:center;gap:8px;">
                        <span style="font-size:18px;font-weight:500;color:${it.clr};">${it.val}</span>
                        <span style="font-size:11px;color:var(--color-text-secondary);line-height:1.3;">${escapeHtml(it.label)}<br><span style="opacity:.7;">of ${it.total} subjects</span></span>
                    </div>`).join('')}
                </div>`;

                // Summary strip
                html += `<div style="display:flex;gap:7px;flex-wrap:wrap;align-items:center;background:var(--color-background-secondary);border:0.5px solid var(--color-border-tertiary);border-radius:8px;padding:9px 13px;margin-bottom:13px;">
                    ${pill(allSubjects.length + ' total subjects', 'secondary', 'book-open-line')}
                    <span style="width:0.5px;background:var(--color-border-tertiary);align-self:stretch;display:inline-block;margin:0 2px;"></span>
                    <span style="font-size:11px;color:var(--color-text-secondary);font-weight:500;">Compulsory:</span>
                    ${pill(compPassCount + ' passed', 'success', 'checkbox-circle-line')}
                    ${pill(compFailCount + ' failed', 'danger', 'close-circle-line')}
                    ${compNotSatCount > 0 ? pill(compNotSatCount + ' not sat', 'warning', 'minus-circle-line') : ''}
                    <span style="width:0.5px;background:var(--color-border-tertiary);align-self:stretch;display:inline-block;margin:0 2px;"></span>
                    <span style="font-size:11px;color:var(--color-text-secondary);font-weight:500;">Optional:</span>
                    ${pill(optPassCount + ' passed', 'success', 'checkbox-circle-line')}
                    ${pill(optFailCount + ' failed', 'danger', 'close-circle-line')}
                </div>`;

                // Table
                html += `<div style="overflow-x:auto;border-radius:10px;border:0.5px solid var(--color-border-tertiary);">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:var(--pay-primary,#1e3a5f);">
                            <th style="padding:9px 12px;text-align:left;color:#fff;font-size:12px;font-weight:600;width:32px;">#</th>
                            <th style="padding:9px 12px;text-align:left;color:#fff;font-size:12px;font-weight:600;">Subject</th>
                            <th style="padding:9px 12px;text-align:center;color:#fff;font-size:12px;font-weight:600;width:50px;">Code</th>
                            <th style="padding:9px 12px;text-align:center;color:#fff;font-size:12px;font-weight:600;width:130px;">Score / 100</th>
                            <th style="padding:9px 12px;text-align:center;color:#fff;font-size:12px;font-weight:600;width:60px;">Grade</th>
                            <th style="padding:9px 12px;text-align:center;color:#fff;font-size:12px;font-weight:600;width:100px;">Min grade</th>
                            <th style="padding:9px 12px;text-align:left;color:#fff;font-size:12px;font-weight:600;">Rule evaluation</th>
                        </tr>
                    </thead><tbody>`;

                // Compulsory section
                if (compulsoryList.length > 0) {
                    html += `<tr><td colspan="7" style="background:var(--color-background-secondary);padding:6px 12px;font-size:10.5px;font-weight:600;color:var(--color-text-secondary);letter-spacing:.04em;text-transform:uppercase;border-bottom:0.5px solid var(--color-border-tertiary);border-top:0.5px solid var(--color-border-tertiary);">
                        <i class="ri-star-fill" style="color:#d97706;margin-right:5px;"></i>Compulsory subjects — always rule-bound &nbsp;·&nbsp; ${compulsoryList.length} subject${compulsoryList.length !== 1 ? 's' : ''}
                    <\/td><\/tr>`;

                    compulsoryList.forEach((s, idx) => {
                        const grade  = s.grade || '—';
                        const ps     = s.pass_status;
                        const rowBdr = ps === 'pass' ? '#10b981' : ps === 'fail' ? '#ef4444' : '#f59e0b';
                        const barBg  = rowBdr;
                        html += `<tr style="border-left:3px solid ${rowBdr};border-bottom:0.5px solid var(--color-border-tertiary);">
                            <td style="padding:10px 12px;color:var(--color-text-secondary);font-size:11px;">${idx + 1}<\/td>
                            <td style="padding:10px 12px;">
                                <strong style="color:var(--color-text-primary);">${escapeHtml(s.subject_name)}</strong>
                                <span style="background:var(--color-background-warning);color:var(--color-text-warning);border:0.5px solid var(--color-border-warning);font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:6px;vertical-align:middle;">COMPULSORY</span>
                             <\/td>
                            <td style="padding:10px 12px;text-align:center;color:var(--color-text-secondary);font-family:monospace;font-size:12px;">${escapeHtml(s.subject_code) || '—'}<\/td>
                            <td style="padding:10px 12px;text-align:center;">${scoreBar2(s.total, barBg)}<\/td>
                            <td style="padding:10px 12px;text-align:center;"><strong style="color:${gradeClr(grade)};font-size:16px;">${grade}</strong><\/td>
                            <td style="padding:10px 12px;text-align:center;">
                                ${s.required_min_grade && s.required_min_grade !== '—'
                                    ? `<span style="background:var(--color-background-info);color:var(--color-text-info);border:0.5px solid var(--color-border-info);font-size:11px;padding:2px 9px;border-radius:10px;font-weight:600;">≥ ${s.required_min_grade}</span>`
                                    : `<span style="color:var(--color-text-secondary);font-size:12px;">—</span>`}
                             <\/td>
                            <td style="padding:10px 12px;">
                                ${ruleTag(s)}
                                ${subNote(s)}
                             <\/td>
                        <\/tr>`;
                    });
                }

                // Optional section
                if (optionalList.length > 0) {
                    html += `<tr><td colspan="7" style="background:var(--color-background-secondary);padding:6px 12px;font-size:10.5px;font-weight:600;color:var(--color-text-secondary);letter-spacing:.04em;text-transform:uppercase;border-bottom:0.5px solid var(--color-border-tertiary);border-top:0.5px solid var(--color-border-tertiary);">
                        <i class="ri-book-line" style="color:#0891b2;margin-right:5px;"></i>Optional subjects — contribute to grade count conditions &nbsp;·&nbsp; ${otherCreditCount} credit${otherCreditCount !== 1 ? 's' : ''} of ${optionalList.length} subjects
                    <\/td><\/tr>`;

                    optionalList.forEach((s, idx) => {
                        const grade    = s.grade || '—';
                        const isCredit = grade !== '—' && isCreditGrade(grade);
                        const isFail   = grade !== '—' && !isPassGrade(grade);
                        const rowBdr   = isCredit ? '#0891b2' : isFail ? '#ef4444' : '#d97706';
                        const barBg    = rowBdr;

                        let resolvedStatus = s.pass_status;
                        if (!resolvedStatus || resolvedStatus === 'optional') {
                            if (s.total === null && grade === '—') resolvedStatus = 'optional_not_sat';
                            else resolvedStatus = failGradeSet.has(grade.toUpperCase()) ? 'optional_fail' : 'optional_pass';
                        }

                        html += `<tr style="border-left:3px solid ${rowBdr};border-bottom:0.5px solid var(--color-border-tertiary);">
                            <td style="padding:10px 12px;color:var(--color-text-secondary);font-size:11px;">${compulsoryList.length + idx + 1}<\/td>
                            <td style="padding:10px 12px;">
                                <strong style="color:var(--color-text-primary);">${escapeHtml(s.subject_name)}</strong>
                                <span style="background:var(--color-background-info);color:var(--color-text-info);border:0.5px solid var(--color-border-info);font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:6px;vertical-align:middle;">OPTIONAL</span>
                             <\/td>
                            <td style="padding:10px 12px;text-align:center;color:var(--color-text-secondary);font-family:monospace;font-size:12px;">${escapeHtml(s.subject_code) || '—'}<\/td>
                            <td style="padding:10px 12px;text-align:center;">${scoreBar2(s.total, barBg)}<\/td>
                            <td style="padding:10px 12px;text-align:center;"><strong style="color:${gradeClr(grade)};font-size:16px;">${grade}</strong><\/td>
                            <td style="padding:10px 12px;text-align:center;"><span style="color:var(--color-text-secondary);font-size:11px;font-style:italic;">No min grade</span><\/td>
                            <td style="padding:10px 12px;">
                                ${ruleTag(s)}
                                ${subNote(s)}
                             <\/td>
                        <\/tr>`;
                    });
                }

                html += `<\/tbody><\/table><\/div>`;
                document.getElementById('allSubjectsContent').innerHTML = html;
            }

            // ── Compulsory subjects summary card ────────────────────────────
            if (compulsoryData && compulsoryData.length > 0) {
                const passCount2   = compulsoryData.filter(s => s.pass_status === 'pass').length;
                const failCount2   = compulsoryData.filter(s => s.pass_status === 'fail').length;
                const notSatCount2 = compulsoryData.filter(s => s.pass_status === 'not_sat').length;

                let html = `<div class="d-flex gap-2 mb-3 flex-wrap">
                    <span class="badge bg-success" style="font-size:13px;padding:6px 12px;">
                        <i class="ri-checkbox-circle-line me-1"></i>${passCount2} Passed
                    </span>
                    <span class="badge bg-danger" style="font-size:13px;padding:6px 12px;">
                        <i class="ri-close-circle-line me-1"></i>${failCount2} Failed
                    </span>`;
                if (notSatCount2 > 0) {
                    html += `<span class="badge bg-secondary" style="font-size:13px;padding:6px 12px;">
                        <i class="ri-minus-line me-1"></i>${notSatCount2} Not Sat
                    </span>`;
                }
                html += `</div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Required</th>
                                <th>Rule Requirement</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;

                compulsoryData.forEach(cs => {
                    const sc2 = cs.pass_status === 'pass' ? 'success' : (cs.pass_status === 'fail' ? 'danger' : 'secondary');
                    const ico = cs.pass_status === 'pass' ? '✓' : (cs.pass_status === 'fail' ? '✗' : '○');
                    html += `<tr>
                        <td><strong>${escapeHtml(cs.subject)}</strong><br>
                            <small class="text-muted">${escapeHtml(cs.subject_code || '')}</small></td>
                        <td><strong style="color:${gradeClr(cs.student_grade || '')}">${cs.student_grade || 'Not Sat'}</strong></td>
                        <td>${cs.required_min_grade || '—'}</td>
                        <td><small class="text-muted">${cs.rule_requirement || '—'}</small></td>
                        <td><span class="badge bg-${sc2}">${ico} ${cs.pass_status_label || cs.pass_status}</span></td>
                     </tr>`;
                });

                html += `</tbody></table></div>`;
                document.getElementById('compulsoryContent').innerHTML = html;
                document.getElementById('compulsoryCard').style.display = 'block';
            }
        }

    } catch (error) {
        Swal.close();
        console.error('Error fetching student details:', error);
        Swal.fire('Error', 'Failed to load student details: ' + (error.response?.data?.message || error.message), 'error');
    }

    new bootstrap.Modal(document.getElementById('promotionModal')).show();
}

// ── Remove student ─────────────────────────────────────────────────────────────
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
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const fd = new FormData();
        fd.append('_method', 'DELETE');
        fd.append('schoolclassid', schoolclassId);
        fd.append('sessionid', sessionId);
        fd.append('termid', termId);

        axios.post(`/promotions/${studentId}`, fd, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'multipart/form-data'
            }
        }).then(response => {
            if (response.data.success) {
                Swal.fire({ icon: 'success', title: 'Removed!', text: response.data.message, timer: 2000, showConfirmButton: false });
                filterData();
            } else {
                Swal.fire('Error!', response.data.message || 'Failed to remove.', 'error');
            }
        }).catch(error => {
            Swal.fire('Error!', error.response?.data?.message || 'Failed to remove student.', 'error');
        });
    });
}

// ── Submit promotion ───────────────────────────────────────────────────────────
function submitPromotion() {
    if (!currentStudentId) {
        Swal.fire('Error!', 'Student ID not found.', 'error');
        return;
    }

    const newClassSelect   = document.getElementById('newClassSelect');
    const newSessionSelect = document.getElementById('newSessionSelect');
    const newTermSelect    = document.getElementById('newTermSelect');

    if (!newClassSelect.value)   { Swal.fire('Error!', 'Please select a new class.',   'error'); return; }
    if (!newSessionSelect.value) { Swal.fire('Error!', 'Please select a new session.', 'error'); return; }
    if (!newTermSelect.value)    { Swal.fire('Error!', 'Please select a new term.',    'error'); return; }

    const promotionCheckbox   = document.getElementById('promotionCheckbox');
    const trialCheckbox       = document.getElementById('trialCheckbox');
    const seePrincipalCheckbox = document.getElementById('seePrincipalCheckbox');
    const repeatCheckbox      = document.getElementById('repeatCheckbox');

    const selectedCount = [promotionCheckbox, trialCheckbox, seePrincipalCheckbox, repeatCheckbox]
        .filter(cb => cb.checked).length;
    if (selectedCount !== 1) {
        Swal.fire('Error!', 'Please select exactly one promotion decision.', 'error');
        return;
    }

    const fd = new FormData();
    fd.append('_method',          'PUT');
    fd.append('new_schoolclassid', newClassSelect.value);
    fd.append('new_sessionid',     newSessionSelect.value);
    fd.append('new_termid',        newTermSelect.value);
    fd.append('promotion',         promotionCheckbox.checked   ? '1' : '0');
    fd.append('trial',             trialCheckbox.checked       ? '1' : '0');
    fd.append('see_principal',     seePrincipalCheckbox.checked ? '1' : '0');
    fd.append('repeat',            repeatCheckbox.checked      ? '1' : '0');

    Swal.fire({
        title: 'Confirm Update',
        text: "Update this student's promotion?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Update',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        axios.post(`/promotions/${currentStudentId}`, fd, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(response => {
            if (response.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('promotionModal')).hide();
                Swal.fire({ icon: 'success', title: 'Success!', text: response.data.message, timer: 2000, showConfirmButton: false });
                filterData();
            } else {
                Swal.fire('Error!', response.data.message || 'Failed to update.', 'error');
            }
        }).catch(error => {
            Swal.fire('Error!', error.response?.data?.message || 'Failed to update promotion.', 'error');
        });
    });
}

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("idclass").addEventListener("change",  filterData);
    document.getElementById("idsession").addEventListener("change", filterData);
    document.getElementById("idterm").addEventListener("change",    filterData);

    let searchTimeout;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterData, 500);
    });

    setupCheckboxHandlers();
    updateStats();
});
</script>
@endsection
