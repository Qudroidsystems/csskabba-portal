@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --report-primary: #1e3a5f;
    --report-accent:  #2563eb;
    --report-success: #16a34a;
    --report-warning: #d97706;
    --report-danger:  #dc2626;
    --report-muted:   #6b7280;
    --report-border:  #e2e8f0;
    --report-bg:      #f8fafc;
    --report-radius:  12px;
    --report-shadow:  0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ──────────────────────────────────────────────── */
.report-hero {
    background: linear-gradient(135deg, var(--report-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--report-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.report-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.report-hero::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -30px;
    width: 260px;
    height: 260px;
    background: rgba(255,255,255,.03);
    border-radius: 50%;
}
.report-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
}
.report-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
    position: relative;
}

/* ── Stat cards ────────────────────────────────────────── */
.stat-card {
    background: #fff;
    border: 1px solid var(--report-border);
    border-radius: var(--report-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--report-shadow);
}
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--report-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: var(--report-muted);
    margin-top: 4px;
}
.stat-card .stat-icon {
    font-size: 32px;
    opacity: .12;
    float: right;
    margin-top: -8px;
}

/* ── Table ─────────────────────────────────────────────── */
.report-table th {
    background: var(--report-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.report-table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--report-border);
    font-size: 13px;
}
.report-table tr:hover td {
    background: #eff6ff;
}

/* ── Badges ────────────────────────────────────────────── */
.report-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.report-badge-male {
    background: #dbeafe;
    color: #2563eb;
}
.report-badge-female {
    background: #fce7f3;
    color: #db2777;
}
.report-badge-other {
    background: #f3f4f6;
    color: #6b7280;
}

/* ── DataTables overrides ──────────────────────────────── */
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--report-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
    transition: border .15s;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--report-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid var(--report-border);
    border-radius: 8px;
    padding: 6px 10px;
    margin: 0 6px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_info {
    font-size: 13px;
    color: var(--report-muted);
}
.dataTables_wrapper .paginate_button {
    border-radius: 6px !important;
    font-size: 13px !important;
    padding: 4px 10px !important;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background: var(--report-accent) !important;
    border-color: var(--report-accent) !important;
    color: #fff !important;
}

/* ── Modal ─────────────────────────────────────────────── */
#columnSelectionModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.modal-hero-bar {
    background: linear-gradient(135deg, var(--report-primary) 0%, #2563eb 100%);
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

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.form-control, .form-select {
    border: 1.5px solid var(--report-border);
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 14px;
    transition: border .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--report-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* ── Alert ─────────────────────────────────────────────── */
.selection-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    min-width: 300px;
    max-width: 90%;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    font-size: 13px;
    font-weight: 500;
}

/* ── Image preview ─────────────────────────────────────── */
.student-preview-img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform .2s;
}
.student-preview-img:hover {
    transform: scale(1.1);
}

/* ── Card styles ───────────────────────────────────────── */
.card {
    border: none;
    border-radius: var(--report-radius);
    box-shadow: var(--report-shadow);
    overflow: hidden;
}
.card-header {
    background: #fff;
    border-bottom: 1px solid var(--report-border);
    padding: 16px 20px;
}
.card-header h5 {
    font-weight: 600;
    color: var(--report-primary);
}
.card-body {
    padding: 20px;
}

/* ── Column selection cards ────────────────────────────── */
.column-group-card {
    margin-bottom: 20px;
    border: 1px solid var(--report-border);
    border-radius: 12px;
    overflow: hidden;
}
.column-group-card .card-header {
    background: #f8fafc;
    padding: 12px 16px;
}
.column-group-card .card-body {
    padding: 16px;
}

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 768px) {
    .report-hero {
        padding: 20px;
    }
    .report-hero h1 {
        font-size: 18px;
    }
    .stat-card .stat-value {
        font-size: 22px;
    }
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Dismissible Alert Notification -->
            <div id="selectionAlert" class="selection-alert" style="display: none;">
                <span id="selectionAlertText">No selections made.</span>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="float: right; margin-top: 2px;"></button>
            </div>

            {{-- Hero Section --}}
            <div class="report-hero">
                <h1><i class="ri-file-list-3-line me-2"></i>{{ $pagetitle }}</h1>
                <p>Generate and manage student terminal reports, progress summaries, and academic transcripts.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('status') ?? session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="studentList">
                {{-- Filter Card --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label">Select Class</label>
                                <select class="form-select" id="idclass" name="schoolclassid">
                                    <option value="ALL">— Select Class —</option>
                                    @foreach ($schoolclasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label">Select Session</label>
                                <select class="form-select" id="idsession" name="sessionid">
                                    <option value="ALL">— Select Session —</option>
                                    @foreach ($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xxl-3 col-sm-6" id="termSelectContainer" style="display: none;">
                                <label class="form-label">Select Term</label>
                                <select class="form-select" id="idterm" name="termid">
                                    <option value="ALL">— Select Term —</option>
                                    <option value="1">First Term</option>
                                    <option value="2">Second Term</option>
                                    <option value="3">Third Term</option>
                                </select>
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label">Search Students</label>
                                <div class="search-box position-relative">
                                    <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search by name or admission number...">
                                    <i class="ri-search-line search-icon position-absolute" style="right: 12px; top: 12px; color: #94a3b8;"></i>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-sm-6 d-flex gap-2 align-items-end">
                                <button type="button" class="btn btn-primary w-50" id="searchBtn" style="display: none;" onclick="filterData()">
                                    <i class="ri-search-line me-1"></i> Search
                                </button>
                                <button type="button" class="btn btn-success w-50" id="printAllBtn" style="display: none;" onclick="printAllResults()">
                                    <i class="ri-printer-line me-1"></i> Print Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Students Table Card --}}
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-user-settings-line me-2"></i>Student Records
                            <span class="badge bg-primary ms-2" id="studentcount">{{ $allstudents ? $allstudents->total() : 0 }}</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                                <i class="ri-refresh-line me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table report-table mb-0" id="studentListTable">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                                <label class="form-check-label" for="checkAll"></label>
                                            </div>
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
                                    </tr>
                                </thead>
                                <tbody id="studentTableBody">
                                    @include('studentreports.partials.student_rows')
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top" id="pagination-container">
                            <div class="text-muted small">
                                Showing {{ $allstudents ? $allstudents->firstItem() : 0 }} to {{ $allstudents ? $allstudents->lastItem() : 0 }} of {{ $allstudents ? $allstudents->total() : 0 }} entries
                            </div>
                            <div>
                                {{ $allstudents ? $allstudents->links('pagination::bootstrap-5') : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image View Modal -->
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content">
                            <div class="modal-hero-bar">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                <h5><i class="ri-image-line me-2"></i>Student Photo</h5>
                            </div>
                            <div class="modal-body text-center p-4">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid rounded" style="max-height: 400px;" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column Selection Modal -->
                <div class="modal fade" id="columnSelectionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-hero-bar">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                <h5><i class="ri-layout-column-line me-2"></i>Select Columns for PDF Report</h5>
                            </div>
                            <div class="modal-body p-4">
                                <div id="columnSelectionContent">
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        Select the columns you want to include in the PDF report. All selected columns will appear in the generated document.
                                    </div>
                                    <div id="columnSelectionLoader" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading column options...</p>
                                    </div>
                                    <div id="columnSelectionForm" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <!-- Student Information Section -->
                                                <div class="column-group-card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0"><i class="ri-user-info-line me-2"></i>Student Information</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="studentInfoColumns"></div>
                                                    </div>
                                                </div>

                                                <!-- Assessments Section -->
                                                <div class="column-group-card">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0"><i class="ri-graduation-cap-line me-2"></i>Assessments</h6>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="selectAllAssessments">
                                                            <label class="form-check-label small" for="selectAllAssessments">Select All</label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="assessmentColumns"></div>
                                                    </div>
                                                </div>

                                                <!-- Scores Section -->
                                                <div class="column-group-card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0"><i class="ri-bar-chart-2-line me-2"></i>Scores & Metrics</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="scoreColumns"></div>
                                                    </div>
                                                </div>

                                                <!-- GPA/CGPA Section -->
                                                <div class="column-group-card">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0"><i class="ri-calculator-line me-2"></i>GPA/CGPA Metrics</h6>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="selectAllGPAMetrics">
                                                            <label class="form-check-label small" for="selectAllGPAMetrics">Select All</label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="gpaColumns"></div>
                                                    </div>
                                                </div>

                                                <!-- Other Information -->
                                                <div class="column-group-card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0"><i class="ri-more-2-line me-2"></i>Other Information</h6>
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
                            <div class="modal-footer border-0 px-4 pb-4">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveColumnSelection" disabled>
                                    <i class="ri-file-pdf-line me-1"></i>Generate PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    console.log("Student Report Index loaded at", new Date().toISOString());

    function updateSelectionAlert() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const selectionAlert = document.getElementById("selectionAlert");
        const selectionAlertText = document.getElementById("selectionAlertText");

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
        alertText.push(`Selected: ${checkedCheckboxes.length} student(s)`);

        if (classSelect.value !== 'ALL' && sessionSelect.value !== 'ALL') {
            selectionAlert.style.display = 'block';
            selectionAlertText.innerText = alertText.join(' | ');
            setTimeout(() => {
                if (selectionAlert.style.display === 'block') {
                    selectionAlert.style.opacity = '0.9';
                }
            }, 3000);
        } else {
            selectionAlert.style.display = 'none';
        }
    }

    function updateSearchButtonVisibility() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const searchBtn = document.getElementById("searchBtn");
        const termSelectContainer = document.getElementById("termSelectContainer");
        const printAllBtn = document.getElementById("printAllBtn");

        searchBtn.style.display = (classSelect.value !== 'ALL' && sessionSelect.value !== 'ALL') ? 'block' : 'none';
        termSelectContainer.style.display = 'none';
        printAllBtn.style.display = 'none';
        updateSelectionAlert();
    }

    function updateTermSelectVisibility() {
        const termSelectContainer = document.getElementById("termSelectContainer");
        const printAllBtn = document.getElementById("printAllBtn");
        const studentCount = parseInt(document.getElementById("studentcount").innerText);

        termSelectContainer.style.display = studentCount > 0 ? 'block' : 'none';
        printAllBtn.style.display = 'none';
        updateSelectionAlert();
    }

    function updatePrintButtonVisibility() {
        const termSelect = document.getElementById("idterm");
        const printAllBtn = document.getElementById("printAllBtn");
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');

        printAllBtn.style.display = (termSelect.value !== 'ALL' && checkedCheckboxes.length > 0) ? 'block' : 'none';
        updateSelectionAlert();
    }

    function filterData() {
        console.log("filterData called");
        if (typeof axios === 'undefined') {
            console.error("Axios is not defined");
            Swal.fire({
                icon: "error",
                title: "Configuration Error",
                text: "Axios library is missing. Please refresh the page.",
                showConfirmButton: true
            });
            return;
        }

        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const searchInput = document.getElementById("searchInput");

        if (!classSelect || !sessionSelect || !termSelect) {
            console.error("Required elements not found");
            return;
        }

        const classValue = classSelect.value;
        const sessionValue = sessionSelect.value;
        const termValue = termSelect.value;
        const searchValue = searchInput ? searchInput.value.trim() : '';

        if (classValue === 'ALL' || sessionValue === 'ALL') {
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">Select class and session to view students</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
            document.getElementById('printAllBtn').style.display = 'none';
            document.getElementById('termSelectContainer').style.display = 'none';
            updateSelectionAlert();
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class and session.",
                showConfirmButton: true,
                confirmButtonColor: "#2563eb"
            });
            return;
        }

        console.log("Sending AJAX request with:", { search: searchValue, schoolclassid: classValue, sessionid: sessionValue, termid: termValue });

        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading students...</p></td></tr>';

        axios.get('{{ route("studentreports.index") }}', {
            params: {
                search: searchValue,
                schoolclassid: classValue,
                sessionid: sessionValue,
                termid: termValue
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("AJAX response received");

            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center py-4 text-muted">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            setupPaginationLinks();
            setupCheckboxListeners();
            updateTermSelectVisibility();
            updatePrintButtonVisibility();

            if (response.data.tableBody && response.data.tableBody.includes('No students found')) {
                Swal.fire({
                    icon: "info",
                    title: "No Results",
                    text: "No students found for the selected class and session.",
                    showConfirmButton: true,
                    confirmButtonColor: "#2563eb"
                });
            }
        }).catch(function (error) {
            console.error("AJAX error:", error);
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                showConfirmButton: true,
                confirmButtonColor: "#2563eb"
            });
        });
    }

    function printAllResults() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const classValue = classSelect.value;
        const sessionValue = sessionSelect.value;
        const termValue = termSelect.value;
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const selectedStudentIds = Array.from(checkedCheckboxes).map(checkbox => checkbox.value);

        if (classValue === 'ALL' || sessionValue === 'ALL' || termValue === 'ALL') {
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class, session, and term.",
                showConfirmButton: true,
                confirmButtonColor: "#2563eb"
            });
            return;
        }

        if (selectedStudentIds.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Students Selected",
                text: "Please select at least one student to generate the PDF.",
                showConfirmButton: true,
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

        window.currentPrintParams = {
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
                bootstrap.Modal.getInstance(document.getElementById('columnSelectionModal')).hide();
            }
        })
        .catch(error => {
            console.error('Error loading column options:', error);
            Swal.fire({
                icon: "error",
                title: "Network Error",
                text: "Failed to load column options. Please try again.",
                confirmButtonColor: "#2563eb"
            });
            bootstrap.Modal.getInstance(document.getElementById('columnSelectionModal')).hide();
        });
    }

    function populateColumnOptions(columns) {
        document.getElementById('studentInfoColumns').innerHTML = '';
        document.getElementById('assessmentColumns').innerHTML = '';
        document.getElementById('scoreColumns').innerHTML = '';
        document.getElementById('gpaColumns').innerHTML = '';
        document.getElementById('otherColumns').innerHTML = '';

        if (columns.student_info) {
            Object.entries(columns.student_info).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox"
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('studentInfoColumns').appendChild(colDiv);
            });
        }

        if (columns.assessments) {
            Object.entries(columns.assessments).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                const subText = config.has_sub_assessments ? '<br><small class="text-muted">Has sub-assessments</small>' : '';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox assessment-checkbox" type="checkbox"
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                            ${subText}
                        </label>
                    </div>
                `;
                document.getElementById('assessmentColumns').appendChild(colDiv);
            });
        }

        if (columns.scores) {
            Object.entries(columns.scores).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox"
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('scoreColumns').appendChild(colDiv);
            });
        }

        if (columns.gpa_metrics) {
            Object.entries(columns.gpa_metrics).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox gpa-checkbox" type="checkbox"
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('gpaColumns').appendChild(colDiv);
            });
        }

        if (columns.other) {
            Object.entries(columns.other).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox"
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('otherColumns').appendChild(colDiv);
            });
        }

        const selectAllAssessments = document.getElementById('selectAllAssessments');
        if (selectAllAssessments) {
            selectAllAssessments.addEventListener('change', function() {
                document.querySelectorAll('.assessment-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }

        const selectAllGPAMetrics = document.getElementById('selectAllGPAMetrics');
        if (selectAllGPAMetrics) {
            selectAllGPAMetrics.addEventListener('change', function() {
                document.querySelectorAll('.gpa-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }
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
                text: "Please select at least one column to include in the PDF.",
                showConfirmButton: true,
                confirmButtonColor: "#2563eb"
            });
            return;
        }

        const params = window.currentPrintParams;
        const columnModal = bootstrap.Modal.getInstance(document.getElementById('columnSelectionModal'));
        columnModal.hide();

        Swal.fire({
            title: 'Generating PDF',
            html: `
                <div class="text-start">
                    <p><strong>Class:</strong> ${document.getElementById('idclass').options[document.getElementById('idclass').selectedIndex].text}</p>
                    <p><strong>Session:</strong> ${document.getElementById('idsession').options[document.getElementById('idsession').selectedIndex].text}</p>
                    <p><strong>Term:</strong> ${document.getElementById('idterm').options[document.getElementById('idterm').selectedIndex].text}</p>
                    <p><strong>Students Selected:</strong> ${params.studentIds.length}</p>
                    <p><strong>Columns Selected:</strong> ${selectedColumns.length}</p>
                </div>
                <p class="mt-3">Generating PDF... Please wait.</p>
            `,
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
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

        const responseMethodInput = document.createElement('input');
        responseMethodInput.type = 'hidden';
        responseMethodInput.name = 'response_method';
        responseMethodInput.value = 'inline';
        form.appendChild(responseMethodInput);

        params.studentIds.forEach((id, index) => {
            const studentIdInput = document.createElement('input');
            studentIdInput.type = 'hidden';
            studentIdInput.name = `studentIds[${index}]`;
            studentIdInput.value = id;
            form.appendChild(studentIdInput);
        });

        selectedColumns.forEach((col, index) => {
            const colInput = document.createElement('input');
            colInput.type = 'hidden';
            colInput.name = `selectedColumns[${index}]`;
            colInput.value = col;
            form.appendChild(colInput);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(() => {
            Swal.close();
        }, 2000);
    });

    function setupPaginationLinks() {
        const paginationLinks = document.querySelectorAll('#pagination-container a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                if (url && !this.classList.contains('disabled')) {
                    loadPage(url);
                }
            });
        });
    }

    function loadPage(url) {
        console.log("Loading page:", url);
        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></td></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center py-4 text-muted">No students found.</td></table>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxListeners();
            updateTermSelectVisibility();
            updatePrintButtonVisibility();
        }).catch(function (error) {
            console.error("Page load error:", error);
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                confirmButtonColor: "#2563eb"
            });
        });
    }

    function setupCheckboxListeners() {
        const checkAll = document.getElementById("checkAll");
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');

        if (checkAll) {
            checkAll.removeEventListener('change', checkAllHandler);
            checkAll.addEventListener('change', checkAllHandler);
        }

        function checkAllHandler(e) {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = e.target.checked;
                const row = checkbox.closest("tr");
                if (row) row.classList.toggle("table-active", e.target.checked);
            });
            updatePrintButtonVisibility();
        }

        checkboxes.forEach(checkbox => {
            checkbox.removeEventListener('change', checkboxChangeHandler);
            checkbox.addEventListener('change', checkboxChangeHandler);
        });

        function checkboxChangeHandler(e) {
            const row = e.target.closest("tr");
            if (row) row.classList.toggle("table-active", e.target.checked);
            const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
            const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]').length;
            if (document.getElementById("checkAll")) {
                document.getElementById("checkAll").checked = checkedCount === allCheckboxes && allCheckboxes > 0;
            }
            updatePrintButtonVisibility();
        }
    }

    function viewImage(imageSrc) {
        const modalImage = document.getElementById('enlargedImage');
        if (modalImage) {
            modalImage.src = imageSrc || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM fully loaded");
        setupCheckboxListeners();

        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");

        if (classSelect) {
            classSelect.addEventListener("change", function () {
                updateSearchButtonVisibility();
                if (termSelect) termSelect.value = 'ALL';
                document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">Select class and session to view students.</td></tr>';
                document.getElementById('pagination-container').innerHTML = '';
                document.getElementById('studentcount').innerText = '0';
            });
        }

        if (sessionSelect) {
            sessionSelect.addEventListener("change", function () {
                updateSearchButtonVisibility();
                if (termSelect) termSelect.value = 'ALL';
                document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">Select class and session to view students.</td></table>';
                document.getElementById('pagination-container').innerHTML = '';
                document.getElementById('studentcount').innerText = '0';
            });
        }

        if (termSelect) {
            termSelect.addEventListener("change", function () {
                updatePrintButtonVisibility();
                if (this.value !== 'ALL') {
                    filterData();
                }
            });
        }

        const modal = document.getElementById('imageViewModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    const imageSrc = button.getAttribute('data-image');
                    const modalImage = modal.querySelector('#enlargedImage');
                    if (modalImage) {
                        modalImage.src = imageSrc || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
                    }
                }
            });
        }

        updateSearchButtonVisibility();
    });
</script>
@endsection
