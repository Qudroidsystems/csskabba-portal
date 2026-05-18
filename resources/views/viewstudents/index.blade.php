@extends('layouts.master')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
:root {
    --cb-navy:      #0f2342;
    --cb-teal:      #0d9488;
    --cb-sky:       #0ea5e9;
    --cb-amber:     #f59e0b;
    --cb-rose:      #f43f5e;
    --cb-green:     #22c55e;
    --cb-muted:     #64748b;
    --cb-border:    #e2e8f0;
    --cb-surface:   #f8fafc;
    --cb-white:     #ffffff;
    --cb-radius:    14px;
    --cb-shadow:    0 4px 16px rgba(15,35,66,.10);
    --cb-shadow-lg: 0 8px 32px rgba(15,35,66,.14);
}

*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: #f1f5f9; }

/* Hero Section */
.cb-hero {
    background: linear-gradient(135deg, var(--cb-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--cb-radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.cb-hero::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
    border-radius: 50%;
}
.cb-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 8px;
}
.cb-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.72);
    margin: 0;
}
.cb-hero .meta-pills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.cb-meta-pill {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Stat Cards */
.cb-stat {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.cb-stat:hover {
    transform: translateY(-2px);
    box-shadow: var(--cb-shadow);
}
.cb-stat .stat-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: var(--cb-radius) var(--cb-radius) 0 0;
}
.cb-stat .stat-value {
    font-size: 30px;
    font-weight: 700;
    color: var(--cb-navy);
    line-height: 1;
    margin-top: 8px;
}
.cb-stat .stat-label {
    font-size: 12px;
    color: var(--cb-muted);
    margin-top: 5px;
    font-weight: 500;
}
.cb-stat .stat-ico {
    font-size: 36px;
    opacity: .08;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
}

/* Filter Panel */
.filter-panel {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    padding: 20px 24px;
    margin-bottom: 28px;
    box-shadow: var(--cb-shadow);
}
.filter-panel h6 {
    font-size: 13px;
    font-weight: 700;
    color: var(--cb-navy);
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}
.filter-item label {
    font-size: 11px;
    font-weight: 600;
    color: var(--cb-muted);
    margin-bottom: 6px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.filter-item input, .filter-item select {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--cb-border);
    border-radius: 10px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.15s;
    background: var(--cb-surface);
}
.filter-item input:focus, .filter-item select:focus {
    border-color: var(--cb-teal);
    outline: none;
    box-shadow: 0 0 0 3px rgba(13,148,136,.1);
}
.btn-filter {
    background: var(--cb-teal);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    justify-content: center;
}
.btn-filter:hover {
    background: #0f766e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13,148,136,0.3);
}
.btn-reset {
    background: #f1f5f9;
    color: var(--cb-muted);
    border: 1.5px solid var(--cb-border);
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    justify-content: center;
}
.btn-reset:hover {
    background: #e2e8f0;
    border-color: var(--cb-teal);
    color: var(--cb-teal);
}

/* Main Card */
.cb-card {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    box-shadow: var(--cb-shadow);
    overflow: hidden;
}
.cb-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--cb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    background: linear-gradient(to right, #f8fafc, #f0fdf9);
}
.cb-card-header h5 {
    font-size: 15px;
    font-weight: 700;
    color: var(--cb-navy);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.class-info-badge {
    background: var(--cb-teal);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}
.btn-back {
    background: #f1f5f9;
    color: var(--cb-muted);
    border: 1px solid var(--cb-border);
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.btn-back:hover {
    background: #e2e8f0;
    color: var(--cb-teal);
    transform: translateY(-1px);
}

/* Table Styles */
.cb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.cb-table thead th {
    background: var(--cb-navy);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 11.5px;
    white-space: nowrap;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,.08);
}
.cb-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--cb-border);
}
.cb-table tbody tr:hover td {
    background: #f0fdf9;
}
.cb-table tbody tr:last-child td {
    border-bottom: none;
}

/* Student Avatar */
.student-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--cb-border);
    cursor: pointer;
    transition: all 0.2s;
}
.student-avatar:hover {
    border-color: var(--cb-teal);
    transform: scale(1.05);
}
.student-name-link {
    font-weight: 600;
    color: var(--cb-navy);
    text-decoration: none;
    transition: color 0.15s;
}
.student-name-link:hover {
    color: var(--cb-teal);
}

/* Gender Badge */
.gender-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.gender-male {
    background: #dbeafe;
    color: #1e40af;
}
.gender-female {
    background: #fce7f3;
    color: #be185d;
}

/* Action Button */
.btn-view-profile {
    background: linear-gradient(135deg, #e0f2fe, #bae6fd);
    color: #0369a1;
    border: 1px solid #7dd3fc;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.btn-view-profile:hover {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(14,165,233,0.25);
}

/* Chart Card */
.chart-card {
    background: var(--cb-white);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius);
    margin-bottom: 28px;
    overflow: hidden;
}
.chart-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--cb-border);
    background: linear-gradient(to right, #f8fafc, #f0fdf9);
}
.chart-header h5 {
    font-size: 14px;
    font-weight: 700;
    color: var(--cb-navy);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chart-body {
    padding: 20px;
}

/* Pagination */
.pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 20px;
}
.page-item {
    display: inline-flex;
    padding: 8px 12px;
    border: 1px solid var(--cb-border);
    border-radius: 8px;
    color: var(--cb-navy);
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.15s;
    background: white;
    cursor: pointer;
}
.page-item:hover:not(.disabled) {
    background: var(--cb-teal);
    color: white;
    border-color: var(--cb-teal);
}
.page-item.active {
    background: var(--cb-teal);
    color: white;
    border-color: var(--cb-teal);
}
.page-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px;
}
.empty-state i {
    font-size: 64px;
    color: #cbd5e1;
    margin-bottom: 16px;
    display: block;
}
.empty-state h6 {
    font-size: 18px;
    color: var(--cb-navy);
    margin-bottom: 8px;
}
.empty-state p {
    color: var(--cb-muted);
    font-size: 13px;
}

/* Modal Styles */
#imageViewModal .modal-content {
    border-radius: var(--cb-radius);
    overflow: hidden;
}
#imageViewModal .modal-header {
    background: linear-gradient(135deg, var(--cb-navy), var(--cb-teal));
    color: white;
    border: none;
}
#imageViewModal .modal-header .btn-close {
    filter: invert(1);
}
#imageViewModal .modal-body img {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
    border-radius: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .cb-hero { padding: 24px 20px; }
    .cb-hero h1 { font-size: 22px; }
    .filter-grid { grid-template-columns: 1fr; }
    .cb-table thead { display: none; }
    .cb-table tbody td {
        display: block;
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    .cb-table tbody td:before {
        content: attr(data-label);
        position: absolute;
        left: 16px;
        width: 45%;
        text-align: left;
        font-weight: 600;
        color: var(--cb-navy);
        font-size: 11px;
    }
    .pagination-wrap {
        justify-content: center;
        flex-wrap: wrap;
    }
}

/* Animation */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.cb-card, .cb-stat, .chart-card {
    animation: fadeInUp 0.4s ease;
}

/* Toast Notification */
.cb-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    z-index: 9999;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
}
.cb-toast-success { background: #059669; }
.cb-toast-error { background: #dc2626; }
.cb-toast-info { background: #3b82f6; }
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

<!-- Hero Section -->
<div class="cb-hero">
    <h1><i class="ri-group-line me-2"></i>My Class Students</h1>
    <p>View and manage students in your assigned class.</p>
    <div class="meta-pills">
        <span class="cb-meta-pill"><i class="ri-building-line"></i>{{ $schoolclass[0]->schoolclass ?? 'N/A' }} {{ $schoolclass[0]->arm ?? '' }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-line"></i>{{ $term[0]->term ?? 'N/A' }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-event-line"></i>{{ $session[0]->session ?? 'N/A' }}</span>
        <span class="cb-meta-pill"><i class="ri-user-line"></i>{{ Auth::user()->name }}</span>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
            <div class="stat-ico"><i class="ri-group-line"></i></div>
            <div class="stat-value">{{ $allstudents->count() }}</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-user-line"></i></div>
            <div class="stat-value text-info">{{ $male ?? 0 }}</div>
            <div class="stat-label">Male Students</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-rose),#f43f5e);"></div>
            <div class="stat-ico"><i class="ri-user-line"></i></div>
            <div class="stat-value text-danger">{{ $female ?? 0 }}</div>
            <div class="stat-label">Female Students</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-green),#4ade80);"></div>
            <div class="stat-ico"><i class="ri-award-line"></i></div>
            <div class="stat-value text-success" id="avgAge">—</div>
            <div class="stat-label">Avg Age</div>
        </div>
    </div>
</div>

<!-- Students by Gender Chart -->
<div class="chart-card">
    <div class="chart-header">
        <h5><i class="ri-bar-chart-line" style="color: var(--cb-teal);"></i> Students by Gender Distribution</h5>
    </div>
    <div class="chart-body">
        <canvas id="studentsByGenderChart" height="80"></canvas>
        <div id="chartError" class="text-danger text-center d-none">Failed to load chart. Please check data.</div>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel">
    <h6><i class="ri-filter-line" style="color:var(--cb-teal)"></i> Filter Students</h6>
    <div class="filter-grid">
        <div class="filter-item">
            <label><i class="ri-search-line"></i> Search</label>
            <input type="text" id="searchInput" class="search" placeholder="Name or Admission No...">
        </div>
        <div class="filter-item">
            <label><i class="ri-user-line"></i> Gender</label>
            <select id="idGender">
                <option value="all">All Genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="filter-item">
            <label><i class="ri-id-card-line"></i> Admission No</label>
            <select id="idAdmissionNo">
                <option value="all">All Admission Numbers</option>
                @foreach ($allstudents as $student)
                    <option value="{{ $student->admissionno }}">{{ $student->admissionno }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <button class="btn-filter" onclick="filterData()"><i class="ri-search-line"></i> Apply Filters</button>
        </div>
        <div class="filter-item">
            <button class="btn-reset" onclick="resetFilters()"><i class="ri-refresh-line"></i> Reset</button>
        </div>
    </div>
</div>

<!-- Students Table Card -->
<div class="cb-card">
    <div class="cb-card-header">
        <h5>
            <i class="ri-table-alt-line" style="color:var(--cb-teal)"></i>
            Student List
            <span class="class-info-badge" id="studentCount">{{ $allstudents->count() }} Students</span>
        </h5>
        <a href="{{ route('myclass.index') }}" class="btn-back">
            <i class="ri-arrow-left-line"></i> Back to Classes
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="cb-table" id="studentListTable">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Gender</th>
                    <th style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody class="list" id="studentTableBody">
                @forelse ($allstudents as $key => $student)
                @php
                    $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                    $fullName = trim($student->firstname . ' ' . $student->lastname . ' ' . $student->othername);
                    $genderClass = $student->gender == 'Male' ? 'gender-male' : 'gender-female';
                    $genderIcon = $student->gender == 'Male' ? 'ri-men-line' : 'ri-women-line';
                @endphp
                <tr>
                    <td data-label="#" class="sn" style="font-weight: 600; color: var(--cb-navy);">{{ $key + 1 }}</td>
                    <td data-label="Admission No" class="admissionno">{{ $student->admissionno }}</td>
                    <td data-label="Student Name" class="name">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $student->picture ? asset('storage/student_avatars/' . basename($student->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                 alt="{{ $fullName }}"
                                 class="student-avatar"
                                 data-bs-toggle="modal"
                                 data-bs-target="#imageViewModal"
                                 data-image="{{ $student->picture ? asset('storage/student_avatars/' . basename($student->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                 data-name="{{ $fullName }}"
                                 data-admission="{{ $student->admissionno }}"
                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                            <a href="{{ route('myclass.studentpersonalityprofile', [$student->stid, $schoolclassid, $sessionid, $termid]) }}" class="student-name-link">
                                {{ $fullName }}
                            </a>
                        </div>
                    </td>
                    <td data-label="Gender">
                        <span class="gender-badge {{ $genderClass }}">
                            <i class="{{ $genderIcon }}"></i> {{ $student->gender }}
                        </span>
                    </td>
                    <td data-label="Action">
                        <a href="{{ route('myclass.studentpersonalityprofile', [$student->stid, $schoolclassid, $sessionid, $termid]) }}" class="btn-view-profile">
                            <i class="ph-eye"></i> View Profile
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="ri-inbox-line"></i>
                            <h6>No Students Found</h6>
                            <p>No students are currently enrolled in this class.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="padding: 20px 24px; border-top: 1px solid var(--cb-border); background: #fafbfc;">
        <div class="row align-items-center">
            <div class="col-sm">
                <div class="text-muted" style="font-size: 12px;">
                    <i class="ri-information-line me-1"></i>
                    Showing <span class="fw-semibold text-dark" id="showingCount">{{ $allstudents->count() }}</span> of
                    <span class="fw-semibold text-dark">{{ $allstudents->count() }}</span> students
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="pagination-wrap" id="pagination-container">
                    <!-- Pagination will be handled by List.js -->
                </div>
            </div>
        </div>
    </div>
</div>

</div></div></div>

<!-- Image View Modal -->
<div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-image-line me-2"></i>Student Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="enlargedImage" src="" alt="Student Picture" class="img-fluid" />
                <div id="modalStudentName" class="mt-3 fw-semibold" style="color: var(--cb-navy);"></div>
                <div id="modalStudentAdmission" class="text-muted small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Initialize List.js for pagination and filtering
    const studentListElement = document.getElementById("studentListTable");
    if (!studentListElement) {
        console.error("Student list table not found");
        return;
    }

    const options = {
        valueNames: ["sn", "admissionno", "name", "gender"],
        page: 10,
        pagination: {
            innerWindow: 2,
            outerWindow: 1,
            left: 0,
            right: 0,
            item: '<li class="page-item"><a class="page-link" href="#"></a></li>'
        }
    };

    let studentList;
    try {
        studentList = new List("studentListTable", options);
        console.log("List.js initialized successfully");
    } catch (error) {
        console.error("Error initializing List.js:", error);
        return;
    }

    // Update showing count on list update
    studentList.on("updated", function (e) {
        const showingCount = document.getElementById("showingCount");
        if (showingCount) {
            showingCount.innerText = e.matchingItems.length;
        }

        // Update pagination display
        const paginationContainer = document.getElementById("pagination-container");
        if (paginationContainer && studentList.page) {
            // Pagination is handled by List.js automatically
        }
    });

    // Initialize Chart.js for Students by Gender
    const ctx = document.getElementById("studentsByGenderChart")?.getContext("2d");
    const chartError = document.getElementById("chartError");
    const maleCount = {{ $male ?? 0 }};
    const femaleCount = {{ $female ?? 0 }};

    if (ctx) {
        try {
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ["Male", "Female"],
                    datasets: [{
                        label: "Number of Students",
                        data: [maleCount, femaleCount],
                        backgroundColor: ["#0ea5e9", "#f43f5e"],
                        borderRadius: 8,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: "#e2e8f0" },
                            title: { display: true, text: "Number of Students", font: { size: 12 } }
                        },
                        x: {
                            grid: { display: false },
                            title: { display: true, text: "Gender", font: { size: 12 } }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: "#0f2342", titleColor: "#fff", bodyColor: "#e2e8f0" }
                    }
                }
            });
        } catch (error) {
            console.error("Error initializing chart:", error);
            if (chartError) chartError.classList.remove("d-none");
        }
    } else {
        console.error("Canvas element not found");
        if (chartError) chartError.classList.remove("d-none");
    }

    // Calculate and display average age
    function calculateAverageAge() {
        let totalAge = 0;
        let count = 0;
        @foreach ($allstudents as $student)
            @if($student->dateofbirth)
                let age = new Date().getFullYear() - new Date('{{ $student->dateofbirth }}').getFullYear();
                totalAge += age;
                count++;
            @endif
        @endforeach
        if (count > 0) {
            document.getElementById('avgAge').textContent = Math.round(totalAge / count);
        } else {
            document.getElementById('avgAge').textContent = '—';
        }
    }
    calculateAverageAge();
});

// Filter function
window.filterData = function () {
    const searchInput = document.getElementById("searchInput")?.value.toLowerCase() || "";
    const genderSelect = document.getElementById("idGender");
    const admissionNoSelect = document.getElementById("idAdmissionNo");
    const selectedGender = genderSelect?.value || "all";
    const selectedAdmissionNo = admissionNoSelect?.value || "all";

    // Get List.js instance
    const table = document.getElementById("studentListTable");
    if (table && table.list) {
        table.list.filter(function (item) {
            const name = item.values().name.toLowerCase();
            const admissionno = item.values().admissionno.toLowerCase();
            const gender = item.values().gender;

            const searchMatch = name.includes(searchInput) || admissionno.includes(searchInput);
            const genderMatch = selectedGender === "all" || gender === selectedGender;
            const admissionMatch = selectedAdmissionNo === "all" || item.values().admissionno === selectedAdmissionNo;

            return searchMatch && genderMatch && admissionMatch;
        });
    }
};

// Reset filters
window.resetFilters = function () {
    document.getElementById("searchInput").value = "";
    const genderSelect = document.getElementById("idGender");
    const admissionNoSelect = document.getElementById("idAdmissionNo");
    if (genderSelect) genderSelect.value = "all";
    if (admissionNoSelect) admissionNoSelect.value = "all";
    filterData();
};

// Image modal handling
const imageViewModal = document.getElementById('imageViewModal');
if (imageViewModal) {
    imageViewModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imageSrc = button.getAttribute('data-image') || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
        const studentName = button.getAttribute('data-name') || 'Student';
        const admissionNo = button.getAttribute('data-admission') || '';

        const modalImage = this.querySelector('#enlargedImage');
        const modalName = this.querySelector('#modalStudentName');
        const modalAdmission = this.querySelector('#modalStudentAdmission');

        modalImage.src = imageSrc;
        modalName.textContent = studentName;
        modalAdmission.textContent = admissionNo ? 'Admission No: ' + admissionNo : '';

        modalImage.onerror = function() {
            this.src = '{{ asset('storage/student_avatars/unnamed.jpg') }}';
        };
    });
}

// Enter key support for search
document.getElementById("searchInput")?.addEventListener("keypress", function(e) {
    if (e.key === "Enter") filterData();
});

// Toast notification helper
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'cb-toast cb-toast-' + (type || 'success');
    toast.innerHTML = '<i class="ri-' + (type === 'success' ? 'checkbox-circle-fill' : 'information-fill') + '"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

@endsection
