@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
<style>
:root {
    --bill-primary: #1e3a5f;
    --bill-accent:  #2563eb;
    --bill-success: #16a34a;
    --bill-warning: #d97706;
    --bill-danger:  #dc2626;
    --bill-muted:   #6b7280;
    --bill-border:  #e2e8f0;
    --bill-bg:      #f8fafc;
    --bill-radius:  12px;
    --bill-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* Hero Section */
.bill-hero {
    background: linear-gradient(135deg, var(--bill-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--bill-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.bill-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.bill-hero::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -30px;
    width: 260px;
    height: 260px;
    background: rgba(255,255,255,.03);
    border-radius: 50%;
}
.bill-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.bill-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

/* Stat Cards */
.stat-card {
    background: #fff;
    border: 1px solid var(--bill-border);
    border-radius: var(--bill-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--bill-shadow);
}
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--bill-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: var(--bill-muted);
    margin-top: 4px;
}
.stat-card .stat-icon {
    font-size: 32px;
    opacity: .12;
    float: right;
    margin-top: -8px;
}
.stat-card.active {
    border: 2px solid var(--bill-accent);
    background: #eff6ff;
}
.stat-card.active .stat-value {
    color: var(--bill-accent);
}

/* Table Styles */
.bill-table {
    width: 100%;
}
.bill-table thead th {
    background: var(--bill-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.bill-table td {
    padding: 12px 16px;
    vertical-align: middle;
    font-size: 13px;
}
.bill-table tbody tr {
    transition: background 0.2s;
}
.bill-table tbody tr:hover {
    background: #eff6ff;
}
.bill-table tbody tr.table-active {
    background: #dbeafe;
}

/* Badges */
.bill-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.bill-badge-primary { background: #dbeafe; color: #2563eb; }
.bill-badge-success { background: #dcfce7; color: #16a34a; }
.bill-badge-warning { background: #fed7aa; color: #d97706; }
.bill-badge-info { background: #cffafe; color: #0891b2; }

/* Student Photo */
.student-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.1);
    cursor: pointer;
    transition: transform 0.2s;
}
.student-photo:hover {
    transform: scale(1.1);
}

/* Filter Card */
.filter-card {
    background: #fff;
    border: 1px solid var(--bill-border);
    border-radius: var(--bill-radius);
    padding: 20px;
    margin-bottom: 24px;
}
.filter-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--bill-muted);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.form-control-custom,
.form-select-custom {
    border: 1.5px solid var(--bill-border);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    transition: all 0.15s;
    width: 100%;
}
.form-control-custom:focus,
.form-select-custom:focus {
    border-color: var(--bill-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* Buttons */
.btn-custom-primary {
    background: var(--bill-accent);
    border: none;
    color: #fff;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-custom-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}
.btn-custom-secondary {
    background: #f1f5f9;
    border: 1px solid var(--bill-border);
    color: var(--bill-muted);
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-custom-secondary:hover {
    background: #e2e8f0;
}

/* Alert */
.selection-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    min-width: 300px;
    max-width: 90%;
    background: #fff;
    border-left: 4px solid var(--bill-accent);
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    border-radius: 8px;
    padding: 12px 20px;
    display: none;
}

/* Modal Styles */
.modal-custom .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
}
.modal-hero-bar {
    background: linear-gradient(135deg, var(--bill-primary) 0%, #2563eb 100%);
    padding: 20px 28px;
    position: relative;
}
.modal-hero-bar h5 {
    color: #fff;
    font-weight: 700;
    margin: 0;
    font-size: 16px;
}
.modal-hero-bar .btn-close {
    position: absolute;
    top: 18px;
    right: 20px;
    filter: invert(1);
}

/* Checkbox Styles */
.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.form-check-input:checked {
    background-color: var(--bill-accent);
    border-color: var(--bill-accent);
}

/* Pagination */
.pagination-custom {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}
.pagination-custom .pagination {
    margin: 0;
}
.pagination-custom .page-item.active .page-link {
    background: var(--bill-accent);
    border-color: var(--bill-accent);
}
.pagination-custom .page-link {
    color: var(--bill-primary);
    border-radius: 6px;
    margin: 0 2px;
}

/* Loading Spinner */
.spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,.5);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}
.spinner-overlay.show {
    display: flex;
}

/* Responsive */
@media (max-width: 768px) {
    .bill-hero {
        padding: 20px;
    }
    .stat-card .stat-value {
        font-size: 22px;
    }
    .bill-table thead th {
        font-size: 11px;
        padding: 8px 12px;
    }
    .bill-table td {
        padding: 8px 12px;
        font-size: 12px;
    }
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Hero Section -->
            <div class="bill-hero">
                <h1><i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }}</h1>
                <p>Generate, manage and print student terminal reports and transcripts.</p>
            </div>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" id="statTotalCard">
                        <div class="stat-icon"><i class="ri-group-line"></i></div>
                        <div class="stat-value" id="statTotal">0</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" id="statMaleCard">
                        <div class="stat-icon"><i class="ri-men-line"></i></div>
                        <div class="stat-value text-primary" id="statMale">0</div>
                        <div class="stat-label">Male Students</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" id="statFemaleCard">
                        <div class="stat-icon"><i class="ri-women-line"></i></div>
                        <div class="stat-value text-success" id="statFemale">0</div>
                        <div class="stat-label">Female Students</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" id="statSubjectsCard">
                        <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                        <div class="stat-value text-warning" id="statSubjects">0</div>
                        <div class="stat-label">Subjects Offered</div>
                    </div>
                </div>
            </div>

            <!-- Selection Alert -->
            <div class="selection-alert" id="selectionAlert">
                <div class="d-flex justify-content-between align-items-center">
                    <span id="selectionAlertText">No selections made.</span>
                    <button type="button" class="btn-close" onclick="closeAlert()"></button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <div class="filter-label">Class</div>
                        <select class="form-select-custom" id="idclass" name="schoolclassid">
                            <option value="ALL">Select Class</option>
                            @foreach ($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="filter-label">Session</div>
                        <select class="form-select-custom" id="idsession" name="sessionid">
                            <option value="ALL">Select Session</option>
                            @foreach ($schoolsessions as $session)
                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6" id="termSelectContainer" style="display: none;">
                        <div class="filter-label">Term</div>
                        <select class="form-select-custom" id="idterm" name="termid">
                            <option value="ALL">Select Term</option>
                            <option value="1">First Term</option>
                            <option value="2">Second Term</option>
                            <option value="3">Third Term</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="filter-label">Search</div>
                        <div class="position-relative">
                            <input type="text" class="form-control-custom" id="searchInput" name="search" placeholder="Search by name or admission no...">
                            <i class="ri-search-line position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); color: var(--bill-muted);"></i>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button class="btn-custom-secondary" id="searchBtn" style="display: none;" onclick="filterData()">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                        <button class="btn-custom-primary" id="printAllBtn" style="display: none;" onclick="printAllResults()">
                            <i class="ri-printer-line me-1"></i> Print Selected Results
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold" style="color: var(--bill-primary)">
                            <i class="ri-user-line me-2"></i>Student List
                            <span class="badge bg-primary ms-2" id="studentcount">0</span>
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="bill-table mb-0" id="studentListTable">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                        </div>
                                    </th>
                                    <th>Admission No</th>
                                    <th>Photo</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Other Name</th>
                                    <th>Gender</th>
                                    <th>Class</th>
                                    <th>Arm</th>
                                    <th>Session</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                @include('studentreports.partials.student_rows')
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-custom p-3 border-top" id="pagination-container">
                        {{ $allstudents ? $allstudents->links('pagination::bootstrap-5') : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image View Modal -->
<div class="modal fade modal-custom" id="imageViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-image-line me-2"></i>Student Photo</h5>
            </div>
            <div class="modal-body text-center p-4">
                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid rounded" style="max-height: 300px;">
            </div>
        </div>
    </div>
</div>

<!-- Column Selection Modal -->
<div class="modal fade modal-custom" id="columnSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-hero-bar">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ri-settings-4-line me-2"></i>Select Columns for PDF Report</h5>
            </div>
            <div class="modal-body p-4">
                <div id="columnSelectionContent">
                    <div class="alert alert-info mb-3">
                        <i class="ri-information-line me-2"></i>
                        Select the columns you want to include in the PDF report.
                    </div>
                    <div id="columnSelectionLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading column options...</p>
                    </div>
                    <div id="columnSelectionForm" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Student Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row" id="studentInfoColumns"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Assessments</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllAssessments">
                                            <label class="form-check-label" for="selectAllAssessments">Select All</label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row" id="assessmentColumns"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Scores & Metrics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row" id="scoreColumns"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">GPA/CGPA Metrics</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllGPAMetrics">
                                            <label class="form-check-label" for="selectAllGPAMetrics">Select All</label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row" id="gpaColumns"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Other Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row" id="otherColumns"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-custom-primary" id="saveColumnSelection" disabled>
                    <i class="ri-printer-line me-1"></i> Generate PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="spinner-overlay" id="loadingSpinner">
    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentPrintParams = null;

function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    if (show) {
        spinner.classList.add('show');
    } else {
        spinner.classList.remove('show');
    }
}

function showAlert(message, type = 'info') {
    const alert = document.getElementById('selectionAlert');
    const alertText = document.getElementById('selectionAlertText');
    alertText.innerHTML = message;
    alert.style.display = 'block';
    setTimeout(() => {
        alert.style.display = 'none';
    }, 5000);
}

function closeAlert() {
    document.getElementById('selectionAlert').style.display = 'none';
}

function updateStats() {
    const rowCount = document.querySelectorAll('#studentTableBody tr').length - 1;
    const maleCount = document.querySelectorAll('#studentTableBody td:nth-child(7):contains("Male")').length;
    const femaleCount = document.querySelectorAll('#studentTableBody td:nth-child(7):contains("Female")').length;

    document.getElementById('statTotal').innerText = rowCount;
    document.getElementById('statMale').innerText = maleCount;
    document.getElementById('statFemale').innerText = femaleCount;
}

function updateSelectionAlert() {
    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const termSelect = document.getElementById("idterm");
    const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');

    let alertText = [];
    if (classSelect.value !== 'ALL') {
        alertText.push(`Class: ${classSelect.options[classSelect.selectedIndex].text}`);
    }
    if (sessionSelect.value !== 'ALL') {
        alertText.push(`Session: ${sessionSelect.options[sessionSelect.selectedIndex].text}`);
    }
    if (termSelect.value !== 'ALL') {
        alertText.push(`Term: ${termSelect.options[termSelect.selectedIndex].text}`);
    }
    alertText.push(`Selected: ${checkedCheckboxes.length} students`);

    showAlert(alertText.join(' | '));
}

function updateSearchButtonVisibility() {
    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const searchBtn = document.getElementById("searchBtn");
    const termSelectContainer = document.getElementById("termSelectContainer");
    const printAllBtn = document.getElementById("printAllBtn");

    const isValid = classSelect.value !== 'ALL' && sessionSelect.value !== 'ALL';
    searchBtn.style.display = isValid ? 'inline-flex' : 'none';
    termSelectContainer.style.display = isValid ? 'block' : 'none';
    printAllBtn.style.display = 'none';
}

function updatePrintButtonVisibility() {
    const termSelect = document.getElementById("idterm");
    const printAllBtn = document.getElementById("printAllBtn");
    const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');

    printAllBtn.style.display = (termSelect.value !== 'ALL' && checkedCheckboxes.length > 0) ? 'inline-flex' : 'none';
}

function filterData() {
    showLoading(true);

    const classValue = document.getElementById("idclass").value;
    const sessionValue = document.getElementById("idsession").value;
    const termValue = document.getElementById("idterm").value;
    const searchValue = document.getElementById("searchInput").value.trim();

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        Swal.fire({
            icon: "warning",
            title: "Missing Selection",
            text: "Please select a valid class and session.",
            confirmButtonColor: "#2563eb"
        });
        showLoading(false);
        return;
    }

    axios.get('{{ route("studentreports.index") }}', {
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
        document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center">No students found.</td></tr>';
        document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
        document.getElementById('studentcount').innerText = response.data.studentCount || '0';

        setupPaginationLinks();
        setupCheckboxListeners();
        updateStats();
        updatePrintButtonVisibility();

        if (response.data.studentCount === 0) {
            Swal.fire({
                icon: "info",
                title: "No Results",
                text: "No students found for the selected filters.",
                confirmButtonColor: "#2563eb"
            });
        }
        showLoading(false);
    }).catch(function(error) {
        console.error("AJAX error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch student data.",
            confirmButtonColor: "#2563eb"
        });
        showLoading(false);
    });
}

function printAllResults() {
    const classValue = document.getElementById("idclass").value;
    const sessionValue = document.getElementById("idsession").value;
    const termValue = document.getElementById("idterm").value;
    const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    const selectedStudentIds = Array.from(checkedCheckboxes).map(checkbox => checkbox.value);

    if (classValue === 'ALL' || sessionValue === 'ALL' || termValue === 'ALL') {
        Swal.fire({
            icon: "warning",
            title: "Missing Selection",
            text: "Please select a valid class, session, and term.",
            confirmButtonColor: "#2563eb"
        });
        return;
    }

    if (selectedStudentIds.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Students Selected",
            text: "Please select at least one student.",
            confirmButtonColor: "#2563eb"
        });
        return;
    }

    const columnModal = new bootstrap.Modal(document.getElementById('columnSelectionModal'));
    columnModal.show();
    loadColumnOptions(classValue, sessionValue, termValue, selectedStudentIds);
}

function loadColumnOptions(classId, sessionId, termId, studentIds) {
    const loader = document.getElementById('columnSelectionLoader');
    const form = document.getElementById('columnSelectionForm');
    const saveBtn = document.getElementById('saveColumnSelection');

    loader.style.display = 'block';
    form.style.display = 'none';
    saveBtn.disabled = true;

    currentPrintParams = {
        classId: classId,
        sessionId: sessionId,
        termId: termId,
        studentIds: studentIds
    };

    fetch('{{ route("studentreports.column-options") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            schoolclassid: classId,
            sessionid: sessionId,
            termid: termId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateColumnOptions(data.columns);
            loader.style.display = 'none';
            form.style.display = 'block';
            saveBtn.disabled = false;
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: data.message || "Failed to load column options.",
                confirmButtonColor: "#2563eb"
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: "error",
            title: "Network Error",
            text: "Failed to load column options.",
            confirmButtonColor: "#2563eb"
        });
    });
}

function populateColumnOptions(columns) {
    const studentInfoDiv = document.getElementById('studentInfoColumns');
    const assessmentDiv = document.getElementById('assessmentColumns');
    const scoreDiv = document.getElementById('scoreColumns');
    const gpaDiv = document.getElementById('gpaColumns');
    const otherDiv = document.getElementById('otherColumns');

    studentInfoDiv.innerHTML = '';
    assessmentDiv.innerHTML = '';
    scoreDiv.innerHTML = '';
    gpaDiv.innerHTML = '';
    otherDiv.innerHTML = '';

    if (columns.student_info) {
        Object.entries(columns.student_info).forEach(([key, config]) => {
            studentInfoDiv.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">${config.label}</label>
                    </div>
                </div>
            `;
        });
    }

    if (columns.assessments) {
        Object.entries(columns.assessments).forEach(([key, config]) => {
            assessmentDiv.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input column-checkbox assessment-checkbox" type="checkbox" id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">${config.label}</label>
                    </div>
                </div>
            `;
        });
    }

    if (columns.scores) {
        Object.entries(columns.scores).forEach(([key, config]) => {
            scoreDiv.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">${config.label}</label>
                    </div>
                </div>
            `;
        });
    }

    if (columns.gpa_metrics) {
        Object.entries(columns.gpa_metrics).forEach(([key, config]) => {
            gpaDiv.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input column-checkbox gpa-checkbox" type="checkbox" id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">${config.label}</label>
                    </div>
                </div>
            `;
        });
    }

    if (columns.other) {
        Object.entries(columns.other).forEach(([key, config]) => {
            otherDiv.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">${config.label}</label>
                    </div>
                </div>
            `;
        });
    }

    document.getElementById('selectAllAssessments').addEventListener('change', function() {
        document.querySelectorAll('.assessment-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllGPAMetrics').addEventListener('change', function() {
        document.querySelectorAll('.gpa-checkbox').forEach(cb => cb.checked = this.checked);
    });
}

document.getElementById('saveColumnSelection').addEventListener('click', function() {
    const selectedColumns = [];
    document.querySelectorAll('.column-checkbox:checked').forEach(cb => {
        selectedColumns.push(cb.dataset.column);
    });

    if (selectedColumns.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Columns Selected",
            text: "Please select at least one column.",
            confirmButtonColor: "#2563eb"
        });
        return;
    }

    const params = currentPrintParams;
    const columnModal = bootstrap.Modal.getInstance(document.getElementById('columnSelectionModal'));
    columnModal.hide();

    Swal.fire({
        title: 'Generating PDF',
        html: `
            <p><strong>Class:</strong> ${document.getElementById('idclass').options[document.getElementById('idclass').selectedIndex].text}</p>
            <p><strong>Session:</strong> ${document.getElementById('idsession').options[document.getElementById('idsession').selectedIndex].text}</p>
            <p><strong>Term:</strong> ${document.getElementById('idterm').options[document.getElementById('idterm').selectedIndex].text}</p>
            <p><strong>Students:</strong> ${params.studentIds.length}</p>
            <p><strong>Columns:</strong> ${selectedColumns.length}</p>
        `,
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("studentreports.exportClassResultsPdf") }}';
    form.target = '_blank';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);

    const classIdInput = document.createElement('input');
    classIdInput.type = 'hidden';
    classIdInput.name = 'schoolclassid';
    classIdInput.value = params.classId;
    form.appendChild(classIdInput);

    const sessionIdInput = document.createElement('input');
    sessionIdInput.type = 'hidden';
    sessionIdInput.name = 'sessionid';
    sessionIdInput.value = params.sessionId;
    form.appendChild(sessionIdInput);

    const termIdInput = document.createElement('input');
    termIdInput.type = 'hidden';
    termIdInput.name = 'termid';
    termIdInput.value = params.termId;
    form.appendChild(termIdInput);

    params.studentIds.forEach((id, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `studentIds[${index}]`;
        input.value = id;
        form.appendChild(input);
    });

    selectedColumns.forEach((col, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `selectedColumns[${index}]`;
        input.value = col;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => Swal.close(), 2000);
});

function setupPaginationLinks() {
    document.querySelectorAll('#pagination-container a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            if (url && !this.classList.contains('disabled')) {
                showLoading(true);
                axios.get(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => {
                    document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center">No students found.</td></tr>';
                    document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
                    document.getElementById('studentcount').innerText = response.data.studentCount || '0';
                    setupPaginationLinks();
                    setupCheckboxListeners();
                    updateStats();
                    updatePrintButtonVisibility();
                    showLoading(false);
                }).catch(error => {
                    console.error("Pagination error:", error);
                    showLoading(false);
                    Swal.fire({ icon: "error", title: "Error", text: "Failed to load page." });
                });
            }
        });
    });
}

function setupCheckboxListeners() {
    const checkAll = document.getElementById("checkAll");
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');

    if (checkAll) {
        checkAll.addEventListener("change", function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const row = checkbox.closest("tr");
                if (row) row.classList.toggle("table-active", this.checked);
            });
            updatePrintButtonVisibility();
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.removeEventListener("change", checkbox._listener);
        checkbox._listener = function() {
            const row = this.closest("tr");
            if (row) row.classList.toggle("table-active", this.checked);
            const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
            const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]').length;
            if (checkAll) checkAll.checked = checkedCount === allCheckboxes && allCheckboxes > 0;
            updatePrintButtonVisibility();
            updateSelectionAlert();
        };
        checkbox.addEventListener("change", checkbox._listener);
    });
}

function enlargeImage(src) {
    const modalImage = document.getElementById('enlargedImage');
    modalImage.src = src || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
    const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
    modal.show();
}

document.addEventListener("DOMContentLoaded", function() {
    setupCheckboxListeners();
    updateStats();

    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const termSelect = document.getElementById("idterm");

    classSelect.addEventListener("change", function() {
        updateSearchButtonVisibility();
        termSelect.value = 'ALL';
    });

    sessionSelect.addEventListener("change", function() {
        updateSearchButtonVisibility();
        termSelect.value = 'ALL';
    });

    termSelect.addEventListener("change", function() {
        updatePrintButtonVisibility();
        if (this.value !== 'ALL') filterData();
    });

    const searchInput = document.getElementById("searchInput");
    searchInput.addEventListener("keypress", function(e) {
        if (e.key === 'Enter') filterData();
    });
});
</script>
@endsection
