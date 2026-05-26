@extends('layouts.master')
@section('content')
<style>
    /* =====================================================
       ENHANCED DASHBOARD ANIMATIONS & STYLES
       ===================================================== */

    /* Card entrance animations */
    @keyframes cardFadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes cardScaleIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes numberCount {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(79, 142, 247, 0.4);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(79, 142, 247, 0);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    /* Animated cards */
    .dashboard-card {
        animation: cardFadeInUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        border: none;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.15);
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.00s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.05s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.10s; }
    .dashboard-card:nth-child(4) { animation-delay: 0.15s; }
    .dashboard-card:nth-child(5) { animation-delay: 0.20s; }
    .dashboard-card:nth-child(6) { animation-delay: 0.25s; }

    /* Card gradients */
    .card-gradient-1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card-gradient-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .card-gradient-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .card-gradient-4 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .card-gradient-5 {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .card-gradient-6 {
        background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
    }

    /* Stat numbers */
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        animation: numberCount 0.8s ease forwards;
        background: linear-gradient(135deg, #1e293b, #334155);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    /* Icon containers */
    .stat-icon {
        width: 55px;
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        transition: all 0.3s ease;
        animation: float 3s ease-in-out infinite;
    }

    .dashboard-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    /* Progress bar animation */
    .progress-bar-animated {
        animation: shimmer 2s infinite linear;
        background: linear-gradient(90deg, #4f8ef7, #a855f7, #4f8ef7);
        background-size: 200% 100%;
    }

    /* Chart containers */
    .chart-container {
        animation: cardScaleIn 0.5s ease forwards;
        transition: all 0.3s ease;
    }

    .chart-container:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px -8px rgba(0, 0, 0, 0.1);
    }

    /* Activity timeline */
    .activity-item {
        animation: slideInRight 0.4s ease forwards;
        opacity: 0;
        animation-fill-mode: forwards;
    }

    .activity-item:nth-child(1) { animation-delay: 0.00s; }
    .activity-item:nth-child(2) { animation-delay: 0.05s; }
    .activity-item:nth-child(3) { animation-delay: 0.10s; }
    .activity-item:nth-child(4) { animation-delay: 0.15s; }
    .activity-item:nth-child(5) { animation-delay: 0.20s; }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Pulse animation for live indicators */
    .live-pulse {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #10b981;
        animation: pulseGlow 1.5s infinite;
        margin-right: 8px;
    }

    /* Custom scrollbar for charts */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.75rem;
        }
        .stat-icon {
            width: 45px;
            height: 45px;
        }
    }

    /* Loading skeleton animation */
    .skeleton-loading {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 fw-semibold">School Analytics Dashboard</h4>
                            <p class="text-muted mb-0 mt-1">
                                <span class="live-pulse"></span> Live data updated just now
                            </p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0 bg-transparent">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                                <li class="breadcrumb-item active">School Analytics</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            @hasrole('Super Admin')
                <!-- Stats Cards Row -->
                <div class="row">
                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Total Population</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="{{ $total_population }}">0</span>
                                        </h2>
                                        <p class="mb-0 mt-2">
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-arrow-up me-1"></i>{{ $population_percentage }}%
                                            </span>
                                            <span class="text-muted ms-1">vs last month</span>
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-primary bg-opacity-10">
                                        <i class="ph-users-three fs-2xl text-primary"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-animated" style="width: {{ min(100, $population_percentage * 5) }}%; background: linear-gradient(90deg, #4f8ef7, #a855f7);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Staff Count</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="{{ $staff_count }}">0</span>
                                        </h2>
                                        <p class="mb-0 mt-2">
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-arrow-up me-1"></i>{{ $staff_percentage }}%
                                            </span>
                                            <span class="text-muted ms-1">vs last month</span>
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-warning bg-opacity-10">
                                        <i class="ph-chalkboard-teacher fs-2xl text-warning"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-animated" style="width: {{ min(100, $staff_percentage * 5) }}%; background: linear-gradient(90deg, #f59e0b, #ef4444);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Male Students</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="{{ $gender_counts['Male'] }}">0</span>
                                        </h2>
                                        <p class="mb-0 mt-2">
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-arrow-up me-1"></i>{{ $male_percentage }}%
                                            </span>
                                            <span class="text-muted ms-1">vs last month</span>
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-info bg-opacity-10">
                                        <i class="ph-gender-male fs-2xl text-info"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-animated" style="width: {{ ($gender_counts['Male'] / max(1, $total_population)) * 100 }}%; background: linear-gradient(90deg, #0ea5e9, #06b6d4);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Female Students</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="{{ $gender_counts['Female'] }}">0</span>
                                        </h2>
                                        <p class="mb-0 mt-2">
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                <i class="bi bi-arrow-down me-1"></i>{{ abs($female_percentage) }}%
                                            </span>
                                            <span class="text-muted ms-1">vs last month</span>
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-danger bg-opacity-10">
                                        <i class="ph-gender-female fs-2xl text-danger"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-animated" style="width: {{ ($gender_counts['Female'] / max(1, $total_population)) * 100 }}%; background: linear-gradient(90deg, #ef4444, #f97316);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats Row -->
                <div class="row mt-3">
                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Old Students</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="{{ $status_counts['Old Student'] ?? 0 }}">0</span>
                                        </h2>
                                        <p class="text-muted mb-0 mt-2"><i class="bi bi-people me-1"></i> Returning students</p>
                                    </div>
                                    <div class="stat-icon bg-success bg-opacity-10">
                                        <i class="ph-star fs-2xl text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">New Students</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="{{ $status_counts['New Student'] ?? 0 }}">0</span>
                                        </h2>
                                        <p class="text-muted mb-0 mt-2"><i class="bi bi-person-plus me-1"></i> New enrollments</p>
                                    </div>
                                    <div class="stat-icon bg-secondary bg-opacity-10">
                                        <i class="ph-rocket-launch fs-2xl text-secondary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Classes</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="0">0</span>
                                        </h2>
                                        <p class="text-muted mb-0 mt-2"><i class="bi bi-grid-3x3 me-1"></i> Active classes</p>
                                    </div>
                                    <div class="stat-icon bg-purple bg-opacity-10">
                                        <i class="ph-graduation-cap fs-2xl text-purple"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-xs">Subjects</p>
                                        <h2 class="stat-number mb-0">
                                            <span class="counter-value" data-target="0">0</span>
                                        </h2>
                                        <p class="text-muted mb-0 mt-2"><i class="bi bi-book me-1"></i> Active subjects</p>
                                    </div>
                                    <div class="stat-icon bg-cyan bg-opacity-10">
                                        <i class="ph-book-open fs-2xl text-cyan"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mt-4">
                    <div class="col-xxl-6">
                        <div class="card chart-container">
                            <div class="card-header bg-transparent">
                                <h5 class="card-title mb-0">Population Distribution</h5>
                                <p class="text-muted small mb-0">Gender and status breakdown</p>
                            </div>
                            <div class="card-body">
                                <canvas id="populationChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-6">
                        <div class="card chart-container">
                            <div class="card-header bg-transparent">
                                <h5 class="card-title mb-0">Quick Stats Overview</h5>
                                <p class="text-muted small mb-0">Key metrics at a glance</p>
                            </div>
                            <div class="card-body">
                                <canvas id="statsChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity and Recent Data Row -->
                <div class="row mt-4">
                    <div class="col-xxl-7">
                        <div class="card chart-container">
                            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Recent Activity Timeline</h5>
                                    <p class="text-muted small mb-0">Latest updates from your school</p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#">Last 7 days</a></li>
                                        <li><a class="dropdown-item" href="#">Last 30 days</a></li>
                                        <li><a class="dropdown-item" href="#">This year</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="acitivity-timeline">
                                    <div class="activity-item d-flex align-items-start mb-4 pb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="ph-user-plus fs-xl text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">New Student Enrollment</h6>
                                            <p class="text-muted mb-1">5 new students joined the school today</p>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> Just now</small>
                                        </div>
                                    </div>
                                    <div class="activity-item d-flex align-items-start mb-4 pb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="ph-chalkboard-teacher fs-xl text-success"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Staff Meeting Scheduled</h6>
                                            <p class="text-muted mb-1">Monthly staff meeting set for Friday 10:00 AM</p>
                                            <small class="text-muted"><i class="bi bi-calendar me-1"></i> Tomorrow</small>
                                        </div>
                                    </div>
                                    <div class="activity-item d-flex align-items-start mb-4 pb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="ph-file-text fs-xl text-warning"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Exam Results Published</h6>
                                            <p class="text-muted mb-1">Terminal examination results are now available</p>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> 2 hours ago</small>
                                        </div>
                                    </div>
                                    <div class="activity-item d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="ph-currency-circle-dollar fs-xl text-info"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Payment Received</h6>
                                            <p class="text-muted mb-1">School fee payments processed for 25 students</p>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> Yesterday</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-5">
                        <div class="card chart-container">
                            <div class="card-header bg-transparent">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                                <p class="text-muted small mb-0">Frequently used features</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <a href="{{ route('student.index') }}" class="text-decoration-none">
                                            <div class="p-3 text-center border rounded-3 hover-scale transition-all">
                                                <i class="ph-users-four fs-2xl text-primary"></i>
                                                <h6 class="mt-2 mb-0">Manage Students</h6>
                                                <small class="text-muted">View all students</small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('staff.payments.dashboard') }}" class="text-decoration-none">
                                            <div class="p-3 text-center border rounded-3 hover-scale transition-all">
                                                <i class="ph-wallet fs-2xl text-success"></i>
                                                <h6 class="mt-2 mb-0">Payroll</h6>
                                                <small class="text-muted">Staff payments</small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('exams.index') }}" class="text-decoration-none">
                                            <div class="p-3 text-center border rounded-3 hover-scale transition-all">
                                                <i class="ph-clipboard-text fs-2xl text-warning"></i>
                                                <h6 class="mt-2 mb-0">Examinations</h6>
                                                <small class="text-muted">Manage exams</small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('attendance.my-classes') }}" class="text-decoration-none">
                                            <div class="p-3 text-center border rounded-3 hover-scale transition-all">
                                                <i class="ph-calendar-check fs-2xl text-info"></i>
                                                <h6 class="mt-2 mb-0">Attendance</h6>
                                                <small class="text-muted">Mark attendance</small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endhasrole
        </div>
    </div>
</div>

<!-- JAVASCRIPT -->
<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Counter animation function
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString();
                }
            }, 20);
        }

        // Initialize counter animations
        const counters = document.querySelectorAll('.counter-value');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            if (target > 0) {
                animateCounter(counter, target);
            } else {
                counter.textContent = '0';
            }
        });

        // Population Distribution Chart (Doughnut)
        const ctx1 = document.getElementById('populationChart')?.getContext('2d');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: ['Male Students', 'Female Students', 'Staff'],
                    datasets: [{
                        data: [{{ $gender_counts['Male'] }}, {{ $gender_counts['Female'] }}, {{ $staff_count }}],
                        backgroundColor: [
                            'rgba(14, 165, 233, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(245, 158, 11, 0.8)'
                        ],
                        borderColor: [
                            'rgba(14, 165, 233, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(245, 158, 11, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%',
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1500
                    }
                }
            });
        }

        // Stats Overview Chart (Bar)
        const ctx2 = document.getElementById('statsChart')?.getContext('2d');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Male', 'Female', 'Staff', 'Old Students', 'New Students'],
                    datasets: [{
                        label: 'Count',
                        data: [
                            {{ $gender_counts['Male'] }},
                            {{ $gender_counts['Female'] }},
                            {{ $staff_count }},
                            {{ $status_counts['Old Student'] ?? 0 }},
                            {{ $status_counts['New Student'] ?? 0 }}
                        ],
                        backgroundColor: [
                            'rgba(14, 165, 233, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(139, 92, 246, 0.7)'
                        ],
                        borderRadius: 8,
                        barPercentage: 0.65,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Count: ${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5],
                                drawBorder: true
                            },
                            ticks: {
                                stepSize: Math.ceil(Math.max(
                                    {{ $gender_counts['Male'] }},
                                    {{ $gender_counts['Female'] }},
                                    {{ $staff_count }}
                                ) / 5)
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Add hover scale effect to quick action cards
        const actionCards = document.querySelectorAll('.hover-scale');
        actionCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px -5px rgba(0,0,0,0.1)';
                this.style.transition = 'all 0.3s ease';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
</script>

<style>
    .hover-scale {
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        cursor: pointer;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .bg-purple {
        background-color: #8b5cf6;
    }

    .text-purple {
        color: #8b5cf6;
    }

    .bg-cyan {
        background-color: #06b6d4;
    }

    .text-cyan {
        color: #06b6d4;
    }

    .bg-opacity-10 {
        opacity: 0.1;
    }

    /* Custom animation delays */
    .activity-item {
        animation: slideInRight 0.5s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>
@endsection
