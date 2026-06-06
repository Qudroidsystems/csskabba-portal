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

.promotion-badge-promoted { background: #10b981; color: white; }
.promotion-badge-trial { background: #f59e0b; color: white; }
.promotion-badge-see_principal { background: #3b82f6; color: white; }
.promotion-badge-repeated { background: #ef4444; color: white; }
.promotion-badge-pending { background: #6b7280; color: white; }

.recommendation-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
}

.recommendation-card .label {
    font-size: 12px;
    color: var(--pay-muted);
    margin-bottom: 4px;
}

.recommendation-card .value {
    font-size: 16px;
    font-weight: 700;
}

.recommendation-card.promoted { border-left: 4px solid #10b981; }
.recommendation-card.trial { border-left: 4px solid #f59e0b; }
.recommendation-card.see_principal { border-left: 4px solid #3b82f6; }
.recommendation-card.repeated { border-left: 4px solid #ef4444; }

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

.bulk-action-bar.visible {
    display: flex;
}

.bulk-action-bar .bulk-count {
    font-size: 13px;
    font-weight: 600;
    color: #92400e;
}

.select-all-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row" style="margin-top: 60px;">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Student Promotions</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

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

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?? session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="studentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idclass" name="schoolclassid">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idsession" name="sessionid">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idterm" name="termid">
                                            <option value="3">Third Term (Promotional)</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" id="searchInput" name="search" placeholder="Search students...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $allstudents->total() }}</span></h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" id="bulkPromoteBtn" style="display: none;">
                                        <i class="ri-group-line me-1"></i>Bulk Promote Selected
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
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" class="select-all-checkbox" id="selectAll" title="Select all">
                                                </th>
                                                <th>Admission No</th>
                                                <th>Picture</th>
                                                <th>Last Name</th>
                                                <th>First Name</th>
                                                <th>Other Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Arm</th>
                                                <th>Session</th>
                                                <th>Overall Avg</th>
                                                <th>Promotion Recommendation</th>
                                                <th>Current Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('promotions.partials.student_rows')
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $allstudents->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promotion Modal -->
                <div id="promotionModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white">
                                <div>
                                    <h5 class="modal-title mb-0"><i class="ri-user-star-line me-2"></i>Student Promotion</h5>
                                    <small class="opacity-75">Update student class and session information</small>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="promotionForm">
                                @csrf
                                <div class="modal-body p-4">
                                    <!-- Student Information Card -->
                                    <div class="card border shadow-sm mb-4">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">
                                                <i class="ri-user-line me-2"></i>Current Student Information
                                            </h6>
                                            <div class="row align-items-center">
                                                <div class="col-md-3 text-center">
                                                    <img id="modalStudentImage"
                                                         src=""
                                                         alt="Student Picture"
                                                         class="img-fluid rounded-circle shadow"
                                                         style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #f0f0f0;"
                                                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                                </div>
                                                <div class="col-md-9">
                                                    <h5 class="mb-3 text-primary" id="modalStudentName"></h5>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                                <i class="ri-book-2-line text-primary fs-5 me-2"></i>
                                                                <div>
                                                                    <small class="text-muted d-block">Current Class</small>
                                                                    <strong id="modalCurrentClass"></strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                                <i class="ri-team-line text-primary fs-5 me-2"></i>
                                                                <div>
                                                                    <small class="text-muted d-block">Current Arm</small>
                                                                    <strong id="modalCurrentArm"></strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                                <i class="ri-calendar-line text-primary fs-5 me-2"></i>
                                                                <div>
                                                                    <small class="text-muted d-block">Current Session</small>
                                                                    <strong id="modalCurrentSession"></strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                                <i class="ri-percent-line text-primary fs-5 me-2"></i>
                                                                <div>
                                                                    <small class="text-muted d-block">Overall Average</small>
                                                                    <strong id="modalOverallAverage"></strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Promotion Recommendation Card -->
                                    <div class="card border shadow-sm mb-4" id="recommendationCard" style="display: none;">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">
                                                <i class="ri-question-line me-2"></i>System Recommendation
                                            </h6>
                                            <div id="recommendationContent"></div>
                                        </div>
                                    </div>

                                    <!-- Compulsory Subjects Status -->
                                    <div class="card border shadow-sm mb-4" id="compulsoryCard" style="display: none;">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">
                                                <i class="ri-book-open-line me-2"></i>Compulsory Subjects Status
                                            </h6>
                                            <div id="compulsoryContent"></div>
                                        </div>
                                    </div>

                                    <!-- Promotion Arrow -->
                                    <div class="text-center mb-4">
                                        <i class="ri-arrow-down-line text-primary fs-1 animate-bounce"></i>
                                    </div>

                                    <!-- New Information Card -->
                                    <div class="card border-primary shadow-sm">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary mb-3">
                                                <i class="ri-refresh-line me-2"></i>New Assignment Details
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">
                                                        <i class="ri-book-open-line me-1"></i>New Class <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select form-select-lg" name="new_schoolclassid" id="newClassSelect" required>
                                                        <option value="">-- Select New Class --</option>
                                                        @foreach ($schoolclasses as $class)
                                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm ?? '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">
                                                        <i class="ri-calendar-event-line me-1"></i>New Session <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select form-select-lg" name="new_sessionid" id="newSessionSelect" required>
                                                        <option value="">-- Select New Session --</option>
                                                        @foreach ($schoolsessions as $session)
                                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">
                                                        <i class="ri-calendar-todo-line me-1"></i>New Term <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select form-select-lg" name="new_termid" id="newTermSelect" required>
                                                        <option value="">-- Select New Term --</option>
                                                        <option value="1">First Term</option>
                                                        <option value="2">Second Term</option>
                                                        <option value="3">Third Term</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Promotion Type -->
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
                                                                    <h6 class="mb-1">Promote Student</h6>
                                                                    <p class="text-muted mb-0 small">Move to next class</p>
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
                                                                    <p class="text-muted mb-0 small">Student repeats current class</p>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" onclick="submitPromotion()">
                                    <i class="ri-save-line me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Promotion Modal -->
                <div class="modal fade" id="bulkPromotionModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
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
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}

.form-check-card .form-check-input {
    display: none;
}

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
.text-success { color: #198754 !important; }
.text-warning { color: #ffc107 !important; }
.text-info { color: #0dcaf0 !important; }
.text-danger { color: #dc3545 !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentStudentId = null;
    let currentSchoolclassId = null;
    let currentSessionId = null;
    let currentTermId = null;

    function updateSearchButtonVisibility() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const searchBtn = document.getElementById("searchBtn");

        if (classSelect && sessionSelect && termSelect) {
            searchBtn.style.display = (classSelect.value !== 'ALL' && sessionSelect.value !== 'ALL') ? 'block' : 'none';
        }
    }

    function filterData() {
        const classValue = document.getElementById("idclass").value;
        const sessionValue = document.getElementById("idsession").value;
        const termValue = document.getElementById("idterm").value;
        const searchValue = document.getElementById("searchInput").value.trim();

        if (classValue === 'ALL' || sessionValue === 'ALL') {
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="14" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
            return;
        }

        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="14" class="text-center">Loading...</td></tr>';

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
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="14" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxHandlers();
        }).catch(function (error) {
            console.error('AJAX Error:', error);
            tableBody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({ icon: "error", title: "Error", text: error.response?.data?.message || "Failed to fetch student data." });
        });
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
        tableBody.innerHTML = '<tr><td colspan="14" class="text-center">Loading...</td></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="14" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxHandlers();
        }).catch(function (error) {
            console.error('Page load error:', error);
            tableBody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        });
    }

    function setupCheckboxHandlers() {
        // Select All functionality
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
        const bulkBtn = document.getElementById('bulkPromoteBtn');

        if (selectedCount > 0) {
            bulkBar.classList.add('visible');
            document.getElementById('bulkCount').innerText = selectedCount + ' selected';
            if (bulkBtn) bulkBtn.style.display = 'block';
        } else {
            bulkBar.classList.remove('visible');
            if (bulkBtn) bulkBtn.style.display = 'none';
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
        document.getElementById('modalStudentImage').src = picture ? `/storage/${picture}` : '{{ asset('storage/student_avatars/unnamed.jpg') }}';

        document.getElementById('promotionForm').reset();
        document.getElementById('newClassSelect').value = '';
        document.getElementById('newSessionSelect').value = '';
        document.getElementById('newTermSelect').value = '';
        document.getElementById('promotionCheckbox').checked = false;
        document.getElementById('trialCheckbox').checked = false;
        document.getElementById('seePrincipalCheckbox').checked = false;
        document.getElementById('repeatCheckbox').checked = false;

        // Fetch recommendation
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await axios.get(`/promotions/student-details/${studentId}/${currentSchoolclassId}/${currentSessionId}/${currentTermId}`);

            if (response.data.success) {
                Swal.close();

                const result = response.data.promotion_result;
                const avg = response.data.overall_average;

                document.getElementById('modalOverallAverage').innerHTML = avg !== null ? `${avg}%` : 'N/A';

                // Show recommendation card
                const recCard = document.getElementById('recommendationCard');
                const recContent = document.getElementById('recommendationContent');

                if (result && result.status !== 'awaiting') {
                    recCard.style.display = 'block';
                    const statusClass = result.status;
                    const statusLabel = result.status_label || result.status;
                    const badgeClass = `promotion-badge-${statusClass}`;

                    let html = `
                        <div class="recommendation-card ${statusClass}">
                            <div class="label">System Recommendation</div>
                            <div class="value">
                                <span class="badge ${badgeClass} fs-6 p-2">${statusLabel}</span>
                            </div>
                    `;

                    if (result.required_average !== null) {
                        html += `<div class="mt-2"><small>Required Average: ${result.required_average}% | Actual: ${result.actual_average || avg}%</small></div>`;
                    }

                    if (result.compulsory_count > 0) {
                        html += `<div class="mt-2"><small>Compulsory Subjects: ${result.passed_compulsory}/${result.compulsory_count} passed</small></div>`;
                    }

                    if (result.failed_compulsory && result.failed_compulsory.length > 0) {
                        html += `<div class="mt-2 text-danger"><small>Failed Compulsory: ${result.failed_compulsory.map(f => f.subject || f.subject_id).join(', ')}</small></div>`;
                    }

                    html += `</div>`;
                    recContent.innerHTML = html;
                } else {
                    recCard.style.display = 'none';
                }

                // Show compulsory subjects
                if (response.data.compulsory_subjects && response.data.compulsory_subjects.length > 0) {
                    const compCard = document.getElementById('compulsoryCard');
                    const compContent = document.getElementById('compulsoryContent');
                    let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Subject</th><th>Min Grade</th><th>Student Grade</th><th>Status</th></tr></thead><tbody>';

                    response.data.compulsory_subjects.forEach(cs => {
                        const studentScore = response.data.scores_count > 0 ? 'Pending' : 'Not Available';
                        html += `<tr><td>${cs.subject?.subject || 'N/A'}</td><td>${cs.min_grade || 'Pass'}</td><td>${studentScore}</td><td><span class="badge bg-warning">Pending</span></td></tr>`;
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
            text: `Are you sure you want to remove ${fullName} from this class?`,
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
                        Swal.fire({ icon: 'success', title: 'Success!', text: response.data.message, timer: 2000, showConfirmButton: false });
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
            text: 'Are you sure you want to update this student\'s promotion?',
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
    });
</script>
@endsection
